<?php

namespace App\Services;

use App\Enums\PlacementTestStatus;
use App\Enums\WritingSubmissionStatus;
use App\Models\LessonProgress;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\RoadmapWeekLesson;
use App\Models\WritingSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function forUser(int $userId): array
    {
        $latestPlacementTest = PlacementTest::latestAnalyzedFor($userId)->first();

        $latestRoadmap = Roadmap::latestForUser($userId)->with('roadmapWeeks')->first();

        $cefrLevel = $latestPlacementTest?->cefr_level;
        $currentWeekNumber = null;

        if ($latestRoadmap && $latestRoadmap->roadmapWeeks->isNotEmpty()) {
            $completedInRoadmap = $this->completedRoadmapLessonIds($userId, $latestRoadmap->id);
            $totalRoadmapLessons = RoadmapWeekLesson::whereHas('roadmapWeek', fn ($q) => $q->where('roadmap_id', $latestRoadmap->id))
                ->count();

            foreach ($latestRoadmap->roadmapWeeks->sortBy('week_number') as $week) {
                $weekLessonIds = RoadmapWeekLesson::where('roadmap_week_id', $week->id)
                    ->pluck('lesson_id')
                    ->toArray();

                $weekCompleted = array_intersect($weekLessonIds, $completedInRoadmap);

                if (count($weekCompleted) < count($weekLessonIds)) {
                    $currentWeekNumber = $week->week_number;
                    break;
                }
            }

            if ($currentWeekNumber === null && $latestRoadmap->roadmapWeeks->isNotEmpty()) {
                $currentWeekNumber = $latestRoadmap->roadmapWeeks->max('week_number');
            }
        }

        $completedLessons = LessonProgress::completedFor($userId)->count();

        $totalLessons = DB::table('lessons')->count();

        $writingHistory = WritingSubmission::where('user_id', $userId)
            ->where('status', WritingSubmissionStatus::Corrected->value)
            ->whereNotNull('score')
            ->orderBy('submitted_at')
            ->get(['submitted_at', 'score']);

        $grammarImprovement = $this->computeSkillTrend($userId, 'grammar_score');
        $vocabularyImprovement = $this->computeSkillTrend($userId, 'vocabulary_score');

        $learningStreak = $this->computeStreak($userId);

        $totalRoadmapLessons = $latestRoadmap
            ? RoadmapWeekLesson::whereHas('roadmapWeek', fn ($q) => $q->where('roadmap_id', $latestRoadmap->id))->count()
            : 0;

        $completedRoadmapLessons = $latestRoadmap
            ? count($this->completedRoadmapLessonIds($userId, $latestRoadmap->id))
            : 0;

        // Overall progress: percentage of lessons in the student's current roadmap that have been marked completed.
        $overallProgressPercentage = $totalRoadmapLessons > 0
            ? round(($completedRoadmapLessons / $totalRoadmapLessons) * 100, 1)
            : 0;

        $nextAction = null;
        if ($latestRoadmap) {
            $nextAction = [
                'lesson_id' => $latestRoadmap->next_lesson_id,
                'topic' => $latestRoadmap->next_topic,
                'writing_prompt' => $latestRoadmap->next_writing_prompt,
            ];

            if ($latestRoadmap->next_lesson_id) {
                $nextLesson = DB::table('lessons')->find($latestRoadmap->next_lesson_id);
                $nextAction['lesson_title'] = $nextLesson?->title;
            } else {
                $nextAction['lesson_title'] = null;
            }
        }

        return [
            'cefr_level' => $cefrLevel,
            'current_week' => $currentWeekNumber,
            'lessons' => [
                'completed' => $completedLessons,
                'total' => $totalLessons,
            ],
            'writing_score_history' => $writingHistory->toArray(),
            'grammar_improvement' => $grammarImprovement,
            'vocabulary_improvement' => $vocabularyImprovement,
            'learning_streak' => $learningStreak,
            'overall_progress_percentage' => $overallProgressPercentage,
            'next_recommended_action' => $nextAction,
        ];
    }

    private function completedRoadmapLessonIds(int $userId, int $roadmapId): array
    {
        $roadmapLessonIds = RoadmapWeekLesson::whereHas('roadmapWeek', fn ($q) => $q->where('roadmap_id', $roadmapId))
            ->pluck('lesson_id')
            ->toArray();

        return LessonProgress::completedFor($userId)
            ->whereIn('lesson_id', $roadmapLessonIds)
            ->pluck('lesson_id')
            ->toArray();
    }

    private function computeSkillTrend(int $userId, string $field): array
    {
        $scores = PlacementTest::where('user_id', $userId)
            ->where('status', PlacementTestStatus::Analyzed)
            ->whereNotNull($field)
            ->orderBy('id')
            ->pluck($field)
            ->toArray();

        if (count($scores) >= 2) {
            $start = (float) $scores[0];
            $end = (float) $scores[count($scores) - 1];

            return [
                'trend' => $this->direction($start, $end),
                'start_score' => $start,
                'current_score' => $end,
            ];
        }

        $writingScores = WritingSubmission::where('user_id', $userId)
            ->where('status', WritingSubmissionStatus::Corrected->value)
            ->whereNotNull('score')
            ->orderBy('submitted_at')
            ->pluck('score')
            ->toArray();

        $allScores = array_merge(
            array_map('floatval', $scores),
            array_map('floatval', $writingScores)
        );

        if (count($allScores) >= 2) {
            $start = $allScores[0];
            $end = $allScores[count($allScores) - 1];

            return [
                'trend' => $this->direction($start, $end),
                'start_score' => $start,
                'current_score' => $end,
            ];
        }

        if (count($allScores) === 1) {
            return [
                'trend' => 'insufficient_data',
                'start_score' => $allScores[0],
                'current_score' => $allScores[0],
            ];
        }

        return [
            'trend' => 'insufficient_data',
            'start_score' => null,
            'current_score' => null,
        ];
    }

    private function direction(float $start, float $end): string
    {
        $diff = $end - $start;

        if (abs($diff) < 2) {
            return 'stable';
        }

        return $diff > 0 ? 'improving' : 'declining';
    }

    private function computeStreak(int $userId): int
    {
        $dates = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->select(DB::raw('DATE(completed_at) as activity_date'))
            ->union(
                DB::table('quiz_attempts')
                    ->where('user_id', $userId)
                    ->whereNotNull('completed_at')
                    ->select(DB::raw('DATE(completed_at) as activity_date'))
            )
            ->union(
                DB::table('writing_submissions')
                    ->where('user_id', $userId)
                    ->whereNotNull('submitted_at')
                    ->select(DB::raw('DATE(submitted_at) as activity_date'))
            )
            ->orderByDesc('activity_date')
            ->pluck('activity_date');

        if ($dates->isEmpty()) {
            return 0;
        }

        $uniqueDates = $dates->unique()->sortDesc()->values();
        $streak = 0;
        $expected = now()->startOfDay();

        $firstDate = Carbon::parse($uniqueDates->first())->startOfDay();

        if ($firstDate->ne($expected)) {
            if ($firstDate->eq($expected->copy()->subDay())) {
                $expected = $firstDate;
            } else {
                return 0;
            }
        }

        foreach ($uniqueDates as $dateStr) {
            $date = Carbon::parse($dateStr)->startOfDay();

            if ($date->ne($expected)) {
                break;
            }

            $streak++;
            $expected = $expected->subDay();
        }

        return $streak;
    }
}

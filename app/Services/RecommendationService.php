<?php

namespace App\Services;

use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\Roadmap;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function refreshForUser(int $userId): void
    {
        $roadmap = Roadmap::latestForUser($userId)
            ->with('roadmapWeeks.roadmapWeekLessons.lesson')
            ->first();

        if (! $roadmap) {
            return;
        }

        $completedLessonIds = LessonProgress::completedFor($userId)->pluck('lesson_id')->toArray();

        $nextLesson = null;
        $nextTopic = null;
        $nextWritingPrompt = null;

        foreach ($roadmap->roadmapWeeks->sortBy('week_number') as $week) {
            $weekLessons = $week->roadmapWeekLessons->sortBy('display_order');

            foreach ($weekLessons as $wl) {
                if (! $wl->lesson) {
                    continue;
                }

                if (! in_array($wl->lesson_id, $completedLessonIds, true)) {
                    $nextLesson = $wl->lesson;
                    $nextTopic = $wl->lesson?->skill?->value;
                    break 2;
                }
            }
        }

        if ($nextLesson) {
            $quiz = $nextLesson->quizzes()->first();
            if ($quiz) {
                $attemptCount = DB::table('quiz_attempts')
                    ->where('user_id', $userId)
                    ->where('quiz_id', $quiz->id)
                    ->count();

                if ($attemptCount === 0) {
                    $nextWritingPrompt = "Complete the quiz for \"{$nextLesson->title}\" first.";
                }
            }

            if (! $nextWritingPrompt) {
                $latestSubmission = DB::table('writing_submissions')
                    ->where('user_id', $userId)
                    ->latest('id')
                    ->first();

                $nextWritingPrompt = $latestSubmission
                    ? "Write a short paragraph using what you learned in \"{$nextLesson->title}\"."
                    : 'Write a short paragraph introducing yourself and your English learning goals.';
            }
        }

        $oldNextLessonId = $roadmap->next_lesson_id;
        $oldNextTopic = $roadmap->next_topic;
        $oldNextWritingPrompt = $roadmap->next_writing_prompt;

        $roadmap->update([
            'next_lesson_id' => $nextLesson?->id,
            'next_topic' => $nextTopic,
            'next_writing_prompt' => $nextWritingPrompt,
        ]);

        $changed = $roadmap->next_lesson_id !== $oldNextLessonId
            || $roadmap->next_topic !== $oldNextTopic
            || $roadmap->next_writing_prompt !== $oldNextWritingPrompt;

        if ($changed) {
            Notification::create([
                'user_id' => $userId,
                'title' => 'New recommendations available',
                'message' => 'Your next recommended action has been updated based on your recent activity.',
            ]);
        }
    }
}

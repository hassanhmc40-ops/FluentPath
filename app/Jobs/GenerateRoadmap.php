<?php

namespace App\Jobs;

use App\Agents\RoadmapGenerationAgent;
use App\Enums\RoadmapStatus;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GenerateRoadmap implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public Roadmap $roadmap,
    ) {}

    public function handle(): void
    {
        $this->roadmap->loadMissing('placementTest');

        $this->roadmap->update(['status' => RoadmapStatus::Processing]);

        $placementTest = $this->roadmap->placementTest;

        $lessons = $this->lessonsForLevel($placementTest->cefr_level?->value);

        $prompt = "You are an expert English teacher creating a personalized 4-week learning roadmap.

Placement Test Results:
- CEFR Level: {$placementTest->cefr_level?->value}
- Grammar Score: {$placementTest->grammar_score}
- Vocabulary Score: {$placementTest->vocabulary_score}
- Writing Score: {$placementTest->writing_score}
- Strengths: ".implode(', ', $placementTest->strengths ?? []).'
- Weaknesses: '.implode(', ', $placementTest->weaknesses ?? []).'

Available Lessons (id, title, skill, level):
'.$lessons->map(fn ($l) => "- {$l->id}: {$l->title} ({$l->skill?->value}, {$l->level?->value})")->implode("\n")."

The learner should primarily work with lessons at or near their CEFR level ({$placementTest->cefr_level?->value}); the lessons listed above have already been filtered to their level plus one level above and one level below.

Create a 4-week roadmap. For each week provide an objective and a list of lesson_ids from the available lessons above that best address the learner's weaknesses while building on their strengths. Order lessons within each week by recommended sequence.";

        try {
            $agent = new RoadmapGenerationAgent(
                instructions: 'You are an expert English teacher designing personalized learning roadmaps.',
                messages: [],
                tools: [],
            );

            $response = $agent->prompt($prompt);

            $data = $response->toArray();

            if (! $this->isValidResponse($data)) {
                Log::error('GenerateRoadmap AI response failed validation', [
                    'roadmap_id' => $this->roadmap->id,
                    'response' => $data,
                ]);

                $this->roadmap->update(['status' => RoadmapStatus::Failed]);

                return;
            }

            $allLessonIds = collect($data['weeks'])->pluck('lesson_ids')->flatten()->unique()->values();

            $existingIds = Lesson::whereIn('id', $allLessonIds)->pluck('id')->toArray();

            $missingIds = $allLessonIds->diff($existingIds)->values();

            if ($missingIds->isNotEmpty()) {
                Log::error('GenerateRoadmap BR04 violation: non-existent lesson_ids', [
                    'roadmap_id' => $this->roadmap->id,
                    'missing_lesson_ids' => $missingIds->toArray(),
                ]);

                $this->roadmap->update(['status' => RoadmapStatus::Failed]);

                return;
            }

            foreach ($data['weeks'] as $weekData) {
                $week = RoadmapWeek::create([
                    'roadmap_id' => $this->roadmap->id,
                    'week_number' => $weekData['week_number'],
                    'objective' => $weekData['objective'],
                ]);

                foreach ($weekData['lesson_ids'] as $order => $lessonId) {
                    $week->roadmapWeekLessons()->create([
                        'lesson_id' => $lessonId,
                        'display_order' => $order + 1,
                    ]);
                }
            }

            $this->roadmap->update([
                'title' => $data['title'],
                'status' => RoadmapStatus::Generated,
                'generated_at' => now(),
            ]);

            Notification::create([
                'user_id' => $this->roadmap->user_id,
                'title' => 'Personalized Roadmap Generated',
                'message' => 'Your personalized learning roadmap has been generated.',
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateRoadmap AI generation failed', [
                'roadmap_id' => $this->roadmap->id,
                'error' => $e->getMessage(),
            ]);

            $this->roadmap->update(['status' => RoadmapStatus::Failed]);
        }
    }

    protected function lessonsForLevel(?string $level): Collection
    {
        $query = Lesson::select('id', 'title', 'skill', 'level');

        $lessons = $query->get();

        if ($level === null) {
            return $lessons;
        }

        $levels = ['A1', 'A2', 'B1', 'B2', 'C1'];
        $index = array_search($level, $levels, true);

        if ($index === false) {
            return $lessons;
        }

        $minimum = $levels[max(0, $index - 1)];
        $maximum = $levels[min(count($levels) - 1, $index + 1)];

        // Level-biased catalog: the learner's level plus one above and one below.
        // Fall back to the full catalog when the filter would leave nothing
        // for the agent to choose from.
        $biased = (clone $query)->whereBetween('level', [$minimum, $maximum])->get();

        return $biased->isNotEmpty() ? $biased : $lessons;
    }

    protected function isValidResponse(mixed $data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        if (! isset($data['title']) || ! is_string($data['title'])) {
            return false;
        }

        if (! isset($data['weeks']) || ! is_array($data['weeks'])) {
            return false;
        }

        if (count($data['weeks']) !== 4) {
            return false;
        }

        foreach ($data['weeks'] as $week) {
            if (! isset($week['week_number']) || ! isset($week['objective']) || ! isset($week['lesson_ids'])) {
                return false;
            }

            if (! is_int($week['week_number']) || $week['week_number'] < 1 || $week['week_number'] > 4) {
                return false;
            }

            if (! is_string($week['objective']) || empty($week['objective'])) {
                return false;
            }

            if (! is_array($week['lesson_ids']) || empty($week['lesson_ids'])) {
                return false;
            }

            foreach ($week['lesson_ids'] as $id) {
                if (! is_int($id)) {
                    return false;
                }
            }
        }

        return true;
    }
}

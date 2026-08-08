<?php

namespace App\Jobs;

use App\Agents\PlacementEvaluationAgent;
use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use App\Enums\RoadmapStatus;
use App\Enums\Skill;
use App\Models\Notification;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class EvaluatePlacementTest implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /** Skills that are graded deterministically (MCQ). */
    protected const AUTO_SCORED_SKILLS = ['grammar', 'vocabulary', 'reading'];

    /** The AI evaluation of a full submission (all writing essays) can take a while. */
    public int $timeout = 300;

    /** Retry transient provider errors (rate limits, 5xx) with backoff. */
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public PlacementTest $placementTest,
    ) {}

    public function handle(): void
    {
        $this->placementTest->loadMissing('placementAnswers.placementQuestion');

        $this->placementTest->update(['status' => PlacementTestStatus::Processing]);

        $answersBySkill = $this->placementTest->placementAnswers
            ->groupBy(fn ($a) => $a->placementQuestion->skill->value)
            ->map(fn ($group) => $group->map(fn ($a) => [
                'question' => $a->placementQuestion->question,
                'answer' => $a->answer,
                'correct_answer' => $a->placementQuestion->correct_answer,
            ]));

        $autoScores = $this->autoScore($answersBySkill);

        $summary = collect(self::AUTO_SCORED_SKILLS)->map(function (string $skill) use ($autoScores, $answersBySkill) {
            $answered = $answersBySkill->get($skill, collect())->count();
            $total = $autoScores[$skill]['total'] ?? $answered;
            $skipped = $total - $answered;

            $line = sprintf('%s score: %s (%d/%d answered, %d skipped)', ucfirst($skill), $autoScores[$skill]['score'], $answered, $total, $skipped);

            return $answered === 0 ? $line.' — the learner skipped the whole part' : $line;
        })->implode("\n");

        $writingCount = $answersBySkill->get('writing', collect())->count();
        $writingTotal = $autoScores['writing']['total'];

        $prompt = 'You are an expert English language teacher. Evaluate this placement test submission.

Auto-scored sections (already graded deterministically, out of 100; skipped questions count as incorrect):
'.$summary.'
Writing answers ('.$writingCount.' of '.$writingTotal.' prompts answered; grade the answers below):'."\n".json_encode($answersBySkill->get('writing', []), JSON_PRETTY_PRINT).
"\n\nAssess the learner's overall CEFR level (A1-C1), assign a writing score (0-100), list strengths and weaknesses, and provide a brief reasoning for your assessment.";

        try {
            $agent = new PlacementEvaluationAgent(
                instructions: 'You are an expert English teacher evaluating a placement test.',
                messages: [],
                tools: [],
            );

            $response = $agent->prompt($prompt, timeout: 120);

            $data = $response->toArray();

            if (! $this->isValidResponse($data)) {
                Log::error('PlacementTest AI response failed validation', [
                    'placement_test_id' => $this->placementTest->id,
                    'response' => $data,
                ]);

                $this->placementTest->update(['status' => PlacementTestStatus::Failed]);

                return;
            }

            $this->placementTest->update([
                'status' => PlacementTestStatus::Analyzed,
                'cefr_level' => $data['cefr_level'],
                'grammar_score' => $autoScores['grammar']['score'],
                'vocabulary_score' => $autoScores['vocabulary']['score'],
                'reading_score' => $autoScores['reading']['score'],
                'writing_score' => $data['writing_score'],
                'strengths' => $data['strengths'],
                'weaknesses' => $data['weaknesses'],
                'reasoning' => $data['reasoning'],
            ]);

            Notification::create([
                'user_id' => $this->placementTest->user_id,
                'title' => 'Placement Test Analyzed',
                'message' => 'Your placement test has been analyzed.',
            ]);

            // Kick off the roadmap right after a successful evaluation so the
            // student lands on a generating roadmap (never a dead end). Guarded
            // with firstOrCreate so a queue retry of this job can't duplicate it.
            $roadmap = Roadmap::firstOrCreate(
                ['placement_test_id' => $this->placementTest->id],
                [
                    'user_id' => $this->placementTest->user_id,
                    'title' => 'Generating...',
                    'status' => RoadmapStatus::Pending,
                ],
            );

            if ($roadmap->wasRecentlyCreated) {
                GenerateRoadmap::dispatch($roadmap);
            }
        } catch (Throwable $e) {
            Log::error('PlacementTest AI evaluation failed', [
                'placement_test_id' => $this->placementTest->id,
                'error' => $e->getMessage(),
            ]);

            // Let the queue retry transient failures (see $tries/$backoff);
            // the failed() hook marks the test as failed once attempts run out.
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('PlacementTest AI evaluation failed after retries', [
            'placement_test_id' => $this->placementTest->id,
            'error' => $e->getMessage(),
        ]);

        $this->placementTest->update(['status' => PlacementTestStatus::Failed]);
    }

    /**
     * Deterministically score the MCQ skills. Skipped questions count as
     * incorrect: score = correct answers over the TOTAL number of questions
     * for the skill in the catalog, scaled to 100 with two decimals.
     *
     * @param  Collection<string, Collection<int, array{question: string, answer: string, correct_answer: ?string}>>  $answersBySkill
     * @return array<string, array{score: float, total: int}>
     */
    protected function autoScore($answersBySkill): array
    {
        $totals = PlacementQuestion::query()
            ->whereIn('skill', self::AUTO_SCORED_SKILLS)
            ->selectRaw('skill, count(*) as total')
            ->groupBy('skill')
            ->pluck('total', 'skill');

        $scores = [];

        foreach (self::AUTO_SCORED_SKILLS as $skill) {
            $total = (int) ($totals[$skill] ?? 0);

            $answers = $answersBySkill->get($skill, collect());

            $correct = $answers->filter(fn (array $a) => $a['correct_answer'] !== null
                && strtolower((string) $a['answer']) === strtolower($a['correct_answer']))->count();

            $scores[$skill] = [
                'score' => $total > 0 ? round(($correct / $total) * 100, 2) : 0.0,
                'total' => $total,
            ];
        }

        $scores['writing'] = ['total' => PlacementQuestion::where('skill', Skill::Writing)->count()];

        return $scores;
    }

    protected function isValidResponse(mixed $data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        $requiredKeys = ['cefr_level', 'writing_score', 'strengths', 'weaknesses', 'reasoning'];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                return false;
            }
        }

        if (! CefrLevel::tryFrom($data['cefr_level'])) {
            return false;
        }

        if (! is_numeric($data['writing_score'])) {
            return false;
        }

        if (! is_array($data['strengths']) || ! is_array($data['weaknesses'])) {
            return false;
        }

        if (! is_string($data['reasoning'])) {
            return false;
        }

        return true;
    }
}

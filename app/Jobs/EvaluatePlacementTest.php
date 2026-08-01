<?php

namespace App\Jobs;

use App\Agents\PlacementEvaluationAgent;
use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use App\Models\Notification;
use App\Models\PlacementTest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvaluatePlacementTest implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

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
            ]));

        $prompt = "You are an expert English language teacher. Evaluate this placement test submission holistically.

Grammar answers:\n".json_encode($answersBySkill->get('grammar', []), JSON_PRETTY_PRINT).
"\n\nVocabulary answers:\n".json_encode($answersBySkill->get('vocabulary', []), JSON_PRETTY_PRINT).
"\n\nWriting answers:\n".json_encode($answersBySkill->get('writing', []), JSON_PRETTY_PRINT).
"\n\nAssess the learner's overall CEFR level (A1-C1), assign per-skill scores (0-100), list strengths and weaknesses, and provide a brief reasoning for your assessment.";

        try {
            $agent = new PlacementEvaluationAgent(
                instructions: 'You are an expert English teacher evaluating a placement test.',
                messages: [],
                tools: [],
            );

            $response = $agent->prompt($prompt);

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
                'grammar_score' => $data['grammar_score'],
                'vocabulary_score' => $data['vocabulary_score'],
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
        } catch (\Throwable $e) {
            Log::error('PlacementTest AI evaluation failed', [
                'placement_test_id' => $this->placementTest->id,
                'error' => $e->getMessage(),
            ]);

            $this->placementTest->update(['status' => PlacementTestStatus::Failed]);
        }
    }

    protected function isValidResponse(mixed $data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        $requiredKeys = ['cefr_level', 'grammar_score', 'vocabulary_score', 'writing_score', 'strengths', 'weaknesses', 'reasoning'];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                return false;
            }
        }

        if (! CefrLevel::tryFrom($data['cefr_level'])) {
            return false;
        }

        if (! is_numeric($data['grammar_score']) || ! is_numeric($data['vocabulary_score']) || ! is_numeric($data['writing_score'])) {
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

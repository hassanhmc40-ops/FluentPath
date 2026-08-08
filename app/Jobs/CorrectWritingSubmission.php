<?php

namespace App\Jobs;

use App\Agents\WritingCorrectionAgent;
use App\Enums\WritingSubmissionStatus;
use App\Events\WritingCorrected;
use App\Models\Notification;
use App\Models\WritingSubmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CorrectWritingSubmission implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public WritingSubmission $writingSubmission,
    ) {}

    public function handle(): void
    {
        $this->writingSubmission->update(['status' => WritingSubmissionStatus::Processing]);

        $prompt = "You are an expert English teacher. Correct the following writing submission.

Prompt: {$this->writingSubmission->prompt}

Original text:
{$this->writingSubmission->original_text}

Please provide a corrected version, a score (0-100), detailed feedback on grammar, vocabulary, and fluency, a list of specific mistakes with corrections and explanations, recommendations for improvement, and suggested next topics to study.";

        try {
            $agent = new WritingCorrectionAgent(
                instructions: 'You are an expert English teacher correcting a writing submission.',
                messages: [],
                tools: [],
            );

            $response = $agent->prompt($prompt, timeout: 120);

            $data = $response->toArray();

            if (! $this->isValidResponse($data)) {
                Log::error('WritingSubmission AI response failed validation', [
                    'writing_submission_id' => $this->writingSubmission->id,
                    'response' => $data,
                ]);

                $this->writingSubmission->update(['status' => WritingSubmissionStatus::Failed]);

                return;
            }

            $this->writingSubmission->update([
                'status' => WritingSubmissionStatus::Corrected,
                'corrected_text' => $data['corrected_text'],
                'score' => $data['score'],
                'grammar_feedback' => $data['grammar_feedback'],
                'vocabulary_feedback' => $data['vocabulary_feedback'],
                'fluency_feedback' => $data['fluency_feedback'],
                'mistakes' => $data['mistakes'],
                'recommendations' => $data['recommendations'],
                'next_topics' => $data['next_topics'],
            ]);

            Notification::create([
                'user_id' => $this->writingSubmission->user_id,
                'title' => 'Writing Correction Completed',
                'message' => 'Your writing submission has been reviewed.',
            ]);

            WritingCorrected::dispatch(
                $this->writingSubmission->user_id,
                $this->writingSubmission->id,
            );
        } catch (Throwable $e) {
            Log::error('WritingSubmission AI correction failed', [
                'writing_submission_id' => $this->writingSubmission->id,
                'error' => $e->getMessage(),
            ]);

            // Let the queue retry transient failures (see $tries/$backoff);
            // the failed() hook marks the submission as failed once attempts run out.
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('WritingSubmission AI correction failed after retries', [
            'writing_submission_id' => $this->writingSubmission->id,
            'error' => $e->getMessage(),
        ]);

        $this->writingSubmission->update(['status' => WritingSubmissionStatus::Failed]);
    }

    protected function isValidResponse(mixed $data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        $requiredKeys = ['corrected_text', 'score', 'grammar_feedback', 'vocabulary_feedback', 'fluency_feedback', 'mistakes', 'recommendations', 'next_topics'];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                return false;
            }
        }

        if (! is_string($data['corrected_text']) || empty($data['corrected_text'])) {
            return false;
        }

        if (! is_numeric($data['score']) || $data['score'] < 0 || $data['score'] > 100) {
            return false;
        }

        if (! is_string($data['grammar_feedback']) || ! is_string($data['vocabulary_feedback']) || ! is_string($data['fluency_feedback'])) {
            return false;
        }

        if (! is_array($data['mistakes'])) {
            return false;
        }

        foreach ($data['mistakes'] as $mistake) {
            if (! is_array($mistake) || ! isset($mistake['original'], $mistake['correction'], $mistake['rule'])) {
                return false;
            }
        }

        if (! is_array($data['recommendations']) || ! is_array($data['next_topics'])) {
            return false;
        }

        foreach ($data['recommendations'] as $r) {
            if (! is_string($r)) {
                return false;
            }
        }

        foreach ($data['next_topics'] as $t) {
            if (! is_string($t)) {
                return false;
            }
        }

        return true;
    }
}

<?php

use App\Agents\WritingCorrectionAgent;
use App\Enums\WritingSubmissionStatus;
use App\Events\WritingCorrected;
use App\Jobs\CorrectWritingSubmission;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Ai as AI;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function validCorrection(float $score = 87.5): array
{
    return [
        'corrected_text' => 'The corrected version of the paragraph.',
        'score' => $score,
        'grammar_feedback' => 'Good use of tenses, but watch subject-verb agreement.',
        'vocabulary_feedback' => 'Nice range of words.',
        'fluency_feedback' => 'Sentences flow well.',
        'mistakes' => [
            ['original' => 'go', 'correction' => 'went', 'rule' => 'Past Simple required for past actions'],
            ['original' => 'she don\'t', 'correction' => 'she doesn\'t', 'rule' => 'Third-person singular needs "doesn\'t"'],
        ],
        'recommendations' => ['Review Past Simple tense', 'Practice subject-verb agreement'],
        'next_topics' => ['Past Simple', 'Subject-Verb Agreement'],
    ];
}

describe('submission', function () {
    it('accepts a writing submission and dispatches the correction job', function () {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/writing-submissions', [
            'prompt' => 'Describe your daily routine.',
            'original_text' => 'Every day I wake up at seven and I eat breakfast.',
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['id', 'status'])
            ->assertJsonPath('status', 'pending');

        $id = $response->json('id');

        $this->assertDatabaseHas('writing_submissions', [
            'id' => $id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Queue::assertPushed(CorrectWritingSubmission::class, fn (CorrectWritingSubmission $job) => $job->writingSubmission->id === $id);
    });

    it('rejects text shorter than 10 characters with 422 and dispatches nothing', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/writing-submissions', [
            'prompt' => 'Describe your daily routine.',
            'original_text' => 'Short.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('original_text');

        $this->assertDatabaseCount('writing_submissions', 0);

        Queue::assertNotPushed(CorrectWritingSubmission::class);
    });

    it('rejects a missing prompt with 422', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/writing-submissions', [
            'original_text' => 'A sufficiently long original text.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('prompt');

        Queue::assertNotPushed(CorrectWritingSubmission::class);
    });

    it('requires authentication', function () {
        $this->postJson('/api/writing-submissions', [
            'prompt' => 'Prompt.',
            'original_text' => 'A sufficiently long original text.',
        ])->assertStatus(401);
    });
});

describe('show', function () {
    it('returns the submission to its owner', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $submission = WritingSubmission::factory()->corrected()->create(['user_id' => $user->id]);

        $this->getJson("/api/writing-submissions/{$submission->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $submission->id)
            ->assertJsonPath('data.status', 'corrected')
            ->assertJsonPath('data.corrected_text', $submission->corrected_text)
            ->assertJsonCount(2, 'data.mistakes')
            ->assertJsonStructure([
                'data' => [
                    'id', 'user_id', 'prompt', 'original_text', 'corrected_text', 'score',
                    'grammar_feedback', 'vocabulary_feedback', 'fluency_feedback',
                    'mistakes', 'recommendations', 'next_topics', 'status', 'submitted_at',
                    'created_at', 'updated_at',
                ],
            ]);
    });

    it('forbids viewing another student\'s submission with 403', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $submission = WritingSubmission::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/writing-submissions/{$submission->id}")->assertStatus(403);
    });

    it('returns 404 for a non-existent submission', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/writing-submissions/999999')->assertStatus(404);
    });
});

describe('correction job', function () {
    it('persists the full correction and marks the submission corrected', function () {
        Event::fake([WritingCorrected::class]);

        $user = User::factory()->create();
        $submission = WritingSubmission::factory()->create(['user_id' => $user->id]);

        AI::fakeAgent(WritingCorrectionAgent::class, [validCorrection(87.5)]);

        (new CorrectWritingSubmission($submission))->handle();

        $submission->refresh();

        expect($submission->status)->toBe(WritingSubmissionStatus::Corrected)
            ->and($submission->corrected_text)->toBe('The corrected version of the paragraph.')
            ->and($submission->score)->toBe('87.50')
            ->and($submission->grammar_feedback)->toBe('Good use of tenses, but watch subject-verb agreement.')
            ->and($submission->mistakes)->toBe([
                ['original' => 'go', 'correction' => 'went', 'rule' => 'Past Simple required for past actions'],
                ['original' => 'she don\'t', 'correction' => 'she doesn\'t', 'rule' => 'Third-person singular needs "doesn\'t"'],
            ])
            ->and($submission->recommendations)->toBe(['Review Past Simple tense', 'Practice subject-verb agreement'])
            ->and($submission->next_topics)->toBe(['Past Simple', 'Subject-Verb Agreement']);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Writing Correction Completed',
        ]);

        Event::assertDispatched(
            WritingCorrected::class,
            fn (WritingCorrected $event) => $event->userId === $user->id && $event->writingSubmissionId === $submission->id
        );
    });

    it('accepts boundary scores of 0 and 100 (BR06)', function () {
        $user = User::factory()->create();

        foreach ([0, 100] as $score) {
            $submission = WritingSubmission::factory()->create(['user_id' => $user->id]);

            AI::fakeAgent(WritingCorrectionAgent::class, [validCorrection($score)]);

            (new CorrectWritingSubmission($submission))->handle();

            expect($submission->fresh()->status)->toBe(WritingSubmissionStatus::Corrected)
                ->and($submission->fresh()->score)->toBe(number_format($score, 2));
        }
    });

    it('marks the submission failed when the score is out of range (BR06)', function () {
        $user = User::factory()->create();
        $submission = WritingSubmission::factory()->create(['user_id' => $user->id]);

        AI::fakeAgent(WritingCorrectionAgent::class, [validCorrection(150)]);

        (new CorrectWritingSubmission($submission))->handle();

        $submission->refresh();

        expect($submission->status)->toBe(WritingSubmissionStatus::Failed)
            ->and($submission->corrected_text)->toBeNull()
            ->and($submission->score)->toBeNull()
            ->and($submission->grammar_feedback)->toBeNull();

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'title' => 'Writing Correction Completed',
        ]);
    });

    it('marks the submission failed when required keys are missing', function () {
        $user = User::factory()->create();
        $submission = WritingSubmission::factory()->create(['user_id' => $user->id]);

        $invalid = validCorrection();
        unset($invalid['corrected_text']);

        AI::fakeAgent(WritingCorrectionAgent::class, [$invalid]);

        (new CorrectWritingSubmission($submission))->handle();

        expect($submission->fresh()->status)->toBe(WritingSubmissionStatus::Failed);
    });

    it('marks the submission failed when a mistake entry is malformed', function () {
        $user = User::factory()->create();
        $submission = WritingSubmission::factory()->create(['user_id' => $user->id]);

        $invalid = validCorrection();
        $invalid['mistakes'] = [['original' => 'go']];

        AI::fakeAgent(WritingCorrectionAgent::class, [$invalid]);

        (new CorrectWritingSubmission($submission))->handle();

        expect($submission->fresh()->status)->toBe(WritingSubmissionStatus::Failed);
    });

    it('rethrows AI failures for queue retry and marks the submission failed via failed()', function () {
        $user = User::factory()->create();
        $submission = WritingSubmission::factory()->create(['user_id' => $user->id]);

        AI::fakeAgent(WritingCorrectionAgent::class, [
            fn () => throw new RuntimeException('Groq is down'),
        ]);

        $job = new CorrectWritingSubmission($submission);

        try {
            $job->handle();
            $this->fail('Expected the AI exception to propagate for queue retry.');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toBe('Groq is down');
        }

        expect($submission->fresh()->status)->toBe(WritingSubmissionStatus::Processing);

        $job->failed(new RuntimeException('Groq is down'));

        expect($submission->fresh()->status)->toBe(WritingSubmissionStatus::Failed);
    });
});

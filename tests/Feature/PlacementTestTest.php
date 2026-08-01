<?php

use App\Agents\PlacementEvaluationAgent;
use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use App\Jobs\EvaluatePlacementTest;
use App\Models\PlacementAnswer;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Ai as AI;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function validPlacementAnalysis(): array
{
    return [
        'cefr_level' => 'B1',
        'grammar_score' => 78,
        'vocabulary_score' => 65.5,
        'writing_score' => 70,
        'strengths' => ['Good grasp of tenses', 'Clear writing'],
        'weaknesses' => ['Phrasal verbs', 'Idioms'],
        'reasoning' => 'The learner demonstrates solid B1-level skills.',
    ];
}

function seedPlacementTestWithAnswers(int $userId): PlacementTest
{
    $questions = [
        PlacementQuestion::factory()->grammar()->create(),
        PlacementQuestion::factory()->vocabulary()->create(),
        PlacementQuestion::factory()->writing()->create(),
    ];

    $test = PlacementTest::factory()->create(['user_id' => $userId]);

    $test->placementAnswers()->createMany(
        collect($questions)->map(fn (PlacementQuestion $q) => [
            'placement_question_id' => $q->id,
            'answer' => 'Student answer.',
        ])->all()
    );

    return $test;
}

describe('submission', function () {
    it('accepts a placement test submission, stores answers and dispatches the evaluation job', function () {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $questions = collect([
            PlacementQuestion::factory()->grammar()->create(),
            PlacementQuestion::factory()->vocabulary()->create(),
        ]);

        $response = $this->postJson('/api/placement-tests', [
            'answers' => $questions->map(fn ($q) => [
                'placement_question_id' => $q->id,
                'answer' => 'My answer here.',
            ])->all(),
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['id', 'status'])
            ->assertJsonPath('status', 'pending');

        $id = $response->json('id');

        $this->assertDatabaseHas('placement_tests', [
            'id' => $id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('placement_answers', 2);

        Queue::assertPushed(EvaluatePlacementTest::class, fn (EvaluatePlacementTest $job) => $job->placementTest->id === $id);
    });

    it('rejects a submission referencing a non-existent question with 422 and does not dispatch a job', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/placement-tests', [
            'answers' => [
                ['placement_question_id' => 999999, 'answer' => 'Answer.'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.0.placement_question_id');

        $this->assertDatabaseCount('placement_tests', 0);

        Queue::assertNotPushed(EvaluatePlacementTest::class);
    });

    it('rejects duplicate questions in one submission with 422', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $question = PlacementQuestion::factory()->create();

        $this->postJson('/api/placement-tests', [
            'answers' => [
                ['placement_question_id' => $question->id, 'answer' => 'First.'],
                ['placement_question_id' => $question->id, 'answer' => 'Second.'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.1.placement_question_id');

        Queue::assertNotPushed(EvaluatePlacementTest::class);
    });

    it('rejects an empty answers payload with 422', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/placement-tests', ['answers' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors('answers');

        Queue::assertNotPushed(EvaluatePlacementTest::class);
    });

    it('requires authentication', function () {
        $this->postJson('/api/placement-tests', [
            'answers' => [['placement_question_id' => 1, 'answer' => 'X.']],
        ])->assertStatus(401);
    });
});

describe('show', function () {
    it('returns the test with its answers to the owner', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $test = seedPlacementTestWithAnswers($user->id);

        $this->getJson("/api/placement-tests/{$test->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $test->id)
            ->assertJsonCount(3, 'data.answers')
            ->assertJsonStructure([
                'data' => [
                    'id', 'status', 'submitted_at', 'cefr_level', 'grammar_score',
                    'vocabulary_score', 'writing_score', 'strengths', 'weaknesses',
                    'reasoning', 'answers' => [
                        '*' => ['id', 'placement_question_id', 'answer', 'score'],
                    ],
                ],
            ]);
    });

    it('forbids viewing another student\'s test with 403', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $test = seedPlacementTestWithAnswers($owner->id);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/placement-tests/{$test->id}")->assertStatus(403);
    });

    it('returns 404 for a non-existent test', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/placement-tests/999999')->assertStatus(404);
    });
});

describe('evaluation job', function () {
    it('marks the test as analyzed and persists the AI evaluation', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        (new EvaluatePlacementTest($test))->handle();

        $test->refresh();

        expect($test->status)->toBe(PlacementTestStatus::Analyzed)
            ->and($test->cefr_level)->toBe(CefrLevel::B1)
            ->and($test->grammar_score)->toBe('78.00')
            ->and($test->vocabulary_score)->toBe('65.50')
            ->and($test->writing_score)->toBe('70.00')
            ->and($test->strengths)->toBe(['Good grasp of tenses', 'Clear writing'])
            ->and($test->weaknesses)->toBe(['Phrasal verbs', 'Idioms'])
            ->and($test->reasoning)->toBe('The learner demonstrates solid B1-level skills.');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Placement Test Analyzed',
        ]);
    });

    it('sends the per-skill answers to the agent in the prompt', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        (new EvaluatePlacementTest($test))->handle();

        AI::assertAgentWasPrompted(
            PlacementEvaluationAgent::class,
            fn ($prompt) => $prompt->contains('Grammar answers') && $prompt->contains('Vocabulary answers') && $prompt->contains('Writing answers')
        );
    });

    it('marks the test as failed when the AI response violates the schema', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        $invalid = validPlacementAnalysis();
        $invalid['cefr_level'] = 'Z9';

        AI::fakeAgent(PlacementEvaluationAgent::class, [$invalid]);

        (new EvaluatePlacementTest($test))->handle();

        $test->refresh();

        expect($test->status)->toBe(PlacementTestStatus::Failed)
            ->and($test->cefr_level)->toBeNull()
            ->and($test->grammar_score)->toBeNull()
            ->and($test->reasoning)->toBeNull();

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'title' => 'Placement Test Analyzed',
        ]);
    });

    it('marks the test as failed when the AI response is missing required keys', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [['cefr_level' => 'B1']]);

        (new EvaluatePlacementTest($test))->handle();

        expect($test->fresh()->status)->toBe(PlacementTestStatus::Failed);
    });

    it('marks the test as failed when the AI call throws', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [
            fn () => throw new RuntimeException('Groq is down'),
        ]);

        (new EvaluatePlacementTest($test))->handle();

        expect($test->fresh()->status)->toBe(PlacementTestStatus::Failed);
    });

    it('does not leave partial data behind on failure', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [['grammar_score' => 90]]);

        (new EvaluatePlacementTest($test))->handle();

        $test->refresh();

        expect($test->status)->toBe(PlacementTestStatus::Failed)
            ->and($test->grammar_score)->toBeNull()
            ->and($test->strengths)->toBeNull()
            ->and($test->weaknesses)->toBeNull();

        expect(PlacementAnswer::where('placement_test_id', $test->id)->count())->toBe(3);
    });
});

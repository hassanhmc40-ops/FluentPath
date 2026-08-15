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
        PlacementQuestion::factory()->reading()->create(),
        PlacementQuestion::factory()->writing()->create(),
    ];

    $test = PlacementTest::factory()->create(['user_id' => $userId]);

    $test->placementAnswers()->createMany(
        collect($questions)->map(fn (PlacementQuestion $q) => [
            'placement_question_id' => $q->id,
            'answer' => $q->correct_answer ?? 'Student writing sample.',
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
            PlacementQuestion::factory()->writing()->create(),
        ]);

        $response = $this->postJson('/api/placement-tests', [
            'answers' => $questions->map(fn ($q) => [
                'placement_question_id' => $q->id,
                'answer' => $q->correct_answer ?? 'My writing sample.',
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

        $this->assertDatabaseCount('placement_answers', 3);

        Queue::assertPushed(EvaluatePlacementTest::class, fn (EvaluatePlacementTest $job) => $job->placementTest->id === $id);
    });

    it('rejects a free-text answer for a multiple choice question with 422', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $question = PlacementQuestion::factory()->grammar()->create();

        $this->postJson('/api/placement-tests', [
            'answers' => [
                ['placement_question_id' => $question->id, 'answer' => 'She is a teacher.'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.0.answer');

        $this->assertDatabaseCount('placement_tests', 0);

        Queue::assertNotPushed(EvaluatePlacementTest::class);
    });

    it('rejects an answer letter outside a-d for a multiple choice question with 422', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $question = PlacementQuestion::factory()->vocabulary()->create();

        $this->postJson('/api/placement-tests', [
            'answers' => [
                ['placement_question_id' => $question->id, 'answer' => 'e'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.0.answer');

        Queue::assertNotPushed(EvaluatePlacementTest::class);
    });

    it('accepts a full 60-question submission (cap raised to 150)', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $questions = collect()
            ->merge(PlacementQuestion::factory()->count(19)->grammar()->multipleChoice()->create())
            ->merge(PlacementQuestion::factory()->count(19)->vocabulary()->multipleChoice()->create())
            ->merge(PlacementQuestion::factory()->count(19)->reading()->multipleChoice()->create())
            ->merge(PlacementQuestion::factory()->count(3)->writing()->create());

        $this->postJson('/api/placement-tests', [
            'answers' => $questions->map(fn ($q) => [
                'placement_question_id' => $q->id,
                'answer' => $q->correct_answer ?? 'A writing sample of sufficient length.',
            ])->all(),
        ])->assertStatus(202);

        $this->assertDatabaseCount('placement_answers', 60);

        Queue::assertPushed(EvaluatePlacementTest::class);
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

    it('accepts a partial submission and stores only the answered questions', function () {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $grammarQuestions = PlacementQuestion::factory()->count(19)->grammar()->multipleChoice()->create();
        $vocabularyQuestions = PlacementQuestion::factory()->count(19)->vocabulary()->multipleChoice()->create();
        $readingQuestions = PlacementQuestion::factory()->count(19)->reading()->multipleChoice()->create();
        $writingQuestions = PlacementQuestion::factory()->count(3)->writing()->create();

        $answers = collect()
            ->push([
                'placement_question_id' => $grammarQuestions->first()->id,
                'answer' => $grammarQuestions->first()->correct_answer,
            ])
            ->push([
                'placement_question_id' => $grammarQuestions->skip(1)->first()->id,
                'answer' => 'a',
            ])
            ->push([
                'placement_question_id' => $writingQuestions->first()->id,
                'answer' => 'My writing sample about a topic.',
            ])
            ->all();

        $response = $this->postJson('/api/placement-tests', ['answers' => $answers]);

        $response->assertStatus(202)
            ->assertJsonStructure(['id', 'status'])
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseCount('placement_answers', 3);

        Queue::assertPushed(EvaluatePlacementTest::class);
    });

    it('rejects a submission where every question was skipped with 422', function () {
        Queue::fake();

        Sanctum::actingAs(User::factory()->create());

        $q1 = PlacementQuestion::factory()->grammar()->create();
        $q2 = PlacementQuestion::factory()->vocabulary()->create();

        $response = $this->postJson('/api/placement-tests', [
            'answers' => [
                ['placement_question_id' => $q1->id, 'answer' => ''],
                ['placement_question_id' => $q2->id, 'answer' => '   '],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('answers')
            ->assertJsonPath('errors.answers.0', 'Answer at least one question to submit the placement test.');

        $this->assertDatabaseCount('placement_tests', 0);

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
            ->assertJsonCount(4, 'data.answers')
            ->assertJsonStructure([
                'data' => [
                    'id', 'status', 'submitted_at', 'cefr_level', 'grammar_score',
                    'vocabulary_score', 'reading_score', 'writing_score', 'strengths', 'weaknesses',
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
    it('marks the test as analyzed, auto-scores the MCQ skills and persists the AI writing score', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        Queue::fake();

        (new EvaluatePlacementTest($test))->handle();

        $test->refresh();

        expect($test->status)->toBe(PlacementTestStatus::Analyzed)
            ->and($test->cefr_level)->toBe(CefrLevel::B1)
            ->and($test->grammar_score)->toBe('100.00')
            ->and($test->vocabulary_score)->toBe('100.00')
            ->and($test->reading_score)->toBe('100.00')
            ->and($test->writing_score)->toBe('70.00')
            ->and($test->strengths)->toBe(['Good grasp of tenses', 'Clear writing'])
            ->and($test->weaknesses)->toBe(['Phrasal verbs', 'Idioms'])
            ->and($test->reasoning)->toBe('The learner demonstrates solid B1-level skills.');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Placement Test Analyzed',
        ]);
    });

    it('scores a mixed MCQ submission by correct answers only', function () {
        $user = User::factory()->create();

        $questions = [
            PlacementQuestion::factory()->grammar()->create(),
            PlacementQuestion::factory()->vocabulary()->create(),
            PlacementQuestion::factory()->reading()->create(),
        ];

        $test = PlacementTest::factory()->create(['user_id' => $user->id]);

        $test->placementAnswers()->createMany(
            collect($questions)->map(fn (PlacementQuestion $q, int $i) => [
                'placement_question_id' => $q->id,
                'answer' => $i === 0 ? 'z' : $q->correct_answer,
            ])->all()
        );

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        Queue::fake();

        (new EvaluatePlacementTest($test))->handle();

        $test->refresh();

        expect($test->grammar_score)->toBe('0.00')
            ->and($test->vocabulary_score)->toBe('100.00')
            ->and($test->reading_score)->toBe('100.00');
    });

    it('sends the auto-score summary and the writing answers to the agent in the prompt', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        Queue::fake();

        (new EvaluatePlacementTest($test))->handle();

        AI::assertAgentWasPrompted(
            PlacementEvaluationAgent::class,
            fn ($prompt) => $prompt->contains('Auto-scored') && $prompt->contains('Writing answers') && $prompt->contains('Grammar score') && ! $prompt->contains('Grammar answers')
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

    it('rejects an AI response that still contains the old grammar score keys only', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [[
            'cefr_level' => 'B1',
            'grammar_score' => 90,
            'vocabulary_score' => 80,
        ]]);

        (new EvaluatePlacementTest($test))->handle();

        expect($test->fresh()->status)->toBe(PlacementTestStatus::Failed);
    });

    it('rethrows AI failures for queue retry and marks the test failed via failed()', function () {
        $user = User::factory()->create();
        $test = seedPlacementTestWithAnswers($user->id);

        AI::fakeAgent(PlacementEvaluationAgent::class, [
            fn () => throw new RuntimeException('Groq is down'),
        ]);

        $job = new EvaluatePlacementTest($test);

        try {
            $job->handle();
            $this->fail('Expected the AI exception to propagate for queue retry.');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toBe('Groq is down');
        }

        expect($test->fresh()->status)->toBe(PlacementTestStatus::Processing);

        $job->failed(new RuntimeException('Groq is down'));

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

        expect(PlacementAnswer::where('placement_test_id', $test->id)->count())->toBe(4);
    });

    it('counts skipped questions as incorrect over the catalog total', function () {
        $user = User::factory()->create();

        $grammarQuestions = PlacementQuestion::factory()->count(19)->grammar()->multipleChoice()->create();
        PlacementQuestion::factory()->count(19)->vocabulary()->multipleChoice()->create();
        PlacementQuestion::factory()->count(19)->reading()->multipleChoice()->create();
        PlacementQuestion::factory()->count(3)->writing()->create();

        $test = PlacementTest::factory()->create(['user_id' => $user->id]);

        // Answer only the first grammar question correctly.
        $test->placementAnswers()->create([
            'placement_question_id' => $grammarQuestions->first()->id,
            'answer' => $grammarQuestions->first()->correct_answer,
        ]);

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        Queue::fake();

        (new EvaluatePlacementTest($test))->handle();

        $test->refresh();

        expect($test->grammar_score)->toBe('5.26')
            ->and($test->vocabulary_score)->toBe('0.00')
            ->and($test->reading_score)->toBe('0.00')
            ->and($test->writing_score)->toBe('70.00')
            ->and($test->status)->toBe(PlacementTestStatus::Analyzed);
    });

    it('reports answered/skipped counts and whole-part skips in the prompt', function () {
        $user = User::factory()->create();

        $grammarQuestions = PlacementQuestion::factory()->count(19)->grammar()->multipleChoice()->create();
        PlacementQuestion::factory()->count(19)->vocabulary()->multipleChoice()->create();
        PlacementQuestion::factory()->count(19)->reading()->multipleChoice()->create();
        $writingQuestions = PlacementQuestion::factory()->count(3)->writing()->create();

        $test = PlacementTest::factory()->create(['user_id' => $user->id]);

        // Answer the first two grammar questions: one correct, one wrong.
        $test->placementAnswers()->create([
            'placement_question_id' => $grammarQuestions->first()->id,
            'answer' => $grammarQuestions->first()->correct_answer,
        ]);

        $wrongAnswer = $grammarQuestions->first()->correct_answer === 'a' ? 'b' : 'a';
        $test->placementAnswers()->create([
            'placement_question_id' => $grammarQuestions->skip(1)->first()->id,
            'answer' => $wrongAnswer,
        ]);

        // Answer one writing question.
        $test->placementAnswers()->create([
            'placement_question_id' => $writingQuestions->first()->id,
            'answer' => 'A writing sample about daily life.',
        ]);

        AI::fakeAgent(PlacementEvaluationAgent::class, [validPlacementAnalysis()]);

        Queue::fake();

        (new EvaluatePlacementTest($test))->handle();

        AI::assertAgentWasPrompted(
            PlacementEvaluationAgent::class,
            fn ($prompt) => $prompt->contains('Grammar score: 5.26 (2/19 answered, 17 skipped)')
                && $prompt->contains('the learner skipped the whole part')
                && $prompt->contains('Writing answers (1 of 3 prompts answered')
                && $prompt->contains('Auto-scored')
        );
    });
});

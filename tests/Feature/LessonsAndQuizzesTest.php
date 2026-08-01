<?php

use App\Enums\LessonProgressStatus;
use App\Events\LessonCompleted;
use App\Events\QuizAttempted;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('lessons index', function () {
    it('lists lessons with the student-facing resource shape', function () {
        Sanctum::actingAs(User::factory()->create());

        Lesson::factory()->count(3)->create();

        $this->getJson('/api/lessons')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'skill', 'level', 'created_at'],
                ],
            ]);
    });

    it('filters lessons by skill and level', function () {
        Sanctum::actingAs(User::factory()->create());

        $grammar = Lesson::factory()->grammar()->create(['level' => 'A1']);
        $vocabulary = Lesson::factory()->vocabulary()->create(['level' => 'A1']);

        $this->getJson('/api/lessons?skill=grammar&level=A1')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $grammar->id);

        $this->getJson('/api/lessons?skill=vocabulary')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $vocabulary->id);
    });

    it('requires authentication', function () {
        $this->getJson('/api/lessons')->assertStatus(401);
    });
});

describe('lesson completion', function () {
    it('marks a lesson as completed', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lesson = Lesson::factory()->create();

        $this->postJson("/api/lessons/{$lesson->id}/complete")
            ->assertStatus(201)
            ->assertJsonPath('data.lesson_id', $lesson->id)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.completed_at', fn ($value) => $value !== null);

        $this->assertDatabaseCount('lesson_progress', 1);
    });

    it('does not create duplicate progress rows when completing twice', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lesson = Lesson::factory()->create();

        // First completion creates the progress row (201); repeating it updates the existing row (200).
        $this->postJson("/api/lessons/{$lesson->id}/complete")->assertStatus(201);
        $this->postJson("/api/lessons/{$lesson->id}/complete")->assertStatus(200);

        $this->assertDatabaseCount('lesson_progress', 1);

        expect(LessonProgress::where('user_id', $user->id)->where('lesson_id', $lesson->id)->count())->toBe(1)
            ->and(LessonProgress::first()->status)->toBe(LessonProgressStatus::Completed);
    });

    it('dispatches LessonCompleted on every completion request', function () {
        Event::fake([LessonCompleted::class]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lesson = Lesson::factory()->create();

        // First completion creates the progress row (201); repeating it updates the existing row (200).
        $this->postJson("/api/lessons/{$lesson->id}/complete")->assertStatus(201);
        $this->postJson("/api/lessons/{$lesson->id}/complete")->assertStatus(200);

        Event::assertDispatched(LessonCompleted::class, 2);
        Event::assertDispatched(
            LessonCompleted::class,
            fn (LessonCompleted $event) => $event->userId === $user->id && $event->lessonId === $lesson->id
        );
    });

    it('returns 404 for a non-existent lesson', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/lessons/999999/complete')->assertStatus(404);
    });
});

describe('quiz show', function () {
    it('never exposes the correct answer to students', function () {
        Sanctum::actingAs(User::factory()->create());

        $quiz = Quiz::factory()->create();
        QuizQuestion::factory()->count(3)->create(['quiz_id' => $quiz->id]);

        $response = $this->getJson("/api/quizzes/{$quiz->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $quiz->id)
            ->assertJsonCount(3, 'data.questions')
            ->assertJsonStructure([
                'data' => [
                    'id', 'lesson_id', 'title', 'description',
                    'questions' => [
                        '*' => ['id', 'quiz_id', 'question', 'option_a', 'option_b', 'option_c', 'option_d', 'created_at'],
                    ],
                ],
            ]);

        collect($response->json('data.questions'))->each(function ($question) {
            expect($question)->not->toHaveKey('correct_answer');
        });
    });

    it('returns 404 for a non-existent quiz', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/quizzes/999999')->assertStatus(404);
    });
});

function seedQuizWithQuestions(int $count = 4): Quiz
{
    $quiz = Quiz::factory()->create();

    for ($i = 0; $i < $count; $i++) {
        QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'option_a' => 'wrong-a',
            'option_b' => 'correct-b',
            'option_c' => 'wrong-c',
            'option_d' => 'wrong-d',
            'correct_answer' => 'b',
        ]);
    }

    return $quiz->fresh()->load('quizQuestions');
}

describe('quiz attempts', function () {
    it('scores a mix of correct and incorrect answers exactly', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quiz = seedQuizWithQuestions(4);

        $answers = $quiz->quizQuestions
            ->values()
            ->map(fn ($question, $index) => [
                'quiz_question_id' => $question->id,
                'selected_option' => in_array($index, [0, 1], true) ? 'b' : 'a',
            ])
            ->all();

        $response = $this->postJson("/api/quizzes/{$quiz->id}/attempts", ['answers' => $answers]);

        $response->assertStatus(201)
            ->assertJsonPath('quiz_id', $quiz->id)
            ->assertJsonPath('score', '50.00')
            ->assertJsonCount(4, 'answers')
            ->assertJsonPath('answers.0.is_correct', true)
            ->assertJsonPath('answers.1.is_correct', true)
            ->assertJsonPath('answers.2.is_correct', false)
            ->assertJsonPath('answers.3.is_correct', false);

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 50.0,
        ]);
    });

    it('scores a perfect attempt as 100', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quiz = seedQuizWithQuestions(3);

        $answers = $quiz->quizQuestions->map(fn ($question) => [
            'quiz_question_id' => $question->id,
            'selected_option' => 'b',
        ])->all();

        $this->postJson("/api/quizzes/{$quiz->id}/attempts", ['answers' => $answers])
            ->assertStatus(201)
            ->assertJsonPath('score', '100.00');
    });

    it('records multiple attempts independently', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quiz = seedQuizWithQuestions(2);

        $allWrong = $quiz->quizQuestions->map(fn ($q) => ['quiz_question_id' => $q->id, 'selected_option' => 'a'])->all();
        $allRight = $quiz->quizQuestions->map(fn ($q) => ['quiz_question_id' => $q->id, 'selected_option' => 'b'])->all();

        $this->postJson("/api/quizzes/{$quiz->id}/attempts", ['answers' => $allWrong])->assertJsonPath('score', '0.00');
        $this->postJson("/api/quizzes/{$quiz->id}/attempts", ['answers' => $allRight])->assertJsonPath('score', '100.00');

        $this->assertDatabaseCount('quiz_attempts', 2);
    });

    it('dispatches QuizAttempted with the computed score', function () {
        Event::fake([QuizAttempted::class]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quiz = seedQuizWithQuestions(2);
        $answers = $quiz->quizQuestions->map(fn ($q) => ['quiz_question_id' => $q->id, 'selected_option' => 'b'])->all();

        $this->postJson("/api/quizzes/{$quiz->id}/attempts", ['answers' => $answers])->assertStatus(201);

        Event::assertDispatched(
            QuizAttempted::class,
            fn (QuizAttempted $event) => $event->userId === $user->id && $event->quizId === $quiz->id && $event->score === 100.0
        );
    });

    it('rejects answers referencing questions from another quiz with 422', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quizA = seedQuizWithQuestions(1);
        $quizB = seedQuizWithQuestions(1);

        $foreignQuestion = $quizB->quizQuestions->first();

        $this->postJson("/api/quizzes/{$quizA->id}/attempts", [
            'answers' => [
                ['quiz_question_id' => $foreignQuestion->id, 'selected_option' => 'a'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.0.quiz_question_id');

        $this->assertDatabaseCount('quiz_attempts', 0);
    });

    it('rejects an invalid selected option with 422', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quiz = seedQuizWithQuestions(1);
        $question = $quiz->quizQuestions->first();

        $this->postJson("/api/quizzes/{$quiz->id}/attempts", [
            'answers' => [
                ['quiz_question_id' => $question->id, 'selected_option' => 'e'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.0.selected_option');

        $this->assertDatabaseCount('quiz_attempts', 0);
    });

    it('rejects duplicate question answers with 422', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $quiz = seedQuizWithQuestions(2);
        $questions = $quiz->quizQuestions;

        $this->postJson("/api/quizzes/{$quiz->id}/attempts", [
            'answers' => [
                ['quiz_question_id' => $questions[0]->id, 'selected_option' => 'a'],
                ['quiz_question_id' => $questions[0]->id, 'selected_option' => 'a'],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('answers.1.quiz_question_id');
    });
});

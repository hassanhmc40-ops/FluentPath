<?php

use App\Models\Lesson;
use App\Models\PlacementQuestion;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('admin middleware', function () {
    it('blocks students from every admin endpoint with 403', function () {
        Sanctum::actingAs(User::factory()->create());

        $lesson = Lesson::factory()->create();
        $quiz = Quiz::factory()->create();
        $quizQuestion = QuizQuestion::factory()->create();
        $placementQuestion = PlacementQuestion::factory()->create();

        $this->getJson('/api/admin/lessons')->assertStatus(403);
        $this->postJson('/api/admin/lessons', [
            'title' => 'New Lesson',
            'skill' => 'grammar',
            'level' => 'A1',
        ])->assertStatus(403);
        $this->putJson("/api/admin/lessons/{$lesson->id}", ['title' => 'Renamed'])->assertStatus(403);
        $this->deleteJson("/api/admin/lessons/{$lesson->id}")->assertStatus(403);

        $this->getJson('/api/admin/quizzes')->assertStatus(403);
        $this->postJson('/api/admin/quizzes', ['lesson_id' => $lesson->id, 'title' => 'Quiz'])->assertStatus(403);
        $this->deleteJson("/api/admin/quizzes/{$quiz->id}")->assertStatus(403);

        $this->getJson('/api/admin/quiz-questions')->assertStatus(403);
        $this->postJson('/api/admin/quiz-questions', [
            'quiz_id' => $quiz->id,
            'question' => 'Q?',
            'option_a' => 'a',
            'option_b' => 'b',
            'option_c' => 'c',
            'option_d' => 'd',
            'correct_answer' => 'b',
        ])->assertStatus(403);
        $this->deleteJson("/api/admin/quiz-questions/{$quizQuestion->id}")->assertStatus(403);

        $this->getJson('/api/admin/placement-questions')->assertStatus(403);
        $this->postJson('/api/admin/placement-questions', [
            'question' => 'PQ?',
            'skill' => 'grammar',
            'level' => 'A1',
        ])->assertStatus(403);
        $this->deleteJson("/api/admin/placement-questions/{$placementQuestion->id}")->assertStatus(403);
    });

    it('returns 401 for unauthenticated admin requests', function () {
        $this->getJson('/api/admin/lessons')->assertStatus(401);
    });
});

describe('admin lessons', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->admin()->create());
    });

    it('creates a lesson', function () {
        $this->postJson('/api/admin/lessons', [
            'title' => 'Present Perfect Fundamentals',
            'skill' => 'grammar',
            'level' => 'B1',
            'content' => "## What you'll learn\nPresent perfect connects the past to the present.",
        ])->assertStatus(201)
            ->assertJsonPath('data.title', 'Present Perfect Fundamentals')
            ->assertJsonPath('data.skill', 'grammar')
            ->assertJsonPath('data.level', 'B1');

        $this->assertDatabaseHas('lessons', ['title' => 'Present Perfect Fundamentals']);
    });

    it('rejects an invalid lesson payload with 422', function () {
        $this->postJson('/api/admin/lessons', [
            'title' => 'Bad Lesson',
            'skill' => 'history',
            'level' => 'Z9',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['skill', 'level']);

        $this->assertDatabaseCount('lessons', 0);
    });

    it('lists lessons', function () {
        Lesson::factory()->count(3)->create();

        $this->getJson('/api/admin/lessons')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    it('updates a lesson', function () {
        $lesson = Lesson::factory()->create();

        $this->putJson("/api/admin/lessons/{$lesson->id}", [
            'title' => 'Renamed Lesson',
            'skill' => 'vocabulary',
        ])->assertStatus(200)
            ->assertJsonPath('data.title', 'Renamed Lesson')
            ->assertJsonPath('data.skill', 'vocabulary');

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'title' => 'Renamed Lesson']);
    });

    it('deletes a lesson', function () {
        $lesson = Lesson::factory()->create();

        $this->deleteJson("/api/admin/lessons/{$lesson->id}")->assertStatus(204);

        $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);

        $this->getJson('/api/admin/lessons')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    });
});

describe('admin quizzes', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->admin()->create());
    });

    it('creates a quiz for an existing lesson', function () {
        $lesson = Lesson::factory()->create();

        $this->postJson('/api/admin/quizzes', [
            'lesson_id' => $lesson->id,
            'title' => 'Grammar Review',
            'description' => 'Quick check',
        ])->assertStatus(201)
            ->assertJsonPath('data.title', 'Grammar Review');

        $this->assertDatabaseHas('quizzes', ['lesson_id' => $lesson->id, 'title' => 'Grammar Review']);
    });

    it('rejects a quiz with a non-existent lesson with 422', function () {
        $this->postJson('/api/admin/quizzes', [
            'lesson_id' => 999999,
            'title' => 'Orphan Quiz',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('lesson_id');
    });

    it('updates and deletes a quiz', function () {
        $quiz = Quiz::factory()->create();

        $this->putJson("/api/admin/quizzes/{$quiz->id}", ['title' => 'Renamed Quiz'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Renamed Quiz');

        $this->deleteJson("/api/admin/quizzes/{$quiz->id}")->assertStatus(204);

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    });
});

describe('admin quiz questions', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->admin()->create());
    });

    it('creates a quiz question with the correct answer', function () {
        $quiz = Quiz::factory()->create();

        $this->postJson('/api/admin/quiz-questions', [
            'quiz_id' => $quiz->id,
            'question' => 'She ___ to school every day.',
            'option_a' => 'go',
            'option_b' => 'goes',
            'option_c' => 'going',
            'option_d' => 'gone',
            'correct_answer' => 'b',
        ])->assertStatus(201)
            ->assertJsonPath('data.correct_answer', 'b');

        $this->assertDatabaseHas('quiz_questions', ['quiz_id' => $quiz->id, 'correct_answer' => 'b']);
    });

    it('rejects an incomplete quiz question payload with 422', function () {
        $quiz = Quiz::factory()->create();

        $this->postJson('/api/admin/quiz-questions', [
            'quiz_id' => $quiz->id,
            'question' => 'Q?',
            'option_a' => 'a',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['option_b', 'option_c', 'option_d', 'correct_answer']);
    });

    it('rejects a quiz question with a non-existent quiz with 422', function () {
        $this->postJson('/api/admin/quiz-questions', [
            'quiz_id' => 999999,
            'question' => 'Q?',
            'option_a' => 'a',
            'option_b' => 'b',
            'option_c' => 'c',
            'option_d' => 'd',
            'correct_answer' => 'b',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('quiz_id');
    });

    it('filters quiz questions by quiz', function () {
        $quizA = Quiz::factory()->create();
        $quizB = Quiz::factory()->create();

        QuizQuestion::factory()->count(2)->create(['quiz_id' => $quizA->id]);
        QuizQuestion::factory()->count(3)->create(['quiz_id' => $quizB->id]);

        $this->getJson("/api/admin/quiz-questions?quiz_id={$quizA->id}")
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    });
});

describe('admin placement questions', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->admin()->create());
    });

    it('creates, updates and deletes a placement question', function () {
        $this->postJson('/api/admin/placement-questions', [
            'question' => 'What does "benevolent" mean?',
            'skill' => 'vocabulary',
            'level' => 'B2',
            'option_a' => 'kind and generous',
            'option_b' => 'quick and energetic',
            'option_c' => 'strict and formal',
            'option_d' => 'silent and reserved',
            'correct_answer' => 'a',
        ])->assertStatus(201)
            ->assertJsonPath('data.skill', 'vocabulary');

        $this->assertDatabaseHas('placement_questions', [
            'question' => 'What does "benevolent" mean?',
            'correct_answer' => 'a',
        ]);

        $question = PlacementQuestion::first();

        $this->putJson("/api/admin/placement-questions/{$question->id}", [
            'level' => 'C1',
        ])->assertStatus(200)
            ->assertJsonPath('data.level', 'C1');

        $this->deleteJson("/api/admin/placement-questions/{$question->id}")->assertStatus(204);

        $this->assertDatabaseMissing('placement_questions', ['id' => $question->id]);
    });

    it('rejects an invalid placement question payload with 422', function () {
        $this->postJson('/api/admin/placement-questions', [
            'question' => '',
            'skill' => 'math',
            'level' => 'Z9',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['question', 'skill', 'level']);
    });

    it('requires answer options and a correct answer for multiple choice skills', function () {
        $this->postJson('/api/admin/placement-questions', [
            'question' => 'Choose the correct form.',
            'skill' => 'grammar',
            'level' => 'A1',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['option_a', 'option_b', 'option_c', 'option_d', 'correct_answer']);
    });

    it('accepts a writing question without options and stores it option-less', function () {
        $this->postJson('/api/admin/placement-questions', [
            'question' => 'Write about your daily routine in 3-4 sentences.',
            'skill' => 'writing',
            'level' => 'B1',
        ])->assertStatus(201);

        $question = PlacementQuestion::first();

        expect($question->correct_answer)->toBeNull()
            ->and($question->option_a)->toBeNull();
    });
});

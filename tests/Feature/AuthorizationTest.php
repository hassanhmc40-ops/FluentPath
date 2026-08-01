<?php

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('placement tests', function () {
    it('forbids a student from viewing another student\'s test', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $test = PlacementTest::factory()->create(['user_id' => $owner->id]);
        $question = PlacementQuestion::factory()->create();
        $test->placementAnswers()->create(['placement_question_id' => $question->id, 'answer' => 'Answer.']);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/placement-tests/{$test->id}")->assertStatus(403);
    });
});

describe('roadmaps', function () {
    it('never exposes another student\'s roadmap', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $test = PlacementTest::factory()->analyzed()->create(['user_id' => $owner->id]);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $owner->id,
            'placement_test_id' => $test->id,
            'status' => 'generated',
        ]);

        $week = RoadmapWeek::factory()->create(['roadmap_id' => $roadmap->id, 'week_number' => 1]);
        RoadmapWeekLesson::factory()->create(['roadmap_week_id' => $week->id, 'lesson_id' => Lesson::factory()->create()->id, 'display_order' => 1]);

        Sanctum::actingAs($intruder);

        $this->getJson('/api/roadmaps')->assertStatus(404);
    });
});

describe('writing submissions', function () {
    it('forbids a student from viewing another student\'s submission', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $submission = WritingSubmission::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/writing-submissions/{$submission->id}")->assertStatus(403);
    });
});

describe('notifications', function () {
    it('index returns only the student\'s own notifications', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        Notification::factory()->count(2)->create(['user_id' => $owner->id]);
        Notification::factory()->count(1)->create(['user_id' => $intruder->id]);

        Sanctum::actingAs($intruder);

        $notification = $intruder->notifications()->first();

        $this->getJson('/api/notifications')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $notification->id);
    });

    it('forbids a student from marking another student\'s notification as read', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $notification = Notification::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $this->postJson("/api/notifications/{$notification->id}/read")->assertStatus(403);
    });
});

describe('lesson progress and quiz attempts', function () {
    it('writes progress rows only for the authenticated student', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $lesson = Lesson::factory()->create();

        LessonProgress::factory()->create([
            'user_id' => $owner->id,
            'lesson_id' => $lesson->id,
            'status' => 'completed',
        ]);

        Sanctum::actingAs($intruder);

        $this->postJson("/api/lessons/{$lesson->id}/complete")->assertStatus(201);

        $this->assertDatabaseCount('lesson_progress', 2);
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $intruder->id, 'lesson_id' => $lesson->id, 'status' => 'completed']);

        expect(LessonProgress::where('user_id', $owner->id)->count())->toBe(1);
    });

    it('writes quiz attempts only for the authenticated student', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $quiz = Quiz::factory()->create();
        $questions = QuizQuestion::factory()->count(2)->create([
            'quiz_id' => $quiz->id,
            'option_a' => 'wrong',
            'option_b' => 'correct',
            'option_c' => 'wrong',
            'option_d' => 'wrong',
            'correct_answer' => 'b',
        ]);

        $owner->quizAttempts()->create(['quiz_id' => $quiz->id, 'score' => 100, 'completed_at' => now()]);

        Sanctum::actingAs($intruder);

        $answers = $questions->map(fn ($q) => ['quiz_question_id' => $q->id, 'selected_option' => 'b'])->all();

        $this->postJson("/api/quizzes/{$quiz->id}/attempts", ['answers' => $answers])->assertStatus(201);

        $this->assertDatabaseCount('quiz_attempts', 2);
        $this->assertDatabaseHas('quiz_attempts', ['user_id' => $intruder->id, 'quiz_id' => $quiz->id, 'score' => 100.0]);

        expect($owner->quizAttempts()->count())->toBe(1);
    });
});

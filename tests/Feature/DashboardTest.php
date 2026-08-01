<?php

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('dashboard for a brand new student', function () {
    it('returns a graceful empty state', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.cefr_level', null)
            ->assertJsonPath('data.current_week', null)
            ->assertJsonPath('data.lessons.completed', 0)
            ->assertJsonPath('data.lessons.total', 0)
            ->assertJsonPath('data.writing_score_history', [])
            ->assertJsonPath('data.grammar_improvement.trend', 'insufficient_data')
            ->assertJsonPath('data.grammar_improvement.start_score', null)
            ->assertJsonPath('data.grammar_improvement.current_score', null)
            ->assertJsonPath('data.vocabulary_improvement.trend', 'insufficient_data')
            ->assertJsonPath('data.learning_streak', 0)
            ->assertJsonPath('data.overall_progress_percentage', 0)
            ->assertJsonPath('data.next_recommended_action', null);
    });

    it('requires authentication', function () {
        $this->getJson('/api/dashboard')->assertStatus(401);
    });
});

describe('dashboard with seeded data', function () {
    it('computes exact numbers from the student\'s data', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lessons = Lesson::factory()->count(5)->create();

        $firstTest = PlacementTest::factory()->analyzed()->create([
            'user_id' => $user->id,
            'cefr_level' => 'A2',
            'grammar_score' => 60,
            'vocabulary_score' => 70,
        ]);

        $secondTest = PlacementTest::factory()->analyzed()->create([
            'user_id' => $user->id,
            'cefr_level' => 'B1',
            'grammar_score' => 80,
            'vocabulary_score' => 65,
        ]);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $secondTest->id,
            'status' => 'generated',
            'next_lesson_id' => $lessons[1]->id,
            'next_topic' => 'grammar',
            'next_writing_prompt' => 'Write a short paragraph using what you learned.',
        ]);

        $week1 = RoadmapWeek::factory()->create(['roadmap_id' => $roadmap->id, 'week_number' => 1, 'objective' => 'Week 1']);
        $week2 = RoadmapWeek::factory()->create(['roadmap_id' => $roadmap->id, 'week_number' => 2, 'objective' => 'Week 2']);

        RoadmapWeekLesson::factory()->create(['roadmap_week_id' => $week1->id, 'lesson_id' => $lessons[0]->id, 'display_order' => 1]);
        RoadmapWeekLesson::factory()->create(['roadmap_week_id' => $week1->id, 'lesson_id' => $lessons[1]->id, 'display_order' => 2]);
        RoadmapWeekLesson::factory()->create(['roadmap_week_id' => $week2->id, 'lesson_id' => $lessons[2]->id, 'display_order' => 1]);
        RoadmapWeekLesson::factory()->create(['roadmap_week_id' => $week2->id, 'lesson_id' => $lessons[3]->id, 'display_order' => 2]);

        LessonProgress::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessons[0]->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        WritingSubmission::factory()->corrected()->create([
            'user_id' => $user->id,
            'submitted_at' => now()->subDays(2),
            'score' => 70,
        ]);

        WritingSubmission::factory()->corrected()->create([
            'user_id' => $user->id,
            'submitted_at' => now()->subDay(),
            'score' => 85,
        ]);

        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.cefr_level', 'B1')
            ->assertJsonPath('data.current_week', 1)
            ->assertJsonPath('data.lessons.completed', 1)
            ->assertJsonPath('data.lessons.total', 5)
            ->assertJsonPath('data.writing_score_history.0.submitted_at', fn ($value) => is_string($value) && str_starts_with($value, now()->subDays(2)->toDateString()))
            ->assertJsonPath('data.writing_score_history.1.submitted_at', fn ($value) => is_string($value) && str_starts_with($value, now()->subDay()->toDateString()))
            ->assertJsonCount(2, 'data.writing_score_history')
            ->assertJsonPath('data.grammar_improvement.trend', 'improving')
            ->assertJsonPath('data.grammar_improvement.start_score', 60)
            ->assertJsonPath('data.grammar_improvement.current_score', 80)
            ->assertJsonPath('data.vocabulary_improvement.trend', 'declining')
            ->assertJsonPath('data.vocabulary_improvement.start_score', 70)
            ->assertJsonPath('data.vocabulary_improvement.current_score', 65)
            ->assertJsonPath('data.learning_streak', 3)
            ->assertJsonPath('data.overall_progress_percentage', 25)
            ->assertJsonPath('data.next_recommended_action.lesson_id', $lessons[1]->id)
            ->assertJsonPath('data.next_recommended_action.topic', 'grammar')
            ->assertJsonPath('data.next_recommended_action.lesson_title', $lessons[1]->title)
            ->assertJsonPath('data.next_recommended_action.writing_prompt', 'Write a short paragraph using what you learned.');
    });

    it('shows the latest analyzed placement test level, not an older one', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => 'A1']);
        PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => 'C1']);

        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.cefr_level', 'C1');
    });
});

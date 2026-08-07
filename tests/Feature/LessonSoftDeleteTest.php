<?php

use App\Enums\CefrLevel;
use App\Models\Lesson;
use App\Models\PlacementTest;
use App\Models\Quiz;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('admin trashes and restores lessons', function () {
    it('moves a lesson to the trash from the admin catalog', function () {
        $admin = User::factory()->admin()->create();
        $lesson = Lesson::factory()->create(['title' => 'Noun Clauses Mastery']);

        $this->actingAs($admin)
            ->from(route('admin.lessons.index'))
            ->delete(route('admin.lessons.destroy', $lesson))
            ->assertRedirect(route('admin.lessons.index'))
            ->assertSessionHas('success', 'Lesson moved to trash.');

        $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);

        $this->get(route('admin.lessons.index'))
            ->assertStatus(200)
            ->assertSee('Trash (1)')
            ->assertDontSee('Noun Clauses Mastery');
    });

    it('lists trashed lessons in the trash view and restores them', function () {
        $admin = User::factory()->admin()->create();
        $lesson = Lesson::factory()->create(['title' => 'Trashed Title']);
        $lesson->delete();

        $this->actingAs($admin);

        $this->get(route('admin.lessons.index', ['trashed' => 1]))
            ->assertStatus(200)
            ->assertSee('Trash · 1')
            ->assertSee('Trashed Title')
            ->assertSee('Restore');

        $this->patch(route('admin.lessons.restore', $lesson))
            ->assertRedirect(route('admin.lessons.index'))
            ->assertSessionHas('success', 'Lesson restored.');

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'deleted_at' => null]);

        $this->get(route('admin.lessons.index'))
            ->assertStatus(200)
            ->assertSee('Trashed Title');
    });

    it('forbids a non-admin from deleting or restoring lessons', function () {
        $student = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $this->actingAs($student);

        $this->delete(route('admin.lessons.destroy', $lesson))->assertStatus(403);

        $lesson->delete();
        $this->patch(route('admin.lessons.restore', $lesson))->assertStatus(403);
    });
});

describe('soft-deleted lessons are hidden from students', function () {
    it('hides a trashed lesson from the student catalog and 404s its page', function () {
        $lesson = Lesson::factory()->create(['level' => CefrLevel::B1, 'title' => 'Noun Clauses Mastery']);
        $lesson->delete();

        $user = User::factory()->create();
        PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => CefrLevel::B1]);
        $this->actingAs($user);

        $this->get('/lessons')
            ->assertStatus(200)
            ->assertDontSee('Noun Clauses Mastery');

        $this->get("/lessons/{$lesson->id}")->assertStatus(404);
    });

    it('hides quizzes whose lesson is trashed from the student exercises page', function () {
        $doomed = Lesson::factory()->create(['level' => CefrLevel::B1]);
        $living = Lesson::factory()->create(['level' => CefrLevel::B1]);
        Quiz::factory()->create(['lesson_id' => $doomed->id, 'title' => 'Doomed Module']);
        Quiz::factory()->create(['lesson_id' => $living->id, 'title' => 'Living Exercise']);
        $doomed->delete();

        $user = User::factory()->create();
        PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => CefrLevel::B1]);
        $this->actingAs($user);

        $this->get('/quizzes')
            ->assertStatus(200)
            ->assertSee('Living Exercise')
            ->assertDontSee('Doomed Module');
    });
});

describe('recommendations skip trashed lessons', function () {
    it('picks the next available lesson when a roadmap lesson is trashed', function () {
        $user = User::factory()->create();
        $placementTest = PlacementTest::factory()->analyzed()->create(['user_id' => $user->id]);

        $trashed = Lesson::factory()->create(['title' => 'Trashed Roadmap Lesson']);
        $next = Lesson::factory()->create(['title' => 'Live Roadmap Lesson']);
        $trashed->delete();

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
            'status' => 'generated',
            'next_lesson_id' => $trashed->id,
            'next_topic' => $trashed->skill->value,
            'next_writing_prompt' => 'Write a short paragraph introducing yourself and your English learning goals.',
        ]);

        $week = RoadmapWeek::factory()->create([
            'roadmap_id' => $roadmap->id,
            'week_number' => 1,
            'objective' => 'Foundations',
        ]);

        RoadmapWeekLesson::factory()->create([
            'roadmap_week_id' => $week->id,
            'lesson_id' => $trashed->id,
            'display_order' => 1,
        ]);

        RoadmapWeekLesson::factory()->create([
            'roadmap_week_id' => $week->id,
            'lesson_id' => $next->id,
            'display_order' => 2,
        ]);

        app(RecommendationService::class)->refreshForUser($user->id);

        expect($roadmap->fresh()->next_lesson_id)->toBe($next->id)
            ->and($roadmap->fresh()->next_topic)->toBe($next->skill->value);
    });
});

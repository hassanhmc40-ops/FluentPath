<?php

use App\Enums\LessonProgressStatus;
use App\Events\LessonCompleted;
use App\Models\Lesson;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function seedRoadmapWithLessons(User $user): array
{
    $lessons = Lesson::factory()->count(3)->create();

    $placementTest = PlacementTest::factory()->analyzed()->create(['user_id' => $user->id]);

    $roadmap = Roadmap::factory()->create([
        'user_id' => $user->id,
        'placement_test_id' => $placementTest->id,
        'status' => 'generated',
        'next_lesson_id' => $lessons[0]->id,
        'next_topic' => $lessons[0]->skill->value,
        'next_writing_prompt' => 'Write a short paragraph introducing yourself and your English learning goals.',
    ]);

    $week = RoadmapWeek::factory()->create([
        'roadmap_id' => $roadmap->id,
        'week_number' => 1,
        'objective' => 'Foundations',
    ]);

    RoadmapWeekLesson::factory()->create([
        'roadmap_week_id' => $week->id,
        'lesson_id' => $lessons[0]->id,
        'display_order' => 1,
    ]);

    RoadmapWeekLesson::factory()->create([
        'roadmap_week_id' => $week->id,
        'lesson_id' => $lessons[1]->id,
        'display_order' => 2,
    ]);

    return [$roadmap, $lessons];
}

describe('lesson completion event', function () {
    it('dispatches LessonCompleted with the user and lesson ids', function () {
        Event::fake([LessonCompleted::class]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lesson = Lesson::factory()->create();

        $this->postJson("/api/lessons/{$lesson->id}/complete")->assertStatus(201);

        Event::assertDispatched(
            LessonCompleted::class,
            fn (LessonCompleted $event) => $event->userId === $user->id && $event->lessonId === $lesson->id
        );
    });
});

describe('recommendation refresh side effects', function () {
    it('updates the next recommended action and notifies once when the next lesson is completed', function () {
        $user = User::factory()->create();
        [$roadmap, $lessons] = seedRoadmapWithLessons($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/lessons/{$lessons[0]->id}/complete")->assertStatus(201);

        expect($roadmap->fresh()->next_lesson_id)->toBe($lessons[1]->id)
            ->and($roadmap->fresh()->next_topic)->toBe($lessons[1]->skill->value);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'New recommendations available',
            'is_read' => false,
        ]);
    });

    it('does not notify again when the same recommendation is recomputed', function () {
        $user = User::factory()->create();
        [, $lessons] = seedRoadmapWithLessons($user);

        Sanctum::actingAs($user);

        // First completion creates the progress row (201); repeating it updates the existing row (200).
        $this->postJson("/api/lessons/{$lessons[0]->id}/complete")->assertStatus(201);
        $this->postJson("/api/lessons/{$lessons[0]->id}/complete")->assertStatus(200);

        $this->assertDatabaseCount('notifications', 1);
    });

    it('does not notify when completing a lesson outside the roadmap (no change)', function () {
        $user = User::factory()->create();
        [, $lessons] = seedRoadmapWithLessons($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/lessons/{$lessons[2]->id}/complete")->assertStatus(201);

        $this->assertDatabaseCount('notifications', 0);
    });

    it('still records the progress for lessons outside the roadmap', function () {
        $user = User::factory()->create();
        [, $lessons] = seedRoadmapWithLessons($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/lessons/{$lessons[2]->id}/complete")->assertStatus(201);

        expect($user->lessonProgress()->where('lesson_id', $lessons[2]->id)->first()->status)
            ->toBe(LessonProgressStatus::Completed);
    });
});

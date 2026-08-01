<?php

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function runDailyMaintenance(): void
{
    test()->artisan('schedule:run');

    $schedule = app(Schedule::class);

    $event = collect($schedule->events())
        ->first(fn ($event) => $event->description === 'app:daily-maintenance');

    expect($event)->not->toBeNull('The app:daily-maintenance schedule event was not registered.');

    $event->run(app());
}

describe('notifications index', function () {
    it('returns only the authenticated user\'s notifications, newest first', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $old = Notification::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(2)]);
        $new = Notification::factory()->create(['user_id' => $user->id, 'created_at' => now()]);
        Notification::factory()->create(['user_id' => $other->id, 'created_at' => now()]);

        Sanctum::actingAs($user);

        $this->getJson('/api/notifications')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $new->id)
            ->assertJsonPath('data.1.id', $old->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'message', 'is_read', 'created_at'],
                ],
                'links',
                'meta',
            ]);
    });

    it('requires authentication', function () {
        $this->getJson('/api/notifications')->assertStatus(401);
    });
});

describe('mark as read', function () {
    it('marks an owned notification as read', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->postJson("/api/notifications/{$notification->id}/read")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.is_read', true);

        expect($notification->fresh()->is_read)->toBeTrue();
    });

    it('forbids marking another user\'s notification as read with 403', function () {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $notification = Notification::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $this->postJson("/api/notifications/{$notification->id}/read")->assertStatus(403);

        expect($notification->fresh()->is_read)->toBeFalse();
    });

    it('returns 404 for a non-existent notification', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/notifications/999999/read')->assertStatus(404);
    });
});

describe('daily maintenance scheduler', function () {
    it('creates a "We miss you!" reminder for inactive students only', function () {
        config(['fluentpath.inactivity_reminder_days' => 3]);

        $inactive = User::factory()->create();
        LessonProgress::factory()->create([
            'user_id' => $inactive->id,
            'status' => 'completed',
            'completed_at' => now()->subDays(10),
        ]);

        $active = User::factory()->create();
        LessonProgress::factory()->create([
            'user_id' => $active->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        runDailyMaintenance();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $inactive->id,
            'title' => 'We miss you!',
            'is_read' => false,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $active->id,
            'title' => 'We miss you!',
        ]);
    });

    it('does not create a duplicate reminder when run again within the window', function () {
        config(['fluentpath.inactivity_reminder_days' => 3]);

        $inactive = User::factory()->create();
        LessonProgress::factory()->create([
            'user_id' => $inactive->id,
            'status' => 'completed',
            'completed_at' => now()->subDays(10),
        ]);

        runDailyMaintenance();
        runDailyMaintenance();

        expect(Notification::where('user_id', $inactive->id)->where('title', 'We miss you!')->count())->toBe(1);
    });

    it('does not duplicate the reminder when an unread one already exists', function () {
        config(['fluentpath.inactivity_reminder_days' => 3]);

        $inactive = User::factory()->create();
        LessonProgress::factory()->create([
            'user_id' => $inactive->id,
            'status' => 'completed',
            'completed_at' => now()->subDays(10),
        ]);

        Notification::factory()->create([
            'user_id' => $inactive->id,
            'title' => 'We miss you!',
            'is_read' => false,
            'created_at' => now()->subDay(),
        ]);

        runDailyMaintenance();

        expect(Notification::where('user_id', $inactive->id)->where('title', 'We miss you!')->count())->toBe(1);
    });

    it('refreshes recommendations for active students with stale roadmaps', function () {
        config(['fluentpath.inactivity_reminder_days' => 3]);

        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        LessonProgress::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'updated_at' => now()->subDays(10),
        ]);

        $week = RoadmapWeek::factory()->create([
            'roadmap_id' => $roadmap->id,
            'week_number' => 1,
            'objective' => 'Foundations',
        ]);

        RoadmapWeekLesson::factory()->create([
            'roadmap_week_id' => $week->id,
            'lesson_id' => $lesson->id,
            'display_order' => 1,
        ]);

        runDailyMaintenance();

        $roadmap = $user->roadmaps()->latest('id')->first();

        expect($roadmap->next_lesson_id)->not->toBeNull()
            ->and($roadmap->next_writing_prompt)->not->toBeNull();
    });
});

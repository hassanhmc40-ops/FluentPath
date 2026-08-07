<?php

use App\Models\User;
use App\Models\UserDailyActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('track daily activity', function () {
    it('records today as an active day when a student opens a page', function () {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')->assertStatus(200);

        $this->assertDatabaseCount('user_daily_activity', 1);
        $this->assertDatabaseHas('user_daily_activity', [
            'user_id' => auth()->id(),
            'activity_date' => now()->toDateString(),
        ]);
    });

    it('records at most one row per user per day', function () {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/lessons')->assertStatus(200);
        $this->get('/placement-test')->assertStatus(200);

        $this->assertDatabaseCount('user_daily_activity', 1);
    });

    it('does not record a row for guests', function () {
        $this->get('/login')->assertStatus(200);

        $this->assertDatabaseCount('user_daily_activity', 0);
    });

    it('does not record a row for admins', function () {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/dashboard')->assertStatus(200);

        $this->assertDatabaseCount('user_daily_activity', 0);
    });

    it('exposes the recorded dates to the streak via the dashboard API', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        UserDailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
        ]);

        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.learning_streak', 1);
    });
});

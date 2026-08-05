<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('onboarding flow', function () {
    it('shows the onboarding page to an authenticated student', function () {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/onboarding')
            ->assertOk()
            ->assertSee('What brings you here?')
            ->assertSee('Work & interviews')
            ->assertSee('3-4 hours')
            ->assertSee('Reading speed');
    });

    it('stores goal, weekly hours and struggle on the user profile', function () {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post('/onboarding', [
                'goal' => 'Work & interviews',
                'weekly_hours' => '3-4 hours',
                'struggle' => 'Writing',
            ])
            ->assertRedirect('/placement-test');

        $user->refresh();
        expect($user->onboarding_goal)->toBe('Work & interviews');
        expect($user->weekly_hours)->toBe('3-4 hours');
        expect($user->struggle)->toBe('Writing');
    });

    it('rejects invalid option values', function () {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post('/onboarding', [
                'goal' => 'Hacking',
                'weekly_hours' => 'all day',
                'struggle' => 'Everything',
            ])
            ->assertSessionHasErrors(['goal', 'weekly_hours', 'struggle']);

        $user->refresh();
        expect($user->onboarding_goal)->toBeNull();
    });

    it('guests are redirected away from the onboarding page', function () {
        $this->get('/onboarding')->assertRedirect('/login');
    });
});

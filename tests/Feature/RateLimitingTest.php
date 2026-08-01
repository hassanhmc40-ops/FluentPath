<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('rate limiting on AI-triggering routes', function () {
    it('allows up to 5 AI submissions per minute on the API, then returns 429', function () {
        Queue::fake();
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/writing-submissions', [
                'prompt' => 'Describe your day.',
                'original_text' => 'Today I woke up early.',
            ])->assertStatus(202);
        }

        $this->postJson('/api/writing-submissions', [
            'prompt' => 'Describe your day.',
            'original_text' => 'Today I woke up early.',
        ])->assertStatus(429);
    });

    it('allows up to 5 AI submissions per minute on the web, then returns 429', function () {
        Queue::fake();
        $this->actingAs(User::factory()->create());

        for ($i = 0; $i < 5; $i++) {
            $this->post('/writing', [
                'prompt' => 'Describe your day.',
                'original_text' => 'Today I woke up early.',
            ])->assertStatus(302);
        }

        $this->post('/writing', [
            'prompt' => 'Describe your day.',
            'original_text' => 'Today I woke up early.',
        ])->assertStatus(429);
    });

    it('limits each user independently', function () {
        Queue::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/writing-submissions', [
                'prompt' => 'Describe your day.',
                'original_text' => 'Today I woke up early.',
            ])->assertStatus(202);
        }

        $this->postJson('/api/writing-submissions', [
            'prompt' => 'Describe your day.',
            'original_text' => 'Today I woke up early.',
        ])->assertStatus(429);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/writing-submissions', [
            'prompt' => 'Describe your day.',
            'original_text' => 'Today I woke up early.',
        ])->assertStatus(202);
    });
});

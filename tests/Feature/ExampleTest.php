<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests hitting the root are sent to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});

test('authenticated users hitting the root go to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
});

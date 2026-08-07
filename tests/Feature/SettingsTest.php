<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('settings page', function () {
    it('hides the danger zone for admins', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/settings')
            ->assertStatus(200)
            ->assertSee('Settings')
            ->assertDontSee('Danger zone')
            ->assertDontSee('Retake placement test');
    });

    it('shows the danger zone to students', function () {
        $student = User::factory()->create();
        $this->actingAs($student);

        $this->get('/settings')
            ->assertStatus(200)
            ->assertSee('Settings')
            ->assertSee('Danger zone')
            ->assertSee('Retake placement test');
    });
});

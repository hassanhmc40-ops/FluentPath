<?php

use App\Enums\CefrLevel;
use App\Models\PlacementTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('placement test history', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('lists previous analyzed attempts below the latest result', function () {
        $old = PlacementTest::factory()->analyzed()->create([
            'user_id' => $this->user->id,
            'submitted_at' => now()->subDays(30),
            'cefr_level' => CefrLevel::B1,
            'grammar_score' => 50.0,
            'vocabulary_score' => 60.0,
            'reading_score' => 70.0,
            'writing_score' => 80.0,
            'strengths' => ['Consistent verb tense'],
            'weaknesses' => ['Passive voice'],
        ]);

        $latest = PlacementTest::factory()->analyzed()->create([
            'user_id' => $this->user->id,
            'cefr_level' => CefrLevel::C1,
            'grammar_score' => 95.0,
        ]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('PREVIOUS RESULTS')
            ->assertSee('C1')
            ->assertSee('B1')
            ->assertSee($old->submitted_at->format('M j, Y'))
            ->assertSee('Consistent verb tense')
            ->assertSee('Passive voice')
            ->assertSee('95');

        $this->assertSame($latest->id, $this->user->placementTests()->latest('id')->first()->id);
    });

    it('hides the history section when there is only one analyzed attempt', function () {
        PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('View my roadmap')
            ->assertDontSee('PREVIOUS RESULTS');
    });

    it('does not show history while a newer test is still processing', function () {
        PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);
        PlacementTest::factory()->create(['user_id' => $this->user->id]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('Evaluating your submission')
            ->assertDontSee('PREVIOUS RESULTS');

        $this->assertDatabaseCount('placement_tests', 2);
    });

    it('only shows history for the authenticated student', function () {
        $other = User::factory()->create();
        PlacementTest::factory()->analyzed()->create([
            'user_id' => $other->id,
            'submitted_at' => now()->subDays(10),
            'cefr_level' => CefrLevel::A2,
            'strengths' => ['Other students grammar flair'],
        ]);

        PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertDontSee('PREVIOUS RESULTS')
            ->assertDontSee('Other students grammar flair');
    });
});

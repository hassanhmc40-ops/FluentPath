<?php

use App\Agents\PlacementEvaluationAgent;
use App\Enums\PlacementTestStatus;
use App\Enums\RoadmapStatus;
use App\Jobs\EvaluatePlacementTest;
use App\Jobs\GenerateRoadmap;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Ai as AI;

uses(RefreshDatabase::class);

/**
 * A valid PlacementEvaluationAgent response (helper names are prefixed to
 * avoid colliding with the globals in PlacementTestTest/RoadmapTest).
 */
function autoGenValidAnalysis(): array
{
    return [
        'cefr_level' => 'A1',
        'writing_score' => 70,
        'strengths' => ['Basic vocabulary'],
        'weaknesses' => ['Present simple'],
        'reasoning' => 'The learner demonstrates beginner-level skills.',
    ];
}

function autoGenAnalyzedTest(int $userId): PlacementTest
{
    return PlacementTest::factory()->analyzed()->create(['user_id' => $userId]);
}

describe('roadmap auto-generation', function () {
    it('creates a roadmap and dispatches GenerateRoadmap when evaluation succeeds', function () {
        Queue::fake();

        $user = User::factory()->create();
        $test = PlacementTest::factory()->create(['user_id' => $user->id]);

        AI::fakeAgent(PlacementEvaluationAgent::class, [autoGenValidAnalysis()]);

        (new EvaluatePlacementTest($test))->handle();

        expect($test->fresh()->status)->toBe(PlacementTestStatus::Analyzed);

        $this->assertDatabaseCount('roadmaps', 1);
        $this->assertDatabaseHas('roadmaps', [
            'user_id' => $user->id,
            'placement_test_id' => $test->id,
            'title' => 'Generating...',
        ]);

        Queue::assertPushed(GenerateRoadmap::class, fn (GenerateRoadmap $job) => $job->roadmap->placement_test_id === $test->id);
    });

    it('does not create a second roadmap when the evaluation job is retried', function () {
        Queue::fake();

        $user = User::factory()->create();
        $test = PlacementTest::factory()->create(['user_id' => $user->id]);

        AI::fakeAgent(PlacementEvaluationAgent::class, [autoGenValidAnalysis()]);

        (new EvaluatePlacementTest($test))->handle();
        (new EvaluatePlacementTest($test))->handle();

        $this->assertDatabaseCount('roadmaps', 1);

        Queue::assertPushed(GenerateRoadmap::class, 1);
    });

    it('auto-generates a roadmap on the roadmap page when the student has an analyzed test', function () {
        Queue::fake();

        $user = User::factory()->create();
        autoGenAnalyzedTest($user->id);

        $this->actingAs($user)->get('/roadmap')
            ->assertStatus(200)
            ->assertSee('Generating your roadmap...');

        $this->assertDatabaseCount('roadmaps', 1);
        $this->assertDatabaseHas('roadmaps', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Queue::assertPushed(GenerateRoadmap::class);
    });

    it('does not dispatch twice when the roadmap page is visited repeatedly', function () {
        Queue::fake();

        $user = User::factory()->create();
        autoGenAnalyzedTest($user->id);

        $this->actingAs($user)->get('/roadmap');
        $this->actingAs($user)->get('/roadmap');

        $this->assertDatabaseCount('roadmaps', 1);

        Queue::assertPushed(GenerateRoadmap::class, 1);
    });

    it('shows the empty state without dispatching when there is no analyzed test', function () {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->get('/roadmap')
            ->assertStatus(200)
            ->assertSee('No roadmap yet');

        $this->assertDatabaseCount('roadmaps', 0);

        Queue::assertNotPushed(GenerateRoadmap::class);
    });

    it('keeps the web store endpoint idempotent for the same analyzed test', function () {
        Queue::fake();

        $user = User::factory()->create();
        autoGenAnalyzedTest($user->id);

        $this->actingAs($user)->post('/roadmap')->assertRedirect('/roadmap');
        $this->actingAs($user)->post('/roadmap')->assertRedirect('/roadmap');

        $this->assertDatabaseCount('roadmaps', 1);

        Queue::assertPushed(GenerateRoadmap::class, 1);
    });

    it('shows the failed card with a retry button when the roadmap failed', function () {
        $user = User::factory()->create();
        Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => autoGenAnalyzedTest($user->id)->id,
            'status' => RoadmapStatus::Failed,
        ]);

        $this->actingAs($user)->get('/roadmap')
            ->assertStatus(200)
            ->assertSee('Roadmap generation failed')
            ->assertSee('Try again')
            ->assertSee('action="/roadmap"', false);
    });

    it('re-dispatches generation when retrying a failed roadmap from the web', function () {
        Queue::fake();

        $user = User::factory()->create();
        Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => autoGenAnalyzedTest($user->id)->id,
            'status' => RoadmapStatus::Failed,
        ]);

        $this->actingAs($user)->post('/roadmap')->assertRedirect('/roadmap');

        $this->assertDatabaseHas('roadmaps', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Queue::assertPushed(GenerateRoadmap::class, 1);
    });
});

<?php

use App\Jobs\CorrectWritingSubmission;
use App\Jobs\EvaluatePlacementTest;
use App\Jobs\GenerateRoadmap;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AI queue resilience', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('gives AI jobs a generous timeout, retries and backoff', function () {
        $test = PlacementTest::factory()->create(['user_id' => $this->user->id]);
        $roadmap = Roadmap::factory()->create(['user_id' => $this->user->id, 'placement_test_id' => $test->id]);
        $writing = WritingSubmission::factory()->create(['user_id' => $this->user->id]);

        foreach ([new EvaluatePlacementTest($test), new GenerateRoadmap($roadmap), new CorrectWritingSubmission($writing)] as $job) {
            expect($job->timeout)->toBe(300)
                ->and($job->tries)->toBe(3)
                ->and($job->backoff)->toBe([10, 30, 60]);
        }
    });

    it('marks the placement test as failed via the failed() hook', function () {
        $test = PlacementTest::factory()->create(['user_id' => $this->user->id]);

        (new EvaluatePlacementTest($test))->failed(new RuntimeException('Groq rate limited'));

        $this->assertDatabaseHas('placement_tests', [
            'id' => $test->id,
            'status' => 'failed',
        ]);
    });

    it('marks the roadmap as failed via the failed() hook', function () {
        $test = PlacementTest::factory()->create(['user_id' => $this->user->id]);
        $roadmap = Roadmap::factory()->create(['user_id' => $this->user->id, 'placement_test_id' => $test->id]);

        (new GenerateRoadmap($roadmap))->failed(new RuntimeException('Groq 5xx'));

        $this->assertDatabaseHas('roadmaps', [
            'id' => $roadmap->id,
            'status' => 'failed',
        ]);
    });

    it('marks the writing submission as failed via the failed() hook', function () {
        $writing = WritingSubmission::factory()->create(['user_id' => $this->user->id]);

        (new CorrectWritingSubmission($writing))->failed(new RuntimeException('Groq 5xx'));

        $this->assertDatabaseHas('writing_submissions', [
            'id' => $writing->id,
            'status' => 'failed',
        ]);
    });

    it('shows a failed-state card with a retake button instead of the form', function () {
        PlacementTest::factory()->failed()->create(['user_id' => $this->user->id]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('Evaluation failed')
            ->assertSee('Retake test')
            ->assertDontSee('Submit placement test');
    });

    it('auto-refreshes the page while the placement test is processing', function () {
        PlacementTest::factory()->create(['user_id' => $this->user->id]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('Evaluating your submission')
            ->assertSee('window.location.reload()');
    });

    it('does not auto-refresh once the placement test is analyzed', function () {
        PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('View my roadmap')
            ->assertDontSee('window.location.reload()');
    });

    it('auto-refreshes the roadmap page while it is generating', function () {
        PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);
        $test = PlacementTest::where('user_id', $this->user->id)->latest('id')->first();
        Roadmap::factory()->create([
            'user_id' => $this->user->id,
            'placement_test_id' => $test->id,
            'status' => 'processing',
        ]);

        $this->get('/roadmap')
            ->assertStatus(200)
            ->assertSee('Generating your roadmap')
            ->assertSee('window.location.reload()');
    });

    it('auto-refreshes the writing page while a submission is being corrected', function () {
        WritingSubmission::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $this->get('/writing')
            ->assertStatus(200)
            ->assertSee('Correcting your text')
            ->assertSee('window.location.reload()');
    });

    it('does not auto-refresh the writing page once the submission is corrected', function () {
        WritingSubmission::factory()->corrected()->create(['user_id' => $this->user->id]);

        $this->get('/writing')
            ->assertStatus(200)
            ->assertSee('Score')
            ->assertDontSee('window.location.reload()');
    });

    it('does not auto-refresh the writing page when the submission failed', function () {
        WritingSubmission::factory()->failed()->create(['user_id' => $this->user->id]);

        $this->get('/writing')
            ->assertStatus(200)
            ->assertSee('Correction could not be completed')
            ->assertDontSee('window.location.reload()');
    });
});

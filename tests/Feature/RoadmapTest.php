<?php

use App\Agents\RoadmapGenerationAgent;
use App\Enums\RoadmapStatus;
use App\Jobs\GenerateRoadmap;
use App\Models\Lesson;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Ai as AI;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function validRoadmapResponse(array $lessonIds): array
{
    return [
        'title' => 'Your 4-Week English Boost',
        'weeks' => [
            ['week_number' => 1, 'objective' => 'Foundations', 'lesson_ids' => [$lessonIds[0], $lessonIds[1]]],
            ['week_number' => 2, 'objective' => 'Practice', 'lesson_ids' => [$lessonIds[2]]],
            ['week_number' => 3, 'objective' => 'Expand', 'lesson_ids' => [$lessonIds[3], $lessonIds[4]]],
            ['week_number' => 4, 'objective' => 'Consolidate', 'lesson_ids' => [$lessonIds[5]]],
        ],
    ];
}

function seedAnalyzedTest(int $userId, ?string $level = null): PlacementTest
{
    $attributes = ['user_id' => $userId];

    if ($level !== null) {
        $attributes['cefr_level'] = $level;
    }

    return PlacementTest::factory()->analyzed()->create($attributes);
}

describe('generation', function () {
    it('rejects roadmap generation before the placement test is analyzed with 422', function () {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/roadmaps')->assertStatus(422)
            ->assertJsonPath('message', 'Placement test must be analyzed before generating a roadmap.');

        Queue::assertNotPushed(GenerateRoadmap::class);
    });

    it('rejects roadmap generation when only a pending test exists', function () {
        Queue::fake();

        $user = User::factory()->create();
        PlacementTest::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        Sanctum::actingAs($user);

        $this->postJson('/api/roadmaps')->assertStatus(422);

        Queue::assertNotPushed(GenerateRoadmap::class);
    });

    it('accepts roadmap generation after analysis and dispatches the job', function () {
        Queue::fake();

        $user = User::factory()->create();
        seedAnalyzedTest($user->id);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/roadmaps');

        $response->assertStatus(202)
            ->assertJsonStructure(['id', 'status'])
            ->assertJsonPath('status', 'pending');

        $id = $response->json('id');

        $this->assertDatabaseHas('roadmaps', [
            'id' => $id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Queue::assertPushed(GenerateRoadmap::class, fn (GenerateRoadmap $job) => $job->roadmap->id === $id);
    });

    it('requires authentication', function () {
        $this->postJson('/api/roadmaps')->assertStatus(401);
    });
});

describe('show', function () {
    it('returns 404 when the user has no roadmap', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/roadmaps')->assertStatus(404);
    });

    it('returns the roadmap with weeks and lessons', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $placementTest = seedAnalyzedTest($user->id);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
            'status' => 'generated',
        ]);

        $lessons = Lesson::factory()->count(2)->create();

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

        $this->getJson('/api/roadmaps')
            ->assertStatus(200)
            ->assertJsonPath('data.id', $roadmap->id)
            ->assertJsonPath('data.status', 'generated')
            ->assertJsonCount(1, 'data.weeks')
            ->assertJsonPath('data.weeks.0.week_number', 1)
            ->assertJsonPath('data.weeks.0.objective', 'Foundations')
            ->assertJsonCount(2, 'data.weeks.0.lessons')
            ->assertJsonPath('data.weeks.0.lessons.0.display_order', 1)
            ->assertJsonPath('data.weeks.0.lessons.0.lesson.id', $lessons[0]->id)
            ->assertJsonPath('data.weeks.0.lessons.1.display_order', 2)
            ->assertJsonPath('data.weeks.0.lessons.1.lesson.id', $lessons[1]->id);
    });
});

describe('generation job', function () {
    it('persists exactly 4 weeks with ordered lessons and marks the roadmap generated', function () {
        $user = User::factory()->create();
        $placementTest = seedAnalyzedTest($user->id);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
            'title' => 'Generating...',
        ]);

        $lessons = Lesson::factory()->count(6)->create();

        AI::fakeAgent(RoadmapGenerationAgent::class, [validRoadmapResponse($lessons->pluck('id')->all())]);

        (new GenerateRoadmap($roadmap))->handle();

        $roadmap->refresh();

        expect($roadmap->status)->toBe(RoadmapStatus::Generated)
            ->and($roadmap->title)->toBe('Your 4-Week English Boost')
            ->and($roadmap->generated_at)->not->toBeNull();

        $this->assertDatabaseCount('roadmap_weeks', 4);
        $this->assertDatabaseCount('roadmap_week_lessons', 6);

        $weeks = RoadmapWeek::where('roadmap_id', $roadmap->id)->orderBy('week_number')->get();

        expect($weeks->pluck('week_number')->all())->toBe([1, 2, 3, 4]);

        $week1Lessons = RoadmapWeekLesson::where('roadmap_week_id', $weeks[0]->id)
            ->orderBy('display_order')
            ->get();

        expect($week1Lessons->pluck('display_order')->all())->toBe([1, 2])
            ->and($week1Lessons->pluck('lesson_id')->all())->toBe([$lessons[0]->id, $lessons[1]->id]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Personalized Roadmap Generated',
        ]);
    });

    it('sends the available lessons to the agent in the prompt', function () {
        $user = User::factory()->create();
        // Level-coherent setup: the placement test is B1 and every lesson is
        // B1, so the level-bias filter keeps the whole catalog in the prompt.
        $placementTest = seedAnalyzedTest($user->id, 'B1');

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
        ]);

        $lessons = Lesson::factory()->count(6)->create(['level' => 'B1']);

        AI::fakeAgent(RoadmapGenerationAgent::class, [validRoadmapResponse($lessons->pluck('id')->all())]);

        (new GenerateRoadmap($roadmap))->handle();

        AI::assertAgentWasPrompted(
            RoadmapGenerationAgent::class,
            fn ($prompt) => $prompt->contains('Available Lessons') && $prompt->contains("{$lessons[0]->id}:")
        );
    });

    it('rejects the whole response and marks the roadmap failed when a lesson_id does not exist (BR04)', function () {
        $user = User::factory()->create();
        $placementTest = seedAnalyzedTest($user->id);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
            'title' => 'Original Title',
        ]);

        $lessons = Lesson::factory()->count(6)->create();

        $invalidResponse = validRoadmapResponse($lessons->pluck('id')->all());
        $invalidResponse['weeks'][0]['lesson_ids'] = [$lessons[0]->id, 999999];

        AI::fakeAgent(RoadmapGenerationAgent::class, [$invalidResponse]);

        (new GenerateRoadmap($roadmap))->handle();

        $roadmap->refresh();

        expect($roadmap->status)->toBe(RoadmapStatus::Failed)
            ->and($roadmap->title)->toBe('Original Title')
            ->and($roadmap->generated_at)->not->toBeNull();

        $this->assertDatabaseCount('roadmap_weeks', 0);
        $this->assertDatabaseCount('roadmap_week_lessons', 0);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'title' => 'Personalized Roadmap Generated',
        ]);
    });

    it('marks the roadmap failed when the response does not contain exactly 4 weeks', function () {
        $user = User::factory()->create();
        $placementTest = seedAnalyzedTest($user->id);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
        ]);

        $lessons = Lesson::factory()->count(3)->create();

        $invalidResponse = [
            'title' => 'Too Short',
            'weeks' => [
                ['week_number' => 1, 'objective' => 'Only week', 'lesson_ids' => [$lessons[0]->id]],
                ['week_number' => 2, 'objective' => 'Second week', 'lesson_ids' => [$lessons[1]->id]],
                ['week_number' => 3, 'objective' => 'Third week', 'lesson_ids' => [$lessons[2]->id]],
            ],
        ];

        AI::fakeAgent(RoadmapGenerationAgent::class, [$invalidResponse]);

        (new GenerateRoadmap($roadmap))->handle();

        expect($roadmap->fresh()->status)->toBe(RoadmapStatus::Failed);

        $this->assertDatabaseCount('roadmap_weeks', 0);
    });

    it('marks the roadmap failed when week data is malformed', function () {
        $user = User::factory()->create();
        $placementTest = seedAnalyzedTest($user->id);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
        ]);

        $lessons = Lesson::factory()->count(6)->create();

        $invalidResponse = validRoadmapResponse($lessons->pluck('id')->all());
        $invalidResponse['weeks'][0]['lesson_ids'] = ['not-an-int'];

        AI::fakeAgent(RoadmapGenerationAgent::class, [$invalidResponse]);

        (new GenerateRoadmap($roadmap))->handle();

        expect($roadmap->fresh()->status)->toBe(RoadmapStatus::Failed);

        $this->assertDatabaseCount('roadmap_weeks', 0);
    });

    it('marks the roadmap failed when the AI call throws', function () {
        $user = User::factory()->create();
        $placementTest = seedAnalyzedTest($user->id);

        $roadmap = Roadmap::factory()->create([
            'user_id' => $user->id,
            'placement_test_id' => $placementTest->id,
        ]);

        AI::fakeAgent(RoadmapGenerationAgent::class, [
            fn () => throw new RuntimeException('Groq is down'),
        ]);

        (new GenerateRoadmap($roadmap))->handle();

        expect($roadmap->fresh()->status)->toBe(RoadmapStatus::Failed);
    });
});

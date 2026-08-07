<?php

use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use App\Models\Lesson;
use App\Models\PlacementTest;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('scopes the lessons page to the student CEFR level', function () {
    $b1Lesson = Lesson::factory()->create(['level' => CefrLevel::B1, 'title' => 'B1 Conditionals Deep Dive']);
    $b1Vocab = Lesson::factory()->create(['level' => CefrLevel::B1, 'title' => 'B1 Travel Vocabulary']);
    Lesson::factory()->create(['level' => CefrLevel::A1, 'title' => 'A1 First Words']);
    Lesson::factory()->create(['level' => CefrLevel::C1, 'title' => 'C1 Idiomatic English']);

    $user = User::factory()->create();
    PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => CefrLevel::B1]);
    $this->actingAs($user);

    $this->get('/lessons')
        ->assertStatus(200)
        ->assertSee('B1 level')
        ->assertSee('B1 Conditionals Deep Dive')
        ->assertSee('B1 Travel Vocabulary')
        ->assertDontSee('A1 First Words')
        ->assertDontSee('C1 Idiomatic English');
});

it('scopes the exercises page to the student CEFR level via the lesson', function () {
    $b1Lesson = Lesson::factory()->create(['level' => CefrLevel::B1]);
    $a2Lesson = Lesson::factory()->create(['level' => CefrLevel::A2]);
    Quiz::factory()->create(['lesson_id' => $b1Lesson->id, 'title' => 'B1 Mix Up Exercise']);
    Quiz::factory()->create(['lesson_id' => $a2Lesson->id, 'title' => 'A2 Telling Time Exercise']);

    $user = User::factory()->create();
    PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => CefrLevel::B1]);
    $this->actingAs($user);

    $this->get('/quizzes')
        ->assertStatus(200)
        ->assertSee('B1 level')
        ->assertSee('B1 Mix Up Exercise')
        ->assertDontSee('A2 Telling Time Exercise');
});

it('uses the most recent analyzed placement test as the level', function () {
    Lesson::factory()->create(['level' => CefrLevel::A2, 'title' => 'A2 Past Stories']);

    $user = User::factory()->create();
    PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => CefrLevel::A1]);
    PlacementTest::factory()->analyzed()->create(['user_id' => $user->id, 'cefr_level' => CefrLevel::A2]);
    $this->actingAs($user);

    $this->get('/lessons')
        ->assertStatus(200)
        ->assertSee('A2 Past Stories');
});

it('shows a placement test CTA instead of lessons when the student has no level', function () {
    Lesson::factory()->count(3)->create();

    $this->actingAs(User::factory()->create());

    $this->get('/lessons')
        ->assertStatus(200)
        ->assertSee('Complete your placement test first')
        ->assertSee('Take the placement test')
        ->assertDontSee('Completed ✓');
});

it('shows a placement test CTA instead of exercises when the student has no level', function () {
    Quiz::factory()->count(3)->create();

    $this->actingAs(User::factory()->create());

    $this->get('/quizzes')
        ->assertStatus(200)
        ->assertSee('Complete your placement test first');
});

it('shows the placement test CTA even when only a pending test exists', function () {
    $user = User::factory()->create();
    PlacementTest::factory()->create(['user_id' => $user->id, 'status' => PlacementTestStatus::Pending]);
    $this->actingAs($user);

    $this->get('/lessons')
        ->assertStatus(200)
        ->assertSee('Complete your placement test first');
});

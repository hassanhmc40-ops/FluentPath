<?php

use App\Enums\RoadmapStatus;
use App\Jobs\EvaluatePlacementTest;
use App\Models\Lesson;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Roadmap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('redirects guests to the login page instead of erroring', function () {
    $this->get('/placement-test')->assertRedirect('/login');
    $this->get('/roadmap')->assertRedirect('/login');
    $this->get('/lessons')->assertRedirect('/login');
    $this->get('/writing')->assertRedirect('/login');
});

describe('student pages', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('renders the main student pages', function () {
        $this->get('/dashboard')->assertStatus(200)->assertSee('Dashboard');
        $this->get('/placement-test')->assertStatus(200)->assertSee('Placement Test');
        $this->get('/roadmap')->assertStatus(200)->assertSee('My Roadmap');
        $this->get('/lessons')->assertStatus(200)->assertSee('Lessons');
        $this->get('/writing')->assertStatus(200)->assertSee('Writing Practice');
        $this->get('/notifications')->assertStatus(200)->assertSee('Notifications');
    });

    it('shows the placement test form with questions', function () {
        PlacementQuestion::factory()->count(3)->create();

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('Submit placement test')
            ->assertSee('type="radio"', false);
    });

    it('submits the placement test and dispatches evaluation', function () {
        Queue::fake();

        $questions = PlacementQuestion::factory()->count(2)->create();

        $this->post('/placement-test', [
            'answers' => $questions->map(fn ($q, $i) => [
                'placement_question_id' => $q->id,
                'answer' => $q->correct_answer ?? "Answer {$i}.",
            ])->values()->all(),
        ])->assertRedirect('/placement-test');

        $this->assertDatabaseCount('placement_tests', 1);
        $this->assertDatabaseCount('placement_answers', 2);
        $this->assertDatabaseHas('placement_answers', [
            'placement_question_id' => $questions->first()->id,
            'answer' => $questions->first()->correct_answer ?? 'Answer 0.',
        ]);
    });

    it('renders the skip buttons and live counter on the placement form', function () {
        PlacementQuestion::factory()->count(3)->create();

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('data-skip=', false)
            ->assertSee('fp-answered-count', false)
            ->assertSee('fp-total-count', false)
            ->assertSee("you can skip questions you don't know", false)
            ->assertSee('Submit placement test');
    });

    it('stores only answered questions when the submission includes skipped entries', function () {
        Queue::fake();

        $questions = PlacementQuestion::factory()->count(2)->create();
        $q1 = $questions[0];
        $q2 = $questions[1];

        $this->post('/placement-test', [
            'answers' => [
                [
                    'placement_question_id' => $q1->id,
                    'answer' => $q1->correct_answer ?? 'Answer 0.',
                ],
                [
                    'placement_question_id' => $q2->id,
                    'answer' => '',
                ],
            ],
        ])->assertRedirect('/placement-test');

        $this->assertDatabaseCount('placement_tests', 1);
        $this->assertDatabaseCount('placement_answers', 1);
        $this->assertDatabaseHas('placement_answers', [
            'placement_question_id' => $q1->id,
        ]);

        Queue::assertPushed(EvaluatePlacementTest::class);
    });

    it('retakes the placement test: deletes the old test and roadmap and shows the form again', function () {
        $test = PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);
        Roadmap::factory()->create([
            'user_id' => $this->user->id,
            'placement_test_id' => $test->id,
        ]);

        $this->get('/placement-test')->assertSee('View my roadmap');

        $this->post('/placement-test/retake')
            ->assertRedirect('/placement-test');

        $this->assertDatabaseCount('placement_tests', 0);
        $this->assertDatabaseCount('roadmaps', 0);

        $this->get('/placement-test')
            ->assertStatus(200)
            ->assertSee('Submit placement test')
            ->assertDontSee('View my roadmap');
    });

    it('retaking is a no-op when the student has no analyzed test', function () {
        $this->post('/placement-test/retake')->assertRedirect('/placement-test');
        $this->assertDatabaseCount('placement_tests', 0);
    });

    it('rejects GET requests to the retake endpoint', function () {
        $this->get('/placement-test/retake')->assertStatus(405);
    });

    it('submits a writing piece and dispatches correction', function () {
        Queue::fake();

        $this->post('/writing', [
            'prompt' => 'Describe your home town.',
            'original_text' => 'My home town is small and quiet.',
        ])->assertRedirect();

        $this->assertDatabaseCount('writing_submissions', 1);
        $this->assertDatabaseHas('writing_submissions', [
            'user_id' => $this->user->id,
            'prompt' => 'Describe your home town.',
        ]);
    });

    it('marks a lesson complete from the web', function () {
        $lesson = Lesson::factory()->create();

        $this->post("/lessons/{$lesson->id}/complete")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $this->user->id,
            'lesson_id' => $lesson->id,
        ]);
    });

    it('takes a quiz from the web and records the attempt', function () {
        $quiz = Quiz::factory()->create();
        $questions = QuizQuestion::factory()->count(2)->create([
            'quiz_id' => $quiz->id,
            'option_a' => 'wrong',
            'option_b' => 'correct',
            'option_c' => 'wrong',
            'option_d' => 'wrong',
            'correct_answer' => 'b',
        ]);

        $this->get("/quizzes/{$quiz->id}")->assertStatus(200)->assertSee($quiz->title);

        $answers = $questions->map(fn ($q) => [
            'quiz_question_id' => $q->id,
            'selected_option' => 'b',
        ])->all();

        $this->post("/quizzes/{$quiz->id}/attempt", ['answers' => $answers])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $this->user->id,
            'quiz_id' => $quiz->id,
            'score' => 100.0,
        ]);
    });

    it('marks a notification as read from the web', function () {
        $notification = $this->user->notifications()->create([
            'title' => 'Welcome',
            'message' => 'Start learning today.',
        ]);

        $this->post("/notifications/{$notification->id}/read")
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    });

    it('shows the roadmap with generated weeks and lessons', function () {
        $test = PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);
        $lesson = Lesson::factory()->create();
        $roadmap = Roadmap::factory()->create([
            'user_id' => $this->user->id,
            'placement_test_id' => $test->id,
            'status' => RoadmapStatus::Generated,
        ]);
        $week = $roadmap->roadmapWeeks()->create(['week_number' => 1, 'objective' => 'Foundations']);
        $week->roadmapWeekLessons()->create(['lesson_id' => $lesson->id, 'display_order' => 1]);

        $this->get('/roadmap')
            ->assertStatus(200)
            ->assertSee('Week 1')
            ->assertSee($lesson->title);
    });

    it('generates a roadmap from the web when a placement test is analyzed', function () {
        Queue::fake();

        PlacementTest::factory()->analyzed()->create(['user_id' => $this->user->id]);

        $this->post('/roadmap')
            ->assertRedirect('/roadmap');

        $this->assertDatabaseCount('roadmaps', 1);
        $this->assertDatabaseHas('roadmaps', ['user_id' => $this->user->id]);
    });

    it('blocks roadmap generation without an analyzed placement test', function () {
        $this->post('/roadmap')
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('roadmaps', 0);
    });
});

describe('admin pages', function () {
    it('forbids students from admin pages', function () {
        $student = User::factory()->create();
        $this->actingAs($student);

        $this->get('/admin/lessons')->assertStatus(403);
        $this->get('/admin/quizzes')->assertStatus(403);
        $this->get('/admin/quiz-questions')->assertStatus(403);
        $this->get('/admin/placement-questions')->assertStatus(403);
    });

    it('renders all admin pages for an admin', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/admin/lessons')->assertStatus(200);
        $this->get('/admin/lessons/create')->assertStatus(200);
        $this->get('/admin/quizzes')->assertStatus(200);
        $this->get('/admin/quizzes/create')->assertStatus(200);
        $this->get('/admin/quiz-questions')->assertStatus(200);
        $this->get('/admin/quiz-questions/create')->assertStatus(200);
        $this->get('/admin/placement-questions')->assertStatus(200)->assertSee('Placement test');
        $this->get('/admin/placement-questions/create')->assertStatus(200);
    });

    it('supports full CRUD on lessons from the web', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->post('/admin/lessons', [
            'title' => 'Past Simple',
            'skill' => 'grammar',
            'level' => 'A2',
            'content' => "## What you'll learn\nThe past simple describes finished actions.\n\n## How it works\n- Regular verbs end in -ed.\n> He walked to work.",
        ])->assertRedirect(route('admin.lessons.index'));

        $lesson = Lesson::where('title', 'Past Simple')->first();
        expect($lesson)->not->toBeNull();

        $this->put("/admin/lessons/{$lesson->id}", [
            'title' => 'Past Simple Verbs',
            'skill' => 'grammar',
            'level' => 'A2',
            'content' => "## What you'll learn\nThe past simple describes finished actions.",
        ])->assertRedirect(route('admin.lessons.index'));

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'title' => 'Past Simple Verbs']);

        $this->delete("/admin/lessons/{$lesson->id}")->assertRedirect(route('admin.lessons.index'));

        $this->assertDatabaseCount('lessons', 0);
    });

    it('supports creating a quiz question from the web', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $quiz = Quiz::factory()->create();

        $this->post('/admin/quiz-questions', [
            'quiz_id' => $quiz->id,
            'question' => 'What is the past of "go"?',
            'option_a' => 'goed',
            'option_b' => 'went',
            'option_c' => 'go',
            'option_d' => 'gone',
            'correct_answer' => 'b',
        ])->assertRedirect(route('admin.quiz-questions.index'));

        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question' => 'What is the past of "go"?',
            'correct_answer' => 'b',
        ]);
    });
});

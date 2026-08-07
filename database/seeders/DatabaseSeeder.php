<?php

namespace Database\Seeders;

use App\Enums\LessonProgressStatus;
use App\Enums\PlacementTestStatus;
use App\Enums\RoadmapStatus;
use App\Enums\WritingSubmissionStatus;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\PlacementAnswer;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->resetTables();

        $contentSeeder = new ContentSeeder;
        $contentSeeder->run();
        $lessonIds = $contentSeeder->lessonIds;

        $answers = require database_path('data/answers.php');
        $writingPack = require database_path('data/vocabulary_writing.php');

        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@fluentpath.com',
        ]);

        $students = [
            'A1' => ['name' => 'Sara Benali', 'email' => 'sara@fluentpath.com', 'scores' => [58, 54, 57, 55], 'attempts' => [62, 58, 71, 66, 74]],
            'A2' => ['name' => 'Yassine El Amrani', 'email' => 'yassine@fluentpath.com', 'scores' => [66, 64, 65, 63], 'attempts' => [70, 66, 78, 72, 81]],
            'B1' => ['name' => 'Lina Haddad', 'email' => 'lina@fluentpath.com', 'scores' => [76, 73, 74, 75], 'attempts' => [78, 74, 85, 79, 88]],
            'B2' => ['name' => 'Omar Tazi', 'email' => 'omar@fluentpath.com', 'scores' => [85, 83, 84, 84], 'attempts' => [86, 82, 91, 85, 93]],
            'C1' => ['name' => 'Nadia Berrada', 'email' => 'nadia@fluentpath.com', 'scores' => [93, 92, 94, 94], 'attempts' => [92, 89, 95, 91, 96]],
        ];

        foreach ($students as $level => $config) {
            // Demo students join 14 days ago so their seeded activity history
            // (placement test at -14 days down to writing at -2 days) is
            // consistent with their account age — a user's streak can never
            // exceed the days their account has existed.
            $student = User::factory()->create([
                'name' => $config['name'],
                'email' => $config['email'],
                'created_at' => now()->subDays(14),
            ]);

            $this->seedPlacementTest($student, $level, $config['scores'], $answers);
            $this->seedRoadmap($student, $level, $lessonIds, $config['attempts']);
            $this->seedWriting($student, $level, $writingPack['writing']);
            $this->seedNotifications($student, $level);
        }
    }

    protected function resetTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            LessonProgress::class,
            RoadmapWeekLesson::class,
            RoadmapWeek::class,
            Roadmap::class,
            QuizAttempt::class,
            QuizQuestion::class,
            Quiz::class,
            WritingSubmission::class,
            PlacementAnswer::class,
            PlacementTest::class,
            Notification::class,
            Lesson::class,
            PlacementQuestion::class,
            User::class,
        ] as $model) {
            $model::query()->delete();
        }

        Schema::enableForeignKeyConstraints();
    }

    protected function seedPlacementTest(User $student, string $level, array $scores, array $answers): void
    {
        [$grammar, $vocabulary, $reading, $writing] = $scores;

        $placementTest = PlacementTest::create([
            'user_id' => $student->id,
            'submitted_at' => now()->subDays(14),
            'status' => PlacementTestStatus::Analyzed,
            'cefr_level' => $level,
            'grammar_score' => $grammar,
            'vocabulary_score' => $vocabulary,
            'reading_score' => $reading,
            'writing_score' => $writing,
            'strengths' => $this->strengthsFor($level),
            'weaknesses' => $this->weaknessesFor($level),
            'reasoning' => $this->reasoningFor($level),
        ]);

        PlacementQuestion::all()
            ->each(function (PlacementQuestion $question) use ($placementTest, $answers, $grammar, $vocabulary, $reading, $writing) {
                $skill = $question->skill->value;

                $score = match ($skill) {
                    'grammar' => $grammar,
                    'vocabulary' => $vocabulary,
                    'reading' => $reading,
                    'writing' => $writing,
                    default => null,
                };

                PlacementAnswer::create([
                    'placement_test_id' => $placementTest->id,
                    'placement_question_id' => $question->id,
                    'answer' => $question->correct_answer !== null
                        ? $this->mcqAnswer($question, $placementTest->cefr_level->value)
                        : $answers[$placementTest->cefr_level->value]['writing'],
                    'score' => $score,
                    'feedback' => $this->feedbackFor($skill),
                ]);
            });
    }

    /**
     * Plausible option-letter answer for an MCQ placement question. Higher
     * levels answer correctly more often (the stored placement test scores
     * already reflect the official level, so these letters are illustrative).
     */
    protected function mcqAnswer(PlacementQuestion $question, string $level): string
    {
        $correctChance = match ($level) {
            'A1' => 0.65,
            'A2' => 0.72,
            'B1' => 0.80,
            'B2' => 0.87,
            default => 0.93,
        };

        if (fake()->randomFloat(2, 0, 1) <= $correctChance) {
            return $question->correct_answer;
        }

        $wrong = array_diff(['a', 'b', 'c', 'd'], [$question->correct_answer]);

        return fake()->randomElement($wrong);
    }

    protected function seedRoadmap(User $student, string $level, array $lessonIds, array $attempts): void
    {
        $templates = require database_path('data/roadmap_templates.php');
        $template = $templates[$level];

        $roadmap = Roadmap::create([
            'user_id' => $student->id,
            'placement_test_id' => PlacementTest::where('user_id', $student->id)->latest('id')->value('id'),
            'title' => $template['title'],
            'status' => RoadmapStatus::Generated,
            'generated_at' => now()->subDays(13),
        ]);

        $completedLessonIds = [];

        foreach ($template['weeks'] as $weekData) {
            $week = RoadmapWeek::create([
                'roadmap_id' => $roadmap->id,
                'week_number' => $weekData['week_number'],
                'objective' => $weekData['objective'],
            ]);

            foreach ($weekData['lesson_keys'] as $order => $key) {
                $lessonId = $lessonIds[$key] ?? null;

                if ($lessonId === null) {
                    continue;
                }

                RoadmapWeekLesson::create([
                    'roadmap_week_id' => $week->id,
                    'lesson_id' => $lessonId,
                    'display_order' => $order + 1,
                ]);

                // Weeks 1-2 fully completed, week 3 first lesson completed.
                if ($weekData['week_number'] <= 2 || ($weekData['week_number'] === 3 && $order === 0)) {
                    $completedLessonIds[] = $lessonId;

                    LessonProgress::create([
                        'user_id' => $student->id,
                        'lesson_id' => $lessonId,
                        'status' => LessonProgressStatus::Completed,
                        'completed_at' => now()->subDays(13 - (count($completedLessonIds))),
                    ]);
                }
            }
        }

        // Next up: the first lesson of week 3 that is not completed yet.
        $week3 = $roadmap->roadmapWeeks()->where('week_number', 3)->first();
        $nextLessonId = $week3?->roadmapWeekLessons()
            ->with('lesson')
            ->get()
            ->map(fn (RoadmapWeekLesson $rwl) => $rwl->lesson_id)
            ->first(fn (int $id) => ! in_array($id, $completedLessonIds, true));

        if ($nextLessonId !== null) {
            $roadmap->update(['next_lesson_id' => $nextLessonId]);
        }

        $completedQuizzes = Quiz::whereIn('lesson_id', $completedLessonIds)->orderBy('id')->take(3)->get();

        $attemptDays = [9, 8, 6, 5, 3];
        $attemptIndex = 0;

        foreach ($completedQuizzes as $index => $quiz) {
            $count = $index === 0 ? 2 : ($index === 1 ? 1 : 2);

            foreach (range(1, $count) as $_) {
                QuizAttempt::create([
                    'user_id' => $student->id,
                    'quiz_id' => $quiz->id,
                    'score' => $attempts[$attemptIndex] ?? 70,
                    'completed_at' => now()->subDays($attemptDays[$attemptIndex] ?? 1),
                ]);

                $attemptIndex++;
            }
        }
    }

    protected function seedWriting(User $student, string $level, array $writingPack): void
    {
        if (empty($writingPack['samples'])) {
            return;
        }

        $prompts = $writingPack['prompts'][$level] ?? ['title' => 'Free writing', 'prompt' => 'Write a short text.'];
        $samples = collect($writingPack['samples'])->where('level', $level)->take(2);
        $days = [7, 2];

        $samples->each(function (array $sample, int $index) use ($student, $prompts, $days) {
            $correctedText = $sample['content'];

            foreach ($sample['mistakes'] ?? [] as $mistake) {
                $correctedText = str_replace($mistake['original'], $mistake['correction'], $correctedText);
            }

            WritingSubmission::create([
                'user_id' => $student->id,
                'prompt' => $prompts['prompt'],
                'original_text' => $sample['content'],
                'corrected_text' => $correctedText,
                'grammar_feedback' => $sample['grammar_feedback'] ?? 'Good progress with grammar.',
                'vocabulary_feedback' => $sample['vocabulary_feedback'] ?? 'Keep building your vocabulary.',
                'fluency_feedback' => $sample['fluency_feedback'] ?? 'Your text flows well.',
                'mistakes' => $sample['mistakes'] ?? [],
                'recommendations' => $sample['recommendations'] ?? [],
                'next_topics' => $sample['recommendations'] ?? [],
                'score' => $sample['score'] ?? 75,
                'status' => WritingSubmissionStatus::Corrected,
                'submitted_at' => now()->subDays($days[$index] ?? 1),
            ]);
        });
    }

    protected function seedNotifications(User $student, string $level): void
    {
        Notification::create([
            'user_id' => $student->id,
            'title' => 'Placement Test Complete',
            'message' => "Your placement test has been analyzed. Your current level is {$level}.",
        ]);

        Notification::create([
            'user_id' => $student->id,
            'title' => 'Roadmap Ready',
            'message' => "Your personalized {$level} roadmap is ready. Start with week one today.",
        ]);

        Notification::create([
            'user_id' => $student->id,
            'title' => 'Writing Corrected',
            'message' => 'Your latest writing submission has been corrected. Check your feedback.',
        ]);

        Notification::create([
            'user_id' => $student->id,
            'title' => 'Welcome to FluentPath',
            'message' => 'Welcome aboard! Complete your placement test to unlock your learning path.',
            'is_read' => true,
        ]);
    }

    protected function strengthsFor(string $level): array
    {
        return match ($level) {
            'A1' => ['understands basic greetings and introductions', 'recognises common everyday nouns'],
            'A2' => ['uses past simple with regular verbs', 'handles everyday shopping vocabulary'],
            'B1' => ['uses present perfect with for and since', 'connects ideas with basic linking words'],
            'B2' => ['handles modals of deduction confidently', 'writes in an appropriate formal register'],
            'C1' => ['uses inversion and fronting naturally', 'controls academic stance markers'],
            default => [],
        };
    }

    protected function weaknessesFor(string $level): array
    {
        return match ($level) {
            'A1' => ['present simple verb endings', 'word order in simple sentences'],
            'A2' => ['irregular verb forms', 'comparatives and superlatives'],
            'B1' => ['first conditional forms', 'passive voice constructions'],
            'B2' => ['reported speech patterns', 'precise collocations'],
            'C1' => ['idiomatic expressions', 'pragmatic nuance in hedging'],
            default => [],
        };
    }

    protected function reasoningFor(string $level): string
    {
        return match ($level) {
            'A1' => 'The learner answered most A1 questions correctly but struggled with verb endings and word order, placing them at the A1 level.',
            'A2' => 'The learner handled everyday topics well and used the past simple with regular verbs, but made consistent errors with irregular forms, placing them at the A2 level.',
            'B1' => 'The learner produced clear connected sentences and managed present perfect correctly, yet conditional and passive structures remain unsteady, placing them at the B1 level.',
            'B2' => 'The learner wrote fluently with an appropriate formal register, but reported speech and precise collocations still need work, placing them at the B2 level.',
            'C1' => 'The learner demonstrated near-native control of advanced structures and academic style, with only idiomatic nuance remaining, placing them at the C1 level.',
            default => 'The learner profile matches the assessed level.',
        };
    }

    protected function feedbackFor(string $skill): string
    {
        return match ($skill) {
            'grammar' => 'Your grammar choices were consistent with your level.',
            'vocabulary' => 'Your vocabulary use matched the task requirements.',
            'reading' => 'You understood the main ideas of the texts.',
            default => 'Your writing was clear and appropriate for the task.',
        };
    }
}

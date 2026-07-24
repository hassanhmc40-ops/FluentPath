<?php

namespace Database\Seeders;

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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@fluentpath.com',
        ]);

        $students = User::factory(5)->create();

        $placementQuestions = PlacementQuestion::factory(12)
            ->sequence(
                ['skill' => 'grammar', 'level' => 'A1'],
                ['skill' => 'grammar', 'level' => 'A2'],
                ['skill' => 'grammar', 'level' => 'B1'],
                ['skill' => 'grammar', 'level' => 'B2'],
                ['skill' => 'vocabulary', 'level' => 'A1'],
                ['skill' => 'vocabulary', 'level' => 'A2'],
                ['skill' => 'vocabulary', 'level' => 'B1'],
                ['skill' => 'vocabulary', 'level' => 'B2'],
                ['skill' => 'writing', 'level' => 'A1'],
                ['skill' => 'writing', 'level' => 'A2'],
                ['skill' => 'writing', 'level' => 'B1'],
                ['skill' => 'writing', 'level' => 'B2'],
            )
            ->create();

        $lessons = Lesson::factory(15)
            ->sequence(
                ['skill' => 'grammar', 'level' => 'A1'],
                ['skill' => 'grammar', 'level' => 'A2'],
                ['skill' => 'grammar', 'level' => 'B1'],
                ['skill' => 'grammar', 'level' => 'B2'],
                ['skill' => 'grammar', 'level' => 'B2'],
                ['skill' => 'vocabulary', 'level' => 'A1'],
                ['skill' => 'vocabulary', 'level' => 'A2'],
                ['skill' => 'vocabulary', 'level' => 'B1'],
                ['skill' => 'vocabulary', 'level' => 'B2'],
                ['skill' => 'vocabulary', 'level' => 'B2'],
                ['skill' => 'writing', 'level' => 'A1'],
                ['skill' => 'writing', 'level' => 'A2'],
                ['skill' => 'writing', 'level' => 'B1'],
                ['skill' => 'writing', 'level' => 'B2'],
                ['skill' => 'writing', 'level' => 'B2'],
            )
            ->create();

        $quizzes = Quiz::factory(10)
            ->recycle($lessons)
            ->create();

        QuizQuestion::factory(40)
            ->recycle($quizzes)
            ->create();

        foreach ($students as $student) {
            $placementTest = PlacementTest::factory()
                ->analyzed()
                ->create(['user_id' => $student->id]);

            PlacementAnswer::factory(count: $placementQuestions->count())
                ->recycle($placementTest)
                ->recycle($placementQuestions)
                ->graded()
                ->create();

            $roadmap = Roadmap::factory()
                ->create([
                    'user_id' => $student->id,
                    'placement_test_id' => $placementTest->id,
                ]);

            $usedLessonIds = [];
            foreach (range(1, 4) as $weekNumber) {
                $week = RoadmapWeek::factory()
                    ->create([
                        'roadmap_id' => $roadmap->id,
                        'week_number' => $weekNumber,
                    ]);

                $weeklyLessons = $lessons->reject(fn ($l) => in_array($l->id, $usedLessonIds))->random(3);
                foreach ($weeklyLessons as $order => $lesson) {
                    $usedLessonIds[] = $lesson->id;
                    RoadmapWeekLesson::factory()
                        ->create([
                            'roadmap_week_id' => $week->id,
                            'lesson_id' => $lesson->id,
                            'display_order' => $order + 1,
                        ]);

                    LessonProgress::factory()
                        ->completed()
                        ->create([
                            'user_id' => $student->id,
                            'lesson_id' => $lesson->id,
                        ]);
                }
            }

            QuizAttempt::factory(3)
                ->recycle($student)
                ->recycle($quizzes)
                ->create();

            WritingSubmission::factory()
                ->corrected()
                ->create(['user_id' => $student->id]);

            Notification::factory(3)
                ->create(['user_id' => $student->id]);

            Notification::factory()
                ->read()
                ->create(['user_id' => $student->id]);
        }
    }
}

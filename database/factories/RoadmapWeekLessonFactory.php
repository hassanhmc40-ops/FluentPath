<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\RoadmapWeek;
use App\Models\RoadmapWeekLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoadmapWeekLesson>
 */
class RoadmapWeekLessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'roadmap_week_id' => RoadmapWeek::factory(),
            'lesson_id' => Lesson::factory(),
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }
}

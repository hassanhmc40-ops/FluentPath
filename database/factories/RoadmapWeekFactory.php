<?php

namespace Database\Factories;

use App\Models\Roadmap;
use App\Models\RoadmapWeek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoadmapWeek>
 */
class RoadmapWeekFactory extends Factory
{
    public function definition(): array
    {
        return [
            'roadmap_id' => Roadmap::factory(),
            'week_number' => fake()->numberBetween(1, 4),
            'objective' => fake()->randomElement([
                'Master basic tenses and common vocabulary',
                'Build confidence with Past Simple and descriptive writing',
                'Learn Present Perfect and expand your vocabulary',
                'Prepare for real-world conversations',
            ]),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\PlacementTest;
use App\Models\Roadmap;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Roadmap>
 */
class RoadmapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'placement_test_id' => PlacementTest::factory()->analyzed(),
            'title' => fake()->randomElement([
                'Your 4-Week English Boost',
                'Personalized Learning Path',
                'From A2 to B1 in 4 Weeks',
                'English Foundations Roadmap',
            ]),
            'generated_at' => now(),
        ];
    }
}

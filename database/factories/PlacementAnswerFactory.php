<?php

namespace Database\Factories;

use App\Models\PlacementAnswer;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlacementAnswer>
 */
class PlacementAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'placement_test_id' => PlacementTest::factory(),
            'placement_question_id' => PlacementQuestion::factory(),
            'answer' => fake()->sentence(),
            'score' => null,
            'feedback' => null,
        ];
    }

    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->randomFloat(2, 0, 100),
            'feedback' => fake()->sentence(),
        ]);
    }
}

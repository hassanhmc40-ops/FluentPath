<?php

namespace Database\Factories;

use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use App\Models\PlacementTest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlacementTest>
 */
class PlacementTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'submitted_at' => now(),
            'status' => PlacementTestStatus::Pending,
            'cefr_level' => null,
            'grammar_score' => null,
            'vocabulary_score' => null,
            'writing_score' => null,
            'strengths' => null,
            'weaknesses' => null,
            'reasoning' => null,
        ];
    }

    public function analyzed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlacementTestStatus::Analyzed,
            'cefr_level' => fake()->randomElement(CefrLevel::cases()),
            'grammar_score' => fake()->randomFloat(2, 40, 100),
            'vocabulary_score' => fake()->randomFloat(2, 40, 100),
            'reading_score' => fake()->randomFloat(2, 40, 100),
            'writing_score' => fake()->randomFloat(2, 40, 100),
            'strengths' => [fake()->sentence(3), fake()->sentence(3)],
            'weaknesses' => [fake()->sentence(3), fake()->sentence(3)],
            'reasoning' => fake()->paragraph(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlacementTestStatus::Failed,
        ]);
    }
}

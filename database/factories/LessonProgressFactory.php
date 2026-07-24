<?php

namespace Database\Factories;

use App\Enums\LessonProgressStatus;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonProgress>
 */
class LessonProgressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'status' => LessonProgressStatus::NotStarted,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LessonProgressStatus::Completed,
            'completed_at' => now()->subHours(fake()->numberBetween(1, 72)),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LessonProgressStatus::InProgress,
        ]);
    }
}

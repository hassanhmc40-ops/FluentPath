<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'title' => fake()->randomElement([
                'Quick Check: Present Simple',
                'Vocabulary Quiz: Daily Routines',
                'Grammar Review: Past Tenses',
                'Writing Skills Assessment',
                'Mixed Practice Quiz',
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}

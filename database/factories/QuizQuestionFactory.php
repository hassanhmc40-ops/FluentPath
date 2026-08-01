<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    public function definition(): array
    {
        $options = [
            fake()->word(),
            fake()->word(),
            fake()->word(),
            fake()->word(),
        ];

        return [
            'quiz_id' => Quiz::factory(),
            'question' => fake()->sentence(8).'?',
            'option_a' => $options[0],
            'option_b' => $options[1],
            'option_c' => $options[2],
            'option_d' => $options[3],
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
        ];
    }
}

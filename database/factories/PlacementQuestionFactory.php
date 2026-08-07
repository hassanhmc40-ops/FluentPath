<?php

namespace Database\Factories;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use App\Models\PlacementQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlacementQuestion>
 */
class PlacementQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(10).'?',
            'skill' => fake()->randomElement(Skill::cases()),
            'level' => fake()->randomElement(CefrLevel::cases()),
            'option_a' => fake()->word(),
            'option_b' => fake()->word(),
            'option_c' => fake()->word(),
            'option_d' => fake()->word(),
            'correct_answer' => 'a',
        ];
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'option_a' => fake()->word(),
            'option_b' => fake()->word(),
            'option_c' => fake()->word(),
            'option_d' => fake()->word(),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
        ]);
    }

    public function grammar(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Grammar,
            'question' => fake()->randomElement([
                'Choose the correct sentence:',
                'Which sentence is grammatically correct?',
                'Fill in the blank: She ___ to school every day.',
                'What is the past tense of "go"?',
            ]),
        ]);
    }

    public function vocabulary(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Vocabulary,
            'question' => fake()->randomElement([
                'What does "benevolent" mean?',
                'Choose the synonym of "happy":',
                'The opposite of "ancient" is:',
                'Which word best completes the sentence?',
            ]),
        ]);
    }

    public function reading(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Reading,
            'question' => fake()->randomElement([
                'Read the short passage and choose the main idea.',
                'Read the passage. What does the writer imply?',
                'Read the text. Which statement is true?',
                'Read the passage. What is the writer\u2019s purpose?',
            ]),
        ]);
    }

    public function writing(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Writing,
            'option_a' => null,
            'option_b' => null,
            'option_c' => null,
            'option_d' => null,
            'correct_answer' => null,
            'question' => fake()->randomElement([
                'Write a short paragraph describing your daily routine.',
                'Describe your favorite place in 5-7 sentences.',
                'What are your goals for learning English?',
                'Write about a memorable experience you had recently.',
            ]),
        ]);
    }
}

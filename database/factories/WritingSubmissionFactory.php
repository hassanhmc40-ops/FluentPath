<?php

namespace Database\Factories;

use App\Enums\WritingSubmissionStatus;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WritingSubmission>
 */
class WritingSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'prompt' => fake()->randomElement([
                'Describe your daily routine.',
                'Write about your favorite hobby.',
                'What did you do last weekend?',
                'Describe a person you admire.',
            ]),
            'original_text' => fake()->paragraph(3),
            'corrected_text' => null,
            'grammar_feedback' => null,
            'vocabulary_feedback' => null,
            'fluency_feedback' => null,
            'mistakes' => null,
            'recommendations' => null,
            'next_topics' => null,
            'score' => null,
            'status' => WritingSubmissionStatus::Pending,
            'submitted_at' => now(),
        ];
    }

    public function corrected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WritingSubmissionStatus::Corrected,
            'corrected_text' => fake()->paragraph(3),
            'grammar_feedback' => 'Good use of tenses, but watch subject-verb agreement.',
            'vocabulary_feedback' => 'Nice range of words; try using more descriptive adjectives.',
            'fluency_feedback' => 'Sentences flow well; consider varying sentence length.',
            'mistakes' => [
                [
                    'original' => 'go',
                    'correction' => 'went',
                    'rule' => 'Past Simple required for past actions',
                ],
                [
                    'original' => 'she don\'t',
                    'correction' => 'she doesn\'t',
                    'rule' => 'Third-person singular needs "doesn\'t"',
                ],
            ],
            'recommendations' => [
                'Review Past Simple tense',
                'Practice subject-verb agreement',
            ],
            'next_topics' => [
                'Past Simple',
                'Subject-Verb Agreement',
            ],
            'score' => fake()->randomFloat(2, 50, 95),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WritingSubmissionStatus::Failed,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Introduction to Present Simple',
                'Past Simple vs Present Perfect',
                'Building Your Vocabulary: Daily Life',
                'Writing Basic Sentences',
                'Understanding Articles: A, An, The',
            ]),
            'skill' => fake()->randomElement(Skill::cases()),
            'level' => fake()->randomElement(CefrLevel::cases()),
        ];
    }

    public function grammar(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Grammar,
            'title' => fake()->randomElement([
                'Present Simple and Continuous',
                'Past Simple Tense',
                'Present Perfect Fundamentals',
                'Conditionals: Zero and First',
                'Modal Verbs: Can, Must, Should',
            ]),
        ]);
    }

    public function vocabulary(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Vocabulary,
            'title' => fake()->randomElement([
                'Everyday Vocabulary: At Home',
                'Workplace English',
                'Travel and Directions',
                'Synonyms and Antonyms',
                'Phrasal Verbs for Beginners',
            ]),
        ]);
    }

    public function writing(): static
    {
        return $this->state(fn (array $attributes) => [
            'skill' => Skill::Writing,
            'title' => fake()->randomElement([
                'Writing Clear Sentences',
                'Paragraph Structure',
                'Describing People and Places',
                'Writing Formal Emails',
                'Expressing Opinions',
            ]),
        ]);
    }
}

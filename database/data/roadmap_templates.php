<?php

/**
 * ROADMAP TEMPLATES — one per CEFR level (A1..C1).
 *
 * Used by DatabaseSeeder to give every demo student a generated roadmap that
 * is coherent with their level. Keys in `lesson_keys` MUST match lesson keys
 * produced by the content packs (database/data/grammar_reading.php and
 * database/data/vocabulary_writing.php): "{skill}-{level-lowercase}-{n}".
 *
 * Week 4 of every template is a review week that reuses earlier lessons.
 *
 * @return array<string, array{title: string, weeks: list<array{week_number: int, objective: string, lesson_keys: list<string>}>}>
 */
return [

    'A1' => [
        'title' => 'English Foundations: First Steps (A1)',
        'weeks' => [
            [
                'week_number' => 1,
                'objective' => 'Introduce yourself: greetings, the verb to be, everyday nouns, and your first short sentences.',
                'lesson_keys' => ['grammar-a1-1', 'vocabulary-a1-1', 'reading-a1-1', 'writing-a1-1'],
            ],
            [
                'week_number' => 2,
                'objective' => 'Talk about daily routines: present simple, common verbs, and simple schedules.',
                'lesson_keys' => ['grammar-a1-2', 'vocabulary-a1-2', 'reading-a1-2', 'writing-a1-2'],
            ],
            [
                'week_number' => 3,
                'objective' => 'Describe people and places: have got, adjectives, prepositions, and shopping vocabulary.',
                'lesson_keys' => ['grammar-a1-3', 'vocabulary-a1-3', 'reading-a1-3', 'writing-a1-3'],
            ],
            [
                'week_number' => 4,
                'objective' => 'Review week: combine everything in short conversations and simple written texts.',
                'lesson_keys' => ['grammar-a1-1', 'vocabulary-a1-2', 'reading-a1-3', 'writing-a1-1'],
            ],
        ],
    ],

    'A2' => [
        'title' => 'Building Confidence: Elementary English (A2)',
        'weeks' => [
            [
                'week_number' => 1,
                'objective' => 'Talk about the past: past simple, irregular verbs, and telling simple stories.',
                'lesson_keys' => ['grammar-a2-1', 'vocabulary-a2-1', 'reading-a2-1', 'writing-a2-1'],
            ],
            [
                'week_number' => 2,
                'objective' => 'Make plans and comparisons: going to, comparatives and superlatives, and travel language.',
                'lesson_keys' => ['grammar-a2-2', 'vocabulary-a2-2', 'reading-a2-2', 'writing-a2-2'],
            ],
            [
                'week_number' => 3,
                'objective' => 'Handle everyday situations: present continuous for plans, directions, and polite requests.',
                'lesson_keys' => ['grammar-a2-3', 'vocabulary-a2-3', 'reading-a2-3', 'writing-a2-3'],
            ],
            [
                'week_number' => 4,
                'objective' => 'Review week: role-play real conversations and write a short story about your week.',
                'lesson_keys' => ['grammar-a2-1', 'vocabulary-a2-2', 'reading-a2-3', 'writing-a2-1'],
            ],
        ],
    ],

    'B1' => [
        'title' => 'Moving Up: Intermediate Foundations (B1)',
        'weeks' => [
            [
                'week_number' => 1,
                'objective' => 'Talk about experiences: present perfect with for/since, and life experiences.',
                'lesson_keys' => ['grammar-b1-1', 'vocabulary-b1-1', 'reading-b1-1', 'writing-b1-1'],
            ],
            [
                'week_number' => 2,
                'objective' => 'Explain and decide: first conditional, modal verbs, and giving opinions.',
                'lesson_keys' => ['grammar-b1-2', 'vocabulary-b1-2', 'reading-b1-2', 'writing-b1-2'],
            ],
            [
                'week_number' => 3,
                'objective' => 'Connect ideas: relative clauses, linking words, and reading longer texts.',
                'lesson_keys' => ['grammar-b1-3', 'vocabulary-b1-3', 'reading-b1-3', 'writing-b1-3'],
            ],
            [
                'week_number' => 4,
                'objective' => 'Review week: write an argument paragraph and solve everyday problem dialogues.',
                'lesson_keys' => ['grammar-b1-1', 'vocabulary-b1-2', 'reading-b1-3', 'writing-b1-1'],
            ],
        ],
    ],

    'B2' => [
        'title' => 'Fluency Builder: Upper-Intermediate (B2)',
        'weeks' => [
            [
                'week_number' => 1,
                'objective' => 'Express nuance: second and third conditionals and modals of deduction.',
                'lesson_keys' => ['grammar-b2-1', 'vocabulary-b2-1', 'reading-b2-1', 'writing-b2-1'],
            ],
            [
                'week_number' => 2,
                'objective' => 'Persuade with register: passive voice, formal versus informal style, and hedging.',
                'lesson_keys' => ['grammar-b2-2', 'vocabulary-b2-2', 'reading-b2-2', 'writing-b2-2'],
            ],
            [
                'week_number' => 3,
                'objective' => 'Handle complex ideas: reported speech, cleft sentences, and advanced collocations.',
                'lesson_keys' => ['grammar-b2-3', 'vocabulary-b2-3', 'reading-b2-3', 'writing-b2-3'],
            ],
            [
                'week_number' => 4,
                'objective' => 'Review week: argue a topic in writing and respond to counterarguments.',
                'lesson_keys' => ['grammar-b2-1', 'vocabulary-b2-2', 'reading-b2-3', 'writing-b2-1'],
            ],
        ],
    ],

    'C1' => [
        'title' => 'Mastery Track: Advanced English (C1)',
        'weeks' => [
            [
                'week_number' => 1,
                'objective' => 'Write with precision: inversion, fronting, and advanced word order.',
                'lesson_keys' => ['grammar-c1-1', 'vocabulary-c1-1', 'reading-c1-1', 'writing-c1-1'],
            ],
            [
                'week_number' => 2,
                'objective' => 'Master academic register: nominalisation, concession, and stance markers.',
                'lesson_keys' => ['grammar-c1-2', 'vocabulary-c1-2', 'reading-c1-2', 'writing-c1-2'],
            ],
            [
                'week_number' => 3,
                'objective' => 'Achieve sophisticated fluency: idioms, precise collocation, and rhetorical devices.',
                'lesson_keys' => ['grammar-c1-3', 'vocabulary-c1-3', 'reading-c1-3', 'writing-c1-3'],
            ],
            [
                'week_number' => 4,
                'objective' => 'Review week: produce and self-edit a polished academic-style essay.',
                'lesson_keys' => ['grammar-c1-1', 'vocabulary-c1-2', 'reading-c1-3', 'writing-c1-1'],
            ],
        ],
    ],

];

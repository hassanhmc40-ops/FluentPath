<?php

/**
 * SAMPLE PLACEMENT TEST WRITING ANSWERS — one free-text answer per CEFR
 * level. Used by DatabaseSeeder to give each demo student (one per level)
 * a realistic, level-appropriate writing submission for every writing
 * question of that level.
 *
 * The answer quality intentionally mirrors the learner's level (A1 writes
 * simple, slightly imperfect English; C1 writes fluent, precise English with
 * near-native collocation).
 *
 * The grammar, vocabulary and reading parts are multiple choice; DatabaseSeeder
 * generates plausible option-letter answers for them on the fly.
 *
 * @return array<string, array{writing: string}>
 */
return [

    'A1' => [
        'writing' => 'My name is Sara. I am 20 years old. I live in Rabat. I like my family and my friends. Every day I go to school.',
    ],

    'A2' => [
        'writing' => 'Last weekend I went to the seaside with my friends. We swam in the sea and ate fish in a small restaurant. It was a nice day, but the weather was a bit cold.',
    ],

    'B1' => [
        'writing' => 'In my opinion, learning a second language is very important. It helps you find a better job, travel more easily, and understand other cultures. However, it takes time and practice. I think schools should start teaching languages earlier.',
    ],

    'B2' => [
        'writing' => 'There is a widespread belief that social media harms young people\'s mental health. While excessive use can indeed increase anxiety, the evidence is not conclusive. It may be more useful to focus on how platforms are used rather than blaming the technology itself. Schools and families should promote healthy digital habits.',
    ],

    'C1' => [
        'writing' => 'It is frequently asserted that higher education has become merely a credentialing mechanism rather than a site of genuine intellectual growth. This claim, though compelling, overlooks the heterogeneous nature of the sector. Elite research universities, vocational institutions, and online providers pursue quite different aims, and any blanket judgement risks oversimplification. On balance, the evidence suggests that the value of a degree depends less on the certificate itself than on the dispositions — curiosity, resilience, and critical thinking — that quality programmes cultivate.',
    ],

];

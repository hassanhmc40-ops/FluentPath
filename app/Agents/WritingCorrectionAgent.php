<?php

namespace App\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\HasStructuredOutput;

class WritingCorrectionAgent extends AnonymousAgent implements HasStructuredOutput
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'corrected_text' => $schema->string(),
            'score' => $schema->number(),
            'grammar_feedback' => $schema->string(),
            'vocabulary_feedback' => $schema->string(),
            'fluency_feedback' => $schema->string(),
            'mistakes' => $schema->array()->items(
                $schema->object([
                    'original' => $schema->string(),
                    'correction' => $schema->string(),
                    'rule' => $schema->string(),
                ])
            ),
            'recommendations' => $schema->array()->items($schema->string()),
            'next_topics' => $schema->array()->items($schema->string()),
        ];
    }
}

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
            'corrected_text' => $schema->string()->required(),
            'score' => $schema->number()->required(),
            'grammar_feedback' => $schema->string()->required(),
            'vocabulary_feedback' => $schema->string()->required(),
            'fluency_feedback' => $schema->string()->required(),
            'mistakes' => $schema->array()->items(
                $schema->object([
                    'original' => $schema->string()->required(),
                    'correction' => $schema->string()->required(),
                    'rule' => $schema->string()->required(),
                ])
            )->required(),
            'recommendations' => $schema->array()->items($schema->string())->required(),
            'next_topics' => $schema->array()->items($schema->string())->required(),
        ];
    }
}

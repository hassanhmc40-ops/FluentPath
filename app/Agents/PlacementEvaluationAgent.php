<?php

namespace App\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\HasStructuredOutput;

class PlacementEvaluationAgent extends AnonymousAgent implements HasStructuredOutput
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'cefr_level' => $schema->string()->enum(['A1', 'A2', 'B1', 'B2', 'C1']),
            'grammar_score' => $schema->number(),
            'vocabulary_score' => $schema->number(),
            'writing_score' => $schema->number(),
            'strengths' => $schema->array()->items($schema->string()),
            'weaknesses' => $schema->array()->items($schema->string()),
            'reasoning' => $schema->string(),
        ];
    }
}
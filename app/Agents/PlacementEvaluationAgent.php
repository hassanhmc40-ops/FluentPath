<?php

namespace App\Agents;

use App\Enums\CefrLevel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\HasStructuredOutput;

class PlacementEvaluationAgent extends AnonymousAgent implements HasStructuredOutput
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'cefr_level' => $schema->string()->enum(array_column(CefrLevel::cases(), 'value'))->required(),
            'writing_score' => $schema->number()->required(),
            'strengths' => $schema->array()->items($schema->string())->required(),
            'weaknesses' => $schema->array()->items($schema->string())->required(),
            'reasoning' => $schema->string()->required(),
        ];
    }
}

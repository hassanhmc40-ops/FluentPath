<?php

namespace App\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\HasStructuredOutput;

class RoadmapGenerationAgent extends AnonymousAgent implements HasStructuredOutput
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string(),
            'weeks' => $schema->array()->items(
                $schema->object([
                    'week_number' => $schema->integer(),
                    'objective' => $schema->string(),
                    'lesson_ids' => $schema->array()->items($schema->integer()),
                ])
            ),
        ];
    }
}
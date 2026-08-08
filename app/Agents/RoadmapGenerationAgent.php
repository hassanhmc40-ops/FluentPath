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
            'title' => $schema->string()->required(),
            'weeks' => $schema->array()->items(
                $schema->object([
                    'week_number' => $schema->integer()->required(),
                    'objective' => $schema->string()->required(),
                    'lesson_ids' => $schema->array()->items($schema->integer())->required(),
                ])
            )->required(),
        ];
    }
}

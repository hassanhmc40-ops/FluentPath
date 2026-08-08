<?php

use App\Agents\PlacementEvaluationAgent;
use App\Agents\RoadmapGenerationAgent;
use App\Agents\WritingCorrectionAgent;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\ObjectSchema;

/**
 * Regression guard: Groq's strict json_schema response_format rejects any
 * schema without a `required` array at every object level (400
 * "invalid JSON schema for response_format"). Laravel's serializer only
 * emits `required` when properties are marked ->required(), so every agent
 * schema must mark its properties (including nested objects).
 */
it('marks every placement evaluation property as required', function () {
    $schema = (new ObjectSchema((new PlacementEvaluationAgent(instructions: 'x', messages: [], tools: []))->schema(new JsonSchemaTypeFactory)))->toSchema();

    expect($schema['required'])->toBe(['cefr_level', 'writing_score', 'strengths', 'weaknesses', 'reasoning'])
        ->and($schema['additionalProperties'])->toBeFalse()
        ->and($schema['properties']['strengths']['items']['type'])->toBe('string');
});

it('marks every writing correction property as required, including nested mistakes', function () {
    $schema = (new ObjectSchema((new WritingCorrectionAgent(instructions: 'x', messages: [], tools: []))->schema(new JsonSchemaTypeFactory)))->toSchema();

    expect($schema['required'])->toBe([
        'corrected_text', 'score', 'grammar_feedback', 'vocabulary_feedback',
        'fluency_feedback', 'mistakes', 'recommendations', 'next_topics',
    ])
        ->and($schema['properties']['mistakes']['items']['required'])->toBe(['original', 'correction', 'rule'])
        ->and($schema['properties']['mistakes']['items']['additionalProperties'])->toBeFalse();
});

it('marks every roadmap generation property as required, including nested weeks', function () {
    $schema = (new ObjectSchema((new RoadmapGenerationAgent(instructions: 'x', messages: [], tools: []))->schema(new JsonSchemaTypeFactory)))->toSchema();

    expect($schema['required'])->toBe(['title', 'weeks'])
        ->and($schema['properties']['weeks']['items']['required'])->toBe(['week_number', 'objective', 'lesson_ids'])
        ->and($schema['properties']['weeks']['items']['properties']['lesson_ids']['items']['type'])->toBe('integer');
});

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlacementQuestionRequest;
use App\Http\Requests\Admin\UpdatePlacementQuestionRequest;
use App\Http\Resources\PlacementQuestionResource;
use App\Models\PlacementQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Admin · Placement Questions
 *
 * Admin-only management of the placement-test question bank (60 questions:
 * 19 grammar, 19 vocabulary, 19 reading and 3 writing prompts).
 * Requires an admin bearer token; students receive `403`.
 */
class PlacementQuestionController extends Controller
{
    /**
     * List all placement questions.
     *
     * @apiResource App\Http\Resources\PlacementQuestionResource
     *
     * @apiResourceModel App\Models\PlacementQuestion
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PlacementQuestion::class);

        return PlacementQuestionResource::collection(PlacementQuestion::all());
    }

    /**
     * Create a placement question.
     *
     * @bodyParam question string required The question text. Example: Choose the word that means "beautiful".
     * @bodyParam skill string required One of: grammar, vocabulary, reading, writing. Example: vocabulary
     * @bodyParam level string required CEFR level: A1–C1. Example: A2
     *
     * @response status=201 {
     *   "id": 1,
     *   "question": "Choose the word that means \"beautiful\".",
     *   "skill": "vocabulary",
     *   "level": "A2"
     * }
     */
    public function store(StorePlacementQuestionRequest $request): JsonResponse|PlacementQuestionResource
    {
        $this->authorize('create', PlacementQuestion::class);

        $question = PlacementQuestion::create($request->validated());

        return (new PlacementQuestionResource($question))->response()->setStatusCode(201);
    }

    /**
     * Show a single placement question.
     *
     * @apiResource App\Http\Resources\PlacementQuestionResource
     *
     * @apiResourceModel App\Models\PlacementQuestion
     */
    public function show(PlacementQuestion $placementQuestion): PlacementQuestionResource
    {
        $this->authorize('view', $placementQuestion);

        return new PlacementQuestionResource($placementQuestion);
    }

    /**
     * Update a placement question.
     *
     * @bodyParam question string The question text.
     * @bodyParam skill string One of: grammar, vocabulary, reading, writing.
     * @bodyParam level string CEFR level: A1–C1.
     *
     * @apiResource App\Http\Resources\PlacementQuestionResource
     *
     * @apiResourceModel App\Models\PlacementQuestion
     */
    public function update(UpdatePlacementQuestionRequest $request, PlacementQuestion $placementQuestion): PlacementQuestionResource
    {
        $this->authorize('update', $placementQuestion);

        $placementQuestion->update($request->validated());

        return new PlacementQuestionResource($placementQuestion);
    }

    /**
     * Delete a placement question.
     *
     * @response status=204
     */
    public function destroy(PlacementQuestion $placementQuestion): JsonResponse
    {
        $this->authorize('delete', $placementQuestion);

        $placementQuestion->delete();

        return response()->json(null, 204);
    }
}

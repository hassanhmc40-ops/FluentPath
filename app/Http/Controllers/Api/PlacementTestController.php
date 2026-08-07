<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlacementTestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPlacementTestRequest;
use App\Http\Resources\PlacementTestResource;
use App\Jobs\EvaluatePlacementTest;
use App\Models\PlacementTest;
use Illuminate\Http\JsonResponse;

class PlacementTestController extends Controller
{
    /**
     * Submit the placement test.
     *
     * Stores the answers and dispatches the AI evaluation job. Responds
     * immediately with `202` — poll `GET /api/placement-tests/{test}` until
     * the status becomes analyzed.
     *
     * @group Placement Test
     *
     * @bodyParam answers array required List of answered placement questions. Example: [{"placement_question_id":1,"answer":"b"},{"placement_question_id":2,"answer":"x"}]
     * @bodyParam answers[].placement_question_id integer required The placement question id.
     * @bodyParam answers[].answer string required The learner's answer (free text).
     *
     * @response status=202 {
     *   "id": 1,
     *   "status": "pending"
     * }
     * @response status=422 {
     *   "message": "Answer at least one question.",
     *   "errors": {"answers": ["Answer at least one question."]}
     * }
     */
    public function store(SubmitPlacementTestRequest $request): JsonResponse
    {
        $placementTest = PlacementTest::create([
            'user_id' => $request->user()->id,
        ]);

        $answers = collect($request->answers)->map(fn ($a) => [
            'placement_question_id' => $a['placement_question_id'],
            'answer' => $a['answer'],
        ]);

        $placementTest->placementAnswers()->createMany($answers->toArray());

        EvaluatePlacementTest::dispatch($placementTest);

        return response()->json([
            'id' => $placementTest->id,
            'status' => PlacementTestStatus::Pending->value,
        ], 202);
    }

    /**
     * Retrieve a placement test result.
     *
     * Includes the AI evaluation (CEFR level, per-skill scores, strengths,
     * weaknesses and reasoning) once the asynchronous job has finished.
     *
     * @group Placement Test
     *
     * @urlParam placement_test integer required The placement test id. Example: 1
     *
     * @apiResource App\Http\Resources\PlacementTestResource
     *
     * @apiResourceModel App\Models\PlacementTest
     */
    public function show(PlacementTest $placementTest): PlacementTestResource
    {
        $this->authorize('view', $placementTest);

        return new PlacementTestResource($placementTest->load('placementAnswers'));
    }
}

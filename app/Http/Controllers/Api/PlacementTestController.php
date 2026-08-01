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

    public function show(PlacementTest $placementTest): PlacementTestResource
    {
        $this->authorize('view', $placementTest);

        return new PlacementTestResource($placementTest->load('placementAnswers'));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoadmapStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoadmapRequest;
use App\Http\Resources\RoadmapResource;
use App\Jobs\GenerateRoadmap;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    /**
     * Generate a personalized 4-week roadmap.
     *
     * Requires a fully analyzed placement test. Dispatches the roadmap
     * generation job and returns `202` immediately.
     *
     * @group Roadmaps
     *
     * @response status=202 {
     *   "id": 1,
     *   "status": "pending"
     * }
     * @response status=422 {
     *   "message": "Placement test must be analyzed before generating a roadmap."
     * }
     */
    public function store(StoreRoadmapRequest $request): JsonResponse
    {
        $latestTest = PlacementTest::latestAnalyzedFor($request->user()->id)->first();

        if (! $latestTest) {
            return response()->json([
                'message' => 'Placement test must be analyzed before generating a roadmap.',
            ], 422);
        }

        $roadmap = Roadmap::create([
            'user_id' => $request->user()->id,
            'placement_test_id' => $latestTest->id,
            'title' => 'Generating...',
        ]);

        GenerateRoadmap::dispatch($roadmap);

        return response()->json([
            'id' => $roadmap->id,
            'status' => RoadmapStatus::Pending->value,
        ], 202);
    }

    /**
     * Retrieve the current roadmap.
     *
     * Returns the latest roadmap for the authenticated student, including
     * its 4 weeks and the lessons assigned to each week. The AI only selects
     * lessons that already exist in the catalog.
     *
     * @group Roadmaps
     *
     * @response status=404 {
     *   "message": "No roadmap found."
     * }
     *
     * @apiResource App\Http\Resources\RoadmapResource
     *
     * @apiResourceModel App\Models\Roadmap
     */
    public function show(Request $request): JsonResponse|RoadmapResource
    {
        $roadmap = Roadmap::latestForUser($request->user()->id)
            ->with('roadmapWeeks.roadmapWeekLessons.lesson')
            ->first();

        if (! $roadmap) {
            return response()->json(['message' => 'No roadmap found.'], 404);
        }

        return new RoadmapResource($roadmap);
    }
}

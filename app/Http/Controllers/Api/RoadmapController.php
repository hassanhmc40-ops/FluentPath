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

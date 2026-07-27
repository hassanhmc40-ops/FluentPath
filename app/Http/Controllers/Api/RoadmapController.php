<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoadmapResource;
use App\Jobs\GenerateRoadmap;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $latestTest = PlacementTest::where('user_id', $request->user()->id)
            ->where('status', 'analyzed')
            ->latest()
            ->first();

        if (! $latestTest) {
            return response()->json([
                'message' => 'Placement test must be analyzed before generating a roadmap.',
            ], 422);
        }

        $roadmap = Roadmap::create([
            'user_id' => $request->user()->id,
            'placement_test_id' => $latestTest->id,
            'title' => 'Generating...',
            'status' => 'pending',
        ]);

        GenerateRoadmap::dispatch($roadmap);

        return response()->json([
            'id' => $roadmap->id,
            'status' => $roadmap->status,
        ], 202);
    }

    public function show(Request $request): JsonResponse|RoadmapResource
    {
        $roadmap = Roadmap::where('user_id', $request->user()->id)
            ->with('roadmapWeeks.roadmapWeekLessons.lesson')
            ->latest('id')
            ->first();

        if (! $roadmap) {
            return response()->json(['message' => 'No roadmap found.'], 404);
        }

        return new RoadmapResource($roadmap);
    }
}
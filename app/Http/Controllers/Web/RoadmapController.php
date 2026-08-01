<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoadmapStatus;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateRoadmap;
use App\Models\LessonProgress;
use App\Models\PlacementTest;
use App\Models\Roadmap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoadmapController extends Controller
{
    public function show(Request $request): View
    {
        $roadmap = Roadmap::latestForUser($request->user()->id)
            ->with('roadmapWeeks.roadmapWeekLessons.lesson')
            ->first();

        $completedLessonIds = LessonProgress::completedFor($request->user()->id)
            ->pluck('lesson_id')
            ->all();

        return view('roadmap', [
            'roadmap' => $roadmap,
            'completedLessonIds' => $completedLessonIds,
            'processing' => $roadmap && in_array($roadmap->status, [
                RoadmapStatus::Pending,
                RoadmapStatus::Processing,
            ], true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $latestTest = PlacementTest::latestAnalyzedFor($request->user()->id)->first();

        if (! $latestTest) {
            return back()->with('error', 'Placement test must be analyzed before generating a roadmap.');
        }

        $roadmap = Roadmap::create([
            'user_id' => $request->user()->id,
            'placement_test_id' => $latestTest->id,
            'title' => 'Generating...',
        ]);

        GenerateRoadmap::dispatch($roadmap);

        return redirect('/roadmap')->with('success', 'Roadmap generation started. This page will refresh automatically.');
    }
}

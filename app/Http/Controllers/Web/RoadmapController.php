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
    /**
     * Shared creation for a pending roadmap. Status is passed explicitly —
     * firstOrCreate never reads column defaults back into the model instance,
     * so without it the status would be null just after creation.
     */
    protected function pendingRoadmapFor(int $userId, int $placementTestId): Roadmap
    {
        return Roadmap::firstOrCreate(
            ['placement_test_id' => $placementTestId],
            [
                'user_id' => $userId,
                'title' => 'Generating...',
                'status' => RoadmapStatus::Pending,
            ],
        );
    }

    public function show(Request $request): View
    {
        $roadmap = Roadmap::latestForUser($request->user()->id)
            ->with('roadmapWeeks.roadmapWeekLessons.lesson')
            ->first();

        // Lazy generation: a student whose placement test has been analyzed
        // (e.g. before this feature existed) gets a roadmap started the moment
        // they open the page. firstOrCreate keeps this idempotent — repeated
        // visits can never create a second roadmap or dispatch a second job.
        if ($roadmap === null) {
            $latestTest = PlacementTest::latestAnalyzedFor($request->user()->id)->first();

            if ($latestTest) {
                $roadmap = $this->pendingRoadmapFor($request->user()->id, $latestTest->id);

                if ($roadmap->wasRecentlyCreated) {
                    GenerateRoadmap::dispatch($roadmap);
                }

                $roadmap->load('roadmapWeeks.roadmapWeekLessons.lesson');
            }
        }

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
            'failed' => $roadmap && $roadmap->status === RoadmapStatus::Failed,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $latestTest = PlacementTest::latestAnalyzedFor($request->user()->id)->first();

        if (! $latestTest) {
            return back()->with('error', 'Placement test must be analyzed before generating a roadmap.');
        }

        $roadmap = $this->pendingRoadmapFor($request->user()->id, $latestTest->id);

        $shouldDispatch = $roadmap->wasRecentlyCreated;

        // "Try again" from the failed card: the row exists, so allow a
        // re-dispatch and move it back to a pending state first.
        if (! $shouldDispatch && $roadmap->status === RoadmapStatus::Failed) {
            $roadmap->update(['status' => RoadmapStatus::Pending]);

            $shouldDispatch = true;
        }

        if ($shouldDispatch) {
            GenerateRoadmap::dispatch($roadmap);
        }

        return redirect('/roadmap')->with('success', 'Roadmap generation started. This page will refresh automatically.');
    }
}

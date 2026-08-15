<?php

namespace App\Http\Controllers\Web;

use App\Enums\PlacementTestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPlacementTestRequest;
use App\Jobs\EvaluatePlacementTest;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlacementTestController extends Controller
{
    public function show(Request $request): View
    {
        $test = PlacementTest::where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        $history = PlacementTest::where('user_id', $request->user()->id)
            ->where('status', PlacementTestStatus::Analyzed)
            ->latest('id')
            ->get()
            ->slice(1)
            ->values();

        return view('placement-test', [
            'test' => $test,
            'history' => $history,
            'failed' => $test && $test->status === PlacementTestStatus::Failed,
            'questions' => PlacementQuestion::orderBy('id')->get(),
            'processing' => $test && in_array($test->status, [
                PlacementTestStatus::Pending,
                PlacementTestStatus::Processing,
            ], true),
            // Transient flag set by the retake flow: show the fresh test form
            // even though an analyzed test exists (it stays in the history).
            'retaking' => $request->boolean('retake'),
        ]);
    }

    public function store(SubmitPlacementTestRequest $request): RedirectResponse
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

        return redirect('/placement-test')->with('success', 'Placement test submitted. Evaluation started �?" this page will refresh automatically.');
    }

    public function retake(Request $request): RedirectResponse
    {
        $test = PlacementTest::where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        // A failed, pending or processing attempt is a dead end — remove it so
        // the student can submit a fresh test. Analyzed tests (and their
        // roadmaps) are preserved as historical record.
        if ($test === null || $test->status !== PlacementTestStatus::Analyzed) {
            $test?->delete();

            return redirect('/placement-test');
        }

        // The latest test is analyzed: keep it as history and open the fresh
        // test form via the transient ?retake=1 flag.
        return redirect('/placement-test?retake=1');
    }
}

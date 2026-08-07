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

        return view('placement-test', [
            'test' => $test,
            'questions' => PlacementQuestion::orderBy('id')->get(),
            'processing' => $test && in_array($test->status, [
                PlacementTestStatus::Pending,
                PlacementTestStatus::Processing,
            ], true),
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

        if ($test && $test->status === PlacementTestStatus::Analyzed) {
            $test->delete(); // cascades placement answers + linked roadmaps
        }

        return redirect('/placement-test');
    }
}

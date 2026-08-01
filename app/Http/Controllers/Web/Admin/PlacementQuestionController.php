<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlacementQuestionRequest;
use App\Http\Requests\Admin\UpdatePlacementQuestionRequest;
use App\Models\PlacementQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlacementQuestionController extends Controller
{
    public function index(): View
    {
        return view('admin.placement-questions.index', [
            'placementQuestions' => PlacementQuestion::orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.placement-questions.create', [
            'skills' => Skill::cases(),
            'levels' => CefrLevel::cases(),
        ]);
    }

    public function store(StorePlacementQuestionRequest $request): RedirectResponse
    {
        PlacementQuestion::create($request->validated());

        return redirect()->route('admin.placement-questions.index')->with('success', 'Placement question created.');
    }

    public function edit(PlacementQuestion $placementQuestion): View
    {
        return view('admin.placement-questions.edit', [
            'placementQuestion' => $placementQuestion,
            'skills' => Skill::cases(),
            'levels' => CefrLevel::cases(),
        ]);
    }

    public function update(UpdatePlacementQuestionRequest $request, PlacementQuestion $placementQuestion): RedirectResponse
    {
        $placementQuestion->update($request->validated());

        return redirect()->route('admin.placement-questions.index')->with('success', 'Placement question updated.');
    }

    public function destroy(PlacementQuestion $placementQuestion): RedirectResponse
    {
        $placementQuestion->delete();

        return redirect()->route('admin.placement-questions.index')->with('success', 'Placement question deleted.');
    }
}

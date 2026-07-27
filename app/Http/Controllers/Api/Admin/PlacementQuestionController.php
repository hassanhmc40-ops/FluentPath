<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlacementQuestionRequest;
use App\Http\Requests\Admin\UpdatePlacementQuestionRequest;
use App\Http\Resources\PlacementQuestionResource;
use App\Models\PlacementQuestion;

class PlacementQuestionController extends Controller
{
    public function index()
    {
        return PlacementQuestionResource::collection(PlacementQuestion::all());
    }

    public function store(StorePlacementQuestionRequest $request)
    {
        $question = PlacementQuestion::create($request->validated());

        return new PlacementQuestionResource($question);
    }

    public function show(PlacementQuestion $placementQuestion)
    {
        return new PlacementQuestionResource($placementQuestion);
    }

    public function update(UpdatePlacementQuestionRequest $request, PlacementQuestion $placementQuestion)
    {
        $placementQuestion->update($request->validated());

        return new PlacementQuestionResource($placementQuestion);
    }

    public function destroy(PlacementQuestion $placementQuestion)
    {
        $placementQuestion->delete();

        return response()->json(null, 204);
    }
}

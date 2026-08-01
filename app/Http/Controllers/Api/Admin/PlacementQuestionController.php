<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlacementQuestionRequest;
use App\Http\Requests\Admin\UpdatePlacementQuestionRequest;
use App\Http\Resources\PlacementQuestionResource;
use App\Models\PlacementQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlacementQuestionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PlacementQuestion::class);

        return PlacementQuestionResource::collection(PlacementQuestion::all());
    }

    public function store(StorePlacementQuestionRequest $request): JsonResponse|PlacementQuestionResource
    {
        $this->authorize('create', PlacementQuestion::class);

        $question = PlacementQuestion::create($request->validated());

        return (new PlacementQuestionResource($question))->response()->setStatusCode(201);
    }

    public function show(PlacementQuestion $placementQuestion): PlacementQuestionResource
    {
        $this->authorize('view', $placementQuestion);

        return new PlacementQuestionResource($placementQuestion);
    }

    public function update(UpdatePlacementQuestionRequest $request, PlacementQuestion $placementQuestion): PlacementQuestionResource
    {
        $this->authorize('update', $placementQuestion);

        $placementQuestion->update($request->validated());

        return new PlacementQuestionResource($placementQuestion);
    }

    public function destroy(PlacementQuestion $placementQuestion): JsonResponse
    {
        $this->authorize('delete', $placementQuestion);

        $placementQuestion->delete();

        return response()->json(null, 204);
    }
}

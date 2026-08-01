<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizQuestionRequest;
use App\Http\Requests\Admin\UpdateQuizQuestionRequest;
use App\Http\Resources\QuizQuestionResource;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuizQuestionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', QuizQuestion::class);

        $query = QuizQuestion::query();

        if ($quizId = request('quiz_id')) {
            $query->where('quiz_id', $quizId);
        }

        return QuizQuestionResource::collection($query->get());
    }

    public function store(StoreQuizQuestionRequest $request): JsonResponse|QuizQuestionResource
    {
        $this->authorize('create', QuizQuestion::class);

        $question = QuizQuestion::create($request->validated());

        return (new QuizQuestionResource($question))->response()->setStatusCode(201);
    }

    public function show(QuizQuestion $quizQuestion): QuizQuestionResource
    {
        $this->authorize('view', $quizQuestion);

        return new QuizQuestionResource($quizQuestion);
    }

    public function update(UpdateQuizQuestionRequest $request, QuizQuestion $quizQuestion): QuizQuestionResource
    {
        $this->authorize('update', $quizQuestion);

        $quizQuestion->update($request->validated());

        return new QuizQuestionResource($quizQuestion);
    }

    public function destroy(QuizQuestion $quizQuestion): JsonResponse
    {
        $this->authorize('delete', $quizQuestion);

        $quizQuestion->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuizController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Quiz::class);

        $quizzes = Quiz::with('lesson:id,title')->withCount('quizQuestions')->get();

        return QuizResource::collection($quizzes);
    }

    public function store(StoreQuizRequest $request): JsonResponse|QuizResource
    {
        $this->authorize('create', Quiz::class);

        $quiz = Quiz::create($request->validated());

        return (new QuizResource($quiz))->response()->setStatusCode(201);
    }

    public function show(Quiz $quiz): QuizResource
    {
        $this->authorize('view', $quiz);

        $quiz->loadCount('quizQuestions');

        return new QuizResource($quiz);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): QuizResource
    {
        $this->authorize('update', $quiz);

        $quiz->update($request->validated());
        $quiz->loadCount('quizQuestions');

        return new QuizResource($quiz);
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return response()->json(null, 204);
    }
}

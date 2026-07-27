<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with('lesson:id,title')->withCount('quizQuestions')->get();

        return QuizResource::collection($quizzes);
    }

    public function store(StoreQuizRequest $request)
    {
        $quiz = Quiz::create($request->validated());

        return new QuizResource($quiz);
    }

    public function show(Quiz $quiz)
    {
        $quiz->loadCount('quizQuestions');

        return new QuizResource($quiz);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $quiz->update($request->validated());
        $quiz->loadCount('quizQuestions');

        return new QuizResource($quiz);
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return response()->json(null, 204);
    }
}

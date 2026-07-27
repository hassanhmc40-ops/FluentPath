<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizQuestionRequest;
use App\Http\Requests\Admin\UpdateQuizQuestionRequest;
use App\Http\Resources\QuizQuestionResource;
use App\Models\QuizQuestion;

class QuizQuestionController extends Controller
{
    public function index()
    {
        $query = QuizQuestion::query();

        if ($quizId = request('quiz_id')) {
            $query->where('quiz_id', $quizId);
        }

        return QuizQuestionResource::collection($query->get());
    }

    public function store(StoreQuizQuestionRequest $request)
    {
        $question = QuizQuestion::create($request->validated());

        return new QuizQuestionResource($question);
    }

    public function show(QuizQuestion $quizQuestion)
    {
        return new QuizQuestionResource($quizQuestion);
    }

    public function update(UpdateQuizQuestionRequest $request, QuizQuestion $quizQuestion)
    {
        $quizQuestion->update($request->validated());

        return new QuizQuestionResource($quizQuestion);
    }

    public function destroy(QuizQuestion $quizQuestion)
    {
        $quizQuestion->delete();

        return response()->json(null, 204);
    }
}

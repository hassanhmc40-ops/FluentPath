<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizAttemptRequest;
use App\Http\Resources\StudentQuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show($id): JsonResponse|StudentQuizResource
    {
        $quiz = Quiz::with('quizQuestions')->findOrFail($id);

        return new StudentQuizResource($quiz);
    }

    public function attempt(SubmitQuizAttemptRequest $request, $id): JsonResponse
    {
        $quiz = Quiz::with('quizQuestions')->findOrFail($id);

        $correctCount = 0;
        $total = $quiz->quizQuestions->count();
        $answers = [];

        foreach ($request->answers as $answer) {
            $question = $quiz->quizQuestions->firstWhere('id', $answer['quiz_question_id']);
            $isCorrect = $question && $question->correct_answer === $answer['selected_option'];

            if ($isCorrect) {
                $correctCount++;
            }

            $answers[] = [
                'quiz_question_id' => $answer['quiz_question_id'],
                'selected_option' => $answer['selected_option'],
                'is_correct' => $isCorrect,
            ];
        }

        $score = $total > 0 ? round(($correctCount / $total) * 100, 2) : 0;

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'completed_at' => now(),
        ]);

        return response()->json([
            'id' => $attempt->id,
            'quiz_id' => $attempt->quiz_id,
            'score' => $attempt->score,
            'completed_at' => $attempt->completed_at,
            'answers' => $answers,
        ], 201);
    }
}
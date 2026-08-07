<?php

namespace App\Http\Controllers\Api;

use App\Events\QuizAttempted;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizAttemptRequest;
use App\Http\Resources\StudentQuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    /**
     * Retrieve a quiz with its questions.
     *
     * Questions do not expose the correct answer.
     *
     * @group Quizzes
     *
     * @urlParam quiz integer required The quiz id. Example: 1
     *
     * @apiResource App\Http\Resources\StudentQuizResource
     *
     * @apiResourceModel App\Models\Quiz
     */
    public function show(Quiz $quiz): StudentQuizResource
    {
        $this->authorize('view', $quiz);

        return new StudentQuizResource($quiz->load('quizQuestions'));
    }

    /**
     * Submit a quiz attempt.
     *
     * Scores the selected options against the quiz questions and stores the
     * attempt. Multiple attempts are allowed.
     *
     * @group Quizzes
     *
     * @urlParam quiz integer required The quiz id. Example: 1
     *
     * @bodyParam answers array required List of answers. Example: [{"quiz_question_id":1,"selected_option":"b"}]
     * @bodyParam answers[].quiz_question_id integer required The quiz question id.
     * @bodyParam answers[].selected_option string required The selected option: a, b, c or d.
     *
     * @response status=201 {
     *   "id": 1,
     *   "quiz_id": 1,
     *   "score": 80,
     *   "completed_at": "2026-08-07T10:00:00.000000Z",
     *   "answers": [
     *     {"quiz_question_id": 1, "selected_option": "b", "is_correct": true}
     *   ]
     * }
     */
    public function attempt(SubmitQuizAttemptRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('create', QuizAttempt::class);

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

        QuizAttempted::dispatch($request->user()->id, $quiz->id, $score);

        return response()->json([
            'id' => $attempt->id,
            'quiz_id' => $attempt->quiz_id,
            'score' => $attempt->score,
            'completed_at' => $attempt->completed_at,
            'answers' => $answers,
        ], 201);
    }
}

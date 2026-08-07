<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizQuestionRequest;
use App\Http\Requests\Admin\UpdateQuizQuestionRequest;
use App\Http\Resources\QuizQuestionResource;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Admin · Quiz Questions
 *
 * Admin-only management of the multiple-choice questions inside quizzes.
 * Requires an admin bearer token; students receive `403`.
 */
class QuizQuestionController extends Controller
{
    /**
     * List quiz questions.
     *
     * Optionally filtered with `?quiz_id=`.
     *
     * @queryParam quiz_id integer Filter questions by quiz. Example: 1
     *
     * @apiResource App\Http\Resources\QuizQuestionResource
     *
     * @apiResourceModel App\Models\QuizQuestion
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', QuizQuestion::class);

        $query = QuizQuestion::query();

        if ($quizId = request('quiz_id')) {
            $query->where('quiz_id', $quizId);
        }

        return QuizQuestionResource::collection($query->get());
    }

    /**
     * Create a quiz question.
     *
     * @bodyParam quiz_id integer required The quiz this question belongs to. Example: 1
     * @bodyParam question string required The question text. Example: Which tense is used with "yesterday"?
     * @bodyParam option_a string required Option A.
     * @bodyParam option_b string required Option B.
     * @bodyParam option_c string required Option C.
     * @bodyParam option_d string required Option D.
     * @bodyParam correct_answer string required The correct option letter: a, b, c or d. Example: b
     *
     * @response status=201 {
     *   "id": 1,
     *   "quiz_id": 1,
     *   "question": "Which tense is used with \"yesterday\"?",
     *   "option_a": "Present Simple",
     *   "option_b": "Past Simple",
     *   "option_c": "Present Perfect",
     *   "option_d": "Future",
     *   "correct_answer": "b"
     * }
     */
    public function store(StoreQuizQuestionRequest $request): JsonResponse|QuizQuestionResource
    {
        $this->authorize('create', QuizQuestion::class);

        $question = QuizQuestion::create($request->validated());

        return (new QuizQuestionResource($question))->response()->setStatusCode(201);
    }

    /**
     * Show a single quiz question.
     *
     * @apiResource App\Http\Resources\QuizQuestionResource
     *
     * @apiResourceModel App\Models\QuizQuestion
     */
    public function show(QuizQuestion $quizQuestion): QuizQuestionResource
    {
        $this->authorize('view', $quizQuestion);

        return new QuizQuestionResource($quizQuestion);
    }

    /**
     * Update a quiz question.
     *
     * @bodyParam quiz_id integer The quiz this question belongs to.
     * @bodyParam question string The question text.
     * @bodyParam option_a string Option A.
     * @bodyParam option_b string Option B.
     * @bodyParam option_c string Option C.
     * @bodyParam option_d string Option D.
     * @bodyParam correct_answer string The correct option letter: a, b, c or d.
     *
     * @apiResource App\Http\Resources\QuizQuestionResource
     *
     * @apiResourceModel App\Models\QuizQuestion
     */
    public function update(UpdateQuizQuestionRequest $request, QuizQuestion $quizQuestion): QuizQuestionResource
    {
        $this->authorize('update', $quizQuestion);

        $quizQuestion->update($request->validated());

        return new QuizQuestionResource($quizQuestion);
    }

    /**
     * Delete a quiz question.
     *
     * @response status=204
     */
    public function destroy(QuizQuestion $quizQuestion): JsonResponse
    {
        $this->authorize('delete', $quizQuestion);

        $quizQuestion->delete();

        return response()->json(null, 204);
    }
}

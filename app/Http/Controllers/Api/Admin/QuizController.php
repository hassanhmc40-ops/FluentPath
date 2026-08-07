<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Admin · Quizzes
 *
 * Admin-only management of quizzes (each tied to exactly one lesson).
 * Requires an admin bearer token; students receive `403`.
 */
class QuizController extends Controller
{
    /**
     * List all quizzes with their lesson and question count.
     *
     * @apiResource App\Http\Resources\QuizResource
     *
     * @apiResourceModel App\Models\Quiz
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Quiz::class);

        $quizzes = Quiz::with('lesson:id,title')->withCount('quizQuestions')->get();

        return QuizResource::collection($quizzes);
    }

    /**
     * Create a quiz.
     *
     * @bodyParam lesson_id integer required The id of the lesson this quiz belongs to. Example: 1
     * @bodyParam title string required Quiz title. Example: Grammar Review
     * @bodyParam description string optional Quiz description.
     *
     * @response status=201 {
     *   "id": 1,
     *   "lesson_id": 1,
     *   "title": "Grammar Review",
     *   "description": "Review the present perfect.",
     *   "quiz_questions_count": 0
     * }
     */
    public function store(StoreQuizRequest $request): JsonResponse|QuizResource
    {
        $this->authorize('create', Quiz::class);

        $quiz = Quiz::create($request->validated());

        return (new QuizResource($quiz))->response()->setStatusCode(201);
    }

    /**
     * Show a single quiz.
     *
     * @apiResource App\Http\Resources\QuizResource
     *
     * @apiResourceModel App\Models\Quiz
     */
    public function show(Quiz $quiz): QuizResource
    {
        $this->authorize('view', $quiz);

        $quiz->loadCount('quizQuestions');

        return new QuizResource($quiz);
    }

    /**
     * Update a quiz.
     *
     * @bodyParam lesson_id integer The id of the lesson this quiz belongs to.
     * @bodyParam title string Quiz title.
     * @bodyParam description string Quiz description.
     *
     * @apiResource App\Http\Resources\QuizResource
     *
     * @apiResourceModel App\Models\Quiz
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz): QuizResource
    {
        $this->authorize('update', $quiz);

        $quiz->update($request->validated());
        $quiz->loadCount('quizQuestions');

        return new QuizResource($quiz);
    }

    /**
     * Delete a quiz.
     *
     * @response status=204
     */
    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return response()->json(null, 204);
    }
}

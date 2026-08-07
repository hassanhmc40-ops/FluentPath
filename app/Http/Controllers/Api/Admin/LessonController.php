<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Admin · Lessons
 *
 * Admin-only management of the lesson catalog. Requires an admin bearer
 * token; students receive `403`.
 */
class LessonController extends Controller
{
    /**
     * List all lessons.
     *
     * @apiResource App\Http\Resources\LessonResource
     *
     * @apiResourceModel App\Models\Lesson
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lesson::class);

        return LessonResource::collection(Lesson::all());
    }

    /**
     * Create a lesson.
     *
     * @bodyParam title string required Lesson title. Example: Present Perfect Fundamentals
     * @bodyParam skill string required One of: grammar, vocabulary, reading, writing. Example: grammar
     * @bodyParam level string required CEFR level: A1–C1. Example: B1
     * @bodyParam content string required The lesson body (markdown-ish, `##` sections). Example: ## What you'll learn\nPresent perfect connects the past to the present.
     *
     * @response status=201 {
     *   "id": 1,
     *   "title": "Present Perfect Fundamentals",
     *   "skill": "grammar",
     *   "level": "B1",
     *   "content": "## What you'll learn\nPresent perfect connects the past to the present."
     * }
     */
    public function store(StoreLessonRequest $request): JsonResponse|LessonResource
    {
        $this->authorize('create', Lesson::class);

        $lesson = Lesson::create($request->validated());

        return (new LessonResource($lesson))->response()->setStatusCode(201);
    }

    /**
     * Show a single lesson.
     *
     * @apiResource App\Http\Resources\LessonResource
     *
     * @apiResourceModel App\Models\Lesson
     */
    public function show(Lesson $lesson): LessonResource
    {
        $this->authorize('view', $lesson);

        return new LessonResource($lesson);
    }

    /**
     * Update a lesson.
     *
     * @bodyParam title string Lesson title. Example: Present Perfect and Past Simple
     * @bodyParam skill string One of: grammar, vocabulary, reading, writing.
     * @bodyParam level string CEFR level: A1–C1.
     * @bodyParam content string The lesson body.
     *
     * @apiResource App\Http\Resources\LessonResource
     *
     * @apiResourceModel App\Models\Lesson
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson): LessonResource
    {
        $this->authorize('update', $lesson);

        $lesson->update($request->validated());

        return new LessonResource($lesson);
    }

    /**
     * Delete (soft-delete) a lesson.
     *
     * The lesson is moved to the trash; it disappears from the student
     * catalog and can be restored from the admin UI.
     *
     * @response status=204
     */
    public function destroy(Lesson $lesson): JsonResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return response()->json(null, 204);
    }
}

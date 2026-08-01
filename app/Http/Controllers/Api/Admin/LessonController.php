<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LessonController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lesson::class);

        return LessonResource::collection(Lesson::all());
    }

    public function store(StoreLessonRequest $request): JsonResponse|LessonResource
    {
        $this->authorize('create', Lesson::class);

        $lesson = Lesson::create($request->validated());

        return (new LessonResource($lesson))->response()->setStatusCode(201);
    }

    public function show(Lesson $lesson): LessonResource
    {
        $this->authorize('view', $lesson);

        return new LessonResource($lesson);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): LessonResource
    {
        $this->authorize('update', $lesson);

        $lesson->update($request->validated());

        return new LessonResource($lesson);
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return response()->json(null, 204);
    }
}

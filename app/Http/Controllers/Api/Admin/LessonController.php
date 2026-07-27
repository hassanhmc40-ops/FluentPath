<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;

class LessonController extends Controller
{
    public function index()
    {
        return LessonResource::collection(Lesson::all());
    }

    public function store(StoreLessonRequest $request)
    {
        $lesson = Lesson::create($request->validated());

        return new LessonResource($lesson);
    }

    public function show(Lesson $lesson)
    {
        return new LessonResource($lesson);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        $lesson->update($request->validated());

        return new LessonResource($lesson);
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Enums\LessonProgressStatus;
use App\Events\LessonCompleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonProgressResource;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $query = Lesson::query();

        if ($request->filled('skill')) {
            $query->where('skill', $request->skill);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        return LessonResource::collection($query->get());
    }

    public function complete(Request $request, $id): JsonResponse|LessonProgressResource
    {
        $lesson = Lesson::findOrFail($id);

        $progress = LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['status' => LessonProgressStatus::Completed, 'completed_at' => now()]
        );

        LessonCompleted::dispatch($request->user()->id, $lesson->id);

        return new LessonProgressResource($progress);
    }
}
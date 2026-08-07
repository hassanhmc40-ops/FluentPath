<?php

namespace App\Http\Controllers\Api;

use App\Enums\CefrLevel;
use App\Enums\LessonProgressStatus;
use App\Enums\Skill;
use App\Events\LessonCompleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonProgressResource;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    /**
     * List lessons.
     *
     * Optionally filtered by skill (`grammar`, `vocabulary`, `reading`,
     * `writing`) and/or CEFR level (`A1`–`C1`).
     *
     * @group Lessons
     *
     * @queryParam skill string Filter by skill. Example: grammar
     * @queryParam level string Filter by CEFR level. Example: B1
     *
     * @apiResource App\Http\Resources\LessonResource
     *
     * @apiResourceModel App\Models\Lesson
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'skill' => ['nullable', Rule::enum(Skill::class)],
            'level' => ['nullable', Rule::enum(CefrLevel::class)],
        ]);

        $query = Lesson::query();

        if ($request->filled('skill')) {
            $query->where('skill', $request->skill);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        return LessonResource::collection($query->get());
    }

    /**
     * Mark a lesson as completed.
     *
     * Creates or updates the lesson progress row and fires the
     * recommendation-refresh pipeline.
     *
     * @group Lessons
     *
     * @urlParam lesson integer required The lesson id. Example: 1
     *
     * @apiResource App\Http\Resources\LessonProgressResource
     *
     * @apiResourceModel App\Models\LessonProgress
     */
    public function complete(Request $request, Lesson $lesson): LessonProgressResource
    {
        $this->authorize('create', LessonProgress::class);

        $progress = LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['status' => LessonProgressStatus::Completed, 'completed_at' => now()]
        );

        LessonCompleted::dispatch($request->user()->id, $lesson->id);

        return new LessonProgressResource($progress);
    }
}

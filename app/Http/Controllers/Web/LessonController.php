<?php

namespace App\Http\Controllers\Web;

use App\Enums\LessonProgressStatus;
use App\Events\LessonCompleted;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Request $request): View
    {
        $lessons = Lesson::with('quizzes')->get();

        $completedLessonIds = LessonProgress::completedFor($request->user()->id)
            ->pluck('lesson_id')
            ->all();

        return view('lessons.index', compact('lessons', 'completedLessonIds'));
    }

    public function show(Request $request, Lesson $lesson): View
    {
        $completed = LessonProgress::completedFor($request->user()->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        return view('lessons.show', [
            'lesson' => $lesson->load('quizzes'),
            'completed' => $completed,
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorize('create', LessonProgress::class);

        LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['status' => LessonProgressStatus::Completed, 'completed_at' => now()]
        );

        LessonCompleted::dispatch($request->user()->id, $lesson->id);

        return back()->with('success', 'Lesson marked as completed.');
    }
}

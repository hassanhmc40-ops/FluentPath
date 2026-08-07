<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Request $request): View
    {
        $trashed = $request->boolean('trashed');

        return view('admin.lessons.index', [
            'lessons' => $trashed
                ? Lesson::withTrashed()->whereNotNull('deleted_at')->withCount('quizzes')->orderByDesc('deleted_at')->get()
                : Lesson::withCount('quizzes')->orderBy('id')->get(),
            'trashed' => $trashed,
            'trashedCount' => Lesson::withTrashed()->whereNotNull('deleted_at')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.lessons.create', [
            'skills' => Skill::cases(),
            'levels' => CefrLevel::cases(),
        ]);
    }

    public function store(StoreLessonRequest $request): RedirectResponse
    {
        Lesson::create($request->validated());

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson created.');
    }

    public function edit(Lesson $lesson): View
    {
        return view('admin.lessons.edit', [
            'lesson' => $lesson,
            'skills' => Skill::cases(),
            'levels' => CefrLevel::cases(),
        ]);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($request->validated());

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson moved to trash.');
    }

    public function restore(string $lesson): RedirectResponse
    {
        $lesson = Lesson::withTrashed()->findOrFail($lesson);

        $this->authorize('delete', $lesson);

        $lesson->restore();

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson restored.');
    }
}

<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(): View
    {
        return view('admin.quizzes.index', [
            'quizzes' => Quiz::with('lesson')->withCount('quizQuestions')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.quizzes.create', [
            'lessons' => Lesson::orderBy('title')->get(),
        ]);
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        Quiz::create($request->validated());

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created.');
    }

    public function edit(Quiz $quiz): View
    {
        return view('admin.quizzes.edit', [
            'quiz' => $quiz,
            'lessons' => Lesson::orderBy('title')->get(),
        ]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $quiz->update($request->validated());

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted.');
    }
}

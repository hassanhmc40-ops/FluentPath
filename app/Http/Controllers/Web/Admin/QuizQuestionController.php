<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizQuestionRequest;
use App\Http\Requests\Admin\UpdateQuizQuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizQuestionController extends Controller
{
    public function index(): View
    {
        return view('admin.quiz-questions.index', [
            'quizQuestions' => QuizQuestion::with('quiz')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.quiz-questions.create', [
            'quizzes' => Quiz::with('lesson')->orderBy('id')->get(),
        ]);
    }

    public function store(StoreQuizQuestionRequest $request): RedirectResponse
    {
        QuizQuestion::create($request->validated());

        return redirect()->route('admin.quiz-questions.index')->with('success', 'Quiz question created.');
    }

    public function edit(QuizQuestion $quizQuestion): View
    {
        return view('admin.quiz-questions.edit', [
            'quizQuestion' => $quizQuestion,
            'quizzes' => Quiz::with('lesson')->orderBy('id')->get(),
        ]);
    }

    public function update(UpdateQuizQuestionRequest $request, QuizQuestion $quizQuestion): RedirectResponse
    {
        $quizQuestion->update($request->validated());

        return redirect()->route('admin.quiz-questions.index')->with('success', 'Quiz question updated.');
    }

    public function destroy(QuizQuestion $quizQuestion): RedirectResponse
    {
        $quizQuestion->delete();

        return redirect()->route('admin.quiz-questions.index')->with('success', 'Quiz question deleted.');
    }
}

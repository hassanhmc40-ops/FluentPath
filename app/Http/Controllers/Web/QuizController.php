<?php

namespace App\Http\Controllers\Web;

use App\Events\QuizAttempted;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizAttemptRequest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $level = $request->user()->currentLevel();

        $quizzes = $level
            ? Quiz::with('lesson')
                ->withCount('quizQuestions')
                ->whereHas('lesson', fn ($query) => $query->where('level', $level->value))
                ->orderBy('id')
                ->get()
            : collect();

        $lastAttempts = QuizAttempt::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->keyBy('quiz_id');

        return view('quizzes.index', [
            'quizzes' => $quizzes,
            'lastAttempts' => $lastAttempts,
            'level' => $level,
        ]);
    }

    public function show(Quiz $quiz): View
    {
        $this->authorize('view', $quiz);

        $attempts = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->orderByDesc('id')
            ->get();

        return view('quizzes.show', [
            'quiz' => $quiz->load('quizQuestions'),
            'attempts' => $attempts,
            'lastScore' => $attempts->first()?->score,
        ]);
    }

    public function attempt(SubmitQuizAttemptRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('create', QuizAttempt::class);

        $correctCount = 0;
        $total = $quiz->quizQuestions->count();

        foreach ($request->answers as $answer) {
            $question = $quiz->quizQuestions->firstWhere('id', $answer['quiz_question_id']);

            if ($question && $question->correct_answer === $answer['selected_option']) {
                $correctCount++;
            }
        }

        $score = $total > 0 ? round(($correctCount / $total) * 100, 2) : 0;

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'completed_at' => now(),
        ]);

        QuizAttempted::dispatch($request->user()->id, $quiz->id, $score);

        return back()->with('success', "Quiz submitted — you scored {$score}%.");
    }
}

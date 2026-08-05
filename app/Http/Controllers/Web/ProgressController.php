<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\WritingSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $lessonEvents = LessonProgress::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->with('lesson')
            ->orderByDesc('completed_at')
            ->limit(30)
            ->get();

        $quizEvents = QuizAttempt::where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->limit(30)
            ->get();

        $writingEvents = WritingSubmission::where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->limit(30)
            ->get();

        $events = collect();

        foreach ($lessonEvents as $event) {
            $events->push([
                'date' => $event->completed_at,
                'type' => 'lesson',
                'title' => 'Completed lesson',
                'description' => $event->lesson->title ?? 'Lesson #'.$event->lesson_id,
            ]);
        }

        foreach ($quizEvents as $event) {
            $events->push([
                'date' => $event->completed_at,
                'type' => 'quiz',
                'title' => 'Quiz attempt',
                'description' => 'Score: '.$event->score.'%',
            ]);
        }

        foreach ($writingEvents as $event) {
            $events->push([
                'date' => $event->submitted_at,
                'type' => 'writing',
                'title' => 'Writing submission',
                'description' => $event->prompt,
            ]);
        }

        $events = $events->sortByDesc('date');

        return view('progress.index', compact('events'));
    }
}

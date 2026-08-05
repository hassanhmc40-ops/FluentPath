<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonProgress;
use App\Models\PlacementTest;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get();

        $studentData = $students->map(function ($student) {
            $latestTest = PlacementTest::where('user_id', $student->id)
                ->where('status', 'analyzed')
                ->latest('id')
                ->first();

            $completedLessons = LessonProgress::where('user_id', $student->id)
                ->where('status', 'completed')
                ->count();

            $lastActivity = collect()
                ->push(['type' => 'lesson', 'date' => LessonProgress::where('user_id', $student->id)->max('completed_at')])
                ->push(['type' => 'quiz', 'date' => QuizAttempt::where('user_id', $student->id)->max('completed_at')])
                ->push(['type' => 'writing', 'date' => WritingSubmission::where('user_id', $student->id)->max('submitted_at')])
                ->filter(fn ($item) => $item['date'])
                ->sortByDesc('date')
                ->first();

            return [
                'user' => $student,
                'level' => $latestTest?->cefr_level?->value ?? '—',
                'completed_lessons' => $completedLessons,
                'last_activity' => $lastActivity ? $lastActivity['date'] : null,
            ];
        });

        return view('admin.students', compact('studentData'));
    }
}

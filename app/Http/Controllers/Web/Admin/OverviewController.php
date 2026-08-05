<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\PlacementTest;
use App\Models\Quiz;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OverviewController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_lessons' => Lesson::count(),
            'total_quizzes' => Quiz::count(),
            'pending_writing' => WritingSubmission::whereIn('status', ['pending', 'processing'])->count(),
            'pending_placement' => PlacementTest::whereIn('status', ['pending', 'processing'])->count(),
        ];

        $mostCompletedLessons = Lesson::withCount(['lessonProgress as completions' => function ($q) {
            $q->where('status', 'completed');
        }])
            ->orderByDesc('completions')
            ->limit(10)
            ->get();

        return view('admin.overview', compact('stats', 'mostCompletedLessons'));
    }
}

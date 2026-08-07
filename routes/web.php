<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Web\Admin\OverviewController;
use App\Http\Controllers\Web\Admin\PlacementQuestionController;
use App\Http\Controllers\Web\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Web\Admin\QuizQuestionController;
use App\Http\Controllers\Web\Admin\StudentController;
use App\Http\Controllers\Web\LessonController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OnboardingController;
use App\Http\Controllers\Web\PlacementTestController;
use App\Http\Controllers\Web\ProgressController;
use App\Http\Controllers\Web\QuizController;
use App\Http\Controllers\Web\RoadmapController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\WritingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(['auth', 'track.daily'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/onboarding', [OnboardingController::class, 'show']);
    Route::post('/onboarding', [OnboardingController::class, 'store']);

    Route::get('/placement-test', [PlacementTestController::class, 'show']);
    Route::post('/placement-test', [PlacementTestController::class, 'store'])->middleware('throttle:ai');
    Route::post('/placement-test/retake', [PlacementTestController::class, 'retake']);

    Route::get('/roadmap', [RoadmapController::class, 'show']);
    Route::post('/roadmap', [RoadmapController::class, 'store'])->middleware('throttle:ai');

    Route::get('/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'complete']);

    Route::get('/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
    Route::post('/quizzes/{quiz}/attempt', [QuizController::class, 'attempt']);

    Route::get('/writing', [WritingController::class, 'index']);
    Route::get('/writing/submissions', [WritingController::class, 'submissions']);
    Route::post('/writing', [WritingController::class, 'store'])->middleware('throttle:ai');

    Route::get('/progress', [ProgressController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [OverviewController::class, 'index'])->name('overview');
    Route::get('/students', [StudentController::class, 'index'])->name('students');
    Route::resource('lessons', AdminLessonController::class)->except('show');
    Route::patch('lessons/{lesson}/restore', [AdminLessonController::class, 'restore'])->name('lessons.restore');
    Route::resource('quizzes', AdminQuizController::class)->except('show');
    Route::resource('quiz-questions', QuizQuestionController::class)->except('show');
    Route::resource('placement-questions', PlacementQuestionController::class)->except('show');
});

<?php

use App\Http\Controllers\Api\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Api\Admin\PlacementQuestionController;
use App\Http\Controllers\Api\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Api\Admin\QuizQuestionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\PlacementTestController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\RoadmapController;
use App\Http\Controllers\Api\WritingSubmissionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/placement-tests', [PlacementTestController::class, 'store']);
    Route::get('/placement-tests/{id}', [PlacementTestController::class, 'show']);

    Route::post('/roadmaps', [RoadmapController::class, 'store']);
    Route::get('/roadmap', [RoadmapController::class, 'show']);

    Route::get('/lessons', [LessonController::class, 'index']);
    Route::post('/lessons/{id}/complete', [LessonController::class, 'complete']);

    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes/{id}/attempts', [QuizController::class, 'attempt']);

    Route::post('/writing-submissions', [WritingSubmissionController::class, 'store']);
    Route::get('/writing-submissions/{id}', [WritingSubmissionController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('lessons', AdminLessonController::class);
    Route::apiResource('quizzes', AdminQuizController::class);
    Route::apiResource('quiz-questions', QuizQuestionController::class);
    Route::apiResource('placement-questions', PlacementQuestionController::class);
});

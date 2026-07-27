<?php

use App\Http\Controllers\Api\Admin\LessonController;
use App\Http\Controllers\Api\Admin\PlacementQuestionController;
use App\Http\Controllers\Api\Admin\QuizController;
use App\Http\Controllers\Api\Admin\QuizQuestionController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('lessons', LessonController::class);
    Route::apiResource('quizzes', QuizController::class);
    Route::apiResource('quiz-questions', QuizQuestionController::class);
    Route::apiResource('placement-questions', PlacementQuestionController::class);
});

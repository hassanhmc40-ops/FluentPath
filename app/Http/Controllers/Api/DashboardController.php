<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the student's progress dashboard.
     *
     * Aggregates CEFR level, current roadmap week, completed/total lessons,
     * writing score history, grammar and vocabulary improvement, learning
     * streak, overall progress and the single next recommended action.
     *
     * @group Dashboard
     *
     * @response {
     *   "cefr_level": "B1",
     *   "current_week": 2,
     *   "lessons": {"completed": 12, "total": 24},
     *   "writing_score_history": [
     *     {"submitted_at": "2026-07-28T10:00:00.000000Z", "score": 82}
     *   ],
     *   "grammar_improvement": {"start_score": 60, "current_score": 74},
     *   "vocabulary_improvement": {"start_score": 58, "current_score": 70},
     *   "learning_streak": 3,
     *   "overall_progress_percentage": 50,
     *   "next_recommended_action": {
     *     "lesson_id": 12,
     *     "topic": "Past Simple",
     *     "lesson_title": "Past Simple: Irregular Verbs",
     *     "writing_prompt": "Write a short paragraph about your weekend."
     *   }
     * }
     */
    public function show(Request $request): DashboardResource
    {
        $data = app(DashboardService::class)->forUser($request->user()->id);

        return new DashboardResource($data);
    }
}

<?php

namespace App\Listeners;

use App\Events\LessonCompleted;
use App\Events\QuizAttempted;
use App\Events\WritingCorrected;
use App\Services\RecommendationService;

class UpdateRecommendations
{
    public function handle(LessonCompleted|QuizAttempted|WritingCorrected $event): void
    {
        app(RecommendationService::class)->refreshForUser($event->userId);
    }
}
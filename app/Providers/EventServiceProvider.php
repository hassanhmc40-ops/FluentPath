<?php

namespace App\Providers;

use App\Events\LessonCompleted;
use App\Events\QuizAttempted;
use App\Events\WritingCorrected;
use App\Listeners\UpdateRecommendations;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LessonCompleted::class => [UpdateRecommendations::class],
        QuizAttempted::class => [UpdateRecommendations::class],
        WritingCorrected::class => [UpdateRecommendations::class],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
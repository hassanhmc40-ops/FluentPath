<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LessonCompleted
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $lessonId,
    ) {}
}
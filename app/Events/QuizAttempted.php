<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class QuizAttempted
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $quizId,
        public float $score,
    ) {}
}

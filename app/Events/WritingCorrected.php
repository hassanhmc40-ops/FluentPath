<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WritingCorrected
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $writingSubmissionId,
    ) {}
}
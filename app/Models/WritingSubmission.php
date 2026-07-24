<?php

namespace App\Models;

use App\Enums\WritingSubmissionStatus;
use Database\Factories\WritingSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WritingSubmission extends Model
{
    /** @use HasFactory<WritingSubmissionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'mistakes' => 'array',
            'recommendations' => 'array',
            'next_topics' => 'array',
            'score' => 'decimal:2',
            'status' => WritingSubmissionStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

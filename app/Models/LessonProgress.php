<?php

namespace App\Models;

use App\Enums\LessonProgressStatus;
use Database\Factories\LessonProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'lesson_id', 'status', 'completed_at'])]
class LessonProgress extends Model
{
    /** @use HasFactory<LessonProgressFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => LessonProgressStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function scopeCompletedFor(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->where('status', LessonProgressStatus::Completed);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

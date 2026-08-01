<?php

namespace App\Models;

use App\Enums\RoadmapStatus;
use Database\Factories\RoadmapFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'placement_test_id', 'title', 'status', 'generated_at', 'next_lesson_id', 'next_topic', 'next_writing_prompt'])]
class Roadmap extends Model
{
    /** @use HasFactory<RoadmapFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => RoadmapStatus::class,
            'generated_at' => 'datetime',
        ];
    }

    public function scopeLatestForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->latest('id');
    }

    public function nextLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'next_lesson_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function placementTest(): BelongsTo
    {
        return $this->belongsTo(PlacementTest::class);
    }

    public function roadmapWeeks(): HasMany
    {
        return $this->hasMany(RoadmapWeek::class);
    }
}

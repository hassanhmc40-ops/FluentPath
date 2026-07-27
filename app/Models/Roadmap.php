<?php

namespace App\Models;

use Database\Factories\RoadmapFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roadmap extends Model
{
    /** @use HasFactory<RoadmapFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
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

<?php

namespace App\Models;

use Database\Factories\RoadmapWeekFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['roadmap_id', 'week_number', 'objective'])]
class RoadmapWeek extends Model
{
    /** @use HasFactory<RoadmapWeekFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'week_number' => 'integer',
        ];
    }

    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    public function roadmapWeekLessons(): HasMany
    {
        return $this->hasMany(RoadmapWeekLesson::class);
    }
}

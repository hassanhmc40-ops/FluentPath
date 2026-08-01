<?php

namespace App\Models;

use Database\Factories\RoadmapWeekLessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['roadmap_week_id', 'lesson_id', 'display_order'])]
class RoadmapWeekLesson extends Model
{
    /** @use HasFactory<RoadmapWeekLessonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
        ];
    }

    public function roadmapWeek(): BelongsTo
    {
        return $this->belongsTo(RoadmapWeek::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

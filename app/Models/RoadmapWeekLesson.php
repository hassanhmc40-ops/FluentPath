<?php

namespace App\Models;

use Database\Factories\RoadmapWeekLessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapWeekLesson extends Model
{
    /** @use HasFactory<RoadmapWeekLessonFactory> */
    use HasFactory;

    public function roadmapWeek(): BelongsTo
    {
        return $this->belongsTo(RoadmapWeek::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

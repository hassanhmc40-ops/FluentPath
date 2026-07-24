<?php

namespace App\Models;

use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use Database\Factories\PlacementTestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlacementTest extends Model
{
    /** @use HasFactory<PlacementTestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'status' => PlacementTestStatus::class,
            'cefr_level' => CefrLevel::class,
            'grammar_score' => 'decimal:2',
            'vocabulary_score' => 'decimal:2',
            'writing_score' => 'decimal:2',
            'strengths' => 'array',
            'weaknesses' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function placementAnswers(): HasMany
    {
        return $this->hasMany(PlacementAnswer::class);
    }

    public function roadmap(): HasOne
    {
        return $this->hasOne(Roadmap::class);
    }
}

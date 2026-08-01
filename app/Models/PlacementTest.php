<?php

namespace App\Models;

use App\Enums\CefrLevel;
use App\Enums\PlacementTestStatus;
use Database\Factories\PlacementTestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'submitted_at', 'status', 'cefr_level', 'grammar_score', 'vocabulary_score', 'writing_score', 'strengths', 'weaknesses', 'reasoning'])]
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

    public function scopeLatestAnalyzedFor(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->where('status', PlacementTestStatus::Analyzed)
            ->latest('id');
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

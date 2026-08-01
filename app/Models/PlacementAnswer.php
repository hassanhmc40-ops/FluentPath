<?php

namespace App\Models;

use Database\Factories\PlacementAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['placement_test_id', 'placement_question_id', 'answer', 'score', 'feedback'])]
class PlacementAnswer extends Model
{
    /** @use HasFactory<PlacementAnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function placementTest(): BelongsTo
    {
        return $this->belongsTo(PlacementTest::class);
    }

    public function placementQuestion(): BelongsTo
    {
        return $this->belongsTo(PlacementQuestion::class);
    }
}

<?php

namespace App\Models;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use Database\Factories\PlacementQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacementQuestion extends Model
{
    /** @use HasFactory<PlacementQuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'skill' => Skill::class,
            'level' => CefrLevel::class,
        ];
    }

    public function placementAnswers(): HasMany
    {
        return $this->hasMany(PlacementAnswer::class);
    }
}

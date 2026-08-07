<?php

namespace App\Models;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use Database\Factories\PlacementQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['question', 'skill', 'level', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'])]
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

    /**
     * Whether this question is a multiple-choice item (all skills except
     * writing) with option_a..d and a correct_answer set.
     */
    public function isMultipleChoice(): bool
    {
        return $this->skill !== Skill::Writing
            && $this->correct_answer !== null;
    }

    public function placementAnswers(): HasMany
    {
        return $this->hasMany(PlacementAnswer::class);
    }
}

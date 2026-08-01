<?php

namespace App\Http\Requests\Admin;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlacementQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('placement_question'));
    }

    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'filled', 'string', 'max:2000'],
            'skill' => ['sometimes', 'filled', Rule::enum(Skill::class)],
            'level' => ['sometimes', 'filled', Rule::enum(CefrLevel::class)],
        ];
    }
}

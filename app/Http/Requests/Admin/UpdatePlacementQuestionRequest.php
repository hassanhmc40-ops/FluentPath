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
            'option_a' => ['sometimes', 'required_unless:skill,writing', 'string', 'max:255'],
            'option_b' => ['sometimes', 'required_unless:skill,writing', 'string', 'max:255'],
            'option_c' => ['sometimes', 'required_unless:skill,writing', 'string', 'max:255'],
            'option_d' => ['sometimes', 'required_unless:skill,writing', 'string', 'max:255'],
            'correct_answer' => ['sometimes', 'required_unless:skill,writing', 'string', 'in:a,b,c,d'],
        ];
    }
}

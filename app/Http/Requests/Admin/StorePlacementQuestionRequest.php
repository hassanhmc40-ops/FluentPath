<?php

namespace App\Http\Requests\Admin;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use App\Models\PlacementQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlacementQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PlacementQuestion::class);
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:2000'],
            'skill' => ['required', Rule::enum(Skill::class)],
            'level' => ['required', Rule::enum(CefrLevel::class)],
            'option_a' => ['required_unless:skill,writing', 'string', 'max:255'],
            'option_b' => ['required_unless:skill,writing', 'string', 'max:255'],
            'option_c' => ['required_unless:skill,writing', 'string', 'max:255'],
            'option_d' => ['required_unless:skill,writing', 'string', 'max:255'],
            'correct_answer' => ['required_unless:skill,writing', 'string', 'in:a,b,c,d'],
        ];
    }
}

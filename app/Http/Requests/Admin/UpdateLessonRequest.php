<?php

namespace App\Http\Requests\Admin;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lesson'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'skill' => ['sometimes', 'filled', Rule::enum(Skill::class)],
            'level' => ['sometimes', 'filled', Rule::enum(CefrLevel::class)],
        ];
    }
}

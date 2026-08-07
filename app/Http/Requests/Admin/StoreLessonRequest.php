<?php

namespace App\Http\Requests\Admin;

use App\Enums\CefrLevel;
use App\Enums\Skill;
use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Lesson::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'skill' => ['required', Rule::enum(Skill::class)],
            'level' => ['required', Rule::enum(CefrLevel::class)],
        ];
    }
}

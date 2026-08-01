<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('quiz_question'));
    }

    public function rules(): array
    {
        return [
            'quiz_id' => ['sometimes', 'integer', 'exists:quizzes,id'],
            'question' => ['sometimes', 'filled', 'string', 'max:2000'],
            'option_a' => ['sometimes', 'filled', 'string', 'max:255'],
            'option_b' => ['sometimes', 'filled', 'string', 'max:255'],
            'option_c' => ['sometimes', 'filled', 'string', 'max:255'],
            'option_d' => ['sometimes', 'filled', 'string', 'max:255'],
            'correct_answer' => ['sometimes', 'filled', Rule::in(['a', 'b', 'c', 'd'])],
        ];
    }
}

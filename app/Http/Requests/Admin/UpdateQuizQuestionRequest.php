<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quiz_id' => ['sometimes', 'integer', 'exists:quizzes,id'],
            'question' => ['sometimes', 'string'],
            'option_a' => ['sometimes', 'string', 'max:255'],
            'option_b' => ['sometimes', 'string', 'max:255'],
            'option_c' => ['sometimes', 'string', 'max:255'],
            'option_d' => ['sometimes', 'string', 'max:255'],
            'correct_answer' => ['sometimes', 'string', 'max:255'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\QuizAttempt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', QuizAttempt::class);
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1', 'max:100'],
            'answers.*.quiz_question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('quiz_questions', 'id')->where('quiz_id', $this->route('quiz')->id),
            ],
            'answers.*.selected_option' => ['required', 'string', Rule::in(['a', 'b', 'c', 'd'])],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.*.quiz_question_id.distinct' => 'Duplicate questions are not allowed.',
            'answers.*.quiz_question_id.exists' => 'One or more questions do not belong to this quiz.',
            'answers.*.selected_option.in' => 'Selected option must be one of: a, b, c, d.',
        ];
    }
}

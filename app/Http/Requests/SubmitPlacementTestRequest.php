<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPlacementTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.placement_question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('placement_questions', 'id'),
            ],
            'answers.*.answer' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.*.placement_question_id.distinct' => 'Duplicate questions are not allowed.',
        ];
    }
}
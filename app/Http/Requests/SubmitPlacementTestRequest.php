<?php

namespace App\Http\Requests;

use App\Models\PlacementTest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPlacementTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PlacementTest::class);
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1', 'max:50'],
            'answers.*.placement_question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('placement_questions', 'id'),
            ],
            'answers.*.answer' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.*.placement_question_id.distinct' => 'Duplicate questions are not allowed.',
        ];
    }
}

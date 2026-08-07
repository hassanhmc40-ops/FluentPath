<?php

namespace App\Http\Requests;

use App\Enums\Skill;
use App\Models\PlacementQuestion;
use App\Models\PlacementTest;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPlacementTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PlacementTest::class);
    }

    /**
     * The placement form always submits an entry per question (a hidden
     * placement_question_id is rendered even when the student skips it).
     * Drop entries without an answer so skipped questions are simply absent
     * from the payload instead of failing the "answer required" rule.
     */
    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('answers'))) {
            return;
        }

        $answered = collect($this->input('answers'))
            ->filter(fn (array $entry) => isset($entry['answer'])
                && is_string($entry['answer'])
                && trim($entry['answer']) !== '')
            ->values()
            ->all();

        $this->merge(['answers' => $answered]);
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1', 'max:150'],
            'answers.*.placement_question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('placement_questions', 'id'),
            ],
            'answers.*.answer' => [
                'required',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, Closure $fail): void {
                    preg_match('/^answers\.(\d+)\.answer$/', $attribute, $matches);

                    if (! isset($matches[1])) {
                        return;
                    }

                    $questionId = $this->input("answers.{$matches[1]}.placement_question_id");

                    if ($questionId === null) {
                        return; // handled by the exists rule
                    }

                    $question = PlacementQuestion::find($questionId);

                    if ($question === null) {
                        return; // handled by the exists rule
                    }

                    if ($question->skill !== Skill::Writing
                        && ! in_array(strtolower((string) $value), ['a', 'b', 'c', 'd'], true)) {
                        $fail('The answer must be one of A, B, C or D for multiple choice questions.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Answer at least one question to submit the placement test.',
            'answers.min' => 'Answer at least one question to submit the placement test.',
            'answers.*.placement_question_id.distinct' => 'Duplicate questions are not allowed.',
        ];
    }
}

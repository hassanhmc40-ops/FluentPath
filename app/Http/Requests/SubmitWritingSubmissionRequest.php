<?php

namespace App\Http\Requests;

use App\Models\WritingSubmission;
use Illuminate\Foundation\Http\FormRequest;

class SubmitWritingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WritingSubmission::class);
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:500'],
            'original_text' => ['required', 'string', 'min:10', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'original_text.min' => 'The text must be at least 10 characters.',
        ];
    }
}

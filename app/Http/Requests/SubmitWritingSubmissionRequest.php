<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitWritingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string'],
            'original_text' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'original_text.min' => 'The text must be at least 10 characters.',
        ];
    }
}
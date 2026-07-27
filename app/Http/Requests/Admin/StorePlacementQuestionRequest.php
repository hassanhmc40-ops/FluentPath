<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlacementQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'skill' => ['required', 'string', 'in:grammar,vocabulary,writing'],
            'level' => ['required', 'string', 'in:A1,A2,B1,B2,C1'],
        ];
    }
}

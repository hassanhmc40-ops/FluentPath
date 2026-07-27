<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlacementQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'string'],
            'skill' => ['sometimes', 'string', 'in:grammar,vocabulary,writing'],
            'level' => ['sometimes', 'string', 'in:A1,A2,B1,B2,C1'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'skill' => ['sometimes', 'string', 'in:grammar,vocabulary,writing'],
            'level' => ['sometimes', 'string', 'in:A1,A2,B1,B2,C1'],
        ];
    }
}

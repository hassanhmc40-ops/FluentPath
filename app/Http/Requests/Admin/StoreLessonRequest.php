<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'skill' => ['required', 'string', 'in:grammar,vocabulary,writing'],
            'level' => ['required', 'string', 'in:A1,A2,B1,B2,C1'],
        ];
    }
}

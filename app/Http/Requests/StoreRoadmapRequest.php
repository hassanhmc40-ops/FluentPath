<?php

namespace App\Http\Requests;

use App\Models\Roadmap;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoadmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Roadmap::class);
    }

    public function rules(): array
    {
        return [];
    }
}

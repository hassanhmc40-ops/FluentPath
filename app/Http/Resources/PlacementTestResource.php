<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlacementTestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'cefr_level' => $this->cefr_level,
            'grammar_score' => $this->grammar_score,
            'vocabulary_score' => $this->vocabulary_score,
            'writing_score' => $this->writing_score,
            'strengths' => $this->strengths,
            'weaknesses' => $this->weaknesses,
            'reasoning' => $this->reasoning,
            'answers' => PlacementAnswerResource::collection($this->whenLoaded('placementAnswers')),
            'created_at' => $this->created_at,
        ];
    }
}
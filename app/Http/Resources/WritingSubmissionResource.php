<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WritingSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'prompt' => $this->prompt,
            'original_text' => $this->original_text,
            'corrected_text' => $this->corrected_text,
            'score' => $this->score,
            'grammar_feedback' => $this->grammar_feedback,
            'vocabulary_feedback' => $this->vocabulary_feedback,
            'fluency_feedback' => $this->fluency_feedback,
            'mistakes' => $this->mistakes,
            'recommendations' => $this->recommendations,
            'next_topics' => $this->next_topics,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

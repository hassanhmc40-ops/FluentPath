<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cefr_level' => $this['cefr_level'],
            'current_week' => $this['current_week'],
            'lessons' => $this['lessons'],
            'writing_score_history' => $this['writing_score_history'],
            'grammar_improvement' => $this['grammar_improvement'],
            'vocabulary_improvement' => $this['vocabulary_improvement'],
            'learning_streak' => $this['learning_streak'],
            'overall_progress_percentage' => $this['overall_progress_percentage'],
            'next_recommended_action' => $this['next_recommended_action'],
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoadmapWeekResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week_number' => $this->week_number,
            'objective' => $this->objective,
            'lessons' => RoadmapWeekLessonResource::collection($this->whenLoaded('roadmapWeekLessons')),
        ];
    }
}

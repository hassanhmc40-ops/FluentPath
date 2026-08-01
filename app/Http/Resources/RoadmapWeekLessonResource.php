<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoadmapWeekLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_order' => $this->display_order,
            'lesson' => new LessonResource($this->whenLoaded('lesson')),
        ];
    }
}

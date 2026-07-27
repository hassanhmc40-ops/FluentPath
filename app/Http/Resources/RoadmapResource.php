<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoadmapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'generated_at' => $this->generated_at,
            'weeks' => RoadmapWeekResource::collection($this->whenLoaded('roadmapWeeks')),
            'created_at' => $this->created_at,
        ];
    }
}
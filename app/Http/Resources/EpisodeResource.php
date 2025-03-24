<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'film_id' => $this->film_id,
            'episode_number' => $this->episode_number,
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
            'duration' => $this->duration,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
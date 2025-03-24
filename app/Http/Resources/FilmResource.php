<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'original_name' => $this->original_name,
            'poster_url' => $this->poster_url,
            'description' => $this->description,
            'total_episodes' => $this->total_episodes,
            'current_episode' => $this->current_episode,
            'quality' => $this->quality,
            'director' => $this->director,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'episodes' => $this->whenLoaded('episodes', function () {
                return $this->episodes->map(function ($episode) {
                    return [
                        'id' => $episode->id,
                        'film_id' => $episode->film_id,
                        'episode_number' => $episode->episode_number,
                        'title' => $episode->title,
                        'embed' => $episode->embed,
                        'created_at' => $episode->created_at,
                        'updated_at' => $episode->updated_at,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 
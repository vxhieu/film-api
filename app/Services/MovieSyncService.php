<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Category;
use App\Models\Episode;
use App\Models\ApiSource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MovieSyncService
{
    public function syncFromApi(ApiSource $source, array $data)
    {
        try {
            DB::beginTransaction();

            $movie = $data['movie'];

            // Create or update movie
            $movieModel = Movie::updateOrCreate(
                ['id' => $movie['id']],
                [
                    'name' => $movie['name'],
                    'slug' => $movie['slug'],
                    'original_name' => $movie['original_name'],
                    'thumb_url' => $movie['thumb_url'],
                    'poster_url' => $movie['poster_url'],
                    'description' => $movie['description'],
                    'total_episodes' => $movie['total_episodes'],
                    'current_episode' => $movie['current_episode'],
                    'time' => $movie['time'],
                    'quality' => $movie['quality'],
                    'language' => $movie['language'],
                    'director' => $movie['director'],
                    'casts' => $movie['casts']
                ]
            );

            // Process categories
            $categoryIds = [];
            foreach ($movie['category'] as $groupData) {
                foreach ($groupData['list'] as $categoryData) {
                    $category = Category::updateOrCreate(
                        ['id' => $categoryData['id']],
                        [
                            'name' => $categoryData['name'],
                            'slug' => Str::slug($categoryData['name'])
                        ]
                    );
                    $categoryIds[] = $category->id;
                }
            }

            // Sync categories
            $movieModel->categories()->sync($categoryIds);

            // Process episodes
            $movieModel->episodes()->delete(); // Remove old episodes
            foreach ($movie['episodes'] as $serverData) {
                foreach ($serverData['items'] as $episodeData) {
                    Episode::create([
                        'movie_id' => $movieModel->id,
                        'server_name' => $serverData['server_name'],
                        'name' => $episodeData['name'],
                        'slug' => $episodeData['slug'],
                        'embed' => $episodeData['embed'],
                        'm3u8' => $episodeData['m3u8']
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Movie synced successfully',
                'movie' => $movieModel
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to sync movie',
                'error' => $e->getMessage()
            ];
        }
    }
} 
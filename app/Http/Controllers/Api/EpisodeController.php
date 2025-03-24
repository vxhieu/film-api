<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Film;
use App\Http\Resources\EpisodeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EpisodeController extends Controller
{
    public function index(Film $film)
    {
        $episodes = $film->episodes()->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => EpisodeResource::collection($episodes),
            'pagination' => [
                'total' => $episodes->total(),
                'per_page' => $episodes->perPage(),
                'current_page' => $episodes->currentPage(),
                'last_page' => $episodes->lastPage()
            ]
        ]);
    }

    public function show(Film $film, Episode $episode)
    {
        return response()->json($episode);
    }

    public function store(Request $request, Film $film)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'server_name' => 'required|string|max:255',
            'embed' => 'nullable|string',
            'm3u8' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['film_id'] = $film->id;

        $episode = Episode::create($validated);
        return response()->json([
            'status' => 'success',
            'data' => new EpisodeResource($episode)
        ], 201);
    }

    public function update(Request $request, Film $film, Episode $episode)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'server_name' => 'sometimes|required|string|max:255',
            'embed' => 'nullable|string',
            'm3u8' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $episode->update($validated);
        return response()->json([
            'status' => 'success',
            'data' => new EpisodeResource($episode)
        ]);
    }

    public function destroy(Film $film, Episode $episode)
    {
        $episode->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Episode deleted successfully'
        ]);
    }
} 
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index()
    {
        $movies = Movie::with(['categories', 'episodes'])->paginate(10);
        return response()->json($movies);
    }

    public function show($id)
    {
        $movie = Movie::with(['categories', 'episodes'])->findOrFail($id);
        return response()->json($movie);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'original_name' => 'nullable|string|max:255',
            'thumb_url' => 'nullable|url',
            'poster_url' => 'nullable|url',
            'description' => 'nullable|string',
            'total_episodes' => 'required|integer|min:0',
            'current_episode' => 'required|integer|min:0',
            'time' => 'nullable|string',
            'quality' => 'nullable|string',
            'language' => 'nullable|string',
            'director' => 'nullable|string',
            'casts' => 'nullable|array',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $movie = Movie::create($validated);
        $movie->categories()->attach($request->categories);

        return response()->json($movie->load(['categories', 'episodes']), 201);
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'original_name' => 'nullable|string|max:255',
            'thumb_url' => 'nullable|url',
            'poster_url' => 'nullable|url',
            'description' => 'nullable|string',
            'total_episodes' => 'sometimes|required|integer|min:0',
            'current_episode' => 'sometimes|required|integer|min:0',
            'time' => 'nullable|string',
            'quality' => 'nullable|string',
            'language' => 'nullable|string',
            'director' => 'nullable|string',
            'casts' => 'nullable|array',
            'categories' => 'sometimes|required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $movie->update($validated);

        if (isset($validated['categories'])) {
            $movie->categories()->sync($validated['categories']);
        }

        return response()->json($movie->load(['categories', 'episodes']));
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json(null, 204);
    }
} 
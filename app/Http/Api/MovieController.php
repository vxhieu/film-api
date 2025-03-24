<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Http\Resources\MovieResource;
use App\Http\Requests\MovieRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::with(['categories.group', 'episodes']);

        // Apply filters
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->has('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Apply sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
        }

        $movies = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'movies' => MovieResource::collection($movies),
            'pagination' => [
                'total' => $movies->total(),
                'per_page' => $movies->perPage(),
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage()
            ]
        ]);
    }

    public function show($id)
    {
        $movie = Movie::with(['categories.group', 'episodes'])->find($id);
        
        if (!$movie) {
            return response()->json([
                'status' => 'error',
                'message' => 'Movie not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'movie' => new MovieResource($movie)
        ]);
    }

    public function showBySlug($slug)
    {
        $movie = Movie::with(['categories.group', 'episodes'])
            ->where('slug', $slug)
            ->first();
        
        if (!$movie) {
            return response()->json([
                'status' => 'error',
                'message' => 'Movie not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'movie' => new MovieResource($movie)
        ]);
    }

    public function store(MovieRequest $request)
    {
        try {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['name']);
            
            $movie = Movie::create($data);

            // Attach categories
            if (!empty($data['categories'])) {
                $movie->categories()->attach($data['categories']);
            }

            // Create episodes
            if (!empty($data['episodes'])) {
                $movie->episodes()->createMany($data['episodes']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Movie created successfully',
                'movie' => new MovieResource($movie->load(['categories.group', 'episodes']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create movie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(MovieRequest $request, $id)
    {
        try {
            $movie = Movie::findOrFail($id);
            $data = $request->validated();
            
            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }
            
            $movie->update($data);

            // Sync categories
            if (isset($data['categories'])) {
                $movie->categories()->sync($data['categories']);
            }

            // Update episodes
            if (isset($data['episodes'])) {
                $movie->episodes()->delete();
                $movie->episodes()->createMany($data['episodes']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Movie updated successfully',
                'movie' => new MovieResource($movie->load(['categories.group', 'episodes']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update movie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $movie = Movie::findOrFail($id);
            $movie->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Movie deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete movie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Movie::with(['categories.group', 'episodes']);

        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('original_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('director', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('casts', 'LIKE', "%{$searchTerm}%");
            });
        }

        $movies = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'movies' => MovieResource::collection($movies),
            'pagination' => [
                'total' => $movies->total(),
                'per_page' => $movies->perPage(),
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage()
            ]
        ]);
    }
} 
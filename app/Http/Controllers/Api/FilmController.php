<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilmRequest;
use App\Http\Resources\FilmResource;
use App\Services\FilmService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    protected $filmService;

    public function __construct(FilmService $filmService)
    {
        $this->filmService = $filmService;
    }

    public function index(Request $request)
    {
        $films = $this->filmService->getAllFilms($request->all());
        
        return response()->json([
            'status' => 'success',
            'data' => FilmResource::collection($films),
            'pagination' => [
                'total' => $films->total(),
                'per_page' => $films->perPage(),
                'current_page' => $films->currentPage(),
                'last_page' => $films->lastPage()
            ]
        ]);
    }

    public function show($id)
    {
        $film = $this->filmService->getFilmById($id);
        
        return response()->json([
            'status' => 'success',
            'data' => new FilmResource($film)
        ]);
    }

    public function showBySlug($slug)
    {
        $film = $this->filmService->getFilmBySlug($slug);
        
        return response()->json([
            'status' => 'success',
            'data' => new FilmResource($film)
        ]);
    }

    public function store(FilmRequest $request)
    {
        try {
            $film = $this->filmService->createFilm($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Film created successfully',
                'data' => new FilmResource($film)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create film',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(FilmRequest $request, $id)
    {
        try {
            $film = $this->filmService->updateFilm($id, $request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Film updated successfully',
                'data' => new FilmResource($film)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update film',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->filmService->deleteFilm($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Film deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete film',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function featured()
    {
        $films = $this->filmService->getFeaturedFilms();
        
        return response()->json([
            'status' => 'success',
            'data' => FilmResource::collection($films)
        ]);
    }
} 
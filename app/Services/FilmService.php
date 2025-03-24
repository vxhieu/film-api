<?php

namespace App\Services;

use App\Models\Film;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FilmService
{
    public function getAllFilms(array $filters = [])
    {
        $query = Film::with(['categories', 'episodes']);

        if (isset($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('id', $filters['category']);
            });
        }

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name':
                    $query->orderBy('name');
                    break;
                case 'views':
                    $query->orderBy('views', 'desc');
                    break;
                case 'oldest':
                    $query->oldest();
                    break;
                default:
                    $query->latest();
            }
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function getFilmById($id)
    {
        return Film::with(['categories', 'episodes'])->findOrFail($id);
    }

    public function getFilmBySlug($slug)
    {
        return Film::with(['categories', 'episodes'])->where('slug', $slug)->firstOrFail();
    }

    public function getFeaturedFilms()
    {
        return Cache::remember('featured_films', 3600, function () {
            return Film::with(['categories', 'episodes'])
                ->where('is_active', true)
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        });
    }

    public function getRecentlyUpdatedFilms($limit = 12)
    {
        return Film::with(['categories', 'episodes'])
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPopularFilms($limit = 12)
    {
        return Film::with(['categories', 'episodes'])
            ->where('is_active', true)
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }

    public function createFilm(array $data)
    {
        try {
            DB::beginTransaction();

            $data['slug'] = Str::slug($data['name']);
            $film = Film::create($data);

            if (!empty($data['categories'])) {
                $film->categories()->attach($data['categories']);
            }

            DB::commit();
            Cache::tags(['films'])->flush();

            return $film->load(['categories', 'episodes']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateFilm($id, array $data)
    {
        try {
            DB::beginTransaction();

            $film = Film::findOrFail($id);

            if (isset($data['name']) && $data['name'] !== $film->name) {
                $data['slug'] = Str::slug($data['name']);
            }

            $film->update($data);

            if (isset($data['categories'])) {
                $film->categories()->sync($data['categories']);
            }

            DB::commit();
            Cache::tags(['films'])->flush();

            return $film->load(['categories', 'episodes']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteFilm($id)
    {
        try {
            DB::beginTransaction();

            $film = Film::findOrFail($id);
            $film->categories()->detach();
            $film->episodes()->delete();
            $film->delete();

            DB::commit();
            Cache::tags(['films'])->flush();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function incrementViews($id)
    {
        $film = Film::findOrFail($id);
        $film->incrementViews();
        return $film;
    }
} 
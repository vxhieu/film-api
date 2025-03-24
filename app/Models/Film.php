<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Film extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'original_name',
        'poster_url',
        'backdrop_url',
        'description',
        'total_episodes',
        'current_episode',
        'time',
        'quality',
        'language',
        'year',
        'director',
        'casts',
        'views',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_episodes' => 'integer',
        'views' => 'integer',
        'casts' => 'array',
        'created_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
    ];

    protected $appends = ['related_films'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($film) {
            if (empty($film->slug)) {
                $film->slug = Str::slug($film->name);
            }
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'film_categories');
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function getRelatedFilmsAttribute()
    {
        // Get films with similar categories
        return self::whereHas('categories', function ($query) {
            $query->whereIn('categories.id', $this->categories->pluck('id'));
        })
        ->where('id', '!=', $this->id)
        ->limit(6)
        ->get();
    }

    public function incrementViews()
    {
        $this->increment('views');
    }
} 
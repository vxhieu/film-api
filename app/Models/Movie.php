<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'original_name',
        'thumb_url',
        'poster_url',
        'description',
        'total_episodes',
        'current_episode',
        'time',
        'quality',
        'language',
        'director',
        'casts'
    ];

    protected $casts = [
        'casts' => 'array',
        'total_episodes' => 'integer',
        'current_episode' => 'integer',
        'created_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = md5(uniqid(rand(), true));
            }
        });
    }
} 
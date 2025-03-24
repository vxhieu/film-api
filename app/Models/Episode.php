<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Episode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'film_id',
        'name',
        'slug',
        'server_name',
        'embed',
        'm3u8',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($episode) {
            if (empty($episode->slug)) {
                $episode->slug = Str::slug($episode->name);
            }
        });
    }

    public function film()
    {
        return $this->belongsTo(Film::class);
    }
} 
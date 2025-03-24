<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'group',
        'group_id',
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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function group()
    {
        return $this->belongsTo(CategoryGroup::class, 'category_group_id');
    }

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

    public function films()
    {
        return $this->belongsToMany(Film::class, 'film_categories');
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'name'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
} 
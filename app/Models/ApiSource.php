<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'config',
        'is_active',
        'last_sync'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'last_sync' => 'datetime',
        'created_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s.u\Z',
    ];
} 
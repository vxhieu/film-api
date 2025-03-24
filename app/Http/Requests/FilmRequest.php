<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilmRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'original_name' => 'nullable|string|max:255',
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
            'categories.*' => 'exists:categories,id',
            'is_active' => 'boolean'
        ];
    }
} 
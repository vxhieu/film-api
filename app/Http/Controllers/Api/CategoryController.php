<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('films')->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::collection($categories),
            'pagination' => [
                'total' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage()
            ]
        ]);
    }

    public function show($id)
    {
        $category = Category::with('films')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => new CategoryResource($category)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'group' => 'nullable|string',
            'group_id' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);
        return response()->json([
            'status' => 'success',
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'group' => 'nullable|string',
            'group_id' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);
        return response()->json([
            'status' => 'success',
            'data' => new CategoryResource($category)
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }
} 
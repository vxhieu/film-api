<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiSource;
use App\Models\Film;
use App\Models\Category;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiSourceController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $sources = ApiSource::all();
        return response()->json([
            'success' => true,
            'data' => $sources
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Log request data for debugging
            Log::info('API Source Store Request:', $request->all());

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'url' => 'required|url',
                'config' => 'required|array',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create API source
            $source = ApiSource::create([
                'name' => $request->name,
                'url' => $request->url,
                'config' => $request->config,
                'is_active' => $request->is_active ?? true,
                'last_sync' => now()
            ]);

            // Create film
            $film = Film::create([
                'name' => $request->config['original_name'],
                'original_name' => $request->config['original_name'],
                'poster_url' => $request->config['poster_url'],
                'description' => $request->config['description'],
                'total_episodes' => $request->config['total_episodes'],
                'current_episode' => $request->config['current_episode'],
                'time' => $request->config['time'],
                'quality' => $request->config['quality'],
                'language' => $request->config['language'],
                'director' => $request->config['director'],
                'casts' => $request->config['casts'],
                'is_active' => true
            ]);

            // Create categories
            foreach ($request->config['category'] as $groupData) {
                $group = $groupData['group'];
                foreach ($groupData['list'] as $categoryData) {
                    $category = Category::firstOrCreate(
                        ['slug' => Str::slug($categoryData['name'])],
                        [
                            'name' => $categoryData['name'],
                            'group' => $group['name'],
                            'group_id' => $group['id'],
                            'is_active' => true
                        ]
                    );
                    $film->categories()->attach($category->id);
                }
            }

            // Create episodes
            foreach ($request->config['episodes'] as $episodeData) {
                foreach ($episodeData['items'] as $item) {
                    $slug = Str::slug($item['name']);
                    $count = 1;
                    
                    // Kiểm tra và tạo slug duy nhất
                    while (Episode::where('slug', $slug)->exists()) {
                        $slug = Str::slug($item['name']) . '-' . $count;
                        $count++;
                    }
                    
                    Episode::create([
                        'film_id' => $film->id,
                        'name' => $item['name'],
                        'slug' => $slug,
                        'server_name' => $episodeData['server_name'],
                        'embed' => $item['embed'],
                        'm3u8' => $item['m3u8'],
                        'is_active' => true
                    ]);
                }
            }

            DB::commit();

            Log::info('API source and related data created successfully:', [
                'source_id' => $source->id,
                'film_id' => $film->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'API source and related data created successfully',
                'data' => [
                    'source' => $source,
                    'film' => $film
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating API source and related data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create API source and related data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $source = ApiSource::findOrFail($id);

            // Validate request
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'url' => 'sometimes|url',
                'config' => 'sometimes|array',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update API source
            $source->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'API source updated successfully',
                'data' => $source
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating API source: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update API source'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $source = ApiSource::findOrFail($id);
            $source->delete();

            return response()->json([
                'success' => true,
                'message' => 'API source deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting API source: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete API source'
            ], 500);
        }
    }

    public function test(Request $request)
    {
        try {
            // Log request data for debugging
            Log::info('API Source Test Request:', $request->all());

            // Validate request
            $validated = $request->validate([
                'url' => 'required|url',
                'jsonData' => 'required|string'
            ]);

            // Parse JSON data
            $jsonData = json_decode($validated['jsonData'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON data: ' . json_last_error_msg(),
                    'data' => null
                ], 400);
            }

            // Log parsed data for debugging
            Log::info('Parsed JSON data:', $jsonData);

            // Validate required fields
            $requiredFields = [
                'name', 'url', 'config', 'is_active'
            ];

            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($jsonData[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data structure: Missing required fields: ' . implode(', ', $missingFields),
                    'data' => null
                ], 400);
            }

            // Validate config structure
            $config = $jsonData['config'];
            $requiredConfigFields = [
                'original_name', 'poster_url', 'description', 'total_episodes',
                'current_episode', 'time', 'quality', 'language', 'director',
                'casts', 'category', 'episodes'
            ];

            $missingConfigFields = [];
            foreach ($requiredConfigFields as $field) {
                if (!isset($config[$field])) {
                    $missingConfigFields[] = $field;
                }
            }

            if (!empty($missingConfigFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid config structure: Missing required fields: ' . implode(', ', $missingConfigFields),
                    'data' => null
                ], 400);
            }

            // Validate category structure
            if (!is_array($config['category'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid category structure: Must be an array',
                    'data' => null
                ], 400);
            }

            // Validate each category group
            foreach ($config['category'] as $groupKey => $groupData) {
                if (!isset($groupData['group']) || !isset($groupData['list'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid category group structure at key: {$groupKey}",
                        'data' => null
                    ], 400);
                }

                // Validate group data
                if (!isset($groupData['group']['id']) || !isset($groupData['group']['name'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid group data at key: {$groupKey}",
                        'data' => null
                    ], 400);
                }

                // Validate category list
                if (!is_array($groupData['list'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid category list at key: {$groupKey}",
                        'data' => null
                    ], 400);
                }

                // Validate each category
                foreach ($groupData['list'] as $category) {
                    if (!isset($category['id']) || !isset($category['name'])) {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid category data in group: {$groupKey}",
                            'data' => null
                        ], 400);
                    }
                }
            }

            // Validate episodes structure
            if (!is_array($config['episodes'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid episodes structure: Must be an array',
                    'data' => null
                ], 400);
            }

            // Validate each episode
            foreach ($config['episodes'] as $episode) {
                if (!isset($episode['server_name']) || !isset($episode['items'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid episode structure: Missing server_name or items',
                        'data' => null
                    ], 400);
                }

                // Validate episode items
                if (!is_array($episode['items'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid episode items structure: Must be an array',
                        'data' => null
                    ], 400);
                }

                // Validate each item
                foreach ($episode['items'] as $item) {
                    $requiredItemFields = ['name', 'slug', 'embed', 'm3u8'];
                    $missingItemFields = [];
                    foreach ($requiredItemFields as $field) {
                        if (!isset($item[$field])) {
                            $missingItemFields[] = $field;
                        }
                    }

                    if (!empty($missingItemFields)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid episode item structure: Missing fields: ' . implode(', ', $missingItemFields),
                            'data' => null
                        ], 400);
                    }
                }
            }

            // Log successful validation
            Log::info('API source test successful', [
                'url' => $validated['url'],
                'name' => $jsonData['name']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'API source is valid',
                'data' => [
                    'url' => $validated['url'],
                    'name' => $jsonData['name'],
                    'config' => $config
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API source test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API source test failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

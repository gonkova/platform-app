<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    // Get all categories with tools (cached)
    public function index()
    {
        $cacheKey = 'categories_with_tools';

        $categories = Cache::remember($cacheKey, 60*60, function () {
            return Category::where('is_active', true)
                ->with(['aiTools' => function ($query) {
                    $query->where('is_active', true)
                          ->where('status', 'approved');
                }])
                ->withCount('aiTools')
                ->orderBy('name')
                ->get();
        });

        return response()->json($categories);
    }

    // Get single category from cached data if possible
    public function show($id)
    {
        $categories = Cache::get('categories_with_tools');

        if ($categories) {
            $category = $categories->firstWhere('id', $id);

            if ($category) {
                return response()->json($category);
            }
        }

        // Ако няма кеш или категорията не е намерена в кеша, зареждаме директно
        $category = Category::with(['aiTools' => function ($query) {
            $query->where('is_active', true)
                  ->where('status', 'approved');
        }])->findOrFail($id);

        return response()->json($category);
    }

    // Create new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);

        $category = Category::create($validated);

        // Clear cached categories
        Cache::forget('categories_with_tools');

        return response()->json([
            'message' => 'Категорията е създадена успешно',
            'category' => $category,
        ], 201);
    }

    // Update category
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        // Clear cached categories
        Cache::forget('categories_with_tools');

        return response()->json([
            'message' => 'Категорията е обновена успешно',
            'category' => $category,
        ]);
    }

    // Delete category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        // Clear cached categories
        Cache::forget('categories_with_tools');

        return response()->json([
            'message' => 'Категорията е изтрита успешно',
        ]);
    }
}

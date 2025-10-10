<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Get all categories
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount('aiTools')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    // Get single category with tools
    public function show($id)
    {
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

        return response()->json([
            'message' => 'Категорията е изтрита успешно',
        ]);
    }
}
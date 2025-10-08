<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use Illuminate\Http\Request;

class AiToolController extends Controller
{
    // Get all AI tools
    public function index(Request $request)
    {
        $query = AiTool::with(['categories', 'roles', 'creator'])
            ->where('is_active', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filter by role
        if ($request->has('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        // Filter by difficulty
        if ($request->has('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Filter free/paid
        if ($request->has('is_free')) {
            $query->where('is_free', $request->boolean('is_free'));
        }

        $tools = $query->orderBy('name')->get();

        return response()->json($tools);
    }

    // Get single tool
    public function show($id)
    {
        $tool = AiTool::with(['categories', 'roles', 'creator'])
            ->findOrFail($id);

        return response()->json($tool);
    }

    // Create new tool
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'required|url',
            'documentation_url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'logo_url' => 'nullable|url',
            'is_free' => 'required|boolean',
            'price' => 'nullable|numeric|min:0',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $tool = AiTool::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'url' => $validated['url'],
            'documentation_url' => $validated['documentation_url'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'logo_url' => $validated['logo_url'] ?? null,
            'is_free' => $validated['is_free'],
            'price' => $validated['price'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // Attach categories and roles
        $tool->categories()->attach($validated['category_ids']);
        $tool->roles()->attach($validated['role_ids']);

        $tool->load(['categories', 'roles']);

        return response()->json([
            'message' => 'AI инструментът е създаден успешно',
            'tool' => $tool,
        ], 201);
    }

    // Update tool
    public function update(Request $request, $id)
    {
        $tool = AiTool::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'url' => 'sometimes|url',
            'documentation_url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'difficulty_level' => 'sometimes|in:beginner,intermediate,advanced',
            'logo_url' => 'nullable|url',
            'is_free' => 'sometimes|boolean',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id',
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        // Update basic fields
        $tool->update(array_diff_key($validated, ['category_ids' => '', 'role_ids' => '']));

        // Update relationships if provided
        if (isset($validated['category_ids'])) {
            $tool->categories()->sync($validated['category_ids']);
        }

        if (isset($validated['role_ids'])) {
            $tool->roles()->sync($validated['role_ids']);
        }

        $tool->load(['categories', 'roles']);

        return response()->json([
            'message' => 'AI инструментът е обновен успешно',
            'tool' => $tool,
        ]);
    }

    // Delete tool
    public function destroy($id)
    {
        $tool = AiTool::findOrFail($id);
        $tool->delete();

        return response()->json([
            'message' => 'AI инструментът е изтрит успешно',
        ]);
    }
}
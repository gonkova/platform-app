<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use Illuminate\Http\Request;

class AiToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AiTool::with(['categories', 'roles', 'creator']);

        // Owner вижда всичко, останалите само approved
        if (auth()->user()->role->name !== 'owner') {
            $query->approved();
        }

        // Филтър по категория
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Филтър по роля
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        // Филтър по difficulty
        if ($request->has('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Филтър по статус (само за owner)
        if ($request->has('status') && auth()->user()->role->name === 'owner') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $aiTools = $query->latest()->paginate(12);

        return response()->json($aiTools);
    }

    // Get single tool
    public function show($id)
    {
        $tool = AiTool::with(['categories', 'roles', 'creator'])
            ->findOrFail($id);

        return response()->json($tool);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'required|url',
            'documentation_url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Създаваме tool-а с pending статус
        $aiTool = AiTool::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'url' => $validated['url'],
            'documentation_url' => $validated['documentation_url'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'is_active' => true,
            'created_by' => auth()->id(),
            'status' => 'pending', // Автоматично pending
        ]);

        // Attach categories
        if (isset($validated['categories'])) {
            $aiTool->categories()->attach($validated['categories']);
        }

        // Attach roles
        if (isset($validated['roles'])) {
            $aiTool->roles()->attach($validated['roles']);
        }

        // Load relationships
        $aiTool->load(['categories', 'roles', 'creator']);

        return response()->json([
            'message' => 'AI Tool created successfully and is pending approval',
            'data' => $aiTool
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
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Update basic fields
        $tool->update(array_diff_key($validated, ['categories' => '', 'roles' => '']));

        // Update relationships if provided
        if (isset($validated['categories'])) {
            $tool->categories()->sync($validated['categories']);
        }

        if (isset($validated['roles'])) {
            $tool->roles()->sync($validated['roles']);
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

    /**
     * Показва само pending tools (за admin)
     */
    public function pending()
    {
        $tools = AiTool::where('status', 'pending')
            ->with(['categories', 'roles', 'creator'])
            ->latest()
            ->paginate(20);

        return response()->json($tools);
    }

    /**
     * Одобрява tool
     */
    public function approve($id)
    {
        $tool = AiTool::findOrFail($id);
        $tool->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return response()->json([
            'message' => 'Tool approved successfully',
            'tool' => $tool
        ]);
    }

    /**
     * Отказва tool
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $tool = AiTool::findOrFail($id);
        $tool->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tool rejected successfully',
            'tool' => $tool
        ]);
    }

    /**
     * Bulk одобрение
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ai_tools,id'
        ]);

        AiTool::whereIn('id', $request->ids)
            ->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Tools approved successfully',
            'count' => count($request->ids)
        ]);
    }

    /**
     * Bulk отказ
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ai_tools,id'
        ]);

        AiTool::whereIn('id', $request->ids)
            ->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Tools rejected successfully',
            'count' => count($request->ids)
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class AiToolController extends Controller
{
    public function index(Request $request)
    {
        $query = AiTool::with(['categories', 'roles', 'creator']);

        if (auth()->user()->role->name !== 'owner') {
            $query->approved();
        }

        if ($request->has('categories') && !empty($request->categories)) {
            $categories = is_array($request->categories)
                ? $request->categories
                : explode(',', $request->categories);

            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('categories.id', $categories);
            });
        }

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        if ($request->has('roles') && !empty($request->roles)) {
            $roles = is_array($request->roles)
                ? $request->roles
                : explode(',', $request->roles);

            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('roles.id', $roles);
            });
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        if ($request->has('difficulty') && !empty($request->difficulty)) {
            $difficulties = is_array($request->difficulty)
                ? $request->difficulty
                : explode(',', $request->difficulty);

            $query->whereIn('difficulty_level', $difficulties);
        }

        if ($request->has('status') && auth()->user()->role->name === 'owner') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['created_at', 'name', 'difficulty_level', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = $request->get('per_page', 12);
        $perPage = min(max($perPage, 6), 50);

        $aiTools = $query->paginate($perPage);
        $stats = $this->getFilterStats($request);

        return response()->json([
            'data' => $aiTools->items(),
            'pagination' => [
                'total' => $aiTools->total(),
                'per_page' => $aiTools->perPage(),
                'current_page' => $aiTools->currentPage(),
                'last_page' => $aiTools->lastPage(),
                'from' => $aiTools->firstItem(),
                'to' => $aiTools->lastItem(),
            ],
            'stats' => $stats,
        ]);
    }

    private function getFilterStats(Request $request)
    {
        $baseQuery = AiTool::query();

        if (auth()->user()->role->name !== 'owner') {
            $baseQuery->approved();
        }

        return [
            'total_tools' => $baseQuery->count(),
            'by_difficulty' => [
                'beginner' => (clone $baseQuery)->where('difficulty_level', 'beginner')->count(),
                'intermediate' => (clone $baseQuery)->where('difficulty_level', 'intermediate')->count(),
                'advanced' => (clone $baseQuery)->where('difficulty_level', 'advanced')->count(),
            ],
        ];
    }

    public function show($id)
    {
        $tool = AiTool::with(['categories', 'roles', 'creator'])
            ->findOrFail($id);

        return response()->json($tool);
    }

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

        $aiTool = AiTool::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'url' => $validated['url'],
            'documentation_url' => $validated['documentation_url'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'is_active' => true,
            'created_by' => auth()->id(),
            'status' => 'pending',
        ]);

        if (isset($validated['categories'])) {
            $aiTool->categories()->attach($validated['categories']);
        }

        if (isset($validated['roles'])) {
            $aiTool->roles()->attach($validated['roles']);
        }

        $aiTool->load(['categories', 'roles', 'creator']);

        ActivityLogger::logCreated($aiTool, "Създаде AI Tool: {$aiTool->name}");

        return response()->json([
            'message' => 'AI Tool created successfully and is pending approval',
            'data' => $aiTool
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $tool = AiTool::findOrFail($id);
        $oldValues = $tool->only([
            'name', 'description', 'url', 'documentation_url',
            'video_url', 'difficulty_level', 'is_active'
        ]);

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

        $tool->update(array_diff_key($validated, ['categories' => '', 'roles' => '']));

        if (isset($validated['categories'])) {
            $tool->categories()->sync($validated['categories']);
        }

        if (isset($validated['roles'])) {
            $tool->roles()->sync($validated['roles']);
        }

        $tool->load(['categories', 'roles']);

        ActivityLogger::logUpdated($tool, $oldValues, "Обнови AI Tool: {$tool->name}");

        return response()->json([
            'message' => 'AI инструментът е обновен успешно',
            'tool' => $tool,
        ]);
    }

    public function destroy($id)
    {
        $tool = AiTool::findOrFail($id);
        $toolName = $tool->name;

        ActivityLogger::logDeleted($tool, "Изтри AI Tool: {$toolName}");

        $tool->delete();

        return response()->json([
            'message' => 'AI инструментът е изтрит успешно',
        ]);
    }

    public function approve($id)
    {
        $tool = AiTool::findOrFail($id);
        $tool->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        ActivityLogger::logApproved($tool, "Одобри AI Tool: {$tool->name}");

        return response()->json([
            'message' => 'Tool approved successfully',
            'tool' => $tool
        ]);
    }

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

        ActivityLogger::logRejected($tool, $validated['reason'], "Отказа AI Tool: {$tool->name}");

        return response()->json([
            'message' => 'Tool rejected successfully',
            'tool' => $tool
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ai_tools,id'
        ]);

        $tools = AiTool::whereIn('id', $request->ids)->get();

        AiTool::whereIn('id', $request->ids)
            ->update(['status' => 'approved']);

        foreach ($tools as $tool) {
            ActivityLogger::logApproved($tool, "Bulk одобрение на AI Tool: {$tool->name}");
        }

        return response()->json([
            'message' => 'Tools approved successfully',
            'count' => count($request->ids)
        ]);
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ai_tools,id'
        ]);

        $tools = AiTool::whereIn('id', $request->ids)->get();

        AiTool::whereIn('id', $request->ids)
            ->update(['status' => 'rejected']);

        foreach ($tools as $tool) {
            ActivityLogger::logRejected($tool, 'Bulk rejection', "Bulk отказ на AI Tool: {$tool->name}");
        }

        return response()->json([
            'message' => 'Tools rejected successfully',
            'count' => count($request->ids)
        ]);
    }

    public function pending()
    {
        $tools = AiTool::where('status', 'pending')
            ->with(['categories', 'roles', 'creator'])
            ->latest()
            ->paginate(20);

        return response()->json($tools);
    }
}

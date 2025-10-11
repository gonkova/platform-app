<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Get all activities (Owner only)
     */
    public function index(Request $request)
    {
        $query = Activity::with('user')->latest();

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        $activities = $query->paginate($request->get('per_page', 20));

        return response()->json($activities);
    }

    /**
     * Get activities for current user
     */
    public function myActivities(Request $request)
    {
        $query = Activity::where('user_id', auth()->id())
            ->latest();

        $activities = $query->paginate($request->get('per_page', 20));

        return response()->json($activities);
    }

    /**
     * Get activity statistics
     */
    /**
 * Get activity statistics
 */
public function stats()
{
    $stats = [
        'total_activities' => Activity::count(),
        'today' => Activity::whereDate('created_at', today())->count(),
        'this_week' => Activity::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count(),
        'by_action' => Activity::selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action'),
        'by_user' => Activity::with('user')
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->filter(function ($activity) {
                return $activity->user !== null;
            })
            ->map(function ($activity) {
                return [
                    'user' => $activity->user->name,
                    'count' => $activity->count
                ];
            })
            ->values(),
    ];

    return response()->json($stats);
}

    /**
     * Get single activity
     */
    public function show($id)
    {
        $activity = Activity::with('user')->findOrFail($id);

        return response()->json($activity);
    }
}
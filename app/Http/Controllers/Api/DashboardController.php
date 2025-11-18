<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Models\UserDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    /**
     * Display a listing of the user's dashboards.
     */
    public function index(Request $request): JsonResponse
    {
        $dashboards = UserDashboard::where('user_id', $request->user()->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dashboards,
        ]);
    }

    /**
     * Store a newly created dashboard.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('user_dashboards')->where('user_id', $request->user()->id),
            ],
            'widgets' => 'nullable|array',
            'widgets.*.id' => 'required|string',
            'widgets.*.widget_id' => 'required|integer|exists:dashboard_widgets,id',
            'widgets.*.position' => 'required|array',
            'widgets.*.position.x' => 'required|integer|min:0',
            'widgets.*.position.y' => 'required|integer|min:0',
            'widgets.*.position.w' => 'required|integer|min:1',
            'widgets.*.position.h' => 'required|integer|min:1',
            'widgets.*.filters' => 'nullable|array',
            'layout' => 'nullable|array',
            'layout.columns' => 'nullable|integer|min:1|max:24',
            'layout.row_height' => 'nullable|integer|min:10',
            'layout.compact_type' => 'nullable|string|in:vertical,horizontal,null',
            'is_default' => 'nullable|boolean',
            'is_shared' => 'nullable|boolean',
        ]);

        // Generate slug if not provided
        if (! isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique for this user
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (UserDashboard::where('user_id', $request->user()->id)
            ->where('slug', $validated['slug'])
            ->exists()) {
            $validated['slug'] = $baseSlug.'-'.$counter;
            $counter++;
        }

        $validated['user_id'] = $request->user()->id;

        $dashboard = UserDashboard::create($validated);

        // If this is marked as default, update other dashboards
        if ($validated['is_default'] ?? false) {
            $dashboard->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'data' => $dashboard,
            'message' => 'Dashboard created successfully',
        ], 201);
    }

    /**
     * Display the specified dashboard with data.
     */
    public function show(Request $request, UserDashboard $dashboard): JsonResponse
    {
        // Check if user owns this dashboard or if it's shared
        if ($dashboard->user_id !== $request->user()->id && ! $dashboard->is_shared) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this dashboard',
            ], 403);
        }

        // Load widget data for each widget in the dashboard
        $widgets = $dashboard->widgets ?? [];
        $widgetsWithData = [];

        foreach ($widgets as $widgetConfig) {
            $widget = DashboardWidget::find($widgetConfig['widget_id']);
            if ($widget) {
                $filters = $widgetConfig['filters'] ?? [];
                $widgetData = $widget->getData($filters);

                $widgetsWithData[] = array_merge($widgetConfig, [
                    'widget_info' => [
                        'name' => $widget->name,
                        'type' => $widget->type,
                        'category' => $widget->category,
                        'description' => $widget->description,
                    ],
                    'data' => $widgetData,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'dashboard' => $dashboard,
                'widgets' => $widgetsWithData,
            ],
        ]);
    }

    /**
     * Update the specified dashboard.
     */
    public function update(Request $request, UserDashboard $dashboard): JsonResponse
    {
        // Check if user owns this dashboard
        if ($dashboard->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this dashboard',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('user_dashboards')
                    ->where('user_id', $request->user()->id)
                    ->ignore($dashboard->id),
            ],
            'widgets' => 'sometimes|array',
            'widgets.*.id' => 'required|string',
            'widgets.*.widget_id' => 'required|integer|exists:dashboard_widgets,id',
            'widgets.*.position' => 'required|array',
            'widgets.*.position.x' => 'required|integer|min:0',
            'widgets.*.position.y' => 'required|integer|min:0',
            'widgets.*.position.w' => 'required|integer|min:1',
            'widgets.*.position.h' => 'required|integer|min:1',
            'widgets.*.filters' => 'nullable|array',
            'layout' => 'sometimes|array',
            'layout.columns' => 'nullable|integer|min:1|max:24',
            'layout.row_height' => 'nullable|integer|min:10',
            'layout.compact_type' => 'nullable|string|in:vertical,horizontal,null',
            'is_default' => 'sometimes|boolean',
            'is_shared' => 'sometimes|boolean',
        ]);

        $dashboard->update($validated);

        // If this is marked as default, update other dashboards
        if (isset($validated['is_default']) && $validated['is_default']) {
            $dashboard->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'data' => $dashboard->fresh(),
            'message' => 'Dashboard updated successfully',
        ]);
    }

    /**
     * Remove the specified dashboard.
     */
    public function destroy(Request $request, UserDashboard $dashboard): JsonResponse
    {
        // Check if user owns this dashboard
        if ($dashboard->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this dashboard',
            ], 403);
        }

        // Don't allow deleting the last dashboard
        $userDashboardCount = UserDashboard::where('user_id', $request->user()->id)->count();
        if ($userDashboardCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your last dashboard',
            ], 422);
        }

        // If this was the default dashboard, set another as default
        $wasDefault = $dashboard->is_default;
        $dashboard->delete();

        if ($wasDefault) {
            $newDefault = UserDashboard::where('user_id', $request->user()->id)->first();
            if ($newDefault) {
                $newDefault->setAsDefault();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard deleted successfully',
        ]);
    }

    /**
     * Set the specified dashboard as default.
     */
    public function setDefault(Request $request, UserDashboard $dashboard): JsonResponse
    {
        // Check if user owns this dashboard
        if ($dashboard->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this dashboard',
            ], 403);
        }

        $dashboard->setAsDefault();

        return response()->json([
            'success' => true,
            'data' => $dashboard->fresh(),
            'message' => 'Dashboard set as default',
        ]);
    }

    /**
     * Share or unshare the specified dashboard.
     */
    public function share(Request $request, UserDashboard $dashboard): JsonResponse
    {
        // Check if user owns this dashboard
        if ($dashboard->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this dashboard',
            ], 403);
        }

        $validated = $request->validate([
            'is_shared' => 'required|boolean',
        ]);

        $dashboard->update(['is_shared' => $validated['is_shared']]);

        $message = $validated['is_shared']
            ? 'Dashboard is now shared with your team'
            : 'Dashboard is now private';

        return response()->json([
            'success' => true,
            'data' => $dashboard->fresh(),
            'message' => $message,
        ]);
    }

    /**
     * Get data for a specific widget.
     */
    public function widgetData(Request $request, UserDashboard $dashboard, string $widgetId): JsonResponse
    {
        // Check if user owns this dashboard or if it's shared
        if ($dashboard->user_id !== $request->user()->id && ! $dashboard->is_shared) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this dashboard',
            ], 403);
        }

        // Find the widget configuration in the dashboard
        $widgets = $dashboard->widgets ?? [];
        $widgetConfig = collect($widgets)->firstWhere('id', $widgetId);

        if (! $widgetConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found in this dashboard',
            ], 404);
        }

        // Get the widget template
        $widget = DashboardWidget::find($widgetConfig['widget_id']);
        if (! $widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget template not found',
            ], 404);
        }

        // Get filters from request or use widget config
        $filters = $request->input('filters', $widgetConfig['filters'] ?? []);

        // Fetch widget data
        $widgetData = $widget->getData($filters);

        return response()->json([
            'success' => true,
            'data' => [
                'widget_id' => $widgetId,
                'widget_info' => [
                    'name' => $widget->name,
                    'type' => $widget->type,
                    'category' => $widget->category,
                    'description' => $widget->description,
                ],
                'data' => $widgetData,
            ],
        ]);
    }

    /**
     * Get shared dashboards from the team.
     */
    public function shared(Request $request): JsonResponse
    {
        $sharedDashboards = UserDashboard::where('is_shared', true)
            ->where('user_id', '!=', $request->user()->id)
            ->with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sharedDashboards,
        ]);
    }
}

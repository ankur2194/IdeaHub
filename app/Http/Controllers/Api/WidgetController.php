<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WidgetController extends Controller
{
    /**
     * Display a listing of available widget templates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DashboardWidget::query();

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Show system widgets and custom widgets for the tenant
        $query->where(function ($q) {
            $q->where('is_system', true)
              ->orWhereNotNull('tenant_id');
        });

        $widgets = $query->orderBy('is_system', 'desc')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Group by category for easier frontend consumption
        $grouped = $widgets->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => [
                'widgets' => $widgets,
                'grouped' => $grouped,
                'types' => DashboardWidget::TYPES,
                'categories' => DashboardWidget::CATEGORIES,
            ],
        ]);
    }

    /**
     * Store a newly created widget template (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can create widget templates.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_keys(DashboardWidget::TYPES))],
            'category' => ['required', Rule::in(array_keys(DashboardWidget::CATEGORIES))],
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'config.status' => 'nullable|string',
            'config.metric' => 'nullable|string',
            'config.aggregation' => 'nullable|string|in:count,sum,avg,min,max',
            'config.time_range' => 'nullable|string|in:7d,30d,90d,1y,all',
            'config.limit' => 'nullable|integer|min:1|max:100',
            'is_system' => 'nullable|boolean',
        ]);

        $widget = DashboardWidget::create($validated);

        return response()->json([
            'success' => true,
            'data' => $widget,
            'message' => 'Widget template created successfully',
        ], 201);
    }

    /**
     * Display the specified widget template.
     */
    public function show(DashboardWidget $widget): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $widget,
        ]);
    }

    /**
     * Update the specified widget template (admin only).
     */
    public function update(Request $request, DashboardWidget $widget): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can update widget templates.',
            ], 403);
        }

        // System widgets cannot be modified
        if ($widget->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System widgets cannot be modified.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => ['sometimes', Rule::in(array_keys(DashboardWidget::TYPES))],
            'category' => ['sometimes', Rule::in(array_keys(DashboardWidget::CATEGORIES))],
            'description' => 'nullable|string',
            'config' => 'sometimes|array',
            'config.status' => 'nullable|string',
            'config.metric' => 'nullable|string',
            'config.aggregation' => 'nullable|string|in:count,sum,avg,min,max',
            'config.time_range' => 'nullable|string|in:7d,30d,90d,1y,all',
            'config.limit' => 'nullable|integer|min:1|max:100',
        ]);

        $widget->update($validated);

        return response()->json([
            'success' => true,
            'data' => $widget->fresh(),
            'message' => 'Widget template updated successfully',
        ]);
    }

    /**
     * Remove the specified widget template (admin only).
     */
    public function destroy(Request $request, DashboardWidget $widget): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can delete widget templates.',
            ], 403);
        }

        // System widgets cannot be deleted
        if ($widget->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System widgets cannot be deleted.',
            ], 422);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget template deleted successfully',
        ]);
    }

    /**
     * Preview a widget with sample or real data.
     */
    public function preview(Request $request, DashboardWidget $widget): JsonResponse
    {
        $validated = $request->validate([
            'filters' => 'nullable|array',
            'use_sample_data' => 'nullable|boolean',
        ]);

        $filters = $validated['filters'] ?? [];
        $useSampleData = $validated['use_sample_data'] ?? false;

        if ($useSampleData) {
            // Return sample data based on widget type
            $sampleData = $this->generateSampleData($widget->type);
        } else {
            // Fetch real data
            $sampleData = $widget->getData($filters);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'widget' => $widget,
                'preview_data' => $sampleData,
            ],
        ]);
    }

    /**
     * Generate sample data for widget preview.
     */
    protected function generateSampleData(string $type): array
    {
        return match($type) {
            'stats_card' => [
                'count' => rand(100, 1000),
                'label' => 'Sample Metric',
                'change' => '+' . rand(5, 25) . '%',
            ],
            'bar', 'line', 'area' => [
                'data' => collect(range(1, 7))->map(fn($i) => [
                    'date' => now()->subDays(7 - $i)->format('Y-m-d'),
                    'count' => rand(10, 100),
                ])->toArray(),
            ],
            'pie' => [
                'data' => [
                    ['status' => 'pending', 'count' => rand(10, 50)],
                    ['status' => 'approved', 'count' => rand(30, 80)],
                    ['status' => 'rejected', 'count' => rand(5, 20)],
                    ['status' => 'implemented', 'count' => rand(15, 40)],
                ],
            ],
            'table', 'list' => [
                'data' => collect(range(1, 5))->map(fn($i) => [
                    'id' => $i,
                    'title' => 'Sample Item ' . $i,
                    'value' => rand(100, 1000),
                    'status' => collect(['pending', 'approved', 'rejected'])->random(),
                    'created_at' => now()->subDays(rand(1, 30))->toIso8601String(),
                ])->toArray(),
            ],
            default => ['message' => 'No sample data available for this widget type'],
        };
    }

    /**
     * Get widget types and categories metadata.
     */
    public function metadata(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'types' => DashboardWidget::TYPES,
                'categories' => DashboardWidget::CATEGORIES,
            ],
        ]);
    }
}

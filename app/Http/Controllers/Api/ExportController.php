<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export analytics report as PDF
     */
    public function exportAnalyticsPDF(Request $request)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        return $this->exportService->exportAnalyticsPDF($filters);
    }

    /**
     * Export analytics report as CSV
     */
    public function exportAnalyticsCSV(Request $request)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        return $this->exportService->exportAnalyticsCSV($filters);
    }

    /**
     * Export ideas list as CSV
     */
    public function exportIdeasCSV(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:draft,pending,approved,rejected,implemented',
            'category_id' => 'nullable|exists:categories,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        return $this->exportService->exportIdeasCSV($filters);
    }

    /**
     * Export users list as CSV
     */
    public function exportUsersCSV(Request $request)
    {
        $filters = $request->validate([
            'role' => 'nullable|in:user,department_head,admin',
            'is_active' => 'nullable|boolean',
        ]);

        return $this->exportService->exportUsersCSV($filters);
    }
}

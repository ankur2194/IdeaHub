<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export analytics report as PDF
     */
    public function exportAnalyticsPDF(array $filters = []): \Illuminate\Http\Response
    {
        $data = $this->getAnalyticsData($filters);

        $pdf = Pdf::loadView('exports.analytics-pdf', [
            'data' => $data,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        return $pdf->download('analytics-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export analytics report as CSV
     */
    public function exportAnalyticsCSV(array $filters = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->getAnalyticsData($filters);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics-report-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($data) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, ['Analytics Report - Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []); // Empty line

            // Overview section
            fputcsv($file, ['Overview']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Ideas', $data['overview']['total_ideas']]);
            fputcsv($file, ['Approved Ideas', $data['overview']['approved_ideas']]);
            fputcsv($file, ['Pending Ideas', $data['overview']['pending_ideas']]);
            fputcsv($file, ['Rejected Ideas', $data['overview']['rejected_ideas']]);
            fputcsv($file, ['Total Users', $data['overview']['total_users']]);
            fputcsv($file, ['Total Comments', $data['overview']['total_comments']]);
            fputcsv($file, ['Approval Rate', $data['overview']['approval_rate'] . '%']);
            fputcsv($file, []); // Empty line

            // Ideas by category
            fputcsv($file, ['Ideas by Category']);
            fputcsv($file, ['Category', 'Count']);
            foreach ($data['ideas_by_category'] as $item) {
                fputcsv($file, [$item['category'] ?? 'Uncategorized', $item['count']]);
            }
            fputcsv($file, []); // Empty line

            // Ideas by status
            fputcsv($file, ['Ideas by Status']);
            fputcsv($file, ['Status', 'Count']);
            foreach ($data['ideas_by_status'] as $item) {
                fputcsv($file, [$item['status'], $item['count']]);
            }
            fputcsv($file, []); // Empty line

            // Top contributors
            fputcsv($file, ['Top Contributors']);
            fputcsv($file, ['User', 'Ideas Submitted', 'Ideas Approved', 'Comments', 'Total Points']);
            foreach ($data['top_contributors'] as $user) {
                fputcsv($file, [
                    $user['name'],
                    $user['ideas_submitted'],
                    $user['ideas_approved'],
                    $user['comments_posted'],
                    $user['points'],
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Export ideas list as CSV
     */
    public function exportIdeasCSV(array $filters = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Idea::with(['user', 'category']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $ideas = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ideas-export-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($ideas) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, ['ID', 'Title', 'Author', 'Category', 'Status', 'Likes', 'Comments', 'Created At', 'Updated At']);

            // Write data
            foreach ($ideas as $idea) {
                fputcsv($file, [
                    $idea->id,
                    $idea->title,
                    $idea->user->name,
                    $idea->category->name ?? 'N/A',
                    $idea->status,
                    $idea->likes_count,
                    $idea->comments_count,
                    $idea->created_at->format('Y-m-d H:i:s'),
                    $idea->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Export users list as CSV
     */
    public function exportUsersCSV(array $filters = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = User::query();

        // Apply filters
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-export-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($users) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Role', 'Department', 'Active',
                'Level', 'Points', 'Ideas Submitted', 'Ideas Approved',
                'Comments Posted', 'Total Badges', 'Created At'
            ]);

            // Write data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->department,
                    $user->is_active ? 'Yes' : 'No',
                    $user->level,
                    $user->points,
                    $user->ideas_submitted,
                    $user->ideas_approved,
                    $user->comments_posted,
                    $user->total_badges,
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Get analytics data for reports
     */
    protected function getAnalyticsData(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subMonths(3)->startOfDay();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfDay();

        // Overview metrics
        $totalIdeas = Idea::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $approvedIdeas = Idea::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'approved')->count();
        $pendingIdeas = Idea::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'pending')->count();
        $rejectedIdeas = Idea::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'rejected')->count();

        $approvalRate = $totalIdeas > 0 ? round(($approvedIdeas / $totalIdeas) * 100, 2) : 0;

        // Ideas by category
        $ideasByCategory = DB::table('ideas')
            ->leftJoin('categories', 'ideas.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, COUNT(*) as count')
            ->whereBetween('ideas.created_at', [$dateFrom, $dateTo])
            ->groupBy('categories.name')
            ->get();

        // Ideas by status
        $ideasByStatus = DB::table('ideas')
            ->selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();

        // Top contributors
        $topContributors = User::select([
            'id', 'name', 'points', 'level',
            'ideas_submitted', 'ideas_approved', 'comments_posted'
        ])
            ->where('ideas_submitted', '>', 0)
            ->orderBy('points', 'desc')
            ->limit(10)
            ->get();

        return [
            'overview' => [
                'total_ideas' => $totalIdeas,
                'approved_ideas' => $approvedIdeas,
                'pending_ideas' => $pendingIdeas,
                'rejected_ideas' => $rejectedIdeas,
                'total_users' => User::count(),
                'total_comments' => DB::table('comments')->count(),
                'approval_rate' => $approvalRate,
            ],
            'ideas_by_category' => $ideasByCategory,
            'ideas_by_status' => $ideasByStatus,
            'top_contributors' => $topContributors,
            'date_range' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
        ];
    }
}

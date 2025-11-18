<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard overview statistics
     */
    public function overview(): JsonResponse
    {
        $totalIdeas = Idea::count();
        $totalUsers = User::where('is_active', true)->count();
        $totalComments = Comment::count();
        $pendingIdeas = Idea::where('status', 'pending')->count();
        $approvedIdeas = Idea::where('status', 'approved')->count();
        $implementedIdeas = Idea::where('status', 'implemented')->count();

        // Calculate this month's stats
        $thisMonthIdeas = Idea::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $lastMonthIdeas = Idea::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $ideasGrowth = $lastMonthIdeas > 0
            ? round((($thisMonthIdeas - $lastMonthIdeas) / $lastMonthIdeas) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_ideas' => $totalIdeas,
                'total_users' => $totalUsers,
                'total_comments' => $totalComments,
                'pending_ideas' => $pendingIdeas,
                'approved_ideas' => $approvedIdeas,
                'implemented_ideas' => $implementedIdeas,
                'this_month_ideas' => $thisMonthIdeas,
                'ideas_growth_percentage' => $ideasGrowth,
            ],
        ]);
    }

    /**
     * Get ideas trend over time
     */
    public function ideasTrend(Request $request): JsonResponse
    {
        $period = $request->get('period', '30days'); // 7days, 30days, 90days, 1year

        $startDate = match ($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '90days' => Carbon::now()->subDays(90),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };

        $groupBy = match ($period) {
            '7days' => 'DATE(created_at)',
            '30days' => 'DATE(created_at)',
            '90days' => 'DATE(created_at)',
            '1year' => 'YEAR(created_at), MONTH(created_at)',
            default => 'DATE(created_at)',
        };

        $ideas = Idea::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            'status'
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date', 'status')
            ->orderBy('date', 'asc')
            ->get();

        // Transform data for frontend charting
        $data = [];
        $dates = [];

        foreach ($ideas as $idea) {
            $date = Carbon::parse($idea->date)->format('Y-m-d');
            if (! in_array($date, $dates)) {
                $dates[] = $date;
            }

            if (! isset($data[$idea->status])) {
                $data[$idea->status] = [];
            }
            $data[$idea->status][$date] = $idea->count;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'dates' => $dates,
                'series' => $data,
            ],
        ]);
    }

    /**
     * Get category distribution
     */
    public function categoryDistribution(): JsonResponse
    {
        $categories = Category::withCount('ideas')
            ->having('ideas_count', '>', 0)
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'value' => $category->ideas_count,
                    'color' => $category->color,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get status breakdown
     */
    public function statusBreakdown(): JsonResponse
    {
        $statuses = Idea::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'label' => ucwords(str_replace('_', ' ', $item->status)),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    /**
     * Get leaderboard (top contributors)
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $topContributors = User::where('is_active', true)
            ->orderBy('points', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'avatar', 'department', 'job_title', 'points'])
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'department' => $user->department,
                    'job_title' => $user->job_title,
                    'points' => $user->points,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topContributors,
        ]);
    }

    /**
     * Get department participation
     */
    public function departmentStats(): JsonResponse
    {
        $departments = Idea::select('users.department', DB::raw('COUNT(ideas.id) as ideas_count'))
            ->join('users', 'ideas.user_id', '=', 'users.id')
            ->whereNotNull('users.department')
            ->groupBy('users.department')
            ->orderBy('ideas_count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department,
                    'ideas_count' => $item->ideas_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $departments,
        ]);
    }

    /**
     * Get recent activity
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $recentIdeas = Idea::with(['user', 'category'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($idea) {
                return [
                    'type' => 'idea',
                    'id' => $idea->id,
                    'title' => $idea->title,
                    'user' => [
                        'name' => $idea->is_anonymous ? 'Anonymous' : $idea->user->name,
                        'avatar' => $idea->is_anonymous ? null : $idea->user->avatar,
                    ],
                    'category' => $idea->category?->name,
                    'status' => $idea->status,
                    'created_at' => $idea->created_at->toIso8601String(),
                    'timestamp' => $idea->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recentIdeas,
        ]);
    }

    /**
     * Get user statistics
     */
    public function userStats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $myIdeas = Idea::where('user_id', $userId)->count();
        $myComments = Comment::where('user_id', $userId)->count();
        $myPoints = $request->user()->points;
        $myApprovedIdeas = Idea::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();
        $myImplementedIdeas = Idea::where('user_id', $userId)
            ->where('status', 'implemented')
            ->count();

        // Get user rank
        $rank = User::where('is_active', true)
            ->where('points', '>', $myPoints)
            ->count() + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'total_ideas' => $myIdeas,
                'total_comments' => $myComments,
                'total_points' => $myPoints,
                'approved_ideas' => $myApprovedIdeas,
                'implemented_ideas' => $myImplementedIdeas,
                'rank' => $rank,
            ],
        ]);
    }
}

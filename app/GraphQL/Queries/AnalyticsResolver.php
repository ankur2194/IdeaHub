<?php

namespace App\GraphQL\Queries;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsResolver
{
    /**
     * Get dashboard analytics.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function dashboard($_, array $args)
    {
        $startDate = $args['start_date'] ?? now()->subDays(30);
        $endDate = $args['end_date'] ?? now();

        // Total counts
        $totalIdeas = Idea::count();
        $totalUsers = User::count();
        $totalComments = Comment::count();

        // Status counts
        $pendingIdeas = Idea::where('status', 'pending')->count();
        $approvedIdeas = Idea::where('status', 'approved')->count();
        $implementedIdeas = Idea::where('status', 'implemented')->count();

        // Time period stats
        $ideasCreated = Idea::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeUsers = User::whereHas('ideas', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->orWhereHas('comments', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Engagement rate (users who have posted ideas or comments / total users)
        $engagementRate = $totalUsers > 0
            ? ($activeUsers / $totalUsers) * 100
            : 0;

        // Ideas by category
        $ideasByCategory = Category::withCount('ideas')
            ->get()
            ->map(function ($category) use ($totalIdeas) {
                return [
                    'category' => $category->name,
                    'count' => $category->ideas_count,
                    'percentage' => $totalIdeas > 0 ? ($category->ideas_count / $totalIdeas) * 100 : 0,
                ];
            })
            ->toArray();

        // Ideas by status
        $ideasByStatus = Idea::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($stat) use ($totalIdeas) {
                return [
                    'status' => $stat->status,
                    'count' => $stat->count,
                    'percentage' => $totalIdeas > 0 ? ($stat->count / $totalIdeas) * 100 : 0,
                ];
            })
            ->toArray();

        // Recent activity (last 10 activities)
        $recentActivity = $this->getRecentActivity(10);

        return [
            'total_ideas' => $totalIdeas,
            'total_users' => $totalUsers,
            'total_comments' => $totalComments,
            'pending_ideas' => $pendingIdeas,
            'approved_ideas' => $approvedIdeas,
            'implemented_ideas' => $implementedIdeas,
            'ideas_created' => $ideasCreated,
            'active_users' => $activeUsers,
            'engagement_rate' => $engagementRate,
            'ideas_by_category' => $ideasByCategory,
            'ideas_by_status' => $ideasByStatus,
            'recent_activity' => $recentActivity,
        ];
    }

    /**
     * Get idea statistics.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function ideaStats($_, array $args)
    {
        $total = Idea::count();
        $drafts = Idea::where('status', 'draft')->count();
        $pending = Idea::where('status', 'pending')->count();
        $approved = Idea::where('status', 'approved')->count();
        $rejected = Idea::where('status', 'rejected')->count();
        $implemented = Idea::where('status', 'implemented')->count();

        // Average time to approval (in days)
        $avgApprovalTime = Idea::whereNotNull('approved_at')
            ->whereNotNull('submitted_at')
            ->get()
            ->avg(function ($idea) {
                return $idea->submitted_at->diffInDays($idea->approved_at);
            });

        // Implementation rate
        $implementationRate = $approved > 0
            ? ($implemented / $approved) * 100
            : 0;

        return [
            'total' => $total,
            'drafts' => $drafts,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'implemented' => $implemented,
            'avg_approval_time' => $avgApprovalTime,
            'implementation_rate' => $implementationRate,
        ];
    }

    /**
     * Get user engagement statistics.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function userEngagement($_, array $args)
    {
        $startDate = $args['start_date'] ?? now()->subDays(30);
        $endDate = $args['end_date'] ?? now();

        // Active users (users with ideas or comments in the period)
        $activeUsers = User::whereHas('ideas', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->orWhereHas('comments', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Total stats
        $ideasSubmitted = Idea::whereBetween('created_at', [$startDate, $endDate])->count();
        $commentsPosted = Comment::whereBetween('created_at', [$startDate, $endDate])->count();
        $likesGiven = DB::table('idea_likes')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Averages
        $totalUsers = User::count();
        $avgIdeasPerUser = $totalUsers > 0 ? $ideasSubmitted / $totalUsers : 0;
        $totalIdeas = Idea::count();
        $avgCommentsPerIdea = $totalIdeas > 0 ? Comment::count() / $totalIdeas : 0;

        // Engagement by department
        $byDepartment = User::select('department', DB::raw('count(*) as users'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->get()
            ->map(function ($dept) {
                $deptUsers = User::where('department', $dept->department);
                $deptIdeas = Idea::whereIn('user_id', $deptUsers->pluck('id'))->count();

                // Engagement score: ideas per user in department
                $engagementScore = $dept->users > 0 ? $deptIdeas / $dept->users : 0;

                return [
                    'department' => $dept->department,
                    'users' => $dept->users,
                    'ideas' => $deptIdeas,
                    'engagement_score' => $engagementScore,
                ];
            })
            ->toArray();

        return [
            'active_users' => $activeUsers,
            'ideas_submitted' => $ideasSubmitted,
            'comments_posted' => $commentsPosted,
            'likes_given' => $likesGiven,
            'avg_ideas_per_user' => $avgIdeasPerUser,
            'avg_comments_per_idea' => $avgCommentsPerIdea,
            'by_department' => $byDepartment,
        ];
    }

    /**
     * Get recent activity.
     *
     * @param  int  $limit
     * @return array
     */
    protected function getRecentActivity($limit = 10)
    {
        $ideas = Idea::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($idea) {
                return [
                    'type' => 'idea_created',
                    'description' => "New idea: {$idea->title}",
                    'user' => $idea->user,
                    'idea' => $idea,
                    'timestamp' => $idea->created_at,
                ];
            });

        $comments = Comment::with(['user', 'idea'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment_posted',
                    'description' => "Comment on: {$comment->idea->title}",
                    'user' => $comment->user,
                    'idea' => $comment->idea,
                    'timestamp' => $comment->created_at,
                ];
            });

        return $ideas->concat($comments)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->toArray();
    }
}

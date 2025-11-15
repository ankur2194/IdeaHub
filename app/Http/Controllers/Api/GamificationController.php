<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    /**
     * Get gamification stats for the authenticated user.
     */
    public function myStats(GamificationService $gamificationService)
    {
        $user = Auth::user();
        $stats = $gamificationService->getUserStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get gamification stats for a specific user.
     */
    public function userStats(User $user, GamificationService $gamificationService)
    {
        $stats = $gamificationService->getUserStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get leaderboard by level.
     */
    public function leaderboard(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        $leaderboard = User::where('is_active', true)
            ->orderBy('level', 'desc')
            ->orderBy('experience', 'desc')
            ->orderBy('points', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get(['id', 'name', 'avatar', 'department', 'level', 'experience', 'title', 'points', 'total_badges']);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get level rankings.
     */
    public function levelRankings()
    {
        $rankings = [
            ['level' => 1, 'title' => 'Newcomer', 'min_level' => 1],
            ['level' => 5, 'title' => 'Rising Star', 'min_level' => 5],
            ['level' => 10, 'title' => 'Active Contributor', 'min_level' => 10],
            ['level' => 20, 'title' => 'Senior Contributor', 'min_level' => 20],
            ['level' => 30, 'title' => 'Expert Innovator', 'min_level' => 30],
            ['level' => 40, 'title' => 'Visionary Leader', 'min_level' => 40],
            ['level' => 50, 'title' => 'Innovation Master', 'min_level' => 50],
        ];

        return response()->json([
            'success' => true,
            'data' => $rankings,
        ]);
    }

    /**
     * Get recent achievements/level ups.
     */
    public function recentAchievements(Request $request)
    {
        $limit = $request->get('limit', 10);

        // Get recent badge earns
        $recentBadges = \DB::table('user_badges')
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->join('users', 'user_badges.user_id', '=', 'users.id')
            ->select([
                'users.id as user_id',
                'users.name as user_name',
                'users.avatar as user_avatar',
                'badges.id as badge_id',
                'badges.name as badge_name',
                'badges.icon as badge_icon',
                'badges.rarity as badge_rarity',
                'user_badges.earned_at',
            ])
            ->orderBy('user_badges.earned_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentBadges,
        ]);
    }

    /**
     * Get XP breakdown for authenticated user.
     */
    public function xpBreakdown()
    {
        $user = Auth::user();

        $breakdown = [
            [
                'action' => 'Idea Submitted',
                'xp' => GamificationService::XP_IDEA_SUBMITTED,
                'count' => $user->ideas_submitted,
                'total_xp' => GamificationService::XP_IDEA_SUBMITTED * $user->ideas_submitted,
            ],
            [
                'action' => 'Idea Approved',
                'xp' => GamificationService::XP_IDEA_APPROVED,
                'count' => $user->ideas_approved,
                'total_xp' => GamificationService::XP_IDEA_APPROVED * $user->ideas_approved,
            ],
            [
                'action' => 'Comment Posted',
                'xp' => GamificationService::XP_COMMENT_CREATED,
                'count' => $user->comments_posted,
                'total_xp' => GamificationService::XP_COMMENT_CREATED * $user->comments_posted,
            ],
            [
                'action' => 'Like Received',
                'xp' => GamificationService::XP_LIKE_RECEIVED,
                'count' => $user->likes_received,
                'total_xp' => GamificationService::XP_LIKE_RECEIVED * $user->likes_received,
            ],
            [
                'action' => 'Like Given',
                'xp' => GamificationService::XP_LIKE_GIVEN,
                'count' => $user->likes_given,
                'total_xp' => GamificationService::XP_LIKE_GIVEN * $user->likes_given,
            ],
        ];

        $totalXpEarned = collect($breakdown)->sum('total_xp');

        return response()->json([
            'success' => true,
            'data' => [
                'breakdown' => $breakdown,
                'total_xp_earned' => $totalXpEarned,
                'current_level' => $user->level,
                'current_xp' => $user->experience,
                'next_level_xp' => $user->getXpForNextLevel(),
            ],
        ]);
    }
}

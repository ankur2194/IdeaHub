<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgeController extends Controller
{
    /**
     * Display a listing of all badges.
     */
    public function index(Request $request)
    {
        $query = Badge::active()->ordered();

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->inCategory($request->category);
        }

        // Filter by rarity
        if ($request->has('rarity')) {
            $query->byRarity($request->rarity);
        }

        $badges = $query->get();

        return response()->json([
            'success' => true,
            'data' => $badges,
        ]);
    }

    /**
     * Display the specified badge.
     */
    public function show(Badge $badge)
    {
        $badge->loadCount('users');

        return response()->json([
            'success' => true,
            'data' => $badge,
        ]);
    }

    /**
     * Get badges earned by a specific user.
     */
    public function userBadges(User $user)
    {
        $badges = $user->badges()
            ->orderByPivot('earned_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $badges,
        ]);
    }

    /**
     * Get badges earned by the authenticated user.
     */
    public function myBadges()
    {
        $user = Auth::user();
        $badges = $user->badges()
            ->orderByPivot('earned_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $badges,
        ]);
    }

    /**
     * Get badge progress for the authenticated user.
     */
    public function progress()
    {
        $user = Auth::user();
        $allBadges = Badge::active()->ordered()->get();

        $progress = $allBadges->map(function ($badge) use ($user) {
            $earned = $user->badges()->where('badge_id', $badge->id)->exists();
            $userBadge = $user->badges()->where('badge_id', $badge->id)->first();

            return [
                'badge' => $badge,
                'earned' => $earned,
                'earned_at' => $earned ? $userBadge->pivot->earned_at : null,
                'progress' => $this->calculateProgress($badge, $user),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Calculate progress toward a specific badge.
     */
    protected function calculateProgress(Badge $badge, User $user): array
    {
        $criteria = $badge->criteria;
        $progress = [];

        foreach ($criteria as $key => $value) {
            $current = match($key) {
                'ideas_submitted' => $user->ideas_submitted,
                'ideas_approved' => $user->ideas_approved,
                'comments_posted' => $user->comments_posted,
                'likes_received' => $user->likes_received,
                'level' => $user->level,
                default => 0,
            };

            $required = is_numeric($value) ? $value : 0;

            $progress[] = [
                'metric' => $key,
                'current' => $current,
                'required' => $required,
                'percentage' => $required > 0 ? min(100, ($current / $required) * 100) : 0,
                'completed' => $current >= $required,
            ];
        }

        return $progress;
    }
}

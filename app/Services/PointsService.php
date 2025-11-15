<?php

namespace App\Services;

use App\Models\User;

class PointsService
{
    // Point values for different actions
    const POINTS_IDEA_SUBMITTED = 10;
    const POINTS_IDEA_APPROVED = 50;
    const POINTS_IDEA_IMPLEMENTED = 100;
    const POINTS_COMMENT_CREATED = 5;
    const POINTS_LIKE_RECEIVED = 2;

    /**
     * Award points to a user
     */
    public function awardPoints(User $user, int $points, string $reason = ''): void
    {
        $user->increment('points', $points);

        // Log the point award (optional: create a points_history table)
        logger()->info("Points awarded", [
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
            'total_points' => $user->points + $points,
        ]);
    }

    /**
     * Award points for idea submission
     */
    public function awardIdeaSubmitted(User $user): void
    {
        $this->awardPoints($user, self::POINTS_IDEA_SUBMITTED, 'Idea submitted');
    }

    /**
     * Award points for idea approval
     */
    public function awardIdeaApproved(User $user): void
    {
        $this->awardPoints($user, self::POINTS_IDEA_APPROVED, 'Idea approved');
    }

    /**
     * Award points for idea implementation
     */
    public function awardIdeaImplemented(User $user): void
    {
        $this->awardPoints($user, self::POINTS_IDEA_IMPLEMENTED, 'Idea implemented');
    }

    /**
     * Award points for comment creation
     */
    public function awardCommentCreated(User $user): void
    {
        $this->awardPoints($user, self::POINTS_COMMENT_CREATED, 'Comment created');
    }

    /**
     * Award points for receiving a like
     */
    public function awardLikeReceived(User $user): void
    {
        $this->awardPoints($user, self::POINTS_LIKE_RECEIVED, 'Like received');
    }

    /**
     * Deduct points (for unlike)
     */
    public function deductPoints(User $user, int $points, string $reason = ''): void
    {
        $user->decrement('points', $points);

        logger()->info("Points deducted", [
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
            'total_points' => $user->points - $points,
        ]);
    }

    /**
     * Deduct points for losing a like
     */
    public function deductLikeRemoved(User $user): void
    {
        $this->deductPoints($user, self::POINTS_LIKE_RECEIVED, 'Like removed');
    }

    /**
     * Get leaderboard (top users by points)
     */
    public function getLeaderboard(int $limit = 10)
    {
        return User::where('is_active', true)
            ->orderBy('points', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'avatar', 'department', 'points']);
    }
}

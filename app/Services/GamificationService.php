<?php

namespace App\Services;

use App\Events\BadgeEarned;
use App\Events\NewNotification;
use App\Events\UserLeveledUp;
use App\Models\Badge;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    // XP values for different actions
    const XP_IDEA_SUBMITTED = 20;

    const XP_IDEA_APPROVED = 100;

    const XP_IDEA_IMPLEMENTED = 200;

    const XP_COMMENT_CREATED = 10;

    const XP_LIKE_RECEIVED = 5;

    const XP_LIKE_GIVEN = 2;

    const XP_BADGE_EARNED = 50;

    /**
     * Award experience points to a user and check for level up.
     */
    public function awardExperience(User $user, int $xp, string $reason = ''): array
    {
        $oldLevel = $user->level;
        $user->experience += $xp;

        // Check for level up
        $leveledUp = false;
        $newBadges = [];

        while ($user->experience >= $user->getXpForNextLevel()) {
            $user->experience -= $user->getXpForNextLevel();
            $user->level++;
            $leveledUp = true;

            // Check for level-based badges
            $newBadges = array_merge($newBadges, $this->checkLevelBadges($user));
        }

        // Update title based on level
        $user->title = $user->rank;
        $user->save();

        // Broadcast level-up event if level changed
        if ($leveledUp) {
            broadcast(new UserLeveledUp($user, $oldLevel, $user->level));

            Log::info('User leveled up', [
                'user_id' => $user->id,
                'old_level' => $oldLevel,
                'new_level' => $user->level,
                'new_rank' => $user->rank,
                'reason' => $reason,
                'badges_earned' => count($newBadges),
                'tenant_id' => $user->tenant_id,
            ]);
        }

        return [
            'xp_awarded' => $xp,
            'total_xp' => $user->experience,
            'old_level' => $oldLevel,
            'new_level' => $user->level,
            'leveled_up' => $leveledUp,
            'new_badges' => $newBadges,
            'reason' => $reason,
        ];
    }

    /**
     * Update user statistics and award XP for idea submission.
     */
    public function trackIdeaSubmitted(User $user): void
    {
        $user->increment('ideas_submitted');
        $result = $this->awardExperience($user, self::XP_IDEA_SUBMITTED, 'Submitted an idea');

        // Check for idea-related badges
        $this->checkIdeaBadges($user);

        // Notify if leveled up
        if ($result['leveled_up']) {
            $this->notifyLevelUp($user, $result['new_level']);
        }
    }

    /**
     * Update user statistics and award XP for idea approval.
     */
    public function trackIdeaApproved(User $user): void
    {
        $user->increment('ideas_approved');
        $result = $this->awardExperience($user, self::XP_IDEA_APPROVED, 'Idea approved');

        $this->checkIdeaBadges($user);

        if ($result['leveled_up']) {
            $this->notifyLevelUp($user, $result['new_level']);
        }
    }

    /**
     * Update user statistics and award XP for idea implementation.
     */
    public function trackIdeaImplemented(User $user): void
    {
        $result = $this->awardExperience($user, self::XP_IDEA_IMPLEMENTED, 'Idea implemented');

        $this->checkIdeaBadges($user);

        if ($result['leveled_up']) {
            $this->notifyLevelUp($user, $result['new_level']);
        }
    }

    /**
     * Update user statistics and award XP for comment.
     */
    public function trackCommentCreated(User $user): void
    {
        $user->increment('comments_posted');
        $result = $this->awardExperience($user, self::XP_COMMENT_CREATED, 'Posted a comment');

        $this->checkCommentBadges($user);

        if ($result['leveled_up']) {
            $this->notifyLevelUp($user, $result['new_level']);
        }
    }

    /**
     * Track when user receives a like.
     */
    public function trackLikeReceived(User $user): void
    {
        $user->increment('likes_received');
        $this->awardExperience($user, self::XP_LIKE_RECEIVED, 'Received a like');

        $this->checkLikeBadges($user);
    }

    /**
     * Track when user gives a like.
     */
    public function trackLikeGiven(User $user): void
    {
        $user->increment('likes_given');
        $this->awardExperience($user, self::XP_LIKE_GIVEN, 'Engaged with content');
    }

    /**
     * Award a badge to a user.
     */
    public function awardBadge(User $user, Badge $badge): bool
    {
        // Check if user already has this badge
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return false;
        }

        // Award the badge
        $user->badges()->attach($badge->id, [
            'earned_at' => now(),
            'progress' => 100,
        ]);

        $user->increment('total_badges');

        // Award bonus XP for earning badge
        if ($badge->points_reward > 0) {
            $user->increment('points', $badge->points_reward);
        }

        $this->awardExperience($user, self::XP_BADGE_EARNED, "Earned badge: {$badge->name}");

        Log::info('Badge earned', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'badge_name' => $badge->name,
            'badge_slug' => $badge->slug,
            'badge_rarity' => $badge->rarity,
            'points_reward' => $badge->points_reward,
            'total_badges' => $user->total_badges,
            'tenant_id' => $user->tenant_id,
        ]);

        // Broadcast badge earned event
        broadcast(new BadgeEarned($user, $badge));

        // Notify user of new badge
        $this->notifyBadgeEarned($user, $badge);

        return true;
    }

    /**
     * Check and award level-based badges.
     */
    protected function checkLevelBadges(User $user): array
    {
        $awarded = [];

        $levelBadges = [
            5 => 'rising-star',
            10 => 'active-contributor',
            20 => 'senior-contributor',
            30 => 'expert-innovator',
            40 => 'visionary-leader',
            50 => 'innovation-master',
        ];

        if (isset($levelBadges[$user->level])) {
            $badge = Badge::where('slug', $levelBadges[$user->level])->first();
            if ($badge && $this->awardBadge($user, $badge)) {
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Check and award idea-related badges.
     */
    protected function checkIdeaBadges(User $user): void
    {
        $badges = [
            ['slug' => 'first-idea', 'count' => 1, 'field' => 'ideas_submitted'],
            ['slug' => 'idea-generator', 'count' => 10, 'field' => 'ideas_submitted'],
            ['slug' => 'innovation-machine', 'count' => 50, 'field' => 'ideas_submitted'],
            ['slug' => 'first-approval', 'count' => 1, 'field' => 'ideas_approved'],
            ['slug' => 'approved-innovator', 'count' => 5, 'field' => 'ideas_approved'],
            ['slug' => 'elite-innovator', 'count' => 25, 'field' => 'ideas_approved'],
        ];

        foreach ($badges as $badgeData) {
            if ($user->{$badgeData['field']} >= $badgeData['count']) {
                $badge = Badge::where('slug', $badgeData['slug'])->first();
                if ($badge) {
                    $this->awardBadge($user, $badge);
                }
            }
        }
    }

    /**
     * Check and award comment-related badges.
     */
    protected function checkCommentBadges(User $user): void
    {
        $badges = [
            ['slug' => 'first-comment', 'count' => 1],
            ['slug' => 'conversationalist', 'count' => 25],
            ['slug' => 'discussion-champion', 'count' => 100],
        ];

        foreach ($badges as $badgeData) {
            if ($user->comments_posted >= $badgeData['count']) {
                $badge = Badge::where('slug', $badgeData['slug'])->first();
                if ($badge) {
                    $this->awardBadge($user, $badge);
                }
            }
        }
    }

    /**
     * Check and award like-related badges.
     */
    protected function checkLikeBadges(User $user): void
    {
        $badges = [
            ['slug' => 'popular-idea', 'count' => 10],
            ['slug' => 'crowd-favorite', 'count' => 50],
            ['slug' => 'viral-innovator', 'count' => 100],
        ];

        foreach ($badges as $badgeData) {
            if ($user->likes_received >= $badgeData['count']) {
                $badge = Badge::where('slug', $badgeData['slug'])->first();
                if ($badge) {
                    $this->awardBadge($user, $badge);
                }
            }
        }
    }

    /**
     * Notify user of level up.
     */
    protected function notifyLevelUp(User $user, int $newLevel): void
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'level_up',
            'title' => '🎉 Level Up!',
            'message' => "Congratulations! You've reached Level {$newLevel} - {$user->rank}",
            'data' => [
                'level' => $newLevel,
                'rank' => $user->rank,
                'next_level_xp' => $user->getXpForNextLevel(),
            ],
        ]);

        // Broadcast notification in real-time
        broadcast(new NewNotification($notification));
    }

    /**
     * Notify user of new badge.
     */
    protected function notifyBadgeEarned(User $user, Badge $badge): void
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'badge_earned',
            'title' => '🏆 New Badge Earned!',
            'message' => "You've earned the '{$badge->name}' badge! {$badge->description}",
            'data' => [
                'badge_id' => $badge->id,
                'badge_name' => $badge->name,
                'badge_rarity' => $badge->rarity,
                'points_reward' => $badge->points_reward,
            ],
        ]);

        // Broadcast notification in real-time
        broadcast(new NewNotification($notification));
    }

    /**
     * Get user's gamification statistics.
     */
    public function getUserStats(User $user): array
    {
        return [
            'level' => $user->level,
            'experience' => $user->experience,
            'next_level_xp' => $user->getXpForNextLevel(),
            'level_progress' => $user->level_progress,
            'rank' => $user->rank,
            'points' => $user->points,
            'total_badges' => $user->total_badges,
            'ideas_submitted' => $user->ideas_submitted,
            'ideas_approved' => $user->ideas_approved,
            'comments_posted' => $user->comments_posted,
            'likes_given' => $user->likes_given,
            'likes_received' => $user->likes_received,
            'badges' => $user->badges()->with('pivot')->get(),
        ];
    }
}

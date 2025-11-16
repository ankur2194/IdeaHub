<?php

namespace App\GraphQL\Queries;

use App\Models\Idea;
use Illuminate\Support\Facades\DB;

class IdeaResolver
{
    /**
     * Get trending ideas based on recent activity.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function trending($_, array $args)
    {
        $days = $args['days'] ?? 7;
        $limit = $args['limit'] ?? 10;

        return Idea::query()
            ->where('status', 'approved')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc(DB::raw('(likes_count * 3) + (comments_count * 2) + (views_count * 1)'))
            ->limit($limit)
            ->get();
    }

    /**
     * Check if the current user has liked the idea.
     *
     * @param  \App\Models\Idea  $idea
     * @param  array<string, mixed>  $args
     */
    public function isLikedByMe(Idea $idea, array $args)
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $idea->likedBy()->where('users.id', $user->id)->exists();
    }
}

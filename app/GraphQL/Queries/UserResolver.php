<?php

namespace App\GraphQL\Queries;

use App\Models\User;

class UserResolver
{
    /**
     * Get user leaderboard.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function leaderboard($_, array $args)
    {
        $limit = $args['limit'] ?? 10;

        return User::query()
            ->where('is_active', true)
            ->orderByDesc('points')
            ->orderByDesc('level')
            ->limit($limit)
            ->get();
    }
}

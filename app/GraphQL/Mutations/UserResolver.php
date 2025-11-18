<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;

class UserResolver
{
    /**
     * Update user profile.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function updateProfile($_, array $args)
    {
        $user = Auth::user();

        $updateData = array_filter([
            'name' => $args['name'] ?? null,
            'avatar' => $args['avatar'] ?? null,
            'department' => $args['department'] ?? null,
            'job_title' => $args['job_title'] ?? null,
        ], fn ($value) => $value !== null);

        $user->update($updateData);

        return $user->fresh();
    }
}

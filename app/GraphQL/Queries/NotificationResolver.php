<?php

namespace App\GraphQL\Queries;

use App\Models\Notification;

class NotificationResolver
{
    /**
     * Get count of unread notifications for the authenticated user.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function unreadCount($_, array $args)
    {
        $user = auth()->user();

        if (!$user) {
            return 0;
        }

        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}

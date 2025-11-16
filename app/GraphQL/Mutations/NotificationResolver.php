<?php

namespace App\GraphQL\Mutations;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationResolver
{
    /**
     * Mark a notification as read.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function markRead($_, array $args)
    {
        $user = Auth::user();
        $notification = Notification::findOrFail($args['id']);

        // Check authorization
        if ($notification->user_id !== $user->id) {
            throw new \Exception('Unauthorized to update this notification.');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $notification->fresh();
    }

    /**
     * Mark all notifications as read.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function markAllRead($_, array $args)
    {
        $user = Auth::user();

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return [
            'message' => "Marked {$count} notifications as read",
            'count' => $count,
        ];
    }
}

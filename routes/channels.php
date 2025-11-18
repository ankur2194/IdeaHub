<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User-specific private channel for notifications
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

// Idea-specific channel for real-time updates
Broadcast::channel('idea.{ideaId}', function (User $user, int $ideaId) {
    // All authenticated users can listen to idea updates
    return true;
});

// Global notifications channel
Broadcast::channel('notifications', function (User $user) {
    // All authenticated users can listen to global notifications
    return true;
});

// Presence channel for online users (optional, for future use)
Broadcast::channel('online', function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

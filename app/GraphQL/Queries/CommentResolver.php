<?php

namespace App\GraphQL\Queries;

use App\Models\Comment;

class CommentResolver
{
    /**
     * Check if the current user has liked the comment.
     *
     * @param  array<string, mixed>  $args
     */
    public function isLikedByMe(Comment $comment, array $args)
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $comment->likedBy()->where('users.id', $user->id)->exists();
    }
}

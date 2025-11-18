<?php

namespace App\GraphQL\Mutations;

use App\Models\Comment;
use App\Models\Idea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentResolver
{
    /**
     * Create a new comment.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function create($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['idea_id']);

        DB::beginTransaction();

        try {
            $comment = Comment::create([
                'idea_id' => $args['idea_id'],
                'user_id' => $user->id,
                'parent_id' => $args['parent_id'] ?? null,
                'content' => $args['content'],
                'likes_count' => 0,
                'is_edited' => false,
            ]);

            // Increment comments count on idea
            $idea->increment('comments_count');

            // Update user stats
            $user->increment('comments_posted');

            DB::commit();

            return $comment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing comment.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function update($_, array $args)
    {
        $user = Auth::user();
        $comment = Comment::findOrFail($args['id']);

        // Check authorization
        if ($comment->user_id !== $user->id) {
            throw new \Exception('Unauthorized to update this comment.');
        }

        $comment->update([
            'content' => $args['content'],
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return $comment->fresh();
    }

    /**
     * Delete a comment.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function delete($_, array $args)
    {
        $user = Auth::user();
        $comment = Comment::findOrFail($args['id']);

        // Check authorization
        if ($comment->user_id !== $user->id && ! $user->isAdmin()) {
            throw new \Exception('Unauthorized to delete this comment.');
        }

        DB::beginTransaction();

        try {
            // Decrement comments count on idea
            $comment->idea->decrement('comments_count');

            // Delete the comment
            $comment->delete();

            DB::commit();

            return [
                'message' => 'Comment deleted successfully',
                'success' => true,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Like a comment.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function like($_, array $args)
    {
        $user = Auth::user();
        $comment = Comment::findOrFail($args['comment_id']);

        // Check if already liked
        if ($comment->likedBy()->where('users.id', $user->id)->exists()) {
            throw new \Exception('You have already liked this comment.');
        }

        DB::beginTransaction();

        try {
            // Attach like
            $comment->likedBy()->attach($user->id);

            // Increment likes count
            $comment->increment('likes_count');

            DB::commit();

            return $comment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Unlike a comment.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function unlike($_, array $args)
    {
        $user = Auth::user();
        $comment = Comment::findOrFail($args['comment_id']);

        // Check if not liked
        if (! $comment->likedBy()->where('users.id', $user->id)->exists()) {
            throw new \Exception('You have not liked this comment.');
        }

        DB::beginTransaction();

        try {
            // Detach like
            $comment->likedBy()->detach($user->id);

            // Decrement likes count
            $comment->decrement('likes_count');

            DB::commit();

            return $comment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

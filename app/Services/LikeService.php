<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LikeService
{
    /**
     * Toggle like/unlike for a likeable model (Idea or Comment).
     *
     * @param  Model  $model  The model to like/unlike (must have likedBy relationship)
     * @param  int  $userId  The user ID performing the action
     * @param  string  $relationshipTable  The pivot table name (idea_likes or comment_likes)
     * @param  string  $foreignKey  The foreign key in pivot table (idea_id or comment_id)
     * @return array Result with liked status and new count
     */
    public function toggleLike(Model $model, int $userId, string $relationshipTable, string $foreignKey): array
    {
        return DB::transaction(function () use ($model, $userId, $relationshipTable, $foreignKey) {
            // Check if already liked
            $alreadyLiked = DB::table($relationshipTable)
                ->where($foreignKey, $model->id)
                ->where('user_id', $userId)
                ->exists();

            if ($alreadyLiked) {
                // Unlike
                DB::table($relationshipTable)
                    ->where($foreignKey, $model->id)
                    ->where('user_id', $userId)
                    ->delete();

                // Decrement likes count
                $model->decrement('likes_count');

                return [
                    'liked' => false,
                    'likes_count' => max(0, $model->likes_count), // Ensure not negative
                    'message' => 'Unliked successfully',
                ];
            } else {
                // Like
                DB::table($relationshipTable)->insert([
                    $foreignKey => $model->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                ]);

                // Increment likes count
                $model->increment('likes_count');

                return [
                    'liked' => true,
                    'likes_count' => $model->likes_count,
                    'message' => 'Liked successfully',
                ];
            }
        });
    }

    /**
     * Like an idea.
     */
    public function likeIdea(\App\Models\Idea $idea, int $userId): array
    {
        return $this->toggleLike($idea, $userId, 'idea_likes', 'idea_id');
    }

    /**
     * Like a comment.
     */
    public function likeComment(\App\Models\Comment $comment, int $userId): array
    {
        return $this->toggleLike($comment, $userId, 'comment_likes', 'comment_id');
    }
}

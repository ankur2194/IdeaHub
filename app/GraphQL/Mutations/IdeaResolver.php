<?php

namespace App\GraphQL\Mutations;

use App\Models\Idea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IdeaResolver
{
    /**
     * Create a new idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function create($_, array $args)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $idea = Idea::create([
                'title' => $args['title'],
                'description' => $args['description'],
                'user_id' => $user->id,
                'category_id' => $args['category_id'] ?? null,
                'status' => 'draft',
                'is_anonymous' => $args['is_anonymous'] ?? false,
                'attachments' => $args['attachments'] ?? null,
                'likes_count' => 0,
                'comments_count' => 0,
                'views_count' => 0,
            ]);

            // Attach tags if provided
            if (!empty($args['tag_ids'])) {
                $idea->tags()->sync($args['tag_ids']);
            }

            // Update user stats
            $user->increment('ideas_submitted');

            DB::commit();

            return $idea->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function update($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['id']);

        // Check authorization
        if ($idea->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('Unauthorized to update this idea.');
        }

        DB::beginTransaction();

        try {
            $updateData = array_filter([
                'title' => $args['title'] ?? null,
                'description' => $args['description'] ?? null,
                'category_id' => $args['category_id'] ?? null,
                'status' => $args['status'] ?? null,
                'is_anonymous' => $args['is_anonymous'] ?? null,
                'attachments' => $args['attachments'] ?? null,
            ], fn($value) => $value !== null);

            $idea->update($updateData);

            // Update tags if provided
            if (isset($args['tag_ids'])) {
                $idea->tags()->sync($args['tag_ids']);
            }

            DB::commit();

            return $idea->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function delete($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['id']);

        // Check authorization
        if ($idea->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('Unauthorized to delete this idea.');
        }

        $idea->delete();

        return [
            'message' => 'Idea deleted successfully',
            'success' => true,
        ];
    }

    /**
     * Like an idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function like($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['idea_id']);

        // Check if already liked
        if ($idea->likedBy()->where('users.id', $user->id)->exists()) {
            throw new \Exception('You have already liked this idea.');
        }

        DB::beginTransaction();

        try {
            // Attach like
            $idea->likedBy()->attach($user->id);

            // Increment likes count
            $idea->increment('likes_count');

            // Update user stats
            $user->increment('likes_given');
            $idea->user->increment('likes_received');

            DB::commit();

            return $idea->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Unlike an idea.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function unlike($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['idea_id']);

        // Check if not liked
        if (!$idea->likedBy()->where('users.id', $user->id)->exists()) {
            throw new \Exception('You have not liked this idea.');
        }

        DB::beginTransaction();

        try {
            // Detach like
            $idea->likedBy()->detach($user->id);

            // Decrement likes count
            $idea->decrement('likes_count');

            // Update user stats
            $user->decrement('likes_given');
            $idea->user->decrement('likes_received');

            DB::commit();

            return $idea->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Submit an idea for approval.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function submit($_, array $args)
    {
        $user = Auth::user();
        $idea = Idea::findOrFail($args['id']);

        // Check authorization
        if ($idea->user_id !== $user->id) {
            throw new \Exception('Unauthorized to submit this idea.');
        }

        if ($idea->status !== 'draft') {
            throw new \Exception('Only draft ideas can be submitted.');
        }

        $idea->update([
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return $idea->fresh();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Idea;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display comments for an idea.
     */
    public function index(Request $request, Idea $idea)
    {
        $comments = $idea->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id') // Only top-level comments
            ->latest()
            ->paginate($request->get('per_page', 20));

        // Add 'liked' attribute to each comment
        if (Auth::check()) {
            $userId = Auth::id();
            $comments->getCollection()->transform(function ($comment) use ($userId) {
                $comment->liked = $comment->likedBy()->where('user_id', $userId)->exists();
                // Also check likes for replies
                if ($comment->replies) {
                    $comment->replies->transform(function ($reply) use ($userId) {
                        $reply->liked = $reply->likedBy()->where('user_id', $userId)->exists();
                        return $reply;
                    });
                }
                return $comment;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, PointsService $pointsService)
    {
        $validated = $request->validate([
            'idea_id' => 'required|exists:ideas,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'idea_id' => $validated['idea_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Increment idea's comment count
        Idea::find($validated['idea_id'])->increment('comments_count');

        // Award points for comment creation
        $pointsService->awardCommentCreated(Auth::user());

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully (+5 points)',
            'data' => $comment,
        ], 201);
    }

    /**
     * Display the specified comment.
     */
    public function show(Comment $comment)
    {
        $comment->load(['user', 'replies.user']);

        return response()->json([
            'success' => true,
            'data' => $comment,
        ]);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment)
    {
        // Check authorization
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this comment',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $validated['content'],
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment,
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment)
    {
        // Check authorization
        if ($comment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this comment',
            ], 403);
        }

        // Decrement idea's comment count
        $comment->idea()->decrement('comments_count');

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Like or unlike a comment.
     */
    public function like(Comment $comment, PointsService $pointsService)
    {
        $user = Auth::user();

        // Check if user already liked this comment
        if ($comment->likedBy()->where('user_id', $user->id)->exists()) {
            // Unlike: remove the like
            $comment->likedBy()->detach($user->id);
            $comment->decrement('likes_count');

            // Deduct points from comment author
            if ($comment->user) {
                $pointsService->deductLikeRemoved($comment->user);
            }

            $liked = false;
            $message = 'Comment unliked';
        } else {
            // Like: add the like
            $comment->likedBy()->attach($user->id);
            $comment->increment('likes_count');

            // Award points to comment author
            if ($comment->user) {
                $pointsService->awardLikeReceived($comment->user);
            }

            $liked = true;
            $message = 'Comment liked';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'liked' => $liked,
                'likes_count' => $comment->likes_count,
            ],
        ]);
    }
}

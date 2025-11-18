<?php

namespace App\Http\Controllers\Api;

use App\Events\CommentCreated;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Idea;
use App\Services\NotificationService;
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
        // Validate pagination limit to prevent excessive resource usage
        $perPage = $request->integer('per_page', 20);
        $perPage = max(1, min($perPage, 100)); // Clamp between 1 and 100

        $comments = $idea->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id') // Only top-level comments
            ->latest()
            ->paginate($perPage);

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
    public function store(Request $request, PointsService $pointsService, NotificationService $notificationService, \App\Services\GamificationService $gamificationService)
    {
        $validated = $request->validate([
            'idea_id' => 'required|exists:ideas,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Security: Verify the idea belongs to the current tenant
        $idea = Idea::findOrFail($validated['idea_id']);
        $currentTenantId = app('current_tenant_id');

        if ($currentTenantId && $idea->tenant_id !== $currentTenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Cannot comment on ideas from other organizations.',
            ], 403);
        }

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

        // Track gamification (XP, badges, levels)
        $gamificationService->trackCommentCreated(Auth::user());

        // Load relationships for notifications
        $comment->load('user', 'idea.user', 'parent.user');

        // Broadcast comment created event
        broadcast(new CommentCreated($comment));

        // Send notifications
        if ($comment->parent_id) {
            // This is a reply - notify the parent comment author
            $notificationService->notifyCommentReply($comment);
        } else {
            // This is a top-level comment - notify the idea author
            $notificationService->notifyCommentPosted($comment);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully (+5 points, +10 XP)',
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
        if ($comment->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this comment',
            ], 403);
        }

        // Comment count decrement is handled by model event in Comment::booted()
        // This ensures it works for both manual and cascade deletes
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Like or unlike a comment.
     */
    public function like(Comment $comment, \App\Services\LikeService $likeService, PointsService $pointsService, \App\Services\GamificationService $gamificationService)
    {
        $user = Auth::user();

        // Use LikeService to handle like/unlike logic
        $result = $likeService->likeComment($comment, $user->id);

        // Award/deduct points and gamification XP
        if ($result['liked']) {
            // Like: award points and XP to comment author
            if ($comment->user) {
                $pointsService->awardLikeReceived($comment->user);
                $gamificationService->trackLikeReceived($comment->user);
            }

            // Track like given by current user
            $gamificationService->trackLikeGiven($user);
        } else {
            // Unlike: deduct points from comment author
            // Note: XP is not deducted as it's a progression system
            if ($comment->user) {
                $pointsService->deductLikeRemoved($comment->user);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'liked' => $result['liked'],
                'likes_count' => $result['likes_count'],
            ],
        ]);
    }
}

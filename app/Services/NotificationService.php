<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\User;
use App\Mail\IdeaSubmittedMail;
use App\Mail\IdeaApprovedMail;
use App\Mail\IdeaRejectedMail;
use App\Mail\CommentPostedMail;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify approvers when an idea is submitted
     */
    public function notifyIdeaSubmitted(Idea $idea): void
    {
        // Get all department heads and admins
        $approvers = User::whereIn('role', ['admin', 'department_head'])
            ->where('is_active', true)
            ->get();

        foreach ($approvers as $approver) {
            // Create in-app notification
            $notification = Notification::create([
                'user_id' => $approver->id,
                'type' => 'idea_submitted',
                'title' => 'New Idea Submitted for Review',
                'message' => "{$idea->user->name} submitted a new idea: \"{$idea->title}\"",
                'data' => [
                    'idea_id' => $idea->id,
                    'idea_title' => $idea->title,
                    'author_name' => $idea->user->name,
                ],
            ]);

            // Send email notification
            try {
                Mail::to($approver->email)->send(new IdeaSubmittedMail($idea, $approver));
                $notification->update(['email_sent' => true]);
            } catch (\Exception $e) {
                logger()->error("Failed to send idea submitted email", [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify idea author when their idea is approved
     */
    public function notifyIdeaApproved(Idea $idea, ?User $approver = null): void
    {
        if (!$idea->user) {
            return;
        }

        $approverName = $approver ? $approver->name : 'the review team';

        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $idea->user_id,
            'type' => 'idea_approved',
            'title' => 'Your Idea Was Approved!',
            'message' => "Congratulations! Your idea \"{$idea->title}\" has been approved by {$approverName}.",
            'data' => [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
                'approver_name' => $approverName,
            ],
        ]);

        // Send email notification
        try {
            if (!$approver) {
                $approver = User::where('role', 'admin')->first();
            }
            Mail::to($idea->user->email)->send(new IdeaApprovedMail($idea, $approver));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error("Failed to send idea approved email", [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify idea author when their idea is rejected
     */
    public function notifyIdeaRejected(Idea $idea, ?string $reason = '', ?User $approver = null): void
    {
        if (!$idea->user) {
            return;
        }

        $approverName = $approver ? $approver->name : 'the review team';

        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $idea->user_id,
            'type' => 'idea_rejected',
            'title' => 'Idea Review Update',
            'message' => "Your idea \"{$idea->title}\" was not approved by {$approverName}. " . ($reason ? "Reason: {$reason}" : ''),
            'data' => [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
                'approver_name' => $approverName,
                'reason' => $reason,
            ],
        ]);

        // Send email notification
        try {
            if (!$approver) {
                $approver = User::where('role', 'admin')->first();
            }
            Mail::to($idea->user->email)->send(new IdeaRejectedMail($idea, $approver, $reason));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error("Failed to send idea rejected email", [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify approver when they have a new approval request
     */
    public function notifyApprovalRequest($approval): void
    {
        $approver = $approval->approver;
        $idea = $approval->idea;

        if (!$approver || !$idea) {
            return;
        }

        // Create in-app notification
        Notification::create([
            'user_id' => $approver->id,
            'type' => 'approval_request',
            'title' => 'New Idea Requires Your Approval',
            'message' => "Idea \"{$idea->title}\" by {$idea->user->name} is awaiting your approval (Level {$approval->level})",
            'data' => [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
                'approval_id' => $approval->id,
                'approval_level' => $approval->level,
                'author_name' => $idea->user->name,
            ],
        ]);
    }

    /**
     * Notify idea author when someone comments on their idea
     */
    public function notifyCommentPosted(Comment $comment): void
    {
        $idea = $comment->idea;

        if (!$idea || !$idea->user) {
            return;
        }

        // Don't notify if the commenter is the idea author
        if ($comment->user_id === $idea->user_id) {
            return;
        }

        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $idea->user_id,
            'type' => 'comment_posted',
            'title' => 'New Comment on Your Idea',
            'message' => "{$comment->user->name} commented on your idea \"{$idea->title}\"",
            'data' => [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
                'comment_id' => $comment->id,
                'commenter_name' => $comment->user->name,
            ],
        ]);

        // Send email notification
        try {
            Mail::to($idea->user->email)->send(new CommentPostedMail($comment, $idea));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error("Failed to send comment posted email", [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify parent comment author when someone replies to their comment
     */
    public function notifyCommentReply(Comment $reply): void
    {
        $parentComment = $reply->parent;

        if (!$parentComment || !$parentComment->user) {
            return;
        }

        // Don't notify if replying to own comment
        if ($reply->user_id === $parentComment->user_id) {
            return;
        }

        $idea = $reply->idea;

        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $parentComment->user_id,
            'type' => 'comment_reply',
            'title' => 'New Reply to Your Comment',
            'message' => "{$reply->user->name} replied to your comment on \"{$idea->title}\"",
            'data' => [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
                'comment_id' => $reply->id,
                'parent_comment_id' => $parentComment->id,
                'replier_name' => $reply->user->name,
            ],
        ]);

        // For simplicity, reuse CommentPostedMail
        try {
            Mail::to($parentComment->user->email)->send(new CommentPostedMail($reply, $idea));
            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error("Failed to send comment reply email", [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }
}

<?php

namespace App\Mail;

use App\Models\Comment;
use App\Models\Idea;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentPostedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Comment $comment,
        public Idea $idea
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💬 New Comment on Your Idea - IdeaHub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.comment-posted',
            with: [
                'ideaTitle' => $this->idea->title,
                'commentContent' => $this->comment->content,
                'commenterName' => $this->comment->user->name,
                'ideaUrl' => config('app.frontend_url', 'http://localhost:5173').'/ideas/'.$this->idea->id,
            ],
        );
    }
}

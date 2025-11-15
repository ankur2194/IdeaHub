<?php

namespace App\Mail;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IdeaSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Idea $idea,
        public User $approver
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 New Idea Submitted for Review - IdeaHub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.idea-submitted',
            with: [
                'ideaTitle' => $this->idea->title,
                'ideaDescription' => $this->idea->description,
                'authorName' => $this->idea->is_anonymous ? 'Anonymous' : $this->idea->user->name,
                'approverName' => $this->approver->name,
                'ideaUrl' => config('app.frontend_url', 'http://localhost:5173') . '/ideas/' . $this->idea->id,
                'categoryName' => $this->idea->category?->name ?? 'Uncategorized',
            ],
        );
    }
}

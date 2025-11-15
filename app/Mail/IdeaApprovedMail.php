<?php

namespace App\Mail;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IdeaApprovedMail extends Mailable
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
            subject: '🎉 Your Idea Was Approved - IdeaHub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.idea-approved',
            with: [
                'ideaTitle' => $this->idea->title,
                'ideaDescription' => $this->idea->description,
                'approverName' => $this->approver->name,
                'ideaUrl' => config('app.frontend_url', 'http://localhost:5173') . '/ideas/' . $this->idea->id,
                'points' => 50, // Points awarded for approval
            ],
        );
    }
}

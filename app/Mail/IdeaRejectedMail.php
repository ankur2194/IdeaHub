<?php

namespace App\Mail;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IdeaRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Idea $idea,
        public User $approver,
        public string $reason = ''
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📝 Idea Review Update - IdeaHub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.idea-rejected',
            with: [
                'ideaTitle' => $this->idea->title,
                'ideaDescription' => $this->idea->description,
                'approverName' => $this->approver->name,
                'reason' => $this->reason,
                'ideaUrl' => config('app.frontend_url', 'http://localhost:5173').'/ideas/'.$this->idea->id,
            ],
        );
    }
}

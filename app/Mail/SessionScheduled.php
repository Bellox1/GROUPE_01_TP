<?php

namespace App\Mail;

use App\Models\CourseSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SessionScheduled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public CourseSession $session)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle séance planifiée : ' . $this->session->course->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.sessions.scheduled',
        );
    }
}

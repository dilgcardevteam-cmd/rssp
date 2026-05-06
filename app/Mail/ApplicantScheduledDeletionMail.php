<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantScheduledDeletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $applicantName,
        public string $applicantCode,
        public string $deadlineText
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DILG-CAR Applicant Record Scheduled for Deletion',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.applicant_scheduled_deletion',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantDeletionWarningMail extends Mailable
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
            view: 'emails.applicant_deletion_warning',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AdminNotifyApplicant extends Mailable
{
    use Queueable, SerializesModels;

    public $actorName;
    public $applicantName;
    public $vacancyId;
    public $positionTitle;
    public $timestamp;
    public $timezone;
    public $documents;

    public function __construct(
        string $actorName = 'Unknown Admin',
        ?string $applicantName = null,
        ?string $vacancyId = null,
        ?string $positionTitle = null,
        array $documents = [],
        string $timestamp = '',
        string $timezone = 'UTC'
    ) {
        $this->actorName = $actorName ?: 'Unknown Admin';
        $this->applicantName = $applicantName ?: 'N/A';
        $this->vacancyId = $vacancyId ?: 'N/A';
        $this->positionTitle = $positionTitle ?: 'N/A';
        $this->documents = $documents;
        $this->timestamp = $timestamp ?: now()->timezone($timezone)->format('Y-m-d H:i:s T');
        $this->timezone = $timezone;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Admin Notification: Applicant Notified',
            from: new Address(
                config('mail.from.address') ?: 'noreply@dilgcar.local',
                config('mail.from.name') ?: 'DILG-CAR'
            )
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_notify_applicant',
            text: 'emails.admin_notify_applicant_plain',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}


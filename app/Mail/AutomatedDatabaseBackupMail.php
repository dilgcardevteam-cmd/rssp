<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutomatedDatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $databaseName,
        public string $filePath,
        public string $fileName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Automated Database Backup - ' . $this->databaseName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.automated_database_backup',
            text: 'emails.automated_database_backup_plain',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName),
        ];
    }
}

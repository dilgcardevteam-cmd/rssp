<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DocumentUploadedNotification extends Notification
{
    private $applicantName;
    private $documentTypes;
    private $uploadTimestamp;
    private $vacancyTitle;
    private $applicantId;
    private $vacancyId;

    /**
     * Create a new notification instance.
     * 
     * @param string $applicantName
     * @param array $documentTypes List of document types uploaded
     * @param string $vacancyTitle
     */
    public function __construct($applicantName, $documentTypes, $vacancyTitle, $applicantId, $vacancyId)
    {
        $this->applicantName = $applicantName;
        $this->documentTypes = $documentTypes; // Array of strings
        $this->vacancyTitle = $vacancyTitle;
        $this->applicantId = $applicantId;
        $this->vacancyId = $vacancyId;
        $this->uploadTimestamp = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Check preferences
        if (method_exists($notifiable, 'wantsNotification') && !$notifiable->wantsNotification('document_uploaded')) {
            return [];
        }
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $docList = implode(', ', array_map(function ($type) {
            return ucwords(str_replace('_', ' ', $type));
        }, $this->documentTypes));

        Log::info("Sending DocumentUploadedNotification to {$notifiable->email} for {$this->applicantName}");

        return (new MailMessage)
            ->subject('New Documents Uploaded: ' . $this->applicantName)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The following documents have been uploaded by applicant ' . $this->applicantName . ' for the position of ' . $this->vacancyTitle . ':')
            ->line('Documents: ' . $docList)
            ->line('Timestamp: ' . $this->uploadTimestamp->toDayDateTimeString())
            ->action('View Application', $this->resolveAdminLink())
            ->line('Thank you for your attention.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Documents Uploaded',
            'message' => $this->applicantName . ' uploaded ' . count($this->documentTypes) . ' document(s).',
            'link' => $this->resolveAdminLink(),
            'section' => 'Application List',
            'user_id' => $this->applicantId,
            'vacancy_id' => $this->vacancyId
        ];
    }

    private function resolveAdminLink(): string
    {
        $userId = (string) ($this->applicantId ?? '');
        $vacancyId = trim((string) ($this->vacancyId ?? ''));

        if ($userId !== '' && $vacancyId !== '') {
            return route('admin.applicant_status', ['user_id' => $userId, 'vacancy_id' => $vacancyId]);
        }

        if ($userId !== '') {
            return route('admin.applicant_records.show', ['user' => $userId]);
        }

        return route('admin.applicant_records.index');
    }
}

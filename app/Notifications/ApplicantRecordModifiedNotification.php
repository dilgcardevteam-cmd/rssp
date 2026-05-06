<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ApplicantRecordModifiedNotification extends Notification
{
    private $modifierName;
    private $applicantName;
    private $changes; // Array or string description of changes
    private $vacancyTitle;
    private $applicantId;
    private $vacancyId;
    private $modificationTimestamp;

    /**
     * Create a new notification instance.
     */
    public function __construct($modifierName, $applicantName, $changes, $vacancyTitle, $applicantId, $vacancyId)
    {
        $this->modifierName = $modifierName;
        $this->applicantName = $applicantName;
        $this->changes = $changes;
        $this->vacancyTitle = $vacancyTitle;
        $this->applicantId = $applicantId;
        $this->vacancyId = $vacancyId;
        $this->modificationTimestamp = now();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Don't notify the person who made the change (if they are an admin user)
        // Note: Logic to exclude the sender should technically be done before dispatching, 
        // but we can add a secondary check here if needed, though usually $notifiable is the recipient.

        if (method_exists($notifiable, 'wantsNotification') && !$notifiable->wantsNotification('applicant_modified')) {
            return [];
        }

        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $changesDescription = is_array($this->changes) ? implode(', ', array_keys($this->changes)) : $this->changes;

        Log::info("Sending ApplicantRecordModifiedNotification to {$notifiable->email} by {$this->modifierName}");

        return (new MailMessage)
            ->subject('Application Modified: ' . $this->applicantName)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Student/Applicant record for ' . $this->applicantName . ' (' . $this->vacancyTitle . ') has been modified.')
            ->line('Modified by: ' . $this->modifierName)
            ->line('Changes: ' . $changesDescription)
            ->line('Timestamp: ' . $this->modificationTimestamp->toDayDateTimeString())
            ->action('View Application', route('admin.applicant_status', ['user_id' => $this->applicantId, 'vacancy_id' => $this->vacancyId]));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Application Modified',
            'message' => 'Admin ' . $this->modifierName . ' updated application for ' . $this->applicantName,
            'link' => route('admin.applicant_status', ['user_id' => $this->applicantId, 'vacancy_id' => $this->vacancyId]),
            'section' => 'Application List',
            'user_id' => $this->applicantId,
            'vacancy_id' => $this->vacancyId
        ];
    }
}

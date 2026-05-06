<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\JobVacancy;
use App\Models\User;

class NotifyApplicationStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $user_id;
    public $vacancy_id;
    public $admin_name;
    public $changes;
    public $status;
    public $date;
    public $applicant_name;
    public $position_title;

    /**
     * Create a new message instance.
     */
    public function __construct($admin_name, $changes, $status, $user_id, $vacancy_id)
    {
        $this->user_id = $user_id;
        $this->vacancy_id = $vacancy_id;
        $this->admin_name = $admin_name;
        $this->changes = $changes;
        $this->status = $status;
        $this->date = now();
        $this->applicant_name = User::where('id', $user_id)->value('name');
        $this->position_title = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DILG-CAR Application Status',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifyApplicationStatus',
            with: [
                'user_id' => $this->user_id,
                'vacancy_id' => $this->vacancy_id,
                'admin_name' => $this->admin_name,
                'changes' => $this->changes,
                'status' => $this->status,
                'date' => $this->date,
                'applicant_name' => $this->applicant_name,
                'position_title' => $this->position_title,

            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

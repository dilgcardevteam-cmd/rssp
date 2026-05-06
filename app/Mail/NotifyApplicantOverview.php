<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JobVacancy;
use App\Models\User;

class NotifyApplicantOverview extends Mailable
{
    use Queueable, SerializesModels;

    public $user_id;
    public $vacancy_id;
    public $applicant_name;
    public $position_title;
    public $documents;
    public $application_remarks;

    // Additional properties for complete overview
    public $place_of_assignment;
    public $compensation;
    public $deadline;
    public $qs_education;
    public $qs_eligibility;
    public $qs_experience;
    public $qs_training;
    public $qs_result;
    public $progress_percentage;
    public $progress_count;
    public $vacancy_type;
    public $reviewer_name;
    public $compliance_notice_mode;

    public function __construct(
        $user_id,
        $vacancy_id,
        $documents,
        $application_remarks,
        $place_of_assignment = null,
        $compensation = 0,
        $deadline = null,
        $qs_education = 'no',
        $qs_eligibility = 'no',
        $qs_experience = 'no',
        $qs_training = 'no',
        $qs_result = 'Not Qualified',
        $progress_percentage = 0,
        $progress_count = '0/0',
        $vacancy_type = 'Plantilla',
        $reviewer_name = null,
        $compliance_notice_mode = 'default'
    ) {
        $this->user_id = $user_id;
        $this->vacancy_id = $vacancy_id;
        $this->documents = $documents;
        $this->application_remarks = $application_remarks;

        // Set additional properties
        $this->place_of_assignment = $place_of_assignment;
        $this->compensation = $compensation;
        $this->deadline = $deadline;
        $this->qs_education = $qs_education;
        $this->qs_eligibility = $qs_eligibility;
        $this->qs_experience = $qs_experience;
        $this->qs_training = $qs_training;
        $this->qs_result = $qs_result;
        $this->progress_percentage = $progress_percentage;
        $this->progress_count = $progress_count;
        $this->vacancy_type = $vacancy_type;
        $this->reviewer_name = $reviewer_name;
        $this->compliance_notice_mode = $compliance_notice_mode;

        $this->applicant_name = User::where('id', $user_id)->value('name');
        $this->position_title = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DILG-CAR Application Document Status Overview'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifyApplicantOverview',
            text: 'emails.notifyApplicantOverview_plain',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

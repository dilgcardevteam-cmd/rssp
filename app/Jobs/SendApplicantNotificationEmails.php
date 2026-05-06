<?php

namespace App\Jobs;

use App\Mail\AdminNotifyApplicant;
use App\Mail\NotifyApplicantOverview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendApplicantNotificationEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public string $userEmail,
        public array $userMailPayload,
        public array $adminMailPayloads
    ) {
    }

    public function handle(): void
    {
        $this->sendApplicantOverviewEmail();
        $this->sendAdminNotificationEmails();
    }

    private function sendApplicantOverviewEmail(): void
    {
        try {
            Mail::to($this->userEmail)->send(new NotifyApplicantOverview(
                $this->userMailPayload['user_id'],
                $this->userMailPayload['vacancy_id'],
                $this->userMailPayload['notify_documents_snapshot'],
                $this->userMailPayload['application_remarks'],
                $this->userMailPayload['place_of_assignment'],
                $this->userMailPayload['compensation'],
                $this->userMailPayload['deadline'],
                $this->userMailPayload['qs_education'],
                $this->userMailPayload['qs_eligibility'],
                $this->userMailPayload['qs_experience'],
                $this->userMailPayload['qs_training'],
                $this->userMailPayload['qs_result'],
                $this->userMailPayload['progress_percentage'],
                $this->userMailPayload['progress_count'],
                $this->userMailPayload['vacancy_type'],
                $this->userMailPayload['reviewer_name'] ?? null,
                $this->userMailPayload['compliance_notice_mode'] ?? 'default'
            ));
        } catch (\Throwable $e) {
            Log::error('Applicant overview email failed', [
                'user_email' => $this->userEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAdminNotificationEmails(): void
    {
        foreach ($this->adminMailPayloads as $payload) {
            $email = (string) ($payload['email'] ?? '');
            if ($email === '') {
                continue;
            }

            try {
                Mail::to($email)->send(new AdminNotifyApplicant(
                    (string) ($payload['actor_name'] ?? 'Unknown Admin'),
                    (string) ($payload['applicant_name'] ?? 'N/A'),
                    (string) ($payload['vacancy_id'] ?? 'N/A'),
                    (string) ($payload['position_title'] ?? 'N/A'),
                    is_array($payload['documents'] ?? null) ? $payload['documents'] : [],
                    (string) ($payload['timestamp'] ?? now()->format('Y-m-d H:i:s T')),
                    (string) ($payload['timezone'] ?? (config('app.timezone') ?: 'UTC'))
                ));
            } catch (\Throwable $e) {
                Log::error('Admin notify email failed', [
                    'recipient' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

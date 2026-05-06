<?php

namespace App\Services;

use App\Mail\ApplicantDeletionCancelledMail;
use App\Mail\ApplicantImmediateDeletionMail;
use App\Mail\ApplicantScheduledDeletionCompletedMail;
use App\Mail\ApplicantScheduledDeletionMail;
use App\Mail\ApplicantScheduledDeletionWarningMail;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicantDeletionWorkflowService
{
    public function __construct(
        private readonly ApplicantRecordDeletionService $deletionService
    ) {
    }

    public function schedule(User $user, Admin $admin): Carbon
    {
        $mailPayload = $this->buildMailPayload($user);
        $now = now();
        $dueAt = $now->copy()->addDays(7);

        $user->forceFill([
            'pending_deletion_at' => $now,
            'deletion_due_at' => $dueAt,
            'deletion_warning_sent_at' => null,
            'deletion_requested_by_admin_id' => $admin->id,
        ])->save();

        activity()
            ->causedBy($admin)
            ->performedOn($user)
            ->event('schedule_delete')
            ->withProperties([
                'section' => 'Applicant Records',
                'user_id' => $user->id,
                'applicant_code' => $user->applicant_code,
                'deletion_due_at' => $dueAt->toDateTimeString(),
            ])
            ->log('Scheduled applicant record for deletion.');

        $this->sendScheduledDeletionEmail($mailPayload['email'], $mailPayload['name'], $mailPayload['code'], $dueAt->format('M d, Y h:i A'));

        return $dueAt;
    }

    public function cancel(User $user, Admin $admin): void
    {
        $mailPayload = $this->buildMailPayload($user);
        $deadline = $user->deletion_due_at;

        $user->forceFill([
            'pending_deletion_at' => null,
            'deletion_due_at' => null,
            'deletion_warning_sent_at' => null,
            'deletion_requested_by_admin_id' => null,
        ])->save();

        activity()
            ->causedBy($admin)
            ->performedOn($user)
            ->event('cancel_delete')
            ->withProperties([
                'section' => 'Applicant Records',
                'user_id' => $user->id,
                'applicant_code' => $user->applicant_code,
                'cancelled_deadline' => $deadline?->toDateTimeString(),
            ])
            ->log('Cancelled scheduled applicant record deletion.');

        $this->sendDeletionCancelledEmail($mailPayload['email'], $mailPayload['name'], $mailPayload['code']);
    }

    public function deleteImmediately(User $user, ?Admin $admin = null): void
    {
        $mailPayload = $this->buildMailPayload($user);
        $deletedAt = now();

        $this->deletionService->delete($user, $admin);
        $this->sendImmediateDeletionEmail($mailPayload['email'], $mailPayload['name'], $mailPayload['code']);
        $this->notifySuperadminsOfCompletedDeletions([$mailPayload['label']], $deletedAt, 'immediate');
    }

    /**
     * @return array{deleted: array<int, string>, warned: array<int, string>}
     */
    public function processDailyTasks(): array
    {
        $deleted = [];
        $warned = [];
        $now = now();
        $warningWindowEnd = $now->copy()->addDays(2);

        $warningCandidates = User::query()
            ->whereNotNull('deletion_due_at')
            ->whereNull('deletion_warning_sent_at')
            ->where('deletion_due_at', '>', $now)
            ->where('deletion_due_at', '<=', $warningWindowEnd)
            ->orderBy('deletion_due_at')
            ->get();

        foreach ($warningCandidates as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $mailPayload = $this->buildMailPayload($user);
            $this->sendScheduledDeletionWarningEmail($mailPayload['email'], $mailPayload['name'], $mailPayload['code'], $mailPayload['deadline_text']);

            $user->forceFill([
                'deletion_warning_sent_at' => $now,
            ])->save();

            $warned[] = $mailPayload['label'];
        }

        $dueUsers = User::query()
            ->whereNotNull('deletion_due_at')
            ->where('deletion_due_at', '<=', $now)
            ->orderBy('deletion_due_at')
            ->get();

        foreach ($dueUsers as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $mailPayload = $this->buildMailPayload($user);

            $this->deletionService->delete($user, null);
            $this->sendScheduledDeletionCompletedEmail($mailPayload['email'], $mailPayload['name'], $mailPayload['code']);

            $deleted[] = $mailPayload['label'];
        }

        if ($deleted !== []) {
            $this->notifySuperadminsOfCompletedDeletions($deleted, $now, 'scheduled');
        }

        return [
            'deleted' => $deleted,
            'warned' => $warned,
        ];
    }

    /**
     * @return array{name: string, code: string, email: string, deadline_text: string, label: string}
     */
    private function buildMailPayload(User $user): array
    {
        $user->loadMissing('personalInformation');

        $personalInfo = $user->personalInformation;
        $fullName = trim(implode(' ', array_filter([
            trim((string) ($personalInfo?->first_name ?? '')),
            filled($personalInfo?->middle_name) ? strtoupper(substr((string) $personalInfo->middle_name, 0, 1)) . '.' : '',
            trim((string) ($personalInfo?->surname ?? '')),
            trim((string) ($personalInfo?->name_extension ?? '')),
        ])));

        $name = $fullName !== '' ? $fullName : trim((string) ($user->name ?: 'Applicant'));
        $code = trim((string) ($user->applicant_code ?: ('USER-' . $user->id)));
        $email = $this->resolveRecipientEmail($user);
        $deadlineText = optional($user->deletion_due_at)->format('M d, Y h:i A') ?: 'N/A';

        return [
            'name' => $name,
            'code' => $code,
            'email' => $email,
            'deadline_text' => $deadlineText,
            'label' => sprintf('%s (%s)', $name, $code),
        ];
    }

    private function resolveRecipientEmail(User $user): string
    {
        $user->loadMissing('personalInformation');

        $candidates = [
            trim((string) ($user->personalInformation?->email_address ?? '')),
            trim((string) $user->email),
        ];

        foreach (array_unique($candidates) as $candidate) {
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        $rawCandidates = array_values(array_filter($candidates, fn (string $value): bool => $value !== ''));

        if ($rawCandidates !== []) {
            Log::warning('Applicant deletion email skipped due to invalid recipient address.', [
                'user_id' => $user->id,
                'applicant_code' => $user->applicant_code,
                'candidates' => $rawCandidates,
            ]);
        }

        return '';
    }

    private function sendScheduledDeletionEmail(string $email, string $name, string $code, string $deadlineText): void
    {
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicantScheduledDeletionMail($name, $code, $deadlineText));
        } catch (\Throwable $exception) {
            Log::error('Applicant scheduled deletion email failed', [
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendScheduledDeletionWarningEmail(string $email, string $name, string $code, string $deadlineText): void
    {
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicantScheduledDeletionWarningMail($name, $code, $deadlineText));
        } catch (\Throwable $exception) {
            Log::error('Applicant scheduled deletion warning email failed', [
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendImmediateDeletionEmail(string $email, string $name, string $code): void
    {
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicantImmediateDeletionMail($name, $code));
        } catch (\Throwable $exception) {
            Log::error('Applicant immediate deletion email failed', [
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendScheduledDeletionCompletedEmail(string $email, string $name, string $code): void
    {
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicantScheduledDeletionCompletedMail($name, $code));
        } catch (\Throwable $exception) {
            Log::error('Applicant scheduled deletion completed email failed', [
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendDeletionCancelledEmail(string $email, string $name, string $code): void
    {
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new ApplicantDeletionCancelledMail($name, $code));
        } catch (\Throwable $exception) {
            Log::error('Applicant deletion cancelled email failed', [
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int, string>  $deleted
     */
    private function notifySuperadminsOfCompletedDeletions(array $deleted, ?Carbon $deletedAt = null, string $mode = 'scheduled'): void
    {
        $title = 'Applicant Records';
        $timestampText = ($deletedAt ?? now())->format('M d, Y h:i A');
        $message = count($deleted) === 1
            ? 'Account deletion completed for ' . $deleted[0] . ' at ' . $timestampText . '.'
            : 'Account deletions completed for ' . $this->summarizeLabels($deleted) . ' at ' . $timestampText . '.';

        $admins = Admin::query()
            ->where('role', 'superadmin')
            ->where('is_active', 1)
            ->get();

        foreach ($admins as $admin) {
            Notification::create([
                'notifiable_type' => Admin::class,
                'notifiable_id' => $admin->id,
                'type' => 'info',
                'data' => [
                    'title' => $title,
                    'message' => $message,
                    'link' => route('admin.applicant_records.index', [], false),
                    'category' => 'applicant_records',
                    'deletion_made_at' => ($deletedAt ?? now())->toIso8601String(),
                    'deletion_mode' => $mode,
                ],
            ]);
        }
    }

    /**
     * @param  array<int, string>  $labels
     */
    public function summarizeLabels(array $labels): string
    {
        if (count($labels) <= 3) {
            return implode(', ', $labels);
        }

        $visible = array_slice($labels, 0, 3);
        return implode(', ', $visible) . sprintf(', and %d more', count($labels) - 3);
    }
}

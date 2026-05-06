<?php

namespace App\Services;

use App\Models\Applications;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApplicantRevisionDeadlineService
{
    /**
     * @return array{warned: array<int, string>, expired: array<int, string>}
     */
    public function processDailyTasks(): array
    {
        $timezone = (string) config('app.timezone', 'Asia/Manila');
        $now = Carbon::now($timezone);
        $warningWindowEnd = $now->copy()->addDays(2);

        $warned = [];
        $expired = [];

        $applications = Applications::query()
            ->with(['user', 'vacancy'])
            ->whereNotNull('deadline_date')
            ->whereNotNull('deadline_time')
            ->orderBy('deadline_date')
            ->orderBy('deadline_time')
            ->get();

        foreach ($applications as $application) {
            $applicationModel = Applications::query()->find($application->id);
            if (!$applicationModel instanceof Applications) {
                continue;
            }

            $status = strtolower(trim((string) ($applicationModel->status ?? '')));
            if (in_array($status, ['qualified', 'not qualified', 'cancelled', 'closed'], true)) {
                continue;
            }

            try {
                $deadline = Carbon::parse(
                    $applicationModel->deadline_date . ' ' . $applicationModel->deadline_time,
                    $timezone
                );
            } catch (
                \Throwable $exception
            ) {
                Log::warning('Skipping application deadline task due to invalid deadline timestamp.', [
                    'application_id' => $application->id,
                    'error' => $exception->getMessage(),
                ]);
                continue;
            }

            if ($deadline->lessThanOrEqualTo($now)) {
                $this->expireApplication($applicationModel, $deadline);
                $expired[] = $this->applicationLabel($applicationModel);
                continue;
            }

            if (
                $deadline->lessThanOrEqualTo($warningWindowEnd)
                && $applicationModel->deadline_warning_sent_at === null
            ) {
                $this->sendWarning($applicationModel, $deadline);
                $applicationModel->forceFill([
                    'deadline_warning_sent_at' => $now,
                ])->save();
                $warned[] = $this->applicationLabel($applicationModel);
            }
        }

        return [
            'warned' => $warned,
            'expired' => $expired,
        ];
    }

    private function sendWarning($application, Carbon $deadline): void
    {
        $user = $application->user;
        if (!$user instanceof User) {
            return;
        }

        $vacancyTitle = trim((string) ($application->vacancy?->position_title ?? 'the position'));
        $deadlineText = $deadline->format('M d, Y h:i A');

        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'warning',
            'created_at' => now(),
            'updated_at' => now(),
            'data' => [
                'type' => 'application_overview',
                'title' => 'Revision Deadline Reminder',
                'message' => sprintf(
                    'Your document revision deadline for %s is nearly due. Deadline: %s.',
                    $vacancyTitle,
                    $deadlineText
                ),
                'level' => 'warning',
                'action_url' => route('application_status', ['user' => $user->id, 'vacancy' => $application->vacancy_id], false),
                'vacancy_id' => $application->vacancy_id,
                'deadline_date' => $application->deadline_date,
                'deadline_time' => $application->deadline_time,
                'notified_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    private function expireApplication($application, Carbon $deadline): void
    {
        $user = $application->user;
        if (!$user instanceof User) {
            return;
        }

        $vacancyTitle = trim((string) ($application->vacancy?->position_title ?? 'the position'));

        $application->forceFill([
            'status' => 'Not Qualified',
            'qs_result' => 'Not Qualified',
            'deadline_warning_sent_at' => $application->deadline_warning_sent_at ?? now(),
        ])->save();

        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'error',
            'created_at' => now(),
            'updated_at' => now(),
            'data' => [
                'type' => 'application_overview',
                'title' => 'Revision Deadline Expired',
                'message' => sprintf(
                    'Your document revision deadline for %s has passed. Your application has been marked as Not Qualified.',
                    $vacancyTitle
                ),
                'level' => 'error',
                'action_url' => route('application_status', ['user' => $user->id, 'vacancy' => $application->vacancy_id], false),
                'vacancy_id' => $application->vacancy_id,
                'deadline_date' => $application->deadline_date,
                'deadline_time' => $application->deadline_time,
                'deadline_passed_at' => $deadline->toDateTimeString(),
                'notified_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    private function applicationLabel($application): string
    {
        $name = trim((string) ($application->user?->name ?? 'Applicant'));
        $vacancyId = trim((string) ($application->vacancy_id ?? ''));

        return $vacancyId !== '' ? sprintf('%s (%s)', $name, $vacancyId) : $name;
    }
}
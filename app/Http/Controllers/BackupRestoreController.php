<?php

namespace App\Http\Controllers;

use App\Mail\AutomatedDatabaseBackupMail;
use App\Models\Admin;
use App\Models\BackupAutomationSetting;
use App\Models\DatabaseBackupRun;
use App\Models\Notification;
use App\Services\DatabaseBackupService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class BackupRestoreController extends Controller
{
    private const BACKUP_REMINDER_DAYS = 30;

    public function __construct(
        private readonly DatabaseBackupService $backupService,
    ) {
    }

    public function index(): View
    {
        $this->ensureSuperadmin();

        $backupReminder = $this->buildBackupReminderState();
        $this->notifyOverdueBackupReminder($backupReminder);

        $connection = $this->backupService->databaseConnection();
        $automationSetting = BackupAutomationSetting::query()->first();

        return view('admin.backup_restore', [
            'backupReminder' => $backupReminder,
            'automationSetting' => $automationSetting,
            'databaseName' => $connection['database'],
            'databaseHost' => $connection['host'],
            'dayOptions' => [
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ],
            'recentBackupRuns' => DatabaseBackupRun::query()
                ->orderByDesc('started_at')
                ->limit(10)
                ->get(),
            'nextScheduledRun' => $this->nextScheduledRun($automationSetting),
        ]);
    }

    public function backup(): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $this->ensureSuperadmin();

        try {
            $connection = $this->backupService->databaseConnection();
            $backup = $this->backupService->createBackup([
                'type' => 'manual',
                'directory' => 'app/backups/manual',
                'prefix' => $connection['database'] . '-backup',
            ]);

            activity()
                ->event('backup')
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties([
                    'section' => 'Backup & Restore',
                    'backup_file' => $backup['filename'],
                    'backup_generated_at' => now()->toDateTimeString(),
                ])
                ->log('Generated database backup.');

            return response()
                ->download($backup['absolute_path'], $backup['filename'], [
                    'Content-Type' => $backup['mime_type'],
                ])
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Backup error: ' . $e->getMessage());

            return back()->withErrors(['msg' => 'Backup failed: ' . $e->getMessage()]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $request->validate([
            'sql_file' => 'required|file|max:524288',
        ]);

        try {
            $uploadedFile = $request->file('sql_file');
            if (strtolower((string) $uploadedFile?->getClientOriginalExtension()) !== 'sql') {
                return redirect()
                    ->route('admin.backup.index')
                    ->withErrors(['sql_file' => 'The backup file must use the .sql extension.']);
            }

            $this->backupService->restoreFromSqlFile((string) $uploadedFile?->getRealPath());

            activity()
                ->event('restore')
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties([
                    'section' => 'Backup & Restore',
                    'restore_file' => (string) ($uploadedFile?->getClientOriginalName() ?? ''),
                    'restore_executed_at' => now()->toDateTimeString(),
                ])
                ->log('Restored database from uploaded backup.');

            return redirect()
                ->route('admin.backup.index')
                ->with('success', 'Database restored successfully.');
        } catch (\Throwable $e) {
            Log::error('Restore error: ' . $e->getMessage());

            return redirect()
                ->route('admin.backup.index')
                ->withErrors(['msg' => 'Restore failed: ' . $e->getMessage()]);
        }
    }

    public function saveSchedule(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $setting = BackupAutomationSetting::query()->firstOrNew();
        $isEnabled = $request->boolean('is_enabled');

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'frequency' => [$isEnabled ? 'required' : 'nullable', 'in:daily,weekly'],
            'weekly_day' => ['nullable', 'integer', 'between:0,6'],
            'run_time' => [$isEnabled ? 'required' : 'nullable', 'date_format:H:i'],
            'recipient_emails' => [$isEnabled ? 'required' : 'nullable', 'string'],
        ]);

        $frequency = $validated['frequency'] ?? ($setting->frequency ?? 'daily');
        $runTime = $validated['run_time'] ?? ((string) ($setting->run_time ?? '18:00:00'));
        $recipientEmails = $isEnabled
            ? $this->parseEmailList((string) ($validated['recipient_emails'] ?? ''))
            : (array) ($setting->recipient_emails ?? []);

        if ($isEnabled && $recipientEmails === []) {
            return back()
                ->withErrors(['recipient_emails' => 'Enter at least one valid email address.'])
                ->withInput();
        }

        if ($isEnabled && $frequency === 'weekly' && ! $request->filled('weekly_day')) {
            return back()
                ->withErrors(['weekly_day' => 'Choose the weekday for weekly backups.'])
                ->withInput();
        }

        $setting->fill([
            'is_enabled' => $isEnabled,
            'frequency' => $frequency,
            'weekly_day' => $frequency === 'weekly'
                ? (int) ($validated['weekly_day'] ?? $setting->weekly_day)
                : null,
            'run_time' => strlen($runTime) === 5 ? $runTime . ':00' : $runTime,
            'recipient_emails' => $recipientEmails,
        ]);
        $setting->encrypt_backup = false;
        $setting->encryption_password = null;

        $setting->save();

        return redirect()
            ->route('admin.backup.index', ['tab' => 'scheduler'])
            ->with('success', 'Backup scheduler settings saved successfully.');
    }

    public function sendTestBackupNow(): RedirectResponse
    {
        $this->ensureSuperadmin();

        $setting = BackupAutomationSetting::query()->first();

        if (! $setting) {
            return redirect()
                ->route('admin.backup.index', ['tab' => 'scheduler'])
                ->with('error', 'Save scheduler settings before sending a test backup.');
        }

        if (! $setting->is_enabled) {
            return redirect()
                ->route('admin.backup.index', ['tab' => 'scheduler'])
                ->with('error', 'Enable automated backup emails before sending a test backup.');
        }

        $recipients = array_values(array_filter($setting->recipient_emails ?? []));
        if ($recipients === []) {
            return redirect()
                ->route('admin.backup.index', ['tab' => 'scheduler'])
                ->with('error', 'Add at least one recipient email before sending a test backup.');
        }

        $backup = null;
        try {
            $connection = $this->backupService->databaseConnection();
            $backup = $this->backupService->createBackup([
                'type' => 'test',
                'directory' => 'app/backups/automated',
                'prefix' => $connection['database'] . '-test-backup',
                'setting_id' => $setting->id,
                'mailed_to' => $recipients,
            ]);

            Mail::to($recipients)->send(new AutomatedDatabaseBackupMail(
                databaseName: $connection['database'],
                filePath: $backup['absolute_path'],
                fileName: $backup['filename'],
            ));

            return redirect()
                ->route('admin.backup.index', ['tab' => 'scheduler'])
                ->with('success', 'Test backup email sent successfully.');
        } catch (\Throwable $exception) {
            if (is_array($backup ?? null) && ! empty($backup['stored_path'])) {
                DatabaseBackupRun::query()
                    ->where('stored_path', $backup['stored_path'])
                    ->latest('id')
                    ->first()?->update([
                        'status' => 'failed',
                        'error_message' => $exception->getMessage(),
                    ]);
            }

            Log::error('Test backup error: ' . $exception->getMessage());

            return redirect()
                ->route('admin.backup.index', ['tab' => 'scheduler'])
                ->with('error', $this->friendlyBackupErrorMessage('Test backup failed.', $exception));
        }
    }

    private function ensureSuperadmin(): void
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
    }

    private function latestBackupAt(): ?Carbon
    {
        $latestBackup = Activity::query()
            ->where('event', 'backup')
            ->latest('created_at')
            ->first(['created_at']);

        if (! $latestBackup || empty($latestBackup->created_at)) {
            return null;
        }

        return $latestBackup->created_at instanceof Carbon
            ? $latestBackup->created_at
            : Carbon::parse((string) $latestBackup->created_at);
    }

    private function buildBackupReminderState(): array
    {
        $latestBackupAt = $this->latestBackupAt();
        if (! $latestBackupAt) {
            return [
                'latest_backup_at' => null,
                'days_since_last_backup' => null,
                'is_overdue' => true,
                'status_label' => 'No backup record found',
                'reminder_message' => 'No successful backup record was found. Please generate a full system backup now.',
            ];
        }

        $daysSinceLastBackup = $latestBackupAt->diffInDays(now());
        $isOverdue = $daysSinceLastBackup >= self::BACKUP_REMINDER_DAYS;

        return [
            'latest_backup_at' => $latestBackupAt,
            'days_since_last_backup' => $daysSinceLastBackup,
            'is_overdue' => $isOverdue,
            'status_label' => $isOverdue ? 'Backup overdue' : 'Backup status healthy',
            'reminder_message' => $isOverdue
                ? "The last successful backup was {$daysSinceLastBackup} day(s) ago. Backup is required to protect system data."
                : 'Backup cadence is within the recommended interval.',
        ];
    }

    private function notifyOverdueBackupReminder(array $backupReminder): void
    {
        if (!(bool) ($backupReminder['is_overdue'] ?? false)) {
            return;
        }

        $admin = Auth::guard('admin')->user();
        if (! $admin || ($admin->role ?? null) !== 'superadmin') {
            return;
        }

        $alreadyNotifiedToday = Notification::query()
            ->where('notifiable_type', Admin::class)
            ->where('notifiable_id', $admin->id)
            ->where('data->category', 'backup_reminder')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyNotifiedToday) {
            return;
        }

        Notification::create([
            'notifiable_type' => Admin::class,
            'notifiable_id' => $admin->id,
            'type' => 'warning',
            'data' => [
                'category' => 'backup_reminder',
                'title' => 'Backup Reminder',
                'message' => (string) ($backupReminder['reminder_message'] ?? 'Backup is required to protect system data.'),
                'action_url' => route('admin.backup.index', [], false),
                'level' => 'warning',
            ],
        ]);
    }

    private function parseEmailList(string $input): array
    {
        $items = preg_split('/[\s,;]+/', $input) ?: [];
        $items = array_unique(array_filter(array_map('trim', $items)));

        return array_values(array_filter($items, static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
    }

    private function friendlyBackupErrorMessage(string $prefix, \Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'failed to authenticate on smtp server')
            || str_contains($message, 'webloginrequired')
            || str_contains($message, 'gsmtp')) {
            return $prefix . ' The email could not be sent because the configured mail account was not accepted by the mail server.';
        }

        if (str_contains($message, 'connection could not be established')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'timed out')) {
            return $prefix . ' The system could not connect to the mail server. Please check the mail configuration and try again.';
        }

        if (str_contains($message, 'unable to locate mysqldump')
            || str_contains($message, 'unable to locate mysql')) {
            return $prefix . ' The required MySQL backup tool could not be found on the server.';
        }

        if (str_contains($message, 'uploaded sql file')
            || str_contains($message, 'backup file')) {
            return $prefix . ' The selected backup file could not be processed. Please verify the file and try again.';
        }

        return $prefix . ' Please review the backup settings and try again.';
    }

    private function nextScheduledRun(?BackupAutomationSetting $setting): ?string
    {
        if (! $setting || ! $setting->is_enabled) {
            return null;
        }

        $candidate = now()->copy()->setTimeFromTimeString((string) $setting->run_time);

        if ($setting->frequency === 'daily') {
            if ($candidate->lte(now())) {
                $candidate->addDay();
            }

            return $candidate->format('F j, Y g:i A');
        }

        $weeklyDay = (int) $setting->weekly_day;
        while ($candidate->dayOfWeek !== $weeklyDay || $candidate->lte(now())) {
            $candidate->addDay();
        }

        return $candidate->format('F j, Y g:i A');
    }
}

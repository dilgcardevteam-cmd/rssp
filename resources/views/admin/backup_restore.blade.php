@extends('layout.admin')
@section('title', 'Backup & Restore')
@section('content')
<div class="py-6 space-y-6 max-w-6xl mx-auto font-montserrat">
    @php
        $backupReminder = $backupReminder ?? [
            'latest_backup_at' => null,
            'days_since_last_backup' => null,
            'is_overdue' => true,
            'status_label' => 'No backup record found',
            'reminder_message' => 'Backup is required to protect system data.',
        ];

        $schedulerState = [
            'is_enabled' => old('is_enabled', $automationSetting?->is_enabled),
            'frequency' => old('frequency', $automationSetting?->frequency ?? 'daily'),
            'weekly_day' => old('weekly_day', $automationSetting?->weekly_day),
            'run_time' => old('run_time', $automationSetting?->run_time ? substr((string) $automationSetting->run_time, 0, 5) : '18:00'),
            'recipient_emails' => old('recipient_emails', $automationSetting?->recipient_emails ? implode(', ', $automationSetting->recipient_emails) : ''),
        ];

        $schedulerFieldErrors = ['is_enabled', 'frequency', 'weekly_day', 'run_time', 'recipient_emails'];
        $activeUtilityTab = request()->query('tab') === 'scheduler' ? 'schedulerPanel' : 'backupRestorePanel';
        foreach ($schedulerFieldErrors as $schedulerFieldError) {
            if ($errors->has($schedulerFieldError)) {
                $activeUtilityTab = 'schedulerPanel';
                break;
            }
        }
    @endphp
    <div>
        <h1 class="text-3xl font-semibold text-[#0D2B70]">Backup &amp; Restore</h1>
        <p class="text-sm text-slate-600 mt-2">
            Protect system data by creating secure snapshots, restoring from verified SQL files, and scheduling automated backup delivery.
        </p>
    </div>

    <hr>

    <div class="rounded-xl border {{ $backupReminder['is_overdue'] ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }} px-4 py-3 relative z-40">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full {{ $backupReminder['is_overdue'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                <i data-feather="{{ $backupReminder['is_overdue'] ? 'alert-triangle' : 'shield' }}" class="w-4 h-4"></i>
            </span>
            <div class="text-sm">
                <p class="font-semibold {{ $backupReminder['is_overdue'] ? 'text-amber-800' : 'text-emerald-800' }}">
                    {{ $backupReminder['status_label'] }}
                </p>
                <p class="{{ $backupReminder['is_overdue'] ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $backupReminder['reminder_message'] }}
                </p>
                <p class="mt-1 text-xs text-slate-600">
                    Last successful backup:
                    @if(!empty($backupReminder['latest_backup_at']))
                        {{ \Carbon\Carbon::parse($backupReminder['latest_backup_at'])->format('F j, Y g:i A') }}
                        @if(!is_null($backupReminder['days_since_last_backup']))
                            ({{ (int) $backupReminder['days_since_last_backup'] }} day(s) ago)
                        @endif
                    @else
                        Not yet recorded
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 relative z-50">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <i data-feather="alert-triangle" class="w-4 h-4"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold">Please review the following:</p>
                    <ul class="list-disc list-inside text-sm mt-1 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="flex gap-2 overflow-x-auto pb-1">
        <button
            type="button"
            class="utility-tab inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $activeUtilityTab === 'backupRestorePanel' ? 'border-[#0D2B70] bg-[#0D2B70] text-white shadow-sm' : 'border-blue-200 bg-blue-50 text-[#0D2B70] hover:bg-blue-100' }}"
            data-utility-tab-target="backupRestorePanel"
            data-active-class="border-[#0D2B70] bg-[#0D2B70] text-white shadow-sm"
            data-inactive-class="border-blue-200 bg-blue-50 text-[#0D2B70]"
            aria-selected="{{ $activeUtilityTab === 'backupRestorePanel' ? 'true' : 'false' }}"
        >
            <i data-feather="database" class="w-4 h-4"></i>
            Backup &amp; Restore
        </button>
        <button
            type="button"
            class="utility-tab inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $activeUtilityTab === 'schedulerPanel' ? 'border-emerald-700 bg-emerald-700 text-white shadow-sm' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
            data-utility-tab-target="schedulerPanel"
            data-active-class="border-emerald-700 bg-emerald-700 text-white shadow-sm"
            data-inactive-class="border-emerald-200 bg-emerald-50 text-emerald-700"
            aria-selected="{{ $activeUtilityTab === 'schedulerPanel' ? 'true' : 'false' }}"
        >
            <i data-feather="clock" class="w-4 h-4"></i>
            Scheduled Backup
        </button>
    </div>

    <section id="backupRestorePanel" class="utility-tab-panel space-y-6 {{ $activeUtilityTab === 'backupRestorePanel' ? '' : 'hidden' }}">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-[#0D2B70]/10 to-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#0D2B70] text-white shadow-sm">
                        <i data-feather="database" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-[#0D2B70]">Backup Database</h2>
                        <p class="text-sm text-slate-600">Generate a full SQL snapshot of the live database.</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Use this before major updates or monthly reporting. Store backups in a secure location.
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 space-y-1">
                    <p><span class="font-semibold text-slate-700">Database:</span> {{ $databaseName ?? config('database.connections.' . config('database.default') . '.database') }}</p>
                    <p><span class="font-semibold text-slate-700">Host:</span> {{ $databaseHost ?? config('database.connections.' . config('database.default') . '.host') }}</p>
                    <p><span class="font-semibold text-slate-700">Next Scheduled Run:</span> {{ $nextScheduledRun ?? 'Scheduler disabled' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.backup.run') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0D2B70] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#002C76]">
                        <i data-feather="download" class="w-4 h-4"></i>
                        Generate Backup (.sql)
                    </button>
                    <span class="text-xs text-slate-500">Creates a downloadable SQL file.</span>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-red-100 bg-gradient-to-r from-red-50 to-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white shadow-sm">
                        <i data-feather="refresh-cw" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-red-700">Restore Database</h2>
                        <p class="text-sm text-slate-600">Upload a verified SQL file and replace existing data.</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Restoring will overwrite current records. Create a fresh backup first.
                </div>
                <form id="restoreDatabaseForm" method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="sql_file" class="text-sm font-semibold text-slate-700">SQL Backup File</label>
                        <input id="sql_file" type="file" name="sql_file" accept=".sql,text/plain"
                            class="mt-2 block w-full rounded-lg border border-transparent text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-red-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-red-700 hover:file:bg-red-100"
                            required>
                        <p id="backupFileValidationMessage" class="mt-2 hidden text-xs font-semibold text-red-700">Please select a valid `.sql` backup file to proceed.</p>
                        <p class="mt-2 text-xs text-slate-500">Accepted format: .sql (plain text)</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                        <i data-feather="alert-triangle" class="w-4 h-4"></i>
                        Restore Database
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-[#0D2B70]">Safety Checklist</h3>
        </div>
        <div class="px-6 py-4 text-sm text-slate-600 space-y-2">
            <p>Confirm the backup file source and keep a copy offsite before restoring.</p>
            <p>Run restores during low-traffic hours to minimize disruption.</p>
            <p>Verify outgoing mail settings before relying on automated backup delivery.</p>
            <p>If you are unsure, contact the system administrator before proceeding.</p>
        </div>
    </div>

    </section>

    <section id="schedulerPanel" class="utility-tab-panel space-y-6 {{ $activeUtilityTab === 'schedulerPanel' ? '' : 'hidden' }}">
    <section id="scheduled-backup" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-emerald-50 via-white to-[#EAF2FF]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                        <i data-feather="clock" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-[#0D2B70]">Scheduled Backup</h2>
                        <p class="text-sm text-slate-600">Configure daily or weekly automated backups and email delivery.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ ($automationSetting?->is_enabled) ? 'border border-emerald-100 bg-emerald-50 text-emerald-700' : 'border border-slate-200 bg-slate-100 text-slate-600' }}">
                    <span class="h-2 w-2 rounded-full {{ ($automationSetting?->is_enabled) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ ($automationSetting?->is_enabled) ? 'Scheduler Enabled' : 'Scheduler Disabled' }}
                </span>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <form method="POST" action="{{ route('admin.backup.schedule') }}" class="grid gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                    <label for="is_enabled" class="flex items-center gap-3 text-sm font-semibold text-blue-900">
                        <input id="is_enabled" name="is_enabled" type="checkbox" value="1" @checked($schedulerState['is_enabled']) class="h-4 w-4 rounded border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]">
                        Enable automated backup emails
                    </label>
                </div>

                <div id="schedulerDetails" class="contents">
                <div>
                    <label for="frequency" class="text-sm font-semibold text-slate-700">Frequency</label>
                    <select id="frequency" name="frequency" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="daily" @selected($schedulerState['frequency'] === 'daily')>Daily</option>
                        <option value="weekly" @selected($schedulerState['frequency'] === 'weekly')>Weekly</option>
                    </select>
                </div>

                <div id="weeklyDayContainer" class="{{ $schedulerState['frequency'] === 'weekly' ? '' : 'hidden' }}">
                    <label for="weekly_day" class="text-sm font-semibold text-slate-700">Weekly Day</label>
                    <select id="weekly_day" name="weekly_day" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="">Select day</option>
                        @foreach ($dayOptions as $dayValue => $dayLabel)
                            <option value="{{ $dayValue }}" @selected((string) $schedulerState['weekly_day'] === (string) $dayValue)>{{ $dayLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="run_time" class="text-sm font-semibold text-slate-700">Run Time</label>
                    <input id="run_time" name="run_time" type="time" value="{{ $schedulerState['run_time'] }}"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/20">
                </div>

                <div class="md:col-span-2">
                    <label for="recipient_emails" class="text-sm font-semibold text-slate-700">Recipient Emails</label>
                    <textarea id="recipient_emails" name="recipient_emails" rows="3" placeholder="name@example.com, admin@example.com"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/20">{{ $schedulerState['recipient_emails'] }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">Separate multiple addresses with commas, spaces, semicolons, or new lines.</p>
                </div>

                </div>

                <div class="md:col-span-2 flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                    <button id="testBackupNowButton" type="submit" form="testBackupNowForm" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50" onclick="return confirm('Send a test backup email now using the current scheduler settings?');">
                        <i data-feather="send" class="w-4 h-4"></i>
                        Send Test Backup Now
                    </button>
                    <button id="saveSchedulerButton" type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0D2B70] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#002C76] disabled:cursor-not-allowed disabled:opacity-50">
                        <i data-feather="save" class="w-4 h-4"></i>
                        Save Scheduler
                    </button>
                </div>
            </form>

            <form id="testBackupNowForm" method="POST" action="{{ route('admin.backup.test') }}">
                @csrf
            </form>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last Run</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $automationSetting?->last_run_at?->format('F j, Y g:i A') ?? 'Not yet run' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last Status</p>
                    <p class="mt-2 text-sm font-semibold {{ $automationSetting?->last_status === 'success' ? 'text-emerald-700' : (($automationSetting?->last_status === 'failed') ? 'text-red-700' : 'text-slate-800') }}">
                        {{ $automationSetting?->last_status ? ucfirst($automationSetting->last_status) : 'No status yet' }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Next Run</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $nextScheduledRun ?? 'Scheduler disabled' }}</p>
                </div>
            </div>

            @if ($automationSetting?->last_error)
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <span class="font-semibold">Last scheduler error:</span> {{ $automationSetting->last_error }}
                </div>
            @endif
        </div>
    </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-[#0D2B70]">Recent Backup Activity</h3>
                <p class="mt-1 text-sm text-slate-600">Manual, test, and scheduled backup runs recorded by the utility.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full text-sm table-fixed">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="w-[16%] px-4 py-3 text-left font-semibold">Started</th>
                            <th class="w-[10%] px-4 py-3 text-left font-semibold">Type</th>
                            <th class="w-[10%] px-4 py-3 text-left font-semibold">Status</th>
                            <th class="w-[24%] px-4 py-3 text-left font-semibold">File</th>
                            <th class="w-[18%] px-4 py-3 text-left font-semibold">Recipients</th>
                            <th class="w-[22%] px-4 py-3 text-left font-semibold">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentBackupRuns as $run)
                            <tr class="align-top">
                                <td class="px-4 py-3 text-slate-700 break-words">{{ $run->started_at?->format('M j, Y g:i A') ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700 break-words">{{ ucfirst($run->backup_type) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $run->status === 'success' ? 'bg-emerald-50 text-emerald-700' : ($run->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-sky-50 text-sky-700') }}">
                                        {{ ucfirst($run->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 break-words">{{ $run->filename ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700 break-words">{{ $run->mailed_to ? implode(', ', $run->mailed_to) : '-' }}</td>
                                <td class="px-4 py-3 text-slate-500 break-words">
                                    @if ($run->error_message)
                                        <div class="max-h-28 overflow-y-auto pr-1 text-xs leading-5 whitespace-normal break-words">
                                            {{ $run->error_message }}
                                        </div>
                                    @else
                                        SQL backup
                                    @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">No backup activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    </section>
</div>

<style>
    .backup-file-input-error {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.18);
    }
</style>

<script>
    (function backupRestorePage() {
        if (typeof window.showAppToast === 'function') {
            @if (session('success'))
                window.showAppToast(@json(session('success')), 'success');
            @endif

            @if (session('error'))
                window.showAppToast(@json(session('error')), 'warning');
            @endif
        }

        const restoreForm = document.getElementById('restoreDatabaseForm');
        const backupFileInput = document.getElementById('sql_file');
        const validationMessage = document.getElementById('backupFileValidationMessage');
        const enableSchedulerCheckbox = document.getElementById('is_enabled');
        const frequencySelect = document.getElementById('frequency');
        const weeklyDayContainer = document.getElementById('weeklyDayContainer');
        const schedulerDetails = document.getElementById('schedulerDetails');
        const recipientEmailsField = document.getElementById('recipient_emails');
        const testBackupNowButton = document.getElementById('testBackupNowButton');
        const saveSchedulerButton = document.getElementById('saveSchedulerButton');
        const utilityTabs = Array.from(document.querySelectorAll('[data-utility-tab-target]'));
        const utilityPanels = Array.from(document.querySelectorAll('.utility-tab-panel'));

        function applyClasses(element, classes, add) {
            classes.split(' ').filter(Boolean).forEach(function (className) {
                element.classList.toggle(className, add);
            });
        }

        function activateUtilityTab(panelId) {
            utilityTabs.forEach(function (tab) {
                const isActive = tab.dataset.utilityTabTarget === panelId;
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                applyClasses(tab, tab.dataset.activeClass, isActive);
                applyClasses(tab, tab.dataset.inactiveClass, !isActive);
            });

            utilityPanels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.id !== panelId);
            });
        }

        utilityTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activateUtilityTab(tab.dataset.utilityTabTarget);
            });
        });

        function updateSchedulerDetailsState() {
            if (!enableSchedulerCheckbox || !schedulerDetails) {
                return;
            }

            const isEnabled = enableSchedulerCheckbox.checked;
            const recipientTokens = recipientEmailsField
                ? recipientEmailsField.value.split(/[\s,;]+/).map(function (value) { return value.trim(); }).filter(Boolean)
                : [];
            const hasValidRecipients = recipientTokens.some(function (email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            });
            schedulerDetails.classList.toggle('opacity-60', !isEnabled);
            schedulerDetails.classList.toggle('pointer-events-none', !isEnabled);

            schedulerDetails.querySelectorAll('input, select, textarea, button').forEach(function (field) {
                field.disabled = !isEnabled;
            });

            if (testBackupNowButton) {
                testBackupNowButton.disabled = !isEnabled || !hasValidRecipients;
            }

            if (saveSchedulerButton) {
                saveSchedulerButton.disabled = !isEnabled || !hasValidRecipients;
            }
        }

        if (frequencySelect && weeklyDayContainer) {
            frequencySelect.addEventListener('change', function () {
                weeklyDayContainer.classList.toggle('hidden', frequencySelect.value !== 'weekly');
            });
        }

        if (enableSchedulerCheckbox) {
            enableSchedulerCheckbox.addEventListener('change', function () {
                updateSchedulerDetailsState();
            });
        }

        if (recipientEmailsField) {
            recipientEmailsField.addEventListener('input', function () {
                updateSchedulerDetailsState();
            });
        }

        updateSchedulerDetailsState();

        if (!restoreForm || !backupFileInput || !validationMessage) {
            return;
        }

        function showValidationError(message) {
            validationMessage.textContent = message;
            validationMessage.classList.remove('hidden');
            backupFileInput.classList.remove('backup-file-input-error');
            void backupFileInput.offsetWidth;
            backupFileInput.classList.add('backup-file-input-error');
        }

        function clearValidationError() {
            validationMessage.classList.add('hidden');
            backupFileInput.classList.remove('backup-file-input-error');
        }

        restoreForm.addEventListener('submit', function (event) {
            const selectedFile = backupFileInput.files && backupFileInput.files[0] ? backupFileInput.files[0] : null;
            if (!selectedFile) {
                event.preventDefault();
                showValidationError('Please select a `.sql` backup file to proceed.');
                backupFileInput.focus();
                return;
            }

            if (!selectedFile.name.toLowerCase().endsWith('.sql')) {
                event.preventDefault();
                showValidationError('Only `.sql` backup files are allowed.');
                backupFileInput.focus();
                return;
            }

            clearValidationError();

            if (!window.confirm('Proceed with database restore? This will overwrite existing data.')) {
                event.preventDefault();
            }
        });

        backupFileInput.addEventListener('change', function () {
            const selectedFile = backupFileInput.files && backupFileInput.files[0] ? backupFileInput.files[0] : null;
            if (selectedFile && selectedFile.name.toLowerCase().endsWith('.sql')) {
                clearValidationError();
            }
        });
    })();
</script>
@endsection

<?php

namespace App\Http\Controllers;

use App\Events\UserNotificationPushed;
use App\Events\ExamProgressUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\Applications;
use App\Models\ExamItems;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Jobs\SendExamNotification;
use App\Mail\NotifyApplicantMail;
use App\Enums\ApplicationStatus;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Cookie;
use App\Models\ExamTabViolation;
use App\Models\Notification;
use App\Models\ApplicationExamAttempt;

class ExamController extends Controller
{
    private const ATTENDANCE_WILL_ATTEND = 'will_attend';
    private const ATTENDANCE_WILL_NOT_ATTEND = 'will_not_attend';
    private const EXAM_SUBMISSION_GRACE_SECONDS = 60;

    private function resolveBatchNo(Request $request): int
    {
        $batch = (int) $request->input('batch', $request->query('batch', 1));
        if ($batch < 1) {
            return 1;
        }
        if ($batch > 3) {
            return 3;
        }
        return $batch;
    }

    private function getExamDetailForBatch(string $vacancyId, int $batchNo): ?ExamDetail
    {
        return ExamDetail::where('vacancy_id', $vacancyId)
            ->where('batch_no', $batchNo)
            ->first();
    }

    private function getOrCreateAttempt(Applications $application, int $batchNo): ApplicationExamAttempt
    {
        return ApplicationExamAttempt::firstOrCreate(
            [
                'application_id' => $application->id,
                'vacancy_id' => $application->vacancy_id,
                'user_id' => $application->user_id,
                'batch_no' => $batchNo,
            ],
            [
                'status' => 'pending',
                'answers' => [],
                'scores' => [],
                'tab_violations' => 0,
                'exam_pause_seconds' => 0,
            ]
        );
    }

    private function hasExamWindowExpired(object $application, int $graceSeconds = self::EXAM_SUBMISSION_GRACE_SECONDS): bool
    {
        if (empty($application->exam_end_time)) {
            return false;
        }

        $cutoff = \Carbon\Carbon::parse((string) $application->exam_end_time)->addSeconds(max(0, $graceSeconds));
        return now()->greaterThan($cutoff);
    }

    private function autoCloseExamIfAllParticipantsDone(string $vacancyId, int $batchNo = 1): void
    {
        $examDetail = ExamDetail::where('vacancy_id', $vacancyId)
            ->where('batch_no', $batchNo)
            ->first();
        if (!$examDetail || !$examDetail->is_started) {
            return;
        }

        $participants = ApplicationExamAttempt::query()
            ->where('vacancy_id', $vacancyId)
            ->where('batch_no', $batchNo)
            ->get(['status']);

        if ($participants->isEmpty()) {
            return;
        }

        $hasUnfinishedParticipants = $participants->contains(function (ApplicationExamAttempt $participant) {
            return !ApplicationStatus::equals($participant->status, ApplicationStatus::SUBMITTED);
        });

        if ($hasUnfinishedParticipants) {
            return;
        }

        $closeTime = now()->subSecond();
        $examDetail->update([
            'is_started' => false,
            'time_end' => $closeTime->format('H:i:s'),
        ]);

        activity()
            ->causedBy(auth('admin')->user() ?? auth()->user())
            ->event('auto_close')
            ->withProperties([
                'vacancy_id' => $vacancyId,
                'batch_no' => $batchNo,
                'participants_count' => $participants->count(),
                'section' => 'Exam Management',
            ])
            ->log('Exam auto-closed because all active examinees submitted.');
    }

    private function qualifiedApplicationsQuery(string $vacancy_id)
    {
        return Applications::query()
            ->where('vacancy_id', $vacancy_id)
            ->statusEquals(ApplicationStatus::QUALIFIED->value);
    }

    private function examTamperCountsByUser(string $vacancyId, array $userIds): array
    {
        $normalizedUserIds = collect($userIds)
            ->filter(fn($userId) => is_numeric($userId) && (int) $userId > 0)
            ->map(fn($userId) => (int) $userId)
            ->unique()
            ->values();

        if ($normalizedUserIds->isEmpty()) {
            return [];
        }

        $rows = Activity::query()
            ->where('event', 'exam_tamper')
            ->where('causer_type', User::class)
            ->where('properties->vacancy_id', $vacancyId)
            ->whereIn('causer_id', $normalizedUserIds->all())
            ->selectRaw('causer_id, COUNT(*) as tamper_count')
            ->groupBy('causer_id')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row->causer_id] = (int) ($row->tamper_count ?? 0);
        }

        return $counts;
    }

    private function attendanceStatusLabel(?string $status): string
    {
        return match ($status) {
            self::ATTENDANCE_WILL_ATTEND => 'I Will Attend',
            self::ATTENDANCE_WILL_NOT_ATTEND => 'I Will Not Attend',
            default => 'No Response',
        };
    }

    private function attendanceStatusBadgeClass(?string $status): string
    {
        return match ($status) {
            self::ATTENDANCE_WILL_ATTEND => 'bg-green-100 text-green-800',
            self::ATTENDANCE_WILL_NOT_ATTEND => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    private function canReceiveExamLink(Applications $application): bool
    {
        return $application->exam_attendance_status === self::ATTENDANCE_WILL_ATTEND;
    }

    private function resolvePublicBaseUrl(Request $request): ?string
    {
        $configured = rtrim((string) config('app.url', ''), '/');
        $requestRoot = rtrim((string) $request->getSchemeAndHttpHost(), '/');

        if ($configured !== '') {
            return $configured;
        }

        return $requestRoot !== '' ? $requestRoot : null;
    }

    private function resolveNotificationSenderName(): string
    {
        $admin = auth('admin')->user();

        $senderName = trim((string) ($admin->name ?? $admin->username ?? $admin->email ?? ''));

        if ($senderName !== '') {
            return $senderName;
        }

        return trim((string) config('mail.from.name', 'DILG-CAR Recruitment Team'));
    }

    private function pauseSecondsSince(?string $pausedAt): int
    {
        if (empty($pausedAt)) {
            return 0;
        }

        try {
            return max(0, \Carbon\Carbon::parse((string) $pausedAt)->diffInSeconds(now(), false));
        } catch (\Throwable) {
            return 0;
        }
    }

    private function accumulatedPauseSeconds(?string $pausedAt, int $storedPauseSeconds): int
    {
        return max(0, $storedPauseSeconds) + $this->pauseSecondsSince($pausedAt);
    }

    private function isExamGloballyPaused(?ExamDetail $examDetail): bool
    {
        return !empty($examDetail?->exam_paused_at);
    }

    private function isApplicationPaused(?Applications $application): bool
    {
        return !empty($application?->exam_paused_at);
    }

    private function resolvePauseState(?object $application = null, ?ExamDetail $examDetail = null): array
    {
        return [
            'global_paused' => $this->isExamGloballyPaused($examDetail),
            'global_pause_seconds' => $examDetail ? $this->accumulatedPauseSeconds($examDetail->exam_paused_at, (int) ($examDetail->exam_pause_seconds ?? 0)) : 0,
            'application_paused' => $this->isApplicationPaused($application),
            'application_pause_seconds' => $application ? $this->accumulatedPauseSeconds($application->exam_paused_at, (int) ($application->exam_pause_seconds ?? 0)) : 0,
        ];
    }

    private function resolveExamRemainingSeconds(?object $application = null, ?ExamDetail $examDetail = null): int
    {
        if (!$application || empty($application->exam_end_time)) {
            return 0;
        }

        try {
            $endTime = \Carbon\Carbon::parse((string) $application->exam_end_time);
            $pauseState = $this->resolvePauseState($application, $examDetail);
            $endTime->addSeconds((int) $pauseState['global_pause_seconds'] + (int) $pauseState['application_pause_seconds']);

            return max(0, now()->diffInSeconds($endTime, false));
        } catch (\Throwable) {
            return 0;
        }
    }

    private function setApplicationPauseState(Applications $application, bool $paused): void
    {
        if ($paused) {
            if (empty($application->exam_paused_at)) {
                $application->update([
                    'exam_paused_at' => now(),
                    'exam_paused_by_admin_id' => auth('admin')->id(),
                ]);
            }

            return;
        }

        $pauseSeconds = $this->accumulatedPauseSeconds($application->exam_paused_at, (int) ($application->exam_pause_seconds ?? 0));
        $application->update([
            'exam_paused_at' => null,
            'exam_paused_by_admin_id' => null,
            'exam_pause_seconds' => $pauseSeconds,
        ]);
    }

    private function setGlobalExamPauseState(ExamDetail $examDetail, bool $paused): void
    {
        if ($paused) {
            if (empty($examDetail->exam_paused_at)) {
                $examDetail->update([
                    'exam_paused_at' => now(),
                    'exam_paused_by_admin_id' => auth('admin')->id(),
                ]);
            }

            return;
        }

        $pauseSeconds = $this->accumulatedPauseSeconds($examDetail->exam_paused_at, (int) ($examDetail->exam_pause_seconds ?? 0));
        $examDetail->update([
            'exam_paused_at' => null,
            'exam_paused_by_admin_id' => null,
            'exam_pause_seconds' => $pauseSeconds,
        ]);
    }

    private function createAttendancePromptNotification(int $userId, string $vacancyId, ?JobVacancy $vacancy = null): void
    {
        $vacancy ??= JobVacancy::select('vacancy_id', 'position_title')->where('vacancy_id', $vacancyId)->first();
        $positionTitle = trim((string) ($vacancy?->position_title ?? 'your examination'));

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'type' => 'info',
            'data' => [
                'type' => 'exam_attendance_prompt',
                'category' => 'exam_lifecycle',
                'section' => 'Exam Management',
                'vacancy_id' => $vacancyId,
                'title' => 'Exam Attendance Confirmation',
                'message' => 'Your exam schedule for ' . $positionTitle . ' is available. Please confirm your attendance.',
                'level' => 'info',
                'action_url' => route('exam.attendance.prompt', ['vacancy_id' => $vacancyId], false),
            ],
            'read_at' => null,
        ]);

        if ($this->shouldBroadcastRealtimeNotifications()) {
            broadcast(new UserNotificationPushed($userId, (string) $notification->id, (array) $notification->data));
        }
    }

    private function createExamLinkNotification(int $userId, string $vacancyId, string $token, ?JobVacancy $vacancy = null): void
    {
        $vacancy ??= JobVacancy::select('vacancy_id', 'position_title')->where('vacancy_id', $vacancyId)->first();
        $positionTitle = trim((string) ($vacancy?->position_title ?? 'your examination'));
        $actionUrl = route('user.exam_lobby', [
            'vacancy_id' => $vacancyId,
            'token' => $token,
        ], false);

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'type' => 'info',
            'data' => [
                'type' => 'exam_link_available',
                'category' => 'exam_lifecycle',
                'section' => 'Exam Management',
                'vacancy_id' => $vacancyId,
                'title' => 'Exam Link Available',
                'message' => 'Your exam link for ' . $positionTitle . ' is now available. Tap this notification to open the exam lobby.',
                'level' => 'info',
                'action_url' => $actionUrl,
            ],
            'read_at' => null,
        ]);

        if ($this->shouldBroadcastRealtimeNotifications()) {
            broadcast(new UserNotificationPushed($userId, (string) $notification->id, (array) $notification->data));
        }
    }

    private function shouldBroadcastRealtimeNotifications(): bool
    {
        return in_array((string) config('broadcasting.default'), ['reverb', 'pusher', 'ably'], true);
    }

    private function shouldBroadcastExamUpdates(): bool
    {
        return in_array((string) config('broadcasting.default'), ['reverb', 'pusher', 'ably'], true);
    }

    private function broadcastExamProgress(Applications $application, string $type, array $meta = []): void
    {
        if (!$this->shouldBroadcastExamUpdates()) {
            return;
        }

        try {
            broadcast(new ExamProgressUpdated(
                vacancyId: (string) $application->vacancy_id,
                userId: (int) $application->user_id,
                type: $type,
                status: is_string($application->status) ? $application->status : null,
                tabViolations: is_numeric($application->tab_violations) ? (int) $application->tab_violations : null,
                occurredAt: now()->toIso8601String(),
                meta: $meta,
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to broadcast exam progress update', [
                'vacancy_id' => $application->vacancy_id,
                'user_id' => $application->user_id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function parseDurationMilliseconds(Request $request, ?string $startedAt, ?string $endedAt): ?int
    {
        $durationMs = $request->input('duration_milliseconds');

        if (is_numeric($durationMs)) {
            return max(0, (int) round((float) $durationMs));
        }

        if (!$startedAt || !$endedAt) {
            return null;
        }

        try {
            $started = \Carbon\Carbon::parse($startedAt);
            $ended = \Carbon\Carbon::parse($endedAt);

            return max(0, (int) ($ended->getTimestampMs() - $started->getTimestampMs()));
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveMaxViolations(string $vacancyId, ?ExamDetail $examDetail = null): int
    {
        $maxViolations = (int) (($examDetail?->max_violations) ?? ExamDetail::where('vacancy_id', $vacancyId)->value('max_violations') ?? 12);

        return max(1, $maxViolations);
    }

    private function formatResumeDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        }

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    private function resolveResumeExamState(?object $application, ?ExamDetail $examDetail = null): array
    {
        $maxViolations = $this->resolveMaxViolations((string) $application->vacancy_id, $examDetail);
        $tabViolations = (int) ($application->tab_violations ?? 0);

        if (!ApplicationStatus::equals($application->status, ApplicationStatus::SUBMITTED)) {
            return [
                'can_resume' => false,
                'reason' => 'Only submitted exam attempts can be resumed.',
                'remaining_seconds' => 0,
                'remaining_label' => null,
                'max_violations' => $maxViolations,
                'is_tab_threshold_submission' => false,
            ];
        }

        $isTabThresholdSubmission = $tabViolations >= $maxViolations;

        if (empty($application->exam_started_at) || empty($application->exam_submitted_at) || empty($application->exam_end_time)) {
            return [
                'can_resume' => false,
                'reason' => 'The saved exam timing data is incomplete for this attempt.',
                'remaining_seconds' => 0,
                'remaining_label' => null,
                'max_violations' => $maxViolations,
                'is_tab_threshold_submission' => $isTabThresholdSubmission,
            ];
        }

        try {
            $submittedAt = \Carbon\Carbon::parse((string) $application->exam_submitted_at);
            $endTime = \Carbon\Carbon::parse((string) $application->exam_end_time);
            $remainingSeconds = max(0, $submittedAt->diffInSeconds($endTime, false));
        } catch (\Throwable) {
            return [
                'can_resume' => false,
                'reason' => 'The saved exam timing data could not be parsed.',
                'remaining_seconds' => 0,
                'remaining_label' => null,
                'max_violations' => $maxViolations,
                'is_tab_threshold_submission' => $isTabThresholdSubmission,
            ];
        }

        if ($remainingSeconds < 1) {
            return [
                'can_resume' => false,
                'reason' => 'There was no remaining exam time left when the submission happened.',
                'remaining_seconds' => 0,
                'remaining_label' => null,
                'max_violations' => $maxViolations,
                'is_tab_threshold_submission' => $isTabThresholdSubmission,
            ];
        }

        return [
            'can_resume' => true,
            'reason' => null,
            'remaining_seconds' => $remainingSeconds,
            'remaining_label' => $this->formatResumeDuration($remainingSeconds),
            'max_violations' => $maxViolations,
            'is_tab_threshold_submission' => $isTabThresholdSubmission,
        ];
    }

    private function mapQualifiedApplicant(Applications $app): array
    {
        $attendanceStatus = $app->exam_attendance_status;
        $attendancePromptNotification = $this->resolveAttendancePromptNotification($app);
        $attendancePromptSentAt = $attendancePromptNotification?->created_at;
        $hasAttendanceResponse = !empty($attendanceStatus) || !is_null($app->exam_attendance_responded_at);

        return [
            'id' => $app->id,
            'user_id' => $app->user_id,
            'vacancy_id' => $app->vacancy_id,
            'name' => $app->user->name ?? 'Unknown',
            'email' => $app->user->email ?? 'N/A',
            'application_date' => $app->created_at?->format('M d, Y') ?? '-',
            'status' => $app->status,
            'link_sent_at' => $app->link_sent_at,
            'link_sent' => !is_null($app->link_sent_at),
            'read_at' => $app->read_at,
            'is_read' => !is_null($app->read_at),
            'attendance_status' => $attendanceStatus,
            'attendance_label' => $this->attendanceStatusLabel($attendanceStatus),
            'attendance_badge_class' => $this->attendanceStatusBadgeClass($attendanceStatus),
            'attendance_remark' => $app->exam_attendance_remark,
            'attendance_responded_at' => optional($app->exam_attendance_responded_at)->format('M d, Y h:i A'),
            'attendance_prompt_sent' => !is_null($attendancePromptSentAt),
            'attendance_prompt_sent_at' => optional($attendancePromptSentAt)->format('Y-m-d H:i:s'),
            'has_attendance_response' => $hasAttendanceResponse,
            'can_receive_exam_link' => $this->canReceiveExamLink($app),
        ];
    }

    private function resolveAttendancePromptNotification(Applications $app)
    {
        $user = $app->user;

        if ($user && $user->relationLoaded('notifications')) {
            return $user->notifications
                ->sortByDesc('created_at')
                ->first(function ($notification) use ($app) {
                    return data_get($notification, 'data.type') === 'exam_attendance_prompt'
                        && (string) data_get($notification, 'data.vacancy_id') === (string) $app->vacancy_id;
                });
        }

        return Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $app->user_id)
            ->where('data->type', 'exam_attendance_prompt')
            ->where('data->vacancy_id', $app->vacancy_id)
            ->latest()
            ->first();
    }

    private function mapAttendanceApplicant(Applications $app): array
    {
        $attendanceStatus = $app->exam_attendance_status;

        return [
            'id' => $app->id,
            'user_id' => $app->user_id,
            'vacancy_id' => $app->vacancy_id,
            'name' => $app->user->name ?? 'Unknown',
            'email' => $app->user->email ?? 'N/A',
            'attendance_status' => $attendanceStatus,
            'attendance_label' => $this->attendanceStatusLabel($attendanceStatus),
            'attendance_badge_class' => $this->attendanceStatusBadgeClass($attendanceStatus),
            'attendance_remark' => $app->exam_attendance_remark,
            'attendance_responded_at' => optional($app->exam_attendance_responded_at)->format('M d, Y h:i A'),
            'can_receive_exam_link' => $this->canReceiveExamLink($app),
            'link_sent' => !is_null($app->link_sent_at),
        ];
    }

    public function submit(Request $request, $vacancy_id)
    {    //dd($request->all());

        $authenticatedUserId = (int) auth()->id();
        if ($authenticatedUserId < 1) {
            abort(401, 'Authentication required.');
        }

        $validated = $request->validate([
            'vacancy_id' => 'required|string',
            'user_id' => 'nullable|integer',
            'answers' => 'nullable|array',
            'batch' => 'nullable|integer|min:1|max:3',
        ]);
        $batchNo = $this->resolveBatchNo($request);

        if ((string) $validated['vacancy_id'] !== (string) $vacancy_id) {
            abort(422, 'Exam payload vacancy mismatch.');
        }

        $answerRecord = Applications::where('vacancy_id', $validated['vacancy_id'])
            ->where('user_id', $authenticatedUserId)
            ->firstOrFail();
        $attempt = $this->getOrCreateAttempt($answerRecord, $batchNo);

        if ($this->hasExamWindowExpired($attempt)) {
            if ($attempt->status !== ApplicationStatus::SUBMITTED->value || is_null($attempt->exam_submitted_at)) {
                $attempt->update([
                    'status' => ApplicationStatus::SUBMITTED->value,
                    'exam_submitted_at' => $attempt->exam_submitted_at ?? now(),
                ]);
                $this->broadcastExamProgress($answerRecord, 'expired-window-submit-blocked');
                $this->autoCloseExamIfAllParticipantsDone((string) $vacancy_id, $batchNo);
            }

            Log::warning('Blocked late exam submission beyond grace window.', [
                'user_id' => $authenticatedUserId,
                'vacancy_id' => $vacancy_id,
                'batch_no' => $batchNo,
                'exam_end_time' => $attempt->exam_end_time,
                'now' => now()->toDateTimeString(),
                'grace_seconds' => self::EXAM_SUBMISSION_GRACE_SECONDS,
            ]);

            return redirect()
                ->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id])
                ->with('error', 'Exam time is over. Late answers were not accepted.');
        }

        // Auto-check MCQ answers and compute per-item scores (tolerant to key/value mismatch and case)
        $items = ExamItems::select('id', 'ans', 'is_essay', 'choices')
            ->where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->get();

        $scores = [];
        $totalMcq = 0;
        $correctMcq = 0;

        foreach ($items as $item) {
            $given = $validated['answers'][$item->id] ?? null;
            if ((int)$item->is_essay === 0) {
                $totalMcq++;
                $isCorrect = false;
                if (!is_null($given)) {
                    $givenStr = trim((string)$given);
                    $ansStr = trim((string)($item->ans ?? ''));
                    // Direct key match (e.g., "A" === "A"), case-insensitive
                    if (strcasecmp($givenStr, $ansStr) === 0) {
                        $isCorrect = true;
                    } else {
                        // Fallback: if 'ans' stores the choice text, verify mapping key->value match
                        $choices = is_array($item->choices) ? $item->choices : [];
                        foreach ($choices as $key => $val) {
                            if (strcasecmp(trim((string)$val), $ansStr) === 0 && strcasecmp(trim((string)$key), $givenStr) === 0) {
                                $isCorrect = true;
                                break;
                            }
                        }
                    }
                }
                $scores[$item->id] = $isCorrect ? 1 : 0;
                if ($isCorrect) $correctMcq++;
            } else {
                // Essays are scored later by admin
                $scores[$item->id] = null;
            }
        }

        $resultStr = $totalMcq > 0 ? ($correctMcq . '/' . $totalMcq) : null;

        // Update the answers and scores fields
        $attempt->update([
            'answers' => $validated['answers'] ?? [],
            'scores' => $scores,
            'result' => $resultStr,
            'status' => ApplicationStatus::SUBMITTED->value,
            'exam_submitted_at' => now(),
        ]);
        $this->broadcastExamProgress($answerRecord, 'submitted', [
            'result' => $resultStr,
            'batch_no' => $batchNo,
        ]);
        $this->autoCloseExamIfAllParticipantsDone((string) $vacancy_id, $batchNo);

        //$message = "submitted successfully";
        //info($message);

        activity()
            ->causedBy(auth()->user())
            ->event('submit')
            ->withProperties(['vacancy_id' => $vacancy_id, 'user_id' => $authenticatedUserId, 'batch_no' => $batchNo, 'section' => 'Exam'])
            ->log('Submitted exam answers.');

        return redirect()->route('user.exam_thankyou', compact('vacancy_id', ));
    }

    public function autoSave(Request $request, $vacancy_id)
    {
        $authenticatedUserId = (int) auth()->id();
        if ($authenticatedUserId < 1) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $validated = $request->validate([
            'vacancy_id' => 'required|string',
            'user_id' => 'nullable|integer',
            'answers' => 'nullable|array',
            'batch' => 'nullable|integer|min:1|max:3',
        ]);
        $batchNo = $this->resolveBatchNo($request);

        if ((string) $validated['vacancy_id'] !== (string) $vacancy_id) {
            return response()->json([
                'success' => false,
                'message' => 'Exam payload vacancy mismatch.',
            ], 422);
        }

        $answerRecord = Applications::where('vacancy_id', $validated['vacancy_id'])
            ->where('user_id', $authenticatedUserId)
            ->firstOrFail();
        $attempt = $this->getOrCreateAttempt($answerRecord, $batchNo);

        // If exam is already submitted, don't allow autosave
        if ($attempt->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Exam already submitted']);
        }

        if ($this->hasExamWindowExpired($attempt)) {
            if ($attempt->status !== ApplicationStatus::SUBMITTED->value || is_null($attempt->exam_submitted_at)) {
                $attempt->update([
                    'status' => ApplicationStatus::SUBMITTED->value,
                    'exam_submitted_at' => $attempt->exam_submitted_at ?? now(),
                ]);
                $this->broadcastExamProgress($answerRecord, 'expired-window-autosave-blocked');
                $this->autoCloseExamIfAllParticipantsDone((string) $vacancy_id, $batchNo);
            }

            return response()->json([
                'success' => false,
                'expired' => true,
                'message' => 'Exam time is over. Autosave is now closed.',
            ], 409);
        }

        // Calculate scores similar to submit, but don't finalize
        $items = ExamItems::select('id', 'ans', 'is_essay', 'choices')
            ->where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->get();

        $scores = [];
        $totalMcq = 0;
        $correctMcq = 0;

        foreach ($items as $item) {
            $given = $validated['answers'][$item->id] ?? null;
            if ((int)$item->is_essay === 0) {
                $totalMcq++;
                $isCorrect = false;
                if (!is_null($given)) {
                    $givenStr = trim((string)$given);
                    $ansStr = trim((string)($item->ans ?? ''));
                    // Direct key match (e.g., "A" === "A"), case-insensitive
                    if (strcasecmp($givenStr, $ansStr) === 0) {
                        $isCorrect = true;
                    } else {
                        // Fallback: if 'ans' stores the choice text, verify mapping key->value match
                        $choices = is_array($item->choices) ? $item->choices : [];
                        foreach ($choices as $key => $val) {
                            if (strcasecmp(trim((string)$val), $ansStr) === 0 && strcasecmp(trim((string)$key), $givenStr) === 0) {
                                $isCorrect = true;
                                break;
                            }
                        }
                    }
                }
                $scores[$item->id] = $isCorrect ? 1 : 0;
                if ($isCorrect) $correctMcq++;
            } else {
                // Essays are scored later by admin
                $scores[$item->id] = null;
            }
        }

        $resultStr = $totalMcq > 0 ? ($correctMcq . '/' . $totalMcq) : null;

        // Update the answers and scores fields
        $attempt->answers = $validated['answers'] ?? [];
        $attempt->scores = $scores;
        $attempt->result = $resultStr;
        // Do NOT change status to submitted
        $attempt->save();
        $this->broadcastExamProgress($answerRecord, 'autosaved', [
            'result' => $resultStr,
            'batch_no' => $batchNo,
        ]);

        return response()->json(['success' => true]);
    }

    public function getExaminationDates(Request $request)
    {
        try {
            // Fetch all scheduled exams with vacancy details
            $exams = ExamDetail::with('vacancy')
                ->where('date', '>=', now()->toDateString()) // Only upcoming exams
                ->whereNotNull('date')
                ->whereNotNull('time')
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get()
                ->map(function ($exam) {
                    return [
                        'id' => $exam->id,
                        'vacancy_id' => $exam->vacancy_id,
                        'position_title' => $exam->vacancy->position_title ?? 'N/A',
                        'vacancy_type' => $exam->vacancy->vacancy_type ?? 'N/A',
                        'date' => $exam->date,
                        'time' => $exam->time,
                        'time_end' => $exam->time_end,
                        'venue' => $exam->place ?? 'TBA',
                        'formatted_date' => \Carbon\Carbon::parse($exam->date)->format('F d, Y'),
                        'formatted_time' => \Carbon\Carbon::parse($exam->time)->format('h:i A'),
                        'formatted_time_end' => $exam->time_end ? \Carbon\Carbon::parse($exam->time_end)->format('h:i A') : null,
                        'status' => $this->getExamStatus($exam),
                    ];
                });

            // Group by date for easier frontend processing
            $groupedByDate = $exams->groupBy('date')->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'formatted_date' => \Carbon\Carbon::parse($date)->format('F d, Y'),
                    'exams' => $items,
                    'count' => $items->count()
                ];
            })->values();

            return response()->json([
                'success' => true,
                'exams' => $exams,
                'grouped_by_date' => $groupedByDate,
                'count' => $exams->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching examination dates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch examination dates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getExamStatus($exam)
    {
        if (!$exam->date || !$exam->time) {
            return 'Unscheduled';
        }
        
        $startDateTime = \Carbon\Carbon::parse($exam->date . ' ' . $exam->time);
        $endDateTime = $exam->time_end 
            ? \Carbon\Carbon::parse($exam->date . ' ' . $exam->time_end)
            : $startDateTime->copy()->addMinutes($exam->duration ?? 0);
        
        $now = now();
        
        if ($now->gt($endDateTime)) {
            return 'Completed';
        } elseif ($exam->is_started || $now->between($startDateTime, $endDateTime)) {
            return 'Ongoing';
        } else {
            return 'Scheduled';
        }
    }

    private function denyViewerAccess(Request $request, string $message = 'Viewer role has read-only exam monitoring access.')
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = auth('admin')->user();
        if (Gate::forUser($admin)->allows('admin.exam.manage')) {
            return null;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()
            ->route('admin_exam_management')
            ->with('error', $message);
    }

    private function isViewerRole(): bool
    {
        if (!auth('admin')->check()) {
            return false;
        }

        $admin = auth('admin')->user();
        return Gate::forUser($admin)->allows('admin.exam.monitor')
            && Gate::forUser($admin)->denies('admin.exam.manage');
    }

    public function logSwitch(Request $request)
    {
        $violationType = strtolower(trim((string) $request->input('type', 'tab-switch')));
        Log::info('Exam client-side violation reported.', [
            'type' => $violationType,
            'time' => $request->input('time'),
        ]);

        $user = auth()->user();
        $vacancyId = $request->input('vacancy_id');
        $countFromClient = (int) ($request->input('count') ?? 0);
        $startedAt = $request->input('started_at');
        $endedAt = $request->input('ended_at');
        $durationMs = $this->parseDurationMilliseconds($request, $startedAt, $endedAt);
        $durationSeconds = is_numeric($request->input('duration_seconds'))
            ? max(0, (int) $request->input('duration_seconds'))
            : ($durationMs !== null ? intdiv($durationMs, 1000) : null);
        $isTamperSignal = in_array($violationType, ['clock-tamper', 'timezone-tamper', 'datetime-tamper'], true);

        $applicationQuery = Applications::where('user_id', $user?->id);
        if ($vacancyId) {
            $applicationQuery->where('vacancy_id', $vacancyId);
        } else {
            $applicationQuery->where('status', 'in-progress');
        }
        $application = $applicationQuery->orderByDesc('exam_started_at')->first();

        if ($application) {
            if (!$isTamperSignal) {
                $application->tab_violations = (int)($application->tab_violations ?? 0) + 1;
                $application->last_tab_violation_at = now();
                $application->save();
            }

            $maxViolations = (int) (ExamDetail::where('vacancy_id', $application->vacancy_id)->value('max_violations') ?? 12);
            if ($maxViolations < 1) {
                $maxViolations = 1;
            }

            if (!$isTamperSignal) {
                try {
                    $violationPayload = [
                        'user_id' => $application->user_id,
                        'vacancy_id' => $application->vacancy_id,
                        'started_at' => $startedAt ? \Carbon\Carbon::parse($startedAt) : now(),
                        'ended_at' => $endedAt ? \Carbon\Carbon::parse($endedAt) : null,
                        'duration_seconds' => $durationSeconds,
                    ];

                    if (Schema::hasColumn('exam_tab_violations', 'duration_milliseconds')) {
                        $violationPayload['duration_milliseconds'] = $durationMs;
                    }

                    ExamTabViolation::create($violationPayload);
                } catch (\Throwable $e) {
                    Log::error('Failed to persist exam tab violation', ['error' => $e->getMessage()]);
                }
            }

            $notificationTitle = 'Tab Switch Detected';
            $notificationMessage = ($user?->name ?? 'Applicant') . ' switched tabs (' . $application->tab_violations . ' total).';
            $activityEvent = 'tab_violation';
            $activityDescription = 'Exam tab switch violation recorded.';
            $progressType = 'tab-violation';

            if ($isTamperSignal) {
                $notificationTitle = 'Exam Time Tamper Signal';
                $notificationMessage = ($user?->name ?? 'Applicant') . ' triggered a ' . strtoupper(str_replace('-', ' ', $violationType)) . ' signal during exam.';
                $activityEvent = 'exam_tamper';
                $activityDescription = 'Exam time/date/timezone tamper signal recorded.';
                $progressType = 'tamper-detected';
            }

            activity()
                ->causedBy($user)
                ->event($activityEvent)
                ->withProperties([
                    'type' => $violationType,
                    'vacancy_id' => $application->vacancy_id,
                    'user_id' => $application->user_id,
                    'count' => $application->tab_violations,
                    'time' => $request->input('time'),
                    'timezone' => $request->input('timezone'),
                    'timezone_offset_minutes' => $request->input('timezone_offset_minutes'),
                    'clock_drift_ms' => is_numeric($request->input('clock_drift_ms')) ? (int) $request->input('clock_drift_ms') : null,
                    'client_now_iso' => $request->input('client_now_iso'),
                    'server_now_iso' => $request->input('server_now_iso'),
                    'duration_seconds' => $durationSeconds,
                    'duration_milliseconds' => $durationMs,
                    'client_count' => $countFromClient,
                    'section' => 'Exam'
                ])
                ->log($activityDescription);

            $this->broadcastExamProgress($application, $progressType, [
                'type' => $violationType,
                'duration_seconds' => $durationSeconds,
                'duration_milliseconds' => $durationMs,
                'count' => (int) $application->tab_violations,
            ]);

            // Broadcast-style admin notification (visible to all admins)
            try {
                \App\Models\Notification::create([
                    'notifiable_type' => 'App\Models\Admin',
                    'notifiable_id' => null, // visible to all admins
                    'type' => 'warning',
                    'data' => [
                        'category' => 'exam_lifecycle',
                        'title' => $notificationTitle,
                        'message' => $notificationMessage,
                        'violation_type' => $violationType,
                        'user_id' => $application->user_id,
                        'vacancy_id' => $application->vacancy_id,
                        'count' => $application->tab_violations,
                        'link' => route('admin.view_exam', ['vacancy_id' => $application->vacancy_id, 'user_id' => $application->user_id], false),
                    ],
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to create admin notification for tab switch', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'ok' => true,
                'count' => (int) $application->tab_violations,
                'max_violations' => $maxViolations,
                'auto_submit' => false,
                'duration_seconds' => $durationSeconds,
                'duration_milliseconds' => $durationMs,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function editExam(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot edit exam content.')) {
            return $denied;
        }

        //info('edit_exam');
        $batchNo = $this->resolveBatchNo($request);
        $exam_items = ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->get();
        $vacancy = JobVacancy::select('position_title', 'vacancy_type')->where('vacancy_id', $vacancy_id)->firstOrFail();

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Accessed edit exam page.');

        return view('admin.exam_edit', ['exam_items' => $exam_items, 'vacancy_id' => $vacancy_id, 'vacancy' => $vacancy, 'selectedBatch' => $batchNo]);
    }

    public function updateExam(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot update exam content.')) {
            return $denied;
        }
        $batchNo = $this->resolveBatchNo($request);

        // Handle both form-encoded and JSON requests
        $raw = $request->input('questions') ?? $request->getContent();

        // If it's a JSON request, parse the JSON body
        if ($request->isJson() && is_null($request->input('questions'))) {
            $jsonData = json_decode($request->getContent(), true);
            $raw = $jsonData['questions'] ?? '';
        }

        \Log::info('updateExam called', ['vacancy_id' => $vacancy_id, 'raw_questions' => substr($raw, 0, 200), 'is_json' => $request->isJson()]);
        $questions = json_decode($raw, true);

        // Try a fallback if JSON decode failed (sometimes escaped strings arrive)
        if (is_null($questions) && is_string($raw) && $raw !== '') {
            $questions = json_decode(stripslashes($raw), true);
        }

        if (!is_array($questions)) {
            \Log::error('Invalid questions payload', ['raw' => $raw]);

            if ($request->isJson()) {
                return response()->json(['msg' => 'Invalid questions payload.'], 400);
            }
            return back()->withErrors(['msg' => 'Invalid questions payload.']);
        }

        // Validate if needed
        foreach ($questions as $idx => $q) {
            // Check question text - frontend may use 'text' or 'duration'
            // Use ternary to handle empty strings properly (not just null)
            $questionText = trim((string) ($q['text'] ?? '')) ?: trim((string) ($q['duration'] ?? ''));

            if ($questionText === '') {
                $msg = "Question #" . ($idx + 1) . " must have text.";

                if ($request->isJson()) {
                    return response()->json(['msg' => $msg], 422);
                }
                return back()->withErrors(['msg' => $msg]);
            }
        }

        $existingItemsCount = ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->count();

        // Delete existing questions for this vacancy
        ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->delete();

        try {
            foreach ($questions as $q) {
                $typeRaw = strtolower((string) ($q['type'] ?? ''));
                $isMCQ = in_array($typeRaw, ['mcq', 'multiple_choice', 'multiple choice', 'multiple-choice']);
                $isEssay = in_array($typeRaw, ['essay', 'essays']);

                $ans = null;
                $choices = null;

                if ($isMCQ) {
                    $choices = is_array($q['choices'] ?? null) ? array_values($q['choices']) : [];

                    if (isset($q['correctAnswer']) && is_numeric($q['correctAnswer'])) {
                        $idx = (int) $q['correctAnswer'];
                        if (isset($choices[$idx]))
                            $ans = $choices[$idx];
                    }

                    if ($ans === null && !empty($q['answer'])) {
                        $ans = $q['answer'];
                    }

                    // Ensure choices is null when empty
                    if (empty($choices))
                        $choices = null;
                }

                // Prefer 'text' then 'duration'
                $questionText = trim((string) ($q['text'] ?? '')) ?: trim((string) ($q['duration'] ?? ''));

                $essayMax = null;
                if ($isEssay) {
                    $essayMax = isset($q['essayMax']) && is_numeric($q['essayMax']) ? max(0, (int)$q['essayMax']) : null;
                }

                $created = ExamItems::create([
                    'vacancy_id' => $vacancy_id,
                    'batch_no' => $batchNo,
                    'question' => $questionText,
                    'is_essay' => $isEssay ? 1 : 0,
                    'ans' => $ans,
                    'choices' => $choices,
                    'essay_max_score' => $essayMax,
                ]);

                \Log::info('Question created', ['id' => $created->id, 'question' => substr($questionText, 0, 50)]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating exam items', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $msg = 'Error saving questions: ' . $e->getMessage();

            if ($request->isJson()) {
                return response()->json(['msg' => $msg], 500);
            }
            return back()->withErrors(['msg' => $msg]);
        }

        $exam_items = ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->get();

        $action = ($existingItemsCount > 0) ? 'update' : 'create';

        activity()
            ->causedBy(auth('admin')->user())
            ->event($action)
            ->withProperties(['vacancy_id' => $vacancy_id, 'batch_no' => $batchNo, 'questions_count' => count($questions), 'section' => 'Exam Management'])
            ->log($action . 'd exam questions.');

        if ($request->isJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully.',
                'questions_count' => count($questions)
            ]);
        }

        return redirect()->route('admin.exam.edit', ['vacancy_id' => $vacancy_id])->with('success', 'Exam updated successfully.');
    }

    public function examManagement(Request $request)
    {
        $isViewer = $this->isViewerRole();
        $search = $request->input('search');
        $jobType = $isViewer ? null : $request->input('job_type');
        $examStatus = $request->input('exam_status');

        if ($isViewer) {
            $allowedStatuses = ['Scheduled', 'Ongoing', 'Completed'];
            if ($examStatus === null || $examStatus === '') {
                $examStatus = $allowedStatuses;
            } elseif (!is_array($examStatus) && !in_array($examStatus, $allowedStatuses, true)) {
                $examStatus = 'Ongoing';
            }
        }

        $jobVacancies = JobVacancy::query()
            ->with(['examDetail']) // Eager load the relationship
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('position_title', 'like', '%' . $search . '%')
                        ->orWhere('vacancy_id', 'like', '%' . $search . '%');
                });
            })
            ->when($jobType, function ($query, $jobType) {
                $query->where('vacancy_type', $jobType);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Append status to each vacancy
        $jobVacancies->transform(function ($vacancy) {
            $status = 'Unscheduled';
            if ($vacancy->examDetail) {
                $status = $this->getExamStatus($vacancy->examDetail);
            }
            $vacancy->exam_status = $status;
            return $vacancy;
        });

        // Filter by Exam Status (PHP-side filtering since status is calculated)
        if ($examStatus) {
            $jobVacancies = $jobVacancies->filter(function ($vacancy) use ($examStatus) {
                if (is_array($examStatus)) {
                    return in_array($vacancy->exam_status, $examStatus, true);
                }
                return $vacancy->exam_status === $examStatus;
            })->values(); // Reset keys
        }

        // Prioritize exam statuses in the list: Scheduled first, Completed last.
        $statusPriority = [
            'Scheduled' => 0,
            'Ongoing' => 1,
            'Unscheduled' => 2,
            'Completed' => 3,
        ];

        $jobVacancies = $jobVacancies
            ->sort(function ($a, $b) use ($statusPriority) {
                $priorityA = $statusPriority[$a->exam_status] ?? 99;
                $priorityB = $statusPriority[$b->exam_status] ?? 99;

                if ($priorityA !== $priorityB) {
                    return $priorityA <=> $priorityB;
                }

                $timeA = $a->created_at ? $a->created_at->timestamp : 0;
                $timeB = $b->created_at ? $b->created_at->timestamp : 0;
                return $timeB <=> $timeA;
            })
            ->values();

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($jobVacancies);
        }

        return view('admin.exam_management', [
            'vacancies' => $jobVacancies,
            'search' => $search,
            'isViewer' => $isViewer,
        ]);
    }

    public function manageExam(Request $request, $vacancy_id)
    {
        $isViewer = $this->isViewerRole();
        $batchNo = $this->resolveBatchNo($request);
        $vacancy = JobVacancy::select('vacancy_id', 'position_title', 'vacancy_type')->where('vacancy_id', $vacancy_id)->first();
        // Only fetch participants who have entered the lobby (read_at is not null), then merge attempt state per batch.
        $baseParticipants = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('read_at')
            ->select('id', 'user_id', 'vacancy_id', 'read_at')
            ->with('user:id,name')
            ->get();

        $attemptsByApplication = ApplicationExamAttempt::where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->whereIn('application_id', $baseParticipants->pluck('id')->all())
            ->get()
            ->keyBy('application_id');

        $participants = $baseParticipants->map(function (Applications $app) use ($attemptsByApplication, $batchNo) {
            $attempt = $attemptsByApplication->get($app->id) ?? $this->getOrCreateAttempt($app, $batchNo);
            $app->status = $attempt->status;
            $app->scores = $attempt->scores;
            $app->answers = $attempt->answers;
            $app->result = $attempt->result;
            $app->exam_end_time = $attempt->exam_end_time;
            $app->exam_submitted_at = $attempt->exam_submitted_at;
            $app->tab_violations = $attempt->tab_violations;
            $app->exam_started_at = $attempt->exam_started_at;
            $app->exam_paused_at = $attempt->exam_paused_at;
            $app->exam_paused_by_admin_id = $attempt->exam_paused_by_admin_id;
            $app->exam_pause_seconds = $attempt->exam_pause_seconds;
            return $app;
        });

        $tamperCountsByUser = $this->examTamperCountsByUser((string) $vacancy_id, $participants->pluck('user_id')->all());

        $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();

        if ($isViewer) {
            $viewerExamStatus = $examDetails ? $this->getExamStatus($examDetails) : 'Unscheduled';
            if (!in_array($viewerExamStatus, ['Ongoing', 'Completed'])) {
                return redirect()
                    ->route('admin_exam_management')
                    ->with('error', 'Viewer can only monitor ongoing or completed exams.');
            }
        }

        $isExamExpired = false;
        if ($examDetails && $examDetails->date && $examDetails->time) {
             $startDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time);
             $endDateTime = $examDetails->time_end 
                ? \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time_end)
                : $startDateTime->copy()->addMinutes($examDetails->duration ?? 0);
             
             if (now()->gt($endDateTime)) {
                 $isExamExpired = true;
             }
        }

        $user_name = [];
        foreach ($participants as $p) {
            $user_id = $p->user_id;
            $user = User::find($user_id);
            $user_name[] = $user ? $user->name : 'Unknown User';
        }

        // Pre-calculate scores for view
        $examItems = ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->get(['id', 'is_essay']);
        $mcItemIds = $examItems->where('is_essay', 0)->pluck('id')->toArray();
        $essayItemIds = $examItems->where('is_essay', 1)->pluck('id')->toArray();

        foreach ($participants as $p) {
            $scores = $p->scores ?? [];
            $answers = $p->answers ?? [];
            $status = strtolower($p->status ?? 'pending');
            
            $mcString = '-';
            $essayString = '-';

            if ($status === 'in-progress') {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) {
                        $mcScore += (int)$scores[$id];
                    }
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';

                $answeredEssay = 0;
                foreach ($essayItemIds as $id) {
                    $val = $answers[$id] ?? null;
                    if (!is_null($val) && trim((string)$val) !== '') {
                        $answeredEssay++;
                    }
                }
                $essayString = count($essayItemIds) > 0 ? "$answeredEssay / " . count($essayItemIds) : '-';
            } elseif ($status === 'submitted' || $isExamExpired) {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) $mcScore += (int)$scores[$id];
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';
    
                $essayScore = 0;
                foreach ($essayItemIds as $id) {
                    if (isset($scores[$id])) $essayScore += (int)$scores[$id];
                }
                $essayString = count($essayItemIds) > 0 ? "$essayScore" : '-';
            }

            $p->mc_score_str = $mcString;
            $p->essay_score_str = $essayString;
            $p->tab_switch_count = (int) ($p->tab_violations ?? 0);
            $p->tamper_logs_count = (int) ($tamperCountsByUser[(int) $p->user_id] ?? 0);
            $p->is_paused = !empty($p->exam_paused_at);
            $p->pause_state = $this->resolvePauseState($p, $examDetails);
            $p->remaining_seconds = $this->resolveExamRemainingSeconds($p, $examDetails);
            $p->resume_action = $this->resolveResumeExamState($p, $examDetails);
        }

        // Get qualified applicants and attendance responses for admin tabs
        $qualifiedApplicants = collect();
        $attendanceApplicants = collect();
        if (!$isViewer) {
            $qualifiedApplications = $this->qualifiedApplicationsQuery($vacancy_id)
                ->with([
                    'user',
                    'user.notifications' => function ($query) use ($vacancy_id) {
                        $query->where('data->type', 'exam_attendance_prompt')
                            ->where('data->vacancy_id', $vacancy_id)
                            ->latest();
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            $qualifiedApplicants = $qualifiedApplications
                ->map(fn(Applications $app) => $this->mapQualifiedApplicant($app))
                ->values();

            $attendanceApplicants = $qualifiedApplications
                ->filter(fn(Applications $app) => !empty($app->exam_attendance_status))
                ->map(fn(Applications $app) => $this->mapAttendanceApplicant($app))
                ->values();
        }

        $causer = auth('admin')->user() ?? auth()->user();

        activity()
            ->causedBy($causer)
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
            ->log('Managed exam participants and details.');

        $scheduleNotifiedByName = null;
        $scheduleNotifiedAt = null;
        $lastSchedule = Activity::where('event', 'notify_schedule')
            ->where('properties->vacancy_id', $vacancy_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastSchedule) {
            $scheduleNotifiedAt = $lastSchedule->created_at;
            if ($lastSchedule->causer) {
                $scheduleNotifiedByName = $lastSchedule->causer->name ?? $lastSchedule->causer->email ?? null;
            }
        }

        $linkSentByName = null;
        $linkSentAt = null;
        $lastLinkSend = Activity::where('event', 'notify')
            ->where('properties->vacancy_id', $vacancy_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastLinkSend) {
            $linkSentAt = $lastLinkSend->created_at;
            if ($lastLinkSend->causer) {
                $linkSentByName = $lastLinkSend->causer->name ?? $lastLinkSend->causer->email ?? null;
            }
        }

        if ($isViewer) {
            return view('viewer.manage_exam_monitor', [
                'vacancy' => $vacancy,
                'participants' => $participants,
                'user_name' => $user_name,
                'examDetails' => $examDetails,
                'examPauseState' => $this->resolvePauseState(null, $examDetails),
                'selectedBatch' => $batchNo,
            ]);
        }

        return view('admin.manage_exam', [
            'vacancy' => $vacancy,
            'participants' => $participants,
            'user_name' => $user_name,
            'examDetails' => $examDetails,
            'qualifiedApplicants' => $qualifiedApplicants,
            'attendanceApplicants' => $attendanceApplicants,
            'scheduleNotifiedByName' => $scheduleNotifiedByName,
            'scheduleNotifiedAt' => $scheduleNotifiedAt,
            'linkSentByName' => $linkSentByName,
            'linkSentAt' => $linkSentAt,
            'selectedBatch' => $batchNo,
        ]);
    }

    public function getQualifiedApplicants(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot access qualified applicants list.')) {
            return $denied;
        }

        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $query = $this->qualifiedApplicationsQuery($vacancy_id)
            ->with([
                'user',
                'personalInformation',
                'user.notifications' => function ($query) use ($vacancy_id) {
                    $query->where('data->type', 'exam_attendance_prompt')
                        ->where('data->vacancy_id', $vacancy_id)
                        ->latest();
                },
            ]);

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $applicants = $query->orderBy('created_at', 'desc')->get();

        // Transform data for the view
        $qualifiedApplicants = $applicants
            ->map(fn(Applications $app) => $this->mapQualifiedApplicant($app))
            ->values();

        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'applicants' => $qualifiedApplicants
            ]);
        }

        return $qualifiedApplicants;
    }

    public function getAttendanceApplicants(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot access attendance applicants list.')) {
            return $denied;
        }

        $qualifiedApplications = $this->qualifiedApplicationsQuery($vacancy_id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $attendanceApplicants = $qualifiedApplications
            ->filter(fn(Applications $app) => !empty($app->exam_attendance_status))
            ->map(fn(Applications $app) => $this->mapAttendanceApplicant($app))
            ->values();

        return response()->json([
            'success' => true,
            'applicants' => $attendanceApplicants
        ]);
    }

    public function attendancePrompt(Request $request, $vacancy_id)
    {
        if (!auth()->check()) {
            return redirect()->route('login.form', [
                'redirect' => 'exam_attendance',
                'vacancy' => $vacancy_id,
            ]);
        }

        $user = auth()->user();
        $application = $this->qualifiedApplicationsQuery($vacancy_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        $hasExistingAttendanceResponse = !empty($application->exam_attendance_status) || !is_null($application->exam_attendance_responded_at);

        $vacancy = JobVacancy::select('vacancy_id', 'position_title', 'vacancy_type')
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();
        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        activity()
            ->causedBy($user)
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Attendance'])
            ->log('Viewed exam attendance prompt.');

        return view('exam.attendance_prompt', [
            'vacancy' => $vacancy,
            'examDetail' => $examDetail,
            'application' => $application,
            'attendanceStatusLabel' => $this->attendanceStatusLabel($application->exam_attendance_status),
            'hasExistingAttendanceResponse' => $hasExistingAttendanceResponse,
        ]);
    }

    public function submitAttendanceResponse(Request $request, $vacancy_id)
    {
        $validated = $request->validate([
            'attendance_status' => ['required', Rule::in([self::ATTENDANCE_WILL_ATTEND, self::ATTENDANCE_WILL_NOT_ATTEND])],
            'attendance_remark' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn() => $request->input('attendance_status') === self::ATTENDANCE_WILL_NOT_ATTEND)],
        ]);

        $application = $this->qualifiedApplicationsQuery($vacancy_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $hadExistingAttendanceResponse = !empty($application->exam_attendance_status) || !is_null($application->exam_attendance_responded_at);

        $attendanceStatus = $validated['attendance_status'];
        $remark = $attendanceStatus === self::ATTENDANCE_WILL_NOT_ATTEND
            ? trim((string) ($validated['attendance_remark'] ?? ''))
            : null;

        $application->update([
            'exam_attendance_status' => $attendanceStatus,
            'exam_attendance_remark' => $remark !== '' ? $remark : null,
            'exam_attendance_responded_at' => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->event('save')
            ->withProperties([
                'vacancy_id' => $vacancy_id,
                'attendance_status' => $attendanceStatus,
                'attendance_override' => $hadExistingAttendanceResponse,
                'section' => 'Exam Attendance',
            ])
            ->log($hadExistingAttendanceResponse
                ? 'Updated exam attendance response.'
                : 'Submitted exam attendance response.');

        // Broadcast attendance update to monitor
        broadcast(new ExamProgressUpdated(
            vacancyId: (string) $vacancy_id,
            userId: (int) auth()->id(),
            type: 'attendance_updated',
            status: $attendanceStatus,
            occurredAt: now()->toIso8601String(),
        ));

        if ($hadExistingAttendanceResponse) {
            $message = $attendanceStatus === self::ATTENDANCE_WILL_ATTEND
                ? 'Your exam attendance response has been updated to Will Attend.'
                : 'Your exam attendance response has been updated to Will Not Attend.';
        } else {
            $message = $attendanceStatus === self::ATTENDANCE_WILL_ATTEND
                ? 'Your exam attendance has been marked as Will Attend.'
                : 'Your exam attendance has been marked as Will Not Attend.';
        }

        return redirect()
            ->route('application_status', ['user' => auth()->id(), 'vacancy' => $vacancy_id])
            ->with('success', $message);
    }

    public function updateAttendanceStatus(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot override attendance status.')) {
            return $denied;
        }

        $validated = $request->validate([
            'attendance_status' => ['required', Rule::in([self::ATTENDANCE_WILL_ATTEND, self::ATTENDANCE_WILL_NOT_ATTEND])],
            'attendance_remark' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn() => $request->input('attendance_status') === self::ATTENDANCE_WILL_NOT_ATTEND)],
        ]);

        $application = $this->qualifiedApplicationsQuery($vacancy_id)
            ->where('user_id', $user_id)
            ->with('user')
            ->firstOrFail();

        $attendanceStatus = $validated['attendance_status'];
        $remark = trim((string) ($validated['attendance_remark'] ?? ''));

        $application->update([
            'exam_attendance_status' => $attendanceStatus,
            'exam_attendance_remark' => $attendanceStatus === self::ATTENDANCE_WILL_NOT_ATTEND && $remark !== '' ? $remark : null,
            'exam_attendance_responded_at' => now(),
        ]);

        activity()
            ->causedBy(auth('admin')->user())
            ->event('update')
            ->withProperties([
                'vacancy_id' => $vacancy_id,
                'user_id' => $user_id,
                'attendance_status' => $attendanceStatus,
                'section' => 'Exam Management',
            ])
            ->log('Overrode applicant exam attendance status.');

        // Broadcast attendance update to monitor
        broadcast(new ExamProgressUpdated(
            vacancyId: (string) $vacancy_id,
            userId: (int) $user_id,
            type: 'attendance_updated',
            status: $attendanceStatus,
            occurredAt: now()->toIso8601String(),
        ));

        return response()->json([
            'success' => true,
            'message' => 'Attendance status updated.',
            'attendance' => $this->mapAttendanceApplicant($application->fresh(['user'])),
            'will_attend_count' => $this->qualifiedApplicationsQuery($vacancy_id)
                ->where('exam_attendance_status', self::ATTENDANCE_WILL_ATTEND)
                ->count(),
        ]);
    }

    public function getLobbyData(Request $request, $vacancy_id)
    {
        $batchNo = $this->resolveBatchNo($request);
        if ($this->isViewerRole()) {
            $viewerExamDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();
            $viewerExamStatus = $viewerExamDetail ? $this->getExamStatus($viewerExamDetail) : 'Unscheduled';
            if ($viewerExamStatus !== 'Ongoing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Viewer can only monitor ongoing exams.',
                ], 403);
            }
        }

        // Get all applications that are considered participants for this exam
        // Filter by those who have "read" the notification or entered the lobby (read_at is not null)
        $baseParticipants = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('read_at')
            ->with('user')
            ->get();

        $attemptsByApplication = ApplicationExamAttempt::where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->whereIn('application_id', $baseParticipants->pluck('id')->all())
            ->get()
            ->keyBy('application_id');

        $participants = $baseParticipants->map(function (Applications $app) use ($attemptsByApplication, $batchNo) {
            $attempt = $attemptsByApplication->get($app->id) ?? $this->getOrCreateAttempt($app, $batchNo);
            $app->status = $attempt->status;
            $app->scores = $attempt->scores;
            $app->answers = $attempt->answers;
            $app->result = $attempt->result;
            $app->tab_violations = $attempt->tab_violations;
            $app->exam_paused_at = $attempt->exam_paused_at;
            $app->exam_pause_seconds = $attempt->exam_pause_seconds;
            $app->exam_started_at = $attempt->exam_started_at;
            $app->exam_submitted_at = $attempt->exam_submitted_at;
            $app->exam_end_time = $attempt->exam_end_time;
            return $app;
        });

        $tamperCountsByUser = $this->examTamperCountsByUser((string) $vacancy_id, $participants->pluck('user_id')->all());

        // Get Exam Items to distinguish MC vs Essay
        $examItems = ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->get(['id', 'is_essay']);
        $mcItemIds = $examItems->where('is_essay', 0)->pluck('id')->toArray();
        $essayItemIds = $examItems->where('is_essay', 1)->pluck('id')->toArray();

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();
        $isExamExpired = false;
        if ($examDetail && $examDetail->date && $examDetail->time) {
             $startDateTime = \Carbon\Carbon::parse($examDetail->date . ' ' . $examDetail->time);
             $endDateTime = $examDetail->time_end 
                ? \Carbon\Carbon::parse($examDetail->date . ' ' . $examDetail->time_end)
                : $startDateTime->copy()->addMinutes($examDetail->duration ?? 0);
             
             if (now()->gt($endDateTime)) {
                 $isExamExpired = true;
             }
        }

        $lobbyData = $participants->map(function ($p) use ($mcItemIds, $essayItemIds, $isExamExpired, $examDetail, $tamperCountsByUser) {
            $statusColors = [
                'ready' => '#4ade80',        // green-400
                'in-progress' => '#facc15',  // yellow-400
                'submitted' => '#3b82f6',    // blue-500
                'pending' => '#f75555',      // red
            ];

            $status = strtolower($p->status ?? 'pending');
            $color = $statusColors[$status] ?? '#9ca3af';

            $scores = $p->scores ?? [];
            $answers = $p->answers ?? [];
             
            $mcString = '-';
            $essayString = '-';

            if ($status === 'in-progress') {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) {
                        $mcScore += (int)$scores[$id];
                    }
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';

                $answeredEssay = 0;
                foreach ($essayItemIds as $id) {
                    $val = $answers[$id] ?? null;
                    if (!is_null($val) && trim((string)$val) !== '') {
                        $answeredEssay++;
                    }
                }
                $essayString = count($essayItemIds) > 0 ? "$answeredEssay / " . count($essayItemIds) : '-';
            } elseif ($status === 'submitted' || $isExamExpired) {
                $mcScore = 0;
                foreach ($mcItemIds as $id) {
                    if (isset($scores[$id])) $mcScore += (int)$scores[$id];
                }
                $mcString = count($mcItemIds) > 0 ? "$mcScore / " . count($mcItemIds) : '-';
    
                $essayScore = 0;
                foreach ($essayItemIds as $id) {
                    if (isset($scores[$id])) $essayScore += (int)$scores[$id];
                }
                $essayString = count($essayItemIds) > 0 ? "$essayScore" : '-';
            }

            return [
                'user_id' => $p->user_id,
                'name' => $p->user->name ?? 'Unknown User',
                'result' => $p->result ?: '-',
                'mc_score' => $mcString,
                'essay_score' => $essayString,
                'status' => $p->status ?? 'Pending',
                'status_color' => $color,
                'vacancy_id' => $p->vacancy_id,
                'tab_switch_count' => (int) ($p->tab_violations ?? 0),
                'tamper_logs_count' => (int) ($tamperCountsByUser[(int) $p->user_id] ?? 0),
                'is_paused' => !empty($p->exam_paused_at),
                'pause_state' => $this->resolvePauseState($p, $examDetail),
                'resume_action' => $this->resolveResumeExamState($p, $examDetail),
            ];
        });

        return response()->json([
            'success' => true,
            'exam' => [
                'is_started' => (bool) ($examDetail?->is_started ?? false),
                'is_paused' => $this->isExamGloballyPaused($examDetail),
                'pause_state' => $this->resolvePauseState(null, $examDetail),
            ],
            'participants' => $lobbyData
        ]);
    }

    public function toggleApplicantPause(Request $request, string $vacancy_id, int $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot pause examinees.')) {
            return $denied;
        }

        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();
        if ($this->isExamGloballyPaused($examDetail) && !$this->isApplicationPaused($application)) {
            return response()->json([
                'success' => false,
                'message' => 'Resume the entire examination before pausing individual examinees.',
            ], 409);
        }

        $isPaused = $this->isApplicationPaused($application);

        $this->setApplicationPauseState($application, !$isPaused);
        $application->refresh();

        activity()
            ->causedBy(auth('admin')->user())
            ->event($isPaused ? 'resume' : 'pause')
            ->withProperties([
                'vacancy_id' => $vacancy_id,
                'user_id' => $user_id,
                'scope' => 'individual',
                'section' => 'Exam Management',
            ])
            ->log($isPaused
                ? 'Resumed an individual examinee from pause.'
                : 'Paused an individual examinee during the exam.');

        return response()->json([
            'success' => true,
            'paused' => !$isPaused,
            'message' => !$isPaused
                ? 'Examinee paused successfully.'
                : 'Examinee resumed successfully.',
            'remaining_seconds' => $this->resolveExamRemainingSeconds($application, $examDetail),
        ]);
    }

    public function toggleExamPause(Request $request, string $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot pause the entire exam.')) {
            return $denied;
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->firstOrFail();

        if (!$examDetail->is_started) {
            return response()->json([
                'success' => false,
                'message' => 'Start the exam before pausing it.',
            ], 400);
        }

        $isPaused = $this->isExamGloballyPaused($examDetail);
        $participantPauseExists = Applications::where('vacancy_id', $vacancy_id)
            ->whereNotNull('exam_paused_at')
            ->exists();

        if (!$isPaused && $participantPauseExists) {
            return response()->json([
                'success' => false,
                'message' => 'Resume all individually paused examinees before pausing the entire exam.',
            ], 409);
        }

        $this->setGlobalExamPauseState($examDetail, !$isPaused);

        activity()
            ->causedBy(auth('admin')->user())
            ->event($isPaused ? 'resume' : 'pause')
            ->withProperties([
                'vacancy_id' => $vacancy_id,
                'scope' => 'entire_exam',
                'section' => 'Exam Management',
            ])
            ->log($isPaused
                ? 'Resumed the entire examination.'
                : 'Paused the entire examination.');

        return response()->json([
            'success' => true,
            'paused' => !$isPaused,
            'message' => !$isPaused
                ? 'The entire exam has been paused.'
                : 'The entire exam has been resumed.',
        ]);
    }

    public function examLobby(Request $request, $vacancy_id)
    {
        if (!auth()->check()) {
            $token = $request->query('token');
            return redirect()->route('login.form', [
                'redirect' => 'exam_lobby',
                'vacancy' => $vacancy_id,
                'token' => $token,
            ]);
        }

        // Mark the applicant as having entered the lobby
        $user_id = auth()->id();
        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();
        $attempt = $this->getOrCreateAttempt($application, $batchNo);

        if (!$this->canReceiveExamLink($application)) {
            return redirect()
                ->route('exam.attendance.prompt', ['vacancy_id' => $vacancy_id])
                ->with('error', 'Only applicants marked as Will Attend can access the exam link.');
        }

        $sessionKey = 'exam_access_' . $vacancy_id;
        $token = $request->query('token');
        $deviceId = Cookie::get('device_id');

        if (is_null($application->exam_token_used_at)) {
            if (empty($token) || $token !== $application->exam_token) {
                abort(403, 'Invalid or missing exam link.');
            }
            if (!$application->exam_token_expires_at || now()->greaterThan($application->exam_token_expires_at)) {
                abort(403, 'This exam link has expired.');
            }
            if (!$deviceId) {
                $deviceId = Str::random(40);
                Cookie::queue(cookie('device_id', $deviceId, 60 * 24 * 365));
            }
            session([$sessionKey => true]);
            $application->update([
                'exam_token_used_at' => now(),
                'exam_token_device_id' => $deviceId,
                'exam_token_used_ip' => $request->ip(),
                'exam_token_used_ua' => (string)$request->userAgent(),
            ]);
        } else {
            $hasSession = session()->get($sessionKey, false);
            $matchesDevice = $deviceId && $application->exam_token_device_id && hash_equals($application->exam_token_device_id, $deviceId);
            if (!$hasSession && !$matchesDevice) {
                abort(403, 'This exam link is already used on a different device.');
            }
        }

        // If already submitted, redirect to thank you
        if ($attempt->status === 'submitted') {
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo]);
        }

        // If already started, redirect to questions
        if ($attempt->exam_started_at) {
            return redirect()->route('user.exam_question_page', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo]);
        }

        $enteredLobby = false;
        if ($application && is_null($application->read_at)) {
            $application->update(['read_at' => now()]);
            $application->refresh();
            $enteredLobby = true;
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();
        $vacancy = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->first();
        $examPauseState = $this->resolvePauseState($attempt, $examDetail);
        $remainingSeconds = $this->resolveExamRemainingSeconds($attempt, $examDetail);

        // If admin has already started the exam, route user straight to questions
        if ($examDetail && $examDetail->is_started) {
            return redirect()->route('user.exam_question_page', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo]);
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam'])
            ->log('Entered exam lobby.');

        if ($enteredLobby) {
            $this->broadcastExamProgress($application, 'entered-lobby');
        }

        $examineeName = auth()->user()->name ?? 'Examinee';
        $examineeNumber = strtoupper('EXM-' . substr(hash('sha256', $vacancy_id . '-' . $user_id), 0, 8));

        return view('exam_user.exam_lobby', compact('vacancy_id', 'examDetail', 'vacancy', 'examineeName', 'examineeNumber', 'examPauseState', 'remainingSeconds', 'batchNo'));
    }

    public function examQuestion(Request $request, $vacancy_id)
    {
        $user_id = auth()->id();
        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();
        $attempt = $this->getOrCreateAttempt($application, $batchNo);

        if (!$this->canReceiveExamLink($application)) {
            return redirect()
                ->route('exam.attendance.prompt', ['vacancy_id' => $vacancy_id])
                ->with('error', 'Only applicants marked as Will Attend can proceed to the exam.');
        }

        // Require token-validated lobby access before question access.
        $sessionKey = 'exam_access_' . $vacancy_id;
        $hasSession = (bool) session()->get($sessionKey, false);
        $deviceId = Cookie::get('device_id');
        $matchesDevice = $deviceId
            && $application->exam_token_device_id
            && hash_equals((string) $application->exam_token_device_id, (string) $deviceId);

        if (is_null($application->exam_token_used_at)) {
            return redirect()
                ->route('user.exam_lobby', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo])
                ->with('error', 'Please open your exam link from the notification first.');
        }

        if (!$hasSession && !$matchesDevice) {
            abort(403, 'This exam session is already active on a different device.');
        }

        if (!$hasSession && $matchesDevice) {
            session([$sessionKey => true]);
        }

        // Check if already submitted
        if ($attempt->status === 'submitted') {
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo]);
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->firstOrFail();

        // If admin hasn't started the exam yet, redirect back to lobby
        if (!$examDetail->is_started) {
            return redirect()->route('user.exam_lobby', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo]);
        }

        $examPauseState = $this->resolvePauseState($attempt, $examDetail);

        // Initialize exam start time for the user if not set yet
        if (!$attempt->exam_started_at) {
            $now = now();
            $duration = $examDetail->duration; // in minutes

            $attempt->update([
                'exam_started_at' => $now,
                'exam_end_time' => $now->copy()->addMinutes($duration),
                'status' => 'in-progress'
            ]);

            // Refresh attempt to get the new values
            $attempt->refresh();
            $this->broadcastExamProgress($application, 'started-exam');
        }

        $now = now();
        $pauseState = $this->resolvePauseState($attempt, $examDetail);
        $endTime = \Carbon\Carbon::parse($attempt->exam_end_time)
            ->addSeconds((int) $pauseState['global_pause_seconds'] + (int) $pauseState['application_pause_seconds']);
        $remaining_seconds = $now->diffInSeconds($endTime, false);

        // If time is up (allow 1 minute grace period for latency)
        if ($remaining_seconds < -60) {
            $payload = ['status' => 'submitted'];
            if (!$attempt->exam_submitted_at) {
                $payload['exam_submitted_at'] = now();
            }
            $attempt->update($payload);
            $this->autoCloseExamIfAllParticipantsDone((string) $vacancy_id, $batchNo);
            return redirect()->route('user.exam_thankyou', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo]);
        }

        if ($remaining_seconds < 0)
            $remaining_seconds = 0;

        $columns = Schema::getColumnListing('exam_items');
        $columns = array_diff($columns, ['ans']);

        $examItems = ExamItems::select($columns)
            ->where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->get();

        $vacancy = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->first();

        activity()
            ->causedBy(auth()->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam'])
            ->log('Viewed exam questions page.');

        $total_seconds = $examDetail->duration * 60;
        $savedAnswers = is_array($attempt->answers) ? $attempt->answers : [];
        $maxViolations = max(1, (int) ($examDetail->max_violations ?? 12));
        $examEndTimeTs = $endTime->timestamp;
        $serverNowTs = $now->timestamp;

        $examineeName = auth()->user()->name ?? 'Examinee';
        $examineeNumber = strtoupper('EXM-' . substr(hash('sha256', $vacancy_id . '-' . $user_id), 0, 8));

        return view('exam_user.exam_question_page', compact('vacancy_id', 'examItems', 'remaining_seconds', 'vacancy', 'total_seconds', 'savedAnswers', 'examineeName', 'examineeNumber', 'maxViolations', 'examEndTimeTs', 'serverNowTs', 'examPauseState', 'batchNo'));
    }

    public function viewExam(Request $request, $vacancy_id, $user_id)
    {
        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::select('id', 'user_id')
            ->where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();
        if (!$application) {
            if (auth('admin')->check() && strcasecmp((string) auth('admin')->user()->role, 'viewer') === 0) {
                return redirect()->route('viewer')->with('error', 'Exam answers are unavailable for the selected applicant.');
            }
            abort(404);
        }
        $attempt = $this->getOrCreateAttempt($application, $batchNo);
        $examItems = ExamItems::select('id', 'question', 'ans', 'is_essay', 'choices', 'essay_max_score')
            ->where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->get();
        $examDetail = ExamDetail::select('vacancy_id', 'max_violations', 'batch_no')
            ->where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->first();
        $positionTitle = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->firstOrFail();
        $userName = User::select('name')->find($user_id);

        $answers = $attempt->answers;
        $scores = $attempt->scores;
        // $answers = json_decode($application->answers, true);
        // $scores = $application->scores;

        //info($answers);

        $examResults = [];

        $examineeCode = strtoupper('EXM-' . substr(hash('sha256', $vacancy_id . '-' . $user_id), 0, 8));

        foreach ($examItems as $item) {
            $givenAnswer = $answers[$item->id] ?? null;
            $score = $scores[$item->id] ?? null;

            $choices = is_array($item->choices) ? $item->choices : [];
            // Normalize to strings
            $givenKey = is_null($givenAnswer) ? null : (string)$givenAnswer;
            $correctKey = is_null($item->ans) ? null : (string)$item->ans;
            $givenText = $givenKey !== null && isset($choices[$givenKey]) ? (string)$choices[$givenKey] : null;
            $correctText = $correctKey !== null && isset($choices[$correctKey]) ? (string)$choices[$correctKey] : null;

            // Determine correctness:
            // Prefer stored score if available; otherwise compute tolerant comparison
            if ($item->is_essay == 0) {
                if (!is_null($score)) {
                    $is_correct = ((int)$score) === 1;
                } else {
                    $isKeyMatch = (!is_null($givenKey) && !is_null($correctKey) && strcasecmp(trim($givenKey), trim($correctKey)) === 0);
                    $is_correct = $isKeyMatch;
                }
            } else {
                $is_correct = null;
            }

            $examResults[] = [
                'id' => $item->id,
                'question' => $item->question,
                'given_answer' => $givenAnswer,
                'given_answer_text' => $givenText,
                'correct_answer' => $item->ans,
                'correct_answer_text' => $correctText,
                'score' => $score,
                'is_correct' => $is_correct,
                'is_essay' => $item->is_essay,
                'essay_max_score' => (int) ($item->essay_max_score ?? 0),
            ];
        }

        //info($examResults);

        activity()
            ->causedBy(auth('admin')->user())
            ->event('view')
            ->withProperties(['vacancy_id' => $vacancy_id, 'user_id' => $user_id, 'section' => 'Exam Management'])
            ->log('Viewed applicant exam answers.');

        return view('admin.exam_view_answers', [
            'examResults' => $examResults,
            'positionTitle' => $positionTitle,
            'vacancy_id' => $vacancy_id,
            'user_id' => $user_id,
            'batch_no' => $batchNo,
            'userName' => $userName,
            'examineeCode' => $examineeCode,
            'application' => $attempt,
            'resumeAction' => $this->resolveResumeExamState($attempt, $examDetail),
        ]);
    }

    public function resumeApplicantExam(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot resume applicant exams.')) {
            return $denied;
        }

        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();
        $attempt = $this->getOrCreateAttempt($application, $batchNo);

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();
        $resumeAction = $this->resolveResumeExamState($attempt, $examDetail);

        if (!($resumeAction['can_resume'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $resumeAction['reason'] ?? 'This exam attempt cannot be resumed.',
            ], 422);
        }

        $remainingSeconds = (int) ($resumeAction['remaining_seconds'] ?? 0);
        $isTabThresholdSubmission = !empty($resumeAction['is_tab_threshold_submission']);

        DB::transaction(function () use ($application, $attempt, $remainingSeconds, $isTabThresholdSubmission) {
            if ($isTabThresholdSubmission) {
                ExamTabViolation::where('vacancy_id', $application->vacancy_id)
                    ->where('user_id', $application->user_id)
                    ->delete();
            }

            $updatePayload = [
                'status' => ApplicationStatus::IN_PROGRESS->value,
                'exam_submitted_at' => null,
                'exam_end_time' => now()->addSeconds($remainingSeconds),
            ];

            if ($isTabThresholdSubmission) {
                $updatePayload['tab_violations'] = 0;
                $updatePayload['last_tab_violation_at'] = null;
            }

            $attempt->update($updatePayload);
        });

        $attempt->refresh();
        $this->broadcastExamProgress($application, 'resumed', [
            'remaining_seconds' => $remainingSeconds,
            'batch_no' => $batchNo,
        ]);

        activity()
            ->causedBy(auth('admin')->user())
            ->event('resume')
            ->withProperties([
                'vacancy_id' => $vacancy_id,
                'user_id' => $user_id,
                'batch_no' => $batchNo,
                'remaining_seconds' => $remainingSeconds,
                'tab_violations' => (int) ($attempt->tab_violations ?? 0),
                'reset_tab_violations' => $isTabThresholdSubmission,
                'section' => 'Exam Management',
            ])
            ->log($isTabThresholdSubmission
                ? 'Resumed applicant exam attempt and reset tab-switch logs after tab-threshold auto-submit.'
                : 'Reopened submitted applicant exam attempt from saved remaining time.');

        return response()->json([
            'success' => true,
            'message' => 'Exam resumed. The applicant can continue from the saved progress.',
            'status' => $attempt->status,
            'remaining_seconds' => $remainingSeconds,
            'remaining_label' => $this->formatResumeDuration($remainingSeconds),
            'exam_end_time' => $attempt->exam_end_time
                ? \Carbon\Carbon::parse((string) $attempt->exam_end_time)->toDateTimeString()
                : null,
        ]);
    }

    public function getExamAnswersJson(Request $request, $vacancy_id, $user_id)
    {
        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::select('id', 'user_id')
            ->where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->first();
        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Exam answers are unavailable for the selected applicant.',
            ], 404);
        }
        $attempt = $this->getOrCreateAttempt($application, $batchNo);
        $examItems = ExamItems::select('id', 'question', 'ans', 'is_essay', 'choices', 'essay_max_score')
            ->where('vacancy_id', $vacancy_id)
            ->where('batch_no', $batchNo)
            ->get();

        $answers = $attempt->answers;
        $scores = $attempt->scores;

        $examResults = [];

        foreach ($examItems as $item) {
            $givenAnswer = $answers[$item->id] ?? null;
            $score = $scores[$item->id] ?? null;

            $choices = is_array($item->choices) ? $item->choices : [];
            // Normalize to strings
            $givenKey = is_null($givenAnswer) ? null : (string)$givenAnswer;
            $correctKey = is_null($item->ans) ? null : (string)$item->ans;
            $givenText = $givenKey !== null && isset($choices[$givenKey]) ? (string)$choices[$givenKey] : null;
            $correctText = $correctKey !== null && isset($choices[$correctKey]) ? (string)$choices[$correctKey] : null;

            // Determine correctness:
            // Prefer stored score if available; otherwise compute tolerant comparison
            if ($item->is_essay == 0) {
                if (!is_null($score)) {
                    $is_correct = ((int)$score) === 1;
                } else {
                    $isKeyMatch = (!is_null($givenKey) && !is_null($correctKey) && strcasecmp(trim($givenKey), trim($correctKey)) === 0);
                    $is_correct = $isKeyMatch;
                }
            } else {
                $is_correct = null;
            }

            $examResults[] = [
                'id' => $item->id,
                'question' => $item->question,
                'given_answer' => $givenAnswer,
                'given_answer_text' => $givenText,
                'correct_answer' => $item->ans,
                'correct_answer_text' => $correctText,
                'score' => $score,
                'is_correct' => $is_correct,
                'is_essay' => $item->is_essay,
                'essay_max_score' => (int) ($item->essay_max_score ?? 0),
            ];
        }

        $hasDurationMilliseconds = Schema::hasColumn('exam_tab_violations', 'duration_milliseconds');
        $logColumns = ['started_at', 'ended_at', 'duration_seconds', 'created_at'];
        if ($hasDurationMilliseconds) {
            $logColumns[] = 'duration_milliseconds';
        }

        $logsRaw = \App\Models\ExamTabViolation::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get($logColumns);
        $logs = $logsRaw->map(function ($l) use ($hasDurationMilliseconds) {
            return [
                'started_at_iso' => optional($l->started_at)->toIso8601String(),
                'ended_at_iso' => optional($l->ended_at)->toIso8601String(),
                'duration_seconds' => $l->duration_seconds,
                'duration_milliseconds' => $hasDurationMilliseconds ? $l->duration_milliseconds : null,
                'created_at_iso' => optional($l->created_at)->toIso8601String(),
            ];
        });

        $tamperLogs = Activity::query()
            ->where('event', 'exam_tamper')
            ->where('causer_type', User::class)
            ->where('causer_id', $user_id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['properties', 'created_at'])
            ->map(function ($activity) use ($vacancy_id) {
                $props = $activity->properties;
                if ($props instanceof \Illuminate\Support\Collection) {
                    $props = $props->toArray();
                }
                if (!is_array($props)) {
                    $props = (array) $props;
                }

                if ((string) ($props['vacancy_id'] ?? '') !== (string) $vacancy_id) {
                    return null;
                }

                return [
                    'type' => (string) ($props['type'] ?? 'clock-tamper'),
                    'timezone' => $props['timezone'] ?? null,
                    'timezone_offset_minutes' => $props['timezone_offset_minutes'] ?? null,
                    'clock_drift_ms' => $props['clock_drift_ms'] ?? null,
                    'client_now_iso' => $props['client_now_iso'] ?? null,
                    'server_now_iso' => $props['server_now_iso'] ?? null,
                    'created_at_iso' => optional($activity->created_at)->toIso8601String(),
                ];
            })
            ->filter()
            ->values()
            ->take(20);

        return response()->json([
            'success' => true,
            'examResults' => $examResults,
            'tab_violations' => (int) ($attempt->tab_violations ?? 0),
            'last_violation_at' => $attempt->last_tab_violation_at,
            'tab_violation_logs' => $logs,
            'exam_tamper_logs' => $tamperLogs,
        ]);
    }

    public function downloadExamPdf(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot export applicant exam answers.')) {
            return $denied;
        }

        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::select('id', 'user_id')
            ->where('user_id', $user_id)->where('vacancy_id', $vacancy_id)->firstOrFail();
        $attempt = $this->getOrCreateAttempt($application, $batchNo);
        $examItems = ExamItems::select('id', 'question', 'is_essay', 'choices', 'essay_max_score', 'ans')
            ->where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->get();
        $positionTitle = JobVacancy::select('position_title')->where('vacancy_id', $vacancy_id)->firstOrFail();
        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();

        $examineeCode = strtoupper('EXM-' . substr(hash('sha256', $vacancy_id . '-' . $user_id), 0, 8));
        $answers = $attempt->answers ?? [];
        $scores = $attempt->scores ?? [];

        // Build PDF inline using FPDF (installed via setasign/fpdf)
        $pdf = new \FPDF();
        $pdf->SetMargins(20, 20, 20);
        $pdf->AddPage();
        $pdf->SetTitle('Examination Result');

        /*
        |--------------------------------------------------------------------------
        | APPLICANT INFORMATION TABLE
        |--------------------------------------------------------------------------
        */

        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 6, 'APPLICANT INFORMATION', 0, 1);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Times', '', 11);

        $dateStr = optional($examDetail)->date
            ? \Carbon\Carbon::parse($examDetail->date)->format('F d, Y')
            : '-';

        $pdf->Cell(55, 6, 'Examinee Code:', 0, 0);
        $pdf->Cell(0, 6, $examineeCode, 0, 1);

        $pdf->Cell(55, 6, 'Position Applied For:', 0, 0);
        // Encoding-safe text conversion for PDF core fonts
        $toPdf = function ($text) {
            $text = (string)$text;
            $replacements = [
                "’" => "'",
                "‘" => "'",
                "“" => '"',
                "”" => '"',
                "–" => "-",
                "—" => "-",
                "…" => "...",
                "•" => "*",
                " " => " ", // non-breaking space
            ];
            $text = strtr($text, $replacements);
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
            if ($converted === false) {
                $converted = utf8_decode($text);
            }
            return $converted;
        };
        $pdf->Cell(0, 6, $toPdf($positionTitle->position_title ?? '-'), 0, 1);

        $pdf->Cell(55, 6, 'Date of Examination:', 0, 0);
        $pdf->Cell(0, 6, $toPdf($dateStr), 0, 1);

        $pdf->Cell(55, 6, 'Time Started:', 0, 0);
        $startedStr = $attempt->exam_started_at
            ? \Carbon\Carbon::parse($attempt->exam_started_at)->format('g:i A')
            : '-';
        $pdf->Cell(0, 6, $startedStr, 0, 1);

        $pdf->Cell(55, 6, 'Time Submitted:', 0, 0);
        // Use dedicated submission timestamp if available
        $submittedStr = $attempt->exam_submitted_at
            ? \Carbon\Carbon::parse($attempt->exam_submitted_at)->format('g:i A')
            : '-';
        $pdf->Cell(0, 6, $submittedStr, 0, 1);

        $pdf->Cell(55, 6, 'Extracted At:', 0, 0);
        $pdf->Cell(0, 6, now()->format('F d, Y g:i A'), 0, 1);

        $pdf->Ln(8);

        /*
        |--------------------------------------------------------------------------
        | RESPONSES SECTION
        |--------------------------------------------------------------------------
        */

        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 6, 'EXAMINATION RESPONSES', 0, 1);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(6);

        $idx = 1;

        foreach ($examItems as $item) {

            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }

            // Question header with right-aligned essay points (if applicable)
            $pdf->SetFont('Times', 'B', 11);
            $qStartY = $pdf->GetY();
            $questionText = $toPdf($idx . '. ' . ($item->question ?? ''));

            if ((int)$item->is_essay === 1) {
                $maxPts = (int)($item->essay_max_score ?? 0);
                if ($maxPts > 0) {
                    $scoreVal = $scores[$item->id] ?? null;
                    $ptsLabel = is_numeric($scoreVal) ? ($scoreVal . '/' . $maxPts . ' pts') : ('/' . $maxPts . ' pts');
                    $label = $toPdf($ptsLabel);
                    $labelW = $pdf->GetStringWidth($label);
                    $rightX = 210 - 20 - $labelW; // page width 210mm, right margin 20mm
                    $pdf->SetXY($rightX, $qStartY);
                    $pdf->SetFont('Times', 'I', 10);
                    $pdf->Cell($labelW, 6, $label, 0, 0, 'R');
                    $pdf->SetXY(20, $qStartY);
                    $pdf->SetFont('Times', 'B', 11);
                }
            }

            $pdf->MultiCell(0, 6, $questionText);

            if ((int)$item->is_essay === 1) {

                $ans = isset($answers[$item->id]) ? (string)$answers[$item->id] : '';

                $pdf->SetFont('Times', '', 11);

                $boxHeight = 36;
                $startY = $pdf->GetY();

                $pdf->Rect(20, $startY, 170, $boxHeight);
                $pdf->SetXY(22, $startY + 3);
                $pdf->MultiCell(166, 6, $toPdf($ans));

                $pdf->SetY($startY + $boxHeight + 8);

            } else {

            $given = $answers[$item->id] ?? null;
            $choiceMap = is_array($item->choices) ? $item->choices : [];

            $display = '-';

            if (!is_null($given)) {
                $keyRaw = (string)$given;
                // Map numeric keys to letters (0->A, 1->B, ...)
                if (is_numeric($keyRaw)) {
                    $idxKey = (int)$keyRaw;
                    $labelKey = chr(ord('A') + $idxKey);
                } else {
                    $labelKey = strtoupper($keyRaw);
                }

                // Handle choice maps that use numeric indexes
                $choiceLookupKey = isset($choiceMap[$keyRaw]) ? $keyRaw : ( (is_numeric($keyRaw) && isset($choiceMap[(int)$keyRaw])) ? (int)$keyRaw : $keyRaw );
                if (isset($choiceMap[$choiceLookupKey])) {
                    $choiceText = (string)$choiceMap[$choiceLookupKey];
                    $display = $labelKey . '. ' . $choiceText;   // eg. B. Lorem Ipsum
                } else {
                    $display = $labelKey; // fallback if mapping missing
                }
            }

            $pdf->SetFont('Times', '', 11);
            $pdf->Cell(45, 6, $toPdf("Applicant's Answer:"), 0, 0);

            $pdf->SetFont('Times', 'B', 11);
            $pdf->MultiCell(0, 6, $toPdf($display));
            }

            $pdf->Ln(8);
            $idx++;
        }

        $content = $pdf->Output('S');
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="exam-result.pdf"',
        ]);
    }

    public function saveResult(Request $request, $vacancy_id, $user_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot score applicant exams.')) {
            return $denied;
        }

        $batchNo = $this->resolveBatchNo($request);
        $scores = $request->input('scores');
        $result = $request->input('result');

        $validated = $request->validate([
            'scores' => 'nullable|array',
        ]);

        // Clamp essay scores within [0, essay_max_score]
        $items = ExamItems::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->get(['id', 'is_essay', 'essay_max_score']);
        $essayMaxMap = [];
        foreach ($items as $it) {
            if ((int)$it->is_essay === 1) {
                $essayMaxMap[$it->id] = is_null($it->essay_max_score) ? null : (int)$it->essay_max_score;
            }
        }

        if (is_array($scores)) {
            foreach ($scores as $qId => $val) {
                if (array_key_exists($qId, $essayMaxMap)) {
                    if ($val === '' || is_null($val)) {
                        // Not scored
                        continue;
                    }
                    $num = (int)$val;
                    $max = $essayMaxMap[$qId] ?? 0;
                    if (!is_null($max)) {
                        if ($num < 0) $num = 0;
                        if ($num > $max) $num = $max;
                    } else {
                        if ($num < 0) $num = 0;
                    }
                    $scores[$qId] = $num;
                } else {
                    // MCQ should be 0/1
                    if ($val === '' || is_null($val)) {
                        $scores[$qId] = null;
                    } else {
                        $scores[$qId] = (int)$val ? 1 : 0;
                    }
                }
            }
        }

        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', $user_id)
            ->firstOrFail();
        $attempt = $this->getOrCreateAttempt($application, $batchNo);
        $attempt->update([
            'scores' => $scores,
            'result' => $result,
        ]);

        activity()
            ->causedBy(auth('admin')->user())
            ->event('save')
            ->withProperties(['vacancy_id' => $vacancy_id, 'user_id' => $user_id, 'batch_no' => $batchNo, 'section' => 'Exam Management'])
            ->log('Saved exam results.');


        return redirect()->route('admin.manage_exam', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo, 'massage' => 'Result Saved!']);
    }

    public function notifyApplicants(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot send exam notifications.')) {
            return $denied;
        }

        try {
            $batchNo = $this->resolveBatchNo($request);
            $validated = $request->validate([
                'max_violations' => 'nullable|integer|min:1',
            ]);

            $publicBaseUrl = $this->resolvePublicBaseUrl($request);
            // Check if details have been saved first
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();

            if (!$examDetail || !$examDetail->details_saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save exam details first before sending links.'
                ], 400);
            }

            if (array_key_exists('max_violations', $validated) && !is_null($validated['max_violations'])) {
                $examDetail->max_violations = (int) $validated['max_violations'];
                $examDetail->save();
            }

            $participants = $this->qualifiedApplicationsQuery($vacancy_id)
                ->where('exam_attendance_status', self::ATTENDANCE_WILL_ATTEND)
                ->get();

            if ($participants->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No qualified applicants marked as Will Attend are eligible to receive the exam link yet.'
                ], 400);
            }

            $userIds = $participants->pluck('user_id')->toArray();

            $senderName = $this->resolveNotificationSenderName();
            $notificationResult = $this->sendRefinedNotifications($userIds, $vacancy_id, $examDetail, $publicBaseUrl, $senderName);
            $sentCount = (int) ($notificationResult['sent'] ?? 0);
            $failedCount = (int) ($notificationResult['failed'] ?? 0);
            $skippedCount = (int) ($notificationResult['skipped'] ?? 0);

            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No exam links were sent. Please check applicant emails and mail configuration.',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ], 500);
            }

            // Update exam details as notified
            $examDetail->update([
                'notified_at' => now(),
                'link_sent' => true,
                'link_sent_at' => now()
            ]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify')
                ->withProperties([
                    'vacancy_id' => $vacancy_id,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'section' => 'Exam Management',
                ])
                ->log('Sent exam notifications for applicants.');

            $message = $failedCount > 0 || $skippedCount > 0
                ? "{$sentCount} applicant(s) notified; {$failedCount} failed, {$skippedCount} skipped."
                : "{$sentCount} applicant(s) notified successfully.";

            return response()->json([
                'success' => true,
                'notified_at' => now()->format('Y-m-d H:i:s'),
                'link_sent_at' => now()->format('Y-m-d H:i:s'),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'partial' => ($failedCount > 0 || $skippedCount > 0),
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error("Error notifying applicants: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send exam schedule notification (no join link) to all applicants.
     * Used by "Save & Notify Applicants".
     */
    public function notifyApplicantsSchedule(Request $request, $vacancy_id)
    {
        try {
            $batchNo = $this->resolveBatchNo($request);
            $publicBaseUrl = $this->resolvePublicBaseUrl($request);
            $senderName = $this->resolveNotificationSenderName();
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();
            if (!$examDetail || !$examDetail->details_saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save exam details first before notifying applicants.'
                ], 400);
            }

            $participants = $this->qualifiedApplicationsQuery($vacancy_id)
                ->select('user_id')
                ->get();
            if ($participants->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No qualified applicants found for this vacancy.'
                ], 400);
            }

            $vacancy = JobVacancy::select('vacancy_id', 'position_title')
                ->where('vacancy_id', $vacancy_id)
                ->first();

            $usersById = User::query()
                ->whereIn('id', $participants->pluck('user_id')->unique()->values())
                ->get(['id', 'email'])
                ->keyBy('id');

            $sentCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            foreach ($participants as $app) {
                $user = $usersById->get($app->user_id);
                if (!$user || empty($user->email)) {
                    $skippedCount++;
                    continue;
                }

                try {
                    // Send immediately so Save & Notify still works even when no queue worker runs.
                    Mail::to($user->email)->send(new NotifyApplicantMail($vacancy_id, $user->id, $examDetail->id, $publicBaseUrl, $senderName));
                    $this->createAttendancePromptNotification((int) $user->id, (string) $vacancy_id, $vacancy);
                    $sentCount++;
                } catch (\Throwable $mailException) {
                    $failedCount++;
                    Log::error('Schedule email send failed', [
                        'vacancy_id' => $vacancy_id,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            }

            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedule emails were sent. Please check applicant emails and mail configuration.',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ], 500);
            }

            $examDetail->update([
                'notified_at' => now(),
            ]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify_schedule')
                ->withProperties([
                    'vacancy_id' => $vacancy_id,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'section' => 'Exam Management',
                ])
                ->log('Sent exam schedule notifications for applicants.');

            $message = $failedCount > 0 || $skippedCount > 0
                ? "Exam schedule emails sent to {$sentCount} applicant(s); {$failedCount} failed, {$skippedCount} skipped."
                : "Exam schedule emails sent to {$sentCount} applicant(s).";

            return response()->json([
                'success' => true,
                'notified_at' => now()->format('Y-m-d H:i:s'),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'partial' => ($failedCount > 0 || $skippedCount > 0),
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Error notifying applicants (schedule): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function notifySelectedApplicants(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot send exam notifications.')) {
            return $denied;
        }

        try {
            $publicBaseUrl = $this->resolvePublicBaseUrl($request);
            $senderName = $this->resolveNotificationSenderName();
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

            $requestedUserIds = collect($validated['user_ids'])
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();
            $batchNo = $this->resolveBatchNo($request);
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->firstOrFail();

            if (!$examDetail->details_saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please save exam details first before sending links.'
                ], 400);
            }

            $eligibleUserIds = $this->qualifiedApplicationsQuery($vacancy_id)
                ->whereIn('user_id', $requestedUserIds)
                ->where('exam_attendance_status', self::ATTENDANCE_WILL_ATTEND)
                ->pluck('user_id')
                ->all();

            if (count($eligibleUserIds) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected applicants must be marked as Will Attend before an exam link can be sent.'
                ], 400);
            }

            $notificationResult = $this->sendRefinedNotifications($eligibleUserIds, $vacancy_id, $examDetail, $publicBaseUrl, $senderName);
            $sentCount = (int) ($notificationResult['sent'] ?? 0);
            $failedCount = (int) ($notificationResult['failed'] ?? 0);
            $skippedCount = (int) ($notificationResult['skipped'] ?? 0) + ($requestedUserIds->count() - count($eligibleUserIds));

            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No exam links were sent to selected applicants.',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                ], 500);
            }

            activity()
                ->causedBy(auth('admin')->user())
                ->event('notify_selected')
                ->withProperties([
                    'vacancy_id' => $vacancy_id,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'skipped_count' => $skippedCount,
                    'section' => 'Exam Management',
                ])
                ->log('Sent exam notifications for selected applicants.');

            $message = $failedCount > 0 || $skippedCount > 0
                ? "{$sentCount} applicant(s) notified; {$failedCount} failed, {$skippedCount} skipped."
                : "{$sentCount} applicants notified successfully.";

            return response()->json([
                'success' => true,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'partial' => ($failedCount > 0 || $skippedCount > 0),
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error("Error in notifySelectedApplicants: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    private function sendRefinedNotifications(array $userIds, string $vacancy_id, ExamDetail $examDetail, ?string $publicBaseUrl = null, ?string $senderName = null)
    {
        return DB::transaction(function () use ($userIds, $vacancy_id, $examDetail, $publicBaseUrl, $senderName) {
            $sender_email = auth('admin')->user()->email ?? config('mail.from.address');
            $senderName ??= $this->resolveNotificationSenderName();
            $applicationColumns = array_flip(Schema::getColumnListing('applications'));
            $vacancy = JobVacancy::select('vacancy_id', 'position_title')->where('vacancy_id', $vacancy_id)->first();
            $result = [
                'sent' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];

            foreach ($userIds as $user_id) {
                // Find application
                $application = Applications::where('vacancy_id', $vacancy_id)
                    ->where('user_id', $user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$application || !$this->canReceiveExamLink($application)) {
                    $result['skipped']++;
                    continue;
                }

                // Generate token for this send with an expiry aligned to the exam window (not an ultra-short TTL).
                $token = Str::random(64);

                $startAt = ($examDetail->date && $examDetail->time)
                    ? \Carbon\Carbon::parse($examDetail->date . ' ' . $examDetail->time)
                    : now();

                $endAt = $examDetail->time_end
                    ? \Carbon\Carbon::parse($examDetail->date . ' ' . $examDetail->time_end)
                    : $startAt->copy()->addMinutes((int) ($examDetail->duration ?? 0));

                $expiryBase = $endAt->greaterThan($startAt) ? $endAt : $startAt->copy()->addHours(6);
                $expiresAt = $expiryBase->copy()->addMinutes(15);

                if ($expiresAt->lessThanOrEqualTo(now())) {
                    // Fallback if the computed window is already past (e.g., stale schedules).
                    $expiresAt = now()->addHours(6);
                }

                $payload = [
                    'exam_token' => $token,
                    'exam_token_expires_at' => $expiresAt,
                    'link_sent_at' => now(),
                ];

                    // Some deployments may not have these tracking columns yet; only update existing columns.
                foreach (['exam_token_used_at', 'exam_token_device_id', 'exam_token_used_ip', 'exam_token_used_ua'] as $optionalColumn) {
                    if (isset($applicationColumns[$optionalColumn])) {
                        $payload[$optionalColumn] = null;
                    }
                    }

                    $application->update($payload);

                try {
                    // Send immediately so the endpoint reflects real delivery attempts.
                    SendExamNotification::dispatchSync($vacancy_id, $user_id, $examDetail->id, $sender_email, $publicBaseUrl, $senderName);
                    $this->createExamLinkNotification((int) $user_id, $vacancy_id, $token, $vacancy);
                    $result['sent']++;
                } catch (\Throwable $sendException) {
                    $result['failed']++;
                    Log::error('Failed to send exam link notification', [
                        'vacancy_id' => $vacancy_id,
                        'user_id' => $user_id,
                        'error' => $sendException->getMessage(),
                    ]);
                }
            }
            return $result;
        });
    }

    public function saveExamDetails(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot modify exam details.')) {
            return $denied;
        }

        try {
            $batchNo = $this->resolveBatchNo($request);
            Log::info('saveExamDetails called', ['vacancy_id' => $vacancy_id, 'notify' => $request->boolean('notify')]);

            $validated = $request->validate([
                'time' => 'required',
                'time_end' => 'required',
                'date' => 'required|date',
                'place' => 'required|string',
                'duration' => 'required|integer',
                'max_violations' => 'nullable|integer|min:1',
                'message' => 'nullable|string',
            ]);

            // Add details_saved flag
            $validated['details_saved'] = true;

            ExamDetail::updateOrCreate(
                ['vacancy_id' => $vacancy_id, 'batch_no' => $batchNo],
                $validated
            );

            $examDetails = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();

            $notified = false;
            $notified_at = null;
            $notifyMessage = null;
            $sentCount = 0;
            $failedCount = 0;
            $skippedCount = 0;

            if ($request->boolean('notify')) {
                Log::info('Calling notifyApplicantsSchedule', ['vacancy_id' => $vacancy_id]);
                $response = $this->notifyApplicantsSchedule($request, $vacancy_id);

                // Check if notification was successful
                $responseData = $response->getData(true);
                if (isset($responseData['success']) && $responseData['success']) {
                    $examDetails->refresh();
                    $notified = true;
                    $notified_at = $examDetails->notified_at;
                    $notifyMessage = $responseData['message'] ?? null;
                    $sentCount = (int) ($responseData['sent_count'] ?? 0);
                    $failedCount = (int) ($responseData['failed_count'] ?? 0);
                    $skippedCount = (int) ($responseData['skipped_count'] ?? 0);
                    Log::info('Schedule notifications sent successfully', ['vacancy_id' => $vacancy_id]);
                } else {
                    Log::error('Schedule notification failed', ['vacancy_id' => $vacancy_id, 'response' => $responseData]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Exam details saved, but schedule notification failed: ' . ($responseData['message'] ?? 'Unknown error')
                    ], 500);
                }
            }

            activity()
                ->causedBy(auth('admin')->user())
                ->event('save')
                ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
                ->log('Saved exam schedule and details.');

            Log::info('saveExamDetails completed successfully', ['vacancy_id' => $vacancy_id, 'notified' => $notified]);

            return response()->json([
                'success' => true,
                'message' => 'Exam details saved.',
                'examDetails' => $examDetails,
                'notified' => $notified,
                'notified_at' => $notified_at,
                'notify_message' => $notifyMessage,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in saveExamDetails', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(fn($err) => implode(', ', $err), $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in saveExamDetails', [
                'vacancy_id' => $vacancy_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startExam(Request $request, $vacancy_id)
    {
        if ($denied = $this->denyViewerAccess($request, 'Viewer cannot start exams.')) {
            return $denied;
        }

        try {
            $batchNo = $this->resolveBatchNo($request);
            $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();

            if (!$examDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam details not found.'
                ], 404);
            }

            if (!$examDetail->link_sent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please send exam links to applicants first.'
                ], 400);
            }

            // Mark exam as started
            $examDetail->update(['is_started' => true]);

            activity()
                ->causedBy(auth('admin')->user())
                ->event('start')
                ->withProperties(['vacancy_id' => $vacancy_id, 'section' => 'Exam Management'])
                ->log('Started the exam.');

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error("Error starting exam: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkExamStatus(Request $request, $vacancy_id)
    {
        if (!auth()->check()) {
            return response()->json(['started' => false], 401);
        }

        $hasApplication = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$hasApplication) {
            return response()->json(['started' => false], 404);
        }

        $batchNo = $this->resolveBatchNo($request);
        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->where('batch_no', $batchNo)->first();

        if (!$examDetail) {
            return response()->json(['started' => false]);
        }

        $application = Applications::where('vacancy_id', $vacancy_id)
            ->where('user_id', auth()->id())
            ->first();

        $attempt = $this->getOrCreateAttempt($application, $batchNo);

        return response()->json([
            'started' => (bool) $examDetail->is_started,
            'paused' => $this->isExamGloballyPaused($examDetail) || $this->isApplicationPaused($attempt),
            'remaining_seconds' => $application ? $this->resolveExamRemainingSeconds($attempt, $examDetail) : null,
        ]);
    }

    public function checkParticipantExamStatus(Request $request, $vacancy_id)
    {
        if (!auth()->check()) {
            return response()->json([
                'authenticated' => false,
                'status' => null,
                'resume_available' => false,
            ], 401);
        }

        $batchNo = $this->resolveBatchNo($request);
        $application = Applications::select('id', 'status')
            ->where('vacancy_id', $vacancy_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$application) {
            return response()->json([
                'authenticated' => true,
                'status' => null,
                'resume_available' => false,
            ], 404);
        }

        $attempt = $this->getOrCreateAttempt($application, $batchNo);
        $isInProgress = ApplicationStatus::equals($attempt->status, ApplicationStatus::IN_PROGRESS);

        return response()->json([
            'authenticated' => true,
            'status' => $attempt->status,
            'resume_available' => $isInProgress && !empty($attempt->exam_started_at),
            'redirect_url' => $isInProgress
                ? route('user.exam_question_page', ['vacancy_id' => $vacancy_id, 'batch' => $batchNo])
                : null,
        ]);
    }

    public function confirmNotification($token)
    {
        $application = Applications::where('exam_token', $token)->first();

        if (!$application) {
            abort(404, 'Invalid token');
        }

        if (!$application->read_at) {
            $application->update(['read_at' => now()]);
        }

        return view('exam.confirmation');
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\ExamDetail;

class SendExamNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vacancyId;
    protected $userId;
    protected $examId;
    protected $senderEmail;
    protected $publicBaseUrl;
    protected $senderName;

    /**
     * Create a new job instance.
     */
    public function __construct($vacancyId, $userId, $examId, $senderEmail, $publicBaseUrl = null, $senderName = null)
    {
        $this->vacancyId = $vacancyId;
        $this->userId = $userId;
        $this->examId = $examId;
        $this->senderEmail = $senderEmail;
        $this->publicBaseUrl = $publicBaseUrl ? rtrim((string) $publicBaseUrl, '/') : null;
        $this->senderName = trim((string) ($senderName ?: config('mail.from.name', 'DILG-CAR Recruitment Team')));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            $vacancy = JobVacancy::where('vacancy_id', $this->vacancyId)->first();
            $examDetail = ExamDetail::find($this->examId);

            if (!$user || !$vacancy || !$examDetail) {
                $context = [
                    'user_id' => $this->userId,
                    'vacancy_id' => $this->vacancyId,
                    'exam_id' => $this->examId
                ];
                Log::error("SendExamNotification: Missing data", $context);
                throw new \RuntimeException('Unable to send exam notification due to missing data.');
            }

            // Generate exam link with token
            $application = \App\Models\Applications::where('user_id', $this->userId)
                ->where('vacancy_id', $this->vacancyId)
                ->first();

            $token = $application ? $application->exam_token : null;
            $expiresAt = $application ? $application->exam_token_expires_at : null;
            if (empty($token)) {
                throw new \RuntimeException('Exam token not found for applicant.');
            }
            $examPath = route('user.exam_lobby', [
                'vacancy_id' => $this->vacancyId,
                'token' => $token,
            ], false);

            // Always anchor externally shared links to APP_URL so they are reachable from other devices.
            $appUrl = $this->publicBaseUrl ?: rtrim((string) config('app.url', ''), '/');
            $examLink = $appUrl !== ''
                ? $appUrl . '/' . ltrim($examPath, '/')
                : url($examPath);

            // Send email using the exam schedule link template
            $fromAddress = (string) config('mail.from.address');
            $fromName = (string) config('mail.from.name', 'DILG-CAR Recruitment');
            $replyToAddress = filter_var((string) $this->senderEmail, FILTER_VALIDATE_EMAIL)
                ? (string) $this->senderEmail
                : null;

            Mail::send('emails.exam_sched_link', [
                'user' => $user,
                'vacancy' => $vacancy,
                'exam' => $examDetail,
                'join_link' => $examLink,
                'link_expires_at' => $expiresAt,
                'senderName' => $this->senderName,
            ], function ($message) use ($user, $vacancy, $fromAddress, $fromName, $replyToAddress) {
                $message->to($user->email, $user->name)
                    ->subject('Examination Schedule - ' . $vacancy->position_title);

                // Use configured mail sender for better SMTP compatibility.
                if ($fromAddress !== '') {
                    $message->from($fromAddress, $fromName);
                }

                if ($replyToAddress) {
                    $message->replyTo($replyToAddress);
                }
            });

            Log::info("Exam notification sent successfully", [
                'user_id' => $this->userId,
                'vacancy_id' => $this->vacancyId,
                'email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error("SendExamNotification failed: " . $e->getMessage(), [
                'user_id' => $this->userId,
                'vacancy_id' => $this->vacancyId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

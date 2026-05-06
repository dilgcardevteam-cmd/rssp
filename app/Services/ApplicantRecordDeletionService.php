<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Applications;
use App\Models\CivilServiceEligibility;
use App\Models\DocumentGalleryItem;
use App\Models\EducationalBackground;
use App\Models\EmailLog;
use App\Models\ExamTabViolation;
use App\Models\FamilyBackground;
use App\Models\LearningAndDevelopment;
use App\Models\MiscInfos;
use App\Models\Notification;
use App\Models\OtherInformation;
use App\Models\PersonalInformation;
use App\Models\Profile;
use App\Models\RelatedQuestions;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Models\VoluntaryWork;
use App\Models\WorkExpSheet;
use App\Models\WorkExperience;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ApplicantRecordDeletionService
{
    public function delete(User $user, ?Admin $deletedBy = null): void
    {
        $filePaths = $this->collectFilePaths($user);
        $activitySubjects = $this->collectActivitySubjects($user);
        $email = trim((string) $user->email);
        $userId = (int) $user->id;
        $applicantCode = trim((string) ($user->applicant_code ?? ''));
        $applicantName = trim((string) ($user->name ?? ''));

        DB::transaction(function () use ($user, $userId, $email, $activitySubjects, $deletedBy, $applicantCode, $applicantName): void {
            $this->deleteActivityLogs($userId, $activitySubjects);

            if (Schema::hasTable('notifications')) {
                Notification::query()
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $userId)
                    ->delete();
            }

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $userId)->delete();
            }

            if ($email !== '' && Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
            }

            if (Schema::hasTable('email_logs')) {
                EmailLog::query()->where('user_id', $userId)->delete();
            }

            if (Schema::hasTable('exam_tab_violations')) {
                ExamTabViolation::query()->where('user_id', $userId)->delete();
            }

            if (Schema::hasTable('work_exp_sheet')) {
                WorkExpSheet::query()->where('user_id', $userId)->delete();
            }

            Applications::query()->where('user_id', $userId)->delete();
            UploadedDocument::query()->where('user_id', $userId)->delete();
            DocumentGalleryItem::query()->where('user_id', $userId)->delete();
            WorkExperience::query()->where('user_id', $userId)->delete();
            CivilServiceEligibility::query()->where('user_id', $userId)->delete();
            LearningAndDevelopment::query()->where('user_id', $userId)->delete();
            VoluntaryWork::query()->where('user_id', $userId)->delete();
            OtherInformation::query()->where('user_id', $userId)->delete();
            PersonalInformation::query()->where('user_id', $userId)->delete();
            FamilyBackground::query()->where('user_id', $userId)->delete();
            EducationalBackground::query()->where('user_id', $userId)->delete();
            RelatedQuestions::query()->where('user_id', $userId)->delete();
            MiscInfos::query()->where('user_id', $userId)->delete();
            Profile::query()->where('user_id', $userId)->delete();

            $user->delete();

            if ($deletedBy) {
                activity()
                    ->causedBy($deletedBy)
                    ->event('delete')
                    ->withProperties([
                        'section' => 'Applicant Records',
                        'user_id' => $userId,
                        'applicant_code' => $applicantCode,
                        'applicant_name' => $applicantName,
                    ])
                    ->log('Permanently deleted applicant record.');
            }
        });

        if ($filePaths !== []) {
            Storage::disk('public')->delete($filePaths);
        }
    }

    /**
     * @return array<int, string>
     */
    private function collectFilePaths(User $user): array
    {
        $userId = (int) $user->id;

        $paths = [
            $user->avatar_path,
            MiscInfos::query()->where('user_id', $userId)->value('photo_upload'),
        ];

        $applicationFiles = Applications::query()
            ->where('user_id', $userId)
            ->pluck('file_storage_path')
            ->all();

        $uploadedDocumentFiles = UploadedDocument::query()
            ->where('user_id', $userId)
            ->pluck('storage_path')
            ->all();

        $galleryFiles = DocumentGalleryItem::query()
            ->where('user_id', $userId)
            ->pluck('storage_path')
            ->all();

        return collect(array_merge($paths, $applicationFiles, $uploadedDocumentFiles, $galleryFiles))
            ->map(fn ($path) => trim((string) $path))
            ->filter(fn ($path) => $path !== '' && strtoupper($path) !== 'NOINPUT')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function collectActivitySubjects(User $user): array
    {
        $userId = (int) $user->id;

        $subjectMap = [
            User::class => [$userId],
            Applications::class => Applications::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            UploadedDocument::class => UploadedDocument::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            DocumentGalleryItem::class => DocumentGalleryItem::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            PersonalInformation::class => PersonalInformation::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            FamilyBackground::class => FamilyBackground::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            EducationalBackground::class => EducationalBackground::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            WorkExperience::class => WorkExperience::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            CivilServiceEligibility::class => CivilServiceEligibility::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            LearningAndDevelopment::class => LearningAndDevelopment::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            VoluntaryWork::class => VoluntaryWork::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            OtherInformation::class => OtherInformation::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            RelatedQuestions::class => RelatedQuestions::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            MiscInfos::class => MiscInfos::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            Profile::class => Profile::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            WorkExpSheet::class => Schema::hasTable('work_exp_sheet')
                ? WorkExpSheet::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [],
            ExamTabViolation::class => Schema::hasTable('exam_tab_violations')
                ? ExamTabViolation::query()->where('user_id', $userId)->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [],
        ];

        return collect($subjectMap)
            ->map(fn ($ids) => array_values(array_unique(array_filter($ids, fn ($id) => $id > 0))))
            ->filter(fn ($ids) => $ids !== [])
            ->all();
    }

    /**
     * @param  array<string, array<int, int>>  $activitySubjects
     */
    private function deleteActivityLogs(int $userId, array $activitySubjects): void
    {
        $activityTable = (string) config('activitylog.table_name', 'activity_log');
        $activityConnection = config('activitylog.database_connection');
        $schema = $activityConnection ? Schema::connection($activityConnection) : Schema::connection(config('database.default'));

        if (!$schema->hasTable($activityTable)) {
            return;
        }

        $connection = $activityConnection ? DB::connection($activityConnection) : DB::connection();

        $connection
            ->table($activityTable)
            ->where(function ($query) use ($userId, $activitySubjects): void {
                $query->where(function ($causerQuery) use ($userId): void {
                    $causerQuery->where('causer_type', User::class)
                        ->where('causer_id', $userId);
                });

                foreach ($activitySubjects as $subjectType => $subjectIds) {
                    $query->orWhere(function ($subjectQuery) use ($subjectType, $subjectIds): void {
                        $subjectQuery->where('subject_type', $subjectType)
                            ->whereIn('subject_id', $subjectIds);
                    });
                }
            })
            ->delete();
    }
}

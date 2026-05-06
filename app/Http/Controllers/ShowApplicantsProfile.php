<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Applications;
use App\Models\PersonalInformation;
use App\Models\JobVacancy;
use App\Models\UploadedDocument;
use App\Models\AdminVacancyAccess;
use App\Models\User;
use App\Services\ApplicantDeletionWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ShowApplicantsProfile extends Controller
{
    private const REVISION_STATUSES = [
        'needs revision',
        'disapproved with deficiency',
    ];

    private function complianceStageStatuses(): array
    {
        return ApplicationStatus::complianceStages();
    }

    private function currentAdmin()
    {
        return Auth::guard('admin')->user();
    }

    private function buildHrDivisionAccessSignature(array $vacancyIds): string
    {
        $normalized = collect($vacancyIds)
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();

        return hash('sha256', implode('|', $normalized));
    }

    private function hrDivisionGrantedVacancyIds(): array
    {
        $admin = $this->currentAdmin();
        if (($admin->role ?? null) !== 'hr_division') {
            return [];
        }

        $grantedVacancyIds = [];
        if (Schema::hasTable('admin_vacancy_accesses')) {
            $grantedVacancyIds = AdminVacancyAccess::query()
                ->where('admin_id', $admin->id)
                ->pluck('vacancy_id')
                ->map(fn($value) => (string) $value)
                ->values()
                ->all();
        }

        $ownedVacancyIds = [];
        if (Schema::hasColumn('job_vacancies', 'created_by_admin_id')) {
            $ownedVacancyIds = JobVacancy::query()
                ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
                ->where('created_by_admin_id', $admin->id)
                ->pluck('vacancy_id')
                ->map(fn($value) => (string) $value)
                ->values()
                ->all();
        }

        return collect(array_merge($grantedVacancyIds, $ownedVacancyIds))
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function hrDivisionAccessSignature(): string
    {
        $admin = $this->currentAdmin();
        if (($admin->role ?? null) !== 'hr_division') {
            return '';
        }

        return $this->buildHrDivisionAccessSignature($this->hrDivisionGrantedVacancyIds());
    }

    private function hrDivisionCanAccessVacancy(?string $vacancyId): bool
    {
        $admin = $this->currentAdmin();
        if (($admin->role ?? null) !== 'hr_division') {
            return true;
        }

        $vacancyId = trim((string) $vacancyId);
        if ($vacancyId === '') {
            return false;
        }

        return JobVacancy::query()
            ->where('vacancy_id', $vacancyId)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->where(function ($query) use ($admin, $vacancyId) {
                $hasScope = false;

                if (Schema::hasColumn('job_vacancies', 'created_by_admin_id')) {
                    $query->where('created_by_admin_id', $admin->id);
                    $hasScope = true;
                }

                if (Schema::hasTable('admin_vacancy_accesses')) {
                    if ($hasScope) {
                        $query->orWhereExists(function ($sub) use ($admin, $vacancyId) {
                            $sub->selectRaw('1')
                                ->from('admin_vacancy_accesses')
                                ->where('admin_vacancy_accesses.admin_id', $admin->id)
                                ->where('admin_vacancy_accesses.vacancy_id', $vacancyId);
                        });
                    } else {
                        $query->whereExists(function ($sub) use ($admin, $vacancyId) {
                            $sub->selectRaw('1')
                                ->from('admin_vacancy_accesses')
                                ->where('admin_vacancy_accesses.admin_id', $admin->id)
                                ->where('admin_vacancy_accesses.vacancy_id', $vacancyId);
                        });
                    }
                    $hasScope = true;
                }

                if (!$hasScope) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->exists();
    }

    private function normalizeStatus(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function resolveApplicantName($application): string
    {
        $pi = $application->personalInformation;

        $fullName = trim(implode(' ', array_filter([
            trim((string) ($pi?->first_name ?? '')),
            filled($pi?->middle_name) ? strtoupper(substr((string) $pi->middle_name, 0, 1)) . '.' : '',
            trim((string) ($pi?->surname ?? '')),
            trim((string) ($pi?->name_extension ?? '')),
        ])));

        if ($fullName !== '') {
            return $fullName;
        }

        return trim((string) ($application->user?->name ?? '')) ?: 'N/A';
    }

    private function hasInitialAssessmentPqeColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('applications', 'initial_assessment_has_pqe');
        }

        return $hasColumn;
    }

    private function isNoPqeApplicant($application): bool
    {
        if (!$this->hasInitialAssessmentPqeColumn()) {
            return false;
        }

        $value = $application->initial_assessment_has_pqe ?? null;

        return $value === false
            || $value === 0
            || $value === '0'
            || $value === 'false'
            || $value === 'no';
    }

    private function isYesPqeApplicant($application): bool
    {
        if (!$this->hasInitialAssessmentPqeColumn()) {
            return false;
        }

        $value = $application->initial_assessment_has_pqe ?? null;

        return $value === true
            || $value === 1
            || $value === '1'
            || $value === 'true'
            || $value === 'yes';
    }

    private function hasUnknownPqeAnswer($application): bool
    {
        if (!$this->hasInitialAssessmentPqeColumn()) {
            return true;
        }

        return !$this->isYesPqeApplicant($application) && !$this->isNoPqeApplicant($application);
    }

    private function buildRevisionLookup($applications, string $vacancyId): array
    {
        $userIds = $applications->pluck('user_id')->filter()->unique()->values();
        if ($userIds->isEmpty()) {
            return [];
        }

        $query = UploadedDocument::query()
            ->whereIn('user_id', $userIds)
            ->whereRaw('LOWER(TRIM(status)) IN (?, ?)', self::REVISION_STATUSES);

        if (Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
            $query->where(function ($sub) use ($vacancyId) {
                $sub->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $revisionUserIds = $query->pluck('user_id')->unique()->all();

        return array_fill_keys($revisionUserIds, true);
    }

    private function determineApplicantStage($application, array $revisionLookup): string
    {
        $status = $this->normalizeStatus($application->status ?? '');
        $qsResult = $this->normalizeStatus($application->qs_result ?? '');
        $fileStatus = $this->normalizeStatus($application->file_status ?? '');
        $vacancyType = strtoupper(trim((string) ($application->vacancy?->vacancy_type ?? '')));

        $isQualified = $qsResult === 'qualified'
            || $status === $this->normalizeStatus(ApplicationStatus::QUALIFIED->value)
            || $status === 'complete';

        if ($isQualified) {
            return 'qualified';
        }

        $hasRevision = isset($revisionLookup[$application->user_id])
            || in_array($fileStatus, self::REVISION_STATUSES, true);

        $complianceStatuses = collect($this->complianceStageStatuses())
            ->map(fn($value) => $this->normalizeStatus($value))
            ->all();

        $isComplianceStatus = in_array($status, $complianceStatuses, true) || $status === 'incomplete';

        if ($hasRevision || $isComplianceStatus) {
            return 'compliance';
        }

        // COS vacancies do not use Initial Assessment Q3 (PQE split).
        // Pending applicants should stay in the New tab.
        if ($vacancyType === 'COS') {
            return 'new';
        }

        if ($this->isNoPqeApplicant($application)) {
            return 'no_pqe';
        }

        if ($this->hasInitialAssessmentPqeColumn()) {
            // Q3 routing:
            // - Yes  -> New
            // - No   -> No PQE
            // - Unknown/legacy values default to New
            return ($this->isYesPqeApplicant($application) || $this->hasUnknownPqeAnswer($application))
                ? 'new'
                : 'no_pqe';
        }

        return 'new';
    }

    private function partitionApplicantsByStage($applications, string $vacancyId): array
    {
        $revisionLookup = $this->buildRevisionLookup($applications, $vacancyId);
        $stages = [
            'new' => collect(),
            'compliance' => collect(),
            'qualified' => collect(),
            'no_pqe' => collect(),
        ];

        foreach ($applications as $application) {
            $stage = $this->determineApplicantStage($application, $revisionLookup);
            if (!array_key_exists($stage, $stages)) {
                $stage = 'new';
            }
            $stages[$stage]->push($application);
        }

        return $stages;
    }

    private function formatApplicants($applications)
    {
        return $applications->map(function ($application) {
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'applicant_code' => $application->user?->applicant_code,
                'vacancy_id' => $application->vacancy_id,
                'name' => $this->resolveApplicantName($application),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });
    }

    private function filterApplicantsBySearch($applications, string $search)
    {
        $needle = strtolower(trim($search));
        if ($needle === '') {
            return $applications;
        }

        return $applications->filter(function ($application) use ($needle) {
            $pi = $application->personalInformation;
            $nameParts = [
                $pi?->first_name,
                $pi?->middle_name,
                $pi?->surname,
                $pi?->name_extension,
                $application->user?->name,
            ];
            $haystack = strtolower(trim(implode(' ', array_filter($nameParts))));
            return $haystack !== '' && str_contains($haystack, $needle);
        });
    }

    private function sortApplicantsByDate($applications, string $sortOrder)
    {
        return $sortOrder === 'oldest'
            ? $applications->sortBy('created_at')
            : $applications->sortByDesc('created_at');
    }

    private function latestComplianceReuploadTimestampByUser($applications, string $vacancyId): array
    {
        $userIds = $applications->pluck('user_id')->filter()->unique()->values();
        if ($userIds->isEmpty()) {
            return [];
        }

        $query = UploadedDocument::query()
            ->whereIn('user_id', $userIds);

        if (Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
            $query->where(function ($sub) use ($vacancyId) {
                $sub->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $latestByUser = [];
        $rows = $query
            ->selectRaw('user_id, MAX(updated_at) as latest_upload_at')
            ->groupBy('user_id')
            ->get();

        foreach ($rows as $row) {
            if (!$row->user_id || !$row->latest_upload_at) {
                continue;
            }

            $latestByUser[$row->user_id] = Carbon::parse((string) $row->latest_upload_at)->timestamp;
        }

        return $latestByUser;
    }

    private function sortComplianceApplicantsByReupload($applications, string $vacancyId, string $sortOrder = 'latest')
    {
        $latestByUser = $this->latestComplianceReuploadTimestampByUser($applications, $vacancyId);

        $sortFn = function ($application) use ($latestByUser) {
            $userId = $application->user_id;
            $fallbackTs = optional($application->updated_at)->timestamp
                ?? optional($application->created_at)->timestamp
                ?? 0;

            return $latestByUser[$userId] ?? $fallbackTs;
        };

        return $sortOrder === 'oldest'
            ? $applications->sortBy($sortFn)
            : $applications->sortByDesc($sortFn);
    }

    private function applicantDisplayName(User $user): string
    {
        $user->loadMissing('personalInformation');

        $personalInfo = $user->personalInformation;
        $fullName = trim(implode(' ', array_filter([
            trim((string) ($personalInfo?->first_name ?? '')),
            filled($personalInfo?->middle_name) ? strtoupper(substr((string) $personalInfo->middle_name, 0, 1)) . '.' : '',
            trim((string) ($personalInfo?->surname ?? '')),
            trim((string) ($personalInfo?->name_extension ?? '')),
        ])));

        return $fullName !== '' ? $fullName : ((string) ($user->name ?: 'Applicant'));
    }

    public function index(Request $request, $vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not available to your HR Division account.');
        }

        logger()->info("Filtering applicants for vacancy: " . $vacancy_id);

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancy_id)
            ->statusEquals(ApplicationStatus::PENDING->value)
            ->orderByDesc('created_at') // Sort from newest to oldest
            ->get();

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'applicant_code' => $application->user?->applicant_code,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return view('admin.applicants_profile', [
            'applicants' => $formattedApplications,
            'filteredVacancyId' => $vacancy_id,
        ]);
    }


    public function reviewedIndex(Request $request, $vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not available to your HR Division account.');
        }

        $sortStatus = $request->input('sort_status');

        $query = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->whereRaw('LOWER(TRIM(status)) <> ?', [strtolower(ApplicationStatus::PENDING->value)])
            ->where('vacancy_id', $vacancy_id); // filter by vacancy_id

        if ($sortStatus) {
            $query->where('status', $sortStatus);
        }

        $applications = $query->get();

        $statusOrder = ['Incomplete' => 1, 'Complete' => 2, 'Closed' => 3];

        $applications = $applications->sortBy(function ($application) use ($statusOrder) {
            return $statusOrder[$application->status] ?? 999;
        });

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'applicant_code' => $application->user?->applicant_code,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return view('admin.reviewed_applicants', [
            'applicants' => $formattedApplications,
            'filteredVacancyId' => $vacancy_id,
        ]);
    }


    public function ajaxSort(Request $request)
    {
        $status = $request->input('sort_status');
        $vacancyId = $request->input('vacancy_id'); // ✅ Add this line

        if (($this->currentAdmin()->role ?? null) === 'hr_division' && empty($vacancyId)) {
            return response()->view('partials.reviewed_applicants_list', [
                'applicants' => collect(),
            ]);
        }

        if (!empty($vacancyId) && !$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.reviewed_applicants_list', [
                'applicants' => collect(),
            ]);
        }

        $query = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->whereRaw('LOWER(TRIM(status)) <> ?', [strtolower(ApplicationStatus::PENDING->value)]);

        if ($vacancyId) {
            $query->where('vacancy_id', $vacancyId); // ✅ Filter by current vacancy
        }

        if ($status) {
            $query->where('status', $status);
        }

        $applications = $query->get();

        $statusOrder = ['Incomplete' => 1, 'Complete' => 2, 'Closed' => 3];

        $applications = $applications->sortBy(function ($application) use ($statusOrder) {
            return $statusOrder[$application->status] ?? 999;
        });

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'applicant_code' => $application->user?->applicant_code,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.reviewed_applicants_list', [
            'applicants' => $formattedApplications
        ]);
    }

    public function applicationsList(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $admin = $this->currentAdmin();
        $grantedVacancyIds = $this->hrDivisionGrantedVacancyIds();
        $isHrDivisionUser = (($admin->role ?? null) === 'hr_division');
        $accessSignature = $isHrDivisionUser
            ? $this->buildHrDivisionAccessSignature($grantedVacancyIds)
            : '';

        $query = JobVacancy::query()
            ->select(['vacancy_id', 'position_title', 'vacancy_type', 'status', 'created_at']);

        if ($isHrDivisionUser) {
            if (empty($grantedVacancyIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
                    ->whereIn('vacancy_id', $grantedVacancyIds);
            }
        }

        // Filter by search text
        if (!empty($search)) {
            $query->where('position_title', 'LIKE', '%' . $search . '%');
        }

        // Filter by status (if used in future)
        if (!empty($status)) {
            $query->where('status', $status);
        }

        $countDefinitions = [
            'applications as pending_count' => function ($q) {
                $q->statusEquals(ApplicationStatus::PENDING->value);

                if ($this->hasInitialAssessmentPqeColumn()) {
                    $q->where(function ($sub) {
                        $sub->whereRaw("UPPER(COALESCE(job_vacancies.vacancy_type, '')) = ?", ['COS'])
                            ->orWhere(function ($yesOrUnknown) {
                                $yesOrUnknown->where('initial_assessment_has_pqe', true)
                                    ->orWhere('initial_assessment_has_pqe', 1)
                                    ->orWhereNull('initial_assessment_has_pqe')
                                    ->orWhereRaw('LOWER(TRIM(CAST(initial_assessment_has_pqe AS CHAR))) IN (?, ?, ?)', ['true', 'yes', '']);
                            });
                    });
                }
            },
            'applications as compliance_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->statusIn($this->complianceStageStatuses())
                        ->orWhereRaw('LOWER(TRIM(status)) = ?', ['incomplete']);
                });
            },
            'applications as qualified_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->statusEquals(ApplicationStatus::QUALIFIED->value)
                        ->orWhereRaw('LOWER(TRIM(status)) = ?', ['complete'])
                        ->orWhereRaw('LOWER(TRIM(qs_result)) = ?', ['qualified']);
                });
            },
        ];

        if ($this->hasInitialAssessmentPqeColumn()) {
            $countDefinitions['applications as no_pqe_count'] = function ($q) {
                $q->statusEquals(ApplicationStatus::PENDING->value)
                    ->whereRaw("UPPER(COALESCE(job_vacancies.vacancy_type, '')) <> ?", ['COS'])
                    ->where(function ($sub) {
                        $sub->where('initial_assessment_has_pqe', false)
                            ->orWhere('initial_assessment_has_pqe', 0)
                            ->orWhereRaw('LOWER(TRIM(CAST(initial_assessment_has_pqe AS CHAR))) IN (?, ?, ?)', ['false', 'no', '0']);
                    });
            };
        }

        // Get all vacancies with counts per status
        $vacancyQuery = $query->withCount($countDefinitions)
            ->orderByRaw("CASE WHEN LOWER(status) = 'open' THEN 1 WHEN LOWER(status) = 'closed' THEN 2 ELSE 99 END")
            ->orderByDesc('created_at');

        // Return JSON if AJAX (for search)
        if ($request->ajax()) {
            $vacancies = $vacancyQuery->limit(200)->get();
            return response()->json($vacancies->values()); // reset keys
        }

        $vacancies = $vacancyQuery->get();

        return view('admin.applications_list', [
            'vacancies' => $vacancies,
            'isHrDivisionUser' => $isHrDivisionUser,
            'accessSignature' => $accessSignature,
        ]);
    }

    public function applicantRecords(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $sort = strtolower(trim((string) $request->input('sort', 'latest')));

        $query = User::query()
            ->with(['personalInformation'])
            ->withCount('applications')
            ->withMax('applications', 'created_at');

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhereHas('personalInformation', function ($personalInfoQuery) use ($like) {
                        $personalInfoQuery->where('first_name', 'LIKE', $like)
                            ->orWhere('middle_name', 'LIKE', $like)
                            ->orWhere('surname', 'LIKE', $like)
                            ->orWhere('name_extension', 'LIKE', $like)
                            ->orWhere('mobile_no', 'LIKE', $like)
                            ->orWhere('email_address', 'LIKE', $like);
                    });
            });
        }

        if ($sort === 'oldest') {
            $query->orderByRaw('CASE WHEN applications_max_created_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('applications_max_created_at')
                ->orderBy('created_at');
        } else {
            $query->orderByRaw('CASE WHEN applications_max_created_at IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('applications_max_created_at')
                ->orderByDesc('created_at');
        }

        $applicants = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->view('partials.applicant_records_results', [
                'applicants' => $applicants,
            ]);
        }

        return view('admin.applicant_records', [
            'applicants' => $applicants,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function showApplicantRecord(User $user)
    {
        $user->load([
            'personalInformation',
            'familyBackground',
            'educationalBackground',
            'workExperience' => fn ($query) => $query->orderByDesc('work_exp_to')->orderByDesc('work_exp_from'),
            'civilServiceEligibility' => fn ($query) => $query->orderByDesc('cs_eligibility_date'),
            'learningAndDevelopment' => fn ($query) => $query->orderByDesc('learning_to')->orderByDesc('learning_from'),
            'voluntaryWork' => fn ($query) => $query->orderByDesc('voluntary_to')->orderByDesc('voluntary_from'),
            'otherInformation',
            'relatedQuestions',
            'miscInfos',
            'profile',
            'applications' => fn ($query) => $query->with('vacancy')->orderByDesc('created_at'),
        ]);
        $user->loadCount('applications');
        $user->loadMax('applications', 'created_at');

        return view('admin.applicant_record_show', [
            'applicant' => $user,
        ]);
    }

    public function scheduleApplicantRecordDeletion(Request $request, User $user, ApplicantDeletionWorkflowService $workflow)
    {
        if ($user->isPendingDeletion()) {
            return response()->json([
                'message' => 'This applicant is already set for deletion.',
            ], 422);
        }

        $displayName = $this->applicantDisplayName($user);
        $deadline = $workflow->schedule($user, Auth::guard('admin')->user());

        return response()->json([
            'message' => sprintf(
                'Applicant record for %s is set for deletion until %s.',
                $displayName,
                $deadline->format('M d, Y h:i A')
            ),
            'deadline' => $deadline->toIso8601String(),
        ]);
    }

    public function cancelApplicantRecordDeletion(Request $request, User $user, ApplicantDeletionWorkflowService $workflow)
    {
        if (!$user->isPendingDeletion()) {
            return response()->json([
                'message' => 'This applicant is not currently set for deletion.',
            ], 422);
        }

        $displayName = $this->applicantDisplayName($user);
        $workflow->cancel($user, Auth::guard('admin')->user());

        return response()->json([
            'message' => sprintf('Scheduled deletion for %s has been cancelled.', $displayName),
        ]);
    }

    public function destroyApplicantRecord(Request $request, User $user, ApplicantDeletionWorkflowService $workflow)
    {
        if ($user->isPendingDeletion()) {
            return response()->json([
                'message' => 'Cancel the scheduled deletion first if you need to change the deletion mode.',
            ], 422);
        }

        $displayName = $this->applicantDisplayName($user);

        try {
            $workflow->deleteImmediately($user, Auth::guard('admin')->user());

            $message = sprintf('Applicant record for %s has been permanently deleted.', $displayName);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                ]);
            }

            return redirect()
                ->route('admin.applicant_records.index')
                ->with('success', $message);
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'Unable to delete the applicant record right now.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', $message);
        }
    }

    public function hrDivisionAccessState()
    {
        $admin = $this->currentAdmin();
        if (!$admin) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $isHrDivisionUser = (($admin->role ?? null) === 'hr_division');
        return response()->json([
            'is_hr_division' => $isHrDivisionUser,
            'access_signature' => $isHrDivisionUser ? $this->hrDivisionAccessSignature() : '',
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function ajaxSortApplicants(Request $request)
    {
        $sortOrder = $request->input('sort_order', 'latest');
        $vacancyId = $request->input('vacancy_id');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.applicants_list_ajax', ['applicants' => collect()]);
        }

        $query = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->statusEquals(ApplicationStatus::PENDING->value);

        if ($sortOrder === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $applications = $query->get();

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'applicant_code' => $application->user?->applicant_code,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return response()->view('partials.applicants_list_ajax', ['applicants' => $formattedApplications]);
    }

    public function allApplicants($vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not available to your HR Division account.');
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancy_id)
            ->orderByDesc('created_at') // Newest first
            ->get();

        $formattedApplications = $applications->map(function ($application) {
            $pi = $application->personalInformation;
            $vacancy = $application->vacancy;

            return [
                'user_id' => $application->user_id,
                'applicant_code' => $application->user?->applicant_code,
                'vacancy_id' => $application->vacancy_id,
                'name' => $pi
                    ? trim("{$pi->first_name} " .
                        ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
                        "{$pi->surname} {$pi->name_extension}")
                    : ($application->user?->name ?? 'N/A'),
                'job_applied' => $vacancy->position_title ?? 'N/A',
                'place_of_assignment' => $vacancy->place_of_assignment ?? 'N/A',
                'status' => $application->status ?? 'N/A',
            ];
        });

        return view('admin.all_applicants_profile', [
            'applicants' => $formattedApplications,
            'filteredVacancyId' => $vacancy_id,
        ]);
    }

    public function manageApplicants(Request $request, $vacancy_id)
    {
        if (!$this->hrDivisionCanAccessVacancy((string) $vacancy_id)) {
            return redirect()->route('applications_list')
                ->with('error', 'Access denied. This COS vacancy is not available to your HR Division account.');
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancy_id)
            ->orderByDesc('created_at')
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancy_id);

        $newApplications = $partitioned['new']->sortByDesc('created_at');
        $complianceApplications = $this->sortComplianceApplicantsByReupload($partitioned['compliance'], (string) $vacancy_id, 'latest');
        $qualifiedApplications = $partitioned['qualified']->sortByDesc('created_at');
        $noPqeApplications = $partitioned['no_pqe']->sortByDesc('created_at');

        $formattedNewApplicants = $this->formatApplicants($newApplications);
        $formattedComplianceApplicants = $this->formatApplicants($complianceApplications);
        $formattedQualifiedApplicants = $this->formatApplicants($qualifiedApplications);
        $formattedNoPqeApplicants = $this->formatApplicants($noPqeApplications);

        // Fetch vacancy info for header
        $vacancyInfo = JobVacancy::select('position_title', 'vacancy_type', 'place_of_assignment')
            ->where('vacancy_id', $vacancy_id)
            ->first();

        $isPlantillaVacancy = strtoupper(trim((string) ($vacancyInfo?->vacancy_type ?? ''))) === 'PLANTILLA';
        $vacancyTypeLabel = strtoupper(trim((string) ($vacancyInfo?->vacancy_type ?? ''))) === 'COS'
            ? 'Contract of Service'
            : $vacancyInfo?->vacancy_type;

        return view('admin.manage_applicants', [
            'newApplicants' => $formattedNewApplicants,
            'complianceApplicants' => $formattedComplianceApplicants,
            'qualifiedApplicants' => $formattedQualifiedApplicants,
            'noPqeApplicants' => $formattedNoPqeApplicants,
            'newApplicantsCount' => $newApplications->count(),
            'complianceApplicantsCount' => $complianceApplications->count(),
            'qualifiedApplicantsCount' => $qualifiedApplications->count(),
            'noPqeApplicantsCount' => $isPlantillaVacancy ? $noPqeApplications->count() : 0,
            'vacancyId' => $vacancy_id,
            'positionTitle' => $vacancyInfo?->position_title,
            'vacancyType' => $vacancyTypeLabel,
            'placeOfAssignment' => $vacancyInfo?->place_of_assignment,
            'showNoPqeTab' => $isPlantillaVacancy,
        ]);
    }

    public function ajaxFilterNewApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_new_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['new'], (string) $search);
        $sorted = $this->sortComplianceApplicantsByReupload($filtered, (string) $vacancyId, (string) $sortOrder)->values();

        return response()->view('partials.manage_new_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

    public function ajaxFilterComplianceApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_new_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['compliance'], (string) $search);
        $sorted = $this->sortApplicantsByDate($filtered, (string) $sortOrder)->values();

        return response()->view('partials.manage_new_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

    public function ajaxFilterQualifiedApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');
        // $status filter removed as we only show Qualified here

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_qualified_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['qualified'], (string) $search);
        $sorted = $this->sortApplicantsByDate($filtered, (string) $sortOrder)->values();

        return response()->view('partials.manage_qualified_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

    public function ajaxFilterNoPqeApplicants(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $search = $request->input('search');
        $sortOrder = $request->input('sort_order', 'latest');

        if (!$this->hrDivisionCanAccessVacancy((string) $vacancyId)) {
            return response()->view('partials.manage_no_pqe_applicants_list', ['applicants' => collect()]);
        }

        $applications = Applications::with(['vacancy', 'personalInformation', 'user'])
            ->where('vacancy_id', $vacancyId)
            ->get();

        $partitioned = $this->partitionApplicantsByStage($applications, (string) $vacancyId);
        $filtered = $this->filterApplicantsBySearch($partitioned['no_pqe'], (string) $search);
        $sorted = $this->sortApplicantsByDate($filtered, (string) $sortOrder)->values();

        return response()->view('partials.manage_no_pqe_applicants_list', [
            'applicants' => $this->formatApplicants($sorted)
        ]);
    }

}

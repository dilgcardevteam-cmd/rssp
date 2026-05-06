<?php

namespace App\Http\Controllers;

use App\Models\Applications;
use App\Models\ExamDetail;
use App\Models\JobVacancy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    private const PASSING_PERCENTAGE = 75.0;
    private const REPORT_CACHE_TTL_SECONDS = 300;
    private const REPORT_CACHE_VERSION = 'v1';

    private const REPORT_TYPES = [
        'vacancy_summary',
        'vacancy_performance',
        'vacancy_detailed',
        'applicant_master_list',
        'applicant_status_analytics',
        'applicant_demographic_breakdown',
        'exam_schedule',
        'exam_result_summary',
        'exam_vacancy_based_result',
    ];

    public function index()
    {
        $vacancies = Cache::remember(
            'reports:index:vacancies:' . self::REPORT_CACHE_VERSION,
            now()->addSeconds(self::REPORT_CACHE_TTL_SECONDS),
            function () {
                return JobVacancy::query()
                    ->select(['vacancy_id', 'position_title', 'vacancy_type', 'status'])
                    ->orderBy('position_title')
                    ->orderBy('vacancy_id')
                    ->get();
            }
        );

        return view('admin.reports.index', compact('vacancies'));
    }

    public function getData(Request $request)
    {
        $type = trim((string) $request->input('type', 'vacancy_summary'));
        if (!in_array($type, self::REPORT_TYPES, true)) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }

        [$start, $end] = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request, $start, $end);
        $payload = $this->reportPayloadFromCache($type, $filters);

        if ($payload === null) {
            return response()->json(['error' => 'Unable to build report'], 422);
        }

        return response()->json($payload);
    }

    public function export(Request $request)
    {
        $type = trim((string) $request->input('type', 'applicant_master_list'));
        if (!in_array($type, self::REPORT_TYPES, true)) {
            return response()->json(['error' => 'Invalid report type'], 400);
        }

        [$start, $end] = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request, $start, $end);
        $payload = $this->reportPayloadFromCache($type, $filters);

        if ($payload === null || empty($payload['table']['headers'] ?? [])) {
            return response()->json(['error' => 'No exportable data found for this report'], 422);
        }

        $format = strtolower(trim((string) $request->input('format', 'csv')));
        if ($format === 'xlsx') {
            $format = 'excel';
        }

        $headers = $payload['table']['headers'];
        $rows = $payload['table']['rows'] ?? [];
        $baseName = Str::slug($type . '-' . now()->format('Y-m-d'));

        if ($format === 'pdf') {
            if ($type !== 'applicant_master_list') {
                return response()->json(['error' => 'PDF export is available for Applicant Master List only.'], 422);
            }

            return $this->exportApplicantMasterListPdf($baseName . '.pdf', $headers, $rows, $payload['title'] ?? 'Applicant Master List');
        }

        if ($format === 'excel') {
            if (!in_array($type, ['applicant_master_list', 'applicant_demographic_breakdown'], true)) {
                return response()->json(['error' => 'Excel export is available for Applicant Master List and Applicant Demographic Breakdown only.'], 422);
            }

            if ($type === 'applicant_demographic_breakdown') {
                return $this->exportApplicantDemographicExcel($baseName . '.xlsx', $payload);
            }

            return $this->exportExcel($baseName . '.xlsx', $headers, $rows, $payload['title'] ?? 'Applicant Master List');
        }

        return $this->exportCsv($baseName . '.csv', $headers, $rows);
    }

    private function resolveDateRange(Request $request): array
    {
        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfDay();

        try {
            if ($request->filled('start_date')) {
                $start = Carbon::parse((string) $request->input('start_date'))->startOfDay();
            }
            if ($request->filled('end_date')) {
                $end = Carbon::parse((string) $request->input('end_date'))->endOfDay();
            }
        } catch (\Throwable $e) {
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now()->endOfDay();
        }

        if ($end->lt($start)) {
            $swap = $start->copy();
            $start = $end->copy()->startOfDay();
            $end = $swap->copy()->endOfDay();
        }

        return [$start, $end];
    }

    private function resolveFilters(Request $request, Carbon $start, Carbon $end): array
    {
        return [
            'start' => $start,
            'end' => $end,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'vacancy_id' => trim((string) $request->input('vacancy_id', '')),
            'status' => trim((string) $request->input('status', '')),
            'qualification' => trim((string) $request->input('qualification', '')),
            'age_group' => trim((string) $request->input('age_group', '')),
        ];
    }

    private function buildReportPayload(string $type, array $filters): ?array
    {
        return match ($type) {
            'vacancy_summary' => $this->buildVacancySummaryReport($filters),
            'vacancy_performance' => $this->buildVacancyPerformanceReport($filters),
            'vacancy_detailed' => $this->buildVacancyDetailedReport($filters),
            'applicant_master_list' => $this->buildApplicantMasterListReport($filters),
            'applicant_status_analytics' => $this->buildApplicantStatusAnalyticsReport($filters),
            'applicant_demographic_breakdown' => $this->buildApplicantDemographicBreakdownReport($filters),
            'exam_schedule' => $this->buildExamScheduleReport($filters),
            'exam_result_summary' => $this->buildExamResultSummaryReport($filters),
            'exam_vacancy_based_result' => $this->buildExamVacancyBasedResultReport($filters),
            default => null,
        };
    }

    private function reportPayloadFromCache(string $type, array $filters): ?array
    {
        $cacheKey = $this->buildReportCacheKey($type, $filters);

        return Cache::remember(
            $cacheKey,
            now()->addSeconds(self::REPORT_CACHE_TTL_SECONDS),
            fn() => $this->buildReportPayload($type, $filters)
        );
    }

    private function buildReportCacheKey(string $type, array $filters): string
    {
        $normalized = [
            'type' => $type,
            'start_date' => (string) ($filters['start_date'] ?? ''),
            'end_date' => (string) ($filters['end_date'] ?? ''),
            'vacancy_id' => (string) ($filters['vacancy_id'] ?? ''),
            'status' => (string) ($filters['status'] ?? ''),
            'qualification' => (string) ($filters['qualification'] ?? ''),
            'age_group' => (string) ($filters['age_group'] ?? ''),
        ];

        return 'reports:data:' . self::REPORT_CACHE_VERSION . ':' . sha1(json_encode($normalized));
    }

    private function buildVacancySummaryReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $totalVacancies = $vacancies->count();
        $activeCount = $vacancies->filter(fn($v) => strtoupper((string) $v->status) !== 'CLOSED')->count();
        $closedCount = $totalVacancies - $activeCount;
        $cosCount = $vacancies->filter(fn($v) => strtoupper((string) $v->vacancy_type) === 'COS')->count();
        $plantillaCount = $totalVacancies - $cosCount;

        $rows = $vacancies
            ->map(function ($vacancy) use ($appsByVacancy) {
                $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());
                $isFilled = $this->isVacancyFilled($vacancyApps);

                return [
                    (string) $vacancy->vacancy_id,
                    (string) $vacancy->position_title,
                    $this->normalizeVacancyType((string) $vacancy->vacancy_type),
                    strtoupper((string) $vacancy->status),
                    $vacancyApps->count(),
                    $isFilled ? 'Filled' : 'Unfilled',
                ];
            })
            ->sortByDesc(fn($row) => (int) $row[4])
            ->values();

        $filledCount = $rows->where(5, 'Filled')->count();
        $unfilledCount = max($totalVacancies - $filledCount, 0);

        return [
            'type' => 'vacancy_summary',
            'title' => 'Vacancy Summary Report',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Total Vacancies', 'value' => $totalVacancies],
                ['label' => 'Active Vacancies', 'value' => $activeCount],
                ['label' => 'Closed Vacancies', 'value' => $closedCount],
                ['label' => 'Filled vs Unfilled', 'value' => "{$filledCount} / {$unfilledCount}"],
            ],
            'charts' => [
                [
                    'title' => 'Active vs Closed',
                    'type' => 'doughnut',
                    'labels' => ['Active', 'Closed'],
                    'datasets' => [[
                        'label' => 'Vacancies',
                        'data' => [$activeCount, $closedCount],
                        'backgroundColor' => $this->monoShades(2),
                    ]],
                ],
                [
                    'title' => 'COS vs Plantilla Distribution',
                    'type' => 'pie',
                    'labels' => ['COS', 'Plantilla'],
                    'datasets' => [[
                        'label' => 'Vacancies',
                        'data' => [$cosCount, $plantillaCount],
                        'backgroundColor' => $this->monoShades(2),
                    ]],
                ],
                [
                    'title' => 'Applicants per Vacancy (Top 10)',
                    'type' => 'bar',
                    'labels' => $rows->take(10)->map(fn($row) => $this->shortLabel($row[1]))->values()->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $rows->take(10)->map(fn($row) => (int) $row[4])->values()->all(),
                        'backgroundColor' => $this->monoColor(0),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicants per Vacancy',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Type', 'Status', 'Applicants', 'Fill State'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }
    private function buildVacancyPerformanceReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $rows = $vacancies
            ->map(function ($vacancy) use ($appsByVacancy) {
                $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());
                $appCount = $vacancyApps->count();

                $firstSuccessful = $vacancyApps
                    ->filter(fn($app) => $this->resolveOutcomeForApplication($app) === 'passed')
                    ->sortBy('created_at')
                    ->first();

                $timeToFillDays = null;
                $firstSuccessfulDate = '-';
                if ($firstSuccessful) {
                    $createdAt = Carbon::parse((string) $vacancy->created_at);
                    $firstSuccessfulAt = Carbon::parse((string) $firstSuccessful->created_at);
                    $timeToFillDays = $createdAt->diffInDays($firstSuccessfulAt);
                    $firstSuccessfulDate = $firstSuccessfulAt->format('M d, Y');
                }

                return [
                    'vacancy_id' => (string) $vacancy->vacancy_id,
                    'position_title' => (string) $vacancy->position_title,
                    'applications' => $appCount,
                    'first_successful_date' => $firstSuccessfulDate,
                    'time_to_fill_days' => $timeToFillDays,
                    'status' => strtoupper((string) $vacancy->status),
                ];
            })
            ->sortByDesc('applications')
            ->values();

        $totalVacancies = $vacancies->count();
        $totalApplications = $applications->count();
        $averageApplicants = $totalVacancies > 0 ? round($totalApplications / $totalVacancies, 2) : 0.0;

        $filledRows = $rows->whereNotNull('time_to_fill_days')->values();
        $avgTimeToFill = $filledRows->isNotEmpty()
            ? round($filledRows->avg('time_to_fill_days'), 2)
            : null;

        $mostApplied = $rows->first();
        $mostAppliedText = $mostApplied
            ? $this->shortLabel((string) $mostApplied['position_title']) . ' (' . (int) $mostApplied['applications'] . ')'
            : 'N/A';

        return [
            'type' => 'vacancy_performance',
            'title' => 'Vacancy Performance Report',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Average Applicants / Vacancy', 'value' => number_format($averageApplicants, 2)],
                ['label' => 'Average Time-to-Fill (Days)', 'value' => $avgTimeToFill === null ? 'N/A' : number_format($avgTimeToFill, 2)],
                ['label' => 'Most Applied Vacancy', 'value' => $mostAppliedText],
                ['label' => 'Vacancies Evaluated', 'value' => $totalVacancies],
            ],
            'charts' => [
                [
                    'title' => 'Applicants per Vacancy',
                    'type' => 'bar',
                    'labels' => $rows->take(10)->map(fn($row) => $this->shortLabel((string) $row['position_title']))->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $rows->take(10)->pluck('applications')->map(fn($v) => (int) $v)->all(),
                        'backgroundColor' => $this->monoColor(0),
                    ]],
                ],
                [
                    'title' => 'Time-to-Fill by Vacancy (Days)',
                    'type' => 'line',
                    'labels' => $filledRows->map(fn($row) => $this->shortLabel((string) $row['position_title']))->all(),
                    'datasets' => [[
                        'label' => 'Days to Fill',
                        'data' => $filledRows->pluck('time_to_fill_days')->map(fn($v) => (int) $v)->all(),
                        'borderColor' => $this->monoColor(2),
                        'backgroundColor' => 'rgba(13, 43, 112, 0.2)',
                        'fill' => true,
                        'tension' => 0.2,
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Vacancy Performance Details',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Applicants', 'First Successful Applicant', 'Time-to-Fill (Days)', 'Vacancy Status'],
                'rows' => $rows->map(function ($row) {
                    return [
                        $row['vacancy_id'],
                        $row['position_title'],
                        (int) $row['applications'],
                        $row['first_successful_date'],
                        $row['time_to_fill_days'] === null ? 'N/A' : (int) $row['time_to_fill_days'],
                        $row['status'],
                    ];
                })->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildVacancyDetailedReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $rows = $vacancies->map(function ($vacancy) use ($appsByVacancy) {
            $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());
            $breakdown = ['reviewed' => 0, 'ongoing' => 0, 'passed' => 0, 'failed' => 0];

            foreach ($vacancyApps as $application) {
                $bucket = $this->resolveAnalyticsBucket($application);
                if (array_key_exists($bucket, $breakdown)) {
                    $breakdown[$bucket]++;
                }
            }

            return [
                (string) $vacancy->position_title,
                $this->normalizeVacancyType((string) $vacancy->vacancy_type),
                Carbon::parse((string) $vacancy->created_at)->format('M d, Y'),
                $vacancy->closing_date ? Carbon::parse((string) $vacancy->closing_date)->format('M d, Y') : '-',
                $vacancyApps->count(),
                $breakdown['reviewed'],
                $breakdown['ongoing'],
                $breakdown['passed'],
                $breakdown['failed'],
            ];
        })->values();

        $reviewedTotal = $rows->sum(fn($row) => (int) $row[5]);
        $ongoingTotal = $rows->sum(fn($row) => (int) $row[6]);
        $passedTotal = $rows->sum(fn($row) => (int) $row[7]);
        $failedTotal = $rows->sum(fn($row) => (int) $row[8]);

        return [
            'type' => 'vacancy_detailed',
            'title' => 'Vacancy Detailed Report (Printable)',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Total Vacancies', 'value' => $vacancies->count()],
                ['label' => 'Total Applicants', 'value' => $rows->sum(fn($row) => (int) $row[4])],
                ['label' => 'Passed', 'value' => $passedTotal],
                ['label' => 'Failed', 'value' => $failedTotal],
            ],
            'charts' => [
                [
                    'title' => 'Status Breakdown',
                    'type' => 'bar',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [$reviewedTotal, $ongoingTotal, $passedTotal, $failedTotal],
                        'backgroundColor' => $this->monoShades(4),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Vacancy Detailed Listing',
                'headers' => ['Vacancy Title', 'Type', 'Opening Date', 'Closing Date', 'Total Applicants', 'Reviewed', 'Ongoing', 'Passed', 'Failed'],
                'rows' => $rows->all(),
            ],
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'printable' => true,
            ],
        ];
    }

    private function buildApplicantMasterListReport(array $filters): array
    {
        $applications = $this->applicantMasterListQuery($filters)->get();
        $statusFilter = strtolower($filters['status']);

        if (in_array($statusFilter, ['reviewed', 'ongoing', 'passed', 'failed', 'withdrawn'], true)) {
            $applications = $applications->filter(function ($application) use ($statusFilter) {
                return $this->resolveAnalyticsBucket($application) === $statusFilter;
            })->values();
        }

        $rows = $applications->map(function ($application) {
            $scorePct = $this->extractScorePercentage((string) ($application->result ?? ''));
            $outcome = $this->resolveOutcomeForApplication($application);
            $userName = trim((string) optional($application->user)->name);
            $userEmail = trim((string) optional($application->user)->email);
            $vacancyTitle = trim((string) optional($application->vacancy)->position_title);

            return [
                Carbon::parse((string) $application->created_at)->format('Y-m-d'),
                $userName !== '' ? $userName : ('Applicant #' . (int) $application->user_id),
                $userEmail !== '' ? $userEmail : '-',
                (string) $application->vacancy_id,
                $vacancyTitle !== '' ? $vacancyTitle : '-',
                (string) $application->status,
                (string) ($application->qs_result ?: '-'),
                (string) ($application->result ?: '-'),
                $scorePct === null ? '-' : number_format($scorePct, 2) . '%',
                $outcome ? ucfirst($outcome) : 'N/A',
            ];
        })->values();

        $bucketCounts = $this->countAnalyticsBuckets($applications);

        return [
            'type' => 'applicant_master_list',
            'title' => 'Applicant Master List',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Total Applicants', 'value' => $applications->count()],
                ['label' => 'Reviewed', 'value' => $bucketCounts['reviewed']],
                ['label' => 'Ongoing', 'value' => $bucketCounts['ongoing']],
                ['label' => 'Passed / Failed', 'value' => $bucketCounts['passed'] . ' / ' . $bucketCounts['failed']],
            ],
            'charts' => [
                [
                    'title' => 'Applicant Outcome Distribution',
                    'type' => 'bar',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed', 'Withdrawn'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [
                            $bucketCounts['reviewed'],
                            $bucketCounts['ongoing'],
                            $bucketCounts['passed'],
                            $bucketCounts['failed'],
                            $bucketCounts['withdrawn'],
                        ],
                        'backgroundColor' => $this->monoShades(5),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicant Master Listing',
                'headers' => ['Date Applied', 'Applicant Name', 'Email', 'Vacancy ID', 'Vacancy Title', 'Status', 'Qualification', 'Exam Result', 'Score %', 'Outcome'],
                'rows' => $rows->all(),
            ],
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'export_pdf' => true,
                'export_excel' => true,
            ],
        ];
    }

    private function buildApplicantStatusAnalyticsReport(array $filters): array
    {
        $applications = $this->applicationsForScope($filters);
        if ($filters['qualification'] !== '') {
            $applications = $applications->filter(fn($app) => strcasecmp((string) ($app->qs_result ?? ''), $filters['qualification']) === 0)->values();
        }

        $bucketCounts = $this->countAnalyticsBuckets($applications);

        return [
            'type' => 'applicant_status_analytics',
            'title' => 'Applicant Status Analytics',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Reviewed', 'value' => $bucketCounts['reviewed']],
                ['label' => 'Ongoing', 'value' => $bucketCounts['ongoing']],
                ['label' => 'Passed', 'value' => $bucketCounts['passed']],
                ['label' => 'Failed', 'value' => $bucketCounts['failed']],
                ['label' => 'Withdrawn', 'value' => $bucketCounts['withdrawn']],
            ],
            'charts' => [
                [
                    'title' => 'Applicant Lifecycle Distribution',
                    'type' => 'doughnut',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed', 'Withdrawn'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [
                            $bucketCounts['reviewed'],
                            $bucketCounts['ongoing'],
                            $bucketCounts['passed'],
                            $bucketCounts['failed'],
                            $bucketCounts['withdrawn'],
                        ],
                        'backgroundColor' => $this->monoShades(5),
                    ]],
                ],
                [
                    'title' => 'Applicant Lifecycle Counts',
                    'type' => 'bar',
                    'labels' => ['Reviewed', 'Ongoing', 'Passed', 'Failed', 'Withdrawn'],
                    'datasets' => [[
                        'label' => 'Count',
                        'data' => [
                            $bucketCounts['reviewed'],
                            $bucketCounts['ongoing'],
                            $bucketCounts['passed'],
                            $bucketCounts['failed'],
                            $bucketCounts['withdrawn'],
                        ],
                        'backgroundColor' => $this->monoColor(0),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicant Status Breakdown',
                'headers' => ['Category', 'Count'],
                'rows' => [
                    ['Reviewed', $bucketCounts['reviewed']],
                    ['Ongoing', $bucketCounts['ongoing']],
                    ['Passed', $bucketCounts['passed']],
                    ['Failed', $bucketCounts['failed']],
                    ['Withdrawn', $bucketCounts['withdrawn']],
                ],
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildApplicantDemographicBreakdownReport(array $filters): array
    {
        $applications = Applications::query()
            ->with([
                'vacancy:vacancy_id,position_title',
                'user:id,name,email',
                'user.personalInformation:user_id,sex,civil_status,date_of_birth,citizenship',
                'user.educationalBackground:user_id,elem_school,jhs_school,shs_school,vocational,college,grad',
            ])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->when($filters['qualification'] !== '', function ($query) use ($filters) {
                $query->where('qs_result', $filters['qualification']);
            })
            ->get();

        $byApplicant = $applications->groupBy('user_id');
        $ageGroupFilter = strtolower(trim((string) ($filters['age_group'] ?? '')));
        $rows = collect();

        $sexCounts = ['Male' => 0, 'Female' => 0, 'Prefer not to say' => 0];
        $ageGroupCounts = ['18-24' => 0, '25-34' => 0, '35-44' => 0, '45-54' => 0, '55+' => 0, 'Unknown' => 0];
        $civilCounts = [];
        $citizenshipCounts = [];
        $educationCounts = [];
        $knownAgeCount = 0;
        $ageTotal = 0;
        $withPdsCount = 0;

        foreach ($byApplicant as $userId => $userApplications) {
            $latestApplication = $userApplications->sortByDesc('created_at')->first();
            if (!$latestApplication) {
                continue;
            }

            $user = $latestApplication->user;
            $personal = optional($user)->personalInformation;
            $education = optional($user)->educationalBackground;

            if ($personal) {
                $withPdsCount++;
            }

            $sex = $this->normalizeSex((string) ($personal->sex ?? ''));
            $civilStatus = $this->normalizeDemographicLabel((string) ($personal->civil_status ?? ''), 'Unspecified');
            $citizenship = $this->normalizeDemographicLabel((string) ($personal->citizenship ?? ''), 'Unspecified');
            $age = $this->computeApplicantAge($personal->date_of_birth ?? null);
            $ageGroup = $this->resolveAgeGroup($age);
            $educationLevel = $this->resolveHighestEducationLevel($education);

            if ($ageGroupFilter !== '' && strtolower($ageGroup) !== $ageGroupFilter) {
                continue;
            }

            $sexCounts[$sex] = ($sexCounts[$sex] ?? 0) + 1;
            $ageGroupCounts[$ageGroup] = ($ageGroupCounts[$ageGroup] ?? 0) + 1;
            $civilCounts[$civilStatus] = ($civilCounts[$civilStatus] ?? 0) + 1;
            $citizenshipCounts[$citizenship] = ($citizenshipCounts[$citizenship] ?? 0) + 1;
            $educationCounts[$educationLevel] = ($educationCounts[$educationLevel] ?? 0) + 1;

            if ($age !== null) {
                $knownAgeCount++;
                $ageTotal += $age;
            }

            $rows->push([
                (int) $userId,
                (string) (optional($user)->name ?: ('Applicant #' . (int) $userId)),
                (string) (optional($user)->email ?: '-'),
                $sex,
                $age === null ? 'N/A' : (string) $age,
                $civilStatus,
                $citizenship,
                $educationLevel,
                $userApplications->count(),
            ]);
        }

        $rows = $rows->sortByDesc(fn($row) => (int) ($row[8] ?? 0))->values();
        $uniqueApplicants = $rows->count();
        $avgAge = $knownAgeCount > 0 ? round($ageTotal / $knownAgeCount, 2) : 0.0;
        $topCivil = collect($civilCounts)->sortDesc()->keys()->first() ?? 'N/A';
        $maleFemaleRatio = ($sexCounts['Male'] ?? 0) . ' : ' . ($sexCounts['Female'] ?? 0) . ' : ' . ($sexCounts['Prefer not to say'] ?? 0);

        $civilSeries = collect($civilCounts)->sortDesc();
        $citizenshipSeries = collect($citizenshipCounts)->sortDesc()->take(6);
        $nationalityPieSource = collect($citizenshipCounts)->sortDesc();
        $nationalityTop = $nationalityPieSource->take(6);
        $nationalityOthers = max($nationalityPieSource->slice(6)->sum(), 0);
        $nationalityPieLabels = $nationalityTop->keys()->values()->all();
        $nationalityPieData = $nationalityTop->values()->all();
        if ($nationalityOthers > 0) {
            $nationalityPieLabels[] = 'Others';
            $nationalityPieData[] = $nationalityOthers;
        }
        $educationSeries = collect($educationCounts)->sortDesc();

        return [
            'type' => 'applicant_demographic_breakdown',
            'title' => 'Applicant Demographic Breakdown',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Unique Applicants', 'value' => $uniqueApplicants],
                ['label' => 'Average Age', 'value' => $knownAgeCount > 0 ? number_format($avgAge, 2) : 'N/A'],
                ['label' => 'Male : Female : Prefer not to say', 'value' => $maleFemaleRatio],
                ['label' => 'With PDS Personal Info', 'value' => $withPdsCount],
                ['label' => 'Most Common Civil Status', 'value' => $topCivil],
            ],
            'charts' => [
                [
                    'title' => 'Sex Distribution',
                    'type' => 'pie',
                    'labels' => array_keys($sexCounts),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => array_values($sexCounts),
                        'backgroundColor' => $this->monoShades(3),
                    ]],
                ],
                                [
                    'title' => 'Nationality Distribution',
                    'type' => 'pie',
                    'labels' => $nationalityPieLabels,
                    'datasets' => [[
                        'label' => 'Applicants by Nationality',
                        'data' => $nationalityPieData,
                        'backgroundColor' => $this->monoShades(max(count($nationalityPieData), 1)),
                    ]],
                ],
                [
                    'title' => 'Civil Status Breakdown',
                    'type' => 'doughnut',
                    'labels' => $civilSeries->keys()->values()->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $civilSeries->values()->all(),
                        'backgroundColor' => $this->monoShades(max($civilSeries->count(), 1)),
                    ]],
                ],
                [
                    'title' => 'Age Group Distribution',
                    'type' => 'bar',
                    'labels' => array_keys($ageGroupCounts),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => array_values($ageGroupCounts),
                        'backgroundColor' => $this->monoColor(1),
                    ]],
                ],
                [
                    'title' => 'Citizenship (Top 6)',
                    'type' => 'bar',
                    'labels' => $citizenshipSeries->keys()->values()->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $citizenshipSeries->values()->all(),
                        'backgroundColor' => $this->monoColor(1),
                    ]],
                ],
                [
                    'title' => 'Highest Education Level',
                    'type' => 'bar',
                    'labels' => $educationSeries->keys()->values()->all(),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => $educationSeries->values()->all(),
                        'backgroundColor' => $this->monoColor(2),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Applicant Demographic Listing',
                'headers' => ['Applicant ID', 'Applicant', 'Email', 'Sex', 'Age', 'Civil Status', 'Citizenship', 'Highest Education', 'Application Count'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildExamScheduleReport(array $filters): array
    {
        $examDetails = ExamDetail::query()
            ->with('vacancy:vacancy_id,position_title,vacancy_type')
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->whereNotNull('date')
            ->whereBetween('date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $vacancyIds = $examDetails->pluck('vacancy_id')->filter()->unique()->values()->all();

        $appsByVacancy = Applications::query()
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->when(!empty($vacancyIds), function ($query) use ($vacancyIds) {
                $query->whereIn('vacancy_id', $vacancyIds);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->get(['vacancy_id', 'link_sent_at', 'exam_token', 'exam_started_at', 'exam_submitted_at'])
            ->groupBy('vacancy_id');

        $now = now();
        $upcomingCount = 0;
        $pastCount = 0;
        $totalInvited = 0;
        $totalAttended = 0;

        $rows = $examDetails->map(function ($exam) use ($appsByVacancy, $now, &$upcomingCount, &$pastCount, &$totalInvited, &$totalAttended) {
            $date = $exam->date ? Carbon::parse((string) $exam->date) : null;
            $startDateTime = null;
            if ($date && $exam->time) {
                $startDateTime = Carbon::parse($date->toDateString() . ' ' . (string) $exam->time);
            }

            $scheduleType = 'Unscheduled';
            if ($startDateTime) {
                if ($startDateTime->gte($now)) {
                    $scheduleType = 'Upcoming';
                    $upcomingCount++;
                } else {
                    $scheduleType = 'Past';
                    $pastCount++;
                }
            }

            $vacancyApps = $appsByVacancy->get((string) $exam->vacancy_id, collect());
            $invited = $vacancyApps->filter(fn($app) => !empty($app->link_sent_at) || !empty($app->exam_token))->count();
            $attended = $vacancyApps->filter(fn($app) => !empty($app->exam_started_at) || !empty($app->exam_submitted_at))->count();

            $totalInvited += $invited;
            $totalAttended += $attended;

            $attendanceRate = $invited > 0 ? round(($attended / $invited) * 100, 2) : 0.0;

            return [
                (string) $exam->vacancy_id,
                (string) (optional($exam->vacancy)->position_title ?? '-'),
                $date ? $date->format('M d, Y') : '-',
                $exam->time ? Carbon::parse((string) $exam->time)->format('h:i A') : '-',
                $exam->time_end ? Carbon::parse((string) $exam->time_end)->format('h:i A') : '-',
                (string) ($exam->place ?: '-'),
                $scheduleType,
                $invited,
                $attended,
                number_format($attendanceRate, 2) . '%',
            ];
        })->values();

        return [
            'type' => 'exam_schedule',
            'title' => 'Exam Schedule Report',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Upcoming Exams', 'value' => $upcomingCount],
                ['label' => 'Past Exams', 'value' => $pastCount],
                ['label' => 'Invited Applicants', 'value' => $totalInvited],
                ['label' => 'Attended Applicants', 'value' => $totalAttended],
            ],
            'charts' => [
                [
                    'title' => 'Upcoming vs Past Exams',
                    'type' => 'doughnut',
                    'labels' => ['Upcoming', 'Past'],
                    'datasets' => [[
                        'label' => 'Exam Schedules',
                        'data' => [$upcomingCount, $pastCount],
                        'backgroundColor' => $this->monoShades(2),
                    ]],
                ],
                [
                    'title' => 'Attendance Rate by Vacancy',
                    'type' => 'bar',
                    'labels' => $rows->map(fn($row) => $this->shortLabel((string) $row[1]))->all(),
                    'datasets' => [[
                        'label' => 'Attendance %',
                        'data' => $rows->map(function ($row) {
                            return (float) str_replace('%', '', (string) $row[9]);
                        })->all(),
                        'backgroundColor' => $this->monoColor(1),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Exam Schedules and Attendance',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Exam Date', 'Start Time', 'End Time', 'Venue', 'Schedule Type', 'Invited', 'Attended', 'Attendance Rate'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildExamResultSummaryReport(array $filters): array
    {
        $applications = Applications::query()
            ->with(['user:id,name,email', 'vacancy:vacancy_id,position_title'])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->where(function ($query) {
                $query->whereNotNull('result')
                    ->orWhereNotNull('exam_submitted_at')
                    ->orWhereNotNull('scores');
            })
            ->get();

        $scored = $applications->map(function ($application) {
            $pct = $this->extractScorePercentage((string) ($application->result ?? ''));
            $outcome = $this->resolveOutcomeForApplication($application);

            if ($pct !== null && $outcome === null) {
                $outcome = $pct >= self::PASSING_PERCENTAGE ? 'passed' : 'failed';
            }

            return [
                'application' => $application,
                'score_pct' => $pct,
                'outcome' => $outcome,
            ];
        })->filter(fn($row) => $row['score_pct'] !== null)->values();

        $passedCount = $scored->filter(fn($row) => $row['outcome'] === 'passed')->count();
        $failedCount = $scored->filter(fn($row) => $row['outcome'] === 'failed')->count();
        $scoredCount = $scored->count();
        $passRate = $scoredCount > 0 ? round(($passedCount / $scoredCount) * 100, 2) : 0.0;
        $averageScore = $scoredCount > 0 ? round((float) $scored->avg('score_pct'), 2) : 0.0;

        $topPerformers = $scored
            ->sortByDesc('score_pct')
            ->take(10)
            ->values();

        $scoreBuckets = [
            '0-49' => 0,
            '50-59' => 0,
            '60-69' => 0,
            '70-79' => 0,
            '80-89' => 0,
            '90-100' => 0,
        ];

        foreach ($scored as $row) {
            $score = (float) $row['score_pct'];
            if ($score < 50) {
                $scoreBuckets['0-49']++;
            } elseif ($score < 60) {
                $scoreBuckets['50-59']++;
            } elseif ($score < 70) {
                $scoreBuckets['60-69']++;
            } elseif ($score < 80) {
                $scoreBuckets['70-79']++;
            } elseif ($score < 90) {
                $scoreBuckets['80-89']++;
            } else {
                $scoreBuckets['90-100']++;
            }
        }

        return [
            'type' => 'exam_result_summary',
            'title' => 'Exam Result Summary',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Pass Rate %', 'value' => number_format($passRate, 2) . '%'],
                ['label' => 'Average Score', 'value' => number_format($averageScore, 2) . '%'],
                ['label' => 'Top Performers', 'value' => $topPerformers->count()],
                ['label' => 'Failed Count', 'value' => $failedCount],
            ],
            'charts' => [
                [
                    'title' => 'Pass vs Failed',
                    'type' => 'doughnut',
                    'labels' => ['Passed', 'Failed'],
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => [$passedCount, $failedCount],
                        'backgroundColor' => $this->monoShades(2),
                    ]],
                ],
                [
                    'title' => 'Score Distribution',
                    'type' => 'bar',
                    'labels' => array_keys($scoreBuckets),
                    'datasets' => [[
                        'label' => 'Applicants',
                        'data' => array_values($scoreBuckets),
                        'backgroundColor' => $this->monoColor(0),
                    ]],
                ],
            ],
            'table' => [
                'title' => 'Top Performers',
                'headers' => ['Applicant', 'Email', 'Vacancy', 'Score %', 'Exam Result', 'Outcome', 'Submitted At'],
                'rows' => $topPerformers->map(function ($row) {
                    $application = $row['application'];
                    return [
                        (string) (optional($application->user)->name ?? ('Applicant #' . (int) $application->user_id)),
                        (string) (optional($application->user)->email ?? '-'),
                        (string) (optional($application->vacancy)->position_title ?? $application->vacancy_id),
                        number_format((float) $row['score_pct'], 2) . '%',
                        (string) ($application->result ?: '-'),
                        ucfirst((string) ($row['outcome'] ?? 'n/a')),
                        $application->exam_submitted_at ? Carbon::parse((string) $application->exam_submitted_at)->format('M d, Y h:i A') : '-',
                    ];
                })->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function buildExamVacancyBasedResultReport(array $filters): array
    {
        $vacancies = $this->vacanciesForScope($filters);
        $applications = $this->applicationsForScope($filters);
        $appsByVacancy = $applications->groupBy('vacancy_id');

        $rows = $vacancies->map(function ($vacancy) use ($appsByVacancy) {
            $vacancyApps = $appsByVacancy->get((string) $vacancy->vacancy_id, collect());

            $invited = $vacancyApps->filter(fn($app) => !empty($app->link_sent_at) || !empty($app->exam_token))->count();
            $attended = $vacancyApps->filter(fn($app) => !empty($app->exam_started_at) || !empty($app->exam_submitted_at))->count();
            $passed = $vacancyApps->filter(fn($app) => $this->resolveOutcomeForApplication($app) === 'passed')->count();
            $failed = $vacancyApps->filter(fn($app) => $this->resolveOutcomeForApplication($app) === 'failed')->count();

            return [
                (string) $vacancy->vacancy_id,
                (string) $vacancy->position_title,
                $invited,
                $attended,
                $passed,
                $failed,
            ];
        })->filter(fn($row) => ((int) $row[2] + (int) $row[3] + (int) $row[4] + (int) $row[5]) > 0)
            ->values();

        return [
            'type' => 'exam_vacancy_based_result',
            'title' => 'Vacancy-Based Exam Result',
            'filters' => $this->publicFilters($filters),
            'summary_cards' => [
                ['label' => 'Vacancies with Exam Data', 'value' => $rows->count()],
                ['label' => 'Applicants Invited', 'value' => $rows->sum(fn($row) => (int) $row[2])],
                ['label' => 'Applicants Attended', 'value' => $rows->sum(fn($row) => (int) $row[3])],
                ['label' => 'Passed / Failed', 'value' => $rows->sum(fn($row) => (int) $row[4]) . ' / ' . $rows->sum(fn($row) => (int) $row[5])],
            ],
            'charts' => [
                [
                    'title' => 'Invited vs Attended vs Passed vs Failed',
                    'type' => 'bar',
                    'labels' => $rows->map(fn($row) => $this->shortLabel((string) $row[1]))->all(),
                    'datasets' => [
                        [
                            'label' => 'Invited',
                            'data' => $rows->map(fn($row) => (int) $row[2])->all(),
                            'backgroundColor' => $this->monoColor(1),
                        ],
                        [
                            'label' => 'Attended',
                            'data' => $rows->map(fn($row) => (int) $row[3])->all(),
                            'backgroundColor' => $this->monoColor(2),
                        ],
                        [
                            'label' => 'Passed',
                            'data' => $rows->map(fn($row) => (int) $row[4])->all(),
                            'backgroundColor' => $this->monoColor(3),
                        ],
                        [
                            'label' => 'Failed',
                            'data' => $rows->map(fn($row) => (int) $row[5])->all(),
                            'backgroundColor' => $this->monoColor(4),
                        ],
                    ],
                ],
            ],
            'table' => [
                'title' => 'Exam Result by Vacancy',
                'headers' => ['Vacancy ID', 'Vacancy Title', 'Applicants Invited', 'Applicants Attended', 'Passed', 'Failed'],
                'rows' => $rows->all(),
            ],
            'meta' => ['generated_at' => now()->toDateTimeString()],
        ];
    }

    private function vacanciesForScope(array $filters): Collection
    {
        return JobVacancy::query()
            ->select(['vacancy_id', 'position_title', 'vacancy_type', 'status', 'created_at', 'closing_date'])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->orderBy('position_title')
            ->orderBy('vacancy_id')
            ->get();
    }

    private function applicationsForScope(array $filters): Collection
    {
        return Applications::query()
            ->select([
                'id',
                'user_id',
                'vacancy_id',
                'status',
                'result',
                'qs_result',
                'link_sent_at',
                'exam_token',
                'exam_started_at',
                'exam_submitted_at',
                'created_at',
            ])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($query) use ($filters) {
                $query->where('vacancy_id', $filters['vacancy_id']);
            })
            ->get();
    }

    private function applicantMasterListQuery(array $filters)
    {
        $query = Applications::query()
            ->with(['user:id,name,email', 'vacancy:vacancy_id,position_title,vacancy_type,status'])
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->when($filters['vacancy_id'] !== '', function ($q) use ($filters) {
                $q->where('vacancy_id', $filters['vacancy_id']);
            })
            ->when($filters['qualification'] !== '', function ($q) use ($filters) {
                $q->where('qs_result', $filters['qualification']);
            })
            ->orderByDesc('created_at');

        $status = strtolower($filters['status']);
        if ($status !== '' && !in_array($status, ['reviewed', 'ongoing', 'passed', 'failed', 'withdrawn'], true)) {
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        return $query;
    }

    private function isVacancyFilled(Collection $applications): bool
    {
        return $applications->contains(function ($application) {
            return $this->resolveOutcomeForApplication($application) === 'passed';
        });
    }

    private function extractScorePercentage(?string $result): ?float
    {
        $result = trim((string) $result);
        if ($result === '') {
            return null;
        }

        if (!preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $result, $matches)) {
            return null;
        }

        $numerator = (float) $matches[1];
        $denominator = (float) $matches[2];
        if ($denominator <= 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function resolveOutcomeForApplication($application): ?string
    {
        $status = strtolower(trim((string) data_get($application, 'status', '')));
        if ($status === 'withdrawn') {
            return 'withdrawn';
        }

        $qsResult = strtolower(trim((string) data_get($application, 'qs_result', '')));
        if ($qsResult === 'qualified') {
            return 'passed';
        }
        if ($qsResult === 'not qualified') {
            return 'failed';
        }

        $score = $this->extractScorePercentage((string) data_get($application, 'result', ''));
        if ($score === null) {
            return null;
        }

        return $score >= self::PASSING_PERCENTAGE ? 'passed' : 'failed';
    }

    private function resolveAnalyticsBucket($application): string
    {
        $outcome = $this->resolveOutcomeForApplication($application);
        if ($outcome === 'withdrawn') {
            return 'withdrawn';
        }
        if ($outcome === 'passed') {
            return 'passed';
        }
        if ($outcome === 'failed') {
            return 'failed';
        }

        $status = strtolower(trim((string) data_get($application, 'status', '')));
        if (in_array($status, ['pending', 'incomplete', 'updated', 'submitted', 'in-progress', 'ready', 'compliance'], true)) {
            return 'ongoing';
        }

        return 'reviewed';
    }

    private function countAnalyticsBuckets(Collection $applications): array
    {
        $counts = [
            'reviewed' => 0,
            'ongoing' => 0,
            'passed' => 0,
            'failed' => 0,
            'withdrawn' => 0,
        ];

        foreach ($applications as $application) {
            $bucket = $this->resolveAnalyticsBucket($application);
            if (array_key_exists($bucket, $counts)) {
                $counts[$bucket]++;
            }
        }

        return $counts;
    }

    private function normalizeDemographicLabel(string $value, string $fallback = 'Unspecified'): string
    {
        $cleaned = trim($value);
        if ($cleaned === '' || strtoupper($cleaned) === 'NOINPUT' || strtoupper($cleaned) === 'N/A') {
            return $fallback;
        }

        return Str::title(strtolower($cleaned));
    }

    private function normalizeSex(string $value): string
    {
        $cleaned = strtolower(trim($value));
        if (in_array($cleaned, ['male', 'm'], true)) {
            return 'Male';
        }
        if (in_array($cleaned, ['female', 'f'], true)) {
            return 'Female';
        }
        if (in_array($cleaned, ['prefer not to say', 'prefer_not_to_say', 'prefer-not-to-say', 'noinput', 'n/a', 'na', ''], true)) {
            return 'Prefer not to say';
        }

        return 'Prefer not to say';
    }

    private function computeApplicantAge($dateOfBirth): ?int
    {
        if (empty($dateOfBirth)) {
            return null;
        }

        try {
            $dob = Carbon::parse((string) $dateOfBirth);
            $age = $dob->age;
            if ($age < 15 || $age > 100) {
                return null;
            }

            return $age;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveAgeGroup(?int $age): string
    {
        if ($age === null) {
            return 'Unknown';
        }
        if ($age <= 24) {
            return '18-24';
        }
        if ($age <= 34) {
            return '25-34';
        }
        if ($age <= 44) {
            return '35-44';
        }
        if ($age <= 54) {
            return '45-54';
        }

        return '55+';
    }

    private function resolveHighestEducationLevel($education): string
    {
        if (!$education) {
            return 'Unspecified';
        }

        $grad = is_array($education->grad ?? null) ? $education->grad : [];
        $college = is_array($education->college ?? null) ? $education->college : [];
        $vocational = is_array($education->vocational ?? null) ? $education->vocational : [];

        if ($this->hasAnyEducationData($grad)) {
            return 'Graduate';
        }
        if ($this->hasAnyEducationData($college)) {
            return 'College';
        }
        if ($this->hasAnyEducationData($vocational)) {
            return 'Vocational';
        }
        if (!empty(trim((string) ($education->shs_school ?? '')))) {
            return 'Senior High';
        }
        if (!empty(trim((string) ($education->jhs_school ?? '')))) {
            return 'Junior High';
        }
        if (!empty(trim((string) ($education->elem_school ?? '')))) {
            return 'Elementary';
        }

        return 'Unspecified';
    }

    private function hasAnyEducationData(array $records): bool
    {
        foreach ($records as $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $value) {
                $text = trim((string) $value);
                if ($text !== '' && strtoupper($text) !== 'NOINPUT' && strtoupper($text) !== 'N/A') {
                    return true;
                }
            }
        }

        return false;
    }

    private function publicFilters(array $filters): array
    {
        return [
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'vacancy_id' => $filters['vacancy_id'],
            'status' => $filters['status'],
            'qualification' => $filters['qualification'],
            'age_group' => $filters['age_group'] ?? '',
            'passing_percentage' => self::PASSING_PERCENTAGE,
        ];
    }

    private function normalizeVacancyType(string $type): string
    {
        return strtoupper(trim($type)) === 'COS' ? 'COS' : 'Plantilla';
    }

    private function shortLabel(string $text, int $limit = 24): string
    {
        return Str::limit(trim($text), $limit, '...');
    }

    private function monoShades(int $count): array
    {
        $palette = [
            '#0A1D4D',
            '#0D2B70',
            '#16408F',
            '#2457AA',
            '#3A6FBE',
            '#5889CD',
            '#7BA7DD',
            '#A1C3EA',
            '#C3DCF4',
            '#E2EEFB',
        ];

        $count = max($count, 1);
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
    }

    private function monoColor(int $index = 0): string
    {
        $spacedStops = [1, 3, 5, 7, 9];
        $palette = $this->monoShades(10);
        $target = $spacedStops[$index] ?? $spacedStops[array_key_last($spacedStops)];
        return $palette[$target];
    }

    private function exportCsv(string $fileName, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportExcel(string $fileName, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Report');

            $col = 1;
            foreach ($headers as $header) {
                $sheet->setCellValue($this->columnIndexToLetter($col) . '1', (string) $header);
                $col++;
            }

            $rowIndex = 2;
            foreach ($rows as $row) {
                $colIndex = 1;
                foreach ($row as $value) {
                    $sheet->setCellValue(
                        $this->columnIndexToLetter($colIndex) . $rowIndex,
                        is_scalar($value) ? (string) $value : json_encode($value)
                    );
                    $colIndex++;
                }
                $rowIndex++;
            }

            $highestColumn = $sheet->getHighestColumn();
            foreach (range('A', $highestColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportApplicantDemographicExcel(string $fileName, array $payload)
    {
        $headers = $payload['table']['headers'] ?? [];
        $rows = $payload['table']['rows'] ?? [];
        $charts = $payload['charts'] ?? [];
        $title = (string) ($payload['title'] ?? 'Applicant Demographic Breakdown');

        return response()->streamDownload(function () use ($headers, $rows, $charts, $title) {
            $spreadsheet = new Spreadsheet();

            $dataSheet = $spreadsheet->getActiveSheet();
            $dataSheet->setTitle('Data');
            $dataSheet->setCellValue('A1', $title);
            $dataSheet->setCellValue('A2', 'Generated At: ' . now()->format('F d, Y h:i A'));

            $headerRow = 4;
            foreach ($headers as $index => $header) {
                $dataSheet->setCellValue($this->columnIndexToLetter($index + 1) . $headerRow, (string) $header);
            }

            $rowIndex = $headerRow + 1;
            foreach ($rows as $row) {
                $colIndex = 1;
                foreach ((array) $row as $value) {
                    $dataSheet->setCellValue(
                        $this->columnIndexToLetter($colIndex) . $rowIndex,
                        is_scalar($value) ? (string) $value : json_encode($value)
                    );
                    $colIndex++;
                }
                $rowIndex++;
            }

            $highestColumn = $dataSheet->getHighestColumn();
            foreach (range('A', $highestColumn) as $column) {
                $dataSheet->getColumnDimension($column)->setAutoSize(true);
            }

            $chartsSheet = new Worksheet($spreadsheet, 'Charts');
            $spreadsheet->addSheet($chartsSheet);
            $chartsSheet->setCellValue('A1', $title . ' - Charts');
            $chartsSheet->setCellValue('A2', 'Generated At: ' . now()->format('F d, Y h:i A'));

            $startRow = 4;
            foreach ($charts as $chartIndex => $chartConfig) {
                $labels = array_values((array) ($chartConfig['labels'] ?? []));
                $datasets = array_values((array) ($chartConfig['datasets'] ?? []));
                if (empty($labels) || empty($datasets)) {
                    continue;
                }

                $chartTitle = (string) ($chartConfig['title'] ?? ('Chart ' . ($chartIndex + 1)));
                $chartsSheet->setCellValue('A' . $startRow, $chartTitle);
                $chartsSheet->setCellValue('A' . ($startRow + 1), 'Series');

                foreach ($labels as $labelIndex => $labelValue) {
                    $chartsSheet->setCellValue($this->columnIndexToLetter($labelIndex + 2) . ($startRow + 1), (string) $labelValue);
                }

                foreach ($datasets as $datasetIndex => $dataset) {
                    $row = $startRow + 2 + $datasetIndex;
                    $chartsSheet->setCellValue('A' . $row, (string) ($dataset['label'] ?? ('Dataset ' . ($datasetIndex + 1))));
                    $dataValues = array_values((array) ($dataset['data'] ?? []));
                    foreach ($dataValues as $labelIndex => $value) {
                        $chartsSheet->setCellValue($this->columnIndexToLetter($labelIndex + 2) . $row, is_numeric($value) ? (float) $value : 0);
                    }
                }

                $maxColumnIndex = max(count($labels) + 1, 2);
                $maxDataColumn = $this->columnIndexToLetter($maxColumnIndex);
                $seriesEndRow = $startRow + 1 + count($datasets);

                $dataSeriesLabels = [];
                $dataSeriesValues = [];
                foreach ($datasets as $datasetIndex => $dataset) {
                    $row = $startRow + 2 + $datasetIndex;
                    $dataSeriesLabels[] = new DataSeriesValues('String', "'Charts'!\$A\${$row}", null, 1);
                    $dataSeriesValues[] = new DataSeriesValues('Number', "'Charts'!\$B\${$row}:\${$maxDataColumn}\${$row}", null, count($labels));
                }

                $xAxisTickValues = [
                    new DataSeriesValues('String', "'Charts'!\$B\$" . ($startRow + 1) . ":\${$maxDataColumn}\$" . ($startRow + 1), null, count($labels)),
                ];

                $chartType = strtolower((string) ($chartConfig['type'] ?? 'bar'));
                $plotType = match ($chartType) {
                    'line' => DataSeries::TYPE_LINECHART,
                    'pie' => DataSeries::TYPE_PIECHART,
                    'doughnut' => DataSeries::TYPE_DONUTCHART,
                    default => DataSeries::TYPE_BARCHART,
                };

                $grouping = $plotType === DataSeries::TYPE_LINECHART
                    ? DataSeries::GROUPING_STANDARD
                    : DataSeries::GROUPING_CLUSTERED;

                $series = new DataSeries(
                    $plotType,
                    $grouping,
                    range(0, max(count($dataSeriesValues) - 1, 0)),
                    $dataSeriesLabels,
                    $xAxisTickValues,
                    $dataSeriesValues
                );

                if ($plotType === DataSeries::TYPE_BARCHART) {
                    $series->setPlotDirection(DataSeries::DIRECTION_COL);
                }

                $plotArea = new PlotArea(null, [$series]);
                $legend = new Legend(Legend::POSITION_RIGHT, null, false);
                $excelChart = new Chart(
                    'chart_' . $chartIndex,
                    new Title($chartTitle),
                    $legend,
                    $plotArea,
                    true,
                    0,
                    null,
                    null
                );

                $chartTop = $startRow;
                $chartBottom = $startRow + 14;
                $excelChart->setTopLeftPosition('F' . $chartTop);
                $excelChart->setBottomRightPosition('O' . $chartBottom);
                $chartsSheet->addChart($excelChart);

                $startRow = $seriesEndRow + 16;
            }

            foreach (range('A', 'E') as $column) {
                $chartsSheet->getColumnDimension($column)->setAutoSize(true);
            }

            $spreadsheet->setActiveSheetIndex(0);
            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function columnIndexToLetter(int $index): string
    {
        $index = max(1, $index);
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }

    private function exportApplicantMasterListPdf(string $fileName, array $headers, array $rows, string $title)
    {
        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $this->toPdfText($title), 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, $this->toPdfText('Generated at: ' . now()->format('F d, Y h:i A')), 0, 1);
        $pdf->Ln(2);

        $contentWidth = 277;
        $columnCount = max(count($headers), 1);
        $baseWidth = (int) floor($contentWidth / $columnCount);
        $widths = array_fill(0, $columnCount, $baseWidth);
        $widths[$columnCount - 1] += $contentWidth - array_sum($widths);

        $pdf->SetFont('Arial', 'B', 8);
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 7, $this->toPdfText(Str::limit((string) $header, 24, '.')), 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        foreach ($rows as $row) {
            if ($pdf->GetY() > 190) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 8);
                foreach ($headers as $index => $header) {
                    $pdf->Cell($widths[$index], 7, $this->toPdfText(Str::limit((string) $header, 24, '.')), 1, 0, 'C');
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 7);
            }

            foreach ($headers as $index => $header) {
                $value = $row[$index] ?? '';
                $text = $this->toPdfText(Str::limit((string) $value, 36, '...'));
                $pdf->Cell($widths[$index], 6, $text, 1, 0, 'L');
            }
            $pdf->Ln();
        }

        $content = $pdf->Output('S');
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function toPdfText(string $text): string
    {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        return $converted === false ? utf8_decode($text) : $converted;
    }
}

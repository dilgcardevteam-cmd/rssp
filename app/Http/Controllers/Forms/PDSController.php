<?php

namespace App\Http\Controllers\Forms;

use App\Support\PreviewUrl;
use App\Enums\ApplicationStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models;
use App\Models\MiscInfos;

// Models
use App\Models\Applications;
use Illuminate\Http\Request;
use App\Models\VoluntaryWork;
use App\Models\WorkExperience;
use App\Models\OtherInformation;
use App\Models\UploadedDocument;
use App\Models\CoursePreset;
use App\Models\EligibilityPreset;
use App\Models\ProgramSuggestion;
use App\Models\Notification;
use App\Models\Admin;
use App\Models\DocumentGalleryItem;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use App\Http\Controllers\JobVacancyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\LearningAndDevelopment;
use Illuminate\Support\Facades\DB;
use App\Models\CivilServiceEligibility;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Services\ApplicationStatusTransitionService;
use App\Services\DocumentGallerySyncService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PDSController extends Controller
{
    private const SEPARATOR = '/|/';
    private const PDF_MIME_TYPES = [
        'application/pdf',
        'application/x-pdf',
    ];
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];
    private const SMALLINT_MAX = 32767;
    private const MAX_UPLOAD_BYTES = 10485760; // 10MB
    private const DOCUMENT_TYPE_ALIASES = [
        'cert_eligibility' => ['cert_elegibility'],
        'cert_employment' => ['certificate_employment'],
        'grade_masteraldoctorate' => ['certificate_grades'],
        'tor_masteraldoctorate' => ['certified_tor'],
        'ipcr' => ['performance_rating'],
        'non_academic' => ['non_academic_awards'],
        'cert_training' => ['certificates_participation'],
        'designation_order' => ['designation_orders'],
        'transcript_records' => ['transcript'],
        'photocopy_diploma' => ['diploma'],
    ];

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $this->syncPdsSessionOwner($request);
            return $next($request);
        });
    }

    private function syncPdsSessionOwner(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $ownerKey = implode('|', [
            'uid:' . (string) $user->id,
            'email:' . (string) ($user->email ?? ''),
            'created:' . (string) optional($user->created_at)->timestamp,
        ]);

        $sessionOwner = (string) $request->session()->get('pds_form_owner', '');
        if ($sessionOwner !== $ownerKey) {
            $request->session()->forget([
                'form',
                'data_learning',
                'data_voluntary',
                'data_otherInfo',
                'vacancy_doc_uploads',
            ]);
        }

        $request->session()->put('pds_form_owner', $ownerKey);
    }

    private function normalizeDateForForm(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        $parsed = $this->parseFlexibleDate($value);
        if ($parsed) {
            return $parsed->format('d-m-Y');
        }

        try {
            if (preg_match('/^\d{4}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m', $value)->format('01-m-Y');
            }
        } catch (\Throwable $e) {
            return $value;
        }

        return $value;
    }

    private function normalizeEducationYearForForm(?string $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^\d{4}$/', $raw)) {
            return $raw;
        }

        try {
            if (preg_match('/^\d{2}-\d{4}$/', $raw)) {
                return Carbon::createFromFormat('m-Y', $raw)->format('Y');
            }

            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw)) {
                return Carbon::createFromFormat('d-m-Y', $raw)->format('Y');
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                return Carbon::createFromFormat('Y-m-d', $raw)->format('Y');
            }
        } catch (\Throwable $e) {
            return $raw;
        }

        return $raw;
    }

    private function normalizeDateForDatabase(?string $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        $parsed = $this->parseFlexibleDate($raw);
        if ($parsed) {
            return $parsed->format('Y-m-d');
        }

        return $raw;
    }

    private function normalizeStrictDateForDatabase($value): ?string
    {
        $raw = is_scalar($value) ? trim((string) $value) : '';
        if ($raw === '') {
            return null;
        }

        $parsed = $this->parseFlexibleDate($raw);
        return $parsed ? $parsed->format('Y-m-d') : null;
    }

    private function parseFlexibleDate(?string $value): ?Carbon
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        $formats = [
            'Y',
            'm-Y',
            'd-m-Y',
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'm-d-Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date && $date->format($format) === $raw) {
                    return $date;
                }
            } catch (\Throwable $e) {
                // Try the next format.
            }
        }

        return null;
    }

    private function normalizeEducationMonthYearForDatabase($value): ?string
    {
        $raw = is_scalar($value) ? trim((string) $value) : '';
        if ($raw === '') {
            return null;
        }

        $parsed = $this->parseFlexibleDate($raw);
        return $parsed ? $parsed->format('m-Y') : null;
    }

    private function normalizeWorkExperienceEndDateForDatabase(?string $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (strtolower($raw) === 'present') {
            return Carbon::today()->toDateString();
        }

        return $this->normalizeDateForDatabase($raw);
    }

    private function parseEducationDateForValidation($value): ?Carbon
    {
        $value = is_string($value) ? trim($value) : null;
        if ($value === null || $value === '') {
            return null;
        }

        return $this->parseFlexibleDate($value);
    }

    private function normalizeEducationEntriesForForm($entries)
    {
        if (!is_array($entries)) {
            return $entries;
        }

        foreach ($entries as $index => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            foreach (['from', 'to'] as $dateKey) {
                $entries[$index][$dateKey] = $this->normalizeEducationYearForForm($entry[$dateKey] ?? null);
            }
        }

        return $entries;
    }

    private function normalizeProgramLookupValue($value): string
    {
        $normalized = trim((string) ($value ?? ''));
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        return strtolower(trim($normalized));
    }

    private function inferGraduateProgramLevel(string $programName): string
    {
        $normalized = strtolower(trim($programName));
        if ($normalized === '') {
            return 'MASTERAL';
        }

        foreach ([
            'doctorate',
            'doctoral',
            'doctor of philosophy',
            'doctor',
            'phd',
            'ph.d',
            'dba',
            'edd',
            'sjd',
            'juridical science',
        ] as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return 'DOCTORATE';
            }
        }

        return 'MASTERAL';
    }

    private function programLevelLabel(string $programLevel): string
    {
        return match (strtoupper(trim($programLevel))) {
            'COLLEGE' => 'College',
            'MASTERAL' => 'Masteral',
            'DOCTORATE' => 'Doctorate',
            default => ucfirst(strtolower(trim($programLevel))),
        };
    }

    private function resolveApplicantDisplayName(): string
    {
        $user = Auth::user();
        if (!$user) {
            return 'An applicant';
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));
        $combined = trim($first . ' ' . $last);
        if ($combined !== '') {
            return $combined;
        }

        return trim((string) ($user->email ?? '')) ?: 'An applicant';
    }

    private function notifyAdminsForProgramSuggestion(ProgramSuggestion $suggestion): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $suggestedName = trim((string) ($suggestion->suggested_name ?? ''));
        if ($suggestedName === '') {
            return;
        }

        $levelCode = strtoupper(trim((string) ($suggestion->program_level ?? 'COLLEGE')));
        $levelLabel = $this->programLevelLabel($levelCode);
        $applicantName = $this->resolveApplicantDisplayName();
        $reviewUrl = route('admin.courses.index', [], false);

        $payload = [
            'title' => 'Program suggestion pending review',
            'message' => $applicantName . ' submitted "' . $suggestedName . '" under ' . $levelLabel . ' for approval.',
            'action_url' => $reviewUrl,
            'link' => $reviewUrl,
            'section' => 'Academic Programs',
            'category' => 'program_suggestion',
            'program_suggestion_id' => $suggestion->id,
            'program_level' => $levelCode,
            'suggested_name' => $suggestedName,
            'suggested_by_user_id' => $suggestion->suggested_by_user_id,
        ];

        foreach (Admin::query()->select('id')->cursor() as $admin) {
            Notification::query()->create([
                'notifiable_type' => Admin::class,
                'notifiable_id' => $admin->id,
                'type' => 'warning',
                'data' => $payload,
                'read_at' => null,
            ]);
        }
    }

    private function queueCollegeProgramSuggestions($collegeRows): void
    {
        if (!Auth::check() || !is_array($collegeRows)) {
            return;
        }

        if (!Schema::hasTable('course_presets') || !Schema::hasTable('program_suggestions')) {
            return;
        }

        $candidatePrograms = [];
        foreach ($collegeRows as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $programName = trim((string) ($entry['basic'] ?? ''));
            if ($this->valueIsEmptyOrNa($programName)) {
                continue;
            }

            $programName = preg_replace('/\s+/u', ' ', $programName) ?? $programName;
            $programName = trim($programName);
            if ($programName === '') {
                continue;
            }

            $normalizedName = $this->normalizeProgramLookupValue($programName);
            if ($normalizedName === '') {
                continue;
            }

            $candidatePrograms[$normalizedName] = $programName;
        }

        if (empty($candidatePrograms)) {
            return;
        }

        $approvedQuery = CoursePreset::query()->select('course_name');
        if (Schema::hasColumn('course_presets', 'program_level')) {
            $approvedQuery->where('program_level', 'COLLEGE');
        }

        $approvedMap = [];
        foreach ($approvedQuery->pluck('course_name') as $approvedName) {
            $lookup = $this->normalizeProgramLookupValue($approvedName);
            if ($lookup !== '') {
                $approvedMap[$lookup] = true;
            }
        }

        $existingSuggestions = ProgramSuggestion::query()
            ->where('program_level', 'COLLEGE')
            ->whereIn('normalized_name', array_keys($candidatePrograms))
            ->get()
            ->keyBy('normalized_name');

        foreach ($candidatePrograms as $normalizedName => $programName) {
            if (isset($approvedMap[$normalizedName])) {
                continue;
            }

            $existing = $existingSuggestions->get($normalizedName);
            if ($existing) {
                if ($existing->status === 'pending') {
                    $existing->update([
                        'suggested_name' => $programName,
                        'suggested_by_user_id' => Auth::id(),
                        'source' => 'pds_c1',
                    ]);
                } elseif ($existing->status === 'declined') {
                    $existing->update([
                        'status' => 'pending',
                        'suggested_name' => $programName,
                        'suggested_by_user_id' => Auth::id(),
                        'source' => 'pds_c1',
                        'reviewed_by_admin_id' => null,
                        'reviewed_at' => null,
                        'course_preset_id' => null,
                    ]);
                    $this->notifyAdminsForProgramSuggestion($existing);
                }

                continue;
            }

            $createdSuggestion = ProgramSuggestion::query()->create([
                'suggested_by_user_id' => Auth::id(),
                'program_level' => 'COLLEGE',
                'suggested_name' => $programName,
                'normalized_name' => $normalizedName,
                'status' => 'pending',
                'source' => 'pds_c1',
            ]);
            $this->notifyAdminsForProgramSuggestion($createdSuggestion);
        }
    }

    private function queueGraduateProgramSuggestions($gradRows): void
    {
        if (!Auth::check() || !is_array($gradRows)) {
            return;
        }

        if (!Schema::hasTable('course_presets') || !Schema::hasTable('program_suggestions')) {
            return;
        }

        $candidatePrograms = [];
        foreach ($gradRows as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $programName = trim((string) ($entry['basic'] ?? ''));
            if ($this->valueIsEmptyOrNa($programName)) {
                continue;
            }

            $programName = preg_replace('/\s+/u', ' ', $programName) ?? $programName;
            $programName = trim($programName);
            if ($programName === '') {
                continue;
            }

            $programLevel = $this->inferGraduateProgramLevel($programName);
            $normalizedName = $this->normalizeProgramLookupValue($programName);
            if ($normalizedName === '') {
                continue;
            }

            $key = $programLevel . '|' . $normalizedName;
            $candidatePrograms[$key] = [
                'program_level' => $programLevel,
                'normalized_name' => $normalizedName,
                'suggested_name' => $programName,
            ];
        }

        if (empty($candidatePrograms)) {
            return;
        }

        $allowedLevels = ['MASTERAL', 'DOCTORATE'];
        $approvedQuery = CoursePreset::query()->select('course_name');
        if (Schema::hasColumn('course_presets', 'program_level')) {
            $approvedQuery->addSelect('program_level')->whereIn('program_level', $allowedLevels);
        }

        $approvedMap = [];
        foreach ($approvedQuery->get() as $approvedRow) {
            $approvedName = (string) ($approvedRow->course_name ?? '');
            $normalizedName = $this->normalizeProgramLookupValue($approvedName);
            if ($normalizedName === '') {
                continue;
            }

            $programLevel = Schema::hasColumn('course_presets', 'program_level')
                ? strtoupper(trim((string) ($approvedRow->program_level ?? 'MASTERAL')))
                : $this->inferGraduateProgramLevel($approvedName);

            if (!in_array($programLevel, $allowedLevels, true)) {
                continue;
            }

            $approvedMap[$programLevel . '|' . $normalizedName] = true;
        }

        $normalizedNames = array_values(array_unique(array_map(
            fn($item) => (string) ($item['normalized_name'] ?? ''),
            array_values($candidatePrograms)
        )));

        $existingRecords = ProgramSuggestion::query()
            ->whereIn('program_level', $allowedLevels)
            ->whereIn('normalized_name', $normalizedNames)
            ->orderByDesc('updated_at')
            ->get();

        $existingByKey = [];
        foreach ($existingRecords as $record) {
            $key = strtoupper(trim((string) ($record->program_level ?? ''))) . '|' . strtolower(trim((string) ($record->normalized_name ?? '')));
            if (!isset($existingByKey[$key])) {
                $existingByKey[$key] = $record;
                continue;
            }

            $current = $existingByKey[$key];
            if ($current->status !== 'pending' && $record->status === 'pending') {
                $existingByKey[$key] = $record;
            }
        }

        foreach ($candidatePrograms as $key => $item) {
            if (isset($approvedMap[$key])) {
                continue;
            }

            $existing = $existingByKey[$key] ?? null;
            if ($existing) {
                if ($existing->status === 'pending') {
                    $existing->update([
                        'suggested_name' => (string) ($item['suggested_name'] ?? ''),
                        'suggested_by_user_id' => Auth::id(),
                        'source' => 'pds_c1',
                    ]);
                    continue;
                }

                if ($existing->status === 'declined') {
                    $existing->update([
                        'status' => 'pending',
                        'suggested_name' => (string) ($item['suggested_name'] ?? ''),
                        'suggested_by_user_id' => Auth::id(),
                        'source' => 'pds_c1',
                        'reviewed_by_admin_id' => null,
                        'reviewed_at' => null,
                        'course_preset_id' => null,
                    ]);
                    $this->notifyAdminsForProgramSuggestion($existing);
                    continue;
                }
            }

            $createdSuggestion = ProgramSuggestion::query()->create([
                'suggested_by_user_id' => Auth::id(),
                'program_level' => (string) ($item['program_level'] ?? 'MASTERAL'),
                'suggested_name' => (string) ($item['suggested_name'] ?? ''),
                'normalized_name' => (string) ($item['normalized_name'] ?? ''),
                'status' => 'pending',
                'source' => 'pds_c1',
            ]);
            $this->notifyAdminsForProgramSuggestion($createdSuggestion);
        }
    }

    private function addEducationDateRangeValidationError(
        \Illuminate\Validation\Validator $validator,
        $fromValue,
        $toValue,
        string $fromField,
        string $toField,
        bool $allowSameYear = false
    ): void {
        $fromDate = $this->parseEducationDateForValidation($fromValue);
        $toDate = $this->parseEducationDateForValidation($toValue);

        if (!$fromDate || !$toDate) {
            return;
        }

        if ($allowSameYear) {
            if ($fromDate->gt($toDate)) {
                $validator->errors()->add($fromField, 'The "From" year must be earlier than or the same as the "To" year.');
                $validator->errors()->add($toField, 'The "To" year must be later than or the same as the "From" year.');
            }

            return;
        }

        if (!$fromDate->gte($toDate)) {
            return;
        }

        $validator->errors()->add($fromField, 'The "From" year must be earlier than the "To" year.');
        $validator->errors()->add($toField, 'The "To" year must be later than the "From" year.');
    }

    private function validateEducationDateRanges(\Illuminate\Validation\Validator $validator, array $payload): void
    {
        $this->addEducationDateRangeValidationError(
            $validator,
            $payload['elem_from'] ?? null,
            $payload['elem_to'] ?? null,
            'elem_from',
            'elem_to'
        );

        $this->addEducationDateRangeValidationError(
            $validator,
            $payload['jhs_from'] ?? null,
            $payload['jhs_to'] ?? null,
            'jhs_from',
            'jhs_to'
        );

        $elemToDate = $this->parseEducationDateForValidation($payload['elem_to'] ?? null);
        $jhsFromDate = $this->parseEducationDateForValidation($payload['jhs_from'] ?? null);
        if ($elemToDate && $jhsFromDate && $jhsFromDate->lt($elemToDate)) {
            $validator->errors()->add('jhs_from', 'Secondary "From" year must not be before Elementary "To" year.');
        }

        foreach (['vocational', 'college', 'grad'] as $educationType) {
            $entries = $payload[$educationType] ?? [];
            if (!is_array($entries)) {
                continue;
            }

            foreach ($entries as $index => $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $this->addEducationDateRangeValidationError(
                    $validator,
                    $entry['from'] ?? null,
                    $entry['to'] ?? null,
                    "{$educationType}.{$index}.from",
                    "{$educationType}.{$index}.to",
                    $educationType === 'vocational'
                );
            }
        }
    }

    private function valueIsEmptyOrNa($value): bool
    {
        $normalized = strtolower(str_replace(' ', '', trim((string) ($value ?? ''))));
        return $normalized === '' || $normalized === 'n/a' || $normalized === 'na' || $normalized === 'n\\a' || $normalized === 'null';
    }

    private function hasMeaningfulEducationValue($value): bool
    {
        $normalized = strtolower(trim((string) ($value ?? '')));
        // Also consider N/A variations as non-meaningful data
        return $normalized !== '' 
            && $normalized !== 'null' 
            && $normalized !== 'noinput' 
            && $normalized !== 'n/a' 
            && $normalized !== 'na' 
            && $normalized !== 'n\\a';
    }

    private function isTruthyFlag($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) ($value ?? '')));
        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }

    private function addHighestLevelRequiredWhenNoYearError(
        \Illuminate\Validation\Validator $validator,
        array $payload,
        string $yearField,
        string $highestLevelField
    ): void {
        $yearMissingOrNa = $this->valueIsEmptyOrNa($payload[$yearField] ?? null);
        $hasHighestLevel = $this->hasMeaningfulEducationValue($payload[$highestLevelField] ?? null);

        if ($yearMissingOrNa && !$hasHighestLevel) {
            $validator->errors()->add(
                $highestLevelField,
                'The Highest Level/Units Earned field is required when Year Graduated is empty or N/A.'
            );
        }
    }

    private function validateEducationCompletionRules(\Illuminate\Validation\Validator $validator, array $payload): void
    {
        $isElementaryGraduate = !$this->valueIsEmptyOrNa($payload['elem_year_graduated'] ?? null);

        $this->addHighestLevelRequiredWhenNoYearError($validator, $payload, 'elem_year_graduated', 'elem_earned');
        if ($isElementaryGraduate) {
            $this->addHighestLevelRequiredWhenNoYearError($validator, $payload, 'jhs_year_graduated', 'jhs_earned');
        }

        $collegeRows = $payload['college'] ?? [];
        if (is_array($collegeRows)) {
            foreach ($collegeRows as $index => $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $hasAnyData = false;
                foreach (['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'] as $field) {
                    if ($this->hasMeaningfulEducationValue($entry[$field] ?? null)) {
                        $hasAnyData = true;
                        break;
                    }
                }

                if (!$hasAnyData) {
                    continue;

                }

                $yearMissingOrNa = $this->valueIsEmptyOrNa($entry['year_graduated'] ?? null);
                $hasHighestLevel = $this->hasMeaningfulEducationValue($entry['earned'] ?? null);

                if ($yearMissingOrNa && !$hasHighestLevel) {
                    $validator->errors()->add(
                        "college.$index.earned",
                        'The Highest Level/Units Earned field is required when Year Graduated is empty or N/A.'
                    );
                }
            }
        }
    }

    private function normalizeTelephoneInput(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null || $digits === '') {
            return $value;
        }

        // Convert international PH prefixes to local 0-prefixed format.
        if (str_starts_with($digits, '63') && strlen($digits) >= 11) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    private function normalizeMobileInput(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null || $digits === '') {
            return $value;
        }

        // Accept +63 9XX XXX XXXX and normalize to 09XXXXXXXXX.
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    private function isGoogleOAuthUser($user): bool
    {
        if (!$user) {
            return false;
        }

        $password = (string) ($user->password ?? '');
        if ($password === '') {
            return false;
        }

        return Hash::check('google-oauth', $password);
    }

    private function mergeC1RegistrationDefaults(array $c1Data, $user): array
    {
        if (!$user) {
            return $c1Data;
        }

        $defaults = [
            'mobile_no' => $this->normalizeMobileInput((string) ($user->phone_number ?? '')) ?: null,
            'email_address' => trim((string) ($user->email ?? '')) ?: null,
        ];

        if (!$this->isGoogleOAuthUser($user)) {
            $defaults = array_merge([
                'surname' => trim((string) ($user->last_name ?? '')) ?: null,
                'first_name' => trim((string) ($user->first_name ?? '')) ?: null,
                'middle_name' => trim((string) ($user->middle_name ?? '')) ?: null,
                'sex' => $this->normalizeSex((string) ($user->sex ?? '')) ?: null,
            ], $defaults);
        }

        foreach ($defaults as $key => $value) {
            $current = $c1Data[$key] ?? null;
            $currentText = trim((string) $current);
            $isMissing = $current === null || $currentText === '' || strtoupper($currentText) === 'NOINPUT';
            if ($isMissing && $value !== null && trim((string) $value) !== '') {
                $c1Data[$key] = $value;
            }
        }

        return $c1Data;
    }

    /**
     * Updates the C1 session data based on the database .If there is no data on the database,
     * the function should return an empty array.
     * @return array|null
     */
    private function c1GetFormFromDB()
    {

        $c1_full_info = [];
        $current_user = Auth::user();
        if (!$current_user) {
            return $c1_full_info;
        }

        $user_personal_info = $current_user->personalInformation?->attributesToArray();
        if ($user_personal_info != null) {
            $user_personal_info['date_of_birth'] = $this->normalizeDateForForm($user_personal_info['date_of_birth'] ?? null);

            [
                $user_personal_info['res_house_no'],
                $user_personal_info['res_street'],
                $user_personal_info['res_sub_vil'],
                $user_personal_info['res_brgy'],
                $user_personal_info['res_city'],
                $user_personal_info['res_province'],
                $user_personal_info['res_zipcode']
            ] = explode(self::SEPARATOR, $user_personal_info['residential_address']);

            $user_personal_info['res_house_no'] = ($user_personal_info['res_house_no'] != '{*}') ? $user_personal_info['res_house_no'] : null;
            $user_personal_info['res_street'] = ($user_personal_info['res_street'] != '{*}') ? $user_personal_info['res_street'] : null;
            $user_personal_info['res_sub_vil'] = ($user_personal_info['res_sub_vil'] != '{*}') ? $user_personal_info['res_sub_vil'] : null;
            $user_personal_info['res_brgy'] = ($user_personal_info['res_brgy'] != '{*}') ? $user_personal_info['res_brgy'] : null;
            $user_personal_info['res_city'] = ($user_personal_info['res_city'] != '{*}') ? $user_personal_info['res_city'] : null;
            $user_personal_info['res_province'] = ($user_personal_info['res_province'] != '{*}') ? $user_personal_info['res_province'] : null;
            $user_personal_info['res_zipcode'] = ($user_personal_info['res_zipcode'] != '{*}') ? $user_personal_info['res_zipcode'] : null;

            [
                $user_personal_info['per_house_no'],
                $user_personal_info['per_street'],
                $user_personal_info['per_sub_vil'],
                $user_personal_info['per_brgy'],
                $user_personal_info['per_city'],
                $user_personal_info['per_province'],
                $user_personal_info['per_zipcode'],
            ] = explode(self::SEPARATOR, $user_personal_info['permanent_address']);

            $user_personal_info['per_house_no'] = ($user_personal_info['per_house_no'] != '{*}') ? $user_personal_info['per_house_no'] : null;
            $user_personal_info['per_street'] = ($user_personal_info['per_street'] != '{*}') ? $user_personal_info['per_street'] : null;
            $user_personal_info['per_sub_vil'] = ($user_personal_info['per_sub_vil'] != '{*}') ? $user_personal_info['per_sub_vil'] : null;
            $user_personal_info['per_brgy'] = ($user_personal_info['per_brgy'] != '{*}') ? $user_personal_info['per_brgy'] : null;
            $user_personal_info['per_city'] = ($user_personal_info['per_city'] != '{*}') ? $user_personal_info['per_city'] : null;
            $user_personal_info['per_province'] = ($user_personal_info['per_province'] != '{*}') ? $user_personal_info['per_province'] : null;
            $user_personal_info['per_zipcode'] = ($user_personal_info['per_zipcode'] != '{*}') ? $user_personal_info['per_zipcode'] : null;

            $c1_full_info = array_merge($c1_full_info, $user_personal_info);
        }

        $c1_full_info = $this->mergeC1RegistrationDefaults($c1_full_info, $current_user);

        $user_family_bg = $current_user->familyBackground?->attributesToArray();
        if ($user_family_bg != null) {

            $c1_full_info['children'] = $user_family_bg['children_info'];
            $c1_full_info = array_merge($c1_full_info, $user_family_bg);
        }

        $user_educational_bg = $current_user->educationalBackground?->attributesToArray();
        if ($user_educational_bg != null) {
            foreach (['elem_from', 'elem_to', 'jhs_from', 'jhs_to', 'shs_from', 'shs_to'] as $dateField) {
                $user_educational_bg[$dateField] = $this->normalizeEducationYearForForm($user_educational_bg[$dateField] ?? null);
            }

            // If Senior High data exists but Junior High doesn't, copy SHS data to JHS fields for form display
            $hasShsBasic = !empty(trim((string) ($user_educational_bg['shs_basic'] ?? '')));
            $hasJhsBasic = !empty(trim((string) ($user_educational_bg['jhs_basic'] ?? '')));
            if ($hasShsBasic && !$hasJhsBasic) {
                $user_educational_bg['jhs_basic'] = $user_educational_bg['shs_basic'];
                $user_educational_bg['jhs_school'] = $user_educational_bg['shs_school'] ?? '';
                $user_educational_bg['jhs_from'] = $user_educational_bg['shs_from'] ?? null;
                $user_educational_bg['jhs_to'] = $user_educational_bg['shs_to'] ?? null;
                $user_educational_bg['jhs_year_graduated'] = $user_educational_bg['shs_year_graduated'] ?? '';
                $user_educational_bg['jhs_academic_honors'] = $user_educational_bg['shs_academic_honors'] ?? '';
                $user_educational_bg['jhs_earned'] = $user_educational_bg['shs_earned'] ?? '';
            }

            foreach (['vocational', 'college', 'grad'] as $_key) {
                $c1_full_info[$_key] = $this->normalizeEducationEntriesForForm($user_educational_bg[$_key] ?? []);
            }
            $c1_full_info = array_merge($c1_full_info, $user_educational_bg);
        }

        // All 'NOINPUT' fields should be displayed as an empty string.
        if ($c1_full_info != null) {

            foreach ($c1_full_info as $key => $value) {
                if ($c1_full_info[$key] == 'NOINPUT') {
                    $c1_full_info[$key] = null;
                }
            }
        }
        return $c1_full_info;
    }


    /**
     * Display C1 page with all session data.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function c1DisplayForm()
    {

        // If form in session already exists then no need to retireve data from the database.
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        } else {
            $sessionC1 = session('form.c1', []);
            if (is_array($sessionC1)) {
                session(['form.c1' => $this->mergeC1RegistrationDefaults($sessionC1, Auth::user())]);
            }
        }
        $vocational_schools = session('form.c1.vocational', []);
        $college_schools = session('form.c1.college', []);
        $grad_schools = session('form.c1.grad', []);
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Viewed C1 form.');
        */
        // dd($vocational_schools);
        return view('pds.pds', compact('vocational_schools', 'college_schools', 'grad_schools'));
    }

    public function importC1Excel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pds_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please upload a valid Excel file (.xlsx or .xls) up to 10MB.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payload = $this->extractC1DataFromExcel($request->file('pds_excel')->getRealPath());

            $existingC1 = session('form.c1', []);
            if (!is_array($existingC1)) {
                $existingC1 = [];
            }

            session([
                'form.c1' => array_merge(
                    $existingC1,
                    $payload['c1']['fields'],
                    [
                        'children' => $payload['c1']['children'],
                        'vocational' => $payload['c1']['vocational'],
                        'college' => $payload['c1']['college'],
                        'grad' => $payload['c1']['grad'],
                    ]
                ),
                'form.c2' => $payload['c2'],
                'data_learning' => $payload['c3']['data_learning'],
                'data_voluntary' => $payload['c3']['data_voluntary'],
                'data_otherInfo' => $payload['c3']['data_otherInfo'],
                'form.c4' => $payload['c4'],
            ]);

            return response()->json([
                'message' => 'Excel file imported for C1-C4. Please review all fields before proceeding.',
                'data' => $payload['c1'],
                'warnings' => $payload['warnings'],
                'missing_report' => $payload['missing_report'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to import C1 Excel.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Unable to process the uploaded Excel file. Please try again with the official template.',
            ], 500);
        }
    }

    public function exportAnnexH1Excel(Request $request)
    {
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        }
        if (!session()->has('form.c2')) {
            session(['form.c2' => $this->c2GetFormFromDB()]);
        }
        if (empty(session('data_learning')) && empty(session('data_voluntary')) && empty(session('data_otherInfo'))) {
            $this->c3GetDatabase();
        }
        if (empty(session('form.c4'))) {
            $this->c4GetDatabase();
        }

        $templateCandidates = [
            base_path('ANNEX H-1 - CS Form No. 212 Revised 2025 - Personal Data Sheet.xlsx'),
            base_path('sample_docs/ANNEX H-1 - CS Form No. 212 Revised 2025 - Personal Data Sheet.xlsx'),
        ];
        $templatePath = null;
        foreach ($templateCandidates as $candidate) {
            if (is_file($candidate)) {
                $templatePath = $candidate;
                break;
            }
        }
        if ($templatePath === null) {
            abort(404, 'ANNEX H-1 Excel template was not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $c1Sheet = $spreadsheet->getSheetByName('C1');
        $c2Sheet = $spreadsheet->getSheetByName('C2');
        $c3Sheet = $spreadsheet->getSheetByName('C3');
        $c4Sheet = $spreadsheet->getSheetByName('C4');
        if (!$c1Sheet || !$c2Sheet || !$c3Sheet || !$c4Sheet) {
            abort(422, 'Incompatible template: expected sheets C1, C2, C3, and C4.');
        }

        $c1 = session('form.c1', []);
        $c2 = session('form.c2', []);
        $dataLearning = session('data_learning', []);
        $dataVoluntary = session('data_voluntary', []);
        $dataOther = session('data_otherInfo', []);
        $c4 = session('form.c4', []);

        $fieldToCellMap = [
            'surname' => 'D10', 'first_name' => 'D11', 'middle_name' => 'D12', 'name_extension' => 'L11',
            'place_of_birth' => 'D15', 'dual_country' => 'J16', 'height' => 'D22', 'weight' => 'D24',
            'blood_type' => 'D25', 'gsis_id_no' => 'D27', 'pagibig_id_no' => 'D29', 'philhealth_no' => 'D31',
            'sss_id_no' => 'D32', 'tin_no' => 'D33', 'agency_employee_no' => 'D34', 'res_house_no' => 'I17',
            'res_street' => 'L17', 'res_sub_vil' => 'I20', 'res_brgy' => 'L20', 'res_city' => 'I22',
            'res_province' => 'L22', 'res_zipcode' => 'I24', 'per_house_no' => 'I25', 'per_street' => 'L25',
            'per_sub_vil' => 'I27', 'per_brgy' => 'L27', 'per_city' => 'I29', 'per_province' => 'L29',
            'per_zipcode' => 'I31', 'telephone_no' => 'I32', 'mobile_no' => 'I33', 'email_address' => 'I34',
            'spouse_surname' => 'D36', 'spouse_first_name' => 'D37', 'spouse_name_extension' => 'G37',
            'spouse_middle_name' => 'D38', 'spouse_occupation' => 'D39', 'spouse_employer' => 'D40',
            'spouse_business_address' => 'D41', 'spouse_telephone' => 'D42', 'father_surname' => 'D43',
            'father_first_name' => 'D44', 'father_name_extension' => 'G44', 'father_middle_name' => 'D45',
            'mother_maiden_surname' => 'D47', 'mother_maiden_first_name' => 'D48', 'mother_maiden_middle_name' => 'D49',
            'elem_school' => 'D54', 'elem_basic' => 'G54', 'elem_earned' => 'L54', 'elem_year_graduated' => 'M54',
            'elem_academic_honors' => 'N54', 'jhs_school' => 'D55', 'jhs_basic' => 'G55', 'jhs_earned' => 'L55',
            'jhs_year_graduated' => 'M55', 'jhs_academic_honors' => 'N55',
        ];
        $addressFields = [
            'res_house_no', 'res_street', 'res_sub_vil', 'res_brgy', 'res_city', 'res_province', 'res_zipcode',
            'per_house_no', 'per_street', 'per_sub_vil', 'per_brgy', 'per_city', 'per_province', 'per_zipcode',
        ];
        foreach ($fieldToCellMap as $field => $cell) {
            $value = $c1[$field] ?? '';
            if (in_array($field, $addressFields, true)) {
                $value = $this->formatAddressForAnnexExport($value);
            } else {
                $value = $this->formatAnnexDisplayValue($value);
            }
            $this->setExcelText($c1Sheet, $cell, $value);
        }
        $this->setExcelText($c1Sheet, 'D13', $this->formatAnnexDisplayValue($this->normalizeDateForExcel($c1['date_of_birth'] ?? '')));
        // Keep text helpers empty; sex/civil status are represented by template checkbox controls.
        $this->setExcelText($c1Sheet, 'D16', '');
        $this->setExcelText($c1Sheet, 'D17', '');
        foreach (['J13', 'K13', 'L13', 'M13', 'N13'] as $cell) {
            $this->setExcelText($c1Sheet, $cell, '');
        }
        $this->setExcelText($c1Sheet, 'J54', $this->formatAnnexDisplayValue($this->normalizeDateForExcel($c1['elem_from'] ?? '')));
        $this->setExcelText($c1Sheet, 'K54', $this->formatAnnexDisplayValue($this->normalizeDateForExcel($c1['elem_to'] ?? '')));
        $this->setExcelText($c1Sheet, 'J55', $this->formatAnnexDisplayValue($this->normalizeDateForExcel($c1['jhs_from'] ?? '')));
        $this->setExcelText($c1Sheet, 'K55', $this->formatAnnexDisplayValue($this->normalizeDateForExcel($c1['jhs_to'] ?? '')));

        $children = is_array($c1['children'] ?? null) ? $c1['children'] : [];
        for ($i = 0; $i < 12; $i++) {
            $row = 37 + $i;
            $item = $children[$i] ?? [];
            $name = trim((string) ($item['name'] ?? ''));
            $dob = $this->normalizeDateForExcel($item['dob'] ?? '');
            if ($name === '' && $dob === '') {
                $this->setExcelText($c1Sheet, "I{$row}", '');
                $this->setExcelText($c1Sheet, "M{$row}", '');
                continue;
            }
            $this->setExcelText($c1Sheet, "I{$row}", $this->formatAnnexDisplayValue($name));
            $this->setExcelText($c1Sheet, "M{$row}", $this->formatAnnexDisplayValue($dob));
        }

        $this->fillEducationRowToC1($c1Sheet, '56', $c1['vocational'][0] ?? []);
        $this->fillEducationRowToC1($c1Sheet, '57', $c1['college'][0] ?? []);
        $this->fillEducationRowToC1($c1Sheet, '58', $c1['grad'][0] ?? []);

        $civilRows = is_array($c2['all_user_civil_service_eligibility'] ?? null) ? $c2['all_user_civil_service_eligibility'] : [];
        for ($i = 0; $i < 7; $i++) {
            $row = 5 + $i;
            $entry = $civilRows[$i] ?? [];
            $hasData = collect([
                $entry['cs_eligibility_career'] ?? '',
                $entry['cs_eligibility_rating'] ?? '',
                $entry['cs_eligibility_date'] ?? '',
                $entry['cs_eligibility_place'] ?? '',
                $entry['cs_eligibility_license'] ?? '',
                $entry['cs_eligibility_validity'] ?? '',
            ])->contains(fn($v) => trim((string) $v) !== '');
            if (!$hasData) {
                foreach (['B', 'F', 'G', 'I', 'J', 'K'] as $col) {
                    $this->setExcelText($c2Sheet, "{$col}{$row}", '');
                }
                continue;
            }
            $this->setExcelText($c2Sheet, "B{$row}", $this->formatAnnexDisplayValue($entry['cs_eligibility_career'] ?? ''));
            $this->setExcelText($c2Sheet, "F{$row}", $this->formatAnnexDisplayValue($entry['cs_eligibility_rating'] ?? ''));
            $this->setExcelText($c2Sheet, "G{$row}", $this->formatAnnexDisplayValue($this->normalizeDateForExcel($entry['cs_eligibility_date'] ?? '')));
            $this->setExcelText($c2Sheet, "I{$row}", $this->formatAnnexDisplayValue($entry['cs_eligibility_place'] ?? ''));
            $this->setExcelText($c2Sheet, "J{$row}", $this->formatAnnexDisplayValue($entry['cs_eligibility_license'] ?? ''));
            $this->setExcelText($c2Sheet, "K{$row}", $this->formatAnnexDisplayValue($this->normalizeDateForExcel($entry['cs_eligibility_validity'] ?? '')));
        }

        $workRows = is_array($c2['all_user_work_exps'] ?? null) ? $c2['all_user_work_exps'] : [];
        for ($i = 0; $i < 28; $i++) {
            $row = 18 + $i;
            $entry = $workRows[$i] ?? [];
            $hasData = collect([
                $entry['work_exp_from'] ?? '',
                $entry['work_exp_to'] ?? '',
                $entry['work_exp_position'] ?? '',
                $entry['work_exp_department'] ?? '',
                $entry['work_exp_status'] ?? '',
                $entry['work_exp_govt_service'] ?? '',
            ])->contains(fn($v) => trim((string) $v) !== '');
            if (!$hasData) {
                foreach (['A', 'C', 'D', 'G', 'J', 'K'] as $col) {
                    $this->setExcelText($c2Sheet, "{$col}{$row}", '');
                }
                continue;
            }
            $this->setExcelText($c2Sheet, "A{$row}", $this->formatAnnexDisplayValue($this->normalizeDateForExcel($entry['work_exp_from'] ?? '')));
            $this->setExcelText($c2Sheet, "C{$row}", $this->formatAnnexDisplayValue($this->normalizeDateForExcel($entry['work_exp_to'] ?? '')));
            $this->setExcelText($c2Sheet, "D{$row}", $this->formatAnnexDisplayValue($entry['work_exp_position'] ?? ''));
            $this->setExcelText($c2Sheet, "G{$row}", $this->formatAnnexDisplayValue($entry['work_exp_department'] ?? ''));
            $this->setExcelText($c2Sheet, "J{$row}", $this->formatAnnexDisplayValue($entry['work_exp_status'] ?? ''));
            $this->setExcelText($c2Sheet, "K{$row}", $this->formatAnnexDisplayValue($entry['work_exp_govt_service'] ?? ''));
        }

        $volRows = is_array($dataVoluntary) ? $dataVoluntary : [];
        for ($i = 0; $i < 7; $i++) {
            $row = 6 + $i;
            $entry = $volRows[$i] ?? [];
            $this->setExcelText($c3Sheet, "B{$row}", $entry['voluntary_org'] ?? '');
            $this->setExcelText($c3Sheet, "E{$row}", $this->normalizeDateForExcel($entry['voluntary_from'] ?? ''));
            $this->setExcelText($c3Sheet, "F{$row}", $this->normalizeDateForExcel($entry['voluntary_to'] ?? ''));
            $this->setExcelText($c3Sheet, "G{$row}", $entry['voluntary_hours'] ?? '');
            $this->setExcelText($c3Sheet, "H{$row}", $entry['voluntary_position'] ?? '');
        }

        $learnRows = is_array($dataLearning) ? $dataLearning : [];
        for ($i = 0; $i < 21; $i++) {
            $row = 18 + $i;
            $entry = $learnRows[$i] ?? [];
            $this->setExcelText($c3Sheet, "B{$row}", $entry['learning_title'] ?? '');
            $this->setExcelText($c3Sheet, "E{$row}", $this->normalizeDateForExcel($entry['learning_from'] ?? ''));
            $this->setExcelText($c3Sheet, "F{$row}", $this->normalizeDateForExcel($entry['learning_to'] ?? ''));
            $this->setExcelText($c3Sheet, "G{$row}", $entry['learning_hours'] ?? '');
            $this->setExcelText($c3Sheet, "H{$row}", $entry['learning_type'] ?? '');
            $this->setExcelText($c3Sheet, "I{$row}", $entry['learning_conducted'] ?? '');
        }

        $skills = $this->normalizeListData($dataOther['skill'] ?? []);
        $distinctions = $this->normalizeListData($dataOther['distinction'] ?? []);
        $organizations = $this->normalizeListData($dataOther['organization'] ?? []);
        for ($i = 0; $i < 7; $i++) {
            $row = 42 + $i;
            $this->setExcelText($c3Sheet, "B{$row}", $skills[$i] ?? '');
            $this->setExcelText($c3Sheet, "C{$row}", $distinctions[$i] ?? '');
            $this->setExcelText($c3Sheet, "I{$row}", $organizations[$i] ?? '');
        }

        foreach (['I6', 'K6', 'I8', 'K8', 'I13', 'K13', 'I18', 'K18', 'I23', 'K23', 'I27', 'K27', 'I31', 'K31', 'I34', 'K34', 'I37', 'K37', 'I43', 'K43', 'I45', 'K45', 'I47', 'K47'] as $checkboxCell) {
            $this->setExcelText($c4Sheet, $checkboxCell, '');
        }
        // Keep template label in G10:L10; place user detail in the line below.
        $this->setExcelText($c4Sheet, 'H11', $this->yesNoDetail($c4['related_34_b'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'H15', $this->yesNoDetail($c4['guilty_35_a'] ?? 'no'));
        [$caseDate, $caseStatus] = $this->parseCriminalCaseValue($c4);
        $this->setExcelText($c4Sheet, 'K20', $this->normalizeDateForExcel($caseDate));
        $this->setExcelText($c4Sheet, 'K21', $caseStatus);
        $this->setExcelText($c4Sheet, 'H25', $this->yesNoDetail($c4['convicted_36'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'H29', $this->yesNoDetail($c4['separated_37'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'K32', $this->yesNoDetail($c4['candidate_38'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'K35', $this->yesNoDetail($c4['resigned_38_b'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'H39', $this->yesNoDetail($c4['immigrant_39'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'L44', $this->yesNoDetail($c4['indigenous_40_a'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'L46', $this->yesNoDetail($c4['pwd_40_b'] ?? 'no'));
        $this->setExcelText($c4Sheet, 'L48', $this->yesNoDetail($c4['solo_parent_40_c'] ?? 'no'));

        $this->setExcelText($c4Sheet, 'A52', $this->formatAnnexDisplayValue($c4['ref1_name'] ?? ''));
        $this->setExcelText($c4Sheet, 'F52', $this->formatAnnexDisplayValue($c4['ref1_address'] ?? ''));
        $this->setExcelText($c4Sheet, 'G52', $this->formatAnnexDisplayValue($c4['ref1_tel'] ?? ''));
        $this->setExcelText($c4Sheet, 'A53', $this->formatAnnexDisplayValue($c4['ref2_name'] ?? ''));
        $this->setExcelText($c4Sheet, 'F53', $this->formatAnnexDisplayValue($c4['ref2_address'] ?? ''));
        $this->setExcelText($c4Sheet, 'G53', $this->formatAnnexDisplayValue($c4['ref2_tel'] ?? ''));
        $this->setExcelText($c4Sheet, 'A54', $this->formatAnnexDisplayValue($c4['ref3_name'] ?? ''));
        $this->setExcelText($c4Sheet, 'F54', $this->formatAnnexDisplayValue($c4['ref3_address'] ?? ''));
        $this->setExcelText($c4Sheet, 'G54', $this->formatAnnexDisplayValue($c4['ref3_tel'] ?? ''));

        $this->setExcelText($c4Sheet, 'B61', $this->formatAnnexDisplayValue($c4['govt_id_type'] ?? ''));
        $this->setExcelText($c4Sheet, 'B62', $this->formatAnnexDisplayValue($c4['govt_id_number'] ?? ''));
        $govtCombined = $this->formatGovtIssuePlaceAndDate(
            $c4['govt_id_place_issued'] ?? '',
            $c4['govt_id_date_issued'] ?? ''
        );
        $this->setExcelText($c4Sheet, 'B64', $this->formatAnnexDisplayValue($govtCombined));

        $filename = 'ANNEX H-1 - CS Form No. 212 Revised 2025 - Personal Data Sheet - ' . now()->format('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/' . uniqid('annex_h1_', true) . '.xlsx');

        // TEMP DEBUG: diagnose civil status checkbox export state.
        try {
            $debugC1States = $this->buildC1TemplateCheckboxStates($c1);
            Log::debug('ANNEX_H1_EXPORT_C1_DEBUG', [
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'civil_status_raw' => $c1['civil_status'] ?? null,
                'civil_status_normalized' => $this->normalizeCivilStatus((string) ($c1['civil_status'] ?? '')),
                'sex_raw' => $c1['sex'] ?? null,
                'sex_normalized' => $this->normalizeSex((string) ($c1['sex'] ?? '')),
                'c1_checkbox_states' => [
                    'single_1058' => $debugC1States[1058] ?? null,
                    'married_1059' => $debugC1States[1059] ?? null,
                    'widowed_1060' => $debugC1States[1060] ?? null,
                    'other_1061' => $debugC1States[1061] ?? null,
                    'separated_1062' => $debugC1States[1062] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::debug('ANNEX_H1_EXPORT_C1_DEBUG_ERROR', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }

        $this->stripWorkbookDefinedNames($spreadsheet);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);
        $this->applyAnnexTemplateCheckboxStates($tempPath, $c1, $c4);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    private function setExcelText($sheet, string $cell, $value): void
    {
        $text = $this->sanitizeForExcelCell($value);
        $sheet->setCellValueExplicit($cell, $text, DataType::TYPE_STRING);
        $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(false);
    }

    private function normalizeDateForExcel($value): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }
        if (strtolower($raw) === 'present') {
            return 'PRESENT';
        }
        return $this->normalizeDateString($raw, 'd-m-Y');
    }

    private function fillEducationRowToC1($sheet, string $row, array $entry): void
    {
        $hasData = false;
        foreach (['school', 'basic', 'from', 'to', 'earned', 'year_graduated', 'academic_honors'] as $key) {
            if (trim((string) ($entry[$key] ?? '')) !== '') {
                $hasData = true;
                break;
            }
        }
        if (!$hasData) {
            foreach (['D', 'G', 'J', 'K', 'L', 'M', 'N'] as $col) {
                $this->setExcelText($sheet, "{$col}{$row}", '');
            }
            return;
        }

        $this->setExcelText($sheet, "D{$row}", $this->formatAnnexDisplayValue($entry['school'] ?? ''));
        $this->setExcelText($sheet, "G{$row}", $this->formatAnnexDisplayValue($entry['basic'] ?? ''));
        $this->setExcelText($sheet, "J{$row}", $this->formatAnnexDisplayValue($this->normalizeDateForExcel($entry['from'] ?? '')));
        $this->setExcelText($sheet, "K{$row}", $this->formatAnnexDisplayValue($this->normalizeDateForExcel($entry['to'] ?? '')));
        $this->setExcelText($sheet, "L{$row}", $this->formatAnnexDisplayValue($entry['earned'] ?? ''));
        $this->setExcelText($sheet, "M{$row}", $this->formatAnnexDisplayValue($entry['year_graduated'] ?? ''));
        $this->setExcelText($sheet, "N{$row}", $this->formatAnnexDisplayValue($entry['academic_honors'] ?? ''));
    }

    private function writeYesNoToCells($sheet, string $yesCell, string $noCell, $value): void
    {
        $normalized = strtolower(trim((string) ($value ?? '')));
        $isYes = $normalized !== '' && $normalized !== 'no';
        $this->setExcelCheckboxMark($sheet, $yesCell, $isYes);
        $this->setExcelCheckboxMark($sheet, $noCell, !$isYes);
    }

    private function setExcelCheckboxMark($sheet, string $cell, bool $checked): void
    {
        $sheet->setCellValueExplicit($cell, $checked ? '☑' : '', DataType::TYPE_STRING);
        $alignment = $sheet->getStyle($cell)->getAlignment();
        $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $alignment->setVertical(Alignment::VERTICAL_CENTER);
        $alignment->setWrapText(false);
        $sheet->getStyle($cell)->getFont()->setBold(false);
    }

    private function writeCitizenshipCheckboxes($sheet, $citizenship): void
    {
        $value = strtolower(trim((string) ($citizenship ?? '')));
        $isDual = str_contains($value, 'dual');
        $isFilipino = $value === 'filipino' || (!$isDual && $value !== '');

        // Clear any accidental text/value in the citizenship checkbox band.
        foreach (['J13', 'K13', 'L13', 'M13', 'N13'] as $cell) {
            $this->setExcelText($sheet, $cell, '');
        }

        // Template C1 checkbox cells for citizenship (within J:N, row 13).
        // Filipino checkbox
        $this->setExcelCheckboxMark($sheet, 'J13', $isFilipino);
        // Dual Citizenship checkbox
        $this->setExcelCheckboxMark($sheet, 'M13', $isDual);
    }

    private function applyAnnexTemplateCheckboxStates(string $xlsxPath, array $c1, array $c4): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            return;
        }

        try {
            $c1States = $this->buildC1TemplateCheckboxStates($c1);
            $c4States = $this->buildC4TemplateCheckboxStates($c4);
            $this->patchVmlCheckboxFile($zip, 'xl/drawings/vmlDrawing1.vml', $c1States);
            $this->patchVmlCheckboxFile($zip, 'xl/drawings/vmlDrawing2.vml', $c4States);
        } finally {
            $zip->close();
        }
    }

    private function patchVmlCheckboxFile(\ZipArchive $zip, string $entryName, array $states): void
    {
        if (empty($states) || $zip->locateName($entryName) === false) {
            return;
        }

        $xml = $zip->getFromName($entryName);
        if (!is_string($xml) || trim($xml) === '') {
            return;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        if (!@$dom->loadXML($xml, LIBXML_NONET)) {
            return;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');
        $xpath->registerNamespace('x', 'urn:schemas-microsoft-com:office:excel');
        $xpath->registerNamespace('o', 'urn:schemas-microsoft-com:office:office');

        foreach ($states as $shapeId => $checked) {
            $shapeNodes = $xpath->query("//v:shape[@id='_x0000_s{$shapeId}' or @o:spid='_x0000_s{$shapeId}']");
            if (!$shapeNodes || $shapeNodes->length === 0) {
                continue;
            }

            foreach ($shapeNodes as $shapeNode) {
                $clientDataNode = $xpath->query("./x:ClientData[@ObjectType='Checkbox']", $shapeNode)->item(0);
                if (!$clientDataNode) {
                    continue;
                }

                $checkedNode = $xpath->query("./x:Checked", $clientDataNode)->item(0);
                if ($checked) {
                    if (!$checkedNode) {
                        $clientDataNode->appendChild($dom->createElementNS('urn:schemas-microsoft-com:office:excel', 'x:Checked'));
                    }
                } elseif ($checkedNode) {
                    $clientDataNode->removeChild($checkedNode);
                }
            }
        }

        $zip->addFromString($entryName, $dom->saveXML());
    }

    private function buildC1TemplateCheckboxStates(array $c1): array
    {
        $value = strtolower(trim((string) ($c1['citizenship'] ?? '')));
        $isDual = str_contains($value, 'dual');
        $isFilipino = $value === 'filipino' || (!$isDual && $value !== '');

        $dualType = strtolower(trim((string) ($c1['dual_type'] ?? '')));
        $isByBirth = $isDual && $dualType === 'by birth';
        $isByNaturalization = $isDual && $dualType === 'by naturalization';

        $sex = $this->normalizeSex((string) ($c1['sex'] ?? ''));
        $isMale = $sex === 'male';
        $isFemale = $sex === 'female';

        $civilStatus = strtolower(trim((string) ($c1['civil_status'] ?? '')));
        $isSingle = $civilStatus === 'single';
        $isMarried = $civilStatus === 'married';
        $isWidowed = in_array($civilStatus, ['widowed', 'widower'], true);
        $isSeparated = $civilStatus === 'separated';
        $isOther = $civilStatus === 'other';

        return [
            1045 => $isFilipino,
            1046 => $isDual,
            1049 => $isMale,
            1050 => $isFemale,
            1058 => $isSingle,
            1059 => $isMarried,
            1060 => $isWidowed,
            1061 => $isOther,
            1062 => $isSeparated,
            1063 => $isByBirth,
            1064 => $isByNaturalization,
        ];
    }

    private function buildC4TemplateCheckboxStates(array $c4): array
    {
        $pairs = $this->c4TemplateCheckboxShapePairs();

        $states = [];
        foreach ($pairs as $field => [$yesShapeId, $noShapeId]) {
            $isYes = $this->isCheckedYes($c4[$field] ?? 'no');
            $states[$yesShapeId] = $isYes;
            $states[$noShapeId] = !$isYes;
        }

        return $states;
    }

    private function c4TemplateCheckboxShapePairs(): array
    {
        return [
            'related_34_a' => [4097, 4098],
            'related_34_b' => [4099, 4100],
            'guilty_35_a' => [4101, 4102],
            'criminal_35_b' => [4103, 4104],
            'convicted_36' => [4105, 4106],
            'separated_37' => [4107, 4108],
            'candidate_38' => [4122, 4123],
            'resigned_38_b' => [4124, 4125],
            'immigrant_39' => [4109, 4110],
            'indigenous_40_a' => [4111, 4114],
            'pwd_40_b' => [4112, 4115],
            'solo_parent_40_c' => [4113, 4116],
        ];
    }

    private function isCheckedYes($value): bool
    {
        $normalized = strtolower(trim((string) ($value ?? '')));
        return $normalized !== '' && $normalized !== 'no';
    }

    private function sanitizeForExcelCell($value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '';
        }

        // Remove invalid XML control characters (keep tab/newline/carriage return).
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
        return $text;
    }

    private function yesNoDetail($value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || strtolower($text) === 'no') {
            return '';
        }
        return $text;
    }

    private function parseCriminalCaseValue(array $c4): array
    {
        $case = $c4['criminal_35_b_array'] ?? null;
        if (is_array($case)) {
            return [
                trim((string) ($case['date'] ?? '')),
                trim((string) ($case['status'] ?? '')),
            ];
        }

        $value = trim((string) ($c4['criminal_35_b'] ?? ''));
        if ($value === '' || strtolower($value) === 'no') {
            return ['', ''];
        }
        $parts = array_map('trim', explode(',', $value, 2));
        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function stripWorkbookDefinedNames($spreadsheet): void
    {
        try {
            $definedNames = $spreadsheet->getDefinedNames();
            if (!is_array($definedNames) || empty($definedNames)) {
                return;
            }

            foreach ($definedNames as $definedName) {
                if (!is_object($definedName) || !method_exists($definedName, 'getName')) {
                    continue;
                }
                $name = (string) $definedName->getName();
                $scope = method_exists($definedName, 'getScope') ? $definedName->getScope() : null;
                $spreadsheet->removeDefinedName($name, $scope);
            }
        } catch (\Throwable $e) {
            // Ignore: export should continue even if named ranges cannot be modified.
        }
    }


    /**
     * Update the C1 session data based on the input fields.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function c1UpdateFormSession(Request $request, $go_to)
    {
        //dd($request->all());

        // Determine if the user has any secondary education data.
        // We gate college/grad data on this — but we must also accept jhs_earned
        // (Highest Level/Units Earned) as a valid indicator, not just jhs_year_graduated.
        $jhsYearGraduated = trim((string) $request->input('jhs_year_graduated'));
        $jhsEarned        = trim((string) $request->input('jhs_earned'));
        $jhsSchool        = trim((string) $request->input('jhs_school'));
        $jhsBasic         = trim((string) $request->input('jhs_basic'));

        $hasSecondaryData = !$this->valueIsEmptyOrNa($jhsYearGraduated)
            || $this->hasMeaningfulEducationValue($jhsEarned)
            || $this->hasMeaningfulEducationValue($jhsSchool)
            || (!$this->valueIsEmptyOrNa($jhsBasic) && strtoupper($jhsBasic) !== 'N/A');

        // Only discard college/grad data when there is truly no secondary education at all.
        $collegeData = $hasSecondaryData ? $request->input('college') : [];
        $gradData    = $hasSecondaryData ? $request->input('grad') : [];

        $normalizedCollegeEntries = $this->normalizeEducationEntriesForForm($collegeData);

        $request->merge([
            'telephone_no' => $this->normalizeTelephoneInput($request->input('telephone_no')),
            'mobile_no' => $this->normalizeMobileInput($request->input('mobile_no')),
            'elem_from' => $this->normalizeEducationYearForForm($request->input('elem_from')),
            'elem_to' => $this->normalizeEducationYearForForm($request->input('elem_to')),
            'jhs_from' => $this->normalizeEducationYearForForm($request->input('jhs_from')),
            'jhs_to' => $this->normalizeEducationYearForForm($request->input('jhs_to')),
            'vocational' => $this->normalizeEducationEntriesForForm($request->input('vocational')),
            'college' => $normalizedCollegeEntries,
            'grad' => $this->normalizeEducationEntriesForForm($gradData),
        ]);

        // For dynamically-loaded address selects (province/city/barangay), the browser may
        // submit an empty value if the PSGC API hasn't finished loading the options yet.
        // Fall back to the previously-saved session value so validation doesn't fail.
        $existingC1Session = (array) session('form.c1', []);
        $addressFallbackFields = [
            'res_province', 'res_city', 'res_brgy',
            'per_province', 'per_city', 'per_brgy',
        ];
        $addressMerge = [];
        foreach ($addressFallbackFields as $addrField) {
            $submitted = trim((string) $request->input($addrField, ''));
            if ($submitted === '') {
                $fromSession = trim((string) ($existingC1Session[$addrField] ?? ''));
                if ($fromSession !== '') {
                    $addressMerge[$addrField] = $fromSession;
                }
            }
        }
        if (!empty($addressMerge)) {
            $request->merge($addressMerge);
        }

        // get key-value pairs only for fields that need validation.
        $validator = Validator::make($request->all(), [
            'surname' => 'required|max:255|string',
            'first_name' => 'required|max:255|string',
            'middle_name' => 'nullable|max:255|string',
            'name_extension' => 'nullable|max:255|string',
            'civil_status' => 'required|string|in:single,married,widowed,widower,separated,other',
            'date_of_birth' => 'required|date_format:d-m-Y',
            'place_of_birth' => 'required|max:255|string',
            'citizenship' => 'required|max:255|in:Filipino,Dual Citizenship',
            'sex' => 'required|in:male,female',
            'blood_type' => 'required|max:255|string',
            'telephone_no' => 'nullable|regex:/^0\d{9,10}$/', // example: 0281234567, 0322123456
            'mobile_no' => 'required|regex:/^09\d{9}$/', // example: +639171234567
            'email_address' => 'required|email:rfc',
            'height' => 'required|numeric|max:999',
            'weight' => 'required|numeric|max:999',
            'res_province' => 'required|string|max:255',
            'res_city' => 'required|string|max:255',
            'res_brgy' => 'required|string|max:255',
            'per_province' => 'required|string|max:255',
            'per_city' => 'required|string|max:255',
            'per_brgy' => 'required|string|max:255',
            'res_zipcode' => 'nullable|string|max:4',
            'per_zipcode' => 'nullable|string|max:4',
            'elem_from' => ['required', 'digits:4'],
            'elem_to' => ['required', 'digits:4'],
            'jhs_from' => ['nullable', 'digits:4'],
            'jhs_to' => ['nullable', 'digits:4'],
            'vocational' => 'nullable|array',
            'vocational.*.from' => ['nullable', 'digits:4'],
            'vocational.*.to' => ['nullable', 'digits:4'],
            'vocational.*.school' => 'nullable|string|max:255',
            'vocational.*.basic' => 'nullable|string|max:255',
            'vocational.*.earned' => 'nullable|string|max:255',
            'vocational.*.year_graduated' => 'nullable|string|max:255',
            'vocational.*.academic_honors' => 'nullable|string|max:255',
            'college' => 'nullable|array',
            'college.*.from' => ['nullable', 'digits:4'],
            'college.*.to' => ['nullable', 'digits:4'],
            'college.*.school' => 'nullable|string|max:255',
            'college.*.basic' => 'nullable|string|max:255',
            'college.*.earned' => 'nullable|string|max:255',
            'college.*.year_graduated' => 'nullable|string|max:255',
            'college.*.academic_honors' => 'nullable|string|max:255',
            'grad' => 'nullable|array',
            'grad.*.from' => ['nullable', 'digits:4'],
            'grad.*.to' => ['nullable', 'digits:4'],
            'grad.*.school' => 'nullable|string|max:255',
            'grad.*.basic' => 'nullable|string|max:255',
            'grad.*.earned' => 'nullable|string|max:255',
            'grad.*.year_graduated' => 'nullable|string|max:255',
            'grad.*.academic_honors' => 'nullable|string|max:255',

        ], [
            'date_of_birth.date_format' => 'The date of birth field must match the format dd-mm-yyyy.',
            'elem_from.digits' => 'The elem from field must be a 4-digit year (YYYY).',
            'elem_to.digits' => 'The elem to field must be a 4-digit year (YYYY).',
            'jhs_from.digits' => 'The jhs from field must be a 4-digit year (YYYY).',
            'jhs_to.digits' => 'The jhs to field must be a 4-digit year (YYYY).',
            'vocational.*.from.digits' => 'The vocational from field must be a 4-digit year (YYYY).',
            'vocational.*.to.digits' => 'The vocational to field must be a 4-digit year (YYYY).',
            'college.*.from.digits' => 'The college from field must be a 4-digit year (YYYY).',
            'college.*.to.digits' => 'The college to field must be a 4-digit year (YYYY).',
            'grad.*.from.digits' => 'The graduate studies from field must be a 4-digit year (YYYY).',
            'grad.*.to.digits' => 'The graduate studies to field must be a 4-digit year (YYYY).',
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($request) {
            $this->validateEducationDateRanges($validator, $request->all());
            $this->validateEducationCompletionRules($validator, $request->all());
        });

        $c1_form_data_valid = $validator->validate();

        foreach (['date_of_birth'] as $dateField) {
            $normalizedDate = $this->normalizeDateForForm($c1_form_data_valid[$dateField] ?? null);
            $c1_form_data_valid[$dateField] = (is_string($normalizedDate) && trim($normalizedDate) === '') ? null : $normalizedDate;
        }

        foreach (['elem_from', 'elem_to', 'jhs_from', 'jhs_to'] as $dateField) {
            $normalizedDate = $this->normalizeEducationYearForForm($c1_form_data_valid[$dateField] ?? null);
            $c1_form_data_valid[$dateField] = (is_string($normalizedDate) && trim($normalizedDate) === '') ? null : $normalizedDate;
        }

        $existingC1FormData = (array) session('form.c1', []);

        // get all key-value pairs for non validated fields.
        $c1_form_data = $request->except([
            '_token',
            'surname',
            'first_name',
            'civil_status',
            'date_of_birth',
            'place_of_birth',
            'citizenship',
            'sex',
            'telephone_no',
            'mobile_no',
            'email_address',
            'height',
            'weight',
            'blood_type',
            'res_zipcode',
            'per_zipcode',
            'elem_from',
            'elem_to',
            'jhs_from',
            'jhs_to',

        ]);

        // Join current request data while preserving existing session fields that may not be present
        // in this payload (e.g., dynamically loaded address selects on intermittent submits).
        $c1_form_data = array_merge($existingC1FormData, $c1_form_data, $c1_form_data_valid);

        // check if there is data for children
        if (!$request->has('children')) {
            $c1_form_data['children'] = null;
        }

        session(['form.c1' => $c1_form_data]);

        // START DB SAVING LOGIC
        $c1_form_data_db = $c1_form_data;
        foreach (['elem_from', 'elem_to', 'jhs_from', 'jhs_to'] as $dateField) {
            $c1_form_data_db[$dateField] = $this->normalizeStrictDateForDatabase($c1_form_data_db[$dateField] ?? null);
        }
        $c1_form_data_db = array_merge([
            'surname' => '',
            'first_name' => '',
            'middle_name' => '',
            'name_extension' => '',
            'sex' => '',
            'civil_status' => '',
            'date_of_birth' => '',
            'place_of_birth' => '',
            'height' => '',
            'weight' => '',
            'blood_type' => '',
            'philhealth_no' => '',
            'tin_no' => '',
            'agency_employee_no' => '',
            'gsis_id_no' => '',
            'pagibig_id_no' => '',
            'sss_id_no' => '',
            'citizenship' => '',
            'telephone_no' => '',
            'mobile_no' => '',
            'email_address' => '',
            'res_house_no' => '',
            'res_street' => '',
            'res_sub_vil' => '',
            'res_brgy' => '',
            'res_city' => '',
            'res_province' => '',
            'res_zipcode' => '',
            'per_house_no' => '',
            'per_street' => '',
            'per_sub_vil' => '',
            'per_brgy' => '',
            'per_city' => '',
            'per_province' => '',
            'per_zipcode' => '',
            'elem_from' => null,
            'elem_to' => null,
            'elem_school' => '',
            'elem_academic_honors' => '',
            'elem_basic' => '',
            'elem_earned' => '',
            'elem_year_graduated' => '',
            'jhs_from' => null,
            'jhs_to' => null,
            'jhs_school' => '',
            'jhs_academic_honors' => '',
            'jhs_basic' => '',
            'jhs_earned' => '',
            'jhs_year_graduated' => '',
        ], $c1_form_data_db);

        // Format residential address
        $house_no_t = ($c1_form_data_db['res_house_no'] != '') ? $c1_form_data_db['res_house_no'] : '{*}';
        $street_t = ($c1_form_data_db['res_street'] != '') ? $c1_form_data_db['res_street'] : '{*}';
        $sub_vil_t = ($c1_form_data_db['res_sub_vil'] != '') ? $c1_form_data_db['res_sub_vil'] : '{*}';
        $brgy_t = ($c1_form_data_db['res_brgy'] != '') ? $c1_form_data_db['res_brgy'] : '{*}';
        $city_t = ($c1_form_data_db['res_city'] != '') ? $c1_form_data_db['res_city'] : '{*}';
        $province_t = ($c1_form_data_db['res_province'] != '') ? $c1_form_data_db['res_province'] : '{*}';
        $zipcode_t = ($c1_form_data_db['res_zipcode'] != '') ? $c1_form_data_db['res_zipcode'] : '{*}';
        $formatted_residential_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

        // Format permanent address
        $house_no_t = ($c1_form_data_db['per_house_no'] != '') ? $c1_form_data_db['per_house_no'] : '{*}';
        $street_t = ($c1_form_data_db['per_street'] != '') ? $c1_form_data_db['per_street'] : '{*}';
        $sub_vil_t = ($c1_form_data_db['per_sub_vil'] != '') ? $c1_form_data_db['per_sub_vil'] : '{*}';
        $brgy_t = ($c1_form_data_db['per_brgy'] != '') ? $c1_form_data_db['per_brgy'] : '{*}';
        $city_t = ($c1_form_data_db['per_city'] != '') ? $c1_form_data_db['per_city'] : '{*}';
        $province_t = ($c1_form_data_db['per_province'] != '') ? $c1_form_data_db['per_province'] : '{*}';
        $zipcode_t = ($c1_form_data_db['per_zipcode'] != '') ? $c1_form_data_db['per_zipcode'] : '{*}';
        $formatted_permanent_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

        $dual_type_t = '';
        if ($c1_form_data_db['citizenship'] === 'Dual Citizenship' || $c1_form_data_db['citizenship'] === 'Dual Citizen') {
            $dual_type_t = $c1_form_data_db['dual_type'] ?? '';
        }

        // Save PersonalInformation
        Models\PersonalInformation::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'surname' => $c1_form_data_db['surname'],
                'first_name' => $c1_form_data_db['first_name'],
                'middle_name' => $c1_form_data_db['middle_name'],
                'name_extension' => $c1_form_data_db['name_extension'],
                'sex' => $c1_form_data_db['sex'],
                'civil_status' => $c1_form_data_db['civil_status'],
                'date_of_birth' => $this->normalizeDateForDatabase($c1_form_data_db['date_of_birth']),
                'place_of_birth' => $c1_form_data_db['place_of_birth'],
                'height' => $c1_form_data_db['height'],
                'weight' => $c1_form_data_db['weight'],
                'blood_type' => $c1_form_data_db['blood_type'],
                'philhealth_no' => $c1_form_data_db['philhealth_no'],
                'tin_no' => $c1_form_data_db['tin_no'],
                'agency_employee_no' => $c1_form_data_db['agency_employee_no'],
                'gsis_id_no' => $c1_form_data_db['gsis_id_no'],
                'pagibig_id_no' => $c1_form_data_db['pagibig_id_no'],
                'sss_id_no' => $c1_form_data_db['sss_id_no'],
                'citizenship' => $c1_form_data_db['citizenship'],
                'dual_type' => $dual_type_t,
                'dual_country' => $c1_form_data_db['dual_country'] ?? null,
                'residential_address' => $formatted_residential_address,
                'permanent_address' => $formatted_permanent_address,
                'telephone_no' => $c1_form_data_db['telephone_no'],
                'mobile_no' => $c1_form_data_db['mobile_no'],
                'email_address' => $c1_form_data_db['email_address']
            ]
        );

        // Save FamilyBackground
        Models\FamilyBackground::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'spouse_surname' => $c1_form_data_db['spouse_surname'] ?? null,
                'spouse_first_name' => $c1_form_data_db['spouse_first_name'] ?? null,
                'spouse_middle_name' => $c1_form_data_db['spouse_middle_name'] ?? null,
                'spouse_name_extension' => $c1_form_data_db['spouse_name_extension'] ?? null,
                'spouse_occupation' => $c1_form_data_db['spouse_occupation'] ?? null,
                'spouse_employer' => $c1_form_data_db['spouse_employer'] ?? null,
                'spouse_business_address' => $c1_form_data_db['spouse_business_address'] ?? null,
                'spouse_telephone' => $c1_form_data_db['spouse_telephone'] ?? null,
                'father_surname' => $c1_form_data_db['father_surname'] ?? null,
                'father_first_name' => $c1_form_data_db['father_first_name'] ?? null,
                'father_middle_name' => $c1_form_data_db['father_middle_name'] ?? null,
                'father_name_extension' => $c1_form_data_db['father_name_extension'] ?? null,
                'mother_maiden_surname' => $c1_form_data_db['mother_maiden_surname'] ?? null,
                'mother_maiden_first_name' => $c1_form_data_db['mother_maiden_first_name'] ?? null,
                'mother_maiden_middle_name' => $c1_form_data_db['mother_maiden_middle_name'] ?? null,
                'children_info' => $c1_form_data_db['children'] ?? null
            ]
        );

        // Determine if this is Senior High School data
        $isSeniorHigh = strtoupper(trim((string) ($c1_form_data_db['jhs_basic'] ?? ''))) === 'SENIOR HIGH SCHOOL';
        $elemFromDb = $this->normalizeStrictDateForDatabase($c1_form_data_db['elem_from'] ?? null);
        $elemToDb = $this->normalizeStrictDateForDatabase($c1_form_data_db['elem_to'] ?? null);
        $jhsFromDb = $this->normalizeStrictDateForDatabase($c1_form_data_db['jhs_from'] ?? null);
        $jhsToDb = $this->normalizeStrictDateForDatabase($c1_form_data_db['jhs_to'] ?? null);
        $elemFromDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data_db['elem_from'] ?? null);
        $elemToDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data_db['elem_to'] ?? null);
        $jhsFromDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data_db['jhs_from'] ?? null);
        $jhsToDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data_db['jhs_to'] ?? null);

        // Save EducationalBackground
        Models\EducationalBackground::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'elem_from' => $elemFromDb,
                'elem_to' => $elemToDb,
                'elem_school' => $c1_form_data_db['elem_school'],
                'elem_academic_honors' => $c1_form_data_db['elem_academic_honors'],
                'elem_basic' => $c1_form_data_db['elem_basic'],
                'elem_earned' => $c1_form_data_db['elem_earned'],
                'elem_year_graduated' => $c1_form_data_db['elem_year_graduated'],
                'jhs_from' => $isSeniorHigh ? null : $jhsFromDb,
                'jhs_to' => $isSeniorHigh ? null : $jhsToDb,
                'jhs_school' => $isSeniorHigh ? '' : $c1_form_data_db['jhs_school'],
                'jhs_academic_honors' => $isSeniorHigh ? '' : $c1_form_data_db['jhs_academic_honors'],
                'jhs_basic' => $isSeniorHigh ? '' : $c1_form_data_db['jhs_basic'],
                'jhs_earned' => $isSeniorHigh ? '' : $c1_form_data_db['jhs_earned'],
                'jhs_year_graduated' => $isSeniorHigh ? '' : $c1_form_data_db['jhs_year_graduated'],
                'shs_from' => $isSeniorHigh ? $jhsFromDb : null,
                'shs_to' => $isSeniorHigh ? $jhsToDb : null,
                'shs_school' => $isSeniorHigh ? $c1_form_data_db['jhs_school'] : '',
                'shs_academic_honors' => $isSeniorHigh ? $c1_form_data_db['jhs_academic_honors'] : '',
                'shs_basic' => $isSeniorHigh ? $c1_form_data_db['jhs_basic'] : '',
                'shs_earned' => $isSeniorHigh ? $c1_form_data_db['jhs_earned'] : '',
                'shs_year_graduated' => $isSeniorHigh ? $c1_form_data_db['jhs_year_graduated'] : '',
                'vocational' => $c1_form_data_db['vocational'] ?? null,
                'college' => $c1_form_data_db['college'] ?? null,
                'grad' => $c1_form_data_db['grad'] ?? null,
            ]
        );

        try {
            $this->queueCollegeProgramSuggestions($c1_form_data_db['college'] ?? []);
            $this->queueGraduateProgramSuggestions($c1_form_data_db['grad'] ?? []);
        } catch (\Throwable $e) {
            Log::warning('Unable to queue program suggestions from PDS submission.', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }

        activity()
            ->causedBy(Auth::user())
            ->log('Updated C1 form session and database.');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        //dd(session('form.c1'));
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);
    }

    private function extractC1DataFromExcel(string $uploadedPath): array
    {
        $spreadsheet = IOFactory::load($uploadedPath);
        $c1Sheet = $spreadsheet->getSheetByName('C1');
        $c2Sheet = $spreadsheet->getSheetByName('C2');
        $c3Sheet = $spreadsheet->getSheetByName('C3');
        $c4Sheet = $spreadsheet->getSheetByName('C4');

        if (!$c1Sheet || !$c2Sheet || !$c3Sheet || !$c4Sheet) {
            throw new \RuntimeException('Incompatible file: expected sheets C1, C2, C3, and C4 were not found.');
        }

        $markerCells = [
            'A3' => 'PERSONAL DATA SHEET',
            'A9' => 'I. PERSONAL INFORMATION',
            'A35' => 'II.  FAMILY BACKGROUND',
        ];
        foreach ($markerCells as $cell => $expected) {
            $actual = $this->normalizedExcelText((string) $c1Sheet->getCell($cell)->getFormattedValue());
            if ($actual !== $this->normalizedExcelText($expected)) {
                throw new \RuntimeException('Incompatible file: please upload the official CS Form No. 212 Revised 2025 Excel template.');
            }
        }

        $cellToFieldMap = [
            'D10' => 'surname', 'D11' => 'first_name', 'D12' => 'middle_name', 'L11' => 'name_extension',
            'D15' => 'place_of_birth', 'J16' => 'dual_country', 'D22' => 'height', 'D24' => 'weight',
            'D25' => 'blood_type', 'D27' => 'gsis_id_no', 'D29' => 'pagibig_id_no', 'D31' => 'philhealth_no',
            'D32' => 'sss_id_no', 'D33' => 'tin_no', 'D34' => 'agency_employee_no', 'I17' => 'res_house_no',
            'L17' => 'res_street', 'I20' => 'res_sub_vil', 'L20' => 'res_brgy', 'I22' => 'res_city',
            'L22' => 'res_province', 'I24' => 'res_zipcode', 'I25' => 'per_house_no', 'L25' => 'per_street',
            'I27' => 'per_sub_vil', 'L27' => 'per_brgy', 'I29' => 'per_city', 'L29' => 'per_province',
            'I31' => 'per_zipcode', 'I32' => 'telephone_no', 'I33' => 'mobile_no', 'I34' => 'email_address',
            'D36' => 'spouse_surname', 'D37' => 'spouse_first_name', 'G37' => 'spouse_name_extension',
            'D38' => 'spouse_middle_name', 'D39' => 'spouse_occupation', 'D40' => 'spouse_employer',
            'D41' => 'spouse_business_address', 'D42' => 'spouse_telephone', 'D43' => 'father_surname',
            'D44' => 'father_first_name', 'G44' => 'father_name_extension', 'D45' => 'father_middle_name',
            'D47' => 'mother_maiden_surname', 'D48' => 'mother_maiden_first_name', 'D49' => 'mother_maiden_middle_name',
            'D54' => 'elem_school', 'G54' => 'elem_basic', 'L54' => 'elem_earned', 'M54' => 'elem_year_graduated',
            'N54' => 'elem_academic_honors', 'D55' => 'jhs_school', 'G55' => 'jhs_basic', 'L55' => 'jhs_earned',
            'M55' => 'jhs_year_graduated', 'N55' => 'jhs_academic_honors',
        ];

        $fields = [];
        foreach ($cellToFieldMap as $cell => $field) {
            $fields[$field] = $this->readCellText($c1Sheet, $cell);
        }
        $c1CheckboxStates = $this->readVmlCheckboxStates($uploadedPath, 'xl/drawings/vmlDrawing1.vml');
        $fields['date_of_birth'] = $this->readCellDate($c1Sheet, 'D13', 'd-m-Y');
        $fields['elem_from'] = $this->readCellDate($c1Sheet, 'J54', 'd-m-Y', true);
        $fields['elem_to'] = $this->readCellDate($c1Sheet, 'K54', 'd-m-Y', true);
        $fields['jhs_from'] = $this->readCellDate($c1Sheet, 'J55', 'd-m-Y', true);
        $fields['jhs_to'] = $this->readCellDate($c1Sheet, 'K55', 'd-m-Y', true);
        $fields['sex'] = $this->sexFromC1Checkboxes($c1CheckboxStates) ?: $this->normalizeSex($this->readCellText($c1Sheet, 'D16'));
        $fields['civil_status'] = $this->civilStatusFromC1Checkboxes($c1CheckboxStates) ?: $this->normalizeCivilStatus($this->readCellText($c1Sheet, 'D17'));
        $fields['citizenship'] = $this->citizenshipFromC1Checkboxes($c1CheckboxStates) ?: $this->normalizeCitizenship($this->readCellText($c1Sheet, 'J13'));
        $fields['blood_type'] = strtoupper(trim((string) ($fields['blood_type'] ?? '')));
        $fields['dual_type'] = $this->dualTypeFromC1Checkboxes($c1CheckboxStates);
        foreach ([
            'res_house_no', 'res_street', 'res_sub_vil', 'res_brgy', 'res_city', 'res_province', 'res_zipcode',
            'per_house_no', 'per_street', 'per_sub_vil', 'per_brgy', 'per_city', 'per_province', 'per_zipcode',
        ] as $addressField) {
            $fields[$addressField] = $this->normalizeAddressFromExcel($fields[$addressField] ?? '');
        }
        if ($fields['citizenship'] !== 'Dual Citizenship') {
            $fields['dual_country'] = '';
            $fields['dual_type'] = '';
        }

        $children = [];
        for ($i = 0; $i < 12; $i++) {
            $row = 37 + $i;
            $name = $this->readCellText($c1Sheet, "I{$row}");
            $dob = $this->readCellDate($c1Sheet, "M{$row}", 'd-m-Y');
            if ($name !== '' || $dob !== '') {
                $children[] = ['name' => $name, 'dob' => $dob];
            }
        }

        $vocationalRow = [
            'from' => $this->readCellDate($c1Sheet, 'J56', 'd-m-Y', true),
            'to' => $this->readCellDate($c1Sheet, 'K56', 'd-m-Y', true),
            'school' => $this->readCellText($c1Sheet, 'D56'),
            'basic' => $this->readCellText($c1Sheet, 'G56'),
            'earned' => $this->readCellText($c1Sheet, 'L56'),
            'year_graduated' => $this->readCellText($c1Sheet, 'M56'),
            'academic_honors' => $this->readCellText($c1Sheet, 'N56'),
        ];
        $collegeRow = [
            'from' => $this->readCellDate($c1Sheet, 'J57', 'd-m-Y', true),
            'to' => $this->readCellDate($c1Sheet, 'K57', 'd-m-Y', true),
            'school' => $this->readCellText($c1Sheet, 'D57'),
            'basic' => $this->readCellText($c1Sheet, 'G57'),
            'earned' => $this->readCellText($c1Sheet, 'L57'),
            'year_graduated' => $this->readCellText($c1Sheet, 'M57'),
            'academic_honors' => $this->readCellText($c1Sheet, 'N57'),
        ];
        $gradRow = [
            'from' => $this->readCellDate($c1Sheet, 'J58', 'd-m-Y', true),
            'to' => $this->readCellDate($c1Sheet, 'K58', 'd-m-Y', true),
            'school' => $this->readCellText($c1Sheet, 'D58'),
            'basic' => $this->readCellText($c1Sheet, 'G58'),
            'earned' => $this->readCellText($c1Sheet, 'L58'),
            'year_graduated' => $this->readCellText($c1Sheet, 'M58'),
            'academic_honors' => $this->readCellText($c1Sheet, 'N58'),
        ];

        $allCivilService = [];
        for ($i = 0; $i < 7; $i++) {
            $row = 5 + $i;
            $entry = [
                'user_id' => Auth::id(),
                'cs_eligibility_career' => $this->readCellText($c2Sheet, "B{$row}"),
                'cs_eligibility_rating' => $this->readCellText($c2Sheet, "F{$row}"),
                'cs_eligibility_date' => $this->readCellDate($c2Sheet, "G{$row}", 'Y-m-d'),
                'cs_eligibility_place' => $this->readCellText($c2Sheet, "I{$row}"),
                'cs_eligibility_license' => $this->readCellText($c2Sheet, "J{$row}"),
                'cs_eligibility_validity' => $this->readCellDate($c2Sheet, "K{$row}", 'Y-m-d'),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $allCivilService[] = $entry;
            }
        }

        $allWorkExp = [];
        for ($i = 0; $i < 28; $i++) {
            $row = 18 + $i;
            $entry = [
                'user_id' => Auth::id(),
                'work_exp_from' => $this->readCellDate($c2Sheet, "A{$row}", 'Y-m-d'),
                'work_exp_to' => $this->readCellDate($c2Sheet, "C{$row}", 'Y-m-d'),
                'work_exp_position' => $this->readCellText($c2Sheet, "D{$row}"),
                'work_exp_department' => $this->readCellText($c2Sheet, "G{$row}"),
                'work_exp_status' => $this->normalizeWorkStatus($this->readCellText($c2Sheet, "J{$row}")),
                'work_exp_govt_service' => $this->normalizeGovServiceFlag($this->readCellText($c2Sheet, "K{$row}")),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $allWorkExp[] = $entry;
            }
        }

        $dataVoluntary = [];
        for ($i = 0; $i < 7; $i++) {
            $row = 6 + $i;
            $entry = [
                'voluntary_org' => $this->readCellText($c3Sheet, "B{$row}"),
                'voluntary_from' => $this->readCellDate($c3Sheet, "E{$row}", 'Y-m-d'),
                'voluntary_to' => $this->readCellDate($c3Sheet, "F{$row}", 'Y-m-d'),
                'voluntary_hours' => $this->readCellText($c3Sheet, "G{$row}"),
                'voluntary_position' => $this->readCellText($c3Sheet, "H{$row}"),
                'user_id' => Auth::id(),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $dataVoluntary[] = $entry;
            }
        }

        $dataLearning = [];
        for ($i = 0; $i < 21; $i++) {
            $row = 18 + $i;
            $entry = [
                'learning_title' => $this->readCellText($c3Sheet, "B{$row}"),
                'learning_from' => $this->readCellDate($c3Sheet, "E{$row}", 'Y-m-d'),
                'learning_to' => $this->readCellDate($c3Sheet, "F{$row}", 'Y-m-d'),
                'learning_hours' => $this->readCellText($c3Sheet, "G{$row}"),
                'learning_type' => $this->readCellText($c3Sheet, "H{$row}"),
                'learning_conducted' => $this->readCellText($c3Sheet, "I{$row}"),
                'user_id' => Auth::id(),
            ];
            if ($this->rowHasData($entry, ['user_id'])) {
                $dataLearning[] = $entry;
            }
        }

        $skills = [];
        $distinctions = [];
        $organizations = [];
        for ($i = 0; $i < 7; $i++) {
            $row = 42 + $i;
            $skill = $this->readCellText($c3Sheet, "B{$row}");
            $dist = $this->readCellText($c3Sheet, "C{$row}");
            $org = $this->readCellText($c3Sheet, "I{$row}");
            if ($skill !== '') {
                $skills[] = $skill;
            }
            if ($dist !== '') {
                $distinctions[] = $dist;
            }
            if ($org !== '') {
                $organizations[] = $org;
            }
        }

        $c4CheckboxStates = $this->readVmlCheckboxStates($uploadedPath, 'xl/drawings/vmlDrawing2.vml');
        $related34A = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4097, 4098, 'I6', 'K6');
        $related34B = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4099, 4100, 'I8', 'K8', 'H11');
        $guilty35A = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4101, 4102, 'I13', 'K13', 'H15');
        $criminal35B = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4103, 4104, 'I18', 'K18');
        $convicted36 = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4105, 4106, 'I23', 'K23', 'H25');
        $separated37 = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4107, 4108, 'I27', 'K27', 'H29');
        $candidate38 = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4122, 4123, 'I31', 'K31', 'K32');
        $resigned38B = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4124, 4125, 'I34', 'K34', 'K35');
        $immigrant39 = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4109, 4110, 'I37', 'K37', 'H39');
        $indigenous40A = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4111, 4114, 'I43', 'K43', 'L44');
        $pwd40B = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4112, 4115, 'I45', 'K45', 'L46');
        $soloParent40C = $this->readYesNoFromTemplateControls($c4Sheet, $c4CheckboxStates, 4113, 4116, 'I47', 'K47', 'L48');

        $criminalDate = $this->readCellDate($c4Sheet, 'K20', 'Y-m-d');
        $criminalStatus = $this->readCellText($c4Sheet, 'K21');
        $criminal35BValue = 'no';
        $criminal35BArray = ['date' => '', 'status' => ''];
        if ($criminal35B === 'yes') {
            $criminal35BValue = trim($criminalDate . ',' . $criminalStatus, ',');
            $criminal35BArray = ['date' => $criminalDate, 'status' => $criminalStatus];
        }

        $govtType = $this->normalizeGovtIdType($this->readCellText($c4Sheet, 'B61'));
        $govtPlaceAndDate = $this->readCellText($c4Sheet, 'B64');
        $govtPlaceIssued = '';
        $govtDateIssued = '';
        if ($govtPlaceAndDate !== '') {
            $parts = array_map('trim', explode('|', $govtPlaceAndDate, 2));
            if (count($parts) === 2) {
                $firstAsDate = $this->normalizeDateString($parts[0], 'Y-m-d');
                $secondAsDate = $this->normalizeDateString($parts[1], 'Y-m-d');

                if ($firstAsDate !== '' && $secondAsDate === '') {
                    // New export order: date | place
                    $govtDateIssued = $firstAsDate;
                    $govtPlaceIssued = $parts[1];
                } elseif ($secondAsDate !== '' && $firstAsDate === '') {
                    // Backward compatibility: place | date
                    $govtPlaceIssued = $parts[0];
                    $govtDateIssued = $secondAsDate;
                } else {
                    // Ambiguous fallback keeps prior behavior.
                    $govtPlaceIssued = $parts[0];
                    $govtDateIssued = $secondAsDate;
                }
            } else {
                $govtPlaceIssued = $govtPlaceAndDate;
            }
        }

        $c4Data = [
            'related_34_a' => $related34A,
            'related_34_b' => $related34B === 'yes' ? $this->readCellText($c4Sheet, 'H11') : 'no',
            'guilty_35_a' => $guilty35A === 'yes' ? $this->readCellText($c4Sheet, 'H15') : 'no',
            'criminal_35_b' => $criminal35BValue,
            'criminal_35_b_array' => $criminal35BArray,
            'convicted_36' => $convicted36 === 'yes' ? $this->readCellText($c4Sheet, 'H25') : 'no',
            'separated_37' => $separated37 === 'yes' ? $this->readCellText($c4Sheet, 'H29') : 'no',
            'candidate_38' => $candidate38 === 'yes' ? $this->readCellText($c4Sheet, 'K32') : 'no',
            'resigned_38_b' => $resigned38B === 'yes' ? $this->readCellText($c4Sheet, 'K35') : 'no',
            'immigrant_39' => $immigrant39 === 'yes' ? $this->readCellText($c4Sheet, 'H39') : 'no',
            'indigenous_40_a' => $indigenous40A === 'yes' ? $this->readCellText($c4Sheet, 'L44') : 'no',
            'pwd_40_b' => $pwd40B === 'yes' ? $this->readCellText($c4Sheet, 'L46') : 'no',
            'solo_parent_40_c' => $soloParent40C === 'yes' ? $this->readCellText($c4Sheet, 'L48') : 'no',
            'ref1_name' => $this->readCellText($c4Sheet, 'A52'),
            'ref1_tel' => $this->readCellText($c4Sheet, 'G52'),
            'ref1_address' => $this->readCellText($c4Sheet, 'F52'),
            'ref2_name' => $this->readCellText($c4Sheet, 'A53'),
            'ref2_tel' => $this->readCellText($c4Sheet, 'G53'),
            'ref2_address' => $this->readCellText($c4Sheet, 'F53'),
            'ref3_name' => $this->readCellText($c4Sheet, 'A54'),
            'ref3_tel' => $this->readCellText($c4Sheet, 'G54'),
            'ref3_address' => $this->readCellText($c4Sheet, 'F54'),
            'govt_id_type' => $govtType['type'],
            'govt_id_other' => $govtType['other'],
            'govt_id_number' => $this->readCellText($c4Sheet, 'B62'),
            'govt_id_date_issued' => $govtDateIssued,
            'govt_id_place_issued' => $govtPlaceIssued,
            'photo_upload' => null,
        ];

        $warnings = [];
        if (($fields['citizenship'] ?? '') === 'Dual Citizenship' && ($fields['dual_type'] ?? '') === '') {
            $warnings[] = 'Dual citizenship type (By Birth / By Naturalization) was not detected from the Excel checkboxes.';
        }
        if (empty($dataLearning) && empty($dataVoluntary) && empty($skills) && empty($distinctions) && empty($organizations)) {
            $warnings[] = 'C3 sheet appears to have no importable entries.';
        }

        return [
            'c1' => [
                'fields' => $fields,
                'children' => $children,
                'vocational' => $this->rowHasData($vocationalRow) ? [$vocationalRow] : [],
                'college' => $this->rowHasData($collegeRow) ? [$collegeRow] : [],
                'grad' => $this->rowHasData($gradRow) ? [$gradRow] : [],
            ],
            'c2' => [
                'all_user_work_exps' => $allWorkExp,
                'all_user_civil_service_eligibility' => $allCivilService,
            ],
            'c3' => [
                'data_learning' => $dataLearning,
                'data_voluntary' => $dataVoluntary,
                'data_otherInfo' => [
                    'skill' => $skills,
                    'distinction' => $distinctions,
                    'organization' => $organizations,
                    'user_id' => Auth::id(),
                ],
            ],
            'c4' => $c4Data,
            'warnings' => $warnings,
            'missing_report' => $this->buildExcelCoverageReport(),
        ];
    }

    private function readVmlCheckboxStates(string $xlsxPath, string $entryName): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            return [];
        }

        try {
            if ($zip->locateName($entryName) === false) {
                return [];
            }

            $xml = $zip->getFromName($entryName);
            if (!is_string($xml) || trim($xml) === '') {
                return [];
            }

            $dom = new \DOMDocument();
            if (!@$dom->loadXML($xml, LIBXML_NONET)) {
                return [];
            }

            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');
            $xpath->registerNamespace('x', 'urn:schemas-microsoft-com:office:excel');
            $xpath->registerNamespace('o', 'urn:schemas-microsoft-com:office:office');

            $states = [];
            $nodes = $xpath->query("//v:shape[x:ClientData[@ObjectType='Checkbox']]");
            if (!$nodes) {
                return [];
            }

            foreach ($nodes as $shapeNode) {
                $shapeId = (string) $shapeNode->getAttribute('id');
                $spid = (string) $shapeNode->getAttributeNS('urn:schemas-microsoft-com:office:office', 'spid');
                if ($shapeId === '' && $spid === '') {
                    continue;
                }

                $numericId = null;
                if (preg_match('/_x0000_s(\d+)$/', $shapeId, $matches)) {
                    $numericId = (int) $matches[1];
                }
                if ($numericId === null && preg_match('/_x0000_s(\d+)$/', $spid, $matches)) {
                    $numericId = (int) $matches[1];
                }

                $clientDataNode = $xpath->query("./x:ClientData[@ObjectType='Checkbox']", $shapeNode)->item(0);
                if (!$clientDataNode) {
                    continue;
                }
                $isChecked = $xpath->query("./x:Checked", $clientDataNode)->length > 0;

                if ($shapeId !== '') {
                    $states[$shapeId] = $isChecked;
                }
                if ($spid !== '') {
                    $states[$spid] = $isChecked;
                }
                if ($numericId !== null) {
                    $states[$numericId] = $isChecked;
                }
            }

            return $states;
        } finally {
            $zip->close();
        }
    }

    private function shapeChecked(array $states, int $shapeId): bool
    {
        return (bool) ($states[$shapeId] ?? false);
    }

    private function sexFromC1Checkboxes(array $states): string
    {
        if ($this->shapeChecked($states, 1049)) {
            return 'male';
        }
        if ($this->shapeChecked($states, 1050)) {
            return 'female';
        }
        return '';
    }

    private function civilStatusFromC1Checkboxes(array $states): string
    {
        if ($this->shapeChecked($states, 1058)) {
            return 'single';
        }
        if ($this->shapeChecked($states, 1059)) {
            return 'married';
        }
        if ($this->shapeChecked($states, 1060)) {
            return 'widowed';
        }
        if ($this->shapeChecked($states, 1062)) {
            return 'separated';
        }
        if ($this->shapeChecked($states, 1061)) {
            return 'other';
        }
        return '';
    }

    private function citizenshipFromC1Checkboxes(array $states): string
    {
        if ($this->shapeChecked($states, 1046)) {
            return 'Dual Citizenship';
        }
        if ($this->shapeChecked($states, 1045)) {
            return 'Filipino';
        }
        return '';
    }

    private function dualTypeFromC1Checkboxes(array $states): string
    {
        if ($this->shapeChecked($states, 1063)) {
            return 'by birth';
        }
        if ($this->shapeChecked($states, 1064)) {
            return 'by naturalization';
        }
        return '';
    }

    private function formatAddressForAnnexExport($value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || $text === '{*}' || strtolower($text) === 'null') {
            return 'N/A';
        }
        return $text;
    }

    private function formatAnnexDisplayValue($value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || $text === '{*}' || strtolower($text) === 'null') {
            return 'N/A';
        }
        return $text;
    }

    private function normalizeAddressFromExcel($value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || strtolower($text) === 'n/a' || strtolower($text) === 'na' || strtolower($text) === 'null' || $text === '{*}') {
            return '';
        }
        return $text;
    }

    private function readCellText($sheet, string $cell): string
    {
        $text = $this->sanitizeExtractedText(trim((string) $sheet->getCell($cell)->getFormattedValue()));
        return $this->normalizeAddressFromExcel($text);
    }

    private function readCellDate($sheet, string $cell, string $outputFormat = 'd-m-Y', bool $monthYearOnly = false): string
    {
        $uploadedCell = $sheet->getCell($cell);
        $raw = $uploadedCell->getValue();
        $asText = trim((string) $uploadedCell->getFormattedValue());
        if ($asText === '' && ($raw === null || $raw === '')) {
            return '';
        }

        try {
            if (is_numeric($raw)) {
                $date = ExcelDate::excelToDateTimeObject((float) $raw);
                if ($monthYearOnly) {
                    $date->modify('first day of this month');
                }
                return $date->format($outputFormat);
            }

            $candidate = trim((string) $raw);
            if ($candidate === '') {
                return '';
            }

            return $this->normalizeDateString($candidate, $outputFormat, $monthYearOnly);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function normalizeDateString(string $value, string $outputFormat = 'd-m-Y', bool $monthYearOnly = false): string
    {
        $candidate = trim($value);
        if ($candidate === '') {
            return '';
        }

        $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'Y-m-d', 'm/Y', 'm-Y', 'Y-m'];
        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $candidate);
                if ($dt !== false) {
                    if ($monthYearOnly || in_array($format, ['m/Y', 'm-Y', 'Y-m'], true)) {
                        $dt->startOfMonth();
                    }
                    return $dt->format($outputFormat);
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            $dt = Carbon::parse($candidate);
            if ($monthYearOnly) {
                $dt->startOfMonth();
            }
            return $dt->format($outputFormat);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function readYesNo($sheet, string $yesCell, string $noCell, ?string $detailCell = null): string
    {
        $yesRaw = $this->normalizedExcelText((string) $sheet->getCell($yesCell)->getFormattedValue());
        $noRaw = $this->normalizedExcelText((string) $sheet->getCell($noCell)->getFormattedValue());

        $yesMarked = $this->isMarkedCellValue($yesRaw);
        $noMarked = $this->isMarkedCellValue($noRaw);

        if ($yesMarked && !$noMarked) {
            return 'yes';
        }
        if ($noMarked && !$yesMarked) {
            return 'no';
        }

        if ($detailCell) {
            $detail = $this->readCellText($sheet, $detailCell);
            if ($detail !== '') {
                return 'yes';
            }
        }

        return 'no';
    }

    private function readYesNoFromTemplateControls(
        $sheet,
        array $states,
        int $yesShapeId,
        int $noShapeId,
        string $yesCell,
        string $noCell,
        ?string $detailCell = null
    ): string {
        $hasYes = array_key_exists($yesShapeId, $states);
        $hasNo = array_key_exists($noShapeId, $states);

        if ($hasYes || $hasNo) {
            $yesChecked = $this->shapeChecked($states, $yesShapeId);
            $noChecked = $this->shapeChecked($states, $noShapeId);

            if ($yesChecked && !$noChecked) {
                return 'yes';
            }
            if ($noChecked && !$yesChecked) {
                return 'no';
            }
            if ($yesChecked) {
                return 'yes';
            }
            if ($noChecked) {
                return 'no';
            }
        }

        return $this->readYesNo($sheet, $yesCell, $noCell, $detailCell);
    }

    private function isMarkedCellValue(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }
        return in_array($trimmed, ['X', '/', 'YES', 'TRUE', '1'], true);
    }

    private function sanitizeExtractedText(string $value): string
    {
        $text = trim($value);
        if ($text === '') {
            return '';
        }

        $placeholders = [
            'House/Block/Lot No.',
            'Street',
            'Subdivision/Village',
            'Barangay',
            'City/Municipality',
            'Province',
            'ZIP Code',
            'Government Issued ID:',
            'ID/License/Passport No.:',
            'Date/Place of Issuance:',
            'Date Filed:',
            'Status of Case/s:',
            'If YES, give details:',
            'If YES, please specify:',
            'If YES, please specify ID No:',
            'If YES, give details (country):',
        ];

        foreach ($placeholders as $placeholder) {
            if (strcasecmp($text, $placeholder) === 0) {
                return '';
            }
        }

        if (preg_match('/^If YES, give details/i', $text)) {
            return '';
        }

        return $text;
    }

    private function normalizeSex(string $value): string
    {
        $normalized = strtolower(trim($value));
        if (str_starts_with($normalized, 'm')) {
            return 'male';
        }
        if (str_starts_with($normalized, 'f')) {
            return 'female';
        }
        return '';
    }

    private function normalizeCivilStatus(string $value): string
    {
        $normalized = strtolower(trim($value));
        return match ($normalized) {
            'single' => 'single',
            'married' => 'married',
            'widowed', 'widower' => 'widowed',
            'separated', 'seperated' => 'separated',
            'other', 'others', 'other/s' => 'other',
            default => '',
        };
    }

    private function normalizeCitizenship(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }
        if (str_contains($normalized, 'dual')) {
            return 'Dual Citizenship';
        }
        if (str_contains($normalized, 'filipino')) {
            return 'Filipino';
        }
        return '';
    }

    private function normalizedExcelText(string $value): string
    {
        return preg_replace('/\s+/', ' ', strtoupper(trim($value))) ?? '';
    }

    private function rowHasData(array $row, array $excludeKeys = []): bool
    {
        foreach ($row as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }
            if (trim((string) $value) !== '') {
                return true;
            }
        }
        return false;
    }

    private function normalizeListData($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn($item) => trim((string) $item),
                $value
            ), static fn($item) => $item !== ''));
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return $this->normalizeListData($decoded);
            }
            return [$trimmed];
        }

        return [];
    }

    private function normalizeWorkStatus(string $value): string
    {
        $normalized = strtolower(trim($value));
        return match ($normalized) {
            'permanent' => 'Permanent',
            'temporary' => 'Temporary',
            'casual' => 'Casual',
            'contractual' => 'Contractual',
            default => '',
        };
    }

    private function normalizeGovServiceFlag(string $value): string
    {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['y', 'yes', 'true', '1'], true)) {
            return 'Y';
        }
        if (in_array($normalized, ['n', 'no', 'false', '0'], true)) {
            return 'N';
        }
        return '';
    }

    private function normalizeGovtIdType(string $value): array
    {
        $raw = trim($value);
        $normalized = strtolower($raw);

        $map = [
            'passport' => 'Passport',
            'gsis' => 'GSIS',
            'sss' => 'SSS',
            'philhealth' => 'PhilHealth',
            "driver's license" => "Driver's License",
            'drivers license' => "Driver's License",
            'prc' => 'PRC',
            "voter's id" => "Voter's ID",
            'voters id' => "Voter's ID",
            'philsys/national id' => 'PhilSys/National ID',
            'philsys national id' => 'PhilSys/National ID',
            'national id' => 'PhilSys/National ID',
        ];

        if (isset($map[$normalized])) {
            return ['type' => $map[$normalized], 'other' => ''];
        }

        if ($raw === '') {
            return ['type' => '', 'other' => ''];
        }

        return ['type' => 'other', 'other' => $raw];
    }

    private function buildExcelCoverageReport(): array
    {
        return [
            'mapped_sections' => ['c1', 'c2', 'c3', 'c4'],
            'missing_in_excel_template' => [
                'c1' => [
                    'cs_id_no (CSC use only)',
                    'dual_type (By Birth / By Naturalization)',
                ],
                'c2' => [],
                'c3' => [],
                'c4' => ['photo_upload'],
                'wes' => [
                    'entries[*].start_date',
                    'entries[*].end_date',
                    'entries[*].position',
                    'entries[*].office',
                    'entries[*].supervisor',
                    'entries[*].agency',
                    'entries[*].accomplishments[*]',
                    'entries[*].duties[*]',
                    'entries[*].isDisplayed',
                ],
            ],
            'notes' => [
                'WES is not part of ANNEX H-1 workbook and cannot be auto-populated from this file.',
                'Only first 7 voluntary/skills rows and first 21 L&D rows are supported by the Excel template.',
                'Only first 7 civil service rows and first 28 work experience rows are supported by the Excel template.',
            ],
        ];
    }


    /**
     * Update the C1 session data based on the database. If there is no data on the database,
     * the function should return an empty array.
     *
     * @return array{all_user_civil_service_eligibility: array, all_user_work_exps: array}
     */
    private function c2GetFormFromDB()
    {

        $current_user_id = Auth::id();
        $all_user_work_exps = WorkExperience::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        /**NOTE:
         * $user_work_exps is a multidimensional array with the format:
         *
         * [ [index1 => [user_work_experience_record1]], [index2 => [user_work_experience_record2]], ... ]
         *
         * a user work experience record have all the attributes stated in its migration file
         * @see yyyy_mm_dd_create_work_experiences_table.php
         *
         * ** This is also true for the variable $civil_service_eligibility below....
         */

        $all_user_civil_service_eligibility = CivilServiceEligibility::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        $c2_full_info = [
            'all_user_work_exps' => $all_user_work_exps,
            'all_user_civil_service_eligibility' => $all_user_civil_service_eligibility
        ];

        return $c2_full_info;
    }


    /**
     * displays C2 page with all session data.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function c2DisplayForm(Request $request)
    {

        // Run if session does not exists. get data from database. if database has no data
        // return an empty array.
        if (!session()->has('form.c2')) {
            session(['form.c2' => $this->c2GetFormFromDB()]);
        }

        // Ensure C1 data is loaded for education level checking
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        }

        // Run if session exists
        $all_user_work_exps = session('form.c2.all_user_work_exps');
        $all_user_civil_service_eligibility = session('form.c2.all_user_civil_service_eligibility');

        // Determine education level for eligibility filtering
        $has_college_degree = $this->hasC2CollegeDegree($request);
        $is_high_school_only = $this->isC2HighSchoolOnlyApplicant($request);
        $is_elementary_only = $this->isC2ElementaryOnlyApplicant($request);

        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Viewed C2 form.');
        */
        return view('pds.c2', compact(
            'all_user_work_exps',
            'all_user_civil_service_eligibility',
            'has_college_degree',
            'is_high_school_only',
            'is_elementary_only'
        ));
    }

    private function normalizeEligibilityNameForComparison($value): string
    {
        $normalized = trim((string) ($value ?? ''));
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        return strtolower(trim($normalized));
    }

    private function hasMeaningfulC1EducationText(mixed $value): bool
    {
        $normalized = trim((string) ($value ?? ''));
        if ($normalized === '') {
            return false;
        }

        $upper = strtoupper($normalized);
        return $upper !== 'NOINPUT' && $upper !== 'N/A' && $upper !== 'NA';
    }

    private function hasMeaningfulC1EducationEntries(mixed $entries): bool
    {
        if (!is_array($entries)) {
            return false;
        }

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            // Check multiple fields for meaningful data
            $fieldsToCheck = ['basic', 'school', 'from', 'to', 'year_graduated', 'earned'];
            foreach ($fieldsToCheck as $field) {
                if ($this->hasMeaningfulC1EducationText($entry[$field] ?? null)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isC2HighSchoolOnlyApplicant(Request $request): bool
    {
        $c1 = (array) $request->session()->get('form.c1', []);

        $hasSecondary = $this->hasMeaningfulC1EducationText($c1['jhs_basic'] ?? null)
            || $this->hasMeaningfulC1EducationText($c1['shs_basic'] ?? null)
            || $this->hasMeaningfulC1EducationText($c1['jhs_school'] ?? null)
            || $this->hasMeaningfulC1EducationText($c1['shs_school'] ?? null);

        $hasHigherEducation = $this->hasMeaningfulC1EducationEntries($c1['college'] ?? null)
            || $this->hasMeaningfulC1EducationEntries($c1['grad'] ?? null)
            || $this->hasMeaningfulC1EducationEntries($c1['vocational'] ?? null);

        return $hasSecondary && !$hasHigherEducation;
    }

    private function hasC2CollegeDegree(Request $request): bool
    {
        $c1 = (array) $request->session()->get('form.c1', []);

        // Check for graduate studies (any graduate level education implies college completion)
        if ($this->hasMeaningfulC1EducationEntries($c1['grad'] ?? null)) {
            return true;
        }

        // Check for college entries
        $college = $c1['college'] ?? null;
        if (is_array($college)) {
            foreach ($college as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                // Check if this entry has any meaningful data
                $hasMeaningfulData = false;
                $fieldsToCheck = ['basic', 'school', 'from', 'to', 'year_graduated', 'earned'];
                foreach ($fieldsToCheck as $field) {
                    if ($this->hasMeaningfulC1EducationText($entry[$field] ?? null)) {
                        $hasMeaningfulData = true;
                        break;
                    }
                }

                if (!$hasMeaningfulData) {
                    continue;
                }

                // Has year graduated means completed degree
                if ($this->hasMeaningfulC1EducationText($entry['year_graduated'] ?? null)) {
                    return true;
                }

                // Check for degree-related keywords in earned field
                $earned = strtolower(trim((string) ($entry['earned'] ?? '')));
                if ($earned !== '' && str_contains($earned, 'graduate')) {
                    return true;
                }

                // Check for degree-related keywords in basic field (course name)
                $basic = strtolower(trim((string) ($entry['basic'] ?? '')));
                if ($basic !== '' && (
                    str_contains($basic, 'bachelor') ||
                    str_contains($basic, 'bs ') ||
                    str_contains($basic, 'ba ') ||
                    str_contains($basic, 'b.s.') ||
                    str_contains($basic, 'b.a.') ||
                    str_contains($basic, 'degree')
                )) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isC2ElementaryOnlyApplicant(Request $request): bool
    {
        $c1 = (array) $request->session()->get('form.c1', []);

        $hasSecondary = $this->hasMeaningfulC1EducationText($c1['jhs_basic'] ?? null)
            || $this->hasMeaningfulC1EducationText($c1['shs_basic'] ?? null)
            || $this->hasMeaningfulC1EducationText($c1['jhs_school'] ?? null)
            || $this->hasMeaningfulC1EducationText($c1['shs_school'] ?? null);

        return !$hasSecondary;
    }

    private function c2EligibilityLevelMap(): array
    {
        $fallback = [
            'csc professional eligibility' => 'Second Level',
            'csc subprofessional eligibility' => 'First Level',
            'bar/board eligibility' => 'Second Level',
            'honor graduate eligibility' => 'Second Level',
            'foreign school honor graduate eligibility' => 'Second Level',
            'scientific and technological specialist eligibility' => 'Second Level',
            'electronic data processing specialist eligibility' => 'Second Level',
            'skills eligibility – category ii' => 'First Level',
            'barangay official eligibility' => 'First Level',
            'barangay health worker eligibility' => 'First Level',
            'barangay nutrition scholar eligibility' => 'First Level',
            'sanggunian member first level eligibility' => 'First Level',
            'sanggunian member second level eligibility' => 'Second Level',
            'veteran preference rating eligibility' => 'Second Level',
            'career service eligibility – preference rating' => 'Second Level',
            'career service eligibility – preference rating for military and uniformed personnel' => 'Second Level',
        ];

        if (!Schema::hasTable('eligibility_presets')) {
            return $fallback;
        }

        try {
            $rows = EligibilityPreset::query()->get(['name', 'level']);
            if ($rows->isEmpty()) {
                return $fallback;
            }

            $mapped = [];
            foreach ($rows as $row) {
                $name = $this->normalizeEligibilityNameForComparison($row->name ?? '');
                if ($name === '') {
                    continue;
                }

                $mapped[$name] = trim((string) ($row->level ?? ''));
            }

            return !empty($mapped) ? $mapped : $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function isSecondLevelEligibilityLabel(?string $level): bool
    {
        return str_contains(strtolower(trim((string) $level)), 'second level');
    }

    private function isCscProfessionalEligibilityName(string $name): bool
    {
        $normalized = $this->normalizeEligibilityNameForComparison($name);
        return str_contains($normalized, 'csc professional') || str_contains($normalized, 'career service professional');
    }

    private function validateC2EligibilityByEducation(\Illuminate\Validation\Validator $validator, Request $request): void
    {
        // College degree holders can select ALL eligibilities - no validation needed
        if ($this->hasC2CollegeDegree($request)) {
            return;
        }

        $submittedCareers = $request->input('cs_eligibility_career', []);
        if (!is_array($submittedCareers)) {
            return;
        }

        $levelByName = $this->c2EligibilityLevelMap();
        $isHighSchoolOnly = $this->isC2HighSchoolOnlyApplicant($request);
        $isElementaryOnly = $this->isC2ElementaryOnlyApplicant($request);

        foreach ($submittedCareers as $index => $career) {
            $normalizedName = $this->normalizeEligibilityNameForComparison($career);
            if ($normalizedName === '') {
                continue;
            }

            $level = $levelByName[$normalizedName] ?? null;

            // First Level is always allowed
            if (!$this->isSecondLevelEligibilityLabel($level)) {
                continue;
            }

            // High school only: allow Second Level only if it's CSC Professional
            if ($isHighSchoolOnly) {
                if ($this->isCscProfessionalEligibilityName($normalizedName)) {
                    continue;
                }
                $validator->errors()->add(
                    "cs_eligibility_career.$index",
                    'For high school-level applicants, only First Level eligibilities and CSC Professional are allowed.'
                );
                continue;
            }

            // Elementary only: disallow ALL Second Level eligibilities
            if ($isElementaryOnly) {
                $validator->errors()->add(
                    "cs_eligibility_career.$index",
                    'For elementary-level applicants, only First Level eligibilities are allowed.'
                );
            }
        }
    }


    /**
     * Updates C2 session data based on the input fields.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function c2UpdateFormSession(Request $request, $go_to)
    {

        //dd($request->except('_token'));
        $validator = Validator::make($request->all(), [
            'work_exp_from' => 'nullable|array',
            'work_exp_from.*' => ['nullable', function ($attribute, $value, $fail) {
                $v = trim((string) ($value ?? ''));
                if ($v === '') return;
                try { \Carbon\Carbon::createFromFormat('Y-m-d', $v); } catch (\Throwable $e) { $fail('Invalid date format (YYYY-MM-DD).'); }
            }],
            'work_exp_to' => 'nullable|array',
            'work_exp_to.*' => ['nullable', 'string', function ($attribute, $value, $fail) {
                $normalized = strtolower(trim((string) ($value ?? '')));
                if ($normalized === '' || $normalized === 'present') {
                    return;
                }

                try {
                    Carbon::createFromFormat('Y-m-d', trim((string) $value));
                } catch (\Throwable $e) {
                    $fail('Inclusive date TO must be a valid date (YYYY-MM-DD) or marked as PRESENT.');
                }
            }],
            'work_exp_position' => 'nullable|array',
            'work_exp_position.*' => 'nullable|string|max:255',
            'work_exp_department' => 'nullable|array',
            'work_exp_department.*' => 'nullable|string|max:255',
            'work_exp_status' => 'nullable|array',
            'work_exp_status.*' => 'nullable|in:Permanent,Temporary,Casual,Contractual',
            'work_exp_govt_service' => 'nullable|array',
            'work_exp_govt_service.*' => 'nullable|in:Y,N',

            'cs_eligibility_career' => 'nullable|array',
            'cs_eligibility_career.*' => 'nullable|string|max:255',
            'cs_eligibility_rating' => 'nullable|array',
            'cs_eligibility_rating.*' => 'nullable|string|max:255',
            'cs_eligibility_date' => 'nullable|array',
            'cs_eligibility_date.*' => ['nullable', function ($attribute, $value, $fail) {
                $v = trim((string) ($value ?? ''));
                if ($v === '') return;
                try { \Carbon\Carbon::createFromFormat('Y-m-d', $v); } catch (\Throwable $e) { $fail('Invalid date format (YYYY-MM-DD).'); }
            }],
            'cs_eligibility_place' => 'nullable|array',
            'cs_eligibility_place.*' => 'nullable|string|max:255',
            'cs_eligibility_license' => 'nullable|array',
            'cs_eligibility_license.*' => 'nullable|string|max:255',
            'cs_eligibility_validity' => 'nullable|array',
            'cs_eligibility_validity.*' => ['nullable', function ($attribute, $value, $fail) {
                $v = trim((string) ($value ?? ''));
                if ($v === '') return;
                try { \Carbon\Carbon::createFromFormat('Y-m-d', $v); } catch (\Throwable $e) { $fail('Invalid date format (YYYY-MM-DD).'); }
            }],
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($request) {
            $fromDates = $request->input('work_exp_from', []);
            $toDates = $request->input('work_exp_to', []);
            if (!is_array($fromDates) || !is_array($toDates)) {
                $this->validateC2EligibilityByEducation($validator, $request);
                return;
            }

            $positions = $request->input('work_exp_position', []);
            $departments = $request->input('work_exp_department', []);
            $statuses = $request->input('work_exp_status', []);
            $govtServiceFlags = $request->input('work_exp_govt_service', []);

            $rowCount = max(
                count($fromDates),
                count($toDates),
                is_array($positions) ? count($positions) : 0,
                is_array($departments) ? count($departments) : 0,
                is_array($statuses) ? count($statuses) : 0,
                is_array($govtServiceFlags) ? count($govtServiceFlags) : 0
            );
            for ($i = 0; $i < $rowCount; $i++) {
                $fromRaw = trim((string) ($fromDates[$i] ?? ''));
                $toRaw = trim((string) ($toDates[$i] ?? ''));
                $positionRaw = trim((string) ($positions[$i] ?? ''));
                $departmentRaw = trim((string) ($departments[$i] ?? ''));
                $statusRaw = trim((string) ($statuses[$i] ?? ''));
                $govtServiceRaw = trim((string) ($govtServiceFlags[$i] ?? ''));

                $hasAnyWorkExperienceValue = $fromRaw !== ''
                    || $toRaw !== ''
                    || $positionRaw !== ''
                    || $departmentRaw !== ''
                    || $statusRaw !== ''
                    || $govtServiceRaw !== '';

                if ($hasAnyWorkExperienceValue && $fromRaw === '') {
                    $rowNumber = $i + 1;
                    $validator->errors()->add(
                        "work_exp_from.$i",
                        "Work Experience row {$rowNumber}: FROM date is required when any work experience details are entered."
                    );
                    continue;
                }

                if ($fromRaw === '' || $toRaw === '' || strtolower($toRaw) === 'present') {
                    continue;
                }

                try {
                    $fromDate = Carbon::createFromFormat('Y-m-d', $fromRaw)->startOfDay();
                    $toDate = Carbon::createFromFormat('Y-m-d', $toRaw)->startOfDay();
                } catch (\Throwable $e) {
                    continue;
                }

                // FROM must not be after TO (same day is allowed).
                if ($fromDate->gt($toDate)) {
                    $rowNumber = $i + 1;
                    $validator->errors()->add(
                        "work_exp_from.$i",
                        "Work Experience row {$rowNumber}: The FROM date must not be later than the TO date."
                    );
                    $validator->errors()->add(
                        "work_exp_to.$i",
                        "Work Experience row {$rowNumber}: The TO date must not be earlier than the FROM date."
                    );
                }
            }

            $examDates = $request->input('cs_eligibility_date', []);
            $validityDates = $request->input('cs_eligibility_validity', []);
            if (is_array($examDates) && is_array($validityDates)) {
                $civilServiceRows = max(count($examDates), count($validityDates));

                for ($i = 0; $i < $civilServiceRows; $i++) {
                    $examRaw = trim((string) ($examDates[$i] ?? ''));
                    $validityRaw = trim((string) ($validityDates[$i] ?? ''));

                    if ($examRaw === '' || $validityRaw === '') {
                        continue;
                    }

                    try {
                        $examDate = Carbon::createFromFormat('Y-m-d', $examRaw)->startOfDay();
                        $validityDate = Carbon::createFromFormat('Y-m-d', $validityRaw)->startOfDay();
                    } catch (\Throwable $e) {
                        continue;
                    }

                    if (!$validityDate->gt($examDate)) {
                        $rowNumber = $i + 1;
                        $validator->errors()->add(
                            "cs_eligibility_validity.$i",
                            "Civil Service Eligibility row {$rowNumber}: Valid Until must be later than Date of Examination/Conferment."
                        );
                    }
                }
            }

            $this->validateC2EligibilityByEducation($validator, $request);
        });

        $validator->validate();

        $c2_form_data = $request->except('_token');
        foreach (['work_exp_from','work_exp_to','work_exp_position','work_exp_department','work_exp_status','work_exp_govt_service'] as $key) {
            if (!isset($c2_form_data[$key]) || !is_array($c2_form_data[$key])) {
                $c2_form_data[$key] = [];
            }
        }

        // ------------------------------
        // WORK EXPERIENCE TABLE
        // ------------------------------
        $work_exp_count = max(
            count($c2_form_data['work_exp_from'] ?? []),
            count($c2_form_data['work_exp_to'] ?? []),
            count($c2_form_data['work_exp_position'] ?? []),
            count($c2_form_data['work_exp_department'] ?? []),
            count($c2_form_data['work_exp_status'] ?? []),
            count($c2_form_data['work_exp_govt_service'] ?? []),
            count($c2_form_data['work_exp_id'] ?? [])
        );
        $all_wex_data = [];
        $persistedWorkIds = [];

        for ($i = 0; $i < $work_exp_count; $i++) {
            $workExpFrom = trim(strip_tags((string) ($c2_form_data['work_exp_from'][$i] ?? '')));
            $workExpToRaw = trim(strip_tags((string) ($c2_form_data['work_exp_to'][$i] ?? '')));
            $isPresentWorkExpTo = strtolower($workExpToRaw) === 'present';

            $data_work_exp = [
                'user_id' => Auth::id(), // store the id of the current user
                'work_exp_from' => $this->normalizeDateForDatabase($workExpFrom),
                // DB column is DATE. Keep schema unchanged by converting PRESENT to a valid date.
                'work_exp_to' => $this->normalizeWorkExperienceEndDateForDatabase($workExpToRaw),
                'work_exp_position' => trim(strip_tags((string) ($c2_form_data['work_exp_position'][$i] ?? ''))),
                'work_exp_department' => trim(strip_tags((string) ($c2_form_data['work_exp_department'][$i] ?? ''))),
                'work_exp_status' => trim(strip_tags((string) ($c2_form_data['work_exp_status'][$i] ?? ''))),
                'work_exp_govt_service' => trim(strip_tags((string) ($c2_form_data['work_exp_govt_service'][$i] ?? '')))
            ];

            $hasWorkData = array_filter([
                $data_work_exp['work_exp_from'],
                $workExpToRaw,
                $data_work_exp['work_exp_position'],
                $data_work_exp['work_exp_department'],
                $data_work_exp['work_exp_status'],
                $data_work_exp['work_exp_govt_service'],
            ], static fn($v) => trim((string) $v) !== '');

            if (empty($hasWorkData)) {
                continue;
            }

            if ($data_work_exp['work_exp_from'] === null) {
                continue;
            }


            // Check if the value of id is zero, a zero value means a record
            // does not exist. thus, create a new record
            $wex_id_temp = $c2_form_data['work_exp_id'][$i] ?? null;
            $wex_id_temp = is_scalar($wex_id_temp) ? trim((string) $wex_id_temp) : '';
            $wex_id = ctype_digit($wex_id_temp) ? (int) $wex_id_temp : 0;

            if ($wex_id > 0) {
                $data_work_exp['id'] = $wex_id;
            } else {
                $data_work_exp['created_at'] = now();
            }
            $data_work_exp['updated_at'] = now();

            if (!empty($data_work_exp['id'])) {
                WorkExperience::where('id', $data_work_exp['id'])->update($data_work_exp);
            } else {
                $newRecord = WorkExperience::create($data_work_exp);
                $data_work_exp['id'] = $newRecord->id;
            }

            if (!empty($data_work_exp['id'])) {
                $persistedWorkIds[] = (int) $data_work_exp['id'];
            }
            $sessionWorkExp = $data_work_exp;
            if ($isPresentWorkExpTo) {
                $sessionWorkExp['work_exp_to'] = 'present';
            }
            $all_wex_data[] = $sessionWorkExp;
            // WorkExperience::upsert($data_work_exp, 'id');
        }

        // Keep DB rows in sync with submitted form rows.
        if (!empty($persistedWorkIds)) {
            WorkExperience::where('user_id', Auth::id())
                ->whereNotIn('id', $persistedWorkIds)
                ->delete();
        } else {
            WorkExperience::where('user_id', Auth::id())->delete();
        }


        // ------------------------------
        // CIVIL SERVICE ELIGIBILITY test
        // ------------------------------
        $civil_service_count = max(
            count($c2_form_data['cs_eligibility_career'] ?? []),
            count($c2_form_data['cs_eligibility_rating'] ?? []),
            count($c2_form_data['cs_eligibility_date'] ?? []),
            count($c2_form_data['cs_eligibility_place'] ?? []),
            count($c2_form_data['cs_eligibility_license'] ?? []),
            count($c2_form_data['cs_eligibility_validity'] ?? []),
            count($c2_form_data['cs_eligibility_id'] ?? [])
        );
        $all_cs_data = [];
        $persistedCivilServiceIds = [];

        for ($i = 0; $i < $civil_service_count; $i++) {

            $data_cs = [
                'user_id' => Auth::id(), // store the id of the current user
                'cs_eligibility_career' => trim(strip_tags($c2_form_data['cs_eligibility_career'][$i])),
                'cs_eligibility_rating' => trim(strip_tags($c2_form_data['cs_eligibility_rating'][$i])),
                'cs_eligibility_date' => $this->normalizeDateForDatabase(trim(strip_tags($c2_form_data['cs_eligibility_date'][$i]))),
                'cs_eligibility_place' => trim(strip_tags($c2_form_data['cs_eligibility_place'][$i])),
                'cs_eligibility_license' => trim(strip_tags($c2_form_data['cs_eligibility_license'][$i])),
                'cs_eligibility_validity' => $this->normalizeDateForDatabase(trim(strip_tags($c2_form_data['cs_eligibility_validity'][$i])))
            ];

            $cs_id_temp = $c2_form_data['cs_eligibility_id'][$i] ?? null;
            $cs_id_temp = is_scalar($cs_id_temp) ? trim((string) $cs_id_temp) : '';
            $cs_id = ctype_digit($cs_id_temp) ? (int) $cs_id_temp : 0;

            if ($cs_id > 0) {
                $data_cs['id'] = $cs_id;
            } else {
                $data_cs['created_at'] = now();
            }
            $data_cs['updated_at'] = now();
            if (!empty($data_cs['id'])) {
                CivilServiceEligibility::where('id', $data_cs['id'])->update($data_cs);
            } else {
                $newRecord = CivilServiceEligibility::create($data_cs);
                $data_cs['id'] = $newRecord->id;
            }

            if (!empty($data_cs['id'])) {
                $persistedCivilServiceIds[] = (int) $data_cs['id'];
            }
            $all_cs_data[] = $data_cs;
            // CivilServiceEligibility::upsert($data_cs, 'id');
        }

        // Keep DB rows in sync with submitted form rows.
        if (!empty($persistedCivilServiceIds)) {
            CivilServiceEligibility::where('user_id', Auth::id())
                ->whereNotIn('id', $persistedCivilServiceIds)
                ->delete();
        } else {
            CivilServiceEligibility::where('user_id', Auth::id())->delete();
        }


        $c2_full_info = [
            'all_user_work_exps' => $all_wex_data,
            'all_user_civil_service_eligibility' => $all_cs_data
        ];

        session(['form.c2' => $c2_full_info]);

        activity()
            ->causedBy(Auth::user())
            ->log('Updated C2 form session.');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        $routeParams = [];
        if ($request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);

    }

    public function c2DeleteRow($target_row, $id)
    {

        switch ($target_row) {
            case 'work-exp-table':
                WorkExperience::destroy($id);
                break;

            case 'civil-service-table':
                CivilServiceEligibility::destroy($id);
                break;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['target_row' => $target_row, 'id' => $id])
            ->log("Deleted row in C2 form.");

        return response('Delete OK', 200);
    }

    // PDS PAGE 3
    public function c3SubmitForm(Request $request, $go_to)
    {

        // ------------------------------
        // LEARNING AND DEVELOPMENT
        // ------------------------- -----
        $entryCount = max(0, (int) $request->input('learning_entry_count', 0)); // get from hidden field

        // If validation fails the inputted data in Learning and Development (L&D) Interventions table
        // c3.blade the data is already in session to auto populate the input fields.
        $learningIndexes = [];
        $data_learning_arrays = [];
        for ($i = 1; $i <= $entryCount; $i++) {
            $row = [
                'learning_title' => trim((string) $request->input("learning_title_$i", '')),
                'learning_type' => trim((string) $request->input("learning_type_$i", '')),
                'learning_from' => trim((string) $request->input("learning_from_$i", '')),
                'learning_to' => trim((string) $request->input("learning_to_$i", '')),
                'learning_hours' => trim((string) $request->input("learning_hours_$i", '')),
                'learning_conducted' => trim((string) $request->input("learning_conducted_$i", '')),
            ];
            if (!$this->hasAutosaveRowData($row)) {
                continue;
            }
            $learningIndexes[] = $i;
            $data_learning_arrays[] = $row;
        }
        session(['data_learning' => $data_learning_arrays]);

        // ---------------------------------------------------------------------------
        // VOLUNTARY WORK EXPERIENCE
        // ---------------------------------------------------------------------------
        $entryCount_vol = max(0, (int) $request->input('voluntary_work_count', 0)); // get from hidden field

        // If validation fails the inputted data in Voluntary Works table
        // c3.blade the data is already in session to auto populate the input fields.
        $voluntaryIndexes = [];
        $data_voluntary_arrays = [];
        for ($i = 1; $i <= $entryCount_vol; $i++) {
            $row = [
                'voluntary_org' => trim((string) $request->input("voluntary_org_$i", '')),
                'voluntary_from' => trim((string) $request->input("voluntary_from_$i", '')),
                'voluntary_to' => trim((string) $request->input("voluntary_to_$i", '')),
                'voluntary_hours' => trim((string) $request->input("voluntary_hours_$i", '')),
                'voluntary_position' => trim((string) $request->input("voluntary_position_$i", '')),
            ];
            if (!$this->hasAutosaveRowData($row)) {
                continue;
            }
            $voluntaryIndexes[] = $i;
            $data_voluntary_arrays[] = $row;
        }
        session(['data_voluntary' => $data_voluntary_arrays]);

        // ---------------------------------------------------------------------------
        // OTHER INFORMATION
        // ---------------------------------------------------------------------------
        $skills = $request->input('skills', []);
        $distinctions = $request->input('distinctions', []);
        $organizations = $request->input('organizations', []);
        if (!is_array($skills)) {
            $skills = [$skills];
        }
        if (!is_array($distinctions)) {
            $distinctions = [$distinctions];
        }
        if (!is_array($organizations)) {
            $organizations = [$organizations];
        }
        $skills = array_values(array_filter($skills, fn($value) => $value !== null && $value !== ''));
        $distinctions = array_values(array_filter($distinctions, fn($value) => $value !== null && $value !== ''));
        $organizations = array_values(array_filter($organizations, fn($value) => $value !== null && $value !== ''));
        $data_other_arrays = [
            'skill' => $skills,
            'distinction' => $distinctions,
            'organization' => $organizations,
            'user_id' => Auth::id(),
        ];
        session(['data_otherInfo' => $data_other_arrays]);
        //dd($data_other_arrays);

        /*
        $user_other_info = OtherInformation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $user_other_info->update([
            'user_id'       => Auth::id(),
            'skill'         => $data_other['skills'],
            'distinction'   => $data_other['distinctions'],
            'organization'  => $data_other['organizations'],
        ]);
        */

        // ========================================================================
        // VALIDATION
        // ========================================================================

        // LEARNING AND DEVELOPMENT VALIDATION
        $rules_data_learning = [];
        foreach ($learningIndexes as $i) {
            $rules_data_learning["learning_title_$i"] = 'required|string|max:255';
            $rules_data_learning["learning_type_$i"] = 'required|string|max:100';
            $rules_data_learning["learning_from_$i"] = 'required|date';
$rules_data_learning["learning_to_$i"] = 'required|date';
            $rules_data_learning["learning_hours_$i"] = 'required|integer|min:1|max:' . self::SMALLINT_MAX;
            $rules_data_learning["learning_conducted_$i"] = 'required|string|max:255';
        }
        $learningMessages = [];
        foreach ($learningIndexes as $i) {
            $learningMessages["learning_hours_$i.integer"] = "Learning and Development row {$i}: Number of hours must be a whole number.";
            $learningMessages["learning_hours_$i.max"] = "Learning and Development row {$i}: Number of hours cannot exceed " . self::SMALLINT_MAX . '.';
        }
        $learningValidator = Validator::make($request->all(), $rules_data_learning, $learningMessages);
        $learningValidator->after(function (\Illuminate\Validation\Validator $validator) use ($request, $learningIndexes) {
            foreach ($learningIndexes as $i) {
                $fromRaw = trim((string) $request->input("learning_from_$i", ''));
                $toRaw = trim((string) $request->input("learning_to_$i", ''));
                if ($fromRaw === '' || $toRaw === '') {
                    continue;
                }

                try {
                    $fromDate = Carbon::parse($fromRaw)->startOfDay();
                    $toDate = Carbon::parse($toRaw)->startOfDay();
                } catch (\Throwable $e) {
                    continue;
                }

                // TO date cannot be before FROM date (same day is allowed)
                if ($toDate->lt($fromDate)) {
                    $validator->errors()->add(
                        "learning_from_$i",
                        "Learning and Development row {$i}: FROM date must not be later than TO date."
                    );
                    $validator->errors()->add(
                        "learning_to_$i",
                        "Learning and Development row {$i}: TO date must not be earlier than FROM date."
                    );
                }
            }
        });
        $validated_data_learning = $learningValidator->validate();

        // FOR session data in LEARNING AND DEVELOPMENT
        $data_learning_arrays = [];
        foreach ($learningIndexes as $i) {
            $data_learning_arrays[] = [
                'learning_title' => $validated_data_learning["learning_title_$i"],
                'learning_type' => $validated_data_learning["learning_type_$i"],
                'learning_from' => $this->normalizeDateForDatabase($validated_data_learning["learning_from_$i"]),
                'learning_to' => $this->normalizeDateForDatabase($validated_data_learning["learning_to_$i"]),
                'learning_hours' => $validated_data_learning["learning_hours_$i"],
                'learning_conducted' => $validated_data_learning["learning_conducted_$i"],
                'user_id' => Auth::id(),
            ];
        }
        // SESSION name table of Learning and Development (L&D) Interventions in c3.blade file
        session(['data_learning' => $data_learning_arrays]);
        //dd(session('data_learning'));


        // VOLUNTARY WORKS VALIDATION
        $rules_data_vol = [];
        foreach ($voluntaryIndexes as $i) {
            $rules_data_vol["voluntary_org_$i"] = 'required|string|max:255';
            $rules_data_vol["voluntary_from_$i"] = 'required|date';
$rules_data_vol["voluntary_to_$i"] = 'required|date';
            $rules_data_vol["voluntary_hours_$i"] = 'required|integer|min:1|max:' . self::SMALLINT_MAX;
            $rules_data_vol["voluntary_position_$i"] = 'required|string|max:255';
        }
        $voluntaryMessages = [];
        foreach ($voluntaryIndexes as $i) {
            $voluntaryMessages["voluntary_hours_$i.integer"] = "Voluntary Work row {$i}: Number of hours must be a whole number.";
            $voluntaryMessages["voluntary_hours_$i.max"] = "Voluntary Work row {$i}: Number of hours cannot exceed " . self::SMALLINT_MAX . '.';
        }
        $voluntaryValidator = Validator::make($request->all(), $rules_data_vol, $voluntaryMessages);
        $voluntaryValidator->after(function (\Illuminate\Validation\Validator $validator) use ($request, $voluntaryIndexes) {
            foreach ($voluntaryIndexes as $i) {
                $fromRaw = trim((string) $request->input("voluntary_from_$i", ''));
                $toRaw = trim((string) $request->input("voluntary_to_$i", ''));
                if ($fromRaw === '' || $toRaw === '') {
                    continue;
                }

                try {
                    $fromDate = Carbon::parse($fromRaw)->startOfDay();
                    $toDate = Carbon::parse($toRaw)->startOfDay();
                } catch (\Throwable $e) {
                    continue;
                }

                // TO date cannot be before FROM date (same day is allowed)
                if ($toDate->lt($fromDate)) {
                    $validator->errors()->add(
                        "voluntary_from_$i",
                        "Voluntary Work row {$i}: FROM date must not be later than TO date."
                    );
                    $validator->errors()->add(
                        "voluntary_to_$i",
                        "Voluntary Work row {$i}: TO date must not be earlier than FROM date."
                    );
                }
            }
        });
        $validated_data_vol = $voluntaryValidator->validate();

        // FOR session data in VOLUNTARY WORK
        $data_voluntary_arrays = [];
        foreach ($voluntaryIndexes as $i) {
            $data_voluntary_arrays[] = [
                'voluntary_org' => $validated_data_vol["voluntary_org_$i"],
                'voluntary_from' => $this->normalizeDateForDatabase($validated_data_vol["voluntary_from_$i"]),
                'voluntary_to' => $this->normalizeDateForDatabase($validated_data_vol["voluntary_to_$i"]),
                'voluntary_hours' => $validated_data_vol["voluntary_hours_$i"],
                'voluntary_position' => $validated_data_vol["voluntary_position_$i"],
                'user_id' => Auth::id(),
            ];
        }
        // SESSION name table of VOLUNTARY WORKS in c3.blade file
        session(['data_voluntary' => $data_voluntary_arrays]);

        // VALIDATION ENDS
        // ========================================================================
/*
        activity()
            ->causedBy(Auth::user())
            ->log('Submitted C3 form data.');
*/
        $sortByDateDesc = function($data, $fieldTo, $fieldFrom) {
            if (!is_array($data)) return [];
            usort($data, function($a, $b) use ($fieldTo, $fieldFrom) {
                $dateA = trim((string) ($a[$fieldTo] ?? ($a[$fieldFrom] ?? '')));
                $dateB = trim((string) ($b[$fieldTo] ?? ($b[$fieldFrom] ?? '')));
                
                $valA = strtoupper($dateA) === 'PRESENT' ? PHP_INT_MAX : (empty($dateA) ? 0 : strtotime($dateA));
                $valB = strtoupper($dateB) === 'PRESENT' ? PHP_INT_MAX : (empty($dateB) ? 0 : strtotime($dateB));
                
                if ($valA === $valB) {
                    $dateAFrom = trim((string) ($a[$fieldFrom] ?? ''));
                    $dateBFrom = trim((string) ($b[$fieldFrom] ?? ''));
                    $valAFrom = strtoupper($dateAFrom) === 'PRESENT' ? PHP_INT_MAX : (empty($dateAFrom) ? 0 : strtotime($dateAFrom));
                    $valBFrom = strtoupper($dateBFrom) === 'PRESENT' ? PHP_INT_MAX : (empty($dateBFrom) ? 0 : strtotime($dateBFrom));
                    return $valBFrom <=> $valAFrom;
                }
                return $valB <=> $valA;
            });
            return $data;
        };

        $c3_learning_and_development_data = $sortByDateDesc(session('data_learning', []), 'learning_to', 'learning_from');
        $c3_voluntary_data = $sortByDateDesc(session('data_voluntary', []), 'voluntary_to', 'voluntary_from');
        
        session(['data_learning' => $c3_learning_and_development_data]);
        session(['data_voluntary' => $c3_voluntary_data]);
        
        $c3_other_information_data = session('data_otherInfo');
        try {
            DB::transaction(function () use ($c3_learning_and_development_data, $c3_voluntary_data, $c3_other_information_data) {
                \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);

                // SAVE C3 to DB
                // LEARNING AND DEVELOPMENT
                LearningAndDevelopment::where('user_id', Auth::id())->delete();
                if (!empty($c3_learning_and_development_data)) {
                    foreach ($c3_learning_and_development_data as $row) {
                        LearningAndDevelopment::create($row);
                    }
                }

                // VOLUNTARY WORK
                VoluntaryWork::where('user_id', Auth::id())->delete();
                if (!empty($c3_voluntary_data)) {
                    foreach ($c3_voluntary_data as $row) {
                        VoluntaryWork::create($row);
                    }
                }

                // OTHER INFORMATION
                if (!empty($c3_other_information_data)) {
                    Models\OtherInformation::updateOrCreate(
                        ['user_id' => Auth::id()],
                        [
                            'skill' => $c3_other_information_data['skill'],
                            'distinction' => $c3_other_information_data['distinction'],
                            'organization' => $c3_other_information_data['organization'],
                        ]
                    );
                }
            });
        } catch (\Throwable $e) {
            Log::error('C3 submit failed', [
                'user_id' => Auth::id(),
                'go_to' => $go_to,
                'simple' => $request->input('simple'),
                'learning_count' => is_array($c3_learning_and_development_data) ? count($c3_learning_and_development_data) : 0,
                'voluntary_count' => is_array($c3_voluntary_data) ? count($c3_voluntary_data) : 0,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'c3_save' => 'Unable to save C3 data. Please check the number of hours and try again.',
            ])->withInput();
        }
        $routeParams = [];
        if ($request->boolean('simple') || $request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        return redirect()->route($go_to, $routeParams);
    }

    public function c3ShowForm()
    {
        if (empty(session('data_learning')) && empty(session('data_voluntary')) && empty(session('data_otherInfo'))) {
            $this->c3GetDatabase();
        }
        $data_learning = session('data_learning', []);
        $data_voluntary = session('data_voluntary', []);
        $data_otherInfo = session('data_otherInfo', []);
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Viewed C3 form.');
        */
        return view('pds.c3', compact('data_learning', 'data_voluntary', 'data_otherInfo'));
    }

    private function syncLearningSessionFromDatabaseIfStale(): void
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return;
        }

        $sessionLearning = session('data_learning', []);
        $sessionCount = is_array($sessionLearning) ? count($sessionLearning) : 0;

        $dbLearning = LearningAndDevelopment::where('user_id', $currentUserId)
            ->orderByDesc('learning_from')
            ->get()
            ->toArray();

        if (count($dbLearning) > $sessionCount) {
            session(['data_learning' => $dbLearning]);
        }
    }

    public function c3GetDatabase()
    {
        $current_user_id = Auth::id();
        $all_user_learningAndDevelopment_data = LearningAndDevelopment::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        $all_user_voluntary_work_data = VoluntaryWork::where(
            'user_id',
            '=',
            $current_user_id
        )->get()->toArray();

        $all_user_other_info_data = OtherInformation::where('user_id', $current_user_id)
            ->first(); // Assuming only one record per user
        if ($all_user_other_info_data) {
            $processed_data = [
                'skill' => $all_user_other_info_data->skill ?? [],
                'distinction' => $all_user_other_info_data->distinction ?? [],
                'organization' => $all_user_other_info_data->organization ?? [],
                'user_id' => $all_user_other_info_data->user_id,
            ];
            session(['data_otherInfo' => $processed_data]);
        }

        session(['data_learning' => $all_user_learningAndDevelopment_data]);
        session(['data_voluntary' => $all_user_voluntary_work_data]);
    }


    // ==============================================================================
    // C4 CONTROLLER
    public function c4SubmitForm(Request $request, $go_to)
    {
        /*
        $hasUploadedPhoto = session()->has('form.c4.photo_upload');
        $request->validate([
            'photo_upload' => $hasUploadedPhoto
                ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
                : 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);
        $temp_photo_path = session('form.c4.photo_upload'); // default to existing photo
        //Handle the photo upload (store in photo/pds-img inside public disk)
        // Only store new photo if uploaded
        if ($request->hasFile('photo_upload') && $request->file('photo_upload')->isValid()) {
            $photo = $request->file('photo_upload');

            //Store the uploaded photo in: storage/app/public/uploads/pds-photo
            $new_photo_path = $photo->store('uploads/pds-photo', 'public');

            //Delete old photo if it's different and exists
            if (!empty($temp_photo_path) &&
                $temp_photo_path !== $new_photo_path &&
                Storage::disk('public')->exists($temp_photo_path)) {
                Storage::disk('public')->delete($temp_photo_path);
            }

            $temp_photo_path = $new_photo_path; // use the new photo path
        } */
        // get the selected option if "Yes" or "No"
        $temp_34_b = $request->input('related_34_b');
        $temp_35_a = $request->input('guilty_35_a');
        $temp_35_b = $request->input('criminal_35_b');
        $temp_36 = $request->input('convicted_36');
        $temp_37 = $request->input('separated_37');
        $temp_38_a = $request->input('candidate_38_a');
        $temp_38_b = $request->input('resigned_38_b');
        $temp_39 = $request->input('immigrant_39');
        $temp_40_a = $request->input('indigenous_40_a');
        $temp_40_b = $request->input('pwd_40_b');
        $temp_40_c = $request->input('solo_parent_40_c');
        $rawGovtIdType = $request->input('govt_id_type');
        $rawGovtIdTypeNormalized = strtolower(trim((string) $rawGovtIdType));

        // If "yes" was selected, use the text area instead
        $related_34_b = $this->userSelection($temp_34_b, $request, 'related_34_b_details');
        $guilty_35_a = $this->userSelection($temp_35_a, $request, 'guilty_35_a_details');
        $convicted_36 = $this->userSelection($temp_36, $request, 'convicted_36_details');
        $separated_37 = $this->userSelection($temp_37, $request, 'separated_37_details');
        $candidate_38_a = $this->userSelection($temp_38_a, $request, 'candidate_38_a_details');
        $resigned_38_b = $this->userSelection($temp_38_b, $request, 'resigned_38_b_details');
        $immigrant_39 = $this->userSelection($temp_39, $request, 'immigrant_39_details');
        $indigenous_40_a = $this->userSelection($temp_40_a, $request, 'indigenous_40_a_details');
        $pwd_40_b = $this->userSelection($temp_40_b, $request, 'pwd_40_b_details');
        $solo_parent_40_c = $this->userSelection($temp_40_c, $request, 'solo_parent_40_c_details');

        $criminal_35_b_array = $request->input('criminal_35_b_details');
        // NUMBER 35_b
        if ($temp_35_b === 'yes') {
            $criminal_35_b = implode(',', $criminal_35_b_array);

        } else {
            $criminal_35_b = 'no';
        }

        $govt_id_type = $rawGovtIdType;
        if ($rawGovtIdTypeNormalized === 'other') {
            $govt_id_type = $request->input('govt_id_other');
            //Updated value into the request for validation to work
            $request->merge(['govt_id_type' => $govt_id_type]);
        }

        $ref1Contact = $this->normalizeReferenceContact($request->input('ref1_tel'));
        $ref2Contact = $this->normalizeReferenceContact($request->input('ref2_tel'));
        $ref3Contact = $this->normalizeReferenceContact($request->input('ref3_tel'));
        $govtIdDateIssued = $this->normalizeGovtIdDateIssued($request->input('govt_id_date_issued'));

        // TODO get the photo upload
        $misc_data = [
            //'user_id'               => Auth::id(),
            'related_34_a' => $request->input('related_34_a'),
            'related_34_b' => $related_34_b,
            'guilty_35_a' => $guilty_35_a,
            'criminal_35_b' => $criminal_35_b,
            'criminal_35_b_array' => $criminal_35_b_array, // remove if insert into database
            'convicted_36' => $convicted_36,
            'separated_37' => $separated_37,
            'candidate_38' => $candidate_38_a,
            'resigned_38_b' => $resigned_38_b,
            'immigrant_39' => $immigrant_39,
            'indigenous_40_a' => $indigenous_40_a,
            'pwd_40_b' => $pwd_40_b,
            'solo_parent_40_c' => $solo_parent_40_c,

            'ref1_name' => $request->input('ref1_name'),
            'ref1_tel' => $ref1Contact,
            'ref1_address' => $request->input('ref1_address'),
            'ref2_name' => $request->input('ref2_name'),
            'ref2_tel' => $ref2Contact,
            'ref2_address' => $request->input('ref2_address'),
            'ref3_name' => $request->input('ref3_name'),
            'ref3_tel' => $ref3Contact,
            'ref3_address' => $request->input('ref3_address'),

            'govt_id_type' => $govt_id_type,
            'govt_id_other' => $request->input('govt_id_other'),
            'govt_id_number' => $request->input('govt_id_number'),
            'govt_id_date_issued' => $govtIdDateIssued,
            'govt_id_place_issued' => $request->input('govt_id_place_issued'),

            'photo_upload' => $temp_photo_path ?? null,
        ];
        session(['form.c4' => $misc_data]);
        //dd(session('form.c4'));
        //dd($request->all());
        //dd(session('form.c4')); // TODO: GETDATABASE

        // Validate the form for the hidden fields (e.g: realated_34_b_details)
        $request->validate([
            'related_34_b_details' => 'required_if:related_34_b,yes|nullable|string|max:255',
            'criminal_35_b_details.date' => 'required_if:criminal_35_b,yes|nullable|date',
            'criminal_35_b_details.status' => 'required_if:criminal_35_b,yes|nullable|string|max:255',
            'convicted_36_details' => 'required_if:convicted_36,yes|nullable|string|max:255',
            'separated_37_details' => 'required_if:separated_37,yes|nullable|string|max:255',
            'candidate_38_a_details' => 'required_if:candidate_38_a,yes|nullable|string|max:255',
            'resigned_38_b_details' => 'required_if:resigned_38_b,yes|nullable|string|max:255',
            'immigrant_39_details' => 'required_if:immigrant_39,yes|nullable|string|max:255',
            'indigenous_40_a_details' => 'required_if:indigenous_40_a,yes|nullable|string|max:255',
            'pwd_40_b_details' => 'required_if:pwd_40_b,yes|nullable|string|max:255',
            'solo_parent_40_c_details' => 'required_if:solo_parent_40_c,yes|nullable|string|max:255',
            'govt_id_other' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($rawGovtIdTypeNormalized) {
                    if ($rawGovtIdTypeNormalized === 'other' && trim((string) $value) === '') {
                        $fail('Please specify the other Government Issued ID type.');
                    }
                },
            ],

        ]);

        // Validation for the data to be inserted in session to database
        $referenceContactRule = function ($attribute, $value, $fail) {
            if (!$this->isValidReferenceContact($value)) {
                $fail('The ' . $this->referenceContactFieldLabel($attribute) . ' must be a valid email address or an 11-digit contact number in the format 09XX XXX XXXX or +63 9XX XXX XXXX.');
            }
        };

        $validator_misc_data = Validator::make($misc_data, [
            'related_34_a' => 'required|string|max:255',
            'related_34_b' => 'required|string|max:255',
            'guilty_35_a' => 'required|string|max:255',
            'criminal_35_b' => 'required|string|max:255',
            'convicted_36' => 'required|string|max:255',
            'separated_37' => 'required|string|max:255',
            'candidate_38' => 'required|string|max:255',
            'resigned_38_b' => 'required|string|max:255',
            'immigrant_39' => 'required|string|max:255',
            'indigenous_40_a' => 'required|string|max:255',
            'pwd_40_b' => 'required|string|max:255',
            'solo_parent_40_c' => 'required|string|max:255',

            'ref1_name' => 'required|string|max:255',
            'ref1_tel' => ['required', 'string', 'max:255', $referenceContactRule],
            'ref1_address' => 'required|string|max:255',
            'ref2_name' => 'required|string|max:255',
            'ref2_tel' => ['required', 'string', 'max:255', $referenceContactRule],
            'ref2_address' => 'required|string|max:255',
            'ref3_name' => 'required|string|max:255',
            'ref3_tel' => ['required', 'string', 'max:255', $referenceContactRule],
            'ref3_address' => 'required|string|max:255',

            'govt_id_type' => 'required|string|max:255',
            'govt_id_number' => 'required|string|max:50',
            'govt_id_date_issued' => ['nullable', 'string', 'max:50', function ($attribute, $value, $fail) {
                if (!$this->isValidGovtIdDateIssued($value)) {
                    $fail('The Date of Issuance must be a valid date or N/A.');
                }
            }],
            'govt_id_place_issued' => 'required|string|max:255',
            'photo_upload' => 'nullable|string',
            'govt_id_other' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($rawGovtIdTypeNormalized) {
                    if ($rawGovtIdTypeNormalized === 'other' && trim((string) $value) === '') {
                        $fail('Please specify the other Government Issued ID type.');
                    }
                },
            ],
        ]);

        $validated_misc_data = $validator_misc_data->validated();
        $validated_misc_data['criminal_35_b_array'] = $criminal_35_b_array;
        $validated_misc_data['govt_id_other'] = $request->input('govt_id_other');
        $validated_misc_data['user_id'] = Auth::id();

        session(['form.c4' => $validated_misc_data]);
        /*
                activity()
                    ->causedBy(Auth::user())
                    ->log('Submitted C4 form data.');
        */
        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);

        // SAVE C4 to DB
        $c4_misc_info_data = session('form.c4');
        if (!empty($c4_misc_info_data)) {
            $dataToSave = $c4_misc_info_data;
            unset($dataToSave['criminal_35_b_array']);

            MiscInfos::updateOrCreate(
                ['user_id' => Auth::id()],
                $dataToSave
            );
        }

        $routeParams = [];
        if ($request->boolean('simple') || $request->query('simple')) {
            $routeParams['simple'] = 1;
        }
        if ($request->boolean('open_docs') || $request->query('open_docs')) {
            $routeParams['open_docs'] = 1;
        }

        return redirect()->route($go_to, $routeParams);
    }

    public function userSelection($sel, Request $request, string $textArea)
    {
        /*
        FOR THE SELECTION OF YES OR NO, WHERE IF THE SELECTED
        IS "NO", data="no". IF "YES" the data="details"
        */

        if ($sel === 'yes') {
            return $request->input($textArea);
        } else {
            return 'no';
        }
    }

    private function resolveAutosaveDetailValue(
        array $incoming,
        array $existing,
        string $selectionField,
        string $detailField,
        string $targetField
    ): string {
        $currentValue = trim((string) ($existing[$targetField] ?? 'no'));

        if (!array_key_exists($selectionField, $incoming)) {
            return $currentValue;
        }

        $selection = trim((string) $incoming[$selectionField]);

        if ($selection === 'no') {
            return 'no';
        }

        if ($selection === 'yes') {
            $detail = trim((string) ($incoming[$detailField] ?? ''));
            if ($detail !== '') {
                return $detail;
            }

            return strtolower($currentValue) !== 'no' ? $currentValue : '';
        }

        return $currentValue;
    }

    private function normalizeReferenceContact($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $value;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if ($digits !== null && preg_match('/^09\d{9}$/', $digits) === 1) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 4);
        }

        return $value;
    }

    private function isValidReferenceContact($value): bool
    {
        $value = trim((string) $value);

        if ($value === '') {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if ($digits === null) {
            return false;
        }

        // Only treat as phone number if digits start with "09" or "63" (representing +63)
        if (preg_match('/^09\d{9}$/', $digits) === 1 || preg_match('/^639\d{9}$/', $digits) === 1) {
            return true;
        }

        // Check if contains + for international format
        if (strpos($value, '+63') !== false && preg_match('/^639\d{9}$/', $digits) === 1) {
            return true;
        }

        // Otherwise, treat as email (allow spaces and various formats)
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false || preg_match('/^[\w\s.+-]+@[\w.-]+\.[a-zA-Z]{2,}$/', $value) === 1;
    }

    private function referenceContactFieldLabel(string $attribute): string
    {
        return match ($attribute) {
            'ref1_tel' => 'Reference 1 contact/email',
            'ref2_tel' => 'Reference 2 contact/email',
            'ref3_tel' => 'Reference 3 contact/email',
            default => str_replace('_', ' ', $attribute),
        };
    }

    private function normalizeGovtIdDateIssued($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return $value;
    }

    private function isValidGovtIdDateIssued($value): bool
    {
        $value = trim((string) $value);

        if ($value === '' || strtoupper($value) === 'N/A') {
            return true;
        }

        try {
            \Carbon\Carbon::parse($value);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function formatGovtIssuePlaceAndDate($place, $date): string
    {
        $rawGovtPlace = trim((string) $place);
        $govtPlace = strtoupper($rawGovtPlace) === 'N/A' ? '' : $rawGovtPlace;
        $rawGovtDate = trim((string) $date);

        if ($rawGovtDate === '' || strtoupper($rawGovtDate) === 'N/A') {
            $govtDate = '';
        } else {
            $govtDate = $this->normalizeDateForExcel($rawGovtDate);
            if ($govtDate === '') {
                $govtDate = $rawGovtDate;
            }
        }

        return implode(' | ', array_values(array_filter([$govtDate, $govtPlace], fn ($value) => $value !== '')));
    }

    public function c4ShowForm()
    {
        if (empty(session('form.c4'))) {
            $this->c4GetDatabase();
        }
        $data = session('form.c4', []);

        /*
        if (!empty($data['photo_upload'])) {
                $encodedPath = base64_encode($data['photo_upload']);
                $data['photo_preview_url'] = url('/preview-file/' . $encodedPath);
        } else {
                $data['photo_preview_url'] = null;
        }
        */
        //dd($data);
/*
        activity()
            ->causedBy(Auth::user())
            ->log('Viewed C4 form.');
*/
        return view('pds.c4', compact('data'));
    }

    // TODO: GETINDATABASE
    public function c4GetDatabase()
    {
        $current_user_id = Auth::id();
        $all_user_miscInfo_data = MiscInfos::where('user_id', '=', $current_user_id)->first();
        if ($all_user_miscInfo_data) {
            $data = $all_user_miscInfo_data->toArray();

            // Normalize unknown government ID types
            $valid_id_types = ['passport', 'gsis', 'sss', 'philhealth', 'drivers', 'prc', 'voters'];
            $original_govt_id = strtolower(trim($data['govt_id_type'] ?? ''));

            if (!in_array($original_govt_id, $valid_id_types)) {
                // Store custom/unknown value in govt_id_other
                $data['govt_id_other'] = $data['govt_id_type'];
                $data['govt_id_type'] = 'other';
            } else {
                // Just to ensure it's clean
                $data['govt_id_type'] = $original_govt_id;
            }

            // Handle "criminal_35_b_array" from "criminal_35_b"
            if (!empty($data['criminal_35_b']) && str_contains($data['criminal_35_b'], ',')) {
                [$date, $status] = explode(',', $data['criminal_35_b'], 2);
                $data['criminal_35_b_array'] = [
                    'date' => $date,
                    'status' => $status,
                ];
            } else {
                $data['criminal_35_b_array'] = [
                    'date' => null,
                    'status' => null,
                ];
            }

            /*
            // For Previewing of photo uploaded in live server
            if (!empty($data['photo_upload'])) {
                $encodedPath = base64_encode($data['photo_upload']);
                $data['photo_preview_url'] = url('/preview-file/' . $encodedPath);
            } else {
                $data['photo_preview_url'] = null;
            }
                */

            session(['form.c4' => $data]);

            //dd(session('form.c4'));
        }

        //session(['form.c4' => $all_user_miscInfo_data]);
        //dd($all_user_miscInfo_data);
    }

    private function resolveAutosaveCount($countValue, int $fallback = 0): int
    {
        if (is_array($countValue)) {
            $countValue = end($countValue);
        }

        if (!is_numeric($countValue)) {
            return max(0, $fallback);
        }

        return max(0, (int) $countValue);
    }

    private function hasAutosaveRowData(array $row, array $excludeKeys = []): bool
    {
        foreach ($row as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }
            if ($value !== null && $value !== '') {
                return true;
            }
        }
        return false;
    }

    public function autosaveDraft(Request $request, string $section)
    {
        $section = strtolower(trim($section));

        switch ($section) {
            case 'c1': {
                $existing = session('form.c1', []);
                if (!is_array($existing)) {
                    $existing = [];
                }
                $incoming = $request->except('_token');
                if (!is_array($incoming)) {
                    $incoming = [];
                }

                // Prevent transient blank autosave payloads from wiping address fields.
                $addressKeys = [
                    'res_house_no',
                    'res_street',
                    'res_sub_vil',
                    'res_brgy',
                    'res_city',
                    'res_province',
                    'res_zipcode',
                    'per_house_no',
                    'per_street',
                    'per_sub_vil',
                    'per_brgy',
                    'per_city',
                    'per_province',
                    'per_zipcode',
                ];
                foreach ($addressKeys as $key) {
                    if (!array_key_exists($key, $incoming)) {
                        continue;
                    }
                    $incomingValue = $incoming[$key];
                    $existingValue = $existing[$key] ?? null;
                    if (
                        is_string($incomingValue)
                        && trim($incomingValue) === ''
                        && is_string($existingValue)
                        && trim($existingValue) !== ''
                    ) {
                        unset($incoming[$key]);
                    }
                }

                session(['form.c1' => array_merge($existing, $incoming)]);
                break;
            }

            case 'c2': {
                $workIds = (array) $request->input('work_exp_id', []);
                $workFrom = (array) $request->input('work_exp_from', []);
                $workTo = (array) $request->input('work_exp_to', []);
                $workPosition = (array) $request->input('work_exp_position', []);
                $workDepartment = (array) $request->input('work_exp_department', []);
                $workStatus = (array) $request->input('work_exp_status', []);
                $workGov = (array) $request->input('work_exp_govt_service', []);

                $workCount = $this->resolveAutosaveCount(
                    $request->input('work_exp_count'),
                    count($workFrom)
                );

                $allWexData = [];
                for ($i = 0; $i < $workCount; $i++) {
                    $row = [
                        'id' => $workIds[$i] ?? null,
                        'user_id' => Auth::id(),
                        'work_exp_from' => trim(strip_tags((string) ($workFrom[$i] ?? ''))),
                        'work_exp_to' => trim(strip_tags((string) ($workTo[$i] ?? ''))),
                        'work_exp_position' => trim(strip_tags((string) ($workPosition[$i] ?? ''))),
                        'work_exp_department' => trim(strip_tags((string) ($workDepartment[$i] ?? ''))),
                        'work_exp_status' => trim(strip_tags((string) ($workStatus[$i] ?? ''))),
                        'work_exp_govt_service' => trim(strip_tags((string) ($workGov[$i] ?? ''))),
                    ];

                    if ($this->hasAutosaveRowData($row, ['id', 'user_id'])) {
                        $allWexData[] = $row;
                    }
                }

                $csIds = (array) $request->input('cs_eligibility_id', []);
                $csCareer = (array) $request->input('cs_eligibility_career', []);
                $csRating = (array) $request->input('cs_eligibility_rating', []);
                $csDate = (array) $request->input('cs_eligibility_date', []);
                $csPlace = (array) $request->input('cs_eligibility_place', []);
                $csLicense = (array) $request->input('cs_eligibility_license', []);
                $csValidity = (array) $request->input('cs_eligibility_validity', []);

                $civilCount = $this->resolveAutosaveCount(
                    $request->input('civil_service_count'),
                    count($csCareer)
                );

                $allCsData = [];
                for ($i = 0; $i < $civilCount; $i++) {
                    $row = [
                        'id' => $csIds[$i] ?? null,
                        'user_id' => Auth::id(),
                        'cs_eligibility_career' => trim(strip_tags((string) ($csCareer[$i] ?? ''))),
                        'cs_eligibility_rating' => trim(strip_tags((string) ($csRating[$i] ?? ''))),
                        'cs_eligibility_date' => trim(strip_tags((string) ($csDate[$i] ?? ''))),
                        'cs_eligibility_place' => trim(strip_tags((string) ($csPlace[$i] ?? ''))),
                        'cs_eligibility_license' => trim(strip_tags((string) ($csLicense[$i] ?? ''))),
                        'cs_eligibility_validity' => trim(strip_tags((string) ($csValidity[$i] ?? ''))),
                    ];

                    if ($this->hasAutosaveRowData($row, ['id', 'user_id'])) {
                        $allCsData[] = $row;
                    }
                }

                session([
                    'form.c2' => [
                        'all_user_work_exps' => $allWexData,
                        'all_user_civil_service_eligibility' => $allCsData,
                    ],
                ]);
                break;
            }

            case 'c3': {
                $entryCountLearning = $this->resolveAutosaveCount($request->input('learning_entry_count'));
                $entryCountVoluntary = $this->resolveAutosaveCount($request->input('voluntary_work_count'));

                $dataLearning = [];
                for ($i = 1; $i <= $entryCountLearning; $i++) {
                    $row = [
                        'learning_title' => trim((string) $request->input("learning_title_$i", '')),
                        'learning_type' => trim((string) $request->input("learning_type_$i", '')),
                        'learning_from' => trim((string) $request->input("learning_from_$i", '')),
                        'learning_to' => trim((string) $request->input("learning_to_$i", '')),
                        'learning_hours' => trim((string) $request->input("learning_hours_$i", '')),
                        'learning_conducted' => trim((string) $request->input("learning_conducted_$i", '')),
                    ];
                    if ($this->hasAutosaveRowData($row)) {
                        $dataLearning[] = $row;
                    }
                }

                $dataVoluntary = [];
                for ($i = 1; $i <= $entryCountVoluntary; $i++) {
                    $row = [
                        'voluntary_org' => trim((string) $request->input("voluntary_org_$i", '')),
                        'voluntary_from' => trim((string) $request->input("voluntary_from_$i", '')),
                        'voluntary_to' => trim((string) $request->input("voluntary_to_$i", '')),
                        'voluntary_hours' => trim((string) $request->input("voluntary_hours_$i", '')),
                        'voluntary_position' => trim((string) $request->input("voluntary_position_$i", '')),
                    ];
                    if ($this->hasAutosaveRowData($row)) {
                        $dataVoluntary[] = $row;
                    }
                }

                $skills = $request->input('skills', []);
                $distinctions = $request->input('distinctions', []);
                $organizations = $request->input('organizations', []);

                if (!is_array($skills)) {
                    $skills = [$skills];
                }
                if (!is_array($distinctions)) {
                    $distinctions = [$distinctions];
                }
                if (!is_array($organizations)) {
                    $organizations = [$organizations];
                }

                $skills = array_values(array_filter($skills, fn($v) => $v !== null && $v !== ''));
                $distinctions = array_values(array_filter($distinctions, fn($v) => $v !== null && $v !== ''));
                $organizations = array_values(array_filter($organizations, fn($v) => $v !== null && $v !== ''));

                session([
                    'data_learning' => $dataLearning,
                    'data_voluntary' => $dataVoluntary,
                    'data_otherInfo' => [
                        'skill' => $skills,
                        'distinction' => $distinctions,
                        'organization' => $organizations,
                        'user_id' => Auth::id(),
                    ],
                ]);
                break;
            }

            case 'c4': {
                $existing = session('form.c4', []);
                if (!is_array($existing)) {
                    $existing = [];
                }
                $incoming = $request->except('_token');
                if (!is_array($incoming)) {
                    $incoming = [];
                }

                $merged = $existing;

                $directScalarFields = [
                    'related_34_a',
                    'ref1_name',
                    'ref1_address',
                    'ref2_name',
                    'ref2_address',
                    'ref3_name',
                    'ref3_address',
                    'govt_id_number',
                ];

                foreach ($directScalarFields as $field) {
                    if (array_key_exists($field, $incoming)) {
                        $merged[$field] = trim((string) $incoming[$field]);
                    }
                }

                $detailMappings = [
                    ['selection' => 'related_34_b', 'detail' => 'related_34_b_details', 'target' => 'related_34_b'],
                    ['selection' => 'guilty_35_a', 'detail' => 'guilty_35_a_details', 'target' => 'guilty_35_a'],
                    ['selection' => 'convicted_36', 'detail' => 'convicted_36_details', 'target' => 'convicted_36'],
                    ['selection' => 'separated_37', 'detail' => 'separated_37_details', 'target' => 'separated_37'],
                    ['selection' => 'candidate_38_a', 'detail' => 'candidate_38_a_details', 'target' => 'candidate_38'],
                    ['selection' => 'resigned_38_b', 'detail' => 'resigned_38_b_details', 'target' => 'resigned_38_b'],
                    ['selection' => 'immigrant_39', 'detail' => 'immigrant_39_details', 'target' => 'immigrant_39'],
                    ['selection' => 'indigenous_40_a', 'detail' => 'indigenous_40_a_details', 'target' => 'indigenous_40_a'],
                    ['selection' => 'pwd_40_b', 'detail' => 'pwd_40_b_details', 'target' => 'pwd_40_b'],
                    ['selection' => 'solo_parent_40_c', 'detail' => 'solo_parent_40_c_details', 'target' => 'solo_parent_40_c'],
                ];

                foreach ($detailMappings as $mapping) {
                    $merged[$mapping['target']] = $this->resolveAutosaveDetailValue(
                        $incoming,
                        $existing,
                        $mapping['selection'],
                        $mapping['detail'],
                        $mapping['target']
                    );
                }

                $existingCriminalArray = $existing['criminal_35_b_array'] ?? [];
                if (!is_array($existingCriminalArray)) {
                    $existingCriminalArray = [];
                }

                $incomingCriminalArray = $incoming['criminal_35_b_details'] ?? null;
                $criminalDate = trim((string) ($existingCriminalArray['date'] ?? ''));
                $criminalStatus = trim((string) ($existingCriminalArray['status'] ?? ''));

                if (is_array($incomingCriminalArray)) {
                    $criminalDate = trim((string) ($incomingCriminalArray['date'] ?? $criminalDate));
                    $criminalStatus = trim((string) ($incomingCriminalArray['status'] ?? $criminalStatus));
                }

                $criminalSelection = array_key_exists('criminal_35_b', $incoming)
                    ? trim((string) $incoming['criminal_35_b'])
                    : null;

                if ($criminalSelection === 'yes') {
                    $merged['criminal_35_b'] = trim($criminalDate . ',' . $criminalStatus, ',');
                    $merged['criminal_35_b_array'] = [
                        'date' => $criminalDate,
                        'status' => $criminalStatus,
                    ];
                } elseif ($criminalSelection === 'no') {
                    $merged['criminal_35_b'] = 'no';
                    $merged['criminal_35_b_array'] = [
                        'date' => '',
                        'status' => '',
                    ];
                } else {
                    if (!array_key_exists('criminal_35_b_array', $merged)) {
                        $merged['criminal_35_b_array'] = [
                            'date' => $criminalDate,
                            'status' => $criminalStatus,
                        ];
                    }
                }

                if (array_key_exists('ref1_tel', $incoming)) {
                    $merged['ref1_tel'] = $this->normalizeReferenceContact($incoming['ref1_tel']);
                }
                if (array_key_exists('ref2_tel', $incoming)) {
                    $merged['ref2_tel'] = $this->normalizeReferenceContact($incoming['ref2_tel']);
                }
                if (array_key_exists('ref3_tel', $incoming)) {
                    $merged['ref3_tel'] = $this->normalizeReferenceContact($incoming['ref3_tel']);
                }

                if (array_key_exists('govt_id_other', $incoming)) {
                    $merged['govt_id_other'] = trim((string) $incoming['govt_id_other']);
                }

                if (array_key_exists('govt_id_type', $incoming)) {
                    $govtIdType = trim((string) $incoming['govt_id_type']);
                    if ($govtIdType === 'other') {
                        $govtIdOther = trim((string) ($incoming['govt_id_other'] ?? ($existing['govt_id_other'] ?? '')));
                        if ($govtIdOther !== '') {
                            $govtIdType = $govtIdOther;
                        }
                    }
                    $merged['govt_id_type'] = $govtIdType;
                }

                $dateNotApplicable = array_key_exists('govt_id_date_not_applicable', $incoming)
                    && (string) $incoming['govt_id_date_not_applicable'] === '1';
                $placeNotApplicable = array_key_exists('govt_id_place_not_applicable', $incoming)
                    && (string) $incoming['govt_id_place_not_applicable'] === '1';

                // Keep only one field marked as not applicable.
                if ($dateNotApplicable && $placeNotApplicable) {
                    $placeNotApplicable = false;
                }

                if ($dateNotApplicable) {
                    $merged['govt_id_date_issued'] = 'N/A';
                } elseif (array_key_exists('govt_id_date_issued', $incoming)) {
                    $merged['govt_id_date_issued'] = $this->normalizeGovtIdDateIssued($incoming['govt_id_date_issued']);
                }

                if ($placeNotApplicable) {
                    $merged['govt_id_place_issued'] = 'N/A';
                } elseif (array_key_exists('govt_id_place_issued', $incoming)) {
                    $merged['govt_id_place_issued'] = trim((string) $incoming['govt_id_place_issued']);
                }

                $merged['user_id'] = Auth::id();
                session(['form.c4' => $merged]);
                break;
            }

            case 'wes': {
                $incomingEntries = $request->input('entries', []);
                if (!is_array($incomingEntries)) {
                    $incomingEntries = [];
                }

                $sessionEntries = [];

                foreach ($incomingEntries as $rawEntry) {
                    if (!is_array($rawEntry)) {
                        continue;
                    }

                    $startDateRaw = trim((string) ($rawEntry['start_date'] ?? ''));
                    $endDateRaw = trim((string) ($rawEntry['end_date'] ?? ''));
                    $position = trim((string) ($rawEntry['position'] ?? ''));
                    $office = trim((string) ($rawEntry['office'] ?? ''));
                    $supervisor = trim((string) ($rawEntry['supervisor'] ?? ''));
                    $agency = trim((string) ($rawEntry['agency'] ?? ''));

                    $present = in_array(
                        strtolower(trim((string) ($rawEntry['present'] ?? '0'))),
                        ['1', 'true', 'on', 'yes'],
                        true
                    );
                    $isDisplayed = !in_array(
                        strtolower(trim((string) ($rawEntry['isDisplayed'] ?? '1'))),
                        ['0', 'false', 'off', 'no'],
                        true
                    );

                    $accomplishmentsRaw = $rawEntry['accomplishments'] ?? [];
                    if (!is_array($accomplishmentsRaw)) {
                        $accomplishmentsRaw = [$accomplishmentsRaw];
                    }
                    $accomplishments = array_values(array_filter(array_map(
                        static fn($value) => trim((string) $value),
                        $accomplishmentsRaw
                    ), static fn($value) => $value !== ''));

                    $dutiesRaw = $rawEntry['duties'] ?? [];
                    if (!is_array($dutiesRaw)) {
                        $dutiesRaw = [$dutiesRaw];
                    }
                    $duties = array_values(array_filter(array_map(
                        static fn($value) => trim((string) $value),
                        $dutiesRaw
                    ), static fn($value) => $value !== ''));

                    $hasAnyData =
                        $startDateRaw !== '' ||
                        $endDateRaw !== '' ||
                        $position !== '' ||
                        $office !== '' ||
                        $supervisor !== '' ||
                        $agency !== '' ||
                        !empty($accomplishments) ||
                        !empty($duties);

                    if (!$hasAnyData) {
                        continue;
                    }

                    $sessionEntries[] = [
                        'start_date' => $startDateRaw,
                        'end_date' => $present ? null : $endDateRaw,
                        'position' => $position,
                        'office' => $office,
                        'supervisor' => $supervisor,
                        'agency' => $agency,
                        'accomplishments' => !empty($accomplishments) ? $accomplishments : [''],
                        'duties' => !empty($duties) ? $duties : [''],
                        'isDisplayed' => $isDisplayed,
                        'present' => $present,
                    ];
                }

                session([
                    'form.wes' => [
                        'entries' => $sessionEntries,
                        'saved_at' => now()->toIso8601String(),
                    ],
                ]);

                break;
            }

            default:
                return response()->json([
                    'ok' => false,
                    'message' => 'Unsupported autosave section.',
                ], 422);
        }

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);

        return response()->json([
            'ok' => true,
            'section' => $section,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    // END C4 CONTROLLER
    // ==============================================================================



    /**
     * Stores all files in a local filesystem (subject to change, probably 💩).
     * Handles auto-deletion of existing files and auto-updating on database
     *
     * @param UploadedFile[] $files
     * @return void
     */
    private function c5StoreFilesToDB(array $files, ?string $vacancyId = null): array
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $supportsRevisionTracking = Schema::hasColumn('uploaded_documents', 'revision_requested_count')
            && Schema::hasColumn('uploaded_documents', 'revision_submitted_at');

        $storedPaths = [];
        foreach ($files as $doc_type => $file) {

            $hashed_name = $file->hashName();
            $store_path = $file->store("uploads/pds-files", 'public');
            if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
                // Reuse legacy null-vacancy row when present to preserve revision history/state.
                $document = UploadedDocument::where('user_id', Auth::id())
                    ->where('document_type', $doc_type)
                    ->where(function ($query) use ($vacancyId) {
                        $query->where('vacancy_id', $vacancyId)->orWhereNull('vacancy_id');
                    })
                    ->orderByRaw("CASE WHEN vacancy_id = ? THEN 0 ELSE 1 END", [$vacancyId])
                    ->orderByDesc('updated_at')
                    ->first();

                if (!$document) {
                    $document = UploadedDocument::create([
                        'user_id' => Auth::id(),
                        'vacancy_id' => $vacancyId,
                        'document_type' => $doc_type,
                    ]);
                }
            } else {
                $match = [
                    'user_id' => Auth::id(),
                    'document_type' => $doc_type
                ];
                if ($supportsVacancyScopedDocs) {
                    $match['vacancy_id'] = $vacancyId;
                }

                $document = UploadedDocument::firstOrCreate($match);
            }

            // Auto-delete if file_paths don't match and if an item.
            if (
                !empty($document->storage_path) &&
                ($document->storage_path !== $store_path) &&
                Storage::disk('public')->exists($document->storage_path)
            ) {
                Storage::disk('public')->delete($document->storage_path);
            }

            $updates = [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $hashed_name,
                'storage_path' => $store_path,
                'mime_type' => $file->getMimeType(),
                'file_size_8b' => $file->getSize(),
                'status' => 'Pending', // Reset status to Pending on new upload
                'remarks' => ''      // Clear old remarks
            ];
            if (
                $supportsRevisionTracking
                && (int) ($document->revision_requested_count ?? 0) > 0
            ) {
                // Always stamp latest compliance submission against the latest revision request.
                $updates['revision_submitted_at'] = now();
            }
            if ($supportsVacancyScopedDocs) {
                $updates['vacancy_id'] = $vacancyId;
            }

            $document->update($updates);
            $storedPaths[] = $store_path;

        }
        activity()
            ->causedBy(Auth::user())
            ->log('Store C5 form.');

        return $storedPaths;
    }

    private function isRevisionStatus(?string $status): bool
    {
        $normalized = strtolower(trim((string) $status));
        return in_array($normalized, ['needs revision', 'disapproved with deficiency'], true);
    }

    private function hasSatisfiedLatestRevisionRequest(?string $requestedAt, ?string $submittedAt): bool
    {
        if (empty($submittedAt)) {
            return false;
        }

        if (empty($requestedAt)) {
            // Legacy fallback when requested_at is unavailable: any submission means request satisfied.
            return true;
        }

        try {
            return Carbon::parse($submittedAt)->greaterThanOrEqualTo(Carbon::parse($requestedAt));
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function isRevisionComplianceLocked(int $requestedCount, ?string $requestedAt, ?string $submittedAt): bool
    {
        return $requestedCount >= 2 && $this->hasSatisfiedLatestRevisionRequest($requestedAt, $submittedAt);
    }

    private function hasRevisionDeadlinePassed(?Applications $application): bool
    {
        if (!$application || empty($application->deadline_date) || empty($application->deadline_time)) {
            return false;
        }

        try {
            $timezone = (string) config('app.timezone', 'Asia/Manila');
            $deadline = Carbon::parse(
                $application->deadline_date . ' ' . $application->deadline_time,
                $timezone
            );
            return Carbon::now($timezone)->greaterThan($deadline);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function hasFinalRevisionDisqualification(Applications $application, string $vacancyId): bool
    {
        if ($this->isRevisionComplianceLocked(
            (int) ($application->file_revision_requested_count ?? 0),
            $application->file_revision_requested_at ?? null,
            $application->file_revision_submitted_at ?? null
        )) {
            return true;
        }

        if (!Schema::hasColumn('uploaded_documents', 'revision_requested_count')) {
            return false;
        }

        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $supportsRequestedAt = Schema::hasColumn('uploaded_documents', 'revision_requested_at');
        $supportsSubmittedAt = Schema::hasColumn('uploaded_documents', 'revision_submitted_at');
        $docsQuery = UploadedDocument::query()
            ->where('user_id', $application->user_id)
            ->where('revision_requested_count', '>=', 2);

        if ($supportsVacancyScopedDocs && $vacancyId !== '') {
            $docsQuery->where(function ($q) use ($vacancyId) {
                $q->where('vacancy_id', $vacancyId)
                    ->orWhereNull('vacancy_id');
            });
        }

        $select = ['revision_requested_count'];
        if ($supportsRequestedAt) {
            $select[] = 'revision_requested_at';
        }
        if ($supportsSubmittedAt) {
            $select[] = 'revision_submitted_at';
        }

        $docs = $docsQuery->get($select);
        return $docs->contains(function ($doc) use ($supportsRequestedAt, $supportsSubmittedAt) {
            $requestedAt = $supportsRequestedAt ? ($doc->revision_requested_at ?? null) : null;
            $submittedAt = $supportsSubmittedAt ? ($doc->revision_submitted_at ?? null) : null;
            return $this->isRevisionComplianceLocked((int) ($doc->revision_requested_count ?? 0), $requestedAt, $submittedAt);
        });
    }

    private function hasUploadedDocumentForType($documents, string $docType): bool
    {
        $candidates = array_merge([$docType], self::DOCUMENT_TYPE_ALIASES[$docType] ?? []);
        foreach ($candidates as $candidate) {
            $doc = $documents[$candidate] ?? null;
            if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
                return true;
            }
        }
        return false;
    }

    private function resolveUploadedFileFromPayload(array $uploads, string $docType): ?UploadedFile
    {
        $candidates = array_merge([$docType], self::DOCUMENT_TYPE_ALIASES[$docType] ?? []);
        foreach ($candidates as $candidate) {
            $file = $uploads[$candidate] ?? null;
            if ($file instanceof UploadedFile) {
                return $file;
            }
        }

        return null;
    }

    private function normalizeUploadAliasMap(array $uploads): array
    {
        foreach (self::DOCUMENT_TYPE_ALIASES as $docType => $aliases) {
            if (array_key_exists($docType, $uploads)) {
                continue;
            }

            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $uploads)) {
                    $uploads[$docType] = $uploads[$alias];
                    break;
                }
            }
        }

        return $uploads;
    }

    private function resolveUploadedDocument($documents, string $docType): ?UploadedDocument
    {
        $doc = $documents[$docType] ?? null;
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }

        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $documents[$alias] ?? null;
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }

        return $doc instanceof UploadedDocument ? $doc : null;
    }

    private function resolveDocumentGalleryItem($documents, string $docType): ?DocumentGalleryItem
    {
        $doc = $documents[$docType] ?? null;
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }

        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $documents[$alias] ?? null;
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }

        return $doc instanceof DocumentGalleryItem ? $doc : null;
    }

    private function loadDocumentGalleryMap(int $userId)
    {
        return DocumentGalleryItem::query()
            ->where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function hasGalleryDocumentForType($documents, string $docType): bool
    {
        $candidates = array_merge([$docType], self::DOCUMENT_TYPE_ALIASES[$docType] ?? []);
        foreach ($candidates as $candidate) {
            $doc = $documents[$candidate] ?? null;
            if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
                return true;
            }
        }
        return false;
    }

    private function loadReusableUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $query = UploadedDocument::where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');

        if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
            $query->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancyId]
            );
        } elseif ($supportsVacancyScopedDocs) {
            $query->orderByRaw('CASE WHEN vacancy_id IS NULL THEN 0 ELSE 1 END');
        }

        return $query
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function upsertVacancyDocumentFromSource(
        UploadedDocument $source,
        string $vacancyId,
        string $targetDocType
    ): UploadedDocument {
        $destination = UploadedDocument::where('user_id', (int) $source->user_id)
            ->where('vacancy_id', $vacancyId)
            ->where('document_type', $targetDocType)
            ->orderByDesc('updated_at')
            ->first();

        $payload = [
            'original_name' => $source->original_name,
            'stored_name' => $source->stored_name,
            'storage_path' => $source->storage_path,
            'mime_type' => $source->mime_type,
            'file_size_8b' => $source->file_size_8b,
            'status' => 'Pending',
            'remarks' => '',
            'last_modified_by' => Auth::user()?->name ?? 'System',
        ];

        if ($destination) {
            $destination->update($payload);
            return $destination;
        }

        return UploadedDocument::create(array_merge($payload, [
            'user_id' => (int) $source->user_id,
            'vacancy_id' => $vacancyId,
            'document_type' => $targetDocType,
        ]));
    }

    private function seedVacancyDocumentsFromReusableUploads(int $userId, string $vacancyId, array $requiredDocs): void
    {
        if (
            empty($vacancyId)
            || empty($requiredDocs)
            || !Schema::hasColumn('uploaded_documents', 'vacancy_id')
        ) {
            return;
        }

        $vacancyDocs = UploadedDocument::where('user_id', $userId)
            ->where('vacancy_id', $vacancyId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $reusableDocs = $this->loadReusableUploadedDocumentsMap($userId, $vacancyId);

        foreach ($requiredDocs as $docType) {
            if ($this->hasUploadedDocumentForType($vacancyDocs, (string) $docType)) {
                continue;
            }

            $sourceDoc = $this->resolveUploadedDocument($reusableDocs, (string) $docType);
            if (!$sourceDoc || empty($sourceDoc->storage_path) || $sourceDoc->storage_path === 'NOINPUT') {
                continue;
            }

            $seeded = $this->upsertVacancyDocumentFromSource($sourceDoc, $vacancyId, (string) $docType);
            $vacancyDocs->put((string) $docType, $seeded);
        }
    }

    private function hasCompleteRequiredDocsForVacancy(
        int $userId,
        string $vacancyId,
        array $requiredDocs
    ): bool {
        if (empty($requiredDocs)) {
            return false;
        }

        $documents = $this->loadDocumentGalleryMap($userId);

        foreach ($requiredDocs as $docType) {
            if (!$this->hasGalleryDocumentForType($documents, $docType)) {
                return false;
            }
        }

        return true;
    }

    private function getSupportingDocumentTypes(): array
    {
        return array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn ($doc) => $doc !== 'isApproved'
        ));
    }

    private function normalizeSupportingDocumentSelection($selection): array
    {
        if (is_string($selection)) {
            $decodedSelection = json_decode($selection, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $selection = $decodedSelection;
            }
        }

        if (!is_array($selection)) {
            return [];
        }

        $allowedTypes = array_fill_keys($this->getSupportingDocumentTypes(), true);
        $normalizedSelection = [];

        foreach ($selection as $documentType) {
            $documentType = (string) $documentType;
            if (isset($allowedTypes[$documentType])) {
                $normalizedSelection[] = $documentType;
            }
        }

        return array_values(array_unique($normalizedSelection));
    }

    private function getRequiredDocumentIdsForVacancy(?Models\JobVacancy $vacancy = null, ?string $docTrack = null): array
    {
        $hasStoredSelection = $vacancy && $vacancy->supporting_documents_required !== null;
        $normalizedTrack = strcasecmp((string) $docTrack, 'COS') === 0 ? 'COS' : 'Plantilla';
        $requiredByTrack = $this->getRequiredDocsByTrack();

        $requiredDocs = $hasStoredSelection
            ? $this->normalizeSupportingDocumentSelection($vacancy->supporting_documents_required)
            : ($requiredByTrack[$normalizedTrack] ?? []);

        return array_values(array_unique($requiredDocs));
    }


    /**
     * Displays the C5 page for PDS.
     * @return \Illuminate\Contracts\View\View
     */
    public function c5DisplayForm()
    {
        $user = Auth::user();

        // ✅ Fix the quote in the view name
        $latestApplication = Applications::where('user_id', $user->id)
            ->with('vacancy')
            ->latest()
            ->first();

        $applicationVacancyId = request('vacancy_id');
        $vacancyForApplication = null;
        if (!empty($applicationVacancyId)) {
            $vacancyForApplication = Models\JobVacancy::where('vacancy_id', $applicationVacancyId)->first();
            if (!$vacancyForApplication) {
                return redirect()->back()->withErrors(['vacancy_id' => 'Selected vacancy was not found.']);
            }
        }
        $hasExistingApplicationLetter = Applications::where('user_id', $user->id)
            ->whereNotNull('file_storage_path')
            ->exists();
        $latestApplicationLetterPath = Applications::where('user_id', $user->id)
            ->whereNotNull('file_storage_path')
            ->latest('updated_at')
            ->value('file_storage_path');
        $applicationLetterPreviewUrl = !empty($latestApplicationLetterPath)
            ? PreviewUrl::forPath((string) $latestApplicationLetterPath)
            : null;

        // Reuse previously uploaded files from prior applications.
        $documents = $this->loadReusableUploadedDocumentsMap(
            (int) $user->id,
            !empty($applicationVacancyId) ? (string) $applicationVacancyId : null
        );
        $documentsResolved = [];
        foreach (array_keys($this->getDocumentLabelMap()) as $docType) {
            $documentsResolved[$docType] = $this->resolveUploadedDocument($documents, (string) $docType);
        }

        // Supporting-document existence should always mirror current document gallery state.
        $galleryDocuments = $this->loadDocumentGalleryMap((int) $user->id);
        $galleryDocumentsResolved = [];
        foreach (array_keys($this->getDocumentLabelMap()) as $docType) {
            $galleryDocumentsResolved[$docType] = $this->resolveDocumentGalleryItem($galleryDocuments, (string) $docType);
        }

        $defaultDocTrack = request('doc_track');
        if ($vacancyForApplication) {
            $defaultDocTrack = strcasecmp((string) $vacancyForApplication->vacancy_type, 'COS') === 0 ? 'COS' : 'Plantilla';
        }
        if (!in_array($defaultDocTrack, ['COS', 'Plantilla'], true)) {
            $defaultDocTrack = $latestApplication?->vacancy?->vacancy_type;
        }
        if (!in_array($defaultDocTrack, ['COS', 'Plantilla'], true)) {
            $defaultDocTrack = 'Plantilla';
        }

        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $vacancyRequiredDocumentIds = $this->getRequiredDocumentIdsForVacancy($vacancyForApplication, $defaultDocTrack);
        $documentLabels = $this->getDocumentLabelMap();
        $isFreshUpload = in_array(request('fresh_upload'), [1, '1', true, 'true'], true) || !empty($applicationVacancyId);
        $hasFreshUploadForVacancy = false;
        if (!empty($applicationVacancyId)) {
            $hasFreshUploadForVacancy = $this->hasCompleteRequiredDocsForVacancy(
                (int) $user->id,
                (string) $applicationVacancyId,
                $vacancyRequiredDocumentIds
            );

            Log::info('C5 display with vacancy context', [
                'user_id' => (int) $user->id,
                'vacancy_id' => (string) $applicationVacancyId,
                'doc_track' => $defaultDocTrack,
                'forced_fresh_upload' => $isFreshUpload,
                'has_complete_required_docs' => $hasFreshUploadForVacancy,
            ]);
        }

        return view('pds.c5', compact(
            'documents',
            'documentsResolved',
            'galleryDocumentsResolved',
            'defaultDocTrack',
            'requiredDocsByTrack',
            'vacancyRequiredDocumentIds',
            'documentLabels',
            'hasExistingApplicationLetter',
            'applicationLetterPreviewUrl',
            'applicationVacancyId',
            'isFreshUpload',
            'hasFreshUploadForVacancy'
        ));
    }



    /**
     * The transition functionality for submission of data. This should perform
     * the uploading of files to the filesystem and createing/updating metadata
     * for that specific file for future retrieval.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\RedirectResponse
     */
    public function finalizePDS(Request $request, $go_to)
    {
        $request->validate(
            [
                'declaration' => 'accepted',
                'consent' => 'accepted',
                'confirmation' => 'accepted',
            ],
            [
                'declaration.accepted' => 'Please check the declaration checkbox to continue.',
                'consent.accepted' => 'Please check the consent checkbox to continue.',
                'confirmation.accepted' => 'Please check the confirmation checkbox to continue.',
            ]
        );

        $docTrack = $request->input('doc_track', 'Plantilla');
        if (!in_array($docTrack, ['COS', 'Plantilla'], true)) {
            $docTrack = 'Plantilla';
        }
        $applicationVacancyId = $request->input('vacancy_id');
        $refererVacancyId = $this->extractVacancyIdFromReferer($request->headers->get('referer'));
        if (!empty($refererVacancyId) && $refererVacancyId !== $applicationVacancyId) {
            Log::warning('C5 vacancy_id mismatch detected; using referer vacancy_id', [
                'user_id' => Auth::id(),
                'posted_vacancy_id' => $applicationVacancyId,
                'referer_vacancy_id' => $refererVacancyId,
                'referer' => $request->headers->get('referer'),
            ]);
            $applicationVacancyId = $refererVacancyId;
        }

        if (!empty($applicationVacancyId)) {
            Log::info('C5 finalize vacancy context', [
                'user_id' => Auth::id(),
                'vacancy_id' => $applicationVacancyId,
                'go_to' => $go_to,
                'fresh_upload' => $request->input('fresh_upload'),
            ]);
        }
        $vacancyForApplication = null;
        if (!empty($applicationVacancyId)) {
            $vacancyForApplication = Models\JobVacancy::where('vacancy_id', $applicationVacancyId)->first();
            if (!$vacancyForApplication) {
                return back()->withErrors(['vacancy_id' => 'Selected vacancy was not found.'])->withInput();
            }
            $docTrack = strcasecmp((string) $vacancyForApplication->vacancy_type, 'COS') === 0 ? 'COS' : 'Plantilla';
        }

        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        $requiredDocs = !empty($applicationVacancyId)
            ? $this->getRequiredDocumentIdsForVacancy($vacancyForApplication, $docTrack)
            : ($requiredDocsByTrack[$docTrack] ?? []);
        $documentLabels = $this->getDocumentLabelMap();
        $uploadedFilesPayload = $request->file('cert_uploads', []);
        if (!is_array($uploadedFilesPayload)) {
            $uploadedFilesPayload = [];
        }
        $uploadedFilesPayload = $this->normalizeUploadAliasMap($uploadedFilesPayload);
        $request->files->set('cert_uploads', $uploadedFilesPayload);

        /********************************
         * +++++ Required Documents
         ********************************/
        // User is allowed to not upload any file
        $request->validate([
            'cert_uploads.application_letter' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.pqe_result' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_eligibility' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_elegibility' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.ipcr' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.non_academic' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_training' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.designation_order' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.transcript_records' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.photocopy_diploma' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.grade_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.tor_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_employment' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.other_documents' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.signed_pds' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.signed_work_exp_sheet' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_lgoo_induction' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.passport_photo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ], [
            'cert_uploads.*.mimes' => 'Only PDF files are allowed.',
            'cert_uploads.*.max' => 'Each file must be 10MB or smaller.',
        ]);

        $existingDocs = $this->loadDocumentGalleryMap((int) Auth::id());

        $existingDocLookup = [];
        foreach ($requiredDocs as $docType) {
            if ($this->hasGalleryDocumentForType($existingDocs, $docType)) {
                $existingDocLookup[$docType] = true;
            }
        }

        $uploaded_files = [];
        $upload_errors = [];
        $submittedUploadLookup = [];
        $validUploadLookup = [];
        foreach (UploadedDocument::DOCUMENTS as $_access) {
            if ($_access === 'isApproved') {
                continue;
            }

            // Files not present in the request should not be processed.
            $_file = $this->resolveUploadedFileFromPayload($uploadedFilesPayload, (string) $_access);
            if (!$_file) {
                continue;
            }
            $submittedUploadLookup[$_access] = true;

            // Check if the requested file has any upload errors.
            if (!$_file->isValid()) {
                $upload_errors["cert_uploads.$_access"] = $this->resolveUploadErrorMessage($_file);
                continue;
            }

            $allowImage = $_access === 'passport_photo';
            [$is_valid, $message] = $this->validateUploadedFile($_file, $allowImage);
            if (!$is_valid) {
                $upload_errors["cert_uploads.$_access"] = $message;
                continue;
            }

            [$scan_ok, $scan_message] = $this->scanUploadedFile($_file);
            if (!$scan_ok) {
                $upload_errors["cert_uploads.$_access"] = $scan_message;
                continue;
            }

            $uploaded_files[$_access] = $_file;
            $validUploadLookup[$_access] = true;
        }

        $missingRequiredDocs = [];
        foreach ($requiredDocs as $docType) {
            if (isset($existingDocLookup[$docType])) {
                continue;
            }
            if (isset($validUploadLookup[$docType])) {
                continue;
            }
            if (isset($submittedUploadLookup[$docType])) {
                continue;
            }
            $missingRequiredDocs[] = $docType;
        }

        $errors = $upload_errors;
        foreach ($missingRequiredDocs as $docType) {
            $field = "cert_uploads.$docType";
            if (isset($errors[$field])) {
                continue;
            }

            $label = $documentLabels[$docType] ?? str_replace('_', ' ', $docType);
            $errors[$field] = "{$label} is required for {$docTrack} applications.";
        }

        if (!empty($missingRequiredDocs)) {
            Log::warning('C5 missing required documents', [
                'user_id' => Auth::id(),
                'vacancy_id' => $applicationVacancyId,
                'doc_track' => $docTrack,
                'missing_docs' => $missingRequiredDocs,
                'existing_doc_keys' => array_keys($existingDocLookup),
                'submitted_upload_keys' => array_keys($submittedUploadLookup),
                'valid_upload_keys' => array_keys($validUploadLookup),
                'upload_error_fields' => array_keys($upload_errors),
                'raw_upload_keys' => array_keys($uploadedFilesPayload),
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $storedPaths = [];
        try {
            return DB::transaction(function () use ($request, $uploaded_files, &$storedPaths, $go_to, $applicationVacancyId, $docTrack) {
                $storedPaths = $this->c5StoreFilesToDB($uploaded_files, $applicationVacancyId);
                if (!empty($applicationVacancyId)) {
                    $allDocumentTypes = array_values(array_filter(
                        UploadedDocument::DOCUMENTS,
                        fn($docType) => $docType !== 'isApproved'
                    ));
                    $this->seedVacancyDocumentsFromReusableUploads(
                        (int) Auth::id(),
                        (string) $applicationVacancyId,
                        $allDocumentTypes
                    );
                }

                // Persist declaration/consent only when DB columns exist.
                if (Schema::hasTable('misc_infos')) {
                    $miscInfoUpdates = [];
                    if (Schema::hasColumn('misc_infos', 'declaration')) {
                        $miscInfoUpdates['declaration'] = $request->boolean('declaration') ? '1' : '0';
                    }
                    if (Schema::hasColumn('misc_infos', 'consent')) {
                        $miscInfoUpdates['consent'] = $request->boolean('consent') ? '1' : '0';
                    }

                    if (!empty($miscInfoUpdates)) {
                        $misc_info = MiscInfos::firstOrCreate(['user_id' => Auth::id()]);
                        $misc_info->update($miscInfoUpdates);
                    }
                }
                
                if (app()->environment('testing') && $request->boolean('simulate_failure')) {
                    throw new \RuntimeException('Simulated failure');
                }

                $hasSessionPayload = static function (string $key): bool {
                    $payload = session($key);
                    if (is_array($payload)) {
                        return !empty($payload);
                    }

                    return !empty($payload);
                };

                $shouldPersistPdsData = $hasSessionPayload('form.c1')
                    || $hasSessionPayload('form.c2')
                    || $hasSessionPayload('data_learning')
                    || $hasSessionPayload('data_voluntary')
                    || $hasSessionPayload('data_otherInfo')
                    || $hasSessionPayload('form.c4');

                if ($shouldPersistPdsData) {
                //********************************
                //* +++++ Personal Information
                //*******************************
                $c1_form_data = array_merge([
                    'surname' => '',
                    'first_name' => '',
                    'middle_name' => '',
                    'name_extension' => '',
                    'civil_status' => '',
                    'date_of_birth' => '',
                    'place_of_birth' => '',
                    'citizenship' => '',
                    'dual_type' => '',
                    'sex' => '',
                    'blood_type' => '',
                    'philhealth_no' => '',
                    'tin_no' => '',
                    'agency_employee_no' => '',
                    'gsis_id_no' => '',
                    'pagibig_id_no' => '',
                    'sss_id_no' => '',
                    'mobile_no' => '',
                    'email_address' => '',
                    'height' => '',
                    'weight' => '',
                    'telephone_no' => '',
                    'dual_country' => '',
                    'spouse_surname' => '',
                    'spouse_first_name' => '',
                    'spouse_middle_name' => '',
                    'spouse_name_extension' => '',
                    'spouse_occupation' => '',
                    'spouse_employer' => '',
                    'spouse_business_address' => '',
                    'spouse_telephone' => '',
                    'father_surname' => '',
                    'father_first_name' => '',
                    'father_middle_name' => '',
                    'father_name_extension' => '',
                    'mother_maiden_surname' => '',
                    'mother_maiden_first_name' => '',
                    'mother_maiden_middle_name' => '',
                    'res_house_no' => '',
                    'res_street' => '',
                    'res_sub_vil' => '',
                    'res_brgy' => '',
                    'res_city' => '',
                    'res_province' => '',
                    'res_zipcode' => '',
                    'per_house_no' => '',
                    'per_street' => '',
                    'per_sub_vil' => '',
                    'per_brgy' => '',
                    'per_city' => '',
                    'per_province' => '',
                    'per_zipcode' => '',
                    'elem_from' => '',
                    'elem_to' => '',
                    'elem_school' => '',
                    'elem_academic_honors' => '',
                    'elem_basic' => '',
                    'elem_earned' => '',
                    'elem_year_graduated' => '',
                    'jhs_from' => '',
                    'jhs_to' => '',
                    'jhs_school' => '',
                    'jhs_academic_honors' => '',
                    'jhs_basic' => '',
                    'jhs_earned' => '',
                    'jhs_year_graduated' => '',
                    'children' => [],
                    'vocational' => [],
                    'college' => [],
                    'grad' => [],
                    'declaration' => $request->input('declaration', '0'),
                    'consent' => $request->input('consent', '0'),
                    'confirmation' => $request->input('confirmation', '0'),
                ], session('form.c1', []));

                $dual_type_t = '';
                if ($c1_form_data['citizenship'] === 'Dual Citizen') {
                    $dual_type_t = $c1_form_data['dual_type'];
                }

                $_haystack = ['children', 'vocational', 'college', 'grad'];
                foreach ($c1_form_data as $_key => $_val) {

                    // Skips the haystack data to be handled later.
                    if (in_array($_key, $_haystack)) {
                        continue;
                    }

                    if (is_array($_val)) {
                        $flattened = [];

                        array_walk_recursive($_val, function ($v) use (&$flattened) {
                            // Only push if it's a string or something castable to string
                            if (is_scalar($v)) {
                                $flattened[] = strip_tags($v);
                            }
                        });

                        $_val = trim(implode(', ', $flattened));
                    } else {
                        $_val = trim(strip_tags($_val));
                    }

                }

                // format residential address for compact database insertion
                $house_no_t = ($c1_form_data['res_house_no'] != '') ? $c1_form_data['res_house_no'] : '{*}';
                $street_t = ($c1_form_data['res_street'] != '') ? $c1_form_data['res_street'] : '{*}';
                $sub_vil_t = ($c1_form_data['res_sub_vil'] != '') ? $c1_form_data['res_sub_vil'] : '{*}';
                $brgy_t = ($c1_form_data['res_brgy'] != '') ? $c1_form_data['res_brgy'] : '{*}';
                $city_t = ($c1_form_data['res_city'] != '') ? $c1_form_data['res_city'] : '{*}';
                $province_t = ($c1_form_data['res_province'] != '') ? $c1_form_data['res_province'] : '{*}';
                $zipcode_t = ($c1_form_data['res_zipcode'] != '') ? $c1_form_data['res_zipcode'] : '{*}';

                $formatted_residential_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

                // format permanent address
                $house_no_t = ($c1_form_data['per_house_no'] != '') ? $c1_form_data['per_house_no'] : '{*}';
                $street_t = ($c1_form_data['per_street'] != '') ? $c1_form_data['per_street'] : '{*}';
                $sub_vil_t = ($c1_form_data['per_sub_vil'] != '') ? $c1_form_data['per_sub_vil'] : '{*}';
                $brgy_t = ($c1_form_data['per_brgy'] != '') ? $c1_form_data['per_brgy'] : '{*}';
                $city_t = ($c1_form_data['per_city'] != '') ? $c1_form_data['per_city'] : '{*}';
                $province_t = ($c1_form_data['per_province'] != '') ? $c1_form_data['per_province'] : '{*}';
                $zipcode_t = ($c1_form_data['per_zipcode'] != '') ? $c1_form_data['per_zipcode'] : '{*}';

                $formatted_permanent_address = "$house_no_t/|/$street_t/|/$sub_vil_t/|/$brgy_t/|/$city_t/|/$province_t/|/$zipcode_t";

                // create a personal information record compact database insertion
                // IF the record does not exist for the current user.
                $user_personal_info = Models\PersonalInformation::firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                $dateOfBirthForUpdate = $this->normalizeDateForDatabase($c1_form_data['date_of_birth']);
                if (empty($dateOfBirthForUpdate)) {
                    $dateOfBirthForUpdate = !empty($user_personal_info->date_of_birth)
                        ? (string) $user_personal_info->date_of_birth
                        : Carbon::now()->toDateString();
                }

                $user_personal_info->update([
                    //'cs_id_no'                  => $c1_form_data['cs_id_no'],
                    'surname' => $c1_form_data['surname'],
                    'name_extension' => $c1_form_data['name_extension'],
                    'first_name' => $c1_form_data['first_name'],
                    'middle_name' => $c1_form_data['middle_name'],
                    'sex' => $c1_form_data['sex'],
                    'civil_status' => $c1_form_data['civil_status'],
                    'date_of_birth' => $dateOfBirthForUpdate,
                    'place_of_birth' => $c1_form_data['place_of_birth'],
                    'height' => $c1_form_data['height'],
                    'weight' => $c1_form_data['weight'],
                    'blood_type' => $c1_form_data['blood_type'],
                    'philhealth_no' => $c1_form_data['philhealth_no'],
                    'tin_no' => $c1_form_data['tin_no'],
                    'agency_employee_no' => $c1_form_data['agency_employee_no'],
                    'gsis_id_no' => $c1_form_data['gsis_id_no'],
                    'pagibig_id_no' => $c1_form_data['pagibig_id_no'],
                    'sss_id_no' => $c1_form_data['sss_id_no'],
                    'citizenship' => $c1_form_data['citizenship'],
                    'dual_type' => $dual_type_t,
                    'dual_country' => $c1_form_data['dual_country'],
                    'residential_address' => $formatted_residential_address,
                    'permanent_address' => $formatted_permanent_address,
                    'telephone_no' => $c1_form_data['telephone_no'],
                    'mobile_no' => $c1_form_data['mobile_no'],
                    'email_address' => $c1_form_data['email_address']
                ]);

                unset($user_personal_info);

                //********************************
                //* +++++ Family Background
                //*******************************

                $user_family_bg = Models\FamilyBackground::firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                $user_family_bg->update([
                    'spouse_surname' => $c1_form_data['spouse_surname'],
                    'spouse_first_name' => $c1_form_data['spouse_first_name'],
                    'spouse_middle_name' => $c1_form_data['spouse_middle_name'],
                    'spouse_name_extension' => $c1_form_data['spouse_name_extension'],
                    'spouse_occupation' => $c1_form_data['spouse_occupation'],
                    'spouse_employer' => $c1_form_data['spouse_employer'],
                    'spouse_business_address' => $c1_form_data['spouse_business_address'],
                    'spouse_telephone' => $c1_form_data['spouse_telephone'],
                    'father_surname' => $c1_form_data['father_surname'],
                    'father_first_name' => $c1_form_data['father_first_name'],
                    'father_middle_name' => $c1_form_data['father_middle_name'],
                    'father_name_extension' => $c1_form_data['father_name_extension'],
                    'mother_maiden_surname' => $c1_form_data['mother_maiden_surname'],
                    'mother_maiden_first_name' => $c1_form_data['mother_maiden_first_name'],
                    'mother_maiden_middle_name' => $c1_form_data['mother_maiden_middle_name'],
                    'children_info' => $c1_form_data['children']
                ]);

                unset($user_family_bg);

                //********************************
                //* +++++ Educational Background
                //********************************

                $user_educational_bg = Models\EducationalBackground::firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                // Determine if this is Senior High School data
                $isSeniorHigh = strtoupper(trim((string) ($c1_form_data['jhs_basic'] ?? ''))) === 'SENIOR HIGH SCHOOL';

                $elemFromDb = $this->normalizeStrictDateForDatabase($c1_form_data['elem_from'] ?? null);
                $elemToDb = $this->normalizeStrictDateForDatabase($c1_form_data['elem_to'] ?? null);
                $jhsFromDb = $this->normalizeStrictDateForDatabase($c1_form_data['jhs_from'] ?? null);
                $jhsToDb = $this->normalizeStrictDateForDatabase($c1_form_data['jhs_to'] ?? null);
                $elemFromDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data['elem_from'] ?? null);
                $elemToDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data['elem_to'] ?? null);
                $jhsFromDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data['jhs_from'] ?? null);
                $jhsToDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data['jhs_to'] ?? null);
                $shsFromDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data['shs_from'] ?? null);
                $shsToDb = $this->normalizeEducationMonthYearForDatabase($c1_form_data['shs_to'] ?? null);

                $user_educational_bg->update([
                    'elem_from' => $elemFromDb,
                    'elem_to' => $elemToDb,
                    'elem_school' => $c1_form_data['elem_school'],
                    'elem_academic_honors' => $c1_form_data['elem_academic_honors'],
                    'elem_basic' => $c1_form_data['elem_basic'],
                    'elem_earned' => $c1_form_data['elem_earned'],
                    'elem_year_graduated' => $c1_form_data['elem_year_graduated'],

                    'jhs_from' => $c1_form_data['jhs_from'],
                    'jhs_to' => $c1_form_data['jhs_to'],
                    'jhs_school' => $c1_form_data['jhs_school'],
                    'jhs_academic_honors' => $c1_form_data['jhs_academic_honors'],
                    'jhs_basic' => $c1_form_data['jhs_basic'],
                    'jhs_earned' => $c1_form_data['jhs_earned'],
                    'jhs_year_graduated' => $c1_form_data['jhs_year_graduated'],

                    /*
                    'shs_from'                  => $c1_form_data['shs_from'],
                    'shs_to'                    => $c1_form_data['shs_to'],
                    'shs_from'                  => $shsFromDb,
                    'shs_to'                    => $shsToDb,
                    'shs_school'                => $c1_form_data['shs_school'],
                    'shs_academic_honors'       => $c1_form_data['shs_academic_honors'],
                    'shs_basic'                 => $c1_form_data['shs_basic'],
                    'shs_earned'                => $c1_form_data['shs_earned'],
                    'shs_earned'                => $c1_form_data['shs_earned'],
                    'shs_year_graduated'        => $c1_form_data['shs_year_graduated'],
                    */

                    'vocational' => $c1_form_data['vocational'] ?? null,
                    'college' => $c1_form_data['college'],
                    'grad' => $c1_form_data['grad'] ?? null,
                ]);

                unset($user_educational_bg);
                // TODO: Fix null new user null required values. Create a middleware so that they cant skip ahead to c5

                // -------------
                // C2 INSERT TO DATABASE
                // ------------
                //********************************
                //* +++++ Work Experience
                //*******************************
                $stripAuditColumns = function (array $row): array {
                    unset($row['id'], $row['created_at'], $row['updated_at'], $row['deleted_at']);
                    return $row;
                };

                $c2_form_data = session('form.c2');
                if (isset($c2_form_data['all_user_work_exps'])) {
                    $user_all_wex_data = $c2_form_data['all_user_work_exps'];

                    WorkExperience::where('user_id', Auth::id())->delete();

                    for ($i = 0; $i < count($user_all_wex_data); $i++) {
                        $workRow = is_array($user_all_wex_data[$i]) ? $stripAuditColumns($user_all_wex_data[$i]) : [];
                        if (empty($workRow)) {
                            continue;
                        }

                        $workRow['user_id'] = Auth::id();
                        $workRow['work_exp_from'] = $this->normalizeDateForDatabase($workRow['work_exp_from'] ?? null);
                        $workRow['work_exp_to'] = $this->normalizeWorkExperienceEndDateForDatabase($workRow['work_exp_to'] ?? null);

                        WorkExperience::upsert($workRow, 'id');
                    }
                }

                //********************************
                //* +++++ Civil Service Eligibility
                //*******************************
                if (isset($c2_form_data['all_user_civil_service_eligibility'])) {
                    $user_all_cs_data = $c2_form_data['all_user_civil_service_eligibility'];

                    CivilServiceEligibility::where('user_id', Auth::id())->delete();
                    for ($i = 0; $i < sizeof($user_all_cs_data); $i++) {
                        $civilServiceRow = is_array($user_all_cs_data[$i]) ? $stripAuditColumns($user_all_cs_data[$i]) : [];
                        if (empty($civilServiceRow)) {
                            continue;
                        }

                        $civilServiceRow['user_id'] = Auth::id();
                        $civilServiceRow['cs_eligibility_date'] = $this->normalizeDateForDatabase($civilServiceRow['cs_eligibility_date'] ?? null);
                        $civilServiceRow['cs_eligibility_validity'] = $this->normalizeDateForDatabase($civilServiceRow['cs_eligibility_validity'] ?? null);

                        CivilServiceEligibility::upsert($civilServiceRow, 'id');
                    }
                }

                // C3 INSERT TO DATABASE
                //LEARNING AND DEVELOPMENT
                $c3_learning_and_development_data = session('data_learning');
                //dd(session('data_learning'));

                if (!empty($c3_learning_and_development_data)) {
                    foreach ($c3_learning_and_development_data as $idx => $row) {
                        if (!is_array($row)) {
                            unset($c3_learning_and_development_data[$idx]);
                            continue;
                        }
                        $row = $stripAuditColumns($row);
                        $row['user_id'] = Auth::id();
                        $row['learning_from'] = $this->normalizeDateForDatabase($row['learning_from'] ?? null);
                        $row['learning_to'] = $this->normalizeDateForDatabase($row['learning_to'] ?? null);
                        $c3_learning_and_development_data[$idx] = $row;
                    }
                    $c3_learning_and_development_data = array_values($c3_learning_and_development_data);

                    LearningAndDevelopment::where('user_id', Auth::id())->delete();
                    LearningAndDevelopment::upsert(
                        $c3_learning_and_development_data,
                        ['learning_title', 'learning_from', 'user_id'], // Unique constraint
                        ['learning_type', 'learning_hours', 'learning_to', 'learning_conducted'] // Fields to update
                    );
                }

                //VOLUNTARY WORK
                $c3_voluntary_data = session('data_voluntary');
                if (!empty($c3_voluntary_data)) {
                    foreach ($c3_voluntary_data as $idx => $row) {
                        if (!is_array($row)) {
                            unset($c3_voluntary_data[$idx]);
                            continue;
                        }
                        $row = $stripAuditColumns($row);
                        $row['user_id'] = Auth::id();
                        $row['voluntary_from'] = $this->normalizeDateForDatabase($row['voluntary_from'] ?? null);
                        $row['voluntary_to'] = $this->normalizeDateForDatabase($row['voluntary_to'] ?? null);
                        $c3_voluntary_data[$idx] = $row;
                    }
                    $c3_voluntary_data = array_values($c3_voluntary_data);

                    VoluntaryWork::where('user_id', Auth::id())->delete();
                    VoluntaryWork::upsert(
                        $c3_voluntary_data,
                        ['voluntary_org', 'voluntary_from', 'user_id'], // Unique constraint
                        ['voluntary_to', 'voluntary_hours', 'voluntary_position'] // Fields to update
                    );
                }
                //OTHER INFORMATION

                $c3_other_information_data = session('data_otherInfo');
                if (!empty($c3_other_information_data)) {
                    $user_other_info = OtherInformation::firstOrCreate([
                        'user_id' => Auth::id()
                    ]);
                    $user_other_info->update([
                        'user_id' => Auth::id(),
                        'skill' => $c3_other_information_data['skill'],
                        'distinction' => $c3_other_information_data['distinction'],
                        'organization' => $c3_other_information_data['organization'],
                    ]);
                }

                //C4 INSERT TO DATABASE
                $c4_misc_info_data = session('form.c4');
                if (!empty($c4_misc_info_data)) {
                    unset($c4_misc_info_data['criminal_35_b_array']); // criminal_35_b_array is not part of the database
                    $misc_info_data = MiscInfos::firstOrCreate([
                        'user_id' => Auth::id()
                    ]);
                    $misc_info_data->update($c4_misc_info_data);
                }
                } else {
                    Log::info('C5 finalize skipped C1-C4 persistence due to empty form session payload', [
                        'user_id' => Auth::id(),
                        'vacancy_id' => $applicationVacancyId,
                    ]);
                }



                // --- NOTIFICATION TRIGGER ---
                // Collect uploaded document types
                $uploadedDocTypes = array_keys($uploaded_files);

                if (!empty($uploadedDocTypes)) {
                    try {
                        $user = Auth::user();
                        // Find latest active application to get context (Vacancy Title)
                        $latestApplication = \App\Models\Applications::where('user_id', $user->id)
                            ->latest()
                            ->with('vacancy')
                            ->first();

                        $vacancyId = !empty($applicationVacancyId)
                            ? (string) $applicationVacancyId
                            : ($latestApplication ? (string) $latestApplication->vacancy_id : null);
                        $vacancyTitle = $latestApplication && $latestApplication->vacancy
                            ? $latestApplication->vacancy->position_title
                            : 'General Update';

                        $admins = \App\Models\Admin::all();
                        foreach ($admins as $admin) {
                            $admin->notify(new \App\Notifications\DocumentUploadedNotification(
                                $user->name,
                                $uploadedDocTypes,
                                $vacancyTitle,
                                $user->id,
                                $vacancyId
                            ));
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send document upload notification: ' . $e->getMessage());
                    }
                }

                activity()
                    ->causedBy(Auth::user())
                    ->event('save')
                    ->log('Finalized PDS submission.');

                if (!empty($applicationVacancyId)) {
                    $vacancyUploads = session('vacancy_doc_uploads', []);
                    $vacancyUploads[$applicationVacancyId] = [
                        'user_id' => Auth::id(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                    session(['vacancy_doc_uploads' => $vacancyUploads]);

                    $submissionResult = $this->createOrUpdateApplicationFromVacancyUploads((string) $applicationVacancyId);
                    if (!$submissionResult['ok']) {
                        $reasonCode = (string) ($submissionResult['reason_code'] ?? '');
                        Log::warning('C5 application submit blocked', [
                            'user_id' => Auth::id(),
                            'vacancy_id' => $applicationVacancyId,
                            'reason' => $submissionResult['message'],
                            'reason_code' => $reasonCode,
                        ]);

                        $redirect = redirect()
                            ->route('display_c5', [
                                'doc_track' => $docTrack,
                                'vacancy_id' => $applicationVacancyId,
                                'simple' => 1,
                            ]);

                        if ($reasonCode === 'qualification') {
                            return $redirect
                                ->with('qualification_feedback', [
                                    'title' => 'You are not yet qualified for this position',
                                    'summary' => 'Please update your PDS details for the items below, then submit again.',
                                    'missing' => array_values((array) ($submissionResult['missing_requirements'] ?? [])),
                                    'next_step_url' => route('job_description', ['id' => $applicationVacancyId]),
                                    'next_step_label' => 'Back to Job Details',
                                ])
                                ->withInput();
                        }

                        if ($reasonCode === 'incomplete_pds') {
                            return $redirect
                                ->with('qualification_feedback', [
                                    'title' => 'Complete your PDS first',
                                    'summary' => 'Some required PDS sections are incomplete. Please complete your PDS before submitting your application.',
                                    'missing' => [],
                                    'next_step_url' => route('display_c1'),
                                    'next_step_label' => 'Go to PDS',
                                ])
                                ->withInput();
                        }

                        if ($reasonCode === 'initial_assessment_incomplete') {
                            return redirect()
                                ->route('job_description', ['id' => $applicationVacancyId])
                                ->with('error', (string) ($submissionResult['message']
                                    ?? 'Please complete the initial assessment for this position before applying.'));
                        }

                        return $redirect->withErrors(['cert_uploads.application_letter' => $submissionResult['message']]);
                    }

                    return redirect()
                        ->route('my_applications')
                        ->with('success', $submissionResult['created']
                            ? 'Application submitted successfully!'
                            : 'Application updated successfully.');
                }

                if ($go_to === 'job_description') {
                    $redirectVacancyId = $request->input('redirect_vacancy_id', $applicationVacancyId);
                    if (!empty($redirectVacancyId)) {
                        return redirect()
                            ->route('job_description', ['id' => $redirectVacancyId])
                            ->with('success', 'Required documents uploaded. You can now continue your application.');
                    }
                    return redirect()->route('job_vacancy');
                }

                return redirect()->route($go_to);
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            Log::error('PDS finalize failed', [
                'user_id' => Auth::id(),
                'go_to' => $go_to,
                'vacancy_id' => $request->input('vacancy_id'),
                'doc_track' => $request->input('doc_track'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->withErrors(['cert_uploads' => 'Upload failed. Please try again.']);
        }
    } // END finalize PDS

    private function initialAssessmentSessionKey(string $vacancyId): string
    {
        return 'initial_assessment_answers.' . trim($vacancyId);
    }

    private function getInitialAssessmentForVacancy(Models\JobVacancy $vacancy): array
    {
        $assessment = session($this->initialAssessmentSessionKey((string) $vacancy->vacancy_id), []);
        return is_array($assessment) ? $assessment : [];
    }

    private function hasCompletedInitialAssessmentForVacancy(Models\JobVacancy $vacancy, array $assessment): bool
    {
        $vacancyId = trim((string) $vacancy->vacancy_id);
        $assessmentVacancyId = trim((string) ($assessment['vacancy_id'] ?? ''));
        $degree = trim((string) ($assessment['degree'] ?? ''));
        $eligibility = trim((string) ($assessment['eligibility'] ?? ''));
        $q1Passed = array_key_exists('q1_passed', $assessment) ? (bool) $assessment['q1_passed'] : false;
        $q2Passed = array_key_exists('q2_passed', $assessment) ? (bool) $assessment['q2_passed'] : false;
        $hasSubscribedPdsAnswered = array_key_exists('has_subscribed_pds', $assessment);

        if (
            $vacancyId === ''
            || $assessmentVacancyId !== $vacancyId
            || $degree === ''
            || $eligibility === ''
            || !$q1Passed
            || !$q2Passed
            || !$hasSubscribedPdsAnswered
        ) {
            return false;
        }

        $isPlantilla = strcasecmp(trim((string) $vacancy->vacancy_type), 'plantilla') === 0;
        if ($isPlantilla && !array_key_exists('has_pqe', $assessment)) {
            return false;
        }

        return true;
    }

    private function buildInitialAssessmentApplicationPayload(array $assessment): array
    {
        $payload = [];

        if (Schema::hasColumn('applications', 'initial_assessment_degree')) {
            $payload['initial_assessment_degree'] = trim((string) ($assessment['degree'] ?? '')) ?: null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_eligibility')) {
            $payload['initial_assessment_eligibility'] = trim((string) ($assessment['eligibility'] ?? '')) ?: null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_q1_passed')) {
            $payload['initial_assessment_q1_passed'] = array_key_exists('q1_passed', $assessment)
                ? (bool) $assessment['q1_passed']
                : null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_q2_passed')) {
            $payload['initial_assessment_q2_passed'] = array_key_exists('q2_passed', $assessment)
                ? (bool) $assessment['q2_passed']
                : null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_has_pqe')) {
            $payload['initial_assessment_has_pqe'] = array_key_exists('has_pqe', $assessment)
                ? (bool) $assessment['has_pqe']
                : null;
        }

        return $payload;
    }

    private function createOrUpdateApplicationFromVacancyUploads(string $vacancyId): array
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $vacancy = Models\JobVacancy::where('vacancy_id', $vacancyId)->first();
        if (!$vacancy) {
            return [
                'ok' => false,
                'created' => false,
                'message' => 'Selected vacancy was not found.',
                'reason_code' => 'vacancy_not_found',
            ];
        }

        $initialAssessment = $this->getInitialAssessmentForVacancy($vacancy);

        $application = Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancyId)
            ->first();

        // Keep update flows intact, but enforce the same apply gate for new application creation.
        if (!$application) {
            $jobVacancyController = app(JobVacancyController::class);
            $hasSubscribedPds = array_key_exists('has_subscribed_pds', $initialAssessment)
                ? (bool) $initialAssessment['has_subscribed_pds']
                : false;

            if (!$hasSubscribedPds && !$jobVacancyController->hasCompletedPdsForApplicant((int) Auth::id())) {
                Log::info('C5 application submit blocked: incomplete PDS', [
                    'user_id' => Auth::id(),
                    'vacancy_id' => $vacancyId,
                ]);

                return [
                    'ok' => false,
                    'created' => false,
                    'message' => 'Please complete your Personal Data Sheet first before applying.',
                    'reason_code' => 'incomplete_pds',
                ];
            }

            if (!$hasSubscribedPds) {
                $qualificationGate = $jobVacancyController->evaluateQualificationGateForApplicant((int) Auth::id(), $vacancy);
                if (!(bool) ($qualificationGate['isQualified'] ?? false)) {
                    Log::info('C5 application submit blocked: qualification requirements not met', [
                        'user_id' => Auth::id(),
                        'vacancy_id' => $vacancyId,
                        'qualification_checks' => $qualificationGate['checks'] ?? [],
                    ]);

                    return [
                        'ok' => false,
                        'created' => false,
                        'message' => (string) ($qualificationGate['message']
                            ?? 'You are not yet qualified to apply for this position.'),
                        'reason_code' => 'qualification',
                        'missing_requirements' => array_values((array) ($qualificationGate['missing_labels'] ?? [])),
                    ];
                }
            }

            if (!$this->hasCompletedInitialAssessmentForVacancy($vacancy, $initialAssessment)) {
                Log::info('C5 application submit blocked: initial assessment not completed for vacancy', [
                    'user_id' => Auth::id(),
                    'vacancy_id' => $vacancyId,
                ]);

                return [
                    'ok' => false,
                    'created' => false,
                    'message' => 'Please complete the initial assessment for this position before applying.',
                    'reason_code' => 'initial_assessment_incomplete',
                ];
            }
        }

        $applicationLetterDocQuery = UploadedDocument::where('user_id', Auth::id())
            ->where('document_type', 'application_letter')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');
        if ($supportsVacancyScopedDocs) {
            $applicationLetterDocQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [$vacancyId]
            );
        }

        $applicationLetterDoc = $applicationLetterDocQuery
            ->latest('updated_at')
            ->first();

        if (!$applicationLetterDoc) {
            $latestApplicationLetter = Applications::where('user_id', Auth::id())
                ->whereNotNull('file_storage_path')
                ->latest('updated_at')
                ->first();

            if ($latestApplicationLetter) {
                $applicationLetterDoc = UploadedDocument::updateOrCreate(
                    $supportsVacancyScopedDocs
                        ? [
                            'user_id' => Auth::id(),
                            'vacancy_id' => $vacancyId,
                            'document_type' => 'application_letter',
                        ]
                        : [
                            'user_id' => Auth::id(),
                            'document_type' => 'application_letter',
                        ],
                    [
                        'original_name' => (string) ($latestApplicationLetter->file_original_name
                            ?: basename((string) $latestApplicationLetter->file_storage_path)),
                        'stored_name' => (string) ($latestApplicationLetter->file_stored_name
                            ?: basename((string) $latestApplicationLetter->file_storage_path)),
                        'storage_path' => (string) $latestApplicationLetter->file_storage_path,
                        'mime_type' => 'application/pdf',
                        'file_size_8b' => (int) ($latestApplicationLetter->file_size_8b ?? 0),
                        'status' => 'Pending',
                        'remarks' => '',
                        'last_modified_by' => Auth::user()?->name ?? 'System',
                    ]
                );
            }
        }

        if (!$applicationLetterDoc) {
            return [
                'ok' => false,
                'created' => false,
                'message' => 'Application Letter is required before submitting your application.',
                'reason_code' => 'application_letter_missing',
            ];
        }

        if (
            $supportsVacancyScopedDocs
            && (string) ($applicationLetterDoc->vacancy_id ?? '') !== $vacancyId
        ) {
            $applicationLetterDoc = $this->upsertVacancyDocumentFromSource(
                $applicationLetterDoc,
                $vacancyId,
                'application_letter'
            );
        }

        $applicationPayload = [
            'file_original_name' => $applicationLetterDoc->original_name,
            'file_stored_name' => $applicationLetterDoc->stored_name,
            'file_storage_path' => $applicationLetterDoc->storage_path,
            // Re-applies should always return application-letter validation to pending.
            'file_status' => 'Pending',
            'file_remarks' => null,
            'file_size_8b' => $applicationLetterDoc->file_size_8b,
            'is_valid' => true,
        ];

        if ($application) {
            if (ApplicationStatus::equals($application->status, ApplicationStatus::COMPLIANCE)) {
                $statusTransitions = app(ApplicationStatusTransitionService::class);
                if ($statusTransitions->canTransition($application->status, ApplicationStatus::UPDATED->value)) {
                    $applicationPayload['status'] = ApplicationStatus::UPDATED->value;
                }
            }

            $application->update($applicationPayload);

            Log::info('C5 application submit updated existing application', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancyId,
                'application_id' => $application->id,
            ]);

            return [
                'ok' => true,
                'created' => false,
                'message' => 'Application updated successfully.',
            ];
        }

        $application = Applications::create(array_merge(
            $applicationPayload,
            $this->buildInitialAssessmentApplicationPayload($initialAssessment),
            [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancyId,
                'status' => ApplicationStatus::PENDING->value,
            ]
        ));

        session()->forget($this->initialAssessmentSessionKey((string) $vacancy->vacancy_id));

        if ($vacancy) {
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'notifiable_type' => 'App\Models\Admin',
                    'notifiable_id' => $admin->id,
                    'type' => 'warning',
                    'data' => [
                        'title' => 'New Job Application',
                        'message' => Auth::user()->name . ' submitted an application for ' . $vacancy->position_title . '.',
                        'link' => route('admin.applicant_status', ['user_id' => Auth::id(), 'vacancy_id' => $vacancyId], false),
                        'section' => 'Application List',
                        'category' => 'document_verification',
                        'user_id' => Auth::id(),
                        'vacancy_id' => $vacancyId,
                    ],
                    'read_at' => null,
                ]);
            }

            activity()
                ->event('apply job')
                ->causedBy(Auth::user())
                ->performedOn($vacancy)
                ->withProperties(['vacancy_id' => $vacancyId, 'section' => 'Job Vacancy'])
                ->log('Applied to job vacancy.');
        }

        Log::info('C5 application submit created application', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancyId,
            'application_id' => $application->id,
        ]);

        return [
            'ok' => true,
            'created' => true,
            'message' => 'Application submitted successfully!',
        ];
    }

    private function validateUploadedFile(UploadedFile $file, bool $allowImage): array
    {
        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            return [false, 'Unable to read uploaded file.'];
        }

        if ($file->getSize() === 0) {
            return [false, 'The file appears to be empty.'];
        }

        if ((int) $file->getSize() > self::MAX_UPLOAD_BYTES) {
            return [false, 'Each file must be 10MB or smaller.'];
        }

        $mimeType = $this->resolveMimeType($path) ?: $file->getClientMimeType();

        if ($allowImage && $this->isAllowedImageMime($mimeType)) {
            return [true, null];
        }

        if (!$this->isAllowedPdfMime($mimeType)) {
            return [false, 'Only PDF files are allowed.'];
        }

        if (!$this->hasPdfHeader($path)) {
            return [false, 'Invalid PDF file content.'];
        }

        return [true, null];
    }

    private function resolveUploadErrorMessage(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Each file must be 10MB or smaller.',
            UPLOAD_ERR_PARTIAL => 'Upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Upload failed because the server temporary folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Upload failed while writing the file. Please try again.',
            UPLOAD_ERR_EXTENSION => 'Upload was blocked by a server extension.',
            default => 'Upload failed. Please try again.',
        };
    }

    private function resolveMimeType(string $path): ?string
    {
        if (!class_exists(\finfo::class)) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($path);
        return $type ?: null;
    }

    private function hasPdfHeader(string $path): bool
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return false;
        }
        $header = fread($handle, 8);
        fclose($handle);
        if (!$header) {
            return false;
        }
        return str_starts_with($header, '%PDF-');
    }

    private function isAllowedPdfMime(?string $mimeType): bool
    {
        if (!$mimeType) {
            return false;
        }
        return in_array(strtolower($mimeType), self::PDF_MIME_TYPES, true);
    }

    private function isAllowedImageMime(?string $mimeType): bool
    {
        if (!$mimeType) {
            return false;
        }
        return in_array(strtolower($mimeType), self::IMAGE_MIME_TYPES, true);
    }

    private function getRequiredDocsByTrack(): array
    {
        $allDocumentTypes = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn($doc) => $doc !== 'isApproved'
        ));

        return [
            'COS' => [
                'passport_photo',
                'signed_pds',
                'signed_work_exp_sheet',
                'photocopy_diploma',
                'application_letter',
                'cert_training',
            ],
            // Plantilla optional docs: if any only.
            'Plantilla' => array_values(array_diff(
                $allDocumentTypes,
                [
                    'tor_masteraldoctorate',
                    'grade_masteraldoctorate',
                    'cert_lgoo_induction',
                    'other_documents',
                    'pqe_result',
                    'ipcr',
                    'non_academic',
                    'designation_order',
                    'cert_employment',
                ]
            )),
        ];
    }

    private function getDocumentLabelMap(): array
    {
        return [
            'application_letter' => 'Application Letter',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2\" x 2\" or Passport Size Picture',
            'cert_eligibility' => 'Certificate of Eligibility/Board Rating',
            'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR (If Any)',
            'non_academic' => 'Non-Academic Awards Received (If Any)',
            'cert_training' => 'Certificates of Training/Participation',
            'designation_order' => 'Confirmed Designation Order/s (If Any)',
            'grade_masteraldoctorate' => 'Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'other_documents' => 'Other Documents Submitted',
        ];
    }

    private function extractVacancyIdFromReferer(?string $referer): ?string
    {
        if (empty($referer)) {
            return null;
        }

        $query = parse_url($referer, PHP_URL_QUERY);
        if (empty($query)) {
            return null;
        }

        parse_str($query, $params);
        $vacancyId = $params['vacancy_id'] ?? null;
        if (!is_string($vacancyId)) {
            return null;
        }

        $vacancyId = trim($vacancyId);
        return $vacancyId !== '' ? $vacancyId : null;
    }

    private function scanUploadedFile(UploadedFile $file): array
    {
        $enabled = filter_var(env('CLAMAV_ENABLED', false), FILTER_VALIDATE_BOOL);
        if (!$enabled) {
            return [true, null];
        }

        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            return [false, 'Virus scan failed.'];
        }

        if (!class_exists(\Symfony\Component\Process\Process::class)) {
            return [false, 'Virus scanner is not available.'];
        }

        $command = env('CLAMAV_PATH', 'clamscan');
        $process = new \Symfony\Component\Process\Process([$command, '--no-summary', $path]);
        $process->run();

        if ($process->isSuccessful()) {
            return [true, null];
        }

        $output = $process->getOutput() . $process->getErrorOutput();
        if (str_contains($output, 'FOUND')) {
            return [false, 'File failed virus scan.'];
        }

        return [false, 'Virus scan could not be completed.'];
    }


    public function showSubmittedForm()
    {
        // TODO: Get all data from DB

        $user = Auth::user();
        // If user has not submitted PDS, redirect to C1 form
        // if (!$user->has_pds) {
        //     return redirect()->route('display_c1');
        // }

        // Redirect to the correct route, not the view
        return redirect()->route('pds.preview')->with('pds_submitted', true);

    }



    /*
     * APPLICATION STATUS UPLOADS IN ADMIN
     * using the same flow as C5 and using its function c5StoreFilesToDB();
     */
    public function uploadApplicationDocuments(Request $request, $user_id, $vacancy_id)
    {
        //dd($request->all());
        $request->validate([
            'cert_uploads.*' => 'nullable|file|mimes:pdf|max:10240'
        ], [
            'cert_uploads.*.mimes' => 'Only PDF files are allowed.',
            'cert_uploads.*.max' => 'Each file must be 10MB or smaller.',
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        if ((int) Auth::id() !== (int) $application->user_id) {
            abort(403, 'Unauthorized access to this application.');
        }

        if ($this->hasRevisionDeadlinePassed($application)) {
            return redirect()->back()->withErrors([
                'deadline' => 'Revision deadline already passed (Philippine Standard Time). Upload is no longer allowed.',
            ]);
        }

        if ($this->hasFinalRevisionDisqualification($application, (string) $vacancy_id)) {
            return redirect()->back()->withErrors([
                'final_revision_block' => 'No further compliance is allowed. You have already used your final revision opportunity.',
            ]);
        }

        $currentDocs = $this->loadReusableUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        $uploaded_files = [];
        $upload_errors = [];
        foreach (UploadedDocument::DOCUMENTS as $doc_type) {
            if (!$request->hasFile("cert_uploads.$doc_type")) {
                continue;
            }

            $file = $request->file("cert_uploads.$doc_type");
            if (!$file->isValid()) {
                $upload_errors["cert_uploads.$doc_type"] = 'Upload failed. Please try again.';
                continue;
            }

            [$is_valid, $message] = $this->validateUploadedFile($file, false);
            if (!$is_valid) {
                $upload_errors["cert_uploads.$doc_type"] = $message;
                continue;
            }

            [$scan_ok, $scan_message] = $this->scanUploadedFile($file);
            if (!$scan_ok) {
                $upload_errors["cert_uploads.$doc_type"] = $scan_message;
                continue;
            }

            if ($doc_type === 'application_letter') {
                $hasExistingApplicationLetter = !empty($application->file_storage_path)
                    && $application->file_storage_path !== 'NOINPUT';
                $applicationLetterInRevision = $this->isRevisionStatus($application->file_status);
                $appRevisionCount = (int) ($application->file_revision_requested_count ?? 0);
                $appRequestedAt = $application->file_revision_requested_at ?? null;
                $appSubmittedAt = $application->file_revision_submitted_at ?? null;

                // Allow initial uploads when there is no existing file yet.
                if ($hasExistingApplicationLetter && !$applicationLetterInRevision) {
                    $upload_errors["cert_uploads.$doc_type"] = 'Cannot upload this document because it is not currently marked as Needs Revision.';
                    continue;
                }

                if (
                    $applicationLetterInRevision
                    && $this->isRevisionComplianceLocked($appRevisionCount, $appRequestedAt, $appSubmittedAt)
                ) {
                    $upload_errors["cert_uploads.$doc_type"] = 'No further compliance is allowed for this document. You have already used your final revision opportunity.';
                    continue;
                }
            } else {
                $existingDoc = $this->resolveUploadedDocument($currentDocs, (string) $doc_type);
                $existingDocHasFile = $existingDoc
                    && !empty($existingDoc->storage_path)
                    && $existingDoc->storage_path !== 'NOINPUT';
                $existingDocInRevision = $existingDoc && $this->isRevisionStatus($existingDoc->status);

                // Allow initial uploads when there is no existing file yet.
                if ($existingDocHasFile && !$existingDocInRevision) {
                    $upload_errors["cert_uploads.$doc_type"] = 'Cannot upload this document because it is not currently marked as Needs Revision.';
                    continue;
                }

                $docRevisionCount = (int) ($existingDoc?->revision_requested_count ?? 0);
                $docRequestedAt = $existingDoc?->revision_requested_at ?? null;
                $docSubmittedAt = $existingDoc?->revision_submitted_at ?? null;
                if (
                    $existingDocInRevision
                    && $this->isRevisionComplianceLocked($docRevisionCount, $docRequestedAt, $docSubmittedAt)
                ) {
                    $upload_errors["cert_uploads.$doc_type"] = 'No further compliance is allowed for this document. You have already used your final revision opportunity.';
                    continue;
                }
            }

            //If it's application_letter, store in Applications model
            if ($doc_type === 'application_letter') {
                $supportsFileRevisionTracking = Schema::hasColumn('applications', 'file_revision_requested_count')
                    && Schema::hasColumn('applications', 'file_revision_submitted_at');

                // Generate unique stored name
                $originalName = $file->getClientOriginalName();
                $storedName = uniqid() . '_' . $originalName;
                $path = $file->storeAs('uploads/application_letters', $storedName, 'public');

                // Delete old file if exists
                if ($application->file_storage_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($application->file_storage_path);
                }

                //Update the application record
                $applicationUpdates = [
                    'file_original_name' => $originalName,
                    'file_stored_name' => $storedName,
                    'file_storage_path' => $path,
                    'file_status' => 'Pending', // Reset status
                    'file_remarks' => null, // Reset remarks
                    'file_size_8b' => $file->getSize(),
                ];
                if (
                    $supportsFileRevisionTracking
                    && (int) ($application->file_revision_requested_count ?? 0) > 0
                ) {
                    // Always stamp latest compliance submission against the latest revision request.
                    $applicationUpdates['file_revision_submitted_at'] = now();
                }

                $application->update($applicationUpdates);
                app(DocumentGallerySyncService::class)->syncApplicationLetterFromApplication($application);

                // *** NEW: Check if application was "Compliance" -> update to "Updated" ***
                if (ApplicationStatus::equals($application->status, ApplicationStatus::COMPLIANCE)) {
                    $statusTransitions = app(ApplicationStatusTransitionService::class);
                    if ($statusTransitions->canTransition($application->status, ApplicationStatus::UPDATED->value)) {
                        $application->update(['status' => ApplicationStatus::UPDATED->value]);
                    }

                    $admins = \App\Models\Admin::all();
                    foreach ($admins as $admin) {
                        \App\Models\Notification::create([
                            'notifiable_type' => 'App\Models\Admin',
                            'notifiable_id' => $admin->id,
                            'type' => 'warning',
                            'data' => [
                                'title' => 'Applicant Updated Documents',
                                'message' => 'Applicant ' . Auth::user()->name . ' has updated their documents for review.',
                                'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id], false),
                                'section' => 'Application List',
                                'user_id' => $user_id,
                                'vacancy_id' => $vacancy_id,
                            ],
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

            } else {
                //For all other documents, add to $uploaded_files for c5StoreFilesToDB
                $uploaded_files[$doc_type] = $file;
            }

            //$uploaded_files[$doc_type] = $file;
        }
        if (!empty($upload_errors)) {
            return redirect()->back()->withErrors($upload_errors);
        }
        if (!empty($uploaded_files)) {
            $this->c5StoreFilesToDB($uploaded_files, $vacancy_id);

            // *** NEW: Check if ANY uploaded file triggers "Compliance" -> "Updated"
            // We need to find the application associated with this user/vacancy if we are in admin context?
            // Actually c5StoreFilesToDB is generic. But here we are in uploadApplicationDocuments
            // which has $user_id and $vacancy_id.
            if (ApplicationStatus::equals($application->status, ApplicationStatus::COMPLIANCE)) {
                $statusTransitions = app(ApplicationStatusTransitionService::class);
                if ($statusTransitions->canTransition($application->status, ApplicationStatus::UPDATED->value)) {
                    $application->update(['status' => ApplicationStatus::UPDATED->value]);
                }

                $admins = \App\Models\Admin::all();
                foreach ($admins as $admin) {
                    \App\Models\Notification::create([
                        'notifiable_type' => 'App\Models\Admin',
                        'notifiable_id' => $admin->id,
                        'type' => 'warning',
                        'data' => [
                            'title' => 'Applicant Updated Documents',
                            'message' => 'Applicant ' . Auth::user()->name . ' has updated their documents for review.',
                            'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id], false),
                            'section' => 'Application List',
                            'user_id' => $user_id,
                            'vacancy_id' => $vacancy_id,
                        ],
                        'read_at' => null
                    ]);
                }
            }
        }

        //$this->c5StoreFilesToDB($uploaded_files);

        activity()
            ->causedBy(Auth::user())
            ->event('save')
            ->withProperties(['user_id' => $user_id, 'vacancy_id' => $vacancy_id, 'section' => 'Personal Data Sheet'])
            ->log('Uploaded application documents (Admin).');

        \App\Models\User::query()->whereKey(Auth::id())->update(['updated_at' => now()]);
        return redirect()->back()->with('success', 'Documents uploaded successfully.');
    }

    public function c1DisplayUpdateForm()
    {
        if (!session()->has('form.c1')) {
            session(['form.c1' => $this->c1GetFormFromDB()]);
        }
        $vocational_schools = session('form.c1.vocational', []);
        $college_schools = session('form.c1.college', []);
        $grad_schools = session('form.c1.grad', []);
        $data = session('form.c1');
        return view('pds_update.pds_update', compact('vocational_schools', 'college_schools', 'grad_schools', 'data'));
    }

    public function c2DisplayUpdateForm()
    {
        if (!session()->has('form.c2')) {
            session(['form.c2' => $this->c2GetFormFromDB()]);
        }
        $all_user_work_exps = session('form.c2.all_user_work_exps', []);
        $all_user_civil_service_eligibility = session('form.c2.all_user_civil_service_eligibility', []);
        return view('pds_update.c2_update', compact('all_user_work_exps', 'all_user_civil_service_eligibility'));
    }

    

    public function c3DisplayUpdateForm()
    {
        if (empty(session('data_learning')) && empty(session('data_voluntary')) && empty(session('data_otherInfo'))) {
            $this->c3GetDatabase();
        }
        $data_learning = session('data_learning', []);
        $data_voluntary = session('data_voluntary', []);
        $data_otherInfo = session('data_otherInfo', []);
        return view('pds_update.c3_update', compact('data_learning', 'data_voluntary', 'data_otherInfo'));
    }

    public function c4DisplayUpdateForm()
    {
        if (empty(session('form.c4'))) {
            $this->c4GetDatabase();
        }
        $data = session('form.c4', []);
        return view('pds_update.c4_update', compact('data'));
    }

    public function c5DisplayUpdateForm()
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $documentCollection = UploadedDocument::where('user_id', Auth::id())
            ->when($supportsVacancyScopedDocs, fn($q) => $q->whereNull('vacancy_id'))
            ->orderByDesc('updated_at')
            ->get();
        $documents = $documentCollection
            ->unique('document_type')
            ->keyBy('document_type');
        return view('pds_update.c5_update', compact('documents'));
    }
}

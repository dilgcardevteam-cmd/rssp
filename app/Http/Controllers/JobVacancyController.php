<?php


namespace App\Http\Controllers;

use App\Support\PreviewUrl;
use App\Enums\ApplicationStatus;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
use App\Models\ExamItems;
use App\Models\Applications;
use App\Models\AdminVacancyAccess;
use App\Models\VacancyTitle;
use Illuminate\Http\Request;
use App\Models\Vacancy;
use App\Models\DocumentGalleryItem;
use App\Models\UploadedDocument;
use App\Models\PersonalInformation;
use App\Models\WorkExperience;
use App\Models\CivilServiceEligibility;
use App\Models\LearningAndDevelopment;
use App\Models\VoluntaryWork;
use App\Models\OtherInformation;
use App\Models\FamilyBackground;
use App\Models\EducationalBackground;
use App\Models\MiscInfos;
use App\Models\EligibilityPreset;
use App\Support\ApplicantOnboarding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use App\Models\WorkExpSheet;

class JobVacancyController extends Controller
{
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

    private const COS_REQUIRED_DOCUMENTS = [
        'passport_photo',
        'signed_pds',
        'signed_work_exp_sheet',
        'photocopy_diploma',
        'application_letter',
        'cert_training',
    ];

    private const EDUCATION_RULE_PARSER_VERSION = 3;

    private const EDUCATION_LEVEL_RANKS = [
        'high_school' => 1,
        'college_undergrad_or_two_years' => 2,
        'bachelor' => 3,
        'law' => 3,
        'masteral' => 4,
        'doctorate' => 5,
    ];

    private const CONTROLLED_EDUCATION_FIELD_GROUPS = [
        'statistics' => ['statistics', 'applied statistics', 'biostatistics'],
        'mathematics' => ['mathematics'],
        'applied_mathematics' => ['applied mathematics'],
        'data_science' => ['data science', 'data analytics'],
        'public_administration' => ['public administration', 'public admin'],
        'political_science' => ['political science'],
        'governance' => ['governance', 'local governance', 'governance operations'],
        'public_policy' => ['public policy', 'policy studies'],
    ];

    private const CONTROLLED_RELATED_EDUCATION_GROUPS = [
        'statistics' => ['mathematics', 'applied_mathematics', 'data_science'],
        'mathematics' => ['statistics', 'applied_mathematics', 'data_science'],
        'applied_mathematics' => ['statistics', 'mathematics', 'data_science'],
        'data_science' => ['statistics', 'mathematics', 'applied_mathematics'],
        'public_administration' => ['political_science', 'governance', 'public_policy'],
        'political_science' => ['public_administration', 'governance', 'public_policy'],
        'governance' => ['public_administration', 'political_science', 'public_policy'],
        'public_policy' => ['public_administration', 'political_science', 'governance'],
    ];

    // Keep empty unless HR policy explicitly whitelists a law-track degree as doctorate-equivalent.
    private const DOCTORATE_EQUIVALENT_LAW_KEYWORDS = [];

    private const ELIGIBILITY_CANONICAL_LABELS = [
        'csc_professional' => 'CSC Professional Eligibility',
        'csc_subprofessional' => 'Subprofessional (Sub-Prof) Eligibility',
        'bar_board' => 'Bar/Board Eligibility',
        'honor_graduate' => 'Honor Graduate Eligibility',
        'foreign_honor_graduate' => 'Foreign School Honor Graduate Eligibility',
        'barangay_health_worker' => 'Barangay Health Worker Eligibility',
        'barangay_nutrition_scholar' => 'Barangay Nutrition Scholar Eligibility',
        'barangay_official' => 'Barangay Official Eligibility',
        'sanggunian_member' => 'Sanggunian Member Eligibility',
        'skills_category_ii' => 'Skills Eligibility-Category II',
        'edp_specialist' => 'Electronic Data Processing Specialist Eligibility',
        'scientific_technological_specialist' => 'Scientific and Technological Specialist Eligibility',
    ];

    private function currentAdmin()
    {
        return Auth::guard('admin')->user();
    }

    private function isHrDivisionAdmin(): bool
    {
        return (($this->currentAdmin()->role ?? null) === 'hr_division');
    }

    private function supportsVacancyCreatorColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'created_by_admin_id');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.created_by_admin_id column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    private function hrDivisionGrantedVacancyIds(int $adminId): array
    {
        if ($adminId <= 0 || !Schema::hasTable('admin_vacancy_accesses')) {
            return [];
        }

        return AdminVacancyAccess::query()
            ->where('admin_id', $adminId)
            ->pluck('vacancy_id')
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function grantHrDivisionAccessToVacancy(string $vacancyId, ?int $adminId = null): void
    {
        if (!$this->isHrDivisionAdmin()) {
            return;
        }

        $adminId = $adminId ?? (int) ($this->currentAdmin()->id ?? 0);
        $vacancyId = trim($vacancyId);

        if ($adminId <= 0 || $vacancyId === '' || !Schema::hasTable('admin_vacancy_accesses')) {
            return;
        }

        try {
            AdminVacancyAccess::query()->firstOrCreate([
                'admin_id' => $adminId,
                'vacancy_id' => $vacancyId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Unable to auto-grant HR Division vacancy access.', [
                'admin_id' => $adminId,
                'vacancy_id' => $vacancyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function applyHrDivisionManagedVacancyScope($query, ?int $adminId = null): void
    {
        $adminId = $adminId ?? (int) ($this->currentAdmin()->id ?? 0);
        $grantedVacancyIds = $this->hrDivisionGrantedVacancyIds($adminId);
        $supportsCreatorColumn = $this->supportsVacancyCreatorColumn();

        $query->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->where(function ($subQuery) use ($adminId, $grantedVacancyIds, $supportsCreatorColumn) {
                $hasScope = false;

                if ($supportsCreatorColumn && $adminId > 0) {
                    $subQuery->where('created_by_admin_id', $adminId);
                    $hasScope = true;
                }

                if (!empty($grantedVacancyIds)) {
                    if ($hasScope) {
                        $subQuery->orWhereIn('vacancy_id', $grantedVacancyIds);
                    } else {
                        $subQuery->whereIn('vacancy_id', $grantedVacancyIds);
                    }
                    $hasScope = true;
                }

                if (!$hasScope) {
                    $subQuery->whereRaw('1 = 0');
                }
            });
    }

    private function hrDivisionCanManageVacancy(JobVacancy $vacancy): bool
    {
        if (!$this->isHrDivisionAdmin()) {
            return true;
        }

        if (strcasecmp((string) ($vacancy->vacancy_type ?? ''), 'COS') !== 0) {
            return false;
        }

        $adminId = (int) ($this->currentAdmin()->id ?? 0);
        if ($this->supportsVacancyCreatorColumn() && $adminId > 0 && (int) ($vacancy->created_by_admin_id ?? 0) === $adminId) {
            return true;
        }

        if (!Schema::hasTable('admin_vacancy_accesses') || $adminId <= 0) {
            return false;
        }

        return AdminVacancyAccess::query()
            ->where('admin_id', $adminId)
            ->where('vacancy_id', (string) $vacancy->vacancy_id)
            ->exists();
    }

    private function denyHrDivisionVacancyAccess(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()->route('vacancies_management')->with('error', $message);
    }

    private function syncVacancyTitleCompensationFromVacancyData(array $vacancyData): void
    {
        if (!Schema::hasTable('vacancy_titles')) {
            return;
        }

        $positionTitle = trim((string) ($vacancyData['position_title'] ?? ''));
        if ($positionTitle === '') {
            return;
        }

        $salaryGrade = $this->normalizeSalaryGrade((string) ($vacancyData['salary_grade'] ?? ''));
        $monthlySalary = (float) ($vacancyData['monthly_salary'] ?? 0);

        $updates = [
            'salary_grade' => $salaryGrade !== '' ? $salaryGrade : null,
            'monthly_salary' => $monthlySalary,
        ];

        $optionalColumns = [
            'vacancy_type',
            'pcn_no',
            'plantilla_item_no',
            'closing_date',
            'place_of_assignment',
            'qualification_education',
            'education_rule_compiled',
            'education_rule_parser_version',
            'education_rule_compiled_at',
            'qualification_training',
            'qualification_experience',
            'qualification_eligibility',
            'supporting_documents_required',
            'competencies',
            'expected_output',
            'scope_of_work',
            'duration_of_work',
            'to_person',
            'to_position',
            'to_office',
            'to_office_address',
            'csc_form_path',
        ];

        foreach ($optionalColumns as $column) {
            if (array_key_exists($column, $vacancyData) && Schema::hasColumn('vacancy_titles', $column)) {
                $updates[$column] = $vacancyData[$column];
            }
        }

        VacancyTitle::query()->updateOrCreate(['position_title' => $positionTitle], $updates);
    }

    private function normalizeSalaryGrade(string $value): string
    {
        $raw = strtoupper(trim($value));
        if (preg_match('/^(?:SG-)?(\d{1,2})$/', $raw, $matches) !== 1) {
            return $raw;
        }

        return 'SG-' . str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT);
    }

    private function strictEducationRequirementValidationRules(): array
    {
        return [
            'required',
            'string',
            'max:1000',
            function ($attribute, $value, $fail): void {
                $error = $this->educationRequirementValidationError($value);
                if ($error !== null) {
                    $fail($error);
                }
            },
        ];
    }

    private function educationRequirementConfigValidationRules(): array
    {
        return [
            'nullable',
            'string',
            'max:20000',
            function ($attribute, $value, $fail): void {
                if (!is_string($value) || trim($value) === '') {
                    return;
                }

                $decoded = json_decode($value, true);
                if (!is_array($decoded)) {
                    $fail('Education requirement configuration is invalid.');
                }
            },
        ];
    }

    private function educationRequirementValidationError($value): ?string
    {
        $requirement = $this->normalizeQualificationRequirement((string) $value);
        if ($requirement === null) {
            return 'Education requirement is required.';
        }

        $rule = $this->buildCompiledEducationRule($requirement);
        if (!is_array($rule)) {
            return 'Education requirement could not be parsed. Please use a clear template.';
        }

        $ruleCode = strtolower(trim((string) ($rule['rule_code'] ?? 'unknown_text')));
        $confidence = strtolower(trim((string) ($rule['confidence'] ?? 'low')));
        if ($ruleCode !== '' && $ruleCode !== 'unknown_text' && $confidence === 'high') {
            return null;
        }

        return 'Education requirement text is ambiguous. Use a clear format like: '
            . '"Bachelor\'s Degree (any field)", '
            . '"Bachelor\'s Degree in Statistics or related field", '
            . '"Completion of 2 years of studies in college", '
            . '"72 units in college", '
            . '"Masteral Degree", or '
            . '"Bachelor of Laws".';
    }

    private function normalizeEducationRequirementFieldList($value): array
    {
        $items = is_array($value) ? $value : preg_split('/[\r\n,;\/]+/', (string) $value);
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(function ($item) {
            $text = trim((string) $item);
            if ($text === '') {
                return null;
            }

            $text = preg_replace('/\s+/', ' ', $text) ?: $text;
            return strtolower($text);
        }, $items))));
    }

    private function normalizeEducationFieldText(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/[^a-z0-9\s]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        return trim($normalized);
    }

    private function educationTextContainsPhrase(string $haystack, string $needle): bool
    {
        $haystack = $this->normalizeEducationFieldText($haystack);
        $needle = $this->normalizeEducationFieldText($needle);
        if ($haystack === '' || $needle === '') {
            return false;
        }

        $pattern = '/\b' . preg_quote($needle, '/') . '\b/i';
        return preg_match($pattern, $haystack) === 1;
    }

    private function resolveEducationFieldGroupsFromText(string $value): array
    {
        $normalizedValue = $this->normalizeEducationFieldText($value);
        if ($normalizedValue === '') {
            return [];
        }

        $groups = [];
        foreach ($this->educationFieldGroupsConfig() as $group => $aliases) {
            foreach ($aliases as $alias) {
                if ($this->educationTextContainsPhrase($normalizedValue, (string) $alias)) {
                    $groups[$group] = true;
                    break;
                }
            }
        }

        return array_keys($groups);
    }

    private function expandRelatedEducationGroups(array $groups): array
    {
        $expanded = [];
        $relatedConfig = $this->educationRelatedGroupsConfig();
        foreach ($groups as $group) {
            $key = trim((string) $group);
            if ($key === '') {
                continue;
            }

            $expanded[$key] = true;
            foreach ((array) ($relatedConfig[$key] ?? []) as $related) {
                $relatedKey = trim((string) $related);
                if ($relatedKey === '') {
                    continue;
                }
                $expanded[$relatedKey] = true;
            }
        }

        return array_keys($expanded);
    }

    private function educationFieldGroupsConfig(): array
    {
        $configured = config('education_field_mapping.field_groups');
        if (!is_array($configured) || empty($configured)) {
            return self::CONTROLLED_EDUCATION_FIELD_GROUPS;
        }

        return $configured;
    }

    private function educationRelatedGroupsConfig(): array
    {
        $configured = config('education_field_mapping.related_groups');
        if (!is_array($configured) || empty($configured)) {
            return self::CONTROLLED_RELATED_EDUCATION_GROUPS;
        }

        return $configured;
    }

    private function isDoctorateEquivalentLawKeywordWhitelisted(string $value): bool
    {
        $normalizedValue = strtolower(trim($value));
        if ($normalizedValue === '') {
            return false;
        }

        foreach (self::DOCTORATE_EQUIVALENT_LAW_KEYWORDS as $keyword) {
            $normalizedKeyword = strtolower(trim((string) $keyword));
            if ($normalizedKeyword === '') {
                continue;
            }

            if (str_contains($normalizedValue, $normalizedKeyword)) {
                return true;
            }
        }

        return false;
    }

    private function textContainsDoctorateLevelKeyword(string $value): bool
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return false;
        }

        if ($this->textContainsAny($normalized, ['juris doctor', 'doctor of jurisprudence'])) {
            return $this->isDoctorateEquivalentLawKeywordWhitelisted($normalized);
        }

        if ($this->textContainsAny($normalized, ['doctoral', 'doctorate', 'doctor of philosophy', 'phd', 'ph.d', 'edd', 'dpa', 'dba', 'sc.d', 'scd'])) {
            return true;
        }

        if (str_contains($normalized, 'doctor of')) {
            return !$this->textContainsAny($normalized, ['juris', 'law']);
        }

        return false;
    }

    private function extractEducationFieldHints(string $requirement): array
    {
        $requirement = trim($requirement);
        if ($requirement === '') {
            return [];
        }

        $candidate = '';
        if (preg_match('/\bin\s+(.+?)(?:\.\s*|,\s*required|\s+required|\s+is required|\s+or related|\s+related field|\s+preferably|$)/i', $requirement, $matches) === 1) {
            $candidate = trim((string) ($matches[1] ?? ''));
        }

        if ($candidate === '') {
            return [];
        }

        $candidate = preg_replace('/\b(degree|bachelor(?:\'s)?|master(?:\'s)?|course|field|fields)\b/i', '', $candidate) ?: $candidate;
        $candidate = preg_replace('/\s+/', ' ', $candidate) ?: $candidate;
        return $this->normalizeEducationRequirementFieldList($candidate);
    }

    private function buildCompiledEducationRule(?string $rawRequirement): ?array
    {
        $requirement = $this->normalizeQualificationRequirement($rawRequirement);
        if ($requirement === null) {
            return null;
        }

        $normalizedText = strtolower(trim((string) preg_replace('/\s+/', ' ', $requirement)));
        $rule = [
            'parser_version' => self::EDUCATION_RULE_PARSER_VERSION,
            'source_text' => $requirement,
            'normalized_text' => $normalizedText,
            'rule_code' => 'unknown_text',
            'required' => true,
            'advisory_only' => false,
            'min_college_years' => null,
            'min_college_units' => null,
            'required_fields' => [],
            'strict_fields' => false,
            'required_level' => null,
            'allow_related_fields' => false,
            'accept_higher_degree' => true,
            'confidence' => 'low',
        ];

        if (
            $this->textContainsAny($normalizedText, ['no education required', 'no education requirement', 'any educational background']) ||
            ($this->textContainsAny($normalizedText, ['none']) && str_contains($normalizedText, 'education'))
        ) {
            $rule['rule_code'] = 'none';
            $rule['required'] = false;
            $rule['confidence'] = 'high';
            return $rule;
        }

        $fieldHints = $this->extractEducationFieldHints($requirement);
        $hasRelatedFieldWording = $this->textContainsAny($normalizedText, ['or related field', 'related field']);
        $hasStrictFieldWording = $this->textContainsAny($normalizedText, ['strict', 'exact']);
        $hasAdminRelevanceWording = $this->textContainsAny($normalizedText, ['relevant to the job', 'relevant to position']);

        if ($this->textContainsAny($normalizedText, ['bachelor of laws', 'llb', 'juris doctor', 'attorney'])) {
            $rule['rule_code'] = 'law_degree';
            $rule['required_level'] = 'bachelor';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsDoctorateLevelKeyword($normalizedText)) {
            $rule['required_level'] = 'doctorate';
            $rule['rule_code'] = empty($fieldHints) ? 'doctorate_any' : 'doctorate_specific';
            $rule['required_fields'] = $fieldHints;
            $rule['allow_related_fields'] = !empty($fieldHints) && $hasRelatedFieldWording;
            $rule['strict_fields'] = $hasStrictFieldWording;
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['master', 'masteral', "master's", 'master of'])) {
            $rule['required_level'] = 'masteral';
            $rule['rule_code'] = empty($fieldHints) ? 'masters_any' : 'masters_specific';
            $rule['required_fields'] = $fieldHints;
            $rule['allow_related_fields'] = !empty($fieldHints) && $hasRelatedFieldWording;
            $rule['strict_fields'] = $hasStrictFieldWording;
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['graduate studies', 'post graduate', 'postgraduate'])) {
            $rule['rule_code'] = 'graduate_studies';
            $rule['required_level'] = 'masteral';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['vocational', 'trade course', 'technical vocational', 'tesda'])) {
            $rule['rule_code'] = 'vocational';
            $rule['confidence'] = 'high';
            return $rule;
        }

        $yearsMatches = [];
        $mentionsCollege = str_contains($normalizedText, 'college');
        $hasYearPattern = preg_match('/\b(\d{1,2})\s*(?:years?|yrs?)\b/i', $normalizedText, $yearsMatches) === 1
            || preg_match('/\bat least\s+(\d{1,2})\s*(?:years?|yrs?)\b/i', $normalizedText, $yearsMatches) === 1;
        if ($mentionsCollege && $hasYearPattern) {
            $rule['rule_code'] = 'college_undergrad_or_two_years';
            $rule['required_level'] = 'college_undergrad_or_two_years';
            $rule['min_college_years'] = max(1, (int) ($yearsMatches[1] ?? 0));
            $rule['confidence'] = 'high';
            return $rule;
        }

        if (preg_match('/\b(\d{1,3})\s*(?:units?|unit)\s*(?:in\s*)?college\b/i', $normalizedText, $unitMatches) === 1) {
            $rule['rule_code'] = 'college_undergrad_or_two_years';
            $rule['required_level'] = 'college_undergrad_or_two_years';
            $rule['min_college_units'] = max(1, (int) ($unitMatches[1] ?? 0));
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['college graduate', 'college degree'])) {
            $rule['rule_code'] = 'college_degree';
            $rule['required_level'] = 'bachelor';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['bachelor', "bachelor's", 'baccalaureate'])) {
            $rule['required_level'] = 'bachelor';
            if (!empty($fieldHints)) {
                $rule['rule_code'] = 'bachelor_specific';
                $rule['required_fields'] = $fieldHints;
                $rule['allow_related_fields'] = $hasRelatedFieldWording;
                $rule['strict_fields'] = $hasStrictFieldWording;
                $rule['confidence'] = 'high';
            } elseif ($hasAdminRelevanceWording) {
                // "Relevant to the job" still requires a bachelor's degree; relevance is verified by admin.
                $rule['rule_code'] = 'bachelor_relevant_admin_review';
                $rule['required'] = true;
                $rule['advisory_only'] = false;
                $rule['confidence'] = 'high';
            } else {
                $rule['rule_code'] = 'bachelor_any';
                $rule['confidence'] = 'high';
            }
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['senior high', 'grade 12', 'shs'])) {
            $rule['rule_code'] = 'senior_high';
            $rule['required_level'] = 'high_school';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['high school'])) {
            $rule['rule_code'] = 'high_school';
            $rule['required_level'] = 'high_school';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['elementary'])) {
            $rule['rule_code'] = 'elementary';
            $rule['confidence'] = 'high';
            return $rule;
        }

        if ($this->textContainsAny($normalizedText, ['college', 'education'])) {
            $rule['rule_code'] = 'any_education';
            $rule['confidence'] = 'medium';
            return $rule;
        }

        return $rule;
    }

    private function decodeEducationRequirementConfig($raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function buildCompiledEducationStoragePayload(?string $rawRequirement, $rawUiConfig = null): array
    {
        $rule = $this->buildCompiledEducationRule($rawRequirement);
        if ($rule === null) {
            return [
                'education_rule_compiled' => null,
                'education_rule_parser_version' => null,
                'education_rule_compiled_at' => null,
            ];
        }

        $uiConfig = $this->decodeEducationRequirementConfig($rawUiConfig);
        if (is_array($uiConfig) && !empty($uiConfig)) {
            $rule['ui_config'] = $uiConfig;
            $rule['ui_config_version'] = 1;
        }

        $encoded = json_encode($rule, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($encoded) || $encoded === '') {
            $encoded = null;
        }

        return [
            'education_rule_compiled' => $encoded,
            'education_rule_parser_version' => (int) ($rule['parser_version'] ?? self::EDUCATION_RULE_PARSER_VERSION),
            'education_rule_compiled_at' => now(),
        ];
    }

    private function decodeCompiledEducationRule($raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function resolveCompiledEducationRuleForVacancy(JobVacancy $vacancy, ?string $normalizedRequirement = null): ?array
    {
        $normalizedRequirement = $normalizedRequirement ?? $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        if ($normalizedRequirement === null) {
            return null;
        }

        $storedRule = $this->decodeCompiledEducationRule($vacancy->education_rule_compiled ?? null);
        if (is_array($storedRule)) {
            $sourceText = $this->normalizeQualificationRequirement((string) ($storedRule['source_text'] ?? ''));
            $parserVersion = (int) ($storedRule['parser_version'] ?? 0);
            if (
                $sourceText !== null
                && $sourceText === $normalizedRequirement
                && $parserVersion === self::EDUCATION_RULE_PARSER_VERSION
            ) {
                return $storedRule;
            }
        }

        return $this->buildCompiledEducationRule($normalizedRequirement);
    }

    private function educationProfileMatchesFieldHints(array $profile, array $requiredFields, bool $strict = false): bool
    {
        if (empty($requiredFields)) {
            return true;
        }

        $requiredLevel = $this->normalizeEducationLevelKey((string) ($profile['required_level_for_match'] ?? 'bachelor')) ?? 'bachelor';
        $eligibleDegreeEntries = $this->educationProfileDegreeEntriesAtOrAboveLevel($profile, $requiredLevel);
        if (empty($eligibleDegreeEntries)) {
            return false;
        }

        foreach ($requiredFields as $field) {
            $needle = $this->normalizeEducationFieldText((string) $field);
            if ($needle === '') {
                continue;
            }

            foreach ($eligibleDegreeEntries as $entry) {
                $degreeText = $this->normalizeEducationFieldText((string) ($entry['text'] ?? ''));
                if ($degreeText === '') {
                    continue;
                }

                if ($strict) {
                    if ($this->educationTextContainsPhrase($degreeText, $needle)) {
                        return true;
                    }
                    continue;
                }

                if ($this->educationFieldHintMatchesDegreeText(
                    $needle,
                    $degreeText,
                    (bool) ($profile['allow_related_fields_for_match'] ?? false)
                )) {
                    return true;
                }
            }
        }

        return false;
    }

    private function educationFieldHintMatchesDegreeText(string $requiredFieldHint, string $degreeText, bool $allowRelatedFields): bool
    {
        $requiredHint = $this->normalizeEducationFieldText($requiredFieldHint);
        $candidateText = $this->normalizeEducationFieldText($degreeText);
        if ($requiredHint === '' || $candidateText === '') {
            return false;
        }

        $requiredGroups = $this->resolveEducationFieldGroupsFromText($requiredHint);
        $candidateGroups = $this->resolveEducationFieldGroupsFromText($candidateText);
        if (empty($requiredGroups)) {
            // Unmapped fields only pass through direct phrase match.
            return $this->educationTextContainsPhrase($candidateText, $requiredHint);
        }

        $allowedGroups = $allowRelatedFields
            ? $this->expandRelatedEducationGroups($requiredGroups)
            : $requiredGroups;

        if (!empty(array_intersect($allowedGroups, $candidateGroups))) {
            return true;
        }

        // Keep exact matching as fallback only for the declared specific field.
        return $this->educationTextContainsPhrase($candidateText, $requiredHint);
    }

    private function normalizeEducationLevelKey(?string $level): ?string
    {
        $normalized = strtolower(trim((string) $level));
        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'high_school', 'senior_high', 'secondary' => 'high_school',
            'college_undergrad_or_two_years', 'college_years', 'college_undergrad', 'two_years_college' => 'college_undergrad_or_two_years',
            'bachelor', 'bachelor_any', 'bachelor_specific' => 'bachelor',
            'masteral', 'masters', 'graduate_studies' => 'masteral',
            'doctorate', 'doctoral' => 'doctorate',
            'law' => 'law',
            default => array_key_exists($normalized, self::EDUCATION_LEVEL_RANKS) ? $normalized : null,
        };
    }

    private function educationLevelRank(?string $level): ?int
    {
        $normalized = $this->normalizeEducationLevelKey($level);
        if ($normalized === null || !array_key_exists($normalized, self::EDUCATION_LEVEL_RANKS)) {
            return null;
        }

        return self::EDUCATION_LEVEL_RANKS[$normalized];
    }

    private function profileHighestEducationLevelRank(array $profile): int
    {
        $rank = 0;

        if ((bool) ($profile['hasHighSchoolOrHigher'] ?? false)) {
            $rank = max($rank, self::EDUCATION_LEVEL_RANKS['high_school']);
        }
        if ((bool) ($profile['hasAtLeastTwoYearsCollege'] ?? false)) {
            $rank = max($rank, self::EDUCATION_LEVEL_RANKS['college_undergrad_or_two_years']);
        }
        if ((bool) ($profile['hasBachelorOrHigher'] ?? false)) {
            $rank = max($rank, self::EDUCATION_LEVEL_RANKS['bachelor']);
        }
        if ((bool) ($profile['hasMasters'] ?? false)) {
            $rank = max($rank, self::EDUCATION_LEVEL_RANKS['masteral']);
        }
        if ((bool) ($profile['hasDoctorate'] ?? false)) {
            $rank = max($rank, self::EDUCATION_LEVEL_RANKS['doctorate']);
        }

        return $rank;
    }

    private function educationProfileMeetsLevel(array $profile, string $requiredLevel): bool
    {
        $requiredRank = $this->educationLevelRank($requiredLevel);
        if ($requiredRank === null) {
            return false;
        }

        return $this->profileHighestEducationLevelRank($profile) >= $requiredRank;
    }

    private function resolveRequiredEducationLevelForRule(string $ruleCode, array $rule): ?string
    {
        $fromRule = $this->normalizeEducationLevelKey((string) ($rule['required_level'] ?? ''));
        if ($fromRule !== null) {
            return $fromRule;
        }

        return match ($ruleCode) {
            'high_school', 'senior_high' => 'high_school',
            'college_undergrad_or_two_years', 'college_years' => 'college_undergrad_or_two_years',
            'college_degree', 'bachelor_any', 'bachelor_specific', 'bachelor_relevant_admin_review', 'law_degree' => 'bachelor',
            'graduate_studies', 'masters', 'masters_any', 'masters_specific' => 'masteral',
            'doctorate', 'doctorate_any', 'doctorate_specific' => 'doctorate',
            default => null,
        };
    }

    private function educationProfileDegreeEntriesAtOrAboveLevel(array $profile, string $requiredLevel): array
    {
        $requiredRank = $this->educationLevelRank($requiredLevel);
        if ($requiredRank === null) {
            return [];
        }

        $entries = $profile['degree_entries'] ?? [];
        if (!is_array($entries)) {
            return [];
        }

        $matches = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $entryLevel = $this->normalizeEducationLevelKey((string) ($entry['level'] ?? ''));
            $entryRank = $this->educationLevelRank($entryLevel);
            if ($entryRank === null || $entryRank < $requiredRank) {
                continue;
            }

            $text = trim((string) ($entry['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $matches[] = [
                'level' => $entryLevel,
                'text' => $text,
            ];
        }

        return $matches;
    }

    private function evaluateCompiledEducationRule(array $profile, array $rule): ?bool
    {
        $ruleCode = strtolower(trim((string) ($rule['rule_code'] ?? '')));
        if ($ruleCode === '' || $ruleCode === 'unknown_text') {
            return null;
        }

        $requiredLevel = $this->resolveRequiredEducationLevelForRule($ruleCode, $rule);

        return match ($ruleCode) {
            'none' => true,
            'any_education' => (bool) ($profile['hasAnyEducation'] ?? false),
            'elementary' => (bool) ($profile['hasElementaryOrHigher'] ?? false),
            'high_school' => (bool) ($profile['hasHighSchoolOrHigher'] ?? false),
            'senior_high' => (bool) ($profile['hasSeniorHighOrHigher'] ?? false),
            'bachelor_relevant_admin_review' => $this->educationProfileMeetsLevel($profile, 'bachelor'),
            'college_undergrad_or_two_years',
            'college_years' => ((($rule['min_college_units'] ?? null) !== null)
                ? (int) ($profile['estimatedCollegeUnits'] ?? 0) >= (int) ($rule['min_college_units'] ?? 0)
                : (int) ($profile['collegeYearsCompleted'] ?? 0) >= max(1, (int) ($rule['min_college_years'] ?? 2)))
                || $this->educationProfileMeetsLevel($profile, 'bachelor'),
            'college_degree' => $this->educationProfileMeetsLevel($profile, 'bachelor'),
            'bachelor_any' => $this->educationProfileMeetsLevel($profile, 'bachelor'),
            'bachelor_specific',
            'masters_specific',
            'doctorate_specific' => is_string($requiredLevel)
                && $this->educationProfileMeetsLevel($profile, $requiredLevel)
                && $this->educationProfileMatchesFieldHints(
                    array_merge($profile, [
                        'required_level_for_match' => $requiredLevel,
                        'allow_related_fields_for_match' => (bool) ($rule['allow_related_fields'] ?? false),
                    ]),
                    (array) ($rule['required_fields'] ?? []),
                    (bool) ($rule['strict_fields'] ?? false)
                ),
            'law_degree' => (bool) ($profile['hasLawDegree'] ?? false),
            'graduate_studies' => $this->educationProfileMeetsLevel($profile, 'masteral'),
            'vocational' => (bool) ($profile['hasVocational'] ?? false)
                || (bool) ($profile['hasCollegeEntryOrHigher'] ?? false),
            'masters',
            'masters_any' => $this->educationProfileMeetsLevel($profile, 'masteral'),
            'doctorate',
            'doctorate_any' => $this->educationProfileMeetsLevel($profile, 'doctorate'),
            default => null,
        };
    }

    private function evaluateLegacyEducationRequirementByText(array $profile, string $requirement): bool
    {
        $requirementLower = strtolower($requirement);
        $mentionsSeniorHighAlternative = $this->textContainsAny($requirementLower, ['senior high', 'grade 12']);
        $mentionsHighSchoolAlternative = str_contains($requirementLower, 'high school');

        if ($this->textContainsDoctorateLevelKeyword($requirementLower)) {
            return (bool) ($profile['hasDoctorate'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, [
            'master',
            'masteral',
            'graduate studies',
            'post graduate',
            'postgraduate',
        ])) {
            return (bool) ($profile['hasMasters'] ?? false)
                || (bool) ($profile['hasDoctorate'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['bachelor of laws', 'llb', 'juris doctor', 'attorney'])) {
            return (bool) ($profile['hasLawDegree'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['vocational', 'trade course', 'technical vocational', 'tesda'])) {
            return (bool) ($profile['hasVocational'] ?? false)
                || (bool) ($profile['hasCollegeEntryOrHigher'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['bachelor', "bachelor's", 'baccalaureate'])) {
            return (bool) ($profile['hasBachelorOrHigher'] ?? false);
        }
        if (str_contains($requirementLower, '2 years') && str_contains($requirementLower, 'college')) {
            return (bool) ($profile['hasAtLeastTwoYearsCollege'] ?? false)
                || ($mentionsSeniorHighAlternative && (bool) ($profile['hasSeniorHighOrHigher'] ?? false))
                || (!$mentionsSeniorHighAlternative && $mentionsHighSchoolAlternative && (bool) ($profile['hasHighSchoolOrHigher'] ?? false));
        }
        if ($this->textContainsAny($requirementLower, ['college graduate', 'college degree'])) {
            return (bool) ($profile['hasCollegeDegreeOrHigher'] ?? false);
        }
        if ($this->textContainsAny($requirementLower, ['bachelor', 'college'])) {
            return (bool) ($profile['hasCollegeEntryOrHigher'] ?? false);
        }
        if ($mentionsSeniorHighAlternative) {
            return (bool) ($profile['hasSeniorHighOrHigher'] ?? false);
        }
        if ($mentionsHighSchoolAlternative) {
            return (bool) ($profile['hasHighSchoolOrHigher'] ?? false);
        }
        if (str_contains($requirementLower, 'elementary')) {
            return (bool) ($profile['hasElementaryOrHigher'] ?? false);
        }

        return (bool) ($profile['hasAnyEducation'] ?? false);
    }

    private function extractAlternativeEducationRequirementSegments(string $requirement): array
    {
        $normalizedRequirement = trim((string) preg_replace('/\s+/', ' ', $requirement));
        if ($normalizedRequirement === '') {
            return [];
        }

        if (!preg_match('/\b(?:or|and\/or)\b/i', $normalizedRequirement)) {
            return [];
        }

        $looksLikeEducationRequirement = function (string $value): bool {
            $lower = strtolower(trim($value));
            if ($lower === '') {
                return false;
            }

            return $this->textContainsAny($lower, [
                'elementary',
                'high school',
                'senior high',
                'grade 12',
                'shs',
                'college',
                'bachelor',
                'baccalaureate',
                'master',
                'masteral',
                'doctorate',
                'doctoral',
                'phd',
                'ph.d',
                'graduate studies',
                'post graduate',
                'postgraduate',
                'vocational',
                'technical vocational',
                'tesda',
                'bachelor of laws',
                'llb',
                'juris doctor',
                'attorney',
                'units',
            ]);
        };

        $segments = [];
        $pushUniqueSegment = function (string $segment) use (&$segments): void {
            $clean = trim($segment, " \t\n\r\0\x0B,.;:()[]{}");
            if ($clean === '') {
                return;
            }

            $key = strtolower($clean);
            if (array_key_exists($key, $segments)) {
                return;
            }

            $segments[$key] = $clean;
        };

        $orParts = preg_split('/\b(?:and\/or|or)\b/i', $normalizedRequirement) ?: [];
        foreach ($orParts as $part) {
            $cleanPart = trim((string) $part);
            if ($cleanPart === '') {
                continue;
            }

            if ($looksLikeEducationRequirement($cleanPart)) {
                $pushUniqueSegment($cleanPart);
            }

            $commaParts = preg_split('/\s*,\s*/', $cleanPart) ?: [];
            foreach ($commaParts as $commaPart) {
                $cleanCommaPart = trim((string) $commaPart);
                if ($cleanCommaPart === '' || !$looksLikeEducationRequirement($cleanCommaPart)) {
                    continue;
                }
                $pushUniqueSegment($cleanCommaPart);
            }
        }

        return array_values($segments);
    }

    private function evaluateAlternativeEducationRequirements(array $profile, string $requirement): ?array
    {
        $segments = $this->extractAlternativeEducationRequirementSegments($requirement);
        if (count($segments) < 2) {
            return null;
        }

        $evaluations = [];
        foreach ($segments as $segment) {
            $rule = $this->buildCompiledEducationRule($segment);
            if (!is_array($rule)) {
                continue;
            }

            $ruleCode = strtolower(trim((string) ($rule['rule_code'] ?? 'unknown_text')));
            $met = $this->evaluateCompiledEducationRule($profile, $rule);
            $usedFallback = false;

            if (!is_bool($met)) {
                $met = $this->evaluateLegacyEducationRequirementByText($profile, $segment);
                $usedFallback = true;
            }

            if ($ruleCode === '' || $ruleCode === 'unknown_text') {
                $ruleCode = $usedFallback ? 'legacy_text_fallback' : 'unknown_text';
            }

            $evaluations[] = [
                'segment' => $segment,
                'met' => (bool) $met,
                'rule_code' => $ruleCode,
                'used_fallback' => $usedFallback,
            ];
        }

        if (count($evaluations) < 2) {
            return null;
        }

        $matched = collect($evaluations)->firstWhere('met', true);

        return [
            'met' => (bool) ($matched['met'] ?? false),
            'matched_rule_code' => $matched['rule_code'] ?? null,
            'evaluations' => $evaluations,
        ];
    }

    private function upsertPositionTemplate(array $validated, ?string $cscFormPath = null, ?int $positionTitleId = null): void
    {
        if (!Schema::hasTable('vacancy_titles')) {
            return;
        }

        $data = [
            'position_title' => trim((string) ($validated['position_title'] ?? '')),
            'salary_grade' => $this->normalizeSalaryGrade((string) ($validated['salary_grade'] ?? '')),
            'monthly_salary' => (float) ($validated['monthly_salary'] ?? 0),
        ];

        $optionalPayload = [
            'vacancy_type',
            'pcn_no',
            'plantilla_item_no',
            'closing_date',
            'place_of_assignment',
            'qualification_education',
            'education_rule_compiled',
            'education_rule_parser_version',
            'education_rule_compiled_at',
            'qualification_training',
            'qualification_experience',
            'qualification_eligibility',
            'supporting_documents_required',
            'competencies',
            'expected_output',
            'scope_of_work',
            'duration_of_work',
            'to_person',
            'to_position',
            'to_office',
            'to_office_address',
        ];

        foreach ($optionalPayload as $column) {
            if (!Schema::hasColumn('vacancy_titles', $column)) {
                continue;
            }

            if ($column === 'supporting_documents_required') {
                $data[$column] = $this->normalizeSupportingDocumentSelection($validated[$column] ?? null);
            } else {
                $data[$column] = $validated[$column] ?? null;
            }
        }

        if ($cscFormPath !== null && Schema::hasColumn('vacancy_titles', 'csc_form_path')) {
            $data['csc_form_path'] = $cscFormPath;
        }

        if ($positionTitleId && $positionTitleId > 0) {
            $existing = VacancyTitle::query()->find($positionTitleId);
            if ($existing) {
                $existing->update($data);
                return;
            }
        }

        VacancyTitle::query()->updateOrCreate(
            ['position_title' => $data['position_title']],
            $data
        );
    }

    public function jobVacancy()
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete onboarding before accessing job vacancies.');
        }

        $examCompletedSql = "exam_details.date IS NOT NULL AND ("
            . "exam_details.date < CURDATE() OR ("
            . "exam_details.date = CURDATE() AND (("
            . "exam_details.time_end IS NOT NULL AND CURTIME() > exam_details.time_end"
            . ") OR ("
            . "exam_details.time_end IS NULL AND exam_details.time IS NOT NULL "
            . "AND CURTIME() > ADDTIME(exam_details.time, '02:00:00')"
            . "))"
            . ")"
            . ")";

        $jobVacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', function ($join) {
                $join->whereRaw('job_vacancies.vacancy_id COLLATE utf8mb4_unicode_ci = exam_details.vacancy_id COLLATE utf8mb4_unicode_ci');
            })
            ->with('examDetail')
            ->orderByRaw("CASE 
                WHEN UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = 'OPEN' AND NOT ({$examCompletedSql}) THEN 1
                ELSE 2
            END")
            ->orderBy('job_vacancies.created_at', 'desc')
            ->orderBy('job_vacancies.vacancy_id', 'asc')
            ->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed job vacancy list.');
        */

        return view('dashboard_user.job_vacancy', ['vacancies' => $jobVacancies]);
    }

    public function jobVacancyManagement()
    {
        $jobVacanciesQuery = JobVacancy::query();

        if ($this->isHrDivisionAdmin()) {
            $this->applyHrDivisionManagedVacancyScope($jobVacanciesQuery);
        }

        $jobVacancies = $jobVacanciesQuery
            ->orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('closing_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->orderBy('vacancy_id', 'asc')
            ->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Accessed job vacancy management page.');
        */

        return view('admin.vacancies_management', [
            'vacancies' => $jobVacancies,
            'isHrDivisionUser' => $this->isHrDivisionAdmin(),
        ]);
    }

    public function edit(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        if (!$this->hrDivisionCanManageVacancy($vacancy)) {
            return $this->denyHrDivisionVacancyAccess(
                $request,
                'Access denied. You can only manage your own or assigned COS vacancies.'
            );
        }

        $signatories = \App\Models\Signatory::query()->orderBy('id')->get();
        $vacancyType = (string) ($vacancy->vacancy_type ?? '');
        $view = strcasecmp(trim($vacancyType), 'Plantilla') === 0
            ? 'admin.vacancy_add_plantilla'
            : 'admin.vacancy_add_cos';

        activity()
            ->event('view')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Editing job vacancy.');

        return view($view, ['vacancy' => $vacancy, 'signatories' => $signatories]);
    }

    public function update(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        $requiresCscFormUpload = strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && (!$this->hasJobVacancyCscFormPathColumn() || empty($vacancy->csc_form_path));

        $validated = $request->validate([
            'vacancy_type' => 'required|in:Plantilla,COS',
            'position_title' => 'required|string|max:255',
            'monthly_salary' => 'required|numeric',
            'place_of_assignment' => 'required|string',
            //'vacancies' => 'required|integer|min:1',
            'closing_date' => 'required|date',
            'qualification_education' => $this->strictEducationRequirementValidationRules(),
            'qualification_education_config' => $this->educationRequirementConfigValidationRules(),
            'qualification_experience' => 'required|string',
            'qualification_training' => 'required|string',
            'qualification_eligibility' => 'nullable|string|required_if:vacancy_type,Plantilla',
            'supporting_documents_required' => 'nullable|json',

            // Plantilla-only
            'competencies' => 'nullable|string',

            // COS only
            'scope_of_work' => 'nullable|string|required_if:vacancy_type,COS',
            'expected_output' => 'nullable|string|required_if:vacancy_type,COS',
            'duration_of_work' => 'nullable|string|required_if:vacancy_type,COS',

            'to_person' => 'required|string',
            'to_position' => 'required|string',
            'to_office' => 'required|string',
            'to_office_address' => 'required|string',

            'salary_grade' => ['required', 'regex:/^SG-\\d{2}$/'],
            'pcn_no' => 'nullable|string',
            'plantilla_item_no' => 'nullable|string',
            'csc_form' => [Rule::requiredIf($requiresCscFormUpload), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        if (!$this->hrDivisionCanManageVacancy($vacancy)) {
            return $this->denyHrDivisionVacancyAccess(
                $request,
                'Access denied. You can only manage your own or assigned COS vacancies.'
            );
        }

        if ($this->isHrDivisionAdmin() && strcasecmp((string) ($validated['vacancy_type'] ?? ''), 'COS') !== 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'HR Division can only update COS vacancies.');
        }

        $compiledEducationStorage = $this->buildCompiledEducationStoragePayload(
            $validated['qualification_education'] ?? null,
            $validated['qualification_education_config'] ?? null
        );

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($vacancy->$key != $value) {
                $changes[$key] = [
                    'old' => $vacancy->$key,
                    'new' => $value
                ];
            }
        }


        $closingDate = Carbon::parse($validated['closing_date']);
        $today = Carbon::today();

        $status = 'OPEN';

        $vacancyUpdateData = [
            'vacancy_type' => $validated['vacancy_type'],
            'position_title' => $validated['position_title'],
            'monthly_salary' => $validated['monthly_salary'],
            'place_of_assignment' => $validated['place_of_assignment'],
            //'vacancies' => $validated['vacancies'],
            'closing_date' => $validated['closing_date'],
            'status' => $status,

            'qualification_education' => $validated['qualification_education'],
            'qualification_experience' => $validated['qualification_experience'],
            'qualification_training' => $validated['qualification_training'],
            'qualification_eligibility' => $validated['qualification_eligibility'] ?? '',
            'supporting_documents_required' => $this->normalizeSupportingDocumentSelection(
                $validated['supporting_documents_required'] ?? null
            ),

            // Plantilla only
            'competencies' => $validated['competencies'] ?? null,

            // COS-only
            'expected_output' => $validated['expected_output'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
            'duration_of_work' => $validated['duration_of_work'] ?? null,

            'to_person' => $validated['to_person'],
            'to_position' => $validated['to_position'],
            'to_office' => $validated['to_office'],
            'to_office_address' => $validated['to_office_address'],

            'salary_grade' => $validated['salary_grade'] ?? null,
            'pcn_no' => $validated['pcn_no'] ?? null,
            'plantilla_item_no' => $validated['plantilla_item_no'] ?? null,

            'last_modified_by' => Auth::guard('admin')->user()?->name ?? 'System',
        ];

        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled')) {
            $vacancyUpdateData['education_rule_compiled'] = $compiledEducationStorage['education_rule_compiled'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_parser_version')) {
            $vacancyUpdateData['education_rule_parser_version'] = $compiledEducationStorage['education_rule_parser_version'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled_at')) {
            $vacancyUpdateData['education_rule_compiled_at'] = $compiledEducationStorage['education_rule_compiled_at'];
        }

        if ($this->hasJobVacancyLastModifiedAtColumn()) {
            $vacancyUpdateData['last_modified_at'] = now();
        }

        $vacancy->update($vacancyUpdateData);
        $this->syncVacancyTitleCompensationFromVacancyData($vacancyUpdateData);

        // Handle CSC Form file upload only when the column exists in this database.
        if ($this->hasJobVacancyCscFormPathColumn() && request()->hasFile('csc_form')) {
            if ($vacancy->csc_form_path) {
                Storage::disk('public')->delete($vacancy->csc_form_path);
            }
            $vacancy->update([
                'csc_form_path' => request()->file('csc_form')->store('csc_forms', 'public'),
            ]);
        }

        if (!empty($changes)) {
            activity()
                ->event('edit')
                ->causedBy(auth('admin')->user())
                ->performedOn($vacancy)
                ->withProperties(['changes' => $changes, 'section' => 'Job Vacancy'])
                ->log('Updated job vacancy fields.');
        }


        return redirect()->route('vacancies_management')->with('success', 'Job vacancy updated successfully.');
    }

    public function storeVacancy(Request $request)
    {
        //try {
        $positionMode = $request->boolean('position_mode');
        if (!$positionMode) {
            $referer = (string) $request->headers->get('referer', '');
            if (
                $referer !== ''
                && (
                    str_contains($referer, '/admin/vacancies_management/add/cos')
                    || str_contains($referer, '/admin/vacancies_management/add/plantilla')
                )
            ) {
                $positionMode = true;
            }
        }
        $positionTitleId = (int) $request->input('position_title_id', 0);
        $templateCscFormPath = '';
        if (
            !$positionMode
            && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && Schema::hasTable('vacancy_titles')
            && Schema::hasColumn('vacancy_titles', 'csc_form_path')
        ) {
            $positionTitle = trim((string) $request->input('position_title', ''));
            if ($positionTitle !== '') {
                $templateQuery = VacancyTitle::query()
                    ->where('position_title', $positionTitle)
                    ->whereNotNull('csc_form_path')
                    ->whereRaw("TRIM(COALESCE(csc_form_path, '')) != ''");

                if (Schema::hasColumn('vacancy_titles', 'vacancy_type')) {
                    $templateQuery->where(function ($q) {
                        $q->whereRaw("UPPER(TRIM(COALESCE(vacancy_type, ''))) = 'PLANTILLA'")
                            ->orWhereNull('vacancy_type')
                            ->orWhereRaw("TRIM(COALESCE(vacancy_type, '')) = ''");
                    });
                }

                $templateTitle = $templateQuery
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();

                $templateCscFormPath = trim((string) ($templateTitle?->csc_form_path ?? ''));
            }
        }

        if ($templateCscFormPath === '' && !$positionMode && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA' && $this->hasJobVacancyCscFormPathColumn()) {
            $positionTitle = trim((string) $request->input('position_title', ''));
            if ($positionTitle !== '') {
                $templateVacancy = JobVacancy::query()
                    ->where('position_title', $positionTitle)
                    ->whereRaw("UPPER(TRIM(COALESCE(vacancy_type, ''))) = 'PLANTILLA'")
                    ->whereNotNull('csc_form_path')
                    ->whereRaw("TRIM(COALESCE(csc_form_path, '')) != ''")
                    ->orderByDesc('updated_at')
                    ->first();
                $templateCscFormPath = trim((string) ($templateVacancy?->csc_form_path ?? ''));
            }
        }

        if ($templateCscFormPath !== '' && !Storage::disk('public')->exists((string) $templateCscFormPath)) {
            $templateCscFormPath = '';
        }

        $requiresCscFormUpload = !$positionMode
            && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && $templateCscFormPath === '';
        $existingPositionTemplate = null;
        if ($positionMode && $positionTitleId > 0 && Schema::hasTable('vacancy_titles')) {
            $existingPositionTemplate = VacancyTitle::query()->find($positionTitleId);
        }
        $requiresPositionModeCscFormUpload = $positionMode
            && strtoupper((string) $request->input('vacancy_type')) === 'PLANTILLA'
            && (
                !Schema::hasColumn('vacancy_titles', 'csc_form_path')
                || !$existingPositionTemplate
                || empty($existingPositionTemplate->csc_form_path)
            );

        if ($positionMode) {
            if (!Schema::hasTable('vacancy_titles')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Positions table is missing. Run migrations first.');
            }

            $validated = $request->validate([
                'position_title' => 'required|string|max:255',
                'vacancy_type' => 'required|in:COS,Plantilla',
                'pcn_no' => 'nullable|string',
                'plantilla_item_no' => 'nullable|string',
                'closing_date' => 'required|date',
                'monthly_salary' => 'required|numeric|min:0',
                'salary_grade' => ['required', 'regex:/^SG-\d{2}$/'],
                'place_of_assignment' => 'required|string',
                'qualification_education' => $this->strictEducationRequirementValidationRules(),
                'qualification_education_config' => $this->educationRequirementConfigValidationRules(),
                'qualification_training' => 'required|string',
                'qualification_experience' => 'required|string',
                'qualification_eligibility' => 'nullable|string|required_if:vacancy_type,Plantilla',
                'supporting_documents_required' => 'nullable|json',
                'competencies' => 'nullable|string',
                'expected_output' => 'nullable|string|required_if:vacancy_type,COS',
                'scope_of_work' => 'nullable|string|required_if:vacancy_type,COS',
                'duration_of_work' => 'nullable|string|required_if:vacancy_type,COS',
                'to_person' => 'nullable|string',
                'to_position' => 'nullable|string',
                'to_office' => 'nullable|string',
                'to_office_address' => 'nullable|string',
                'csc_form' => [Rule::requiredIf($requiresPositionModeCscFormUpload), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            ]);

            if ($this->isHrDivisionAdmin() && strcasecmp((string) ($validated['vacancy_type'] ?? ''), 'COS') !== 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'HR Division can only create COS positions.');
            }

            $compiledEducationStorage = $this->buildCompiledEducationStoragePayload(
                $validated['qualification_education'] ?? null,
                $validated['qualification_education_config'] ?? null
            );
            $validated = array_merge($validated, $compiledEducationStorage);

            $cscFormPath = null;
            if ($request->hasFile('csc_form') && Schema::hasColumn('vacancy_titles', 'csc_form_path')) {
                $cscFormPath = $request->file('csc_form')->store('csc_forms', 'public');
            }

            $this->upsertPositionTemplate($validated, $cscFormPath, $positionTitleId > 0 ? $positionTitleId : null);

            activity()
                ->event('create')
                ->causedBy(auth('admin')->user())
                ->withProperties([
                    'position_title' => $validated['position_title'],
                    'vacancy_type' => $validated['vacancy_type'],
                    'section' => 'Positions',
                ])
                ->log('Created or updated position template.');

            return redirect()->route('admin.positions.index')->with('success', 'Position saved successfully.');
        }

        $validated = $request->validate([
            'position_title' => 'required|string|max:255',
            'vacancy_type' => 'required|in:COS,Plantilla',
            'pcn_no' => 'nullable|string',
            'plantilla_item_no' => 'nullable|string',
            'closing_date' => 'required|date|after_or_equal:today',
            // 'status' => 'nullable|in:OPEN,CLOSED', // Status is auto-set to OPEN
            'monthly_salary' => 'required|numeric',
            'salary_grade' => ['required', 'regex:/^SG-\\d{2}$/'],
            'place_of_assignment' => 'required|string',

            // Qualification standards
            'qualification_education' => $this->strictEducationRequirementValidationRules(),
            'qualification_education_config' => $this->educationRequirementConfigValidationRules(),
            'qualification_training' => 'required|string',
            'qualification_experience' => 'required|string',
            'qualification_eligibility' => 'nullable|string|required_if:vacancy_type,Plantilla',
            'supporting_documents_required' => 'nullable|json',

            // Plantilla-only
            'competencies' => 'nullable|string',

            // COS-only
            'expected_output' => 'nullable|string|required_if:vacancy_type,COS',
            'scope_of_work' => 'nullable|string|required_if:vacancy_type,COS',
            'duration_of_work' => 'nullable|string|required_if:vacancy_type,COS',

            // Application submission
            'to_person' => 'required|string',
            'to_position' => 'required|string',
            'to_office' => 'required|string',
            'to_office_address' => 'required|string',

            // CSC Form
            'csc_form' => [Rule::requiredIf($requiresCscFormUpload), 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        if ($this->isHrDivisionAdmin() && strcasecmp((string) ($validated['vacancy_type'] ?? ''), 'COS') !== 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'HR Division can only create COS vacancies.');
        }

        $compiledEducationStorage = $this->buildCompiledEducationStoragePayload(
            $validated['qualification_education'] ?? null,
            $validated['qualification_education_config'] ?? null
        );

        // 🔷 Generate vacancy_id
        /*
        $positionTitle = $validated['position_title'];
        $words = preg_split('/\s+/', $positionTitle);
        $ranks = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'];
        $filteredWords = array_values(array_filter($words, fn($word) => !in_array(strtoupper($word), $ranks)));

        if(count($filteredWords) == 1){
            $letters = strtoupper(substr($filteredWords[0], 0, 3));
        } else {
            $letters = '';
            for($i = 0; $i < min(3, count($filteredWords)); $i++){
                $letters .= strtoupper(substr($filteredWords[$i], 0, 1));
            }
        }

        $latestVacancy = JobVacancy::where('vacancy_id', 'like', $letters . '-%')->latest('vacancy_id')->first();
        $num = $latestVacancy ? intval(substr($latestVacancy->vacancy_id, strpos($latestVacancy->vacancy_id, '-') + 1)) + 1 : 1;
        $vacancy_id = $letters . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        */

        $closingDate = Carbon::parse($validated['closing_date']);
        $today = Carbon::today();

        $status = 'OPEN'; // Default status for new vacancies

        $hasCscFormPathColumn = $this->hasJobVacancyCscFormPathColumn();

        // 🔷 Create vacancy
        $vacancyData = [
            //'vacancy_id' => $vacancy_id,
            'position_title' => $validated['position_title'],
            'vacancy_type' => $validated['vacancy_type'],
            'pcn_no' => $validated['pcn_no'] ?? null,
            'plantilla_item_no' => $validated['plantilla_item_no'] ?? null,
            'closing_date' => $validated['closing_date'],

            'status' => $status,
            'monthly_salary' => $validated['monthly_salary'],
            'salary_grade' => $validated['salary_grade'] ?? null,
            'place_of_assignment' => $validated['place_of_assignment'],

            // Qualification standards
            'qualification_education' => $validated['qualification_education'],
            'qualification_training' => $validated['qualification_training'],
            'qualification_experience' => $validated['qualification_experience'],
            'qualification_eligibility' => $validated['qualification_eligibility'] ?? '',
            'supporting_documents_required' => $this->normalizeSupportingDocumentSelection(
                $validated['supporting_documents_required'] ?? null
            ),

            // Plantilla only
            'competencies' => $validated['competencies'] ?? null,

            // COS only
            'expected_output' => $validated['expected_output'] ?? null,
            'scope_of_work' => $validated['scope_of_work'] ?? null,
            'duration_of_work' => $validated['duration_of_work'] ?? null,


            // Application submission
            'to_person' => $validated['to_person'],
            'to_position' => $validated['to_position'],
            'to_office' => $validated['to_office'],
            'to_office_address' => $validated['to_office_address'],
        ];

        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled')) {
            $vacancyData['education_rule_compiled'] = $compiledEducationStorage['education_rule_compiled'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_parser_version')) {
            $vacancyData['education_rule_parser_version'] = $compiledEducationStorage['education_rule_parser_version'];
        }
        if (Schema::hasColumn('job_vacancies', 'education_rule_compiled_at')) {
            $vacancyData['education_rule_compiled_at'] = $compiledEducationStorage['education_rule_compiled_at'];
        }

        if ($this->supportsVacancyCreatorColumn()) {
            $vacancyData['created_by_admin_id'] = Auth::guard('admin')->id();
        }

        // Some environments may not yet have the csc_form_path column.
        if ($hasCscFormPathColumn) {
            $vacancyData['csc_form_path'] = $request->hasFile('csc_form')
                ? $request->file('csc_form')->store('csc_forms', 'public')
                : ($templateCscFormPath !== '' ? $templateCscFormPath : null);
        }

        $vacancy = JobVacancy::create($vacancyData);
        $vacancy->refresh();
        $this->grantHrDivisionAccessToVacancy((string) ($vacancy->vacancy_id ?? ''));
        $this->syncVacancyTitleCompensationFromVacancyData($vacancyData);


        ExamDetail::create(['vacancy_id' => $vacancy->vacancy_id]);
        Log::info('Competencies field debug:', ['competencies' => $validated['competencies'] ?? 'NOT SET']);

        activity()
            ->event('create')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Created new job vacancy.');


        return redirect()->route('vacancies_management')->with('success', 'Vacancy created successfully.');
        /*} catch (\Exception $e) {
            Log::error('Vacancy Store Error: '.$e->getMessage());
            Log::error('Request Data: ' . json_encode($request->all()));
            return back()->with('error', 'Error: '.$e->getMessage());
        }*/

    }


    public function delete(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        if (!$this->hrDivisionCanManageVacancy($vacancy)) {
            return $this->denyHrDivisionVacancyAccess(
                $request,
                'Access denied. You can only manage your own or assigned COS vacancies.'
            );
        }

        ExamDetail::where('vacancy_id', $vacancy_id)->delete();
        ExamItems::where('vacancy_id', $vacancy_id)->delete();
        Applications::where('vacancy_id', $vacancy_id)->delete();

        $vacancy->delete();

        activity()
            ->event('delete')
            ->causedBy(auth('admin')->user())
            ->performedOn($vacancy)
            ->withProperties(['position_title' => $vacancy->position_title, 'section' => 'Job Vacancy'])
            ->log('Deleted job vacancy.');


        return redirect()->route('vacancies_management')->with('success', 'Vacancy deleted successfully.');
    }

    public function jobDescription(Request $request, $vacancy_id)
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('onboarding_prefill_vacancy_id', $vacancy_id)
                ->with('status', 'Please complete onboarding before viewing position details.');
        }

        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        $requiredEligibilityItems = $this->extractVacancyEligibilityItems((string) ($vacancy->qualification_eligibility ?? ''));
        $qualificationEligibilityDisplay = !empty($requiredEligibilityItems)
            ? $this->formatVacancyEligibilityDisplay($requiredEligibilityItems)
            : (trim((string) ($vacancy->qualification_eligibility ?? '')) ?: 'Not specified');

        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasCompletedPdsForApply = Auth::check()
            ? $this->hasCompletedPdsForApply((int) Auth::id())
            : false;

        // Auto-populate initial assessment from PDS data if PDS is complete and assessment not yet done
        if (Auth::check() && $hasCompletedPdsForApply) {
            $sessionKey = $this->initialAssessmentSessionKey((string) $vacancy->vacancy_id);
            $existingAssessment = session($sessionKey, []);
            $hasSubscribedPdsAnswered = array_key_exists('has_subscribed_pds', $existingAssessment);

            if (!$hasSubscribedPdsAnswered) {
                $degree = $this->resolveHighestDegreeFromPds((int) Auth::id());
                $eligibility = $this->resolvePrimaryEligibilityFromPds((int) Auth::id());

                if ($degree !== '' && $eligibility !== '') {
                    $isPlantilla = strcasecmp(trim((string) $vacancy->vacancy_type), 'plantilla') === 0;
                    $autoAssessment = [
                        'vacancy_id' => (string) $vacancy->vacancy_id,
                        'degree' => $degree,
                        'eligibility' => $eligibility,
                        'q1_passed' => true,
                        'q2_passed' => true,
                        'has_subscribed_pds' => true,
                        'auto_populated' => true,
                        'updated_at' => now()->toIso8601String(),
                    ];
                    // For Plantilla, set has_pqe default (PDS doesn't track PQE, user can update via manual assessment if needed)
                    if ($isPlantilla) {
                        $autoAssessment['has_pqe'] = false;
                    }
                    session([$sessionKey => $autoAssessment]);
                }
            }
        }

        $hasApplied = Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy_id)
            ->exists();

        $normalizedVacancyTrack = $this->normalizeTrack($vacancy->vacancy_type);
        $docTrackMismatchState = [
            'hasMismatch' => false,
            'submittedTrack' => null,
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
            ]),
        ];
        $requiredDocsModalState = [
            'hasMissing' => false,
            'previewDocs' => [],
            'vacancyTrack' => $normalizedVacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $normalizedVacancyTrack,
                'vacancy_id' => $vacancy->vacancy_id,
            ]),
        ];
        $qualificationGateState = [
            'isQualified' => true,
            'message' => null,
            'checks' => [],
        ];

        if (Auth::check()) {
            $docTrackMismatchState = $this->getDocumentTrackMismatchState((int) Auth::id(), $vacancy);
            $requiredDocsModalState = $this->getRequiredDocsModalState((int) Auth::id(), $vacancy, (string) $vacancy->vacancy_id);
            $qualificationGateState = $this->evaluateApplicantQualificationGateForVacancy((int) Auth::id(), $vacancy);
        }

        $missingQualificationLabels = $this->collectMissingQualificationLabels((array) ($qualificationGateState['checks'] ?? []));
        $isQualificationQualified = empty($missingQualificationLabels);
        $qualificationMismatchMessage = $qualificationGateState['message'] ?? null;
        if (!$isQualificationQualified && empty($qualificationMismatchMessage)) {
            $qualificationMismatchMessage = 'You are not yet qualified to apply for this position. '
                . 'Please review the missing requirements and update your PDS.';
        }

        $assessmentProgramOptions = [
            'COLLEGE' => [
                'Bachelor of Laws / Juris Doctor',
                'BS Accountancy',
                'BS Information Technology',
                'BS Computer Science',
                'BS Information Systems',
                'Bachelor of Public Administration',
                'BS Psychology',
            ],
            'MASTERAL' => [
                'Master of Public Administration',
                'Master in Information Technology',
                'Master in Business Administration',
                'Master of Arts in Education',
                'Master of Arts in Psychology',
            ],
            'DOCTORATE' => [
                'Doctor of Philosophy in Public Administration',
                'Doctor of Philosophy in Information Technology',
                'Doctor of Education',
                'Doctor of Philosophy in Psychology',
                'Doctor of Juridical Science',
            ],
        ];
        try {
            $coursePresetTable = Schema::hasTable('course_presets')
                ? 'course_presets'
                : (Schema::hasTable('course_preset') ? 'course_preset' : null);

            if ($coursePresetTable !== null) {
                $nameColumn = Schema::hasColumn($coursePresetTable, 'course_name')
                    ? 'course_name'
                    : (Schema::hasColumn($coursePresetTable, 'name') ? 'name' : null);
                $hasProgramLevelColumn = Schema::hasColumn($coursePresetTable, 'program_level');
                $hasLegacyLevelColumn = !$hasProgramLevelColumn && Schema::hasColumn($coursePresetTable, 'level');

                if ($nameColumn !== null) {
                    $selectColumns = [$nameColumn];
                    if ($hasProgramLevelColumn) {
                        $selectColumns[] = 'program_level';
                    } elseif ($hasLegacyLevelColumn) {
                        $selectColumns[] = 'level';
                    }

                    $rows = DB::table($coursePresetTable)
                        ->orderBy($nameColumn)
                        ->get($selectColumns);

                    $grouped = [
                        'COLLEGE' => [],
                        'MASTERAL' => [],
                        'DOCTORATE' => [],
                    ];

                    foreach ($rows as $row) {
                        $programName = trim((string) ($row->{$nameColumn} ?? ''));
                        if ($programName === '') {
                            continue;
                        }

                        $rawLevel = $hasProgramLevelColumn
                            ? (string) ($row->program_level ?? '')
                            : ($hasLegacyLevelColumn ? (string) ($row->level ?? '') : '');
                        $programLevel = strtoupper(trim($rawLevel));

                        if (!in_array($programLevel, ['COLLEGE', 'MASTERAL', 'DOCTORATE'], true)) {
                            $nameHaystack = strtolower($programName);
                            if (
                                str_contains($nameHaystack, 'doctorate')
                                || str_contains($nameHaystack, 'doctoral')
                                || str_contains($nameHaystack, 'doctor of philosophy')
                                || str_contains($nameHaystack, 'phd')
                                || str_contains($nameHaystack, 'ph.d')
                                || str_contains($nameHaystack, 'edd')
                                || str_contains($nameHaystack, 'sjd')
                            ) {
                                $programLevel = 'DOCTORATE';
                            } elseif (
                                str_contains($nameHaystack, 'masteral')
                                || str_contains($nameHaystack, 'master')
                                || str_contains($nameHaystack, "master's")
                                || str_contains($nameHaystack, 'mba')
                                || str_contains($nameHaystack, 'mpa')
                                || str_contains($nameHaystack, 'msc')
                                || str_contains($nameHaystack, 'm.s')
                                || str_contains($nameHaystack, 'm.a')
                            ) {
                                $programLevel = 'MASTERAL';
                            } else {
                                $programLevel = 'COLLEGE';
                            }
                        }

                        $grouped[$programLevel][] = $programName;
                    }

                    foreach ($grouped as $level => $items) {
                        $resolvedItems = collect($items)
                            ->map(fn($value) => trim((string) $value))
                            ->filter(fn($value) => $value !== '')
                            ->unique(fn($value) => strtolower($value))
                            ->sort(fn($a, $b) => strnatcasecmp((string) $a, (string) $b))
                            ->values()
                            ->all();

                        if (!empty($resolvedItems)) {
                            $assessmentProgramOptions[$level] = $resolvedItems;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Unable to load program preset options for initial assessment.', [
                'error' => $e->getMessage(),
            ]);
        }

        $assessmentEligibilityOptions = [];
        try {
            $eligibilityPresetTable = Schema::hasTable('eligibility_preset')
                ? 'eligibility_preset'
                : (Schema::hasTable('eligibility_presets') ? 'eligibility_presets' : null);

            if ($eligibilityPresetTable !== null) {
                $eligibilityRows = DB::table($eligibilityPresetTable)
                    ->orderBy('name')
                    ->get(['name', 'legal_basis', 'level']);

                foreach ($eligibilityRows as $row) {
                    $name = trim((string) ($row->name ?? ''));
                    if ($name !== '') {
                        $assessmentEligibilityOptions[] = [
                            'name' => $name,
                            'legal_basis' => trim((string) ($row->legal_basis ?? '')),
                            'level' => trim((string) ($row->level ?? '')),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Unable to load eligibility preset options for initial assessment.', [
                'error' => $e->getMessage(),
            ]);
        }

        // Ensure vacancy-specific eligibility requirements appear in Question 2 options
        // (e.g., Driver's License) even when not part of preset tables.
        try {
            $vacancyEligibilityItems = $this->extractVacancyEligibilityItems(
                (string) ($vacancy->qualification_eligibility ?? '')
            );

            // Build a lookup map from eligibility name to level for vacancy items
            $eligibilityLevelLookup = [];
            if ($eligibilityPresetTable !== null) {
                $presetLevels = DB::table($eligibilityPresetTable)
                    ->get(['name', 'level']);
                foreach ($presetLevels as $preset) {
                    $presetName = trim((string) ($preset->name ?? ''));
                    $presetLevel = trim((string) ($preset->level ?? ''));
                    if ($presetName !== '' && $presetLevel !== '') {
                        $eligibilityLevelLookup[strtolower($presetName)] = $presetLevel;
                    }
                }
            }

            foreach ($vacancyEligibilityItems as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '' && !collect($assessmentEligibilityOptions)->contains(fn($opt) => strcasecmp($opt['name'], $name) === 0)) {
                    // Look up level from presets if not provided
                    $level = trim((string) ($item['level'] ?? ''));
                    if ($level === '') {
                        $level = $eligibilityLevelLookup[strtolower($name)] ?? '';
                    }
                    $assessmentEligibilityOptions[] = [
                        'name' => $name,
                        'legal_basis' => trim((string) ($item['legal_basis'] ?? '')),
                        'level' => $level,
                    ];
                }
            }

            // Sort by name
            $assessmentEligibilityOptions = collect($assessmentEligibilityOptions)
                ->sortBy(fn($item) => strnatcasecmp($item['name'], $item['name']))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Unable to merge vacancy-specific eligibility options for initial assessment.', [
                'vacancy_id' => (string) ($vacancy->vacancy_id ?? ''),
                'error' => $e->getMessage(),
            ]);
        }

        return view('dashboard_user.job_description', [
            'vacancy' => $vacancy,
            'qualificationEligibilityDisplay' => $qualificationEligibilityDisplay,
            'hasPDS' => $hasPDS,
            'hasCompletedPdsForApply' => $hasCompletedPdsForApply,
            'hasApplied' => $hasApplied,
            'docTrackMismatch' => $docTrackMismatchState['hasMismatch'],
            'mismatchSubmittedTrack' => $docTrackMismatchState['submittedTrack'],
            'vacancyTrack' => $requiredDocsModalState['vacancyTrack'],
            'docUploadRedirectUrl' => $requiredDocsModalState['redirectUrl'],
            'hasMissingRequiredDocs' => $requiredDocsModalState['hasMissing'],
            'requiredDocsPreview' => $requiredDocsModalState['previewDocs'],
            'isEligibilityQualified' => $isQualificationQualified,
            'eligibilityMismatchMessage' => $qualificationMismatchMessage,
            'qualificationChecks' => $qualificationGateState['checks'],
            'missingQualificationLabels' => $missingQualificationLabels,
            'assessmentProgramOptions' => $assessmentProgramOptions,
            'assessmentEligibilityOptions' => $this->filterEligibilityOptionsByEducation(
                $assessmentEligibilityOptions,
                Auth::id()
            ),
        ]);


    }

    public function submitInitialAssessment(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::query()->where('vacancy_id', (string) $vacancy_id)->firstOrFail();

        $validated = $request->validate([
            'degree' => ['required', 'string', 'max:255'],
            'eligibility' => ['required', 'string', 'max:255'],
            'has_pqe' => ['nullable', 'boolean'],
            'has_subscribed_pds' => ['nullable', 'boolean'],
        ]);

        $degree = trim((string) ($validated['degree'] ?? ''));
        $eligibility = trim((string) ($validated['eligibility'] ?? ''));

        $educationAligned = $this->isInitialAssessmentEducationAligned($vacancy, $degree);
        $eligibilityAligned = $this->isInitialAssessmentEligibilityAligned($vacancy, $eligibility);

        if (!$educationAligned || !$eligibilityAligned) {
            return response()->json([
                'ok' => false,
                'education_aligned' => $educationAligned,
                'eligibility_aligned' => $eligibilityAligned,
                'message' => $this->buildInitialAssessmentNotQualifiedMessage($educationAligned, $eligibilityAligned),
            ], 422);
        }

        $sessionPayload = [
            'vacancy_id' => (string) $vacancy->vacancy_id,
            'degree' => $degree,
            'eligibility' => $eligibility,
            'q1_passed' => true,
            'q2_passed' => true,
            'updated_at' => now()->toIso8601String(),
        ];

        if (array_key_exists('has_pqe', $validated)) {
            $sessionPayload['has_pqe'] = (bool) $validated['has_pqe'];
        }

        if (array_key_exists('has_subscribed_pds', $validated)) {
            $sessionPayload['has_subscribed_pds'] = (bool) $validated['has_subscribed_pds'];
        }

        session([$this->initialAssessmentSessionKey((string) $vacancy->vacancy_id) => $sessionPayload]);

        return response()->json([
            'ok' => true,
            'education_aligned' => true,
            'eligibility_aligned' => true,
            'requires_pqe' => strcasecmp(trim((string) $vacancy->vacancy_type), 'plantilla') === 0
                && !array_key_exists('has_pqe', $validated),
            'redirect_to' => route('display_c1'),
        ]);
    }

    public function adminFilterVacancy(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $job = $request->get('job');
        $place = $request->get('place');
        $sort = trim((string) $request->get('sort', ''));
        $isHrDivisionUser = $this->isHrDivisionAdmin();

        $vacanciesQuery = JobVacancy::query();

        if ($isHrDivisionUser) {
            $this->applyHrDivisionManagedVacancyScope($vacanciesQuery);
        }

        $vacancies = $vacanciesQuery
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($job, function ($query) use ($job) {
                $query->where('vacancy_type', $job);
            })
            ->when($place, function ($query) use ($place) {
                $query->where('place_of_assignment', $place);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q
                        ->orWhere('vacancy_id', 'like', "%{$search}%")
                        ->orWhere('position_title', 'like', "%{$search}%")
                        ->orWhere('vacancy_type', 'like', "%{$search}%")
                        ->orWhere('monthly_salary', 'like', "%{$search}%")
                        ->orWhere('place_of_assignment', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('closing_date', 'like', "%{$search}%")
                        ->orWhere('qualification_education', 'like', "%{$search}%")
                        ->orWhere('qualification_training', 'like', "%{$search}%")
                        ->orWhere('qualification_experience', 'like', "%{$search}%")
                        ->orWhere('qualification_eligibility', 'like', "%{$search}%")
                        ->orWhere('scope_of_work', 'like', "%{$search}%")
                        ->orWhere('expected_output', 'like', "%{$search}%")
                        ->orWhere('duration_of_work', 'like', "%{$search}%")
                        ->orWhere('to_person', 'like', "%{$search}%")
                        ->orWhere('to_position', 'like', "%{$search}%")
                        ->orWhere('to_office', 'like', "%{$search}%")
                        ->orWhere('to_office_address', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%")
                        ->orWhere('updated_at', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN status = 'OPEN' THEN 1 ELSE 2 END")
            ->orderBy('closing_date', 'asc');

        if ($sort === 'oldest') {
            $vacancies->orderBy('created_at', 'asc');
        } else {
            $vacancies->orderBy('created_at', 'desc');
        }

        $vacancies = $vacancies
            ->orderBy('vacancy_id', 'asc')
            ->get();

        session(['vacancyFilterSearch' => $search]);
        session(['vacancyFilterJob' => $job]);
        session(['vacancyFilterStatus' => $status]);
        session(['vacancyFilterPlace' => $place]);

        /*activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (admin).');
        */

        return view('partials.admin_vacancy_list', [
            'vacancies' => $vacancies,
            'isHrDivisionUser' => $isHrDivisionUser,
        ])->render();
    }


    public function filterVacancy(Request $request)
    {
        $examCompletedSql = "exam_details.date IS NOT NULL AND ("
            . "exam_details.date < CURDATE() OR ("
            . "exam_details.date = CURDATE() AND (("
            . "exam_details.time_end IS NOT NULL AND CURTIME() > exam_details.time_end"
            . ") OR ("
            . "exam_details.time_end IS NULL AND exam_details.time IS NOT NULL "
            . "AND CURTIME() > ADDTIME(exam_details.time, '02:00:00')"
            . "))"
            . ")"
            . ")";

        $vacancies = JobVacancy::select('job_vacancies.*')
            ->leftJoin('exam_details', function ($join) {
                $join->whereRaw('job_vacancies.vacancy_id COLLATE utf8mb4_unicode_ci = exam_details.vacancy_id COLLATE utf8mb4_unicode_ci');
            })
            ->with('examDetail');

        if ($request->search) {
            $s = trim($request->search);
            $vacancies->where(function ($q) use ($s) {
                $q->where('position_title', 'like', "%{$s}%")
                    ->orWhere('place_of_assignment', 'like', "%{$s}%")
                    ->orWhere('job_vacancies.vacancy_id', 'like', "%{$s}%")
                    ->orWhere('vacancy_type', 'like', "%{$s}%");
            });
        }
        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $normalizedStatus = strtoupper($status);
            if ($normalizedStatus === 'OPEN') {
                $vacancies->whereRaw("UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = 'OPEN'")
                    ->whereRaw("NOT ({$examCompletedSql})");
            } elseif ($normalizedStatus === 'CLOSED') {
                $vacancies->whereRaw(
                    "UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = 'CLOSED' OR ("
                    . "UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = 'OPEN' AND ({$examCompletedSql})"
                    . ")"
                );
            } else {
                $vacancies->whereRaw(
                    "UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = ?",
                    [$normalizedStatus]
                );
            }
        }

        $type = trim((string) $request->input('type', ''));
        if ($type !== '') {
            $vacancies->whereRaw(
                "LOWER(TRIM(COALESCE(vacancy_type, ''))) = ?",
                [strtolower($type)]
            );
        }

        if ($request->place) {
            $vacancies->where('place_of_assignment', $request->place);
        }

        if ($request->salary) {
            [$min, $max] = explode('-', $request->salary);
            $vacancies->whereBetween('monthly_salary', [$min * 1000, $max * 1000]);
        }

        // Effective status sorting: OPEN first, CLOSED-equivalent after.
        $vacancies->orderByRaw("CASE 
            WHEN UPPER(TRIM(COALESCE(job_vacancies.status, ''))) = 'OPEN' AND NOT ({$examCompletedSql}) THEN 1
            ELSE 2
        END");

        if ($request->sort == 'latest') {
            $vacancies->orderBy('job_vacancies.created_at', 'desc');
        } elseif ($request->sort == 'oldest') {
            $vacancies->orderBy('job_vacancies.created_at', 'asc');
        } else {
            $vacancies->orderBy('job_vacancies.created_at', 'desc');
        }

        $vacancies->orderBy('job_vacancies.vacancy_id', 'asc');

        $vacancies = $vacancies->get();

        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Filtered job vacancies (user).');

        */

        return view('partials.vacancy_list', compact('vacancies'))->render();
    }

    public function getOpenVacanciesForDashboard()
    {
        $userId = Auth::id();

        $vacancies = collect();
        $openVacanciesQuery = JobVacancy::query()->where('status', 'OPEN');
        $openVacancyCount = (clone $openVacanciesQuery)->count();
        $cosVacancyCount = (clone $openVacanciesQuery)
            ->whereRaw('UPPER(vacancy_type) = ?', ['COS'])
            ->count();
        $plantillaVacancyCount = max($openVacancyCount - $cosVacancyCount, 0);

        $applications = \App\Models\Applications::query()
            ->select([
                'id',
                'user_id',
                'vacancy_id',
                'status',
                'qs_result',
                'deadline_date',
                'deadline_time',
                'created_at',
            ])
            ->where('user_id', $userId)
            ->with(['vacancy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdsProgress = (int) round($this->calculatePdsProgress(Auth::id()));
        $hasPDS = PersonalInformation::where('user_id', Auth::id())->exists();
        $hasWES = WorkExpSheet::where('user_id', Auth::id())->exists();

        // Application Status Summary
        $statusSummary = $applications->groupBy('status')->map->count();

        // Upcoming exams for user's applied vacancies
        $vacancyIds = $applications->pluck('vacancy_id')->filter()->unique()->values();
        $now = Carbon::now()->toDateTimeString();
        $upcomingExamsCount = ExamDetail::whereIn('vacancy_id', $vacancyIds)
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') > ?", [$now])
            ->count();
        $upcomingExams = collect();

        // Required Documents Status
        $uploadedDocuments = UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
        $documentStatusSummary = [];
        foreach (UploadedDocument::DOCUMENTS as $docType) {
            if ($docType === 'isApproved')
                continue;
            $doc = $uploadedDocuments->get($docType);
            $documentStatusSummary[] = [
                'type' => $docType,
                'status' => $doc ? ($doc->status ?? 'PENDING') : 'Not Submitted',
            ];
        }
        // Include quick flags for PDS/WES completion
        $documentStatusSummary[] = ['type' => 'pds', 'status' => $hasPDS ? 'Completed' : 'Incomplete'];
        $documentStatusSummary[] = ['type' => 'wes', 'status' => $hasWES ? 'Completed' : 'Incomplete'];

        // Recently closed positions among user's applications
        $recentlyClosedApplications = $applications->filter(function ($app) {
            return $app->vacancy && $app->vacancy->status === 'CLOSED';
        })->values();

        // Deadline countdown per active application
        $now = Carbon::now();
        $deadlineCountdown = $applications
            ->filter(function ($app) {
                if (!$app->deadline_date || !$app->deadline_time) {
                    return false;
                }

                $applicationStatus = strtolower(trim((string) ($app->status ?? '')));
                $qsResult = strtolower(trim((string) ($app->qs_result ?? '')));
                $isTerminalStatus = in_array($applicationStatus, ['closed', 'qualified'], true);
                $isVacancyClosed = $app->vacancy && strtolower((string) ($app->vacancy->status ?? '')) === 'closed';

                return !$isTerminalStatus && !$isVacancyClosed && $qsResult !== 'qualified';
            })
            ->map(function ($app) use ($now) {
                $deadline = Carbon::parse($app->deadline_date . ' ' . $app->deadline_time);
                $secondsRemaining = $now->diffInSeconds($deadline, false);
                if ($secondsRemaining <= 0) {
                    return null;
                }

                return [
                    'vacancy_id' => $app->vacancy_id,
                    'position_title' => $app->vacancy->position_title ?? '',
                    'deadline' => $deadline->toDateTimeString(),
                    'days_remaining' => (int) ceil($secondsRemaining / 86400),
                ];
            })
            ->filter()
            ->sortBy('days_remaining')
            ->values();

        // Notifications/Alerts (latest 5, and unread count)
        $recentNotifications = Auth::user()?->notifications()->orderBy('created_at', 'desc')->take(5)->get() ?? collect();
        $unreadNotificationsCount = Auth::user()?->unreadNotifications()->count() ?? 0;

        $authUser = Auth::user();
        $savedOnboarding = ApplicantOnboarding::payload($authUser);
        $requiresApplicantOnboarding = ApplicantOnboarding::shouldRequire($authUser);
        $openOnboardingModal = $requiresApplicantOnboarding || (bool) session('open_onboarding_modal', false);

        $onboardingVacancies = JobVacancy::query()
            ->where('status', 'OPEN')
            ->whereRaw('DATE(closing_date) >= DATE(NOW())')
            ->orderBy('closing_date')
            ->get([
                'vacancy_id',
                'position_title',
                'vacancy_type',
                'place_of_assignment',
                'qualification_education',
                'qualification_experience',
                'qualification_training',
                'qualification_eligibility',
            ]);

        $onboardingVacancyOptions = $onboardingVacancies->map(function (JobVacancy $vacancy) {
            return [
                'vacancy_id' => (string) $vacancy->vacancy_id,
                'position_title' => (string) $vacancy->position_title,
                'vacancy_type' => (string) ($vacancy->vacancy_type ?? ''),
                'place_of_assignment' => (string) ($vacancy->place_of_assignment ?? ''),
                'requirements' => [
                    'education' => $this->onboardingRequirementText($vacancy->qualification_education),
                    'experience' => $this->onboardingRequirementText($vacancy->qualification_experience),
                    'training' => $this->onboardingRequirementText($vacancy->qualification_training),
                    'eligibility' => $this->onboardingEligibilityText($vacancy->qualification_eligibility),
                ],
            ];
        })->values();

        $prefillOnboardingVacancyId = trim((string) session('onboarding_prefill_vacancy_id', ''));
        $selectedOnboardingVacancyId = $prefillOnboardingVacancyId !== ''
            ? $prefillOnboardingVacancyId
            : trim((string) ($savedOnboarding['preferred_vacancy_id'] ?? ''));
        $hasSelectedOnboardingVacancy = $selectedOnboardingVacancyId !== ''
            && $onboardingVacancyOptions->contains(
                fn (array $item) => (string) ($item['vacancy_id'] ?? '') === $selectedOnboardingVacancyId
            );
        if (!$hasSelectedOnboardingVacancy) {
            $selectedOnboardingVacancyId = (string) ($onboardingVacancyOptions->first()['vacancy_id'] ?? '');
        }

        return view('dashboard_user.dashboard_user', [
            'vacancies' => $vacancies,
            'openVacancyCount' => $openVacancyCount,
            'applications' => $applications,
            'pdsProgress' => $pdsProgress,
            'hasPDS' => $hasPDS,
            'hasWES' => $hasWES,
            'statusSummary' => $statusSummary,
            'cosVacancyCount' => $cosVacancyCount,
            'plantillaVacancyCount' => $plantillaVacancyCount,
            'upcomingExams' => $upcomingExams,
            'upcomingExamsCount' => $upcomingExamsCount,
            'documentStatusSummary' => $documentStatusSummary,
            'recentlyClosedApplications' => $recentlyClosedApplications,
            'deadlineCountdown' => $deadlineCountdown,
            'recentNotifications' => $recentNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'requiresApplicantOnboarding' => $requiresApplicantOnboarding,
            'openOnboardingModal' => $openOnboardingModal,
            'onboardingVacancyOptions' => $onboardingVacancyOptions,
            'selectedOnboardingVacancyId' => $selectedOnboardingVacancyId,
            'savedApplicantOnboarding' => $savedOnboarding,
        ]);

    }

    private function onboardingRequirementText(?string $raw): string
    {
        $text = trim((string) $raw);
        if ($text === '') {
            return 'Not specified';
        }

        return preg_replace('/\s+/', ' ', $text) ?: $text;
    }

    private function onboardingEligibilityText(?string $raw): string
    {
        $items = $this->extractVacancyEligibilityItems((string) ($raw ?? ''));
        if (!empty($items)) {
            return $this->formatVacancyEligibilityDisplay($items);
        }

        return $this->onboardingRequirementText($raw);
    }


    public function apply(Request $request, $vacancy_id)
    {
        $vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->firstOrFail();
        Log::info('Apply request received', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy_id,
        ]);

        if (ApplicantOnboarding::shouldRequire(Auth::user())) {
            Log::info('Apply blocked: applicant onboarding not completed', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('onboarding_prefill_vacancy_id', $vacancy->vacancy_id)
                ->with('status', 'Please complete onboarding before you apply.');
        }

        $initialAssessment = session($this->initialAssessmentSessionKey((string) $vacancy->vacancy_id), []);
        $hasSubscribedPds = is_array($initialAssessment) && array_key_exists('has_subscribed_pds', $initialAssessment)
            ? (bool) $initialAssessment['has_subscribed_pds']
            : false;

        if (!$hasSubscribedPds && !$this->hasCompletedPdsForApply((int) Auth::id())) {
            Log::info('Apply blocked: incomplete PDS', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('pds_required_prompt', true);
        }

        // Check if user already applied
        $existing = \App\Models\Applications::where('user_id', Auth::id())
            ->where('vacancy_id', $vacancy->vacancy_id)
            ->first();

        if ($existing) {
            $existingStatus = strtolower(trim((string) ($existing->status ?? '')));

            Log::info('Apply skipped: already applied', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'application_id' => $existing->id,
                'status' => $existing->status,
            ]);

            if ($existingStatus === 'cancelled') {
                return redirect()
                    ->route('my_applications')
                    ->with('error', 'You cancelled this application and can no longer apply to this position again.');
            }

            return redirect()
                ->route('my_applications')
                ->with('error', 'Application already exists for this vacancy.');
        }

        if (!$this->hasCompletedInitialAssessmentForVacancy($vacancy, (array) $initialAssessment)) {
            Log::info('Apply blocked: initial assessment not completed for vacancy', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('error', 'Please complete the initial assessment for this position before applying.');
        }

        $requiredDocsModalState = $this->getRequiredDocsModalState((int) Auth::id(), $vacancy, (string) $vacancy->vacancy_id);
        if ($requiredDocsModalState['hasMissing']) {
            Log::info('Apply blocked: required docs missing', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('required_docs_prompt', [
                    'vacancy_id' => $vacancy->vacancy_id,
                    'vacancy_track' => $requiredDocsModalState['vacancyTrack'],
                    'redirect_url' => $requiredDocsModalState['redirectUrl'],
                    'preview_docs' => $requiredDocsModalState['previewDocs'],
                ]);
        }

        $docTrackMismatchState = $this->getDocumentTrackMismatchState((int) Auth::id(), $vacancy);
        if ($docTrackMismatchState['hasMismatch']) {
            Log::info('Apply blocked: doc track mismatch', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
                'submitted_track' => $docTrackMismatchState['submittedTrack'],
                'vacancy_track' => $docTrackMismatchState['vacancyTrack'],
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('doc_track_mismatch', [
                    'vacancy_id' => $vacancy->vacancy_id,
                    'submitted_track' => $docTrackMismatchState['submittedTrack'],
                    'vacancy_track' => $docTrackMismatchState['vacancyTrack'],
                    'redirect_url' => $docTrackMismatchState['redirectUrl'],
                ]);
        }

        if (!$hasSubscribedPds) {
            $qualificationGate = $this->evaluateApplicantQualificationGateForVacancy((int) Auth::id(), $vacancy);
            $missingQualificationLabels = $this->collectMissingQualificationLabels((array) ($qualificationGate['checks'] ?? []));
            if (!empty($missingQualificationLabels)) {
                Log::info('Apply blocked: qualification requirements not met', [
                    'user_id' => Auth::id(),
                    'vacancy_id' => $vacancy_id,
                    'qualification_checks' => $qualificationGate['checks'],
                ]);

                $qualificationMismatchMessage = $qualificationGate['message'] ?? (
                    'You are not yet qualified to apply for this position. '
                    . 'Please review the missing requirements and update your PDS.'
                );

                return redirect()
                    ->route('job_description', ['id' => $vacancy->vacancy_id])
                    ->with('error', $qualificationMismatchMessage);
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType((string) $vacancy->vacancy_type, $vacancy);
        $this->seedVacancyDocumentsFromReusableUploads(
            (int) Auth::id(),
            (string) $vacancy->vacancy_id,
            $requiredDocumentIds
        );

        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $applicationLetterDocQuery = UploadedDocument::where('user_id', Auth::id())
            ->where('document_type', 'application_letter')
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');
        if ($supportsVacancyScopedDocs) {
            $applicationLetterDocQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancy->vacancy_id]
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
                            'vacancy_id' => (string) $vacancy->vacancy_id,
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
            Log::info('Apply blocked: application letter not found in UploadedDocument', [
                'user_id' => Auth::id(),
                'vacancy_id' => $vacancy_id,
            ]);
            return redirect()
                ->route('job_description', ['id' => $vacancy->vacancy_id])
                ->with('required_docs_prompt', [
                    'vacancy_track' => $requiredDocsModalState['vacancyTrack'],
                    'redirect_url' => $requiredDocsModalState['redirectUrl'],
                    'preview_docs' => $requiredDocsModalState['previewDocs'],
                ]);
        }

        if (
            $supportsVacancyScopedDocs
            && (string) ($applicationLetterDoc->vacancy_id ?? '') !== (string) $vacancy->vacancy_id
        ) {
            $applicationLetterDoc = $this->upsertVacancyDocumentFromSource(
                $applicationLetterDoc,
                (string) $vacancy->vacancy_id,
                'application_letter'
            );
        }


        // Create application
        $educationRequirementSnapshot = $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        $educationRuleSnapshot = $this->resolveCompiledEducationRuleForVacancy($vacancy, $educationRequirementSnapshot);

        $applicationPayload = [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => ApplicationStatus::PENDING->value,
            'is_valid' => true,

            'file_original_name' => $applicationLetterDoc->original_name,
            'file_stored_name' => $applicationLetterDoc->stored_name,
            'file_storage_path' => $applicationLetterDoc->storage_path,
            'file_status' => 'Submitted',
            'file_remarks' => null,
            'file_size_8b' => $applicationLetterDoc->file_size_8b,
        ];

        if (Schema::hasColumn('applications', 'education_requirement_snapshot')) {
            $applicationPayload['education_requirement_snapshot'] = $educationRequirementSnapshot;
        }
        if (Schema::hasColumn('applications', 'education_rule_snapshot')) {
            $applicationPayload['education_rule_snapshot'] = $educationRuleSnapshot;
        }
        if (Schema::hasColumn('applications', 'education_rule_snapshot_version')) {
            $applicationPayload['education_rule_snapshot_version'] = is_array($educationRuleSnapshot)
                ? (int) ($educationRuleSnapshot['parser_version'] ?? self::EDUCATION_RULE_PARSER_VERSION)
                : null;
        }

        if (Schema::hasColumn('applications', 'initial_assessment_degree')) {
            $applicationPayload['initial_assessment_degree'] = trim((string) ($initialAssessment['degree'] ?? '')) ?: null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_eligibility')) {
            $applicationPayload['initial_assessment_eligibility'] = trim((string) ($initialAssessment['eligibility'] ?? '')) ?: null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_q1_passed')) {
            $applicationPayload['initial_assessment_q1_passed'] = array_key_exists('q1_passed', $initialAssessment)
                ? (bool) $initialAssessment['q1_passed']
                : null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_q2_passed')) {
            $applicationPayload['initial_assessment_q2_passed'] = array_key_exists('q2_passed', $initialAssessment)
                ? (bool) $initialAssessment['q2_passed']
                : null;
        }
        if (Schema::hasColumn('applications', 'initial_assessment_has_pqe')) {
            $applicationPayload['initial_assessment_has_pqe'] = array_key_exists('has_pqe', $initialAssessment)
                ? (bool) $initialAssessment['has_pqe']
                : null;
        }

        $application = \App\Models\Applications::create($applicationPayload);
        session()->forget($this->initialAssessmentSessionKey((string) $vacancy->vacancy_id));
        Log::info('Apply success: application created', [
            'user_id' => Auth::id(),
            'vacancy_id' => $vacancy_id,
            'application_id' => $application->id,
        ]);

        // Consume fresh-upload marker for this vacancy after successful application submit.
        $vacancyUploads = session('vacancy_doc_uploads', []);
        if (is_array($vacancyUploads) && array_key_exists((string) $vacancy->vacancy_id, $vacancyUploads)) {
            unset($vacancyUploads[(string) $vacancy->vacancy_id]);
            session(['vacancy_doc_uploads' => $vacancyUploads]);
        }

        // Keep apply response fast: store lightweight DB notifications directly.
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'notifiable_type' => 'App\Models\Admin',
                'notifiable_id' => $admin->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'New Job Application',
                    'message' => Auth::user()->name . ' submitted an application for ' . $vacancy->position_title . '.',
                    'link' => route('admin.applicant_status', ['user_id' => Auth::id(), 'vacancy_id' => $vacancy->vacancy_id], false),
                    'section' => 'Application List',
                    'category' => 'document_verification',
                    'user_id' => Auth::id(),
                    'vacancy_id' => $vacancy->vacancy_id,
                ],
                'read_at' => null,
            ]);
        }

        activity()
            ->event('apply job')
            ->causedBy(Auth::user())
            ->performedOn($vacancy)
            ->withProperties(['vacancy_id' => $vacancy->vacancy_id, 'section' => 'Job Vacancy'])
            ->log('Applied to job vacancy.');

        return redirect()->route('my_applications')->with('success', 'Application submitted successfully!');
    }

    public function myApplications()
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete onboarding before viewing applications.');
        }

        $applications = $this->buildMyApplicationsQuery(request())->get();
        $filterOptions = $this->getMyApplicationFilterOptions();
        /*
        activity()
            ->causedBy(auth()->user())
            ->log('Viewed my applications.');
        */

        return view('dashboard_user.my_applications', [
            'applications' => $applications,
            'filterOptions' => $filterOptions,
        ]);
    }

    // USEREND application status
    public function applicationStatus($user_id, $vacancy_id)
    {
        if (Auth::check() && ApplicantOnboarding::shouldRequire(Auth::user())) {
            return redirect()
                ->route('dashboard_user')
                ->with('open_onboarding_modal', true)
                ->with('status', 'Please complete onboarding before viewing application status.');
        }

        if ((int) Auth::id() !== (int) $user_id) {
            abort(403, 'Unauthorized access to this application.');
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->firstOrFail();

        if (strcasecmp(trim((string) ($application->status ?? '')), 'Not Qualified') === 0) {
            return redirect()->route('my_applications')
                ->with('error', 'This application is already marked as Not Qualified and can no longer be opened.');
        }

        $examDetail = ExamDetail::where('vacancy_id', $vacancy_id)->first();

        $snapshotNotification = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user_id)
            ->where('data->type', 'application_overview')
            ->where('data->vacancy_id', $vacancy_id)
            ->latest()
            ->first();
        $snapshotData = $snapshotNotification?->data ?? null;
        $snapshotDocumentsById = collect($snapshotData['documents'] ?? [])->keyBy('id');

        $adminName = $snapshotData['last_modified_by'] ?? null;
        $lastModifiedAt = $snapshotData['notified_at'] ?? null;

        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        $isFinalRevisionDisqualified = $this->hasFinalRevisionDisqualification($application, $uploadedDocuments);
        $documents = [];
        $reviewStatuses = ['verified', 'okay/confirmed', 'needs revision', 'disapproved with deficiency'];

        $resolveApplicantVisibleState = function (
            string $liveStatus,
            bool $hasFile,
            ?array $snapshotDoc,
            string $liveRemarks = '',
            ?string $liveLastModifiedBy = null
        ) use ($reviewStatuses): array {
            $resolvedStatus = trim($liveStatus) !== '' ? $liveStatus : ($hasFile ? 'Pending' : 'Not Submitted');
            $resolvedRemarks = $liveRemarks;
            $resolvedLastModifiedBy = $liveLastModifiedBy;

            $isLiveReviewStatus = in_array(strtolower(trim($resolvedStatus)), $reviewStatuses, true);
            if (!$isLiveReviewStatus) {
                return [
                    'status' => $resolvedStatus,
                    'remarks' => $resolvedRemarks,
                    'last_modified_by' => $resolvedLastModifiedBy,
                ];
            }

            $snapshotStatus = trim((string) ($snapshotDoc['status'] ?? ''));
            $snapshotIsReviewStatus = $snapshotStatus !== ''
                && in_array(strtolower($snapshotStatus), $reviewStatuses, true);

            if ($snapshotIsReviewStatus) {
                return [
                    'status' => $snapshotStatus,
                    'remarks' => (string) ($snapshotDoc['remarks'] ?? ''),
                    'last_modified_by' => $snapshotDoc['last_modified_by'] ?? null,
                ];
            }

            return [
                'status' => $hasFile ? 'Pending' : 'Not Submitted',
                'remarks' => '',
                'last_modified_by' => null,
            ];
        };

        $labelMap = [
            'application_letter' => 'Application Letter',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
            'ipcr' => 'Performance Rating/IPCR in the last period (if applicable)',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificate/s of Training Attended/Participated relevant to the position being applied',
            'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'other_documents' => 'Other Documents Submitted',
        ];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            // Skip "isApproved" since it's not a document
            if ($docType === 'isApproved')
                continue;

            if ($docType === 'application_letter') {
                $hasFile = !empty($application->file_storage_path);
                $snapshotDoc = $snapshotDocumentsById->get('application_letter');
                $visibleState = $resolveApplicantVisibleState(
                    (string) ($application->file_status ?? ($hasFile ? 'Pending' : 'Not Submitted')),
                    $hasFile,
                    is_array($snapshotDoc) ? $snapshotDoc : null,
                    (string) ($application->file_remarks ?? ''),
                    $application->file_last_modified_by ?? null
                );

                $documents[] = [
                    'id' => 'application_letter',
                    'name' => $labelMap['application_letter'],
                    'text' => $labelMap['application_letter'],
                    'status' => $visibleState['status'],
                    'preview' => PreviewUrl::forPath($application->file_storage_path),
                    'remarks' => $visibleState['remarks'],
                    'last_modified_by' => $visibleState['last_modified_by'],
                    'isBold' => true,
                ];
            } else {
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';

                $status = 'Not Submitted';
                if ($doc) {
                    if (!empty($doc->status)) {
                        $status = $doc->status;
                    } elseif ($hasFile) {
                        $status = 'Pending';
                    }
                }

                $snapshotDoc = $snapshotDocumentsById->get($docType);
                $visibleState = $resolveApplicantVisibleState(
                    $status,
                    $hasFile,
                    is_array($snapshotDoc) ? $snapshotDoc : null,
                    (string) ($doc?->remarks ?? ''),
                    $doc?->last_modified_by
                );

                $documents[] = [
                    'id' => $docType,
                    'name' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $visibleState['status'],
                    'preview' => ($doc && !empty($doc->storage_path)) ? PreviewUrl::forPath($doc->storage_path) : '',
                    'remarks' => $visibleState['remarks'],
                    'last_modified_by' => $visibleState['last_modified_by'],
                    'isBold' => true,
                ];
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType($application->vacancy?->vacancy_type, $application->vacancy);
        $documents = $this->sortDocumentsForRequiredPriority($documents, $requiredDocumentIds);

        $displayApplicationStatus = $application->status ?? 'Pending';
        $applicationStatusNormalized = strtolower(trim((string) $displayApplicationStatus));
        // Cancellation is allowed only while application is still pending.
        $canCancelApplication = ($applicationStatusNormalized === 'pending');
        $hasAdminValidatedDocuments = trim((string) ($application->qs_result ?? '')) !== '';

        if ($hasAdminValidatedDocuments) {
            $displayQsEducation = $application->qs_education ?? 'no';
            $displayQsEligibility = $application->qs_eligibility ?? 'no';
            $displayQsExperience = $application->qs_experience ?? 'no';
            $displayQsTraining = $application->qs_training ?? 'no';
            $displayQsResult = $application->qs_result ?? 'Pending';
        } else {
            $displayQsEducation = 'pending';
            $displayQsEligibility = 'pending';
            $displayQsExperience = 'pending';
            $displayQsTraining = 'pending';
            $displayQsResult = 'Pending';
        }

        $displayDeadlineDate = $application->deadline_date ?? null;
        $displayDeadlineTime = $application->deadline_time ?? null;
        $hasDeadlineConfigured = !empty($displayDeadlineDate) && !empty($displayDeadlineTime);
        $showDeadlineSubmissionCard = $hasAdminValidatedDocuments && $hasDeadlineConfigured;
        $displayApplicationRemarks = $application->application_remarks ?? '';

        /*
        activity()
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties(['vacancy_id' => $application->vacancy_id])
            ->log('Viewed application status.');
        */

        return view('dashboard_user.application_status', compact(
            'application',
            'examDetail',
            'documents',
            'requiredDocumentIds',
            'adminName',
            'lastModifiedAt',
            'displayApplicationStatus',
            'displayQsEducation',
            'displayQsEligibility',
            'displayQsExperience',
            'displayQsTraining',
            'displayQsResult',
            'displayDeadlineDate',
            'displayDeadlineTime',
            'hasAdminValidatedDocuments',
            'hasDeadlineConfigured',
            'showDeadlineSubmissionCard',
            'canCancelApplication',
            'displayApplicationRemarks',
            'isFinalRevisionDisqualified',
            'user_id',
            'vacancy_id'
        ));
    }

    public function cancelApplication(Request $request, $user_id, $vacancy_id)
    {
        if ((int) Auth::id() !== (int) $user_id) {
            abort(403, 'Unauthorized access to this application.');
        }

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->firstOrFail();

        $currentStatus = strtolower(trim((string) ($application->status ?? '')));
        if ($currentStatus !== 'pending') {
            return redirect()
                ->route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id])
                ->with('error', 'Only pending applications can be cancelled.');
        }

        $application->update(['status' => 'Cancelled']);

        $vacancy = JobVacancy::query()
            ->where('vacancy_id', (string) $vacancy_id)
            ->first();

        // Keep cancellation response fast by writing DB notifications directly for admins.
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'notifiable_type' => 'App\\Models\\Admin',
                'notifiable_id' => $admin->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'Application Cancelled',
                    'message' => (Auth::user()->name ?? 'Applicant') . ' cancelled an application for ' . ($vacancy->position_title ?? 'a vacancy') . '.',
                    'link' => route('admin.applicant_status', ['user_id' => $user_id, 'vacancy_id' => $vacancy_id], false),
                    'section' => 'Application List',
                    'category' => 'application_status_update',
                    'user_id' => $user_id,
                    'vacancy_id' => $vacancy_id,
                ],
                'read_at' => null,
            ]);
        }

        activity()
            ->causedBy(Auth::user())
            ->performedOn($application)
            ->withProperties([
                'user_id' => $user_id,
                'vacancy_id' => $vacancy_id,
                'section' => 'Application List',
            ])
            ->log('Cancelled application.');

        return redirect()
            ->route('my_applications')
            ->with('success', 'Your application has been cancelled.');
    }

    /**
     * Get updated documents for AJAX refresh (user endpoint)
     */
    public function getUpdatedDocumentsUser(Request $request, $user_id, $vacancy_id)
    {
        if ((int) Auth::id() !== (int) $user_id) {
            return response()->json(['error' => 'Unauthorized access to this application.'], 403);
        }

        // Debug logging
        \Log::info("getUpdatedDocumentsUser called", [
            'user_id' => $user_id,
            'vacancy_id' => $vacancy_id,
            'auth_user_id' => Auth::id(),
            'method' => $request->method()
        ]);

        $application = Applications::where('user_id', $user_id)
            ->where('vacancy_id', $vacancy_id)
            ->with(['personalInformation', 'vacancy'])
            ->first();

        if (!$application) {
            \Log::error("Application not found", ['user_id' => $user_id, 'vacancy_id' => $vacancy_id]);
            return response()->json(['error' => 'Application not found'], 404);
        }

        if (strcasecmp(trim((string) ($application->status ?? '')), 'Not Qualified') === 0) {
            return response()->json([
                'error' => 'This application is already marked as Not Qualified and can no longer be opened.'
            ], 403);
        }

        // Use the same logic as applicationStatus method
        $snapshotNotification = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user_id)
            ->where('data->type', 'application_overview')
            ->where('data->vacancy_id', $vacancy_id)
            ->latest()
            ->first();
        $snapshotData = $snapshotNotification?->data ?? null;
        $snapshotDocumentsById = collect($snapshotData['documents'] ?? [])->keyBy('id');

        $uploadedDocuments = $this->loadUploadedDocumentsMap((int) $user_id, (string) $vacancy_id);
        $isFinalRevisionDisqualified = $this->hasFinalRevisionDisqualification($application, $uploadedDocuments);
        $documents = [];
        $reviewStatuses = ['verified', 'okay/confirmed', 'needs revision', 'disapproved with deficiency'];

        $resolveApplicantVisibleState = function (
            string $liveStatus,
            bool $hasFile,
            ?array $snapshotDoc,
            string $liveRemarks = '',
            ?string $liveLastModifiedBy = null
        ) use ($reviewStatuses): array {
            $resolvedStatus = trim($liveStatus) !== '' ? $liveStatus : ($hasFile ? 'Pending' : 'Not Submitted');
            $resolvedRemarks = $liveRemarks;
            $resolvedLastModifiedBy = $liveLastModifiedBy;

            $isLiveReviewStatus = in_array(strtolower(trim($resolvedStatus)), $reviewStatuses, true);
            if (!$isLiveReviewStatus) {
                return [
                    'status' => $resolvedStatus,
                    'remarks' => $resolvedRemarks,
                    'last_modified_by' => $resolvedLastModifiedBy,
                ];
            }

            $snapshotStatus = trim((string) ($snapshotDoc['status'] ?? ''));
            $snapshotIsReviewStatus = $snapshotStatus !== ''
                && in_array(strtolower($snapshotStatus), $reviewStatuses, true);

            if ($snapshotIsReviewStatus) {
                return [
                    'status' => $snapshotStatus,
                    'remarks' => (string) ($snapshotDoc['remarks'] ?? ''),
                    'last_modified_by' => $snapshotDoc['last_modified_by'] ?? null,
                ];
            }

            return [
                'status' => $hasFile ? 'Pending' : 'Not Submitted',
                'remarks' => '',
                'last_modified_by' => null,
            ];
        };

        // Debug: Log uploaded documents count
        \Log::info("Uploaded documents found", ['count' => $uploadedDocuments->count()]);

        $labelMap = [
            'application_letter' => 'Application Letter',
            'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
            'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
            'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
            'cert_eligibility' => 'Certificate of Eligibility / Board Rating',
            'ipcr' => 'Performance Rating/IPCR in the last period (if applicable)',
            'non_academic' => 'Non-Academic Awards Received',
            'cert_training' => 'Certificate/s of Training Attended/Participated relevant to the position being applied',
            'designation_order' => 'List with Certified Photocopy of Duly Confirmed Designation Order/s',
            'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
            'photocopy_diploma' => 'Diploma',
            'grade_masteraldoctorate' => 'Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned',
            'tor_masteraldoctorate' => 'Certified Photocopy of TOR with Masteral/Doctorate Degree',
            'cert_employment' => 'Certificate of Employment (If Any)',
            'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
            'passport_photo' => '2" x 2" or Passport Size Picture',
            'other_documents' => 'Other Documents Submitted',
        ];

        foreach (UploadedDocument::DOCUMENTS as $docType) {
            // Skip "isApproved" since it's not a document
            if ($docType === 'isApproved')
                continue;

            if ($docType === 'application_letter') {
                $hasFile = !empty($application->file_storage_path);
                $snapshotDoc = $snapshotDocumentsById->get('application_letter');
                $visibleState = $resolveApplicantVisibleState(
                    (string) ($application->file_status ?? ($hasFile ? 'Pending' : 'Not Submitted')),
                    $hasFile,
                    is_array($snapshotDoc) ? $snapshotDoc : null,
                    (string) ($application->file_remarks ?? ''),
                    $application->file_last_modified_by ?? null
                );

                $documents[] = [
                    'id' => 'application_letter',
                    'name' => $labelMap['application_letter'],
                    'text' => $labelMap['application_letter'],
                    'status' => $visibleState['status'],
                    'preview' => PreviewUrl::forPath($application->file_storage_path),
                    'remarks' => $visibleState['remarks'],
                    'last_modified_by' => $visibleState['last_modified_by'],
                    'isBold' => true,
                ];
            } else {
                $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';

                // Debug: Log document details
                \Log::info("Document check for {$docType} in getUpdatedDocumentsUser", [
                    'doc_found' => $doc ? true : false,
                    'has_file' => $hasFile,
                    'storage_path' => $doc?->storage_path,
                    'status' => $doc?->status,
                    'last_modified_by' => $doc?->last_modified_by
                ]);

                $status = 'Not Submitted';
                if ($doc) {
                    if (!empty($doc->status)) {
                        $status = $doc->status;
                    } elseif ($hasFile) {
                        $status = 'Pending';
                    }
                }

                $snapshotDoc = $snapshotDocumentsById->get($docType);
                $visibleState = $resolveApplicantVisibleState(
                    $status,
                    $hasFile,
                    is_array($snapshotDoc) ? $snapshotDoc : null,
                    (string) ($doc?->remarks ?? ''),
                    $doc?->last_modified_by
                );

                $documents[] = [
                    'id' => $docType,
                    'name' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'text' => $labelMap[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
                    'status' => $visibleState['status'],
                    'preview' => ($doc && !empty($doc->storage_path)) ? PreviewUrl::forPath($doc->storage_path) : '',
                    'remarks' => $visibleState['remarks'],
                    'last_modified_by' => $visibleState['last_modified_by'],
                    'isBold' => true,
                ];
            }
        }

        $requiredDocumentIds = $this->getRequiredDocumentIdsForVacancyType($application->vacancy?->vacancy_type, $application->vacancy);
        $documents = $this->sortDocumentsForRequiredPriority($documents, $requiredDocumentIds);

        \Log::info("Final documents array in getUpdatedDocumentsUser", ['count' => count($documents)]);

        return response()->json([
            'documents' => $documents,
            'requiredDocumentIds' => $requiredDocumentIds,
            'application' => [
                'status' => $application->status ?? 'Pending',
                'qs_result' => $application->qs_result ?? null,
                'file_last_modified_by' => $application->file_last_modified_by ?? null,
                'deadline_date' => $application->deadline_date ?? null,
                'deadline_time' => $application->deadline_time ?? null,
                'is_past_deadline' => $this->hasRevisionDeadlinePassed($application),
                'final_revision_disqualified' => $isFinalRevisionDisqualified,
            ]
        ]);
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

    private function hasFinalRevisionDisqualification(Applications $application, $uploadedDocuments): bool
    {
        if ($this->isRevisionComplianceLocked(
            (int) ($application->file_revision_requested_count ?? 0),
            $application->file_revision_requested_at ?? null,
            $application->file_revision_submitted_at ?? null
        )) {
            return true;
        }

        foreach ($uploadedDocuments as $doc) {
            if ($this->isRevisionComplianceLocked(
                (int) ($doc->revision_requested_count ?? 0),
                $doc->revision_requested_at ?? null,
                $doc->revision_submitted_at ?? null
            )) {
                return true;
            }
        }

        return false;
    }

    private function resolveUploadedDocument($uploadedDocuments, string $docType): ?UploadedDocument
    {
        $doc = $uploadedDocuments->get($docType);
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }
        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $uploadedDocuments->get($alias);
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }
        return $doc ?: null;
    }

    private function loadUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
        $docsQuery = UploadedDocument::where('user_id', $userId);
        if ($supportsVacancyScopedDocs) {
            if (!empty($vacancyId)) {
                $docsQuery->where('vacancy_id', $vacancyId);
            } else {
                $docsQuery->whereNull('vacancy_id');
            }
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function loadReusableUploadedDocumentsMap(int $userId, ?string $vacancyId = null)
    {
        $supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');

        $docsQuery = UploadedDocument::where('user_id', $userId)
            ->whereNotNull('storage_path')
            ->where('storage_path', '!=', 'NOINPUT');

        if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
            $docsQuery->orderByRaw(
                "CASE WHEN vacancy_id = ? THEN 0 WHEN vacancy_id IS NULL THEN 1 ELSE 2 END",
                [(string) $vacancyId]
            );
        } elseif ($supportsVacancyScopedDocs) {
            $docsQuery->orderByRaw('CASE WHEN vacancy_id IS NULL THEN 0 ELSE 1 END');
        }

        $docs = $docsQuery
            ->orderByDesc('updated_at')
            ->get();

        return $docs
            ->unique('document_type')
            ->keyBy('document_type');
    }

    private function hasStoredUploadedDocument($uploadedDocuments, string $docType): bool
    {
        $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
        return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
    }

    private function resolveDocumentGalleryItem($documents, string $docType): ?DocumentGalleryItem
    {
        $doc = $documents->get($docType);
        if ($doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT') {
            return $doc;
        }

        foreach (self::DOCUMENT_TYPE_ALIASES[$docType] ?? [] as $alias) {
            $aliasDoc = $documents->get($alias);
            if ($aliasDoc && !empty($aliasDoc->storage_path) && $aliasDoc->storage_path !== 'NOINPUT') {
                return $aliasDoc;
            }
        }

        return $doc ?: null;
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

    private function hasStoredDocumentGalleryItem($documents, string $docType): bool
    {
        $doc = $this->resolveDocumentGalleryItem($documents, $docType);
        return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
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
            if ($this->hasStoredUploadedDocument($vacancyDocs, (string) $docType)) {
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

    private function resolveVacancySupportingDocumentSelection(?JobVacancy $vacancy = null): array
    {
        if (!$vacancy) {
            return [];
        }

        if ($vacancy->supporting_documents_required !== null) {
            return $this->normalizeSupportingDocumentSelection($vacancy->supporting_documents_required);
        }

        if (!Schema::hasTable('vacancy_titles')) {
            return [];
        }

        $positionTitleCandidates = array_values(array_unique(array_filter([
            trim((string) $vacancy->getRawOriginal('position_title')),
            trim((string) $vacancy->position_title),
        ])));

        if (empty($positionTitleCandidates)) {
            return [];
        }

        $normalizedTrack = $this->normalizeTrack($vacancy->vacancy_type ?? null);
        $templateVacancy = VacancyTitle::query()
            ->where(function ($query) use ($positionTitleCandidates) {
                foreach ($positionTitleCandidates as $index => $positionTitle) {
                    if ($index === 0) {
                        $query->where('position_title', $positionTitle);
                    } else {
                        $query->orWhere('position_title', $positionTitle);
                    }
                }
            })
            ->whereRaw("UPPER(TRIM(COALESCE(vacancy_type, ''))) = ?", [strtoupper($normalizedTrack)])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (!$templateVacancy || $templateVacancy->supporting_documents_required === null) {
            return [];
        }

        return $this->normalizeSupportingDocumentSelection($templateVacancy->supporting_documents_required);
    }

    private function getRequiredDocumentIdsForVacancyType(?string $vacancyType, ?JobVacancy $vacancy = null): array
    {
        $requiredDocumentIds = $this->resolveVacancySupportingDocumentSelection($vacancy);

        if (empty($requiredDocumentIds)) {
            $requiredDocumentIds = $this->getRequiredDocsByTrack()[$this->normalizeTrack($vacancyType)] ?? [];
        }

        usort($requiredDocumentIds, function ($a, $b) {
            $labelA = strtolower($this->getDocumentLabelMap()[$a] ?? $a);
            $labelB = strtolower($this->getDocumentLabelMap()[$b] ?? $b);
            return $labelA <=> $labelB;
        });

        return $requiredDocumentIds;
    }

    private function sortDocumentsForRequiredPriority(array $documents, array $requiredDocumentIds): array
    {
        $requiredLookup = array_fill_keys($requiredDocumentIds, true);

        usort($documents, function ($a, $b) use ($requiredLookup) {
            $requiredA = isset($requiredLookup[$a['id'] ?? '']) ? 0 : 1;
            $requiredB = isset($requiredLookup[$b['id'] ?? '']) ? 0 : 1;

            if ($requiredA !== $requiredB) {
                return $requiredA - $requiredB;
            }

            $labelA = strtolower((string) ($a['text'] ?? $a['name'] ?? $a['id'] ?? ''));
            $labelB = strtolower((string) ($b['text'] ?? $b['name'] ?? $b['id'] ?? ''));
            return $labelA <=> $labelB;
        });

        return $documents;
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
            'passport_photo' => '2" x 2" or Passport Size Picture',
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

    private function getRequiredDocsByTrack(): array
    {
        $allDocumentTypes = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn($doc) => $doc !== 'isApproved'
        ));

        return [
            'COS' => self::COS_REQUIRED_DOCUMENTS,
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

    private function normalizeTrack(?string $track): string
    {
        return strcasecmp((string) $track, 'COS') === 0 ? 'COS' : 'Plantilla';
    }

    private function getRequiredDocsModalState(int $userId, ?JobVacancy $vacancy, ?string $vacancyId = null): array
    {
        $vacancyTrack = $this->normalizeTrack($vacancy?->vacancy_type);
        $requiredDocs = $this->getRequiredDocumentIdsForVacancyType($vacancy?->vacancy_type, $vacancy);
        $documentLabels = $this->getDocumentLabelMap();

        $previewDocs = array_map(function (string $docType) use ($documentLabels) {
            return [
                'key' => $docType,
                'label' => $documentLabels[$docType] ?? ucwords(str_replace('_', ' ', $docType)),
            ];
        }, $requiredDocs);

        $galleryDocuments = $this->loadDocumentGalleryMap($userId);

        $hasMissing = collect($requiredDocs)->contains(function (string $docType) use ($galleryDocuments) {
            return !$this->hasStoredDocumentGalleryItem($galleryDocuments, $docType);
        });

        return [
            'hasMissing' => $hasMissing,
            'previewDocs' => $previewDocs,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
            ]),
        ];
    }

    private function getTrackCompletenessByUser(int $userId, ?JobVacancy $vacancy = null): array
    {
        $requiredDocsByTrack = $this->getRequiredDocsByTrack();
        
        // If a specific vacancy is provided, use its custom requirements for its track
        if ($vacancy) {
            $track = $this->normalizeTrack($vacancy->vacancy_type);
            $requiredDocsByTrack[$track] = $this->getRequiredDocumentIdsForVacancyType($vacancy->vacancy_type, $vacancy);
        }

        $galleryDocuments = $this->loadDocumentGalleryMap($userId);

        $isComplete = [];
        foreach ($requiredDocsByTrack as $track => $requiredDocs) {
            $isComplete[$track] = collect($requiredDocs)->every(function (string $docType) use ($galleryDocuments) {
                return $this->hasStoredDocumentGalleryItem($galleryDocuments, $docType);
            });
        }

        return $isComplete;
    }

    private function getDocumentTrackMismatchState(int $userId, ?JobVacancy $vacancy = null): array
    {
        $vacancyType = $vacancy?->vacancy_type;
        $vacancyId = $vacancy?->vacancy_id;

        $vacancyTrack = $this->normalizeTrack($vacancyType);
        $otherTrack = $vacancyTrack === 'COS' ? 'Plantilla' : 'COS';
        $trackCompleteness = $this->getTrackCompletenessByUser($userId, $vacancy);

        $hasMismatch = ($trackCompleteness[$otherTrack] ?? false) && !($trackCompleteness[$vacancyTrack] ?? false);

        return [
            'hasMismatch' => $hasMismatch,
            'submittedTrack' => $hasMismatch ? $otherTrack : null,
            'vacancyTrack' => $vacancyTrack,
            'redirectUrl' => route('display_c5', [
                'doc_track' => $vacancyTrack,
                'vacancy_id' => $vacancyId,
            ]),
        ];
    }

    private function hasCompletedPdsForApply(int $userId): bool
    {
        $personalInfo = PersonalInformation::where('user_id', $userId)->first();
        $familyBackground = FamilyBackground::where('user_id', $userId)->first();
        $educationBackground = EducationalBackground::where('user_id', $userId)->first();
        $miscInfo = MiscInfos::where('user_id', $userId)->first();

        if (!$personalInfo || !$familyBackground || !$educationBackground || !$miscInfo) {
            return false;
        }

        return $this->hasMeaningfulValue($personalInfo->surname)
            && $this->hasMeaningfulValue($personalInfo->first_name)
            && $this->hasMeaningfulValue($personalInfo->mobile_no)
            && $this->hasMeaningfulValue($personalInfo->email_address)
            && $this->hasMeaningfulValue($familyBackground->mother_maiden_surname)
            && $this->hasMeaningfulValue($familyBackground->mother_maiden_first_name)
            && $this->hasMeaningfulValue($educationBackground->elem_school)
            && $this->hasMeaningfulValue($educationBackground->jhs_school)
            && $this->hasMeaningfulValue($miscInfo->govt_id_type)
            && $this->hasMeaningfulValue($miscInfo->govt_id_number);
    }

    public function hasCompletedPdsForApplicant(int $userId): bool
    {
        return $this->hasCompletedPdsForApply($userId);
    }

    private function hasMeaningfulValue($value): bool
    {
        if (is_array($value)) {
            return !empty(array_filter($value, fn($item) => $this->hasMeaningfulValue($item)));
        }

        $normalized = trim((string) $value);
        return $normalized !== '' && strtoupper($normalized) !== 'NOINPUT';
    }

    private function evaluateApplicantQualificationGateForVacancy(int $userId, JobVacancy $vacancy): array
    {
        $educationCheck = $this->evaluateEducationRequirementForApplicant($userId, $vacancy);
        $experienceCheck = $this->evaluateExperienceRequirementForApplicant($userId, $vacancy->qualification_experience ?? null);
        $trainingCheck = $this->evaluateTrainingRequirementForApplicant($userId, $vacancy->qualification_training ?? null);
        $eligibilityState = $this->evaluateApplicantEligibilityForVacancy($userId, $vacancy);

        $eligibilityRequired = !empty($eligibilityState['requiredEligibilities'] ?? []);
        $eligibilityCheck = [
            'required' => $eligibilityRequired,
            'met' => !$eligibilityRequired || (bool) ($eligibilityState['isEligible'] ?? false),
            'status' => !$eligibilityRequired
                ? 'na'
                : ((bool) ($eligibilityState['isEligible'] ?? false) ? 'yes' : 'no'),
            'requirement' => $eligibilityRequired
                ? implode(', ', (array) ($eligibilityState['requiredEligibilities'] ?? []))
                : null,
            'message' => $eligibilityState['message'] ?? null,
            'required_values' => array_values((array) ($eligibilityState['requiredEligibilities'] ?? [])),
            'applicant_values' => array_values((array) ($eligibilityState['applicantEligibilities'] ?? [])),
        ];

        $checks = [
            'education' => $educationCheck,
            'training' => $trainingCheck,
            'experience' => $experienceCheck,
            'eligibility' => $eligibilityCheck,
        ];

        $missingLabels = $this->collectMissingQualificationLabels($checks);

        $isQualified = empty($missingLabels);
        $message = null;
        if (!$isQualified) {
            $message = 'You are not yet qualified to apply for this position. '
                . 'Please review the missing requirements and update your PDS.';
        }

        return [
            'isQualified' => $isQualified,
            'message' => $message,
            'checks' => $checks,
        ];
    }

    public function evaluateQualificationGateForApplicant(int $userId, JobVacancy $vacancy): array
    {
        $result = $this->evaluateApplicantQualificationGateForVacancy($userId, $vacancy);
        $result['missing_labels'] = $this->collectMissingQualificationLabels((array) ($result['checks'] ?? []));
        return $result;
    }

    private function collectMissingQualificationLabels(array $checks): array
    {
        $missingLabels = [];
        foreach ($checks as $field => $check) {
            if (($check['required'] ?? false) && !($check['met'] ?? false)) {
                $label = ucfirst((string) $field);
                $requirement = trim((string) ($check['requirement'] ?? ''));
                $missingLabels[] = $requirement !== ''
                    ? "{$label} ({$requirement})"
                    : $label;
            }
        }

        return $missingLabels;
    }

    private function evaluateEducationRequirementForApplicant(int $userId, JobVacancy $vacancy): array
    {
        $profile = $this->buildApplicantEducationProfile($userId);
        $requirement = $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        if ($requirement === null) {
            return [
                'required' => false,
                'met' => true,
                'status' => 'na',
                'requirement' => null,
            ];
        }

        $alternativeEvaluation = $this->evaluateAlternativeEducationRequirements($profile, $requirement);
        if (is_array($alternativeEvaluation)) {
            $met = (bool) ($alternativeEvaluation['met'] ?? false);
            $matchedRuleCode = trim((string) ($alternativeEvaluation['matched_rule_code'] ?? ''));
            $isRelevantBachelorRule = $matchedRuleCode === 'bachelor_relevant_admin_review';

            return [
                'required' => true,
                'met' => $met,
                'status' => $met ? 'yes' : 'no',
                'requirement' => $requirement,
                'rule_code' => 'composite_or',
                'explanation' => $isRelevantBachelorRule
                    ? 'At least one OR-based education alternative is satisfied. Relevance to the position is still subject to HR review.'
                    : 'Education requirement contains OR alternatives. Applicant is qualified if at least one alternative is met.',
                'compiled_rule' => [
                    'rule_code' => 'composite_or',
                    'alternatives' => $alternativeEvaluation['evaluations'] ?? [],
                ],
                'applicant_profile' => $profile,
            ];
        }

        $compiledRule = $this->resolveCompiledEducationRuleForVacancy($vacancy, $requirement);
        $met = is_array($compiledRule) ? $this->evaluateCompiledEducationRule($profile, $compiledRule) : null;
        $usedFallback = false;
        if (!is_bool($met)) {
            $met = $this->evaluateLegacyEducationRequirementByText($profile, $requirement);
            $usedFallback = true;
        }
        $isAdvisoryOnly = is_array($compiledRule) && (bool) ($compiledRule['advisory_only'] ?? false) && !$usedFallback;
        if ($isAdvisoryOnly) {
            $met = true;
        }

        $ruleCode = is_array($compiledRule) ? (string) ($compiledRule['rule_code'] ?? '') : '';
        if ($ruleCode === '') {
            $ruleCode = $usedFallback ? 'legacy_text_fallback' : null;
        } elseif ($usedFallback) {
            $ruleCode = 'legacy_text_fallback';
        }
        $isRelevantBachelorRule = $ruleCode === 'bachelor_relevant_admin_review' && !$usedFallback;

        return [
            'required' => !$isAdvisoryOnly,
            'met' => $met,
            'status' => $isAdvisoryOnly ? 'na' : ($met ? 'yes' : 'no'),
            'requirement' => $requirement,
            'rule_code' => $ruleCode,
            'explanation' => $isRelevantBachelorRule
                ? 'This vacancy accepts any bachelor\'s degree for initial screening. HR will verify relevance to the position during review.'
                : ($isAdvisoryOnly
                    ? 'Education requirement uses relevant-field wording; final verification is for admin review.'
                    : ($usedFallback
                        ? 'Requirement text was ambiguous, so legacy text matching was used.'
                        : null)),
            'compiled_rule' => is_array($compiledRule) ? $compiledRule : null,
            'applicant_profile' => $profile,
        ];
    }

    private function evaluateExperienceRequirementForApplicant(int $userId, ?string $rawRequirement): array
    {
        $requirement = $this->normalizeQualificationRequirement($rawRequirement);
        if ($requirement === null) {
            return [
                'required' => false,
                'met' => true,
                'status' => 'na',
                'requirement' => null,
                'actual_months' => 0,
                'required_months' => null,
                'submitted' => false,
            ];
        }

        $workExperiences = WorkExperience::query()
            ->where('user_id', $userId)
            ->get();

        $totalMonths = $this->sumApplicantExperienceMonths($workExperiences);
        $submitted = $this->hasQualificationDocumentSubmission($userId, 'experience');
        $requiredMonths = $this->parseQualificationRequirementMonths($requirement);
        $met = $submitted;

        return [
            'required' => true,
            'met' => $met,
            'status' => $met ? 'yes' : 'no',
            'requirement' => $requirement,
            'actual_months' => $totalMonths,
            'required_months' => $requiredMonths,
            'matched_topic_months' => null,
            'requires_topic_match' => false,
            'topic_match_met' => $submitted,
            'submitted' => $submitted,
        ];
    }

    private function resolveExperienceTopicKeywords(string $requirementLower): ?array
    {
        if ($this->textContainsAny($requirementLower, [
            'management and supervision',
            'management',
            'managerial',
            'supervision',
            'supervisory',
        ])) {
            return [
                'management',
                'managerial',
                'supervision',
                'supervisory',
            ];
        }

        if ($this->textContainsAny($requirementLower, [
            'lgoo',
            'local governance',
            'governance operations',
            'community development',
            'strategic thinking',
            'planning',
        ])) {
            return [
                'lgoo',
                'local governance',
                'governance operations',
                'community development',
                'strategic thinking',
                'planning',
            ];
        }

        return null;
    }

    private function sumApplicantExperienceMonths($records, ?array $keywords = null): int
    {
        $totalMonths = 0;
        foreach ($records as $work) {
            if (!empty($keywords) && !$this->experienceRecordMatchesKeywords($work, $keywords)) {
                continue;
            }

            $fromRaw = trim((string) ($work->work_exp_from ?? ''));
            if ($fromRaw === '') {
                continue;
            }

            try {
                $from = Carbon::parse($fromRaw);
            } catch (\Throwable $e) {
                continue;
            }

            $toRaw = trim((string) ($work->work_exp_to ?? ''));
            if ($toRaw === '' || strtolower($toRaw) === 'present') {
                $to = Carbon::now();
            } else {
                try {
                    $to = Carbon::parse($toRaw);
                } catch (\Throwable $e) {
                    continue;
                }
            }

            if ($to->lessThan($from)) {
                continue;
            }

            $totalMonths += $from->diffInMonths($to) + 1;
        }

        return $totalMonths;
    }

    private function experienceRecordMatchesKeywords($work, array $keywords): bool
    {
        $haystack = strtolower(trim(implode(' ', [
            (string) ($work->work_exp_position ?? ''),
            (string) ($work->work_exp_department ?? ''),
            (string) ($work->work_exp_status ?? ''),
        ])));

        return $this->textContainsAny($haystack, $keywords);
    }

    private function evaluateTrainingRequirementForApplicant(int $userId, ?string $rawRequirement): array
    {
        $requirement = $this->normalizeQualificationRequirement($rawRequirement);
        if ($requirement === null) {
            return [
                'required' => false,
                'met' => true,
                'status' => 'na',
                'requirement' => null,
                'actual_hours' => 0,
                'required_hours' => null,
                'submitted' => false,
            ];
        }

        $records = LearningAndDevelopment::query()
            ->where('user_id', $userId)
            ->get();

        $totalHours = $this->sumApplicantTrainingHours($records);
        $requiredHours = $this->parseQualificationRequirementHours($requirement);
        $submitted = $this->hasQualificationDocumentSubmission($userId, 'training');
        $met = $submitted;

        return [
            'required' => true,
            'met' => $met,
            'status' => $met ? 'yes' : 'no',
            'requirement' => $requirement,
            'actual_hours' => $totalHours,
            'required_hours' => $requiredHours,
            'matched_topic_hours' => null,
            'requires_topic_match' => false,
            'topic_match_met' => $submitted,
            'submitted' => $submitted,
        ];
    }

    private function hasQualificationDocumentSubmission(int $userId, string $field): bool
    {
        $galleryDocuments = $this->loadDocumentGalleryMap($userId);
        $docTypes = match ($field) {
            'experience' => ['signed_work_exp_sheet', 'cert_employment', 'other_documents'],
            'training' => ['cert_training', 'cert_lgoo_induction', 'other_documents'],
            default => [],
        };

        foreach ($docTypes as $docType) {
            if ($this->hasStoredDocumentGalleryItem($galleryDocuments, $docType)) {
                return true;
            }
        }

        if ($field === 'experience') {
            return WorkExperience::where('user_id', $userId)->exists();
        }

        if ($field === 'training') {
            return LearningAndDevelopment::where('user_id', $userId)->exists();
        }

        return false;
    }

    private function resolveTrainingTopicKeywords(string $requirementLower): ?array
    {
        if ($this->textContainsAny($requirementLower, [
            'lgoo',
            'local governance',
            'governance operations',
            'community development',
            'strategic thinking',
        ])) {
            return [
                'lgoo',
                'local governance',
                'governance operations',
                'community development',
                'strategic thinking',
            ];
        }

        if ($this->textContainsAny($requirementLower, [
            'management and supervision',
            'management',
            'managerial',
            'supervision',
            'supervisory',
        ])) {
            return [
                'management',
                'managerial',
                'supervision',
                'supervisory',
            ];
        }

        return null;
    }

    private function sumApplicantTrainingHours($records, ?array $keywords = null): int
    {
        $hours = (float) $records->sum(function ($row) use ($keywords) {
            if (!empty($keywords) && !$this->trainingRecordMatchesKeywords($row, $keywords)) {
                return 0;
            }

            $value = $row->learning_hours ?? 0;
            return is_numeric($value) ? (float) $value : 0;
        });

        return (int) round($hours);
    }

    private function trainingRecordMatchesKeywords($row, array $keywords): bool
    {
        $haystack = strtolower(trim(implode(' ', [
            (string) ($row->learning_title ?? ''),
            (string) ($row->learning_type ?? ''),
            (string) ($row->learning_conducted ?? ''),
        ])));

        return $this->textContainsAny($haystack, $keywords);
    }

    private function buildApplicantEducationProfile(int $userId): array
    {
        $defaultProfile = [
            'hasElementaryOrHigher' => false,
            'hasSeniorHighOrHigher' => false,
            'hasHighSchoolOrHigher' => false,
            'hasCollegeEntryOrHigher' => false,
            'hasCollegeDegreeOrHigher' => false,
            'hasBachelorOrHigher' => false,
            'hasVocational' => false,
            'hasAtLeastTwoYearsCollege' => false,
            'collegeYearsCompleted' => 0,
            'estimatedCollegeUnits' => 0,
            'estimatedCollegeSemesters' => 0,
            'hasGrad' => false,
            'hasMasters' => false,
            'hasDoctorate' => false,
            'hasLawDegree' => false,
            'hasAnyEducation' => false,
            'educationKeywordHaystack' => '',
            'degree_entries' => [],
        ];

        $education = EducationalBackground::query()->where('user_id', $userId)->first();
        if (!$education) {
            return $defaultProfile;
        }

        $hasElementary = $this->hasMeaningfulValue($education->elem_school)
            || $this->hasMeaningfulValue($education->elem_year_graduated)
            || $this->hasMeaningfulValue($education->elem_earned);

        $secondaryBasic = strtolower(trim((string) ($education->jhs_basic ?? '')));
        $mentionsShsTrack = $this->textContainsAny($secondaryBasic, ['senior high', 'grade 12', 'shs']);
        $hasSecondaryRecord = $this->hasMeaningfulValue($education->jhs_school)
            || $this->hasMeaningfulValue($education->jhs_year_graduated)
            || $this->hasMeaningfulValue($education->jhs_earned)
            || $mentionsShsTrack;

        $hasShsCompleted = $mentionsShsTrack
            && (
                $this->hasMeaningfulValue($education->jhs_year_graduated)
                || $this->textContainsAny(strtolower((string) ($education->jhs_earned ?? '')), ['grade 12', 'completed', 'graduate', 'graduated'])
            );

        // Legacy SHS columns are retained for compatibility with previously saved data.
        if (!$hasShsCompleted) {
            $hasShsCompleted = ($this->hasMeaningfulValue($education->shs_year_graduated) || $this->textContainsAny(strtolower((string) ($education->shs_earned ?? '')), ['grade 12', 'completed', 'graduate', 'graduated']))
                && ($this->hasMeaningfulValue($education->shs_school) || $this->hasMeaningfulValue($education->shs_basic) || $this->hasMeaningfulValue($education->shs_earned));
        }

        $hasLegacyHighSchoolFromSecondary = $hasSecondaryRecord
            && !$mentionsShsTrack
            && $this->secondaryBasicIsLegacyHighSchool((string) ($education->jhs_basic ?? ''));
        $hasVocational = $this->hasMeaningfulValue($education->vocational);
        $hasCollege = $this->hasMeaningfulValue($education->college);

        // Treat graduate-level entries as valid even when users accidentally place them in College.
        $graduateKeywords = [
            'master',
            'masteral',
            "master's",
            'post graduate',
            'postgraduate',
            'doctoral',
            'doctorate',
            'doctor of philosophy',
            'phd',
            'ph.d',
            'llm',
            'm.a',
            'm.s',
            'msc',
            'mba',
            'mpa',
        ];
        $hasGrad = $this->hasMeaningfulValue($education->grad)
            || $this->educationEntriesContainKeywords($education->grad, $graduateKeywords)
            || $this->educationEntriesContainKeywords($education->college, $graduateKeywords);
        $hasMasters = $this->educationEntriesContainKeywords(
            [$education->grad, $education->college],
            ['master', 'masteral', "master's", 'mba', 'mpa', 'msc', 'm.s', 'm.a']
        );
        $hasDoctorate = $this->educationEntriesContainDoctorateKeywords([$education->grad, $education->college]);
        $hasCollegeDegree = $this->hasCollegeDegree($education->college);
        $collegeYearsCompleted = $this->estimateHighestCollegeYearsCompleted($education->college);
        if ($hasGrad || $hasCollegeDegree) {
            // Graduate studies imply college completion.
            $collegeYearsCompleted = max($collegeYearsCompleted, 4);
        }
        $estimatedCollegeUnits = max(
            $collegeYearsCompleted * 36,
            $this->estimateHighestCollegeUnitsCompleted($education->college)
        );
        $estimatedCollegeSemesters = max(
            $collegeYearsCompleted * 2,
            $this->estimateHighestCollegeSemestersCompleted($education->college)
        );
        $hasAtLeastTwoYearsCollege = $collegeYearsCompleted >= 2;
        $hasLawDegree = $this->valueContainsAnyKeyword(
            [
                $education->college,
                $education->grad,
                $education->elem_earned,
                $education->jhs_earned,
                $education->jhs_basic,
                $education->shs_earned,
            ],
            ['bachelor of laws', 'llb', 'juris doctor', 'attorney', 'law']
        );

        $hasSeniorHighOrHigher = $hasShsCompleted || $hasVocational || $hasCollege || $hasGrad;
        $hasHighSchoolOrHigher = $hasLegacyHighSchoolFromSecondary || $hasSeniorHighOrHigher;
        $hasCollegeEntryOrHigher = $hasCollege || $hasGrad;
        $hasCollegeDegreeOrHigher = $hasCollegeDegree || $hasGrad;
        $hasBachelorOrHigher = $hasCollegeDegreeOrHigher || $hasLawDegree;
        $hasElementaryOrHigher = $hasElementary || $hasHighSchoolOrHigher;
        $hasAnyEducation = $hasElementary || $hasSecondaryRecord || $hasVocational || $hasCollege || $hasGrad;
        $educationKeywordHaystack = strtolower(trim(implode(' ', array_filter([
            $this->flattenEducationValueToText($education->college),
            $this->flattenEducationValueToText($education->grad),
            $this->flattenEducationValueToText($education->vocational),
            (string) ($education->elem_earned ?? ''),
            (string) ($education->jhs_earned ?? ''),
            (string) ($education->shs_earned ?? ''),
            (string) ($education->jhs_basic ?? ''),
            (string) ($education->shs_basic ?? ''),
        ]))));
        $degreeEntries = $this->buildApplicantDegreeEntries($education);

        return [
            'hasElementaryOrHigher' => $hasElementaryOrHigher,
            'hasSeniorHighOrHigher' => $hasSeniorHighOrHigher,
            'hasHighSchoolOrHigher' => $hasHighSchoolOrHigher,
            'hasCollegeEntryOrHigher' => $hasCollegeEntryOrHigher,
            'hasCollegeDegreeOrHigher' => $hasCollegeDegreeOrHigher,
            'hasBachelorOrHigher' => $hasBachelorOrHigher,
            'hasVocational' => $hasVocational,
            'hasAtLeastTwoYearsCollege' => $hasAtLeastTwoYearsCollege,
            'collegeYearsCompleted' => $collegeYearsCompleted,
            'estimatedCollegeUnits' => $estimatedCollegeUnits,
            'estimatedCollegeSemesters' => $estimatedCollegeSemesters,
            'hasGrad' => $hasGrad,
            'hasMasters' => $hasMasters,
            'hasDoctorate' => $hasDoctorate,
            'hasLawDegree' => $hasLawDegree,
            'hasAnyEducation' => $hasAnyEducation,
            'educationKeywordHaystack' => $educationKeywordHaystack,
            'degree_entries' => $degreeEntries,
        ];
    }

    private function educationEntriesContainDoctorateKeywords($entries): bool
    {
        if (!is_array($entries)) {
            return $this->textContainsDoctorateLevelKeyword((string) $entries);
        }

        foreach ($entries as $entry) {
            if (is_array($entry)) {
                if ($this->educationEntriesContainDoctorateKeywords($entry)) {
                    return true;
                }
                continue;
            }

            if ($this->textContainsDoctorateLevelKeyword((string) $entry)) {
                return true;
            }
        }

        return false;
    }

    private function buildApplicantDegreeEntries(EducationalBackground $education): array
    {
        $entries = [];
        $pushEntry = function (string $level, string $text) use (&$entries): void {
            $normalizedLevel = $this->normalizeEducationLevelKey($level);
            $normalizedText = $this->normalizeEducationFieldText($text);
            if ($normalizedLevel === null || $normalizedText === '') {
                return;
            }

            $key = $normalizedLevel . '|' . $normalizedText;
            if (array_key_exists($key, $entries)) {
                return;
            }

            $entries[$key] = [
                'level' => $normalizedLevel,
                'text' => $normalizedText,
            ];
        };

        $collect = function ($rawEntries, string $source) use (&$pushEntry): void {
            if (!is_array($rawEntries)) {
                return;
            }

            foreach ($rawEntries as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $entryText = trim(implode(' ', array_filter([
                    (string) ($entry['basic'] ?? ''),
                    (string) ($entry['earned'] ?? ''),
                    (string) ($entry['school'] ?? ''),
                ])));
                if ($entryText === '') {
                    continue;
                }

                $isLawTrack = $this->valueContainsAnyKeyword($entryText, ['bachelor of laws', 'llb', 'juris doctor', 'doctor of jurisprudence', 'attorney']);
                if ($isLawTrack) {
                    $pushEntry('law', $entryText);
                }

                if ($this->textContainsDoctorateLevelKeyword($entryText)) {
                    $pushEntry('doctorate', $entryText);
                    continue;
                }

                if ($this->textContainsAny(strtolower($entryText), ['master', 'masteral', "master's", 'master of', 'mba', 'mpa', 'msc', 'm.s', 'm.a'])) {
                    $pushEntry('masteral', $entryText);
                    continue;
                }

                $isGraduated = $this->hasMeaningfulValue($entry['year_graduated'] ?? null)
                    || $this->textContainsAny(strtolower((string) ($entry['earned'] ?? '')), ['graduate', 'graduated', 'degree', 'bachelor', 'baccalaureate']);

                if ($source === 'grad' && ($isGraduated || $this->hasMeaningfulValue($entry['school'] ?? null))) {
                    $pushEntry('masteral', $entryText);
                    continue;
                }

                if ($source === 'college' && $isGraduated) {
                    $pushEntry('bachelor', $entryText);
                }
            }
        };

        $collect($education->college, 'college');
        $collect($education->grad, 'grad');

        return array_values($entries);
    }

    private function hasCollegeDegree($entries): bool
    {
        if (!is_array($entries)) {
            return false;
        }

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            if ($this->hasMeaningfulValue($entry['year_graduated'] ?? null)) {
                return true;
            }

            $earned = strtolower(trim((string) ($entry['earned'] ?? '')));
            if ($earned !== '' && $this->textContainsAny($earned, [
                'graduate',
                'degree',
                'baccalaureate',
                'bachelor',
            ])) {
                return true;
            }
        }

        return false;
    }

    private function educationEntriesContainKeywords($entries, array $keywords): bool
    {
        if (!is_array($entries)) {
            $text = strtolower(trim((string) $entries));
            return $text !== '' && $this->textContainsAny($text, $keywords);
        }

        foreach ($entries as $entry) {
            if (is_array($entry)) {
                $basic = strtolower(trim((string) ($entry['basic'] ?? '')));
                $earned = strtolower(trim((string) ($entry['earned'] ?? '')));
                $haystack = trim($basic . ' ' . $earned);
                if ($haystack !== '' && $this->textContainsAny($haystack, $keywords)) {
                    return true;
                }

                if ($this->educationEntriesContainKeywords($entry, $keywords)) {
                    return true;
                }

                continue;
            }

            $text = strtolower(trim((string) $entry));
            if ($text !== '' && $this->textContainsAny($text, $keywords)) {
                return true;
            }
        }

        return false;
    }

    private function secondaryBasicIsSeniorHigh(string $value): bool
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, 'senior high') || str_contains($normalized, 'grade 12');
    }

    private function secondaryBasicIsLegacyHighSchool(string $value): bool
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            // Legacy rows may have empty basic text but were historically "High School".
            return true;
        }

        if ($this->secondaryBasicIsSeniorHigh($normalized)) {
            return false;
        }

        return str_contains($normalized, 'high school') && !str_contains($normalized, 'junior');
    }

    private function estimateHighestCollegeYearsCompleted($entries): int
    {
        if (!is_array($entries)) {
            return 0;
        }

        $maxYears = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $hasAnyEntryValue = $this->hasMeaningfulValue($entry['school'] ?? null)
                || $this->hasMeaningfulValue($entry['basic'] ?? null)
                || $this->hasMeaningfulValue($entry['from'] ?? null)
                || $this->hasMeaningfulValue($entry['to'] ?? null)
                || $this->hasMeaningfulValue($entry['earned'] ?? null)
                || $this->hasMeaningfulValue($entry['year_graduated'] ?? null);

            if (!$hasAnyEntryValue) {
                continue;
            }

            $yearsFromEarned = $this->extractYearsFromEducationLevelText((string) ($entry['earned'] ?? ''));
            $yearsFromDates = $this->estimateYearsFromEducationDateRange(
                (string) ($entry['from'] ?? ''),
                (string) ($entry['to'] ?? '')
            );

            $isGraduated = $this->hasMeaningfulValue($entry['year_graduated'] ?? null)
                || str_contains(strtolower((string) ($entry['earned'] ?? '')), 'graduate');

            $entryMax = max($yearsFromEarned, $yearsFromDates);
            if ($isGraduated) {
                $entryMax = max($entryMax, 4);
            }

            $maxYears = max($maxYears, $entryMax);
        }

        return $maxYears;
    }

    private function estimateHighestCollegeUnitsCompleted($entries): int
    {
        if (!is_array($entries)) {
            return 0;
        }

        $maxUnits = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $yearsFromDates = $this->estimateYearsFromEducationDateRange(
                (string) ($entry['from'] ?? ''),
                (string) ($entry['to'] ?? '')
            );
            $unitsFromYears = $yearsFromDates > 0 ? $yearsFromDates * 36 : 0;
            $unitsFromEarned = $this->extractUnitsFromEducationLevelText((string) ($entry['earned'] ?? ''));
            $maxUnits = max($maxUnits, $unitsFromYears, $unitsFromEarned);
        }

        return $maxUnits;
    }

    private function estimateHighestCollegeSemestersCompleted($entries): int
    {
        if (!is_array($entries)) {
            return 0;
        }

        $maxSemesters = 0;
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $yearsFromDates = $this->estimateYearsFromEducationDateRange(
                (string) ($entry['from'] ?? ''),
                (string) ($entry['to'] ?? '')
            );
            $semestersFromYears = $yearsFromDates > 0 ? $yearsFromDates * 2 : 0;
            $semestersFromEarned = $this->extractSemestersFromEducationLevelText((string) ($entry['earned'] ?? ''));
            $maxSemesters = max($maxSemesters, $semestersFromYears, $semestersFromEarned);
        }

        return $maxSemesters;
    }

    private function extractYearsFromEducationLevelText(string $value): int
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/\b([1-9]|10)(?:st|nd|rd|th)?\s*year\b/i', $normalized, $matches) === 1) {
            return (int) $matches[1];
        }

        $wordToYear = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4,
            'fifth' => 5,
        ];

        foreach ($wordToYear as $word => $year) {
            if (str_contains($normalized, $word . ' year')) {
                return $year;
            }
        }

        $units = $this->extractUnitsFromEducationLevelText($normalized);
        if ($units > 0) {
            return max(0, (int) floor($units / 36));
        }

        $semesters = $this->extractSemestersFromEducationLevelText($normalized);
        if ($semesters > 0) {
            return max(0, (int) floor($semesters / 2));
        }

        return 0;
    }

    private function extractUnitsFromEducationLevelText(string $value): int
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*units?\b/i', $normalized, $unitMatches) === 1) {
            $units = (float) $unitMatches[1];
            return $units > 0 ? (int) floor($units) : 0;
        }

        return 0;
    }

    private function extractSemestersFromEducationLevelText(string $value): int
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/\b(\d+)\s*semesters?\b/i', $normalized, $semesterMatches) === 1) {
            $semesters = (int) $semesterMatches[1];
            return $semesters > 0 ? $semesters : 0;
        }

        return 0;
    }

    private function estimateYearsFromEducationDateRange(string $from, string $to): int
    {
        $fromDate = $this->parseEducationDateValue($from);
        if (!$fromDate) {
            return 0;
        }

        $toDate = $this->parseEducationDateValue($to) ?? Carbon::now();
        if ($toDate->lessThan($fromDate)) {
            return 0;
        }

        $months = $fromDate->diffInMonths($toDate) + 1;
        return (int) max(1, (int) ceil($months / 12));
    }

    private function parseEducationDateValue(string $value): ?Carbon
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $normalized);
            } catch (\Throwable $e) {
                // Try the next supported format.
            }
        }

        try {
            return Carbon::parse($normalized);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function valueContainsAnyKeyword($value, array $keywords): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->valueContainsAnyKeyword($item, $keywords)) {
                    return true;
                }
            }
            return false;
        }

        $text = strtolower(trim((string) $value));
        if ($text === '') {
            return false;
        }

        return $this->textContainsAny($text, $keywords);
    }

    private function flattenEducationValueToText($value): string
    {
        if (is_array($value)) {
            $chunks = [];
            foreach ($value as $item) {
                $chunk = $this->flattenEducationValueToText($item);
                if ($chunk !== '') {
                    $chunks[] = $chunk;
                }
            }
            return trim(implode(' ', $chunks));
        }

        return trim((string) $value);
    }

    private function textContainsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower((string) $keyword))) {
                return true;
            }
        }

        return false;
    }

    private function normalizeQualificationRequirement(?string $value): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $lower = strtolower($normalized);

        if (in_array($lower, ['na', 'n/a', 'none', 'not applicable', 'nil', '-'], true)) {
            return null;
        }

        if (str_contains($lower, 'none required') || str_contains($lower, 'not required')) {
            return null;
        }

        return $normalized;
    }

    private function parseQualificationRequirementMonths(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches) === 1) {
            $amount = (float) $matches[1];
            if (str_contains($lower, 'month')) {
                return (int) round($amount);
            }
            if (str_contains($lower, 'year') || str_contains($lower, 'yr')) {
                return (int) round($amount * 12);
            }
        }

        return null;
    }

    private function parseQualificationRequirementHours(string $value): ?int
    {
        $lower = strtolower($value);
        if (preg_match('/\bhours?\b|\bhrs?\b/', $lower) !== 1) {
            return null;
        }

        if (preg_match('/(\d+(?:\.\d+)?)/', $lower, $matches) === 1) {
            return (int) round((float) $matches[1]);
        }

        return null;
    }

    private function initialAssessmentSessionKey(string $vacancyId): string
    {
        return 'initial_assessment_answers.' . trim($vacancyId);
    }

    private function hasCompletedInitialAssessmentForVacancy(JobVacancy $vacancy, array $assessment): bool
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

    private function resolveHighestDegreeFromPds(int $userId): string
    {
        $education = EducationalBackground::where('user_id', $userId)->first();
        if (!$education) {
            return '';
        }

        // Check for graduate degrees first (highest priority) - field is 'basic' for degree/course
        $gradEntries = $education->grad ?? [];
        if (is_array($gradEntries) && !empty($gradEntries)) {
            foreach ($gradEntries as $entry) {
                // 'basic' field holds degree/course for college/grad entries
                $degree = trim((string) ($entry['basic'] ?? ''));
                if ($degree !== '' && strtoupper($degree) !== 'NOINPUT') {
                    return $degree;
                }
            }
        }

        // Check college entries - field is 'basic' for degree/course
        $collegeEntries = $education->college ?? [];
        if (is_array($collegeEntries) && !empty($collegeEntries)) {
            foreach ($collegeEntries as $entry) {
                // 'basic' field holds degree/course for college entries
                $degree = trim((string) ($entry['basic'] ?? ''));
                if ($degree !== '' && strtoupper($degree) !== 'NOINPUT') {
                    return $degree;
                }
            }
        }

        // Check vocational entries - field is 'basic' for course
        $vocationalEntries = $education->vocational ?? [];
        if (is_array($vocationalEntries) && !empty($vocationalEntries)) {
            foreach ($vocationalEntries as $entry) {
                // 'basic' field holds course for vocational entries
                $course = trim((string) ($entry['basic'] ?? ''));
                if ($course !== '' && strtoupper($course) !== 'NOINPUT') {
                    return $course;
                }
            }
        }

        // Check senior high
        $shsBasic = trim((string) ($education->shs_basic ?? ''));
        if ($shsBasic !== '' && strtoupper($shsBasic) !== 'NOINPUT') {
            return $shsBasic;
        }

        // Check junior high
        $jhsBasic = trim((string) ($education->jhs_basic ?? ''));
        if ($jhsBasic !== '' && strtoupper($jhsBasic) !== 'NOINPUT') {
            return $jhsBasic;
        }

        // Check elementary (lowest priority)
        $elemBasic = trim((string) ($education->elem_basic ?? ''));
        if ($elemBasic !== '' && strtoupper($elemBasic) !== 'NOINPUT') {
            return $elemBasic;
        }

        return '';
    }

    private function resolvePrimaryEligibilityFromPds(int $userId): string
    {
        $eligibilities = CivilServiceEligibility::where('user_id', $userId)->get();

        if ($eligibilities->isEmpty()) {
            return '';
        }

        foreach ($eligibilities as $eligibility) {
            // Try cs_eligibility_career first, then fall back to other fields
            $name = trim((string) ($eligibility->cs_eligibility_career ?? ''));
            if ($name !== '' && strtoupper($name) !== 'NOINPUT') {
                return $name;
            }
            // Some systems may store in eligibility_name column
            $name = trim((string) ($eligibility->eligibility_name ?? ''));
            if ($name !== '' && strtoupper($name) !== 'NOINPUT') {
                return $name;
            }
        }

        return '';
    }

    /**
     * Check if user has only high school education (no college/graduate/vocational).
     * Used to filter eligibility options.
     */
    private function hasOnlyHighSchoolEducation(int $userId): bool
    {
        $education = EducationalBackground::where('user_id', $userId)->first();
        if (!$education) {
            return false;
        }

        // Check if user has any college, graduate, or vocational education
        $collegeEntries = $education->college ?? [];
        if (is_array($collegeEntries) && !empty($collegeEntries)) {
            foreach ($collegeEntries as $entry) {
                $degree = trim((string) ($entry['basic'] ?? ''));
                if ($degree !== '' && strtoupper($degree) !== 'NOINPUT') {
                    return false; // Has college education
                }
            }
        }

        $gradEntries = $education->grad ?? [];
        if (is_array($gradEntries) && !empty($gradEntries)) {
            foreach ($gradEntries as $entry) {
                $degree = trim((string) ($entry['basic'] ?? ''));
                if ($degree !== '' && strtoupper($degree) !== 'NOINPUT') {
                    return false; // Has graduate education
                }
            }
        }

        $vocationalEntries = $education->vocational ?? [];
        if (is_array($vocationalEntries) && !empty($vocationalEntries)) {
            foreach ($vocationalEntries as $entry) {
                $course = trim((string) ($entry['basic'] ?? ''));
                if ($course !== '' && strtoupper($course) !== 'NOINPUT') {
                    return false; // Has vocational education
                }
            }
        }

        // Check if they have at least high school (JHS or SHS)
        $jhsBasic = trim((string) ($education->jhs_basic ?? ''));
        $shsBasic = trim((string) ($education->shs_basic ?? ''));
        $hasHighSchool = ($jhsBasic !== '' && strtoupper($jhsBasic) !== 'NOINPUT') ||
                         ($shsBasic !== '' && strtoupper($shsBasic) !== 'NOINPUT');

        return $hasHighSchool;
    }

    /**
     * Filter eligibility options based on user's education level.
     * If user has only high school, show ONLY First Level eligibilities.
     * Exception: CSC Professional (Second Level) is allowed for high school graduates.
     *
     * @param array $options Array of eligibility objects with name, legal_basis, level
     * @param int|null $userId Current user ID
     * @return array Filtered eligibility options
     */
    private function filterEligibilityOptionsByEducation(array $options, ?int $userId): array
    {
        if (!$userId || !$this->hasOnlyHighSchoolEducation($userId)) {
            return $options;
        }

        // High school only: show First Level + CSC Professional (Second Level exception)
        return collect($options)
            ->filter(function (array $option) {
                $level = strtolower(trim((string) ($option['level'] ?? '')));
                $name = strtolower(trim((string) ($option['name'] ?? '')));

                // Always allow First Level
                if (str_contains($level, 'first')) {
                    return true;
                }

                // Exception: Allow CSC Professional for high school graduates
                if (str_contains($level, 'second') && str_contains($name, 'csc professional')) {
                    return true;
                }

                // Exclude other Second Level eligibilities
                return false;
            })
            ->values()
            ->all();
    }

    private function isInitialAssessmentEducationAligned(JobVacancy $vacancy, string $degree): bool
    {
        $requirement = $this->normalizeQualificationRequirement($vacancy->qualification_education ?? null);
        if ($requirement === null || $this->isRequirementAny($requirement)) {
            return true;
        }

        $profile = $this->buildInitialAssessmentEducationProfile($degree);
        $alternativeEvaluation = $this->evaluateAlternativeEducationRequirements($profile, $requirement);
        if (is_array($alternativeEvaluation)) {
            return (bool) ($alternativeEvaluation['met'] ?? false);
        }

        $compiledRule = $this->resolveCompiledEducationRuleForVacancy($vacancy, $requirement);
        $met = is_array($compiledRule) ? $this->evaluateCompiledEducationRule($profile, $compiledRule) : null;
        $usedFallback = false;

        if (!is_bool($met)) {
            $met = $this->evaluateLegacyEducationRequirementByText($profile, $requirement);
            $usedFallback = true;
        }

        $isAdvisoryOnly = is_array($compiledRule)
            && (bool) ($compiledRule['advisory_only'] ?? false)
            && !$usedFallback;

        if ($isAdvisoryOnly) {
            return true;
        }

        return (bool) $met;
    }

    private function buildInitialAssessmentEducationProfile(string $degree): array
    {
        $rawInput = trim((string) preg_replace('/\s+/', ' ', $degree));
        $normalized = strtolower($rawInput);
        if (in_array($normalized, ['na', 'n/a', 'none', 'not applicable', 'nil', '-', 'noinput'], true)) {
            $rawInput = '';
            $normalized = '';
        }

        $hasInput = $rawInput !== '';
        $yearsFromText = $this->extractYearsFromEducationLevelText($normalized);
        $unitsFromText = $this->extractUnitsFromEducationLevelText($normalized);
        $semestersFromText = $this->extractSemestersFromEducationLevelText($normalized);
        $resolvedLevel = $this->resolveInitialAssessmentEducationLevel(
            $normalized,
            $yearsFromText,
            $unitsFromText,
            $semestersFromText
        );
        $resolvedRank = $this->educationLevelRank($resolvedLevel) ?? 0;
        $highSchoolRank = $this->educationLevelRank('high_school') ?? 1;
        $collegeUndergradRank = $this->educationLevelRank('college_undergrad_or_two_years') ?? 2;
        $bachelorRank = $this->educationLevelRank('bachelor') ?? 3;
        $masteralRank = $this->educationLevelRank('masteral') ?? 4;
        $doctorateRank = $this->educationLevelRank('doctorate') ?? 5;
        $isLawTrack = $resolvedLevel === 'law';
        $hasDoctorate = $resolvedRank >= $doctorateRank;
        $hasMasters = $resolvedRank >= $masteralRank;
        $hasBachelorOrHigher = $resolvedRank >= $bachelorRank || $isLawTrack;
        $hasCollegeEntryOrHigher = $resolvedRank >= $collegeUndergradRank || $hasBachelorOrHigher;
        $hasSeniorHighExplicit = $this->textContainsAny($normalized, ['senior high', 'grade 12', 'shs']);
        $hasHighSchoolExplicit = $this->textContainsAny($normalized, ['high school', 'secondary']);
        $collegeYearsCompleted = max(
            $yearsFromText,
            ($resolvedRank >= $bachelorRank || $isLawTrack) ? 4 : ($resolvedRank >= $collegeUndergradRank ? 1 : 0)
        );
        $estimatedCollegeUnits = max(
            $unitsFromText,
            $collegeYearsCompleted > 0 ? $collegeYearsCompleted * 36 : 0
        );
        $hasAtLeastTwoYearsCollege = $collegeYearsCompleted >= 2
            || $unitsFromText >= 72
            || $semestersFromText >= 4
            || $hasBachelorOrHigher;
        $degreeEntries = [];
        if ($hasInput && ($hasBachelorOrHigher || $hasMasters || $hasDoctorate)) {
            $entryLevel = $isLawTrack ? 'law' : ($resolvedLevel ?? 'bachelor');
            $normalizedDegreeText = $this->normalizeEducationFieldText($rawInput);
            if ($normalizedDegreeText !== '') {
                $degreeEntries[] = [
                    'level' => $entryLevel,
                    'text' => $normalizedDegreeText,
                ];
            }
        }

        return [
            'hasAnyEducation' => $hasInput,
            'hasElementaryOrHigher' => $hasInput,
            'hasHighSchoolOrHigher' => ($resolvedRank >= $highSchoolRank) || $hasHighSchoolExplicit || $hasSeniorHighExplicit,
            'hasSeniorHighOrHigher' => ($resolvedRank > $highSchoolRank) || $hasSeniorHighExplicit,
            'hasCollegeEntryOrHigher' => $hasCollegeEntryOrHigher,
            'hasCollegeDegreeOrHigher' => $hasBachelorOrHigher,
            'hasBachelorOrHigher' => $hasBachelorOrHigher,
            'hasAtLeastTwoYearsCollege' => $hasAtLeastTwoYearsCollege,
            'collegeYearsCompleted' => $collegeYearsCompleted,
            'estimatedCollegeUnits' => $estimatedCollegeUnits,
            'estimatedCollegeSemesters' => max(
                $semestersFromText,
                $collegeYearsCompleted > 0 ? $collegeYearsCompleted * 2 : 0
            ),
            'hasVocational' => str_contains($normalized, 'tesda') || str_contains($normalized, 'vocational') || str_contains($normalized, 'technical'),
            'hasGrad' => $hasMasters || $hasDoctorate,
            'hasMasters' => $hasMasters,
            'hasDoctorate' => $hasDoctorate,
            'hasLawDegree' => $isLawTrack,
            'educationKeywordHaystack' => $normalized,
            'degree_entries' => $degreeEntries,
        ];
    }

    private function resolveInitialAssessmentEducationLevel(
        string $normalizedDegree,
        int $yearsFromText,
        int $unitsFromText,
        int $semestersFromText
    ): ?string {
        if (trim($normalizedDegree) === '') {
            return null;
        }

        if ($this->valueContainsAnyKeyword($normalizedDegree, ['bachelor of laws', 'llb', 'll.b', 'juris doctor', 'doctor of jurisprudence', 'attorney'])) {
            return 'law';
        }

        if ($this->textContainsDoctorateLevelKeyword($normalizedDegree)) {
            return 'doctorate';
        }

        if ($this->textContainsAny($normalizedDegree, ['master', 'masteral', "master's", 'master of', 'mba', 'mpa', 'msc', 'm.s', 'm.a'])) {
            return 'masteral';
        }

        if ($this->textContainsBachelorLevelKeyword($normalizedDegree)) {
            return 'bachelor';
        }

        // Initial assessment accepts user-entered course names. If a user provides a
        // likely degree program title without explicit level markers (e.g. "Information Technology"),
        // treat it as bachelor-level for generic bachelor screening.
        if ($this->looksLikeCourseNameWithoutExplicitLevel($normalizedDegree)) {
            return 'bachelor';
        }

        $mentionsCollege = $this->textContainsAny($normalizedDegree, ['college', 'undergraduate', 'undergrad']);
        if ($mentionsCollege || $yearsFromText > 0 || $unitsFromText > 0 || $semestersFromText > 0) {
            return 'college_undergrad_or_two_years';
        }

        if ($this->textContainsAny($normalizedDegree, ['senior high', 'grade 12', 'shs', 'high school', 'secondary'])) {
            return 'high_school';
        }

        return null;
    }

    private function textContainsBachelorLevelKeyword(string $value): bool
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return false;
        }

        if ($this->textContainsAny($normalized, ['bachelor', "bachelor's", 'baccalaureate', 'college graduate', 'college degree'])) {
            return true;
        }

        return preg_match('/\b(b\.?\s?s\.?|b\.?\s?a\.?|a\.?\s?b\.?|bsc|bs[a-z]{2,6}|ba[a-z]{2,6}|beed|bsed)\b/i', $normalized) === 1;
    }

    private function looksLikeCourseNameWithoutExplicitLevel(string $value): bool
    {
        $normalized = $this->normalizeEducationFieldText($value);
        if ($normalized === '') {
            return false;
        }

        // Do not infer bachelor-level when lower-level or non-degree cues are explicit.
        if ($this->textContainsAny($normalized, [
            'elementary',
            'high school',
            'senior high',
            'grade 12',
            'grade 11',
            'grade 10',
            'undergraduate',
            'undergrad',
            'college undergraduate',
            'first year',
            'second year',
            'third year',
            'fourth year',
            '1st year',
            '2nd year',
            '3rd year',
            '4th year',
            'units',
            'semester',
            'vocational',
            'tesda',
            'certificate',
            'diploma',
            'no education',
            'none',
            'not applicable',
        ])) {
            return false;
        }

        // If it contains only letters/spaces and resembles a program title,
        // allow bachelor inference for initial screening.
        $tokenCount = preg_match_all('/[a-z]+/i', $normalized, $matches);
        if ($tokenCount === false || $tokenCount === 0) {
            return false;
        }

        if ($tokenCount < 2 && strlen($normalized) < 8) {
            return false;
        }

        return true;
    }

    private function isInitialAssessmentEligibilityAligned(JobVacancy $vacancy, string $eligibility): bool
    {
        $rawRequirement = $this->normalizeQualificationRequirement($vacancy->qualification_eligibility ?? null);
        if ($rawRequirement === null || $this->isRequirementAny($rawRequirement)) {
            return true;
        }

        $requiredItems = $this->extractVacancyEligibilityItems((string) $rawRequirement);
        if (empty($requiredItems)) {
            return true;
        }

        $inputLabel = $this->canonicalEligibilityLabelFromName($eligibility);
        if ($inputLabel === '') {
            return false;
        }

        $inputLevelRank = $this->resolveEligibilityLevelRank('', $inputLabel);

        foreach ($requiredItems as $requiredItem) {
            $requiredName = trim((string) ($requiredItem['name'] ?? ''));
            if ($requiredName === '') {
                continue;
            }

            if ($this->eligibilityNamesMatch($requiredName, $inputLabel)) {
                return true;
            }

            $requiredLevelRank = $this->resolveEligibilityLevelRank(
                (string) ($requiredItem['level'] ?? ''),
                $requiredName
            );
            if ($this->isGenericEligibilityLevelRequirement($requiredName, $requiredLevelRank)) {
                if ($this->eligibilityLevelMeetsGenericRequirement($requiredLevelRank, $inputLevelRank)) {
                    return true;
                }
                continue;
            }

            $requiredGroup = $this->canonicalEligibilityGroup($this->normalizeEligibilityKey($requiredName));
            $inputGroup = $this->canonicalEligibilityGroup($this->normalizeEligibilityKey($inputLabel));

            // Keep hierarchy support, but only for recognized compatible groups.
            if (
                $this->eligibilityLevelSatisfiesRequirement($requiredLevelRank, $inputLevelRank)
                && $requiredGroup !== ''
                && $inputGroup !== ''
                && $this->eligibilityGroupSatisfiesRequirement($requiredGroup, $inputGroup)
            ) {
                return true;
            }
        }

        return false;
    }

    private function isRequirementAny(string $requirement): bool
    {
        $normalized = strtolower(trim($requirement));
        if ($normalized === '') {
            return true;
        }

        if (in_array($normalized, ['any', 'all', 'all courses', 'any course', 'any degree', 'not specified'], true)) {
            return true;
        }

        return preg_match('/\bany\b/i', $normalized) === 1;
    }

    private function buildInitialAssessmentNotQualifiedMessage(bool $educationAligned, bool $eligibilityAligned): string
    {
        if (!$educationAligned && !$eligibilityAligned) {
            return 'You are not qualified for this position based on both Education and Eligibility requirements.';
        }

        if (!$educationAligned) {
            return 'You are not qualified for this position based on the Education requirement.';
        }

        return 'You are not qualified for this position based on the Eligibility requirement.';
    }

    private function evaluateApplicantEligibilityForVacancy(int $userId, JobVacancy $vacancy): array
    {
        $requiredItems = $this->extractVacancyEligibilityItems((string) ($vacancy->qualification_eligibility ?? ''));
        $requiredEligibilities = $this->extractVacancyEligibilityNames((string) ($vacancy->qualification_eligibility ?? ''));

        // Vacancy has no explicit eligibility requirement.
        if (empty($requiredEligibilities)) {
            return [
                'isEligible' => true,
                'message' => null,
                'requiredEligibilities' => [],
                'applicantEligibilities' => [],
            ];
        }

        $applicantEligibilities = $this->extractApplicantEligibilityNames($userId);

        if (empty($applicantEligibilities)) {
            return [
                'isEligible' => false,
                'message' => 'This vacancy requires civil service eligibility (' . implode(', ', $requiredEligibilities) . '). Please update your PDS Civil Service Eligibility (C2) before applying.',
                'requiredEligibilities' => $requiredEligibilities,
                'applicantEligibilities' => [],
            ];
        }

        $requiredByKey = [];
        foreach ($requiredEligibilities as $name) {
            $key = $this->normalizeEligibilityKey($name);
            if ($key !== '' && !array_key_exists($key, $requiredByKey)) {
                $requiredByKey[$key] = $name;
            }
        }
        if (empty($requiredByKey)) {
            return [
                'isEligible' => true,
                'message' => null,
                'requiredEligibilities' => [],
                'applicantEligibilities' => [],
            ];
        }

        $requiredProfilesByKey = [];
        foreach ($requiredItems as $item) {
            $requiredName = trim((string) ($item['name'] ?? ''));
            $requiredKey = $this->normalizeEligibilityKey($requiredName);
            if ($requiredKey === '' || !array_key_exists($requiredKey, $requiredByKey)) {
                continue;
            }

            $requiredLevelRank = $this->resolveEligibilityLevelRank(
                (string) ($item['level'] ?? ''),
                $requiredName
            );
            if (!array_key_exists($requiredKey, $requiredProfilesByKey)) {
                $requiredProfilesByKey[$requiredKey] = [
                    'name' => $requiredByKey[$requiredKey],
                    'levelRank' => $requiredLevelRank,
                ];
                continue;
            }

            $existingRank = $requiredProfilesByKey[$requiredKey]['levelRank'] ?? null;
            if ($existingRank === null && $requiredLevelRank !== null) {
                $requiredProfilesByKey[$requiredKey]['levelRank'] = $requiredLevelRank;
            }
        }

        foreach ($requiredByKey as $requiredKey => $requiredName) {
            if (!array_key_exists($requiredKey, $requiredProfilesByKey)) {
                $requiredProfilesByKey[$requiredKey] = [
                    'name' => $requiredName,
                    'levelRank' => $this->resolveEligibilityLevelRank('', (string) $requiredName),
                ];
            }
        }

        $applicantByKey = [];
        foreach ($applicantEligibilities as $name) {
            $key = $this->normalizeEligibilityKey($name);
            if ($key !== '' && !array_key_exists($key, $applicantByKey)) {
                $applicantByKey[$key] = $name;
            }
        }
        if (empty($applicantByKey)) {
            return [
                'isEligible' => false,
                'message' => 'This vacancy requires civil service eligibility (' . implode(', ', array_values($requiredByKey)) . '). Please update your PDS Civil Service Eligibility (C2) before applying.',
                'requiredEligibilities' => array_values($requiredByKey),
                'applicantEligibilities' => [],
            ];
        }

        $applicantProfilesByKey = [];
        foreach ($applicantByKey as $applicantKey => $applicantName) {
            $applicantProfilesByKey[$applicantKey] = [
                'name' => $applicantName,
                'levelRank' => $this->resolveEligibilityLevelRank('', (string) $applicantName),
            ];
        }

        $matchedKeys = array_intersect(array_keys($requiredByKey), array_keys($applicantByKey));
        if (empty($matchedKeys)) {
            foreach ($requiredProfilesByKey as $requiredKey => $requiredProfile) {
                $requiredName = (string) ($requiredProfile['name'] ?? '');
                $requiredLevelRank = $requiredProfile['levelRank'] ?? null;

                if ($this->isGenericEligibilityLevelRequirement($requiredName, $requiredLevelRank)) {
                    foreach ($applicantProfilesByKey as $applicantProfile) {
                        $applicantLevelRank = $applicantProfile['levelRank'] ?? null;
                        if ($this->eligibilityLevelMeetsGenericRequirement($requiredLevelRank, $applicantLevelRank)) {
                            $matchedKeys[] = $requiredKey;
                            break;
                        }
                    }
                    continue;
                }

                foreach ($applicantProfilesByKey as $applicantProfile) {
                    $applicantName = (string) ($applicantProfile['name'] ?? '');
                    $applicantLevelRank = $applicantProfile['levelRank'] ?? null;
                    $requiredGroup = $this->canonicalEligibilityGroup($this->normalizeEligibilityKey($requiredName));
                    $applicantGroup = $this->canonicalEligibilityGroup($this->normalizeEligibilityKey($applicantName));

                    if ($this->eligibilityNamesMatch($requiredName, $applicantName)) {
                        $matchedKeys[] = $requiredKey;
                        break;
                    }

                    // Keep hierarchy support, but only when both eligibility groups are recognized and compatible.
                    if (
                        $this->eligibilityLevelSatisfiesRequirement($requiredLevelRank, $applicantLevelRank)
                        && $requiredGroup !== ''
                        && $applicantGroup !== ''
                        && $this->eligibilityGroupSatisfiesRequirement($requiredGroup, $applicantGroup)
                    ) {
                        $matchedKeys[] = $requiredKey;
                        break;
                    }
                }
            }
            $matchedKeys = array_values(array_unique($matchedKeys));
        }

        if (!empty($matchedKeys)) {
            return [
                'isEligible' => true,
                'message' => null,
                'requiredEligibilities' => array_values($requiredByKey),
                'applicantEligibilities' => array_values($applicantByKey),
            ];
        }

        return [
            'isEligible' => false,
            'message' => 'Your declared civil service eligibility does not match this vacancy requirement. Required: '
                . implode(', ', array_values($requiredByKey))
                . '. Your current entries: '
                . implode(', ', array_values($applicantByKey))
                . '.',
            'requiredEligibilities' => array_values($requiredByKey),
            'applicantEligibilities' => array_values($applicantByKey),
        ];
    }

    private function extractApplicantEligibilityNames(int $userId): array
    {
        $records = CivilServiceEligibility::query()
            ->where('user_id', $userId)
            ->pluck('cs_eligibility_career')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->values()
            ->all();

        return $this->uniqueEligibilityNames($records);
    }

    private function extractVacancyEligibilityNames(string $rawEligibility): array
    {
        $items = $this->extractVacancyEligibilityItems($rawEligibility);
        if (empty($items)) {
            return [];
        }

        $names = array_map(static function (array $item) {
            return trim((string) ($item['name'] ?? ''));
        }, $items);

        return $this->uniqueEligibilityNames($names);
    }

    private function extractVacancyEligibilityItems(string $rawEligibility): array
    {
        $normalizedRequirement = $this->normalizeQualificationRequirement($rawEligibility);
        if ($normalizedRequirement === null) {
            return [];
        }

        $rawEligibility = $normalizedRequirement;
        $items = [];
        $parsed = json_decode($rawEligibility, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $source = array_key_exists('name', $parsed) ? [$parsed] : $parsed;
                foreach ($source as $entry) {
                    if (is_string($entry)) {
                        $name = $this->canonicalEligibilityLabelFromName((string) $entry);
                        if ($name === '') {
                            continue;
                        }
                        $items[] = [
                            'name' => $name,
                        'legal_basis' => '',
                        'level' => '',
                    ];
                    continue;
                }

                if (!is_array($entry)) {
                    continue;
                }

                $name = $this->canonicalEligibilityLabelFromName((string) ($entry['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $items[] = [
                    'name' => $name,
                    'legal_basis' => trim((string) ($entry['legalBasis'] ?? $entry['legal_basis'] ?? '')),
                    'level' => trim((string) ($entry['level'] ?? '')),
                ];
            }

            $deduped = [];
            $seen = [];
            foreach ($items as $item) {
                $key = $this->normalizeEligibilityKey((string) ($item['name'] ?? ''));
                if ($key === '' || array_key_exists($key, $seen)) {
                    continue;
                }
                $seen[$key] = true;
                $deduped[] = $item;
            }

            if (!empty($deduped)) {
                return $deduped;
            }
        }

        $legacyTokens = preg_split('/[\r\n;,]+/', $rawEligibility) ?: [];
        $legacyTokens = array_map(static function ($token) {
            return trim((string) $token);
        }, $legacyTokens);
        $legacyTokens = $this->uniqueEligibilityNames($legacyTokens);

        return array_map(static function ($name) {
            return [
                'name' => $name,
                'legal_basis' => '',
                'level' => '',
            ];
        }, $legacyTokens);
    }

    private function formatVacancyEligibilityDisplay(array $items): string
    {
        $lines = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $parts = [];
            $legalBasis = trim((string) ($item['legal_basis'] ?? ''));
            $level = trim((string) ($item['level'] ?? ''));

            if ($legalBasis !== '') {
                $parts[] = 'Legal Basis: ' . $legalBasis;
            }
            if ($level !== '') {
                $parts[] = 'Level: ' . $level;
            }

            $lines[] = empty($parts)
                ? $name
                : $name . ' (' . implode(' | ', $parts) . ')';
        }

        return empty($lines) ? 'Not specified' : implode("\n", $lines);
    }

    private function uniqueEligibilityNames(array $names): array
    {
        $deduped = [];
        $seen = [];

        foreach ($names as $name) {
            $label = $this->canonicalEligibilityLabelFromName((string) $name);
            $key = $this->normalizeEligibilityKey($label);
            if ($label === '' || $key === '' || array_key_exists($key, $seen)) {
                continue;
            }

            $seen[$key] = true;
            $deduped[] = $label;
        }

        return $deduped;
    }

    private function canonicalEligibilityLabelFromName(string $value): string
    {
        $label = trim($value);
        if ($label === '') {
            return '';
        }

        $group = $this->canonicalEligibilityGroup($this->normalizeEligibilityKey($label));
        if ($group !== '' && array_key_exists($group, self::ELIGIBILITY_CANONICAL_LABELS)) {
            return self::ELIGIBILITY_CANONICAL_LABELS[$group];
        }

        return $label;
    }

    private function normalizeEligibilityKey(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return '';
        }

        return preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';
    }

    private function eligibilityNamesMatch(string $requiredName, string $applicantName): bool
    {
        $requiredKey = $this->normalizeEligibilityKey($requiredName);
        $applicantKey = $this->normalizeEligibilityKey($applicantName);

        if ($requiredKey === '' || $applicantKey === '') {
            return false;
        }

        if ($requiredKey === $applicantKey) {
            return true;
        }

        $requiredGroup = $this->canonicalEligibilityGroup($requiredKey);
        $applicantGroup = $this->canonicalEligibilityGroup($applicantKey);
        if ($requiredGroup !== '' && $requiredGroup === $applicantGroup) {
            return true;
        }

        // Allow minor wording differences for manually entered "Others" values.
        $minLen = min(strlen($requiredKey), strlen($applicantKey));
        if ($minLen >= 8 && (str_contains($requiredKey, $applicantKey) || str_contains($applicantKey, $requiredKey))) {
            return true;
        }

        return false;
    }

    private function eligibilityGroupSatisfiesRequirement(string $requiredGroup, string $applicantGroup): bool
    {
        if ($requiredGroup === $applicantGroup) {
            return true;
        }

        // Keep RA 1080 / Bar-Board requirements strict by policy.
        if ($requiredGroup === 'bar_board') {
            return false;
        }

        $requiredLevelRank = $this->eligibilityGroupLevelRank($requiredGroup);
        $applicantLevelRank = $this->eligibilityGroupLevelRank($applicantGroup);
        if ($this->eligibilityLevelSatisfiesRequirement($requiredLevelRank, $applicantLevelRank)) {
            return true;
        }

        return false;
    }

    private function eligibilityLevelSatisfiesRequirement(?int $requiredLevelRank, ?int $applicantLevelRank): bool
    {
        if ($requiredLevelRank === null || $applicantLevelRank === null) {
            return false;
        }

        // Same-level different eligibility types should not pass by level alone.
        return $applicantLevelRank > $requiredLevelRank;
    }

    private function eligibilityLevelMeetsGenericRequirement(?int $requiredLevelRank, ?int $applicantLevelRank): bool
    {
        if ($requiredLevelRank === null || $applicantLevelRank === null) {
            return false;
        }

        // Generic requirements like "First Level" accept same or higher level.
        return $applicantLevelRank >= $requiredLevelRank;
    }

    private function isGenericEligibilityLevelRequirement(string $requiredName, ?int $requiredLevelRank): bool
    {
        if ($requiredLevelRank === null) {
            return false;
        }

        $name = trim($requiredName);
        if ($name === '') {
            return false;
        }

        $normalizedKey = $this->normalizeEligibilityKey($name);
        $group = $this->canonicalEligibilityGroup($normalizedKey);

        // If no canonical group is recognized but a level rank exists,
        // treat the requirement as level-based (same-or-higher can satisfy).
        return $group === '';
    }

    private function resolveEligibilityLevelRank(?string $declaredLevel, string $eligibilityName): ?int
    {
        $declaredRank = $this->parseEligibilityLevelRank($declaredLevel);
        if ($declaredRank !== null) {
            return $declaredRank;
        }

        $eligibilityName = trim($eligibilityName);
        if ($eligibilityName === '') {
            return null;
        }

        $fromName = $this->parseEligibilityLevelRank($eligibilityName);
        if ($fromName !== null) {
            return $fromName;
        }

        $normalizedName = $this->normalizeEligibilityKey($eligibilityName);
        if ($normalizedName !== '') {
            $presetMap = $this->eligibilityPresetLevelMap();
            if (array_key_exists($normalizedName, $presetMap)) {
                return $presetMap[$normalizedName];
            }
        }

        $group = $this->canonicalEligibilityGroup($normalizedName);
        return $this->eligibilityGroupLevelRank($group);
    }

    private function eligibilityGroupLevelRank(string $group): ?int
    {
        if ($group === '') {
            return null;
        }

        return match ($group) {
            'csc_subprofessional',
            'skills_category_ii',
            'barangay_official',
            'sanggunian_member',
            'barangay_health_worker',
            'barangay_nutrition_scholar' => 1,

            'bar_board',
            'csc_professional',
            'honor_graduate',
            'foreign_honor_graduate',
            'edp_specialist',
            'scientific_technological_specialist' => 2,

            default => null,
        };
    }

    private function parseEligibilityLevelRank(?string $value): ?int
    {
        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/\b(\d+)\s*(?:st|nd|rd|th)?\s*level\b/', $normalized, $matches) === 1) {
            $rank = (int) ($matches[1] ?? 0);
            return $rank > 0 ? $rank : null;
        }

        if (preg_match('/\blevel\s*(\d+)\b/', $normalized, $matches) === 1) {
            $rank = (int) ($matches[1] ?? 0);
            return $rank > 0 ? $rank : null;
        }

        if (preg_match('/\blevel\s*(x|ix|iv|v?i{1,3}|v)\b/', $normalized, $matches) === 1) {
            $roman = strtolower((string) ($matches[1] ?? ''));
            $romanMap = [
                'i' => 1,
                'ii' => 2,
                'iii' => 3,
                'iv' => 4,
                'v' => 5,
                'vi' => 6,
                'vii' => 7,
                'viii' => 8,
                'ix' => 9,
                'x' => 10,
            ];
            if (array_key_exists($roman, $romanMap)) {
                return $romanMap[$roman];
            }
        }

        $wordMap = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4,
            'fifth' => 5,
            'sixth' => 6,
            'seventh' => 7,
            'eighth' => 8,
            'ninth' => 9,
            'tenth' => 10,
        ];
        foreach ($wordMap as $word => $rank) {
            if (str_contains($normalized, $word)) {
                return $rank;
            }
        }

        if (preg_match('/\b(\d+)\s*(?:st|nd|rd|th)\b/', $normalized, $matches) === 1) {
            $rank = (int) ($matches[1] ?? 0);
            return $rank > 0 ? $rank : null;
        }

        return null;
    }

    private function eligibilityPresetLevelMap(): array
    {
        static $map = null;
        if (is_array($map)) {
            return $map;
        }

        $map = [];
        if (!Schema::hasTable('eligibility_presets')) {
            return $map;
        }

        try {
            $rows = EligibilityPreset::query()->get(['name', 'level']);
            foreach ($rows as $row) {
                $name = trim((string) ($row->name ?? ''));
                if ($name === '') {
                    continue;
                }

                $rank = $this->parseEligibilityLevelRank((string) ($row->level ?? ''));
                if ($rank === null) {
                    continue;
                }

                $rawNameKey = $this->normalizeEligibilityKey($name);
                if ($rawNameKey !== '') {
                    $map[$rawNameKey] = $rank;
                }

                $canonicalName = $this->canonicalEligibilityLabelFromName($name);
                $canonicalNameKey = $this->normalizeEligibilityKey($canonicalName);
                if ($canonicalNameKey !== '') {
                    $map[$canonicalNameKey] = $rank;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Unable to build eligibility preset level map.', [
                'error' => $e->getMessage(),
            ]);
            $map = [];
        }

        return $map;
    }

    private function canonicalEligibilityGroup(string $normalizedKey): string
    {
        if ($normalizedKey === '') {
            return '';
        }

        $contains = static function (string $needle) use ($normalizedKey): bool {
            return str_contains($normalizedKey, $needle);
        };

        if ($contains('ra1080') || ($contains('bar') && $contains('board'))) {
            return 'bar_board';
        }
        if ($contains('subprofessional') || $contains('subprof')) {
            return 'csc_subprofessional';
        }
        $isCsFamily = str_starts_with($normalizedKey, 'csc')
            || str_starts_with($normalizedKey, 'cse')
            || str_starts_with($normalizedKey, 'cs')
            || $contains('civilservice')
            || $contains('careerservice');
        if ($isCsFamily && ($contains('professional') || $contains('prof'))) {
            return 'csc_professional';
        }
        if ($contains('foreign') && $contains('honor') && $contains('graduate')) {
            return 'foreign_honor_graduate';
        }
        if ($contains('honor') && $contains('graduate')) {
            return 'honor_graduate';
        }
        if ($contains('barangay') && $contains('health') && $contains('worker')) {
            return 'barangay_health_worker';
        }
        if ($contains('barangay') && $contains('nutrition') && $contains('scholar')) {
            return 'barangay_nutrition_scholar';
        }
        if ($contains('barangay') && $contains('official')) {
            return 'barangay_official';
        }
        if ($contains('sanggunian') && $contains('member')) {
            return 'sanggunian_member';
        }
        if ($contains('skills') && ($contains('categoryii') || $contains('category2'))) {
            return 'skills_category_ii';
        }
        if ($contains('electronic') && $contains('dataprocessing') && $contains('specialist')) {
            return 'edp_specialist';
        }
        if ($contains('scientific') && $contains('technological') && $contains('specialist')) {
            return 'scientific_technological_specialist';
        }

        return '';
    }

    public function calculatePdsProgress($userId)
    {
        $userId = (int) $userId;

        // Determine required docs from tracks the user has applied for.
        $applicationTracks = Applications::where('user_id', $userId)
            ->with('vacancy:vacancy_id,vacancy_type')
            ->get()
            ->map(fn($app) => $this->normalizeTrack($app->vacancy?->vacancy_type))
            ->filter()
            ->unique()
            ->values();

        $allRequiredDocs = collect();
        $userApplications = Applications::where('user_id', $userId)
            ->with('vacancy')
            ->get();

        if ($userApplications->isEmpty()) {
            // Fallback to default Plantilla track if no applications exist
            $allRequiredDocs = collect($this->getRequiredDocsByTrack()['Plantilla'] ?? []);
        } else {
            foreach ($userApplications as $app) {
                if ($app->vacancy) {
                    $docs = $this->getRequiredDocumentIdsForVacancyType($app->vacancy->vacancy_type, $app->vacancy);
                    $allRequiredDocs = $allRequiredDocs->merge($docs);
                }
            }
        }

        $requiredDocumentIds = $allRequiredDocs->unique()->values();

        $totalRequiredDocs = $requiredDocumentIds->count();
        if ($totalRequiredDocs === 0) {
            return 0;
        }

        $uploadedDocuments = UploadedDocument::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->unique('document_type')
            ->keyBy('document_type');

        $hasApplicationLetterInApplications = Applications::where('user_id', $userId)
            ->whereNotNull('file_storage_path')
            ->exists();

        $completedRequiredDocs = $requiredDocumentIds->filter(function (string $docType) use ($uploadedDocuments, $hasApplicationLetterInApplications) {
            if ($docType === 'application_letter') {
                if ($hasApplicationLetterInApplications) {
                    return true;
                }

                $applicationLetterDoc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
                return $applicationLetterDoc && !empty($applicationLetterDoc->storage_path) && $applicationLetterDoc->storage_path !== 'NOINPUT';
            }

            $doc = $this->resolveUploadedDocument($uploadedDocuments, $docType);
            return $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
        })->count();

        return (int) round(($completedRequiredDocs / $totalRequiredDocs) * 100);
    }

    private function hasJobVacancyCscFormPathColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'csc_form_path');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.csc_form_path column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    private function hasJobVacancyLastModifiedAtColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            $hasColumn = Schema::hasColumn('job_vacancies', 'last_modified_at');
        } catch (\Throwable $e) {
            $hasColumn = false;
            Log::warning('Unable to detect job_vacancies.last_modified_at column.', [
                'error' => $e->getMessage(),
            ]);
        }

        return $hasColumn;
    }

    public function sortMyApplications(Request $request)
    {
        $applications = $this->buildMyApplicationsQuery($request)->get();
        $hasActiveFilters = $this->requestHasMyApplicationFilters($request);

        return view('partials.application_list_container', [
            'applications' => $applications,
            'hasActiveFilters' => $hasActiveFilters,
        ])->render();
    }

    private function buildMyApplicationsQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $place = trim((string) $request->query('place', ''));
        $vacancyType = trim((string) $request->query('vacancy_type', ''));
        $status = trim((string) $request->query('status', ''));
        $sortOrder = strtolower(trim((string) $request->query('sort_order', 'latest')));

        $query = Applications::query()
            ->where('user_id', Auth::id())
            ->with('vacancy');

        if ($search !== '') {
            $query->where(function ($applicationQuery) use ($search) {
                $applicationQuery
                    ->where('vacancy_id', 'like', '%' . $search . '%')
                    ->orWhereHas('vacancy', function ($vacancyQuery) use ($search) {
                        $vacancyQuery->where('position_title', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($place !== '') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($place) {
                $vacancyQuery->where('place_of_assignment', $place);
            });
        }

        if ($vacancyType !== '') {
            $query->whereHas('vacancy', function ($vacancyQuery) use ($vacancyType) {
                $vacancyQuery->whereRaw("LOWER(TRIM(COALESCE(vacancy_type, ''))) = ?", [strtolower($vacancyType)]);
            });
        }

        if ($status !== '') {
            $statusFilter = strtolower($status);
            if ($statusFilter === 'completed') {
                $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) IN (?, ?, ?, ?)", ['submitted', 'in-progress', 'completed', 'complete']);
            } elseif ($statusFilter === 'needs revision') {
                $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) IN (?, ?, ?)", ['compliance', 'needs revision', 'disapproved with deficiency']);
            } else {
                $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", [$statusFilter]);
            }
        }

        $query->orderByRaw("CASE
            WHEN LOWER(TRIM(COALESCE(status, ''))) = 'pending' THEN 0
            WHEN LOWER(TRIM(COALESCE(status, ''))) IN ('compliance', 'needs revision', 'disapproved with deficiency') THEN 1
            WHEN LOWER(TRIM(COALESCE(status, ''))) IN ('submitted', 'in-progress', 'completed', 'complete') THEN 2
            WHEN LOWER(TRIM(COALESCE(status, ''))) = 'not qualified' THEN 3
            WHEN LOWER(TRIM(COALESCE(status, ''))) IN ('cancelled', 'closed') THEN 4
            ELSE 5
        END");
        $query->orderBy('created_at', $sortOrder === 'oldest' ? 'asc' : 'desc');

        return $query;
    }

    private function mapMyApplicationsStatusLabel(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        return match ($normalized) {
            'submitted', 'in-progress', 'completed', 'complete' => 'Completed',
            'compliance', 'needs revision', 'disapproved with deficiency' => 'Needs Revision',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            'closed' => 'Closed',
            default => trim((string) $status) !== '' ? (string) $status : 'Pending',
        };
    }

    private function getMyApplicationFilterOptions(): array
    {
        $userId = Auth::id();

        $statuses = Applications::query()
            ->where('user_id', $userId)
            ->whereNotNull('status')
            ->pluck('status')
            ->map(fn($status) => $this->mapMyApplicationsStatusLabel($status))
            ->filter()
            ->unique(fn($status) => strtolower($status))
            ->sortBy(function ($status) {
                $key = strtolower(trim((string) $status));
                return match ($key) {
                    'pending' => 1,
                    'needs revision' => 2,
                    'completed' => 3,
                    'not qualified' => 4,
                    'closed' => 5,
                    'cancelled' => 6,
                    default => 99,
                };
            })
            ->values();

        return [
            'vacancyTypes' => collect(['COS', 'Plantilla']),
            'statuses' => $statuses,
        ];
    }

    private function requestHasMyApplicationFilters(Request $request): bool
    {
        foreach (['search', 'place', 'vacancy_type', 'status'] as $key) {
            if (trim((string) $request->query($key, '')) !== '') {
                return true;
            }
        }

        return false;
    }
}

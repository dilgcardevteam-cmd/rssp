<?php

namespace App\Http\Controllers;

use App\Models\CoursePreset;
use App\Models\ProgramSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class CoursePresetController extends Controller
{
    private const PROGRAM_LEVELS = ['COLLEGE', 'MASTERAL', 'DOCTORATE'];

    private const PROGRAM_LEVEL_LABELS = [
        'COLLEGE' => 'College',
        'MASTERAL' => 'Masteral',
        'DOCTORATE' => 'Doctorate',
    ];

    private const DEFAULT_PROGRAMS = [
        ['code' => 'LLB_JD', 'name' => 'Bachelor of Laws / Juris Doctor', 'level' => 'COLLEGE'],
        ['code' => 'BS_ACCOUNTANCY', 'name' => 'BS Accountancy', 'level' => 'COLLEGE'],
        ['code' => 'BS_INFORMATION_TECHNOLOGY', 'name' => 'BS Information Technology', 'level' => 'COLLEGE'],
        ['code' => 'BS_COMPUTER_SCIENCE', 'name' => 'BS Computer Science', 'level' => 'COLLEGE'],
        ['code' => 'BS_INFORMATION_SYSTEMS', 'name' => 'BS Information Systems', 'level' => 'COLLEGE'],
        ['code' => 'B_PUBLIC_ADMIN', 'name' => 'Bachelor of Public Administration', 'level' => 'COLLEGE'],
        ['code' => 'BS_PSYCHOLOGY', 'name' => 'BS Psychology', 'level' => 'COLLEGE'],
        ['code' => 'MASTER_PUBLIC_ADMIN', 'name' => 'Master of Public Administration', 'level' => 'MASTERAL'],
        ['code' => 'MASTER_IT', 'name' => 'Master in Information Technology', 'level' => 'MASTERAL'],
        ['code' => 'MBA', 'name' => 'Master in Business Administration', 'level' => 'MASTERAL'],
        ['code' => 'MASTER_EDUCATION', 'name' => 'Master of Arts in Education', 'level' => 'MASTERAL'],
        ['code' => 'MASTER_PSYCHOLOGY', 'name' => 'Master of Arts in Psychology', 'level' => 'MASTERAL'],
        ['code' => 'PHD_PUBLIC_ADMIN', 'name' => 'Doctor of Philosophy in Public Administration', 'level' => 'DOCTORATE'],
        ['code' => 'PHD_IT', 'name' => 'Doctor of Philosophy in Information Technology', 'level' => 'DOCTORATE'],
        ['code' => 'EDD', 'name' => 'Doctor of Education', 'level' => 'DOCTORATE'],
        ['code' => 'PHD_PSYCHOLOGY', 'name' => 'Doctor of Philosophy in Psychology', 'level' => 'DOCTORATE'],
        ['code' => 'SJD', 'name' => 'Doctor of Juridical Science', 'level' => 'DOCTORATE'],
    ];

    private function seedProgramsFromCsv(): Collection
    {
        static $cached = null;

        if ($cached instanceof Collection) {
            return $cached;
        }

        $path = base_path('philippines_academic_programs_seed_list.csv');
        if (!is_file($path) || !is_readable($path)) {
            $cached = collect();
            return $cached;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $cached = collect();
            return $cached;
        }

        $header = fgetcsv($handle);
        if (!is_array($header)) {
            fclose($handle);
            $cached = collect();
            return $cached;
        }

        $normalizedHeader = array_map(
            static fn($column) => strtolower(trim((string) $column)),
            $header
        );

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (!is_array($data) || count($data) === 0) {
                continue;
            }

            $row = [];
            foreach ($normalizedHeader as $index => $column) {
                $row[$column] = isset($data[$index]) ? trim((string) $data[$index]) : '';
            }

            $name = trim((string) ($row['program_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $rows[] = [
                'id' => 0,
                'code' => (string) ($row['program_code'] ?? ''),
                'name' => $name,
                'level' => $this->normalizeLevel((string) ($row['program_level'] ?? 'COLLEGE')),
            ];
        }

        fclose($handle);

        $cached = $this->sortedCourses(collect($rows));

        return $cached;
    }

    private function mergedSeedPrograms(?string $levelFilter = null): Collection
    {
        $csvPrograms = $this->seedProgramsFromCsv();
        $fallbackPrograms = $this->sortedCourses(collect(self::DEFAULT_PROGRAMS))
            ->map(function (array $item) {
                return [
                    'id' => 0,
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'level' => $this->normalizeLevel((string) ($item['level'] ?? 'COLLEGE')),
                ];
            });

        $merged = collect();
        $seen = [];

        foreach ($fallbackPrograms->concat($csvPrograms) as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $level = $this->normalizeLevel((string) ($item['level'] ?? 'COLLEGE'));
            if ($name === '') {
                continue;
            }

            $key = $level . '|' . mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $merged->push([
                'id' => (int) ($item['id'] ?? 0),
                'code' => (string) ($item['code'] ?? ''),
                'name' => $name,
                'level' => $level,
            ]);
        }

        $merged = $this->sortedCourses($merged);

        if ($levelFilter !== null) {
            $merged = $merged->where('level', $levelFilter)->values();
        }

        return $merged->values();
    }

    private function canManageCourses(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return in_array($role, ['superadmin', 'admin'], true);
    }

    private function hasCoursesTable(): bool
    {
        return Schema::hasTable('course_presets');
    }

    private function hasProgramLevelColumn(): bool
    {
        return $this->hasCoursesTable() && Schema::hasColumn('course_presets', 'program_level');
    }

    private function hasProgramSuggestionsTable(): bool
    {
        return Schema::hasTable('program_suggestions');
    }

    private function normalizeCode(string $value): string
    {
        $normalized = strtoupper(trim($value));
        $normalized = preg_replace('/[^A-Z0-9]+/', '_', $normalized) ?: '';
        $normalized = trim($normalized, '_');

        if ($normalized === '') {
            return 'PROGRAM';
        }

        return $normalized;
    }

    private function normalizeLevel(?string $value): string
    {
        $level = strtoupper(trim((string) $value));
        if (in_array($level, self::PROGRAM_LEVELS, true)) {
            return $level;
        }

        return 'COLLEGE';
    }

    private function resolvedLevelFilter($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $level = strtoupper(trim($value));
        if ($level === '' || !in_array($level, self::PROGRAM_LEVELS, true)) {
            return null;
        }

        return $level;
    }

    private function nextUniqueCode(string $baseCode, ?int $ignoreId = null): string
    {
        $code = $this->normalizeCode($baseCode);
        $counter = 2;

        while (true) {
            $query = CoursePreset::query()->where('course_code', $code);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                return $code;
            }

            $code = $this->normalizeCode($baseCode) . '_' . $counter;
            $counter++;
        }
    }

    private function sortedCourses(Collection $items): Collection
    {
        $levelOrder = array_flip(self::PROGRAM_LEVELS);

        return $items
            ->sortBy(function ($item) use ($levelOrder) {
                $level = $this->normalizeLevel((string) ($item->program_level ?? $item['level'] ?? 'COLLEGE'));
                $name = strtolower(trim((string) ($item->course_name ?? $item['name'] ?? '')));
                $weight = $levelOrder[$level] ?? 99;

                return sprintf('%02d_%s', $weight, $name);
            })
            ->values();
    }

    private function defaultPrograms(?string $levelFilter = null): Collection
    {
        return $this->mergedSeedPrograms($levelFilter);
    }

    private function programsPayload(?string $levelFilter = null): array
    {
        $seedPrograms = $this->mergedSeedPrograms($levelFilter);

        if (!$this->hasCoursesTable()) {
            return [
                'success' => true,
                'data' => $seedPrograms->values(),
                'levels' => self::PROGRAM_LEVEL_LABELS,
            ];
        }

        $select = ['id', 'course_code', 'course_name'];
        $hasLevel = $this->hasProgramLevelColumn();
        if ($hasLevel) {
            $select[] = 'program_level';
        }

        $databasePrograms = $this->sortedCourses(CoursePreset::query()->get($select))
            ->map(function ($row) use ($hasLevel) {
                return [
                    'id' => (int) ($row->id ?? 0),
                    'code' => (string) ($row->course_code ?? ''),
                    'name' => (string) ($row->course_name ?? ''),
                    'level' => $hasLevel
                        ? $this->normalizeLevel((string) ($row->program_level ?? 'COLLEGE'))
                        : 'COLLEGE',
                ];
            })
            ->values();

        $data = collect();
        $seen = [];

        foreach ($seedPrograms->concat($databasePrograms) as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $level = $this->normalizeLevel((string) ($row['level'] ?? 'COLLEGE'));
            if ($name === '') {
                continue;
            }

            if ($levelFilter !== null && $level !== $levelFilter) {
                continue;
            }

            $key = $level . '|' . mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $data->push([
                'id' => (int) ($row['id'] ?? 0),
                'code' => (string) ($row['code'] ?? ''),
                'name' => $name,
                'level' => $level,
            ]);
        }

        $data = $this->sortedCourses($data)->values();

        return [
            'success' => true,
            'data' => $data,
            'levels' => self::PROGRAM_LEVEL_LABELS,
        ];
    }

    private function inferProgramLevel(?string $storedLevel, string $code = '', string $name = ''): string
    {
        $normalizedStoredLevel = strtoupper(trim((string) $storedLevel));
        if (in_array($normalizedStoredLevel, self::PROGRAM_LEVELS, true)) {
            return $normalizedStoredLevel;
        }

        $haystack = strtolower(trim(implode(' ', array_filter([
            $normalizedStoredLevel,
            $code,
            $name,
        ]))));

        if ($haystack !== '') {
            foreach (['doctorate', 'doctoral', 'doctor of philosophy', 'phd', 'ph.d', 'edd', 'sjd'] as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return 'DOCTORATE';
                }
            }

            foreach (['masteral', 'master', "master's", 'mba', 'mpa', 'msc', 'm.s', 'm.a'] as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return 'MASTERAL';
                }
            }
        }

        return 'COLLEGE';
    }

    private function synchronizeProgramPresets(): void
    {
        if (!$this->hasCoursesTable() || !$this->hasProgramLevelColumn()) {
            return;
        }

        $programs = CoursePreset::query()->get(['id', 'course_code', 'course_name', 'program_level']);

        foreach ($programs as $program) {
            $resolvedLevel = $this->inferProgramLevel(
                (string) ($program->program_level ?? ''),
                (string) ($program->course_code ?? ''),
                (string) ($program->course_name ?? '')
            );

            if (strtoupper(trim((string) ($program->program_level ?? ''))) === $resolvedLevel) {
                continue;
            }

            $program->program_level = $resolvedLevel;
            $program->save();
        }

        foreach (self::DEFAULT_PROGRAMS as $defaultProgram) {
            $defaultCode = (string) ($defaultProgram['code'] ?? '');
            $defaultName = trim((string) ($defaultProgram['name'] ?? ''));
            $defaultLevel = $this->normalizeLevel((string) ($defaultProgram['level'] ?? 'COLLEGE'));

            if ($defaultCode === '' || $defaultName === '') {
                continue;
            }

            $existing = CoursePreset::query()
                ->where('course_code', $defaultCode)
                ->orWhere('course_name', $defaultName)
                ->first();

            if ($existing) {
                if ($this->normalizeLevel((string) ($existing->program_level ?? 'COLLEGE')) !== $defaultLevel) {
                    $existing->update(['program_level' => $defaultLevel]);
                }
                continue;
            }

            CoursePreset::query()->create([
                'course_code' => $this->nextUniqueCode($defaultCode),
                'course_name' => $defaultName,
                'program_level' => $defaultLevel,
            ]);
        }
    }

    public function index()
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            $courses = collect();
        } else {
            $this->synchronizeProgramPresets();

            $select = ['id', 'course_code', 'course_name'];
            if ($this->hasProgramLevelColumn()) {
                $select[] = 'program_level';
            }

            $courses = $this->sortedCourses(CoursePreset::query()->get($select));
        }

        $pendingSuggestions = collect();
        if ($this->hasProgramSuggestionsTable()) {
            $pendingSuggestions = ProgramSuggestion::query()
                ->with(['suggestedBy:id,first_name,last_name,email'])
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('admin.courses.index', [
            'courses' => $courses,
            'programLevels' => self::PROGRAM_LEVELS,
            'programLevelLabels' => self::PROGRAM_LEVEL_LABELS,
            'pendingSuggestions' => $pendingSuggestions,
        ]);
    }

    public function downloadTemplate()
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Programs');
        $sheet->setCellValue('A1', 'level');
        $sheet->setCellValue('B1', 'course');
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(60);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'Academic-Settings.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Programs table is missing. Run migrations first.']);
        }

        $programLevel = $this->normalizeLevel((string) $request->input('program_level', 'COLLEGE'));
        $nameRule = Rule::unique('course_presets', 'course_name');
        if ($this->hasProgramLevelColumn()) {
            $nameRule = $nameRule->where(function ($query) use ($programLevel) {
                $query->where('program_level', $programLevel);
            });
        }

        $validated = $request->validate([
            'program_level' => ['required', Rule::in(self::PROGRAM_LEVELS)],
            'course_name' => ['required', 'string', 'max:255', $nameRule],
        ]);

        $programName = trim((string) ($validated['course_name'] ?? ''));
        $programLevel = $this->normalizeLevel((string) ($validated['program_level'] ?? 'COLLEGE'));
        $programCode = $this->nextUniqueCode($programLevel . '_' . $programName);

        $payload = [
            'course_code' => $programCode,
            'course_name' => $programName,
        ];

        if ($this->hasProgramLevelColumn()) {
            $payload['program_level'] = $programLevel;
        }

        CoursePreset::query()->create($payload);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Program added successfully.');
    }

    public function previewImport(Request $request)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return response()->json([
                'message' => 'Programs table is missing. Run migrations first.',
            ], 422);
        }

        $validated = $request->validate([
            'program_level' => ['required', Rule::in(self::PROGRAM_LEVELS)],
            'import_file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:5120'],
        ]);

        try {
            $preview = $this->buildImportPreview(
                $request->file('import_file'),
                $this->normalizeLevel((string) ($validated['program_level'] ?? 'COLLEGE'))
            );
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unable to read the uploaded file. Use CSV or Excel with one program name per row.',
            ], 422);
        }

        return response()->json($preview);
    }

    public function import(Request $request)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Programs table is missing. Run migrations first.']);
        }

        $validated = $request->validate([
            'program_level' => ['required', Rule::in(self::PROGRAM_LEVELS)],
            'import_file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:5120'],
        ]);

        $programLevel = $this->normalizeLevel((string) ($validated['program_level'] ?? 'COLLEGE'));

        try {
            $preview = $this->buildImportPreview($request->file('import_file'), $programLevel);
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['import_file' => 'Unable to read the uploaded file. Use CSV or Excel with one program name per row.']);
        }

        $imported = 0;
        foreach ($preview['items'] as $item) {
            if (($item['status'] ?? '') !== 'ready') {
                continue;
            }

            $programName = trim((string) ($item['name'] ?? ''));
            $itemLevel = $this->normalizeLevel((string) ($item['level'] ?? $programLevel));
            if ($programName === '') {
                continue;
            }

            $payload = [
                'course_code' => $this->nextUniqueCode($itemLevel . '_' . $programName),
                'course_name' => $programName,
            ];

            if ($this->hasProgramLevelColumn()) {
                $payload['program_level'] = $itemLevel;
            }

            CoursePreset::query()->create($payload);
            $imported++;
        }

        if ($imported === 0) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['import_file' => 'No new programs were imported. Remove duplicates or blank rows and try again.']);
        }

        $skipped = max(0, (int) ($preview['summary']['total_rows'] ?? 0) - $imported);
        $message = $imported === 1 ? '1 program imported successfully.' : $imported . ' programs imported successfully.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' row' . ($skipped === 1 ? ' was' : 's were') . ' skipped.';
        }

        return redirect()
            ->route('admin.courses.index')
            ->with('success', $message);
    }

    public function update(Request $request, int $id)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Programs table is missing. Run migrations first.']);
        }

        $course = CoursePreset::query()->findOrFail($id);

        $programLevel = $this->normalizeLevel((string) $request->input('program_level', $course->program_level ?? 'COLLEGE'));
        $nameRule = Rule::unique('course_presets', 'course_name')->ignore($course->id);
        if ($this->hasProgramLevelColumn()) {
            $nameRule = $nameRule->where(function ($query) use ($programLevel) {
                $query->where('program_level', $programLevel);
            });
        }

        $validated = $request->validate([
            'program_level' => ['required', Rule::in(self::PROGRAM_LEVELS)],
            'course_name' => ['required', 'string', 'max:255', $nameRule],
        ]);

        $payload = [
            'course_name' => trim((string) ($validated['course_name'] ?? '')),
        ];

        if ($this->hasProgramLevelColumn()) {
            $payload['program_level'] = $this->normalizeLevel((string) ($validated['program_level'] ?? 'COLLEGE'));
        }

        $course->update($payload);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(int $id)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['course_presets' => 'Programs table is missing. Run migrations first.']);
        }

        $course = CoursePreset::query()->findOrFail($id);
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Program deleted successfully.');
    }

    public function listJson(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $levelFilter = $this->resolvedLevelFilter($request->query('level'));
        if ($this->hasCoursesTable()) {
            $this->synchronizeProgramPresets();
        }

        return response()->json($this->programsPayload($levelFilter));
    }

    public function publicListJson(Request $request)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $levelFilter = $this->resolvedLevelFilter($request->query('level'));
        if ($this->hasCoursesTable()) {
            $this->synchronizeProgramPresets();
        }

        return response()->json($this->programsPayload($levelFilter));
    }

    public function approveSuggestion(int $id)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasCoursesTable() || !$this->hasProgramSuggestionsTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['program_suggestions' => 'Required tables are missing. Run migrations first.']);
        }

        $suggestion = ProgramSuggestion::query()->findOrFail($id);
        if ($suggestion->status !== 'pending') {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['program_suggestions' => 'Only pending suggestions can be approved.']);
        }

        $programLevel = $this->normalizeLevel((string) ($suggestion->program_level ?? 'COLLEGE'));
        $programName = trim((string) ($suggestion->suggested_name ?? ''));
        if ($programName === '') {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['program_suggestions' => 'Suggestion name is empty and cannot be approved.']);
        }

        $programQuery = CoursePreset::query();
        if ($this->hasProgramLevelColumn()) {
            $programQuery->where('program_level', $programLevel);
        }

        $existingProgram = $programQuery
            ->whereRaw('LOWER(course_name) = ?', [strtolower($programName)])
            ->first();

        if (!$existingProgram) {
            $payload = [
                'course_code' => $this->nextUniqueCode($programLevel . '_' . $programName),
                'course_name' => $programName,
            ];
            if ($this->hasProgramLevelColumn()) {
                $payload['program_level'] = $programLevel;
            }
            $existingProgram = CoursePreset::query()->create($payload);
        }

        $suggestion->update([
            'status' => 'approved',
            'course_preset_id' => $existingProgram->id,
            'reviewed_by_admin_id' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Program suggestion approved successfully.');
    }

    public function declineSuggestion(int $id)
    {
        if (!$this->canManageCourses()) {
            abort(403);
        }

        if (!$this->hasProgramSuggestionsTable()) {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['program_suggestions' => 'Program suggestions table is missing. Run migrations first.']);
        }

        $suggestion = ProgramSuggestion::query()->findOrFail($id);
        if ($suggestion->status !== 'pending') {
            return redirect()
                ->route('admin.courses.index')
                ->withErrors(['program_suggestions' => 'Only pending suggestions can be declined.']);
        }

        $suggestion->update([
            'status' => 'declined',
            'reviewed_by_admin_id' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Program suggestion declined.');
    }

    private function buildImportPreview($file, string $programLevel): array
    {
        $rows = $this->parseImportRows($file->getRealPath());

        $existingNamesByLevel = [];
        foreach (CoursePreset::query()->get(['course_name', 'program_level']) as $existingCourse) {
            $existingLevel = $this->hasProgramLevelColumn()
                ? $this->normalizeLevel((string) ($existingCourse->program_level ?? 'COLLEGE'))
                : 'COLLEGE';
            $existingName = mb_strtolower(trim((string) ($existingCourse->course_name ?? '')));
            if ($existingName === '') {
                continue;
            }

            if (!isset($existingNamesByLevel[$existingLevel])) {
                $existingNamesByLevel[$existingLevel] = [];
            }

            $existingNamesByLevel[$existingLevel][$existingName] = true;
        }

        $items = [];
        $seenInFile = [];
        $readyCount = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $normalized = mb_strtolower($name);
            $rawLevel = trim((string) ($row['level'] ?? ''));
            $levelState = $this->resolveImportLevel($rawLevel, $programLevel);
            $effectiveLevel = $levelState['level'];
            $status = 'ready';
            $message = 'Ready to import';

            if ($name === '') {
                $status = 'empty';
                $message = 'Blank row skipped';
            } elseif (!$levelState['valid']) {
                $status = 'invalid_level';
                $message = 'Invalid level value';
            } elseif (isset($seenInFile[$effectiveLevel . '|' . $normalized])) {
                $status = 'duplicate_file';
                $message = 'Duplicate in upload';
            } elseif (isset($existingNamesByLevel[$effectiveLevel][$normalized])) {
                $status = 'duplicate_existing';
                $message = 'Already exists';
            } else {
                $readyCount++;
                $seenInFile[$effectiveLevel . '|' . $normalized] = true;
            }

            $items[] = [
                'row_number' => (int) ($row['row_number'] ?? 0),
                'level' => $levelState['display_level'],
                'name' => $name,
                'status' => $status,
                'message' => $message,
            ];
        }

        return [
            'program_level' => $programLevel,
            'items' => $items,
            'summary' => [
                'total_rows' => count($items),
                'ready_rows' => $readyCount,
                'skipped_rows' => count($items) - $readyCount,
            ],
        ];
    }

    private function parseImportRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheet(0);
        $rawRows = $sheet->toArray('', true, true, false);

        if (count($rawRows) === 0) {
            return [];
        }

        $headerIndex = null;
        $levelIndex = null;
        $firstRow = array_map(
            static fn($value) => strtolower(trim((string) $value)),
            (array) ($rawRows[0] ?? [])
        );

        foreach ($firstRow as $index => $column) {
            if ($levelIndex === null && in_array($column, ['program_level', 'level'], true)) {
                $levelIndex = (int) $index;
            }

            if ($headerIndex === null && in_array($column, ['program_name', 'course_name', 'program', 'course', 'name'], true)) {
                $headerIndex = (int) $index;
            }
        }

        $startIndex = $headerIndex === null ? 0 : 1;
        $parsed = [];

        for ($rowIndex = $startIndex; $rowIndex < count($rawRows); $rowIndex++) {
            $row = array_map(
                static fn($value) => trim((string) $value),
                (array) ($rawRows[$rowIndex] ?? [])
            );

            $name = '';
            $level = '';
            if ($headerIndex !== null) {
                $name = trim((string) ($row[$headerIndex] ?? ''));
            } else {
                foreach ($row as $cell) {
                    if ($cell !== '') {
                        $name = $cell;
                        break;
                    }
                }
            }

            if ($levelIndex !== null) {
                $level = trim((string) ($row[$levelIndex] ?? ''));
            }

            $parsed[] = [
                'row_number' => $rowIndex + 1,
                'level' => $level,
                'name' => $name,
            ];
        }

        return $parsed;
    }

    private function resolveImportLevel(?string $value, string $defaultLevel): array
    {
        $rawLevel = strtoupper(trim((string) $value));
        if ($rawLevel === '') {
            return [
                'level' => $defaultLevel,
                'display_level' => $defaultLevel,
                'valid' => true,
            ];
        }

        if (in_array($rawLevel, self::PROGRAM_LEVELS, true)) {
            return [
                'level' => $rawLevel,
                'display_level' => $rawLevel,
                'valid' => true,
            ];
        }

        return [
            'level' => $defaultLevel,
            'display_level' => $rawLevel,
            'valid' => false,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CoursePreset;
use App\Models\ProgramSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
        return $this->sortedCourses(collect(self::DEFAULT_PROGRAMS))
            ->map(function (array $item) {
                return [
                    'id' => 0,
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'level' => $this->normalizeLevel((string) ($item['level'] ?? 'COLLEGE')),
                ];
            })
            ->when($levelFilter !== null, function (Collection $collection) use ($levelFilter) {
                return $collection->where('level', $levelFilter)->values();
            });
    }

    private function programsPayload(?string $levelFilter = null): array
    {
        if (!$this->hasCoursesTable()) {
            return [
                'success' => true,
                'data' => $this->defaultPrograms($levelFilter)->values(),
                'levels' => self::PROGRAM_LEVEL_LABELS,
            ];
        }

        $select = ['id', 'course_code', 'course_name'];
        $hasLevel = $this->hasProgramLevelColumn();
        if ($hasLevel) {
            $select[] = 'program_level';
        }

        $data = $this->sortedCourses(CoursePreset::query()->get($select))
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
            ->when($levelFilter !== null, function (Collection $collection) use ($levelFilter) {
                return $collection->where('level', $levelFilter)->values();
            })
            ->values();

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
}

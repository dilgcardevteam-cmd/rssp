<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Models\VacancyTitle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PositionUtilityController extends Controller
{
    private function isHrDivision(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return $role === 'hr_division';
    }

    private function canManagePositions(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return in_array($role, ['superadmin', 'admin', 'hr_division'], true);
    }

    private function hasPositionsTable(): bool
    {
        return Schema::hasTable('vacancy_titles');
    }

    private function hasPositionsColumn(string $column): bool
    {
        return $this->hasPositionsTable() && Schema::hasColumn('vacancy_titles', $column);
    }

    private function positionsBaseQuery(): ?Builder
    {
        if (!$this->hasPositionsTable()) {
            return null;
        }

        $columns = [
            'id',
            'position_title',
            'salary_grade',
            'monthly_salary',
            'updated_at',
        ];

        foreach ([
            'vacancy_type',
            'pcn_no',
            'plantilla_item_no',
            'closing_date',
            'place_of_assignment',
            'qualification_education',
            'qualification_training',
            'qualification_experience',
            'qualification_eligibility',
            'competencies',
            'expected_output',
            'scope_of_work',
            'duration_of_work',
            'to_person',
            'to_position',
            'to_office',
            'to_office_address',
            'csc_form_path',
            'supporting_documents_required',
        ] as $optionalColumn) {
            if ($this->hasPositionsColumn($optionalColumn)) {
                $columns[] = $optionalColumn;
            }
        }

        return VacancyTitle::query()->select($columns);
    }

    private function applyHrDivisionPositionScope(Builder $query): void
    {
        if (!$this->isHrDivision() || !$this->hasPositionsColumn('vacancy_type')) {
            return;
        }

        $query->whereRaw('UPPER(TRIM(COALESCE(vacancy_type, ""))) = ?', ['COS']);
    }

    private function mapPositionRecord(VacancyTitle $row): VacancyTitle
    {
        $row->setAttribute('vacancy_id', 'TITLE-' . (string) $row->id);
        $row->setAttribute('vacancy_type', strtoupper(trim((string) ($row->vacancy_type ?? ''))));
        $row->setAttribute('place_of_assignment', trim((string) ($row->place_of_assignment ?? '')));
        return $row;
    }

    private function firstNonEmptyString(...$values): string
    {
        foreach ($values as $value) {
            $text = trim((string) ($value ?? ''));
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function formatDateForResponse($value): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function resolveVacancyFallback(string $positionTitle, string $vacancyType = ''): ?JobVacancy
    {
        $title = trim($positionTitle);
        if ($title === '') {
            return null;
        }

        $query = JobVacancy::query()
            ->where('position_title', $title)
            ->select([
                'position_title',
                'vacancy_type',
                'salary_grade',
                'monthly_salary',
                'pcn_no',
                'plantilla_item_no',
                'closing_date',
                'place_of_assignment',
                'qualification_education',
                'qualification_training',
                'qualification_experience',
                'qualification_eligibility',
                'competencies',
                'expected_output',
                'scope_of_work',
                'duration_of_work',
                'to_person',
                'to_position',
                'to_office',
                'to_office_address',
                'updated_at',
                'created_at',
            ]);

        if (Schema::hasColumn('job_vacancies', 'csc_form_path')) {
            $query->addSelect('csc_form_path');
        }

        if (Schema::hasColumn('job_vacancies', 'supporting_documents_required')) {
            $query->addSelect('supporting_documents_required');
        }

        $type = strtoupper(trim($vacancyType));
        if ($type !== '') {
            $typedMatch = (clone $query)
                ->whereRaw('UPPER(TRIM(COALESCE(vacancy_type, ""))) = ?', [$type])
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->first();

            if ($typedMatch) {
                return $typedMatch;
            }
        }

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();
    }

    public function index(Request $request)
    {
        if (!$this->canManagePositions()) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));
        $query = $this->positionsBaseQuery();

        if (!$query) {
            return view('admin.positions.index', [
                'positions' => collect(),
                'search' => $search,
            ]);
        }

        $this->applyHrDivisionPositionScope($query);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('position_title', 'like', '%' . $search . '%')
                    ->orWhere('salary_grade', 'like', '%' . $search . '%')
                    ->orWhereRaw('CAST(monthly_salary AS CHAR) like ?', ['%' . $search . '%']);

                if ($this->hasPositionsColumn('vacancy_type')) {
                    $q->orWhere('vacancy_type', 'like', '%' . $search . '%');
                }
                if ($this->hasPositionsColumn('place_of_assignment')) {
                    $q->orWhere('place_of_assignment', 'like', '%' . $search . '%');
                }
            });
        }

        /** @var Collection<int, VacancyTitle> $positions */
        $positions = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn(VacancyTitle $row) => $this->mapPositionRecord($row))
            ->unique(function (VacancyTitle $row) {
                $title = strtolower(trim((string) $row->position_title));
                $type = strtoupper(trim((string) ($row->vacancy_type ?? '')));
                return $title . '|' . $type;
            })
            ->values();

        return view('admin.positions.index', compact('positions', 'search'));
    }

    public function listJson(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $query = $this->positionsBaseQuery();
        if (!$query) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $this->applyHrDivisionPositionScope($query);

        $type = strtoupper(trim((string) $request->query('vacancy_type', '')));
        $allowedTypes = ['COS', 'PLANTILLA'];
        $filterType = in_array($type, $allowedTypes, true) ? $type : null;

        if ($filterType && $this->hasPositionsColumn('vacancy_type')) {
            $query->where(function ($q) use ($filterType) {
                $q->whereRaw('UPPER(TRIM(COALESCE(vacancy_type, ""))) = ?', [$filterType])
                    ->orWhereNull('vacancy_type')
                    ->orWhereRaw('TRIM(COALESCE(vacancy_type, "")) = ""');
            });
        }

        $query->orderBy('position_title');
        if ($this->hasPositionsColumn('vacancy_type')) {
            $query->orderBy('vacancy_type');
        }
        $query->orderByDesc('updated_at');

        /** @var Collection<int, VacancyTitle> $positions */
        $positions = $query
            ->get()
            ->map(fn(VacancyTitle $row) => $this->mapPositionRecord($row))
            ->unique(function (VacancyTitle $row) {
                $title = strtolower(trim((string) $row->position_title));
                $type = strtoupper(trim((string) ($row->vacancy_type ?? '')));
                return $title . '|' . $type;
            })
            ->values();

        $data = $positions->map(function (VacancyTitle $row) use ($filterType) {
            $resolvedType = strtoupper($this->firstNonEmptyString($row->vacancy_type, $filterType));

            $fallback = null;
            $needsFallback = $this->firstNonEmptyString(
                $row->place_of_assignment,
                $row->qualification_education,
                $row->qualification_training,
                $row->qualification_experience,
                $row->qualification_eligibility,
                $row->competencies,
                $row->expected_output,
                $row->scope_of_work,
                $row->duration_of_work,
                $row->to_person,
                $row->to_position,
                $row->to_office,
                $row->to_office_address
            ) === '';

            if ($needsFallback) {
                $fallback = $this->resolveVacancyFallback((string) ($row->position_title ?? ''), $resolvedType);
            }

            return [
                'vacancy_id' => (string) ($row->vacancy_id ?? ('TITLE-' . (string) $row->id)),
                'position_title' => (string) ($row->position_title ?? ''),
                'vacancy_type' => strtoupper($this->firstNonEmptyString($row->vacancy_type, $fallback?->vacancy_type, $filterType)),
                'salary_grade' => $this->firstNonEmptyString($row->salary_grade, $fallback?->salary_grade),
                'monthly_salary' => $row->monthly_salary ?? $fallback?->monthly_salary,
                'pcn_no' => $this->firstNonEmptyString($row->pcn_no, $fallback?->pcn_no),
                'plantilla_item_no' => $this->firstNonEmptyString($row->plantilla_item_no, $fallback?->plantilla_item_no),
                'closing_date' => $this->formatDateForResponse($row->closing_date) ?: $this->formatDateForResponse($fallback?->closing_date),
                'place_of_assignment' => $this->firstNonEmptyString($row->place_of_assignment, $fallback?->place_of_assignment),
                'qualification_education' => $this->firstNonEmptyString($row->qualification_education, $fallback?->qualification_education),
                'qualification_training' => $this->firstNonEmptyString($row->qualification_training, $fallback?->qualification_training),
                'qualification_experience' => $this->firstNonEmptyString($row->qualification_experience, $fallback?->qualification_experience),
                'qualification_eligibility' => $this->firstNonEmptyString($row->qualification_eligibility, $fallback?->qualification_eligibility),
                'competencies' => $this->firstNonEmptyString($row->competencies, $fallback?->competencies),
                'expected_output' => $this->firstNonEmptyString($row->expected_output, $fallback?->expected_output),
                'scope_of_work' => $this->firstNonEmptyString($row->scope_of_work, $fallback?->scope_of_work),
                'duration_of_work' => $this->firstNonEmptyString($row->duration_of_work, $fallback?->duration_of_work),
                'to_person' => $this->firstNonEmptyString($row->to_person, $fallback?->to_person),
                'to_position' => $this->firstNonEmptyString($row->to_position, $fallback?->to_position),
                'to_office' => $this->firstNonEmptyString($row->to_office, $fallback?->to_office),
                'to_office_address' => $this->firstNonEmptyString($row->to_office_address, $fallback?->to_office_address),
                'csc_form_path' => $this->firstNonEmptyString($row->csc_form_path, $fallback?->csc_form_path),
                'supporting_documents_required' => $row->supporting_documents_required ?? $fallback?->supporting_documents_required,
            ];
        })->values();

        if ($filterType) {
            $data = $data
                ->filter(function (array $item) use ($filterType) {
                    return strtoupper(trim((string) ($item['vacancy_type'] ?? ''))) === $filterType;
                })
                ->values();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

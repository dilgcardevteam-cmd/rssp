<?php

namespace App\Http\Controllers;

use App\Models\EligibilityPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class EligibilityPresetController extends Controller
{
    private const DEFAULT_PRESETS = [
        ['name' => 'CSC Professional Eligibility', 'legal_basis' => 'CSR 2017 / PD 807', 'level' => 'Second Level'],
        ['name' => 'CSC Subprofessional Eligibility', 'legal_basis' => 'CSR 2017 / PD 807', 'level' => 'First Level'],
        ['name' => 'Bar/Board Eligibility', 'legal_basis' => 'RA 1080', 'level' => 'Second Level'],
        ['name' => 'Honor Graduate Eligibility', 'legal_basis' => 'PD 907', 'level' => 'Second Level'],
        ['name' => 'Foreign School Honor Graduate Eligibility', 'legal_basis' => 'CSC Resolution No. 1302714', 'level' => 'Second Level'],
        ['name' => 'Scientific and Technological Specialist Eligibility', 'legal_basis' => 'PD 997', 'level' => 'Second Level'],
        ['name' => 'Electronic Data Processing Specialist Eligibility', 'legal_basis' => 'CSC Resolution No. 90-083', 'level' => 'Second Level'],
        ['name' => 'Skills Eligibility – Category II', 'legal_basis' => 'CSC MC No. 11, s. 1996, as amended', 'level' => 'First Level'],
        ['name' => 'Barangay Official Eligibility', 'legal_basis' => 'RA 7160', 'level' => 'First Level'],
        ['name' => 'Barangay Health Worker Eligibility', 'legal_basis' => 'RA 7883', 'level' => 'First Level'],
        ['name' => 'Barangay Nutrition Scholar Eligibility', 'legal_basis' => 'PD 1569', 'level' => 'First Level'],
        ['name' => 'Sanggunian Member First Level Eligibility', 'legal_basis' => 'RA 10156', 'level' => 'First Level'],
        ['name' => 'Sanggunian Member Second Level Eligibility', 'legal_basis' => 'RA 10156', 'level' => 'Second Level'],
        ['name' => 'Veteran Preference Rating Eligibility', 'legal_basis' => 'Professional or Subprofessional, depending on exam/rating', 'level' => 'Second Level'],
        ['name' => 'Career Service Eligibility – Preference Rating', 'legal_basis' => 'CSE-PR', 'level' => 'Second Level'],
        ['name' => 'Career Service Eligibility – Preference Rating for Military and Uniformed Personnel', 'legal_basis' => 'CSE-PR for MUP', 'level' => 'Second Level'],
    ];

    private function levelRank(?string $level): int
    {
        $normalized = strtolower(trim((string) $level));

        if (str_contains($normalized, 'second')) {
            return 1;
        }

        if (str_contains($normalized, 'first')) {
            return 2;
        }

        return 3;
    }

    private function hierarchyRank(?string $name): int
    {
        $normalized = strtolower(trim((string) $name));

        return match (true) {
            str_contains($normalized, 'csc professional') || str_contains($normalized, 'career service professional') => 10,
            str_contains($normalized, 'csc subprofessional') => 20,
            str_contains($normalized, 'bar/board') || (str_contains($normalized, 'bar') && str_contains($normalized, 'board')) => 30,
            str_contains($normalized, 'honor graduate') && !str_contains($normalized, 'foreign') => 40,
            str_contains($normalized, 'foreign school honor graduate') => 50,
            str_contains($normalized, 'scientific and technological specialist') => 60,
            str_contains($normalized, 'electronic data processing specialist') => 70,
            str_contains($normalized, 'skills eligibility') && str_contains($normalized, 'category ii') => 80,
            str_contains($normalized, 'barangay official') => 90,
            str_contains($normalized, 'barangay health worker') => 100,
            str_contains($normalized, 'barangay nutrition scholar') => 110,
            str_contains($normalized, 'sanggunian member') && str_contains($normalized, 'first level') => 120,
            str_contains($normalized, 'sanggunian member') && str_contains($normalized, 'second level') => 130,
            str_contains($normalized, 'veteran preference rating') => 140,
            str_contains($normalized, 'career service eligibility') && str_contains($normalized, 'military') => 160,
            str_contains($normalized, 'career service eligibility') && str_contains($normalized, 'preference rating') => 150,
            default => 999,
        };
    }

    private function fieldValue(array|object $item, string $field): string
    {
        if (is_array($item)) {
            return trim((string) ($item[$field] ?? ''));
        }

        return trim((string) ($item->{$field} ?? ''));
    }

    private function sortByHierarchy(Collection $items): Collection
    {
        $rows = $items->values()->all();

        usort($rows, function (array|object $left, array|object $right): int {
            $leftLevelRank = $this->levelRank($this->fieldValue($left, 'level'));
            $rightLevelRank = $this->levelRank($this->fieldValue($right, 'level'));
            if ($leftLevelRank !== $rightLevelRank) {
                return $leftLevelRank <=> $rightLevelRank;
            }

            $leftHierarchyRank = $this->hierarchyRank($this->fieldValue($left, 'name'));
            $rightHierarchyRank = $this->hierarchyRank($this->fieldValue($right, 'name'));
            if ($leftHierarchyRank !== $rightHierarchyRank) {
                return $leftHierarchyRank <=> $rightHierarchyRank;
            }

            return strcasecmp($this->fieldValue($left, 'name'), $this->fieldValue($right, 'name'));
        });

        return collect($rows)->values();
    }

    private function canManageEligibilities(): bool
    {
        $role = Auth::guard('admin')->user()->role ?? null;
        return in_array($role, ['superadmin', 'admin'], true);
    }

    private function hasPresetsTable(): bool
    {
        return Schema::hasTable('eligibility_presets');
    }

    public function index()
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }

        $eligibilities = $this->hasPresetsTable()
            ? $this->sortByHierarchy(EligibilityPreset::query()->get())
            : collect();

        return view('admin.eligibilities.index', compact('eligibilities'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }
        if (!$this->hasPresetsTable()) {
            return redirect()
                ->route('admin.eligibilities.index')
                ->withErrors(['eligibility_presets' => 'Eligibility presets table is missing. Run migrations first.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:eligibility_presets,name',
            'legal_basis' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        EligibilityPreset::query()->create([
            'name' => trim((string) ($validated['name'] ?? '')),
            'legal_basis' => trim((string) ($validated['legal_basis'] ?? '')),
            'level' => trim((string) ($validated['level'] ?? '')),
        ]);

        return redirect()
            ->route('admin.eligibilities.index')
            ->with('success', 'Eligibility added successfully.');
    }

    public function update(Request $request, int $id)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }
        if (!$this->hasPresetsTable()) {
            return redirect()
                ->route('admin.eligibilities.index')
                ->withErrors(['eligibility_presets' => 'Eligibility presets table is missing. Run migrations first.']);
        }

        $eligibility = EligibilityPreset::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:eligibility_presets,name,' . $eligibility->id,
            'legal_basis' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        $eligibility->update([
            'name' => trim((string) ($validated['name'] ?? '')),
            'legal_basis' => trim((string) ($validated['legal_basis'] ?? '')),
            'level' => trim((string) ($validated['level'] ?? '')),
        ]);

        return redirect()
            ->route('admin.eligibilities.index')
            ->with('success', 'Eligibility updated successfully.');
    }

    public function destroy(int $id)
    {
        if (!$this->canManageEligibilities()) {
            abort(403);
        }
        if (!$this->hasPresetsTable()) {
            return redirect()
                ->route('admin.eligibilities.index')
                ->withErrors(['eligibility_presets' => 'Eligibility presets table is missing. Run migrations first.']);
        }

        $eligibility = EligibilityPreset::query()->findOrFail($id);
        $eligibility->delete();

        return redirect()
            ->route('admin.eligibilities.index')
            ->with('success', 'Eligibility deleted successfully.');
    }

    public function listJson()
    {
        if (!Auth::guard('admin')->check() && !Auth::guard('web')->check()) {
            abort(403);
        }

        if (!$this->hasPresetsTable()) {
            return response()->json([
                'success' => true,
                'data' => $this->sortByHierarchy(collect(self::DEFAULT_PRESETS))->values(),
            ]);
        }

        $data = $this->sortByHierarchy(
            EligibilityPreset::query()->get(['id', 'name', 'legal_basis', 'level'])
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

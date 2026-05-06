<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkExpSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;



class WorkExpSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //($request->all());
        $user_id = Auth::id();

        $existingEntries = WorkExpSheet::where('user_id', $user_id)->exists();

        $validated = Validator::make($request->all(), [
            'entries' => ['nullable', 'array'],
            'entries.*.start_date' => ['nullable', 'date'],
            'entries.*.end_date' => ['nullable', 'date'],
            'entries.*.present' => ['nullable', 'boolean'],
            'entries.*.position' => ['nullable', 'string', 'max:255'],
            'entries.*.office' => ['nullable', 'string', 'max:255'],
            'entries.*.supervisor' => ['nullable', 'string', 'max:255'],
            'entries.*.agency' => ['nullable', 'string', 'max:255'],
            'entries.*.accomplishments' => ['nullable', 'array'],
            'entries.*.accomplishments.*' => ['nullable', 'string', 'max:1000'],
            'entries.*.duties' => ['nullable', 'array'],
            'entries.*.duties.*' => ['nullable', 'string', 'max:1000'],
            'entries.*.isDisplayed' => ['nullable', 'boolean'],
        ]);

        $validated->after(function ($validator) use ($request) {
            foreach ((array) $request->input('entries', []) as $index => $work) {
                $startDate = trim((string) ($work['start_date'] ?? ''));
                $endDate = trim((string) ($work['end_date'] ?? ''));

                if ($startDate !== '' && $endDate !== '' && $endDate < $startDate) {
                    $validator->errors()->add("entries.$index.end_date", 'The end date must be a date after or equal to the start date.');
                }
            }
        });

        $validated = $validated->validate();
        $entries = $this->normalizeWesEntries($validated['entries'] ?? []);

        DB::transaction(function () use ($entries, $user_id) {
            WorkExpSheet::where('user_id', $user_id)->delete();

            foreach ($entries as $work) {
                $isPresent = (bool) ($work['present'] ?? false);
                $endDate = $isPresent ? null : ($work['end_date'] ?? null);

                WorkExpSheet::create([
                    'user_id' => $user_id,
                    'start_date' => $work['start_date'],
                    'end_date' => $endDate,
                    'position' => trim((string) ($work['position'] ?? '')),
                    'office' => trim((string) ($work['office'] ?? '')),
                    'supervisor' => trim((string) ($work['supervisor'] ?? '')),
                    'agency' => trim((string) ($work['agency'] ?? '')),
                    'accomplishments' => $work['accomplishments'] ?? [],
                    'duties' => $work['duties'] ?? [],
                    'isDisplayed' => $work['isDisplayed'] ?? true,
                ]);
            }
        });

        // Explicit save should supersede any transient autosave draft for WES.
        $request->session()->forget('form.wes');

        $action = $existingEntries ? 'Update' : 'Create';

        activity()
            ->causedBy(Auth::user())
            ->event($action)
            ->withProperties([
                'entries_count' => count($entries),
                'action_type' => $action,
                'section' => 'Work Experience Sheet',
            ])
            ->log($action . 'd Work Experience Sheet');

        if ($request->input('after_action') === 'preview') {
            return redirect()
                ->route('pds.preview')
                ->with('success', 'Work Experience Sheet Saved!');
        }

        if ($request->input('after_action') === 'dashboard') {
            return redirect()
                ->route('dashboard_user')
                ->with('success', 'Work Experience Sheet Saved!');
        }

        if ($request->input('after_action') === 'next') {
            return redirect()
                ->route('display_wes', ['open_docs' => 1])
                ->with('success', 'Work Experience Sheet Saved!');
        }

        return redirect()
            ->route('display_wes', ['open_docs' => 1])
            ->with('success', 'Work Experience Sheet Saved!');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $sessionEntries = session('form.wes.entries', []);
        if (is_array($sessionEntries) && count($sessionEntries) > 0) {
            $workEntries = collect($sessionEntries)->map(function ($entry) {
                return [
                    'start_date' => $entry['start_date'] ?? null,
                    'end_date' => $entry['end_date'] ?? null,
                    'position' => $entry['position'] ?? '',
                    'office' => $entry['office'] ?? '',
                    'supervisor' => $entry['supervisor'] ?? '',
                    'agency' => $entry['agency'] ?? '',
                    'accomplishments' => is_array($entry['accomplishments'] ?? null)
                        ? $entry['accomplishments']
                        : [''],
                    'duties' => is_array($entry['duties'] ?? null)
                        ? $entry['duties']
                        : [''],
                    'isDisplayed' => (bool) ($entry['isDisplayed'] ?? true),
                ];
            });
        } else {
            $workEntries = WorkExpSheet::where('user_id', Auth::id())->get();
        }

        //info($workEntries);
        return view('pds.wes', ['workEntries' => $workEntries]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function normalizeWesEntries(array $entries): array
    {
        return collect($entries)
            ->map(function (array $work) {
                $accomplishments = array_values(array_filter(
                    array_map(
                        fn ($value) => trim((string) $value),
                        is_array($work['accomplishments'] ?? null) ? $work['accomplishments'] : []
                    ),
                    fn ($value) => $value !== ''
                ));

                $duties = array_values(array_filter(
                    array_map(
                        fn ($value) => trim((string) $value),
                        is_array($work['duties'] ?? null) ? $work['duties'] : []
                    ),
                    fn ($value) => $value !== ''
                ));

                return [
                    'start_date' => trim((string) ($work['start_date'] ?? '')),
                    'end_date' => trim((string) ($work['end_date'] ?? '')),
                    'present' => filter_var($work['present'] ?? false, FILTER_VALIDATE_BOOL),
                    'position' => trim((string) ($work['position'] ?? '')),
                    'office' => trim((string) ($work['office'] ?? '')),
                    'supervisor' => trim((string) ($work['supervisor'] ?? '')),
                    'agency' => trim((string) ($work['agency'] ?? '')),
                    'accomplishments' => $accomplishments,
                    'duties' => $duties,
                    'isDisplayed' => filter_var($work['isDisplayed'] ?? true, FILTER_VALIDATE_BOOL),
                ];
            })
            ->filter(fn (array $work) => $this->hasMeaningfulWesEntry($work))
            ->values()
            ->all();
    }

    private function hasMeaningfulWesEntry(array $work): bool
    {
        return $work['start_date'] !== ''
            || $work['end_date'] !== ''
            || $work['position'] !== ''
            || $work['office'] !== ''
            || $work['supervisor'] !== ''
            || $work['agency'] !== ''
            || !empty($work['accomplishments'])
            || !empty($work['duties']);
    }
}

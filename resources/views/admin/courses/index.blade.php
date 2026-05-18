@extends('layout.admin')
@section('title', 'Academic Programs')
@section('content')
@php
    $levelLabels = $programLevelLabels ?? [
        'COLLEGE' => 'College',
        'MASTERAL' => 'Masteral',
        'DOCTORATE' => 'Doctorate',
    ];
    $levelOptions = $programLevels ?? array_keys($levelLabels);
    $programItems = ($courses ?? collect())->map(function ($item) {
        $item->program_level = strtoupper((string) ($item->program_level ?? 'COLLEGE'));
        return $item;
    });
    $pendingSuggestionItems = $pendingSuggestions ?? collect();

    $countByLevel = [];
    foreach ($levelOptions as $code) {
        $code = strtoupper((string) $code);
        $countByLevel[$code] = $programItems->where('program_level', $code)->count();
    }
@endphp

<div class="mx-auto max-w-7xl space-y-5 p-6 font-montserrat">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-semibold tracking-tight text-[#0D2B70]">Academic programs</h1>
        <p class="mt-1 text-sm text-slate-600">Manage degree and course options used in education requirement settings.</p>
    </section>

    @if ($errors->any())
        <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-red-700 relative z-50">
            <ul class="list-inside list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div id="programSuccessAlert" class="flex items-start justify-between gap-3 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 relative z-50">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" id="dismissProgramSuccess" class="rounded p-1 text-emerald-700 hover:bg-emerald-100" aria-label="Dismiss">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <section class="grid grid-cols-1 gap-3 md:grid-cols-3">
        @foreach ($levelOptions as $code)
            @php
                $levelCode = strtoupper((string) $code);
                $label = $levelLabels[$levelCode] ?? $levelCode;
                $count = (int) ($countByLevel[$levelCode] ?? 0);
            @endphp
            <button
                type="button"
                data-level-card="{{ $levelCode }}"
                class="rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-slate-300 hover:shadow"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold text-[#0D2B70]">{{ $count }}</p>
                <p class="text-xs text-slate-500">{{ Str::plural('program', $count) }}</p>
            </button>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-12">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-4">
            <h2 class="text-base font-semibold text-slate-900">Add program</h2>
            <p class="mt-1 text-sm text-slate-600">Add a new degree/course option for education requirement dropdowns.</p>

            <form id="programAddForm" method="POST" action="{{ route('admin.courses.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="add_program_level" class="mb-1 block text-sm font-medium text-slate-700">Program level</label>
                    <select id="add_program_level" name="program_level" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        @foreach ($levelOptions as $code)
                            @php $levelCode = strtoupper((string) $code); @endphp
                            <option value="{{ $levelCode }}" {{ old('program_level', 'COLLEGE') === $levelCode ? 'selected' : '' }}>{{ $levelLabels[$levelCode] ?? $levelCode }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="add_program_name" class="mb-1 block text-sm font-medium text-slate-700">Program name</label>
                    <input id="add_program_name" type="text" name="course_name" required value="{{ old('course_name') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="e.g., BS Information Technology">
                    <p class="mt-1 text-xs font-semibold text-red-600">Do not abbreviate.</p>
                    <p id="programCodePreview" class="mt-1 text-xs text-slate-500">Code preview: <span class="font-mono text-slate-700">-</span></p>
                    <p id="programDuplicateWarning" class="mt-1 hidden text-xs font-medium text-amber-700">This program already exists in the selected level.</p>
                </div>
                <div class="space-y-2">
                    <button id="programAddSubmit" type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#12347f] disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add program</span>
                    </button>
                    <button id="openProgramImportModal" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa-solid fa-file-import"></i>
                        <span>Import programs</span>
                    </button>
                </div>
            </form>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-8">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Pending suggestions</h2>
                        <p class="text-sm text-slate-600">Review applicant-submitted entries not yet in your saved list.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $pendingSuggestionItems->count() }} pending</span>
                </div>
            </div>
            @if ($pendingSuggestionItems->isEmpty())
                <div class="px-5 py-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">No pending suggestions</p>
                    <p class="mt-1 text-sm text-slate-500">New applicant suggestions will appear here.</p>
                </div>
            @else
                <div class="max-h-[340px] overflow-x-auto overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Program</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Level</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Submitted by</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($pendingSuggestionItems as $suggestion)
                                @php
                                    $suggestionLevel = strtoupper((string) ($suggestion->program_level ?? 'COLLEGE'));
                                    $suggestionLevelLabel = $levelLabels[$suggestionLevel] ?? $suggestionLevel;
                                    $submittedBy = trim(implode(' ', array_filter([
                                        (string) ($suggestion->suggestedBy->first_name ?? ''),
                                        (string) ($suggestion->suggestedBy->last_name ?? ''),
                                    ])));
                                    if ($submittedBy === '') {
                                        $submittedBy = (string) ($suggestion->suggestedBy->email ?? 'Unknown applicant');
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-900">{{ $suggestion->suggested_name }}</p>
                                        <p class="text-xs text-slate-500">{{ optional($suggestion->created_at)->format('M d, Y h:i A') }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-slate-700">{{ $suggestionLevelLabel }}</td>
                                    <td class="px-5 py-3 text-slate-700">{{ $submittedBy }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('admin.courses.suggestions.approve', $suggestion->id) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100" onclick="return confirm('Approve this suggestion and add it to saved programs?');">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.courses.suggestions.decline', $suggestion->id) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100" onclick="return confirm('Decline this suggestion?');">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Saved programs</h2>
                    <p class="text-sm text-slate-600">Search, filter, edit, and delete approved degree/course options.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input id="programSearch" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:w-72" placeholder="Search program name">
                    <select id="programLevelFilter" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">All levels</option>
                        @foreach ($levelOptions as $code)
                            @php $levelCode = strtoupper((string) $code); @endphp
                            <option value="{{ $levelCode }}">{{ $levelLabels[$levelCode] ?? $levelCode }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Program name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Level</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="programTableBody" class="divide-y divide-slate-100 bg-white">
                    @forelse($programItems as $item)
                        @php
                            $itemLevel = strtoupper((string) ($item->program_level ?? 'COLLEGE'));
                            $itemLevelLabel = $levelLabels[$itemLevel] ?? $itemLevel;
                        @endphp
                        <tr data-row="program" data-level="{{ $itemLevel }}" data-name="{{ strtolower((string) $item->course_name) }}" class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $item->course_name }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $itemLevelLabel }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="program-edit-btn rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" data-id="{{ $item->id }}" data-name="{{ $item->course_name }}" data-level="{{ $itemLevel }}">Edit</button>
                                    <button type="button" class="program-delete-btn rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100" data-id="{{ $item->id }}" data-name="{{ $item->course_name }}">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="programEmptyRow">
                            <td colspan="3" class="px-5 py-10 text-center text-slate-500">No programs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p id="programFilterEmptyState" class="hidden px-5 py-4 text-sm text-slate-500">No matching programs found.</p>
    </section>
</div>

<div id="editModal" class="fixed inset-0 z-[120] hidden bg-black/40">
    <div class="grid min-h-screen place-items-center p-4">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-[#0D2B70]">Edit program</h3>
            <form id="editForm" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_program_level" class="mb-1 block text-sm font-medium text-slate-700">Program level</label>
                    <select id="edit_program_level" name="program_level" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        @foreach ($levelOptions as $code)
                            @php $levelCode = strtoupper((string) $code); @endphp
                            <option value="{{ $levelCode }}">{{ $levelLabels[$levelCode] ?? $levelCode }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_course_name" class="mb-1 block text-sm font-medium text-slate-700">Program name</label>
                    <input id="edit_course_name" type="text" name="course_name" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-close-edit class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white hover:bg-[#12347f]">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-[120] hidden bg-black/40">
    <div class="grid min-h-screen place-items-center p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-red-700">Delete program</h3>
            <p class="mt-2 text-sm text-slate-600">You are about to delete <span id="deleteProgramName" class="font-semibold text-slate-900"></span>.</p>
            <form id="deleteConfirmForm" method="POST" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" data-close-delete class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>
</div>

<div id="programImportModal" class="fixed inset-0 z-[130] hidden bg-black/40">
    <div class="grid min-h-screen place-items-center p-4">
        <div class="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-[#0D2B70]">Import programs</h3>
                    <p class="mt-1 text-sm text-slate-600">Upload a CSV or Excel file using the `level` and `course` columns.</p>
                </div>
                <button type="button" data-close-import class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Close import modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="programImportForm" method="POST" action="{{ route('admin.courses.import') }}" enctype="multipart/form-data" class="space-y-5 px-6 py-5">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="import_program_level" class="mb-1 block text-sm font-medium text-slate-700">Program level</label>
                        <select id="import_program_level" name="program_level" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            @foreach ($levelOptions as $code)
                                @php $levelCode = strtoupper((string) $code); @endphp
                                <option value="{{ $levelCode }}">{{ $levelLabels[$levelCode] ?? $levelCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="programImportFile" class="mb-1 block text-sm font-medium text-slate-700">Upload file</label>
                        <input id="programImportFile" type="file" name="import_file" accept=".csv,.txt,.xls,.xlsx" required class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.courses.template') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fa-solid fa-download"></i>
                                <span>Download template</span>
                            </a>
                            <p class="text-xs text-slate-500">Use the academic program template before uploading.</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Accepted files: CSV, TXT, XLS, XLSX. Required columns: `level` and `course`. If `level` is blank, the selected program level will be used.</p>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50">
                    <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900">Uploaded data preview</h4>
                            <p id="programImportStatus" class="mt-1 text-xs text-slate-500">Choose a file to preview uploaded programs.</p>
                        </div>
                        <p id="programImportSummary" class="text-xs font-medium text-slate-600"></p>
                    </div>
                    <div class="max-h-[360px] overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Row</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Level</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Program name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                                </tr>
                            </thead>
                            <tbody id="programImportPreviewBody" class="divide-y divide-slate-200 bg-white">
                                <tr id="programImportEmptyRow">
                                    <td colspan="4" class="px-4 py-10 text-center text-slate-500">No uploaded data yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" data-close-import class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button id="programImportSubmit" type="submit" disabled class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white hover:bg-[#12347f] disabled:cursor-not-allowed disabled:opacity-60">Save imported programs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const rows = Array.from(document.querySelectorAll('tr[data-row="program"]'));
    const searchInput = document.getElementById('programSearch');
    const levelFilter = document.getElementById('programLevelFilter');
    const cards = Array.from(document.querySelectorAll('[data-level-card]'));
    const filterEmptyState = document.getElementById('programFilterEmptyState');
    const staticEmptyRow = document.getElementById('programEmptyRow');

    const addNameInput = document.getElementById('add_program_name');
    const addLevelInput = document.getElementById('add_program_level');
    const addSubmit = document.getElementById('programAddSubmit');
    const codePreview = document.getElementById('programCodePreview');
    const duplicateWarning = document.getElementById('programDuplicateWarning');
    const existingEntries = rows.map((row) => ({
        level: String(row.dataset.level || '').toUpperCase(),
        name: String(row.dataset.name || '').trim().toLowerCase(),
    }));

    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const editNameInput = document.getElementById('edit_course_name');
    const editLevelInput = document.getElementById('edit_program_level');

    const deleteModal = document.getElementById('deleteModal');
    const deleteConfirmForm = document.getElementById('deleteConfirmForm');
    const deleteProgramName = document.getElementById('deleteProgramName');

    const importModal = document.getElementById('programImportModal');
    const openImportButton = document.getElementById('openProgramImportModal');
    const importForm = document.getElementById('programImportForm');
    const importLevelInput = document.getElementById('import_program_level');
    const importFileInput = document.getElementById('programImportFile');
    const importPreviewBody = document.getElementById('programImportPreviewBody');
    const importStatus = document.getElementById('programImportStatus');
    const importSummary = document.getElementById('programImportSummary');
    const importSubmit = document.getElementById('programImportSubmit');

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function normalize(v) {
        return String(v || '').trim().toLowerCase();
    }

    function updateCardState() {
        const active = String(levelFilter?.value || '').toUpperCase();
        cards.forEach((card) => {
            const level = String(card.dataset.levelCard || '').toUpperCase();
            const selected = active !== '' && active === level;
            card.classList.toggle('ring-2', selected);
            card.classList.toggle('ring-[#0D2B70]', selected);
            card.classList.toggle('border-[#0D2B70]', selected);
        });
    }

    function applyFilters() {
        const query = normalize(searchInput?.value);
        const selectedLevel = String(levelFilter?.value || '').toUpperCase();
        let visible = 0;

        rows.forEach((row) => {
            const rowName = normalize(row.dataset.name);
            const rowLevel = String(row.dataset.level || '').toUpperCase();
            const show = (query === '' || rowName.includes(query)) && (selectedLevel === '' || rowLevel === selectedLevel);
            row.classList.toggle('hidden', !show);
            if (show) visible += 1;
        });

        if (filterEmptyState) {
            filterEmptyState.classList.toggle('hidden', !(rows.length > 0 && visible === 0));
        }
        if (staticEmptyRow) {
            staticEmptyRow.classList.toggle('hidden', rows.length > 0);
        }
        updateCardState();
    }

    function generatedCode(level, name) {
        const normalizedLevel = String(level || '').trim().toUpperCase() || 'PROGRAM';
        const normalizedName = String(name || '').trim().toUpperCase().replace(/[^A-Z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        return normalizedName ? `${normalizedLevel}_${normalizedName}` : '-';
    }

    function updateAddFormHints() {
        if (!addNameInput || !addLevelInput) return;
        const level = String(addLevelInput.value || '').toUpperCase();
        const nameRaw = String(addNameInput.value || '');
        const name = normalize(nameRaw);
        const duplicate = name !== '' && existingEntries.some((entry) => entry.level === level && entry.name === name);

        if (codePreview) {
            codePreview.innerHTML = `Code preview: <span class="font-mono text-slate-700">${generatedCode(level, nameRaw)}</span>`;
        }
        if (duplicateWarning) duplicateWarning.classList.toggle('hidden', !duplicate);
        if (addSubmit) addSubmit.disabled = duplicate;
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function closeDeleteModal() {
        if (!deleteModal) return;
        deleteModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openImportModal() {
        if (!importModal) return;
        if (importLevelInput && addLevelInput) {
            importLevelInput.value = String(addLevelInput.value || 'COLLEGE').toUpperCase();
        }
        importModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeImportModal() {
        if (!importModal) return;
        importModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function resetImportPreview(message) {
        if (importPreviewBody) {
            importPreviewBody.innerHTML = `
                <tr id="programImportEmptyRow">
                    <td colspan="4" class="px-4 py-10 text-center text-slate-500">${escapeHtml(message || 'No uploaded data yet.')}</td>
                </tr>
            `;
        }
        if (importStatus) {
            importStatus.textContent = message || 'Choose a file to preview uploaded programs.';
        }
        if (importSummary) {
            importSummary.textContent = '';
        }
        if (importSubmit) {
            importSubmit.disabled = true;
        }
    }

    function badgeMarkup(status, message) {
        const label = escapeHtml(message || status || 'Unknown');

        if (status === 'ready') {
            return `<span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">${label}</span>`;
        }
        if (status === 'duplicate_existing' || status === 'duplicate_file') {
            return `<span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">${label}</span>`;
        }

        return `<span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">${label}</span>`;
    }

    function renderImportPreview(payload) {
        const items = Array.isArray(payload?.items) ? payload.items : [];
        const summary = payload?.summary || {};
        const readyRows = Number(summary.ready_rows || 0);
        const totalRows = Number(summary.total_rows || 0);
        const skippedRows = Number(summary.skipped_rows || 0);

        if (importPreviewBody) {
            if (items.length === 0) {
                importPreviewBody.innerHTML = `
                    <tr id="programImportEmptyRow">
                        <td colspan="4" class="px-4 py-10 text-center text-slate-500">No importable rows were found in the uploaded file.</td>
                    </tr>
                `;
            } else {
                importPreviewBody.innerHTML = items.map((item) => `
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500">${escapeHtml(item.row_number)}</td>
                        <td class="px-4 py-3 text-slate-700">${escapeHtml(item.level || '-')}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">${escapeHtml(item.name || '-')}</td>
                        <td class="px-4 py-3">${badgeMarkup(item.status, item.message)}</td>
                    </tr>
                `).join('');
            }
        }

        if (importStatus) {
            importStatus.textContent = readyRows > 0
                ? 'Review the uploaded rows below before saving.'
                : 'No new programs are ready to import from this file.';
        }
        if (importSummary) {
            importSummary.textContent = `${readyRows} ready, ${skippedRows} skipped, ${totalRows} total`;
        }
        if (importSubmit) {
            importSubmit.disabled = readyRows < 1;
        }
    }

    function extractImportError(payload) {
        if (payload?.message) {
            return String(payload.message);
        }

        const errors = payload?.errors || {};
        const firstField = Object.keys(errors)[0];
        if (firstField && Array.isArray(errors[firstField]) && errors[firstField][0]) {
            return String(errors[firstField][0]);
        }

        return 'Unable to preview the uploaded file.';
    }

    async function previewImportFile() {
        if (!importFileInput?.files?.length || !importLevelInput || !importForm) {
            resetImportPreview('Choose a file to preview uploaded programs.');
            return;
        }

        const formData = new FormData();
        formData.append('_token', importForm.querySelector('input[name="_token"]')?.value || '');
        formData.append('program_level', importLevelInput.value || 'COLLEGE');
        formData.append('import_file', importFileInput.files[0]);

        if (importStatus) {
            importStatus.textContent = 'Reading uploaded file...';
        }
        if (importSummary) {
            importSummary.textContent = '';
        }
        if (importSubmit) {
            importSubmit.disabled = true;
        }

        try {
            const response = await fetch(@json(route('admin.courses.preview_import')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(extractImportError(payload));
            }

            renderImportPreview(payload);
        } catch (error) {
            resetImportPreview(error?.message || 'Unable to preview the uploaded file.');
        }
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (levelFilter) levelFilter.addEventListener('change', applyFilters);
    cards.forEach((card) => {
        card.addEventListener('click', () => {
            if (!levelFilter) return;
            const level = String(card.dataset.levelCard || '').toUpperCase();
            levelFilter.value = levelFilter.value === level ? '' : level;
            applyFilters();
        });
    });

    if (addNameInput) addNameInput.addEventListener('input', updateAddFormHints);
    if (addLevelInput) addLevelInput.addEventListener('change', updateAddFormHints);
    if (openImportButton) openImportButton.addEventListener('click', openImportModal);
    if (importFileInput) importFileInput.addEventListener('change', previewImportFile);
    if (importLevelInput) importLevelInput.addEventListener('change', () => {
        if (importFileInput?.files?.length) {
            previewImportFile();
        }
    });

    document.querySelectorAll('.program-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const id = String(button.dataset.id || '').trim();
            if (!id || !editModal || !editForm) return;
            editForm.action = `{{ url('/admin/utilities/courses') }}/${id}`;
            if (editNameInput) editNameInput.value = String(button.dataset.name || '');
            if (editLevelInput) editLevelInput.value = String(button.dataset.level || 'COLLEGE').toUpperCase();
            editModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    });

    document.querySelectorAll('.program-delete-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const id = String(button.dataset.id || '').trim();
            if (!id || !deleteModal || !deleteConfirmForm) return;
            deleteConfirmForm.action = `{{ url('/admin/utilities/courses') }}/${id}`;
            if (deleteProgramName) deleteProgramName.textContent = String(button.dataset.name || 'this program');
            deleteModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    });

    document.querySelectorAll('[data-close-edit]').forEach((button) => button.addEventListener('click', closeEditModal));
    document.querySelectorAll('[data-close-delete]').forEach((button) => button.addEventListener('click', closeDeleteModal));
    document.querySelectorAll('[data-close-import]').forEach((button) => button.addEventListener('click', closeImportModal));

    if (editModal) {
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) closeEditModal();
        });
    }
    if (deleteModal) {
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) closeDeleteModal();
        });
    }
    if (importModal) {
        importModal.addEventListener('click', (event) => {
            if (event.target === importModal) closeImportModal();
        });
    }
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (importModal && !importModal.classList.contains('hidden')) {
            closeImportModal();
            return;
        }
        if (editModal && !editModal.classList.contains('hidden')) {
            closeEditModal();
            return;
        }
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteModal();
        }
    });

    const successAlert = document.getElementById('programSuccessAlert');
    const dismissSuccessButton = document.getElementById('dismissProgramSuccess');
    if (dismissSuccessButton && successAlert) {
        dismissSuccessButton.addEventListener('click', () => successAlert.classList.add('hidden'));
        window.setTimeout(() => successAlert.classList.add('hidden'), 4500);
    }

    updateAddFormHints();
    applyFilters();
    resetImportPreview('Choose a file to preview uploaded programs.');
})();
</script>
@endsection

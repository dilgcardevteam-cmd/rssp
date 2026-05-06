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
                <button id="programAddSubmit" type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#12347f] disabled:cursor-not-allowed disabled:opacity-60">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add program</span>
                </button>
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

    const successAlert = document.getElementById('programSuccessAlert');
    const dismissSuccessButton = document.getElementById('dismissProgramSuccess');
    if (dismissSuccessButton && successAlert) {
        dismissSuccessButton.addEventListener('click', () => successAlert.classList.add('hidden'));
        window.setTimeout(() => successAlert.classList.add('hidden'), 4500);
    }

    updateAddFormHints();
    applyFilters();
})();
</script>
@endsection


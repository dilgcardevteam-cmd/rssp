@extends('layout.admin')
@section('title', 'Positions')
@section('main-padding', 'px-5')

@section('content')
@php
    $adminRole = Auth::guard('admin')->user()->role ?? null;
    $isHrDivisionUser = $adminRole === 'hr_division';
@endphp
<div class="h-full max-h-full w-full font-montserrat flex flex-col pb-4 gap-4 overflow-hidden">
    <div class="border-b border-[#0D2B70] pb-3 flex-none">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-[#0D2B70]">Positions</h1>
                <p class="text-sm text-slate-600 mt-2">
                    Existing positions from created vacancies. Add a new position or edit an existing one.
                </p>
            </div>

            <div class="relative self-start" x-data="{ open: false }" @click.outside="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex h-[42px] items-center gap-2 rounded-full bg-[#0D2B70] px-4 text-sm font-semibold text-white hover:bg-[#0D2B70]/90 transition-colors">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Position</span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-150" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 top-full mt-2 z-30 min-w-[12rem] rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
                    x-cloak>
                    <a href="{{ route('addcos') }}"
                        class="use-loader block px-4 py-2 text-sm font-medium text-[#0D2B70] hover:bg-[#0D2B70]/5 transition-colors">
                        Add COS
                    </a>
                    @unless($isHrDivisionUser)
                        <a href="{{ route('addplantilla') }}"
                            class="use-loader block px-4 py-2 text-sm font-medium text-[#0D2B70] hover:bg-[#0D2B70]/5 transition-colors">
                            Add Plantilla
                        </a>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    @php
        $assignmentOptions = $positions
            ->pluck('place_of_assignment')
            ->filter()
            ->map(fn($v) => trim((string)$v))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    @endphp

    {{-- Search + Filter Card --}}
    <div class="flex-none bg-white border border-slate-200 rounded-2xl shadow-sm px-2 py-3 flex flex-col gap-3">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end">

        {{-- Search --}}
        <div class="min-w-0 flex-1">
            <p class="text-[0.65rem] font-semibold tracking-widest text-slate-400 uppercase mb-1.5">Search</p>
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                <input
                    id="positions-search"
                    name="search"
                    value="{{ $search }}"
                    type="search"
                    placeholder="Search by vacancy ID, job title, salary, or assignment…"
                    class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm text-[#0D2B70] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/30 focus:border-[#0D2B70] bg-slate-50 transition"
                >
            </div>
        </div>

        {{-- Filter Buttons Row --}}
        <div class="flex flex-wrap items-center gap-2 lg:flex-nowrap">

            {{-- Vacancy Type Dropdown (Alpine) --}}
            <div class="relative" id="type-filter-group" x-data="{ open: false, label: 'All' }" @click.outside="open = false">
                <button type="button" @click="open = !open"
                    class="flex h-[42px] items-center gap-2 whitespace-nowrap px-4 rounded-full border border-[#0D2B70] text-[#0D2B70] text-sm font-medium hover:bg-[#0D2B70]/5 transition-colors duration-150 select-none">
                    {{-- Briefcase icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                    </svg>
                    <span x-text="label" class="min-w-[2.5rem] text-center"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 top-full mt-1.5 z-30 bg-white border border-slate-200 rounded-xl shadow-lg py-1 min-w-[8rem]" x-cloak>
                    @foreach(['all' => 'All', 'plantilla' => 'Plantilla', 'cos' => 'COS'] as $val => $lbl)
                    <button type="button" data-type="{{ $val }}"
                        class="type-filter-btn w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-[#0D2B70]/5 transition-colors"
                        @click="label = '{{ $lbl }}'; open = false">
                        {{ $lbl }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Place of Assignment Dropdown (Alpine) --}}
            @if($assignmentOptions->isNotEmpty())
            <div class="relative" x-data="{ open: false, label: 'All' }" @click.outside="open = false">
                <button type="button" @click="open = !open"
                    class="flex h-[42px] items-center gap-2 whitespace-nowrap px-4 rounded-full border border-[#0D2B70] text-[#0D2B70] text-sm font-medium hover:bg-[#0D2B70]/5 transition-colors duration-150 select-none">
                    {{-- Filter icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M13 16h-2" />
                    </svg>
                    <span x-text="label" class="max-w-[10rem] truncate text-center"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 top-full mt-1.5 z-30 bg-white border border-slate-200 rounded-xl shadow-lg py-1 min-w-[12rem] max-h-60 overflow-y-auto" x-cloak>
                    <button type="button" data-assignment=""
                        class="assignment-filter-btn w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-[#0D2B70]/5 transition-colors"
                        @click="label = 'All'; open = false">All</button>
                    @foreach($assignmentOptions as $assignment)
                    <button type="button" data-assignment="{{ strtolower($assignment) }}"
                        class="assignment-filter-btn w-full text-left px-4 py-2 text-sm text-[#0D2B70] hover:bg-[#0D2B70]/5 transition-colors"
                        @click="label = '{{ addslashes($assignment) }}'; open = false">
                        {{ $assignment }}
                    </button>
                    @endforeach
                </div>
            </div>
            @else
            {{-- Placeholder button when no assignments exist --}}
            <div class="relative" x-data="{ open: false }">
                <button type="button"
                    class="flex h-[42px] items-center gap-2 whitespace-nowrap px-4 rounded-full border border-[#0D2B70] text-[#0D2B70] text-sm font-medium opacity-50 cursor-default select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M13 16h-2" />
                    </svg>
                    <span>All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
            @endif

        </div>
        </div>
    </div>
<!-- Table Position -->
    <div class="flex-1 flex flex-col min-h-0 margin-b-600 overflow-hidden border border-[#0D2B70] rounded-xl">
        <div class="flex-none bg-[#0D2B70] text-white">
            <table class="w-full border-collapse table-fixed">
            <!-- Table Header Position -->
                <thead>
                    <tr>
                        <th class="w-[35%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-left">Job Title</th>
                        <th class="w-[17%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-left">Monthly Salary</th>
                        <th class="w-[18%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-left">Last Used</th>
                        <th class="w-[20%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-left">Place of Assignment</th>
                        <th class="w-[10%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="flex-1 overflow-y-auto min-h-0">
            <table class="w-full text-left border-collapse table-fixed">
                <tbody id="positions-tbody" class="divide-y divide-[#0D2B70]">
                    @forelse($positions as $position)
                        <tr class="text-sm text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200"
                            data-search="{{ strtolower(implode(' ', array_filter([(string)$position->vacancy_id, (string)$position->position_title, (string)$position->vacancy_type, $position->monthly_salary !== null ? number_format((float)$position->monthly_salary, 2) : '', (string)$position->place_of_assignment]))) }}"
                            data-type="{{ strtolower((string)$position->vacancy_type) }}"
                            data-assignment="{{ strtolower(trim((string)$position->place_of_assignment)) }}">
                            <td class="w-[35%] px-3 py-2">
                                <p class="font-medium">{{ $position->position_title }}</p>
                                <p class="mt-0.5 text-xs italic text-[#0D2B70]/70">{{ strtoupper((string) $position->vacancy_type) }}</p>
                            </td>
                            <td class="w-[17%] px-3 py-2">
                                {{ $position->monthly_salary !== null ? 'PHP ' . number_format((float) $position->monthly_salary, 2) : 'N/A' }}
                            </td>
                            <td class="w-[18%] px-3 py-2">
                                {{ optional($position->updated_at)->format('F j, Y') ?: 'N/A' }}
                            </td>
                            <td class="w-[20%] px-3 py-2">{{ $position->place_of_assignment ?: 'N/A' }}</td>
                            <td class="w-[10%] px-3 py-2 text-center">
                                <a
                                    href="{{ $isHrDivisionUser || strcasecmp((string) $position->vacancy_type, 'COS') === 0
                                        ? route('addcos', ['reuse_title' => $position->id])
                                        : route('addplantilla', ['reuse_title' => $position->id]) }}"
                                    class="use-loader inline-flex items-center justify-center text-[#0D2B70] py-1 px-3 rounded-md text-xl transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)] hover:scale-110"
                                    aria-label="Edit Position"
                                    title="Edit Position">
                                    <i class="fa-solid fa-pen-to-square h-10 w-10"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-500 text-2xl">
                                <i data-feather="info" class="w-7 h-7 inline-block mr-2 text-gray-400"></i>
                                No positions found
                            </td>
                        </tr>
                    @endforelse
                    <tr id="positions-no-results" class="hidden">
                        <td colspan="5" class="py-10 text-center text-gray-500 text-2xl">
                            <i data-feather="search" class="w-7 h-7 inline-block mr-2 text-gray-400"></i>
                            No positions match your search
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input     = document.getElementById('positions-search');
    const tbody     = document.getElementById('positions-tbody');
    const noResults = document.getElementById('positions-no-results');

    if (!input || !tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));

    let activeType       = 'all';
    let activeAssignment = '';

    function applyFilters() {
        const q = input.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(function (row) {
            const matchSearch     = q === '' || row.dataset.search.includes(q);
            const matchType       = activeType === 'all' || row.dataset.type === activeType;
            const matchAssignment = activeAssignment === '' || row.dataset.assignment === activeAssignment;
            const visible         = matchSearch && matchType && matchAssignment;

            row.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        if (noResults) {
            noResults.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }
    }

    // Search input
    input.addEventListener('input', function () {
        applyFilters();
        const url = new URL(window.location.href);
        this.value.trim() ? url.searchParams.set('search', this.value.trim()) : url.searchParams.delete('search');
        history.replaceState(null, '', url.toString());
    });

    // Type filter buttons (inside Alpine dropdown)
    document.addEventListener('click', function (e) {
        const typeBtn = e.target.closest('.type-filter-btn');
        if (typeBtn) {
            activeType = typeBtn.dataset.type;
            applyFilters();
        }
        const assignBtn = e.target.closest('.assignment-filter-btn');
        if (assignBtn) {
            activeAssignment = assignBtn.dataset.assignment;
            applyFilters();
        }
    });

    // Apply on load
    applyFilters();
});
</script>
@endpush

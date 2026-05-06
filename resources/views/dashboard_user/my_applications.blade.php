@extends('layout.app')

@section('title', 'My Applications')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .application-search-spinner {
            animation: application-search-spin 0.7s linear infinite;
            transform-origin: center;
        }

        @keyframes application-search-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    @php
        $selectedSearch = request('search', '');
        $selectedPlace = request('place', '');
        $selectedVacancyType = request('vacancy_type', '');
        $selectedStatus = request('status', '');
        $selectedSortOrder = request('sort_order', 'latest');
        $hasActiveFilters = filled($selectedSearch) || filled($selectedPlace) || filled($selectedVacancyType) || filled($selectedStatus);
        $placeOptions = [
            'DILG-CAR',
            'DILG-CAR Regional Office',
            'Apayao Provincial Office',
            'Abra Provincial Office',
            'Mountain Province Provincial Office',
            'Ifugao Provincial Office',
            'Kalinga Provincial Office',
            'Benguet Provincial Office',
            'Baguio City Office',
        ];
    @endphp

    <div class="px-4 pb-8 sm:px-8">
        <!-- Header Section -->
        <div class="flex-none flex items-center mb-6 sm:mb-10 pace-x-4 max-w-full">
            <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-2xl sm:text-4xl font-montserrat py-2 tracking-wide select-none">
                <span class="whitespace-nowrap text-[#0D2B70]">My Applications</span>
            </h1>
        </div>

        <section x-data="{ filtersOpen: true }" class="mb-4 rounded-2xl border border-[#0D2B70]/15 bg-white/80 p-3 sm:p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">Filters</h2>
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    :aria-expanded="filtersOpen.toString()"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-[#0D2B70] hover:text-[#173f96]"
                >
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'"></span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': filtersOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div x-show="filtersOpen" x-transition class="mt-3">
                <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.5fr)_repeat(4,minmax(0,1fr))]">
                    <div>
                        <label for="applicationSearchInput" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">
                            Search
                        </label>
                        <div class="relative">
                            <input
                                id="applicationSearchInput"
                                type="search"
                                value="{{ $selectedSearch }}"
                                placeholder="Search by job title or vacancy ID"
                                aria-label="Search by job title or vacancy ID"
                                class="w-full rounded-lg border border-[#0D2B70]/20 py-2 pl-10 pr-10 text-sm text-[#0D2B70] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                            </svg>
                            <span id="applicationSearchSpinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-[#0D2B70]" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="application-search-spinner h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-90" d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                                </svg>
                            </span>
                        </div>
                        <p id="applicationSearchStatus" class="sr-only" aria-live="polite"></p>
                    </div>

                    <div>
                        <label for="myApplicationsPlaceFilter" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">
                            Place of Assignment
                        </label>
                        <select id="myApplicationsPlaceFilter" class="w-full rounded-lg border border-[#0D2B70]/20 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                            <option value="">All Places</option>
                            @foreach ($placeOptions as $place)
                                <option value="{{ $place }}" @selected($selectedPlace === $place)>{{ $place }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="myApplicationsTypeFilter" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">
                            COS / Plantilla
                        </label>
                        <select id="myApplicationsTypeFilter" class="w-full rounded-lg border border-[#0D2B70]/20 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                            <option value="">All Types</option>
                            @foreach (($filterOptions['vacancyTypes'] ?? collect()) as $vacancyType)
                                <option value="{{ $vacancyType }}" @selected(strtolower($selectedVacancyType) === strtolower($vacancyType))>{{ $vacancyType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="myApplicationsStatusFilter" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">
                            Status
                        </label>
                        <select id="myApplicationsStatusFilter" class="w-full rounded-lg border border-[#0D2B70]/20 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                            <option value="">All Statuses</option>
                            @foreach (($filterOptions['statuses'] ?? collect()) as $statusOption)
                                <option value="{{ $statusOption }}" @selected(strtolower($selectedStatus) === strtolower($statusOption))>{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sortMyApplications" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">
                            Sort
                        </label>
                        <select id="sortMyApplications" class="w-full rounded-lg border border-[#0D2B70]/20 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                            <option value="latest" @selected($selectedSortOrder === 'latest')>Latest</option>
                            <option value="oldest" @selected($selectedSortOrder === 'oldest')>Oldest</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 flex justify-end">
                    <button id="resetMyApplicationsFilters" type="button" class="text-xs font-semibold text-[#0D2B70] underline underline-offset-2 hover:text-[#173f96]">
                        Reset filters
                    </button>
                </div>
            </div>
        </section>
            
        <!-- Application List -->
        <div id="applicationListContainer" class="space-y-6 application-list-mobile">
            @include('partials.application_list_container', ['applications' => $applications, 'hasActiveFilters' => $hasActiveFilters])
        </div>
        
        @include('partials.loader')
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const applicationListContainer = document.getElementById('applicationListContainer');
    const searchInput = document.getElementById('applicationSearchInput');
    const placeFilter = document.getElementById('myApplicationsPlaceFilter');
    const vacancyTypeFilter = document.getElementById('myApplicationsTypeFilter');
    const statusFilter = document.getElementById('myApplicationsStatusFilter');
    const sortFilter = document.getElementById('sortMyApplications');
    const resetButton = document.getElementById('resetMyApplicationsFilters');
    const searchSpinner = document.getElementById('applicationSearchSpinner');
    const searchStatus = document.getElementById('applicationSearchStatus');

    if (!applicationListContainer || !searchInput || !placeFilter || !vacancyTypeFilter || !statusFilter || !sortFilter) {
        return;
    }

    let debounceTimer = null;
    let activeController = null;
    let latestRequestId = 0;
    let debouncePending = false;
    let searchRequestInFlight = false;

    const setSearchIndicator = (message = '') => {
        const shouldShowSpinner = debouncePending || searchRequestInFlight;
        searchSpinner?.classList.toggle('hidden', !shouldShowSpinner);
        if (searchStatus) {
            searchStatus.textContent = message;
        }
    };

    const buildParams = () => {
        const params = new URLSearchParams();
        const search = searchInput.value.trim();

        if (search !== '') {
            params.set('search', search);
        }

        if (placeFilter.value !== '') {
            params.set('place', placeFilter.value);
        }

        if (vacancyTypeFilter.value !== '') {
            params.set('vacancy_type', vacancyTypeFilter.value);
        }

        if (statusFilter.value !== '') {
            params.set('status', statusFilter.value);
        }

        if (sortFilter.value !== 'latest') {
            params.set('sort_order', sortFilter.value);
        }

        return params;
    };

    const syncUrl = () => {
        const params = buildParams();
        const queryString = params.toString();
        const nextUrl = queryString ? `{{ route('my_applications') }}?${queryString}` : `{{ route('my_applications') }}`;
        window.history.replaceState({}, '', nextUrl);
    };

    const fetchApplications = async (options = {}) => {
        const initiatedBySearch = options.initiatedBySearch === true;
        const requestId = ++latestRequestId;

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        debouncePending = false;
        searchRequestInFlight = initiatedBySearch;
        setSearchIndicator(initiatedBySearch ? 'Loading applications...' : '');
        syncUrl();

        try {
            const params = buildParams();
            const requestUrl = params.toString()
                ? `{{ route('my_applications.sort') }}?${params.toString()}`
                : `{{ route('my_applications.sort') }}`;

            const response = await fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            if (requestId !== latestRequestId) {
                return;
            }

            applicationListContainer.innerHTML = html;
            feather.replace();
            setSearchIndicator(initiatedBySearch ? 'Applications updated.' : '');
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('Failed to load applications:', error);
            setSearchIndicator(initiatedBySearch ? 'Failed to load applications.' : '');

            if (typeof showAppToast === 'function') {
                showAppToast('Failed to load applications.');
            }
        } finally {
            if (requestId === latestRequestId) {
                searchRequestInFlight = false;
                activeController = null;
                setSearchIndicator(initiatedBySearch ? (searchStatus?.textContent || '') : '');
            }
        }
    };

    const scheduleSearch = () => {
        debouncePending = true;
        setSearchIndicator('Searching applications...');

        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => {
            fetchApplications({ initiatedBySearch: true });
        }, 350);
    };

    searchInput.addEventListener('input', scheduleSearch);
    placeFilter.addEventListener('change', () => fetchApplications());
    vacancyTypeFilter.addEventListener('change', () => fetchApplications());
    statusFilter.addEventListener('change', () => fetchApplications());
    sortFilter.addEventListener('change', () => fetchApplications());

    resetButton?.addEventListener('click', () => {
        searchInput.value = '';
        placeFilter.value = '';
        vacancyTypeFilter.value = '';
        statusFilter.value = '';
        sortFilter.value = 'latest';
        window.clearTimeout(debounceTimer);
        debouncePending = false;
        searchRequestInFlight = false;
        setSearchIndicator('');
        fetchApplications();
    });
})();
</script>
@endpush

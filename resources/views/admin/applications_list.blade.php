@extends('layout.admin')
@section('title', 'DILG - Applications List')
@section('content')

<main class="w-full h-full min-h-0 flex flex-col gap-4 overflow-hidden pb-4">

    <!-- Header with back arrow and title -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1
            class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Applications List</span>
        </h1>
    </section>

    <section class="flex-none mt-1 w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
        <form onsubmit="return false;" class="relative w-full no-spinner" data-loading-handled="1">
            <div class="grid w-full gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative sm:col-span-2 lg:col-span-3">
                    <label for="searchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Search
                    </label>
                    <input id="searchInput" type="search" placeholder="Search by vacancy ID or job title" aria-label="Search"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </div>
                <div>
                    <label for="statusFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Status
                    </label>
                    <select aria-label="Filter by Status" id="statusFilter"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="">All</option>
                        <option value="open">OPEN</option>
                        <option value="closed">CLOSED</option>
                    </select>
                </div>
            </div>
        </form>
    </section>

    <!-- Table Container -->
    <div class="mb-4 w-full min-w-0 flex-1 flex flex-col min-h-0 overflow-hidden rounded-xl border border-[#0D2B70]">
        <!-- HEADER - Fixed outside scrollable area -->
        <div class="flex-none bg-[#0D2B70] text-white">
            <table class="w-full text-left border-collapse table-fixed">
                <thead>
                    <tr>
                        <th class="w-[15%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider">Vacancy ID</th>
                        <th class="w-[30%] px-3 py-2 text-[11px] font-semibold uppercase tracking-wider">Job Title</th>
                        <th class="w-[15%] px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">Status</th>
                        <th class="w-[40%] px-3 py-2 text-center text-[11px] font-semibold uppercase tracking-wider">Manage Applicants</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!-- SCROLLABLE BODY CONTAINER -->
        <div class="flex-1 overflow-y-auto min-h-0">
            <table class="w-full align-items-center text-left border-collapse table-fixed">
                <tbody id="vacancy-list" class="divide-y divide-[#0D2B70]">
                    @forelse ($vacancies as $vacancy)
                        @php
                            $normalizedVacancyType = strtoupper(trim((string) ($vacancy->vacancy_type ?? '')));
                            $isPlantillaVacancy = $normalizedVacancyType === 'PLANTILLA';
                            $vacancyTypeLabel = $normalizedVacancyType === 'COS'
                                ? 'Contract of Service'
                                : ($vacancy->vacancy_type ?? '');
                        @endphp
                        <tr class="text-sm text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                            <td class="w-[15%] px-3 py-2">{{ $vacancy->vacancy_id }}</td>

                            <td class="w-[30%] px-3 py-2">
                                <p class="font-medium">{{ $vacancy->position_title }}</p>
                                <p class="mt-0.5 text-xs italic text-[#0D2B70]/70">
                                    {{ $vacancyTypeLabel }}
                                </p>
                            </td>

                            <td class="w-[15%] px-3 py-2 text-left">
                                <div class="flex items-center justify-start gap-1.5 font-normal">
                                    @php
                                        $statusColor = match (strtolower($vacancy->status)) {
                                            'open' => 'bg-green-600',
                                            'closed' => 'bg-red-600',
                                            default => 'bg-gray-400'
                                        };
                                    @endphp

                                    <span class="inline-block h-2.5 w-2.5 rounded-full {{ $statusColor }}"></span>
                                    <span class="text-xs font-semibold uppercase">
                                        {{ $vacancy->status }}
                                    </span>
                                </div>
                            </td>

                            <td class="w-[40%] px-3 py-2 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=new"
                                    class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                    <span>New</span>
                                    @if(isset($vacancy->pending_count) && $vacancy->pending_count > 0)
                                        <span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-red-600 text-[9px] font-bold text-white shadow-sm">
                                            {{ $vacancy->pending_count }}
                                        </span>
                                    @endif
                                    </a>
                                    <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=compliance"
                                    class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                    <span>Compliance</span>
                                    @if(isset($vacancy->compliance_count) && $vacancy->compliance_count > 0)
                                        <span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-orange-500 text-[9px] font-bold text-white shadow-sm">
                                            {{ $vacancy->compliance_count }}
                                        </span>
                                    @endif
                                    </a>
                                    <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=qualified"
                                    class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                    <span>Qualified</span>
                                    @if(isset($vacancy->qualified_count) && $vacancy->qualified_count > 0)
                                        <span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-green-600 text-[9px] font-bold text-white shadow-sm">
                                            {{ $vacancy->qualified_count }}
                                        </span>
                                    @endif
                                    </a>
                                    @if($isPlantillaVacancy)
                                        <a href="{{ route('admin.manage_applicants', ['vacancy_id' => $vacancy->vacancy_id]) }}?tab=no-pqe"
                                        class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                        <span>No PQE</span>
                                        @if(isset($vacancy->no_pqe_count) && $vacancy->no_pqe_count > 0)
                                            <span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-slate-600 text-[9px] font-bold text-white shadow-sm">
                                                {{ $vacancy->no_pqe_count }}
                                            </span>
                                        @endif
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-5 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p>No job vacancies found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                
                <!-- Optional: Add bottom padding for better scrolling experience -->
                <tfoot>
                    <tr>
                        <td colspan="4" class="h-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>           
    @include('partials.loader')
</main>

    <script>
        // Debounce Function to prevent traffic overload
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const searchForm = searchInput?.form ?? null;
        const vacancyListContainer = document.getElementById('vacancy-list');
        const applicationsListUrl = @json(route('applications_list'));
        const hrAccessStateUrl = @json(route('admin.applications_list.access_state'));
        const isHrDivisionUser = @json($isHrDivisionUser ?? false);
        let latestHrAccessSignature = @json($accessSignature ?? '');
        let hrAccessPollTimer = null;
        let hrAccessPollController = null;
        let hrAccessPollInFlight = false;
        let accessRefreshLocked = false;

        function getSearchAndStatus() {
            return {
                search: searchInput.value.trim(),
                status: statusFilter.value.trim()
            };
        }

        // Debounced Search Handler (500ms delay)
        const handleSearch = debounce(function () {
            const { search, status } = getSearchAndStatus();
            fetchVacancies(search, status);
        }, 500);

        searchInput.addEventListener('input', handleSearch);
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
        if (searchForm) {
            searchForm.addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                const overlay = document.getElementById('loader');
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('pds-loading-nonblocking');
                    overlay.setAttribute('aria-busy', 'false');
                }
            });
        }

        statusFilter.addEventListener('change', function () {
            const { search, status } = getSearchAndStatus();
            fetchVacancies(search, status);
        });


        function fetchVacancies(search = '', status = '') {
            const params = new URLSearchParams({
                search: search,
                status: status
            });

            fetch(`${applicationsListUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => renderVacancies(data))
                .catch(error => console.error('Error:', error));
        }

        function renderVacancies(vacancies) {
            const container = vacancyListContainer;
            container.innerHTML = '';

            if (vacancies.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500 text-xl">
                            No applications found.
                        </td>
                    </tr>
                `;
                return;
            }

            vacancies.forEach(vacancy => {
                const vacancyTypeRaw = String(vacancy.vacancy_type ?? '');
                const normalizedVacancyType = vacancyTypeRaw.trim().toUpperCase();
                const isPlantillaVacancy = normalizedVacancyType === 'PLANTILLA';
                const vacancyTypeLabel = normalizedVacancyType === 'COS' ? 'Contract of Service' : vacancyTypeRaw;
                const statusColor = {
                    'open': 'bg-green-600',
                    'closed': 'bg-red-600'
                }[vacancy.status?.toLowerCase()] ?? 'bg-gray-400';

                container.innerHTML += `
                <tr class="text-sm text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                    <td class="w-[15%] px-3 py-2 text-left">${vacancy.vacancy_id}</td>
                    <td class="w-[30%] px-3 py-2 text-left">
                        <p>${vacancy.position_title}</p>
                        <p class="text-xs italic text-[#0D2B70]/70">${vacancyTypeLabel}</p>
                    </td>
                    <td class="w-[15%] px-3 py-2 text-left">
                        <div class="flex items-center justify-start gap-1.5 font-normal">
                            <span class="inline-block h-2.5 w-2.5 rounded-full ${statusColor}"></span>
                            <span class="text-xs font-semibold uppercase">${vacancy.status}</span>
                        </div>
                    </td>
                    <td class="w-[40%] px-3 py-2 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=new" class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                <span>New</span>
                                ${vacancy.pending_count > 0 ? `<span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-red-600 text-[9px] font-bold text-white shadow-sm">${vacancy.pending_count}</span>` : ''}
                            </a>
                            <a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=compliance" class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                <span>Compliance</span>
                                ${vacancy.compliance_count > 0 ? `<span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-orange-500 text-[9px] font-bold text-white shadow-sm">${vacancy.compliance_count}</span>` : ''}
                            </a>
                            <a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=qualified" class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                <span>Qualified</span>
                                ${vacancy.qualified_count > 0 ? `<span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-green-600 text-[9px] font-bold text-white shadow-sm">${vacancy.qualified_count}</span>` : ''}
                            </a>
                            ${isPlantillaVacancy ? `<a href="/admin/manage_applicants/${vacancy.vacancy_id}?tab=no-pqe" class="relative group use-loader inline-flex h-8 w-24 items-center justify-center rounded-md border border-[#0D2B70] text-xs font-bold text-[#0D2B70] transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md">
                                <span>No PQE</span>
                                ${vacancy.no_pqe_count > 0 ? `<span class="absolute -right-2 -top-2 z-10 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-slate-600 text-[9px] font-bold text-white shadow-sm">${vacancy.no_pqe_count}</span>` : ''}
                            </a>` : ''}
                        </div>
                    </td>
                </tr>`;
            });
        }

        function triggerAccessRefresh() {
            if (accessRefreshLocked) return;
            accessRefreshLocked = true;

            if (vacancyListContainer) {
                vacancyListContainer.innerHTML = `
                    <tr>
                        <td colspan="4" class="py-10 px-6 text-center text-[#0D2B70]">
                            <div class="inline-flex items-center gap-2 rounded-full border border-[#0D2B70]/20 bg-[#0D2B70]/5 px-4 py-2 text-sm font-semibold">
                                Access updated by superadmin. Refreshing list...
                            </div>
                        </td>
                    </tr>
                `;
            }

            const overlay = document.getElementById('loader');
            const liveRegion = document.getElementById('loader-live');
            const loaderText = document.getElementById('loader-text');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.remove('pds-loading-nonblocking');
                overlay.setAttribute('aria-busy', 'true');
            }
            if (liveRegion) liveRegion.textContent = 'Refreshing access...';
            if (loaderText) loaderText.textContent = 'Refreshing access...';

            window.location.replace(`${applicationsListUrl}?access_updated=${Date.now()}`);
        }

        async function pollHrDivisionAccessState() {
            if (!isHrDivisionUser || accessRefreshLocked || hrAccessPollInFlight) return;
            hrAccessPollInFlight = true;
            hrAccessPollController = new AbortController();

            try {
                const response = await fetch(`${hrAccessStateUrl}?_=${Date.now()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-store'
                    },
                    cache: 'no-store',
                    signal: hrAccessPollController.signal
                });

                if (!response.ok) return;
                const payload = await response.json();
                if (!payload || !payload.is_hr_division) return;

                const nextSignature = String(payload.access_signature || '');
                if (latestHrAccessSignature !== '' && nextSignature !== latestHrAccessSignature) {
                    triggerAccessRefresh();
                    return;
                }

                latestHrAccessSignature = nextSignature;
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    console.error('Access state polling error:', error);
                }
            } finally {
                hrAccessPollInFlight = false;
            }
        }

        if (isHrDivisionUser) {
            pollHrDivisionAccessState();
            hrAccessPollTimer = window.setInterval(pollHrDivisionAccessState, 1200);

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    pollHrDivisionAccessState();
                }
            });

            window.addEventListener('beforeunload', () => {
                if (hrAccessPollTimer) {
                    clearInterval(hrAccessPollTimer);
                    hrAccessPollTimer = null;
                }
                if (hrAccessPollController) {
                    hrAccessPollController.abort();
                }
            });
        }
    </script>
@endsection

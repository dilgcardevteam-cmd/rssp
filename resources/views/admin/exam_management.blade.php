@extends('layout.admin')
@section('title', 'DILG - Admin Exam Management')
@section('content')

<main class="w-full h-full min-h-0 flex flex-col gap-4 overflow-hidden">
    @php
        $isViewerMode = (bool) ($isViewer ?? ((Auth::guard('admin')->user()->role ?? null) === 'viewer'));
    @endphp

    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Exam Management</span>
        </h1>
    </section>

    <div class="flex flex-1 min-h-0 overflow-hidden flex-col gap-4">
        <!-- search, filters, and exam library button -->
        <section class="flex-none mt-1 w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
            <form onsubmit="return false;" class="relative w-full no-spinner" data-loading-handled="1">
                <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="grid w-full gap-3 md:grid-cols-4">
                        <div class="relative md:col-span-2">
                            <label for="examIdFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Search
                            </label>
                            <input
                                id="examIdFilter"
                                type="text"
                                placeholder="Search by Job Title or ID"
                                aria-label="Search by Job Title or ID"
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-10 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                            />
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"
                                />
                            </svg>
                            <svg id="examSearchMiniLoader"
                                class="pointer-events-none absolute right-3 top-[31px] hidden h-4 w-4 animate-spin text-[#0D2B70]"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </div>

                        <div>
                            <label for="jobTypeFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Job Type
                            </label>
                            <select id="jobTypeFilter"
                                    {{ $isViewerMode ? 'disabled' : '' }}
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                                @if($isViewerMode)
                                    <option value="" selected>All</option>
                                @else
                                    <option value="">All</option>
                                    <option value="COS">COS</option>
                                    <option value="Plantilla">Plantilla</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label for="examStatusFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </label>
                            <select id="examStatusFilter"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                                @if($isViewerMode)
                                    <option value="" selected>All</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
                                @else
                                    <option value="">All</option>
                                    <option value="Unscheduled">Unscheduled</option>
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    @if(!$isViewerMode)
                        <div class="shrink-0 lg:pl-2">
                            <button onclick="window.location.href='{{ route('admin.exam_library') }}'" type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-5 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white lg:min-w-[160px]">
                                Exam Library
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </section>

        <!-- TABLE -->
        <div class="mb-4 w-full min-w-0 flex-1 flex flex-col min-h-0 overflow-hidden rounded-xl border border-[#0D2B70]">
            <div class="flex-none bg-[#0D2B70] text-white">
                <table class="w-full text-left border-collapse table-fixed">
                    <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                    <tr>
                        <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Vacancy ID</th>
                        <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[35%]">Job Title</th>
                        <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[20%]">Job Type</th>
                        <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Status</th>
                        <th class="py-4 px-6 text-center font-bold uppercase text-sm tracking-wider w-[15%]">Action</th>
                    </tr>
                </thead>
                </table>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left border-collapse table-fixed">
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($vacancies as $vacancy)
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class=" px-6 text-center text-[#0D2B70] font-semibold w-[15%]">
                                {{ $vacancy->vacancy_id }}
                            </td>
                            <td class=" px-6 text-center text-[#0D2B70] font-medium w-[35%]">
                                {{ $vacancy->position_title }}
                            </td>
                            <td class=" px-6 text-center text-[#0D2B70] w-[20%]">
                                {{ $vacancy->vacancy_type }}
                            </td>
                            <td class=" px-6 text-center w-[15%]">
                                @php
                                    $statusClass = 'bg-gray-100 text-gray-800 border border-gray-400';
                                    $statusText  = 'Not Scheduled';
                                    $isOngoing   = false;
    
                                    if ($vacancy->exam_status === 'Scheduled') {
                                        $statusClass = 'bg-blue-100 text-blue-800 border border-blue-400';
                                        $statusText  = 'Exam Scheduled';
                                    } elseif ($vacancy->exam_status === 'Ongoing') {
                                        $statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-400';
                                        $statusText  = 'Exam in Progress';
                                        $isOngoing   = true;
                                    } elseif ($vacancy->exam_status === 'Completed') {
                                        $statusClass = 'bg-green-100 text-green-800 border border-green-400';
                                        $statusText  = 'Exam Completed';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    @if($isOngoing)
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                                        </span>
                                    @endif
                                    <span>{{ $statusText }}</span>
                                </span>
                            </td>
                            <td class=" px-6 text-center w-[15%]">
                                @php
                                    $canMonitor = !$isViewerMode || in_array($vacancy->exam_status, ['Ongoing', 'Completed']);
                                @endphp
                                <button 
                                    @if($canMonitor)
                                        onclick="window.location.href='{{ route('admin.manage_exam', $vacancy->vacancy_id) }}'"
                                    @else
                                        disabled
                                    @endif
                                    class="text-[#0D2B70] border border-[#0D2B70] font-bold py-2 px-6 rounded-md text-sm
                                    transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                    {{ $canMonitor ? 'hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md' : 'opacity-50 cursor-not-allowed' }}"
                                >
                                    {{ $isViewerMode ? 'Monitor' : 'Manage' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if($vacancies->isEmpty())
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-500 font-medium">
                                No records found.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const isViewerMode = @json($isViewerMode);
        const searchInput = document.getElementById('examIdFilter');
        const searchForm = searchInput?.form ?? null;
        const searchMiniLoader = document.getElementById('examSearchMiniLoader');
        const jobTypeFilter = document.getElementById('jobTypeFilter');
        const examStatusFilter = document.getElementById('examStatusFilter');
        let pendingSearchFetches = 0;

        function setSearchLoading(isLoading) {
            if (!searchMiniLoader) return;
            if (isLoading) {
                searchMiniLoader.classList.remove('hidden');
            } else {
                searchMiniLoader.classList.add('hidden');
            }
        }

        // DEBOUNCE FUNCTION WAG IDELETE PLS
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Debounced Search Handler (500ms delay)
        const handleSearch = debounce(function() {
            fetchVacancies();
        }, 500);

        // Listener for Search Input
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

        // Listener for Job Type Dropdown
        jobTypeFilter.addEventListener('change', function() {
            fetchVacancies();
        });

        // Listener for Exam Status Dropdown
        examStatusFilter.addEventListener('change', function() {
            fetchVacancies();
        });

        function fetchVacancies() {
            const query = searchInput.value;
            const jobType = isViewerMode ? '' : jobTypeFilter.value;
            const examStatus = isViewerMode ? 'Ongoing' : examStatusFilter.value;

            // Build query parameters
            const params = new URLSearchParams();
            if (query) params.append('search', query);
            if (jobType) params.append('job_type', jobType);
            if (examStatus) params.append('exam_status', examStatus);

            pendingSearchFetches += 1;
            setSearchLoading(true);

            fetch(`/admin/exam_management?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                renderVacancies(data);
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                pendingSearchFetches = Math.max(0, pendingSearchFetches - 1);
                if (pendingSearchFetches === 0) {
                    setSearchLoading(false);
                }
            });
        }

        function renderVacancies(vacancies) {
            const container = document.querySelector('tbody');
            container.innerHTML = '';

            if (vacancies.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500 font-medium">
                            No records found.
                        </td>
                    </tr>
                `;
                return;
            }

            vacancies.forEach(vacancy => {
                let statusClass = 'bg-gray-100 text-gray-800 border border-gray-400';
                let statusText  = 'Not Scheduled';
                let pingHtml    = '';

                if (vacancy.exam_status === 'Scheduled') {
                    statusClass = 'bg-blue-100 text-blue-800 border border-blue-400';
                    statusText  = 'Exam Scheduled';
                } else if (vacancy.exam_status === 'Ongoing') {
                    statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-400';
                    statusText  = 'Exam in Progress';
                    pingHtml = `
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>`;
                } else if (vacancy.exam_status === 'Completed') {
                    statusClass = 'bg-green-100 text-green-800 border border-green-400';
                    statusText  = 'Exam Completed';
                }

                container.innerHTML += `
                <tr class="hover:bg-blue-50 transition-colors duration-200">
                    <td class="py-4 px-6 text-center text-[#0D2B70] font-semibold w-[15%]">
                        ${vacancy.vacancy_id}
                    </td>
                    <td class="py-4 px-6 text-center text-[#0D2B70] font-medium w-[35%]">
                        ${vacancy.position_title}
                    </td>
                    <td class="py-4 px-6 text-center text-[#0D2B70] w-[20%]">
                        ${vacancy.vacancy_type}
                    </td>
                    <td class="py-4 px-6 text-center w-[15%]">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold ${statusClass}">
                            ${pingHtml}${statusText}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center w-[15%]">
                        <button 
                                ${isViewerMode && !['Ongoing', 'Completed'].includes(vacancy.exam_status) ? 'disabled' : `onclick="window.location.href='/admin/exam_management/${encodeURIComponent(vacancy.vacancy_id)}/manage'"`} 
                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-2 px-6 rounded-md text-sm
                                transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]
                                ${isViewerMode && !['Ongoing', 'Completed'].includes(vacancy.exam_status) ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md'}">
                            ${isViewerMode ? 'Monitor' : 'Manage'}
                        </button>
                    </td>
                </tr>
                `;
            });
        }
    </script>
    @include('partials.loader')
</main>

@endsection

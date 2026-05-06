<!-- resources/views/dashboard_user/job_vacancy.blade.php -->

@extends('layout.app')

@section('title', 'Job Vacancies')

@section('content')
    <div class="px-4 pb-3 sm:px-8 h-[calc(100vh-8rem)] md:h-[calc(100dvh-8rem)] flex flex-col">
<!-- Updated HTML with mobile classes -->
        <!-- Header Section -->
            <div class="flex-none flex items-center mb-4 sm:mb-5 space-x-4 max-w-full">
                <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-2xl sm:text-3xl font-montserrat py-1.5 tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">Search Job Vacancies</span>
                </h1>
            </div>

<!-- Sorting & Filtering -->
<section x-data="{ filtersOpen: true }" class="flex-none mb-3 rounded-2xl border border-[#0D2B70]/20 bg-white/70 p-2.5 sm:p-3 shadow-sm">
    <div class="flex items-center justify-between">
        <h2 class="text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">Filters</h2>
        <button type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()"
            class="inline-flex items-center gap-1 text-xs font-semibold text-[#0D2B70] hover:text-[#173f96]">
            <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'"></span>
            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': filtersOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>

    <div x-show="filtersOpen" x-transition class="mt-2">
    <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_1.3fr_0.9fr] gap-2">
        <div class="space-y-2">
            <div>
                <label for="searchInput" class="block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70] mb-0.5">Search</label>
                <form class="relative" onsubmit="return false;">
                    <input id="searchInput" type="search" placeholder="Search by title"
                        aria-label="Search vacancies"
                        class="pl-10 pr-4 py-2 rounded-lg w-full border border-[#0D2B70]/25 placeholder:text-slate-400 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>
            </div>
            <div>
                <label for="placeFilter" class="block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70] mb-0.5">Place of Assignment</label>
                <select id="placeFilter"
                    class="w-full rounded-lg border border-[#0D2B70]/25 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                    <option value="">All Places</option>
                    <option value="DILG-CAR Regional Office">DILG-CAR Regional Office</option>
                    <option value="Apayao Provincial Office">Apayao Provincial Office</option>
                    <option value="Abra Provincial Office">Abra Provincial Office</option>
                    <option value="Mountain Province Provincial Office">Mountain Province Provincial Office</option>
                    <option value="Ifugao Provincial Office">Ifugao Provincial Office</option>
                    <option value="Kalinga Provincial Office">Kalinga Provincial Office</option>
                    <option value="Benguet Provincial Office">Benguet Provincial Office</option>
                    <option value="Baguio City Office">Baguio City Office</option>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <div>
                <label for="salaryFilter" class="block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70] mb-0.5">Monthly Salary</label>
                <select id="salaryFilter"
                    class="w-full rounded-lg border border-[#0D2B70]/25 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                    <option value="">All Salaries</option>
                    <option value="10-20">&#8369;10,000 - &#8369;20,000</option>
                    <option value="20-30">&#8369;20,001 - &#8369;30,000</option>
                    <option value="30-40">&#8369;30,001 - &#8369;40,000</option>
                    <option value="40-50">&#8369;40,001 - &#8369;50,000</option>
                    <option value="50-60">&#8369;50,001 - &#8369;60,000</option>
                    <option value="60-70">&#8369;60,001 - &#8369;70,000</option>
                    <option value="70-80">&#8369;70,001 - &#8369;80,000</option>
                    <option value="80-90">&#8369;80,001 - &#8369;90,000</option>
                    <option value="90-100">&#8369;90,001 - &#8369;100,000</option>
                    <option value="100-1000">&#8369;100,000+</option>
                </select>
            </div>
            <div>
                <label for="typeFilter" class="block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70] mb-0.5">Vacancy Type</label>
                <select id="typeFilter"
                    class="w-full rounded-lg border border-[#0D2B70]/25 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                    <option value="">All Types</option>
                    <option value="COS">Contract of Service</option>
                    <option value="Plantilla">Plantilla</option>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <div>
                <label for="sortFilter" class="block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70] mb-0.5">Sort</label>
                <select id="sortFilter"
                    class="w-full rounded-lg border border-[#0D2B70]/25 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                    <option value="latest" selected>Latest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
            <div>
                <label for="statusFilter" class="block text-[10px] font-bold uppercase tracking-wide text-[#0D2B70] mb-0.5">Status</label>
                <select id="statusFilter"
                    class="w-full rounded-lg border border-[#0D2B70]/25 bg-white px-3 py-2 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/35">
                    <option value="" selected>All</option>
                    <option value="OPEN">Open</option>
                    <option value="CLOSED">Closed</option>
                </select>
            </div>
        </div>
    </div>
    <div class="mt-1 text-right">
        <button id="clearFilters" type="button" class="text-[#0D2B70] text-xs underline underline-offset-2 hover:text-[#173f96]">
            Reset Filters
        </button>
    </div>
    </div>
</section>

<!-- Job Vacancies List -->
<div class="bg-white rounded-xl border border-[#0D2B70] shadow-sm overflow-x-auto overflow-y-hidden flex flex-col flex-1 min-h-0" style="--vacancy-cols: 28% 12% 19% 16% 11% 14%;">
    <div class="min-w-[980px] flex flex-col flex-1 min-h-0">
        <!-- Desktop Header -->
        <div class="hidden lg:grid flex-none bg-[#0D2B70] text-white text-xs font-bold uppercase tracking-wider sticky top-0 z-10" style="grid-template-columns: var(--vacancy-cols);">
            <div class="py-3 px-4">Job Title</div>
            <div class="py-3 px-4">Salary</div>
            <div class="py-3 px-4">Place of Assignment</div>
            <div class="py-3 px-4 text-center">Deadline</div>
            <div class="py-3 px-4 text-center">Status</div>
            <div class="py-3 px-4 text-center">Actions</div>
        </div>

        <!-- List Container -->
        <div id="vacancy-list" class="flex-1 overflow-y-auto divide-y divide-gray-200 lg:divide-blue-100">
            @include('partials.vacancy_list', ['vacancies' => $vacancies])
        </div>
    </div>
</div>
</div>

@include('partials.loader')

        <script>
            function debounce(fn, ms) { let t; return function(){ clearTimeout(t); const a=arguments, self=this; t=setTimeout(function(){ fn.apply(self,a); }, ms); }; }
            function fetchVacancies() {
                const status = document.getElementById('statusFilter').value;
                const sort = document.getElementById('sortFilter').value;
                const type = document.getElementById('typeFilter').value;
                const salary = document.getElementById('salaryFilter').value;
                const place = document.getElementById('placeFilter').value;
                const search = document.getElementById('searchInput').value.trim();
                const loader = document.getElementById('loader');
                loader?.classList.remove('hidden');

                // Build query parameters
                const params = new URLSearchParams({
                    status: status,
                    sort: sort,
                    type: type,
                    salary: salary,
                    place: place,
                    search: search
                });

                fetch(`/job-vacancies/filter?${params.toString()}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('vacancy-list').innerHTML = html;
                        feather.replace();
                        loader?.classList.add('hidden');
                    })
                    .catch(() => {
                        showAppToast('Failed to load vacancies.');
                        loader?.classList.add('hidden');
                    });
            }
            const fetchVacanciesDebounced = debounce(fetchVacancies, 300);

            function attachFilterListeners() {
                document.getElementById('statusFilter')?.addEventListener('change', fetchVacancies);
                document.getElementById('sortFilter')?.addEventListener('change', fetchVacancies);
                document.getElementById('typeFilter')?.addEventListener('change', fetchVacancies);
                document.getElementById('salaryFilter')?.addEventListener('change', fetchVacancies);
                document.getElementById('placeFilter')?.addEventListener('change', fetchVacancies);
                document.getElementById('searchInput')?.addEventListener('input', fetchVacanciesDebounced);
                document.getElementById('clearFilters')?.addEventListener('click', () => {
                    document.getElementById('searchInput').value = '';
                    document.getElementById('sortFilter').value = 'latest';
                    document.getElementById('statusFilter').value = '';
                    document.getElementById('typeFilter').value = '';
                    document.getElementById('salaryFilter').value = '';
                    document.getElementById('placeFilter').value = '';
                    fetchVacancies();
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    attachFilterListeners();
                    fetchVacancies();
                });
            } else {
                attachFilterListeners();
                fetchVacancies();
            }
        </script>

@endsection

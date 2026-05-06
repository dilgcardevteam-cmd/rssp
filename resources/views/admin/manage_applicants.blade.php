@extends('layout.admin')
@section('title', 'DILG - Manage Applicants')
@section('content')

    <main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

        <!-- Header with back arrow and title -->
        <section class="flex-none flex items-center space-x-4 max-w-full">
            <button aria-label="Back" onclick="window.location.href='{{ route('applications_list') }}'" class="group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <div class="flex items-center justify-between w-full border-b border-[#0D2B70] py-2">
                <h1 class="text-white text-4xl font-montserrat tracking-wide select-none">
                    <span class="whitespace-nowrap text-[#0D2B70]">Applicants Management</span>
                </h1>
            </div>
        </section>

       <!-- Position Title -->
        <div class="flex-none flex gap-2 px-5">
            <div class="text-left">
                <p class="text-[#0D2B70] text-xl font-semibold">
                    {{ $positionTitle ?? 'Vacancy ' . $vacancyId }}
                    @if(!empty($vacancyType) || !empty($placeOfAssignment))
                        <span class="text-[#0D2B70]/70 text-sm italic font-normal">
                            • {{ $vacancyType ?? '' }}{{ !empty($vacancyType) && !empty($placeOfAssignment) ? ' • ' : '' }}{{ $placeOfAssignment ?? '' }}
                        </span>
                    @endif
                </p>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="flex-none flex gap-2 border-b border-[#0D2B70]">
            <button id="tab-new" onclick="switchTab('new')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-[#0D2B70] bg-blue-50 transition-all duration-200">
                New Applicants
                @if($newApplicantsCount > 0)
                    <span class="ml-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $newApplicantsCount }}
                    </span>
                @endif
            </button>
            <button id="tab-compliance" onclick="switchTab('compliance')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-transparent hover:bg-blue-50 transition-all duration-200">
                Compliance
                @if($complianceApplicantsCount > 0)
                    <span class="ml-2 bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $complianceApplicantsCount }}
                    </span>
                @endif
            </button>
            <button id="tab-qualified" onclick="switchTab('qualified')"
                class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-transparent hover:bg-blue-50 transition-all duration-200">
                Qualified Applicants
                @if($qualifiedApplicantsCount > 0)
                    <span class="ml-2 bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $qualifiedApplicantsCount }}
                    </span>
                @endif
            </button>
            @if($showNoPqeTab ?? false)
                <button id="tab-no-pqe" onclick="switchTab('no-pqe')"
                    class="tab-button px-6 py-3 font-semibold text-[#0D2B70] border-b-4 border-transparent hover:bg-blue-50 transition-all duration-200">
                    No PQE
                    @if(($noPqeApplicantsCount ?? 0) > 0)
                        <span class="ml-2 bg-slate-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $noPqeApplicantsCount }}
                        </span>
                    @endif
                </button>
            @endif
        </div>

        <!-- Tab Content: New Applicants -->
        <div id="content-new" class="tab-content flex-1 flex flex-col min-h-0 overflow-hidden">
            <!-- Search and Sort - Both Aligned Left -->
            <div class="flex-none flex flex-wrap items-end gap-6 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative">
                    <input id="searchInputNew" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-[300px]" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <!-- Sort Dropdown -->
                <div class="flex flex-col gap-1">
                    <label for="sortOrderNew" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderNew"
                        class="rounded-md text-[#0D2B70] py-1.5 px-3 font-semibold cursor-pointer border border-[#0D2B70] w-[150px]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[20%]">Name</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Job Applied</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[15%]">Status</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-center w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="new-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($newApplicants as $applicant)
                                @php
                                    $statusKey = strtolower(trim((string) ($applicant['status'] ?? '')));
                                    $statusClass = match ($statusKey) {
                                        'cancelled' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-yellow-100 text-yellow-800',
                                    };
                                    $isLockedStatus = in_array($statusKey, ['cancelled', 'closed'], true);
                                @endphp
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[20%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-left w-[15%]">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center w-[15%]">
                                        @if(!$isLockedStatus)
                                            <button
                                                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                                        No new applicants found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Compliance -->
        <div id="content-compliance" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <!-- Search and Sort - Both Aligned Left -->
            <div class="flex-none flex flex-wrap items-end gap-6 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative">
                    <input id="searchInputCompliance" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-[300px]" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <!-- Sort Dropdown -->
                <div class="flex flex-col gap-1">
                    <label for="sortOrderCompliance" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderCompliance"
                        class="rounded-md text-[#0D2B70] py-1.5 px-3 font-semibold cursor-pointer border border-[#0D2B70] w-[150px]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[20%]">Name</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Job Applied</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[15%]">Status</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-center w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="compliance-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($complianceApplicants as $applicant)
                                @php
                                    $statusKey = strtolower(trim((string) ($applicant['status'] ?? '')));
                                    $statusClass = match ($statusKey) {
                                        'cancelled' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-orange-100 text-orange-800',
                                    };
                                    $isLockedStatus = in_array($statusKey, ['cancelled', 'closed'], true);
                                @endphp
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[20%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-left w-[15%]">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center w-[15%]">
                                        @if(!$isLockedStatus)
                                            <button
                                                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                                        No applicants in compliance.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Qualified Applicants -->
        <div id="content-qualified" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <!-- Search and Sort - Both Aligned Left -->
            <div class="flex-none flex flex-wrap items-end gap-6 mb-4">
                <!-- Search Bar -->
                <form onsubmit="return false;" class="relative">
                    <input id="searchInputQualified" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-[300px]" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <!-- Sort Dropdown -->
                <div class="flex flex-col gap-1">
                    <label for="sortOrderQualified" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderQualified"
                        class="rounded-md text-[#0D2B70] py-1.5 px-3 font-semibold cursor-pointer border border-[#0D2B70] w-[150px]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[30%]">Name</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[30%]">Job Applied</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-center w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="qualified-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse ($qualifiedApplicants as $applicant)
                                @php
                                    $statusKey = strtolower(trim((string) ($applicant['status'] ?? '')));
                                    $isLockedStatus = in_array($statusKey, ['cancelled', 'closed'], true);
                                @endphp
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[30%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[30%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-center w-[15%]">
                                        @if(!$isLockedStatus)
                                            <button
                                                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-gray-500 text-xl">
                                        No qualified applicants found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($showNoPqeTab ?? false)
        <div id="content-no-pqe" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="flex-none flex flex-wrap items-end gap-6 mb-4">
                <form onsubmit="return false;" class="relative">
                    <input id="searchInputNoPqe" type="search" placeholder="Search applicants" aria-label="Search"
                        class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-[300px]" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <div class="flex flex-col gap-1">
                    <label for="sortOrderNoPqe" class="font-semibold text-[#0D2B70] text-sm">Sort By</label>
                    <select aria-label="Sort by date" id="sortOrderNoPqe"
                        class="rounded-md text-[#0D2B70] py-1.5 px-3 font-semibold cursor-pointer border border-[#0D2B70] w-[150px]">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                            <tr>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[20%]">Name</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Job Applied</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[25%]">Place of Assignment</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-left w-[15%]">Status</th>
                                <th class="py-4 px-6 font-bold uppercase text-sm tracking-wider text-center w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="no-pqe-applicants-list" class="divide-y divide-[#0D2B70]">
                            @forelse (($noPqeApplicants ?? []) as $applicant)
                                @php
                                    $statusKey = strtolower(trim((string) ($applicant['status'] ?? '')));
                                    $isLockedStatus = in_array($statusKey, ['cancelled', 'closed'], true);
                                @endphp
                                <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                    <td class="py-4 px-6 text-left w-[20%]">{{ $applicant['name'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['job_applied'] }}</td>
                                    <td class="py-4 px-6 text-left w-[25%]">{{ $applicant['place_of_assignment'] }}</td>
                                    <td class="py-4 px-6 text-left w-[15%]">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $applicant['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center w-[15%]">
                                        @if(!$isLockedStatus)
                                            <button
                                                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                                        No applicants without PQE found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @include('partials.loader')
    </main>

    <script>
        const vacancyId = "{{ $vacancyId }}";
        const showNoPqeTab = @json((bool) ($showNoPqeTab ?? false));
        const allowedTabs = showNoPqeTab ? ['new', 'compliance', 'qualified', 'no-pqe'] : ['new', 'compliance', 'qualified'];

        // Tab switching
        function switchTab(tab, updateHistory = true) {
            console.log('Switching to tab:', tab); // Debugging

            // Update URL query param without full reload
            if (updateHistory) {
                const url = new URL(window.location);
                if (url.searchParams.get('tab') !== tab) {
                    url.searchParams.set('tab', tab);
                    window.history.pushState({}, '', url);
                }
            }

            allowedTabs.forEach(t => {
                const tabBtn = document.getElementById(`tab-${t}`);
                const content = document.getElementById(`content-${t}`);

                if (!tabBtn || !content) return;

                if (t === tab) {
                    tabBtn.classList.add('border-[#0D2B70]', 'bg-blue-50');
                    tabBtn.classList.remove('border-transparent');
                    content.classList.remove('hidden');
                    content.classList.add('flex');
                } else {
                    tabBtn.classList.remove('border-[#0D2B70]', 'bg-blue-50');
                    tabBtn.classList.add('border-transparent');
                    content.classList.add('hidden');
                    content.classList.remove('flex');
                }
            });
        }

        // Handle Back/Forward Browser Buttons
        window.addEventListener('popstate', function (event) {
            const params = new URLSearchParams(window.location.search);
            // Default to 'new' if no tab param exists
            const tab = (params.get('tab') || 'new').trim().toLowerCase();
            if (allowedTabs.includes(tab)) {
                switchTab(tab, false); // false = don't push state again
            } else {
                switchTab('new', false);
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            // Get tab from URL, trim whitespace, lowercase, and default to empty string if null
            const initialTab = (params.get('tab') || '').trim().toLowerCase();

            console.log('Initial Tab from URL:', initialTab); // Debugging

            if (allowedTabs.includes(initialTab)) {
                switchTab(initialTab);
            } else {
                switchTab('new');
            }
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // New Applicants - Search and Sort
        const searchInputNew = document.getElementById('searchInputNew');
        const sortOrderNew = document.getElementById('sortOrderNew');

        const handleNewApplicantsFilter = debounce(function () {
            const search = searchInputNew.value.trim();
            const sortOrder = sortOrderNew.value;
            fetchNewApplicants(search, sortOrder);
        }, 500);

        if (searchInputNew) searchInputNew.addEventListener('input', handleNewApplicantsFilter);
        if (sortOrderNew) sortOrderNew.addEventListener('change', handleNewApplicantsFilter);

        function fetchNewApplicants(search = '', sortOrder = 'latest') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                sort_order: sortOrder
            });

            fetch(`/admin/manage_applicants/new?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('new-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        // Compliance Applicants - Search and Sort
        const searchInputCompliance = document.getElementById('searchInputCompliance');
        const sortOrderCompliance = document.getElementById('sortOrderCompliance');

        const handleComplianceApplicantsFilter = debounce(function () {
            const search = searchInputCompliance.value.trim();
            const sortOrder = sortOrderCompliance.value;
            fetchComplianceApplicants(search, sortOrder);
        }, 500);

        if (searchInputCompliance) searchInputCompliance.addEventListener('input', handleComplianceApplicantsFilter);
        if (sortOrderCompliance) sortOrderCompliance.addEventListener('change', handleComplianceApplicantsFilter);

        function fetchComplianceApplicants(search = '', sortOrder = 'latest') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                sort_order: sortOrder
            });

            fetch(`/admin/manage_applicants/compliance?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('compliance-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        // Qualified Applicants - Search and Sort
        const searchInputQualified = document.getElementById('searchInputQualified');
        const sortOrderQualified = document.getElementById('sortOrderQualified');

        const handleQualifiedApplicantsFilter = debounce(function () {
            const search = searchInputQualified.value.trim();
            const sortOrder = sortOrderQualified.value;
            fetchQualifiedApplicants(search, sortOrder);
        }, 500);

        if (searchInputQualified) searchInputQualified.addEventListener('input', handleQualifiedApplicantsFilter);
        if (sortOrderQualified) sortOrderQualified.addEventListener('change', handleQualifiedApplicantsFilter);

        function fetchQualifiedApplicants(search = '', sortOrder = 'latest') {
            const params = new URLSearchParams({
                vacancy_id: vacancyId,
                search: search,
                sort_order: sortOrder
            });

            fetch(`/admin/manage_applicants/qualified?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('qualified-applicants-list').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        if (showNoPqeTab) {
            const searchInputNoPqe = document.getElementById('searchInputNoPqe');
            const sortOrderNoPqe = document.getElementById('sortOrderNoPqe');

            const handleNoPqeApplicantsFilter = debounce(function () {
                const search = searchInputNoPqe.value.trim();
                const sortOrder = sortOrderNoPqe.value;
                fetchNoPqeApplicants(search, sortOrder);
            }, 500);

            if (searchInputNoPqe) searchInputNoPqe.addEventListener('input', handleNoPqeApplicantsFilter);
            if (sortOrderNoPqe) sortOrderNoPqe.addEventListener('change', handleNoPqeApplicantsFilter);

            function fetchNoPqeApplicants(search = '', sortOrder = 'latest') {
                const params = new URLSearchParams({
                    vacancy_id: vacancyId,
                    search: search,
                    sort_order: sortOrder
                });

                fetch(`/admin/manage_applicants/no-pqe?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('no-pqe-applicants-list').innerHTML = html;
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>

@endsection
@forelse ($vacancies as $vacancy)
    <div class="flex flex-col lg:grid lg:items-center text-[#0D2B70] hover:bg-blue-50 transition-colors duration-200 p-5 lg:p-0 border-b border-gray-100 lg:border-none relative group text-sm" style="grid-template-columns: var(--vacancy-cols, 28% 12% 19% 16% 11% 14%);">

        <!-- Job Title -->
        <div class="lg:py-2.5 lg:px-4 mb-3 lg:mb-0">
            @php
                $vacancyTypeRaw = trim((string) ($vacancy->vacancy_type ?? ''));
                $vacancyTypeLabel = strcasecmp($vacancyTypeRaw, 'cos') === 0
                    ? 'Contract of Service'
                    : ($vacancyTypeRaw !== '' ? $vacancyTypeRaw : '');

                $effectiveVacancyStatus = strtoupper(trim((string) ($vacancy->status ?? '')));
            @endphp
            <p
                class="font-bold text-base lg:text-sm lg:font-medium leading-tight block lg:truncate lg:whitespace-nowrap lg:overflow-hidden"
                title="{{ $vacancy->position_title }}"
            >{{ $vacancy->position_title }}</p>
            <p class="text-[#0D2B70]/70 text-xs italic mt-0.5">{{ $vacancyTypeLabel }}</p>
        </div>

        <!-- Salary -->
        <div class="lg:py-2.5 lg:px-3 mb-2 lg:mb-0 flex items-center lg:block">
            <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-24 shrink-0">Salary</span>
            <span class="font-medium text-xs lg:text-sm">&#8369;{{ number_format($vacancy->monthly_salary, 2) }}</span>
        </div>

        <!-- Place -->
        <div class="lg:py-2.5 lg:px-10 mb-2 lg:mb-0 flex items-center lg:block">
            <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-24 shrink-0">Assignment</span>
            <span class="text-xs sm:text-sm lg:text-xs block lg:truncate lg:whitespace-nowrap lg:overflow-hidden"
                title="{{ $vacancy->place_of_assignment }}">
                {{ $vacancy->place_of_assignment }}
            </span>
        </div>

        <!-- Deadline -->
        <div class="lg:py-2.5 lg:px-10 mb-2 lg:mb-0 flex items-center lg:justify-center lg:block">
            <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-24 shrink-0">Deadline</span>
            <div class="flex lg:flex-col lg:items-center gap-2 lg:gap-0">
                @php
                    $closing = \Carbon\Carbon::parse($vacancy->closing_date);
                    $daysLeft = now()->diffInDays($closing, false);
                    $isUrgent = $effectiveVacancyStatus === 'OPEN' && $daysLeft >= 0 && $daysLeft <= 7;
                @endphp
                <div class="flex flex-row items-center gap-2">
                    @if($isUrgent)
                        <span
                            class="inline-flex items-center justify-center text-red-800 bg-red-100 ring-1 ring-red-300 rounded-full p-1 animate-pulse shadow-sm"
                            title="Expiring Soon"
                            aria-label="Expiring Soon"
                        >
                            <i data-feather="alert-circle" class="w-4 h-4"></i>
                        </span>
                    @endif
                    <span class="font-semibold text-xs lg:text-xs">{{ $closing->format('M d, Y') }}</span>
                </div>

            </div>
        </div>

        <!-- Status -->
        <div class="lg:py-2.5 lg:px-10 mb-2 lg:mb-0 flex items-center lg:justify-center">
            <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-24 shrink-0">Status</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $effectiveVacancyStatus === 'OPEN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $effectiveVacancyStatus }}
            </span>
        </div>

        <!-- Actions -->
        <div class="lg:py-2.5 lg:px-10 mt-2 lg:mt-0 flex lg:justify-center w-full">
            <button
                onclick="window.location.href='{{ route('job_description', $vacancy->vacancy_id) }}'"
                class="use-loader w-full lg:w-auto justify-center text-[#0D2B70] border border-[#0D2B70] font-bold py-2.5 lg:py-1 px-3 rounded-lg text-xs transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md inline-flex items-center gap-2 group-hover:bg-[#0D2B70] group-hover:text-white lg:group-hover:bg-transparent lg:group-hover:text-[#0D2B70] lg:hover:!bg-[#0D2B70] lg:hover:!text-white">
                <i data-feather="eye" class="w-4 h-4"></i>
                <span>View</span>
            </button>
        </div>
    </div>
@empty
    <div class="text-center py-10 text-gray-500 text-xl flex flex-col items-center justify-center h-full min-h-[200px]">
        <div class="bg-gray-100 p-4 rounded-full mb-3">
            <i data-feather="inbox" class="w-8 h-8 text-gray-400"></i>
        </div>
        <span class="font-semibold">No Job Vacancy Found</span>
        <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filters.</p>
    </div>
@endforelse


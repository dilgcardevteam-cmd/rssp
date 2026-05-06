@php
    $hasActiveFilters = $hasActiveFilters ?? false;
@endphp

@if ($applications->count() > 0)
<div class="rounded-xl border border-[#0D2B70] mt-2 overflow-hidden flex flex-col min-h-0 bg-white shadow-sm">
    <div class="bg-[#0D2B70] text-white flex-none hidden lg:block">
        <div class="flex items-center text-sm font-bold uppercase tracking-wider">
            <div class="py-4 px-6 w-[20%]">Vacancy No.</div>
            <div class="py-4 px-6 w-[30%]">Position Title</div>
            <div class="py-4 px-6 w-[25%]">Place of Assignment</div>
            <div class="py-4 px-6 w-[10%] text-center">Status</div>
            <div class="py-4 px-6 w-[15%] text-center">Actions</div>
        </div>
    </div>
    
    <div class="flex-1 overflow-auto max-h-[70vh] lg:max-h-[60vh]">
        <div class="divide-y divide-gray-200 lg:divide-[#0D2B70]">
            @foreach ($applications as $application)
                <div class="flex flex-col lg:flex-row lg:items-center text-[#0D2B70] hover:bg-blue-50 transition-colors duration-200 p-5 lg:p-0">
                    <!-- Vacancy No -->
                    <div class="lg:py-4 lg:px-6 lg:w-[20%] mb-2 lg:mb-0 flex items-center lg:block">
                        <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-32 shrink-0">Vacancy No.</span>
                        <span class="font-semibold">{{ $application->vacancy_id ?? $application->vacancy->vacancy_id ?? 'N/A' }}</span>
                    </div>

                    <!-- Position -->
                    <div class="lg:py-4 lg:px-6 lg:w-[30%] mb-2 lg:mb-0">
                        <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide block mb-1">Position</span>
                        @php
                            $vacTypeRaw = trim((string) ($application->vacancy->vacancy_type ?? ''));
                            $vacTypeLabel = strcasecmp($vacTypeRaw, 'cos') === 0
                                ? 'Contract of Service'
                                : ($vacTypeRaw !== '' ? $vacTypeRaw : '');
                        @endphp
                        <p class="font-bold lg:font-medium">{{ $application->vacancy->position_title ?? 'Position Title Unavailable' }}</p>
                        <p class="text-[#0D2B70]/70 text-sm lg:text-[0.9rem] italic">{{ $vacTypeLabel }}</p>
                    </div>

                    <!-- Place -->
                    <div class="lg:py-4 lg:px-6 lg:w-[25%] mb-2 lg:mb-0 flex items-center lg:block">
                        <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-32 shrink-0">Assignment</span>
                        <span class="text-sm lg:text-base">{{ $application->vacancy->place_of_assignment ?? 'N/A' }}</span>
                    </div>

                    <!-- Status -->
                    <div class="lg:py-4 lg:px-6 lg:w-[10%] mb-4 lg:mb-0 flex items-center lg:justify-center">
                        <span class="lg:hidden text-xs font-bold text-slate-400 uppercase tracking-wide w-32 shrink-0">Status</span>
                        @php
                            $rawStatus = (string) ($application->status ?? '');
                            $statusNormalized = strtolower(trim($rawStatus));
                            $displayStatus = match ($statusNormalized) {
                                'submitted', 'in-progress', 'completed', 'complete' => 'Completed',
                                'compliance', 'needs revision', 'disapproved with deficiency' => 'Needs Revision',
                                'pending' => 'Pending',
                                'cancelled' => 'Cancelled',
                                'closed' => 'Closed',
                                default => trim($rawStatus) !== '' ? $rawStatus : 'Pending',
                            };
                            $displayStatusNormalized = strtolower(trim($displayStatus));
                            $isNotQualified = $statusNormalized === 'not qualified';
                            $isCancelled = $statusNormalized === 'cancelled' || $statusNormalized === 'closed';
                            $badge = 'bg-gray-100 text-gray-800';
                            if ($displayStatusNormalized === 'completed') $badge = 'bg-green-100 text-green-800';
                            elseif ($displayStatusNormalized === 'needs revision') $badge = 'bg-orange-100 text-orange-800';
                            elseif ($displayStatusNormalized === 'closed') $badge = 'bg-red-100 text-red-800';
                            elseif ($displayStatusNormalized === 'pending') $badge = 'bg-yellow-100 text-yellow-800';
                            elseif ($isNotQualified) $badge = 'bg-red-100 text-red-800';
                            elseif ($isCancelled) $badge = 'bg-red-100 text-red-800';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                            {{ $displayStatus }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="lg:py-4 lg:px-6 lg:w-[15%] flex lg:justify-center gap-2">
                        @if($isNotQualified)
                            <button
                                type="button"
                                disabled
                                class="w-full lg:w-auto justify-center text-gray-400 border border-gray-300 font-bold py-2.5 lg:py-1 px-4 rounded-md text-sm inline-flex items-center gap-2 cursor-not-allowed opacity-70">
                                <i data-feather="lock" class="w-4 h-4"></i>
                                <span>Closed</span>
                            </button>
                        @else
                            <button
                                onclick="window.location.href='{{ route('application_status', [$application->user_id, $application->vacancy_id]) }}'"
                                class="use-loader w-full lg:w-auto justify-center text-[#0D2B70] border border-[#0D2B70] font-bold py-2.5 lg:py-1 px-4 rounded-md text-sm transition-all 
                                duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md inline-flex items-center gap-2">
                                <i data-feather="eye" class="w-4 h-4"></i>
                                <span>View</span>
                            </button>
                            
                            @if($displayStatusNormalized === 'pending')
                            <form id="cancel-application-form-{{ $application->vacancy_id }}" method="POST" action="{{ route('application_status.cancel', ['user' => $application->user_id, 'vacancy' => $application->vacancy_id]) }}">
                                @csrf
                                <button type="button" onclick="if(confirm('Cancel this application? This action cannot be undone.')) document.getElementById('cancel-application-form-{{ $application->vacancy_id }}').submit();" class="w-full lg:w-auto justify-center text-red-600 border border-red-500 font-bold py-2.5 lg:py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-red-500 hover:text-white hover:shadow-md inline-flex items-center gap-2">
                                    <i data-feather="x-circle" class="w-4 h-4"></i>
                                    <span>Cancel</span>
                                </button>
                            </form>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else
    <div class="text-center text-gray-600 font-montserrat text-lg mt-10">
        <i data-feather="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-400"></i>
        @if ($hasActiveFilters)
            <p class="font-semibold">No applications matched your filters.</p>
            <p class="text-sm text-gray-500">Try a different search term or clear one of the dropdown filters.</p>
        @else
            <p class="font-semibold">No applications yet.</p>
            <p class="text-sm text-gray-500">Browse available job vacancies and apply to get started!</p>
        @endif
    </div>
@endif

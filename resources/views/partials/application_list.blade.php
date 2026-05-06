@php
    use Carbon\Carbon;

    $isPastDue = false;

    if (!empty($application->deadline_date) && !empty($application->deadline_time)) {
        $deadline = Carbon::parse($application->deadline_date . ' ' . $application->deadline_time);
        $now = Carbon::now();
        $isPastDue = $now->greaterThan($deadline);
    }
@endphp

<div class="border-4 {{ $application['status'] === 'Closed' ? 'border-gray-400' : 'border-blue-950' }} rounded-lg p-5 flex flex-col justify-between">
    <div class="flex justify-between items-start">
        <h2 class="mb-2 text-xl sm:text-3xl font-extrabold font-montserrat text-[#002C76] {{ $application['status'] === 'Closed' ? 'text-gray-500' : '' }}">
            {{ $application->vacancy->position_title ?? 'Position Title Unavailable' }}
            <span class="text-sm {{ $application['status'] === 'Closed' ? 'text-gray-500' : '' }} font-montserrat">
                ({{ $application->vacancy->vacancy_type ?? '' }})
            </span>
        </h2>

        @if ($isPastDue)
            <span class="flex items-center gap-1 text-xs sm:text-sm font-montserrat text-red-500 font-bold">
                <i data-feather="alert-triangle" class="w-4 h-4"></i>
                Deadline Passed
            </span>
        @endif
    </div>

    <p class="font-montserrat text-sm sm:text-base {{ $application['status'] === 'Closed' ? 'text-gray-500' : '' }}">
        <span class="font-bold text-sm sm:text-base">PLACE OF ASSIGNMENT:</span> {{ $application->vacancy->place_of_assignment ?? 'N/A' }}
    </p>
    <p class="font-montserrat text-sm sm:text-base {{ $application['status'] === 'Closed' ? 'text-gray-500' : '' }}">
        <span class="font-bold text-sm sm:text-base">COMPENSATION:</span> ₱{{ number_format($application->vacancy->monthly_salary ?? 0, 2) }}
    </p>

    <div class="flex items-center justify-between mt-4">
        <span class="text-sm sm:text-base font-semibold font-montserrat
            {{ $application['status'] === 'Incomplete' ? 'text-orange-500' : '' }}
            {{ $application['status'] === 'Complete' ? 'text-green-500' : '' }}
            {{ $application['status'] === 'Closed' ? 'text-red-500' : '' }}
            {{ $application['status'] === 'Pending' ? 'text-yellow-600' : '' }}
            {{ strtolower(trim((string) ($application['status'] ?? ''))) === 'cancelled' ? 'text-red-500' : '' }}">
            STATUS: {{ $application->status }}
        </span>

        <button onclick="window.location.href='{{ route('application_status', [$application->user_id, $application->vacancy_id]) }}'"
            class="use-loader border border-red-400 font-montserrat text-black font-semibold px-4 py-2 rounded-full text-xs sm:text-sm shadow-md flex items-center gap-3 hover:bg-red-400 hover:text-white transition">
            <i data-feather="eye" class="w-5 h-5 text-black-400"></i> View Status
        </button>
    </div>
</div>

<script>
    feather.replace();
</script>

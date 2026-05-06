<div class="bg-white border-4 border-[#002C76] rounded-xl p-6 shadow-md space-y-2 relative w-full">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#002C76] font-montserrat">
        {{ $vacancy->position_title }}
        @php
            $rawType = strtolower(trim($vacancy->vacancy_type));
            $displayType = match ($rawType) {
                'plantilla' => 'Plantilla Item',
                'cos' => 'Contract of Service',
                default => $vacancy->vacancy_type,
            };
        @endphp

    <span class="font-semibold text-gray-500 text-lg">({{ $displayType }})</span>

    </h2>
    <p class="font-montserrat text-black font-bold text-lg">Monthly Salary:
        ₱{{ number_format($vacancy->monthly_salary, 2) }}</p>
    <p class="font-montserrat text-black font-semibold text-sm sm:text-base">Place of Assignment:
        {{ $vacancy->place_of_assignment }}
    </p>
    <div class="flex items-center gap-2 mt-2">
        <span
            class="flex items-center gap-1 {{ $vacancy->status === 'OPEN' ? 'text-green-600' : 'text-red-600' }} font-bold text-sm">
            <span
                class="w-3 h-3 {{ $vacancy->status === 'OPEN' ? 'bg-green-600' : 'bg-red-600' }} rounded-full inline-block"></span>
            {{ $vacancy->status }}
        </span>
        <span class="text-gray-500 text-base">Closes
            {{ \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('n/j/Y g:i A') }},</span>
        <span class="text-gray-500 text-base">Last Updated
            {{ date('n/j/Y g:i A', strtotime($vacancy->updated_at)) }}</span>
    </div>
    <a href="{{ route('job_description', $vacancy->vacancy_id) }}"
        class="use-loader mt-3 inline-flex items-center gap-2 rounded-full bg-gray-200 text-[#002C76] px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-300 transition w-fit">
        <i data-feather="eye" class="w-4 h-4"></i> View this Job Vacancy
    </a>
</div>

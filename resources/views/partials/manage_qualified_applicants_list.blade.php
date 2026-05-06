@forelse ($applicants as $applicant)
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

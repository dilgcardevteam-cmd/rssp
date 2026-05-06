@forelse ($applicants as $applicant)
    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
        <td class="py-4 px-6">{{ $applicant['name'] }}</td>
        <td class="py-4 px-6">{{ $applicant['job_applied'] }}</td>
        <td class="py-4 px-6">{{ $applicant['place_of_assignment'] }}</td>
        <td class="py-4 px-6 text-center">
            @php
                $statusClass = match ($applicant['status']) {
                    'Complete' => 'bg-green-100 text-green-800',
                    'Incomplete' => 'bg-yellow-100 text-yellow-800',
                    'Closed' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                {{ $applicant['status'] }}
            </span>
        </td>
        <td class="py-4 px-6 text-center">
            <button
                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                <x-heroicon-o-eye class="w-4 h-4" />
                <span>View</span>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
            No reviewed applicants found.
        </td>
    </tr>
@endforelse
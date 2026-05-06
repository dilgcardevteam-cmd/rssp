
            <div class="border-4 {{ $applicant['status'] === 'Closed' ? 'border-gray-400' : 'border-blue-950' }} rounded-lg p-5 flex flex-col justify-between">
                <h2 class="text-3xl font-extrabold font-montserrat text-[#002C76] {{ $applicant['status'] === 'Closed' ? 'text-gray-500' : '' }}">
                    {{ $applicant['name'] }}
                </h2>

                <p class="font-montserrat {{ $applicant['status'] === 'Closed' ? 'text-gray-500' : '' }}">
                    <span class="font-bold">Job Applied:</span> {{ $applicant['job_applied'] }}
                </p>

                <p class="font-montserrat {{ $applicant['status'] === 'Closed' ? 'text-gray-500' : '' }}">
                    <span class="font-bold">Place of Assignment:</span> {{ $applicant['place_of_assignment'] }}
                </p>

                <div class="flex items-center justify-between mt-4">
                    <span class="font-semibold font-montserrat
                        {{ $applicant['status'] === 'Incomplete' ? 'text-orange-500' : '' }}
                        {{ $applicant['status'] === 'Complete' ? 'text-green-500' : '' }}
                        {{ $applicant['status'] === 'Closed' ? 'text-red-500' : '' }}
                        {{ $applicant['status'] === 'Pending' ? 'text-yellow-600' : '' }}">
                        STATUS: {{ $applicant['status'] }}
                    </span>

                    <button onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                        class="use-loader border border-red-400 font-montserrat text-black font-semibold px-4 py-2 rounded-full text-sm shadow-md flex items-center gap-3 hover:bg-red-400 hover:text-white transition {{ $applicant['status'] === 'Closed' ? 'hover:bg-gray-400' : '' }}">
                        <i data-feather="eye" class="w-5 h-5 text-black-400"></i> View Status
                    </button>
                </div>
            </div>

        // In your Blade view, temporarily add this to debug
        {{-- Debugging --}}
        <pre style="display: none;">
            @php
                \Log::info('New Applicants:', $newApplicants->toArray());
            @endphp
        </pre>

        <!-- Or display on screen temporarily -->
        @foreach($newApplicants as $applicant)
            <div style="display: none;">
                Name: {{ $applicant['name'] }} 
                Applicant ID: {{ $applicant['applicant_code'] ?? $applicant['user_id'] }}
            </div>
@endforeach

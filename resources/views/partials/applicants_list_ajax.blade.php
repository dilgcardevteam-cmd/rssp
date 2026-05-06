@forelse($applicants as $applicant)
    @include('partials.applicant_list', ['applicant' => $applicant])
@empty
    <div class="text-center text-gray-500 font-semibold text-2xl mt-10">
        <i data-feather="user-x" class="w-6 h-6 inline-block mr-2 text-gray-400"></i>
        No applicants for this job.
    </div>
@endforelse

@extends('layout.admin')

@section('title', 'All Applicants Profile')

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DILG - All Applicants Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>

@section('content')
<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen w-full">
        <!-- Main Content -->
        <main class="w-full max-w-7xl space-y-6">
            <!-- Header with Back Button and Title -->
            <section class="flex items-center gap-4">
                <button aria-label="Go back" title="Go back" onclick="window.location.href='{{ route('applications_list') }}'"
                    class="w-12 h-12 rounded-full bg-[#D8DCE3] flex justify-center items-center text-[#1E3664] hover:bg-[#c0c7d8] transition">
                    <!-- Back arrow icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="#1E3664"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <h1
                    class="flex items-center gap-3 w-full bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold font-montserrat px-8 py-4 tracking-wide select-none">
                    <!-- Bootstrap Person icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        class="bi bi-person" viewBox="0 0 16 16">
                        <path
                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                    </svg>
                    <span class="whitespace-nowrap">ALL APPLICANTS PROFILE</span>
                </h1>
            </section>

            <section class="flex flex-wrap gap-4">
                <p class="text-lg font-bold font-montserrat text-black-600">SORT</p>
                <select id="sortOrder" class="border border-gray-300 rounded-lg px-4 py-2 text-sm font-montserrat">
                    <option value="latest">LATEST</option>
                    <option value="oldest">OLDEST</option>
                </select>
            </section>

            <!-- Add a container around your applicants list for replacement -->
            <section id="applicantList" class="space-y-6">
                @forelse($applicants as $applicant)
                    @include('partials.applicant_list', ['applicant' => $applicant])
                @empty
                    <div class="text-center text-gray-500 font-semibold text-2xl mt-10">
                        <i data-feather="user-x" class="w-6 h-6 inline-block mr-2 text-gray-400"></i>
                        No applicants for this job.
                    </div>
                @endforelse
            </section>
            @include('partials.loader')
        </main> 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
    document.getElementById('sortOrder').addEventListener('change', function () {
        const sortOrder = this.value;
        const vacancyId = @json($filteredVacancyId);

        axios.get(`/admin/applicants-profile/sort`, {
            params: {
                sort_order: sortOrder,
                vacancy_id: vacancyId
            }
        })
        .then(response => {
            document.getElementById('applicantList').innerHTML = response.data;
            feather.replace(); // re-render icons if needed
        })
        .catch(error => {
            console.error("Sorting failed:", error);
        });
    });
    </script>
</body>
@endsection

@extends('layout.admin')

@section('title', 'Applicants Profile')

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DILG - Applicants Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>

@section('content')
<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen w-full">
        <!-- Main Content -->
        <main class="w-full space-y-6">
            <!-- Header with Back Button and Title -->
            <section class="flex items-center gap-4 border-b border-[#0D2B70]">
                <div class="flex items-center gap-4">
                    <button aria-label="Back" onclick="window.location.href='{{ route('applications_list') }}'" class="use-loader group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                        <span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">Applicants Profile</span>
                    </h1>
                </div>
            </section>

            <section class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex items-center space-x-3">
                    <p class="text-lg font-bold font-montserrat text-black-600">SORT</p>
                    <select id="sortOrder" class="border border-gray-300 rounded-lg px-4 py-2 text-sm font-montserrat">
                        <option value="latest">LATEST</option>
                        <option value="oldest">OLDEST</option>
                    </select>
                </div>
                <div class="flex items-center space-x-3">
                    @include('partials.alerts_template', [
                        'id' => 'exportAll',
                        'showTrigger' => true,
                        'triggerText' => 'Export Log',
                        'triggerClass' => 'flex items-center px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-800 transition',
                        'title' => 'Export Confirmation',
                        'message' => 'Are you sure you want to export all reviewed application?',
                        'showCancel' => true,
                        'cancelText' => 'No, Cancel',
                        'okText' => 'Yes, Export',
                        'okAction' => "window.location.href='" . route('exportNotReviewed', $filteredVacancyId) . "'",
                    ])
                </div>
            </section>

            <!-- Add a container around your applicants list for replacement -->
            <section id="applicantList" class="space-y-6">
                @forelse($applicants as $applicant)
                    @include('partials.applicant_list', ['applicant' => $applicant])
                @empty
                    <div class="text-center text-gray-500 font-semibold text-2xl mt-10">
                        <i data-feather="user-x" class="w-6 h-6 inline-block mr-2 text-gray-400"></i>
                        No new applicants for this job yet.
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

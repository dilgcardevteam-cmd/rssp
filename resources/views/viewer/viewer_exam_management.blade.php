@extends('layout.viewer')

@section('title', 'DILG - Manage Exam')

@section('content')
<main class="w-full max-w-7xl space-x-2">
    <!-- Title bar -->
    <div class="flex items-center gap-4 mb-5">
        <button aria-label="Back" onclick="window.history.back()" class="p-2 rounded-full bg-[#D9D9D9] hover:bg-[#002C76] h-11 w-11 flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#002c76] hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Title and Sub-labels Container -->
        <div class="flex flex-col gap-2 w-full">
            <div class="flex items-center px-4 py-2 rounded-xl bg-[#F1F6FF] border-2 border-[#002C76] text-[#002C76] font-extrabold font-montserrat text-3xl gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V6a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V18a2 2 0 01-2 2z" />
                </svg>
                Manage Exam
            </div>
        </div>
    </div>

    <!-- Vacancy Info + Action Button -->
    <section class="bg-[#F1F6FF] p-6 rounded-xl shadow-sm">
        <!-- Top Row: Info and Copy Button -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
            <!-- Left Info -->
            <div class="space-y-1 text-sm text-[#002C76] font-montserrat">
                <p><span class="font-bold">VACANCY ID:</span> FAIII-001</p>
                <p><span class="font-bold">EXAM ID:</span> FAIII-001-EXAM</p>
                <p><span class="font-bold">EXAM LINK:</span> <span id="exam-link">sample.domain.com/exam/faiii-001/faiii-001-exam</span></p>
            </div>

            <!-- Copy Button -->
            <button onclick="copyExamLink()" class="bg-[#2559B1] hover:bg-blue-900 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                <i class="fa-regular fa-copy"></i> Copy Link
            </button>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-300 my-4"></div>

        <!-- Bottom Row: Title + Status -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <!-- Job Title -->
            <div>
                <div class="text-xl md:text-2xl font-extrabold text-[#002C76]">Financial Analyst III</div>
                <div class="text-sm font-semibold text-[#002C76]">(Contract of Service)</div>
            </div>

            <!-- Start Exam -->
            <div class="flex flex-col items-center justify-center space-y-1 text-center min-w-[170px]">
                <button class="bg-green-600 hover:bg-green-800 transition text-white text-sm font-semibold rounded-full flex items-center gap-2 px-5 py-2">
                    <i class="fa-solid fa-play"></i> Start Exam
                </button>
                <span class="text-sm font-bold text-red-600">2/4 are READY</span>
            </div>
        </div>

        <!-- Time/Date/Place (static text) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-[#002C76] font-montserrat">
            <div>
                <label class="block font-semibold mb-1">Time:</label>
                <p class="px-3 py-2 border border-[#002C76] rounded-md shadow-sm bg-white">08:30 AM</p>
            </div>
            <div>
                <label class="block font-semibold mb-1">Date:</label>
                <p class="px-3 py-2 border border-[#002C76] rounded-md shadow-sm bg-white">2025-07-01</p>
            </div>
            <div>
                <label class="block font-semibold mb-1">Place:</label>
                <p class="px-3 py-2 border border-[#002C76] rounded-md shadow-sm bg-white">DILG-CAR Regional Office</p>
            </div>
        </div>
    </section>

    <!-- Table Header -->
    <section class="grid grid-cols-4 gap-5 bg-[#0D2B70] text-white font-bold rounded-3xl py-5 px-6 select-none w-full mt-6">
        <div class="flex items-center justify-start">NAME</div>
        <div class="flex items-center justify-center">SCORE</div>
        <div class="flex items-center justify-center">STATUS</div>
        <div class="flex items-center justify-center"></div>
    </section>

    <!-- Table Rows -->
    <section class="space-y-3 w-full mt-4">
        @php
            $participants = [
                ['name' => 'Sophia D. First', 'score' => '7/10', 'status' => ['READY', 'green'], 'button' => true],
                ['name' => 'Halden D. Nagcharge', 'score' => '10/10', 'status' => ['FINISHED', 'blue'], 'button' => true],
                ['name' => 'Tom N. Jerry', 'score' => '', 'status' => ['ONGOING', 'yellow'], 'button' => true],
                ['name' => 'Sing Q. Wenta', 'score' => '', 'status' => ['OFFLINE', 'gray'], 'button' => false],
                ['name' => 'Test', 'score' => '', 'status' => ['NOT READY', 'red'], 'button' => false]
            ];
        @endphp

        @foreach ($participants as $p)
        <div class="grid grid-cols-4 gap-4 border-2 border-[#0D2B70] rounded-3xl py-4 px-6 items-center text-[#0D2B70] bg-white shadow-sm">
            <!-- Name -->
            <div class="font-bold text-sm truncate">{{ $p['name'] }}</div>

            <!-- Score -->
            <div class="flex justify-center font-bold text-sm">{{ $p['score'] ?: '-' }}</div>

            <!-- Status -->
            <div class="text-sm font-semibold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle" style="color: {{ $p['status'][1] }}"></i>
                {{ $p['status'][0] }}
            </div>

            <!-- Button -->
            <div class="flex justify-center">
                @if($p['button'])
                <a href="{{ route('viewer.view_exam') }}" class="bg-[#00127.0.0.1] hover:bg-green-900 transition text-white text-sm font-medium rounded-full flex items-center gap-2 px-4 py-1.5">
                    <i class="fa-solid fa-play"></i>
                    View Answers
                </a>
                @else
                <div class="h-[36px] w-[130px]"></div> <!-- Layout placeholder -->
                @endif
            </div>
        </div>
        @endforeach
    </section>
</main>

<script>
    function copyExamLink() {
        const link = document.getElementById('exam-link').textContent.trim();
        navigator.clipboard.writeText(link).then(() => {
            showAppToast('Exam link copied to clipboard!');
        }).catch(() => {
            showAppToast('Failed to copy link.');
        });
    }
</script>
@include('partials.loader')
@endsection


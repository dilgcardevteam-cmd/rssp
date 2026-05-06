@extends('layout.admin')
@section('title', 'DILG - View Exam')
@section('content')

<main class="w-full max-w-7xl space-x-6">
    <!-- Title bar -->
    <div class="flex items-center gap-4 mb-8">
        <!-- Back Button -->
        <button aria-label="Back" onclick="window.history.back()" class="p-2 rounded-full bg-[#D9D9D9] hover:bg-[#002C76] h-11 w-11 flex items-center justify-center transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#002c76] hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>


        <!-- Title and Sub-labels Container -->
        <div class="flex flex-col gap-2 w-full">
            <!-- Edit Exam Label -->
            <div class="flex items-center px-4 py-2 rounded-xl bg-[#F1F6FF] border-2 border-[#002C76] text-[#002C76] font-extrabold font-montserrat font-black text-3xl gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V6a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V18a2 2 0 01-2 2z" />
                </svg>
                Edit Exam
            </div>

            <!-- Job Title + Label -->
            <div class="flex items-center justify-between px-4 py-2 rounded-xl border-2 border-[#002C76] font-montserrat font-extrabold text-xl text-black">
                <span>{{ $positionTitle->position_title }}</span>
                <span class="text-base">EXAMINATION</span>
            </div>
        </div>
    </div>

    <!-- Questions List -->
    <section class="space-y-6">
        @include('partials.essay_question')
    </section>
    @include('partials.loader')
</main>
@endsection

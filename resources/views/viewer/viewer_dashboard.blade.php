@extends('layout.viewer')
@section('title', 'DILG - Viewer Exam Management')
@section('content')

<main class="w-full max-w-7xl space-y-6">

    <section>
            <p class="text-xl font-normal text-black font-montserrat">Welcome back,</p>
            <h1 class="text-3xl font-extrabold text-black uppercase font-montserrat">viewer</h1>
    </section>

    <!-- Header with back arrow and title -->
    <section class="flex items-center space-x-4 mb-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full bg-[#0D2B70] text-white rounded-xl text-2xl font-extrabold font-montserrat px-8 py-4 tracking-wide select-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
            </svg>
            <span class="whitespace-nowrap">EXAM MANAGEMENT</span>
        </h1>
    </section>

    <!-- Table Header -->
    <section class="grid grid-cols-[1.4fr_3.2fr_3.1fr_1.9fr_2fr_1.5fr] gap-4 bg-[#0D2B70] text-white font-bold rounded-xl py-5 px-6 select-none overflow-hidden">
        <div class="flex items-center justify-center">VACANCY ID</div>
        <div class="flex items-center justify-center">JOB TITLE</div>
        <div class="flex items-center justify-center">EXAM ID</div>
        <div class="flex items-center justify-center"></div>
        <div class="flex items-center justify-center"></div>
    </section>


    <!-- Backend to be implemented: GO TO THEIR OWN RESPECTIVE LINK -->
    <section class="space-y-4">
        @foreach ([
            [
                'id' => 'FAIII-001',
                'title' => 'Financial Analyst III',
                'exam' => 'FAIII-001-EXAM',
                'link' => '',
                'edit' => '/admin/exam_management/edit_exam',
                'manage' => '/admin/exam_management/manage_exam',
            ],
            [
                'id' => 'SG-001',
                'title' => 'Security Guards',
                'exam' => 'SG-001-EXAM',
                'link' => '/exam/join/SG-001',
                'edit' => '/admin/exam_management/view_exam/SG-001',
                'manage' => '/admin/exam_management/manage_exam/SG-001',
            ],
            [
                'id' => 'EIII-001',
                'title' => 'ENGINEER III',
                'exam' => 'EIII-001-EXAM',
                'link' => '/exam/join/EIII-001',
                'edit' => '/admin/exam_management/view_exam/EIII-001',
                'manage' => '/admin/exam_management/manage_exam/EIII-001',
            ],
        ] as $row)
        <div class="grid grid-cols-[1.2fr_2fr_1.5fr_auto_auto_1fr] gap-4 border-2 border-[#0D2B70] rounded-xl py-5 px-6 items-center text-[#0D2B70] select-none overflow-x-hidden">
            <div class="font-extrabold">{{ $row['id'] }}</div>
            <div>
                <p class="font-extrabold">{{ $row['title'] }}</p>
                <p class="text-[#0D2B70]/70 text-[0.9rem] italic">(Contract of Service)</p>
            </div>
            <div class="text-center font-semibold">{{ $row['exam'] }}</div>

            <!-- Copy Link -->
            <div class="text-center w-fit mx-auto">
                <button onclick="window.location.href='{{ $row['link'] }}'"
                class="bg-[#2559B1] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    Copy Exam Join Link
                </button>
            </div>

            <!-- Status -->
            <div class="text-center w-fit mx-auto">
                <a href="{{ route('viewer.exam_management') }}" class="bg-[#002C76] hover:opacity-80 transition text-white font-semibold rounded-full flex items-center gap-2 px-4 py-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281a1.1 1.1 0 0 0 .865.997l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827a1.1 1.1 0 0 0 0 1.983l1.004.827a1.125 1.125 0 0 1 .26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456a1.1 1.1 0 0 0-.865.997l-.213 1.281a1.125 1.125 0 0 1-1.11.94h-2.594a1.125 1.125 0 0 1-1.11-.94l-.213-1.281a1.1 1.1 0 0 0-.865-.997l-1.217.456a1.125 1.125 0 0 1-1.369-.491l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827a1.1 1.1 0 0 0 0-1.983l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456a1.1 1.1 0 0 0 .865-.997l.214-1.28Z" />
                    </svg>
                    Manage
                </a>
            </div>
        </div>
        @endforeach
    </section>
    @include('partials.loader')
</main>

@endsection

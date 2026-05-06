<!DOCTYPE html>
<html lang="en">

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'DILG RHRMSPB')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>

    @include('partials.global_toast')
    @stack('styles')
</head>

<body class="bg-[#F3F8FF] min-h-screen flex flex-col text-gray-900 px-6">
    <div class="flex flex-col h-screen overflow-hidden">

        {{-- Header --}}
        <header class="px-10 py-4 flex items-center gap-4 border-b border-[#002C76]">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-14 w-14 rounded-full border border-gray-300" />
            <div>
                <div class="text-sm text-[#002C76] font-bold uppercase tracking-wide leading-snug">
                    DILG - CAR
                </div>
                <div class="text-xl text-[#002C76] font-bold uppercase tracking-tight leading-tight">
                    RHRMSPB SYSTEM
                </div>
                <div class="text-sm text-gray-700 font-semibold uppercase tracking-wide">
                    Examination
                </div>
            </div>
        </header>

        {{-- Main content area --}}
        <div class="flex-1 overflow-auto px-10">
            <main class="min-h-[calc(100vh-8.5rem)] p-6 pb-10 px-5 space-y-10">
                @yield('content')
            </main>
        </div>

    </div>

    <script>
        feather.replace();
    </script>

    @stack('scripts')
</body>

</html>

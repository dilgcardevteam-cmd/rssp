<!-- resources/views/partials/mobile-sidebar.blade.php -->
<div class="lg:hidden">
    <!-- Overlay -->
    <div 
        x-show="mobileSidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/80 z-40" 
        @click="mobileSidebarOpen = false"
        style="display: none;"
        aria-hidden="true"
    ></div>

    <!-- Sidebar -->
    <div 
        x-show="mobileSidebarOpen"
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl flex flex-col h-full"
        style="display: none;"
        role="dialog"
        aria-modal="true"
    >
        <!-- Header -->
        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-10 w-10 rounded-full" />
            <div class="font-bold text-sm text-[#002C76] font-montserrat leading-tight">
                DILG - CAR <br>
                <span class="text-xs font-medium tracking-tight">RECRUITMENT SELECTION AND PLACEMENT PORTAL</span>
            </div>
            <button @click="mobileSidebarOpen = false" class="ml-auto text-gray-600 hover:text-gray-800 p-1">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-1 overflow-y-auto font-montserrat p-4 space-y-2">
            <x-mobile-nav-link icon="home" label="Home" :active="request()->routeIs('dashboard_user')" href="{{ route('dashboard_user') }}" />
            <x-mobile-nav-link icon="archive" label="Job Vacancies" :active="request()->routeIs('job_vacancy')" href="{{ route('job_vacancy') }}" />
            <x-mobile-nav-link icon="user" label="My Applications" :active="request()->routeIs('my_applications')" href="{{ route('my_applications') }}" />
            <x-mobile-nav-link icon="file-text" label="Personal Data Sheet" :active="request()->routeIs('display_c1')" href="{{ route('display_c1', ['simple' => 1]) }}" />
            <x-mobile-nav-link icon="info" label="About This Website" :active="request()->routeIs('about')" href="{{ route('about') }}" />
            <x-mobile-nav-link icon="book-open" label="Manual" :active="request()->routeIs('manual.user')" href="{{ route('manual.user') }}" />
        </nav>
    </div>
</div>

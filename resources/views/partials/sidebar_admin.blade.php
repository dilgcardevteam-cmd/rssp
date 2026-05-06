<!-- resources/views/partials/sidebar_admin.blade.php -->
@php
    $adminRole = Auth::guard('admin')->user()->role ?? null;
    $isPositionsContext = request()->routeIs('admin.positions.*')
        || request()->routeIs('addcos')
        || request()->routeIs('addplantilla');
    $isProgramsContext = request()->routeIs('admin.courses.*');
    $isVacanciesContext = request()->routeIs('vacancies_management')
        || request()->routeIs('vacancies.addcos')
        || request()->routeIs('vacancies.addplantilla')
        || request()->routeIs('vacancies.edit');
@endphp

<aside
    class="sidebar relative ml-5 mt-5 mb-5 flex flex-col justify-between bg-white text-[#002C76] rounded-xl shadow-2xl overflow-y-auto overflow-x-hidden h-[calc(100vh-2.5rem)] w-72 flex-shrink-0">
    <div class="flex-1 overflow-y-auto scrollbar-thin">
        <a href="#" class="flex items-center gap-2 pt-14 px-2 cursor-pointer overflow-hidden">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-12 w-12 rounded-full border border-white flex-shrink-0 transition-all duration-300" />
            <div class="whitespace-nowrap overflow-hidden transition-all duration-300">
                <div class="font-bold font-montserrat text-[#002C76] text-[20px] uppercase leading-tight tracking-wide">
                    DILG - CAR
                </div>
                <div class="text-[16px] leading-4 font-bold font-montserrat tracking-tighter text-[#002C76] uppercase">
                    RECRUITMENT SELECTION
                    <br>
                    AND PLACEMENT PORTAL<br>
                    <span class="text-[#C9282D]">ADMIN PANEL</span>
                </div>
            </div>
        </a>

        <nav class="mt-8 space-y-1 px-2 font-montserrat pb-4">
            @if(in_array($adminRole, ['superadmin', 'admin', 'hr_division', 'viewer'], true))
                <a href="{{ $adminRole === 'viewer' ? route('viewer') : route('dashboard_admin') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ (request()->routeIs('dashboard_admin') || request()->routeIs('viewer'))
        ? 'bg-[#002C76] text-white shadow-md'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">HOME</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'hr_division'], true))
                <a href="{{ route('vacancies_management') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ $isVacanciesContext
        ? 'bg-[#002C76] text-white shadow-md'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">VACANCIES MANAGEMENT</span>
                </a>
            @endif

            @if($adminRole === 'hr_division')
                <a href="{{ route('admin.positions.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ $isPositionsContext
        ? 'bg-[#002C76] text-white shadow-md'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i class="fa-solid fa-layer-group w-5 h-5 flex-shrink-0"></i>
                    <span class="ml-3">POSITIONS</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'hr_division'], true))
                <a href="{{ route('applications_list') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('applications_list') || request()->routeIs('admin.applicant_status*')
        ? 'bg-[#002C76] text-white shadow-md'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">APPLICANTS MANAGEMENT</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'viewer'], true))
                <a href="{{ route('admin_exam_management') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('admin_exam_management') || request()->routeIs('admin.exam*')
        ? 'bg-[#002C76] text-white shadow-md'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">EXAM MANAGEMENT</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'viewer', 'hr_division'], true))
                <a href="{{ route('manual.admin') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('manual.admin')
        ? 'bg-[#002C76] text-white shadow-md'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i data-feather="book-open" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">MANUAL</span>
                </a>
            @endif

            @if($adminRole === 'superadmin')
                <a href="{{ route('admin_account_management') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('admin_account_management')
                            ? 'bg-[#002C76] text-white shadow-md'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i class="fa-solid fa-users-gear w-5 h-5 flex-shrink-0"></i>
                    <span class="ml-3">USER MANAGEMENT</span>
                </a>

                <a href="{{ route('admin.applicant_records.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('admin.applicant_records.index')
                            ? 'bg-[#002C76] text-white shadow-md'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                    <i class="fa-solid fa-folder-open w-5 h-5 flex-shrink-0"></i>
                    <span class="ml-3">APPLICANT RECORDS</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin'], true))
                @php
                    $utilitiesOpen = request()->routeIs('admin_activity_log')
                        || request()->routeIs('signatories.*')
                        || request()->routeIs('admin.reports.index')
                        || request()->routeIs('admin.backup.index')
                        || $isPositionsContext
                        || $isProgramsContext;
                @endphp
                <div x-data="{ submenuOpen: {{ $utilitiesOpen ? 'true' : 'false' }} }" class="relative">
                    <button @click="submenuOpen = !submenuOpen"
                        data-utils-toggle="desktop"
                        class="w-full group flex items-center justify-between rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md">
                        <div class="flex items-center">
                            <i class="fa-solid fa-screwdriver-wrench w-5 h-5 flex-shrink-0"></i>
                            <span class="ml-3 uppercase">UTILITIES TOOLS</span>
                        </div>
                        <div class="transition-transform duration-200" :class="{ 'rotate-180': submenuOpen }">
                            <i class="fa-solid fa-chevron-down w-5 h-5 stroke-[2.5] flex-shrink-0"></i>
                        </div>
                    </button>

                    <div x-show="submenuOpen" x-collapse data-utils-panel="desktop" data-initial-open="{{ $utilitiesOpen ? '1' : '0' }}" class="pl-4 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('signatories.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 {{ request()->routeIs('signatories.*')
                                ? 'bg-[#002C76] text-white shadow-md'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                            <i data-feather="edit-3" class="w-5 h-5 stroke-[2.5] flex-shrink-0 ml-2"></i>
                            <span class="ml-3">SIGNATORIES</span>
                        </a>

                        <a href="{{ route('admin.positions.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 {{ $isPositionsContext
                                ? 'bg-[#002C76] text-white shadow-md'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                            <i class="fa-solid fa-layer-group w-5 h-5 flex-shrink-0 ml-2"></i>
                            <span class="ml-3">POSITIONS</span>
                        </a>

                        <a href="{{ route('admin.courses.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 {{ $isProgramsContext
                                ? 'bg-[#002C76] text-white shadow-md'
                                : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                            <i class="fa-solid fa-graduation-cap w-5 h-5 flex-shrink-0 ml-2"></i>
                            <span class="ml-3">PROGRAMS</span>
                        </a>

                        <a href="{{ route('admin_activity_log') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ request()->routeIs('admin_activity_log')
                                    ? 'bg-[#002C76] text-white shadow-md'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                            <i data-feather="clock" class="w-5 h-5 stroke-[2.5] flex-shrink-0 ml-2"></i>
                            <span class="ml-3">ACTIVITY LOG</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ request()->routeIs('admin.reports.index')
                                    ? 'bg-[#002C76] text-white shadow-md'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                            <i data-feather="bar-chart-2" class="w-5 h-5 stroke-[2.5] flex-shrink-0 ml-2"></i>
                            <span class="ml-3">REPORTS</span>
                        </a>

                        @if($adminRole === 'superadmin')
                            <a href="{{ route('admin.backup.index') }}" class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                    {{ request()->routeIs('admin.backup.index')
                                        ? 'bg-[#002C76] text-white shadow-md'
                                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }}">
                                <i class="fa-solid fa-database w-5 h-5 flex-shrink-0 ml-2"></i>
                                <span class="ml-3">BACKUP & RESTORE</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>
    </div>
</aside>

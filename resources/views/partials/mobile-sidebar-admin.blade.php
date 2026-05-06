<!-- resources/views/partials/mobile-sidebar-admin.blade.php -->
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

<div class="lg:hidden" x-data="{ mobileSidebarOpen: false }">
    <aside
        x-show="!mobileSidebarOpen"
        x-cloak
        x-transition:enter="transition ease-in-out duration-200 transform"
        x-transition:enter-start="-translate-x-6 opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in-out duration-150 transform"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-6 opacity-0"
        class="fixed inset-y-0 left-0 z-[60] w-16 bg-white shadow-xl border-r border-slate-100"
        style="display: none;"
        aria-label="Collapsed sidebar"
    >
        <button
            @click="mobileSidebarOpen = true"
            class="absolute top-4 right-3 p-2 bg-white rounded-lg shadow-md border border-slate-200 hover:bg-slate-100 transition-colors"
            aria-label="Open sidebar"
        >
            <i data-feather="menu" class="w-5 h-5 text-[#002C76]"></i>
        </button>
    </aside>

    <div
        x-show="mobileSidebarOpen"
        x-cloak
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

    <aside
        x-show="mobileSidebarOpen"
        x-cloak
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-[70] w-72 bg-white shadow-xl flex flex-col h-full"
        style="display: none;"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-10 w-10 rounded-full" />
            <div class="font-bold text-sm text-[#002C76] font-montserrat leading-tight">
                DILG - CAR <br>
                <span class="text-xs font-medium tracking-tight">RECRUITMENT SELECTION AND PLACEMENT PORTAL</span><br>
                <span class="text-xs font-semibold text-[#C9282D] tracking-tight">ADMIN PANEL</span>
            </div>
            <button
                @click="mobileSidebarOpen = false"
                class="ml-auto p-2 bg-white rounded-lg shadow-sm border border-slate-200 hover:bg-slate-100 transition-colors"
                aria-label="Close sidebar"
            >
                <i data-feather="menu" class="w-5 h-5 text-[#002C76]"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto font-montserrat p-4 space-y-2">
            @if(in_array($adminRole, ['superadmin', 'admin', 'hr_division'], true))
                <a href="{{ route('dashboard_admin') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('dashboard_admin')
        ? 'bg-[#002C76] text-white'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">HOME</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'hr_division'], true))
                <a href="{{ route('vacancies_management') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ $isVacanciesContext
        ? 'bg-[#002C76] text-white'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">VACANCIES MANAGEMENT</span>
                </a>
            @endif

            @if($adminRole === 'hr_division')
                <a href="{{ route('admin.positions.index') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ $isPositionsContext
        ? 'bg-[#002C76] text-white'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i class="fa-solid fa-layer-group w-5 h-5 flex-shrink-0"></i>
                    <span class="ml-3">POSITIONS</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'hr_division'], true))
                <a href="{{ route('applications_list') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('applications_list') || request()->routeIs('admin.applicant_status*')
        ? 'bg-[#002C76] text-white'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">APPLICANTS MANAGEMENT</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'viewer'], true))
                <a href="{{ route('admin_exam_management') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('admin_exam_management') || request()->routeIs('admin.exam*')
        ? 'bg-[#002C76] text-white'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">EXAM MANAGEMENT</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin', 'viewer', 'hr_division'], true))
                <a href="{{ route('manual.admin') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('manual.admin')
        ? 'bg-[#002C76] text-white'
        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="book-open" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span class="ml-3">MANUAL</span>
                </a>
            @endif

            @if($adminRole === 'superadmin')
                <a href="{{ route('admin_account_management') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('admin_account_management')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i class="fa-solid fa-users-gear w-5 h-5 flex-shrink-0"></i>
                    <span class="ml-3">USER MANAGEMENT</span>
                </a>

                <a href="{{ route('admin.applicant_records.index') }}"
                    @click="mobileSidebarOpen = false"
                    class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                        {{ request()->routeIs('admin.applicant_records.index')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i class="fa-solid fa-folder-open w-5 h-5 flex-shrink-0"></i>
                    <span class="ml-3">APPLICANT RECORDS</span>
                </a>
            @endif

            @if(in_array($adminRole, ['superadmin', 'admin'], true))
                @php
                    $utilitiesOpenMobile = request()->routeIs('admin_activity_log')
                        || request()->routeIs('signatories.*')
                        || request()->routeIs('admin.reports.index')
                        || request()->routeIs('admin.backup.index')
                        || $isPositionsContext
                        || $isProgramsContext;
                @endphp
                <div x-data="{ submenuOpen: {{ $utilitiesOpenMobile ? 'true' : 'false' }} }" class="relative">
                    <button @click="submenuOpen = !submenuOpen"
                        data-utils-toggle="mobile"
                        class="w-full group flex items-center justify-between rounded-md px-4 py-2 text-sm font-bold transition-all duration-200 text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <div class="flex items-center">
                            <i class="fa-solid fa-screwdriver-wrench w-5 h-5 flex-shrink-0"></i>
                            <span class="ml-3 uppercase">UTILITIES</span>
                        </div>
                        <div class="transition-transform duration-200" :class="{ 'rotate-180': submenuOpen }">
                            <i class="fa-solid fa-chevron-down w-4 h-4"></i>
                        </div>
                    </button>

                    <div x-show="submenuOpen" x-collapse data-utils-panel="mobile" data-initial-open="{{ $utilitiesOpenMobile ? '1' : '0' }}" class="pl-4 mt-1 space-y-1 overflow-hidden">
                        <a href="{{ route('signatories.index') }}"
                            @click="mobileSidebarOpen = false"
                            class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ request()->routeIs('signatories.*')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="edit-3" class="w-5 h-5 stroke-[2.5] flex-shrink-0"></i>
                            <span class="ml-3">SIGNATORIES</span>
                        </a>

                        <a href="{{ route('admin.positions.index') }}"
                            @click="mobileSidebarOpen = false"
                            class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ $isPositionsContext
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i class="fa-solid fa-layer-group w-5 h-5 flex-shrink-0"></i>
                                <span class="ml-3">POSITIONS</span>
                        </a>

                        <a href="{{ route('admin.courses.index') }}"
                            @click="mobileSidebarOpen = false"
                            class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ $isProgramsContext
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i class="fa-solid fa-graduation-cap w-5 h-5 flex-shrink-0"></i>
                            <span class="ml-3">PROGRAMS</span>
                        </a>

                        <a href="{{ route('admin_activity_log') }}"
                            @click="mobileSidebarOpen = false"
                            class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ request()->routeIs('admin_activity_log')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="clock" class="w-5 h-5 stroke-[2.5] flex-shrink-0"></i>
                            <span class="ml-3">ACTIVITY LOG</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}"
                            @click="mobileSidebarOpen = false"
                            class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                {{ request()->routeIs('admin.reports.index')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="bar-chart-2" class="w-5 h-5 stroke-[2.5] flex-shrink-0"></i>
                            <span class="ml-3">REPORTS</span>
                        </a>

                        @if($adminRole === 'superadmin')
                            <a href="{{ route('admin.backup.index') }}"
                                @click="mobileSidebarOpen = false"
                                class="use-loader flex items-center rounded-md px-4 py-2 text-sm font-bold transition-all duration-200
                                    {{ request()->routeIs('admin.backup.index')
                                        ? 'bg-[#002C76] text-white'
                                        : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                                <i class="fa-solid fa-database w-5 h-5 flex-shrink-0"></i>
                                <span class="ml-3">BACKUP & RESTORE</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>
    </aside>
</div>

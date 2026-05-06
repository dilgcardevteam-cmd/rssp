<!-- resources/views/partials/sidebar.blade.php -->
<aside id="sidebar"
    class="sidebar sidebar-transition fixed ml-5 mt-5 mb-5 flex flex-col justify-between bg-white text-[#002C76] rounded-xl shadow-lg overflow-hidden w-16 relative">

    <!-- Upper -->
    <div>
        <button id="toggleSidebar" class="p-2 focus:outline-none absolute top-3 left-3 z-20" aria-label="Toggle sidebar">
            <i data-feather="menu" class="w-5 h-5 stroke-[3]"></i>
        </button>

        <a href="#" class="flex items-center gap-2 pt-14 px-2 cursor-pointer">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                class="h-12 w-12 rounded-full border border-white flex-shrink-0 logo-transition" />
            <div id="sidebarText" class="sidebar-text-hidden whitespace-nowrap overflow-hidden">
                <div class="font-bold font-montserrat text-[#002C76] text-[20px] uppercase leading-tight tracking-wide">
                    DILG - CAR
                </div>
                <div class="text-[10px] leading-4 font-bold font-montserrat tracking-tighter text-[#002C76] uppercase">
                    RECRUITMENT AND 
                    <br>
                    SELECTION PORTAL
                </div>
            </div>
        </a>

        <!-- Only Exam Management Link -->
        <nav class="mt-8 space-y-1 px-2 font-montserrat">
            <a href="{{ route('viewer') }}"
                class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold {{ request()->routeIs('viewer') ? 'bg-[#002C76] text-white shadow-md' : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }} transition-all duration-200">
                <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textHome" class="sidebar-text-hidden ml-3">HOME</span>
            </a>

            <a href="{{ route('admin_exam_management') }}"
                class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold {{ request()->routeIs('admin_exam_management') ? 'bg-[#002C76] text-white shadow-md' : 'text-[#002C76] hover:text-white hover:bg-[#002C76] hover:shadow-md' }} transition-all duration-200">
                <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textExamManagement" class="sidebar-text-hidden ml-3">EXAM MANAGEMENT</span>
            </a>

            <a href="{{ route('manual.admin') }}"
                class="use-loader group flex items-center rounded-md px-4 py-2 text-sm font-bold text-[#002C76] hover:text-white hover:bg-[#002c76] active:bg-[#002c76] active:text-white transition">
                <i data-feather="book-open" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                <span id="textManual" class="sidebar-text-hidden ml-3">MANUAL</span>
            </a>
        </nav>
    </div>

    <!-- No logout in viewer sidebar -->
</aside>

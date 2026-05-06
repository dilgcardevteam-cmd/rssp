    <!-- resources/views/partials/sidebar.blade.php -->
    @php
        $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
    @endphp
    <aside id="sidebar"
        class="sidebar sidebar-transition fixed top-5 left-5 flex flex-col justify-between bg-white text-[#002C76] rounded-xl shadow-lg {{ $simple ? 'overflow-y-hidden w-72' : 'overflow-hidden w-16' }} z-[60] h-[95vh]">

        <style>
            #pdsMenu.pds-menu, #docsMenu.pds-menu {
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                transition: max-height 200ms ease, opacity 200ms ease;
            }
            #pdsMenu.pds-menu.show, #docsMenu.pds-menu.show {
                max-height: 600px;
                opacity: 1;
            }
            #pdsCaret.rotate-180, #docsCaret.rotate-180 {
                transform: rotate(180deg);
                transition: transform 200ms ease;
            }
        </style>

        <!-- Toggle Button (mobile only) -->
        <button id="toggleSidebar" class="lg:hidden p-2 focus:outline-none absolute top-3 right-3 z-20" aria-label="Toggle sidebar">
            <i data-feather="menu" class="w-5 h-5 stroke-[3]"></i>
        </button>

        <!-- Upper Section -->
        <div>
            <a class="flex items-center gap-2 pt-14 px-2">
                <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                    class="h-12 w-12 rounded-full border border-white flex-shrink-0 logo-transition" />
                <div id="sidebarText" class="{{ $simple ? 'sidebar-text-visible' : 'sidebar-text-hidden' }} whitespace-nowrap overflow-hidden">
                    <div class="font-bold font-montserrat text-[#002C76] text-[20px] uppercase leading-tight tracking-wide">
                        DILG - CAR
                    </div>
                    <div class="text-[16px] leading-4 font-bold font-montserrat tracking-tighter text-[#002C76] uppercase">
                        RECRUITMENT SELECTION
                        <br>
                        AND PLACEMENT PORTAL
                    </div>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="mt-8 space-y-1 px-2 font-montserrat" aria-label="Main navigation">
                <a href="{{ route('dashboard_user') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('dashboard_user')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="home" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textHome" class="sidebar-text-hidden ml-3">HOME</span>
                </a>

                <a href="{{ route('job_vacancy') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('job_vacancy')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textJobVacancies" class="sidebar-text-hidden ml-3">JOB VACANCIES</span>
                </a>

                <a href="{{ route('my_applications') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('my_applications')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textMyApplications" class="sidebar-text-hidden ml-3">MY APPLICATIONS</span>
                </a>

                <div class="w-full">
                    <div class="flex items-center justify-between w-full rounded-md px-4 py-2 text-sm font-bold transition
                        {{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5'))
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <a href="{{ route('display_c1', ['simple' => 1]) }}" class="flex items-center use-loader">
                            <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                            <span id="textPersonalDataSheet" class="sidebar-text-hidden ml-3">PERSONAL DATA SHEET</span>
                        </a>
                        <button type="button" id="pdsToggle" aria-expanded="{{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5')) ? 'true' : 'false' }}" class="ml-2">
                            <i id="pdsCaret" data-feather="chevron-down" class="w-4 h-4 stroke-[3]"></i>
                        </button>
                    </div>
                    <div id="pdsMenu" class="pds-menu {{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5')) ? 'show' : '' }} mt-1 pl-10 space-y-1">
                        <a href="{{ route('display_c1', ['simple' => 1]) }}"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition use-loader
                                {{ request()->routeIs('display_c1')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="user" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">PERSONAL INFORMATION</span>
                        </a>
                        <a href="{{ route('display_c2', ['simple' => 1]) }}"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition use-loader
                                {{ request()->routeIs('display_c2')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="briefcase" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">WORK EXPERIENCE</span>
                        </a>
                        <a href="{{ route('display_c3', ['simple' => 1]) }}"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition use-loader
                                {{ request()->routeIs('display_c3')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="book-open" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">LEARNING &amp; DEVELOPMENT</span>
                        </a>
                        <a href="{{ route('display_c4', ['simple' => 1]) }}"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition use-loader
                                {{ request()->routeIs('display_c4')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="info" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">OTHER INFORMATION</span>
                        </a>
                        <a href="{{ route('display_wes', ['simple' => 1]) }}"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition use-loader
                                {{ request()->routeIs('display_wes')
                                    ? 'bg-[#002C76] text-white'
                                    : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                            <i data-feather="briefcase" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">WORK EXPERIENCE SHEET</span>
                        </a>
                    </div>
                </div>

                <div class="w-full">
                    <div class="flex items-center justify-between w-full rounded-md px-4 py-2 text-sm font-bold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                        <a href="#" class="flex items-center" id="btnDocs">
                            <i data-feather="download" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                            <span id="textDownloadDocs" class="sidebar-text-hidden ml-3">DOWNLOAD DOCUMENTS</span>
                        </a>
                        <button type="button" id="docsToggle" aria-expanded="false" class="ml-2">
                            <i id="docsCaret" data-feather="chevron-down" class="w-4 h-4 stroke-[3]"></i>
                        </button>
                    </div>
                    <div id="docsMenu" class="pds-menu mt-1 pl-10 space-y-1">
                        <a href="{{ route('pds.preview') }}" target="_blank" rel="noopener"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                            <i data-feather="file-text" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">PERSONAL DATA SHEET</span>
                        </a>
                        <a href="{{ route('wes.preview') }}" target="_blank" rel="noopener"
                            class="flex items-center rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                            <i data-feather="file" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                            <span class="ml-3">WORK EXPERIENCE SHEET</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('about') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('about')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="info" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textAboutWebsite" class="sidebar-text-hidden ml-3">ABOUT THIS WEBSITE</span>
                </a>

                <a href="{{ route('manual.user') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('manual.user')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="book-open" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textManual" class="sidebar-text-hidden ml-3">MANUAL</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section intentionally empty: logout moved to profile menu -->
    </aside>

    <!-- Sidebar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            feather.replace();
            const pdsToggle = document.getElementById('pdsToggle');
            const pdsMenu = document.getElementById('pdsMenu');
            const pdsCaret = document.getElementById('pdsCaret');
            if (pdsToggle && pdsMenu && pdsCaret) {
                if (pdsMenu.classList.contains('show')) {
                    pdsCaret.classList.add('rotate-180');
                }
                const collapsed = sessionStorage.getItem('pdsCollapsed') === 'true';
                if (collapsed) {
                    pdsMenu.classList.remove('show');
                    pdsCaret.classList.remove('rotate-180');
                }
                pdsToggle.addEventListener('click', () => {
                    pdsMenu.classList.toggle('show');
                    pdsCaret.classList.toggle('rotate-180');
                    const nowCollapsed = !pdsMenu.classList.contains('show');
                    sessionStorage.setItem('pdsCollapsed', nowCollapsed ? 'true' : 'false');
                });
            }
            const pdsLink = document.querySelector('#sidebar a[href*="display_c1"]');
            if (pdsLink) {
                pdsLink.classList.remove('use-loader');
                pdsLink.addEventListener('click', (e) => {
                    e.preventDefault();
                });
            }
            const pdsMenuLinks = document.querySelectorAll('#pdsMenu a');
            pdsMenuLinks.forEach(link => {
                link.addEventListener('click', async (e) => {
                    sessionStorage.setItem('pdsCollapsed', 'false');

                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                        return;
                    }

                    const href = link.getAttribute('href');
                    if (!href) {
                        return;
                    }

                    e.preventDefault();
                    if (typeof window.__pdsAutosaveNow === 'function') {
                        try {
                            await window.__pdsAutosaveNow();
                        } catch (error) {
                            console.warn('Autosave flush before sidebar navigation failed:', error);
                        }
                    }

                    window.location.href = href;
                });
            });

            // Download Documents Toggle Logic
            const docsToggle = document.getElementById('docsToggle');
            const docsMenu = document.getElementById('docsMenu');
            const docsCaret = document.getElementById('docsCaret');
            const btnDocs = document.getElementById('btnDocs');

            if (docsToggle && docsMenu && docsCaret) {
                const openDocsOnLoad = new URLSearchParams(window.location.search).get('open_docs') === '1';
                const docsOpen = openDocsOnLoad || sessionStorage.getItem('docsOpen') === 'true';
                if (docsOpen) {
                    docsMenu.classList.add('show');
                    docsCaret.classList.add('rotate-180');
                    sessionStorage.setItem('docsOpen', 'true');
                }

                const toggleDocs = () => {
                    docsMenu.classList.toggle('show');
                    docsCaret.classList.toggle('rotate-180');
                    sessionStorage.setItem('docsOpen', docsMenu.classList.contains('show'));
                };

                docsToggle.addEventListener('click', toggleDocs);
                if (btnDocs) {
                    btnDocs.addEventListener('click', (e) => {
                        e.preventDefault();
                        toggleDocs();
                    });
                }
            }

            // Keep sidebar fixed in simple mode: page scroll should not scroll the sidebar itself.
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('toggleSidebar');
            const logo = document.querySelector('img[alt="DILG Logo"]');
            const textElements = [
                "sidebarText", "textHome", "textJobVacancies", "textMyApplications",
                "textPersonalDataSheet", "textDownloadDocs", "textAboutWebsite", "textWorkExperience", "textManual"
            ].map(id => document.getElementById(id));

            const isSimple = {{ $simple ? 'true' : 'false' }};
            let storedState = localStorage.getItem('userSidebarOpen');
            let isOpen = storedState === null ? true : storedState === 'true';

            function openSidebar() {
                sidebar?.classList.remove('w-16');
                sidebar?.classList.add('w-72');
                logo?.classList.remove('logo-small');
                textElements.forEach(el => {
                    el?.classList.remove('sidebar-text-hidden');
                    el?.classList.add('sidebar-text-visible');
                });
                isOpen = true;
                localStorage.setItem('userSidebarOpen', 'true');
            }

            function closeSidebar() {
                sidebar?.classList.remove('w-72');
                sidebar?.classList.add('w-16');
                logo?.classList.add('logo-small');
                if (!isSimple) {
                    textElements.forEach(el => {
                        el?.classList.remove('sidebar-text-visible');
                        el?.classList.add('sidebar-text-hidden');
                    });
                }
                isOpen = false;
                localStorage.setItem('userSidebarOpen', 'false');
            }

            if (isSimple) {
                sidebar?.classList.remove('overflow-y-auto');
                sidebar?.classList.add('overflow-y-hidden');
            }

            toggleButton?.addEventListener('click', () => {
                isOpen ? closeSidebar() : openSidebar();
            });

            // Always open on desktop, always closed on mobile
            if (window.innerWidth >= 1024) {
                openSidebar();
            } else {
                closeSidebar();
            }

            // Auto open/close on resize
            let userSidebarResizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(userSidebarResizeTimer);
                userSidebarResizeTimer = setTimeout(() => {
                    if (window.innerWidth >= 1024) {
                        openSidebar();
                    } else {
                        closeSidebar();
                    }
                }, 150);
            });
        });
    </script>

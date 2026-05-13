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
            #sidebar.sidebar-collapsed #pdsMenu,
            #sidebar.sidebar-collapsed #docsMenu,
            #sidebar.sidebar-collapsed #pdsToggle,
            #sidebar.sidebar-collapsed #docsToggle {
                display: none;
            }
            #sidebar.sidebar-collapsed [data-sidebar-label] {
                opacity: 0;
                width: 0;
                max-width: 0;
                margin-left: 0;
                overflow: hidden;
                pointer-events: none;
            }
            #sidebar.sidebar-collapsed nav {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
            }
            #sidebar.sidebar-collapsed nav > a,
            #sidebar.sidebar-collapsed nav > div > div,
            #sidebar.sidebar-collapsed > div > a {
                justify-content: center;
            }
        </style>

        <button id="desktopSidebarToggle" type="button"
            class="hidden lg:flex items-center justify-center absolute top-3 right-3 z-20 h-9 w-9 rounded-lg text-[#002C76] transition hover:bg-[#EAF2FF]"
            aria-label="Collapse sidebar"
            aria-expanded="{{ $simple ? 'true' : 'false' }}">
            <i id="desktopSidebarToggleIcon" data-feather="menu" class="w-4 h-4 stroke-[3]"></i>
        </button>

        <!-- Toggle Button (mobile only) -->
        <button id="toggleSidebar" class="lg:hidden p-2 focus:outline-none absolute top-3 right-3 z-20" aria-label="Toggle sidebar">
            <i data-feather="menu" class="w-5 h-5 stroke-[3]"></i>
        </button>

        <!-- Upper Section -->
        <div>
            <a class="flex items-center gap-2 pt-14 px-2">
                <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo"
                    class="h-12 w-12 rounded-full border border-white flex-shrink-0 logo-transition" />
                <div id="sidebarText" data-sidebar-label class="{{ $simple ? 'sidebar-text-visible' : 'sidebar-text-hidden' }} whitespace-nowrap overflow-hidden">
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
                    <span id="textHome" data-sidebar-label class="sidebar-text-hidden ml-3">HOME</span>
                </a>

                <a href="{{ route('job_vacancy') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('job_vacancy')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="archive" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textJobVacancies" data-sidebar-label class="sidebar-text-hidden ml-3">JOB VACANCIES</span>
                </a>

                <a href="{{ route('my_applications') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('my_applications')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="user" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textMyApplications" data-sidebar-label class="sidebar-text-hidden ml-3">MY APPLICATIONS</span>
                </a>

                <div class="w-full">
                    <div data-sidebar-pds-trigger class="flex items-center justify-between w-full rounded-md px-4 py-2 text-sm font-bold transition cursor-pointer
                        {{ (request()->routeIs('display_c1') || request()->routeIs('display_c2') || request()->routeIs('display_c3') || request()->routeIs('display_c4') || request()->routeIs('display_wes') || request()->routeIs('display_c5'))
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                        <div class="flex items-center">
                            <i data-feather="file-text" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                            <span id="textPersonalDataSheet" data-sidebar-label class="sidebar-text-hidden ml-3">PERSONAL DATA SHEET</span>
                        </div>
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
                            <span class="ml-3">ELIGIBILITY AND WORK EXPERIENCE</span>
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
                        <div class="w-full">
                            <div class="flex items-center justify-between w-full rounded-md px-3 py-2 text-sm font-semibold transition text-[#002C76] hover:text-white hover:bg-[#002C76]">
                                <a href="#" class="flex items-center" id="btnDocs">
                                    <i data-feather="download" class="w-4 h-4 stroke-[3] flex-shrink-0"></i>
                                    <span id="textDownloadDocs" class="ml-3">DOWNLOAD DOCUMENTS</span>
                                </a>
                                <button type="button" id="docsToggle" aria-expanded="false" class="ml-2">
                                    <i id="docsCaret" data-feather="chevron-down" class="w-4 h-4 stroke-[3]"></i>
                                </button>
                            </div>
                            <div id="docsMenu" class="pds-menu mt-1 ml-7 space-y-1">
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
                    </div>
                </div>

                <a href="{{ route('about') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('about')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="info" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textAboutWebsite" data-sidebar-label class="sidebar-text-hidden ml-3">ABOUT THIS WEBSITE</span>
                </a>

                <a href="{{ route('manual.user') }}"
                    class="group flex items-center rounded-md px-4 py-2 text-sm font-bold transition use-loader
                        {{ request()->routeIs('manual.user')
                            ? 'bg-[#002C76] text-white'
                            : 'text-[#002C76] hover:text-white hover:bg-[#002C76]' }}">
                    <i data-feather="book-open" class="w-5 h-5 stroke-[3] flex-shrink-0"></i>
                    <span id="textManual" data-sidebar-label class="sidebar-text-hidden ml-3">MANUAL</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section intentionally empty: logout moved to profile menu -->
    </aside>

    <!-- Sidebar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            feather.replace();
            const pdsTrigger = document.querySelector('[data-sidebar-pds-trigger]');
            const pdsToggle = document.getElementById('pdsToggle');
            const pdsMenu = document.getElementById('pdsMenu');
            const pdsCaret = document.getElementById('pdsCaret');
            const setPdsMenuExpanded = (expanded) => {
                if (!pdsMenu || !pdsCaret || !pdsToggle) {
                    return;
                }

                pdsMenu.classList.toggle('show', expanded);
                pdsCaret.classList.toggle('rotate-180', expanded);
                pdsToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                sessionStorage.setItem('pdsCollapsed', expanded ? 'false' : 'true');
            };
            const togglePdsMenu = () => {
                if (!pdsMenu) {
                    return;
                }

                if (!isOpen) {
                    openSidebar();
                    setPdsMenuExpanded(true);
                    return;
                }

                setPdsMenuExpanded(!pdsMenu.classList.contains('show'));
            };
            if (pdsToggle && pdsMenu && pdsCaret) {
                if (pdsMenu.classList.contains('show')) {
                    pdsCaret.classList.add('rotate-180');
                }
                const collapsed = sessionStorage.getItem('pdsCollapsed') === 'true';
                if (collapsed) {
                    setPdsMenuExpanded(false);
                } else {
                    setPdsMenuExpanded(pdsMenu.classList.contains('show'));
                }
                pdsToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    togglePdsMenu();
                });
            }
            const pdsMenuLinks = document.querySelectorAll('#pdsMenu a');
            pdsMenuLinks.forEach(link => {
                link.addEventListener('click', async (e) => {
                    sessionStorage.setItem('pdsCollapsed', 'false');

                    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || link.target === '_blank' || link.id === 'btnDocs') {
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
                        if (!isOpen) {
                            openSidebar();
                        }
                        toggleDocs();
                    });
                }
            }

            // Keep sidebar fixed in simple mode: page scroll should not scroll the sidebar itself.
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('toggleSidebar');
            const desktopToggleButton = document.getElementById('desktopSidebarToggle');
            const desktopToggleIcon = document.getElementById('desktopSidebarToggleIcon');
            const logo = document.querySelector('img[alt="DILG Logo"]');
            const sidebarOffsetTargets = Array.from(document.querySelectorAll('[data-sidebar-offset], [data-sidebar-max-width-open], [data-sidebar-max-width-closed]'));
            const textElements = [
                "sidebarText", "textHome", "textJobVacancies", "textMyApplications",
                "textPersonalDataSheet", "textDownloadDocs", "textAboutWebsite", "textWorkExperience", "textManual"
            ].map(id => document.getElementById(id));

            const isSimple = {{ $simple ? 'true' : 'false' }};
            let storedState = localStorage.getItem('userSidebarOpen');
            let isOpen = storedState === null ? true : storedState === 'true';
            const getBreakpointWidth = (breakpoint) => breakpoint === 'md' ? 768 : 1024;
            const isDesktopViewport = () => window.innerWidth >= 1024;

            function syncSidebarOffsets() {
                sidebarOffsetTargets.forEach((target) => {
                    const breakpoint = target.getAttribute('data-sidebar-offset-breakpoint') || 'lg';
                    const openOffset = target.getAttribute('data-sidebar-offset-open');
                    const closedOffset = target.getAttribute('data-sidebar-offset-closed');
                    const openMaxWidth = target.getAttribute('data-sidebar-max-width-open');
                    const closedMaxWidth = target.getAttribute('data-sidebar-max-width-closed');

                    if (window.innerWidth >= getBreakpointWidth(breakpoint)) {
                        if (openOffset && closedOffset) {
                            target.style.marginLeft = isOpen ? openOffset : closedOffset;
                        }
                        if (openMaxWidth && closedMaxWidth) {
                            target.style.maxWidth = isOpen ? openMaxWidth : closedMaxWidth;
                        }
                        return;
                    }

                    if (openOffset && closedOffset) {
                        target.style.marginLeft = '';
                    }
                    if (openMaxWidth && closedMaxWidth) {
                        target.style.maxWidth = '';
                    }
                });
            }

            function syncSidebarStateUi() {
                sidebar?.classList.toggle('sidebar-collapsed', !isOpen);
                desktopToggleButton?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                desktopToggleButton?.setAttribute('aria-label', isOpen ? 'Collapse sidebar' : 'Expand sidebar');
                syncSidebarOffsets();
            }

            function openSidebar({ persist = true } = {}) {
                sidebar?.classList.remove('w-16');
                sidebar?.classList.add('w-72');
                logo?.classList.remove('logo-small');
                textElements.forEach(el => {
                    el?.classList.remove('sidebar-text-hidden');
                    el?.classList.add('sidebar-text-visible');
                });
                isOpen = true;
                if (persist) {
                    localStorage.setItem('userSidebarOpen', 'true');
                }
                syncSidebarStateUi();
            }

            function closeSidebar({ persist = true } = {}) {
                sidebar?.classList.remove('w-72');
                sidebar?.classList.add('w-16');
                logo?.classList.add('logo-small');
                textElements.forEach(el => {
                    el?.classList.remove('sidebar-text-visible');
                    el?.classList.add('sidebar-text-hidden');
                });
                isOpen = false;
                if (persist) {
                    localStorage.setItem('userSidebarOpen', 'false');
                }
                syncSidebarStateUi();
            }

            if (isSimple) {
                sidebar?.classList.remove('overflow-y-auto');
                sidebar?.classList.add('overflow-y-hidden');
            }

            toggleButton?.addEventListener('click', () => {
                isOpen ? closeSidebar({ persist: false }) : openSidebar({ persist: false });
            });

            desktopToggleButton?.addEventListener('click', () => {
                isOpen ? closeSidebar() : openSidebar();
            });

            if (pdsTrigger) {
                pdsTrigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    togglePdsMenu();
                });
            }

            function applyResponsiveSidebarState() {
                storedState = localStorage.getItem('userSidebarOpen');
                isOpen = storedState === null ? true : storedState === 'true';

                if (isDesktopViewport()) {
                    if (isOpen) {
                        openSidebar({ persist: false });
                    } else {
                        closeSidebar({ persist: false });
                    }
                    return;
                }

                closeSidebar({ persist: false });
            }

            applyResponsiveSidebarState();

            // Auto open/close on resize
            let userSidebarResizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(userSidebarResizeTimer);
                userSidebarResizeTimer = setTimeout(() => {
                    applyResponsiveSidebarState();
                }, 150);
            });
        });
    </script>

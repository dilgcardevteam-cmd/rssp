<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DILG Dashboard')</title>

    <!-- Tailwind CSS + Alpine + Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine Plugins -->
    <script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Alpine Core -->
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .badge-notification {
            position: absolute;
            top: -0.2rem;
            right: -0.3rem;
            background-color: #ef4444;
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0 0.3rem;
            border-radius: 9999px;
            line-height: 1rem;
            user-select: none;
        }

        .sidebar a {
            display: flex;
            align-items: center;
        }

        /* Custom scrollbar for left panel */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: #374151;
            /* Tailwind gray-700 */
            border-radius: 9999px;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
            appearance: textfield;
        }

        .page-enter {
            opacity: 0;
        }

        .page-enter.page-ready {
            opacity: 1;
            transition: opacity 180ms ease-out;
        }

        @media (prefers-reduced-motion: reduce) {
            .page-enter,
            .page-enter.page-ready {
                opacity: 1;
                transition: none;
            }
        }
    </style>

    @include('partials.global_toast')
    @stack('styles')
</head>

@php
$lockScreenScroll = request()->routeIs('vacancies_management')
        || request()->routeIs('applications_list')
        || request()->routeIs('admin_activity_log')
        || request()->routeIs('admin_exam_management')
        || request()->routeIs('admin.manage_exam')
        || request()->routeIs('admin.positions.*');
@endphp

<body class="bg-[#F1F6FC] h-screen font-sans font-montserrat text-gray-900 overflow-hidden">

    <!-- Full-page loader overlay -->
    <div id="loader" class="hidden fixed inset-0 z-[9999] bg-[#F1F6FC]/80 backdrop-blur-sm flex items-center justify-center">
        <div class="flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-full border-4 border-[#002C76]/20 border-t-[#002C76] animate-spin"></div>
            <span class="text-sm font-semibold text-[#002C76] tracking-wide">Loading...</span>
        </div>
    </div>

    <!-- App Container: Sidebar + Content -->
    <div id="app-wrapper" class="flex h-screen w-full overflow-hidden">

        <div class="lg:hidden">
            @include('partials.mobile-sidebar-admin')
        </div>

        <div class="hidden lg:block">
            @include('partials.sidebar_admin')
        </div>

        <!-- Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative min-w-0">
            <!-- Top Header (Notification Bell & Profile) -->
            <header
                class="flex justify-end items-center px-6 sm:px-8 md:px-10 pt-5 sm:pt-6 pb-3 shrink-0 z-50 ">
                <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-white/80 backdrop-blur-sm px-2 py-1 shadow-sm">
                    <!-- Notification Bell -->
                    <div id="notifBell" class="relative">
                        <button id="notifToggle" aria-label="Notifications"
                            class="relative h-10 w-10 rounded-full text-slate-500 hover:text-[#0D2B70] hover:bg-slate-100/80 transition-colors">
                            <i data-feather="bell" class="w-5 h-5 mx-auto"></i>
                            <span id="notifBadge"
                                class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center border-2 border-white"
                                style="display: none;">0</span>
                        </button>

                        <div id="notifMenu"
                            class="hidden absolute right-0 mt-3 w-[24rem] sm:w-[26rem] bg-white shadow-2xl rounded-2xl border border-slate-200 overflow-hidden z-50 transform origin-top-right transition-all duration-200">
                            <div
                                class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
                                <h3 class="font-bold text-[#0D2B70] text-base">Notifications</h3>
                                <button id="notifMarkAll"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                    Mark all as read
                                </button>
                            </div>

                            <ul id="notifList" class="max-h-[420px] overflow-y-auto divide-y divide-slate-100 scrollbar-thin"></ul>

                            <div class="px-4 py-3 bg-white border-t border-slate-100 text-center">
                                <a href="{{ route('admin.notifications.index', [], false) }}"
                                    class="text-xs font-bold text-[#0D2B70] hover:text-blue-700 hover:underline">
                                    View Full History
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-slate-200"></div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 rounded-full pl-1 pr-2 py-1 focus:outline-none hover:bg-slate-100/80 transition-colors">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold text-slate-800 leading-tight">
                                    {{ Auth::guard('admin')->user()->name ?? 'Admin User' }}
                                </p>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wide">
                                    {{ match (Auth::guard('admin')->user()->role ?? 'admin') {
                                        'superadmin' => 'Superadmin',
                                        'admin' => 'Admin (HR)',
                                        'hr_division' => 'HR Division',
                                        'viewer' => 'Viewer',
                                        default => 'Administrator',
                                    } }}
                                </p>
                            </div>
                            <div
                                class="w-9 h-9 rounded-full bg-[#0D2B70] text-white flex items-center justify-center font-bold text-base shadow-sm">
                                {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                            </div>
                            <i data-feather="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>

                        <!-- Profile Menu -->
                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 mt-3 w-52 bg-white border border-slate-200 rounded-xl shadow-xl z-50 py-1"
                            style="display: none;" x-cloak>
                            @if(in_array((Auth::guard('admin')->user()->role ?? null), ['superadmin', 'admin'], true))
                                <a href="{{ route('admin.account.settings') }}"
                                    class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#0D2B70]">
                                    <i data-feather="settings" class="w-4 h-4 inline-block mr-2"></i> Account Settings
                                </a>
                            @endif
                            <div class="border-t border-slate-100 my-1"></div>
                            <form id="adminLogoutForm" method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="button" @click.prevent="$dispatch('open-logout-confirm')"
                                    class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-medium">
                                    <i data-feather="log-out" class="w-4 h-4 inline-block mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Scrollable -->
            <main id="page-shell"
                class="page-enter flex-1 {{ $lockScreenScroll ? 'overflow-hidden pb-0' : 'overflow-y-auto overflow-x-hidden pb-6 sm:pb-8 md:pb-10' }} @yield('main-padding', 'px-6 sm:px-8 md:px-10') pt-0 relative scroll-smooth">
                @yield('content')
            </main>
        </div>

    </div>
</body>

<!-- REUSABLE CONFIRMATION MODAL -->
<x-confirm-modal title="Confirm Logout" message="Are you sure you want to log out?" event="open-logout-confirm"
    confirm="confirm-logout" />


<!-- JS Scripts -->
<script>
    feather.replace();

    const form = document.querySelector('form');
    const loader = document.getElementById('loader');

    if (form) {
        form.addEventListener('submit', () => {
            loader?.classList.remove('hidden');
        });
    }

document.addEventListener('DOMContentLoaded', () => {
        const alpineProbe = document.querySelector('[x-data]');
        const alpineOperational = Boolean(
            window.Alpine && alpineProbe && (alpineProbe._x_dataStack || alpineProbe.__x)
        );

        if (!alpineOperational) {
            document.querySelectorAll('[data-utils-panel]').forEach((panel) => {
                const key = panel.getAttribute('data-utils-panel');
                const initialOpen = panel.getAttribute('data-initial-open') === '1';
                panel.style.display = initialOpen ? '' : 'none';

                const toggle = document.querySelector(`[data-utils-toggle="${key}"]`);
                if (!toggle) return;
                toggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    const isHidden = panel.style.display === 'none';
                    panel.style.display = isHidden ? '' : 'none';
                });
            });
        }

        requestAnimationFrame(() => {
            document.getElementById('page-shell')?.classList.add('page-ready');
        });

        @if (session('applicant_deletion_daily_notice'))
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(@json(session('applicant_deletion_daily_notice')), 'success', 7000);
            }
        @endif

        document.querySelectorAll('a.use-loader').forEach(link => {
            link.addEventListener('click', function (e) {
                if (this.target !== '_blank') {
                    e.preventDefault();
                    loader?.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 100);
                }
            });
        });

        const notifToggle = document.getElementById('notifToggle');
        const notifMenu = document.getElementById('notifMenu');
        const notifBadge = document.getElementById('notifBadge');
        const notifList = document.getElementById('notifList');
        const notifMarkAll = document.getElementById('notifMarkAll');
        let loading = false;
        const normalizeNotificationUrl = (targetUrl) => {
            if (!targetUrl) return '';
            try {
                const parsed = new URL(targetUrl, window.location.origin);
                if (parsed.origin !== window.location.origin) {
                    return `${parsed.pathname}${parsed.search}${parsed.hash}`;
                }
                return parsed.href;
            } catch (_) {
                return targetUrl;
            }
        };

        window.normalizeNotificationUrl = normalizeNotificationUrl;

        const iconMap = {
            success: 'check',
            warning: 'alert-triangle',
            error: 'x',
            info: 'bell'
        };
        const iconClassMap = {
            success: 'bg-emerald-50 text-emerald-600',
            warning: 'bg-amber-50 text-amber-600',
            error: 'bg-red-50 text-red-600',
            info: 'bg-slate-100 text-slate-500'
        };

        function formatTime(value) {
            const ts = new Date(value);
            if (Number.isNaN(ts.getTime())) return '';
            const seconds = Math.floor((Date.now() - ts.getTime()) / 1000);
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
            if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
            return ts.toLocaleString();
        }

        function renderEmptyState() {
            if (!notifList) return;
            notifList.innerHTML = `
                <li class="px-5 py-10 text-center text-slate-500">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3">
                        <i data-feather="bell-off" class="w-5 h-5 text-slate-400"></i>
                    </div>
                    <p class="text-sm">No notifications yet</p>
                </li>
            `;
        }

        function renderNotificationItems(items) {
            if (!notifList) return;
            if (!Array.isArray(items) || items.length === 0) {
                renderEmptyState();
                if (window.feather) feather.replace();
                return;
            }

            notifList.innerHTML = '';
            items.forEach((n) => {
                const level = n?.data?.level || n?.type || 'info';
                const iconName = iconMap[level] || iconMap.info;
                const iconTone = iconClassMap[level] || iconClassMap.info;
                const unread = !n.read_at;

                const li = document.createElement('li');
                li.className = `px-5 py-4 hover:bg-slate-50 transition-colors cursor-pointer ${unread ? 'bg-blue-50/30' : 'bg-white'}`;
                li.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 ${iconTone}">
                            <i data-feather="${iconName}" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm ${unread ? 'font-semibold' : 'font-medium'} text-[#0D2B70] leading-5">
                                    ${n?.data?.title || 'Notification'}
                                </p>
                                <span class="text-[11px] text-slate-400 whitespace-nowrap">${formatTime(n.created_at)}</span>
                            </div>
                            <p class="text-sm text-slate-600 mt-1 leading-6">
                                ${n?.data?.message || ''}
                            </p>
                        </div>
                    </div>
                `;

                li.addEventListener('click', async () => {
                    try {
                        await fetch(`/notifications/${n.id}/read`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            keepalive: true
                        });
                    } catch (_) {}

                    fetchCount();

                    const targetUrl = n?.data?.action_url || n?.data?.link;
                    if (targetUrl) window.location.href = normalizeNotificationUrl(targetUrl);
                });

                notifList.appendChild(li);
            });

            if (window.feather) feather.replace();
        }

        function fetchCount() {
            fetch('/notifications/count')
                .then(r => r.json())
                .then(d => {
                    const count = d.count || 0;
                    if (notifBadge) {
                        notifBadge.textContent = count;
                        notifBadge.style.display = count > 0 ? 'flex' : 'none';
                    }
                });
        }

        function fetchItems() {
            if (loading) return;
            loading = true;
            fetch('/notifications/fetch')
                .then(r => r.json())
                .then(d => renderNotificationItems(d.data || d.notifications || []))
                .finally(() => { loading = false; });
        }

        notifToggle?.addEventListener('click', () => {
            notifMenu?.classList.toggle('hidden');
            if (notifMenu && !notifMenu.classList.contains('hidden')) {
                fetchItems();
                fetchCount();
            }
        });

        notifMarkAll?.addEventListener('click', () => {
            fetch('/notifications/mark-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            }).then(() => {
                fetchCount();
                fetchItems();
            });
        });

        document.addEventListener('click', (e) => {
            if (!notifMenu || notifMenu.classList.contains('hidden')) return;
            if (!notifMenu.contains(e.target) && !notifToggle?.contains(e.target)) {
                notifMenu.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') notifMenu?.classList.add('hidden');
        });

        setInterval(() => {
            fetchCount();
            if (notifMenu && !notifMenu.classList.contains('hidden')) fetchItems();
        }, 30000);

        window.addEventListener('focus', () => {
            fetchCount();
            if (notifMenu && !notifMenu.classList.contains('hidden')) fetchItems();
        });

        fetchCount();
    });

    window.addEventListener('pageshow', function (event) {
        // Hide loader when page is restored from cache
        document.getElementById('loader')?.classList.add('hidden');
        document.querySelector('.background')?.classList.add('hidden');
    });

    function viewPDF(filePath, title = 'Document') {
        const previewContainer = document.getElementById('pdf-preview');

        previewContainer.innerHTML = `
                <div class="border rounded-lg shadow p-4 mt-4">
                    <p class="font-semibold text-gray-700 mb-2">📄 ${title}</p>
                    <embed src="${filePath}" type="application/pdf" class="w-full h-96 rounded border">
                </div>
            `;
    }

    // Submit admin logout only after confirmation
    window.addEventListener('confirm-logout', () => {
        try {
            localStorage.clear();
            sessionStorage.clear();
        } catch (e) {}
        const logoutForm = document.getElementById('adminLogoutForm');
        if (logoutForm) logoutForm.submit();
    });

</script>

@include('partials.idle_logout', [
    'idleLogoutEnabled' => auth('admin')->check(),
    'idleLogoutRoute' => route('admin.logout'),
])


@stack('scripts')

</html>

<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'DILG Dashboard')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/dilg_logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/dilg_logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @php
        $metaTitle = 'DILG - CAR Recruitment Selection and Placement Portal';
        $metaDescription = 'Isa ka bang "Matino, Mahusay, at Maaasahan" na manggagawang Pilipino?';
        $metaImage = asset('images/dilg_rsp_thumbnail.png');
        $metaUrl = url()->current();

        if (request()->is('login')) {
            $metaTitle = 'Login - DILG Recruitment Portal';
            $metaDescription = 'Access your account to view job vacancies and submit your application.';
            $metaImage = asset('images/dilg_login_thumbnail.png');
        } elseif (request()->is('jobs/*')) {
            $metaTitle = 'View Job Vacancy - DILG CAR';
            $metaDescription = 'Explore available job opportunities and join our team at DILG CAR.';
        }
    @endphp

    <!-- Open Graph Meta -->
    <meta property="og:title" content="{{ $metaTitle }}" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:image" content="{{ $metaImage }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:url" content="{{ $metaUrl }}" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $metaTitle }}" />
    <meta name="twitter:description" content="{{ $metaDescription }}" />
    <meta name="twitter:image" content="{{ $metaImage }}" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])

    <!-- Styles -->
    <style>
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

        .sidebar-transition {
            transition: width 0.4s ease, padding 0.4s ease;
        }

        .sidebar-text-hidden {
            opacity: 0;
            pointer-events: none;
            width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-text-visible {
            opacity: 1;
            pointer-events: auto;
            width: auto;
            transition: all 0.3s ease;
        }

        .logo-transition {
            transition: all 0.3s ease;
        }

        .logo-small {
            max-width: 48px;
            max-height: 48px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
        }

        /* Remove number input arrows */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
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

        @media (max-width: 1024px) {
            .sidebar-desktop {
                display: none;
            }
        }
    </style>

    @include('partials.global_toast')
    @stack('styles')
</head>

<body x-data="{ mobileSidebarOpen: false, showLogoutModal: false }"
    class="bg-[#F3F8FF] min-h-screen font-montserrat text-gray-900 overflow-x-hidden">

    <!-- 🔥 Mobile Toggle Button -->
    <button @click="mobileSidebarOpen = true"
        class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-white rounded-full shadow-md mt-4">
        <i data-feather="menu" class="w-5 h-5"></i>
    </button>

    <!-- 📱 Mobile Sidebar (only visible on small screens) -->
    <div class="lg:hidden">
        @include('partials.mobile-sidebar')
    </div>

    <!-- 💻 Main Layout -->
    <div class="flex h-screen w-full overflow-hidden">

        <!-- 🖥️ Desktop Sidebar (hidden on mobile) -->
        <div class="sidebar-desktop">
            @include('partials.sidebar')
        </div>

        <!-- Main Content -->
        <main id="page-shell" class="page-enter flex-1 overflow-y-auto pt-0 lg:ml-[20.5rem] transition-all duration-300">
            <header
                class="sticky top-0 z-40 bg-[#F3F8FF] backdrop-blur px-4 sm:px-8 py-3 flex items-center justify-end gap-6">
                <div id="notifBell" class="relative group">
                    <button id="notifToggle" aria-label="Notifications"
                        class="relative p-2 rounded-full hover:bg-blue-50 transition-colors group-hover:bg-blue-50">
                        <i data-feather="bell" class="w-6 h-6 text-[#0D2B70]"></i>
                        <span id="notifBadge"
                            class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center border-2 border-[#F3F8FF]"
                            style="display: none;">0</span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="notifMenu"
                        class="hidden absolute right-0 mt-3 w-80 sm:w-96 bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden transform origin-top-right transition-all duration-200 z-50">
                        <!-- Header -->
                        <div
                            class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-10">
                            <h3 class="font-bold text-[#0D2B70] text-base">Notifications</h3>
                            <button id="notifMarkAll"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                Mark all as read
                            </button>
                        </div>

                        <!-- List -->
                        <ul id="notifList" class="max-h-[400px] overflow-y-auto divide-y divide-gray-50 scrollbar-thin">
                            <!-- Items will be injected here via JS -->
                        </ul>

                        <!-- Footer -->
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-center">
                            <a href="{{ route('notifications.index', [], false) }}"
                                class="text-xs font-bold text-[#0D2B70] hover:text-blue-700 hover:underline">
                                View Full History
                            </a>
                        </div>

                        <!-- Loader (Hidden by default) -->
                        <div id="notifLoader"
                            class="hidden absolute inset-0 bg-white/80 flex items-center justify-center z-20">
                            <div
                                class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <button id="profileToggle" aria-label="Profile menu"
                        class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
                        @php
                            $u = Auth::user();
                            $accountMiddleInitial = filled($u?->middle_name)
                                ? mb_substr(trim((string) $u->middle_name), 0, 1) . '.'
                                : '';
                            $accountNameParts = array_filter([
                                trim((string) ($u?->first_name ?? '')),
                                $accountMiddleInitial,
                                trim((string) ($u?->last_name ?? '')),
                            ], fn($part) => $part !== '');
                            $accountDisplayName = $accountNameParts ? trim(implode(' ', $accountNameParts)) : null;

                            $displayName = $accountDisplayName ?: ($u?->name ?: 'N/A');

                            $avatar = $u?->avatar_path ? asset('storage/' . $u->avatar_path) : null;
                        @endphp
                        <div class="h-8 w-8 overflow-hidden rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                            @if($avatar)
                                <img
                                    src="{{ $avatar }}"
                                    alt="Avatar"
                                    class="h-8 w-8 rounded-full object-cover"
                                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                >
                                <div class="hidden h-8 w-8 flex items-center justify-center bg-slate-100 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 10-16 0"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                            @else
                                <div class="h-8 w-8 flex items-center justify-center bg-slate-100 text-slate-500" aria-label="Profile placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 10-16 0"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <span class="text-sm font-semibold">{{ $displayName }}</span>
                        <i data-feather="chevron-down" class="w-4 h-4"></i>
                    </button>
                    <div id="profileMenu"
                        class="hidden absolute right-0 mt-2 w-56 bg-white shadow-lg rounded-lg border border-gray-200 p-2">
                        <a href="{{ route('account.settings') }}"
                            class="block px-3 py-2 text-sm rounded hover:bg-gray-100">
                            <i data-feather="settings" class="w-4 h-4 inline-block mr-2"></i> Account Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-3 py-2 text-sm rounded hover:bg-gray-100">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            <!-- <div class="p-3 sm:p-10 pt-8 mt-0 sm:mt-1 space-y-10"> -->
            <div class="mt-0 sm:mt-1 space-y-10">
                @yield('content')
            </div>
        </main>

        <!-- Chatbot -->
        @if(!request()->routeIs('about'))
            @include('partials.chat_ai')
        @endif
    </div>

    <!-- Feather + Sidebar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();
            requestAnimationFrame(() => {
                document.getElementById('page-shell')?.classList.add('page-ready');
            });

            const notifToggle = document.getElementById('notifToggle');
            const notifMenu = document.getElementById('notifMenu');
            const notifBadge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notifList');
            const notifLoadMore = document.getElementById('notifLoadMore');
            const notifMarkAll = document.getElementById('notifMarkAll');
            let page = 1;
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

            function renderNotifications(items) {
                const list = Array.isArray(items) ? items : [];
                const fragment = document.createDocumentFragment();
                notifList.innerHTML = '';

                if (!list.length) {
                    const empty = document.createElement('li');
                    empty.className = 'px-5 py-8 text-center text-sm text-slate-500';
                    empty.textContent = 'No notifications yet.';
                    fragment.appendChild(empty);
                    notifList.appendChild(fragment);
                    return;
                }

                list.forEach((n) => {
                    const level = (n?.data?.level || 'info').toLowerCase();
                    const unread = !n?.read_at;
                    const item = document.createElement('li');
                    item.className = `px-4 py-3 transition-colors cursor-pointer ${unread ? 'bg-blue-50/30' : 'bg-white'} hover:bg-slate-50`;

                    item.innerHTML = `
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm ${unread ? 'font-semibold' : 'font-medium'} text-[#0D2B70]">${n?.data?.title || 'Notification'}</p>
                                <p class="text-xs text-slate-600 mt-1 line-clamp-2">${n?.data?.message || ''}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap">${n?.created_at ? new Date(n.created_at).toLocaleString() : ''}</span>
                        </div>
                    `;

                    item.addEventListener('click', async () => {
                        try {
                            await fetch("/notifications/" + n.id + "/read", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                keepalive: true
                            });
                        } catch (e) {
                            // Ignore and allow navigation
                        } finally {
                            fetchCount();
                        }

                        const targetUrl = n?.data?.action_url || n?.data?.link;
                        if (targetUrl) {
                            window.location.href = normalizeNotificationUrl(targetUrl);
                        }
                    });

                    fragment.appendChild(item);
                });

                notifList.appendChild(fragment);
            }

            function fetchCount() {
                fetch("/notifications/count", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => {
                        notifBadge.textContent = d.display ?? (d.count >= 100 ? '99+' : d.count);
                        notifBadge.style.display = d.count > 0 ? 'flex' : 'none';
                    });
            }
            function fetchItems(reset = false) {
                if (loading) return; loading = true;
                if (reset) { page = 1; notifList.innerHTML = ''; }
                fetch("/notifications/fetch?page=" + page, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => {
                        renderNotifications(d.data || []);
                        page = d.current_page + 1;
                        if (notifLoadMore) {
                            notifLoadMore.style.display = d.next_page_url ? 'block' : 'none';
                        }
                    }).finally(() => { loading = false; });
            }
            notifToggle?.addEventListener('click', () => {
                notifMenu.classList.toggle('hidden');
                if (!notifMenu.classList.contains('hidden')) { fetchItems(true); fetchCount(); }
            });
            notifLoadMore?.addEventListener('click', () => fetchItems(false));
            notifMarkAll?.addEventListener('click', () => {
                fetch("/notifications/mark-all", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(() => { fetchCount(); notifMenu.classList.add('hidden'); });
            });
            const profileToggle = document.getElementById('profileToggle');
            const profileMenu = document.getElementById('profileMenu');
            profileToggle?.addEventListener('click', () => profileMenu.classList.toggle('hidden'));
            setInterval(fetchCount, 15000);
            fetchCount();
            const isAuthed = @json(auth()->check());
            const channelId = @json(auth()->id());
            if (window.Echo && isAuthed && channelId) {
                window.Echo.private('notifications.' + channelId).listen('.NewSystemNotification', (event) => {
                    fetchCount();
                    if (!notifMenu?.classList.contains('hidden')) {
                        fetchItems(true);
                    }
                    if (typeof showAppToast === 'function') {
                        const title = event?.data?.title ? String(event.data.title).trim() : 'New notification';
                        const message = event?.data?.message ? String(event.data.message).trim() : '';
                        const toastMessage = message !== '' ? `${title}: ${message}` : title;
                        const toastType = event?.data?.level ? String(event.data.level).toLowerCase() : 'info';
                        showAppToast(toastMessage, toastType);
                    }
                });
            }
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Hide loader when page is restored from cache
                document.getElementById('loader')?.classList.add('hidden');
            }
        });
    </script>
    <script>
        (function () {
            function clearPdsDraftStorage() {
                const prefixes = ['dilg-car:pds:', 'pds:'];
                const stores = [window.localStorage, window.sessionStorage];

                stores.forEach((store) => {
                    try {
                        const keysToRemove = [];
                        for (let i = 0; i < store.length; i += 1) {
                            const key = store.key(i);
                            if (!key) continue;
                            if (prefixes.some((prefix) => key.startsWith(prefix))) {
                                keysToRemove.push(key);
                            }
                        }
                        keysToRemove.forEach((key) => store.removeItem(key));
                    } catch (_) {
                        // Ignore storage access errors.
                    }
                });
            }

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form || form.tagName !== 'FORM') return;
                const action = (form.getAttribute('action') || '').toLowerCase();
                if (!action.includes('/logout')) return;
                clearPdsDraftStorage();
            }, true);
        })();
    </script>

    @include('partials.idle_logout', [
        'idleLogoutEnabled' => auth()->check(),
        'idleLogoutRoute' => route('logout'),
    ])

    @stack('scripts')
</body>

</html>

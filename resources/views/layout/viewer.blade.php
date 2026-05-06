<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'DILG Dashboard')</title>

    <!-- Tailwind CSS + Alpine + Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

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
    </style>

    @include('partials.global_toast')
    @stack('styles')
</head>

<body class="bg-[#F3F8FF] h-screen font-sans font-montserrat text-gray-900 overflow-hidden">

    <!-- App Container: Sidebar + Content -->
    <div class="flex h-screen w-full overflow-hidden">

        {{-- Sidebar --}}
        @include('partials.sidebar_viewer')

        {{-- Global Logout --}}
        <form id="viewerAdminLogoutForm" method="POST" action="{{ route('admin.logout') }}" class="hidden">
            @csrf
        </form>

        {{-- Main Content Scrollable --}}
        <main class="flex-1 overflow-y-auto p-10 pt-14 space-y-10">
            @yield('content')
        </main>

    </div>

    <!-- JS Scripts -->
    <script>
        feather.replace();

        const sidebar = document.getElementById('sidebar');
        const textElements = [
            "sidebarText",
            "textExamManagement",
            "textLogOut"
        ].map(id => document.getElementById(id));

        const logo = document.querySelector('img[alt="DILG Logo"]');
        const toggleButton = document.getElementById('toggleSidebar');
        let isOpen = localStorage.getItem('sidebarOpen') === 'true'; // Retrieve sidebar state from localStorage

        function openSidebar() {
            sidebar.classList.remove('w-16');
            sidebar.classList.add('w-72');
            logo.classList.remove('logo-small');
            textElements.forEach(el => {
                el.classList.remove('sidebar-text-hidden');
                el.classList.add('sidebar-text-visible');
            });
            isOpen = true;
            localStorage.setItem('sidebarOpen', 'true'); // Save state to localStorage
        }

        function closeSidebar() {
            sidebar.classList.remove('w-72');
            sidebar.classList.add('w-16');
            logo.classList.add('logo-small');
            textElements.forEach(el => {
                el.classList.remove('sidebar-text-visible');
                el.classList.add('sidebar-text-hidden');
            });
            isOpen = false;
            localStorage.setItem('sidebarOpen', 'false'); // Save state to localStorage
        }

        toggleButton?.addEventListener('click', () => {
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        window.onload = () => {
            if (isOpen) {
                openSidebar();
            } else {
                closeSidebar();
            }
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function enforce(input) {
                if (!input) return;
                function normalize() {
                    const v = input.value;
                    if (!v) return;
                    const parts = v.split('-');
                    if (!parts.length) return;
                    const y = parts[0];
                    if (y && y.length > 4) {
                        parts[0] = y.slice(0, 4);
                        const nv = parts.filter(Boolean).join('-');
                        if (nv !== v) input.value = nv;
                    }
                }
                input.addEventListener('input', normalize);
                input.addEventListener('change', normalize);
                if (input._flatpickr) {
                    const fp = input._flatpickr;
                    const fn = function (selectedDates, dateStr, instance) {
                        if (dateStr) {
                            const ps = dateStr.split('-');
                            if (ps[0] && ps[0].length > 4) {
                                ps[0] = ps[0].slice(0, 4);
                                instance.setDate(ps.filter(Boolean).join('-'), false);
                            }
                        }
                    };
                    const oc = fp.config.onChange;
                    if (Array.isArray(oc)) {
                        oc.push(fn);
                    } else if (oc) {
                        fp.config.onChange = [oc, fn];
                    } else {
                        fp.config.onChange = [fn];
                    }
                }
            }
            document.querySelectorAll('input[type="date"]').forEach(enforce);
            const ob = new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    m.addedNodes.forEach(function (n) {
                        if (n.nodeType !== 1) return;
                        if (n.tagName === 'INPUT' && n.type === 'date') enforce(n);
                        if (n.querySelectorAll) n.querySelectorAll('input[type="date"]').forEach(enforce);
                    });
                });
            });
            ob.observe(document.body, { childList: true, subtree: true });
        });
    </script>

    <script>
        const form = document.querySelector('form');
        const loader = document.getElementById('loader');

        if (form) {
            form.addEventListener('submit', () => {
                loader?.classList.remove('hidden');
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('a.use-loader').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    loader?.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 100);
                });
            });
        });

        window.addEventListener('pageshow', function (event) {
            // Hide loader when page is restored from cache
            document.getElementById('loader')?.classList.add('hidden');
            document.querySelector('.background')?.classList.add('hidden');
        });
    </script>
    <script>
        window.addEventListener('confirm-logout', () => {
            try {
                localStorage.clear();
                sessionStorage.clear();
            } catch (e) {}
            const f = document.getElementById('viewerAdminLogoutForm');
            if (f) f.submit();
        });
    </script>
    <script>
function viewPDF(filePath, title = 'Document') {
    const previewContainer = document.getElementById('pdf-preview');

    previewContainer.innerHTML = `
        <div class="border rounded-lg shadow p-4 mt-4">
            <p class="font-semibold text-gray-700 mb-2">📄 ${title}</p>
            <embed src="${filePath}" type="application/pdf" class="w-full h-96 rounded border">
        </div>
    `;
}
</script>

    @include('partials.idle_logout', [
        'idleLogoutEnabled' => auth('admin')->check(),
        'idleLogoutRoute' => route('admin.logout'),
    ])

    @stack('scripts')
</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Personal Data Sheet - CS Form 212</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <script src="https://unpkg.com/alpinejs" defer></script> -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        (function earlySimpleEnforce(){
            try {
                var url = new URL(window.location.href);
                var isPdsPath = /\/pds\//i.test(url.pathname) || /\/c[1-5]|\/wes/i.test(url.pathname);
                if (isPdsPath && !url.searchParams.has('simple')) {
                    url.searchParams.set('simple','1');
                    window.location.replace(url.toString());
                }
            } catch(e){}
        })();
        document.addEventListener('DOMContentLoaded', function(){
            if (window.feather && typeof window.feather.replace === 'function') {
                window.feather.replace();
            }
        });
    </script>
    <script>
        (function(){
            async function refreshCsrfToken() {
                try {
                    const response = await fetch('/csrf-token', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) {
                        return null;
                    }
                    const data = await response.json();
                    if (data && data.token) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) {
                            meta.setAttribute('content', data.token);
                        }
                        document.querySelectorAll('input[name="_token"]').forEach((input) => {
                            input.value = data.token;
                        });
                        return data.token;
                    }
                } catch (e) {
                    return null;
                }
                return null;
            }
            async function ensureFreshToken() {
                await refreshCsrfToken();
            }
            function wireSubmitRefresh() {
                document.addEventListener('submit', async function(event){
                    const form = event.target;
                    if (!form || form.dataset.csrfRefreshing === '1') {
                        return;
                    }
                    event.preventDefault();
                    form.dataset.csrfRefreshing = '1';
                    await ensureFreshToken();
                    if (form.requestSubmit) {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                    setTimeout(function() {
                        delete form.dataset.csrfRefreshing;
                    }, 0);
                }, true);
            }
            function isBackForward() {
                const navEntries = performance.getEntriesByType('navigation');
                if (navEntries && navEntries.length > 0) {
                    return navEntries[0].type === 'back_forward';
                }
                return false;
            }
            document.addEventListener('DOMContentLoaded', function(){
                wireSubmitRefresh();
                if (isBackForward()) {
                    ensureFreshToken();
                }
            });
            window.addEventListener('pageshow', function(event){
                if (event.persisted) {
                    // Hide loader when page is restored from cache instead of reloading
                    document.getElementById('loader')?.classList.add('hidden');
                }
            });
        })();
    </script>
    @include('partials.global_toast')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }

        .animate-slide-up {
            animation: slideUp 0.3s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #3b82f6;
            border-color: #6B7280;
        }

        /* Floating label styles */
        .floating-label {
            transition: all 0.2s ease-out;
        }

        .floating-label-input:focus + .floating-label,
        .floating-label-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.25rem) scale(0.85);
            color: #6B7280;
            background-color: white;
            padding: 0 0.25rem;
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Progress indicator */
        .progress-step {
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background-color: #3b82f6;
            color: white;
        }

        .progress-step.completed {
            background-color: #10b981;
            color: white;
        }

        .progress-step.available {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        /* Arrow styles */
        .arrow {
            transition: all 0.3s ease;
            margin: 0 0.5rem;
            font-weight: bold;
            color: #d1d5db;
        }

        .arrow.completed {
            color: #10b981;
        }

        .arrow.active {
            color: #3b82f6;
        }

        /* For Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }

        /* For Firefox */
        input[type=number] {
          -moz-appearance: textfield;
        }

        label, .caps {
            text-transform: uppercase;
        }

        input:not([type=email]):not([type=password]):not([type=hidden]):not([type=date]):not([type=datetime-local]):not([type=month]):not([type=week]):not([type=time]):not([type=number]):not([type=range]):not([type=color]):not([type=checkbox]):not([type=radio]):not([type=file]):not([data-uppercase=off]),
        textarea:not([data-uppercase=off]) {
            text-transform: uppercase;
        }

        .floating-label-input {
            font-size: clamp(0.95rem, 0.9rem + 0.3vw, 1.1rem);
            line-height: 1.55;
            padding: 0.9rem 1rem;
            letter-spacing: 0.01em;
            color: #111827;
        }
        .floating-label {
            font-size: clamp(0.85rem, 0.8rem + 0.25vw, 0.98rem);
            color: #374151;
            letter-spacing: 0.02em;
        }
        @media (max-width: 640px) {
            .floating-label-input {
                padding: 0.75rem 0.9rem;
                line-height: 1.5;
            }
            .floating-label {
                font-size: 0.9rem;
            }
        }
        .floating-label-input::placeholder {
            color: #6B7280;
        }
        .floating-label-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        /* Validation styles */
        .error-field {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2147483647;
            max-width: 400px;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            transform: translateX(100%);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .notification.success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }

        .notification.warning {
            background-color: #fffbeb;
            border: 1px solid #fed7aa;
            color: #d97706;
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .mobile-stack {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .mobile-full-width {
                width: 100% !important;
            }
            
            .mobile-text-sm {
                font-size: 0.875rem;
            }
            
            .mobile-py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
            
            .mobile-px-3 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            /* Hide desktop progress text on mobile */
            .progress-step span:not(.material-icons) {
                display: none;
            }
            
            /* Make progress steps smaller on mobile */
            .progress-step {
                padding: 0.5rem;
                min-width: auto;
            }

            .notification {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }

        /* Mobile Navigation Menu in Header */
        @media (max-width: 768px) {
            .mobile-nav-menu {
                background: white;
                border-top: 1px solid #e5e7eb;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .mobile-nav-menu.open {
                max-height: 400px;
                overflow-y: auto;
            }
            
            .nav-menu-item {
                display: flex;
                align-items: center;
                padding: 0.875rem 1rem;
                border-bottom: 1px solid #f3f4f6;
                transition: all 0.2s ease;
                cursor: pointer;
                text-decoration: none;
                color: inherit;
                background: white;
            }
            
            .nav-menu-item:hover {
                background: #f8fafc;
            }
            
            .nav-menu-item.active {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                color: white;
                border-left: 4px solid #1e40af;
            }
            
            .nav-menu-item.completed {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                border-left: 4px solid #047857;
            }
            
            .nav-menu-item.available {
                color: #6b7280;
                background: white;
            }
            
            .nav-menu-item:last-child {
                border-bottom: none;
            }
            
            .nav-item-icon {
                margin-right: 0.75rem;
                min-width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .nav-item-content {
                flex: 1;
                min-width: 0;
            }
            
            .nav-item-title {
                font-weight: 600;
                font-size: 0.875rem;
                margin-bottom: 0.125rem;
                line-height: 1.2;
            }
            
            .nav-item-description {
                font-size: 0.75rem;
                opacity: 0.8;
                line-height: 1.3;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .nav-item-status {
                display: flex;
                align-items: center;
                font-size: 0.7rem;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.025em;
                margin-left: 0.5rem;
                flex-shrink: 0;
            }
            
            .nav-item-status span:last-child {
                margin-left: 0.25rem;
            }
        }

        /* Ensure proper header stacking */
        @media (max-width: 768px) {
            header {
                position: sticky;
                top: 0;
                z-index: 50;
            }
        }

        /* Hide mobile nav on desktop */
        @media (min-width: 769px) {
            .mobile-nav-menu {
                display: none !important;
            }
        }
    </style>
    @livewireStyles
</head>
@php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
@endphp
<body class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen">
    <!-- Notification Container -->
    <div id="notificationContainer"></div>

        @if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-[2147483647] bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
    @endif

    @php
        $pdsErrorMessages = collect();
        if ($errors->any()) {
            $pdsErrorMessages = $pdsErrorMessages->merge($errors->all());
        }
        if (session('error')) {
            $pdsErrorMessages = $pdsErrorMessages->push(session('error'));
        }
    @endphp

    @if ($pdsErrorMessages->isNotEmpty())
    <div id="pds-error-banner" class="mx-auto max-w-7xl px-2 pt-4 sm:px-4 lg:px-8 md:ml-20 lg:ml-[20.5rem] relative z-[60]">
        <div class="rounded-2xl border border-red-200 bg-red-50 text-red-800 shadow-lg">
            <div class="flex items-start gap-3 px-4 py-4">
                <span class="material-icons mt-0.5 text-red-600">error</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold uppercase tracking-wide">Error</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-6">
                        @foreach ($pdsErrorMessages as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="dismissPdsErrorBanner()" class="ml-auto rounded-lg p-1 text-red-500 transition hover:bg-red-100 hover:text-red-700" aria-label="Dismiss error message">
                    <span class="material-icons text-xl">close</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(!$simple)
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50 glass-effect">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">
                <div class="flex items-center">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-xl sm:text-2xl">article</span>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900">Personal Data Sheet</h1>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button class="md:hidden flex items-center px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors" onclick="toggleMobileNav()">
                    <span class="material-icons text-lg mr-1">menu</span>
                    <span class="text-sm font-medium">Menu</span>
                </button>
                
                <!-- Desktop Info -->
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-sm text-gray-500">CS Form No. 212 (Revised 2025)</span>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div id="mobileNavMenu" class="mobile-nav-menu md:hidden">
            <div class="max-w-7xl mx-auto px-2 sm:px-4">
                <div class="nav-menu-content">
                    <!-- Menu items will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </header>
    @endif

    @if(!$simple)
    <!-- Desktop Progress Indicator -->
    <div class="hidden md:block bg-white shadow-sm sticky top-14 sm:top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 overflow-hidden" id="progressBar">
                    <!-- Progress steps will be dynamically generated here -->
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($simple)
        <div class="flex min-h-screen w-full">
            <div class="sidebar-desktop">
                @include('partials.sidebar')
            </div>
            <main class="flex-1 overflow-x-hidden p-3 sm:p-10 pt-8 pb-10 mt-0 sm:mt-1 space-y-10 md:ml-20 lg:ml-[20.5rem] transition-all duration-300">
                @yield('content')
            </main>
        </div>
    @else
        <main class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8 mb-20 sm:mb-0">
            @yield('content')
        </main>
    @endif

    <script>
        (function enforceSimpleLayout() {
            try {
                const url = new URL(window.location.href);
                const isPdsPath = /\/pds\//i.test(url.pathname) || /\/c[1-5]|\/wes/i.test(url.pathname);
                const hasSimple = url.searchParams.has('simple');
                if (isPdsPath && !hasSimple) {
                    url.searchParams.set('simple', '1');
                    window.location.replace(url.toString());
                }
            } catch (e) {
                console.warn('Simple layout enforcement skipped:', e);
            }
        })();

        // Track completed sections for visual indicators only
        const completedSections = new Set();

        // Define all steps
        const steps = [
            {
                id: 'display_c1',
                label: 'PERSONAL INFORMATION',
                icon: 'person',
                title: 'Personal Information'
            },
            {
                id: 'display_c2',
                label: 'WORK EXPERIENCE',
                icon: 'work',  
                title: 'Work Experience'
            },
            {
                id: 'display_c3',
                label: 'LEARNING & DEVELOPMENT',
                icon: 'school',
                title: 'Learning & Development'
            },
            {
                id: 'display_c4',
                label: 'OTHER INFORMATION',
                icon: 'info',
                title: 'Other Information'
            },
            {
                id: 'display_wes',
                label: 'WORK EXPERIENCE SHEET',
                icon: 'assignment',
                title: 'Work Experience Sheet'
            },
            {
                id: 'display_c5',
                label: 'UPLOAD PDF',
                icon: 'upload_file',
                title: 'Upload PDF'
            }
        ];

        // Function to get current page from URL
        function getCurrentPageFromURL() {
            const path = window.location.pathname.toLowerCase();
            
            if (path.includes('/pds/c1') || path.includes('/c1')) return 'display_c1';
            if (path.includes('/pds/c2') || path.includes('/c2')) return 'display_c2';
            if (path.includes('/pds/c3') || path.includes('/c3')) return 'display_c3';
            if (path.includes('/pds/c4') || path.includes('/c4')) return 'display_c4';
            if (path.includes('/pds/wes') || path.includes('/wes')) return 'display_wes';
            if (path.includes('/pds/c5') || path.includes('/c5')) return 'display_c5';
            
            // Default to first page if no match
            return 'display_c1';
        }

        // Set current page based on URL
        let currentPage = getCurrentPageFromURL();

        // Initialize completed sections based on current page
        function initializeCompletedSections() {
            const stepIds = steps.map(step => step.id);
            const currentIndex = stepIds.indexOf(currentPage);
            
            // Mark all previous sections as completed for visual indicators
            for (let i = 0; i < currentIndex; i++) {
                completedSections.add(stepIds[i]);
            }
            
            console.log('Initialized completed sections based on URL:', Array.from(completedSections));
        }

        // Notification system
        function showNotification(message, type = 'error', duration = 5000) {
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(message, type, duration);
                return;
            }

            const container = document.getElementById('notificationContainer');
            if (!container) return;
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            notification.innerHTML = `
                <div class="flex items-start">
                    <span class="material-icons text-lg mr-2 mt-0.5">
                        ${type === 'error' ? 'error' : type === 'success' ? 'check_circle' : 'warning'}
                    </span>
                    <div class="flex-1">
                        <p class="font-medium">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-70 hover:opacity-100">
                        <span class="material-icons text-sm">close</span>
                    </button>
                </div>
            `;
            
            container.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Auto-remove notification
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, duration);
        }

        function dismissPdsErrorBanner() {
            const banner = document.getElementById('pds-error-banner');
            if (banner) {
                banner.remove();
            }
        }

        // Form validation functions (optional - for form quality but not navigation blocking)
        function validateField(fieldId) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(`${fieldId}-error`);
            
            if (!field) return true;
            
            let isValid = true;
            let errorMessage = '';
            
            // Clear previous error state
            field.classList.remove('error-field');
            if (errorElement) errorElement.textContent = '';
            
            // Check if field is required and empty
            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
                errorMessage = 'This field is required.';
            }
            
            // Additional validation based on field type
            if (field.value.trim()) {
                switch (field.type) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(field.value)) {
                            isValid = false;
                            errorMessage = 'Please enter a valid email address.';
                        }
                        break;
                    case 'date':
                        const date = new Date(field.value);
                        const today = new Date();
                        if (date > today) {
                            isValid = false;
                            errorMessage = 'Date cannot be in the future.';
                        }
                        break;
                }
            }
            
            // Show error state if invalid
            if (!isValid) {
                field.classList.add('error-field');
                if (errorElement) {
                    errorElement.textContent = errorMessage;
                }
                
                // Add shake animation
                field.classList.add('animate-shake');
                setTimeout(() => {
                    field.classList.remove('animate-shake');
                }, 500);
            }
            
            return isValid;
        }

        function markSectionComplete(sectionId) {
            completedSections.add(sectionId);
            console.log('Completed sections:', Array.from(completedSections));
            updateProgressBar();
            updateMobileNavMenu();
        }

        // Free navigation to any section - no restrictions
        async function navigateToSection(sectionId) {
            console.log('Navigating to:', sectionId);
            
            // If clicking on current page, do nothing
            if (sectionId === currentPage) {
                closeMobileNav();
                return true;
            }
            
            // Update current page
            currentPage = sectionId;
            updateProgressBar();
            updateMobileNavMenu();
            closeMobileNav();
            
            console.log(`Navigating to: ${sectionId}`);
            
            // Navigate to the URL - Updated mapping
            const urlMap = {
                'display_c1': '/pds/c1',
                'display_c2': '/pds/c2', 
                'display_c3': '/pds/c3',
                'display_c4': '/pds/c4',
                'display_wes': '/pds/wes',
                'display_c5': '/pds/c5'
            };
            
            console.log(`Navigating from ${currentPage} to ${sectionId}`);
            console.log(`URL will be: ${urlMap[sectionId]}`);
            
            // Navigate to the actual URL
            if (urlMap[sectionId]) {
                if (typeof window.__pdsAutosaveNow === 'function') {
                    const NAV_AUTOSAVE_MAX_WAIT_MS = 900;
                    try {
                        // Avoid blocking tab switches on a slow autosave response.
                        await Promise.race([
                            Promise.resolve(window.__pdsAutosaveNow({
                                force: false,
                                maxWaitMs: NAV_AUTOSAVE_MAX_WAIT_MS,
                            })),
                            new Promise((resolve) => setTimeout(resolve, NAV_AUTOSAVE_MAX_WAIT_MS + 150)),
                        ]);
                    } catch (error) {
                        console.warn('Autosave flush before navigation failed:', error);
                    }
                }
                window.location.href = urlMap[sectionId];
            } else {
                console.error(`No URL mapping found for section: ${sectionId}`);
                showNotification(`Navigation error: Invalid section ${sectionId}`, 'error');
            }
            
            // Show success message
            showNotification(
                `Navigating to ${steps.find(s => s.id === sectionId)?.title}...`,
                'success',
                2000
            );
            
            return true;
        }

        // Mobile Navigation Functions
        function toggleMobileNav() {
            const menu = document.getElementById('mobileNavMenu');
            if (!menu) return;
            const isOpen = menu.classList.contains('open');
            
            if (isOpen) {
                closeMobileNav();
            } else {
                openMobileNav();
            }
        }
        
        function openMobileNav() {
            const menu = document.getElementById('mobileNavMenu');
            if (!menu) return;
            menu.classList.add('open');
        }
        
        function closeMobileNav() {
            const menu = document.getElementById('mobileNavMenu');
            if (!menu) return;
            menu.classList.remove('open');
        }

        // Updated progress bar - all sections are always available
        function updateProgressBar() {
            if ({{ $simple ? 'true' : 'false' }}) return;
            const progressBar = document.getElementById('progressBar');
            if (!progressBar) return;
            
            let html = '';
            
            steps.forEach((step, index) => {
                let stepClass, iconType;
                const isCompleted = completedSections.has(step.id);
                const isCurrent = step.id === currentPage;
                
                if (isCompleted && !isCurrent) {
                    // Completed sections (not current)
                    stepClass = 'completed';
                    iconType = 'check_circle';
                } else if (isCurrent) {
                    // Current page shows as active
                    stepClass = 'active';
                    iconType = step.icon;
                } else {
                    // All other sections are available
                    stepClass = 'available';
                    iconType = step.icon;
                }
                
                const clickHandler = `onclick="console.log('Clicked: ${step.id}'); navigateToSection('${step.id}')"`;
                const cursorClass = 'cursor-pointer hover:shadow-md hover:scale-105';
                
                html += `
                    <button ${clickHandler} class="progress-step ${stepClass} flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 ${cursorClass}">
                        <span class="material-icons text-sm mr-1">${iconType}</span>
                        ${step.label}
                    </button>
                `;
                
                if (index < steps.length - 1) {
                    const arrowClass = isCompleted ? 'completed' : isCurrent ? 'active' : '';
                    html += `<span class="arrow ${arrowClass}">→</span>`;
                }
            });
            
            progressBar.innerHTML = html;
        }

        // Updated mobile nav menu - all sections are always available
        function updateMobileNavMenu() {
            if ({{ $simple ? 'true' : 'false' }}) return;
            const menuContent = document.querySelector('.nav-menu-content');
            if (!menuContent) return;
            
            let html = '';
            
            steps.forEach((step, index) => {
                const isCompleted = completedSections.has(step.id);
                const isCurrent = step.id === currentPage;
                
                let itemClass, statusIcon, statusText;
                
                if (isCompleted && !isCurrent) {
                    itemClass = 'completed';
                    statusIcon = 'check_circle';
                    statusText = 'Completed';
                } else if (isCurrent) {
                    itemClass = 'active';
                    statusIcon = 'radio_button_checked';
                    statusText = 'Current';
                } else {
                    itemClass = 'available';
                    statusIcon = 'radio_button_unchecked';
                    statusText = 'Available';
                }
                
                const clickHandler = `navigateToSection('${step.id}')`;
                
                html += `
                    <div class="nav-menu-item ${itemClass}" onclick="${clickHandler}">
                        <div class="nav-item-icon">
                            <span class="material-icons">${step.icon}</span>
                        </div>
                        <div class="nav-item-content">
                            <div class="nav-item-title">${step.title}</div>
                            <div class="nav-item-description">${getStepDescription(step.id)}</div>
                        </div>
                        <div class="nav-item-status">
                            <span class="material-icons text-sm mr-1">${statusIcon}</span>
                            <span>${statusText}</span>
                        </div>
                    </div>
                `;
            });
            
            menuContent.innerHTML = html;
        }

        function getStepDescription(stepId) {
            const descriptions = {
                'display_c1': 'Basic personal details, contact info, and identification',
                'display_c2': 'Employment history and professional background',
                'display_c3': 'Educational background and training programs',
                'display_c4': 'Additional details, references, and declarations',
                'display_wes': 'Detailed duties and accomplishments (attached sheet)',
                'display_c5': 'Generate and download your completed PDS form'
            };
            return descriptions[stepId] || '';
        }

        // Handle clicks outside menu to close it
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileNavMenu');
            const menuButton = document.querySelector('[onclick="toggleMobileNav()"]');
            
            if (menu && menu.classList.contains('open') && 
                menuButton &&
                !menu.contains(event.target) && 
                !menuButton.contains(event.target)) {
                closeMobileNav();
            }
        });

        // Handle escape key to close menu
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMobileNav();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                closeMobileNav();
            }
        });

        function initializeProgressBar() {
            updateProgressBar();
            updateMobileNavMenu();
        }

        // Add real-time validation to form fields (optional quality check)
        function setupFieldValidation() {
            const fields = document.querySelectorAll('input, textarea, select');
            
            fields.forEach(field => {
                // Validate on blur (non-blocking)
                field.addEventListener('blur', function() {
                    validateField(this.id);
                });
                
                // Clear error state on input
                field.addEventListener('input', function() {
                    if (this.classList.contains('error-field')) {
                        this.classList.remove('error-field');
                        const errorElement = document.getElementById(`${this.id}-error`);
                        if (errorElement) {
                            errorElement.textContent = '';
                        }
                    }
                });
                
                // Mobile: scroll field into view when focused
                field.addEventListener('focus', function() {
                    if (window.innerWidth < 768) {
                        setTimeout(() => {
                            this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                    }
                });
            });
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing PDS Form (No Lock Feature)...');
            console.log('URL Path:', window.location.pathname);
            console.log('Detected current page:', currentPage);
            
            // Initialize completed sections based on current URL
            initializeCompletedSections();
            
            console.log('Completed sections after URL detection:', Array.from(completedSections));
            
            initializeProgressBar();
            setupFieldValidation();
            
            // Auto-save form data (using memory instead of localStorage for demo)
            const formDataStore = {};
            
            const form = document.getElementById('personalInfoForm');
            if (form) {
                // Save data on change
                form.addEventListener('input', function(e) {
                    formDataStore[e.target.id] = e.target.value;
                    console.log('Form data updated:', formDataStore);
                });
            }
        });

        // Prevent form submission with Enter key (to avoid accidental navigation)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'submit') {
                e.preventDefault();
                
                // Move to next field instead
                const inputs = Array.from(document.querySelectorAll('input, textarea, select'));
                const currentIndex = inputs.indexOf(e.target);
                if (currentIndex < inputs.length - 1) {
                    inputs[currentIndex + 1].focus();
                }
            }
        });

        // Optional: Function to mark section as complete when user finishes filling it
        function completeCurrentSection() {
            markSectionComplete(currentPage);
            showNotification('Section marked as complete!', 'success', 3000);
        }

        // Debug function to check current state
        function debugState() {
            console.log('=== DEBUG STATE ===');
            console.log('URL Path:', window.location.pathname);
            console.log('Current page:', currentPage);
            console.log('Completed sections:', Array.from(completedSections));
            steps.forEach(step => {
                const completed = completedSections.has(step.id);
                const current = step.id === currentPage;
                console.log(`${step.id}: completed=${completed}, current=${current}`);
            });
            console.log('==================');
        }

        // Function to simulate different URLs for testing
        function simulateURL(path) {
            console.log(`\n=== SIMULATING URL: ${path} ===`);
            
            // Clear previous state
            completedSections.clear();
            
            // Temporarily change the pathname for testing
            Object.defineProperty(window.location, 'pathname', {
                writable: true,
                value: path
            });
            
            // Reinitialize based on new URL
            currentPage = getCurrentPageFromURL();
            initializeCompletedSections();
            
            console.log(`Current page: ${currentPage}`);
            console.log('Completed sections:', Array.from(completedSections));
            
            updateProgressBar();
            updateMobileNavMenu();
            
            console.log('=== SIMULATION COMPLETE ===\n');
        }

        // Function to simulate completing a section (for testing)
        function simulateCompleteSection(sectionId) {
            console.log(`\n=== COMPLETING SECTION: ${sectionId} ===`);
            markSectionComplete(sectionId);
            debugState();
        }

        // Expose functions globally for testing and manual completion
        window.debugState = debugState;
        window.simulateURL = simulateURL;
        window.simulateCompleteSection = simulateCompleteSection;
        window.completeCurrentSection = completeCurrentSection;
        window.navigateToSection = navigateToSection;
        
        // Test the initial state
        setTimeout(() => {
            console.log('=== INITIAL STATE (NO LOCK FEATURE) ===');
            console.log('All sections are freely accessible!');
            debugState();
        }, 1000);
    </script>
    <script>
        (function(){
            const keyPrefix = 'pds:';
            const userStorageKey = @json(
                auth()->check()
                    ? ('uid:' . auth()->id())
                    : 'guest'
            );
            const pageSearchKey = (function () {
                try {
                    const url = new URL(window.location.href);
                    const entries = Array.from(url.searchParams.entries())
                        .filter(([k]) => k !== '_token')
                        .sort((a, b) => a[0].localeCompare(b[0]));
                    if (entries.length === 0) return '';
                    return '?' + entries.map(([k, v]) => `${k}=${v}`).join('&').toLowerCase();
                } catch (e) {
                    return '';
                }
            })();
            const pageKey = keyPrefix + userStorageKey + ':' + window.location.pathname.toLowerCase() + pageSearchKey;
            const coreKey = keyPrefix + userStorageKey + ':core';
            function getStore() {
                try {
                    sessionStorage.setItem('__pds_test__','1');
                    sessionStorage.removeItem('__pds_test__');
                    return sessionStorage;
                } catch(_) {
                    return { getItem(){return null;}, setItem(){}, removeItem(){} };
                }
            }
            const store = getStore();
            function loadState() {
                try {
                    const raw = store.getItem(pageKey);
                    return raw ? JSON.parse(raw) : {};
                } catch(e) {
                    return {};
                }
            }
            function saveState(state) {
                try {
                    store.setItem(pageKey, JSON.stringify(state));
                } catch(e) {}
            }
            function loadCore() {
                try {
                    const raw = store.getItem(coreKey);
                    return raw ? JSON.parse(raw) : {};
                } catch(e) {
                    return {};
                }
            }
            function saveCore(state) {
                try {
                    store.setItem(coreKey, JSON.stringify(state));
                } catch(e) {}
            }
            function saveCoreValue(k, v) {
                const s = loadCore();
                s[k] = v;
                saveCore(s);
            }
            function sanitize(el, v) {
                if (v == null) return v;
                let s = typeof v === 'string' ? v.trim() : v;
                const ml = el.maxLength && el.maxLength > 0 ? el.maxLength : null;
                if (ml) s = String(s).slice(0, ml);
                return s;
            }
            function setValue(el, val) {
                const t = el.type;
                if (t === 'checkbox') {
                    el.checked = !!val;
                } else if (t === 'radio') {
                    if (el.value === String(val)) el.checked = true;
                } else {
                    el.value = val == null ? '' : String(val);
                }
                if (el.tagName === 'SELECT') el.dispatchEvent(new Event('change'));
            }
            function getValue(el) {
                const t = el.type;
                if (t === 'checkbox') return el.checked;
                if (t === 'radio') return el.checked ? el.value : null;
                return el.value;
            }
            function keyFor(el) {
                return el.name || el.id || '';
            }
            function shouldPersist(el) {
                if (!el || el.disabled) return false;
                const t = String(el.type || '').toLowerCase();
                // Browser blocks programmatic value set on file inputs, and hidden fields must never be restored.
                if (t === 'file' || t === 'hidden') return false;
                const n = String(el.name || '').toLowerCase();
                if (n === '_token') return false;
                const allowed = new Set([
                    'sex',
                    'civil_status',
                    'citizenship',
                    'dual_type',
                    'dual_country',
                    'res_province',
                    'res_city',
                    'res_brgy',
                    'per_province',
                    'per_city',
                    'per_brgy',
                ]);
                return allowed.has(n);
            }
            function debounce(fn, ms) {
                let t; return function(){ clearTimeout(t); const a=arguments, self=this; t=setTimeout(function(){ fn.apply(self,a); }, ms); };
            }
            const saveDebounced = debounce(function(k, v){
                const s = loadState(); s[k] = v; saveState(s);
            }, 150);
            function bind(el, state) {
                if (!shouldPersist(el)) return;
                const k = keyFor(el);
                if (!k) return;
                const coreKeys = new Set(['sex','civil_status','citizenship','dual_type','dual_country']);
                const coreState = loadCore();
                const hasCore = coreKeys.has(k) && coreState.hasOwnProperty(k) && coreState[k] !== undefined && coreState[k] !== null;
                const hasPage = state.hasOwnProperty(k) && state[k] !== null && state[k] !== undefined;
                if (hasCore) {
                    setValue(el, coreState[k]);
                } else if (hasPage) {
                    setValue(el, state[k]);
                }
                const handler = () => {
                    const v = sanitize(el, getValue(el));
                    if (v === null && el.type === 'radio') return;
                    saveDebounced(k, v);
                    if (coreKeys.has(k)) saveCoreValue(k, v);
                };
                el.addEventListener('input', handler);
                el.addEventListener('change', handler);
            }
            function init(root) {
                const state = loadState();
                root.querySelectorAll('input, textarea, select').forEach(el => bind(el, state));
            }
            document.addEventListener('DOMContentLoaded', function(){
                init(document);
                const observer = new MutationObserver(function(muts){
                    muts.forEach(m => {
                        m.addedNodes.forEach(n => {
                            if (n.nodeType === 1) init(n);
                        });
                    });
                });
                observer.observe(document.body, { childList: true, subtree: true });
                window.addEventListener('beforeunload', function(){
                    const inputs = document.querySelectorAll('input, textarea, select');
                    const s = loadState();
                    const c = loadCore();
                    const coreKeys = new Set(['sex','civil_status','citizenship','dual_type','dual_country']);
                    inputs.forEach(el => {
                        if (!shouldPersist(el)) return;
                        const k = keyFor(el);
                        if (k) s[k] = sanitize(el, getValue(el));
                    });
                    saveState(s);
                    inputs.forEach(el => {
                        if (!shouldPersist(el)) return;
                        const k = keyFor(el);
                        if (k && coreKeys.has(k)) c[k] = sanitize(el, getValue(el));
                    });
                    saveCore(c);
                });
            });
        })();
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
    @include('partials.pds_uppercase_inputs')
    @livewireScripts

</body>
</html>

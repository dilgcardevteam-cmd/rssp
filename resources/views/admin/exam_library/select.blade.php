@extends('layout.admin')
@section('title', 'Select from Exam Library')
@section('content')

    <main class="w-full min-h-screen flex flex-col space-y-6 p-6">
        <!-- Header -->
        <section class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button aria-label="Back" onclick="window.history.back()" class="group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h1 class="text-4xl font-bold text-[#0D2B70] font-montserrat">Import from Exam Library</h1>
            </div>
            <div class="text-sm text-gray-600">This page is read-only. Select a series to import its questions into your exam.</div>
        </section>

        <!-- Search -->
        <div class="flex gap-4 items-center">
            <div class="relative flex-1 max-w-md">
                <input id="searchInput" type="search" placeholder="Search question series..."
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]" />
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </div>
        </div>

        <div id="series-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
            <!-- Grid populated by JS -->
        </div>

        @include('partials.loader')
    </main>

    <script>
        const returnUrl = new URLSearchParams(window.location.search).get('return');
        let allSeries = @json($series);

        document.getElementById('searchInput').addEventListener('input', function (e) {
            const search = e.target.value.toLowerCase();
            const filtered = allSeries.filter(series =>
                series.series_name.toLowerCase().includes(search) ||
                (series.description && series.description.toLowerCase().includes(search))
            );
            renderSeries(filtered);
        });

        function renderSeries(series) {
            const grid = document.getElementById('series-grid');

            if (series.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 text-xl">No question series found</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = series.map(item => `
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-all duration-200">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-[#0D2B70] flex-1">${item.series_name}</h3>
                    </div>
                    ${item.description ? `<p class="text-gray-600 text-sm mb-4 line-clamp-2">${item.description}</p>` : ''}
                    <div class="flex items-center justify-between text-sm text-gray-500 border-t pt-4">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">${item.questions_count} Questions</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="px-4 py-2 bg-[#002C76] text-white rounded" onclick="importSeries(${item.id})">Import</button>
                            <span class="text-xs ml-2">${formatDate(item.created_at)}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
            return date.toLocaleDateString();
        }

        // initial render
        renderSeries(allSeries);

        async function importSeries(id) {
            try {
                const response = await fetch(`/admin/exam-library/series/${id}/questions?ajax=1`);
                if (!response.ok) throw new Error('Failed to fetch series questions');
                const questions = await response.json();

                // store in localStorage for the caller to pick up
                let pendingQuestions = [];
                try {
                    const existingPayload = JSON.parse(localStorage.getItem('importedQuestions') || '{}');
                    if (existingPayload && Array.isArray(existingPayload.questions)) {
                        pendingQuestions = existingPayload.questions;
                    }
                } catch (storageError) {
                    pendingQuestions = [];
                }

                localStorage.setItem('importedQuestions', JSON.stringify({
                    series_id: id,
                    questions: [...pendingQuestions, ...questions]
                }));

                // redirect back to caller
                if (returnUrl) window.location.href = returnUrl;
                else showAlert('Questions stored locally. Return to the exam editor to import.', 'success');
            } catch (err) {
                console.error(err);
                showAlert('Failed to import questions. Please try again.', 'error');
            }
        }

        function showAlert(message, type) {
            const toastType = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : (type === 'error' ? 'error' : 'info'));
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(message, toastType);
                return;
            }

            // Last-resort fallback
            if (typeof window.__nativeAlert === 'function') {
                window.__nativeAlert(String(message));
            }
        }

        function closeAlert() {
            // No-op: retained for compatibility with existing close button markup.
        }
    </script>


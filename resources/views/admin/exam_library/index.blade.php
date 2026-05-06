@extends('layout.admin')
@section('title', 'Exam Library')
@section('content')

    <main class="w-full min-h-screen flex flex-col space-y-6 p-6">
        <!-- Header -->
        <section class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button aria-label="Back" onclick="window.history.back()" class="group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h1 class="text-4xl font-bold text-[#0D2B70] font-montserrat">Exam Library</h1>
            </div>
            <button id="createSeriesBtn" onclick="openCreateSeriesModal()"
                class="bg-[#0D2B70] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#002C76] transition-all duration-200 flex items-center gap-2 shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Question Series
            </button>
        </section>

        <!-- Success/Error Messages -->
        <div id="alert-container" class="hidden">
            <div id="alert-message"
                class="px-4 py-3 rounded-lg shadow text-sm font-semibold flex items-center justify-between" role="alert">
                <span id="alert-text"></span>
                <button onclick="closeAlert()" class="font-bold text-lg hover:opacity-70">&times;</button>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="flex gap-4 items-center">
            <div class="relative flex-1 max-w-md">
                <input id="searchInput" type="search" placeholder="Search question series..."
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]" />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </div>
        </div>

        <!-- Question Series Grid -->
        <div id="series-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($series as $item)
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-all duration-200 cursor-pointer"
                    onclick="viewSeries({{ $item->id }})">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-[#0D2B70] flex-1">{{ $item->series_name }}</h3>
                        <div class="flex gap-2">
                            <button onclick="event.stopPropagation(); editSeries({{ $item->id }})"
                                class="text-blue-600 hover:text-blue-800 p-2" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="event.stopPropagation(); deleteSeries({{ $item->id }})"
                                class="text-red-600 hover:text-red-800 p-2" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if($item->description)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $item->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-sm text-gray-500 border-t pt-4">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">{{ $item->questions_count }} Questions</span>
                        </div>
                        <span class="text-xs">{{ $item->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300 mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 text-xl">No question series found</p>
                    <p class="text-gray-400 text-sm mt-2">Create your first question series to get started</p>
                </div>
            @endforelse
        </div>

        <!-- Create/Edit Series Modal -->
        <div id="seriesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4">
                <h2 id="modalTitle" class="text-2xl font-bold text-[#0D2B70] mb-6">Create Question Series</h2>

                <form id="seriesForm" onsubmit="saveSeries(event)">
                    <input type="hidden" id="seriesId" value="">

                    <div class="mb-4">
                        <label for="seriesName" class="block text-sm font-semibold text-gray-700 mb-2">Series Name *</label>
                        <input type="text" id="seriesName" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
                            placeholder="e.g., Math - Algebra Basics">
                    </div>

                    <div class="mb-6">
                        <label for="seriesDescription"
                            class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="seriesDescription" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
                            placeholder="Brief description of this question series..."></textarea>
                    </div>

                    <div class="flex gap-4 justify-end">
                        <button type="button" onclick="closeSeriesModal()"
                            class="px-6 py-2 border border-gray-300 rounded-lg font-semibold hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#0D2B70] text-white rounded-lg font-semibold hover:bg-[#002C76] transition">
                            Save Series
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @include('partials.loader')
    </main>

    <script>
        let allSeries = @json($series);

        // Search functionality
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
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-all duration-200 cursor-pointer"
                    onclick="viewSeries(${item.id})">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-[#0D2B70] flex-1">${item.series_name}</h3>
                        <div class="flex gap-2">
                            <button onclick="event.stopPropagation(); editSeries(${item.id})" 
                                class="text-blue-600 hover:text-blue-800 p-2" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="event.stopPropagation(); deleteSeries(${item.id})" 
                                class="text-red-600 hover:text-red-800 p-2" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    ${item.description ? `<p class="text-gray-600 text-sm mb-4 line-clamp-2">${item.description}</p>` : ''}
                    <div class="flex items-center justify-between text-sm text-gray-500 border-t pt-4">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">${item.questions_count} Questions</span>
                        </div>
                        <span class="text-xs">${formatDate(item.created_at)}</span>
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

        function openCreateSeriesModal() {
            document.getElementById('modalTitle').textContent = 'Create Question Series';
            document.getElementById('seriesId').value = '';
            document.getElementById('seriesName').value = '';
            document.getElementById('seriesDescription').value = '';
            document.getElementById('seriesModal').classList.remove('hidden');
        }

        function closeSeriesModal() {
            document.getElementById('seriesModal').classList.add('hidden');
        }

        function editSeries(id) {
            const series = allSeries.find(s => s.id === id);
            if (!series) return;

            document.getElementById('modalTitle').textContent = 'Edit Question Series';
            document.getElementById('seriesId').value = series.id;
            document.getElementById('seriesName').value = series.series_name;
            document.getElementById('seriesDescription').value = series.description || '';
            document.getElementById('seriesModal').classList.remove('hidden');
        }

        async function saveSeries(event) {
            event.preventDefault();

            const id = document.getElementById('seriesId').value;
            const data = {
                series_name: document.getElementById('seriesName').value,
                description: document.getElementById('seriesDescription').value,
            };

            const url = id ? `/admin/exam-library/series/${id}` : '/admin/exam-library/series';
            const method = id ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    closeSeriesModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            }
        }

        async function deleteSeries(id) {
            if (!confirm('Are you sure you want to delete this question series? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`/admin/exam-library/series/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            }
        }

        function viewSeries(id) {
            window.location.href = `/admin/exam-library/series/${id}`;
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

@endsection


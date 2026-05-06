@extends('layout.admin')
@section('title', 'Admin Activity Log')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<main class="w-full h-full min-h-0 flex flex-col gap-4 pb-4 overflow-hidden font-montserrat" x-data="logTable()">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Activity Log</span>
        </h1>
    </section>

    <section class="flex-none mt-1 w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
        <div class="flex w-full flex-col gap-4">
            <form onsubmit="return false;" class="relative w-full">
                <label for="activitySearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input
                    id="activitySearchInput"
                    type="search"
                    placeholder="Search by activity description"
                    x-model="search"
                    @input="onInputChange"
                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </form>

            <div class="grid w-full gap-2 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6">
                <div class="min-w-0">
                    <select
                        x-model="sortOrder"
                        @change="onFilterChange"
                        class="w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm outline-none transition focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="desc">Latest</option>
                        <option value="asc">Oldest</option>
                    </select>
                </div>

                <div class="min-w-0">
                    <select
                        x-model="adminName"
                        @change="onFilterChange"
                        class="w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm outline-none transition focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="">All Admins</option>
                        @foreach ($adminNames as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <select
                        x-model="section"
                        @change="onFilterChange"
                        class="w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm outline-none transition focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="">All Sections</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec }}">{{ $sec }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <input
                        x-ref="dateRangeInput"
                        x-model="dateRange"
                        type="text"
                        placeholder="Select date range"
                        class="w-full rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm outline-none transition focus:ring-2 focus:ring-[#0D2B70]/20"
                    />
                </div>

                <div class="min-w-0 sm:col-span-2 lg:col-span-1 xl:col-span-2 flex justify-end">
                    @include('partials.alerts_template', [
                        'id' => 'exportAll',
                        'showTrigger' => true,
                        'triggerText' => 'Export Log',
                        'triggerClass' => 'w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-5 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white',
                        'title' => 'Export Confirmation',
                        'message' => 'Are you sure you want to export all activities?',
                        'showCancel' => true,
                        'cancelText' => 'No, Cancel',
                        'okText' => 'Yes, Export',
                        'okAction' => "window.location.href='" . route('exportActivities') . "'",
                    ])
                </div>
            </div>
        </div>
    </section>

    <section class="flex-none flex items-center justify-between">
        <p class="text-sm text-slate-600" x-show="logsData.length > 0">
            Showing <span x-text="startEntry"></span>-<span x-text="endEntry"></span> of <span x-text="logsData.length"></span> entries
        </p>
        <p class="text-sm text-slate-600" x-show="logsData.length === 0">
            No records found.
        </p>

        <div class="flex items-center gap-3">
            <button @click="prevPage" :disabled="currentPage === 1"
                class="h-8 w-8 rounded-full bg-gray-200 text-[#0D2B70] hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                &lt;
            </button>

            <span class="text-sm font-semibold text-slate-700">
                <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
            </span>

            <button @click="nextPage" :disabled="currentPage === totalPages"
                class="h-8 w-8 rounded-full bg-gray-200 text-[#0D2B70] hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                &gt;
            </button>
        </div>
    </section>

    <section class="flex-1 min-h-0">
        <div class="h-full flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl bg-white">
            <div class="bg-[#0D2B70] text-white rounded-t-xl">
                <table class="w-full border-collapse table-fixed">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-[11px] font-semibold text-center w-[17%]">Timestamp</th>
                            <th class="px-4 py-3 text-[11px] font-semibold text-center w-[16%]">User</th>
                            <th class="px-4 py-3 text-[11px] font-semibold text-center w-[12%]">Role</th>
                            <th class="px-4 py-3 text-[11px] font-semibold text-center w-[15%]">Section</th>
                            <th class="px-4 py-3 text-[11px] font-semibold text-left w-[40%]">Description</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="w-full border-collapse table-fixed">
                    <tbody class="divide-y divide-[#0D2B70]">
                        <template x-if="isLoading">
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-500">Loading activity logs...</td>
                            </tr>
                        </template>

                        <template x-if="!isLoading && paginatedLogs.length === 0">
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-500">No records found.</td>
                            </tr>
                        </template>

                        <template x-for="log in paginatedLogs" :key="log.id">
                            <tr class="hover:bg-blue-50/40 text-[#0D2B70]">
                                <td class="w-[17%] px-4 py-3 text-sm text-center" x-text="log.timestamp"></td>
                                <td class="w-[16%] px-4 py-3 text-sm text-center truncate" x-text="log.admin_name"></td>
                                <td class="w-[12%] px-4 py-3 text-sm text-center" x-text="log.role"></td>
                                <td class="w-[15%] px-4 py-3 text-sm text-center" x-text="log.section"></td>
                                <td class="w-[40%] px-4 py-3 text-sm text-left whitespace-normal break-words">
                                    <div x-html="log.description_html"></div>
                                    <template x-if="log.email_log_id">
                                        <div class="mt-1">
                                            <a
                                                class="inline-flex items-center gap-2 text-xs font-semibold text-[#0D2B70] underline hover:no-underline"
                                                :href="`${emailLogBaseUrl}/${log.email_log_id}`"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                View email
                                            </a>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const emailLogBaseUrl = @json(url('/admin/email-logs'));

    function logTable() {
        return {
            logsData: [],
            perPage: 10,
            currentPage: 1,
            search: '',
            adminName: '',
            section: '',
            dateRange: '',
            sortOrder: 'desc',
            isLoading: false,
            debounceTimer: null,

            init() {
                this.initDateRangePicker();
                this.fetchLogs();
            },

            initDateRangePicker() {
                if (!this.$refs.dateRangeInput || typeof flatpickr === 'undefined') return;

                flatpickr(this.$refs.dateRangeInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    onClose: (_selectedDates, dateStr) => {
                        this.dateRange = dateStr;
                        this.onFilterChange();
                    },
                });
            },

            onInputChange() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.onFilterChange();
                }, 400);
            },

            onFilterChange() {
                this.currentPage = 1;
                this.fetchLogs();
            },

            fetchLogs() {
                const params = new URLSearchParams({
                    search: this.search,
                    admin_name: this.adminName,
                    section: this.section,
                    date_range: this.dateRange,
                    sort: this.sortOrder,
                });

                this.isLoading = true;

                fetch(`{{ route('admin.activity_log.fetch') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then((res) => res.json())
                    .then((data) => {
                        this.logsData = Array.isArray(data) ? data : [];
                        if (this.currentPage > this.totalPages) {
                            this.currentPage = this.totalPages;
                        }
                    })
                    .catch((error) => {
                        console.error('Failed to fetch activity logs:', error);
                        this.logsData = [];
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.logsData.length / this.perPage));
            },

            get paginatedLogs() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.logsData.slice(start, start + this.perPage);
            },

            get startEntry() {
                if (this.logsData.length === 0) return 0;
                return (this.currentPage - 1) * this.perPage + 1;
            },

            get endEntry() {
                if (this.logsData.length === 0) return 0;
                return Math.min(this.currentPage * this.perPage, this.logsData.length);
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
        };
    }
</script>
@endpush

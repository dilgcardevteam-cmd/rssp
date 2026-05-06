@extends('layout.admin')
@section('title', 'DILG - Dashboard Admin')

@section('content')
    <div class="flex flex-col h-full gap-3 md:gap-4 overflow-y-auto scrollbar-thin">

        <!-- Welcome Section -->
        <!-- dynamic message eyyy💅 -->
        <section class="text-center sm:text-left">
            <div class="text-xl font-normal mb-1 font-montserrat text-[#002C76]">
                @php
                    $hour = now()->format('H');

                    if ($hour >= 5 && $hour < 12) {
                        $greeting = 'Good morning';
                    } elseif ($hour >= 12 && $hour < 17) {
                        $greeting = 'Good afternoon';
                    } elseif ($hour >= 17 && $hour < 21) {
                        $greeting = 'Good evening';
                    } else {
                        $greeting = 'Good night';
                    }
                @endphp

                {{ $greeting }},
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl tracking-tight font-montserrat text-[#002C76]">
                {{ auth('admin')->user()->name ?? 'Admin' }}
            </h1>
        </section>

        <!-- Key Metrics Grid -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 shrink-0">
            <!-- Available Vacancies -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <!-- Content -->
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">AVAILABLE
                            VACANCIES</span>

                        <div class="flex items-center">
                            <!-- Total Count -->
                            <div class="flex flex-col items-start">
                                <span
                                    class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $openVacancyCount }}</span>
                                <span
                                    class="text-[9px] sm:text-[10px] text-gray-600 uppercase tracking-wider mt-1">POSITIONS</span>
                            </div>

                            <!-- Vertical Divider -->
                            <div class="h-10 sm:h-12 w-0.5 bg-[#002C76] mx-2 sm:mx-4"></div>

                            <!-- Breakdown -->
                            <div class="flex flex-col justify-center gap-1">
                                <div class="text-xs sm:text-sm text-gray-800 font-montserrat">
                                    <span class="font-bold">{{ $cosVacancyCount }}</span> <span
                                        class="hidden sm:inline">Contract of Service</span><span
                                        class="sm:hidden">COS</span>
                                </div>
                                <div class="text-xs sm:text-sm text-gray-800 font-montserrat">
                                    <span class="font-bold">{{ $plantillaVacancyCount }}</span> Plantilla
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon (Briefcase) -->
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] transition-colors duration-300 group-hover:text-white"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7h-4a2 2 0 01-2-2V4H10v1a2 2 0 01-2 2H4v11a2 2 0 002 2h12a2 2 0 002-2V7z" />
                        </svg>
                    </div>
                </div>
                <!-- Decorative Background Circle -->
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>

            <!-- Reviewed Applications -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Reviewed
                            Applications</span>
                        <span
                            class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $reviewedApplicationsCount }}</span>
                    </div>
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] group-hover:text-white transition-colors duration-300"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5h6m-6 4h6m-7 4l2 2 4-4M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Upcoming
                            Exams</span>
                        <span
                            class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $upcomingExamsCount }}</span>
                    </div>
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] group-hover:text-white transition-colors duration-300"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10m-12 9h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>

            <!-- System Users -->
            <div
                class="cursor-pointer group relative overflow-hidden bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                <div class="flex justify-between items-start z-10 relative h-full">
                    <div class="flex flex-col justify-center h-full space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider">System
                            Users</span>
                        <span
                            class="font-extrabold text-2xl sm:text-3xl md:text-4xl text-[#002C76] font-montserrat">{{ $systemUsersCount }}</span>
                    </div>
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 group-hover:bg-[#002C76] transition-colors duration-300">
                        <svg class="w-5 h-5 text-[#002C76] group-hover:text-white transition-colors duration-300"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-1a4 4 0 00-4-4h-1M9 20H4v-1a4 4 0 014-4h1m6-5a4 4 0 10-8 0 4 4 0 008 0zm6 4a3 3 0 10-6 0 3 3 0 006 0z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-20 h-20 bg-blue-50 rounded-full -mr-6 -mb-6 opacity-20 group-hover:scale-150 transition-transform duration-500 ease-out">
                </div>
            </div>
        </section>

        <!-- Main Analytics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-4 flex-1 min-h-0">

            <!-- Left Column: Line Chart & Bottom Widgets -->
            <div class="lg:col-span-2 flex flex-col gap-3 md:gap-4 h-full min-h-0">

                <!-- Monthly Applications Chart -->
                <div
                    class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 flex-1 min-h-[300px] lg:min-h-0 flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-2 shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-chart-line text-[#002C76] text-xs sm:text-sm"></i>
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Monthly Applications
                            </h2>
                        </div>
                        <div id="chartLoadingIndicator" class="hidden">
                            <div class="animate-pulse flex items-center gap-2">
                                <div class="h-2 w-2 bg-[#002C76] rounded-full"></div>
                                <div class="h-2 w-2 bg-[#002C76] rounded-full animation-delay-200"></div>
                                <div class="h-2 w-2 bg-[#002C76] rounded-full animation-delay-400"></div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full flex-1 min-h-0">
                        <canvas id="monthlyApplicantsLineChart"></canvas>
                    </div>
                    <div id="noDataMessage" class="hidden flex-1 flex items-center justify-center">
                        <p class="text-gray-500 text-sm">No application data available for
                            {{ $selectedYear ?? now()->year }}
                        </p>
                    </div>
                </div>

                <!-- Bottom Widgets Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 h-auto min-h-[160px] lg:min-h-[180px] shrink-0">
                    <!-- Applicants Status (Pie Chart) -->
                    <div
                        class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col min-h-[180px] lg:h-full lg:min-h-0 overflow-hidden">
                        <div class="flex items-center gap-2 mb-2 shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-users text-[#002C76] text-xs sm:text-sm"></i>
                            </div>
                            <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Applicants Status</h2>
                        </div>
                        <div class="flex-1 flex items-center justify-center relative min-h-0 overflow-hidden">
                            <div
                                class="relative w-full h-full max-h-[140px] sm:max-h-[160px] flex items-center justify-center">
                                <canvas id="applicantsPie"></canvas>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Right Column: Job Vacancies Ratio -->
                    <div class="lg:col-span-1 h-full min-h-0">
                        <div
                            class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 min-h-[180px] lg:h-full lg:min-h-0 flex flex-col">
                            <div class="flex items-center gap-2 mb-3 md:mb-4 shrink-0">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                    <i class="fas fa-briefcase text-[#002C76] text-xs sm:text-sm"></i>
                                </div>
                                <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Job Vacancies Ratio</h2>
                            </div>
                            <div class="flex-1 relative min-h-0">
                                <canvas id="jobBarChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Modal for Exam Details (Hidden by default) -->
                    <div id="examModal"
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-xl max-w-md w-full mx-4 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-[#002C76]">Examination Details</h3>
                                <button onclick="closeExamModal()" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="modalExamDetails" class="space-y-4 max-h-96 overflow-y-auto">
                                <!-- Will be populated by JavaScript -->
                            </div>
                            <button onclick="closeExamModal()"
                                class="mt-6 w-full bg-[#002C76] text-white py-2 rounded-lg font-bold hover:bg-[#001a4d] transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Examinations (Calendar) -->
            <div
                class="bg-white border border-gray-200 rounded-lg md:rounded-xl p-3 md:p-4 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col min-h-[400px] lg:h-full lg:min-h-0 overflow-hidden">
                <div class="flex items-center gap-2 mb-2 shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-50 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-[#002C76] text-xs sm:text-sm"></i>
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-[#002C76] font-montserrat">Examination Calendar
                    </h2>
                </div>

                <!-- Custom Calendar with Tailwind -->
                <div class="w-full bg-white rounded-lg">
                    <!-- Calendar Header -->
                    <div class="flex items-center justify-between mb-4">
                        <button onclick="previousMonth()"
                            class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                            <i class="fas fa-chevron-left text-[#002C76] text-sm"></i>
                        </button>
                        <h3 id="calendarMonthYear" class="text-sm font-bold text-[#002C76]">February 2026</h3>
                        <button onclick="nextMonth()" class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                            <i class="fas fa-chevron-right text-[#002C76] text-sm"></i>
                        </button>
                    </div>

                    <!-- Week Days -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center text-[10px] font-bold text-[#002C76]">S</div>
                        <div class="text-center text-[10px] font-bold text-[#002C76]">M</div>
                        <div class="text-center text-[10px] font-bold text-[#002C76]">T</div>
                        <div class="text-center text-[10px] font-bold text-[#002C76]">W</div>
                        <div class="text-center text-[10px] font-bold text-[#002C76]">T</div>
                        <div class="text-center text-[10px] font-bold text-[#002C76]">F</div>
                        <div class="text-center text-[10px] font-bold text-[#002C76]">S</div>
                    </div>

                    <!-- Calendar Days Grid -->
                    <div id="calendarDays" class="grid grid-cols-7 gap-1">
                        <!-- Days will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center gap-3 mt-2 text-[10px] text-gray-600">
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-[#002C76] rounded-full"></div>
                        <span>Exam Date</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-yellow-100 border border-yellow-500 rounded-full"></div>
                        <span>Today</span>
                    </div>
                </div>


                <!-- Loading State -->
                <div id="calendarLoading" class="flex justify-center items-center py-4">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#002C76]"></div>
                </div>

                <!-- Error State (hidden by default) -->
                <div id="calendarError" class="hidden text-center text-red-500 text-sm py-4">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Failed to load examination dates.
                </div>

                <!-- No Exams State (hidden by default) -->
                <div id="noExams" class="hidden text-center text-gray-500 text-sm py-4">
                    <i class="fas fa-calendar-times mr-1"></i>
                    No scheduled examinations found.
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <!-- Libraries -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js"></script>


        <script>
            document.addEventListener("DOMContentLoaded", function () {

                // --- Monthly Applications Chart ---
                const ctxLine = document.getElementById('monthlyApplicantsLineChart');
                const noDataMessage = document.getElementById('noDataMessage');
                const chartLoadingIndicator = document.getElementById('chartLoadingIndicator');

                if (ctxLine) {
                    // Get data from backend
                    const chartLabels = {!! json_encode($chartLabels ?? []) !!};
                    const chartData = {!! json_encode($chartData ?? []) !!};

                    // Debug logging
                    console.log('Chart Labels:', chartLabels);
                    console.log('Chart Data:', chartData);

                    // Check if there's any data
                    const hasData = chartData && Array.isArray(chartData) && chartData.some(value => value > 0);

                    console.log('Has Data:', hasData);

                    if (!hasData) {
                        // Hide chart and show no data message
                        ctxLine.style.display = 'none';
                        if (noDataMessage) {
                            noDataMessage.classList.remove('hidden');
                        }
                    } else {
                        // Show chart and hide no data message
                        ctxLine.style.display = 'block';
                        if (noDataMessage) {
                            noDataMessage.classList.add('hidden');
                        }

                        new Chart(ctxLine, {
                            type: 'line',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: 'Applications',
                                    data: chartData,
                                    borderColor: '#002C76',
                                    backgroundColor: (context) => {
                                        const ctx = context.chart.ctx;
                                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                        gradient.addColorStop(0, 'rgba(0, 44, 118, 0.2)');
                                        gradient.addColorStop(1, 'rgba(0, 44, 118, 0)');
                                        return gradient;
                                    },
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 2,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#002C76',
                                    pointBorderWidth: 2,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#002C76',
                                        titleFont: { family: 'Montserrat', size: 12 },
                                        bodyFont: { family: 'Montserrat', size: 11 },
                                        padding: 8,
                                        cornerRadius: 6,
                                        displayColors: false,
                                        callbacks: {
                                            label: function (context) {
                                                return 'Applications: ' + context.parsed.y;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            font: { family: 'Montserrat', size: 10 },
                                            maxRotation: 0,
                                            autoSkip: true,
                                            maxTicksLimit: 12
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        border: { dash: [4, 4] },
                                        grid: { color: '#f3f4f6' },
                                        ticks: {
                                            font: { family: 'Montserrat', size: 10 },
                                            callback: function (value) {
                                                if (Number.isInteger(value)) {
                                                    return value;
                                                }
                                            }
                                        }
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index'
                                }
                            }
                        });
                    }
                }

                // --- Applicants Pie Chart ---
                const ctxPie = document.getElementById('applicantsPie');
                if (ctxPie) {
                    const reviewedCount = {{ $reviewedApplicationsCount ?? 0 }};
                    const ongoingCount = {{ $onGoingApplicationsCount ?? 0 }};

                    new Chart(ctxPie, {
                        type: 'doughnut',
                        data: {
                            labels: ['Reviewed', 'Ongoing'],
                            datasets: [{
                                data: [reviewedCount, ongoingCount],
                                backgroundColor: ['#002C76', '#9CA3AF'],
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { family: 'Montserrat', size: 10 },
                                        usePointStyle: true,
                                        padding: 12,
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#002C76',
                                    titleFont: { family: 'Montserrat', size: 11 },
                                    bodyFont: { family: 'Montserrat', size: 10 },
                                    padding: 8,
                                    cornerRadius: 6
                                }
                            }
                        }
                    });
                }

                // --- Job Vacancies Ratio Bar Chart ---
                const ctxBar = document.getElementById('jobBarChart');
                if (ctxBar) {
                    const cosCount = {{ $cosVacancyCount ?? 0 }};
                    const plantillaCount = {{ $plantillaVacancyCount ?? 0 }};

                    const barValuePlugin = {
                        id: 'barValue',
                        afterDatasetsDraw(chart) {
                            const { ctx } = chart;
                            const dataset = chart.data.datasets[0];
                            const meta = chart.getDatasetMeta(0);
                            ctx.save();
                            ctx.fillStyle = '#111827';
                            ctx.font = 'bold 11px Montserrat';
                            ctx.textAlign = 'left';
                            meta.data.forEach((bar, idx) => {
                                const val = dataset.data[idx];
                                const props = bar.getProps(['x','y','width','height'], true);
                                const rightX = props.x + (props.width / 2) + 10;
                                const midY = props.y + (props.height / 2);
                                ctx.fillText(val, rightX, midY);
                            });
                            ctx.restore();
                        }
                    };

                    new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: ['COS', 'Plantilla'],
                            datasets: [{
                                label: 'Vacancies',
                                data: [cosCount, plantillaCount],
                                backgroundColor: ['#002C76', '#9CA3AF'],
                                borderRadius: 6,
                                barThickness: 40,
                                maxBarThickness: 60
                            }]
                        },
                        plugins: [barValuePlugin],
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#002C76',
                                    titleFont: { family: 'Montserrat', size: 11 },
                                    bodyFont: { family: 'Montserrat', size: 10 },
                                    padding: 8,
                                    cornerRadius: 6,
                                    callbacks: {
                                        label: function (context) {
                                            return 'Vacancies: ' + context.parsed.y;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f3f4f6' },
                                    ticks: {
                                        font: { family: 'Montserrat', size: 10 },
                                        callback: function (value) {
                                            if (Number.isInteger(value)) {
                                                return value;
                                            }
                                        }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { family: 'Montserrat', size: 11 } }
                                }
                            }
                        }
                    });
                }



            });
        </script>


        <script>
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            let examData = [];

            document.addEventListener('DOMContentLoaded', function () {
                fetchExaminationDates();
            });

            function fetchExaminationDates() {
                const loadingEl = document.getElementById('calendarLoading');
                const errorEl = document.getElementById('calendarError');
                const noExamsEl = document.getElementById('noExams');

                // Show loading, hide others
                loadingEl.classList.remove('hidden');
                errorEl.classList.add('hidden');
                noExamsEl.classList.add('hidden');

                // Fetch examination dates from your backend
                fetch('/api/examination-dates', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Hide loading
                        loadingEl.classList.add('hidden');

                        if (data.success && data.exams && data.exams.length > 0) {
                            examData = data.exams;

                            // Render calendar
                            renderCalendar(currentMonth, currentYear, data.exams);

                            // Show calendar elements
                            document.querySelector('.grid.grid-cols-7').classList.remove('hidden');
                        } else {
                            // Show no exams message
                            noExamsEl.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching examination dates:', error);
                        loadingEl.classList.add('hidden');
                        errorEl.classList.remove('hidden');
                    });
            }

            function renderCalendar(month, year, exams) {
                const calendarDays = document.getElementById('calendarDays');
                const monthYearEl = document.getElementById('calendarMonthYear');

                // Set month and year display
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                monthYearEl.textContent = `${monthNames[month]} ${year}`;

                // Get first day of month and total days
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                // Get exam dates for this month
                const examDatesThisMonth = exams
                    .filter(exam => {
                        const examDate = new Date(exam.date);
                        return examDate.getMonth() === month && examDate.getFullYear() === year;
                    })
                    .map(exam => new Date(exam.date).getDate());

                // Clear previous days
                calendarDays.innerHTML = '';

                // Add empty cells for days before month starts
                for (let i = 0; i < firstDay; i++) {
                    const emptyDay = document.createElement('div');
                    emptyDay.className = 'text-center py-1 text-xs text-gray-300';
                    emptyDay.textContent = '';
                    calendarDays.appendChild(emptyDay);
                }

                // Add days of month
                const today = new Date();
                const isCurrentMonth = today.getMonth() === month && today.getFullYear() === year;
                const todayDate = today.getDate();

                for (let day = 1; day <= daysInMonth; day++) {
                    const dayCell = document.createElement('div');

                    // Base classes
                    let cellClasses = 'text-center py-1 text-xs rounded-full cursor-pointer transition-colors ';

                    // Check if this date has an exam
                    if (examDatesThisMonth.includes(day)) {
                        cellClasses += 'bg-[#002C76] text-white font-bold hover:bg-[#001a4d] ';
                    } else {
                        cellClasses += 'hover:bg-gray-100 ';
                    }

                    // Check if this is today
                    if (isCurrentMonth && day === todayDate) {
                        cellClasses += 'border-2 border-yellow-500 ';
                    }

                    dayCell.className = cellClasses;
                    dayCell.textContent = day;
                    dayCell.setAttribute('data-date', `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`);

                    // Add click event to show exams on this date
                    dayCell.onclick = function () {
                        const dateStr = this.getAttribute('data-date');
                        const examsOnDate = exams.filter(exam => exam.date === dateStr);
                        if (examsOnDate.length > 0) {
                            showExamDetails(examsOnDate);
                        }
                    };

                    calendarDays.appendChild(dayCell);
                }
            }

            function getStatusClass(status) {
                switch (status) {
                    case 'Ongoing': return 'bg-yellow-100 text-yellow-800';
                    case 'Scheduled': return 'bg-green-100 text-green-800';
                    case 'Completed': return 'bg-gray-100 text-gray-600';
                    default: return 'bg-blue-100 text-blue-800';
                }
            }

            function previousMonth() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderCalendar(currentMonth, currentYear, examData);
            }

            function nextMonth() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                renderCalendar(currentMonth, currentYear, examData);
            }

            function showExamDetails(exams) {
                const modal = document.getElementById('examModal');
                const modalDetails = document.getElementById('modalExamDetails');

                modalDetails.innerHTML = exams.map(exam => `
                                                                                        <div class="border-l-4 border-[#002C76] pl-3 py-2">
                                                                                            <h4 class="font-bold text-[#002C76] text-sm">${exam.position_title}</h4>
                                                                                            <div class="text-xs text-gray-600 mt-1 space-y-1">
                                                                                                <p class="flex items-center gap-2">
                                                                                                    <i class="fas fa-calendar w-4 text-[#002C76]"></i>
                                                                                                    <span>${exam.formatted_date}</span>
                                                                                                </p>
                                                                                                <p class="flex items-center gap-2">
                                                                                                    <i class="fas fa-clock w-4 text-[#002C76]"></i>
                                                                                                    <span>${exam.formatted_time} ${exam.formatted_time_end ? '- ' + exam.formatted_time_end : ''}</span>
                                                                                                </p>
                                                                                                <p class="flex items-center gap-2">
                                                                                                    <i class="fas fa-map-marker-alt w-4 text-[#002C76]"></i>
                                                                                                    <span>${exam.venue}</span>
                                                                                                </p>
                                                                                                <p class="flex items-center gap-2 mt-2">
                                                                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold ${getStatusClass(exam.status)}">${exam.status}</span>
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    `).join('');

                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeExamModal() {
                const modal = document.getElementById('examModal');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Close modal when clicking outside
            document.getElementById('examModal').addEventListener('click', function (e) {
                if (e.target === this) {
                    closeExamModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeExamModal();
                }
            });
        </script>

        <style>
            /* Custom scrollbar using Tailwind classes */
            .scrollbar-thin::-webkit-scrollbar {
                width: 4px;
            }

            .scrollbar-thin::-webkit-scrollbar-track {
                background: #f3f4f6;
                border-radius: 4px;
            }

            .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #002C76;
                border-radius: 4px;
            }

            .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                background: #001a4d;
            }

            /* For Firefox */
            .scrollbar-thin {
                scrollbar-width: thin;
                scrollbar-color: #002C76 #f3f4f6;
            }
        </style>
    @endpush
@endsection

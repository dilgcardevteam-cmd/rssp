@extends('layout.app')
@section('title', 'Dashboard | DILG-CAR')

@section('content')
    <div class="bg-[#f5f7fb]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Hero / Welcome Section -->
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0c2a6a] via-[#1b3f9a] to-[#1f67d1] text-white shadow-2xl">
                <div class="relative z-10 p-6 sm:p-7 lg:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="space-y-3">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.2em] text-white/70 font-semibold">Applicant Portal</p>
                        <h1 class="font-montserrat font-extrabold text-2xl sm:text-3xl lg:text-4xl">
                            @php
                                $hour = now()->format('H');
                                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                            @endphp
                            {{ $greeting }}, {{ Auth::user()->name }}!
                        </h1>
                        <p class="text-blue-100 text-sm sm:text-base max-w-2xl leading-relaxed">
                            Welcome to your applicant portal. Track your applications, manage your PDS, and stay updated with the
                            latest announcements.
                        </p>
                        <div class="flex flex-wrap gap-3 pt-1">
                            <a href="{{ route('job_vacancy') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-[#0D2B70] shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-gray-100">
                                <i data-feather="search" class="w-4 h-4"></i>
                                Search Jobs
                            </a>
                            <a href="{{ route('account.settings') }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/15 px-5 py-2.5 text-sm font-bold text-white backdrop-blur-sm transition hover:-translate-y-0.5 hover:bg-white/25">
                                <i data-feather="settings" class="w-4 h-4"></i>
                                Account Settings
                            </a>
                        </div>
                    </div>
                </div>
                @php
                    $pdsProgressSafe = max(0, min(100, (int) ($pdsProgress ?? 0)));
                    $pdsRadius = 26;
                    $pdsCircumference = 2 * M_PI * $pdsRadius;
                    $pdsOffset = $pdsCircumference - ($pdsCircumference * $pdsProgressSafe / 100);
                @endphp
                <div class="absolute right-4 top-4 sm:right-6 sm:top-6 z-20 w-60 sm:w-72">
                    <div class="flex items-center justify-between gap-3 sm:gap-4 rounded-2xl bg-white/95 backdrop-blur shadow-xl border border-slate-100 px-4 py-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 shadow-sm">
                                    <i data-feather="file-text" class="h-4 w-4"></i>
                                </span>
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Submitted Documents</span>
                            </div>
                        </div>
                        <div class="relative h-16 w-16 flex items-center justify-center">
                            <svg class="h-16 w-16 -rotate-90" viewBox="0 0 60 60">
                                <circle
                                    class="text-slate-200"
                                    stroke="currentColor"
                                    stroke-width="6"
                                    fill="transparent"
                                    r="{{ $pdsRadius }}"
                                    cx="30"
                                    cy="30"
                                ></circle>
                                <circle
                                    class="text-emerald-500"
                                    stroke="currentColor"
                                    stroke-width="6"
                                    stroke-linecap="round"
                                    fill="transparent"
                                    r="{{ $pdsRadius }}"
                                    cx="30"
                                    cy="30"
                                    style="stroke-dasharray: {{ $pdsCircumference }}; stroke-dashoffset: {{ $pdsOffset }};"
                                ></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-extrabold text-[#0D2B70]">{{ $pdsProgressSafe }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -left-10 -bottom-16 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute right-6 -top-10 h-32 w-32 rounded-full bg-white/15 blur-2xl"></div>
            </div>

            <!-- Quick Stats Grid -->
            @php
                $actionRequiredCount = collect($deadlineCountdown ?? [])->count();
                $onboardingErrorBag = $errors->getBag('onboarding');
                $onboardingInitialReadiness = [
                    'education' => old('readiness_education', ''),
                    'experience' => old('readiness_experience', ''),
                    'training' => old('readiness_training', ''),
                    'eligibility' => old('readiness_eligibility', ''),
                ];
                $onboardingInitialVacancyId = old('preferred_vacancy_id', $selectedOnboardingVacancyId ?? '');
                $shouldOpenOnboardingModal = (bool) ($openOnboardingModal ?? false) || $onboardingErrorBag->any();
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="cursor-pointer hover:scale-105 hover:shadow-lg transition group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-[#0D2B70]">
                                <i data-feather="briefcase" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Applications</p>
                                <p class="text-3xl font-extrabold text-[#0D2B70]">
                                    {{ $applications->filter(fn($a) => strtolower(trim((string) ($a->status ?? ''))) === 'pending')->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute right-4 top-4 max-w-xs rounded-xl bg-slate-900/90 px-3 py-2 text-[11px] text-slate-50 shadow-lg opacity-0 translate-y-2 transition duration-200 group-hover:opacity-100 group-hover:translate-y-0">
                        Shows how many of your applications are currently pending review.
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-blue-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>

                <div class="cursor-pointer hover:scale-105 hover:shadow-lg transition group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                                <i data-feather="clock" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deadlines This Week</p>
                                <p class="text-3xl font-extrabold text-[#0D2B70]">
                                    {{ collect($deadlineCountdown)->filter(fn($d) => $d['days_remaining'] <= 5)->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute right-4 top-4 max-w-xs rounded-xl bg-slate-900/90 px-3 py-2 text-[11px] text-slate-50 shadow-lg opacity-0 translate-y-2 transition duration-200 group-hover:opacity-100 group-hover:translate-y-0">
                        Counts vacancies with application deadlines happening within the next few days.
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-orange-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>

                <div class="cursor-pointer hover:scale-105 hover:shadow-lg transition group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                <i data-feather="calendar" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scheduled Exams</p>
                                <p class="text-3xl font-extrabold text-[#0D2B70]">{{ $upcomingExamsCount ?? $upcomingExams->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute right-4 top-4 max-w-xs rounded-xl bg-slate-900/90 px-3 py-2 text-[11px] text-slate-50 shadow-lg opacity-0 translate-y-2 transition duration-200 group-hover:opacity-100 group-hover:translate-y-0">
                        Shows how many upcoming exams you are currently scheduled to take.
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-purple-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>

                <div class="cursor-pointer hover:scale-105 hover:shadow-lg transition group relative overflow-hidden rounded-2xl bg-white p-4 shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <button type="button" id="action-required-card" class="flex items-center gap-3 text-left">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                                    <i data-feather="alert-triangle" class="w-5 h-5"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Action Required</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-3xl font-extrabold text-[#0D2B70]">{{ $actionRequiredCount }}</p>
                                        <span class="text-[11px] font-semibold text-orange-600">Tap to view list</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute right-4 top-4 max-w-xs rounded-xl bg-slate-900/90 px-3 py-2 text-[11px] text-slate-50 shadow-lg opacity-0 translate-y-2 transition duration-200 group-hover:opacity-100 group-hover:translate-y-0">
                        Highlights applications or requirements that need your attention right now.
                    </div>
                    <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-orange-50/60 blur-3xl transition-transform duration-500 group-hover:scale-110"></div>
                </div>
            </div>

            @if($actionRequiredCount > 0)
                <div id="action-required-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
                    <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-slate-100 p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-[#0D2B70] font-bold">
                                <i data-feather="alert-circle" class="h-4 w-4 text-orange-500"></i>
                                <span>Action Required</span>
                                <span class="text-xs font-semibold text-orange-600">({{ $actionRequiredCount }})</span>
                            </div>
                            <button type="button" id="action-required-close" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Close</button>
                        </div>
                        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                            @foreach(collect($deadlineCountdown) as $deadline)
                                @php
                                    $daysRemaining = (int) ($deadline['days_remaining'] ?? 0);
                                @endphp
                                <div class="rounded-xl border border-orange-100 bg-orange-50 px-3 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-orange-800">Due in {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'Day' : 'Days' }}</p>
                                    <p class="mt-1 text-sm font-bold text-[#0D2B70] leading-snug">{{ $deadline['position_title'] }}</p>
                                    <p class="text-[11px] text-slate-600">Deadline: {{ \Carbon\Carbon::parse($deadline['deadline'])->format('M d, h:i A') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Content Layout -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <!-- My Applications Section -->
                <section class="xl:col-span-7 overflow-hidden rounded-2xl bg-white shadow-lg shadow-slate-200/70 border border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 sm:px-6 py-3">
                        <div class="flex items-center gap-2 text-[#0D2B70] font-bold">
                            <i data-feather="briefcase" class="w-4 h-4"></i>
                            My Applications
                        </div>
                        <a href="{{ route('my_applications') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">View All</a>
                    </div>

                    @if($applications->isEmpty())
                        <div class="p-8 text-center">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50">
                                <i data-feather="inbox" class="h-8 w-8 text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">No applications yet</h3>
                            <p class="mt-1 text-sm text-slate-500">You have not applied to any job vacancies yet.</p>
                            <div class="mt-4">
                                <a href="{{ route('job_vacancy') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-bold text-white shadow-md transition hover:bg-[#0b2560]">
                                    <i data-feather="arrow-right" class="w-4 h-4"></i>
                                    Explore Jobs
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($applications->take(5) as $app)
                                <div class="flex flex-col gap-3 p-4 transition hover:bg-blue-50/30">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-base font-bold text-[#0D2B70] leading-tight">
                                                    {{ $app->vacancy->position_title ?? 'Unknown Position' }}
                                                </h3>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                                        'qualified' => 'bg-blue-100 text-blue-700',
                                                        'hired' => 'bg-green-100 text-green-700',
                                                        'rejected' => 'bg-red-100 text-red-700',
                                                        'closed' => 'bg-gray-100 text-gray-600',
                                                        'not qualified' => 'bg-red-100 text-red-700',
                                                    ];
                                                    $statusKey = strtolower(trim($app->status));
                                                    $statusClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600';
                                                @endphp
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide rounded-full {{ $statusClass }}">
                                                    {{ $app->status }}
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                                <span class="flex items-center gap-1"><i data-feather="map-pin" class="h-3 w-3"></i>{{ $app->vacancy->place_of_assignment ?? 'N/A' }}</span>
                                                <span class="flex items-center gap-1"><i data-feather="calendar" class="h-3 w-3"></i>Applied {{ $app->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('application_status', ['user' => Auth::id(), 'vacancy' => $app->vacancy_id]) }}"
                                                class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-[#0D2B70] shadow-sm transition hover:border-[#0D2B70] hover:bg-[#0D2B70] hover:text-white">
                                                View Status
                                                <i data-feather="chevron-right" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Right Column: PDS Links -->
                <div class="xl:col-span-5 space-y-4">
                    <div class="rounded-2xl bg-white shadow-lg shadow-slate-200/70 border border-slate-100 p-4">
                        <!-- <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 font-bold text-[#0D2B70]">
                                <i data-feather="file-text" class="h-4 w-4"></i>
                                Submitted Documents
                            </div>
                            <span class="text-xs font-semibold text-green-600">{{ $pdsProgress }}% of documents submitted</span>
                        </div>
                        <div class="mb-4 h-2 w-full rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-green-500" style="width: {{ $pdsProgress }}%"></div>
                        </div> -->
                        <div class="mb-3 flex items-center gap-2 font-bold text-[#0D2B70]">
                            <i data-feather="file-text" class="h-4 w-4"></i>
                            Personal Data Sheet
                        </div>
                        @php
                            $pdsLinks = [
                                ['name' => 'Personal Information', 'route' => 'display_c1', 'icon' => 'user'],
                                ['name' => 'Family Background', 'route' => 'display_c1', 'icon' => 'users', 'fragment' => 'family-background'],
                                ['name' => 'Educational Background', 'route' => 'display_c1', 'icon' => 'book', 'fragment' => 'educational-background'],
                                ['name' => 'Civil Service & Work Exp.', 'route' => 'display_c2', 'icon' => 'briefcase', 'fragment' => 'civil-service-eligibility'],
                                ['name' => 'Voluntary Work & Training', 'route' => 'display_c3', 'icon' => 'award'],
                                ['name' => 'Other Information', 'route' => 'display_c4', 'icon' => 'info'],
                            ];
                        @endphp
                        <div class="space-y-2">
                            @foreach($pdsLinks as $link)
                                @php
                                    $linkHref = route($link['route']) . (isset($link['fragment']) ? ('#' . $link['fragment']) : '');
                                @endphp
                                <a href="{{ $linkHref }}" class="flex items-center gap-3 rounded-lg border border-slate-100 px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                                        <i data-feather="{{ $link['icon'] }}" class="h-4 w-4"></i>
                                    </span>
                                    <span class="flex-1 truncate">{{ $link['name'] }}</span>
                                    <i data-feather="chevron-right" class="h-4 w-4 text-slate-400"></i>
                                </a>
                            @endforeach
                            <a href="{{ route('display_wes', ['simple' => 1]) }}" class="flex items-center gap-3 rounded-lg border border-dashed border-blue-200 bg-blue-50/60 px-3 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-blue-600 border border-blue-200">
                                    <i data-feather="file-plus" class="h-4 w-4"></i>
                                </span>
                                <span class="flex-1 truncate">Work Experience Sheet</span>
                                <i data-feather="plus" class="h-4 w-4 text-blue-600"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            @if(false && (($requiresApplicantOnboarding ?? false) || $shouldOpenOnboardingModal))
                <div id="applicant-onboarding-modal" class="hidden fixed inset-0 z-[1300] flex items-start sm:items-center justify-center p-2 sm:p-4 overflow-y-auto">
                    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
                    <div class="relative w-full max-w-4xl max-h-[calc(100vh-1rem)] sm:max-h-[90vh] rounded-2xl border border-[#0D2B70]/20 bg-white shadow-2xl overflow-hidden flex flex-col"
                        x-data="dashboardOnboardingWizard(
                            @js($onboardingVacancyOptions ?? []),
                            @js($onboardingInitialVacancyId),
                            @js($onboardingInitialReadiness)
                        )">
                        <div class="bg-gradient-to-r from-[#0D2B70] via-[#1D4AA3] to-[#2A63C6] px-4 sm:px-6 py-4 text-white">
                            <p class="text-xs uppercase tracking-[0.2em] text-blue-100 font-semibold">Applicant Onboarding</p>
                            <h2 class="text-xl font-bold">Position Readiness Screening</h2>
                            <p class="text-sm text-blue-100 mt-1">Complete this before you continue with job applications.</p>
                        </div>

                        <form method="POST" action="{{ route('applicant.onboarding.store') }}" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                            @csrf

                            @if($onboardingErrorBag->any())
                                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    <p class="font-semibold mb-1">Please fix the onboarding details below:</p>
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($onboardingErrorBag->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-2">
                                <template x-for="step in [1,2,3]" :key="step">
                                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="currentStep === step ? 'bg-[#0D2B70] text-white' : (currentStep > step ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500')">
                                        <span class="inline-flex items-center justify-center h-5 w-5 rounded-full text-[11px] font-bold"
                                            :class="currentStep === step ? 'bg-white/20 text-white' : (currentStep > step ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700')"
                                            x-text="currentStep > step ? '✓' : step"></span>
                                        <span x-text="stepLabels[step - 1]"></span>
                                    </div>
                                </template>
                            </div>

                            <div x-show="currentStep === 1" x-transition class="space-y-3">
                                <h3 class="text-lg font-bold text-[#0D2B70]">Step 1. Select Preferred Position</h3>
                                <label for="modal_preferred_vacancy_id" class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Open Positions
                                </label>
                                <select
                                    id="modal_preferred_vacancy_id"
                                    name="preferred_vacancy_id"
                                    x-model="selectedVacancyId"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/25"
                                >
                                    <option value="">Select position</option>
                                    <template x-for="vacancy in vacancies" :key="vacancy.vacancy_id">
                                        <option :value="vacancy.vacancy_id" x-text="vacancy.position_title + ' (' + vacancy.vacancy_type + ')'"></option>
                                    </template>
                                </select>
                                <p x-show="vacancies.length === 0" class="text-xs text-rose-600">
                                    No open positions are currently available. Please contact HR for assistance.
                                </p>
                            </div>

                            <div x-show="currentStep === 2" x-transition class="space-y-3">
                                <h3 class="text-lg font-bold text-[#0D2B70]">Step 2. Requirement Readiness</h3>
                                <template x-for="item in readinessItems" :key="item.key">
                                    <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/70">
                                        <p class="text-sm font-bold text-[#0D2B70]" x-text="item.label"></p>
                                        <p class="text-xs text-slate-600 mt-1 whitespace-pre-line" x-text="requirementText(item.key)"></p>
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <label class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 cursor-pointer">
                                                <input type="radio" class="accent-emerald-600" :name="'readiness_' + item.key" value="yes" x-model="readiness[item.key]">
                                                Yes, available
                                            </label>
                                            <label class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 cursor-pointer">
                                                <input type="radio" class="accent-rose-600" :name="'readiness_' + item.key" value="no" x-model="readiness[item.key]">
                                                Not yet
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="currentStep === 3" x-transition class="space-y-3">
                                <h3 class="text-lg font-bold text-[#0D2B70]">Step 3. Confirm and Submit</h3>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                                    <p><span class="font-semibold text-[#0D2B70]">Preferred Position:</span> <span x-text="selectedVacancy ? selectedVacancy.position_title : 'Not selected'"></span></p>
                                </div>
                                <label class="flex items-start gap-2 rounded-lg border border-slate-200 p-3 bg-white cursor-pointer">
                                    <input type="checkbox" name="attest_truthful" value="1" x-model="attestTruthful" class="mt-0.5 accent-[#0D2B70]">
                                    <span class="text-sm text-slate-700">I certify that all my onboarding declarations are true and accurate.</span>
                                </label>
                                <label class="flex items-start gap-2 rounded-lg border border-slate-200 p-3 bg-white cursor-pointer">
                                    <input type="checkbox" name="attest_accountability" value="1" x-model="attestAccountability" class="mt-0.5 accent-[#0D2B70]">
                                    <span class="text-sm text-slate-700">I understand that incomplete or incorrect declarations may affect my application.</span>
                                </label>
                            </div>

                            <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm flex items-center justify-between gap-3 pt-2 border-t border-slate-200">
                                <button
                                    type="button"
                                    @click="prevStep()"
                                    x-show="currentStep > 1"
                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                                >
                                    Back
                                </button>

                                <div class="ml-auto flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="nextStep()"
                                        x-show="currentStep < 3"
                                        :disabled="!canProceed"
                                        class="inline-flex items-center gap-2 rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A245D] disabled:cursor-not-allowed disabled:bg-slate-400"
                                    >
                                        Continue
                                    </button>

                                    <button
                                        type="submit"
                                        x-show="currentStep === 3"
                                        :disabled="!canSubmit"
                                        class="inline-flex items-center gap-2 rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A245D] disabled:cursor-not-allowed disabled:bg-slate-400"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function dashboardOnboardingWizard(vacancies, initialVacancyId, initialReadiness) {
            return {
                vacancies: Array.isArray(vacancies) ? vacancies : [],
                selectedVacancyId: initialVacancyId || '',
                currentStep: 1,
                stepLabels: ['Position', 'Readiness', 'Confirmation'],
                readinessItems: [
                    { key: 'education', label: 'Education' },
                    { key: 'experience', label: 'Experience' },
                    { key: 'training', label: 'Training' },
                    { key: 'eligibility', label: 'Eligibility' },
                ],
                readiness: {
                    education: ['yes', 'no'].includes(initialReadiness?.education) ? initialReadiness.education : '',
                    experience: ['yes', 'no'].includes(initialReadiness?.experience) ? initialReadiness.experience : '',
                    training: ['yes', 'no'].includes(initialReadiness?.training) ? initialReadiness.training : '',
                    eligibility: ['yes', 'no'].includes(initialReadiness?.eligibility) ? initialReadiness.eligibility : '',
                },
                attestTruthful: false,
                attestAccountability: false,
                get selectedVacancy() {
                    return this.vacancies.find((vacancy) => String(vacancy.vacancy_id) === String(this.selectedVacancyId)) || null;
                },
                requirementText(key) {
                    return this.selectedVacancy?.requirements?.[key] || 'Not specified';
                },
                get canProceed() {
                    if (this.currentStep === 1) {
                        return this.selectedVacancyId !== '';
                    }
                    if (this.currentStep === 2) {
                        return this.readinessItems.every((item) => ['yes', 'no'].includes(this.readiness[item.key]));
                    }
                    return true;
                },
                get canSubmit() {
                    return this.selectedVacancyId !== '' && this.attestTruthful && this.attestAccountability;
                },
                nextStep() {
                    if (!this.canProceed) return;
                    this.currentStep = Math.min(this.currentStep + 1, 3);
                },
                prevStep() {
                    this.currentStep = Math.max(this.currentStep - 1, 1);
                },
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            const actionCard = document.getElementById('action-required-card');
            const actionModal = document.getElementById('action-required-modal');
            const actionClose = document.getElementById('action-required-close');
            const openModal = () => {
                if (!actionModal) return;
                actionModal.classList.remove('hidden');
            };
            const closeModal = () => {
                if (!actionModal) return;
                actionModal.classList.add('hidden');
            };
            [actionCard, actionClose, actionModal].forEach((el) => {
                if (!el) return;
                if (el === actionCard) el.addEventListener('click', openModal);
                if (el === actionClose) el.addEventListener('click', closeModal);
                if (el === actionModal) el.addEventListener('click', (e) => { if (e.target === actionModal) closeModal(); });
            });

            const normalizeNotificationUrl = window.normalizeNotificationUrl || ((targetUrl) => targetUrl || '');
            document.querySelectorAll('.js-recent-notification').forEach((item) => {
                item.addEventListener('click', () => {
                    const targetUrl = item.dataset.link;
                    if (!targetUrl) return;
                    window.location.href = normalizeNotificationUrl(targetUrl);
                });
            });

            const onboardingModal = document.getElementById('applicant-onboarding-modal');
            const shouldOpenOnboardingModal = @json($shouldOpenOnboardingModal);
            if (onboardingModal && shouldOpenOnboardingModal) {
                onboardingModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        });
    </script>
@endpush

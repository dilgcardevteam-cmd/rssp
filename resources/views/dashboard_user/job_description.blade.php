@if(session('success'))
    @include('partials.alerts_template', [
        'showTrigger' => false,
        'title' => 'Success',
        'message' => session('success'),
        'okText' => 'OK',
        'showCancel' => false
    ])
@endif

@if(session('error'))
    @include('partials.alerts_template', [
        'showTrigger' => false,
        'title' => 'Not allowed',
        'message' => session('error'),
        'okText' => 'OK',
        'showCancel' => false
    ])
@endif

@extends('layout.app')
@section('title', 'DILG - Job Description')

@push('styles')
    <style>
        body {
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
    @php
        $requiredDocsPayload = session('required_docs_prompt');
        $showRequiredDocsModalOnLoad = is_array($requiredDocsPayload)
            && (($requiredDocsPayload['vacancy_id'] ?? $vacancy->vacancy_id) === $vacancy->vacancy_id);
        $showPdsRequiredModalOnLoad = (bool) session('pds_required_prompt', false);

        $mismatchPayload = session('doc_track_mismatch');
        $showMismatchModalOnLoad = is_array($mismatchPayload)
            && (($mismatchPayload['vacancy_id'] ?? $vacancy->vacancy_id) === $vacancy->vacancy_id);
        $hasDocTrackMismatch = (bool) ($docTrackMismatch ?? false);
        $submittedTrackForModal = $mismatchSubmittedTrack ?? ($mismatchPayload['submitted_track'] ?? null);
        $vacancyTrackForModal = $vacancyTrack ?? ($mismatchPayload['vacancy_track'] ?? 'Plantilla');
        $docUploadRedirectUrlForModal = $docUploadRedirectUrl
            ?? route('display_c5', [
                'doc_track' => $vacancyTrackForModal,
                'vacancy_id' => $vacancy->vacancy_id,
            ]);
        $requiredDocsTrackForModal = $vacancyTrackForModal;
        $requiredDocsRedirectUrlForModal = $docUploadRedirectUrlForModal;
        $requiredDocsPreviewForModal = $requiredDocsPreview ?? [];
        $hasMissingRequiredDocsForModal = (bool) ($hasMissingRequiredDocs ?? false);
        $hasIncompletePdsForApply = !($hasCompletedPdsForApply ?? false);
        $isEligibilityQualifiedForPanel = (bool) ($isEligibilityQualified ?? true);

        $statusRaw = strtolower(trim((string) $vacancy->status));
        $isClosed = in_array($statusRaw, ['closed', 'no', '0', 'inactive'], true);
        $status = $isClosed ? 'Closed' : 'Open';
        $typeIsPlantilla = strcasecmp(trim((string) $vacancy->vacancy_type), 'plantilla') === 0;
        $typeIsCos = strcasecmp(trim((string) $vacancy->vacancy_type), 'cos') === 0;
        $typeLabel = $typeIsCos
            ? 'Contract of Service Position'
            : ($typeIsPlantilla ? 'Plantilla Position' : (string) $vacancy->vacancy_type);

        $datePostedDisplay = optional($vacancy->created_at)->format('M d, Y') ?? 'Not specified';
        $deadlineDisplay = \Carbon\Carbon::parse($vacancy->closing_date)->subMinute()->format('M d, Y g:i A');
        $salaryValue = $vacancy->monthly_salary;
        $monthlySalaryDisplay = is_numeric($salaryValue)
            ? 'PHP ' . number_format((float) $salaryValue, 2)
            : ((string) ($salaryValue ?: 'Not specified'));
        $qualificationChecksForPanel = is_array($qualificationChecks ?? null) ? $qualificationChecks : [];
        $missingQualificationLabelsForPanel = is_array($missingQualificationLabels ?? null)
            ? array_values(array_filter(array_map(fn($value) => trim((string) $value), $missingQualificationLabels)))
            : [];
        // Check if initial assessment is already completed (manually or auto-populated)
        $initialAssessmentSessionKey = 'initial_assessment_answers.' . trim((string) $vacancy->vacancy_id);
        $existingAssessment = session($initialAssessmentSessionKey, []);
        $hasCompletedInitialAssessment = is_array($existingAssessment)
            && array_key_exists('has_subscribed_pds', $existingAssessment);

        $showInitialAssessmentFlow = !$isClosed
            && !$hasApplied
            && !$hasCompletedInitialAssessment;
        $qualificationLabelMap = [
            'education' => 'Education',
            'training' => 'Training',
            'experience' => 'Experience',
            'eligibility' => 'Eligibility',
        ];
    @endphp

    <main class="flex-1 min-w-0 font-montserrat mr-4 space-y-5 pb-6">
        <section class="relative overflow-hidden rounded-3xl border border-[#0D2B70]/15 bg-gradient-to-br from-white via-[#F4F8FF] to-[#E8F0FF] p-5 sm:p-8 shadow-sm">
            <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#0D2B70]/10 blur-2xl"></div>
            <div class="absolute -left-12 -bottom-12 h-36 w-36 rounded-full bg-[#1D4ED8]/10 blur-2xl"></div>

            <div class="relative flex flex-col gap-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#0D2B70]/70 font-semibold">Career Opportunity</p>
                        <h1 class="text-2xl sm:text-4xl font-extrabold text-[#0D2B70] leading-tight break-words">
                            {{ $vacancy->position_title }}
                        </h1>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full border border-[#0D2B70]/20 bg-white px-3 py-1 text-xs font-semibold text-[#0D2B70]">
                            <i data-feather="briefcase" class="h-3.5 w-3.5"></i>
                            {{ $typeLabel }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border {{ $isClosed ? 'bg-red-100 text-red-700 border-red-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-sm">
                    <div class="rounded-xl border border-[#0D2B70]/15 bg-white/90 px-3 py-2">
                        <p class="text-[#0D2B70]/70">Date Posted</p>
                        <p class="font-bold text-[#0D2B70]">{{ $datePostedDisplay }}</p>
                    </div>
                    <div class="rounded-xl border border-[#0D2B70]/15 bg-white/90 px-3 py-2">
                        <p class="text-[#0D2B70]/70">Deadline</p>
                        <p class="font-bold text-[#0D2B70]">{{ $deadlineDisplay }}</p>
                    </div>
                    <div class="rounded-xl border border-[#0D2B70]/15 bg-white/90 px-3 py-2">
                        <p class="text-[#0D2B70]/70">Place of Assignment</p>
                        <p class="font-bold text-[#0D2B70]">{{ $vacancy->place_of_assignment ?: 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.7fr)_360px] gap-5 items-start">
            <div class="space-y-5">
                <article class="rounded-2xl border border-[#0D2B70]/15 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-[#0D2B70]/10 text-[#0D2B70] flex items-center justify-center">
                            <i data-feather="award" class="h-4 w-4"></i>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D2B70]">Qualification Standards</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Education</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $vacancy->qualification_education ?: 'Not specified' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Experience</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $vacancy->qualification_experience ?: 'Not specified' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Training</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $vacancy->qualification_training ?: 'Not specified' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Eligibility</p>
                            <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($qualificationEligibilityDisplay ?? ($vacancy->qualification_eligibility ?: 'Not specified'))) !!}</p>
                        </div>
                    </div>

                    @if($typeIsPlantilla && !empty($vacancy->competencies))
                        <div class="mt-3 rounded-xl border border-slate-200 p-3 bg-white">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Competencies</p>
                            <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->competencies)) !!}</p>
                        </div>
                    @endif
                </article>

                @if($typeIsCos)
                    <article class="rounded-2xl border border-[#0D2B70]/15 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="h-8 w-8 rounded-lg bg-[#0D2B70]/10 text-[#0D2B70] flex items-center justify-center">
                                <i data-feather="layers" class="h-4 w-4"></i>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-[#0D2B70]">COS Engagement Details</h2>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Scope of Work</p>
                                <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->scope_of_work)) !!}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Expected Output</p>
                                <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->expected_output)) !!}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Duration of Work</p>
                                <p class="text-sm text-slate-700 mt-1 leading-6">{!! nl2br(e($vacancy->duration_of_work)) !!}</p>
                            </div>
                        </div>
                    </article>
                @endif

                <article class="rounded-2xl border border-[#0D2B70]/15 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-8 w-8 rounded-lg bg-[#0D2B70]/10 text-[#0D2B70] flex items-center justify-center">
                            <i data-feather="send" class="h-4 w-4"></i>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D2B70]">Application Instructions</h2>
                    </div>

                    <div class="space-y-3 text-sm text-slate-700">
                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="font-semibold text-[#0D2B70]">How to Apply</p>
                            <p class="mt-1">
                                Complete your application in this system by reviewing the qualification standards, uploading all required documents, and clicking Apply for This Position before the deadline. Ensure that all submitted information and documents are complete, accurate, and truthful.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-3 bg-slate-50/70">
                            <p class="font-semibold text-[#0D2B70]">Address To</p>
                            <p class="mt-1">
                                {{ $vacancy->to_person }}, {{ $vacancy->to_position }}, {{ $vacancy->to_office }}, {{ $vacancy->to_office_address }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                            <p class="font-semibold text-red-700">Notice</p>
                            <p class="mt-1 text-red-700 font-medium">APPLICATIONS WITH INCOMPLETE DOCUMENTS SHALL NOT BE ENTERTAINED.</p>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="space-y-5 xl:sticky xl:top-6">
                <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-bold text-[#0D2B70]">Application Panel</h3>
                    <p class="text-xs text-slate-500 mt-1">Check readiness, then submit your application.</p>

                    <div class="mt-4 rounded-xl border border-[#0D2B70]/15 bg-[#F8FAFF] p-3">
                        <p class="text-xs text-slate-500">Monthly Compensation</p>
                        <p class="text-xl font-extrabold text-[#0D2B70]">{{ $monthlySalaryDisplay }}</p>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 justify-center">
                        @if ($hasApplied)
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-400 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="check-circle" class="w-4 h-4"></i> ALREADY APPLIED
                            </button>
                        @elseif (!$isClosed && $hasIncompletePdsForApply)
                            <button type="button" onclick="openApplyModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-500 hover:bg-green-600 text-white text-sm font-semibold transition">
                                <i data-feather="clipboard" class="w-4 h-4"></i> APPLY
                            </button>
                        @elseif (!$isClosed && !$isEligibilityQualifiedForPanel)
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-500 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="x-circle" class="w-4 h-4"></i> NOT ELIGIBLE
                            </button>
                        @elseif (!$isClosed)
                            <button type="button" onclick="openApplyModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                                <i data-feather="arrow-right" class="w-4 h-4"></i> APPLY FOR THIS POSITION
                            </button>
                        @else
                            <button disabled
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-400 text-white text-sm font-semibold cursor-not-allowed">
                                <i data-feather="x-circle" class="w-4 h-4"></i> APPLICATION CLOSED
                            </button>
                        @endif

                        @if(!$isClosed && !$hasApplied && !$hasIncompletePdsForApply && !$hasCompletedInitialAssessment)
                            <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                                <p class="font-semibold">Before applying, complete the initial assessment first.</p>
                                <p class="mt-1">This runs each time you submit an application for this vacancy.</p>
                            </div>
                        @elseif(!$isClosed && !$hasApplied && !$hasIncompletePdsForApply && !$isEligibilityQualifiedForPanel)
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                <p class="font-semibold">You are not yet qualified for this position.</p>
                                <p class="mt-1">{{ $eligibilityMismatchMessage ?: 'Please complete the missing requirement(s) below.' }}</p>
                                @if(!empty($missingQualificationLabelsForPanel))
                                    <ul class="mt-2 list-disc list-inside space-y-1">
                                        @foreach($missingQualificationLabelsForPanel as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$isClosed && !$hasApplied && $hasIncompletePdsForApply)
                    <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-bold text-[#0D2B70]">Qualification Check</h3>
                        <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-3 text-sm text-blue-800">
                            Complete your PDS first so we can check your Education and Eligibility requirements, and verify your submitted Training and Experience entries.
                        </div>
                    </div>
                @elseif(!$isClosed && !$hasApplied && !empty($qualificationChecksForPanel))
                    <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                        <h3 class="text-base font-bold text-[#0D2B70]">Qualification Check</h3>
                        <div class="mt-3 space-y-2">
                            @foreach($qualificationLabelMap as $field => $label)
                                @php
                                    $check = $qualificationChecksForPanel[$field] ?? null;
                                    $checkStatus = is_array($check) ? ($check['status'] ?? 'na') : 'na';
                                    $required = is_array($check) ? (bool) ($check['required'] ?? false) : false;
                                    $met = is_array($check) ? (bool) ($check['met'] ?? false) : true;
                                    $isSubmissionField = in_array($field, ['training', 'experience'], true);
                                    $submitted = is_array($check)
                                        ? (bool) ($check['submitted'] ?? false)
                                        : false;
                                    $requirementText = trim((string) (($check['requirement'] ?? '') ?: ''));
                                @endphp
                                <div class="rounded-lg border border-slate-200 px-3 py-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
                                        @if(!$required || $checkStatus === 'na')
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">
                                                Not Required
                                            </span>
                                        @elseif($isSubmissionField && $submitted)
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                For validation
                                            </span>
                                        @elseif($isSubmissionField)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                                                Not Submitted
                                            </span>
                                        @elseif($met)
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                Met
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                                                Not Met
                                            </span>
                                        @endif
                                    </div>
                                    @if($requirementText !== '')
                                        <p class="mt-1 text-xs text-slate-500">Required: {{ $requirementText }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="rounded-2xl border border-[#0D2B70]/20 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-bold text-[#0D2B70]">Vacancy Snapshot</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Vacancy Type</dt>
                            <dd class="font-semibold text-slate-700 text-right">{{ $vacancy->vacancy_type ?: 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Status</dt>
                            <dd class="font-semibold {{ $isClosed ? 'text-red-600' : 'text-emerald-600' }}">{{ $status }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Posted</dt>
                            <dd class="font-semibold text-slate-700 text-right">{{ $datePostedDisplay }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Deadline</dt>
                            <dd class="font-semibold text-slate-700 text-right">{{ $deadlineDisplay }}</dd>
                        </div>
                        @if($typeIsPlantilla)
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Salary Grade</dt>
                                <dd class="font-semibold text-slate-700 text-right">{{ $vacancy->salary_grade ?: 'N/A' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Plantilla Item No.</dt>
                                <dd class="font-semibold text-slate-700 text-right">{{ $vacancy->plantilla_item_no ?: 'N/A' }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if($typeIsPlantilla)
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            @if(!empty($vacancy->csc_form_path))
                                <a href="{{ \App\Support\PreviewUrl::forPath($vacancy->csc_form_path) }}" target="_blank"
                                    class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-semibold rounded-md border border-[#0D2B70] text-[#0D2B70] hover:bg-[#0D2B70] hover:text-white transition">
                                    View CSC Form Attachment
                                </a>
                            @else
                                <div class="text-xs text-slate-500 border border-slate-200 bg-slate-50 rounded-md p-2">
                                    No CSC form attachment uploaded.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </aside>
        </section>

        <div id="pdsRequiredModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/55 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <h2 class="text-lg font-semibold text-[#002C76] mb-3">Complete Personal Data Sheet First</h2>
                <p class="text-sm text-gray-700 mb-6">
                    You need to complete your Personal Data Sheet from Personal Information up to Work Experience Sheet before applying for a job.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closePdsRequiredModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="window.location.href='{{ route('display_c1', ['simple' => 1]) }}'" class="use-loader px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Go to PDS
                    </button>
                </div>
            </div>
        </div>

        <div id="requiredDocsModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 overflow-hidden border border-[#0D2B70]/10">
                <div class="bg-[#0D2B70] px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">
                        Required Documents ({{ $requiredDocsTrackForModal }})
                    </h2>
                    <p class="text-sm text-blue-100 mt-1">
                        Upload these documents to continue your application.
                    </p>
                </div>

                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-4">
                        Previously uploaded required documents are reused automatically for your next applications.
                        Upload only the required documents that are still missing for this vacancy.
                    </p>

                    <p class="text-sm text-red-700 mb-4 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span>
                            <span class="font-medium">Note:</span>
                            Make sure all required documents are available before applying.
                            Upload any missing required document now to continue your application.
                        </span>
                    </p>

                    <p class="text-sm text-red-700 mb-4 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span>
                            <span class="font-medium">Warning:</span>
                            Reliability in your dishonesty about the required documents may affect your application negatively.
                        </span>
                    </p>

                    <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-xl p-4 mb-6 bg-slate-50">
                        <ul class="space-y-3">
                            @php
                                $filteredDocs = $requiredDocsPreviewForModal;
                                if (strtolower($vacancy->vacancy_type) === 'cos') {
                                    $filteredDocs = collect($requiredDocsPreviewForModal)->filter(function($doc) {
                                        $docLabel = is_array($doc) ? ($doc['label'] ?? '') : '';
                                        return !str_contains(strtolower($docLabel), 'certificate of employment')
                                            && !str_contains(strtolower($docLabel), 'certificate of training')
                                            && !str_contains(strtolower($docLabel), 'transcript of record');
                                    })->values()->toArray();
                                }
                            @endphp

                            @forelse($filteredDocs as $doc)
                                @php
                                    $docLabel = is_array($doc) ? ($doc['label'] ?? 'Document') : 'Document';
                                @endphp
                                <li class="flex items-start gap-3 rounded-lg bg-white border border-slate-200 p-3">
                                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#0D2B70] text-[11px] font-bold text-white">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="text-sm text-slate-800 leading-5">{{ $docLabel }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500">No required documents found.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeRequiredDocsModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="button" onclick="window.location.href='{{ $requiredDocsRedirectUrlForModal }}'" class="use-loader px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                            Go to Upload Documents
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="docTrackMismatchModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <h2 class="text-lg font-semibold text-[#002C76] mb-3">Required Documents Mismatch</h2>
                <p class="text-sm text-gray-700 mb-6">
                    @if($submittedTrackForModal)
                        You submitted <span class="font-semibold">{{ $submittedTrackForModal }}</span>
                        documents, but this vacancy is <span class="font-semibold">{{ $vacancyTrackForModal }}</span>.
                    @else
                        This vacancy requires <span class="font-semibold">{{ $vacancyTrackForModal }}</span> documents.
                    @endif
                    Please upload the required {{ $vacancyTrackForModal }} documents first.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeDocTrackMismatchModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="window.location.href='{{ $docUploadRedirectUrlForModal }}'" class="use-loader px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Go to Upload PDF ({{ $vacancyTrackForModal }})
                    </button>
                </div>
            </div>
        </div>

        <div id="initialAssessmentEducationModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 border border-[#0D2B70]/10">
                <p class="text-xs uppercase tracking-[0.15em] text-[#0D2B70]/70 font-semibold">Initial Assessment</p>
                <h2 class="text-lg font-semibold text-[#002C76] mt-1">Question 1</h2>
                <p class="text-sm text-gray-700 mt-3 font-medium">What is your highest educational attainment?</p>
                <div class="mt-4">
                    <label for="initialAssessmentEducationAttainment" class="block text-xs uppercase tracking-wide text-slate-500 mb-2">Highest Education Attainment</label>
                    <select
                        id="initialAssessmentEducationAttainment"
                        data-assessment-education-level="1"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                    >
                        <option value="">Select highest educational attainment</option>
                        <option value="HIGH_SCHOOL_GRAD">Junior High School Graduate</option>
                        <option value="SENIOR_HIGH_SCHOOL_GRAD">Senior High School Graduate</option>
                        <option value="COLLEGE_2Y">Completion of 2 Years in College</option>
                        <option value="BACHELOR">Bachelors Degree</option>
                        <option value="MASTERAL">Masteral Degree</option>
                        <option value="DOCTORATE">Doctorate Degree</option>
                        <option value="OTHERS">Others (Specify)</option>
                    </select>
                </div>
                <div id="initialAssessmentDegreeFieldWrap" class="mt-4">
                    <label id="initialAssessmentDegreeLabel" for="initialAssessmentDegreeInput" class="block text-xs uppercase tracking-wide text-slate-500 mb-2">Degree/Course</label>
                    <div class="relative">
                        <input
                            id="initialAssessmentDegreeInput"
                            type="text"
                            autocomplete="off"
                            placeholder="Search from the list or type your degree/course"
                            data-assessment-input="degree"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                        >
                        <div id="initialAssessmentDegreeMenu" class="absolute left-0 right-0 z-40 mt-1 hidden rounded-xl border border-slate-200 bg-white shadow-lg" data-assessment-menu="degree">
                            <div id="initialAssessmentDegreeOptionsWrap" class="max-h-56 overflow-auto py-1" data-assessment-options="degree"></div>
                        </div>
                    </div>
                    <p id="initialAssessmentDegreeHint" class="mt-1 text-xs text-gray-500">Search from the list or type if your degree/course is not available.</p>
                </div>
                <p id="initialAssessmentEducationLevelHint" class="mt-2 hidden text-xs text-gray-500">No degree/course selection is required for this attainment.</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeInitialAssessmentEducationModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="goToInitialAssessmentEligibility()" class="px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <div id="initialAssessmentEligibilityModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 border border-[#0D2B70]/10">
                <p class="text-xs uppercase tracking-[0.15em] text-[#0D2B70]/70 font-semibold">Initial Assessment</p>
                <h2 class="text-lg font-semibold text-[#002C76] mt-1">Question 2</h2>
                <p class="text-sm text-gray-700 mt-3 font-medium">What is your civil service eligibility?</p>
                <div class="mt-4">
                    <label for="initialAssessmentEligibilityInput" class="block text-xs uppercase tracking-wide text-slate-500 mb-2">Civil Service Eligibility</label>
                    <div class="relative">
                        <input
                            id="initialAssessmentEligibilityInput"
                            type="text"
                            autocomplete="off"
                            placeholder="Search from the list or type your eligibility"
                            data-assessment-input="eligibility"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                        >
                        <div id="initialAssessmentEligibilityMenu" class="absolute left-0 right-0 z-40 mt-1 hidden rounded-xl border border-slate-200 bg-white shadow-lg" data-assessment-menu="eligibility">
                            <div id="initialAssessmentEligibilityOptionsWrap" class="max-h-56 overflow-auto py-1" data-assessment-options="eligibility"></div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Search from the list or type if your eligibility is not available.</p>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeInitialAssessmentEligibilityModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="goBackToInitialAssessmentEducation()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Back
                    </button>
                    <button type="button" onclick="completeInitialAssessmentEligibility()" class="px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                        Continue
                    </button>
                </div>
            </div>
        </div>

        <div id="initialAssessmentPqeModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <p class="text-xs uppercase tracking-[0.15em] text-[#0D2B70]/70 font-semibold">Initial Assessment</p>
                <h2 class="text-lg font-semibold text-[#002C76] mt-1">Question 3</h2>
                <p class="text-sm text-gray-700 mt-3 font-medium">Have you taken and passed the PQE (Pre Qualifying Exam)?</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeInitialAssessmentPqeModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="answerInitialAssessmentPqe(false)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        No
                    </button>
                    <button type="button" onclick="answerInitialAssessmentPqe(true)" class="px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                        Yes
                    </button>
                </div>
            </div>
        </div>

        <div id="initialAssessmentSubscribedPdsModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <p class="text-xs uppercase tracking-[0.15em] text-[#0D2B70]/70 font-semibold">Initial Assessment</p>
                <h2 class="text-lg font-semibold text-[#002C76] mt-1">Question 4</h2>
                <p class="text-sm text-gray-700 mt-3 font-medium">Do you have a subscribed PDS form?</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeInitialAssessmentSubscribedPdsModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Close
                    </button>
                    <button type="button" onclick="answerInitialAssessmentSubscribedPds(false)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        No
                    </button>
                    <button type="button" onclick="answerInitialAssessmentSubscribedPds(true)" class="px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                        Yes
                    </button>
                </div>
            </div>
        </div>

        <div id="initialAssessmentFeedbackModal" class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/60 backdrop-blur-md hidden px-4 py-6">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-[#0D2B70]/10">
                <p class="text-xs uppercase tracking-[0.15em] text-[#0D2B70]/70 font-semibold">Initial Assessment</p>
                <h2 id="initialAssessmentFeedbackTitle" class="text-lg font-semibold text-[#002C76] mt-1">Assessment Result</h2>
                <p id="initialAssessmentFeedbackMessage" class="text-sm text-gray-700 mt-3 leading-6"></p>
                <div id="initialAssessmentFeedbackNoticeActions" class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeInitialAssessmentFeedbackModal()" class="px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                        Close
                    </button>
                </div>
                <div id="initialAssessmentFeedbackDecisionActions" class="mt-6 hidden justify-end gap-2">
                    <button type="button" onclick="closeInitialAssessmentFeedbackModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        No
                    </button>
                    <button type="button" onclick="confirmInitialAssessmentFeedback()" class="px-4 py-2 bg-[#0D2B70] text-white rounded-lg hover:bg-[#0A245D]">
                        Yes, Continue
                    </button>
                </div>
            </div>
        </div>

        @include('partials.loader')
    </main>
@endsection

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        if (id === 'initialAssessmentFeedbackModal' && typeof initialAssessmentFeedbackState === 'object') {
            initialAssessmentFeedbackState.onConfirm = null;
        }
        document.body.classList.remove('overflow-hidden');
    }

    function openApplyModal() {
        const shouldRunInitialAssessment = @json($showInitialAssessmentFlow);
        if (shouldRunInitialAssessment) { openModal('initialAssessmentEducationModal'); return; }

        if (hasIncompletePds) { openModal('pdsRequiredModal'); return; }

        if (hasDocTrackMismatch) { openModal('docTrackMismatchModal'); return; }

        openModal('requiredDocsModal');
    }

    function closePdsRequiredModal()       { closeModal('pdsRequiredModal'); }
    function closeRequiredDocsModal()      { closeModal('requiredDocsModal'); }
    function closeDocTrackMismatchModal()  { closeModal('docTrackMismatchModal'); }
    function closeInitialAssessmentEducationModal() { closeModal('initialAssessmentEducationModal'); }
    function closeInitialAssessmentEligibilityModal() { closeModal('initialAssessmentEligibilityModal'); }
    function closeInitialAssessmentPqeModal() { closeModal('initialAssessmentPqeModal'); }
    function closeInitialAssessmentSubscribedPdsModal() { closeModal('initialAssessmentSubscribedPdsModal'); }
    function closeInitialAssessmentFeedbackModal() {
        closeModal('initialAssessmentFeedbackModal');
        initialAssessmentFeedbackState.onConfirm = null;
    }

    const initialAssessmentState = {
        educationAttainment: '',
        degree: '',
        eligibility: '',
        hasPqe: null,
        hasSubscribedPds: null,
    };
    const ASSESSMENT_OTHERS_LABEL = 'Others (Specify)';
    const initialAssessmentFeedbackState = {
        onConfirm: null,
    };

    const initialAssessmentSubmitUrl = @json(route('initial_assessment.submit', ['vacancy_id' => $vacancy->vacancy_id]));
    const pdsRedirectUrl = @json(route('display_c1', ['simple' => 1]));
    const hasIncompletePds = @json($hasIncompletePdsForApply);
    const hasDocTrackMismatch = @json($hasDocTrackMismatch);
    const initialAssessmentEducationAttainmentMeta = {
        HIGH_SCHOOL_GRAD: {
            label: 'Junior High School Graduate',
            programLevel: null,
            requiresProgram: false,
            fallbackDegree: 'Junior High School Graduate',
            degreeLabel: 'Degree/Course',
            degreePlaceholder: 'Search from the list or type your degree/course',
            degreeHint: 'Search from the list or type if your degree/course is not available.',
        },
        SENIOR_HIGH_SCHOOL_GRAD: {
            label: 'Senior High School Graduate',
            programLevel: null,
            requiresProgram: false,
            fallbackDegree: 'Senior High School Graduate',
            degreeLabel: 'Degree/Course',
            degreePlaceholder: 'Search from the list or type your degree/course',
            degreeHint: 'Search from the list or type if your degree/course is not available.',
        },
        COLLEGE_2Y: {
            label: 'Completion of 2 Years in College',
            programLevel: 'COLLEGE',
            requiresProgram: true,
            fallbackDegree: 'Completion of 2 years of studies in college',
            degreeLabel: 'Course',
            degreePlaceholder: 'Search from the list or type your course',
            degreeHint: 'Search from the list or type if your course is not available.',
        },
        BACHELOR: {
            label: 'Bachelors Degree',
            programLevel: 'COLLEGE',
            requiresProgram: true,
            fallbackDegree: "Bachelor's Degree",
            degreeLabel: 'Degree/Course',
            degreePlaceholder: 'Search from the list or type your degree/course',
            degreeHint: 'Search from the list or type if your degree/course is not available.',
        },
        MASTERAL: {
            label: 'Masteral Degree',
            programLevel: 'MASTERAL',
            requiresProgram: true,
            fallbackDegree: 'Masteral Degree',
            degreeLabel: 'Degree/Course',
            degreePlaceholder: 'Search from the list or type your degree/course',
            degreeHint: 'Search from the list or type if your degree/course is not available.',
        },
        DOCTORATE: {
            label: 'Doctorate Degree',
            programLevel: 'DOCTORATE',
            requiresProgram: true,
            fallbackDegree: 'Doctorate Degree',
            degreeLabel: 'Degree/Course',
            degreePlaceholder: 'Search from the list or type your degree/course',
            degreeHint: 'Search from the list or type if your degree/course is not available.',
        },
        OTHERS: {
            label: 'Others (Specify)',
            programLevel: null,
            requiresProgram: true,
            fallbackDegree: '',
            degreeLabel: 'Specify Educational Attainment / Degree',
            degreePlaceholder: 'Type your educational attainment or degree',
            degreeHint: 'Enter your educational attainment or degree if not listed above.',
        },
    };
    const initialAssessmentOptions = {
        degreeByLevel: @json($assessmentProgramOptions ?? ['COLLEGE' => [], 'MASTERAL' => [], 'DOCTORATE' => []]),
        eligibility: @json($assessmentEligibilityOptions ?? []),
    };

    // Helper to format eligibility display text from structured data
    function formatEligibilityDisplay(item) {
        if (typeof item === 'string') {
            return item; // Fallback for legacy string format
        }
        if (!item || typeof item !== 'object') {
            return '';
        }
        const name = item.name || '';
        const legalBasis = item.legal_basis || '';
        const level = item.level || '';

        if (legalBasis && level) {
            return `${name} (${legalBasis} | ${level})`;
        } else if (legalBasis) {
            return `${name} (${legalBasis})`;
        } else if (level) {
            return `${name} (${level})`;
        }
        return name;
    }

    // Helper to get eligibility name for comparison
    function getEligibilityName(item) {
        if (typeof item === 'string') {
            return item;
        }
        return item?.name || '';
    }

    function escapeAssessmentOptionHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeAssessmentInput(value) {
        return String(value || '').trim().replace(/\s+/g, ' ');
    }

    function assessmentEducationLevelEl() {
        return document.querySelector('[data-assessment-education-level]');
    }

    function currentAssessmentEducationAttainmentCode() {
        const levelEl = assessmentEducationLevelEl();
        return normalizeAssessmentInput(levelEl ? levelEl.value : '').toUpperCase();
    }

    function currentAssessmentEducationMeta() {
        const code = currentAssessmentEducationAttainmentCode();
        return initialAssessmentEducationAttainmentMeta[code] || null;
    }

    function syncInitialAssessmentEducationFieldState() {
        const degreeWrap = document.getElementById('initialAssessmentDegreeFieldWrap');
        const degreeLabel = document.getElementById('initialAssessmentDegreeLabel');
        const degreeInput = document.getElementById('initialAssessmentDegreeInput');
        const degreeHint = document.getElementById('initialAssessmentDegreeHint');
        const levelHint = document.getElementById('initialAssessmentEducationLevelHint');
        if (!degreeWrap || !degreeLabel || !degreeInput || !degreeHint || !levelHint) {
            return;
        }

        const meta = currentAssessmentEducationMeta();
        if (!meta) {
            degreeWrap.classList.remove('hidden');
            degreeLabel.textContent = 'Degree/Course';
            degreeInput.disabled = true;
            degreeInput.value = '';
            degreeInput.placeholder = 'Select highest educational attainment first';
            degreeHint.textContent = 'Select your highest educational attainment first to load the degree/course list.';
            degreeHint.classList.remove('hidden');
            levelHint.classList.add('hidden');
            closeAssessmentMenu('degree');
            return;
        }

        degreeLabel.textContent = meta.degreeLabel || 'Degree/Course';
        if (!meta.requiresProgram) {
            degreeWrap.classList.add('hidden');
            degreeInput.disabled = true;
            degreeInput.value = '';
            degreeInput.placeholder = meta.degreePlaceholder || 'Search from the list or type your degree/course';
            degreeHint.classList.add('hidden');
            levelHint.textContent = `No degree/course selection is required for ${meta.label}.`;
            levelHint.classList.remove('hidden');
            closeAssessmentMenu('degree');
            return;
        }

        degreeWrap.classList.remove('hidden');
        degreeInput.disabled = false;
        degreeInput.placeholder = meta.degreePlaceholder || 'Search from the list or type your degree/course';
        degreeHint.textContent = meta.degreeHint || 'Search from the list or type if your degree/course is not available.';
        degreeHint.classList.remove('hidden');
        levelHint.classList.add('hidden');
    }

    function assessmentInputEl(type) {
        return document.querySelector(`[data-assessment-input="${type}"]`);
    }

    function assessmentMenuEl(type) {
        return document.querySelector(`[data-assessment-menu="${type}"]`);
    }

    function assessmentOptionsWrapEl(type) {
        return document.querySelector(`[data-assessment-options="${type}"]`);
    }

    function currentAssessmentOptions(type) {
        if (type === 'degree') {
            const meta = currentAssessmentEducationMeta();
            if (!meta || !meta.requiresProgram || !meta.programLevel) {
                return [ASSESSMENT_OTHERS_LABEL];
            }

            const byLevel = initialAssessmentOptions.degreeByLevel;
            if (!byLevel || typeof byLevel !== 'object') {
                return [ASSESSMENT_OTHERS_LABEL];
            }

            const raw = byLevel[meta.programLevel];
            const options = Array.isArray(raw) ? raw : [];
            const unique = Array.from(new Set(options.map((item) => normalizeAssessmentInput(item)).filter((item) => item !== '')));
            if (!unique.includes(ASSESSMENT_OTHERS_LABEL)) {
                unique.push(ASSESSMENT_OTHERS_LABEL);
            }
            return unique;
        }

        const raw = initialAssessmentOptions[type];
        const options = Array.isArray(raw) ? raw : [];
        // For eligibility, keep structured objects; for others, use string normalization
        if (type === 'eligibility') {
            // Filter unique by name, keeping the full object structure
            const seen = new Set();
            const unique = options.filter((item) => {
                const name = getEligibilityName(item);
                const normalized = normalizeAssessmentInput(name).toLowerCase();
                if (normalized === '' || seen.has(normalized)) {
                    return false;
                }
                seen.add(normalized);
                return true;
            });
            return unique;
        }
        // Legacy string handling for other types
        const unique = Array.from(new Set(options.map((item) => normalizeAssessmentInput(item)).filter((item) => item !== '')));
        if (!unique.includes(ASSESSMENT_OTHERS_LABEL)) {
            unique.push(ASSESSMENT_OTHERS_LABEL);
        }
        return unique;
    }

    function filterAssessmentOptions(type, query) {
        const normalizedQuery = normalizeAssessmentInput(query).toLowerCase();
        const options = currentAssessmentOptions(type);
        if (!normalizedQuery) {
            return options;
        }

        // For eligibility, search in both name and formatted display
        if (type === 'eligibility') {
            return options.filter((item) => {
                const displayText = formatEligibilityDisplay(item).toLowerCase();
                const name = getEligibilityName(item).toLowerCase();
                return displayText.includes(normalizedQuery) || name.includes(normalizedQuery);
            });
        }

        // Legacy string handling for other types
        return options.filter((item) => String(item || '').toLowerCase().includes(normalizedQuery));
    }

    function closeAssessmentMenu(type) {
        const input = assessmentInputEl(type);
        const menu = assessmentMenuEl(type);
        if (!menu) {
            return;
        }

        menu.classList.add('hidden');
        if (input) {
            input.setAttribute('aria-expanded', 'false');
        }
    }

    function closeAllAssessmentMenus() {
        closeAssessmentMenu('degree');
        closeAssessmentMenu('eligibility');
    }

    function renderAssessmentOptions(type) {
        const input = assessmentInputEl(type);
        const optionsWrap = assessmentOptionsWrapEl(type);
        if (!input || !optionsWrap) {
            return;
        }

        if (type === 'degree') {
            const meta = currentAssessmentEducationMeta();
            if (!meta) {
                optionsWrap.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">Select highest educational attainment first.</div>';
                return;
            }

            if (!meta.requiresProgram) {
                optionsWrap.innerHTML = `<div class="px-3 py-2 text-sm text-slate-500">No degree/course selection is required for ${escapeAssessmentOptionHtml(meta.label)}.</div>`;
                return;
            }
        }

        const filtered = filterAssessmentOptions(type, input.value).slice(0, 200);
        if (filtered.length === 0) {
            optionsWrap.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">No matches found. Keep typing to use your own entry.</div>';
            return;
        }

        const selectedValue = normalizeAssessmentInput(input.value).toLowerCase();
        optionsWrap.innerHTML = filtered.map((item) => {
            // For eligibility, use structured data formatting
            if (type === 'eligibility') {
                const displayText = formatEligibilityDisplay(item);
                const name = getEligibilityName(item);
                const label = normalizeAssessmentInput(name);
                const selectedClass = selectedValue === label.toLowerCase() ? ' bg-slate-100 font-medium' : '';
                return `<button type="button" class="block w-full px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 focus:bg-slate-100${selectedClass}" data-assessment-option="${type}" data-label="${escapeAssessmentOptionHtml(label)}" data-display="${escapeAssessmentOptionHtml(displayText)}">${escapeAssessmentOptionHtml(displayText)}</button>`;
            }
            // Legacy string handling for other types
            const name = item;
            const label = normalizeAssessmentInput(name);
            const selectedClass = selectedValue === label.toLowerCase() ? ' bg-slate-100 font-medium' : '';
            return `<button type="button" class="block w-full px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 focus:bg-slate-100${selectedClass}" data-assessment-option="${type}" data-label="${escapeAssessmentOptionHtml(label)}">${escapeAssessmentOptionHtml(label)}</button>`;
        }).join('');
    }

    function openAssessmentMenu(type) {
        const input = assessmentInputEl(type);
        const menu = assessmentMenuEl(type);
        if (!input || !menu || input.disabled) {
            closeAssessmentMenu(type);
            return;
        }

        closeAllAssessmentMenus();
        renderAssessmentOptions(type);
        menu.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    }

    function bindAssessmentDropdown(type) {
        const input = assessmentInputEl(type);
        const optionsWrap = assessmentOptionsWrapEl(type);
        if (!input || !optionsWrap || input.dataset.bound === '1') {
            return;
        }

        input.dataset.bound = '1';
        input.setAttribute('aria-expanded', 'false');
        input.addEventListener('focus', () => openAssessmentMenu(type));
        input.addEventListener('click', () => openAssessmentMenu(type));
        input.addEventListener('input', () => {
            // Clear data-value when user types their own value
            if (type === 'eligibility') {
                input.removeAttribute('data-value');
            }
            openAssessmentMenu(type);
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAssessmentMenu(type);
                return;
            }

            if (event.key === 'Enter') {
                const menu = assessmentMenuEl(type);
                if (menu && !menu.classList.contains('hidden')) {
                    const firstOption = optionsWrap.querySelector(`button[data-assessment-option="${type}"]`);
                    if (firstOption) {
                        event.preventDefault();
                        firstOption.click();
                    }
                }
            }
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => {
                const active = document.activeElement;
                const menu = assessmentMenuEl(type);
                if (menu && active && menu.contains(active)) {
                    return;
                }
                closeAssessmentMenu(type);
            }, 120);
        });

        optionsWrap.addEventListener('click', (event) => {
            const target = event.target instanceof HTMLElement
                ? event.target.closest(`button[data-assessment-option="${type}"]`)
                : null;
            if (!target || !(target instanceof HTMLElement)) {
                return;
            }

            const label = normalizeAssessmentInput(target.getAttribute('data-label') || '');
            if (!label) {
                return;
            }

            if (label === ASSESSMENT_OTHERS_LABEL) {
                input.value = '';
                closeAssessmentMenu(type);
                if (type === 'degree') {
                    input.placeholder = 'Type your educational attainment or degree';
                }
                if (type === 'eligibility') {
                    input.placeholder = 'Type your eligibility (e.g., Driver\'s License)';
                }
                input.focus();
                return;
            }

            // For eligibility, use the formatted display text for visual, but store just the name
            if (type === 'eligibility') {
                const displayText = target.getAttribute('data-display') || label;
                input.value = displayText;
                // Store the actual name as a data attribute for form submission
                input.setAttribute('data-value', label);
            } else {
                input.value = label;
            }
            closeAssessmentMenu(type);
            input.focus();
        });
    }

    function closeInitialAssessmentFlowModals() {
        closeModal('initialAssessmentEducationModal');
        closeModal('initialAssessmentEligibilityModal');
        closeModal('initialAssessmentPqeModal');
        closeModal('initialAssessmentSubscribedPdsModal');
    }

    function setInitialAssessmentFeedbackContent(title, message, isDecision) {
        const titleEl = document.getElementById('initialAssessmentFeedbackTitle');
        const messageEl = document.getElementById('initialAssessmentFeedbackMessage');
        const noticeActionsEl = document.getElementById('initialAssessmentFeedbackNoticeActions');
        const decisionActionsEl = document.getElementById('initialAssessmentFeedbackDecisionActions');

        if (titleEl) {
            titleEl.textContent = title;
        }
        if (messageEl) {
            messageEl.textContent = message;
        }
        if (noticeActionsEl) {
            noticeActionsEl.classList.toggle('hidden', isDecision);
            noticeActionsEl.classList.toggle('flex', !isDecision);
        }
        if (decisionActionsEl) {
            decisionActionsEl.classList.toggle('hidden', !isDecision);
            decisionActionsEl.classList.toggle('flex', isDecision);
        }
    }

    function showInitialAssessmentNotice(title, message) {
        initialAssessmentFeedbackState.onConfirm = null;
        setInitialAssessmentFeedbackContent(title, message, false);
        openModal('initialAssessmentFeedbackModal');
    }

    function showInitialAssessmentDecision(title, message, onConfirm) {
        initialAssessmentFeedbackState.onConfirm = typeof onConfirm === 'function' ? onConfirm : null;
        setInitialAssessmentFeedbackContent(title, message, true);
        openModal('initialAssessmentFeedbackModal');
    }

    function confirmInitialAssessmentFeedback() {
        const callback = initialAssessmentFeedbackState.onConfirm;
        closeInitialAssessmentFeedbackModal();
        if (typeof callback === 'function') {
            callback();
        }
    }

    function getContinueAssessmentPromptMessage() {
        if (initialAssessmentState.hasSubscribedPds === true) {
            return 'You passed the initial assessment. Do you want to continue and upload your required documents now?';
        }
        if (hasIncompletePds) {
            return 'You passed the initial assessment. Do you want to continue and fill up your PDS now?';
        }
        if (hasDocTrackMismatch) {
            return 'You passed the initial assessment. Do you want to continue and update the required documents now?';
        }
        return 'You passed the initial assessment. Do you want to continue with your application now?';
    }

    async function submitInitialAssessment(options = {}) {
        const payload = {
            education_attainment: initialAssessmentState.educationAttainment,
            degree: initialAssessmentState.degree,
            eligibility: initialAssessmentState.eligibility,
        };

        if (typeof options.hasPqe === 'boolean') {
            payload.has_pqe = options.hasPqe;
        }

        if (typeof options.hasSubscribedPds === 'boolean') {
            payload.has_subscribed_pds = options.hasSubscribedPds;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const response = await fetch(initialAssessmentSubmitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.ok === false) {
            closeInitialAssessmentFlowModals();
            showInitialAssessmentNotice(
                'Not Qualified to Apply',
                String(data.message || 'Based on your initial assessment responses, you are currently not qualified for this position.')
            );
            return false;
        }

        return data;
    }

    function continueAfterInitialAssessment() {
        if (initialAssessmentState.hasSubscribedPds === true) {
            if (hasDocTrackMismatch) {
                openModal('docTrackMismatchModal');
                return;
            }

            openModal('requiredDocsModal');
            return;
        }

        if (hasIncompletePds) {
            window.location.href = pdsRedirectUrl;
            return;
        }

        if (hasDocTrackMismatch) {
            openModal('docTrackMismatchModal');
            return;
        }

        openModal('requiredDocsModal');
    }

    function goToInitialAssessmentEligibility() {
        const educationLevelSelect = assessmentEducationLevelEl();
        const educationAttainment = normalizeAssessmentInput(educationLevelSelect ? educationLevelSelect.value : '').toUpperCase();
        const educationMeta = initialAssessmentEducationAttainmentMeta[educationAttainment] || null;
        if (!educationMeta) {
            showInitialAssessmentNotice(
                'Education Attainment is Required',
                'Please select your highest educational attainment to continue with the initial assessment.'
            );
            return;
        }

        const degreeInput = document.getElementById('initialAssessmentDegreeInput');
        let degree = normalizeAssessmentInput(degreeInput ? degreeInput.value : '');
        if (educationMeta.requiresProgram && degree === '') {
            showInitialAssessmentNotice(
                'Degree is Required',
                'Please enter your degree/course to continue with the initial assessment.'
            );
            return;
        }

        if (!educationMeta.requiresProgram) {
            degree = normalizeAssessmentInput(educationMeta.fallbackDegree || educationMeta.label || '');
        }

        initialAssessmentState.educationAttainment = educationAttainment;
        initialAssessmentState.degree = degree;
        initialAssessmentState.hasPqe = null;
        initialAssessmentState.hasSubscribedPds = null;
        closeModal('initialAssessmentEducationModal');
        openModal('initialAssessmentEligibilityModal');
    }

    function goBackToInitialAssessmentEducation() {
        closeModal('initialAssessmentEligibilityModal');
        openModal('initialAssessmentEducationModal');
    }

    async function completeInitialAssessmentEligibility() {
        const eligibilityInput = document.getElementById('initialAssessmentEligibilityInput');
        let eligibility = normalizeAssessmentInput(eligibilityInput ? eligibilityInput.value : '');
        if (eligibility === '') {
            showInitialAssessmentNotice(
                'Eligibility is Required',
                'Please enter your civil service eligibility to continue with the initial assessment.'
            );
            return;
        }

        // If data-value exists (from dropdown selection), use that; otherwise use input value
        const dataValue = eligibilityInput.getAttribute('data-value');
        if (dataValue) {
            eligibility = normalizeAssessmentInput(dataValue);
        } else {
            // Extract name from formatted text like "Name (Legal Basis | Level)"
            // by taking everything before the first " ("
            const parenIndex = eligibility.indexOf(' (');
            if (parenIndex > 0) {
                eligibility = normalizeAssessmentInput(eligibility.substring(0, parenIndex));
            }
        }

        initialAssessmentState.eligibility = eligibility;
        const result = await submitInitialAssessment();
        if (!result) {
            return;
        }

        closeModal('initialAssessmentEligibilityModal');
        const isPlantilla = @json($typeIsPlantilla);
        if (isPlantilla && result.requires_pqe) {
            initialAssessmentState.hasPqe = null;
            openModal('initialAssessmentPqeModal');
            return;
        }

        openModal('initialAssessmentSubscribedPdsModal');
    }

    async function answerInitialAssessmentPqe(hasPqe) {
        initialAssessmentState.hasPqe = hasPqe;
        const result = await submitInitialAssessment({ hasPqe });
        if (!result) {
            return;
        }

        closeModal('initialAssessmentPqeModal');
        openModal('initialAssessmentSubscribedPdsModal');
    }

    async function answerInitialAssessmentSubscribedPds(hasSubscribedPds) {
        initialAssessmentState.hasSubscribedPds = hasSubscribedPds;

        const submitOptions = {
            hasSubscribedPds,
        };

        if (typeof initialAssessmentState.hasPqe === 'boolean') {
            submitOptions.hasPqe = initialAssessmentState.hasPqe;
        }

        const result = await submitInitialAssessment(submitOptions);
        if (!result) {
            return;
        }

        closeModal('initialAssessmentSubscribedPdsModal');
        showInitialAssessmentDecision(
            'Qualified to Proceed',
            getContinueAssessmentPromptMessage(),
            continueAfterInitialAssessment
        );
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalIds = [
            'pdsRequiredModal',
            'requiredDocsModal',
            'docTrackMismatchModal',
            'initialAssessmentEducationModal',
            'initialAssessmentEligibilityModal',
            'initialAssessmentPqeModal',
            'initialAssessmentSubscribedPdsModal',
            'initialAssessmentFeedbackModal',
        ];
        modalIds.forEach((id) => {
            const modal = document.getElementById(id);
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                modalIds.forEach((id) => {
                    const modal = document.getElementById(id);
                    if (modal && !modal.classList.contains('hidden')) {
                        closeModal(id);
                    }
                });
            }
        });

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        bindAssessmentDropdown('degree');
        bindAssessmentDropdown('eligibility');
        const educationLevelSelect = assessmentEducationLevelEl();
        if (educationLevelSelect) {
            educationLevelSelect.addEventListener('change', () => {
                const degreeInput = document.getElementById('initialAssessmentDegreeInput');
                if (degreeInput) {
                    degreeInput.value = '';
                }
                syncInitialAssessmentEducationFieldState();
            });
        }
        syncInitialAssessmentEducationFieldState();
        document.addEventListener('click', (event) => {
            if (!(event.target instanceof Node)) {
                return;
            }
            if (!event.target.closest('[data-assessment-input]') && !event.target.closest('[data-assessment-menu]')) {
                closeAllAssessmentMenus();
            }
        });

        const showPdsRequiredModalOnLoad = @json($showPdsRequiredModalOnLoad);
        if (showPdsRequiredModalOnLoad) {
            openModal('pdsRequiredModal');
        }

        const showRequiredDocsModalOnLoad = @json($showRequiredDocsModalOnLoad);
        if (showRequiredDocsModalOnLoad) {
            openModal('requiredDocsModal');
        }

        const showMismatchModalOnLoad = @json($showMismatchModalOnLoad);
        if (showMismatchModalOnLoad) {
            openModal('docTrackMismatchModal');
        }

        window.submitApplication = openApplyModal;
        window.confirmApply = openApplyModal;
        window.openApplyConfirmationModal = openApplyModal;
        window.closeApplyConfirmationModal = function () {};
        window.addEventListener('confirm-apply-modal', function () {
            openApplyModal();
        });
    });
</script>

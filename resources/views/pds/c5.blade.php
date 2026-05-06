@extends('layout.pds_layout')
@section('title','Upload PDF')
@section('content')
@php
    $documentMeta = [
        'application_letter' => ['label' => 'Application Letter', 'accept' => 'application/pdf'],
        'pqe_result' => ['label' => 'Pre-Qualifying Exam (PQE) Result', 'accept' => 'application/pdf'],
        'transcript_records' => ['label' => 'Transcript of Records (Baccalaureate Degree)', 'accept' => 'application/pdf'],
        'photocopy_diploma' => ['label' => 'Diploma', 'accept' => 'application/pdf'],
        'signed_pds' => ['label' => 'Signed and Subscribed Personal Data Sheet', 'accept' => 'application/pdf'],
        'signed_work_exp_sheet' => ['label' => 'Signed Work Experience Sheet', 'accept' => 'application/pdf'],
        'cert_lgoo_induction' => ['label' => 'Certificate of Completion of LGOO Induction Training', 'accept' => 'application/pdf'],
        'passport_photo' => ['label' => '2" x 2" or Passport Size Picture', 'accept' => 'application/pdf,image/*'],
        'cert_eligibility' => ['label' => 'Certificate of Eligibility/Board Rating', 'accept' => 'application/pdf'],
        'ipcr' => ['label' => 'Certification of Numerical Rating/Performance Rating/IPCR', 'accept' => 'application/pdf'],
        'non_academic' => ['label' => 'Non-Academic Awards Received', 'accept' => 'application/pdf'],
        'cert_training' => ['label' => 'Certificates of Training/Participation Relevant to the Position', 'accept' => 'application/pdf'],
        'designation_order' => ['label' => 'Confirmed Designation Order/s', 'accept' => 'application/pdf'],
        'grade_masteraldoctorate' => ['label' => 'Certificate of Grades with Masteral/Doctorate Units Earned', 'accept' => 'application/pdf'],
        'tor_masteraldoctorate' => ['label' => 'TOR with Masteral/Doctorate Degree', 'accept' => 'application/pdf'],
        'cert_employment' => ['label' => 'Certificate of Employment', 'accept' => 'application/pdf'],
        'other_documents' => ['label' => 'Other Documents Submitted', 'accept' => 'application/pdf'],
    ];

    $isApplicationFlow = !empty($applicationVacancyId);
    $requiredDocsByTrack = $requiredDocsByTrack ?? ['COS' => [], 'Plantilla' => []];
    $vacancyRequiredDocumentIds = is_array($vacancyRequiredDocumentIds ?? null)
        ? array_values(array_unique($vacancyRequiredDocumentIds))
        : [];
    // In application flow, always lock UI track to vacancy track to match backend validation.
    $activeTrack = $isApplicationFlow
        ? ($defaultDocTrack ?? 'Plantilla')
        : old('doc_track', $defaultDocTrack ?? 'Plantilla');
    if (!in_array($activeTrack, ['COS', 'Plantilla'], true)) {
        $activeTrack = 'Plantilla';
    }
    $requiresFreshUpload = !empty($isFreshUpload);
    $applicationLetterPreviewUrl = $applicationLetterPreviewUrl ?? null;
@endphp
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $qualificationFeedback = session('qualification_feedback');
        @endphp
        @if(is_array($qualificationFeedback))
            @php
                $feedbackTitle = trim((string) ($qualificationFeedback['title'] ?? 'Application Requirement Check'));
                $feedbackSummary = trim((string) ($qualificationFeedback['summary'] ?? 'Please review your details before submitting.'));
                $feedbackMissing = is_array($qualificationFeedback['missing'] ?? null)
                    ? array_values(array_filter(array_map(fn($value) => trim((string) $value), $qualificationFeedback['missing'])))
                    : [];
                $feedbackNextStepUrl = trim((string) ($qualificationFeedback['next_step_url'] ?? ''));
                $feedbackNextStepLabel = trim((string) ($qualificationFeedback['next_step_label'] ?? 'Review Details'));
            @endphp
            <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-sm">
                <p class="text-sm font-bold uppercase tracking-wide">{{ $feedbackTitle }}</p>
                <p class="mt-2 text-sm">{{ $feedbackSummary }}</p>

                @if(!empty($feedbackMissing))
                    <div class="mt-3 rounded-lg border border-amber-200 bg-white/70 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Please complete</p>
                        <ul class="mt-2 list-disc list-inside text-sm text-amber-900 space-y-1">
                            @foreach($feedbackMissing as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($feedbackNextStepUrl !== '')
                    <div class="mt-3">
                        <a href="{{ $feedbackNextStepUrl }}"
                           class="use-loader inline-flex items-center rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                            {{ $feedbackNextStepLabel !== '' ? $feedbackNextStepLabel : 'Review Details' }}
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <form id="myForm" method="POST" action="{{ route('finalize_pds', ['go_to' => $isApplicationFlow ? 'job_description' : 'display_final_pds']) }}" enctype="multipart/form-data" data-upload-retry="1">
            @csrf
            <input type="hidden" name="doc_track" id="doc-track-input" value="{{ $activeTrack }}">
            @if($isApplicationFlow)
                <input type="hidden" name="vacancy_id" value="{{ $applicationVacancyId }}">
                <input type="hidden" name="redirect_vacancy_id" value="{{ $applicationVacancyId }}">
            @endif
            @if($requiresFreshUpload)
                <input type="hidden" name="fresh_upload" value="1">
            @endif

            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Supporting Documents</h2>
                </div>
                @if($isApplicationFlow)
                    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                        @if($hasFreshUploadForVacancy)
                            All required documents for this vacancy are already available from your previous uploads. Upload only files you want to update.
                        @else
                            Previously uploaded documents are detected automatically. Upload only the required documents that are still missing for this vacancy.
                        @endif
                    </div>
                @endif
                <p class="text-base font-semibold text-gray-900 mb-6">
                    Reminder: If you need to upload multiple files for a single document, please combine them into one file.
                </p>

                <p class="text-sm text-red-700 mb-4 flex items-start gap-2"> 
                    <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"> 
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd">
                        </path> 
                    </svg> 
                    <span> 
                        <span class="font-medium">Note: </span> 
                        Only PDF files are supported, except for the 2x2 photo, which can be uploaded separately. 
                    </span> 
                </p>

                <p class="text-sm text-red-700 mb-4 flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>
                        <span class="font-medium">Important:</span>
                        If your documents are marked as <span class="font-semibold">'Needs Revision'</span> again,
                        your application may be tagged as unqualified and resubmission may be closed.
                    </span>
                </p>

                <p class="text-sm text-red-700 mb-4 flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-700 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>
                        <span class="font-medium">Warning:</span>
                        Please upload only complete and truthful documents. Incorrect or misleading submissions can
                        affect your application status.
                    </span>
                </p>

                <div class="border-b border-gray-200 mb-6">
                    @if($isApplicationFlow)
                        <div class="pb-2">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-700">
                                Document Track: {{ $activeTrack }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700 ml-2">
                                Vacancy: {{ $applicationVacancyId }}
                            </span>
                        </div>
                    @else
                        <nav class="flex gap-6">
                            <button
                                id="tab-cos"
                                type="button"
                                onclick="switchDocTrack('COS')"
                                class="tab-button pb-2 font-bold text-sm uppercase tracking-wide transition-all duration-200"
                            >
                                COS
                            </button>
                            <button
                                id="tab-plantilla"
                                type="button"
                                onclick="switchDocTrack('Plantilla')"
                                class="tab-button pb-2 font-bold text-sm uppercase tracking-wide transition-all duration-200"
                            >
                                Plantilla
                            </button>
                        </nav>
                    @endif
                </div>

                <p id="doc-track-hint" class="mb-6 text-sm text-slate-600"></p>

                <div id="documents-container">
                @foreach ($documentMeta as $docType => $meta)
                    @php
                        $doc = ($documentsResolved[$docType] ?? null) ?: ($documents[$docType] ?? null);
                        $galleryDoc = $galleryDocumentsResolved[$docType] ?? null;
                        $status = trim((string) ($doc->status ?? ''));
                        $isApproved = strcasecmp($status, 'Okay/Confirmed') === 0;
                        $docStoragePath = trim((string) ($galleryDoc->storage_path ?? ''));
                        $previewUrl = '';
                        if ($docStoragePath !== '' && $docStoragePath !== 'NOINPUT') {
                            $previewUrl = \App\Support\PreviewUrl::forPath($docStoragePath);
                        }
                        $hasStoredDoc = $previewUrl !== '';
                        $hasExisting = $hasStoredDoc;
                        $isApproved = $isApproved && $hasExisting;
                        $requiredCos = $isApplicationFlow
                            ? in_array($docType, $vacancyRequiredDocumentIds, true)
                            : in_array($docType, $requiredDocsByTrack['COS'] ?? [], true);
                        $requiredPlantilla = $isApplicationFlow
                            ? in_array($docType, $vacancyRequiredDocumentIds, true)
                            : in_array($docType, $requiredDocsByTrack['Plantilla'] ?? [], true);
                        $requiredNow = $activeTrack === 'COS' ? $requiredCos : $requiredPlantilla;
                        $inputId = 'cert-upload-' . str_replace('_', '-', $docType);
                    @endphp
                    <div
                        class="doc-row w-full mb-6 border-b border-dashed border-gray-300 pb-4"
                        data-required-cos="{{ $requiredCos ? 1 : 0 }}"
                        data-required-plantilla="{{ $requiredPlantilla ? 1 : 0 }}"
                        data-order="{{ $loop->index }}"
                    >
                        <div class="flex items-center justify-between w-full gap-4">
                            <h3 class="text-gray-700 font-medium">
                                {{ $meta['label'] }}
                                <span
                                    class="doc-required-badge text-sm font-semibold {{ $requiredNow ? 'text-red-600' : 'text-blue-500' }}"
                                    data-required-cos="{{ $requiredCos ? 1 : 0 }}"
                                    data-required-plantilla="{{ $requiredPlantilla ? 1 : 0 }}"
                                    data-doc-type="{{ $docType }}"
                                >
                                    @if($docType === 'pqe_result')
                                        (if taken and passed)
                                    @else
                                        {{ $requiredNow ? '(required)' : '(if any)' }}
                                    @endif
                                </span>
                            </h3>

                            @if ($isApproved && !$requiresFreshUpload)
                                <div class="text-green-600 text-sm font-semibold">
                                    This document is already approved.
                                </div>
                            @else
                                <div class="flex items-center gap-3">
                                    @if($previewUrl !== '')
                                        <a
                                            href="{{ $previewUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100"
                                        >
                                            Preview
                                        </a>
                                    @endif
                                    @if($hasExisting)
                                        <button
                                            type="button"
                                            onclick="document.getElementById('{{ $inputId }}')?.click()"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-md hover:bg-slate-200"
                                        >
                                            Re-upload
                                        </button>
                                    @endif
                                    <label
                                        for="{{ $inputId }}"
                                        class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer"
                                    >
                                        <span class="material-icons text-5xl {{ $hasExisting ? 'text-green-500' : 'text-blue-400' }}">
                                            {{ $hasExisting ? 'check_circle' : 'cloud_upload' }}
                                        </span>
                                    </label>
                                    <input
                                        type="file"
                                        id="{{ $inputId }}"
                                        name="cert_uploads[{{ $docType }}]"
                                        accept="{{ $meta['accept'] }}"
                                        class="doc-upload-input absolute opacity-0 w-px h-px"
                                        data-has-existing="{{ $hasExisting ? 1 : 0 }}"
                                        data-required-cos="{{ $requiredCos ? 1 : 0 }}"
                                        data-required-plantilla="{{ $requiredPlantilla ? 1 : 0 }}"
                                        {{ ($requiredNow && !$hasExisting) ? 'required' : '' }}
                                    >
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            </section>

            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in mt-8">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">verified_user</span>
                    <h2 class="text-2xl font-bold text-gray-900">Declaration</h2>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-1">
                            <p class="text-sm text-yellow-800 leading-relaxed">
                                42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct and complete statement pursuant to the provisions of pertinent laws, rules and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="declaration" value="1" class="mt-1 mr-3" required {{ old('declaration') ? 'checked' : '' }}>
                        <span class="text-gray-700">
                            I certify that all information provided in this form is true and correct to the best of my knowledge.
                        </span>
                    </label>

                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="consent" value="1" class="mt-1 mr-3" required {{ old('consent') ? 'checked' : '' }}>
                        <span class="text-gray-700">
                            I consent to the collection and processing of my personal data in accordance with the Data Privacy Act of 2012.
                        </span>
                    </label>

                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="confirmation" value="1" class="mt-1 mr-3" required {{ old('confirmation') ? 'checked' : '' }}>
                        <span class="text-gray-700">
                            I confirm that all uploaded documents are correct, complete, and accurately represent the required information.
                        </span>
                    </label>
                </div>
            </section>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ $isApplicationFlow ? route('job_description', ['id' => $applicationVacancyId]) : route('display_wes', ['simple' => 1]) }}'" class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="submit" class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">check_circle</span>
                    Submit Application
                </button>
            </div>
        </form>

        <footer class="mt-12 text-center text-sm text-gray-600">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS Form No. 212 (Revised 2025)</p>
        </footer>
    </main>
@endsection

<script>
    const MAX_UPLOAD_BYTES = 10 * 1024 * 1024;

    const lockDocTrack = @json($isApplicationFlow);
    const lockedTrack = @json($activeTrack);

    function reorderDocumentRows(track) {
        const container = document.getElementById('documents-container');
        if (!container) return;

        const rows = Array.from(container.querySelectorAll('.doc-row'));
        rows.sort((a, b) => {
            const reqA = track === 'COS'
                ? a.dataset.requiredCos === '1'
                : a.dataset.requiredPlantilla === '1';
            const reqB = track === 'COS'
                ? b.dataset.requiredCos === '1'
                : b.dataset.requiredPlantilla === '1';

            if (reqA !== reqB) {
                return reqB - reqA; // required first
            }

            const orderA = Number(a.dataset.order || 0);
            const orderB = Number(b.dataset.order || 0);
            return orderA - orderB;
        });

        rows.forEach((row) => container.appendChild(row));
    }

    function switchDocTrack(track) {
        const normalized = track === 'COS' ? 'COS' : 'Plantilla';
        if (lockDocTrack && normalized !== lockedTrack) {
            return;
        }
        const hiddenInput = document.getElementById('doc-track-input');
        if (hiddenInput) hiddenInput.value = normalized;

        const cosBtn = document.getElementById('tab-cos');
        const plantillaBtn = document.getElementById('tab-plantilla');
        const activate = (btn) => {
            if (!btn) return;
            btn.classList.add('text-[#0D2B70]', 'border-b-2', 'border-[#0D2B70]');
            btn.classList.remove('text-gray-400', 'border-transparent');
        };
        const deactivate = (btn) => {
            if (!btn) return;
            btn.classList.remove('text-[#0D2B70]', 'border-b-2', 'border-[#0D2B70]');
            btn.classList.add('text-gray-400', 'border-b-2', 'border-transparent');
        };

        if (normalized === 'COS') {
            activate(cosBtn);
            deactivate(plantillaBtn);
        } else {
            activate(plantillaBtn);
            deactivate(cosBtn);
        }

        const hint = document.getElementById('doc-track-hint');
        if (hint) {
            hint.textContent = lockDocTrack
                ? `This application uses ${normalized} requirements.`
                : normalized === 'COS'
                ? 'COS requirements are active. Required documents are based on COS vacancy rules.'
                : 'Plantilla requirements are active. Some supporting documents are optional and marked as (if any).';
        }

        document.querySelectorAll('.doc-required-badge').forEach((badge) => {
            const docType = badge.dataset.docType;
            const required = normalized === 'COS'
                ? badge.dataset.requiredCos === '1'
                : badge.dataset.requiredPlantilla === '1';
            
            // Special handling for PQE
            if (docType === 'pqe_result') {
                badge.textContent = '(if taken and passed)';
                badge.classList.remove('text-red-600');
                badge.classList.add('text-blue-500');
            } else {
                badge.textContent = required ? '(required)' : '(if any)';
                badge.classList.toggle('text-red-600', required);
                badge.classList.toggle('text-blue-500', !required);
            }
        });

        document.querySelectorAll('.doc-upload-input').forEach((input) => {
            const required = normalized === 'COS'
                ? input.dataset.requiredCos === '1'
                : input.dataset.requiredPlantilla === '1';
            const hasExisting = input.dataset.hasExisting === '1';
            if (required && !hasExisting) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });

        reorderDocumentRows(normalized);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('myForm');
        const params = new URLSearchParams(window.location.search);
        const vacancyFromUrl = params.get('vacancy_id');
        if (form && vacancyFromUrl) {
            const syncVacancyFields = () => {
                const v = vacancyFromUrl.trim();
                form.querySelectorAll('input[name="vacancy_id"], input[name="redirect_vacancy_id"]').forEach((input) => {
                    input.value = v;
                });
            };
            syncVacancyFields();
            form.addEventListener('submit', syncVacancyFields, true);
        }

        const initialTrack = document.getElementById('doc-track-input')?.value || 'Plantilla';
        switchDocTrack(initialTrack);

        const updateUploadState = (input) => {
            const row = input.closest('.doc-row');
            if (!row) return;

            const label = row.querySelector('.cert-upload-area');
            const icon = label?.querySelector('.material-icons');
            if (!label || !icon) return;

            const hasExisting = input.dataset.hasExisting === '1';
            const hasSelectedFile = (input.files?.length || 0) > 0;

            if (hasSelectedFile) {
                label.classList.add('bg-green-100', 'border-green-400');
                icon.textContent = 'upload_file';
                icon.classList.remove('text-blue-400');
                icon.classList.add('text-green-500');
                return;
            }

            label.classList.remove('bg-green-100', 'border-green-400');
            icon.textContent = hasExisting ? 'check_circle' : 'cloud_upload';
            icon.classList.remove('text-green-500', 'text-blue-400');
            icon.classList.add(hasExisting ? 'text-green-500' : 'text-blue-400');
        };

        document.querySelectorAll('.doc-upload-input').forEach((input) => {
            input.addEventListener('change', function () {
                const selectedFile = input.files && input.files[0] ? input.files[0] : null;
                if (selectedFile && selectedFile.size > MAX_UPLOAD_BYTES) {
                    input.value = '';
                    const message = 'Each file must be 10MB or smaller.';
                    if (typeof showAppToast === 'function') {
                        showAppToast(message);
                    } else {
                        alert(message);
                    }
                }
                updateUploadState(input);
            });
            updateUploadState(input);
        });
    });

    function submit(location){
        const form = document.querySelector('#myForm');
        form.action = `/pds/finalize/${location}`;
        form.requestSubmit();
    }
</script>

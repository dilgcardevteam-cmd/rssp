@extends('layout.app')

@section('title', 'Job Status')

@php
  use Carbon\Carbon;

  $isPastDeadline = false;
  $isFinalRevisionDisqualified = (bool) ($isFinalRevisionDisqualified ?? false);

  if (!empty($displayDeadlineDate)) {
    $deadlineTime = $displayDeadlineTime ?? '23:59:59';
    $deadline = Carbon::parse($displayDeadlineDate . ' ' . $deadlineTime);
    $isPastDeadline = Carbon::now()->greaterThan($deadline);
  }
@endphp

@section('content')
            <div class="space-y-6">
              <div class="bg-white p-6 rounded-xl shadow-lg font-montserrat">
                <!-- Header Section -->
                <div class="flex items-center gap-4 border-b border-[#0D2B70] pb-4 mb-6">
                  <button onclick="window.location.href='{{ route('my_applications') }}'" class="use-loader group">
                    <svg xmlns="http://www.w3.org/2000/svg"
                      class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                    <span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat font-bold">
                      Application Status
                    </span>
                  </h1>
                </div>

                <!-- Session Messages -->
                @if (session('success'))
                  <div class="mb-6 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg shadow text-sm font-semibold flex items-center justify-between"
                    role="alert">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()"
                      class="text-green-800 hover:text-red-600 font-bold text-lg">&times;</button>
                  </div>
                @endif
                @if ($errors->any())
                  <div class="mb-6 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow text-sm font-semibold"
                    role="alert">
                    <ul class="list-disc list-inside">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                @if (session('comply_redirect'))
                  <div class="mb-6 px-4 py-3 bg-blue-100 border border-blue-400 text-blue-800 rounded-lg shadow text-sm font-semibold flex items-start gap-3"
                    role="alert">
                    <i data-feather="info" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                      <p class="font-bold mb-1">📋 Document Submission Required</p>
                      <p class="font-normal">Please review the document status below and upload any required or corrected documents. Make sure all documents marked for revision are updated before the deadline.</p>
                    </div>
                    <button onclick="this.parentElement.remove()"
                      class="text-blue-800 hover:text-red-600 font-bold text-lg">&times;</button>
                  </div>
                @endif

                @if($isFinalRevisionDisqualified)
                  <div class="mb-6 px-4 py-3 bg-red-100 border border-red-400 text-red-800 rounded-lg shadow text-sm font-semibold flex items-start gap-3"
                    role="alert">
                    <i data-feather="alert-octagon" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                      <p class="font-bold mb-1">Application Result</p>
                      <p class="font-normal">I am sorry to inform you that, you are not qualified for this position. No further compliance is allowed.</p>
                    </div>
                    <button onclick="this.parentElement.remove()"
                      class="text-red-800 hover:text-red-600 font-bold text-lg">&times;</button>
                  </div>
                @endif

                <!-- Applicant Header -->
                <div class="mb-6">
                  <!-- Applicant name and last modified info -->
                  <div class="flex flex-row justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-[#002C76]">
                      {{ $application->personalInformation->first_name ?? '' }}
                      @if($application->personalInformation && $application->personalInformation->middle_name)
                        {{ substr(trim($application->personalInformation->middle_name), 0, 1) . '.' }}
                      @endif
                      {{ $application->personalInformation->surname ?? '' }}
                    </h2>
                    <div class="text-xs sm:text-sm text-gray-700">
                      LAST MODIFIED:
                      @if ($adminName && $lastModifiedAt)
                        <span class="font-semibold">{{ $adminName }}</span>
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($lastModifiedAt)->format('F d, Y h:i A') }}</span>
                      @else
                        <span class="italic text-gray-500 font-semibold">Not modified yet</span>
                      @endif
                    </div>
                  </div>

                  <!-- Job Details Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                      <div class="text-xs font-semibold text-gray-700 uppercase mb-1">Position Applied:</div>
                      <div class="text-sm text-gray-900">{{ $application->vacancy->position_title }}</div>
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-gray-700 uppercase mb-1">Place of Assignment:</div>
                      <div class="text-sm text-gray-900">{{ $application->vacancy->place_of_assignment }}</div>
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-gray-700 uppercase mb-1">Compensation:</div>
                      <div class="text-sm text-gray-900">₱{{ number_format($application->vacancy->monthly_salary, 2) }}</div>
                    </div>
                  </div>

                  <!-- Main Info Cards -->
                  <div class="grid grid-cols-1 {{ $showDeadlineSubmissionCard ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-4">

                    <!-- Deadline Card -->
                    @if($showDeadlineSubmissionCard)
                      <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
                        <div class="text-sm font-semibold text-gray-700 mb-3">Deadline for Submission:</div>
                        @if($displayQsResult === 'Qualified')
                          <div class="text-sm text-green-600 font-semibold italic">Not applicable (Requirements complete)</div>
                        @elseif($displayQsResult === 'Not Qualified')
                          <div class="text-sm text-red-600 font-semibold italic">Not applicable (Application not qualified)</div>
                        @else
                          <div class="text-sm font-semibold text-[#002C76]">
                            {{ \Carbon\Carbon::parse($displayDeadlineDate . ' ' . $displayDeadlineTime)->format('F d, Y h:i A') }}
                          </div>
                          @if($isPastDeadline)
                            <div class="text-red-500 text-xs mt-2 flex items-center gap-1">
                              <i data-feather="alert-triangle" class="inline w-3 h-3"></i> Deadline Passed
                            </div>
                          @endif
                        @endif
                      </div>
                    @endif

                    <!-- Qualification Standards Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
                      <div class="flex flex-row mb-4 gap-4">
                        <div class="text-sm font-semibold text-gray-700">Qualification Standards:</div>

                        <!-- Result -->
                        <div class="flex items-center cursor-default">
                          @php
                            $resultStatus = $displayQsResult ?? 'Pending';
                            $textColor = $resultStatus === 'Qualified'
                              ? 'text-green-600'
                              : ($resultStatus === 'Needs Revisions'
                                  ? 'text-amber-600'
                                  : ($resultStatus === 'Pending' ? 'text-yellow-600' : 'text-red-600'));
                          @endphp
                          <span class="text-sm font-semibold {{ $textColor }}">{{ $resultStatus }}</span>
                        </div>
                      </div>
                      <div class="grid grid-cols-2 md:grid-cols-4 items-center gap-x-4 gap-y-2">
                        <!-- Education -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $displayQsEducation == 'yes' ? 'bg-green-500' : ($displayQsEducation == 'na' ? 'bg-gray-400' : ($displayQsEducation == 'pending' ? 'bg-yellow-400' : 'bg-red-500')) }}"></span>
                          <span class="text-xs text-gray-700">Education</span>
                        </div>

                        <!-- Eligibility -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $displayQsEligibility == 'yes' ? 'bg-green-500' : ($displayQsEligibility == 'na' ? 'bg-gray-400' : ($displayQsEligibility == 'pending' ? 'bg-yellow-400' : 'bg-red-500')) }}"></span>
                          <span class="text-xs text-gray-700">Eligibility</span>
                        </div>

                        <!-- Experience -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $displayQsExperience == 'yes' ? 'bg-green-500' : ($displayQsExperience == 'na' ? 'bg-gray-400' : ($displayQsExperience == 'pending' ? 'bg-yellow-400' : 'bg-red-500')) }}"></span>
                          <span class="text-xs text-gray-700">Experience</span>
                        </div>

                        <!-- Training -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $displayQsTraining == 'yes' ? 'bg-green-500' : ($displayQsTraining == 'na' ? 'bg-gray-400' : ($displayQsTraining == 'pending' ? 'bg-yellow-400' : 'bg-red-500')) }}"></span>
                          <span class="text-xs text-gray-700">Training</span>
                        </div>
                      </div>
                    </div>

                    <!-- Application Status Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
                      <div class="text-sm font-semibold text-gray-700 mb-3">Application Status:</div>
                      @php
                        $status = $displayApplicationStatus;
                        $badgeClasses = [
                          'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-400',
                          'Complete' => 'bg-green-100 text-green-800 border-green-400',
                          'Incomplete' => 'bg-orange-100 text-orange-800 border-orange-400',
                          'Closed' => 'bg-red-100 text-red-800 border-red-400',
                          'Cancelled' => 'bg-red-100 text-red-800 border-red-400',
                        ];
                      @endphp
                      <div class="px-4 py-2 rounded-full border font-semibold text-sm text-center {{ $badgeClasses[$status] ?? 'bg-gray-100 text-gray-800 border-gray-400' }}">
                        {{ strtoupper($status) }}
                      </div>
                    </div>
                  </div>
                </div>

                @php
                  $attendanceStatus = $application->exam_attendance_status;
                  $attendanceLabel = match ($attendanceStatus) {
                    'will_attend' => 'I Will Attend',
                    'will_not_attend' => 'I Will Not Attend',
                    default => 'No Response Yet',
                  };
                  $attendanceBadgeClass = match ($attendanceStatus) {
                    'will_attend' => 'bg-green-100 text-green-800 border-green-300',
                    'will_not_attend' => 'bg-red-100 text-red-800 border-red-300',
                    default => 'bg-gray-100 text-gray-700 border-gray-300',
                  };
                @endphp

                <div class="mb-6 rounded-xl border border-[#0D2B70]/15 bg-slate-50 p-5 shadow-sm">
                  <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Exam Attendance</p>
                      <div class="mt-2 inline-flex rounded-full border px-4 py-2 text-sm font-semibold {{ $attendanceBadgeClass }}">
                        {{ $attendanceLabel }}
                      </div>
                    </div>
                    <div class="text-sm text-slate-600 md:text-right">
                      <p class="font-semibold text-slate-700">Responded At</p>
                      <p>{{ $application->exam_attendance_responded_at ? $application->exam_attendance_responded_at->format('F d, Y h:i A') : 'Not yet submitted' }}</p>
                    </div>
                  </div>

                  @if($application->exam_attendance_remark)
                    <div class="mt-4 rounded-lg border border-slate-200 bg-white px-4 py-3">
                      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Remark</p>
                      <p class="mt-1 text-sm text-slate-700">{{ $application->exam_attendance_remark }}</p>
                    </div>
                  @endif
                </div>


                <!-- Action Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-6 mb-6">
                  <a href="{{ route('job_description', ['id' => $application->vacancy->vacancy_id]) }}" class="w-full">
                    <button
                      class="use-loader w-full border-2 border-[#002C76] text-[#002C76] rounded-lg px-4 py-2 text-sm flex items-center justify-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                      <i data-feather="eye" class="w-5 h-5"></i> View Job Description
                    </button>
                  </a>
                  <a href="{{ route('display_c1', ['simple' => 1]) }}" class="w-full">
                    <button
                      class="use-loader w-full border-2 border-[#002C76] text-[#002C76] rounded-lg px-4 py-2 text-sm flex items-center justify-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                      <i data-feather="eye" class="w-5 h-5"></i> View or Edit PDS
                    </button>
                  </a>
                </div>

                <!-- Document Section -->
                <div class="flex flex-col lg:flex-row gap-4">
                  <!-- Left Side Panel - Required Documents -->
                  <section aria-label="Required Documents Panel"
                    class="w-full lg:w-72 flex-none bg-white rounded-lg border border-gray-300 p-3 shadow-lg flex flex-col">
                    <h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide flex-none">Required Documents</h2>
                    <p class="text-xs font-semibold mb-2 text-gray-600">Documents marked <span class="text-red-600 font-bold">(required)</span> must be uploaded for this vacancy.</p>
                    <p class="text-xs font-semibold mb-3 text-gray-600">Upload your documents below. If you need to upload multiple files for a single document, please combine them into one file.</p>
                    <div class="pr-1">
                      <form id="document-upload-form" method="POST" data-upload-retry="1"
                        action="{{ route('application_status.upload', [$application->user_id, $application->vacancy_id]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input id="single-doc-upload-input" type="file" class="hidden" accept="application/pdf">
                        <ul class="text-xs text-gray-700 space-y-1" id="document-list">
                          <!-- Documents will be injected here by JS -->
                        </ul>
                      </form>
                    </div>
                  </section>

                  <!-- Right Side Panel - Document Preview -->
                  <section aria-label="Document Preview"
                    class="flex-1 bg-white rounded-xl border border-gray-300 shadow-lg p-6 flex flex-col min-w-0 min-h-[600px]">

                    <!-- Document Header -->
                    <div class="mb-4 w-full pb-2 border-b border-gray-400 flex-none">
                      <h2 id="document-title" class="text-xl md:text-2xl font-bold text-[#002C76] mb-2">Select a Document</h2>
                      <p class="text-sm text-gray-600">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Status:</span>
                        <span id="document-status-text" class="font-semibold">Pending</span>
                      </p>
                      <p id="document-modified" class="text-xs text-gray-500 hidden">
                        Last modified by <span class="font-semibold text-gray-700"></span>
                      </p>
                    </div>

                    <!-- Document Remarks Box -->
                    <div id="document-remarks-section" class="mb-4 w-full hidden flex-none">
                      <div class="flex items-center justify-between mb-2">
                        <label for="remarks" class="block text-sm font-semibold text-[#002C76]">
                          Document Remarks:
                          <span id="remarks-status"
                            class="text-green-600 text-xs ml-2 opacity-0 transition-opacity duration-500">Saved</span>
                        </label>
                        <button id="upload-new-document-btn" type="button"
                          class="hidden use-loader inline-flex items-center gap-2 rounded-lg bg-[#002C76] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0D2B70] transition">
                          <i data-feather="upload" class="w-4 h-4"></i>
                          Upload New Document
                        </button>
                      </div>
                      <textarea id="remarks" rows="3"
                        class="w-full text-sm text-gray-700 rounded-lg p-3 resize-none border border-[#002C76] focus:border-[#0066CC] focus:ring-2 focus:ring-blue-200 transition bg-gray-50"
                        placeholder="Remarks for this document..." readonly></textarea>
                    </div>

                    <!-- Preview Frame -->
                    <div class="flex-1 bg-gray-50 rounded-xl border border-[#002C76] p-0 overflow-hidden relative">
                      <div id="preview-loader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10 hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#002C76]"></div>
                      </div>
                      <iframe id="doc-preview" src="about:blank" title="Document Preview"
                        class="w-full h-full rounded-md flex-grow border-0 bg-white"
                        loading="lazy" scrolling="no"></iframe>
                    </div>
                  </section>
                </div>

                <!-- Application Remarks -->
                @php
                  $isQualified = strtolower($displayQsResult ?? '') === 'qualified'
                    || strtolower($displayApplicationStatus ?? '') === 'qualified';
                @endphp
                @if(!$isQualified)
                  <div class="grid grid-cols-1 gap-4 mt-6">
                    <div class="bg-white rounded-lg p-4 border border-gray-300">
                      <div class="font-bold text-gray-800 mb-2">APPLICATION REMARKS</div>
                      <p class="text-sm text-gray-700">
                        {{ $displayApplicationRemarks ?: 'No remarks at this time.' }}
                      </p>
                    </div>
                  </div>
                @endif


        <script>
          const MAX_UPLOAD_BYTES = 10 * 1024 * 1024;
          let documents = @json($documents);
          const requiredDocumentIds = @json($requiredDocumentIds ?? []);
          let requiredDocumentSet = new Set(requiredDocumentIds);
          let isPastDeadline = @json($isPastDeadline);
          let isFinalRevisionDisqualified = @json($isFinalRevisionDisqualified ?? false);
          let currentSelectedDoc = null;

          function isRequiredDocument(docId) {
            return requiredDocumentSet.has(docId);
          }

          function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
          }

          function getDocumentLabelHtml(doc) {
            const label = escapeHtml(doc?.text || doc?.name || '');
            const docId = doc?.id || '';
            let suffix = '<span class="text-blue-500 font-semibold ml-1">(if any)</span>';

            if (docId === 'pqe_result') {
              suffix = '<span class="text-blue-500 font-semibold ml-1">(if taken and passed)</span>';
            } else if (isRequiredDocument(docId)) {
              suffix = '<span class="text-red-600 font-semibold ml-1">(required)</span>';
            }

            return `${label} ${suffix}`;
          }

          function sortDocumentsForRequiredPriority(docList) {
            return [...(docList || [])].sort((a, b) => {
              const requiredA = isRequiredDocument(a?.id) ? 0 : 1;
              const requiredB = isRequiredDocument(b?.id) ? 0 : 1;
              if (requiredA !== requiredB) {
                return requiredA - requiredB; // required first, optional last
              }

              const labelA = (a?.text || a?.name || a?.id || '').toLowerCase();
              const labelB = (b?.text || b?.name || b?.id || '').toLowerCase();
              return labelA.localeCompare(labelB);
            });
          }

          // Status icon helper
          function getStatusIcon(status) {
            if (status === "Verified" || status === "Okay/Confirmed") {
              return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
            } else if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
              return `<svg class="w-4 h-4 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
            } else if (status === "Not Submitted") {
              return `<span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>`;
            }
            return `<span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>`;
          }

          // Function to update document preview
          function isRevisionStatus(status) {
            return status === "Needs Revision" || status === "Disapproved With Deficiency";
          }

          function updateUploadNewButton(doc) {
            const btn = document.getElementById('upload-new-document-btn');
            if (!btn) return;

            const shouldShow = !!doc && isRevisionStatus(doc.status) && !isFinalRevisionDisqualified && !isPastDeadline;
            btn.classList.toggle('hidden', !shouldShow);
            btn.disabled = !shouldShow;
          }

          function handleDocumentClick(doc) {
            if (currentSelectedDoc && currentSelectedDoc.id === doc.id) return;
            
            currentSelectedDoc = doc;

            // Highlight active item
            const allButtons = document.querySelectorAll('#document-list button');
            allButtons.forEach(b => {
              b.classList.remove("bg-blue-50", "ring-1", "ring-blue-200");
            });
            const activeLi = document.getElementById(`doc-item-${doc.id}`);
            if(activeLi) {
              const activeBtn = activeLi.querySelector('button');
              if(activeBtn) activeBtn.classList.add("bg-blue-50", "ring-1", "ring-blue-200");
            }

            // Update header
            document.getElementById('document-title').textContent = doc.text || doc.name;
            
            const statusText = document.getElementById('document-status-text');
            if (statusText) {
              const status = doc.status || 'Pending';
              statusText.textContent = status;
              statusText.className = 'font-semibold ';
              if (status === "Verified" || status === "Okay/Confirmed") {
                statusText.classList.add("text-[#00730A]");
              } else if (status === "Needs Revision" || status === "Disapproved With Deficiency")  {
                statusText.classList.add("text-[#BC0000]");
              } else if (status === "Not Submitted") {
                statusText.classList.add("text-gray-500");
              } else {
                statusText.classList.add("text-orange-600");
              }
            }

            const modifiedEl = document.getElementById('document-modified');
            if (modifiedEl) {
              const modifiedSpan = modifiedEl.querySelector('span');
              if (doc.last_modified_by) {
                modifiedEl.classList.remove('hidden');
                if (modifiedSpan) modifiedSpan.textContent = doc.last_modified_by;
              } else {
                modifiedEl.classList.add('hidden');
                if (modifiedSpan) modifiedSpan.textContent = '';
              }
            }

            // Update remarks
            const remarksEl = document.getElementById('remarks');
            const remarksSection = document.getElementById('document-remarks-section');
            
            if (remarksEl) {
              remarksEl.value = doc.remarks || "";
            }
            
            if (doc.status === "Needs Revision" || doc.status === "Disapproved With Deficiency") {
              if (remarksSection) remarksSection.classList.remove('hidden');
            } else {
              if (remarksSection) remarksSection.classList.add('hidden');
            }

            updateUploadNewButton(doc);

            // Load preview
            const previewLoader = document.getElementById('preview-loader');
            const docPreview = document.getElementById('doc-preview');
            
            if (previewLoader) previewLoader.classList.remove('hidden');
            
            setTimeout(() => {
              if (docPreview) {
                docPreview.onload = () => {
                  if (previewLoader) previewLoader.classList.add('hidden');
                };
                let previewUrl = doc.preview || "about:blank";
                if (previewUrl && previewUrl !== "about:blank" && /\.pdf(\?|#|$)/i.test(previewUrl)) {
                  const hashSeparator = previewUrl.includes('#') ? '&' : '#';
                  previewUrl = `${previewUrl}${hashSeparator}toolbar=0&navpanes=0&scrollbar=0&view=FitH`;
                }
                docPreview.src = previewUrl;
              }
            }, 10);
          }

          // Render documents list
          function renderDocuments(docList) {
            const listEl = document.getElementById('document-list');
            if (!listEl) return;
            listEl.innerHTML = "";

            docList.forEach(doc => {
              const li = document.createElement('li');
              li.id = `doc-item-${doc.id}`;
              li.className = "mb-1";

              const btn = document.createElement('button');
              btn.type = "button";
              btn.className = "w-full text-left p-2 rounded-md hover:bg-gray-100 flex items-start gap-2 transition-colors duration-150 border border-transparent focus:outline-none focus:ring-2 focus:ring-blue-200";

              const status = doc.status || 'Pending';
              let icon = getStatusIcon(status);
              let textColorClass = "text-gray-700";
              if (status === "Verified" || status === "Okay/Confirmed") {
                textColorClass = "text-[#00730A] font-bold";
              } else if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
                textColorClass = "text-[#BC0000] font-bold";
              } else if (status === "Not Submitted") {
                textColorClass = "text-gray-400";
              } else {
                textColorClass = "text-orange-500 font-medium";
              }

              const iconWrapper = document.createElement('span');
              iconWrapper.className = "mt-0.5 flex-shrink-0 w-4 h-4 flex items-center justify-center";
              iconWrapper.innerHTML = icon;

              const textWrapper = document.createElement('span');
              textWrapper.className = `${textColorClass} text-xs flex-1 break-words`;
              textWrapper.innerHTML = getDocumentLabelHtml(doc);

              btn.appendChild(iconWrapper);
              btn.appendChild(textWrapper);

              btn.onclick = function(e) {
                e.preventDefault();
                handleDocumentClick(doc);
              };

              li.appendChild(btn);
              listEl.appendChild(li);
            });
          }

          // Initialize
          document.addEventListener('DOMContentLoaded', function() {
            const cancelModal = document.getElementById('cancel-application-modal');
            const cancelModalOpenBtn = document.getElementById('open-cancel-modal-btn');
            const cancelModalCloseBtn = document.getElementById('cancel-application-modal-close-btn');
            const cancelModalConfirmBtn = document.getElementById('cancel-application-modal-confirm-btn');
            const cancelModalBackdrop = document.getElementById('cancel-application-modal-backdrop');
            const cancelApplicationForm = document.getElementById('cancel-application-form');

            const openCancelModal = () => {
              if (!cancelModal) return;
              cancelModal.classList.remove('hidden');
              cancelModal.setAttribute('aria-hidden', 'false');
              cancelModalConfirmBtn?.focus();
            };

            const closeCancelModal = () => {
              if (!cancelModal) return;
              cancelModal.classList.add('hidden');
              cancelModal.setAttribute('aria-hidden', 'true');
              cancelModalOpenBtn?.focus();
            };

            cancelModalOpenBtn?.addEventListener('click', openCancelModal);
            cancelModalCloseBtn?.addEventListener('click', closeCancelModal);
            cancelModalBackdrop?.addEventListener('click', closeCancelModal);
            cancelModalConfirmBtn?.addEventListener('click', function () {
              if (!cancelApplicationForm) return;
              cancelApplicationForm.submit();
            });

            document.addEventListener('keydown', function (event) {
              if (event.key !== 'Escape') return;
              if (!cancelModal || cancelModal.classList.contains('hidden')) return;
              closeCancelModal();
            });

            console.log("Documents from backend:", documents);
            documents = sortDocumentsForRequiredPriority(documents);
            renderDocuments(documents);
            updateUploadNewButton(null);

            const uploadButton = document.getElementById('upload-new-document-btn');
            const uploadInput = document.getElementById('single-doc-upload-input');
            const uploadForm = document.getElementById('document-upload-form');

            uploadButton?.addEventListener('click', function () {
              if (isFinalRevisionDisqualified || isPastDeadline) return;
              if (!currentSelectedDoc || !isRevisionStatus(currentSelectedDoc.status)) return;
              if (!uploadInput) return;

              uploadInput.name = `cert_uploads[${currentSelectedDoc.id}]`;
              uploadInput.value = '';
              uploadInput.click();
            });

            uploadInput?.addEventListener('change', function () {
              if (isFinalRevisionDisqualified || isPastDeadline) return;
              if (!uploadInput.files || uploadInput.files.length === 0) return;

              const selectedFile = uploadInput.files[0];
              if (selectedFile && selectedFile.size > MAX_UPLOAD_BYTES) {
                uploadInput.value = '';
                const message = 'Each file must be 10MB or smaller.';
                if (typeof showAppToast === 'function') {
                  showAppToast(message);
                } else {
                  alert(message);
                }
                return;
              }

              if (!currentSelectedDoc || !isRevisionStatus(currentSelectedDoc.status)) return;
              if (!uploadForm) return;

              uploadInput.name = `cert_uploads[${currentSelectedDoc.id}]`;
              uploadForm.submit();
            });
            
            // Force immediate refresh on page load
            setTimeout(async function() {
                console.log('Forcing immediate refresh...');
                try {
                    const response = await fetch(`/application_status/{{ $user_id }}/{{ $vacancy_id }}/documents`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    
                    console.log('Immediate refresh - Response status:', response.status);
                    console.log('Immediate refresh - Response ok:', response.ok);
                    
                    if (response.ok) {
                        const data = await response.json();
                        console.log('Immediate refresh - Updated documents:', data);
                        console.log('Document details:');
                        data.documents?.forEach((doc, index) => {
                            console.log(`Doc ${index}: ${doc.name} - Status: "${doc.status}" - ID: ${doc.id}`);
                        });

                        if (Array.isArray(data.requiredDocumentIds)) {
                            requiredDocumentSet = new Set(data.requiredDocumentIds);
                        }
                        if (data.application && Object.prototype.hasOwnProperty.call(data.application, 'final_revision_disqualified')) {
                            isFinalRevisionDisqualified = !!data.application.final_revision_disqualified;
                        }
                        if (data.application && Object.prototype.hasOwnProperty.call(data.application, 'is_past_deadline')) {
                            isPastDeadline = !!data.application.is_past_deadline;
                        }

                        // Update documents array (required first, optional last)
                        documents = sortDocumentsForRequiredPriority(data.documents || documents);

                        // Re-render documents with new sorted data
                        renderDocuments(documents);
                        updateUploadNewButton(currentSelectedDoc);
                    }
                } catch (error) {
                    console.error('Error in immediate refresh:', error);
                }
            }, 1000); // Force refresh after 1 second
            
            // Auto-refresh documents every 5 seconds
            let refreshCount = 0;
            setInterval(async function() {
                refreshCount++;
                console.log(`Auto-refresh attempt #${refreshCount}`);
                try {
                    console.log('Fetching documents from:', `/application_status/{{ $user_id }}/{{ $vacancy_id }}/documents`);
                    const response = await fetch(`/application_status/{{ $user_id }}/{{ $vacancy_id }}/documents`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    
                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);
                    
                    if (response.ok) {
                        const data = await response.json();
                        console.log('Updated documents:', data);
                        console.log('Document details:');
                        data.documents?.forEach((doc, index) => {
                            console.log(`Doc ${index}: ${doc.name} - Status: "${doc.status}" - ID: ${doc.id}`);
                        });

                        if (Array.isArray(data.requiredDocumentIds)) {
                            requiredDocumentSet = new Set(data.requiredDocumentIds);
                        }
                        if (data.application && Object.prototype.hasOwnProperty.call(data.application, 'final_revision_disqualified')) {
                            isFinalRevisionDisqualified = !!data.application.final_revision_disqualified;
                        }
                        if (data.application && Object.prototype.hasOwnProperty.call(data.application, 'is_past_deadline')) {
                            isPastDeadline = !!data.application.is_past_deadline;
                        }

                        // Update documents array (required first, optional last)
                        documents = sortDocumentsForRequiredPriority(data.documents || documents);

                        // Re-render documents with new sorted data
                        renderDocuments(documents);
                        updateUploadNewButton(currentSelectedDoc);
                    } else {
                        console.error('Response not ok:', response.statusText);
                    }
                } catch (error) {
                    console.error('Error fetching documents:', error);
                }
            }, 5000); // Check every 5 seconds

            // Auto-save remarks
            let remarksTimeout;
            const remarksEl = document.getElementById('remarks');
            if (remarksEl) {
              remarksEl.addEventListener('input', function() {
                if (!currentSelectedDoc) return;

                const value = this.value;
                currentSelectedDoc.remarks = value;

                const statusEl = document.getElementById('remarks-status');
                if (statusEl) {
                  statusEl.classList.remove('opacity-100');
                  statusEl.classList.add('opacity-0');
                }

                clearTimeout(remarksTimeout);
                remarksTimeout = setTimeout(() => {
                  if (statusEl) {
                    statusEl.classList.remove('opacity-0');
                    statusEl.classList.add('opacity-100');
                    setTimeout(() => {
                      statusEl.classList.remove('opacity-100');
                      statusEl.classList.add('opacity-0');
                    }, 2000);
                  }
                }, 1000);
              });
            }
          });
        </script>
            </div>
            @include('partials.loader')
@endsection

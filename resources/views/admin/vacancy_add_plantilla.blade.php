@extends('layout.admin')
@section('title', 'Job Details - Plantilla Position')
@section('main-padding', 'px-3 sm:px-4 md:px-5')
@section('content')

  @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative z-50" role="alert">
      <strong class="font-bold">Success!</strong>
      <span class="block sm:inline">{{ session('success') }}</span>
    </div>
  @endif

  @if (session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative z-50" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ session('error') }}</span>
    </div>
  @endif

  @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative z-50" role="alert">
      <strong class="font-bold">Caution!</strong> There were some problems with your input.
      <ul class="mt-2 list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <main
    class="w-full max-w-full min-h-screen overflow-x-hidden rounded-2xl bg-slate-100 p-2 font-montserrat md:p-3 lg:p-4">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @php
      $formSource = $vacancy ?? ($templateVacancy ?? null);
      $isCreateMode = !isset($vacancy);
      $positionMode = (bool) ($positionMode ?? false);
      $disablePositionFields = $positionMode && $isCreateMode;
      $defaultSignatory = $signatories->first();
      $defaultToPerson = old(
        'to_person',
        $formSource?->to_person
        ?? ($defaultSignatory ? trim(($defaultSignatory->first_name ?? '') . ' ' . ($defaultSignatory->middle_name ?? '') . ' ' . ($defaultSignatory->last_name ?? '')) : '')
      );
      $defaultToPosition = old('to_position', $formSource?->to_position ?? ($defaultSignatory->designation ?? ''));
      $defaultToOffice = old('to_office', $formSource?->to_office ?? ($defaultSignatory->office ?? ''));
      $defaultToOfficeAddress = old('to_office_address', $formSource?->to_office_address ?? ($defaultSignatory->office_address ?? ''));
      $defaultClosingDate = old(
        'closing_date',
        (!$isCreateMode && isset($formSource) && !empty($formSource->closing_date))
        ? \Carbon\Carbon::parse($formSource->closing_date)->format('Y-m-d')
        : (
          $disablePositionFields
          ? (
            isset($formSource) && !empty($formSource->closing_date)
            ? \Carbon\Carbon::parse($formSource->closing_date)->format('Y-m-d')
            : now()->format('Y-m-d')
          )
          : ''
        )
      );
      $displayToPerson = $disablePositionFields ? '' : $defaultToPerson;
      $displayToPosition = $disablePositionFields ? '' : $defaultToPosition;
      $displayToOffice = $disablePositionFields ? '' : $defaultToOffice;
      $displayToOfficeAddress = $disablePositionFields ? '' : $defaultToOfficeAddress;
      $displayClosingDate = $disablePositionFields ? '' : $defaultClosingDate;
      $sectionTitle = 'text-lg font-semibold text-slate-900';
      $fieldLabel = 'mb-2 block text-sm font-medium text-slate-700';
      $fieldInput = 'h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100';
      $fieldTextarea = 'min-h-[108px] w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100';
      $allSupportingDocumentTypes = collect(\App\Models\UploadedDocument::DOCUMENTS)
        ->reject(fn($doc) => $doc === 'isApproved')
        ->values()
        ->all();
      $defaultRequiredSupportingDocuments = strtoupper((string) old('vacancy_type', $formSource?->vacancy_type ?? 'Plantilla')) === 'COS'
        ? ['passport_photo', 'signed_pds', 'signed_work_exp_sheet', 'photocopy_diploma', 'application_letter', 'cert_training']
        : collect($allSupportingDocumentTypes)
          ->reject(fn($doc) => in_array($doc, ['tor_masteraldoctorate', 'grade_masteraldoctorate', 'cert_lgoo_induction', 'other_documents', 'pqe_result', 'ipcr', 'non_academic', 'designation_order', 'cert_employment'], true))
          ->values()
          ->all();
      $persistedSupportingDocumentSelection = old('supporting_documents_required', $formSource?->supporting_documents_required);
      if (is_string($persistedSupportingDocumentSelection)) {
        $decodedSelection = json_decode($persistedSupportingDocumentSelection, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          $persistedSupportingDocumentSelection = $decodedSelection;
        }
      }
      $selectedSupportingDocuments = is_array($persistedSupportingDocumentSelection)
        ? array_values(array_unique(array_values(array_filter($persistedSupportingDocumentSelection, fn($doc) => in_array((string) $doc, $allSupportingDocumentTypes, true)))))
        : $defaultRequiredSupportingDocuments;
      $documentLabelMap = [
        'application_letter' => 'Application Letter',
        'pqe_result' => 'Pre-Qualifying Exam (PQE) Result',
        'transcript_records' => 'Transcript of Records (Baccalaureate Degree)',
        'photocopy_diploma' => 'Diploma',
        'signed_pds' => 'Signed and Subscribed Personal Data Sheet',
        'signed_work_exp_sheet' => 'Signed Work Experience Sheet',
        'cert_lgoo_induction' => 'Certificate of Completion of LGOO Induction Training',
        'passport_photo' => '2" x 2" or Passport Size Picture',
        'cert_eligibility' => 'Certificate of Eligibility/Board Rating',
        'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR',
        'non_academic' => 'Non-Academic Awards Received',
        'cert_training' => 'Certificates of Training/Participation',
        'designation_order' => 'Confirmed Designation Order/s',
        'grade_masteraldoctorate' => 'Certificate of Grades with Masteral/Doctorate Units Earned',
        'tor_masteraldoctorate' => 'TOR with Masteral/Doctorate Degree',
        'cert_employment' => 'Certificate of Employment',
        'other_documents' => 'Other Documents Submitted',
      ];
    @endphp

    <div class="w-full min-w-0">
      <div class="mb-6">
        <div>
          <button type="button" onclick="handleBack()"
            class="mb-4 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
            <span
              class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-600">&larr;</span>
            <span>Go back</span>
          </button>

          <section class="flex-none flex items-center space-x-4 max-w-full">
            <h1
              class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
              <span class="whitespace-nowrap text-[#0D2B70]">Plantilla Position</span>
            </h1>
          </section>

          <p class="mt-2 text-sm text-slate-600">
            Complete the details below to create or update this job posting.
          </p>
        </div>
      </div>

      @if(!isset($vacancy) && isset($templateVacancy))
        <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-slate-700">
          Reusing details from vacancy <span class="font-semibold">{{ $templateVacancy->vacancy_id }}</span>.
        </div>
      @endif

      <form id="plantillaForm"
        action="{{ isset($vacancy) ? route('plantilla.update', $vacancy->vacancy_id) : route('plantilla.store') }}"
        method="POST" enctype="multipart/form-data" class="w-full max-w-full min-w-0 space-y-6 overflow-x-hidden">
        @csrf
        @if(isset($vacancy))
          @method('PUT')
        @endif

        <input type="hidden" name="vacancy_type" value="Plantilla">
        <input type="hidden" name="position_mode" value="{{ $positionMode ? '1' : '0' }}">
        <input type="hidden" name="position_title_id"
          value="{{ old('position_title_id', $positionMode ? ($formSource?->id ?? '') : '') }}">
        <input type="hidden" id="supporting_documents_required" name="supporting_documents_required"
          value='@json($selectedSupportingDocuments)'>

        <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-6 border-b border-slate-200 pb-5">
            <h2 class="{{ $sectionTitle }}">Job Information</h2>
            <p class="mt-1 text-sm text-slate-600">
              Enter the core details of the position and where it will be assigned.
            </p>
          </div>

          <div class="space-y-5">
            <div>
              <label class="{{ $fieldLabel }}">Position Title <span class="text-red-600">*</span></label>
              @if($disablePositionFields)
                <input id="position_title_select" type="text" name="position_title" required
                  value="{{ old('position_title', $formSource?->position_title ?? '') }}" class="{{ $fieldInput }}"
                  placeholder="Enter position title">
              @else
                <select id="position_title_select" name="position_title" required class="{{ $fieldInput }}">
                  <option value="">-- Select Position Title --</option>
                </select>
              @endif
              <p id="position_title_error" class="mt-1 hidden text-sm text-red-600">Position title is required.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="{{ $fieldLabel }}">Salary Grade <span class="text-red-600">*</span></label>
                <input id="salary_grade" required type="text" name="salary_grade"
                  value="{{ old('salary_grade', $formSource?->salary_grade ?? '') }}" class="{{ $fieldInput }}">
                <p id="salary_grade_error" class="mt-1 hidden text-sm text-red-600">Salary grade must be in SG-00 format
                  (example: SG-23).</p>
              </div>
              <div>
                <label class="{{ $fieldLabel }}">Monthly Salary <span class="text-red-600">*</span></label>
                <div class="relative">
                  <span
                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">PHP</span>
                  <input id="monthly_salary_display" type="text" inputmode="decimal"
                    value="{{ old('monthly_salary', $formSource?->monthly_salary ?? '') }}"
                    class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-14 pr-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                  <input id="monthly_salary" required type="hidden" name="monthly_salary"
                    value="{{ old('monthly_salary', $formSource?->monthly_salary ?? '') }}">
                </div>
                <p id="monthly_salary_error" class="mt-1 hidden text-sm text-red-600"></p>
              </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="{{ $fieldLabel }}">Deadline of Application <span class="text-red-600">*</span></label>
                <input id="closing_date" type="date" name="closing_date" value="{{ $displayClosingDate }}"
                  placeholder="Select deadline" {{ $disablePositionFields ? 'disabled' : '' }} class="{{ $fieldInput }}">
                @if($disablePositionFields)
                  <input type="hidden" name="closing_date" value="{{ $defaultClosingDate }}">
                @endif
                <p id="closing_date_error" class="mt-1 hidden text-sm text-red-600">Deadline of application is required.
                </p>
                @if($disablePositionFields)
                  <p class="mt-1 text-xs leading-5 text-slate-500">Deadline is managed in Add Vacancy.</p>
                @endif
              </div>

              <div>
                <label class="{{ $fieldLabel }}">Place of Assignment <span class="text-red-600">*</span></label>
                @php
                  $placeOptions = ['DILG-CAR', 'DILG-CAR Regional Office', 'Apayao Provincial Office', 'Abra Provincial Office', 'Mountain Province Provincial Office', 'Ifugao Provincial Office', 'Kalinga Provincial Office', 'Benguet Provincial Office', 'Baguio City Office'];
                  $selectedPlace = trim((string) old('place_of_assignment', $formSource?->place_of_assignment ?? ''));
                  $selectedPlaceLower = strtolower($selectedPlace);
                  $hasSelectedPlace = collect($placeOptions)->contains(
                    fn($place) => strtolower(trim((string) $place)) === $selectedPlaceLower
                  );
                @endphp
                <select id="place_of_assignment" name="place_of_assignment" required class="{{ $fieldInput }}">
                  <option value="" disabled {{ $selectedPlace === '' ? 'selected' : '' }}>Place of Assignment</option>
                  @if($selectedPlace !== '' && !$hasSelectedPlace)
                    <option value="{{ $selectedPlace }}" selected>{{ $selectedPlace }}</option>
                  @endif
                  @foreach ($placeOptions as $office)
                    <option value="{{ $office }}" {{ strtolower(trim((string) $office)) === $selectedPlaceLower ? 'selected' : '' }}>{{ $office }}</option>
                  @endforeach
                </select>
                <p id="place_of_assignment_error" class="mt-1 hidden text-sm text-red-600">Place of assignment is
                  required.</p>
              </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="{{ $fieldLabel }}">Position Control Number</label>
                  <input type="text" id="pcn_no" name="pcn_no" value="{{ old('pcn_no', $formSource?->pcn_no ?? '') }}"
                    class="{{ $fieldInput }}" autocomplete="off">
              </div>
              <div>
                <label class="{{ $fieldLabel }}">Plantilla Item No.</label>
                <input type="text" name="plantilla_item_no"
                  value="{{ old('plantilla_item_no', $formSource?->plantilla_item_no ?? '') }}" class="{{ $fieldInput }}">
              </div>
            </div>
          </div>
        </section>

        <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-6 border-b border-slate-200 pb-5">
            <h2 class="{{ $sectionTitle }}">Qualification Standards</h2>
            <p class="mt-1 text-sm text-slate-600">
              Define the education, training, experience, and eligibility requirements.
            </p>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            @include('admin.partials.qualification_education_builder')
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
              <label class="{{ $fieldLabel }}">Training <span class="text-red-600">*</span></label>
              <textarea name="qualification_training"
                class="{{ $fieldTextarea }}">{{ old('qualification_training', $formSource?->qualification_training ?? '') }}</textarea>
            </div>
          </div>

          <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="{{ $fieldLabel }}">Experience <span class="text-red-600">*</span></label>
            <textarea name="qualification_experience"
              class="{{ $fieldTextarea }}">{{ old('qualification_experience', $formSource?->qualification_experience ?? '') }}</textarea>
          </div>

          <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="{{ $fieldLabel }}">Eligibility <span class="text-red-600">*</span></label>
            <input type="hidden" id="qualification_eligibility_hidden" name="qualification_eligibility"
              value="{{ old('qualification_eligibility', $formSource?->qualification_eligibility ?? '') }}">

            <div id="eligibility-list" class="space-y-3"></div>

            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-4">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Select Eligibility
              </label>

              <div class="grid gap-3">
                <select id="eligibility-select" class="{{ $fieldInput }}">
                  <option value="">Select eligibility from the official list</option>
                </select>
              </div>

              <button id="eligibility-add-custom-btn" type="button"
                class="mt-3 inline-flex h-9 items-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 hover:bg-slate-100">
                Add Others
              </button>

              <div id="eligibility-custom-editor" class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Others Eligibility Details
                </p>
                <div class="grid gap-3 md:grid-cols-3">
                  <input id="eligibility-custom-name" type="text" placeholder="Eligibility Name"
                    class="{{ $fieldInput }}">
                  <input id="eligibility-custom-legal" type="text" placeholder="Legal Basis" class="{{ $fieldInput }}">
                  <input id="eligibility-custom-level" type="text" placeholder="Level (First or Second Level)"
                    class="{{ $fieldInput }}">
                </div>
                <div class="mt-3 flex gap-2">
                  <button id="eligibility-custom-save" type="button"
                    class="inline-flex h-9 items-center rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white hover:bg-slate-800">
                    Add Others
                  </button>
                  <button id="eligibility-custom-cancel" type="button"
                    class="inline-flex h-9 items-center rounded-lg border border-slate-300 px-3 text-xs font-medium text-slate-700 hover:bg-slate-100">
                    Cancel
                  </button>
                </div>
              </div>

              <p id="eligibility_add_error" class="mt-2 hidden text-xs text-red-600"></p>
              <p class="mt-1 text-xs leading-5 text-slate-500">
                Choose from the official list to add it immediately. If not listed, click Add Others.
              </p>
            </div>

            <p id="qualification_eligibility_error" class="mt-2 hidden text-sm text-red-600">
              At least one eligibility is required.
            </p>
          </div>
        </section>

        <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-6 border-b border-slate-200 pb-5">
            <h2 class="{{ $sectionTitle }}">Competencies</h2>
            <p class="mt-1 text-sm text-slate-600">
              List key competencies expected from applicants.
            </p>
          </div>
          <textarea name="competencies" rows="4"
            class="{{ $fieldTextarea }}">{{ old('competencies', $formSource?->competencies ?? '') }}</textarea>
        </section>

        <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-6 border-b border-slate-200 pb-5">
            <h2 class="{{ $sectionTitle }}">Supporting Documents</h2>
            <p class="mt-1 text-sm text-slate-600">
              Mark documents as required for this vacancy. Unchecked documents are optional (if any).
            </p>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            @foreach($allSupportingDocumentTypes as $supportingDocType)
              @php
                $supportingDocKey = (string) $supportingDocType;
                $isChecked = in_array($supportingDocKey, $selectedSupportingDocuments, true);
                $documentLabel = $documentLabelMap[$supportingDocKey] ?? ucwords(str_replace('_', ' ', $supportingDocKey));
              @endphp
              <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <input type="checkbox" value="{{ $supportingDocKey }}"
                  class="supporting-document-checkbox mt-1 h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600"
                  {{ $isChecked ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">{{ $documentLabel }}</span>
              </label>
            @endforeach
          </div>
        </section>

        @if($disablePositionFields)
          <input type="hidden" name="to_person" value="{{ $defaultToPerson }}">
          <input type="hidden" name="to_position" value="{{ $defaultToPosition }}">
          <input type="hidden" name="to_office" value="{{ $defaultToOffice }}">
          <input type="hidden" name="to_office_address" value="{{ $defaultToOfficeAddress }}">
        @else
          <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 border-b border-slate-200 pb-5">
              <h2 class="{{ $sectionTitle }}">Application Submission Details</h2>
              <p class="mt-1 text-sm text-slate-600">
                Provide the receiving office and contact person for applications.
              </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="{{ $fieldLabel }}">Name of Head <span class="text-red-600">*</span></label>
                <select id="signatory_select" name="to_person" class="{{ $fieldInput }}">
                  <option value="">-- Select Regional Director --</option>
                  @forelse($signatories as $signatory)
                    <option value="{{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}"
                      data-designation="{{ $signatory->designation }}" data-office="{{ $signatory->office }}"
                      data-office_address="{{ $signatory->office_address }}" {{ $displayToPerson === ($signatory->first_name . ' ' . $signatory->middle_name . ' ' . $signatory->last_name) || (!$disablePositionFields && count($signatories) === 1 && $displayToPerson === '') ? 'selected' : '' }}>
                      {{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}
                    </option>
                  @empty
                    <option value="">No Regional Director configured</option>
                  @endforelse
                </select>
              </div>

              <div>
                <label class="{{ $fieldLabel }}">Office <span class="text-red-600">*</span></label>
                <input type="text" id="to_office" name="to_office" value="{{ $displayToOffice }}" class="{{ $fieldInput }}">
              </div>

              <div>
                <label class="{{ $fieldLabel }}">Designation <span class="text-red-600">*</span></label>
                <input type="text" id="to_position" name="to_position" value="{{ $displayToPosition }}"
                  class="{{ $fieldInput }}">
              </div>

              <div>
                <label class="{{ $fieldLabel }}">Office Address <span class="text-red-600">*</span></label>
                <input type="text" id="to_office_address" name="to_office_address" value="{{ $displayToOfficeAddress }}"
                  class="{{ $fieldInput }}">
              </div>
            </div>
          </section>
        @endif

        <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-6 border-b border-slate-200 pb-5">
            <h2 class="{{ $sectionTitle }}">CSC Form Attachment <span class="text-red-600">*</span></h2>
            <p class="mt-1 text-sm text-slate-600">
              Attach CSC form files (PDF, DOC, DOCX - max 10MB). Required for Plantilla.
            </p>
          </div>

          <label for="csc_form_upload_plantilla"
            class="inline-flex w-full cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-slate-500" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span id="csc_form_filename_plantilla" class="truncate">
              @if(!empty($formSource?->csc_form_path))
                {{ basename($formSource->csc_form_path) }}
              @else
                Choose a file to upload...
              @endif
            </span>
          </label>
          <input id="csc_form_upload_plantilla" type="file" name="csc_form" accept=".pdf,.doc,.docx" class="sr-only"
            data-has-existing="{{ !empty($formSource?->csc_form_path) ? '1' : '0' }}"
            data-existing-filename="{{ !empty($formSource?->csc_form_path) ? basename($formSource->csc_form_path) : '' }}"
            {{ !empty($formSource?->csc_form_path) ? '' : 'required' }}
            onchange="document.getElementById('csc_form_filename_plantilla').textContent = this.files[0] ? this.files[0].name : ((this.dataset.hasExisting === '1' && this.dataset.existingFilename) ? this.dataset.existingFilename : 'Choose a file to upload...'); if (typeof checkAllFieldsFilled === 'function') { checkAllFieldsFilled(); }">
          <p id="csc_form_error" class="mt-2 hidden text-sm text-red-600">CSC form attachment is required for Plantilla.
          </p>
          @if(!empty($formSource?->csc_form_path))
            <p class="mt-2 text-xs text-slate-500">
              Current file:
              <a href="{{ \App\Support\PreviewUrl::forPath($formSource->csc_form_path) }}" target="_blank"
                class="text-[#0D2B70] underline">{{ basename($formSource->csc_form_path) }}</a>
              - Upload a new file to replace it.
            </p>
          @endif
        </section>
      </form>

      <div
        class="sticky bottom-4 z-10 mt-6 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <p class="text-sm text-slate-600">
            Review required fields before saving this vacancy.
          </p>
          <div class="flex flex-col items-start gap-2 md:items-end">
            <span id="form-error-msg" class="hidden text-xs text-red-600">Please fill in all fields.</span>
            <div class="flex gap-3">
              <button id="vacancy-discard-btn" type="button" onclick="handleBack()"
                class="inline-flex h-11 items-center justify-center rounded-xl border border-red-300 bg-white px-5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                Discard
              </button>

              <button id="vacancy-save-btn" type="button" disabled
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 opacity-50 cursor-not-allowed">
                <span id="save-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
                <span id="save-loader" class="hidden">
                  <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                </span>
                <span id="save-text">Save</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    @include('partials.loader')
  </main>

  <!-- Confirmation Modal -->
  <x-confirm-modal title="Add Job Vacancy" message="Are you sure you want to add this job vacancy?"
    event="open-plantilla-save-confirm" confirm="confirm-plantilla-save" />

  <x-confirm-modal title="Discard Changes" message="You have unsaved changes. Are you sure you want to leave this page?"
    event="open-plantilla-discard-confirm" confirm="confirm-plantilla-discard" />

  @include('admin.partials.qualification_education_builder_script')

  <script>
    function goBack() {
      const positionMode = @json($positionMode);
      window.location.href = positionMode
        ? "{{ route('admin.positions.index') }}"
        : "{{ route('vacancies_management') }}";
    }

    function handleBack() {
      if (isFormDirty()) {
        window.dispatchEvent(new CustomEvent('open-plantilla-discard-confirm'));
      } else {
        goBack();
      }
    }

    window.addEventListener('confirm-plantilla-discard', () => {
      goBack();
    });

    function isFormDirty() {
      const form = document.getElementById('plantillaForm');
      const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
      let dirty = false;

      inputs.forEach(input => {
        if (input.hasAttribute('readonly')) return;
        if (input.type === 'checkbox' || input.type === 'radio') {
          if (input.checked !== input.defaultChecked) dirty = true;
        } else {
          if (input.value !== input.defaultValue) dirty = true;
        }
      });
      return dirty;
    }
  </script>

  <script>
  // --- PCN Auto-fill Mechanism ---
  document.addEventListener('DOMContentLoaded', function () {
    const pcnField = document.getElementById('pcn_no');
    const form = document.getElementById('plantillaForm');
    // Only auto-fill if creating (not editing existing)
    const isCreateMode = @json($isCreateMode);
    if (!pcnField || !isCreateMode) return;

    // Helper to pad number
    function pad(num, size) {
      let s = String(num);
      while (s.length < size) s = '0' + s;
      return s;
    }

    // Fetch the current count of plantilla job postings for the year
    async function fetchPCNCount() {
      try {
        // You should create this endpoint in your backend to return the count for the year/month
        const now = new Date();
        const year = now.getFullYear();
        const month = pad(now.getMonth() + 1, 2);
        // Optionally, you can use the selected month if you want to base on closing_date
        const res = await fetch(`/admin/plantilla/pcn-count?year=${year}&month=${month}`);
        const data = await res.json();
        if (data && typeof data.count === 'number') {
          return data.count;
        }
      } catch (e) {}
      return 0;
    }

    async function autoFillPCN() {
      const now = new Date();
      const year = now.getFullYear();
      const month = pad(now.getMonth() + 1, 2);
      const count = await fetchPCNCount();
      // The next posting is count+1
      const nth = pad(count + 1, 3);
      const pcn = `PCN-${year}-${month}-${nth}`;
      if (pcnField.value === '' || pcnField.value.startsWith('PCN-')) {
        pcnField.value = pcn;
      }
    }

    // Auto-fill on page load
    autoFillPCN();

    // Optionally, re-auto-fill when closing_date changes (if you want to base PCN on closing_date)
    const closingDateInput = document.getElementById('closing_date');
    if (closingDateInput) {
      closingDateInput.addEventListener('change', autoFillPCN);
    }
  });
  </script>


  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const supportingDocumentsHidden = document.getElementById('supporting_documents_required');
      const supportingDocumentCheckboxes = document.querySelectorAll('.supporting-document-checkbox');
      const plantillaForm = document.getElementById('plantillaForm');

      const syncSupportingDocumentSelection = () => {
        if (!supportingDocumentsHidden) {
          return;
        }

        const selected = Array.from(supportingDocumentCheckboxes)
          .filter((checkbox) => checkbox.checked)
          .map((checkbox) => checkbox.value);

        supportingDocumentsHidden.value = JSON.stringify(selected);

        if (typeof checkAllFieldsFilled === 'function') {
          checkAllFieldsFilled();
        }
      };

      supportingDocumentCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncSupportingDocumentSelection);
      });

      plantillaForm?.addEventListener('submit', syncSupportingDocumentSelection);
      syncSupportingDocumentSelection();

      const closingDateInput = document.getElementById('closing_date');
      if (closingDateInput && !closingDateInput.disabled) {
        flatpickr("#closing_date", {
          // monthSelectorType: "dropdown", //added
          altInput: true,
          altFormat: "F j, Y",
          dateFormat: "Y-m-d",
          minDate: "today",
          maxDate: "2099-12-31"
        });
      }

      // Auto-fill signatory fields
      const signatorySelect = document.getElementById('signatory_select');
      const positionField = document.getElementById('to_position');
      const officeField = document.getElementById('to_office');
      const officeAddressField = document.getElementById('to_office_address');
      const disablePositionFields = @json($disablePositionFields);

      if (signatorySelect && positionField && officeField && officeAddressField) {
        function handleSignatoryChange() {
          const selectedOption = signatorySelect.options[signatorySelect.selectedIndex];

          if (selectedOption.value === '') {
            // No selection - clear fields but keep disabled
            positionField.value = '';
            officeField.value = '';
            officeAddressField.value = '';
          } else {
            // Selection made - populate fields (remain disabled)
            positionField.value = selectedOption.dataset.designation;
            officeField.value = selectedOption.dataset.office;
            officeAddressField.value = selectedOption.dataset.office_address;
          }

          // Always keep these fields disabled
          // positionField.disabled = true;
          // officeField.disabled = true;
          // officeAddressField.disabled = true;
        }

        if (!disablePositionFields && signatorySelect.value === '' && signatorySelect.options.length > 1) {
          signatorySelect.selectedIndex = 1;
        }

        signatorySelect.addEventListener('change', handleSignatoryChange);
        // Initialize on page load
        handleSignatoryChange();
      }

      if (typeof checkAllFieldsFilled === 'function') {
        checkAllFieldsFilled();
      }

      // Positions dropdown population
      const titleSelect = document.getElementById('position_title_select');
      const sgField = document.getElementById('salary_grade');
      const salField = document.getElementById('monthly_salary');
      const salDisplayField = document.getElementById('monthly_salary_display');

      const formatMoney = (val) => {
        if (!val) return '';
        const num = parseFloat(val.toString().replace(/,/g, ''));
        if (isNaN(num)) return '';
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      };

      if (salDisplayField && salField) {
        if (salField.value) salDisplayField.value = formatMoney(salField.value);
        salDisplayField.addEventListener('input', (e) => {
          let raw = e.target.value.replace(/[^0-9.]/g, '');
          const parts = raw.split('.');
          if (parts.length > 2) raw = parts[0] + '.' + parts.slice(1).join('');
          salField.value = raw;
          triggerFieldEvents(salField);
        });
        salDisplayField.addEventListener('blur', () => {
          if (salField.value) salDisplayField.value = formatMoney(salField.value);
        });
        salDisplayField.addEventListener('focus', () => {
          salDisplayField.value = salField.value;
        });
        salField.addEventListener('change', () => {
          if (document.activeElement !== salDisplayField) {
            salDisplayField.value = formatMoney(salField.value);
          }
        });
      }

      const isCreateMode = @json($isCreateMode);
      const cscInputField = document.getElementById('csc_form_upload_plantilla');
      const cscFilenameField = document.getElementById('csc_form_filename_plantilla');
      const positionLookup = new Map();
      const normalizeText = (value) => String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
      const extractFileName = (path) => {
        const raw = String(path || '').trim();
        if (!raw) return '';
        const parts = raw.split(/[\\/]/);
        return parts[parts.length - 1] || '';
      };
      const defaultCscLabel = 'Choose a file to upload...';
      const initialHasExistingCsc = cscInputField?.dataset?.hasExisting === '1';
      const initialCscFilename = String(cscInputField?.dataset?.existingFilename || '').trim();
      const initialCscLabel = cscFilenameField
        ? String(cscFilenameField.textContent || '').trim() || defaultCscLabel
        : defaultCscLabel;
      const setCscTemplateState = (path, restoreInitial = false) => {
        if (!cscInputField || !cscFilenameField) return;

        if (restoreInitial) {
          cscInputField.dataset.hasExisting = initialHasExistingCsc ? '1' : '0';
          cscInputField.dataset.existingFilename = initialCscFilename;
          cscInputField.required = !initialHasExistingCsc;
          cscInputField.value = '';
          cscFilenameField.textContent = initialHasExistingCsc && initialCscFilename ? initialCscLabel : defaultCscLabel;
          return;
        }

        const templatePath = String(path || '').trim();
        const templateFilename = extractFileName(templatePath);
        const hasExisting = templateFilename !== '';
        cscInputField.dataset.hasExisting = hasExisting ? '1' : '0';
        cscInputField.dataset.existingFilename = templateFilename;
        cscInputField.required = !hasExisting;
        cscInputField.value = '';
        cscFilenameField.textContent = hasExisting ? templateFilename : defaultCscLabel;
      };
      const extractSalaryGradeDigits = (value) => String(value || '').replace(/\D/g, '').slice(0, 2);
      const formatSalaryGrade = (value, padToTwoDigits = true) => {
        const digits = extractSalaryGradeDigits(value);
        if (!digits) return '';
        return `SG-${padToTwoDigits ? digits.padStart(2, '0') : digits}`;
      };
      const triggerFieldEvents = (field) => {
        if (!field) return;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
      };
      const setSelectValue = (field, value) => {
        if (!field) return;
        const target = String(value || '').trim();
        if (!target) {
          field.value = '';
          triggerFieldEvents(field);
          return;
        }

        let matchedOption = Array.from(field.options || []).find((option) => normalizeText(option.value) === normalizeText(target));
        if (!matchedOption) {
          matchedOption = document.createElement('option');
          matchedOption.value = target;
          matchedOption.textContent = target;
          field.appendChild(matchedOption);
        }
        field.value = matchedOption.value;
        triggerFieldEvents(field);
      };
      const setValueByName = (name, value) => {
        const field = document.querySelector(`[name="${name}"]`);
        if (!field) return;

        if (field.tagName === 'SELECT') {
          setSelectValue(field, value);
          return;
        }

        const nextValue = String(value ?? '');
        if (name === 'closing_date' && field._flatpickr) {
          field._flatpickr.setDate(nextValue, true, 'Y-m-d');
          triggerFieldEvents(field);
          return;
        }

        field.value = nextValue;
        triggerFieldEvents(field);
      };
      const setValueById = (id, value) => {
        const field = document.getElementById(id);
        if (!field) return;
        field.value = String(value ?? '');
        triggerFieldEvents(field);
      };

      const applyPositionData = (record) => {
        if (!record || typeof record !== 'object') return;
        const hasProp = (key) => Object.prototype.hasOwnProperty.call(record, key);

        if (sgField) {
          sgField.value = formatSalaryGrade(record.salary_grade || '');
          triggerFieldEvents(sgField);
        }
        if (salField) {
          salField.value = record.monthly_salary ?? '';
          triggerFieldEvents(salField);
        }

        // For vacancy-mode add/edit, deadline must be set manually by the user.
        if (hasProp('closing_date') && @json($positionMode)) setValueByName('closing_date', record.closing_date);
        if (hasProp('place_of_assignment')) setValueByName('place_of_assignment', record.place_of_assignment);
        if (hasProp('pcn_no')) setValueByName('pcn_no', record.pcn_no);
        if (hasProp('plantilla_item_no')) setValueByName('plantilla_item_no', record.plantilla_item_no);
        if (hasProp('qualification_education')) {
          if (typeof window.setEducationRequirementFromRaw === 'function') {
            window.setEducationRequirementFromRaw(record.qualification_education || '');
          } else {
            setValueByName('qualification_education', record.qualification_education);
          }
        }
        if (hasProp('qualification_training')) setValueByName('qualification_training', record.qualification_training);
        if (hasProp('qualification_experience')) setValueByName('qualification_experience', record.qualification_experience);
        if (hasProp('competencies')) setValueByName('competencies', record.competencies);

        const personName = hasProp('to_person') ? String(record.to_person || '').trim() : '';
        let matchedSignatory = null;
        if (signatorySelect && hasProp('to_person')) {
          const signatoryOptions = Array.from(signatorySelect.options || []);
          if (personName) {
            matchedSignatory =
              signatoryOptions.find((option) => normalizeText(option.value) === normalizeText(personName))
              || signatoryOptions.find((option) => normalizeText(option.textContent || '') === normalizeText(personName))
              || signatoryOptions.find((option) => normalizeText(option.dataset.designation || '') === normalizeText(personName));
          }

          if (matchedSignatory) {
            signatorySelect.value = matchedSignatory.value;
          } else if (personName === '') {
            signatorySelect.value = '';
          } else if (!signatorySelect.value && signatoryOptions.length > 1) {
            signatorySelect.selectedIndex = 1;
          }
          triggerFieldEvents(signatorySelect);
        }

        if (!matchedSignatory) {
          if (hasProp('to_position')) setValueById('to_position', record.to_position);
          if (hasProp('to_office')) setValueById('to_office', record.to_office);
          if (hasProp('to_office_address')) setValueById('to_office_address', record.to_office_address);
        }

        if (hasProp('qualification_eligibility')) {
          if (typeof window.setEligibilityFromRaw === 'function') {
            window.setEligibilityFromRaw(record.qualification_eligibility || '');
          } else {
            setValueByName('qualification_eligibility', record.qualification_eligibility || '');
          }
        }

        setCscTemplateState(hasProp('csc_form_path') ? record.csc_form_path : '');

        if (hasProp('supporting_documents_required')) {
          let reqDocs = record.supporting_documents_required;
          if (typeof reqDocs === 'string') {
            try { reqDocs = JSON.parse(reqDocs); } catch(e) { reqDocs = []; }
          }
          if (Array.isArray(reqDocs)) {
            const checkboxes = document.querySelectorAll('.supporting-document-checkbox');
            checkboxes.forEach(cb => {
              cb.checked = reqDocs.includes(cb.value);
            });
            const hiddenInput = document.getElementById('supporting_documents_required');
            if (hiddenInput) {
              hiddenInput.value = JSON.stringify(reqDocs);
            }
          }
        }

        if (typeof checkAllFieldsFilled === 'function') {
          checkAllFieldsFilled();
        }
      };

      if (sgField) {
        sgField.maxLength = 5;
        sgField.inputMode = 'numeric';
        const normalizeSalaryGradeInput = () => {
          sgField.value = formatSalaryGrade(sgField.value, false);
          if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
        };
        const finalizeSalaryGrade = () => {
          sgField.value = formatSalaryGrade(sgField.value, true);
          if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
        };
        sgField.value = formatSalaryGrade(sgField.value);
        sgField.addEventListener('input', normalizeSalaryGradeInput);
        sgField.addEventListener('blur', finalizeSalaryGrade);
      }
      if (titleSelect && String(titleSelect.tagName || '').toUpperCase() === 'SELECT') {
        fetch("{{ route('admin.positions.list', ['vacancy_type' => 'Plantilla']) }}")
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              const current = "{{ old('position_title', $formSource?->position_title ?? '') }}";
              let currentFound = false;
              data.data.forEach(o => {
                const title = String(o.position_title || '').trim();
                if (!title) {
                  return;
                }
                positionLookup.set(normalizeText(title), o);

                const opt = document.createElement('option');
                opt.value = title;
                const vacancyType = String(o.vacancy_type || '').trim();
                opt.textContent = vacancyType ? `${title} (${vacancyType})` : title;
                if (current && normalizeText(current) === normalizeText(title)) opt.selected = true;
                if (current && normalizeText(current) === normalizeText(title)) {
                  currentFound = true;
                }
                titleSelect.appendChild(opt);
              });
              if (current && !currentFound) {
                const fallbackOption = document.createElement('option');
                fallbackOption.value = current;
                fallbackOption.textContent = current;
                fallbackOption.selected = true;
                titleSelect.appendChild(fallbackOption);
              }
              if (isCreateMode) {
                const initialKey = normalizeText(titleSelect.value);
                if (initialKey && positionLookup.has(initialKey)) {
                  applyPositionData(positionLookup.get(initialKey));
                }
              }
              if (typeof checkAllFieldsFilled === 'function') {
                checkAllFieldsFilled();
              }
            }
          }).catch(() => { });
        titleSelect.addEventListener('change', () => {
          const record = positionLookup.get(normalizeText(titleSelect.value));
          if (record) {
            applyPositionData(record);
            return;
          }
          if (sgField) {
            sgField.value = formatSalaryGrade('');
            triggerFieldEvents(sgField);
          }
          if (salField) {
            salField.value = '';
            triggerFieldEvents(salField);
          }
          setCscTemplateState('', true);
          if (typeof checkAllFieldsFilled === 'function') {
            checkAllFieldsFilled();
          }
        });
      }
    });

    // Structured eligibility UI state + interactions
    const defaultPredefinedEligibilities = [
      { name: 'Bar/Board Eligibility', legalBasis: 'RA 1080', level: 'Second Level' },
      { name: 'CSC Professional Eligibility', legalBasis: 'CSR 2017/PD 807', level: 'Second Level' },
      { name: 'Honor Graduate Eligibility', legalBasis: 'PD 907', level: 'Second Level' },
      { name: 'Foreign School Honor Graduate Eligibility', legalBasis: 'CSC Res. 1302714', level: 'Second Level' },
      { name: 'Scientific and Technological Specialist Eligibility', legalBasis: 'PD 997', level: 'Second Level' },
      { name: 'Electronic Data Processing Specialist Eligibility', legalBasis: 'CSC Res. 90-083', level: 'Second Level' },
      { name: 'Subprofessional (Sub-Prof) Eligibility', legalBasis: 'CSR 2017/PD 807', level: 'First Level' },
      { name: 'Skills Eligibility-Category II', legalBasis: 'CSC MC 11, s.1996', level: 'First Level' },
      { name: 'Barangay Official Eligibility', legalBasis: 'RA 7160', level: 'First Level' },
      { name: 'Sanggunian Member Eligibility', legalBasis: 'RA 10156', level: 'First Level' },
      { name: 'Barangay Health Worker Eligibility', legalBasis: 'RA 7883', level: 'First Level' },
      { name: 'Barangay Nutrition Scholar Eligibility', legalBasis: 'PD 1569', level: 'First Level' },
    ];

    let predefinedEligibilities = [...defaultPredefinedEligibilities];
    let eligibilityState = [];
    let editingEligibilityId = null;

    async function loadPredefinedEligibilities() {
      try {
        const response = await fetch("{{ route('admin.eligibilities.list') }}");
        const payload = await response.json();
        if (payload?.success && Array.isArray(payload.data) && payload.data.length > 0) {
          predefinedEligibilities = payload.data
            .map(item => ({
              name: String(item.name || '').trim(),
              legalBasis: String(item.legal_basis || '').trim(),
              level: String(item.level || '').trim(),
            }))
            .filter(item => item.name !== '');
          return;
        }
      } catch (error) {
        // Keep defaults if endpoint is unavailable.
      }

      predefinedEligibilities = [...defaultPredefinedEligibilities];
    }

    function normalizeEligibilityName(value) {
      return String(value || '').trim().toLowerCase();
    }

    function escapeEligibilityHtml(value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function createEligibilityItem(payload) {
      return {
        id: 'elig-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8),
        name: String(payload.name || '').trim(),
        legalBasis: String(payload.legalBasis || '').trim(),
        level: String(payload.level || '').trim(),
        isCustom: Boolean(payload.isCustom),
      };
    }

    function hasDuplicateEligibilityName(name, ignoreId = null) {
      const target = normalizeEligibilityName(name);
      return eligibilityState.some(item => normalizeEligibilityName(item.name) === target && item.id !== ignoreId);
    }

    function parseInitialEligibility(rawValue) {
      const raw = String(rawValue || '').trim();
      if (!raw) {
        return [];
      }

      let parsedItems = [];

      try {
        const parsed = JSON.parse(raw);
        const source = Array.isArray(parsed) ? parsed : [parsed];
        parsedItems = source
          .filter(item => item && typeof item === 'object' && String(item.name || '').trim() !== '')
          .map(item => createEligibilityItem({
            name: item.name,
            legalBasis: item.legalBasis || '',
            level: item.level || '',
            isCustom: Boolean(item.isCustom),
          }));
      } catch (_) {
        const tokens = raw.split(/\r?\n|;/).map(token => token.trim()).filter(Boolean);
        parsedItems = tokens.map(token => {
          const preset = predefinedEligibilities.find(p => normalizeEligibilityName(p.name) === normalizeEligibilityName(token));
          if (preset) return createEligibilityItem(preset);
          return createEligibilityItem({
            name: token,
            legalBasis: '',
            level: '',
            isCustom: true,
          });
        });
      }

      const deduped = [];
      const seen = new Set();
      parsedItems.forEach(item => {
        const key = normalizeEligibilityName(item.name);
        if (!key || seen.has(key)) return;
        seen.add(key);
        deduped.push(item);
      });

      return deduped;
    }

    function syncEligibilityHiddenField() {
      const hidden = document.getElementById('qualification_eligibility_hidden');
      if (!hidden) return;

      if (!eligibilityState.length) {
        hidden.value = '';
      } else {
        hidden.value = JSON.stringify(
          eligibilityState.map(({ id, ...rest }) => rest)
        );
      }

      window.eligibilityState = eligibilityState;
    }

    function hasEligibilityItems() {
      return Array.isArray(eligibilityState) && eligibilityState.length > 0;
    }

    window.hasEligibilityItems = hasEligibilityItems;

    document.addEventListener('DOMContentLoaded', async function () {
      const listEl = document.getElementById('eligibility-list');
      const hiddenEl = document.getElementById('qualification_eligibility_hidden');
      const selectEl = document.getElementById('eligibility-select');
      const addCustomBtn = document.getElementById('eligibility-add-custom-btn');
      const customEditor = document.getElementById('eligibility-custom-editor');
      const customNameEl = document.getElementById('eligibility-custom-name');
      const customLegalEl = document.getElementById('eligibility-custom-legal');
      const customLevelEl = document.getElementById('eligibility-custom-level');
      const customSaveBtn = document.getElementById('eligibility-custom-save');
      const customCancelBtn = document.getElementById('eligibility-custom-cancel');
      const addErrorEl = document.getElementById('eligibility_add_error');

      if (!listEl || !hiddenEl || !selectEl || !addCustomBtn || !customEditor || !customNameEl || !customLegalEl || !customLevelEl || !customSaveBtn || !customCancelBtn || !addErrorEl) {
        return;
      }

      await loadPredefinedEligibilities();

      function setAddError(message) {
        if (!message) {
          addErrorEl.textContent = '';
          addErrorEl.classList.add('hidden');
          return;
        }
        addErrorEl.textContent = message;
        addErrorEl.classList.remove('hidden');
      }

      function renderEligibilitySelectOptions() {
        const current = String(selectEl.value || '');
        const selectedNames = new Set(eligibilityState.map(item => normalizeEligibilityName(item.name)));
        const available = predefinedEligibilities.filter(item => !selectedNames.has(normalizeEligibilityName(item.name)));

        selectEl.innerHTML = `
              <option value="">Select eligibility from the official list</option>
              ${available.map(item => `<option value="${escapeEligibilityHtml(item.name)}">${escapeEligibilityHtml(item.name)} (${escapeEligibilityHtml(item.legalBasis)} | ${escapeEligibilityHtml(item.level)})</option>`).join('')}
          `;

        if (current && available.some(item => item.name === current)) {
          selectEl.value = current;
        }
      }

      function closeCustomEditor() {
        customEditor.classList.add('hidden');
        customNameEl.value = '';
        customLegalEl.value = '';
        customLevelEl.value = '';
      }

      function openCustomEditor(initialName = '') {
        customEditor.classList.remove('hidden');
        customNameEl.value = initialName;
        customLegalEl.value = '';
        customLevelEl.value = '';
        customNameEl.focus();
      }

      function addPresetByName(name) {
        const preset = predefinedEligibilities.find(item => item.name === name);
        if (!preset) return;
        if (hasDuplicateEligibilityName(preset.name)) {
          setAddError('This eligibility already exists in your selected list.');
          return;
        }
        eligibilityState.push(createEligibilityItem(preset));
        syncEligibilityHiddenField();
        renderEligibilityList();
        renderEligibilitySelectOptions();
        setAddError('');
        closeCustomEditor();
        if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
      }

      function renderEligibilityList() {
        if (!eligibilityState.length) {
          listEl.innerHTML = `
                  <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500">
                      No eligibilities selected yet.
                  </div>
              `;
          return;
        }

        listEl.innerHTML = eligibilityState.map(item => {
          if (editingEligibilityId === item.id) {
            return `
                      <div class="rounded-2xl border border-slate-300 bg-white p-4" data-eligibility-item="${escapeEligibilityHtml(item.id)}">
                          <div class="grid gap-3 md:grid-cols-3">
                              <input data-field="name" type="text" value="${escapeEligibilityHtml(item.name)}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100" placeholder="Eligibility Name">
                              <input data-field="legalBasis" type="text" value="${escapeEligibilityHtml(item.legalBasis)}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100" placeholder="Legal Basis">
                              <input data-field="level" type="text" value="${escapeEligibilityHtml(item.level)}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100" placeholder="Level">
                          </div>
                          <div class="mt-3 flex gap-2">
                              <button type="button" data-action="save-edit" data-id="${escapeEligibilityHtml(item.id)}" class="inline-flex h-9 items-center rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white hover:bg-slate-800">Save</button>
                              <button type="button" data-action="cancel-edit" data-id="${escapeEligibilityHtml(item.id)}" class="inline-flex h-9 items-center rounded-lg border border-slate-300 px-3 text-xs font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                          </div>
                      </div>
                  `;
          }

          return `
                  <div class="rounded-2xl border border-slate-300 bg-white p-4" data-eligibility-item="${escapeEligibilityHtml(item.id)}">
                      <div class="flex items-start justify-between gap-3">
                          <div>
                              <p class="text-sm font-semibold text-slate-900">${escapeEligibilityHtml(item.name)}</p>
                              ${item.isCustom ? '<p class="mt-1 text-xs font-medium text-slate-500">Custom eligibility</p>' : ''}
                          </div>
                          <div class="flex gap-2">
                              <button type="button" data-action="edit" data-id="${escapeEligibilityHtml(item.id)}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Edit</button>
                              <button type="button" data-action="remove" data-id="${escapeEligibilityHtml(item.id)}" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Remove</button>
                          </div>
                      </div>
                      <div class="mt-4 grid gap-3 md:grid-cols-3">
                          <div class="rounded-xl bg-slate-50 p-3">
                              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Eligibility Name</p>
                              <p class="mt-1 text-sm text-slate-900">${escapeEligibilityHtml(item.name || '-')}</p>
                          </div>
                          <div class="rounded-xl bg-slate-50 p-3">
                              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Legal Basis</p>
                              <p class="mt-1 text-sm text-slate-900">${escapeEligibilityHtml(item.legalBasis || '-')}</p>
                          </div>
                          <div class="rounded-xl bg-slate-50 p-3">
                              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Level</p>
                              <p class="mt-1 text-sm text-slate-900">${escapeEligibilityHtml(item.level || '-')}</p>
                          </div>
                      </div>
                  </div>
              `;
        }).join('');
      }

      window.setEligibilityFromRaw = function (rawValue) {
        eligibilityState = parseInitialEligibility(rawValue);
        editingEligibilityId = null;
        syncEligibilityHiddenField();
        renderEligibilityList();
        renderEligibilitySelectOptions();
        setAddError('');
        if (typeof checkAllFieldsFilled === 'function') {
          checkAllFieldsFilled();
        }
      };

      eligibilityState = parseInitialEligibility(hiddenEl.value);
      syncEligibilityHiddenField();
      renderEligibilityList();
      renderEligibilitySelectOptions();

      selectEl.addEventListener('change', function () {
        const selectedName = String(selectEl.value || '').trim();
        if (!selectedName) {
          return;
        }
        addPresetByName(selectedName);
      });

      addCustomBtn.addEventListener('click', function () {
        openCustomEditor('');
        setAddError('');
      });

      customSaveBtn.addEventListener('click', function () {
        const payload = {
          name: customNameEl.value.trim(),
          legalBasis: customLegalEl.value.trim(),
          level: customLevelEl.value.trim(),
          isCustom: true,
        };

        if (!payload.name) {
          setAddError('Custom eligibility name is required.');
          return;
        }

        if (hasDuplicateEligibilityName(payload.name)) {
          setAddError('This eligibility already exists in your selected list.');
          return;
        }

        eligibilityState.push(createEligibilityItem(payload));
        syncEligibilityHiddenField();
        renderEligibilityList();
        renderEligibilitySelectOptions();
        closeCustomEditor();
        setAddError('');
        if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
      });

      customCancelBtn.addEventListener('click', function () {
        closeCustomEditor();
        setAddError('');
      });

      listEl.addEventListener('click', function (event) {
        const actionEl = event.target.closest('[data-action]');
        if (!actionEl) return;

        const action = actionEl.getAttribute('data-action');
        const id = actionEl.getAttribute('data-id') || '';
        const itemIndex = eligibilityState.findIndex(item => item.id === id);

        if (action === 'remove' && itemIndex >= 0) {
          eligibilityState.splice(itemIndex, 1);
          editingEligibilityId = null;
          syncEligibilityHiddenField();
          renderEligibilityList();
          renderEligibilitySelectOptions();
          if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
          return;
        }

        if (action === 'edit' && itemIndex >= 0) {
          editingEligibilityId = id;
          renderEligibilityList();
          return;
        }

        if (action === 'cancel-edit') {
          editingEligibilityId = null;
          renderEligibilityList();
          return;
        }

        if (action === 'save-edit' && itemIndex >= 0) {
          const wrapper = actionEl.closest('[data-eligibility-item]');
          if (!wrapper) return;

          const nameInput = wrapper.querySelector('[data-field="name"]');
          const legalInput = wrapper.querySelector('[data-field="legalBasis"]');
          const levelInput = wrapper.querySelector('[data-field="level"]');

          const nextName = String(nameInput?.value || '').trim();
          if (!nextName) {
            setAddError('Eligibility name is required when editing.');
            return;
          }

          if (hasDuplicateEligibilityName(nextName, id)) {
            const proceed = window.confirm('An eligibility with this name already exists. Save anyway?');
            if (!proceed) return;
          }

          eligibilityState[itemIndex] = {
            ...eligibilityState[itemIndex],
            name: nextName,
            legalBasis: String(legalInput?.value || '').trim(),
            level: String(levelInput?.value || '').trim(),
          };

          editingEligibilityId = null;
          setAddError('');
          syncEligibilityHiddenField();
          renderEligibilityList();
          renderEligibilitySelectOptions();
          if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
        }
      });
    });

    // Validate all fields
    function checkAllFieldsFilled() {
      const form = document.getElementById('plantillaForm');
      const requiredFields = new Set([
        'position_title',
        'salary_grade',
        'monthly_salary',
        'closing_date',
        'place_of_assignment',
        'qualification_education',
        'qualification_training',
        'qualification_experience',
        'qualification_eligibility',
        'to_person',
        'to_position',
        'to_office',
        'to_office_address',
      ]);
      const disablePositionFields = @json($disablePositionFields);
      if (disablePositionFields) {
        requiredFields.delete('closing_date');
        requiredFields.delete('to_person');
        requiredFields.delete('to_position');
        requiredFields.delete('to_office');
        requiredFields.delete('to_office_address');
      }
      const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
      let allFilled = true;

      inputs.forEach(input => {
        if (!requiredFields.has(input.name)) return;

        // For Select
        if (input.tagName === 'SELECT') {
          if (!input.value || input.value === '') allFilled = false;
          if (input.selectedOptions.length > 0 && input.selectedOptions[0].disabled) allFilled = false;
          return;
        }

        const value = input.value.trim();
        if (!value) {
          allFilled = false;
          return;
        }

        if (input.name === 'salary_grade' && !/^SG-\d{2}$/.test(value)) {
          allFilled = false;
        }
      });

      if (requiredFields.has('qualification_education')) {
        const educationCode = document.getElementById('minimum_education_code');
        const educationHidden = document.getElementById('qualification_education');
        if (!educationCode || !String(educationCode.value || '').trim()) {
          allFilled = false;
        }
        if (!educationHidden || !String(educationHidden.value || '').trim()) {
          allFilled = false;
        }
        if (typeof window.validateEducationRequirementConfig === 'function') {
          const educationValidation = window.validateEducationRequirementConfig();
          if (!educationValidation.valid) {
            allFilled = false;
          }
        }
      }

      if (requiredFields.has('qualification_eligibility')) {
        const hiddenEligibility = document.getElementById('qualification_eligibility_hidden');
        const selectEligibility = document.getElementById('eligibility-select');
        const hasItems = typeof hasEligibilityItems === 'function' && hasEligibilityItems();
        const hasHiddenValue = Boolean(String(hiddenEligibility?.value || '').trim());
        const hasSelectedValue = Boolean(String(selectEligibility?.value || '').trim());
        if (!(hasItems || hasHiddenValue || hasSelectedValue)) {
          allFilled = false;
        }
      }

      // Defer strict CSC validation to confirm handler to avoid false disabled states
      // caused by browser file input state timing.
      if (!document.getElementById('csc_form_upload_plantilla')) {
        allFilled = false;
      }

      const saveBtn = document.getElementById('vacancy-save-btn');
      const errorMsg = document.getElementById('form-error-msg');

      if (allFilled) {
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        errorMsg.classList.add('hidden');
      } else {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        errorMsg.classList.remove('hidden');
      }
    }

    // Add listeners for validation
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('plantillaForm');
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        input.addEventListener('input', checkAllFieldsFilled);
        input.addEventListener('change', checkAllFieldsFilled);
      });
      // Initial check
      checkAllFieldsFilled();
    });

    // Open save confirmation modal from save button click.
    document.addEventListener('DOMContentLoaded', () => {
      const saveBtn = document.getElementById('vacancy-save-btn');
      if (!saveBtn) return;
      saveBtn.addEventListener('click', () => {
        if (saveBtn.disabled) return;
        window.dispatchEvent(new CustomEvent('open-plantilla-save-confirm'));
      });
    });

    // Validate and submit on confirm
    window.addEventListener('confirm-plantilla-save', () => {
      const disablePositionFields = @json($disablePositionFields);
      const form = document.getElementById('plantillaForm');
      const errors = [];
      const show = (el, msg) => { if (el) { el.textContent = msg; el.classList.remove('hidden'); } };
      const hide = (el) => { if (el) { el.textContent = ''; el.classList.add('hidden'); } };
      // Fields
      const positionTitle = document.getElementById('position_title_select');
      const salaryGrade = document.getElementById('salary_grade');
      const closingDate = document.getElementById('closing_date');
      const place = document.getElementById('place_of_assignment');
      const monthlySalary = document.getElementById('monthly_salary');
      // Errors
      const eTitle = document.getElementById('position_title_error');
      const eSalaryGrade = document.getElementById('salary_grade_error');
      const eClosing = document.getElementById('closing_date_error');
      const ePlace = document.getElementById('place_of_assignment_error');
      const eEducation = document.getElementById('qualification_education_error');
      const eEligibility = document.getElementById('qualification_eligibility_error');
      const eSalary = document.getElementById('monthly_salary_error');
      const eCsc = document.getElementById('csc_form_error');
      // Reset
      [eTitle, eSalaryGrade, eClosing, ePlace, eEducation, eEligibility, eSalary, eCsc].forEach(hide);
      // Validate basics
      if (!positionTitle || !positionTitle.value.trim()) { errors.push('Position title is required.'); show(eTitle, 'Position title is required.'); }
      if (!salaryGrade || !/^SG-\d{2}$/.test(String(salaryGrade.value || '').trim())) { errors.push('Salary grade must be in SG-00 format.'); show(eSalaryGrade, 'Salary grade must be in SG-00 format (example: SG-23).'); }
      if (!disablePositionFields && !closingDate.value) { errors.push('Deadline is required.'); show(eClosing, 'Deadline of application is required.'); }
      if (!place.value) { errors.push('Place of assignment is required.'); show(ePlace, 'Place of assignment is required.'); }
      const educationCode = document.getElementById('minimum_education_code');
      const educationHidden = document.getElementById('qualification_education');
      const educationValidation = typeof window.validateEducationRequirementConfig === 'function'
        ? window.validateEducationRequirementConfig()
        : { valid: Boolean(educationHidden && String(educationHidden.value || '').trim()), message: '' };
      if (
        !educationCode
        || !String(educationCode.value || '').trim()
        || !educationValidation.valid
        || !educationHidden
        || !String(educationHidden.value || '').trim()
      ) {
        errors.push('Education requirement is required.');
        show(eEducation, educationValidation.message || 'Education requirement is required.');
      }
      let hasEligibilityValue = typeof hasEligibilityItems === 'function' && hasEligibilityItems();
      const eligibilityHidden = document.getElementById('qualification_eligibility_hidden');
      const eligibilitySelect = document.getElementById('eligibility-select');
      if (!hasEligibilityValue && String(eligibilityHidden?.value || '').trim() !== '') {
        hasEligibilityValue = true;
      }
      if (!hasEligibilityValue && String(eligibilitySelect?.value || '').trim() !== '') {
        // If admin selected one from dropdown but skipped "Add Selected",
        // convert it into a valid hidden payload on save.
        const selectedName = String(eligibilitySelect.value || '').trim();
        if (eligibilityHidden) {
          eligibilityHidden.value = JSON.stringify([{
            name: selectedName,
            legalBasis: '',
            level: '',
            isCustom: false,
          }]);
        }
        hasEligibilityValue = true;
      }
      if (!hasEligibilityValue) { errors.push('At least one eligibility is required.'); show(eEligibility, 'At least one eligibility is required.'); }
      const cscInput = document.getElementById('csc_form_upload_plantilla');
      const hasExistingCsc = cscInput?.dataset?.hasExisting === '1';
      const hasSelectedCsc = Boolean(cscInput?.files && cscInput.files.length > 0);
      if (!(hasExistingCsc || hasSelectedCsc)) { errors.push('CSC form attachment is required for Plantilla.'); show(eCsc, 'CSC form attachment is required for Plantilla.'); }
      // Salary checks
      const MAX = 1000000;
      const MIN = 0;
      const sal = parseFloat(monthlySalary.value);
      if (isNaN(sal)) { errors.push('Monthly salary is required.'); show(eSalary, 'Monthly salary is required.'); }
      else if (sal < MIN) { errors.push('Monthly salary cannot be negative.'); show(eSalary, 'Monthly salary cannot be negative.'); }
      else if (sal > MAX) { errors.push('Monthly salary exceeds allowed maximum (1,000,000).'); show(eSalary, 'Monthly salary exceeds allowed maximum (1,000,000).'); }
      if (errors.length === 0) {
        // Disable button and show loader
        const btn = document.getElementById('vacancy-save-btn');
        const icon = document.getElementById('save-icon');
        const loader = document.getElementById('save-loader');
        const text = document.getElementById('save-text');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.classList.add('hidden');
        loader.classList.remove('hidden');
        text.textContent = 'SAVING...';

        form.submit();
      }
    });

    // Live salary validation
    document.addEventListener('input', (e) => {
      if (e.target && e.target.id === 'monthly_salary') {
        const eSalary = document.getElementById('monthly_salary_error');
        const sal = parseFloat(e.target.value);
        if (isNaN(sal)) { eSalary.textContent = 'Monthly salary is required.'; eSalary.classList.remove('hidden'); }
        else if (sal < 0) { eSalary.textContent = 'Monthly salary cannot be negative.'; eSalary.classList.remove('hidden'); }
        else if (sal > 1000000) { eSalary.textContent = 'Monthly salary exceeds allowed maximum (1,000,000).'; eSalary.classList.remove('hidden'); }
        else { eSalary.textContent = ''; eSalary.classList.add('hidden'); }
      }
    });
  </script>

@endsection
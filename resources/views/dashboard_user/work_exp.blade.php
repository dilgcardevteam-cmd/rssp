<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Work Experience Sheet</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  @include('partials.global_toast')
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }
    .sidebar-text-hidden {
      display: none;
    }
    @media (min-width: 768px) {
      .sidebar-text-hidden {
        display: inline;
      }
    }
    
    /*------ Eye Checkbox ------*/
    .container {
      --color: #a5a5b0;
      --size: 24px;
      display: inline-flex;
      align-items: center;
      position: relative;
      cursor: pointer;
      font-size: var(--size);
      user-select: none;
      fill: var(--color);
      width: var(--size); /* optional: keep icon area tight */
    }

    .container .eye,
    .container .eye-slash {
      position: absolute;
      animation: keyframes-fill 0.5s;
    }

    .container .eye-slash {
      display: none;
    }

    .container input:checked ~ .eye {
      display: none;
    }

    .container input:checked ~ .eye-slash {
      display: block;
    }

    .container input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
      height: 0;
      width: 0;
    }

    @keyframes keyframes-fill {
      0% {
        transform: scale(0);
        opacity: 0;
      }
      50% {
        transform: scale(1.2);
      }
    }

    /* Mobile-specific improvements */
    @media (max-width: 767px) {
      /* Reduce padding on mobile */
      .mobile-padding {
        padding: 12px !important;
      }
      
      /* Better spacing for form fields */
      .mobile-form-spacing {
        margin-bottom: 16px !important;
      }
      
      /* Adjust button sizes for mobile */
      .mobile-button {
        padding: 12px 16px !important;
        font-size: 14px !important;
      }
      
      /* Better input field sizing */
      .mobile-input {
        height: 44px !important;
        font-size: 16px !important; /* Prevents zoom on iOS */
      }
      
      /* Compact fieldset styling */
      .mobile-fieldset {
        padding: 16px 12px !important;
        margin-bottom: 20px !important;
      }
      
      /* Mobile-friendly grid adjustments */
      .mobile-grid {
        display: block !important;
      }
      
      .mobile-grid > div {
        margin-bottom: 16px !important;
      }
      
      /* Better mobile header */
      .mobile-header {
        font-size: 24px !important;
        padding-bottom: 12px !important;
        margin-bottom: 20px !important;
      }
      
      /* Compact instructions section */
      .mobile-instructions {
        padding: 12px !important;
        font-size: 13px !important;
      }
      
      /* Better mobile alerts positioning */
      .mobile-alert {
        top: 10px !important;
        right: 10px !important;
        left: 10px !important;
        width: auto !important;
        max-width: none !important;
      }
      
      /* Mobile-friendly duration input layout */
      .mobile-duration {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
      }
      
      .mobile-duration-to {
        text-align: left !important;
        padding: 0 !important;
        font-size: 14px !important;
        color: #6b7280 !important;
      }
      
      /* Better checkbox area for mobile */
      .mobile-checkbox-area {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 12px !important;
        background-color: #f8fafc !important;
        border-radius: 8px !important;
        margin-bottom: 16px !important;
      }
      
      /* Mobile button groups */
      .mobile-button-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        width: 100% !important;
      }
      
      .mobile-button-group button {
        width: 100% !important;
        justify-content: center !important;
      }
      
      /* Better mobile footer */
      .mobile-footer {
        padding: 16px !important;
        background-color: white !important;
        border-top: 1px solid #e5e7eb !important;
        position: sticky !important;
        bottom: 0 !important;
        z-index: 10 !important;
      }
      
      /* Improve form element spacing on mobile */
      .mobile-form-element {
        margin-bottom: 12px !important;
      }
      
      /* Better mobile labels */
      .mobile-label {
        font-size: 14px !important;
        margin-bottom: 6px !important;
        font-weight: 600 !important;
      }
      
      /* Mobile-friendly remove buttons */
      .mobile-remove-btn {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
      }
      
      /* Better mobile list item spacing */
      .mobile-list-item {
        display: flex !important;
        gap: 8px !important;
        margin-bottom: 12px !important;
        align-items: stretch !important;
      }
      
      /* Mobile-friendly add buttons */
      .mobile-add-btn {
        width: 100% !important;
        margin-top: 8px !important;
        justify-content: center !important;
      }
    }
    
    /* Touch-friendly improvements for all screen sizes */
    input[type="text"], input[type="date"], textarea {
      -webkit-appearance: none;
      border-radius: 6px !important;
    }
    
    button {
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }
    
    /* Improve focus states for mobile */
    @media (max-width: 767px) {
      input:focus, textarea:focus, button:focus {
        outline: 2px solid #3b82f6 !important;
        outline-offset: 2px !important;
      }
    }

  </style>
</head>

<body class="bg-gray-100 py-4 sm:py-10 px-2 sm:px-4 overflow-x-hidden">

<!-- Success & Error Alerts -->
@if(session('success'))
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
     class="fixed mobile-alert top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Success!</strong>
    <p class="text-sm">{{ session('success') }}</p>
</div>
@endif

@if ($errors->any())
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
     class="fixed mobile-alert top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm">
    <strong class="font-bold">Whoops!</strong>
    <ul class="list-disc list-inside text-sm mt-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="workExperienceForm" action="{{ route('work_experience_store') }}" method="POST">
  @csrf
  <input type="hidden" name="after_action" id="after_action" value="save">

  <main class="max-w-full md:max-w-5xl mx-auto bg-white border border-gray-300 mobile-padding p-4 md:p-6 rounded-[10px] shadow-md overflow-x-auto"
        x-data="{
          entries: (() => {
            let data = {{ old('entries') ? json_encode(old('entries')) : ($workEntries->isEmpty() ? json_encode([[ 'start_date' => '', 'end_date' => '', 'position' => '', 'office' => '', 'supervisor' => '', 'agency' => '', 'accomplishments' => [''], 'duties' => [''], 'isDisplayed' => true, ]]) : $workEntries->toJson()) }};
            return data.map(entry => ({
              ...entry,
              start_date: entry.start_date ? new Date(entry.start_date).toLocaleDateString('en-CA').split('T')[0] : '',
              end_date: entry.end_date ? new Date(entry.end_date).toLocaleDateString('en-CA').split('T')[0] : '',
              present: entry.end_date === null,
            }));
          })(),
        }" id="workSheet">

    <h2 class="text-2xl md:text-3xl mobile-header font-bold text-[#002C76] mb-6 border-b-4 pb-2 border-[#002C76] text-center">
      Work Experience Sheet
    </h2>

    <!-- Instructions -->
    <section class="bg-blue-50 border border-blue-300 rounded-md mobile-instructions p-4 mb-6 text-sm text-gray-700">
      <h3 class="text-lg font-semibold text-blue-900 mb-2">ðŸ“Œ Instructions:</h3>
      <ul class="list-disc pl-6 space-y-1">
        <li>Fill in the work experience details you want to include as accurately as possible.</li>
        <li>Use the <strong>"âž• Add Work Entry"</strong> button to add more entries if needed.</li>
        <li>Click <strong>"ðŸ—‘ Remove Entry"</strong> to delete a specific work experience block.</li>
        <li>Include only the work experiences relevant to the position being applied to.</li>
        <li>For current roles, check <strong>"Present"</strong>. Work experience should be listed from most recent.</li>
      </ul>
    </section>

    <!-- Entries -->
    <template x-for="(entry, index) in entries" :key="index">
      <div>
        <div class="mobile-checkbox-area m-4">
          <input type="hidden" :name="'entries[' + index + '][isDisplayed]'" :value="entry.isDisplayed ? 1 : 0">

          <label class="container">
            <input type="checkbox" x-model="entry.isDisplayed">
            <svg class="eye" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512">
              <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
            </svg>
            <svg class="eye-slash" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512">
              <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L408 294.5c8.4-19.3 10.6-41.4 4.8-63.3c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3c0 10.2-2.4 19.8-6.6 28.3l-90.3-70.8zM373 389.9c-16.4 6.5-34.3 10.1-53 10.1c-79.5 0-144-64.5-144-144c0-6.9 .5-13.6 1.4-20.2L83.1 161.5C60.3 191.2 44 220.8 34.5 243.7c-3.3 7.9-3.3 16.7 0 24.6c14.9 35.7 46.2 87.7 93 131.1C174.5 443.2 239.2 480 320 480c47.8 0 89.9-12.9 126.2-32.5L373 389.9z"/>
            </svg>
          </label>

          <span class="text-sm mobile-label font-bold" :class="entry.isDisplayed ? '' : 'text-gray-400'" x-text="entry.isDisplayed ? 'Shown when exported' : 'Hidden at export'"></span>
        </div>

        <fieldset :class="entry.isDisplayed ? '' : 'bg-gray-100 text-gray-500 cursor-not-allowed'" class="mobile-fieldset p-4 mb-6 rounded-[10px] border space-y-3">
          <!-- DURATION & POSITION -->
          <div class="grid grid-cols-1 md:grid-cols-2 mobile-grid gap-4">
            <div class="mobile-form-element">
              <label class="block text-sm font-semibold mobile-label" :class="entry.isDisplayed ? '' : 'text-gray-400'">DURATION</label>
              <div class="flex flex-col sm:flex-row mobile-duration gap-2">
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][start_date]'" type="date" class="mobile-input h-11 w-full border rounded px-3 py-2" x-model="entry.start_date">
                <span class="text-center sm:pt-2 mobile-duration-to text-gray-500">to</span>
                <div class="flex flex-col w-full">
                  <input :class="entry.present ? 'text-gray-400' : ''" :readonly="!entry.isDisplayed || entry.present" :disabled="!entry.isDisplayed || entry.present" :name="'entries[' + index + '][end_date]'" type="date" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.end_date">
                  <input type="hidden" :name="'entries[' + index + '][present]'" :value="entry.present ? 1 : 0">
                  <label class="text-xs mt-1"><input type="checkbox" x-model="entry.present" @change="if (entry.present) { entry.end_date = '' }"> Present</label>
                </div>
              </div>
            </div>
            <div class="mobile-form-element">
              <label class="block text-sm font-semibold mobile-label" :class="entry.isDisplayed ? '' : 'text-gray-400'">POSITION</label>
              <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][position]'" type="text" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.position" placeholder="e.g. IT Officer">
            </div>
          </div>

          <!-- OFFICE, SUPERVISOR, AGENCY -->
          <div class="grid grid-cols-1 md:grid-cols-2 mobile-grid gap-4">
            <div class="mobile-form-element">
              <label class="block text-sm font-semibold mobile-label" :class="entry.isDisplayed ? '' : 'text-gray-400'">OFFICE/UNIT</label>
              <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][office]'" type="text" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.office">
            </div>
            <div class="mobile-form-element">
              <label class="block text-sm font-semibold mobile-label" :class="entry.isDisplayed ? '' : 'text-gray-400'">IMMEDIATE SUPERVISOR</label>
              <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][supervisor]'" type="text" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.supervisor">
            </div>
            <div class="mobile-form-element">
              <label class="block text-sm font-semibold mobile-label" :class="entry.isDisplayed ? '' : 'text-gray-400'">AGENCY/LOCATION</label>
              <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][agency]'" type="text" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.agency">
            </div>
          </div>

          <!-- ACCOMPLISHMENTS -->
          <div class="mobile-form-element">
            <label class="block text-sm font-semibold mobile-label flex items-center gap-1" :class="entry.isDisplayed ? '' : 'text-gray-400'">
              <i data-feather="check-circle" class="w-4 h-4 text-green-600"></i> ACCOMPLISHMENTS
            </label>
            <template x-for="(accomp, accIndex) in entry.accomplishments" :key="accIndex">
              <div class="mobile-list-item flex gap-2 mb-2">
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][accomplishments][' + accIndex + ']'" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.accomplishments[accIndex]">
                <button type="button" @click="entry.accomplishments.splice(accIndex, 1)" :disabled="!entry.isDisplayed" class="mobile-remove-btn bg-red-600 text-white px-2 py-1 rounded hover:bg-red-800 disabled:bg-gray-400">
                  âœ•
                </button>
              </div>
            </template>
            <div class="text-right">
              <button type="button" @click="entry.accomplishments.push('')" :disabled="!entry.isDisplayed" class="mobile-add-btn bg-[#002C76] text-white px-3 py-2 rounded hover:bg-blue-800 disabled:bg-gray-400">+ Add Accomplishment</button>
            </div>
          </div>

          <!-- DUTIES -->
          <div class="mobile-form-element">
            <label class="block text-sm font-semibold mobile-label flex items-center gap-1" :class="entry.isDisplayed ? '' : 'text-gray-400'">
              <i data-feather="tool" class="w-4 h-4 text-blue-600"></i> DUTIES
            </label>
            <template x-for="(duty, dutyIndex) in entry.duties" :key="dutyIndex">
              <div class="mobile-list-item flex gap-2 mb-2">
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][duties][' + dutyIndex + ']'" class="mobile-input w-full border rounded px-3 py-2" x-model="entry.duties[dutyIndex]">
                <button type="button" @click="entry.duties.splice(dutyIndex, 1)" :disabled="!entry.isDisplayed" class="mobile-remove-btn bg-red-600 text-white px-2 py-1 rounded hover:bg-red-800 disabled:bg-gray-400">
                  âœ•
                </button>
              </div>
            </template>
            <div class="text-right">
              <button type="button" @click="entry.duties.push('')" :disabled="!entry.isDisplayed" class="mobile-add-btn bg-[#002C76] text-white px-3 py-2 rounded hover:bg-blue-800 disabled:bg-gray-400">+ Add Duty</button>
            </div>
          </div>

          <!-- Remove Entry -->
          <div class="text-right mt-4">
            <button type="button" @click="entries.splice(index, 1)" class="mobile-button bg-red-600 text-white px-4 py-2 rounded hover:bg-red-800" x-show="entries.length > 1">
              <i data-feather="trash-2" class="inline w-4 h-4"></i> Remove Entry
            </button>
          </div>
        </fieldset>
      </div>
    </template>

    <!-- Add Entry Button -->
    <div class="text-right mb-6">
      <button type="button" @click="entries.push({ start_date: '', end_date: '', position: '', office: '', supervisor: '', agency: '', accomplishments: [''], duties: [''], isDisplayed: true, present: null })" class="mobile-button bg-[#002C76] text-white font-semibold px-4 py-2 rounded hover:bg-blue-800 flex items-center gap-1 w-full sm:w-auto justify-center">
        <i data-feather="plus-circle" class="w-5 h-5"></i> Add Work Entry
      </button>
    </div>
  </main>

  <!-- Footer Buttons -->
  <div class="mobile-footer max-w-full md:max-w-5xl mx-auto mt-6 flex flex-col md:flex-row gap-4 justify-between items-center">
    <button type="button" onclick="window.location.href='{{ route('dashboard_user') }}'" class="mobile-button use-loader w-full md:w-auto bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 flex items-center gap-1 justify-center">
      <i data-feather="arrow-left" class="w-4 h-4"></i> Back to Dashboard
    </button>

    <div class="mobile-button-group flex flex-col sm:flex-row gap-4 w-full md:w-auto justify-end">
      <button type="button" onclick="submitWithDownload()" class="mobile-button use-loader bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900 flex items-center gap-1 justify-center">
        <i data-feather="download" class="w-4 h-4"></i> Download WES
      </button>
      <button type="submit" onclick="document.getElementById('after_action').value = 'save';" class="mobile-button bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-1 justify-center">
        <i data-feather="save" class="w-4 h-4"></i> Save
      </button>
    </div>
  </div>
</form>

<!-- Feather Icons -->
<script>
  feather.replace();
  function submitWithDownload() {
    const actionField = document.getElementById('after_action');
    const form = document.getElementById('workExperienceForm');
    if (!actionField || !form) return showAppToast('Form not found.');
    actionField.value = 'download';
    form.submit();
  }
</script>

@if (session('after_action') === 'download')
<script>
  window.onload = function () {
    window.location.href = "{{ route('wes.preview') }}";
  };
</script>
@endif

@include('partials.loader')
</body>
</html>

@extends('layout.pds_layout')
@section('title', 'Work Experience Sheet')
@section('content')
  @php
    $isSimpleMode = request()->boolean('simple');
    $isOpenDocs   = request()->boolean('open_docs');
    $wesAfterAction = $isSimpleMode ? 'preview' : 'next';
  @endphp
  <form id="workExperienceForm" action="{{ route('work_experience_store') }}" method="POST">
    @csrf
    <input type="hidden" name="simple_mode" value="{{ $isSimpleMode ? 1 : 0 }}">
    <input type="hidden" name="after_action" id="after_action" value="{{ $wesAfterAction }}">

    <main class="max-w-full md:max-w-6xl mx-auto bg-white border border-gray-200 p-6 md:p-8 rounded-2xl shadow-lg" x-data="{
                entries: (() => {
                  let data = {{ old('entries') ? json_encode(old('entries')) : ($workEntries->isEmpty() ? json_encode([['start_date' => '', 'end_date' => '', 'position' => '', 'office' => '', 'supervisor' => '', 'agency' => '', 'accomplishments' => [''], 'duties' => [''], 'isDisplayed' => true,]]) : $workEntries->toJson()) }};
                  return data.map(entry => ({
                    ...entry,
                    start_date: entry.start_date ? new Date(entry.start_date).toLocaleDateString('en-CA').split('T')[0] : '',
                    end_date: entry.end_date ? new Date(entry.end_date).toLocaleDateString('en-CA').split('T')[0] : '',
                    present: entry.end_date === null,
                  }));
                })(),
              }" id="workSheet">

      <div class="text-center mb-6">
        <p class="text-xs md:text-sm italic text-slate-700">Attachment to CS Form No. 212</p>
        <h2 class="text-2xl md:text-3xl font-bold text-[#002C76]">WORK EXPERIENCE SHEET</h2>
      </div>

      <section class="bg-slate-50 border border-slate-200 rounded-xl p-4 md:p-5 mb-8 text-sm text-gray-700">
        <div class="font-semibold text-slate-800 mb-2">Instructions:</div>
        <ol class="list-decimal pl-5 space-y-1">
          <li>Include only the work experiences relevant to the position being applied to.</li>
          <li>The duration should include start and finish dates, if known month in abbreviated form, if known, and year in full. For the current position, use the word Present. Work experience should be listed from most recent first.</li>
        </ol>
        <p class="mt-2 text-xs md:text-sm"><span class="font-semibold underline">Sample:</span> If applying to Supervising Administrative Officer (Human Resource Management Officer IV)</p>
      </section>

      <template x-for="(entry, index) in entries" :key="index">
        <section class="mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-blue-50 text-[#002C76] flex items-center justify-center font-bold"
                x-text="index + 1"></div>
              <div>
                <div class="text-sm font-semibold text-slate-700">Entry</div>
                <label class="flex items-center gap-2 text-xs text-slate-500">
                  <input type="checkbox" x-model="entry.isDisplayed">
                  <span x-text="entry.isDisplayed ? 'Shown when exported' : 'Hidden at export'"></span>
                </label>
              </div>
            </div>
            <button type="button" @click="entries.splice(index, 1)"
              class="text-xs font-semibold text-red-600 hover:text-red-700" x-show="entries.length > 1">
              Remove
            </button>
            <input type="hidden" :name="'entries[' + index + '][isDisplayed]'" :value="entry.isDisplayed ? 1 : 0">
          </div>

          <fieldset :class="entry.isDisplayed ? '' : 'bg-slate-50 text-slate-400 cursor-not-allowed'"
            class="rounded-xl border border-slate-200 p-4 md:p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Duration</label>
                <div class="flex flex-col sm:flex-row gap-2">
                  <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][start_date]'" type="text"
                    class="wes-date h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.start_date"
                    placeholder="Start date">
                  <span class="text-center sm:pt-2 text-slate-400">to</span>
                  <div class="flex flex-col w-full">
                    <input :class="entry.present ? 'text-slate-400' : ''" :readonly="!entry.isDisplayed || entry.present"
                      :disabled="!entry.isDisplayed || entry.present" :name="'entries[' + index + '][end_date]'" type="text"
                      class="wes-date h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.end_date"
                      placeholder="End date">
                    <input type="hidden" :name="'entries[' + index + '][present]'" :value="entry.present ? 1 : 0">
                    <label class="text-xs mt-1 text-slate-500 flex items-center gap-2">
                      <input type="checkbox" x-model="entry.present" @change="if (entry.present) { entry.end_date = '' }">
                      Present
                    </label>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Position</label>
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][position]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.position"
                  placeholder="e.g. Administrative Officer">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Name of Office/Unit</label>
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][office]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.office"
                  placeholder="Office or unit">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Immediate Supervisor</label>
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][supervisor]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.supervisor"
                  placeholder="Supervisor name">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Name of Agency/Organization and Location</label>
                <input :readonly="!entry.isDisplayed" :name="'entries[' + index + '][agency]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.agency"
                  placeholder="Agency or location">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">List of Accomplishments and Contributions (if any)</label>
                <template x-for="(accomp, accIndex) in entry.accomplishments" :key="accIndex">
                  <div class="flex gap-2 mb-2">
                    <input :readonly="!entry.isDisplayed"
                      :name="'entries[' + index + '][accomplishments][' + accIndex + ']'"
                      class="h-10 w-full border border-slate-200 rounded-lg px-3 py-2"
                      x-model="entry.accomplishments[accIndex]" placeholder="Achievement or outcome">
                    <button type="button" @click="entry.accomplishments.splice(accIndex, 1)"
                      :disabled="!entry.isDisplayed" class="border-2 border-red-600 hover:bg-white bg-red-600 hover:text-red-700 text-white 
                       px-4 py-2 rounded-md text-sm flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </template>
                <button type="button" @click="entry.accomplishments.push('')" :disabled="!entry.isDisplayed"
                  class="border-2 border-[#002C76] bg-[#002C76] hover:bg-white text-sm hover:text-[#002C76] 
                      text-white px-4 py-2 rounded-md flex items-center gap-2">
                  + Add accomplishment
                </button>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Summary of Actual Duties</label>
                <template x-for="(duty, dutyIndex) in entry.duties" :key="dutyIndex">
                  <div class="flex gap-2 mb-2">
                    <input :readonly="!entry.isDisplayed"
                      :name="'entries[' + index + '][duties][' + dutyIndex + ']'"
                      class="h-10 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.duties[dutyIndex]"
                      placeholder="Key responsibility">
                    <button type="button" @click="entry.duties.splice(dutyIndex, 1)" :disabled="!entry.isDisplayed"
                      class="border-2 border-red-600 hover:bg-white bg-red-600 hover:text-red-700 text-white 
                       px-4 py-2 rounded-md flex text-sm items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </template>
                <button type="button" @click="entry.duties.push('')" :disabled="!entry.isDisplayed"
                  class="border-2 border-[#002C76] bg-[#002C76] hover:bg-white text-sm hover:text-[#002C76] 
                      text-white px-4 py-2 rounded-md flex items-center gap-2">
                  + Add duty
                </button>
              </div>
            </div>
          </fieldset>
        </section>
      </template>

      <div class="text-right mb-6">
        <button type="button"
          @click="entries.push({ start_date: '', end_date: '', position: '', office: '', supervisor: '', agency: '', accomplishments: [''], duties: [''], isDisplayed: true, present: null })"
          class="bg-[#002C76] text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 w-full sm:w-auto">
          + Add work entry
        </button>
      </div>

      <div class="mt-8 pt-6 border-t border-slate-200">
        <div class="max-w-md ml-auto text-center text-slate-700">
          <div class="border-t border-slate-600 pt-1 text-sm">(Signature over Printed Name of Employee/Applicant)</div>
          <div class="mt-6 text-left text-sm">Date: ____________________</div>
        </div>
      </div>
    </main>

    <div class="max-w-full md:max-w-5xl mx-auto mt-6 flex flex-col md:flex-row gap-4 justify-between items-center">
      <button type="button" onclick="window.location.href='{{ route('display_c4', ['simple' => 1]) }}'"
        class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
        <span class="material-icons mr-2">arrow_back</span>
        Previous
      </button>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto justify-end">
        <button type="submit" onclick="document.getElementById('after_action').value = '{{ $wesAfterAction }}';"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1 justify-center">
          Save
        </button>
      </div>
    </div>
  </form>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      function initWESDates() {
        document.querySelectorAll('.wes-date').forEach(function (el) {
          if (!el.classList.contains('flatpickr-input')) {
            flatpickr(el, { dateFormat: 'Y-m-d', allowInput: true });
          }
        });
      }
      initWESDates();
      const root = document.getElementById('workSheet');
      if (root) {
        const observer = new MutationObserver(initWESDates);
        observer.observe(root, { childList: true, subtree: true });
      }
    });
  </script>
  <script>
    (function () {
      function initAutosave() {
      const form = document.getElementById('workExperienceForm');
      if (!form) return;

      const autosaveUrl = @json(route('pds.autosave', ['section' => 'wes']));
      const AUTOSAVE_INTERVAL_MS = 15000;
      let isDirty = false;
      let isSubmitting = false;
      let inFlight = false;
      let queued = false;

      const markDirty = () => { isDirty = true; };
      form.addEventListener('input', markDirty);
      form.addEventListener('change', markDirty);
      form.addEventListener('click', (event) => {
        if (event.target.closest('button[type="button"], input[type="checkbox"]')) {
          markDirty();
        }
      });
      form.addEventListener('submit', () => { isSubmitting = true; });

      async function saveDraft(force = false) {
        if (isSubmitting) return;
        if (!force && !isDirty) return;
        if (inFlight) {
          queued = true;
          return;
        }

        inFlight = true;
        try {
          const formData = new FormData(form);
          const response = await fetch(autosaveUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
          });
          if (response.ok) {
            isDirty = false;
          }
        } catch (error) {
          // Keep normal submit as fallback.
        } finally {
          inFlight = false;
          if (queued) {
            queued = false;
            saveDraft(true);
          }
        }
      }

      const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

      async function flushDraftNow(options = {}) {
        const force = options.force === true;
        const parsedMaxWaitMs = Number(options.maxWaitMs);
        const maxWaitMs = Number.isFinite(parsedMaxWaitMs) && parsedMaxWaitMs > 0
          ? parsedMaxWaitMs
          : 1200;
        const startedAt = Date.now();

        while (inFlight && (Date.now() - startedAt) < maxWaitMs) {
          if (force) {
            queued = true;
          }
          await sleep(80);
        }

        if (inFlight) {
          return false;
        }

        await saveDraft(force);

        while ((inFlight || queued) && (Date.now() - startedAt) < maxWaitMs) {
          if (!inFlight && queued) {
            queued = false;
            await saveDraft(force);
            continue;
          }
          await sleep(80);
        }

        return !(inFlight || queued);
      }

      window.__pdsAutosaveNow = flushDraftNow;

      setInterval(() => saveDraft(false), AUTOSAVE_INTERVAL_MS);

      document.addEventListener('visibilitychange', () => {
        if (document.hidden && isDirty) {
          saveDraft(true);
        }
      });

      window.addEventListener('pagehide', () => {
        if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
        const formData = new FormData(form);
        navigator.sendBeacon(autosaveUrl, formData);
      });

      window.addEventListener('beforeunload', () => {
        if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
        const formData = new FormData(form);
        navigator.sendBeacon(autosaveUrl, formData);
      });
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutosave, { once: true });
      } else {
        initAutosave();
      }
    })();
  </script>
  @include('partials.loader')
@endsection


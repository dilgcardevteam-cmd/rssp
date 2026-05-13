@extends('layout.pds_layout')
@section('title', 'Work Experience Sheet')
@section('content')
  @php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
    $isSimpleMode = request()->boolean('simple');
    $isOpenDocs   = request()->boolean('open_docs');
    $wesAfterAction = $isSimpleMode ? 'preview' : 'next';
  @endphp
  <style>
    .wes-page {
      position: relative;
      color: #163053;
    }

    .wes-page::before {
      content: '';
      position: fixed;
      inset: 0;
      z-index: -1;
      background:
        radial-gradient(circle at top left, rgba(13, 91, 215, 0.14), transparent 28%),
        radial-gradient(circle at top right, rgba(0, 44, 118, 0.08), transparent 24%),
        linear-gradient(180deg, #f7faff 0%, #edf3fb 100%);
    }

    .wes-banner {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      margin-bottom: 1rem;
      padding: 1rem 1.1rem;
      border: 1px solid rgba(164, 188, 227, 0.45);
      border-radius: 1.25rem;
      background: linear-gradient(135deg, rgba(0, 44, 118, 0.92) 0%, rgba(17, 94, 201, 0.9) 100%);
      color: #fff;
      box-shadow: 0 18px 40px rgba(14, 36, 82, 0.18);
    }

    .wes-banner p {
      margin: 0;
      color: rgba(255, 255, 255, 0.82);
    }

    .wes-banner-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 0.6rem;
    }

    .wes-banner-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.42rem 0.75rem;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.18);
      background: rgba(255, 255, 255, 0.12);
      font-size: 0.78rem;
      line-height: 1.1;
    }

    .wes-sheet {
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(153, 176, 214, 0.32) !important;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 255, 0.96) 100%);
      box-shadow: 0 16px 40px rgba(15, 36, 79, 0.08), 0 2px 8px rgba(15, 36, 79, 0.04) !important;
    }

    .wes-sheet::before {
      content: '';
      position: absolute;
      inset: 0 0 auto;
      height: 4px;
      background: linear-gradient(90deg, #002c76 0%, #2563eb 56%, #7fb2ff 100%);
    }

    .wes-title-block {
      border-bottom: 1px solid #dbe7f7;
      padding-bottom: 1rem;
    }

    .wes-instructions {
      border: 1px solid #d8e4f8 !important;
      background: linear-gradient(180deg, #f8fbff 0%, #f1f6ff 100%) !important;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .wes-entry-shell {
      border: 1px solid #d8e4f8;
      border-radius: 1rem;
      background: linear-gradient(180deg, rgba(248, 251, 255, 0.94) 0%, rgba(255, 255, 255, 0.98) 100%);
      padding: 1rem;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .wes-entry-header {
      padding-bottom: 0.9rem;
      margin-bottom: 0.9rem;
      border-bottom: 1px solid #e2eaf6;
    }

    .wes-entry-number {
      width: 2.4rem;
      height: 2.4rem;
      border-radius: 999px;
      background: linear-gradient(135deg, #e6efff 0%, #f7faff 100%);
      color: #002c76;
      box-shadow: inset 0 0 0 1px rgba(115, 151, 210, 0.22);
    }

    .wes-fieldset {
      border-color: #d8e4f8 !important;
      background: rgba(255, 255, 255, 0.72);
    }

    .wes-page input[type='text'],
    .wes-page input[type='date'],
    .wes-page textarea {
      border-color: #d3deef !important;
      background: rgba(255, 255, 255, 0.96);
      box-shadow: 0 1px 2px rgba(15, 36, 79, 0.03);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .wes-page input[type='text']:hover,
    .wes-page input[type='date']:hover,
    .wes-page textarea:hover {
      border-color: #b8cae7 !important;
    }

    .wes-page input[type='text']:focus,
    .wes-page input[type='date']:focus,
    .wes-page textarea:focus {
      border-color: #0d5bd7 !important;
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
      outline: none;
    }

    .wes-primary-action,
    .wes-secondary-action,
    .wes-submit-button,
    .wes-back-button {
      border-radius: 0.95rem !important;
      box-shadow: 0 12px 24px rgba(0, 44, 118, 0.14);
    }

    .wes-secondary-action {
      box-shadow: 0 10px 22px rgba(15, 36, 79, 0.08);
    }

    .wes-signature-card {
      border: 1px solid #d8e4f8;
      border-radius: 1rem;
      background: linear-gradient(180deg, rgba(248, 251, 255, 0.94) 0%, rgba(255, 255, 255, 0.98) 100%);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .wes-submit-bar {
      position: sticky;
      bottom: 1rem;
      z-index: 20;
      padding: 1rem;
      border: 1px solid rgba(162, 183, 218, 0.4);
      border-radius: 1.15rem;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      box-shadow: 0 18px 40px rgba(15, 36, 79, 0.12);
    }

    .wes-submit-button {
      background: linear-gradient(135deg, #0d5bd7 0%, #002c76 100%) !important;
    }
  </style>
  <form id="workExperienceForm" action="{{ route('work_experience_store') }}" method="POST">
    @csrf
    <input type="hidden" name="simple_mode" value="{{ $isSimpleMode ? 1 : 0 }}">
    <input type="hidden" name="after_action" id="after_action" value="{{ $wesAfterAction }}">

    <main class="wes-page {{ $simple ? 'w-full max-w-none' : 'max-w-full md:max-w-6xl mx-auto' }}" x-data="{
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

      <div class="wes-banner">
        <div>
          <strong class="text-lg sm:text-xl font-semibold">Complete the Work Experience Sheet with relevant employment details.</strong>
          <p>The add/remove entry flow and autosave behavior remain the same. This update only improves the page presentation.</p>
        </div>
        <div class="wes-banner-meta">
          <span class="wes-banner-chip">
            <span class="material-icons text-sm">work_history</span>
            Relevant work entries
          </span>
          <span class="wes-banner-chip">
            <span class="material-icons text-sm">assignment</span>
            Duties and accomplishments
          </span>
        </div>
      </div>

      <div class="wes-sheet {{ $simple ? 'w-full max-w-none' : 'max-w-full md:max-w-6xl mx-auto' }} bg-white p-6 md:p-8 rounded-2xl">
      <div class="wes-title-block text-center mb-6">
        <p class="text-xs md:text-sm italic text-slate-700">Attachment to CS Form No. 212</p>
        <h2 class="text-2xl md:text-3xl font-bold text-[#002C76]">WORK EXPERIENCE SHEET</h2>
      </div>

      <section class="wes-instructions bg-slate-50 border border-slate-200 rounded-xl p-4 md:p-5 mb-8 text-sm text-gray-700">
        <div class="font-semibold text-slate-800 mb-2">Instructions:</div>
        <ol class="list-decimal pl-5 space-y-1">
          <li>Include only the work experiences relevant to the position being applied to.</li>
          <li>The duration should include start and finish dates, if known month in abbreviated form, if known, and year in full. For the current position, use the word Present. Work experience should be listed from most recent first.</li>
        </ol>
        <p class="mt-2 text-xs md:text-sm"><span class="font-semibold underline">Sample:</span> If applying to Supervising Administrative Officer (Human Resource Management Officer IV)</p>
      </section>

      <template x-for="(entry, index) in entries" :key="index">
        <section class="wes-entry-shell mb-6">
          <div class="wes-entry-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
              <div class="wes-entry-number flex items-center justify-center font-bold"
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
            class="wes-fieldset rounded-xl border border-slate-200 p-4 md:p-5 space-y-4">
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
                  class="wes-primary-action border-2 border-[#002C76] bg-[#002C76] hover:bg-white text-sm hover:text-[#002C76] 
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
                  class="wes-primary-action border-2 border-[#002C76] bg-[#002C76] hover:bg-white text-sm hover:text-[#002C76] 
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
          class="wes-primary-action bg-[#002C76] text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 w-full sm:w-auto">
          + Add work entry
        </button>
      </div>

      <div class="wes-signature-card mt-8 pt-6 p-5 border-t border-slate-200">
        <div class="max-w-md ml-auto text-center text-slate-700">
          <div class="border-t border-slate-600 pt-1 text-sm">(Signature over Printed Name of Employee/Applicant)</div>
          <div class="mt-6 text-left text-sm">Date: ____________________</div>
        </div>
      </div>
      </div>
    </main>

    <div class="wes-submit-bar {{ $simple ? 'w-full max-w-none' : 'max-w-full md:max-w-6xl mx-auto' }} mt-6 flex flex-col md:flex-row gap-4 justify-between items-center">
      <button type="button" onclick="window.location.href='{{ route('display_c4', ['simple' => 1]) }}'"
        class="wes-back-button use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
        <span class="material-icons mr-2">arrow_back</span>
        Previous
      </button>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto justify-end">
        <button type="submit" onclick="document.getElementById('after_action').value = '{{ $wesAfterAction }}';"
          class="wes-submit-button bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1 justify-center">
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
      const AUTOSAVE_DEBOUNCE_MS = 600;
      let isDirty = false;
      let isSubmitting = false;
      let inFlight = false;
      let queued = false;
      let autosaveTimer = null;

      const scheduleAutosave = () => {
        if (isSubmitting) return;
        window.clearTimeout(autosaveTimer);
        autosaveTimer = window.setTimeout(() => {
          saveDraft(false);
        }, AUTOSAVE_DEBOUNCE_MS);
      };

      const markDirty = () => {
        isDirty = true;
        scheduleAutosave();
      };
      form.addEventListener('input', markDirty);
      form.addEventListener('change', markDirty);
      form.addEventListener('click', (event) => {
        if (event.target.closest('button[type="button"], input[type="checkbox"]')) {
          markDirty();
        }
      });
      form.addEventListener('submit', () => {
        isSubmitting = true;
        window.clearTimeout(autosaveTimer);
      });

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
          window.clearTimeout(autosaveTimer);
          saveDraft(true);
        }
      });

      window.addEventListener('pagehide', () => {
        window.clearTimeout(autosaveTimer);
        if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
        const formData = new FormData(form);
        navigator.sendBeacon(autosaveUrl, formData);
      });

      window.addEventListener('beforeunload', () => {
        window.clearTimeout(autosaveTimer);
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

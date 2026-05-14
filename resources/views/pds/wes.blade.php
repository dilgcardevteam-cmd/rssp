@extends('layout.pds_layout')
@section('title', 'Work Experience Sheet')
@section('content')
  @php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
    $isSimpleMode = request()->boolean('simple');
    $isOpenDocs   = request()->boolean('open_docs');
    $wesAfterAction = $isSimpleMode ? 'stay' : 'next';
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
      background: linear-gradient(135deg, #001a45 0%, #002c76 58%, #0b4ea8 100%);
      color: #fff;
      box-shadow: 0 18px 40px rgba(14, 36, 82, 0.18);
    }

    .wes-banner p {
      margin: 0;
      color: rgba(255, 255, 255, 0.82);
    }

    .wes-banner-title {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
    }

    .wes-banner-title .material-icons {
      font-size: 1.8rem;
      color: rgba(255, 255, 255, 0.96);
    }

    .wes-banner-title strong {
      font-size: clamp(1.2rem, 1rem + 0.65vw, 1.7rem);
      line-height: 1.1;
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

    .wes-preview-fab {
      position: fixed;
      right: 1.25rem;
      bottom: 1.25rem;
      z-index: 70;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.55rem;
      min-height: 3.5rem;
      padding: 0.9rem 1.2rem;
      border: 1px solid rgba(189, 213, 255, 0.35);
      border-radius: 999px;
      background: linear-gradient(135deg, #002c76 0%, #0d5bd7 100%);
      color: #fff;
      font-size: 0.9rem;
      font-weight: 700;
      line-height: 1;
      box-shadow: 0 18px 36px rgba(7, 26, 67, 0.28);
      backdrop-filter: blur(12px);
      transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease, background 0.2s ease;
      touch-action: none;
      cursor: grab;
      user-select: none;
    }

    .wes-preview-fab:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 22px 42px rgba(7, 26, 67, 0.34);
    }

    .wes-preview-fab.is-dragging,
    .wes-preview-fab:active {
      cursor: grabbing;
    }

    .wes-preview-fab:disabled {
      cursor: not-allowed;
      opacity: 0.72;
      background: linear-gradient(135deg, #8a97ad 0%, #a7b2c6 100%);
      box-shadow: 0 10px 22px rgba(15, 36, 79, 0.12);
      transform: none;
    }

    @media (max-width: 640px) {
      .wes-preview-fab {
        left: 1rem;
        right: 1rem;
        bottom: calc(6.25rem + env(safe-area-inset-bottom, 0px));
        width: auto;
      }
    }
  </style>
  <form id="workExperienceForm" action="{{ route('work_experience_store') }}" method="POST">
    @csrf
    <input type="hidden" name="simple_mode" value="{{ $isSimpleMode ? 1 : 0 }}">
    <input type="hidden" name="open_docs_mode" value="{{ $isOpenDocs ? 1 : 0 }}">
    <input type="hidden" name="after_action" id="after_action" value="{{ $wesAfterAction }}">

    <main class="wes-page {{ $simple ? 'w-full max-w-none' : 'max-w-full md:max-w-6xl mx-auto' }}" x-data="{
                draftKey: 'dilg-car:pds:wes:draft:v1',
                normalizeEntries(rows) {
                  const source = Array.isArray(rows) && rows.length ? rows : [{
                    start_date: '',
                    end_date: '',
                    position: '',
                    office: '',
                    supervisor: '',
                    agency: '',
                    accomplishments: [''],
                    duties: [''],
                    isDisplayed: true,
                  }];

                  return source.map((entry) => {
                    const startDate = entry.start_date ? new Date(entry.start_date).toLocaleDateString('en-CA').split('T')[0] : '';
                    const endDate = entry.end_date ? new Date(entry.end_date).toLocaleDateString('en-CA').split('T')[0] : '';
                    const accomplishments = Array.isArray(entry.accomplishments) && entry.accomplishments.length ? entry.accomplishments : [''];
                    const duties = Array.isArray(entry.duties) && entry.duties.length ? entry.duties : [''];
                    const isDisplayed = entry.isDisplayed === false || entry.isDisplayed === 0 || entry.isDisplayed === '0' ? false : true;

                    return {
                      ...entry,
                      start_date: startDate,
                      end_date: endDate,
                      accomplishments,
                      duties,
                      isDisplayed,
                      present: entry.present === true || entry.present === 1 || entry.present === '1' || entry.end_date === null,
                    };
                  });
                },
                loadEntries() {
                  const serverData = {{ old('entries') ? json_encode(old('entries')) : ($workEntries->isEmpty() ? json_encode([['start_date' => '', 'end_date' => '', 'position' => '', 'office' => '', 'supervisor' => '', 'agency' => '', 'accomplishments' => [''], 'duties' => [''], 'isDisplayed' => true,]]) : $workEntries->toJson()) }};
                  try {
                    const saved = window.localStorage.getItem(this.draftKey);
                    if (saved) {
                      const parsed = JSON.parse(saved);
                      if (Array.isArray(parsed?.entries)) {
                        return this.normalizeEntries(parsed.entries);
                      }
                    }
                  } catch (_) {
                    // Ignore malformed local drafts and fall back to server state.
                  }
                  return this.normalizeEntries(serverData);
                },
                persistDraft() {
                  try {
                    window.localStorage.setItem(this.draftKey, JSON.stringify({ entries: this.entries }));
                  } catch (_) {
                    // Ignore storage write failures.
                  }
                },
                entries: [],
              }" x-init="entries = loadEntries(); $watch('entries', () => persistDraft())" id="workSheet">

      <div class="wes-banner">
        <div>
          <div class="wes-banner-title">
            <span class="material-icons">work_history</span>
            <strong class="font-semibold">Work Experience Sheet</strong>
          </div>
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
                <div class="text-base md:text-lg font-semibold text-slate-700" x-text="(() => { const title = [(entry.position || '').trim(), (entry.agency || '').trim()].filter(Boolean).join(' - ') || 'Position - Name of Agency'; const start = (entry.start_date || '').trim() || 'Start Date'; const end = entry.present ? 'Present' : ((entry.end_date || '').trim() || 'End Date'); return `${title} (${start} to ${end})`; })()"></div>
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
        <button type="button" id="wesSaveBtn"
          class="wes-submit-button bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1 justify-center">
          Save
        </button>
      </div>
    </div>
  </form>

  <div id="wesSaveSuccessModal" class="hidden fixed inset-0 z-[110] bg-black bg-opacity-50 p-4 flex items-center justify-center">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl border border-blue-100">
      <div class="bg-gradient-to-br from-[#001a45] via-[#002c76] to-[#0b4ea8] px-6 py-5 text-white">
        <div class="flex items-center gap-3">
          <span class="material-icons flex h-11 w-11 items-center justify-center rounded-full bg-white/15 text-2xl ring-1 ring-white/25">task_alt</span>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">Saved Successfully</p>
            <h3 class="text-xl font-bold leading-tight">Work Experience Sheet Completed</h3>
          </div>
        </div>
      </div>
      <div class="px-6 py-5">
        <div class="rounded-xl border border-blue-100 bg-blue-50/70 px-4 py-3">
          <p class="text-sm leading-relaxed text-slate-700">
            Your Work Experience Sheet changes were saved. Use the Preview button any time to review the latest WES.
          </p>
        </div>
        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <button type="button" id="wesSaveSuccessClose" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#002c76] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/20 hover:bg-[#001f54]">
            <span class="material-icons text-base">check</span>
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <button
    type="button"
    id="wesPreviewBtn"
    class="wes-preview-fab"
    aria-controls="wesPreviewOverlay"
    aria-haspopup="dialog"
  >
    <span class="material-icons !text-base">visibility</span>
    WES Preview
  </button>

  <div id="wesPreviewOverlay" class="hidden fixed inset-0 z-[100] bg-black bg-opacity-50 p-4 sm:p-8 flex items-center justify-center">
    <div class="bg-white w-full max-w-6xl h-[90vh] overflow-hidden rounded-xl shadow-2xl flex flex-col">
      <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b shrink-0">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Work Experience Sheet Preview</h3>
        <button id="wesPreviewClose" class="p-2 rounded hover:bg-gray-100">
          <span class="material-icons">close</span>
        </button>
      </div>
      <div class="p-4 sm:p-6 flex-1 min-h-0">
        <div class="mb-3 text-xs text-gray-500">Preview is rendered from the PDF template and auto-filled from your saved WES data.</div>
        <div class="w-full h-[calc(100%-1.75rem)] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
          <iframe
            id="wesPdfPreviewFrame"
            title="WES PDF Preview"
            src="about:blank"
            data-preview-src="{{ route('wes.preview', ['embedded' => 1]) }}"
            scrolling="no"
            class="w-full h-full"
          ></iframe>
        </div>
      </div>
    </div>
  </div>

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
  <script>
    (function () {
      function initWesPreview() {
        const openBtn = document.getElementById('wesPreviewBtn');
        const overlay = document.getElementById('wesPreviewOverlay');
        const closeBtn = document.getElementById('wesPreviewClose');
        const frame = document.getElementById('wesPdfPreviewFrame');
        if (!openBtn || !overlay || !closeBtn || !frame) return;

        const closeOverlay = () => overlay.classList.add('hidden');

        openBtn.addEventListener('click', async () => {
          const lastDragAt = Number(openBtn.dataset.lastDragAt || '0');
          if (Date.now() - lastDragAt < 250) {
            return;
          }

          if (openBtn.disabled) return;

          const originalText = openBtn.innerHTML;
          openBtn.innerHTML = '<span class="material-icons text-sm animate-spin">autorenew</span>Preparing...';
          openBtn.disabled = true;

          try {
            if (typeof window.__pdsAutosaveNow === 'function') {
              await window.__pdsAutosaveNow({ force: true, maxWaitMs: 5000 });
            }

            const previewSrc = frame.dataset.previewSrc || @json(route('wes.preview'));
            const separator = previewSrc.includes('?') ? '&' : '?';
            frame.src = previewSrc + separator + 'ts=' + Date.now();
            overlay.classList.remove('hidden');
          } finally {
            openBtn.innerHTML = originalText;
            openBtn.disabled = false;
          }
        });

        closeBtn.addEventListener('click', closeOverlay);
        overlay.addEventListener('click', (event) => {
          if (event.target === overlay) {
            closeOverlay();
          }
        });
        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
            closeOverlay();
          }
        });
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWesPreview, { once: true });
      } else {
        initWesPreview();
      }
    })();
  </script>
  <script>
    (function () {
      function initDraggableWesPreviewButton() {
        const button = document.getElementById('wesPreviewBtn');
        if (!button) return;

        let isPointerDown = false;
        let isDragging = false;
        let pointerId = null;
        let startX = 0;
        let startY = 0;
        let originLeft = 0;
        let originTop = 0;
        let suppressClick = false;

        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

        const getViewportBounds = () => {
          const rect = button.getBoundingClientRect();
          return {
            minLeft: 8,
            minTop: 8,
            maxLeft: Math.max(8, window.innerWidth - rect.width - 8),
            maxTop: Math.max(8, window.innerHeight - rect.height - 8),
          };
        };

        const applyPosition = (left, top) => {
          button.style.left = `${left}px`;
          button.style.top = `${top}px`;
          button.style.right = 'auto';
          button.style.bottom = 'auto';
        };

        const syncToViewport = () => {
          const rect = button.getBoundingClientRect();
          const bounds = getViewportBounds();
          applyPosition(
            clamp(rect.left, bounds.minLeft, bounds.maxLeft),
            clamp(rect.top, bounds.minTop, bounds.maxTop)
          );
        };

        button.addEventListener('pointerdown', (event) => {
          if (event.button !== 0) return;

          const rect = button.getBoundingClientRect();
          isPointerDown = true;
          isDragging = false;
          pointerId = event.pointerId;
          startX = event.clientX;
          startY = event.clientY;
          originLeft = rect.left;
          originTop = rect.top;

          applyPosition(originLeft, originTop);
          button.setPointerCapture(pointerId);
        });

        button.addEventListener('pointermove', (event) => {
          if (!isPointerDown || event.pointerId !== pointerId) return;

          const deltaX = event.clientX - startX;
          const deltaY = event.clientY - startY;

          if (!isDragging && (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4)) {
            isDragging = true;
            button.classList.add('is-dragging');
          }

          if (!isDragging) return;

          event.preventDefault();
          const bounds = getViewportBounds();
          applyPosition(
            clamp(originLeft + deltaX, bounds.minLeft, bounds.maxLeft),
            clamp(originTop + deltaY, bounds.minTop, bounds.maxTop)
          );
        });

        button.addEventListener('pointerup', (event) => {
          if (event.pointerId !== pointerId) return;

          if (isDragging) {
            event.preventDefault();
            event.stopPropagation();
            suppressClick = true;
            button.dataset.lastDragAt = String(Date.now());
          }

          button.classList.remove('is-dragging');
          isPointerDown = false;
          isDragging = false;
          pointerId = null;
        });

        button.addEventListener('pointercancel', () => {
          button.classList.remove('is-dragging');
          isPointerDown = false;
          isDragging = false;
          pointerId = null;
        });

        button.addEventListener('click', (event) => {
          if (suppressClick) {
            event.preventDefault();
            event.stopPropagation();
            suppressClick = false;
          }
        }, true);

        window.addEventListener('resize', syncToViewport);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDraggableWesPreviewButton, { once: true });
      } else {
        initDraggableWesPreviewButton();
      }
    })();
  </script>
  <script>
    (function () {
      function initWesSaveFlow() {
        const form = document.getElementById('workExperienceForm');
        const saveBtn = document.getElementById('wesSaveBtn');
        const afterAction = document.getElementById('after_action');
        const successModal = document.getElementById('wesSaveSuccessModal');
        const successClose = document.getElementById('wesSaveSuccessClose');
        if (!form || !saveBtn || !afterAction || !successModal || !successClose) return;

        const notify = (message, type = 'error', duration = 6000) => {
          if (!message) return;
          if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
            return;
          }
          if (typeof window.showAppToast === 'function') {
            window.showAppToast(message, type, duration);
          }
        };

        const closeSuccessModal = () => successModal.classList.add('hidden');
        const openSuccessModal = () => successModal.classList.remove('hidden');

        successClose.addEventListener('click', closeSuccessModal);
        successModal.addEventListener('click', (event) => {
          if (event.target === successModal) {
            closeSuccessModal();
          }
        });
        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && !successModal.classList.contains('hidden')) {
            closeSuccessModal();
          }
        });

        let isSaving = false;
        const saveCurrentPage = async () => {
          if (isSaving) return;
          isSaving = true;
          afterAction.value = 'stay';
          const originalText = saveBtn.innerHTML;
          saveBtn.disabled = true;
          saveBtn.innerHTML = '<span class="material-icons text-sm animate-spin">autorenew</span>Saving...';

          try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              },
              credentials: 'same-origin',
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => ({}));
              const errors = payload?.errors ? Object.values(payload.errors).flat() : [];
              notify(errors[0] || payload?.message || 'Unable to save the Work Experience Sheet. Please review the form and try again.');
              return;
            }

            openSuccessModal();
          } catch (error) {
            notify('Unable to save the Work Experience Sheet due to a network or server error. Please try again.');
          } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
            afterAction.value = @json($wesAfterAction);
            isSaving = false;
          }
        };

        saveBtn.addEventListener('click', saveCurrentPage);
        form.addEventListener('submit', (event) => {
          event.preventDefault();
          event.stopImmediatePropagation();
          saveCurrentPage();
        }, true);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWesSaveFlow, { once: true });
      } else {
        initWesSaveFlow();
      }
    })();
  </script>
  <script>
    (function () {
      function initWesErrorToast() {
        const errorMessages = @json($errors->all());
        const sessionError = @json(session('error'));
        const firstError = sessionError || (errorMessages.length ? errorMessages[0] : '');
        if (!firstError) return;

        const message = `${firstError}${errorMessages.length > 1 ? ` (+${errorMessages.length - 1} more)` : ''}`;
        if (typeof window.showNotification === 'function') {
          window.showNotification(message, 'error');
          return;
        }
        if (typeof window.showAppToast === 'function') {
          window.showAppToast(message, 'error', 6000);
        }
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWesErrorToast, { once: true });
      } else {
        initWesErrorToast();
      }
    })();
  </script>
  @include('partials.loader')
@endsection

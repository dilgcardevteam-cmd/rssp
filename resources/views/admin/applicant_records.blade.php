@extends('layout.admin')
@section('title', 'DILG - Applicant Records')

@section('content')
<main class="mx-auto w-full">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Applicant Records</span>
        </h1>
    </section>

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <form id="applicantRecordsForm" method="GET" action="{{ route('admin.applicant_records.index') }}" class="flex w-full flex-col gap-3 lg:flex-row lg:items-end">
                <div class="relative w-full lg:max-w-md">
                    <label for="applicantSearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Search
                    </label>
                    <input id="applicantSearchInput" name="search" type="search" value="{{ $search }}"
                        placeholder="Search applicant name, email, or mobile number"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-11 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                    <svg class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <div id="applicantSearchSpinner" class="pointer-events-none absolute right-3 top-[39px] hidden -translate-y-1/2 text-[#0D2B70]">
                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-[#0D2B70]/20 border-t-[#0D2B70]"></div>
                    </div>
                </div>

                <div class="w-full sm:max-w-[220px]">
                    <label for="applicantSort" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Sort
                    </label>
                    <select id="applicantSort" name="sort"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        <option value="latest" @selected($sort === 'latest')>Latest application</option>
                        <option value="oldest" @selected($sort === 'oldest')>Oldest application</option>
                    </select>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a id="applicantResetBtn" href="{{ route('admin.applicant_records.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>

            <p id="applicantRecordsTotal" class="shrink-0 text-sm font-semibold text-[#0D2B70] xl:pb-2">
                Total: {{ number_format($applicants->total()) }}
            </p>
        </div>
    </section>

    <div id="applicantRecordsResultsWrapper" class="relative mt-4">
        <div id="applicantRecordsLoading" class="pointer-events-none absolute inset-0 z-10 hidden rounded-2xl bg-white/70 backdrop-blur-[1px]">
            <div class="flex h-full items-center justify-center">
                <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-sm">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-[#0D2B70]/20 border-t-[#0D2B70]"></div>
                    <span class="text-sm font-semibold text-[#0D2B70]">Loading applicant records...</span>
                </div>
            </div>
        </div>

        <div id="applicantRecordsResults">
            @include('partials.applicant_records_results', ['applicants' => $applicants])
        </div>
    </div>

    <div id="applicantDeleteModeModal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm">
        <div class="w-full max-w-xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_30px_90px_-40px_rgba(15,23,42,0.65)]">
            <div class="border-b border-slate-100 px-6 py-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Delete Applicant Record</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-900">Choose deletion mode</h2>
                <p class="mt-2 text-sm text-slate-600">Select whether the record should be deleted now or scheduled for deletion after 7 days.</p>
            </div>
            <div class="px-6 py-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Applicant</p>
                    <p id="deleteModeApplicantName" class="mt-2 text-base font-semibold text-slate-900">N/A</p>
                    <p id="deleteModeApplicantCode" class="mt-1 text-sm text-slate-500">N/A</p>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <button type="button" id="chooseImmediateDeletion" class="rounded-3xl border border-rose-200 bg-rose-50 p-5 text-left shadow-sm transition hover:border-rose-300 hover:bg-rose-100">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600">Immediate Deletion</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">Delete now</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Proceed with the current verification flow and delete the record right away.</p>
                    </button>
                    <button type="button" id="chooseScheduledDeletion" class="rounded-3xl border border-amber-200 bg-amber-50 p-5 text-left shadow-sm transition hover:border-amber-300 hover:bg-amber-100">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Set for Deletion</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">Schedule 7-day deletion</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Mark the record for deletion, allow cancellation within 7 days, and send the 2-day warning email automatically.</p>
                    </button>
                </div>
            </div>
            <div class="flex justify-end border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                <button type="button" data-close-modal="all" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <div id="applicantImmediateDeleteModal" class="fixed inset-0 z-[81] hidden items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_30px_90px_-40px_rgba(15,23,42,0.65)]">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Delete Applicant Record</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">Immediate deletion flow</h2>
                </div>
                <button type="button" data-close-modal="all" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <section id="immediateDeleteStepConfirm" class="space-y-5">
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-4">
                        <p class="text-sm font-semibold text-rose-700">You are about to permanently delete this applicant record.</p>
                        <p class="mt-2 text-sm leading-6 text-rose-700/90">This removes the applicant account and all applicant-linked records from the portal right away.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Applicant</p>
                        <p id="immediateDeleteApplicantName" class="mt-2 text-base font-semibold text-slate-900">N/A</p>
                        <p id="immediateDeleteApplicantCode" class="mt-1 text-sm text-slate-500">N/A</p>
                    </div>
                </section>
                <section id="immediateDeleteStepChallenge" class="hidden space-y-5">
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-sm font-semibold text-amber-800">Type the generated code exactly as shown to continue.</p>
                        <p class="mt-2 text-sm leading-6 text-amber-800/90">This check is case-sensitive and uses 7 random letters.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Verification Code</p>
                        <div id="immediateDeleteChallengeCode" class="mt-3 rounded-2xl bg-white px-4 py-3 text-center font-mono text-2xl font-bold tracking-[0.35em] text-[#0D2B70] ring-1 ring-slate-200"></div>
                    </div>
                    <div>
                        <label for="immediateDeleteChallengeInput" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Enter Code</label>
                        <input id="immediateDeleteChallengeInput" type="text" autocomplete="off" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" placeholder="Type the 7-letter code exactly" />
                        <p id="immediateDeleteChallengeHint" class="mt-2 text-xs text-slate-500">Proceed becomes available only when the code matches exactly.</p>
                    </div>
                </section>
                <section id="immediateDeleteStepWarning" class="hidden space-y-5">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                        <p class="text-sm font-semibold text-rose-700">Final warning</p>
                        <p class="mt-2 text-sm leading-6 text-rose-700/90">This action is permanent and cannot be undone from the admin panel.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">The following records will be removed</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            <li>Applicant account details and profile information</li>
                            <li>PDS records, work experience sheet, and related personal-information tables</li>
                            <li>Applications, uploaded documents, gallery files, notifications, sessions, and exam-tab records</li>
                        </ul>
                    </div>
                </section>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" id="immediateDeleteCancelButton" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancel</button>
                <button type="button" id="immediateDeleteBackButton" class="hidden inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Back</button>
                <button type="button" id="immediateDeletePrimaryButton" class="inline-flex items-center justify-center rounded-2xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0A235C]">Continue</button>
            </div>
        </div>
    </div>

    <div id="applicantScheduleDeleteModal" class="fixed inset-0 z-[82] hidden items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_30px_90px_-40px_rgba(15,23,42,0.65)]">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Set Applicant for Deletion</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">Confirm scheduled deletion</h2>
                </div>
                <button type="button" data-close-modal="all" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                    <p class="text-sm font-semibold text-amber-800">This applicant record will be marked for deletion for 7 days.</p>
                    <p class="mt-2 text-sm leading-6 text-amber-800/90">Within that period, superadmin can still cancel the deletion. Two days before the deadline, the applicant will receive a warning email.</p>
                </div>
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Applicant</p>
                    <p id="scheduleDeleteApplicantName" class="mt-2 text-base font-semibold text-slate-900">N/A</p>
                    <p id="scheduleDeleteApplicantCode" class="mt-1 text-sm text-slate-500">N/A</p>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Expected Deadline</p>
                    <p id="scheduleDeleteDeadlinePreview" class="mt-2 text-sm font-semibold text-slate-800">N/A</p>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" id="scheduleDeleteCancelButton" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Cancel</button>
                <button type="button" id="scheduleDeleteConfirmButton" class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Confirm Set for Deletion</button>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('applicantRecordsForm');
        const searchInput = document.getElementById('applicantSearchInput');
        const sortSelect = document.getElementById('applicantSort');
        const resetBtn = document.getElementById('applicantResetBtn');
        const resultsContainer = document.getElementById('applicantRecordsResults');
        const loadingOverlay = document.getElementById('applicantRecordsLoading');
        const searchSpinner = document.getElementById('applicantSearchSpinner');
        const totalLabel = document.getElementById('applicantRecordsTotal');
        const baseUrl = @json(route('admin.applicant_records.index'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const modeModal = document.getElementById('applicantDeleteModeModal');
        const immediateModal = document.getElementById('applicantImmediateDeleteModal');
        const scheduleModal = document.getElementById('applicantScheduleDeleteModal');
        const modeApplicantName = document.getElementById('deleteModeApplicantName');
        const modeApplicantCode = document.getElementById('deleteModeApplicantCode');
        const immediateApplicantName = document.getElementById('immediateDeleteApplicantName');
        const immediateApplicantCode = document.getElementById('immediateDeleteApplicantCode');
        const immediateChallengeCode = document.getElementById('immediateDeleteChallengeCode');
        const immediateChallengeInput = document.getElementById('immediateDeleteChallengeInput');
        const immediateChallengeHint = document.getElementById('immediateDeleteChallengeHint');
        const scheduleApplicantName = document.getElementById('scheduleDeleteApplicantName');
        const scheduleApplicantCode = document.getElementById('scheduleDeleteApplicantCode');
        const scheduleDeadlinePreview = document.getElementById('scheduleDeleteDeadlinePreview');
        const chooseImmediateDeletion = document.getElementById('chooseImmediateDeletion');
        const chooseScheduledDeletion = document.getElementById('chooseScheduledDeletion');
        const immediateDeleteCancelButton = document.getElementById('immediateDeleteCancelButton');
        const immediateDeleteBackButton = document.getElementById('immediateDeleteBackButton');
        const immediateDeletePrimaryButton = document.getElementById('immediateDeletePrimaryButton');
        const scheduleDeleteCancelButton = document.getElementById('scheduleDeleteCancelButton');
        const scheduleDeleteConfirmButton = document.getElementById('scheduleDeleteConfirmButton');
        const immediateSteps = {
            confirm: document.getElementById('immediateDeleteStepConfirm'),
            challenge: document.getElementById('immediateDeleteStepChallenge'),
            warning: document.getElementById('immediateDeleteStepWarning'),
        };
        [modeModal, immediateModal, scheduleModal].forEach((modal) => modal && document.body.appendChild(modal));
        let debounceTimer = null;
        let activeController = null;
        let latestRequestId = 0;
        let deleteRequestInFlight = false;
        let scheduleRequestInFlight = false;
        let immediateDeleteStep = 'confirm';
        let immediateDeleteChallenge = '';
        let deletionTarget = { destroyUrl: '', scheduleUrl: '', name: 'Applicant', code: 'N/A' };
        const errorState = `<section class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm"><div class="px-5 py-10 text-center text-sm font-medium text-rose-600">Unable to load applicant records. Please try again.</div></section>`;
        const challengeCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        const showStatus = (type, message) => {
            if (!message) return;
            if (typeof window.showAppToast === 'function') return window.showAppToast(message, type);
            window.alert(message);
        };
        const setLoading = (isLoading) => {
            loadingOverlay.classList.toggle('hidden', !isLoading);
            searchSpinner.classList.toggle('hidden', !isLoading);
        };
        const syncTotalLabel = () => {
            const totalSource = resultsContainer.querySelector('[data-total]');
            if (totalSource && totalLabel) totalLabel.textContent = `Total: ${totalSource.dataset.total ?? '0'}`;
        };
        const buildUrl = () => {
            const params = new URLSearchParams();
            const search = searchInput.value.trim();
            const sort = sortSelect.value.trim();
            if (search !== '') params.set('search', search);
            if (sort !== '') params.set('sort', sort);
            const query = params.toString();
            return query ? `${baseUrl}?${query}` : baseUrl;
        };
        const openModal = (modal) => { modal?.classList.remove('hidden'); modal?.classList.add('flex'); };
        const closeModal = (modal) => { modal?.classList.add('hidden'); modal?.classList.remove('flex'); };
        const formatPreviewDeadline = () => new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000));
        const generateChallengeCode = () => Array.from({ length: 7 }, () => challengeCharacters[Math.floor(Math.random() * challengeCharacters.length)]).join('');
        const populateDeletionTarget = ({ destroyUrl = '', scheduleUrl = '', name = 'Applicant', code = 'N/A' }) => {
            deletionTarget = { destroyUrl, scheduleUrl, name, code };
            modeApplicantName.textContent = name;
            modeApplicantCode.textContent = `Applicant ID: ${code}`;
            immediateApplicantName.textContent = name;
            immediateApplicantCode.textContent = `Applicant ID: ${code}`;
            scheduleApplicantName.textContent = name;
            scheduleApplicantCode.textContent = `Applicant ID: ${code}`;
            scheduleDeadlinePreview.textContent = formatPreviewDeadline();
        };
        const updateImmediateDeleteUI = () => {
            const isConfirm = immediateDeleteStep === 'confirm';
            const isChallenge = immediateDeleteStep === 'challenge';
            const isWarning = immediateDeleteStep === 'warning';
            const hasMatchingChallenge = immediateChallengeInput.value === immediateDeleteChallenge;
            immediateSteps.confirm.classList.toggle('hidden', !isConfirm);
            immediateSteps.challenge.classList.toggle('hidden', !isChallenge);
            immediateSteps.warning.classList.toggle('hidden', !isWarning);
            immediateDeleteBackButton.classList.toggle('hidden', isConfirm || deleteRequestInFlight);
            immediateDeletePrimaryButton.classList.remove('cursor-not-allowed', 'opacity-60', 'bg-slate-300', 'hover:bg-slate-300', 'bg-rose-600', 'hover:bg-rose-700', 'bg-[#0D2B70]', 'hover:bg-[#0A235C]');
            if (isConfirm) {
                immediateDeletePrimaryButton.textContent = 'Continue';
                immediateDeletePrimaryButton.disabled = false;
                immediateDeletePrimaryButton.classList.add('bg-[#0D2B70]', 'hover:bg-[#0A235C]');
                return;
            }
            if (isChallenge) {
                immediateDeletePrimaryButton.textContent = 'Proceed';
                immediateDeletePrimaryButton.disabled = !hasMatchingChallenge;
                immediateDeletePrimaryButton.classList.add(hasMatchingChallenge ? 'bg-[#0D2B70]' : 'bg-slate-300');
                immediateDeletePrimaryButton.classList.add(hasMatchingChallenge ? 'hover:bg-[#0A235C]' : 'hover:bg-slate-300');
                if (!hasMatchingChallenge) immediateDeletePrimaryButton.classList.add('cursor-not-allowed', 'opacity-60');
                return;
            }
            immediateDeletePrimaryButton.textContent = deleteRequestInFlight ? 'Deleting...' : 'Permanently Delete';
            immediateDeletePrimaryButton.disabled = deleteRequestInFlight;
            immediateDeletePrimaryButton.classList.add('bg-rose-600', 'hover:bg-rose-700');
            if (deleteRequestInFlight) immediateDeletePrimaryButton.classList.add('cursor-not-allowed', 'opacity-60');
        };
        const resetImmediateDeleteFlow = () => {
            immediateDeleteStep = 'confirm';
            immediateDeleteChallenge = '';
            immediateChallengeInput.value = '';
            immediateChallengeCode.textContent = '';
            immediateChallengeHint.textContent = 'Proceed becomes available only when the code matches exactly.';
            immediateChallengeHint.className = 'mt-2 text-xs text-slate-500';
            updateImmediateDeleteUI();
        };
        const closeAllDeletionModals = () => {
            if (deleteRequestInFlight || scheduleRequestInFlight) return;
            closeModal(modeModal); closeModal(immediateModal); closeModal(scheduleModal); resetImmediateDeleteFlow();
        };
        const sendJsonRequest = async (url, method = 'POST') => {
            const response = await fetch(url, { method, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' } });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(payload.message ?? 'Request failed.');
            return payload;
        };

        const fetchResults = async (url = null) => {
            if (activeController) activeController.abort();
            activeController = new AbortController();
            const requestId = ++latestRequestId;
            const targetUrl = url ?? buildUrl();

            setLoading(true);

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: activeController.signal
                });

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                const html = await response.text();
                if (requestId !== latestRequestId) return;

                resultsContainer.innerHTML = html;
                syncTotalLabel();
                window.history.replaceState({}, '', targetUrl);
            } catch (error) {
                if (error.name === 'AbortError') return;
                resultsContainer.innerHTML = errorState;
            } finally {
                if (requestId === latestRequestId) {
                    setLoading(false);
                }
            }
        };

        const debouncedFetch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchResults(), 400);
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
        });

        searchInput.addEventListener('input', debouncedFetch);
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        sortSelect.addEventListener('change', () => {
            fetchResults();
        });

        resetBtn.addEventListener('click', (event) => {
            event.preventDefault();
            searchInput.value = '';
            sortSelect.value = 'latest';
            fetchResults(baseUrl);
        });

        chooseImmediateDeletion.addEventListener('click', () => { closeModal(modeModal); resetImmediateDeleteFlow(); openModal(immediateModal); });
        chooseScheduledDeletion.addEventListener('click', () => { closeModal(modeModal); openModal(scheduleModal); });
        immediateChallengeInput.addEventListener('input', () => {
            const isExactMatch = immediateChallengeInput.value === immediateDeleteChallenge;
            if (immediateChallengeInput.value === '') {
                immediateChallengeHint.textContent = 'Proceed becomes available only when the code matches exactly.';
                immediateChallengeHint.className = 'mt-2 text-xs text-slate-500';
            } else if (isExactMatch) {
                immediateChallengeHint.textContent = 'Code matched. You can proceed to the final warning.';
                immediateChallengeHint.className = 'mt-2 text-xs text-emerald-600';
            } else {
                immediateChallengeHint.textContent = 'Code does not match yet. Case matters.';
                immediateChallengeHint.className = 'mt-2 text-xs text-rose-600';
            }
            updateImmediateDeleteUI();
        });
        immediateDeletePrimaryButton.addEventListener('click', async () => {
            if (immediateDeleteStep === 'confirm') {
                immediateDeleteChallenge = generateChallengeCode();
                immediateDeleteStep = 'challenge';
                immediateChallengeCode.textContent = immediateDeleteChallenge;
                immediateChallengeInput.value = '';
                immediateChallengeInput.focus();
                return updateImmediateDeleteUI();
            }
            if (immediateDeleteStep === 'challenge') {
                if (immediateChallengeInput.value !== immediateDeleteChallenge) return;
                immediateDeleteStep = 'warning';
                return updateImmediateDeleteUI();
            }
            deleteRequestInFlight = true;
            immediateDeleteCancelButton.disabled = true;
            immediateDeleteBackButton.disabled = true;
            updateImmediateDeleteUI();
            try {
                const payload = await sendJsonRequest(deletionTarget.destroyUrl, 'DELETE');
                deleteRequestInFlight = false;
                immediateDeleteCancelButton.disabled = false;
                immediateDeleteBackButton.disabled = false;
                closeAllDeletionModals();
                showStatus('success', payload.message ?? 'Applicant record deleted.');
                fetchResults(window.location.href);
            } catch (error) {
                deleteRequestInFlight = false;
                immediateDeleteCancelButton.disabled = false;
                immediateDeleteBackButton.disabled = false;
                updateImmediateDeleteUI();
                showStatus('error', error.message ?? 'Unable to delete applicant record.');
            }
        });
        immediateDeleteBackButton.addEventListener('click', () => {
            if (deleteRequestInFlight) return;
            if (immediateDeleteStep === 'warning') immediateDeleteStep = 'challenge';
            else if (immediateDeleteStep === 'challenge') immediateDeleteStep = 'confirm';
            updateImmediateDeleteUI();
        });
        immediateDeleteCancelButton.addEventListener('click', closeAllDeletionModals);
        scheduleDeleteCancelButton.addEventListener('click', closeAllDeletionModals);
        scheduleDeleteConfirmButton.addEventListener('click', async () => {
            scheduleRequestInFlight = true;
            scheduleDeleteCancelButton.disabled = true;
            scheduleDeleteConfirmButton.disabled = true;
            scheduleDeleteConfirmButton.textContent = 'Setting...';
            try {
                const payload = await sendJsonRequest(deletionTarget.scheduleUrl, 'POST');
                scheduleRequestInFlight = false;
                scheduleDeleteCancelButton.disabled = false;
                scheduleDeleteConfirmButton.disabled = false;
                scheduleDeleteConfirmButton.textContent = 'Confirm Set for Deletion';
                closeAllDeletionModals();
                showStatus('success', payload.message ?? 'Applicant record set for deletion.');
                fetchResults(window.location.href);
            } catch (error) {
                scheduleRequestInFlight = false;
                scheduleDeleteCancelButton.disabled = false;
                scheduleDeleteConfirmButton.disabled = false;
                scheduleDeleteConfirmButton.textContent = 'Confirm Set for Deletion';
                showStatus('error', error.message ?? 'Unable to set applicant record for deletion.');
            }
        });
        document.querySelectorAll('[data-close-modal="all"]').forEach((button) => button.addEventListener('click', closeAllDeletionModals));
        [modeModal, immediateModal, scheduleModal].forEach((modal) => modal?.addEventListener('click', (event) => event.target === modal && closeAllDeletionModals()));
        document.addEventListener('keydown', (event) => event.key === 'Escape' && closeAllDeletionModals());

        resultsContainer.addEventListener('click', async (event) => {
            const deleteButton = event.target.closest('[data-delete-applicant-url]');
            if (deleteButton) {
                event.preventDefault();
                populateDeletionTarget({
                    destroyUrl: deleteButton.dataset.deleteApplicantUrl ?? '',
                    scheduleUrl: deleteButton.dataset.scheduleApplicantUrl ?? '',
                    name: deleteButton.dataset.deleteApplicantName ?? 'Applicant',
                    code: deleteButton.dataset.deleteApplicantCode ?? 'N/A',
                });
                openModal(modeModal);
                return;
            }

            const cancelButton = event.target.closest('[data-cancel-applicant-deletion-url]');
            if (cancelButton) {
                event.preventDefault();
                const applicantName = cancelButton.dataset.cancelApplicantName ?? 'this applicant';
                if (!window.confirm(`Cancel the scheduled deletion for ${applicantName}?`)) return;
                try {
                    const payload = await sendJsonRequest(cancelButton.dataset.cancelApplicantDeletionUrl ?? '', 'POST');
                    showStatus('success', payload.message ?? 'Scheduled deletion cancelled.');
                    fetchResults(window.location.href);
                } catch (error) {
                    showStatus('error', error.message ?? 'Unable to cancel scheduled deletion.');
                }
                return;
            }

            const link = event.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href') ?? '';
            if (!href.includes('page=')) return;

            event.preventDefault();
            fetchResults(link.href);
        });

        resetImmediateDeleteFlow();
        syncTotalLabel();
    });
</script>
@endsection

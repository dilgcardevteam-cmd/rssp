@extends('layout.exam_user')

@section('title', 'Exam')

@push('styles')
<style>
    body {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    .question-text {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        -webkit-touch-callout: none;
    }
    .exam-protected .question-text {
        pointer-events: auto;
    }
    input, textarea, button, label {
        user-select: auto;
        -webkit-user-select: auto;
        -moz-user-select: auto;
        -ms-user-select: auto;
    }
</style>
@endpush

<!-- Floating Sticky Timer (Overlay Style) -->
<div x-data="{ expanded: true }" class="fixed mt-3 right-10 bg-white rounded-lg px-4 py-2 shadow-lg z-50 border border-gray-200">
    <div class="flex items-center justify-end gap-4 mb-2">
        <!-- Circular Progress (Pacman-ish ring) -->
        <div class="relative w-12 h-12 flex-shrink-0">
            <svg class="transform -rotate-90 w-12 h-12">
                <!-- Background Circle -->
                <circle cx="24" cy="24" r="18" stroke="#e5e7eb" stroke-width="6" fill="transparent" />
                <!-- Progress Circle -->
                <circle id="timer-circle" cx="24" cy="24" r="18" stroke="#002C76" stroke-width="6" fill="transparent" stroke-linecap="round" class="transition-all duration-1000 ease-linear" />
            </svg>
        </div>

        <div>
            <p class="uppercase text-xs text-gray-500 text-right">Time Remaining</p>
            <p id="timer" class="text-2xl font-bold text-gray-800 text-right">00:00</p>
        </div>
        
        <!-- Toggle Button -->
        <button @click="expanded = !expanded" class="ml-1 text-gray-400 hover:text-[#002C76] focus:outline-none transition-colors" title="Toggle Time Info">
            <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    <!-- Collapsible PST Section -->
    <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="border-t border-gray-200 pt-2">
        <p class="uppercase text-xs text-gray-500 text-right">Philippine Standard Time</p>
        <p id="pst-time" class="text-lg font-bold text-[#002C76] text-right">--:--:--</p>
        <p id="pst-date" class="text-xs text-gray-600 text-right">--</p>
    </div>
</div>

@section('content')
<!-- Original Header -->
<div class="px-6 mb-6">
    @php
        $displayExamineeName = $examineeName ?? (auth()->user()->name ?? 'Examinee');
        $displayExamineeNumber = $examineeNumber ?? strtoupper('EXM-' . substr(hash('sha256', ($vacancy_id ?? 'UNKNOWN') . '-' . (auth()->id() ?? '0')), 0, 8));
    @endphp
    <h2 class="text-2xl font-bold uppercase text-[#002C76]">{{ $vacancy->position_title ?? 'Examination' }}</h2>
    <p class="uppercase text-sm font-semibold text-gray-700 tracking-wide">Examination</p>
    <div class="mt-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 max-w-xl">
        <p class="text-sm text-gray-700"><span class="font-semibold text-[#002C76]">Examinee:</span> {{ $displayExamineeName }}</p>
        <p class="text-sm text-gray-700"><span class="font-semibold text-[#002C76]">Examinee No.:</span> {{ $displayExamineeNumber }}</p>
    </div>
</div>

 @if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
@endif

@if ($errors->any())
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Whoops!</strong>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="exam-form" class="no-spinner" action="{{ route('exam.submit', ['vacancy_id' => $vacancy_id]) }}" method="POST">
    @csrf

    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
    <input type="hidden" name="vacancy_id" value="{{ $vacancy_id }}">
    <input type="hidden" name="batch" value="{{ (int) ($batchNo ?? request('batch', 1)) }}">

    <div id="question-container" class="px-6 pb-10"></div>

    <div id="submitContainer" class="px-6 mt-6 max-w-3xl mx-auto hidden">
        <div x-data="{ showSubmitConfirm: false }" class="inline">
            <div class="flex justify-end">
                <button type="button" id="openSubmitBtn" @click="showSubmitConfirm = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold transition min-w-[150px] disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Submit
                </button>
        </div>
            <div x-show="showSubmitConfirm" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;" @keydown.escape.window="showSubmitConfirm = false">
                <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl relative">
                    <button type="button" @click="showSubmitConfirm = false" class="absolute top-4 right-4 text-gray-400 text-xl font-bold hover:text-red-600">&times;</button>
                    <h2 class="text-2xl font-extrabold text-[#002C76] text-center mb-2">Submission</h2>
                    <p class="text-gray-700 text-sm text-center mb-6">Click <span class="font-semibold text-[#0D2B70]">Submit</span> to finalize your answers.</p>
                    <div class="flex justify-center gap-4">
                        <button type="button" @click="showSubmitConfirm = false" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full font-semibold transition">Cancel</button>
                        <button type="button" id="confirmSubmitBtn" @click="window.prepareSubmit()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div x-data="timesUpModal()" x-init="init()" id="timesup-modal-wrapper">
    @include('partials.exam_timesup')
</div>

<div class="fixed bottom-6 right-6 z-50 w-[min(24rem,calc(100vw-2rem))] space-y-2">
    <div id="saveNotification" class="hidden rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 shadow-lg transition-opacity duration-300">
        Answers restored from your latest autosave.
    </div>
    <div id="examWarningNotification" class="hidden rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 shadow-lg">
        Warning: Suspicious activity detected.
    </div>
</div>

<div id="examPausedOverlay" class="hidden fixed inset-0 z-[90] bg-slate-950/70 backdrop-blur-sm">
    <div class="flex h-full items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl border border-amber-200 bg-white p-8 text-center shadow-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Examination Paused</p>
            <h3 class="mt-3 text-2xl font-bold text-[#002C76]">The administrator has paused this exam.</h3>
            <p class="mt-2 text-sm text-slate-600">Your progress remains saved. Please wait here until the exam resumes.</p>
            <p id="examResumeCountdown" class="mt-4 hidden text-lg font-semibold text-amber-600"></p>
        </div>
    </div>
</div>

<script>
    const Questions = @json($examItems);
    const savedAnswers = @json($savedAnswers ?? []);
    const examPauseState = @json($examPauseState ?? ['global_paused' => false, 'application_paused' => false]);

    // Questions are shown in original order

    const totalItems = Questions.length;
    const container = document.getElementById('question-container');
    const submitContainer = document.getElementById('submitContainer');
    const form = document.getElementById('exam-form');
    const openSubmitBtn = document.getElementById('openSubmitBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');

    const answers = {};
    const normalizedSavedAnswers = Object.fromEntries(
        Object.entries(savedAnswers || {}).map(([questionId, value]) => [String(questionId), value ?? ''])
    );
    let switchCount = 0;
    const AUTOSAVE_DEBOUNCE_MS = 900;
    const AUTOSAVE_PERIODIC_MS = 15000;
    let autoSaveInFlight = false;
    let autoSaveQueued = false;
    let answersDirty = false;
    let lastSavedFingerprint = '';
    let unloadSaveTriggered = false;
    let restoredNoticeShown = false;
    let examPaused = !!(examPauseState.global_paused || examPauseState.application_paused);
    let lastSyncedExamPaused = null;
    let examStatusPollInterval = null;
    let examResumeCountdownInterval = null;
    let examResumeCountdownActive = false;

    Questions.forEach((q, idx) => q.number = idx + 1);

    function hasAnswerValue(value) {
        return value !== null && value !== undefined && String(value) !== '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function collectCurrentAnswers() {
        form.querySelectorAll('[name^="answers["]').forEach(input => {
            const idMatch = input.name.match(/answers\[(\d+)]/);
            if (!idMatch) return;
            const questionId = idMatch[1]   ;

            if (input.type === 'radio') {
                if (input.checked) {
                    answers[questionId] = input.value;
                } else if (!(questionId in answers)) {
                    answers[questionId] = ''; // explicitly store empty if nothing checked yet
                }
            } else {
                answers[questionId] = input.value.trim();
            }
        });
    }


/*
    function prepareSubmit() {
        collectCurrentAnswers(); // updates 'answers' object with current screen inputs

        // Remove old hidden inputs if re-preparing
        form.querySelectorAll('input[name^="answers["]').forEach(el => el.remove());

        // For each collected answer, create a hidden input
        Object.entries(answers).forEach(([questionId, value]) => {
            console.log(value);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `answers[${questionId}]`;
            input.value = value;
            form.appendChild(input);
        });

        form.submit(); // finally submit
    }*/

    // Ensure native submits are routed through prepareSubmit to avoid loader issues
    form.addEventListener('submit', (e) => {
        if (!window.isSubmitting) {
            e.preventDefault();
            prepareSubmit();
        }
    });

    renderAllQuestions();

    let duration = Math.max(0, {{ (int) $remaining_seconds }});
    const totalDuration = {{ (int) $total_seconds }}; // Total duration from controller
    const examEndTimeMs = {{ (int) ($examEndTimeTs ?? 0) }} * 1000;
    const serverNowMs = {{ (int) ($serverNowTs ?? now()->timestamp) }} * 1000;
    const serverClockStartPerf = performance.now();
    const timerDisplay = document.getElementById('timer');
    const timerCircle = document.getElementById('timer-circle');
    
    // Circle Config
    const radius = 18;
    const circumference = 2 * Math.PI * radius;
    if (timerCircle) {
        timerCircle.style.strokeDasharray = `${circumference} ${circumference}`;
        timerCircle.style.strokeDashoffset = circumference;
    }
    
    function nowFromServerClockMs() {
        return serverNowMs + (performance.now() - serverClockStartPerf);
    }

    function getServerNowDate() {
        return new Date(nowFromServerClockMs());
    }

    function computeRemainingSecondsFromServerClock() {
        if (examEndTimeMs <= 0) {
            return Math.max(0, duration);
        }

        return Math.max(0, Math.floor((examEndTimeMs - nowFromServerClockMs()) / 1000));
    }

    // Initial display from server-anchored clock.
    duration = computeRemainingSecondsFromServerClock();
    updateTimerDisplay(duration);

    let hasTriggeredTimesUp = false;
    let timerInterval = null;

    function handleTimesUp() {
        if (hasTriggeredTimesUp) {
            return;
        }

        hasTriggeredTimesUp = true;
        clearInterval(timerInterval);
        collectCurrentAnswers();
        window.triggerTimesUp();
    }

    timerInterval = setInterval(() => {
        if (examPaused) {
            return;
        }

        duration = computeRemainingSecondsFromServerClock();
        updateTimerDisplay(duration);

        if (duration <= 0) {
            handleTimesUp();
        }
    }, 1000);

    function updatePST() {
        const now = getServerNowDate();
        const pstTimeFormatter = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        const pstDateFormatter = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        const timeStr = pstTimeFormatter.format(now);
        const dateStr = pstDateFormatter.format(now);

        const tEl = document.getElementById('pst-time');
        const dEl = document.getElementById('pst-date');
        if(tEl) tEl.textContent = timeStr;
        if(dEl) dEl.textContent = dateStr;
    }
    setInterval(updatePST, 1000);
    updatePST();

    function updateTimerDisplay(seconds) {
        if (seconds < 0) seconds = 0;
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);
        
        // Update Circle
        if (timerCircle) {
            const offset = circumference - (seconds / totalDuration) * circumference;
            timerCircle.style.strokeDashoffset = offset;
            
            // Color change warning
            if (seconds < 60) {
                timerCircle.style.stroke = '#DC2626'; // Red
            } else if (seconds < 300) {
                timerCircle.style.stroke = '#F59E0B'; // Orange
            } else {
                timerCircle.style.stroke = '#002C76'; // Blue
            }
        }

        let timeStr = '';
        if (h > 0) {
            timeStr += `${String(h).padStart(2, '0')}:`;
        }
        timeStr += `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        
        timerDisplay.textContent = timeStr;
        
        // Visual warning when low on time
        if (seconds < 60) {
            timerDisplay.classList.add('text-red-600');
            timerDisplay.classList.add('animate-pulse');
        }
    }

    function showSaveNotification() {
        const notif = document.getElementById('saveNotification');
        notif.classList.remove('hidden');
        notif.style.opacity = 1;
        setTimeout(() => { notif.style.opacity = 0; setTimeout(() => notif.classList.add('hidden'), 300); }, 2000);
    }

    function showPausedOverlay(shouldShow) {
        const overlay = document.getElementById('examPausedOverlay');
        if (!overlay) return;

        overlay.classList.toggle('hidden', !shouldShow);
    }

    function clearResumeCountdown() {
        if (examResumeCountdownInterval) {
            clearInterval(examResumeCountdownInterval);
            examResumeCountdownInterval = null;
        }

        examResumeCountdownActive = false;

        const countdownEl = document.getElementById('examResumeCountdown');
        if (countdownEl) {
            countdownEl.classList.add('hidden');
            countdownEl.textContent = '';
        }
    }

    function finishResumeCountdown(remainingSeconds = null) {
        clearResumeCountdown();

        examPaused = false;
        lastSyncedExamPaused = false;
        showPausedOverlay(false);

        if (typeof remainingSeconds === 'number' && remainingSeconds >= 0) {
            duration = remainingSeconds;
        } else {
            duration = computeRemainingSecondsFromServerClock();
        }

        updateTimerDisplay(duration);
        updateSubmitEnabled();

        if (!timerInterval) {
            timerInterval = setInterval(() => {
                if (examPaused) {
                    return;
                }

                duration = computeRemainingSecondsFromServerClock();
                updateTimerDisplay(duration);

                if (duration <= 0) {
                    handleTimesUp();
                }
            }, 1000);
        }
    }

    function startResumeCountdown(remainingSeconds = null) {
        if (examResumeCountdownActive) {
            return;
        }

        examResumeCountdownActive = true;
        showPausedOverlay(true);

        const countdownEl = document.getElementById('examResumeCountdown');
        let secondsRemaining = 2;

        if (countdownEl) {
            countdownEl.textContent = 'Resuming in 2 seconds...';
            countdownEl.classList.remove('hidden');
        }

        examResumeCountdownInterval = window.setInterval(() => {
            secondsRemaining -= 1;

            if (countdownEl) {
                countdownEl.textContent = secondsRemaining > 0
                    ? `Resuming in ${secondsRemaining} second${secondsRemaining === 1 ? '' : 's'}...`
                    : 'Resuming now...';
            }

            if (secondsRemaining <= 0) {
                finishResumeCountdown(remainingSeconds);
            }
        }, 1000);
    }

    function syncPauseState(nextState, remainingSeconds = null) {
        const nextPaused = !!nextState;

        if (!nextPaused && examResumeCountdownActive) {
            return;
        }

        if (nextPaused) {
            clearResumeCountdown();
            examPaused = true;
            showPausedOverlay(true);
        }

        const pauseStateChanged = lastSyncedExamPaused === null || lastSyncedExamPaused !== nextPaused;

        if (nextPaused && pauseStateChanged) {
            if (typeof remainingSeconds === 'number' && remainingSeconds >= 0) {
                duration = remainingSeconds;
                updateTimerDisplay(duration);
            }

            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            if (confirmSubmitBtn) {
                confirmSubmitBtn.disabled = true;
            }
            lastSyncedExamPaused = nextPaused;
            return;
        }

        if (!nextPaused && pauseStateChanged && lastSyncedExamPaused === true) {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }

            startResumeCountdown(remainingSeconds);
            lastSyncedExamPaused = nextPaused;
            return;
        }

        if (!nextPaused) {
            examPaused = false;
            lastSyncedExamPaused = nextPaused;
            updateSubmitEnabled();
            return;
        }

        updateSubmitEnabled();

        if (!timerInterval) {
            timerInterval = setInterval(() => {
                if (examPaused) {
                    return;
                }

                duration = computeRemainingSecondsFromServerClock();
                updateTimerDisplay(duration);

                if (duration <= 0) {
                    handleTimesUp();
                }
            }, 1000);
        }

        lastSyncedExamPaused = examPaused;
    }

    function pollExamStatus() {
        fetch("{{ route('exam.status.check', ['vacancy_id' => $vacancy_id]) }}?batch={{ (int) ($batchNo ?? request('batch', 1)) }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (!data) return;
            syncPauseState(!!data.paused, typeof data.remaining_seconds === 'number' ? data.remaining_seconds : null);
        })
        .catch(() => {});
    }

    function startExamStatusPolling() {
        if (examStatusPollInterval) return;

        pollExamStatus();
        examStatusPollInterval = setInterval(pollExamStatus, 3000);
    }

    function timesUpModal() {
        return {
            showTimesUp: false,
            init() { window.triggerTimesUp = () => { this.showTimesUp = true; }; }
        };
    }

    syncPauseState(examPaused, duration);
    startExamStatusPolling();

    window.allowFocusLoss = false;
    window.isSubmitting = false;
    window.allowFullscreenExit = false;
    const examMaxViolations = Math.max(1, Number(@json((int) ($maxViolations ?? 12))) || 12);

    const antiCheat = (() => {
        const warningEl = document.getElementById('examWarningNotification');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let active = false;
        let warningTimeout = null;
        let thresholdViolations = 0;
        const tabSwitchEvents = [];
        let hiddenAt = null;
        let violationCounter = 0;
        let lastWidth = window.innerWidth;
        let lastHeight = window.innerHeight;
        let resizeIgnoreUntil = 0;
        let devtoolsInterval = null;
        let lastDevtoolsWarningAt = 0;
        let tamperDetectionInterval = null;
        const baselineTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        const baselineTimezoneOffsetMinutes = new Date().getTimezoneOffset();
        let lastTimezoneTamperLoggedAt = 0;
        let lastTimezoneTamperSignature = '';
        let lastClockTamperLoggedAt = 0;
        const expectedTimezone = @json((string) config('app.timezone', 'Asia/Manila'));
        const expectedTimezoneOffsetMinutes = {{ (int) (-\Carbon\Carbon::now(config('app.timezone', 'Asia/Manila'))->utcOffset()) }};
        const allowedTimezoneNames = new Set([
            expectedTimezone,
            'Asia/Manila',
            'Asia/Shanghai',
        ]);

        function isExamActive() {
            return active && !window.allowFocusLoss && !window.isSubmitting;
        }

        function showWarning(message) {
            if (!warningEl) return;
            warningEl.textContent = message;
            warningEl.classList.remove('hidden');
            if (warningTimeout) clearTimeout(warningTimeout);
            warningTimeout = setTimeout(() => warningEl.classList.add('hidden'), 3200);
        }

        function isFullscreenActive() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement);
        }

        async function requestExamFullscreen() {
            if (!isExamActive() || isFullscreenActive()) return;
            const root = document.documentElement;
            try {
                resizeIgnoreUntil = Date.now() + 1500;
                if (root.requestFullscreen) {
                    await root.requestFullscreen();
                } else if (root.webkitRequestFullscreen) {
                    root.webkitRequestFullscreen();
                }
            } catch (_) {
                // Browser may require user gesture; listeners will retry.
            }
        }

        function logViolation(type, payload = {}) {
            const now = new Date();
            violationCounter += 1;
            fetch('/log-switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                keepalive: true,
                body: JSON.stringify({
                    type,
                    count: violationCounter,
                    time: now.toISOString(),
                    vacancy_id: '{{ $vacancy_id }}',
                    ...payload
                })
            })
            .then(response => response.ok ? response.json() : null)
            .catch(() => {});
        }

        function registerViolation(type, message, payload = {}) {
            if (!isExamActive()) return;
            thresholdViolations += 1;
            showWarning(message);
            logViolation(type, payload);
            if (thresholdViolations >= examMaxViolations) {
                showWarning(`Maximum violations reached (${examMaxViolations}). You must submit manually.`);
            }
        }

        function detectTimeTampering() {
            if (!isExamActive()) return;

            const clientTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            const timezoneOffsetMinutes = new Date().getTimezoneOffset();
            const clientNowMs = Date.now();
            const serverNowMsEstimated = nowFromServerClockMs();
            const clockDriftMs = Math.round(clientNowMs - serverNowMsEstimated);
            const absDriftMs = Math.abs(clockDriftMs);

            const timezoneChanged = baselineTimezone && clientTimezone && clientTimezone !== baselineTimezone;
            const offsetChanged = timezoneOffsetMinutes !== baselineTimezoneOffsetMinutes;
            const offsetMatchesExpected = timezoneOffsetMinutes === expectedTimezoneOffsetMinutes;
            const timezoneAllowed = !clientTimezone || allowedTimezoneNames.has(clientTimezone);
            const timezoneMismatch = !timezoneAllowed && !offsetMatchesExpected;
            const offsetMismatch = !offsetMatchesExpected;

            if (timezoneChanged || offsetChanged || timezoneMismatch || offsetMismatch) {
                const nowMs = Date.now();
                const tamperSignature = [clientTimezone, timezoneOffsetMinutes, baselineTimezone, baselineTimezoneOffsetMinutes].join('|');
                const beyondCooldown = (nowMs - lastTimezoneTamperLoggedAt) >= 30000;

                if (tamperSignature !== lastTimezoneTamperSignature || beyondCooldown) {
                    lastTimezoneTamperLoggedAt = nowMs;
                    lastTimezoneTamperSignature = tamperSignature;

                    const reasons = [];
                    if (timezoneChanged) reasons.push('timezone-changed');
                    if (offsetChanged) reasons.push('offset-changed');
                    if (timezoneMismatch) reasons.push('timezone-name-not-allowed');
                    if (offsetMismatch) reasons.push('not-utc-plus-8');

                    registerViolation('timezone-tamper', 'Timezone mismatch detected during exam monitoring.', {
                        timezone: clientTimezone,
                        timezone_offset_minutes: timezoneOffsetMinutes,
                        baseline_timezone: baselineTimezone,
                        baseline_timezone_offset_minutes: baselineTimezoneOffsetMinutes,
                        expected_timezone: expectedTimezone,
                        expected_timezone_offset_minutes: expectedTimezoneOffsetMinutes,
                        tamper_reason: reasons.join(',') || 'timezone-mismatch',
                        client_now_iso: new Date(clientNowMs).toISOString(),
                        server_now_iso: new Date(serverNowMsEstimated).toISOString(),
                        clock_drift_ms: clockDriftMs,
                    });
                }
            }

            const nowMs = Date.now();
            if (absDriftMs >= 180000 && (nowMs - lastClockTamperLoggedAt) >= 60000) {
                lastClockTamperLoggedAt = nowMs;
                registerViolation('clock-tamper', 'Significant client/server clock drift detected.', {
                    timezone: clientTimezone,
                    timezone_offset_minutes: timezoneOffsetMinutes,
                    client_now_iso: new Date(clientNowMs).toISOString(),
                    server_now_iso: new Date(serverNowMsEstimated).toISOString(),
                    clock_drift_ms: clockDriftMs,
                });
            }
        }

        function onFullscreenChange() {
            if (!isExamActive()) return;
            if (!isFullscreenActive()) {
                registerViolation('fullscreen-exit', 'Fullscreen is required during the exam.');
                requestExamFullscreen();
            }
        }

        function onVisibilityChange() {
            if (!isExamActive()) return;
            if (document.visibilityState === 'hidden') {
                hiddenAt = new Date();
            } else if (hiddenAt) {
                switchCount += 1;
                const endedAt = new Date();
                const durationMilliseconds = Math.max(0, endedAt.getTime() - hiddenAt.getTime());
                const durationSeconds = Math.floor(durationMilliseconds / 1000);
                tabSwitchEvents.push({
                    switch_count: switchCount,
                    started_at: hiddenAt.toISOString(),
                    ended_at: endedAt.toISOString(),
                    duration_seconds: durationSeconds,
                    duration_milliseconds: durationMilliseconds
                });
                window.tabSwitchMetrics = {
                    total_switches: switchCount,
                    total_hidden_seconds: Number((tabSwitchEvents.reduce((sum, item) => sum + item.duration_milliseconds, 0) / 1000).toFixed(3)),
                    total_hidden_milliseconds: tabSwitchEvents.reduce((sum, item) => sum + item.duration_milliseconds, 0),
                    events: [...tabSwitchEvents]
                };
                registerViolation(
                    'tab-switch',
                    `Warning ${switchCount}: Stay on the exam page.`,
                    {
                        started_at: hiddenAt.toISOString(),
                        ended_at: endedAt.toISOString(),
                        duration_seconds: durationSeconds,
                        duration_milliseconds: durationMilliseconds,
                        switch_count: switchCount,
                        total_switches: switchCount
                    }
                );
                hiddenAt = null;
                requestExamFullscreen();
            }
        }

        function onWindowBlur() {
            if (!isExamActive()) return;
            hiddenAt = hiddenAt || new Date();
        }

        function onWindowFocus() {
            if (!isExamActive()) return;
            requestExamFullscreen();
        }

        function onKeydown(event) {
            if (!isExamActive()) return;

            const key = event.key || '';
            const lower = key.toLowerCase();
            const blockedDirect = ['f12', 'alt'];

            if (blockedDirect.includes(lower)) {
                event.preventDefault();
                event.stopPropagation();
                registerViolation('blocked-key', 'Restricted keyboard shortcut detected.', {
                    key,
                    ctrl: !!event.ctrlKey,
                    alt: !!event.altKey,
                    shift: !!event.shiftKey,
                    meta: !!event.metaKey
                });
            }
        }

        function onContextMenu(event) {
            if (!isExamActive()) return;
            event.preventDefault();
            registerViolation('context-menu', 'Right-click is disabled during the exam.');
        }

        function onClipboard(event) {
            if (!isExamActive()) return;
            event.preventDefault();
            registerViolation(`clipboard-${event.type}`, `${event.type.toUpperCase()} is disabled during the exam.`);
        }

        function onSelectStart(event) {
            if (!isExamActive()) return;
            const target = event.target instanceof Element ? event.target : event.target?.parentElement;
            if (!target) return;
            const inEditable = !!target.closest('input, textarea');
            if (inEditable) return;
            if (target.closest('.question-text')) {
                event.preventDefault();
                registerViolation('text-selection', 'Selecting question text is disabled.');
            }
        }

        function onDragStart(event) {
            if (!isExamActive()) return;
            const target = event.target instanceof Element ? event.target : event.target?.parentElement;
            if (!target) return;
            if (target.closest('.question-text')) {
                event.preventDefault();
            }
        }

        function onResize() {
            if (!isExamActive()) return;

            const now = Date.now();
            const widthChange = Math.abs(window.innerWidth - lastWidth);
            const heightChange = Math.abs(window.innerHeight - lastHeight);
            lastWidth = window.innerWidth;
            lastHeight = window.innerHeight;

            if (now <= resizeIgnoreUntil) return;
            if (widthChange < 120 && heightChange < 120) return;

            registerViolation('window-resize', 'Window resize detected. Keep your exam window unchanged.', {
                width: window.innerWidth,
                height: window.innerHeight
            });
        }

        function detectDevtools() {
            if (!isExamActive()) return;
            const threshold = 160;
            const opened = (window.outerWidth - window.innerWidth > threshold) || (window.outerHeight - window.innerHeight > threshold);
            if (!opened) return;

            const now = Date.now();
            if (now - lastDevtoolsWarningAt < 4000) return;
            lastDevtoolsWarningAt = now;
            registerViolation('devtools-heuristic', 'Developer-tools-like window pattern detected.');
        }

        function onBeforeUnload(event) {
            if (!isExamActive()) return;
            event.preventDefault();
            event.returnValue = 'Exam is active. Leaving now may submit or invalidate your attempt.';
        }

        function activate() {
            if (active) return;
            active = true;
            window.allowFullscreenExit = false;
            document.body.classList.add('exam-protected');

            document.addEventListener('fullscreenchange', onFullscreenChange);
            document.addEventListener('webkitfullscreenchange', onFullscreenChange);
            document.addEventListener('visibilitychange', onVisibilityChange);
            window.addEventListener('blur', onWindowBlur);
            window.addEventListener('focus', onWindowFocus);
            window.addEventListener('resize', onResize);
            window.addEventListener('beforeunload', onBeforeUnload);

            document.addEventListener('keydown', onKeydown, true);
            document.addEventListener('contextmenu', onContextMenu, true);
            ['copy', 'cut', 'paste'].forEach(evt => document.addEventListener(evt, onClipboard, true));
            document.addEventListener('selectstart', onSelectStart, true);
            document.addEventListener('dragstart', onDragStart, true);

            document.addEventListener('click', requestExamFullscreen, { passive: true });
            document.addEventListener('keydown', requestExamFullscreen, { passive: true });

            devtoolsInterval = setInterval(detectDevtools, 2000);
            tamperDetectionInterval = setInterval(detectTimeTampering, 5000);
            requestExamFullscreen();
            detectTimeTampering();
        }

        function deactivate() {
            if (!active) return;
            active = false;
            window.allowFullscreenExit = true;
            document.body.classList.remove('exam-protected');

            document.removeEventListener('fullscreenchange', onFullscreenChange);
            document.removeEventListener('webkitfullscreenchange', onFullscreenChange);
            document.removeEventListener('visibilitychange', onVisibilityChange);
            window.removeEventListener('blur', onWindowBlur);
            window.removeEventListener('focus', onWindowFocus);
            window.removeEventListener('resize', onResize);
            window.removeEventListener('beforeunload', onBeforeUnload);

            document.removeEventListener('keydown', onKeydown, true);
            document.removeEventListener('contextmenu', onContextMenu, true);
            ['copy', 'cut', 'paste'].forEach(evt => document.removeEventListener(evt, onClipboard, true));
            document.removeEventListener('selectstart', onSelectStart, true);
            document.removeEventListener('dragstart', onDragStart, true);

            document.removeEventListener('click', requestExamFullscreen, { passive: true });
            document.removeEventListener('keydown', requestExamFullscreen, { passive: true });

            if (warningTimeout) clearTimeout(warningTimeout);
            if (devtoolsInterval) clearInterval(devtoolsInterval);
            devtoolsInterval = null;
            if (tamperDetectionInterval) clearInterval(tamperDetectionInterval);
            tamperDetectionInterval = null;
            hiddenAt = null;
        }

        return { activate, deactivate };
    })();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => antiCheat.activate(), { once: true });
    } else {
        antiCheat.activate();
    }
    function prepareSubmit() {
        collectCurrentAnswers(); // updates 'answers' object with current screen inputs

        // Remove old hidden inputs if re-preparing
        form.querySelectorAll('input[type="hidden"][name^="answers["]]').forEach(el => el.remove());

        // For each collected answer, create a hidden input
        Object.entries(answers).forEach(([questionId, value]) => {
            console.log(value);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `answers[${questionId}]`;
            input.value = value;
            form.appendChild(input);
        });

        // Pause the timer while submitting
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        examPaused = true;

        window.isSubmitting = true;
        window.allowFocusLoss = true;
        antiCheat.deactivate();
        document.getElementById('exam-form').submit();
    }

    function isAllAnswered() {
        let allAnswered = true;
        Questions.forEach((q) => {
            if (!q.is_essay) {
                const checked = document.querySelector(`input[name="answers[${q.id}]"]:checked`);
                if (!checked) allAnswered = false;
            } else {
                const ta = document.querySelector(`textarea[name="answers[${q.id}]"]`);
                const val = (ta?.value || '').trim();
                if (val.length === 0) allAnswered = false;
            }
        });
        return allAnswered;
    }

    function updateSubmitEnabled() {
        const enabled = isAllAnswered();
        if (openSubmitBtn) {
            openSubmitBtn.disabled = !enabled;
            openSubmitBtn.classList.toggle('opacity-50', !enabled);
            openSubmitBtn.classList.toggle('cursor-not-allowed', !enabled);
        }
        if (confirmSubmitBtn) {
            confirmSubmitBtn.disabled = !enabled;
        }
    }

    function attachAnswerListeners() {
        document.querySelectorAll('input[type="radio"][name^="answers["]').forEach(input => {
            input.addEventListener('change', () => {
                updateSubmitEnabled();
                answersDirty = true;
                autoSaveDebounced();
            });
        });
        document.querySelectorAll('textarea[name^="answers["]').forEach(ta => {
            ta.addEventListener('input', () => {
                updateSubmitEnabled();
                answersDirty = true;
                autoSaveDebounced();
            });
            ta.addEventListener('change', () => {
                updateSubmitEnabled();
                answersDirty = true;
                autoSaveDebounced();
            });
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function doAutoSave(showToast = true, force = false) {
        if (window.isSubmitting) return;

        collectCurrentAnswers();

        const fingerprint = JSON.stringify(answers);
        if (!force && !answersDirty && fingerprint === lastSavedFingerprint) {
            return;
        }

        if (autoSaveInFlight) {
            autoSaveQueued = true;
            return;
        }

        autoSaveInFlight = true;

        fetch("{{ route('exam.autosave', ['vacancy_id' => $vacancy_id]) }}?batch={{ (int) ($batchNo ?? request('batch', 1)) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                vacancy_id: '{{ $vacancy_id }}',
                user_id: '{{ Auth::id() }}',
                answers: answers
            })
        }).then(res => res.json()).then(data => {
            if (!data.success) return;

            lastSavedFingerprint = fingerprint;
            answersDirty = false;

            if (showToast) { showSaveNotification(); }
        }).catch(() => {}).finally(() => {
            autoSaveInFlight = false;

            if (autoSaveQueued && !window.isSubmitting) {
                autoSaveQueued = false;
                doAutoSave(false, true);
            }
        });
    }

    const autoSaveDebounced = debounce(() => doAutoSave(true), AUTOSAVE_DEBOUNCE_MS);

    function flushAutosaveOnUnload() {
        if (window.isSubmitting || unloadSaveTriggered) return;

        collectCurrentAnswers();
        const fingerprint = JSON.stringify(answers);
        if (!answersDirty && fingerprint === lastSavedFingerprint) return;

        unloadSaveTriggered = true;

        const url = "{{ route('exam.autosave', ['vacancy_id' => $vacancy_id]) }}?batch={{ (int) ($batchNo ?? request('batch', 1)) }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        try {
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('vacancy_id', '{{ $vacancy_id }}');
                formData.append('batch', '{{ (int) ($batchNo ?? request('batch', 1)) }}');
                formData.append('user_id', '{{ Auth::id() }}');

                Object.entries(answers).forEach(([questionId, value]) => {
                    formData.append(`answers[${questionId}]`, value ?? '');
                });

                const sent = navigator.sendBeacon(url, formData);
                if (sent) {
                    lastSavedFingerprint = fingerprint;
                    answersDirty = false;
                    return;
                }
            }
        } catch (_) {}

        try {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                keepalive: true,
                body: JSON.stringify({
                    vacancy_id: '{{ $vacancy_id }}',
                    user_id: '{{ Auth::id() }}',
                    answers: answers
                })
            });
            lastSavedFingerprint = fingerprint;
            answersDirty = false;
        } catch (_) {}
    }

    function renderAllQuestions() {
    container.innerHTML = '';
        Questions.forEach((q, idx) => {
        const qDiv = document.createElement('div');
        qDiv.className = 'bg-white rounded-xl border border-blue-200 shadow-md p-6 mb-6 max-w-3xl mx-auto';
        const questionId = String(q.id);
        const savedValue = Object.prototype.hasOwnProperty.call(normalizedSavedAnswers, questionId)
            ? normalizedSavedAnswers[questionId]
            : '';

        const number = idx + 1;
        let html = `<p class="text-lg font-semibold text-gray-800">QUESTION ${number} of ${Questions.length}</p>`;
        html += `<p class="mb-3 text-gray-700 question-text">${q.question}</p>`;

        if (!q.is_essay) {
            const opts = q.choices;
            html += Object.entries(opts).map(([key, val]) =>
                `<label class="block text-[#002C76] font-semibold">
                    <input type="radio" name="answers[${q.id}]" value="${key}" class="mr-2" ${hasAnswerValue(savedValue) && String(savedValue) === String(key) ? 'checked' : ''}>
                    ${val}
                </label>`
            ).join('');
        } else {
            html += `<textarea name="answers[${q.id}]" class="w-full border border-gray-300 rounded-md p-4 min-h-[150px]" placeholder="Type your answer...">${escapeHtml(savedValue)}</textarea>`;
        }

        qDiv.innerHTML = html;
        container.appendChild(qDiv);
    });

    // Always show submit button at bottom now
    submitContainer.classList.remove('hidden');
    // After rendering inputs, attach listeners and set initial state
    attachAnswerListeners();
    Object.entries(normalizedSavedAnswers).forEach(([questionId, value]) => {
        answers[String(questionId)] = value ?? '';
    });
    collectCurrentAnswers();
    lastSavedFingerprint = JSON.stringify(answers);
    answersDirty = false;
    updateSubmitEnabled();
    if (!restoredNoticeShown && Object.keys(normalizedSavedAnswers).length > 0) {
        restoredNoticeShown = true;
        showSaveNotification();
    }
}

    // Periodic autosave as fallback (silent)
    let periodicAutoSave = setInterval(() => {
        if (!window.isSubmitting && answersDirty) doAutoSave(false);
    }, AUTOSAVE_PERIODIC_MS);

    window.addEventListener('beforeunload', () => {
        flushAutosaveOnUnload();
        if (periodicAutoSave) clearInterval(periodicAutoSave);
    });
    window.addEventListener('pagehide', flushAutosaveOnUnload);
    window.addEventListener('pageshow', () => { unloadSaveTriggered = false; });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            flushAutosaveOnUnload();
        }
    });

    // Ensure confirm button reflects latest state when opening modal
    if (openSubmitBtn) {
        openSubmitBtn.addEventListener('click', () => {
            updateSubmitEnabled();
        });
    }
    // Clear periodic autosave before final submit
    (function wrapSubmit() {
        const original = prepareSubmit;
        window.prepareSubmit = function() {
            if (periodicAutoSave) clearInterval(periodicAutoSave);
            original();
        };
    })();
</script>
@include('partials.loader')
@endsection

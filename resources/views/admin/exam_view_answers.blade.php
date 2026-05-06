@extends('layout.admin')

@section('title', ($userName->name ?? 'Examinee') . ' - Answers')

@push('styles')
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, .font-montserrat {
            font-family: 'Montserrat', sans-serif !important;
        }
    </style>
@endpush

@section('content')
@php
    $canManageExam = auth('admin')->check()
        && \Illuminate\Support\Facades\Gate::forUser(auth('admin')->user())->allows('admin.exam.manage');
    $canResumeExam = $canManageExam && !empty($resumeAction['can_resume']);
@endphp
<div class="px-6 mb-6 flex justify-between items-center font-montserrat">
    <div class="flex items-center gap-4">
        <!-- Back Button -->
        <!-- <button aria-label="Go back" title="Go back"
            class="w-12 h-12 rounded-full bg-[#D8DCE3] flex justify-center items-center text-[#1E3664] hover:bg-[#c0c7d8] transition"
            onclick="triggerBackConfirm()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="#1E3664" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button> -->

        <div>
            <h2 id="applicant-name" class="text-2xl font-bold uppercase text-[#002C76]">{{ $userName->name }}</h2>
            <p class="uppercase text-sm font-semibold text-gray-700 tracking-wide">Examination Answers</p>
            <p class="text-xs font-medium text-gray-500">For Position: <span class="font-semibold text-[#002C76]">{{ $positionTitle->position_title }}</span></p>
            <p class="text-xs font-medium text-gray-500">Examinee Code: <span class="font-semibold">{{ $examineeCode }}</span></p>
            <p class="text-xs font-medium text-gray-500">Tab Violations: <span id="tab-violations-count" class="font-semibold text-red-600">{{ $application->tab_violations ?? 0 }}</span></p>
        </div>
    </div>

    <div class="flex flex-col items-end space-y-1 text-sm text-gray-700 font-montserrat">
        <div class="text-right leading-tight">
            <p><span class="font-medium text-gray-600">Last Refreshed:</span> <span id="last-refreshed">--</span></p>
            <p><span class="font-medium text-gray-600">Score:</span> <span id="score">--</span></p>
            @unless ($canManageExam)
                <p class="text-xs font-semibold text-blue-700">Read-only monitor mode</p>
            @endunless
        </div>
        <div class="flex items-center gap-2">
            <button onclick="fetchAnswers(true)" aria-label="Refresh answers" title="Refresh answers"
                class="mt-2 w-10 h-10 rounded-full bg-blue-600 hover:bg-blue-700 flex justify-center items-center transition shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <polyline points="23 20 23 14 17 14"></polyline>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                </svg>
            </button>
            @if ($canManageExam)
                @if ($canResumeExam)
                    <button type="button"
                        onclick="triggerResumeExamConfirm()"
                        title="Resume exam with {{ $resumeAction['remaining_label'] ?? 'saved' }} remaining"
                        class="mt-2 px-3 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700 transition">
                        Resume Exam
                    </button>
                @endif
                <a href="{{ route('admin.view_exam.pdf', ['vacancy_id' => $vacancy_id, 'user_id' => $user_id]) }}" target="_blank"
                   class="mt-2 px-3 py-2 rounded bg-[#0D2B70] text-white hover:bg-[#002C76] transition">
                   Download PDF
                </a>
            @endif
        </div>
    </div>
</div>


<div class="flex flex-col lg:flex-row w-full gap-6">
    <!-- Tab Switch Log Section - Left Side -->
    <div class="lg:w-1/4 px-6 mt-4">
        <details class="w-full bg-white rounded-xl border border-blue-200 shadow">
            <summary class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-gray-700 flex items-center justify-between">
                <span>Tab Switch Log</span>
                <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="px-4 pb-4">
                <ul id="tab-violation-log" class="space-y-1 list-inside list-disc pl-4">
                    <li class="text-xs text-gray-400">No tab switch logs</li>
                </ul>
            </div>
        </details>

        <details class="w-full bg-white rounded-xl border border-amber-200 shadow mt-4">
            <summary class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-gray-700 flex items-center justify-between">
                <span>Time Tamper Log</span>
                <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="px-4 pb-4">
                <ul id="exam-tamper-log" class="space-y-1 list-inside list-disc pl-4">
                    <li class="text-xs text-gray-400">No time tamper logs</li>
                </ul>
            </div>
        </details>
    </div>

    <!-- Save Scores Form Section - Right Side -->
    <div class="lg:w-3/4 px-6 mt-4">
        <form id="saveScoresForm" action="{{ route('admin.save_result', ['vacancy_id' => $vacancy_id, 'user_id' => $user_id] ) }}" method="POST">
            @csrf
            <input type="hidden" name="result" id="result">
            <div id="question-container" class="bg-white rounded-xl border border-blue-200 shadow p-6 font-montserrat">
                <!-- Question content will be loaded here -->
            </div>
        </form>
    </div>
</div>


@php
    $examRealtimeConnection = (string) config('broadcasting.default');
    $examRealtimeOptions = (array) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.options', []);
    $examRealtimeKey = (string) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.key', '');
    $examRealtimeEnabled = in_array($examRealtimeConnection, ['reverb', 'pusher'], true) && $examRealtimeKey !== '';
    $examRealtimeConfig = [
        'enabled' => $examRealtimeEnabled,
        'key' => $examRealtimeKey,
        'wsHost' => (string) ($examRealtimeOptions['host'] ?? request()->getHost()),
        'wsPort' => (int) ($examRealtimeOptions['port'] ?? 80),
        'wssPort' => (int) ($examRealtimeOptions['port'] ?? 443),
        'forceTLS' => (bool) ($examRealtimeOptions['useTLS'] ?? request()->isSecure()),
    ];
@endphp
@if ($examRealtimeEnabled)
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
@endif
<script>
    const canManageExam = @json($canManageExam);
    const examRealtimeConfig = @json($examRealtimeConfig);
    const examViewVacancyId = @json($vacancy_id);
    const examViewUserId = Number(@json((int) $user_id));
    let Questions = @json($examResults);
    let hasChanges = false;

    // This object stores the updated correctness per question
    const checkedAnswers = {};
    let pollingInterval = null;
    let fetchInFlight = null;
    let fetchQueued = false;
    let fetchTimer = null;
    let realtimeClient = null;
    let realtimeConnected = false;
    const FAST_POLL_MS = 2000;
    const SAFETY_POLL_MS = 15000;

    let correctCount = 0;
    let final_score = 0;
    let highest_score = 0;

    function hasDisplayValue(value) {
        return value !== null && value !== undefined && String(value) !== '';
    }

    function formatManilaDateTime(isoString) {
        if (!isoString) return '-';

        const dt = new Date(isoString);
        if (Number.isNaN(dt.getTime())) return '-';

        const datePart = dt.toLocaleDateString('en-PH', {
            timeZone: 'Asia/Manila',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const timePart = dt.toLocaleTimeString('en-PH', {
            timeZone: 'Asia/Manila',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).replace(/\s/g, '');

        return `${datePart} ${timePart}`;
    }
    function formatDuration(durationSeconds, durationMilliseconds) {
        if (durationMilliseconds !== null && durationMilliseconds !== undefined && durationMilliseconds !== '') {
            const totalMs = Math.max(0, Number(durationMilliseconds) || 0);
            const wholeSeconds = Math.floor(totalMs / 1000);
            const ms = totalMs % 1000;
            return `${wholeSeconds}s ${ms}ms`;
        }

        if (durationSeconds !== null && durationSeconds !== undefined && durationSeconds !== '') {
            return `${durationSeconds}s`;
        }

        return 'Duration not captured';
    }

    function formatViolationType(type) {
        const normalized = String(type || '').trim();
        if (!normalized) return 'Clock Tamper';
        return normalized
            .split('-')
            .map(part => part.charAt(0).toUpperCase() + part.slice(1))
            .join(' ');
    }

    function getPollIntervalMs() {
        return realtimeConnected ? SAFETY_POLL_MS : FAST_POLL_MS;
    }

    function startPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(() => fetchAnswers(false, 'poll'), getPollIntervalMs());
    }

    function queueFetchAnswers(reason = 'queued', delay = 0, isManual = false) {
        if (fetchTimer) clearTimeout(fetchTimer);
        fetchTimer = setTimeout(() => {
            fetchTimer = null;
            fetchAnswers(isManual, reason);
        }, delay);
    }

    function initRealtime() {
        if (!examRealtimeConfig.enabled || typeof window.Pusher === 'undefined') return;

        try {
            realtimeClient = new window.Pusher(examRealtimeConfig.key, {
                wsHost: examRealtimeConfig.wsHost,
                wsPort: examRealtimeConfig.wsPort,
                wssPort: examRealtimeConfig.wssPort,
                forceTLS: !!examRealtimeConfig.forceTLS,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                }
            });

            const participantChannel = realtimeClient.subscribe(`private-exam-participant.${examViewVacancyId}.${examViewUserId}`);
            participantChannel.bind('exam.progress.updated', (event) => {
                if (Number(event?.user_id) !== examViewUserId) return;
                queueFetchAnswers('realtime-event', 80);
            });

            realtimeClient.connection.bind('connected', () => {
                realtimeConnected = true;
                startPolling();
                queueFetchAnswers('realtime-connected', 0);
            });

            realtimeClient.connection.bind('disconnected', () => {
                realtimeConnected = false;
                startPolling();
            });

            realtimeClient.connection.bind('unavailable', () => {
                realtimeConnected = false;
                startPolling();
            });

            realtimeClient.connection.bind('error', () => {
                realtimeConnected = false;
                startPolling();
            });
        } catch (error) {
            console.error('Realtime init failed:', error);
            realtimeConnected = false;
            startPolling();
        }
    }

    function fetchAnswers(isManual = false, reason = 'manual') {
        if (fetchInFlight) {
            fetchQueued = true;
            return fetchInFlight;
        }

        const refreshEl = document.getElementById('last-refreshed');
        if(refreshEl) refreshEl.classList.add('animate-pulse', 'text-blue-600');

        const url = `{{ route('admin.view_exam.json', ['vacancy_id' => $vacancy_id, 'user_id' => $user_id]) }}?_=${Date.now()}`;

        fetchInFlight = fetch(url, {
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const vioEl = document.getElementById('tab-violations-count');
                    if (vioEl && typeof data.tab_violations !== 'undefined') {
                        vioEl.textContent = data.tab_violations;
                    }
                    const logEl = document.getElementById('tab-violation-log');
                    if (logEl && Array.isArray(data.tab_violation_logs)) {
                        logEl.innerHTML = data.tab_violation_logs.map(l => {
                            const startedIso = l.started_at_iso || l.started_at || null;
                            const started = formatManilaDateTime(startedIso);
                            const dur = formatDuration(l.duration_seconds, l.duration_milliseconds);
                            return `<li class="text-xs text-gray-600"><span class="font-semibold">${started}</span> &bull; Duration: <span class="font-semibold">${dur}</span></li>`;
                        }).join('') || '<li class="text-xs text-gray-400">No tab switch logs</li>';
                    }
                    const tamperEl = document.getElementById('exam-tamper-log');
                    if (tamperEl && Array.isArray(data.exam_tamper_logs)) {
                        tamperEl.innerHTML = data.exam_tamper_logs.map(l => {
                            const loggedAt = formatManilaDateTime(l.created_at_iso || null);
                            const type = formatViolationType(l.type);
                            const timezone = l.timezone ? `Timezone: ${l.timezone}` : 'Timezone: -';
                            const drift = (l.clock_drift_ms !== null && l.clock_drift_ms !== undefined && l.clock_drift_ms !== '')
                                ? `Clock drift: ${Number(l.clock_drift_ms)}ms`
                                : 'Clock drift: -';
                            return `<li class="text-xs text-amber-700"><span class="font-semibold">${loggedAt}</span> &bull; ${type} &bull; ${timezone} &bull; ${drift}</li>`;
                        }).join('') || '<li class="text-xs text-gray-400">No time tamper logs</li>';
                    }
                    // Update the Questions data source
                    // We need to be careful not to overwrite 'score' if we want to preserve local edits,
                    // BUT for now let's assume 'Questions' tracks the server state.
                    // The dropdowns are the 'local state'.
                    
                    // Actually, we only care about 'given_answer' updating.
                    const newQuestions = data.examResults;
                    
                    newQuestions.forEach(nq => {
                        const oldQ = Questions.find(oq => oq.id === nq.id);
                        if (oldQ) {
                            oldQ.given_answer = nq.given_answer;
                            oldQ.given_answer_text = nq.given_answer_text;
                            oldQ.is_correct = nq.is_correct;
                            // We don't update 'score' here to avoid messing with manual grading in progress
                            // unless we want to sync concurrent admins. Let's stick to answers.
                        }
                    });

                    updateAnswersUI();
                    
                    if(refreshEl) {
                        refreshEl.textContent = new Date().toLocaleString();
                        refreshEl.classList.remove('animate-pulse', 'text-blue-600');
                    }
                }
            })
            .catch(err => console.error(`Exam fetch error (${reason}):`, err))
            .finally(() => {
                if (refreshEl) {
                    refreshEl.classList.remove('animate-pulse', 'text-blue-600');
                }
                fetchInFlight = null;
                if (fetchQueued) {
                    fetchQueued = false;
                    queueFetchAnswers('queued', 120);
                }
            });

        return fetchInFlight;
    }

    function updateAnswersUI() {
        Questions.forEach(q => {
            // Update Answer Text
            const ansEl = document.getElementById(`answer-text-${q.id}`);
            if (ansEl) {
                if (q.is_essay) {
                     // For essay
                     ansEl.textContent = hasDisplayValue(q.given_answer) ? q.given_answer : 'No answer yet';
                } else {
                     // For MCQ
                     const text = hasDisplayValue(q.given_answer)
                        ? `${q.given_answer}${q.given_answer_text ? ' - ' + q.given_answer_text : ''}`
                        : 'No answer';
                     ansEl.textContent = text;
                }
            }

            // Update Badge (MCQ only)
            if (!q.is_essay) {
                const badgeEl = document.getElementById(`status-badge-${q.id}`);
                const inputEl = document.querySelector(`input[name="scores[${q.id}]"]`);
                
                if (badgeEl) {
                    if (q.is_correct) {
                        badgeEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700';
                        badgeEl.textContent = 'Correct';
                    } else {
                        badgeEl.className = 'text-sm font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700';
                        badgeEl.textContent = 'Incorrect';
                    }
                }
                
                // Also update the hidden input for score if auto-graded
                if (inputEl) {
                    inputEl.value = q.is_correct ? '1' : '0';
                }
            }
        });
        
        // Recompute score based on new auto-grades + current dropdown values
        recomputeScore();
    }

    function recomputeScore() {
        let mcqCorrect = 0;
        let mcqCount = 0;
        let essaySum = 0;
        let essayMax = 0;

        Questions.forEach((q) => {
            if (q.is_essay) {
                const maxVal = (q.essay_max_score && !isNaN(q.essay_max_score)) ? parseInt(q.essay_max_score, 10) : 0;
                essayMax += maxVal;
                const inp = document.getElementById(`score-input-${q.id}`);
                const val = inp && inp.value !== '' ? parseInt(inp.value, 10) : null;
                if (val !== null) essaySum += Math.max(0, Math.min(val, maxVal || val));
            } else {
                mcqCount++;
                if (q.is_correct) mcqCorrect++;
            }
        });

        final_score = mcqCorrect + essaySum;
        highest_score = mcqCount + essayMax;

        const scoreEl = document.getElementById('score');
        if(scoreEl) scoreEl.textContent = `${final_score} / ${highest_score}`;

        const resultEl = document.getElementById('result');
        if(resultEl) resultEl.value = `${final_score} / ${highest_score}`;
    }

    function handleEssayInput(el, maxVal) {
        if (!el) return;
        if (el.value === '') {
            recomputeScore();
            return;
        }
        let v = parseInt(el.value, 10);
        if (isNaN(v)) {
            el.value = '';
            recomputeScore();
            return;
        }
        if (v < 0) v = 0;
        if (maxVal && v > maxVal) v = maxVal;
        el.value = v;
        recomputeScore();
    }


    // Optional: Apply on page load
    document.addEventListener("DOMContentLoaded", () => {
        renderAnswers();
        initRealtime();
        startPolling();
        queueFetchAnswers('initial', 0);
        if (canManageExam) {
            document.addEventListener('input', (e) => {
                if (e.target && e.target.id && e.target.id.startsWith('score-input-')) {
                    hasChanges = true;
                }
            });
            const form = document.getElementById('saveScoresForm');
            if (form) {
                form.addEventListener('submit', () => { hasChanges = false; });
            }
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) return;
        queueFetchAnswers('visibility', 0);
        startPolling();
    });

    function renderAnswers() {
        const container = document.getElementById('question-container');
        const refreshedEl = document.getElementById('last-refreshed');
        const scoreEl = document.getElementById('score');
        container.innerHTML = '';

        Questions.forEach((q, index) => {
            const userAnswer = hasDisplayValue(q.given_answer) ? q.given_answer : 'No answer yet';
            // use manual check if available, otherwise fallback to auto
            const isCorrect = q.is_correct;
            const isEssay = q.is_essay;
            if (isCorrect) correctCount++;

            const div = document.createElement('div');
            div.className = 'bg-white rounded-xl border border-blue-200 shadow-md p-6 mb-6 max-w-3xl mx-auto';

            div.innerHTML = `
                <div class="flex justify-between items-start mb-1">
                    <div>
                        <p class="text-lg font-semibold text-gray-800">QUESTION ${index + 1} of ${Questions.length}</p>
                    </div>
            <div class="flex items-center gap-3">
                        ${!isEssay ? `
                            <input name="scores[${q.id}]" type="hidden" ${isCorrect ? 'value="1"' : 'value="0"'}>
                            <span id="status-badge-${q.id}" class="text-sm font-semibold px-3 py-1 rounded-full ${isCorrect ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                ${isCorrect ? 'Correct' : 'Incorrect'}
                            </span>
                        ` : `
                            <div class="flex items-center gap-3">
                                <label for="score-input-${q.id}" class="text-sm text-gray-700 font-semibold">Score</label>
                                <input
                                    type="number"
                                    name="scores[${q.id}]"
                                    id="score-input-${q.id}"
                                    value="${(q.score !== null && q.score !== undefined && q.score !== '') ? q.score : ''}"
                                    min="0"
                                    ${q.essay_max_score ? `max="${q.essay_max_score}"` : ''}
                                    placeholder="0${q.essay_max_score ? ' - ' + q.essay_max_score : ''}"
                                    class="w-28 text-sm font-semibold px-3 py-1 rounded border border-gray-300 focus:ring-1 focus:ring-[#0D2B70]"
                                    ${!canManageExam ? 'readonly disabled' : ''}
                                    oninput="handleEssayInput(this, ${q.essay_max_score || 0});"
                                />
                                ${q.essay_max_score ? `<span class="text-sm text-gray-500">/ ${q.essay_max_score} pts</span>` : ''}
                            </div>
                        `}
                    </div>
                </div>
                <p class="mb-3 text-gray-700">${q.question}</p>
                ${
                    !isEssay
                    ? `
                        <div class="bg-gray-50 border border-gray-200 rounded px-4 py-3 space-y-1">
                            <p class="text-sm">
                                <span class="font-semibold text-gray-700 mr-1">Examinee Answer:</span>
                                <span id="answer-text-${q.id}" class="text-gray-900">
                                    ${hasDisplayValue(q.given_answer) ? `${q.given_answer}${q.given_answer_text ? ' - ' + q.given_answer_text : ''}` : 'No answer'}
                                </span>
                            </p>
                            <p class="text-sm">
                                <span class="font-semibold text-gray-700 mr-1">Correct Answer:</span>
                                <span class="text-gray-900">
                                    ${hasDisplayValue(q.correct_answer) ? `${q.correct_answer}${q.correct_answer_text ? ' - ' + q.correct_answer_text : ''}` : '-'}
                                </span>
                            </p>
                        </div>
                      `
                    : `
                        <div class="bg-gray-50 border border-gray-200 rounded px-4 py-3">
                            <p class="whitespace-pre-line">
                                <span class="font-semibold text-gray-700 mr-1">Examinee Answer:</span>
                                <span id="answer-text-${q.id}">${userAnswer}</span>
                            </p>
                        </div>
                      `
                }
            `;


            container.appendChild(div);

            // nothing extra for essay here; input handler manages clamping

        });

        // Save score + time
        if(refreshedEl) refreshedEl.textContent = new Date().toLocaleString();
        if(scoreEl) scoreEl.textContent = `${final_score} / ${highest_score}`;

        // Show Save Score button
        if (canManageExam) {
            const saveBtn = document.createElement('div');
            saveBtn.className = 'flex justify-end mt-8 max-w-3xl mx-auto';
            saveBtn.innerHTML = `
                <button type="button" onclick="triggerSaveScoresConfirm()"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
                    Save Score
                </button>
            `;
            container.appendChild(saveBtn);
        }
        recomputeScore();
    }

    function updateCorrect(questionId, isChecked) {
        checkedAnswers[questionId] = isChecked;
        renderAnswers(); // re-render to update tag color/score
    }

    /*function saveScore() {
        const results = Questions.map(q => ({
            question_id: q.id,
            is_correct: checkedAnswers[q.id] ?? (
                mockAnswers[q.id]?.trim().toLowerCase() === correctAnswers[q.id]?.trim().toLowerCase()
            )
        }));

        console.log('Saving score:', results);

        showAppToast('Score saved successfully! (Check console for data)');
        // TODO: Send via AJAX to backend
    }*/

    function triggerSaveScoresConfirm() {
        if (!canManageExam) return;
        window.dispatchEvent(new CustomEvent('open-save-scores-confirm'));
    }
    function triggerResumeExamConfirm() {
        if (!canManageExam) return;
        window.dispatchEvent(new CustomEvent('open-resume-exam-confirm'));
    }
    function triggerBackConfirm() {
        if (hasChanges) {
            window.dispatchEvent(new CustomEvent('open-leave-confirm'));
        } else {
            window.history.back();
        }
    }
    window.addEventListener('confirm-save-scores', () => {
        const f = document.getElementById('saveScoresForm');
        if (f) f.submit();
    });
    window.addEventListener('confirm-resume-exam', () => {
        fetch(`{{ route('admin.exam.resume', ['vacancy_id' => $vacancy_id, 'user_id' => $user_id]) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to resume exam.');
            }
            return data;
        })
        .then((data) => {
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(data.message || 'Exam resumed successfully.', 'success');
            }
            window.location.href = `{{ route('admin.manage_exam', ['vacancy_id' => $vacancy_id]) }}`;
        })
        .catch((error) => {
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(error.message || 'Unable to resume the exam.', 'error');
            }
        });
    });
    window.addEventListener('confirm-leave', () => {
        window.history.back();
    });
</script>

@include('partials.loader')

<x-confirm-modal 
    title="Save Scores"
    message="Save the scores to the database?"
    event="open-save-scores-confirm"
    confirm="confirm-save-scores"
/>
<x-confirm-modal 
    title="Unsaved Changes"
    message="You have unsaved changes. Leave without saving?"
    event="open-leave-confirm"
    confirm="confirm-leave"
/>
<x-confirm-modal
    title="Resume Exam"
    message="Reopen this applicant's submitted exam attempt from saved progress and restore the remaining time? Use this when a submission happened accidentally."
    event="open-resume-exam-confirm"
    confirm="confirm-resume-exam"
    confirmText="Resume"
    tone="success"
/>

@endsection

@extends('layout.exam_user')

@push('styles')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;     /* Firefox */
    }

    ::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    .flex-1 {
        height: 100%;
    }

    main {
        overflow: hidden !important;
        height: 100% !important;
    }
</style>
@endpush

@section('scroll_class', 'flex-1 h-full')

@section('content')
<div class="flex-1 flex items-stretch justify-center h-full">
    <div class="flex gap-6 w-full">
        <!-- Left card with exam details -->
        <div class="flex-1 bg-white rounded-xl shadow-md border border-blue-300 p-8 flex flex-col justify-center items-center">
            <div class="flex flex-col items-center text-center space-y-2">
                <h3 class="text-3xl font-extrabold text-black">ENGINEER III</h3>
                <p class="text-lg font-semibold tracking-widest uppercase text-gray-700">EXAMINATION</p>
                <p class="text-gray-700 text-lg">July 3, 2024 | 10:00 AM</p>
                <p class="text-gray-700 text-lg mb-4">DILG-CAR Regional Office</p>


                <div id="waitingMessage" class="mt-6 flex items-center gap-2">
                    <p class="text-xl font-black text-blue-600">
                        You have successfully submitted your examination. <br>
                        You may now close this window. <br>
                        Thank you!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.loader')
@endsection

@php
    $examRealtimeConnection = (string) config('broadcasting.default');
    $examRealtimeOptions = (array) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.options', []);
    $examRealtimeKey = (string) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.key', '');
    $examRealtimeEnabled = auth()->check() && in_array($examRealtimeConnection, ['reverb', 'pusher'], true) && $examRealtimeKey !== '';
    $examRealtimeConfig = [
        'enabled' => $examRealtimeEnabled,
        'key' => $examRealtimeKey,
        'wsHost' => (string) ($examRealtimeOptions['host'] ?? request()->getHost()),
        'wsPort' => (int) ($examRealtimeOptions['port'] ?? 80),
        'wssPort' => (int) ($examRealtimeOptions['port'] ?? 443),
        'forceTLS' => (bool) ($examRealtimeOptions['useTLS'] ?? request()->isSecure()),
    ];
@endphp

@push('scripts')
    @if ($examRealtimeEnabled)
        <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    @endif
    <script>
        (function () {
            const examRealtimeConfig = @json($examRealtimeConfig);
            const examVacancyId = @json($vacancy_id ?? null);
            const examUserId = Number(@json((int) (auth()->id() ?? 0)));
            const resumeRedirectUrl = @json(isset($vacancy_id) ? route('user.exam_question_page', ['vacancy_id' => $vacancy_id]) : null);
            const attemptStatusUrl = @json(isset($vacancy_id) ? route('exam.attempt_status', ['vacancy_id' => $vacancy_id]) : null);
            const waitingMessage = document.getElementById('waitingMessage');
            let realtimeClient = null;
            let redirectScheduled = false;
            let pollTimer = null;
            let pollInFlight = false;

            if (!examVacancyId || !examUserId || !resumeRedirectUrl || !attemptStatusUrl) {
                return;
            }

            function redirectToExam() {
                if (redirectScheduled) return;
                redirectScheduled = true;

                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }

                if (waitingMessage) {
                    waitingMessage.innerHTML = `
                        <p class="text-xl font-black text-emerald-600">
                            Your exam has been resumed by the administrator.<br>
                            Redirecting you back to the examination now.
                        </p>
                    `;
                }

                if (typeof window.showAppToast === 'function') {
                    window.showAppToast('Your exam has been resumed. Redirecting to the examination.', 'success', 2500);
                }

                setTimeout(() => {
                    window.location.href = resumeRedirectUrl;
                }, 700);
            }

            function checkAttemptStatus() {
                if (redirectScheduled || pollInFlight) return;
                pollInFlight = true;

                fetch(attemptStatusUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || `Attempt status check failed (${response.status})`);
                    }
                    return data;
                })
                .then((data) => {
                    if (data && data.resume_available && data.redirect_url) {
                        redirectToExam();
                    }
                })
                .catch((error) => {
                    console.error('Thank-you attempt-status poll failed:', error);
                })
                .finally(() => {
                    pollInFlight = false;
                });
            }

            function initRealtime() {
                if (!examRealtimeConfig.enabled || typeof window.Pusher === 'undefined') {
                    return;
                }

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

                    const participantChannel = realtimeClient.subscribe(`private-exam-participant.${examVacancyId}.${examUserId}`);
                    participantChannel.bind('exam.progress.updated', (event) => {
                        if (Number(event?.user_id) !== examUserId) return;

                        const eventType = String(event?.type || '').toLowerCase();
                        const eventStatus = String(event?.status || '').toLowerCase();

                        if (eventType === 'resumed' || eventStatus === 'in-progress') {
                            redirectToExam();
                        }
                    });
                } catch (error) {
                    console.error('Thank-you realtime init failed:', error);
                }
            }

            initRealtime();
            checkAttemptStatus();
            pollTimer = setInterval(checkAttemptStatus, 3000);
        })();
    </script>
@endpush

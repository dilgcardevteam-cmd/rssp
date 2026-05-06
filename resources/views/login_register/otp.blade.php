<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verification Code - DILG CAR Recruitment and Selection Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />
    @include('partials.global_toast')

</head>

@if (!isset($status))
    <script>
        window.location.href = "{{ route('register.form') }}";
    </script>
@endif

<body class="relative min-h-screen overflow-x-hidden bg-[#031029] p-4 md:p-6">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(24,99,156,0.78)_0%,rgba(255,255,255,0.012)_24%,rgba(255,255,255,0)_100%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(3,13,33,0.28)_0%,rgba(3,13,33,0)_44%,rgba(3,13,33,0.2)_100%)]"></div>
        <div class="absolute inset-0 opacity-35 bg-[radial-gradient(circle_at_76%_24%,rgba(255,255,255,0.16)_0%,rgba(255,255,255,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,transparent_24%)]"></div>
        <div class="absolute inset-0 opacity-20 mix-blend-soft-light bg-[radial-gradient(rgba(255,255,255,0.45)_0.55px,transparent_0.55px)] [background-size:9px_9px]"></div>
    </div>
    <div aria-hidden="true" class="pointer-events-none absolute right-[4%] top-1/2 z-0 h-[min(40rem,46vw)] w-[min(40rem,46vw)] -translate-y-1/2 rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.34)_0%,rgba(96,165,250,0.16)_34%,rgba(59,130,246,0.03)_62%,rgba(59,130,246,0)_72%)] blur-[14px]"></div>

    <div class="relative z-10 mx-auto flex min-h-[calc(100vh-1.5rem)] w-full max-w-6xl items-center justify-center">
        <div class="grid w-full overflow-hidden rounded-[1.9rem] border border-white/20 bg-[linear-gradient(140deg,rgba(255,255,255,0.96)_0%,rgba(247,251,255,0.96)_100%)] shadow-[0_30px_85px_rgba(2,9,25,0.37)] backdrop-blur-[10px] lg:grid-cols-[1fr_0.95fr]">
            <section class="relative overflow-hidden bg-[linear-gradient(145deg,rgba(255,255,255,0.1)_0%,rgba(255,255,255,0)_36%),linear-gradient(175deg,#081c47_0%,#0d2b70_54%,#18468f_100%)] px-6 py-9 text-white sm:px-10 lg:px-12">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_16%_14%,rgba(245,200,75,0.12)_0%,rgba(245,200,75,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,rgba(255,255,255,0.03)_20%,rgba(255,255,255,0)_46%)] opacity-70"></div>
                <div class="mx-auto flex h-full max-w-xl flex-col justify-center">
                    <div class="mb-8 flex items-center gap-4">
                        <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-14 w-14 rounded-full bg-white/10 p-1 shadow-[0_10px_20px_rgba(2,12,34,0.25)]" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.34em] text-blue-100">DILG CAR</p>
                            <h2 class="mt-1 font-['Space_Grotesk'] text-2xl font-extrabold tracking-tight">OTP Verification</h2>
                        </div>
                    </div>

                    <p class="max-w-lg text-sm leading-relaxed text-blue-100 sm:text-base">
                        A verification code was sent to your email. Enter the 6-digit OTP to continue account verification.
                    </p>

                    <p class="mt-10 text-sm font-semibold tracking-[0.12em] text-yellow-300">
                        MATINO. MAHUSAY. MAAASAHAN.
                    </p>
                </div>
            </section>

            <section class="bg-[linear-gradient(180deg,rgba(255,255,255,0.82)_0%,rgba(248,251,255,0.92)_100%)] px-6 py-9 sm:px-10 lg:px-12">
                <div class="mx-auto max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Secure Access</p>
                    <h3 class="mt-2 font-['Space_Grotesk'] text-3xl font-extrabold tracking-tight text-[#0D2B70]">Enter Verification Code</h3>
                    <p class="mt-2 text-sm text-slate-600">Code sent to <span class="font-semibold">{{ $email ?? old('email') }}</span></p>

                    @if (!empty($fallbackOtp))
                        <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800">
                            <p class="text-xs font-semibold uppercase tracking-[0.1em]">Local OTP Fallback</p>
                            <p class="mt-1 text-sm">Email sending failed on local SMTP. Use this OTP:</p>
                            <p class="mt-2 text-xl font-extrabold tracking-[0.2em]">{{ $fallbackOtp }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('otp_check', [], false) }}" class="mt-7 space-y-5" autocomplete="off">
                        @csrf
                        <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <input
                                required
                                type="text"
                                name="otp"
                                inputmode="numeric"
                                pattern="\d*"
                                maxlength="6"
                                placeholder="Enter 6-digit OTP"
                                autocomplete="one-time-code"
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] py-3 pl-12 pr-4 text-center text-lg tracking-[0.35em] text-[#0D2B70] outline-none transition hover:border-[#b5c8e7] hover:bg-white focus:border-[#0D2B70] focus:bg-white focus:ring-2 focus:ring-[#0D2B70]/20"
                            />
                        </div>

                        @if ($errors->any())
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                                <ul class="list-disc pl-5 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                            <p id="timer" class="text-sm text-slate-700">
                                Resend OTP in <span id="countdown" class="font-bold text-[#0D2B70]">00:30</span>
                            </p>
                            <a href="#" id="resend-link" class="hidden text-sm font-semibold text-[#0D2B70] hover:underline">
                                Resend Code
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-[0.85rem] bg-[linear-gradient(135deg,#0d2b70_0%,#174493_100%)] px-4 py-3 text-sm font-semibold text-white shadow-[0_9px_20px_rgba(13,43,112,0.26)] transition hover:-translate-y-[1px] hover:brightness-[1.02] hover:shadow-[0_12px_26px_rgba(13,43,112,0.35)]">
                            Verify OTP
                        </button>

                        <p class="text-center text-sm text-slate-600">
                            <a href="{{ route('login.form', [], false) }}" class="use-loader font-semibold text-[#0D2B70] hover:underline">Back to Login</a>
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        const countdownEl = document.getElementById('countdown');
        const resendLink = document.getElementById('resend-link');
        const timerEl = document.getElementById('timer');
        const defaultCooldown = 30;
        const storageKey = 'register_otp_resend_available_at';
        const serverNowMs = {{ (int) ($serverNowTs ?? now()->timestamp) }} * 1000;
        const bootPerfNowMs = performance.now();
        const serverResendAvailableAt = {{ (int) ($resendAvailableAtTs ?? now()->timestamp) }} * 1000;
        const storedResendAvailableAt = Number(sessionStorage.getItem(storageKey) || 0);
        const initialResendAvailableAt = Math.max(serverResendAvailableAt, storedResendAvailableAt);
        let resendAvailableAt = initialResendAvailableAt;
        let timerInterval = null;

        function nowFromServerClockMs() {
            return serverNowMs + (performance.now() - bootPerfNowMs);
        }

        function secondsLeft(targetMs) {
            return Math.max(0, Math.ceil((targetMs - nowFromServerClockMs()) / 1000));
        }

        function renderCountdown() {
            const countdown = secondsLeft(resendAvailableAt);
            const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
            const seconds = String(countdown % 60).padStart(2, '0');
            countdownEl.textContent = `${minutes}:${seconds}`;

            if (countdown <= 0) {
                clearInterval(timerInterval);
                sessionStorage.removeItem(storageKey);
                timerEl.classList.add('hidden');
                resendLink.classList.remove('hidden');
                return;
            }
        }

        function startCooldownAbsolute(targetTimestampMs) {
            resendAvailableAt = Number(targetTimestampMs) || 0;

            if (resendAvailableAt <= nowFromServerClockMs()) {
                clearInterval(timerInterval);
                sessionStorage.removeItem(storageKey);
                timerEl.classList.add('hidden');
                resendLink.classList.remove('hidden');
                return;
            }

            clearInterval(timerInterval);
            sessionStorage.setItem(storageKey, String(Math.round(resendAvailableAt)));
            resendLink.classList.add('hidden');
            timerEl.classList.remove('hidden');
            renderCountdown();
            timerInterval = setInterval(renderCountdown, 1000);
        }

        function startCooldownBySeconds(seconds) {
            const safeSeconds = Math.max(0, Number(seconds) || defaultCooldown);
            const nextAllowedAt = nowFromServerClockMs() + (safeSeconds * 1000);
            startCooldownAbsolute(nextAllowedAt);
        }

        startCooldownAbsolute(initialResendAvailableAt);

        resendLink.addEventListener('click', async function (event) {
            event.preventDefault();

            try {
                const response = await fetch("{{ route('otp_resend', [], false) }}", {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: '{{ $email ?? old('email') }}' })
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const retryAfter = Number(data.retry_after || defaultCooldown);
                    if (response.status === 429) {
                        showAppToast(data.message || `Please wait ${retryAfter} seconds.`);
                        const serverNextAllowed = Number(data.resend_available_at || 0) * 1000;
                        if (serverNextAllowed > 0) {
                            startCooldownAbsolute(serverNextAllowed);
                        } else {
                            startCooldownBySeconds(retryAfter);
                        }
                        return;
                    }
                    throw new Error(data.message || 'Resend failed');
                }

                showAppToast(data.message || 'New OTP sent successfully.');
                const serverNextAllowed = Number(data.resend_available_at || 0) * 1000;
                if (serverNextAllowed > 0) {
                    startCooldownAbsolute(serverNextAllowed);
                } else {
                    startCooldownBySeconds(Number(data.retry_after || defaultCooldown));
                }
            } catch (error) {
                console.error('Resend error:', error);
                timerEl.classList.remove('hidden');
                timerEl.innerHTML = '<span class="text-sm text-red-600">Failed to resend OTP. Try again.</span>';
            }
        });
    </script>

    @include('partials.loader')
</body>
</html>

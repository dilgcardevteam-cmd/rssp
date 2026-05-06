<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verification Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    @include('partials.global_toast')
</head>

<body class="flex h-screen flex-col bg-white font-['Montserrat']">
    <!-- Header Bar -->
    <header class="bg-[#002b6d] flex items-center h-20 px-6 space-x-6">
        <div class="flex-shrink-0">
            <img
                src="{{ asset('images/dilg_logo.png') }}"
                alt="DILG Logo"
                class="mx-auto mb-5 mt-5 max-w-[67px]"
                loading="lazy"
            />
        </div>
        <div class="flex flex-col text-white leading-tight max-w-lg">
            <span class="text-sm font-bold">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</span>
            <span class="text-xs opacity-70">CORDILLERA ADMINISTRATIVE REGION</span>
            <span class="text-xs font-bold text-yellow-400">
                RECRUITMENT SELECTION AND PLACEMENT PORTAL
            </span>
        </div>
    </header>

    <!-- Main content -->
    <main class="flex-grow flex justify-center items-center">
        <form method="POST" action="{{ route('forgot.password.verify.otp') }}">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">
            <div class="bg-[#002b6d] rounded-3xl py-5 px-8 flex flex-col items-center shadow-md w-auto h-auto">
                <h1 class="mb-1 mt-10 text-center text-xl font-bold text-white">VERIFICATION CODE</h1>
                <p class="mb-10 max-w-xs text-center text-sm text-white">
                    We have sent the OTP code to your email address.<br>
                    OTP expires in 5 minutes, after which you will need to resend a new OTP.
                </p>

                <input
                    required
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    pattern="\d*"
                    maxlength="6"
                    placeholder="Enter verification code"
                    class="w-full max-w-xs rounded-full px-6 py-2 text-center placeholder:font-semibold placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                @if ($errors->any())
                    <div class="mb-3 mt-3 text-sm text-red-500">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-4 text-center text-xs text-white">
                    <span id="timer">
                        Resend OTP in <span id="countdown"></span>
                    </span>
                    <a href="#"
                        id="resend-link"
                        class="font-bold hover:underline hidden text-yellow-300">
                        RESEND CODE
                    </a>
                </div>

                <button
                    class="bg-yellow-400 hover:bg-yellow-500 mb-10 text-gray-700 font-semibold rounded-full mt-6 py-2 px-14 shadow-md focus:outline-none focus:ring-4 focus:ring-blue-300"
                    type="submit">
                    NEXT
                </button>
            </div>
        </form>
    </main>

    <script>
        let countdownEl = document.getElementById('countdown');
        const resendLink = document.getElementById('resend-link');
        const timerSpan = document.getElementById('timer');
        const defaultCooldown = 30;
        const storageKey = 'forgot_password_otp_resend_available_at';
        const serverNowMs = {{ (int) ($serverNowTs ?? now()->timestamp) }} * 1000;
        const bootPerfNowMs = performance.now();
        const serverResendAvailableAtMs = {{ (int) ($resendAvailableAtTs ?? now()->timestamp) }} * 1000;
        const storedResendAvailableAtMs = Number(sessionStorage.getItem(storageKey) || 0);
        let resendAvailableAtMs = Math.max(serverResendAvailableAtMs, storedResendAvailableAtMs);
        let timerInterval = null;

        function nowFromServerClockMs() {
            return serverNowMs + (performance.now() - bootPerfNowMs);
        }

        function secondsLeft(targetMs) {
            return Math.max(0, Math.ceil((targetMs - nowFromServerClockMs()) / 1000));
        }

        function renderCountdown() {
            const countdown = secondsLeft(resendAvailableAtMs);
            const minutes = String(Math.floor(countdown / 60)).padStart(2, '0');
            const seconds = String(countdown % 60).padStart(2, '0');

            if (countdownEl) countdownEl.textContent = `${minutes}:${seconds}`;

            if (countdown <= 0) {
                clearInterval(timerInterval);
                sessionStorage.removeItem(storageKey);
                resendLink.classList.remove('hidden');
                if (timerSpan) {
                    timerSpan.innerHTML = '';
                }
            }
        }

        function startCooldownAbsolute(targetTimestampMs) {
            resendAvailableAtMs = Number(targetTimestampMs) || 0;

            if (resendAvailableAtMs <= nowFromServerClockMs()) {
                clearInterval(timerInterval);
                sessionStorage.removeItem(storageKey);
                resendLink.classList.remove('hidden');
                if (timerSpan) {
                    timerSpan.innerHTML = '';
                }
                return;
            }

            sessionStorage.setItem(storageKey, String(Math.round(resendAvailableAtMs)));
            resendLink.classList.add('hidden');
            if (timerSpan && !timerSpan.querySelector('#countdown')) {
                timerSpan.innerHTML = 'Resend OTP in <span id="countdown"></span>';
                countdownEl = document.getElementById('countdown');
            }
            clearInterval(timerInterval);
            renderCountdown();
            timerInterval = setInterval(renderCountdown, 1000);
        }

        function startCooldownBySeconds(seconds) {
            const safeSeconds = Math.max(0, Number(seconds) || defaultCooldown);
            const nextAllowedAt = nowFromServerClockMs() + (safeSeconds * 1000);
            startCooldownAbsolute(nextAllowedAt);
        }

        startCooldownAbsolute(resendAvailableAtMs);

        resendLink.addEventListener('click', async function (event) {
            event.preventDefault();

            try {
                const response = await fetch("{{ route('forgot.password.otp.resend') }}", {
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
                        showAppToast(data.error || data.message || `Please wait ${retryAfter} seconds.`);
                        const serverNextAllowed = Number(data.resend_available_at || 0) * 1000;
                        if (serverNextAllowed > 0) {
                            startCooldownAbsolute(serverNextAllowed);
                        } else {
                            startCooldownBySeconds(retryAfter);
                        }
                        return;
                    }

                    throw new Error(data.error || data.message || 'Resend failed');
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
                if (timerSpan) {
                    timerSpan.innerHTML = '<span class="text-red-500">Failed to resend OTP. Try again later.</span>';
                }
            }
        });
    </script>

    @include('partials.loader')
</body>
</html>

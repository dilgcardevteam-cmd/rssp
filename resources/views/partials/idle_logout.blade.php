@php
    $idleLogoutEnabled = $idleLogoutEnabled ?? false;
    $idleLogoutRoute = $idleLogoutRoute ?? null;
@endphp

@if ($idleLogoutEnabled && filled($idleLogoutRoute))
    <script>
        (function () {
            const IDLE_TIMEOUT_MS = 30 * 60 * 1000;
            const logoutUrl = @json($idleLogoutRoute);
            const csrfToken = @json(csrf_token());

            if (!logoutUrl || !csrfToken) {
                return;
            }

            let logoutTriggered = false;
            let idleTimerId = null;

            const submitLogout = () => {
                if (logoutTriggered) {
                    return;
                }

                logoutTriggered = true;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = logoutUrl;
                form.style.display = 'none';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrfToken;
                form.appendChild(tokenInput);

                document.body.appendChild(form);
                form.submit();
            };

            const resetIdleTimer = () => {
                if (logoutTriggered) {
                    return;
                }

                if (idleTimerId !== null) {
                    clearTimeout(idleTimerId);
                }

                idleTimerId = window.setTimeout(submitLogout, IDLE_TIMEOUT_MS);
            };

            document.addEventListener('mousemove', resetIdleTimer, { passive: true });
            document.addEventListener('mouseenter', resetIdleTimer, { passive: true });

            window.addEventListener('beforeunload', () => {
                if (idleTimerId !== null) {
                    clearTimeout(idleTimerId);
                }
            });

            resetIdleTimer();
        })();
    </script>
@endif
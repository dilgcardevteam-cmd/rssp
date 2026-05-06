<?php
    $idleLogoutEnabled = $idleLogoutEnabled ?? false;
    $idleLogoutRoute = $idleLogoutRoute ?? null;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($idleLogoutEnabled && filled($idleLogoutRoute)): ?>
    <script>
        (function () {
            const IDLE_TIMEOUT_MS = 30 * 60 * 1000;
            const logoutUrl = <?php echo json_encode($idleLogoutRoute, 15, 512) ?>;
            const csrfToken = <?php echo json_encode(csrf_token(), 15, 512) ?>;

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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\xampp\htdocs\rhrmspb\resources\views/partials/idle_logout.blade.php ENDPATH**/ ?>
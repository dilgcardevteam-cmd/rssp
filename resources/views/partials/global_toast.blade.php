<script>
    (function () {
        if (window.__globalToastBridgeInit) return;
        window.__globalToastBridgeInit = true;

        const styleId = 'app-global-toast-style';
        const containerId = 'app-global-toast-container';
        const defaultDuration = 4600;
        const maxVisibleToasts = 5;

        function ensureStyles() {
            if (document.getElementById(styleId)) return;

            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                #${containerId} {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 2147483647;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    pointer-events: none;
                    max-width: min(460px, calc(100vw - 24px));
                }
                .app-toast {
                    --toast-accent: #2563eb;
                    --toast-border: #bfdbfe;
                    --toast-bg: #eff6ff;
                    --toast-icon-bg: rgba(37, 99, 235, 0.12);
                    --toast-icon: #1d4ed8;
                    pointer-events: auto;
                    position: relative;
                    display: grid;
                    grid-template-columns: 34px 1fr auto;
                    align-items: start;
                    gap: 12px;
                    border: 1px solid var(--toast-border);
                    border-radius: 14px;
                    background: linear-gradient(120deg, var(--toast-bg) 0%, #ffffff 66%);
                    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
                    padding: 12px 14px 14px;
                    overflow: hidden;
                    transform: translateX(26px) scale(0.98);
                    opacity: 0;
                    transition: opacity 0.22s ease, transform 0.22s ease;
                    font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
                }
                .app-toast::before {
                    content: '';
                    position: absolute;
                    left: 0;
                    top: 0;
                    bottom: 0;
                    width: 4px;
                    background: var(--toast-accent);
                }
                .app-toast.show {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                }
                .app-toast.hide {
                    opacity: 0;
                    transform: translateX(26px) scale(0.98);
                }
                .app-toast-icon-wrap {
                    width: 34px;
                    height: 34px;
                    border-radius: 999px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    background: var(--toast-icon-bg);
                    color: var(--toast-icon);
                    flex-shrink: 0;
                }
                .app-toast-icon-wrap svg {
                    width: 18px;
                    height: 18px;
                }
                .app-toast-content {
                    min-width: 0;
                    padding-top: 1px;
                }
                .app-toast-title {
                    margin: 0 0 2px;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    color: #64748b;
                }
                .app-toast-message {
                    margin: 0;
                    font-size: 15px;
                    line-height: 1.38;
                    color: #0f172a;
                    word-break: break-word;
                    white-space: pre-line;
                }
                .app-toast-close {
                    border: 1px solid transparent;
                    background: transparent;
                    color: #64748b;
                    cursor: pointer;
                    width: 28px;
                    height: 28px;
                    border-radius: 999px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    margin-top: -2px;
                    transition: background-color 0.16s ease, color 0.16s ease, border-color 0.16s ease;
                }
                .app-toast-close:hover {
                    color: #0f172a;
                    background: rgba(15, 23, 42, 0.06);
                    border-color: rgba(100, 116, 139, 0.22);
                }
                .app-toast-close svg {
                    width: 14px;
                    height: 14px;
                }
                .app-toast-progress {
                    position: absolute;
                    left: 4px;
                    right: 0;
                    bottom: 0;
                    height: 3px;
                    background: rgba(148, 163, 184, 0.22);
                    overflow: hidden;
                }
                .app-toast-progress::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: var(--toast-accent);
                    transform-origin: left center;
                    animation: app-toast-progress var(--app-toast-duration, 4600ms) linear forwards;
                }
                @keyframes app-toast-progress {
                    from { transform: scaleX(1); }
                    to { transform: scaleX(0); }
                }
                .app-toast-success {
                    --toast-accent: #059669;
                    --toast-border: #a7f3d0;
                    --toast-bg: #ecfdf5;
                    --toast-icon-bg: rgba(5, 150, 105, 0.12);
                    --toast-icon: #047857;
                }
                .app-toast-error {
                    --toast-accent: #dc2626;
                    --toast-border: #fecaca;
                    --toast-bg: #fef2f2;
                    --toast-icon-bg: rgba(220, 38, 38, 0.12);
                    --toast-icon: #b91c1c;
                }
                .app-toast-warning {
                    --toast-accent: #d97706;
                    --toast-border: #fde68a;
                    --toast-bg: #fffbeb;
                    --toast-icon-bg: rgba(217, 119, 6, 0.12);
                    --toast-icon: #b45309;
                }
                .app-toast-info {
                    --toast-accent: #2563eb;
                    --toast-border: #bfdbfe;
                    --toast-bg: #eff6ff;
                    --toast-icon-bg: rgba(37, 99, 235, 0.12);
                    --toast-icon: #1d4ed8;
                }
                @media (max-width: 640px) {
                    #${containerId} {
                        left: 12px;
                        right: 12px;
                        top: 12px;
                        max-width: calc(100vw - 24px);
                    }
                    .app-toast {
                        grid-template-columns: 30px 1fr auto;
                        padding: 11px 12px 12px;
                    }
                    .app-toast-icon-wrap {
                        width: 30px;
                        height: 30px;
                    }
                    .app-toast-message {
                        font-size: 14px;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        function ensureContainer() {
            let container = document.getElementById(containerId);
            if (container) return container;
            if (!document.body) return null;

            container = document.createElement('div');
            container.id = containerId;
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
            return container;
        }

        function inferType(message, preferred) {
            const normalizedPreferred = (preferred || '').toString().toLowerCase();
            if (['success', 'error', 'warning', 'info'].includes(normalizedPreferred)) {
                return normalizedPreferred;
            }

            const text = String(message || '').toLowerCase();
            if (/(error|failed|unable|cannot|invalid|denied|forbidden)/.test(text)) return 'error';
            if (/(warning|caution)/.test(text)) return 'warning';
            if (/(success|saved|sent|updated|completed|copied|started)/.test(text)) return 'success';
            return 'info';
        }

        function titleFor(type) {
            if (type === 'success') return 'Success';
            if (type === 'error') return 'Error';
            if (type === 'warning') return 'Warning';
            return 'Info';
        }

        function iconFor(type) {
            if (type === 'success') {
                return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            }
            if (type === 'error') {
                return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>';
            }
            if (type === 'warning') {
                return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8V13" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"/><circle cx="12" cy="17" r="1.2" fill="currentColor"/><path d="M10.29 3.86L1.82 18A2 2 0 003.53 21H20.47A2 2 0 0022.18 18L13.71 3.86A2 2 0 0010.29 3.86Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>';
            }
            return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 10V16" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"/><circle cx="12" cy="7.5" r="1.2" fill="currentColor"/></svg>';
        }

        function closeIcon() {
            return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
        }

        function showAppToast(message, type = 'info', duration = defaultDuration) {
            if (message === undefined || message === null) return;

            ensureStyles();
            const container = ensureContainer();
            if (!container) {
                document.addEventListener('DOMContentLoaded', function onceReady() {
                    document.removeEventListener('DOMContentLoaded', onceReady);
                    showAppToast(message, type, duration);
                });
                return;
            }

            const resolvedType = inferType(message, type);
            const timeout = Number.isFinite(Number(duration)) ? Number(duration) : defaultDuration;
            const safeDuration = Math.max(1000, timeout);

            const toast = document.createElement('div');
            toast.className = `app-toast app-toast-${resolvedType}`;
            toast.style.setProperty('--app-toast-duration', `${safeDuration}ms`);
            toast.setAttribute('role', resolvedType === 'error' ? 'alert' : 'status');
            toast.setAttribute('aria-live', resolvedType === 'error' ? 'assertive' : 'polite');

            const icon = document.createElement('span');
            icon.className = 'app-toast-icon-wrap';
            icon.innerHTML = iconFor(resolvedType);

            const content = document.createElement('div');
            content.className = 'app-toast-content';

            const heading = document.createElement('p');
            heading.className = 'app-toast-title';
            heading.textContent = titleFor(resolvedType);

            const text = document.createElement('p');
            text.className = 'app-toast-message';
            text.textContent = String(message);

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'app-toast-close';
            closeButton.setAttribute('aria-label', 'Dismiss notification');
            closeButton.innerHTML = closeIcon();

            const progress = document.createElement('div');
            progress.className = 'app-toast-progress';

            content.appendChild(heading);
            content.appendChild(text);

            toast.appendChild(icon);
            toast.appendChild(content);
            toast.appendChild(closeButton);
            toast.appendChild(progress);
            container.appendChild(toast);

            while (container.children.length > maxVisibleToasts) {
                container.firstElementChild?.remove();
            }

            requestAnimationFrame(() => toast.classList.add('show'));

            let removed = false;
            const removeToast = () => {
                if (removed) return;
                removed = true;
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 220);
            };

            closeButton.addEventListener('click', removeToast);
            setTimeout(removeToast, safeDuration);
        }

        window.showAppToast = showAppToast;
        window.showToast = window.showToast || showAppToast;
        window.showToastNotification = window.showToastNotification || showAppToast;

        if (!window.__nativeAlert && typeof window.alert === 'function') {
            window.__nativeAlert = window.alert.bind(window);
        }

        window.alert = function (message) {
            const resolvedType = inferType(message, 'info');
            if (typeof window.showNotification === 'function') {
                try {
                    window.showNotification(String(message), resolvedType);
                    return;
                } catch (_) {
                    // Fallback to global toast if another notification system fails.
                }
            }
            showAppToast(message, resolvedType);
        };
    })();
</script>

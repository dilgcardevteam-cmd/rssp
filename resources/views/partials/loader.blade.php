<style>
  .pds-loading-overlay {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(255, 255, 255, 0.1);
    /* Low opacity for glass effect foundation */
    backdrop-filter: blur(8px);
    /* Glassmorphism blur */
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2147483000;
    pointer-events: auto;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
  }

  /* Show state */
  .pds-loading-overlay:not(.hidden) {
    opacity: 1;
    visibility: visible;
  }

  .pds-loading-overlay.hidden {
    display: flex !important;
    /* Override display: none to allow transition */
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
  }

  .pds-loading-overlay.pds-loading-nonblocking {
    pointer-events: none;
    background: transparent;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
  }

  /* Container for spinner and text */
  .pds-loading-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 32px 48px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 24px;
    box-shadow:
      0 4px 6px -1px rgba(0, 0, 0, 0.1),
      0 2px 4px -1px rgba(0, 0, 0, 0.06),
      0 20px 25px -5px rgba(0, 0, 0, 0.1),
      0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: scale(0.95);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .pds-loading-overlay:not(.hidden) .pds-loading-panel {
    transform: scale(1);
  }

  /* Modern Spinner */
  .pds-loading-spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    position: relative;
    background: conic-gradient(from 0deg, rgba(59, 130, 246, 0) 0%, #3b82f6 100%);
    /* Tailwind blue-500 */
    animation: pds-spin 1s linear infinite;
    /* Create the hole in the middle */
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 0);
    mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 0);
  }

  /* Loading text */
  .pds-loading-text {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
    /* Gray 800 */
    letter-spacing: 0.01em;
    animation: pds-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }

  .pds-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  @keyframes pds-spin {
    to {
      transform: rotate(360deg);
    }
  }

  @keyframes pds-pulse {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0.7;
    }
  }
</style>

<div class="pds-loading-overlay hidden" id="loader" role="status" aria-live="polite" aria-busy="false">
  <div class="pds-loading-panel">
    <div class="pds-loading-spinner" aria-hidden="true"></div>
    <div class="pds-loading-text" id="loader-text">Loading...</div>
  </div>
</div>
<div class="pds-sr-only" id="loader-live" aria-live="polite">Ready</div>

<script>
  (function () {
    if (window.__pdsLoadingInitialized) {
      return;
    }
    window.__pdsLoadingInitialized = true;
    const overlay = document.getElementById('loader');
    const live = document.getElementById('loader-live');
    const text = document.getElementById('loader-text');
    const nonBlockingDelay = 10000;
    let unblockTimer = null;

    // Ensure overlay is attached to <body>, so fixed positioning covers the full viewport
    // even when this partial is included inside transformed/scrollable containers.
    if (overlay && overlay.parentElement !== document.body) {
      document.body.appendChild(overlay);
    }
    if (live && live.parentElement !== document.body) {
      document.body.appendChild(live);
    }

    function setLive(message) {
      if (live) live.textContent = message;
      if (text) text.textContent = message;
    }

    function showOverlay() {
      if (!overlay) return;
      overlay.classList.remove('hidden');
      overlay.classList.remove('pds-loading-nonblocking');
      overlay.setAttribute('aria-busy', 'true');
      setLive('Loading...');
      if (unblockTimer) {
        clearTimeout(unblockTimer);
      }
      unblockTimer = setTimeout(() => {
        overlay.classList.add('pds-loading-nonblocking');
        overlay.setAttribute('aria-busy', 'false');
        setLive('Still working...');
      }, nonBlockingDelay);
    }

    function hideOverlay() {
      if (!overlay) return;
      overlay.classList.add('hidden');
      overlay.classList.remove('pds-loading-nonblocking');
      overlay.setAttribute('aria-busy', 'false');
      setLive('Ready');
      if (unblockTimer) {
        clearTimeout(unblockTimer);
        unblockTimer = null;
      }
    }

    function disableSubmitButtons(form) {
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.dataset.loadingDisabled = '1';
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
        // Add visual indication if needed, for now reliance on overlay is enough
        button.style.opacity = '0.7';
        button.style.cursor = 'not-allowed';
      });
    }

    function enableSubmitButtons(form) {
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        if (button.dataset.loadingDisabled === '1') {
          button.disabled = false;
          button.dataset.loadingDisabled = '0';
          button.removeAttribute('aria-disabled');
          button.style.opacity = '';
          button.style.cursor = '';
        }
      });
    }

    function restoreSubmitButtons() {
      document.querySelectorAll('form').forEach((form) => {
        enableSubmitButtons(form);
        form.dataset.retrySubmitting = '0';
      });
    }

    function hideWhenInteractive() {
      // Small delay to ensure smooth transition out
      setTimeout(() => {
        requestAnimationFrame(() => {
          hideOverlay();
        });
      }, 300);
    }

    document.addEventListener('submit', function (event) {
      const form = event.target;
      if (!form || form.dataset.loadingHandled === '1') return;
      if (form.classList.contains('no-spinner')) return;
      if (form.checkValidity && !form.checkValidity()) return;
      // Don't mark handled yet for retries, but do show overlay
      // form.dataset.loadingHandled = '1'; 
      disableSubmitButtons(form);
      showOverlay();

      // Auto-hide if navigation doesn't happen quickly (fallback)
      // or if it's a non-navigating submit (handled elsewhere usually)
      setTimeout(() => {
        if (overlay && overlay.classList.contains('pds-loading-nonblocking')) {
          enableSubmitButtons(form);
        }
      }, nonBlockingDelay + 50);
    }, true);

    // Specific handling for standard submit events that might loop (retry logic from original)
    document.addEventListener('submit', function (event) {
      const form = event.target;
      if (!form || form.dataset.uploadRetry !== '1') return;
      // ... (Original retry logic kept simple here, assuming it's correctly handled by the framework or other scripts)
      // If we need the complex fetch retry logic, we should keep it. 
      // Re-injecting the original retry logic for safety as it was quite specific.

      if (!window.fetch || !window.FormData) return;
      if (form.dataset.retrySubmitting === '1') return;

      if (form.checkValidity && !form.checkValidity()) {
        if (form.reportValidity) form.reportValidity();
        return;
      }

      event.preventDefault();
      form.dataset.retrySubmitting = '1';
      const action = form.action;
      const method = (form.method || 'POST').toUpperCase();
      const maxAttempts = 2;
      let attempt = 0;

      const submitAttempt = async () => {
        const formData = new FormData(form);
        let csrfToken = form.querySelector('input[name="_token"]')?.value
          || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // If no CSRF token, try to fetch one
        if (!csrfToken) {
          try {
            const tokenResponse = await fetch('/csrf-token', {
              method: 'GET',
              credentials: 'same-origin'
            });
            const tokenData = await tokenResponse.json();
            csrfToken = tokenData.token;
            if (csrfToken) {
              formData.set('_token', csrfToken);
              // Update meta tag
              const metaTag = document.querySelector('meta[name="csrf-token"]');
              if (metaTag) {
                metaTag.setAttribute('content', csrfToken);
              }
            }
          } catch (e) {
            console.warn('Could not fetch fresh CSRF token:', e);
          }
        }

        // Ensure CSRF token is included
        if (csrfToken) {
          formData.set('_token', csrfToken);
        }

        try {
          const response = await fetch(action, {
            method,
            body: formData,
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
              ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
            }
          });

          const contentType = response.headers.get('content-type') || '';

          if (response.redirected) {
            window.location.href = response.url;
            return;
          }

          if (response.ok) {
            if (contentType.includes('text/html')) {
              const html = await response.text();
              document.open();
              document.write(html);
              document.close();
              return;
            }
            window.location.reload();
            return;
          }

          // If error, show it
          const html = await response.text();
          
          // Check for CSRF mismatch error
          if (html.includes('CSRF token mismatch') || response.status === 419) {
            // Try to refresh CSRF token and retry once
            if (attempt === 0) {
              try {
                const tokenResponse = await fetch('/csrf-token', {
                  method: 'GET',
                  credentials: 'same-origin'
                });
                const tokenData = await tokenResponse.json();
                const newToken = tokenData.token;
                
                if (newToken) {
                  // Update all CSRF tokens on page
                  document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = newToken;
                  });
                  const metaTag = document.querySelector('meta[name="csrf-token"]');
                  if (metaTag) {
                    metaTag.setAttribute('content', newToken);
                  }
                  
                  // Retry with new token
                  attempt += 1;
                  setTimeout(submitAttempt, 1000);
                  return;
                }
              } catch (e) {
                console.warn('Could not refresh CSRF token:', e);
              }
            }
          }
          
          document.open();
          document.write(html);
          document.close();

        } catch (e) {
          attempt += 1;
          if (attempt < maxAttempts) {
            setTimeout(submitAttempt, 800 * attempt);
            return;
          }
          // Fallback to normal submit
          form.dataset.retrySubmitting = '0';
          form.submit();
        }
      };
      submitAttempt();
    }, true);


    document.querySelectorAll('a.use-loader').forEach((link) => {
      link.addEventListener('click', function (e) {
        // checks if ctrl/meta/shift key is pressed (opening in new tab)
        if (e.ctrlKey || e.metaKey || e.shiftKey) return;
        showOverlay();
      });
    });

    document.querySelectorAll('button.use-loader').forEach((button) => {
      button.addEventListener('click', function () {
        showOverlay();
      });
    });

    document.addEventListener('DOMContentLoaded', hideWhenInteractive);
    window.addEventListener('load', hideWhenInteractive);

    // Robustness: hide the loader if the user navigates back (bfcache) or returns to the tab.
    window.addEventListener('pageshow', function (event) {
      hideOverlay();
      restoreSubmitButtons();
    });

    window.addEventListener('focus', function() {
      // If user comes back from another tab/window, ensure loader is gone.
      setTimeout(hideOverlay, 100);
    });
  })();
</script>

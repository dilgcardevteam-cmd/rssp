<script>
    (function () {
        const blockedInputTypes = new Set([
            'hidden',
            'email',
            'password',
            'file',
            'date',
            'datetime-local',
            'month',
            'week',
            'time',
            'number',
            'range',
            'color',
            'checkbox',
            'radio',
            'submit',
            'reset',
            'button',
            'image',
            'url'
        ]);

        function isUppercaseTarget(field) {
            if (!field || field.disabled || field.readOnly) return false;
            if (field.dataset && field.dataset.uppercase === 'off') return false;

            const tagName = String(field.tagName || '').toLowerCase();
            const fieldName = String(field.name || '').toLowerCase();

            if (fieldName === '_token' || fieldName.includes('otp') || fieldName.includes('password') || fieldName.includes('email')) {
                return false;
            }

            if (tagName === 'textarea') {
                return true;
            }

            if (tagName !== 'input') {
                return false;
            }

            const type = String(field.type || 'text').toLowerCase();
            return !blockedInputTypes.has(type);
        }

        function uppercaseField(field) {
            if (!isUppercaseTarget(field)) return;

            const current = String(field.value ?? '');
            const upper = current.toUpperCase();
            if (current === upper) return;

            let start = null;
            let end = null;
            const canRestoreCaret = document.activeElement === field && typeof field.selectionStart === 'number' && typeof field.selectionEnd === 'number';
            if (canRestoreCaret) {
                start = field.selectionStart;
                end = field.selectionEnd;
            }

            field.value = upper;

            if (canRestoreCaret && start !== null && end !== null) {
                field.setSelectionRange(start, end);
            }
        }

        function normalizeNode(node) {
            if (!node || node.nodeType !== 1) return;

            if (node.matches && node.matches('input, textarea')) {
                uppercaseField(node);
            }

            const fields = node.querySelectorAll ? node.querySelectorAll('input, textarea') : [];
            fields.forEach(uppercaseField);
        }

        document.addEventListener('input', function (event) {
            if (event.isComposing) return;
            uppercaseField(event.target);
        }, true);

        document.addEventListener('change', function (event) {
            uppercaseField(event.target);
        }, true);

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form || !form.querySelectorAll) return;
            form.querySelectorAll('input, textarea').forEach(uppercaseField);
        }, true);

        document.addEventListener('DOMContentLoaded', function () {
            normalizeNode(document.body);

            if (!window.MutationObserver || !document.body) return;
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => normalizeNode(node));
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    })();
</script>

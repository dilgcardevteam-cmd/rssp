<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'event' => 'open-confirm-modal',
    'confirm' => 'confirm-action',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'tone' => 'auto',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'event' => 'open-confirm-modal',
    'confirm' => 'confirm-action',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'tone' => 'auto',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $detectedTone = strtolower((string) $tone);
    if ($detectedTone === 'auto') {
        $haystack = strtolower(trim($title . ' ' . $message));
        $dangerKeywords = ['delete', 'deactivate', 'remove', 'discard', 'revoke', 'disable'];
        $detectedTone = collect($dangerKeywords)->contains(fn ($word) => str_contains($haystack, $word))
            ? 'danger'
            : 'primary';
    }

    $confirmButtonClass = match ($detectedTone) {
        'danger' => 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-200',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 focus-visible:ring-emerald-200',
        default => 'bg-[#0D2B70] hover:bg-[#0A2259] focus-visible:ring-[#0D2B70]/25',
    };

    $accentClass = match ($detectedTone) {
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        default => 'border-blue-200 bg-blue-50 text-blue-700',
    };
?>

<div
    x-data="{ open: false }"
    x-on:<?php echo e($event); ?>.window="open = true"
    @keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[10000] overflow-y-auto bg-slate-900/60 backdrop-blur-md"
            style="display: none;"
        >
        <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>

        <div class="relative flex min-h-full w-full items-center justify-center px-4 py-6">
            <div
                role="dialog"
                aria-modal="true"
                aria-label="<?php echo e($title); ?>"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
            >
        <div class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
            <div class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border <?php echo e($accentClass); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-bold text-slate-900"><?php echo e($title); ?></h2>
                <p class="mt-0.5 text-xs text-slate-500">Please confirm this action before proceeding.</p>
            </div>
            <button
                type="button"
                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-200"
                @click="open = false"
                aria-label="Close"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-5 py-4">
            <p class="text-sm leading-relaxed text-slate-700">
                <?php echo e($message); ?>

            </p>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-200"
                @click="open = false"
            >
                <?php echo e($cancelText); ?>

            </button>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white transition focus:outline-none focus-visible:ring-2 <?php echo e($confirmButtonClass); ?>"
                @click="open = false; $dispatch('<?php echo e($confirm); ?>')"
            >
                <?php echo e($confirmText); ?>

            </button>
        </div>
            </div>
        </div>
        </div>
    </template>
</div>
<?php /**PATH C:\xampp\htdocs\rhrmspb\resources\views/components/confirm-modal.blade.php ENDPATH**/ ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'alertModal',
    'showTrigger' => true,
    'triggerText' => 'Open',
    'triggerClass' => 'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold transition',
    'title' => 'Alert',
    'message' => 'Are you sure?',
    'showCancel' => true,
    'cancelText' => 'Cancel',
    'okText' => 'OK',
    'okAction' => '',
    'content' => '',
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
    'id' => 'alertModal',
    'showTrigger' => true,
    'triggerText' => 'Open',
    'triggerClass' => 'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold transition',
    'title' => 'Alert',
    'message' => 'Are you sure?',
    'showCancel' => true,
    'cancelText' => 'Cancel',
    'okText' => 'OK',
    'okAction' => '',
    'content' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $modalId = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $id) ?: 'alertModal');
    $openEvent = 'open-confirm-' . $modalId;
    $confirmEvent = 'confirm-' . $modalId;
    $hasCustomContent = !empty(trim((string) $content));
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasCustomContent): ?>
    <div
        x-data
        <?php if(!$showTrigger): ?>
            x-init="window.dispatchEvent(new CustomEvent(<?php echo \Illuminate\Support\Js::from($openEvent)->toHtml() ?>))"
        <?php endif; ?>
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrigger): ?>
            <button
                type="button"
                @click="window.dispatchEvent(new CustomEvent(<?php echo \Illuminate\Support\Js::from($openEvent)->toHtml() ?>))"
                class="<?php echo e($triggerClass); ?>">
                <?php echo e($triggerText); ?>

            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal478a1f1aae64dbc95dada8f274f43099 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal478a1f1aae64dbc95dada8f274f43099 = $attributes; } ?>
<?php $component = App\View\Components\ConfirmModal::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('confirm-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ConfirmModal::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($message),'event' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($openEvent),'confirm' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($confirmEvent),'confirmText' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($okText),'cancelText' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cancelText)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal478a1f1aae64dbc95dada8f274f43099)): ?>
<?php $attributes = $__attributesOriginal478a1f1aae64dbc95dada8f274f43099; ?>
<?php unset($__attributesOriginal478a1f1aae64dbc95dada8f274f43099); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal478a1f1aae64dbc95dada8f274f43099)): ?>
<?php $component = $__componentOriginal478a1f1aae64dbc95dada8f274f43099; ?>
<?php unset($__componentOriginal478a1f1aae64dbc95dada8f274f43099); ?>
<?php endif; ?>
    </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty(trim((string) $okAction))): ?>
        <script>
            window.addEventListener(<?php echo json_encode($confirmEvent, 15, 512) ?>, function () {
                <?php echo $okAction; ?>

            });
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php else: ?>
    <div x-data="{ showModal: <?php echo e($showTrigger ? 'false' : 'true'); ?> }">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrigger): ?>
            <button
                type="button"
                @click="showModal = true"
                class="<?php echo e($triggerClass); ?>">
                <?php echo e($triggerText); ?>

            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <template x-teleport="body">
            <div
                x-show="showModal"
                x-cloak
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[10000] overflow-y-auto bg-slate-900/60 backdrop-blur-md"
                style="display: none;"
                @keydown.escape.window="showModal = false"
            >
                <div class="absolute inset-0" @click="showModal = false" aria-hidden="true"></div>

                <div class="relative flex min-h-full w-full items-center justify-center px-4 py-6">
                    <div
                        class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
                    >
                    <button
                        type="button"
                        @click="showModal = false"
                        class="absolute right-3 top-3 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-bold text-slate-900"><?php echo e($title); ?></h2>
                    </div>

                    <div class="px-5 py-4">
                        <p class="mb-4 text-sm leading-relaxed text-slate-700"><?php echo $message; ?></p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCancel || !empty($okText)): ?>
                            <div class="flex justify-end gap-2 pb-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCancel): ?>
                                    <button
                                        type="button"
                                        @click="showModal = false"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                                    >
                                        <?php echo e($cancelText); ?>

                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$content): ?>
                                    <button
                                        type="button"
                                        @click="showModal = false; <?php echo e($okAction); ?>;"
                                        class="inline-flex items-center justify-center rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]"
                                    >
                                        <?php echo e($okText); ?>

                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php echo $content; ?>

                    </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\DILG-CAR\resources\views/partials/alerts_template.blade.php ENDPATH**/ ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['icon', 'label', 'href', 'active' => false]));

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

foreach (array_filter((['icon', 'label', 'href', 'active' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a href="<?php echo e($href); ?>"
   class="flex items-center gap-3 px-3 py-3 rounded-md text-base font-semibold use-loader
   <?php echo e($active ? 'bg-[#002C76] text-white' : 'text-[#002C76] hover:bg-[#002C76]/10'); ?>">
    <i data-feather="<?php echo e($icon); ?>" class="w-5 h-5 stroke-[2.5]"></i>
    <span><?php echo e($label); ?></span>
</a>
<?php /**PATH C:\xampp\htdocs\rhrmspb\resources\views/components/mobile-nav-link.blade.php ENDPATH**/ ?>
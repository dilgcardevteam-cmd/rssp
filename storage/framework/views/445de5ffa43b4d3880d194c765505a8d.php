<!-- resources/views/partials/mobile-sidebar.blade.php -->
<div class="lg:hidden">
    <!-- Overlay -->
    <div 
        x-show="mobileSidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/80 z-40" 
        @click="mobileSidebarOpen = false"
        style="display: none;"
        aria-hidden="true"
    ></div>

    <!-- Sidebar -->
    <div 
        x-show="mobileSidebarOpen"
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl flex flex-col h-full"
        style="display: none;"
        role="dialog"
        aria-modal="true"
    >
        <!-- Header -->
        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
            <img src="<?php echo e(asset('images/dilg_logo.png')); ?>" alt="DILG Logo" class="h-10 w-10 rounded-full" />
            <div class="font-bold text-sm text-[#002C76] font-montserrat leading-tight">
                DILG - CAR <br>
                <span class="text-xs font-medium tracking-tight">RECRUITMENT SELECTION AND PLACEMENT PORTAL</span>
            </div>
            <button @click="mobileSidebarOpen = false" class="ml-auto text-gray-600 hover:text-gray-800 p-1">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-1 overflow-y-auto font-montserrat p-4 space-y-2">
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'home','label' => 'Home','active' => request()->routeIs('dashboard_user'),'href' => ''.e(route('dashboard_user')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','label' => 'Home','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard_user')),'href' => ''.e(route('dashboard_user')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'archive','label' => 'Job Vacancies','active' => request()->routeIs('job_vacancy'),'href' => ''.e(route('job_vacancy')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'archive','label' => 'Job Vacancies','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('job_vacancy')),'href' => ''.e(route('job_vacancy')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'user','label' => 'My Applications','active' => request()->routeIs('my_applications'),'href' => ''.e(route('my_applications')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user','label' => 'My Applications','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('my_applications')),'href' => ''.e(route('my_applications')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'file-text','label' => 'Personal Data Sheet','active' => request()->routeIs('display_c1'),'href' => ''.e(route('display_c1', ['simple' => 1])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'file-text','label' => 'Personal Data Sheet','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('display_c1')),'href' => ''.e(route('display_c1', ['simple' => 1])).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'info','label' => 'About This Website','active' => request()->routeIs('about'),'href' => ''.e(route('about')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'info','label' => 'About This Website','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('about')),'href' => ''.e(route('about')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav-link','data' => ['icon' => 'book-open','label' => 'Manual','active' => request()->routeIs('manual.user'),'href' => ''.e(route('manual.user')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'book-open','label' => 'Manual','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('manual.user')),'href' => ''.e(route('manual.user')).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $attributes = $__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__attributesOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e)): ?>
<?php $component = $__componentOriginal100407cb35adcdd0f10ffceb1c5a472e; ?>
<?php unset($__componentOriginal100407cb35adcdd0f10ffceb1c5a472e); ?>
<?php endif; ?>
        </nav>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\rhrmspb\resources\views/partials/mobile-sidebar.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Exam Attendance'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 sm:px-8 py-6 border-b border-slate-100 bg-slate-50">
            <p class="text-xs font-semibold tracking-[0.24em] uppercase text-slate-500">Examination Attendance</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-[#0D2B70]"><?php echo e($vacancy->position_title); ?></h1>
            <p class="mt-2 text-sm text-slate-600">
                Please confirm whether you can attend the scheduled examination.
            </p>
        </div>

        <div class="px-6 sm:px-8 py-6 space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($hasExistingAttendanceResponse)): ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Your exam attendance response has already been recorded. You can still override your attendance below.
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        <?php echo e($examDetail?->date ? \Carbon\Carbon::parse($examDetail->date)->format('F d, Y') : 'To be announced'); ?>

                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Time</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        <?php echo e($examDetail?->time ? \Carbon\Carbon::parse($examDetail->time)->format('h:i A') : 'To be announced'); ?>

                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Venue</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        <?php echo e($examDetail?->place ?: 'To be announced'); ?>

                    </p>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examDetail?->message): ?>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-slate-700">
                    <p class="font-semibold text-[#0D2B70]">Admin Message</p>
                    <p class="mt-2 whitespace-pre-line"><?php echo e($examDetail->message); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current Response</p>
                <p class="mt-2 text-sm font-semibold text-slate-800"><?php echo e($attendanceStatusLabel); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($application->exam_attendance_responded_at): ?>
                    <p class="mt-1 text-xs text-slate-500">
                        Updated <?php echo e($application->exam_attendance_responded_at->format('F d, Y h:i A')); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($application->exam_attendance_remark): ?>
                    <p class="mt-3 text-sm text-slate-700">
                        <span class="font-semibold text-slate-900">Remark:</span>
                        <?php echo e($application->exam_attendance_remark); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <form id="attendanceResponseForm" method="POST" action="<?php echo e(route('exam.attendance.respond', ['vacancy_id' => $vacancy->vacancy_id])); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>

                <div>
                    <p class="text-sm font-semibold text-slate-900">Can you attend the examination?</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="attendance-option flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-green-300 hover:bg-green-50">
                            <input type="radio" name="attendance_status" value="will_attend"
                                <?php echo e(old('attendance_status', $application->exam_attendance_status) === 'will_attend' ? 'checked' : ''); ?>

                                required
                                class="mt-1 h-4 w-4 border-slate-300 text-green-600 focus:ring-green-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">Yes, I will attend</span>
                                <span class="mt-1 block text-xs text-slate-500">You will be eligible to receive the exam link.</span>
                            </span>
                        </label>

                        <label class="attendance-option flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-red-300 hover:bg-red-50">
                            <input type="radio" name="attendance_status" value="will_not_attend"
                                <?php echo e(old('attendance_status', $application->exam_attendance_status) === 'will_not_attend' ? 'checked' : ''); ?>

                                required
                                class="mt-1 h-4 w-4 border-slate-300 text-red-600 focus:ring-red-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">No, I will not attend</span>
                                <span class="mt-1 block text-xs text-slate-500">Please provide a short remark so the admin can review it.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div id="remarkWrap" class="space-y-2">
                    <label for="attendance_remark" class="text-sm font-semibold text-slate-900">Remark<span style="color:red">*</span></label>
                    <textarea id="attendance_remark" name="attendance_remark" rows="4" maxlength="1000"
                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-800 focus:border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]/20"
                        placeholder="Tell us why you cannot attend."><?php echo e(old('attendance_remark', $application->exam_attendance_remark)); ?></textarea>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="<?php echo e(route('dashboard_user')); ?>"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Back to Dashboard
                    </a>
                    <button id="attendanceConfirmButton" type="button"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#0D2B70] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#0A1F4D] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:hover:bg-slate-300"
                        disabled>
                        <?php echo e(!empty($hasExistingAttendanceResponse) ? 'Override Attendance Response' : 'Save Attendance Response'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($component)) { $__componentOriginal478a1f1aae64dbc95dada8f274f43099 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal478a1f1aae64dbc95dada8f274f43099 = $attributes; } ?>
<?php $component = App\View\Components\ConfirmModal::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('confirm-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ConfirmModal::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!empty($hasExistingAttendanceResponse) ? 'Confirm Attendance Override' : 'Confirm Attendance Response'),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!empty($hasExistingAttendanceResponse)
        ? 'You already responded to this attendance prompt. Do you want to override your previous attendance response?'
        : 'Do you want to save your attendance response for this examination?'),'event' => 'open-attendance-confirm-modal','confirm' => 'confirm-attendance-response','confirm-text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!empty($hasExistingAttendanceResponse) ? 'Yes, Override' : 'Yes, Save'),'cancel-text' => 'Cancel']); ?>
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

<script>
    (function () {
        const form = document.getElementById('attendanceResponseForm');
        const confirmButton = document.getElementById('attendanceConfirmButton');
        const remarkWrap = document.getElementById('remarkWrap');
        const remarkField = document.getElementById('attendance_remark');
        const radios = document.querySelectorAll('input[name="attendance_status"]');

        function syncSubmitState() {
            const selected = document.querySelector('input[name="attendance_status"]:checked');
            const requiresRemark = selected && selected.value === 'will_not_attend';
            const hasRemark = remarkField ? remarkField.value.trim() !== '' : false;
            const canSubmit = !!selected && (!requiresRemark || hasRemark);

            if (confirmButton) {
                confirmButton.disabled = !canSubmit;
            }
        }

        function syncAttendanceRemark() {
            const selected = document.querySelector('input[name="attendance_status"]:checked');
            const showRemark = selected && selected.value === 'will_not_attend';

            if (remarkWrap) {
                remarkWrap.style.display = showRemark ? '' : 'none';
            }

            if (remarkField) {
                remarkField.required = !!showRemark;
            }

            syncSubmitState();
        }

        radios.forEach((radio) => radio.addEventListener('change', syncAttendanceRemark));
        remarkField?.addEventListener('input', syncSubmitState);
        syncAttendanceRemark();

        confirmButton?.addEventListener('click', function () {
            window.dispatchEvent(new CustomEvent('open-attendance-confirm-modal'));
        });

        window.addEventListener('confirm-attendance-response', function () {
            form?.requestSubmit();
        });
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\rhrmspb\resources\views/exam/attendance_prompt.blade.php ENDPATH**/ ?>
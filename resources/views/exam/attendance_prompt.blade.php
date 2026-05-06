@extends('layout.app')

@section('title', 'Exam Attendance')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 sm:px-8 py-6 border-b border-slate-100 bg-slate-50">
            <p class="text-xs font-semibold tracking-[0.24em] uppercase text-slate-500">Examination Attendance</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-[#0D2B70]">{{ $vacancy->position_title }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                Please confirm whether you can attend the scheduled examination.
            </p>
        </div>

        <div class="px-6 sm:px-8 py-6 space-y-6">
            @if(!empty($hasExistingAttendanceResponse))
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Your exam attendance response has already been recorded. You can still override your attendance below.
                </div>
            @endif

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ $examDetail?->date ? \Carbon\Carbon::parse($examDetail->date)->format('F d, Y') : 'To be announced' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Time</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ $examDetail?->time ? \Carbon\Carbon::parse($examDetail->time)->format('h:i A') : 'To be announced' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Venue</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ $examDetail?->place ?: 'To be announced' }}
                    </p>
                </div>
            </div>

            @if($examDetail?->message)
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-slate-700">
                    <p class="font-semibold text-[#0D2B70]">Admin Message</p>
                    <p class="mt-2 whitespace-pre-line">{{ $examDetail->message }}</p>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current Response</p>
                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $attendanceStatusLabel }}</p>
                @if($application->exam_attendance_responded_at)
                    <p class="mt-1 text-xs text-slate-500">
                        Updated {{ $application->exam_attendance_responded_at->format('F d, Y h:i A') }}
                    </p>
                @endif
                @if($application->exam_attendance_remark)
                    <p class="mt-3 text-sm text-slate-700">
                        <span class="font-semibold text-slate-900">Remark:</span>
                        {{ $application->exam_attendance_remark }}
                    </p>
                @endif
            </div>

            <form id="attendanceResponseForm" method="POST" action="{{ route('exam.attendance.respond', ['vacancy_id' => $vacancy->vacancy_id]) }}" class="space-y-5">
                @csrf

                <div>
                    <p class="text-sm font-semibold text-slate-900">Can you attend the examination?</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="attendance-option flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-green-300 hover:bg-green-50">
                            <input type="radio" name="attendance_status" value="will_attend"
                                {{ old('attendance_status', $application->exam_attendance_status) === 'will_attend' ? 'checked' : '' }}
                                required
                                class="mt-1 h-4 w-4 border-slate-300 text-green-600 focus:ring-green-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">Yes, I will attend</span>
                                <span class="mt-1 block text-xs text-slate-500">You will be eligible to receive the exam link.</span>
                            </span>
                        </label>

                        <label class="attendance-option flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-red-300 hover:bg-red-50">
                            <input type="radio" name="attendance_status" value="will_not_attend"
                                {{ old('attendance_status', $application->exam_attendance_status) === 'will_not_attend' ? 'checked' : '' }}
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
                        placeholder="Tell us why you cannot attend.">{{ old('attendance_remark', $application->exam_attendance_remark) }}</textarea>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('dashboard_user') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Back to Dashboard
                    </a>
                    <button id="attendanceConfirmButton" type="button"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#0D2B70] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#0A1F4D] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:hover:bg-slate-300"
                        disabled>
                        {{ !empty($hasExistingAttendanceResponse) ? 'Override Attendance Response' : 'Save Attendance Response' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirm-modal
    :title="!empty($hasExistingAttendanceResponse) ? 'Confirm Attendance Override' : 'Confirm Attendance Response'"
    :message="!empty($hasExistingAttendanceResponse)
        ? 'You already responded to this attendance prompt. Do you want to override your previous attendance response?'
        : 'Do you want to save your attendance response for this examination?'"
    event="open-attendance-confirm-modal"
    confirm="confirm-attendance-response"
    :confirm-text="!empty($hasExistingAttendanceResponse) ? 'Yes, Override' : 'Yes, Save'"
    cancel-text="Cancel"
/>

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
@endsection

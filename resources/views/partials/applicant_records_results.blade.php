@php
    $buildApplicantName = static function ($applicant) {
        $pi = $applicant->personalInformation;

        if (!$pi) {
            return $applicant->name ?: 'N/A';
        }

        return trim(
            ($pi->first_name ?? '') . ' ' .
            ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') .
            ($pi->surname ?? '') . ' ' .
            ($pi->name_extension ?? '')
        ) ?: ($applicant->name ?: 'N/A');
    };
@endphp

<span class="hidden" data-total="{{ number_format($applicants->total()) }}"></span>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Applicant</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Email</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">Applications</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Last Applied</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Deletion</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($applicants as $applicant)
                    @php
                        $personalInfo = $applicant->personalInformation;
                        $email = $personalInfo?->email_address ?: $applicant->email ?: 'N/A';
                        $mobile = $personalInfo?->mobile_no ?: $applicant->phone_number ?: 'N/A';
                        $applicantCode = $applicant->applicant_code ?: ('USER-' . $applicant->id);
                        $lastApplied = $applicant->applications_max_created_at
                            ? \Illuminate\Support\Carbon::parse($applicant->applications_max_created_at)->format('M d, Y h:i A')
                            : 'N/A';
                        $deletionDeadline = $applicant->deletion_due_at
                            ? \Illuminate\Support\Carbon::parse($applicant->deletion_due_at)->format('M d, Y h:i A')
                            : null;
                        $deletionRequestAt = $applicant->deletion_requested_by_applicant_at
                            ? \Illuminate\Support\Carbon::parse($applicant->deletion_requested_by_applicant_at)->format('M d, Y h:i A')
                            : null;
                        $deletionRequestReceivedAt = $applicant->deletion_request_received_by_admin_at
                            ? \Illuminate\Support\Carbon::parse($applicant->deletion_request_received_by_admin_at)->format('M d, Y h:i A')
                            : null;
                    @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-5 py-4 align-top">
                            <div class="font-semibold text-slate-800">{{ $buildApplicantName($applicant) }}</div>
                            <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">Applicant ID: {{ $applicantCode }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $mobile }}</div>
                        </td>
                        <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $email }}</td>
                        <td class="px-5 py-4 align-top text-center">
                            <span class="inline-flex min-w-[42px] items-center justify-center rounded-full bg-[#0D2B70]/10 px-3 py-1 text-sm font-semibold text-[#0D2B70]">
                                {{ $applicant->applications_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $lastApplied }}</td>
                        <td class="px-5 py-4 align-top">
                            @if ($deletionDeadline)
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700 ring-1 ring-amber-200">
                                    Set for Deletion
                                </span>
                                <div class="mt-2 text-sm font-medium text-slate-700">Deadline: {{ $deletionDeadline }}</div>
                            @elseif ($deletionRequestAt)
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700 ring-1 ring-rose-200">
                                    Requested by Applicant
                                </span>
                                <div class="mt-2 text-sm font-medium text-slate-700">Requested: {{ $deletionRequestAt }}</div>
                                <div class="mt-1 text-xs text-slate-500">Received: {{ $deletionRequestReceivedAt ?: 'N/A' }}</div>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.applicant_records.show', $applicant) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-[#0D2B70] bg-white px-3 py-2 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white">
                                    View
                                </a>
                                @if ($deletionDeadline)
                                    <button type="button"
                                        data-cancel-applicant-deletion-url="{{ route('admin.applicant_records.cancel', $applicant) }}"
                                        data-cancel-applicant-name="{{ $buildApplicantName($applicant) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-100">
                                        Cancel Deletion
                                    </button>
                                @else
                                    <button type="button"
                                        data-delete-applicant-url="{{ route('admin.applicant_records.destroy', $applicant) }}"
                                        data-schedule-applicant-url="{{ route('admin.applicant_records.schedule', $applicant) }}"
                                        data-delete-applicant-name="{{ $buildApplicantName($applicant) }}"
                                        data-delete-applicant-code="{{ $applicantCode }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-100">
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm font-medium text-slate-500">
                            No applicant records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($applicants->hasPages())
        <div class="border-t border-slate-200 px-5 py-4">
            {{ $applicants->links() }}
        </div>
    @endif
</section>

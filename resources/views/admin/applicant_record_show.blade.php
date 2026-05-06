@extends('layout.admin')
@section('title', 'DILG - Applicant Profile')

@php
    $personalInfo = $applicant->personalInformation;
    $profile = $applicant->profile;
    $applications = $applicant->applications ?? collect();
    $middleInitial = filled($applicant->middle_name) ? strtoupper(mb_substr(trim($applicant->middle_name), 0, 1)) . '.' : '';
    $displayName = trim(implode(' ', array_filter([
        trim($applicant->first_name ?? ''),
        $middleInitial,
        trim($applicant->last_name ?? ''),
    ], fn ($part) => $part !== '')));
    $displayName = $displayName !== '' ? $displayName : ($applicant->name ?: 'N/A');
    $applicantCode = $applicant->applicant_code ?: ('USER-' . $applicant->id);
    $lastApplied = $applicant->applications_max_created_at
        ? \Illuminate\Support\Carbon::parse($applicant->applications_max_created_at)->format('M d, Y h:i A')
        : 'N/A';
    $initials = collect(preg_split('/\s+/', $displayName) ?: [])
        ->filter()
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
    $initials = $initials !== '' ? $initials : 'AP';

    $formatValue = static function ($value, string $fallback = 'N/A'): string {
        if (is_array($value)) {
            $value = collect($value)
                ->flatten()
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->implode(', ');
        }

        $value = trim((string) $value);
        return $value !== '' ? $value : $fallback;
    };

    $formatDate = static function ($value, string $fallback = 'N/A'): string {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return $fallback;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $formatAddress = static function ($value, string $fallback = 'N/A'): string {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        $normalized = str_replace(['{|/|', '/|/', '{*}', '|/'], [' ', ', ', '', ' '], $value);
        $normalized = preg_replace('/\s*,\s*,+/', ', ', (string) $normalized);
        $normalized = preg_replace('/\s+/', ' ', (string) $normalized);
        $normalized = trim((string) $normalized, " ,");

        return $normalized !== '' ? $normalized : $fallback;
    };
@endphp

@section('content')
<main class="mx-auto w-full max-w-6xl" x-data="{ activeTab: 'profile' }">
    <section class="flex items-center gap-4 border-b border-[#0D2B70] pb-3">
        <a href="{{ route('admin.applicant_records.index') }}" class="inline-flex items-center justify-center rounded-full bg-white p-2 text-[#0D2B70] shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Applicant Records</p>
            <h1 class="text-3xl font-semibold text-[#0D2B70]">Profile | {{ $displayName }}</h1>
        </div>
    </section>

    <section class="mt-5 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_18px_50px_-30px_rgba(15,23,42,0.28)]">
        <div class="border-b border-slate-100 bg-[linear-gradient(135deg,#f7fbff_0%,#eef4ff_45%,#ffffff_100%)] px-6 py-7 sm:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4 sm:gap-5">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#0D2B70] text-xl font-bold text-white shadow-lg shadow-[#0D2B70]/20">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-[#0D2B70]/8 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#0D2B70]">
                                Applicant Profile
                            </span>
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 ring-1 ring-emerald-100">
                                {{ $applicant->applications_count }} {{ \Illuminate\Support\Str::plural('Application', $applicant->applications_count) }}
                            </span>
                        </div>
                        <h2 class="mt-3 break-words text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $displayName }}</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                            Review the applicant's account details, Personal Data Sheet preview, and vacancy application history in one place.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[360px] lg:max-w-[420px]">
                    <div class="rounded-2xl bg-white/90 p-4 ring-1 ring-slate-200 backdrop-blur">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Applicant ID</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $applicantCode }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/90 p-4 ring-1 ring-slate-200 backdrop-blur">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Registered</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ optional($applicant->created_at)->format('M d, Y') ?: 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/90 p-4 ring-1 ring-slate-200 backdrop-blur">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Last Applied</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $lastApplied }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-5 sm:px-8">
        <div class="flex flex-wrap gap-2 border-b border-slate-100 pb-4">
            <button type="button" @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-[#0D2B70] text-white shadow-md shadow-[#0D2B70]/20' : 'bg-white text-[#0D2B70] ring-1 ring-slate-200 hover:bg-slate-50'"
                class="rounded-2xl px-4 py-2.5 text-sm font-semibold transition">
                Profile
            </button>
            <button type="button" @click="activeTab = 'pds'"
                :class="activeTab === 'pds' ? 'bg-[#0D2B70] text-white shadow-md shadow-[#0D2B70]/20' : 'bg-white text-[#0D2B70] ring-1 ring-slate-200 hover:bg-slate-50'"
                class="rounded-2xl px-4 py-2.5 text-sm font-semibold transition">
                PDS
            </button>
            <button type="button" @click="activeTab = 'vacancies'"
                :class="activeTab === 'vacancies' ? 'bg-[#0D2B70] text-white shadow-md shadow-[#0D2B70]/20' : 'bg-white text-[#0D2B70] ring-1 ring-slate-200 hover:bg-slate-50'"
                class="rounded-2xl px-4 py-2.5 text-sm font-semibold transition">
                Vacancies Applied
            </button>
        </div>

        <div class="mt-6" x-show="activeTab === 'profile'" x-cloak>
            <div class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="space-y-4">
                    <section class="rounded-3xl border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] p-5 shadow-sm">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Account Details</p>
                                <h3 class="mt-1 text-lg font-semibold text-slate-900">Core applicant account information</h3>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Email</p>
                                <p class="mt-2 break-all text-sm font-semibold text-slate-800">{{ $formatValue($applicant->email) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Contact Number</p>
                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $formatValue($personalInfo?->mobile_no ?: $applicant->phone_number) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Sex</p>
                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $formatValue($personalInfo?->sex ?: $applicant->sex) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Registered At</p>
                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ optional($applicant->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Address and Bio</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Location and profile notes</h3>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Residential Address</p>
                                <p class="mt-2 break-words text-sm leading-6 text-slate-700">{{ $formatAddress($personalInfo?->residential_address) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Permanent Address</p>
                                <p class="mt-2 break-words text-sm leading-6 text-slate-700">{{ $formatAddress($personalInfo?->permanent_address) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 md:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Bio</p>
                                <p class="mt-2 break-words text-sm leading-6 text-slate-700">{{ $formatValue($profile?->bio ?: $applicant->bio) }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-4">
                    <section class="rounded-3xl border border-slate-200 bg-[#0D2B70] p-5 text-white shadow-lg shadow-[#0D2B70]/15">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/65">Quick Snapshot</p>
                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Birth Date</p>
                                <p class="mt-2 text-base font-semibold">{{ $formatDate($personalInfo?->date_of_birth) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Civil Status</p>
                                <p class="mt-2 text-base font-semibold">{{ $formatValue($personalInfo?->civil_status) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Citizenship</p>
                                <p class="mt-2 text-base font-semibold">{{ $formatValue($personalInfo?->citizenship) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Place of Birth</p>
                                <p class="mt-2 break-words text-base font-semibold">{{ $formatValue($personalInfo?->place_of_birth) }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Status Highlights</p>
                        <div class="mt-4 grid gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-medium text-slate-600">Total Applications</span>
                                    <span class="text-2xl font-semibold text-slate-900">{{ $applicant->applications_count }}</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Last Application Activity</p>
                                <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $lastApplied }}</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="mt-6" x-show="activeTab === 'pds'" x-cloak>
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Personal Data Sheet</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">Generated PDF Preview</h3>
                        <p class="mt-1 text-sm text-slate-500">Preview the applicant's generated PDS PDF and download a copy.</p>
                    </div>
                    <a href="{{ route('admin.applicant_records.pds', ['user' => $applicant->id, 'download' => 1, 'force_fpdi' => 1]) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0A235C]">
                        Download PDF
                    </a>
                </div>

                <div class="p-5">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-inner">
                        <iframe
                            title="PDS PDF Preview"
                            src="{{ route('admin.applicant_records.pds', ['user' => $applicant->id, 'preview' => 1, 'force_fpdi' => 1]) }}"
                            class="h-[72vh] w-full bg-white"
                            loading="lazy"
                        ></iframe>
                    </div>
                </div>
            </section>
        </div>

        <div class="mt-6" x-show="activeTab === 'vacancies'" x-cloak>
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Vacancies Applied</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">Application history and vacancy tracking</h3>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-[#0D2B70]/8 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#0D2B70]">
                        {{ $applications->count() }} {{ \Illuminate\Support\Str::plural('Record', $applications->count()) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Vacancy</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Type</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Place of Assignment</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Application Status</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">Applied At</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($applications as $application)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-5 py-4 align-top">
                                        <div class="font-semibold text-slate-800">{{ $formatValue($application->vacancy?->position_title) }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $formatValue($application->vacancy_id) }}</div>
                                    </td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $formatValue($application->vacancy?->vacancy_type) }}</td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $formatValue($application->vacancy?->place_of_assignment) }}</td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 ring-1 ring-slate-200">
                                            {{ $formatValue($application->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ optional($application->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</td>
                                    <td class="px-5 py-4 align-top text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.applicant_status', ['user_id' => $application->user_id, 'vacancy_id' => $application->vacancy_id]) }}"
                                                class="inline-flex items-center justify-center rounded-xl border border-[#0D2B70] bg-white px-2.5 py-1.5 text-xs font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white">
                                                Status
                                            </a>
                                            @php
                                                $hasExamActivity = !is_null($application->answers)
                                                    || !is_null($application->scores)
                                                    || !is_null($application->exam_started_at)
                                                    || !is_null($application->exam_submitted_at);
                                            @endphp
                                            @if ($hasExamActivity)
                                                <a href="{{ route('admin.view_exam', ['vacancy_id' => $application->vacancy_id, 'user_id' => $application->user_id]) }}"
                                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-[#0D2B70] hover:text-[#0D2B70]">
                                                    Exam Result
                                                </a>
                                            @else
                                                <button type="button" disabled
                                                    class="inline-flex cursor-not-allowed items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-400 shadow-sm">
                                                    Exam Result
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm font-medium text-slate-500">
                                        No vacancy applications found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        </div>
    </section>
</main>
@endsection

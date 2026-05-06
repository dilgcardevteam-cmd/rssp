@extends('layout.admin')
@section('title', 'DILG - User Management')
@section('content')

<main class="mx-auto w-full">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">User Management</span>
        </h1>
    </section>

    <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-end">
                <div class="relative w-full lg:max-w-md">
                    <label for="adminSearchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Search
                    </label>
                    <input id="adminSearchInput" type="search" placeholder="Search name, email, office, or role"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-12 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                    <svg class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <button id="adminSearchClear" type="button" hidden
                        class="absolute right-2 top-[39px] -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Clear search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:min-w-[370px]">
                    <div>
                        <label for="adminRoleFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Role
                        </label>
                        <select id="adminRoleFilter"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            <option value="">All roles</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin (HR)</option>
                            <option value="hr_division">HR Division</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div>
                        <label for="adminStatusFilter" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </label>
                        <select id="adminStatusFilter"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            <option value="">All status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending Approval</option>
                            <option value="declined">Declined</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="shrink-0">
                @include('partials.admin_add_account')
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
            <button id="adminFilterReset" type="button" hidden
                class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                Reset filters
            </button>
        </div>
    </section>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Accounts Table</p>
            <span id="pendingCountBadge"
                class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                Pending registrations: <span id="pendingCountValue">0</span>
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1240px] table-fixed text-sm">
                <colgroup>
                    <col class="w-[26%]">
                    <col class="w-[14%]">
                    <col class="w-[22%]">
                    <col class="w-[18%]">
                    <col class="w-[8%]">
                    <col class="w-[12%]">
                </colgroup>
                <thead class="bg-[#0D2B70] text-white">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Email</th>
                        <th class="px-5 py-3 text-left font-semibold">Role</th>
                        <th class="px-5 py-3 text-left font-semibold">Office / Designation</th>
                        <th class="px-5 py-3 text-left font-semibold">Access</th>
                        <th class="px-5 py-3 text-center font-semibold">Status</th>
                        <th class="w-[230px] min-w-[230px] px-5 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminRows" class="divide-y divide-slate-200">
                    @include('partials.admin_list', [
                        'admins' => $admins,
                        'hrDivisionAccessMap' => $hrDivisionAccessMap ?? [],
                        'hrDivisionAccessLabelMap' => $hrDivisionAccessLabelMap ?? [],
                    ])
                </tbody>
            </table>
        </div>
    </section>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
            class="fixed right-5 top-5 z-50 w-full max-w-sm rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg">
            <p class="text-sm font-semibold">Success</p>
            <p class="mt-1 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)" x-show="show" x-transition
            class="fixed right-5 top-5 z-50 w-full max-w-sm rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-800 shadow-lg">
            <p class="text-sm font-semibold">Error</p>
            <ul class="mt-1 list-disc pl-5 text-sm">
                @if(is_array(session('error')))
                    @foreach (session('error') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @else
                    <li>{{ session('error') }}</li>
                @endif
            </ul>
        </div>
    @endif

    <div id="adminApproveModal"
        class="fixed inset-0 z-[9990] hidden items-center justify-center bg-slate-900/55 px-4 py-6 backdrop-blur-sm"
        data-route-template="{{ url('/admin/__ID__/approve') }}">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-[#0D2B70]">Approve Account</h2>
                    <p class="text-xs text-slate-500">Assign role before final approval.</p>
                </div>
                <button type="button" id="adminApproveModalClose"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="adminApproveForm" method="POST" class="js-admin-approve-form no-spinner space-y-4 p-5">
                @csrf
                <p class="text-sm text-slate-700">
                    Approving account:
                    <span id="adminApproveTargetName" class="font-semibold text-[#0D2B70]">-</span>
                </p>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Assign Role</label>
                    <div class="grid gap-2">
                        <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                            <div class="flex items-start gap-2">
                                <input type="radio" name="approval_role" value="admin" class="mt-0.5 accent-[#0D2B70]" checked>
                                <div>
                                    <p class="text-sm font-semibold text-[#0D2B70]">Admin (HR)</p>
                                    <p class="text-xs text-slate-500">All admin tools except user management.</p>
                                </div>
                            </div>
                        </label>
                        <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                            <div class="flex items-start gap-2">
                                <input type="radio" name="approval_role" value="hr_division" class="mt-0.5 accent-[#0D2B70]">
                                <div>
                                    <p class="text-sm font-semibold text-[#0D2B70]">HR Division</p>
                                    <p class="text-xs text-slate-500">Applicants management only.</p>
                                </div>
                            </div>
                        </label>
                        <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                            <div class="flex items-start gap-2">
                                <input type="radio" name="approval_role" value="viewer" class="mt-0.5 accent-[#0D2B70]">
                                <div>
                                    <p class="text-sm font-semibold text-[#0D2B70]">Viewer</p>
                                    <p class="text-xs text-slate-500">Exam management only.</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" id="adminApproveModalCancel"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Approve Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="hrAccessModal"
        class="fixed inset-0 z-[9990] hidden items-center justify-center bg-slate-900/55 px-4 py-6 backdrop-blur-sm"
        data-route-template="{{ url('/admin/__ID__/hr-vacancy-access') }}">
        <div class="w-full max-w-6xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-[#0D2B70]">HR Division Vacancy Access</h2>
                    <p class="text-xs text-slate-500">Grant or revoke COS vacancy visibility for this HR Division account.</p>
                </div>
                <button type="button" id="hrAccessModalClose"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="hrAccessForm" method="POST" class="js-admin-hr-access-form no-spinner space-y-4 p-5">
                @csrf
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-sm text-slate-700">
                        Updating access for:
                        <span id="hrAccessTargetName" class="font-semibold text-[#0D2B70]">-</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-600">
                        Default is no access. Only COS positions in the granted list will appear for this HR Division user.
                    </p>
                </div>

                <div class="grid gap-4 xl:grid-cols-[1fr_auto_1fr]">
                    <section class="overflow-hidden rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-[#0D2B70]">Available COS Positions</p>
                                <p class="text-xs text-slate-500">Select vacancies to grant access.</p>
                            </div>
                            <span id="hrAccessAvailableCount"
                                class="inline-flex min-w-[2rem] justify-center rounded-full border border-slate-300 bg-white px-2 py-0.5 text-xs font-semibold text-slate-700">
                                0
                            </span>
                        </div>
                        <div class="border-y border-slate-200 bg-white px-3 py-2">
                            <label for="hrAccessAvailableSearch" class="sr-only">Search available COS positions</label>
                            <input id="hrAccessAvailableSearch" type="search" placeholder="Search available positions..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <select id="hrAccessAvailable" multiple size="12"
                            class="hr-transfer-select h-[320px] w-full border-0 bg-white px-2 py-2 text-sm text-slate-700 outline-none focus:ring-0"></select>
                    </section>

                    <div class="flex flex-row items-center justify-center gap-2 xl:flex-col">
                        <button id="hrAccessMoveRight" type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-[#0D2B70] bg-[#0D2B70] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259] disabled:cursor-not-allowed disabled:border-slate-300 disabled:bg-slate-200 disabled:text-slate-500"
                            aria-label="Grant selected positions">
                            Add selected
                        </button>
                        <button id="hrAccessMoveLeft" type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-400"
                            aria-label="Remove selected positions">
                            Remove selected
                        </button>
                        <button id="hrAccessMoveAllLeft" type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:border-slate-300 disabled:bg-slate-100 disabled:text-slate-400"
                            aria-label="Remove all granted positions">
                            Clear all
                        </button>
                    </div>

                    <section class="overflow-hidden rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-[#0D2B70]">Granted COS Positions</p>
                                <p class="text-xs text-slate-500">These vacancies are visible to this user.</p>
                            </div>
                            <span id="hrAccessGrantedCount"
                                class="inline-flex min-w-[2rem] justify-center rounded-full border border-slate-300 bg-white px-2 py-0.5 text-xs font-semibold text-slate-700">
                                0
                            </span>
                        </div>
                        <div class="border-y border-slate-200 bg-white px-3 py-2">
                            <label for="hrAccessGrantedSearch" class="sr-only">Search granted COS positions</label>
                            <input id="hrAccessGrantedSearch" type="search" placeholder="Search granted positions..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <select id="hrAccessGranted" multiple size="12"
                            class="hr-transfer-select h-[320px] w-full border-0 bg-white px-2 py-2 text-sm text-slate-700 outline-none focus:ring-0"></select>
                    </section>
                </div>

                <div id="hrAccessHiddenInputs"></div>

                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                    <p class="text-xs font-medium text-slate-600">
                        Total granted: <span id="hrAccessSummaryCount" class="font-semibold text-[#0D2B70]">0</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" id="hrAccessModalCancel"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Save Access
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <x-confirm-modal title="Confirm Activation" message="Are you sure you want to activate this account?"
        event="open-admin-activate-confirm" confirm="confirm-admin-activate" />
    <x-confirm-modal title="Confirm Deactivation" message="Are you sure you want to deactivate this account?"
        event="open-admin-deactivate-confirm" confirm="confirm-admin-deactivate" />
    <x-confirm-modal title="Confirm Approval" message="Approve this account and apply the selected role?"
        event="open-admin-approve-confirm" confirm="confirm-admin-approve" confirmText="Approve" tone="success" />
    <x-confirm-modal title="Confirm Decline" message="Decline this registration request?"
        event="open-admin-decline-confirm" confirm="confirm-admin-decline" confirmText="Decline" tone="danger" />
    <x-confirm-modal title="Confirm Changes" message="Are you sure you want to save changes to this account?"
        event="open-admin-edit-confirm" confirm="confirm-admin-edit" />
    <x-confirm-modal title="Confirm Access Update" message="Save COS vacancy access for this HR Division account?"
        event="open-admin-hr-access-confirm" confirm="confirm-admin-hr-access" />

    @include('partials.loader')
</main>
@endsection

@push('styles')
<style>
    .role-option {
        transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
    }

    .role-option:has(input[type="radio"]:checked) {
        border-color: #0d2b70;
        background-color: #eff6ff;
        box-shadow: 0 0 0 2px rgba(13, 43, 112, 0.1);
    }

    .hr-transfer-select option {
        border-radius: 8px;
        margin: 1px 0;
        padding: 8px 10px;
    }

    .hr-transfer-select option:checked {
        background: #0d2b70 linear-gradient(0deg, #0d2b70 0%, #0d2b70 100%);
        color: #ffffff;
    }
</style>
@endpush

@push('scripts')
@php
    $cosVacancyOptions = collect($cosVacancies ?? [])->map(function ($vacancy) {
        $status = strtoupper((string) ($vacancy->status ?? ''));
        $label = trim((string) ($vacancy->vacancy_id ?? '')) . ' - ' . trim((string) ($vacancy->position_title ?? 'Untitled Position'));
        if ($status !== '') {
            $label .= ' (' . $status . ')';
        }

        return [
            'vacancy_id' => (string) ($vacancy->vacancy_id ?? ''),
            'label' => $label,
        ];
    })->values();
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let pendingConfirmationForm = null;
        const approveModal = document.getElementById('adminApproveModal');
        const approveForm = document.getElementById('adminApproveForm');
        const approveTargetName = document.getElementById('adminApproveTargetName');
        const approveModalClose = document.getElementById('adminApproveModalClose');
        const approveModalCancel = document.getElementById('adminApproveModalCancel');
        const hrAccessModal = document.getElementById('hrAccessModal');
        const hrAccessForm = document.getElementById('hrAccessForm');
        const hrAccessTargetName = document.getElementById('hrAccessTargetName');
        const hrAccessModalClose = document.getElementById('hrAccessModalClose');
        const hrAccessModalCancel = document.getElementById('hrAccessModalCancel');
        const hrAccessAvailable = document.getElementById('hrAccessAvailable');
        const hrAccessGranted = document.getElementById('hrAccessGranted');
        const hrAccessMoveRight = document.getElementById('hrAccessMoveRight');
        const hrAccessMoveLeft = document.getElementById('hrAccessMoveLeft');
        const hrAccessMoveAllLeft = document.getElementById('hrAccessMoveAllLeft');
        const hrAccessAvailableSearch = document.getElementById('hrAccessAvailableSearch');
        const hrAccessGrantedSearch = document.getElementById('hrAccessGrantedSearch');
        const hrAccessAvailableCount = document.getElementById('hrAccessAvailableCount');
        const hrAccessGrantedCount = document.getElementById('hrAccessGrantedCount');
        const hrAccessSummaryCount = document.getElementById('hrAccessSummaryCount');
        const hrAccessHiddenInputs = document.getElementById('hrAccessHiddenInputs');
        const hrCosVacancies = @json($cosVacancyOptions);
        const sortedHrCosVacancies = [...hrCosVacancies].sort((a, b) =>
            String(a?.label || a?.vacancy_id || '').localeCompare(
                String(b?.label || b?.vacancy_id || ''),
                undefined,
                { sensitivity: 'base' }
            )
        );

        const showLoaderOverlay = () => {
            const overlay = document.getElementById('loader');
            const liveRegion = document.getElementById('loader-live');
            const loaderText = document.getElementById('loader-text');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.remove('pds-loading-nonblocking');
                overlay.setAttribute('aria-busy', 'true');
            }
            if (liveRegion) liveRegion.textContent = 'Loading...';
            if (loaderText) loaderText.textContent = 'Loading...';
        };

        const submitPendingForm = () => {
            if (!pendingConfirmationForm) return;
            const form = pendingConfirmationForm;
            pendingConfirmationForm = null;
            showLoaderOverlay();
            form.submit();
        };

        const createVacancyOption = (vacancyId, label) => {
            const option = document.createElement('option');
            option.value = String(vacancyId || '');
            option.textContent = String(label || vacancyId || '');
            return option;
        };

        const moveSelectedOptions = (source, target) => {
            if (!source || !target) return;
            const selectedOptions = Array.from(source.selectedOptions);
            selectedOptions.forEach((option) => {
                option.selected = false;
                target.appendChild(option);
            });
        };

        const moveAllOptions = (source, target) => {
            if (!source || !target) return;
            Array.from(source.options).forEach((option) => {
                option.selected = false;
                target.appendChild(option);
            });
        };

        const updateHrAccessCounts = () => {
            const availableCount = hrAccessAvailable ? hrAccessAvailable.options.length : 0;
            const grantedCount = hrAccessGranted ? hrAccessGranted.options.length : 0;

            if (hrAccessAvailableCount) {
                hrAccessAvailableCount.textContent = String(availableCount);
            }
            if (hrAccessGrantedCount) {
                hrAccessGrantedCount.textContent = String(grantedCount);
            }
            if (hrAccessSummaryCount) {
                hrAccessSummaryCount.textContent = String(grantedCount);
            }
        };

        const refreshHrTransferButtons = () => {
            const canMoveRight = hrAccessAvailable
                ? Array.from(hrAccessAvailable.selectedOptions).some((option) => !option.hidden)
                : false;
            const canMoveLeft = hrAccessGranted
                ? Array.from(hrAccessGranted.selectedOptions).some((option) => !option.hidden)
                : false;

            if (hrAccessMoveRight) hrAccessMoveRight.disabled = !canMoveRight;
            if (hrAccessMoveLeft) hrAccessMoveLeft.disabled = !canMoveLeft;
            if (hrAccessMoveAllLeft) hrAccessMoveAllLeft.disabled = !hrAccessGranted || hrAccessGranted.options.length === 0;
        };

        const filterSelectOptions = (selectElement, query) => {
            if (!selectElement) return;
            const needle = String(query || '').trim().toLowerCase();
            Array.from(selectElement.options).forEach((option) => {
                const haystack = String(option.textContent || '').toLowerCase();
                const shouldShow = needle === '' || haystack.includes(needle);
                option.hidden = !shouldShow;
                if (!shouldShow) option.selected = false;
            });
        };

        const applyHrAccessFilters = () => {
            filterSelectOptions(hrAccessAvailable, hrAccessAvailableSearch?.value);
            filterSelectOptions(hrAccessGranted, hrAccessGrantedSearch?.value);
            refreshHrTransferButtons();
        };

        const syncHrAccessHiddenInputs = () => {
            if (!hrAccessHiddenInputs || !hrAccessGranted) return;
            hrAccessHiddenInputs.innerHTML = '';

            const fragment = document.createDocumentFragment();
            Array.from(hrAccessGranted.options).forEach((option) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'vacancy_ids[]';
                input.value = option.value;
                fragment.appendChild(input);
            });
            hrAccessHiddenInputs.appendChild(fragment);

            updateHrAccessCounts();
            refreshHrTransferButtons();
        };

        const fillHrAccessLists = (grantedIdsRaw) => {
            if (!hrAccessAvailable || !hrAccessGranted) return;
            hrAccessAvailable.innerHTML = '';
            hrAccessGranted.innerHTML = '';

            const grantedIds = new Set((Array.isArray(grantedIdsRaw) ? grantedIdsRaw : []).map((id) => String(id)));
            const availableFragment = document.createDocumentFragment();
            const grantedFragment = document.createDocumentFragment();

            sortedHrCosVacancies.forEach((vacancy) => {
                const vacancyId = String(vacancy.vacancy_id || '');
                if (vacancyId === '') return;
                const option = createVacancyOption(vacancyId, vacancy.label || vacancyId);
                if (grantedIds.has(vacancyId)) {
                    grantedFragment.appendChild(option);
                } else {
                    availableFragment.appendChild(option);
                }
            });

            hrAccessAvailable.appendChild(availableFragment);
            hrAccessGranted.appendChild(grantedFragment);
            if (hrAccessAvailableSearch) hrAccessAvailableSearch.value = '';
            if (hrAccessGrantedSearch) hrAccessGrantedSearch.value = '';
            applyHrAccessFilters();
            syncHrAccessHiddenInputs();
        };

        const closeHrAccessModal = () => {
            if (!hrAccessModal) return;
            hrAccessModal.classList.add('hidden');
            hrAccessModal.classList.remove('flex');
        };

        const openHrAccessModal = (adminId, adminName, grantedIdsRaw) => {
            if (!hrAccessModal || !hrAccessForm) return;
            const routeTemplate = hrAccessModal.dataset.routeTemplate || '';
            hrAccessForm.action = routeTemplate.replace('__ID__', String(adminId));
            if (hrAccessTargetName) {
                hrAccessTargetName.textContent = adminName || '-';
            }
            fillHrAccessLists(grantedIdsRaw);
            hrAccessModal.classList.remove('hidden');
            hrAccessModal.classList.add('flex');
            if (hrAccessAvailableSearch) {
                hrAccessAvailableSearch.focus();
            }
        };

        const closeApproveModal = () => {
            if (!approveModal) return;
            approveModal.classList.add('hidden');
            approveModal.classList.remove('flex');
        };

        const openApproveModal = (adminId, adminName) => {
            if (!approveModal || !approveForm) return;
            const routeTemplate = approveModal.dataset.routeTemplate || '';
            approveForm.action = routeTemplate.replace('__ID__', String(adminId));
            if (approveTargetName) {
                approveTargetName.textContent = adminName || '-';
            }
            approveModal.classList.remove('hidden');
            approveModal.classList.add('flex');
        };

        if (approveModalClose) {
            approveModalClose.addEventListener('click', closeApproveModal);
        }
        if (approveModalCancel) {
            approveModalCancel.addEventListener('click', closeApproveModal);
        }
        if (approveModal) {
            approveModal.addEventListener('click', (event) => {
                if (event.target === approveModal) {
                    closeApproveModal();
                }
            });
        }
        if (hrAccessModalClose) {
            hrAccessModalClose.addEventListener('click', closeHrAccessModal);
        }
        if (hrAccessModalCancel) {
            hrAccessModalCancel.addEventListener('click', closeHrAccessModal);
        }
        if (hrAccessModal) {
            hrAccessModal.addEventListener('click', (event) => {
                if (event.target === hrAccessModal) {
                    closeHrAccessModal();
                }
            });
        }
        if (hrAccessMoveRight) {
            hrAccessMoveRight.addEventListener('click', () => {
                moveSelectedOptions(hrAccessAvailable, hrAccessGranted);
                applyHrAccessFilters();
                syncHrAccessHiddenInputs();
            });
        }
        if (hrAccessMoveLeft) {
            hrAccessMoveLeft.addEventListener('click', () => {
                moveSelectedOptions(hrAccessGranted, hrAccessAvailable);
                applyHrAccessFilters();
                syncHrAccessHiddenInputs();
            });
        }
        if (hrAccessMoveAllLeft) {
            hrAccessMoveAllLeft.addEventListener('click', () => {
                moveAllOptions(hrAccessGranted, hrAccessAvailable);
                applyHrAccessFilters();
                syncHrAccessHiddenInputs();
            });
        }
        if (hrAccessAvailableSearch) {
            hrAccessAvailableSearch.addEventListener('input', applyHrAccessFilters);
        }
        if (hrAccessGrantedSearch) {
            hrAccessGrantedSearch.addEventListener('input', applyHrAccessFilters);
        }
        if (hrAccessAvailable) {
            hrAccessAvailable.addEventListener('change', refreshHrTransferButtons);
            hrAccessAvailable.addEventListener('dblclick', () => {
                moveSelectedOptions(hrAccessAvailable, hrAccessGranted);
                applyHrAccessFilters();
                syncHrAccessHiddenInputs();
            });
        }
        if (hrAccessGranted) {
            hrAccessGranted.addEventListener('change', refreshHrTransferButtons);
            hrAccessGranted.addEventListener('dblclick', () => {
                moveSelectedOptions(hrAccessGranted, hrAccessAvailable);
                applyHrAccessFilters();
                syncHrAccessHiddenInputs();
            });
        }

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.matches('.js-admin-status-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                const action = form.dataset.action === 'deactivate' ? 'open-admin-deactivate-confirm' : 'open-admin-activate-confirm';
                window.dispatchEvent(new CustomEvent(action));
                return;
            }

            if (form.matches('.js-admin-edit-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-edit-confirm'));
                return;
            }

            if (form.matches('.js-admin-approve-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-approve-confirm'));
                return;
            }

            if (form.matches('.js-admin-decline-form')) {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-decline-confirm'));
                return;
            }

            if (form.matches('.js-admin-hr-access-form')) {
                event.preventDefault();
                syncHrAccessHiddenInputs();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-admin-hr-access-confirm'));
                return;
            }
        }, true);

        document.addEventListener('click', (event) => {
            const approveButton = event.target.closest('.js-open-approve-modal');
            if (approveButton) {
                const adminId = approveButton.dataset.adminId;
                const adminName = approveButton.dataset.adminName;
                if (!adminId) return;
                openApproveModal(adminId, adminName);
                return;
            }

            const hrAccessButton = event.target.closest('.js-open-hr-access-modal');
            if (!hrAccessButton) return;
            const adminId = hrAccessButton.dataset.adminId;
            const adminName = hrAccessButton.dataset.adminName;
            if (!adminId) return;

            let grantedIds = [];
            try {
                const raw = hrAccessButton.dataset.grantedVacancyIds || '[]';
                grantedIds = JSON.parse(raw);
                if (!Array.isArray(grantedIds)) {
                    grantedIds = [];
                }
            } catch (e) {
                grantedIds = [];
            }

            openHrAccessModal(adminId, adminName, grantedIds);
        });

        window.addEventListener('confirm-admin-activate', submitPendingForm);
        window.addEventListener('confirm-admin-deactivate', submitPendingForm);
        window.addEventListener('confirm-admin-approve', () => {
            closeApproveModal();
            submitPendingForm();
        });
        window.addEventListener('confirm-admin-decline', submitPendingForm);
        window.addEventListener('confirm-admin-edit', submitPendingForm);
        window.addEventListener('confirm-admin-hr-access', () => {
            closeHrAccessModal();
            submitPendingForm();
        });

        const searchInput = document.getElementById('adminSearchInput');
        const clearBtn = document.getElementById('adminSearchClear');
        const roleFilter = document.getElementById('adminRoleFilter');
        const statusFilter = document.getElementById('adminStatusFilter');
        const resetFiltersBtn = document.getElementById('adminFilterReset');
        const rowsContainer = document.getElementById('adminRows');
        const pendingCountBadge = document.getElementById('pendingCountBadge');
        const pendingCountValue = document.getElementById('pendingCountValue');
        if (!searchInput || !rowsContainer || !roleFilter || !statusFilter || !resetFiltersBtn) return;

        const updatePendingCount = () => {
            if (!pendingCountBadge || !pendingCountValue) return;
            const pendingRows = rowsContainer.querySelectorAll('tr[data-status="pending"]').length;
            pendingCountValue.textContent = String(pendingRows);

            const hasPending = pendingRows > 0;
            pendingCountBadge.classList.toggle('border-amber-200', hasPending);
            pendingCountBadge.classList.toggle('bg-amber-50', hasPending);
            pendingCountBadge.classList.toggle('text-amber-700', hasPending);
            pendingCountBadge.classList.toggle('border-slate-200', !hasPending);
            pendingCountBadge.classList.toggle('bg-slate-100', !hasPending);
            pendingCountBadge.classList.toggle('text-slate-600', !hasPending);
        };

        const loadingRow = `
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <svg class="h-4 w-4 animate-spin text-[#0D2B70]" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-20"></circle>
                            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" class="opacity-90"></path>
                        </svg>
                        Searching accounts...
                    </div>
                </td>
            </tr>`;

        const errorRow = `
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-rose-600">
                    Unable to load search results. Please try again.
                </td>
            </tr>`;

        const searchUrl = '{{ route('admin.search') }}';
        let searchTimer = null;
        let activeController = null;
        let latestRequestId = 0;

        const updateControlsVisibility = () => {
            clearBtn.hidden = searchInput.value.trim() === '';
            resetFiltersBtn.hidden = searchInput.value.trim() === '' && roleFilter.value === '' && statusFilter.value === '';
        };

        const buildSearchParams = () => {
            const params = new URLSearchParams();
            const query = searchInput.value.trim();
            if (query !== '') params.set('query', query);
            if (roleFilter.value !== '') params.set('role', roleFilter.value);
            if (statusFilter.value !== '') params.set('status', statusFilter.value);
            return params;
        };

        const fetchRows = async () => {
            if (activeController) activeController.abort();
            activeController = new AbortController();
            const requestId = ++latestRequestId;

            rowsContainer.innerHTML = loadingRow;
            try {
                const params = buildSearchParams();
                const response = await fetch(`${searchUrl}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: activeController.signal
                });
                if (!response.ok) throw new Error(`Search request failed with ${response.status}`);
                const html = await response.text();
                if (requestId !== latestRequestId) return;
                rowsContainer.innerHTML = html;
                updatePendingCount();
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(rowsContainer);
                }
            } catch (e) {
                if (e.name === 'AbortError') return;
                rowsContainer.innerHTML = errorRow;
                updatePendingCount();
            }
        };

        const debouncedFetch = () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchRows, 380);
        };

        searchInput.addEventListener('input', () => {
            updateControlsVisibility();
            debouncedFetch();
        });

        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            updateControlsVisibility();
            fetchRows();
            searchInput.focus();
        });

        roleFilter.addEventListener('change', () => {
            updateControlsVisibility();
            fetchRows();
        });

        statusFilter.addEventListener('change', () => {
            updateControlsVisibility();
            fetchRows();
        });

        resetFiltersBtn.addEventListener('click', () => {
            searchInput.value = '';
            roleFilter.value = '';
            statusFilter.value = '';
            updateControlsVisibility();
            fetchRows();
        });

        updateControlsVisibility();
        updatePendingCount();
    });
</script>
@endpush

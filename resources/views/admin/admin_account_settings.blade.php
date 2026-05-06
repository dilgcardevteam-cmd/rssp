@extends('layout.admin')
@section('title', 'DILG - Account Settings')

@section('content')
@php
    $roleLabel = match ($admin->role ?? null) {
        'superadmin' => 'Superadmin',
        'admin' => 'Admin (HR)',
        'hr_division' => 'HR Division',
        'viewer' => 'Viewer',
        default => 'Administrator',
    };

    $rawName = trim((string) ($admin->name ?? ''));
    $nameParts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $defaultFirstName = $nameParts[0] ?? '';
    $defaultLastName = count($nameParts) > 1 ? (string) end($nameParts) : '';
    $defaultMiddleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';

    $displayName = trim(implode(' ', array_filter([$defaultFirstName, $defaultMiddleName, $defaultLastName])));
    if ($displayName === '') {
        $displayName = $admin->name ?? 'N/A';
    }

    $settingsErrors = $errors->settingsUpdate->all();
    $passwordErrors = $errors->passwordUpdate->all();
    $openEditModal = !empty($settingsErrors);
    $openPasswordModal = !empty($passwordErrors);

    $initials = strtoupper(
        mb_substr(trim($defaultFirstName), 0, 1) .
        mb_substr(trim($defaultLastName), 0, 1)
    );
    $initials = $initials !== '' ? $initials : 'AD';
@endphp

<main class="mx-auto w-full max-w-5xl px-4 pb-8 sm:px-8"
    x-data="{ showEditModal: {{ $openEditModal ? 'true' : 'false' }}, showPasswordModal: {{ $openPasswordModal ? 'true' : 'false' }} }"
    x-on:force-close-edit-modal.window="showEditModal = false"
    x-effect="document.documentElement.classList.toggle('overflow-hidden', showEditModal || showPasswordModal); document.body.classList.toggle('overflow-hidden', showEditModal || showPasswordModal)">
    <section class="mb-4 flex items-center space-x-4">
        <h1 class="flex w-full items-center gap-3 border-b border-[#0D2B70] pb-2 text-3xl font-montserrat font-bold tracking-wide text-[#0D2B70]">
            Account Settings
        </h1>
    </section>

    @if (session('settings_success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('settings_success') }}
        </div>
    @endif

    @if (session('password_success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('password_success') }}
        </div>
    @endif

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-[#0D2B70]">Profile Details</h2>
                <p class="mt-1 text-sm text-slate-500">Manage your administrator account details and password.</p>
            </div>
            <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                {{ $roleLabel }}
            </span>
        </div>

        <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-2">
            <div class="mt-4 grid gap-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Name</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $displayName }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                    <p class="mt-1 break-all text-sm font-semibold text-slate-800">{{ $admin->email ?: 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Office</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $admin->office ?: 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Designation</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $admin->designation ?: 'N/A' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Status</p>
                    <p class="mt-1 text-sm font-semibold {{ (int) ($admin->is_active ?? 0) === 1 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ (int) ($admin->is_active ?? 0) === 1 ? 'Active' : 'Inactive' }}
                    </p>
                </div>
            </div>

            <div class="mt-5 flex flex-col items-center justify-center gap-3 rounded-xl px-4 py-4">
                <div class="flex h-48 w-48 items-center justify-center rounded-full bg-[#0D2B70] text-4xl font-bold text-white ring-2 ring-blue-100">
                    {{ $initials }}
                </div>
                <p class="text-sm font-semibold text-slate-700">{{ $roleLabel }}</p>
                <p class="text-center text-xs text-slate-500">Administrator accounts use the same account settings flow as the other user profiles.</p>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="button" @click="showEditModal = true"
                class="rounded-xl border border-[#0D2B70] px-4 py-2 text-sm font-semibold text-[#0D2B70] transition hover:bg-[#0D2B70] hover:text-white">
                Edit
            </button>
            <button type="button" @click="showPasswordModal = true"
                class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                Reset Password
            </button>
        </div>
    </section>

    <template x-teleport="body">
        <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 z-[1000] bg-slate-900/60"
            style="display:none;" @keydown.escape.window="window.dispatchEvent(new CustomEvent('request-close-edit-modal'))">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#0D2B70]">Edit Profile Details</h3>
                            <p class="text-sm text-slate-500">Update your administrator account details.</p>
                        </div>
                        <button type="button" class="js-edit-close-btn rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.account.settings.update') }}" id="editAdminProfileForm" class="space-y-5 p-6">
                        @csrf
                        @method('PUT')

                        @if (!empty($settingsErrors))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($settingsErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $defaultFirstName) }}" data-initial="{{ old('first_name', $defaultFirstName) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $defaultMiddleName) }}" data-initial="{{ old('middle_name', $defaultMiddleName) }}"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $defaultLastName) }}" data-initial="{{ old('last_name', $defaultLastName) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Office</label>
                                <input type="text" name="office" value="{{ old('office', $admin->office) }}" data-initial="{{ old('office', $admin->office) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Designation</label>
                                <input type="text" name="designation" value="{{ old('designation', $admin->designation) }}" data-initial="{{ old('designation', $admin->designation) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $admin->email) }}" data-initial="{{ old('email', $admin->email) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                            <button type="button" class="js-edit-close-btn rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="submit" id="editAdminProfileSaveBtn" disabled
                                class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-[#0D2B70]">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="showPasswordModal" x-transition.opacity class="fixed inset-0 z-[1000] bg-slate-900/60"
            style="display:none;" @keydown.escape.window="showPasswordModal = false">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#0D2B70]">Reset Password</h3>
                            <p class="text-sm text-slate-500">Use a strong password with uppercase, lowercase, number, and symbol.</p>
                        </div>
                        <button type="button" @click="showPasswordModal = false"
                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.account.password.update') }}" class="space-y-5 p-6">
                        @csrf
                        @method('PUT')

                        @if (!empty($passwordErrors))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($passwordErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Current Password</label>
                            <input type="password" name="current_password" required autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">New Password</label>
                            <input type="password" name="new_password" minlength="8"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}" required autocomplete="new-password"
                                title="Use at least 8 characters with uppercase, lowercase, number, and special character."
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" minlength="8"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}" required autocomplete="new-password"
                                title="Use at least 8 characters with uppercase, lowercase, number, and special character."
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                            <button type="button" @click="showPasswordModal = false"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="submit"
                                class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <x-confirm-modal
        title="Confirm Save Changes"
        message="Save these profile detail changes?"
        event="open-account-settings-save-confirm"
        confirm="confirm-account-settings-save"
    />
    <x-confirm-modal
        title="Discard Changes?"
        message="You have unsaved changes. Close this form without saving?"
        event="open-account-settings-discard-confirm"
        confirm="confirm-account-settings-discard"
        confirmText="Discard"
        tone="danger"
    />
</main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('editAdminProfileForm');
            const saveBtn = document.getElementById('editAdminProfileSaveBtn');
            const editCloseButtons = document.querySelectorAll('.js-edit-close-btn');

            if (!form || !saveBtn) {
                return;
            }

            const trackedInputs = Array.from(form.querySelectorAll(
                'input[name="first_name"], input[name="middle_name"], input[name="last_name"], input[name="office"], input[name="designation"], input[name="email"]'
            ));

            let editFormDirty = false;
            let pendingConfirmationForm = null;

            const hasChanges = () => trackedInputs.some((input) => (input.value ?? '') !== (input.dataset.initial ?? ''));

            const updateSaveState = () => {
                editFormDirty = hasChanges();
                saveBtn.disabled = !editFormDirty || !form.checkValidity();
            };

            trackedInputs.forEach((input) => {
                input.addEventListener('input', updateSaveState);
            });

            const requestCloseEditModal = () => {
                if (editFormDirty) {
                    window.dispatchEvent(new CustomEvent('open-account-settings-discard-confirm'));
                    return;
                }

                window.dispatchEvent(new CustomEvent('force-close-edit-modal'));
            };

            editCloseButtons.forEach((button) => {
                button.addEventListener('click', requestCloseEditModal);
            });

            window.addEventListener('request-close-edit-modal', requestCloseEditModal);

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                pendingConfirmationForm = form;
                window.dispatchEvent(new CustomEvent('open-account-settings-save-confirm'));
            });

            window.addEventListener('confirm-account-settings-save', () => {
                if (!pendingConfirmationForm) {
                    return;
                }

                const submitForm = pendingConfirmationForm;
                pendingConfirmationForm = null;
                submitForm.submit();
            });

            window.addEventListener('confirm-account-settings-discard', () => {
                window.dispatchEvent(new CustomEvent('force-close-edit-modal'));
            });

            updateSaveState();
        });
    </script>
@endpush

@props(['admin'])
@php
    $isEditingCurrent = session('_editing') == $admin->id;
    $editErrors = ($isEditingCurrent && is_array(session('error'))) ? session('error') : [];
    $isCurrentUser = (int) (auth('admin')->id() ?? 0) === (int) $admin->id;
    $isSuperadminAccount = (($admin->role ?? null) === 'superadmin');
    $selectedAccountType = $isEditingCurrent ? old('account_type', $admin->role) : $admin->role;

    $rawName = trim((string) ($admin->name ?? ''));
    $nameParts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $defaultFirstName = $nameParts[0] ?? '';
    $defaultLastName = count($nameParts) > 1 ? (string) end($nameParts) : '';
    $defaultMiddleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';

    $firstNameValue = $isEditingCurrent ? old('first_name', $defaultFirstName) : $defaultFirstName;
    $middleNameValue = $isEditingCurrent ? old('middle_name', $defaultMiddleName) : $defaultMiddleName;
    $lastNameValue = $isEditingCurrent ? old('last_name', $defaultLastName) : $defaultLastName;
    $displayIdentity = trim((string) ($admin->name ?? '')) ?: ($admin->email ?? ('Admin #' . $admin->id));
@endphp

<div x-data="{ showEditAccount: {{ $isEditingCurrent ? 'true' : 'false' }} }" class="inline-flex shrink-0">
    <button type="button" @click="showEditAccount = true"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 text-slate-600 transition hover:bg-slate-100 hover:text-slate-800"
        aria-label="Edit account" title="Edit account">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
    </button>

    <div x-show="showEditAccount" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 px-4 py-8"
        style="display: none;" @keydown.escape.window="showEditAccount = false" @click.self="showEditAccount = false">
        <div class="mx-auto flex min-h-full w-full max-w-3xl items-center justify-center">
            <div class="w-full rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#0D2B70]">Edit Account</h2>
                        <p class="text-sm text-slate-500">Update profile details and account role.</p>
                    </div>
                    <button type="button" @click="showEditAccount = false"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form class="js-admin-edit-form no-spinner space-y-5 p-6" method="POST" data-admin-name="{{ $displayIdentity }}"
                    action="{{ route('admin.update', $admin->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_editing" value="{{ $admin->id }}">

                    @if (!empty($editErrors))
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                            <ul class="list-disc pl-5">
                                @foreach ($editErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                            <input type="text" name="first_name"
                                value="{{ $firstNameValue }}"
                                required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                            <input type="text" name="last_name"
                                value="{{ $lastNameValue }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                            <input type="text" name="middle_name"
                                value="{{ $middleNameValue }}"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Office</label>
                            <input type="text" name="office"
                                value="{{ $isEditingCurrent ? old('office', $admin->office) : $admin->office }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Designation</label>
                            <input type="text" name="designation"
                                value="{{ $isEditingCurrent ? old('designation', $admin->designation) : $admin->designation }}"
                                required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                            <input type="email" name="email"
                                value="{{ $isEditingCurrent ? old('email', $admin->email) : $admin->email }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Account Type</label>
                        @if ($isSuperadminAccount)
                            <input type="hidden" name="account_type" value="superadmin">
                            <div class="rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs text-amber-800">
                                Superadmin account type is locked and cannot be changed in Edit Account.
                            </div>
                            <div class="mt-2 rounded-xl border border-violet-200 bg-violet-50 p-3">
                                <p class="text-sm font-semibold text-violet-700">Superadmin</p>
                                <p class="text-xs text-violet-700/90">Full control over all modules and users.</p>
                            </div>
                        @else
                            <p class="mb-2 text-xs text-slate-500">Assign one of the available account types for this user.</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                    <div class="flex items-start gap-2">
                                        <input type="radio" name="account_type" value="admin" class="mt-0.5 accent-[#0D2B70]"
                                            {{ $selectedAccountType === 'admin' ? 'checked' : '' }}>
                                        <div>
                                            <p class="text-sm font-semibold text-[#0D2B70]">Admin (HR)</p>
                                            <p class="text-xs text-slate-500">All admin tools except user management.</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                    <div class="flex items-start gap-2">
                                        <input type="radio" name="account_type" value="hr_division" class="mt-0.5 accent-[#0D2B70]"
                                            {{ $selectedAccountType === 'hr_division' ? 'checked' : '' }}>
                                        <div>
                                            <p class="text-sm font-semibold text-[#0D2B70]">HR Division</p>
                                            <p class="text-xs text-slate-500">Applicants management only.</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                    <div class="flex items-start gap-2">
                                        <input type="radio" name="account_type" value="viewer" class="mt-0.5 accent-[#0D2B70]"
                                            {{ $selectedAccountType === 'viewer' ? 'checked' : '' }}>
                                        <div>
                                            <p class="text-sm font-semibold text-[#0D2B70]">Viewer</p>
                                            <p class="text-xs text-slate-500">Exam management only.</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" @click="showEditAccount = false"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $createHasErrors = $errors->any() && (old('first_name') || old('last_name') || old('middle_name') || old('email') || old('account_type'));
@endphp

<div x-data="{ showCreateAccount: {{ $createHasErrors ? 'true' : 'false' }} }" class="inline-flex">
    <button type="button" @click="showCreateAccount = true"
        class="inline-flex items-center gap-2 rounded-xl border border-[#0D2B70] bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0A2259]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Add Account
    </button>

    <div x-show="showCreateAccount" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50 px-4 py-8"
        style="display: none;" @keydown.escape.window="showCreateAccount = false" @click.self="showCreateAccount = false">
        <div class="mx-auto flex min-h-full w-full max-w-3xl items-center justify-center">
            <div class="w-full rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#0D2B70]">Create System Account</h2>
                        <p class="text-sm text-slate-500">Set identity details and assign access role.</p>
                    </div>
                    <button type="button" @click="showCreateAccount = false"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.store') }}" method="POST" class="space-y-5 p-6">
                    @csrf

                    @if ($createHasErrors)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Office</label>
                            <input type="text" name="office" value="{{ old('office') }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Designation</label>
                            <input type="text" name="designation" value="{{ old('designation') }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                            <input type="password" name="password" required minlength="8"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Account Type</label>
                        <p class="mb-2 text-xs text-slate-500">Only one active superadmin is allowed. Creating a new one deactivates the previous superadmin account.</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="account_type" value="superadmin" class="mt-0.5 accent-[#0D2B70]" required
                                        {{ old('account_type') === 'superadmin' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-semibold text-[#0D2B70]">Superadmin</p>
                                        <p class="text-xs text-slate-500">Full control over all modules and users.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="account_type" value="admin" class="mt-0.5 accent-[#0D2B70]"
                                        {{ old('account_type') === 'admin' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-semibold text-[#0D2B70]">Admin (HR)</p>
                                        <p class="text-xs text-slate-500">All admin tools except user management.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="account_type" value="hr_division" class="mt-0.5 accent-[#0D2B70]"
                                        {{ old('account_type') === 'hr_division' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-semibold text-[#0D2B70]">HR Division</p>
                                        <p class="text-xs text-slate-500">Applicants management only.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="role-option cursor-pointer rounded-xl border border-slate-300 p-3 hover:border-[#0D2B70]">
                                <div class="flex items-start gap-2">
                                    <input type="radio" name="account_type" value="viewer" class="mt-0.5 accent-[#0D2B70]"
                                        {{ old('account_type') === 'viewer' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-semibold text-[#0D2B70]">Viewer</p>
                                        <p class="text-xs text-slate-500">Exam management only.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" @click="showCreateAccount = false"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@extends('layout.app')
@section('title', 'Account Settings')

@section('content')
    @php
        $editErrorKeys = ['first_name', 'middle_name', 'last_name', 'email', 'phone', 'address', 'bio', 'avatar'];
        $passwordErrorKeys = ['current_password', 'password', 'password_confirmation'];
        $editErrors = collect($editErrorKeys)->flatMap(fn($key) => $errors->get($key))->all();
        $passwordErrors = collect($passwordErrorKeys)->flatMap(fn($key) => $errors->get($key))->all();
        $openEditModal = !empty($editErrors);
        $openPasswordModal = !empty($passwordErrors);
        $galleryItems = $galleryItems ?? collect();
        $documentTypeOptions = $documentTypeOptions ?? [];
        $openDocumentGallery = session()->has('document_gallery_success')
            || $errors->has('gallery_document')
            || $errors->has('document_type')
            || $errors->has('replacement_gallery_document');
    @endphp

    <main class="mx-auto w-full max-w-5xl px-4 pb-8 sm:px-8"
        x-data="{ showEditModal: {{ $openEditModal ? 'true' : 'false' }}, showPasswordModal: {{ $openPasswordModal ? 'true' : 'false' }}, activeSection: '{{ $openDocumentGallery ? 'gallery' : 'account' }}' }"
        x-on:force-close-edit-modal.window="showEditModal = false"
        x-effect="document.documentElement.classList.toggle('overflow-hidden', showEditModal || showPasswordModal); document.body.classList.toggle('overflow-hidden', showEditModal || showPasswordModal)">
        <section class="mb-4 flex items-center space-x-4">
            <h1 class="flex w-full items-center gap-3 border-b border-[#0D2B70] pb-2 text-3xl font-montserrat font-bold tracking-wide text-[#0D2B70]">
                Account Settings
            </h1>
        </section>

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="flex flex-wrap gap-2">
                <button type="button"
                    @click="activeSection = 'account'"
                    :class="activeSection === 'account'
                        ? 'border-[#0D2B70] bg-[#0D2B70] text-white shadow-sm'
                        : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'"
                    class="rounded-full border px-4 py-2 text-sm font-semibold transition">
                    Account Settings
                </button>
                <button type="button"
                    @click="activeSection = 'gallery'"
                    :class="activeSection === 'gallery'
                        ? 'border-[#0D2B70] bg-[#0D2B70] text-white shadow-sm'
                        : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'"
                    class="rounded-full border px-4 py-2 text-sm font-semibold transition">
                    Document Gallery
                </button>
            </div>
            <p class="mt-3 text-sm text-slate-500">
                Use the pills above to switch between your profile details and your saved documents.
            </p>
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

        @if (session('document_gallery_success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('document_gallery_success') }}
            </div>
        @endif

        @if ($errors->has('account_deletion_request'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first('account_deletion_request') }}
            </div>
        @endif

        @php
            $hasStoredAvatar = filled($user->avatar_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar_path);
            $avatar = $hasStoredAvatar ? asset('storage/' . $user->avatar_path) : null;
            $profile = $user->profile;
            $personalInfo = $personalInfo ?? $user->personalInformation;
            $isGoogleSignup = $isGoogleSignup ?? false;

            $hasPdsProfile = $personalInfo && collect([
                $personalInfo->first_name,
                $personalInfo->surname,
                $personalInfo->email_address,
                $personalInfo->mobile_no,
                $personalInfo->telephone_no,
            ])->filter(fn($value) => filled($value))->isNotEmpty();

            $usePdsProfile = $hasPdsProfile;
            $allowAccountFallback = !$usePdsProfile && !$isGoogleSignup;

            $middleInitial = filled($personalInfo?->middle_name)
                ? mb_substr(trim($personalInfo->middle_name), 0, 1) . '.'
                : '';
            $pdsNameParts = array_filter([
                trim($personalInfo?->first_name ?? ''),
                $middleInitial,
                trim($personalInfo?->surname ?? ''),
                trim($personalInfo?->name_extension ?? ''),
            ], fn($part) => $part !== '');
            $pdsName = $pdsNameParts ? trim(implode(' ', $pdsNameParts)) : null;

            $accountFirstName = trim((string) ($user->first_name ?? ''));
            $accountLastName = trim((string) ($user->last_name ?? ''));
            $accountMiddleInitial = filled($user->middle_name)
                ? mb_substr(trim($user->middle_name), 0, 1) . '.'
                : '';
            $accountNameParts = array_filter([
                $accountFirstName,
                $accountMiddleInitial,
                $accountLastName,
            ], fn($part) => $part !== '');
            $accountDisplayName = $accountNameParts ? trim(implode(' ', $accountNameParts)) : 'N/A';

            $accountEmail = $user->email ?: 'N/A';
            $accountPhone = $user->phone_number ?: ($profile?->phone ?: 'N/A');
            $initials = strtoupper(
                mb_substr($accountFirstName, 0, 1) .
                mb_substr($accountLastName, 0, 1)
            );
            $initials = $initials !== '' ? $initials : 'NA';
        @endphp

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" x-show="activeSection === 'account'" x-transition>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-[#0D2B70]">Profile Details</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage your account profile and avatar.</p>
                </div>
                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Applicant
                </span>
            </div>

            <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-2">
                <!-- ACCOUNT DETAILS -->
                <div class="mt-4 grid gap-4 flex flex-col">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Name</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $accountDisplayName }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                        <p class="mt-1 break-all text-sm font-semibold text-slate-800">{{ $accountEmail }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Phone</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $accountPhone }}</p>
                    </div>
                </div>

                <!-- PROFILE PIC -->
                <div class="mt-5 flex flex-col items-center justify-center gap-3 rounded-xl px-4 py-4">
                    <!-- avatar -->
                    <div class="flex items-center gap-3">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="Avatar" class="h-48 w-48 rounded-full object-cover ring-2 ring-blue-100">
                        @else
                            <div class="flex h-48 w-48 items-center justify-center rounded-full bg-blue-600 text-4xl font-bold text-white ring-2 ring-blue-100">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500">Change avatar in Edit Profile.</p>
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

            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50/70 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Account Deletion Request</p>
                @if($user->deletion_due_at)
                    <p class="mt-2 text-sm text-rose-800">
                        Your account is already set for deletion by admin.
                    </p>
                    <p class="mt-1 text-xs text-rose-700">
                        Deletion deadline: {{ optional($user->deletion_due_at)->format('M d, Y h:i A') ?: 'N/A' }}
                    </p>
                @elseif($user->deletion_requested_by_applicant_at)
                    <p class="mt-2 text-sm text-rose-800">
                        Your deletion request has been sent and is pending admin action.
                    </p>
                    <p class="mt-1 text-xs text-rose-700">
                        Requested at: {{ optional($user->deletion_requested_by_applicant_at)->format('M d, Y h:i A') ?: 'N/A' }}
                    </p>
                    <p class="mt-1 text-xs text-rose-700">
                        Received by admin at: {{ optional($user->deletion_request_received_by_admin_at)->format('M d, Y h:i A') ?: 'N/A' }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-rose-800">
                        Send a request to admin if you want your applicant account permanently deleted. Only admin can perform the deletion.
                    </p>
                    <form id="request-account-deletion-form" method="POST" action="{{ route('profile.request_account_deletion') }}" class="mt-3">
                        @csrf
                        <button type="button"
                            @click="$dispatch('open-request-account-deletion-modal')"
                            class="rounded-xl border border-rose-500 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-600 hover:text-white">
                            Request Account Deletion
                        </button>
                    </form>
                    <x-confirm-modal
                        title="Request Account Deletion"
                        message="Send account deletion request to admin? Only admin can delete your applicant account."
                        event="open-request-account-deletion-modal"
                        confirm="confirm-request-account-deletion"
                        confirmText="Send Request"
                        cancelText="Keep Account"
                        tone="danger"
                    />
                @endif
            </div>
        </section>

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" x-show="activeSection === 'gallery'" x-transition
            x-data="documentGalleryManager()" @submit="handleSubmit($event)">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-[#0D2B70]">Document Gallery</h2>
                    <p class="mt-1 text-sm text-slate-500">Save, reuse, and restore your uploaded documents here.</p>
                </div>
                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Reusable Files
                </span>
            </div>
            <div x-show="successMessage" x-transition class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-text="successMessage"></div>
            <div x-show="errorMessages.length" x-transition class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <template x-for="message in errorMessages" :key="message">
                    <p x-text="message"></p>
                </template>
            </div>
            <div x-show="isSubmitting" x-transition class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                Updating document gallery...
            </div>
            <div x-ref="galleryContent">
                @include('profile.partials.document_gallery_content')
            </div>
            <div x-show="toastVisible" x-transition.opacity.duration.200ms
                class="fixed right-4 top-24 z-[1100] w-full max-w-sm rounded-2xl border px-4 py-3 shadow-xl"
                :class="toastType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800'">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5">
                        <svg x-show="toastType === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="toastType !== 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold" x-text="toastType === 'success' ? 'Success' : 'Action needed'"></p>
                        <p class="mt-1 text-sm" x-text="toastMessage"></p>
                    </div>
                </div>
            </div>
        </section>

        @once
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('documentGalleryManager', () => ({
                        isSubmitting: false,
                        successMessage: '',
                        errorMessages: [],
                        toastVisible: false,
                        toastMessage: '',
                        toastType: 'success',
                        toastTimer: null,
                        showToast(message, type = 'success') {
                            this.toastMessage = message;
                            this.toastType = type;
                            this.toastVisible = true;

                            if (this.toastTimer) {
                                clearTimeout(this.toastTimer);
                            }

                            this.toastTimer = setTimeout(() => {
                                this.toastVisible = false;
                            }, 3200);
                        },
                        async handleSubmit(event) {
                            const form = event.target.closest('form[data-gallery-async]');
                            if (!form) {
                                return;
                            }

                            event.preventDefault();

                            if (this.isSubmitting) {
                                return;
                            }

                            this.successMessage = '';
                            this.errorMessages = [];
                            this.isSubmitting = true;

                            const submitButton = event.submitter || form.querySelector('button[type="submit"]');
                            const hasInlineLoader = submitButton && submitButton.hasAttribute('data-gallery-loading-button');
                            const buttonLabel = hasInlineLoader ? submitButton.querySelector('.js-gallery-btn-label') : null;
                            const buttonLoader = hasInlineLoader ? submitButton.querySelector('.js-gallery-btn-loader') : null;

                            if (submitButton) {
                                submitButton.disabled = true;
                            }

                            if (hasInlineLoader && buttonLabel && buttonLoader) {
                                buttonLabel.classList.add('hidden');
                                buttonLoader.classList.remove('hidden');
                                buttonLoader.classList.add('inline-flex');
                            }

                            try {
                                const response = await fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    body: new FormData(form),
                                });

                                const payload = await response.json().catch(() => ({}));

                                if (!response.ok) {
                                    this.errorMessages = Object.values(payload.errors || {}).flat();
                                    if (!this.errorMessages.length) {
                                        this.errorMessages = [payload.message || 'We could not update the document gallery right now.'];
                                    }
                                    this.showToast(this.errorMessages[0], 'error');
                                    return;
                                }

                                if (payload.html && this.$refs.galleryContent) {
                                    this.$refs.galleryContent.innerHTML = payload.html;
                                    if (window.Alpine) {
                                        window.Alpine.initTree(this.$refs.galleryContent);
                                    }
                                }

                                this.successMessage = payload.message || 'Document gallery updated.';
                                this.showToast(this.successMessage, 'success');
                            } catch (error) {
                                this.errorMessages = ['We could not update the document gallery right now.'];
                                this.showToast(this.errorMessages[0], 'error');
                            } finally {
                                if (hasInlineLoader && buttonLabel && buttonLoader) {
                                    buttonLabel.classList.remove('hidden');
                                    buttonLoader.classList.add('hidden');
                                    buttonLoader.classList.remove('inline-flex');
                                }
                                if (submitButton) {
                                    submitButton.disabled = false;
                                }
                                this.isSubmitting = false;
                            }
                        },
                    }));

                    window.addEventListener('confirm-request-account-deletion', () => {
                        const form = document.getElementById('request-account-deletion-form');
                        if (form) {
                            form.submit();
                        }
                    });

                    Alpine.data('savedDocumentsSearch', () => ({
                        searchTerm: '',
                        appliedSearch: '',
                        isSearchPending: false,
                        searchTimer: null,
                        escapeHtml(value) {
                            return String(value ?? '')
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
                        },
                        escapeRegex(value) {
                            return String(value ?? '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        },
                        queueSearch() {
                            if (this.searchTimer) {
                                clearTimeout(this.searchTimer);
                            }
                            this.isSearchPending = true;
                            this.searchTimer = setTimeout(() => {
                                this.appliedSearch = this.searchTerm.trim().toLowerCase();
                                this.isSearchPending = false;
                            }, 300);
                        },
                        matchesDocument(searchText) {
                            if (!this.appliedSearch) {
                                return true;
                            }
                            return String(searchText || '').includes(this.appliedSearch);
                        },
                        highlightText(value) {
                            const safeValue = this.escapeHtml(value);
                            if (!this.appliedSearch) {
                                return safeValue;
                            }

                            const regex = new RegExp(this.escapeRegex(this.appliedSearch), 'ig');
                            return safeValue.replace(regex, (match) =>
                                `<mark class="rounded bg-amber-200 px-1 text-slate-900">${match}</mark>`
                            );
                        },
                        hasMatches() {
                            if (!this.appliedSearch || !this.$refs.availableDocumentsGrid) {
                                return true;
                            }
                            return Array.from(this.$refs.availableDocumentsGrid.querySelectorAll('[data-search-text]'))
                                .some((item) => item.dataset.searchText.includes(this.appliedSearch));
                        },
                    }));
                });
            </script>
        @endonce

        <template x-teleport="body">
        <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 z-[1000] bg-slate-900/60"
            style="display:none;" @keydown.escape.window="window.dispatchEvent(new CustomEvent('request-close-edit-modal'))">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#0D2B70]">Edit Profile Details</h3>
                            <p class="text-sm text-slate-500">Update your account details.</p>
                        </div>
                        <button type="button" class="js-edit-close-btn rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="editProfileForm" class="no-spinner space-y-5 p-6">
                        @csrf

                        @if (!empty($editErrors))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($editErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-center">
                                <div id="editAvatarPreviewCircle"
                                    class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-xl font-bold text-white ring-2 ring-blue-100"
                                    data-initial-avatar="{{ $avatar ? e($avatar) : '' }}"
                                    data-has-avatar="{{ $avatar ? '1' : '0' }}">
                                    @if ($avatar)
                                        <img src="{{ $avatar }}" alt="Avatar Preview" id="editAvatarPreviewImage" class="h-full w-full object-cover">
                                    @else
                                        <span id="editAvatarPreviewInitials">{{ $initials }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-[220px]">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Avatar</label>
                                    <input type="file" name="avatar" id="editProfileAvatarInput" accept="image/png,image/jpeg"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700">
                                    <p class="mt-1 text-xs text-slate-500">PNG/JPG up to 2MB.</p>
                                    @error('avatar')
                                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" data-initial="{{ old('first_name', $user->first_name) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" data-initial="{{ old('middle_name', $user->middle_name) }}"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" data-initial="{{ old('last_name', $user->last_name) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" data-initial="{{ old('email', $user->email) }}" required
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone_number ?: ($profile?->phone ?? '')) }}" data-initial="{{ old('phone', $user->phone_number ?: ($profile?->phone ?? '')) }}"
                                    style="-moz-appearance: textfield; -webkit-appearance: textfield;"
                                    maxlength="11"
                                    pattern="^09\d{9}$"
                                    title="Use format: 09XXXXXXXXX"
                                    inputmode="numeric"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);"
                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20 invalid:border-rose-400 invalid:bg-rose-50 invalid:text-rose-700 invalid:focus:border-rose-500 invalid:focus:ring-rose-100">
                                <p class="mt-1 text-xs text-slate-500">Format: 09XXXXXXXXX</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                            <button type="button" class="js-edit-close-btn rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="submit" id="editProfileSaveBtn" disabled
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

                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-5 p-6">
                        @csrf

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
                            <input type="password" name="password" required autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm New Password</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
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

        @include('partials.loader')
    </main>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('editProfileForm');
                const saveBtn = document.getElementById('editProfileSaveBtn');
                const avatarInput = document.getElementById('editProfileAvatarInput');
                const avatarPreviewCircle = document.getElementById('editAvatarPreviewCircle');
                const editCloseButtons = document.querySelectorAll('.js-edit-close-btn');
                if (!form || !saveBtn || !avatarInput || !avatarPreviewCircle) return;

                const trackedInputs = Array.from(form.querySelectorAll('input[name="first_name"], input[name="middle_name"], input[name="last_name"], input[name="email"], input[name="phone"]'));
                const initialAvatar = avatarPreviewCircle.dataset.initialAvatar || '';
                const hasInitialAvatar = avatarPreviewCircle.dataset.hasAvatar === '1';
                let editFormDirty = false;
                let pendingConfirmationForm = null;
                let isSaveSubmitting = false;

                const buildInitials = () => {
                    const first = (form.querySelector('input[name="first_name"]')?.value || '').trim();
                    const last = (form.querySelector('input[name="last_name"]')?.value || '').trim();
                    const initials = ((first[0] || '') + (last[0] || '')).toUpperCase();
                    return initials || 'NA';
                };

                const updateAvatarPreview = () => {
                    const file = avatarInput.files && avatarInput.files[0] ? avatarInput.files[0] : null;
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            avatarPreviewCircle.innerHTML = `<img src="${e.target?.result || ''}" alt="Avatar Preview" class="h-full w-full object-cover">`;
                        };
                        reader.readAsDataURL(file);
                        return;
                    }

                    if (hasInitialAvatar && initialAvatar !== '') {
                        avatarPreviewCircle.innerHTML = `<img src="${initialAvatar}" alt="Avatar Preview" class="h-full w-full object-cover">`;
                    } else {
                        avatarPreviewCircle.innerHTML = `<span id="editAvatarPreviewInitials">${buildInitials()}</span>`;
                    }
                };

                const hasTextChanges = () => trackedInputs.some((input) => (input.value ?? '') !== (input.dataset.initial ?? ''));
                const hasAvatarChange = () => !!(avatarInput.files && avatarInput.files.length > 0);
                const showSystemLoader = (message = 'Loading...') => {
                    const loader = document.getElementById('loader');
                    if (!loader) return;

                    loader.classList.remove('hidden');
                    loader.classList.remove('pds-loading-nonblocking');
                    loader.setAttribute('aria-busy', 'true');

                    const loaderText = document.getElementById('loader-text');
                    if (loaderText) {
                        loaderText.textContent = message;
                    }

                    const loaderLive = document.getElementById('loader-live');
                    if (loaderLive) {
                        loaderLive.textContent = message;
                    }
                };

                const updateSaveState = () => {
                    const changed = hasTextChanges() || hasAvatarChange();
                    editFormDirty = changed;
                    saveBtn.disabled = !changed || !form.checkValidity();
                };

                trackedInputs.forEach((input) => {
                    input.addEventListener('input', () => {
                        updateAvatarPreview();
                        updateSaveState();
                    });
                });

                avatarInput.addEventListener('change', () => {
                    updateAvatarPreview();
                    updateSaveState();
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

                    if (isSaveSubmitting) {
                        return;
                    }

                    if (!form.checkValidity()) {
                        if (typeof form.reportValidity === 'function') {
                            form.reportValidity();
                        }
                        return;
                    }

                    pendingConfirmationForm = form;
                    window.dispatchEvent(new CustomEvent('open-account-settings-save-confirm'));
                });

                window.addEventListener('confirm-account-settings-save', () => {
                    if (!pendingConfirmationForm) return;
                    if (isSaveSubmitting) return;

                    isSaveSubmitting = true;
                    const submitForm = pendingConfirmationForm;
                    pendingConfirmationForm = null;
                    saveBtn.disabled = true;
                    showSystemLoader('Saving changes...');
                    submitForm.submit();
                });

                window.addEventListener('confirm-account-settings-discard', () => {
                    window.dispatchEvent(new CustomEvent('force-close-edit-modal'));
                });

                updateAvatarPreview();
                updateSaveState();
            });
        </script>
    @endpush
@endsection

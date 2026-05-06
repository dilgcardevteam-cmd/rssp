<!DOCTYPE html>
<html
  lang="en"
  x-data="signupPage({ hasErrors: '{{ $errors->any() ? 'true' : 'false' }}' })"
  x-init="initModal()"
  @privacy-agreed.window="checkboxChecked = true; showModal = false"
  @privacy-modal-closed.window="showModal = false"
  @privacy-disagreed.window="checkboxChecked = false"
>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Register - DILG CAR Recruitment and Selection Portal</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />

</head>
<body class="relative min-h-screen overflow-x-hidden bg-[#031029] p-3 md:p-4">
  @if(session('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
      class="fixed top-5 right-5 z-50 w-full max-w-sm rounded-xl border border-green-400 bg-green-100 px-4 py-3 text-green-700 shadow-lg">
      <strong class="font-bold">Success!</strong>
      <p class="text-sm">{{ session('success') }}</p>
    </div>
  @endif

  @if ($errors->any())
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
      class="fixed top-5 right-5 z-50 w-full max-w-sm rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-lg">
      <strong class="font-bold">Whoops!</strong>
      <ul class="mt-1 list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <template x-if="showModal">
    <div>
      @include('partials.data_privacy_notice_signup_version')
    </div>
  </template>

  <div aria-hidden="true" class="pointer-events-none absolute inset-0">
    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(24,99,156,0.78)_0%,rgba(255,255,255,0.012)_24%,rgba(255,255,255,0)_100%)]"></div>
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(3,13,33,0.28)_0%,rgba(3,13,33,0)_44%,rgba(3,13,33,0.2)_100%)]"></div>
    <div class="absolute inset-0 opacity-35 bg-[radial-gradient(circle_at_76%_24%,rgba(255,255,255,0.16)_0%,rgba(255,255,255,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,transparent_24%)]"></div>
    <div class="absolute inset-0 opacity-20 mix-blend-soft-light bg-[radial-gradient(rgba(255,255,255,0.45)_0.55px,transparent_0.55px)] [background-size:9px_9px]"></div>
  </div>
  <div aria-hidden="true" class="pointer-events-none absolute right-[4%] top-1/2 z-0 h-[min(40rem,46vw)] w-[min(40rem,46vw)] -translate-y-1/2 rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.34)_0%,rgba(96,165,250,0.16)_34%,rgba(59,130,246,0.03)_62%,rgba(59,130,246,0)_72%)] blur-[14px]"></div>

  <div class="relative z-10 mx-auto flex min-h-[calc(100vh-1.5rem)] w-full max-w-[1240px] items-center justify-center">
    <div class="relative z-10 w-full overflow-hidden rounded-[22px] border border-white/20 bg-[linear-gradient(140deg,rgba(255,255,255,0.96)_0%,rgba(247,251,255,0.96)_100%)] shadow-[0_30px_85px_rgba(2,9,25,0.37)] backdrop-blur-[10px]">
      <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.42)_0%,rgba(255,255,255,0.04)_34%,rgba(255,255,255,0)_100%),radial-gradient(circle_at_14%_78%,rgba(41,91,168,0.18)_0%,rgba(41,91,168,0)_40%)]"></div>
      <section class="relative z-10 max-h-[calc(100vh-1.5rem)] overflow-y-auto bg-[linear-gradient(180deg,rgba(255,255,255,0.82)_0%,rgba(248,251,255,0.92)_100%)] px-5 py-5 sm:px-8 sm:py-7 lg:px-10 lg:py-8">
        <div class="mx-auto w-full">
          <div class="mb-5 flex flex-col gap-4 border-b border-[#d6dceb] pb-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-14 w-14 items-center justify-center">
                <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-12 w-12">
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#1d3f79]">DILG CAR</p>
                <h2 class="mt-1 font-['Space_Grotesk'] text-3xl font-extrabold tracking-tight text-[#162a56]">Create Applicant Account</h2>
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-[#4c638e]">
                  Set up your account for email verification, application tracking, and secure access to the recruitment portal.
                </p>
              </div>
            </div>

            <div class="rounded-xl border border-[#d2d9ea] bg-[#eef2f8] px-4 py-3 text-sm text-[#4b5f86] lg:max-w-sm">
              <div class="flex items-start gap-3">
                <span class="mt-0.5 text-[#2d4f8c]"><i class="fa-solid fa-shield-halved"></i></span>
                <div>
                  <p class="font-semibold text-[#223a69]">Secure registration</p>
                  <p class="mt-1">Your email address will be used for OTP verification and important status updates.</p>
                </div>
              </div>
            </div>
          </div>

          <form id="registerForm" method="POST" action="{{ route('register') }}" autocomplete="off"
            class="space-y-4"
            x-on:submit.prevent="submitForm($el)">
            @csrf

            <div class="grid gap-4 rounded-2xl border border-[#d5dcea] bg-[#f2f5fb] p-4 md:p-5 xl:grid-cols-[1.15fr_1fr] xl:divide-x xl:divide-[#dce2ef]">
              <section class="rounded-xl bg-transparent p-0 md:pr-5">
                <div class="mb-3">
                  <p class="text-xs font-bold uppercase tracking-wide text-[#2b4575]">Personal Information</p>
                  <p class="mt-1 text-sm text-[#4f6590]">Provide the name and profile details associated with your application.</p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                  <div>
                    
                    <label for="first_name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">First Name <span class="text-red-600">*</span></label>
                    <input id="first_name" type="text" name="first_name" placeholder="First name" value="{{ old('first_name') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200"
                      pattern="^[A-Za-z\s\-\.]{2,50}$"
                      title="Name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
                  </div>

                  <div>
                    <label for="middle_name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Middle Name</label>
                    <input id="middle_name" type="text" name="middle_name" placeholder="Middle name" value="{{ old('middle_name') }}"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200"
                      pattern="^[A-Za-z\s\-\.]{2,50}$"
                      title="Middle name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
                  </div>

                  <div>
                    <label for="last_name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Last Name <span class="text-red-600">*</span></label>
                    <input id="last_name" type="text" name="last_name" placeholder="Last name" value="{{ old('last_name') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200"
                      pattern="^[A-Za-z\s\-\.]{2,50}$"
                      title="Name should contain only letters, spaces, hyphens, or periods (2-50 characters).">
                  </div>
                </div>
                @error('first_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('middle_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('last_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('fname') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('mname') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('lname') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4">
                  <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">Sex <span class="text-red-600">*</span></p>
                  <div class="grid gap-2 sm:grid-cols-3">
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-xs font-medium text-slate-700 transition hover:border-[#0D2B70]/40 sm:text-sm">
                      <input type="radio" name="sex" value="Male" {{ old('sex') === 'Male' ? 'checked' : '' }} required
                        class="h-4 w-4 border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]/30">
                      <span>Male</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-xs font-medium text-slate-700 transition hover:border-[#0D2B70]/40 sm:text-sm">
                      <input type="radio" name="sex" value="Female" {{ old('sex') === 'Female' ? 'checked' : '' }} required
                        class="h-4 w-4 border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]/30">
                      <span>Female</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-xs font-medium text-slate-700 transition hover:border-[#0D2B70]/40 sm:text-sm">
                      <input type="radio" name="sex" value="Prefer not to say" {{ old('sex') === 'Prefer not to say' ? 'checked' : '' }} required
                        class="h-4 w-4 border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]/30">
                      <span>Prefer not to say</span>
                    </label>
                  </div>
                </div>

              </section>

              <section class="rounded-xl bg-transparent p-0 md:pl-5">
                <div class="mb-3">
                  <p class="text-xs font-bold uppercase tracking-wide text-[#2b4575]">Account Details</p>
                  <p class="mt-1 text-sm text-[#4f6590]">Use active contact details create a strong password for secure access.</p>
                </div>

                <div class="space-y-3">
                  <div>
                    <label for="phone_number" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Contact Number <span class="text-red-600">*</span></label>
                    <input id="phone_number" type="text" name="phone_number" placeholder="09XX XXX XXXX" value="{{ old('phone_number') }}" required
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200"
                      pattern="^09[0-9]{2}\s[0-9]{3}\s[0-9]{4}$"
                      title="Contact number must follow the format 09XX XXX XXXX."
                      aria-describedby="phone_number_hint phone_number_feedback"
                      inputmode="numeric"
                      autocomplete="tel"
                      maxlength="13">
                    <p id="phone_number_hint" class="mt-1 text-xs text-slate-500">Format: 09XX XXX XXXX</p>
                    <p id="phone_number_feedback" class="hidden mt-1 text-sm text-red-600" aria-live="polite"></p>
                    @error('phone_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                  </div>

                  <div>
                    <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email Address <span class="text-red-600">*</span></label>
                    <input id="email" type="email" name="email" placeholder="name@example.com" value="{{ old('email') }}" required
                      aria-describedby="email_feedback"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                    <p id="email_feedback" class="hidden mt-1 text-sm text-red-600" aria-live="polite"></p>
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                  </div>

                  <div>
                    <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                    <div class="relative">
                      <input id="password" type="password" name="password" placeholder="Create password" required minlength="8"
                        aria-describedby="password_requirements password_feedback"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{8,}"
                        title="Password must be at least 8 characters long and include uppercase and lowercase letters, a number, and a special character.">
                      <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    <p id="password_feedback" class="hidden mt-1 text-sm text-red-600" aria-live="polite"></p>
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                  </div>

                  <div>
                    <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm Password</label>
                    <div class="relative">
                      <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm password" required minlength="8"
                        aria-describedby="password_confirmation_feedback"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-200">
                      <button type="button" id="togglePasswordConfirm" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    <p id="password_confirmation_feedback" class="hidden mt-1 text-sm text-red-600" aria-live="polite"></p>
                    @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                  </div>

                  <div id="password_requirements" aria-hidden="true" class="hidden rounded-lg border border-slate-200 bg-white px-3 py-3 text-xs text-slate-700 sm:text-sm">
                    <p class="font-semibold text-slate-800">Password must include:</p>
                    <div class="mt-2 space-y-1.5">
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="length">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>At least 8 characters</span>
                      </div>
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="mixedCase">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>Uppercase and lowercase letters</span>
                      </div>
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="number">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>At least 1 number</span>
                      </div>
                      <div class="password-requirement flex items-center gap-2 text-slate-500" data-rule="symbol">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-200 bg-transparent text-[9px] text-slate-300">
                          <i class="fas fa-check"></i>
                        </span>
                        <span>At least 1 special character</span>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <section class="rounded-xl border border-[#d3dbe9] bg-[#eef2f8] p-3 sm:p-4">
              <div class="flex items-start gap-2 rounded-lg border border-[#d2d9ea] bg-[#f2f5fb] px-3 py-2.5">
                <input
                  type="checkbox"
                  id="agree"
                  x-model="checkboxChecked"
                  class="mt-0.5 rounded border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]/30">
                <label for="agree" class="text-xs leading-relaxed text-slate-600 sm:text-sm">
                  I have read and agree to the
                  <span @click.prevent.stop="showModal = true" class="cursor-pointer font-semibold text-[#0D2B70] underline underline-offset-2 hover:text-[#0A2259]">Data Privacy Notice</span>
                </label>
              </div>

              <div class="mt-3 grid gap-2 sm:grid-cols-2">
                <button type="submit"
                  :disabled="!checkboxChecked || isSubmitting"
                  :class="{ 'opacity-50 cursor-not-allowed': !checkboxChecked, 'cursor-wait': isSubmitting }"
                  class="w-full rounded-lg bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]">
                  <span x-text="isSubmitting ? 'Processing...' : 'Create Account'"></span>
                </button>

                <a :class="{
                    'opacity-50 cursor-not-allowed pointer-events-none': !checkboxChecked,
                    'use-loader flex w-full items-center justify-center gap-2 rounded-lg border border-[#c5d0e4] bg-[#f8faff] px-4 py-2.5 text-sm font-semibold text-[#0D2B70] transition hover:bg-[#eef3fb]': true
                  }"
                  :href="checkboxChecked ? '{{ route('google.login', [], false) }}' : '#'">
                  <img src="{{ asset('images/google-icon.png') }}" alt="Google Icon" class="h-4 w-4">
                  Continue with Google
                </a>
              </div>

              <p class="mt-2.5 text-center text-xs text-slate-600 sm:text-sm">
                Already have an account?
                <a href="{{ route('login.form') }}" class="use-loader font-semibold text-[#0D2B70] hover:underline">Log in</a>
              </p>
            </section>
          </form>
        </div>
      </section>
    </div>
  </div>

  @include('partials.loader')

  <script>
    const FIELD_BASE_CLASSES = ['border-slate-300', 'focus:ring-blue-200', 'focus:border-blue-900'];
    const FIELD_ERROR_CLASSES = ['border-red-500', 'focus:ring-red-100', 'focus:border-red-500'];
    const FIELD_SUCCESS_CLASSES = ['border-emerald-500', 'focus:ring-emerald-100', 'focus:border-emerald-500'];

    function applyFieldClasses(input, classesToAdd) {
      if (!input) return;
      input.classList.remove(...FIELD_BASE_CLASSES, ...FIELD_ERROR_CLASSES, ...FIELD_SUCCESS_CLASSES);
      input.classList.add(...classesToAdd);
    }

    function setFieldState(input, feedback, state, message = '') {
      if (!input || !feedback) return state !== 'invalid';

      if (state === 'invalid') {
        applyFieldClasses(input, FIELD_ERROR_CLASSES);
        input.setCustomValidity(message);
        feedback.textContent = message;
        feedback.classList.remove('hidden');
        return false;
      }

      input.setCustomValidity('');
      feedback.textContent = '';
      feedback.classList.add('hidden');

      if (state === 'valid') {
        applyFieldClasses(input, FIELD_SUCCESS_CLASSES);
      } else {
        applyFieldClasses(input, FIELD_BASE_CLASSES);
      }

      return true;
    }

    function formatPhoneNumber(value) {
      const digits = value.replace(/\D/g, '').slice(0, 11);
      const parts = [];

      if (digits.length > 0) parts.push(digits.slice(0, 4));
      if (digits.length > 4) parts.push(digits.slice(4, 7));
      if (digits.length > 7) parts.push(digits.slice(7, 11));

      return parts.join(' ');
    }

    function validatePhoneNumber(force = false) {
      const input = document.getElementById('phone_number');
      const feedback = document.getElementById('phone_number_feedback');
      if (!input || !feedback) return true;

      input.value = formatPhoneNumber(input.value);
      const normalized = input.value.replace(/\D/g, '');
      const showFeedback = force || input.dataset.touched === 'true';

      if (normalized === '') {
        return force
          ? setFieldState(input, feedback, 'invalid', 'Contact number is required.')
          : setFieldState(input, feedback, 'neutral');
      }

      if (!/^09\d{9}$/.test(normalized)) {
        return showFeedback
          ? setFieldState(input, feedback, 'invalid', 'Enter a valid contact number using the format 09XX XXX XXXX.')
          : setFieldState(input, feedback, 'neutral');
      }

      return setFieldState(input, feedback, 'valid');
    }

    function validateEmailAddress(force = false) {
      const input = document.getElementById('email');
      const feedback = document.getElementById('email_feedback');
      if (!input || !feedback) return true;

      const value = input.value.trim();
      input.value = value;
      const showFeedback = force || input.dataset.touched === 'true';
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (value === '') {
        return force
          ? setFieldState(input, feedback, 'invalid', 'Email address is required.')
          : setFieldState(input, feedback, 'neutral');
      }

      if (!emailPattern.test(value)) {
        return showFeedback
          ? setFieldState(input, feedback, 'invalid', 'Enter a valid email address.')
          : setFieldState(input, feedback, 'neutral');
      }

      return setFieldState(input, feedback, 'valid');
    }

    function updatePasswordRequirements() {
      const input = document.getElementById('password');
      if (!input) return { length: false, mixedCase: false, number: false, symbol: false };

      const value = input.value;
      const status = {
        length: value.length >= 8,
        mixedCase: /[a-z]/.test(value) && /[A-Z]/.test(value),
        number: /\d/.test(value),
        symbol: /[^A-Za-z0-9\s]/.test(value),
      };

      document.querySelectorAll('.password-requirement').forEach((item) => {
        const isMet = Boolean(status[item.dataset.rule]);
        const badge = item.querySelector('span');

        item.classList.toggle('text-emerald-700', isMet);
        item.classList.toggle('text-slate-500', !isMet);

        if (badge) {
          badge.classList.toggle('border-emerald-200', isMet);
          badge.classList.toggle('bg-emerald-50', isMet);
          badge.classList.toggle('text-emerald-600', isMet);
          badge.classList.toggle('border-slate-200', !isMet);
          badge.classList.toggle('bg-transparent', !isMet);
          badge.classList.toggle('text-slate-300', !isMet);
        }
      });

      return status;
    }

    function setPasswordRequirementsVisibility(isVisible) {
      const panel = document.getElementById('password_requirements');
      if (!panel) return;

      panel.classList.toggle('hidden', !isVisible);
      panel.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
    }

    function validatePassword(force = false) {
      const input = document.getElementById('password');
      const feedback = document.getElementById('password_feedback');
      if (!input || !feedback) return true;

      const value = input.value;
      const showFeedback = force || input.dataset.touched === 'true';
      const requirements = updatePasswordRequirements();
      const isValid = Object.values(requirements).every(Boolean);

      if (value === '') {
        return force
          ? setFieldState(input, feedback, 'invalid', 'Password is required.')
          : setFieldState(input, feedback, 'neutral');
      }

      if (!isValid) {
        return showFeedback
          ? setFieldState(input, feedback, 'invalid', 'Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.')
          : setFieldState(input, feedback, 'neutral');
      }

      return setFieldState(input, feedback, 'valid');
    }

    function validatePasswordConfirmation(force = false) {
      const passwordInput = document.getElementById('password');
      const confirmInput = document.getElementById('password_confirmation');
      const feedback = document.getElementById('password_confirmation_feedback');
      if (!passwordInput || !confirmInput || !feedback) return true;

      const value = confirmInput.value;
      const showFeedback = force || confirmInput.dataset.touched === 'true';

      if (value === '') {
        return force
          ? setFieldState(confirmInput, feedback, 'invalid', 'Please confirm your password.')
          : setFieldState(confirmInput, feedback, 'neutral');
      }

      if (value !== passwordInput.value) {
        return showFeedback
          ? setFieldState(confirmInput, feedback, 'invalid', 'Passwords do not match.')
          : setFieldState(confirmInput, feedback, 'neutral');
      }

      return setFieldState(confirmInput, feedback, 'valid');
    }

    function validateRegistrationForm(form) {
      const isPhoneValid = validatePhoneNumber(true);
      const isEmailValid = validateEmailAddress(true);
      const isPasswordValid = validatePassword(true);
      const isPasswordConfirmationValid = validatePasswordConfirmation(true);
      const isFormValid = isPhoneValid && isEmailValid && isPasswordValid && isPasswordConfirmationValid && form.checkValidity();

      if (!isFormValid) {
        form.reportValidity();
      }

      return isFormValid;
    }

    function bindBlurValidation(input, validator, options = {}) {
      if (!input) return;

      const { onInput, onFocus, onBlur } = options;

      input.addEventListener('input', () => {
        if (typeof onInput === 'function') {
          onInput();
        }
      });

      input.addEventListener('focus', () => {
        if (typeof onFocus === 'function') {
          onFocus();
        }
      });

      input.addEventListener('blur', () => {
        input.dataset.touched = 'true';
        validator(false);

        if (typeof onBlur === 'function') {
          onBlur();
        }
      });
    }

    function setupPasswordToggle(buttonId, inputId) {
      const button = document.getElementById(buttonId);
      const input = document.getElementById(inputId);
      if (!button || !input) return;

      button.addEventListener('click', function () {
        const isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

        const icon = this.querySelector('i');
        if (!icon) return;

        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      });
    }

    function initializeRegisterForm() {
      const phoneInput = document.getElementById('phone_number');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const passwordConfirmInput = document.getElementById('password_confirmation');
      const passwordToggle = document.getElementById('togglePassword');

      const syncPasswordRequirementsVisibility = () => {
        const activeElement = document.activeElement;
        const shouldShow = activeElement === passwordInput || activeElement === passwordToggle;

        setPasswordRequirementsVisibility(shouldShow);
      };

      if (phoneInput) {
        phoneInput.value = formatPhoneNumber(phoneInput.value);
      }

      bindBlurValidation(phoneInput, validatePhoneNumber, {
        onInput: () => {
          phoneInput.value = formatPhoneNumber(phoneInput.value);
        },
      });
      bindBlurValidation(emailInput, validateEmailAddress);
      bindBlurValidation(passwordInput, validatePassword, {
        onInput: () => {
          updatePasswordRequirements();
        },
        onFocus: () => {
          setPasswordRequirementsVisibility(true);
          updatePasswordRequirements();
        },
        onBlur: () => {
          requestAnimationFrame(syncPasswordRequirementsVisibility);

          if (
            passwordConfirmInput &&
            passwordConfirmInput.value !== '' &&
            passwordConfirmInput.dataset.touched === 'true'
          ) {
            validatePasswordConfirmation(false);
          }
        },
      });
      bindBlurValidation(passwordConfirmInput, validatePasswordConfirmation);

      if (passwordToggle) {
        passwordToggle.addEventListener('focus', () => {
          setPasswordRequirementsVisibility(true);
          updatePasswordRequirements();
        });
        passwordToggle.addEventListener('blur', () => {
          requestAnimationFrame(syncPasswordRequirementsVisibility);
        });
      }

      updatePasswordRequirements();
      setPasswordRequirementsVisibility(false);
      setupPasswordToggle('togglePassword', 'password');
      setupPasswordToggle('togglePasswordConfirm', 'password_confirmation');
    }

    initializeRegisterForm();

    // Keep this global because the privacy modal is rendered inside <template x-if>,
    // where inline <script> tags may not reliably execute in some production setups.
    window.privacyTimer = window.privacyTimer || function privacyTimer() {
      return {
        timeLeft: 5,
        privacyMessage: false,
        showModal: true,

        initTimer() {
          this.timeLeft = 5;
          const timer = setInterval(() => {
            if (this.timeLeft > 0) {
              this.timeLeft--;
            } else {
              clearInterval(timer);
            }
          }, 1000);
        },

        closeModal() {
          if (this.timeLeft <= 0) {
            this.showModal = false;
            this.$dispatch('privacy-modal-closed');
          }
        },

        agreeClicked() {
          if (this.timeLeft <= 0) {
            this.showModal = false;
            this.$dispatch('privacy-agreed');
          }
        },

        disagreeClicked() {
          this.$dispatch('privacy-disagreed');
          this.privacyMessage = true;
          this.showModal = false;
          setTimeout(() => {
            window.location.href = '/';
          }, 3000);
        },
      };
    };

    function signupPage({ hasErrors }) {
      return {
        showModal: false,
        checkboxChecked: false,
        hasErrors: hasErrors === 'true',
        isSubmitting: false,
        submitForm(form) {
          if (!this.checkboxChecked) {
            return;
          }

          if (!validateRegistrationForm(form)) {
            return;
          }

          this.isSubmitting = true;
          form.submit();
        },
        initModal() {
          if (!this.hasErrors && !localStorage.getItem('modalShown')) {
            this.showModal = true;
            localStorage.setItem('modalShown', 'yes');
          }
        }
      }
    }
  </script>
</body>
</html>

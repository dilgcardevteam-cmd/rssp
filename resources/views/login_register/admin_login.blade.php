<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Access - DILG CAR Recruitment and Selection Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
@php
    $registerErrors = $errors->getBag('adminRegister');
    $openRegisterModal = $registerErrors->any() || old('auth_tab') === 'register';
@endphp
<body class="relative min-h-screen overflow-x-hidden overflow-y-auto bg-[radial-gradient(circle_at_85%_18%,rgba(79,172,254,0.24)_0%,rgba(79,172,254,0)_38%),radial-gradient(circle_at_12%_82%,rgba(15,100,201,0.22)_0%,rgba(15,100,201,0)_34%),linear-gradient(140deg,#031029_0%,#0a255f_46%,#12387e_100%)] p-3 font-['Montserrat'] md:p-6 lg:overflow-hidden">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(24,99,156,0.78)_0%,rgba(255,255,255,0.012)_24%,rgba(255,255,255,0)_100%),linear-gradient(90deg,rgba(3,13,33,0.28)_0%,rgba(3,13,33,0)_44%,rgba(3,13,33,0.2)_100%)]"></div>
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_76%_24%,rgba(255,255,255,0.16)_0%,rgba(255,255,255,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,transparent_24%)] opacity-35"></div>
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.45)_0.55px,transparent_0.55px)] opacity-20 mix-blend-soft-light [background-size:9px_9px]"></div>

    <div class="relative z-10 mx-auto flex min-h-[calc(100vh-1.5rem)] w-full max-w-7xl items-center justify-center isolate">
        <div aria-hidden="true" class="pointer-events-none absolute left-1/2 top-[56%] z-0 h-[min(30rem,82vw)] w-[min(30rem,82vw)] -translate-x-1/2 -translate-y-[44%] rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.34)_0%,rgba(96,165,250,0.16)_34%,rgba(59,130,246,0.03)_62%,rgba(59,130,246,0)_72%)] blur-[14px] lg:hidden"></div>
        <div aria-hidden="true" class="pointer-events-none absolute right-[4%] top-[52%] z-0 hidden h-[min(40rem,46vw)] w-[min(40rem,46vw)] -translate-y-1/2 rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.34)_0%,rgba(96,165,250,0.16)_34%,rgba(59,130,246,0.03)_62%,rgba(59,130,246,0)_72%)] blur-[14px] lg:block"></div>

        <div class="relative z-10 grid w-full max-h-[calc(100vh-1.5rem)] overflow-hidden rounded-[1.9rem] border border-white/20 bg-[linear-gradient(140deg,rgba(255,255,255,0.96)_0%,rgba(247,251,255,0.96)_100%)] shadow-[0_30px_85px_rgba(2,9,25,0.37)] backdrop-blur-[10px] lg:grid-cols-[1.15fr_1fr]">
            <section class="relative hidden overflow-hidden bg-[linear-gradient(145deg,rgba(255,255,255,0.1)_0%,rgba(255,255,255,0)_36%),linear-gradient(175deg,#081c47_0%,#0d2b70_54%,#18468f_100%)] px-6 py-10 text-white shadow-[inset_-1px_0_0_rgba(255,255,255,0.08)] lg:flex lg:items-center lg:justify-center xl:px-8">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_16%_14%,rgba(245,200,75,0.12)_0%,rgba(245,200,75,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,rgba(255,255,255,0.03)_20%,rgba(255,255,255,0)_46%)] opacity-70"></div>

                <div class="relative z-10 w-full max-w-[40rem] px-2 text-center sm:px-4 xl:px-6">
                    <img
                        src="{{ asset('images/dilg_logo.png') }}"
                        alt="DILG Logo"
                        class="mx-auto h-24 w-24 rounded-full bg-white/10 p-3 shadow-[0_16px_32px_rgba(2,12,34,0.24)] xl:h-28 xl:w-28"
                    />
                    <p class="mt-6 text-sm uppercase tracking-[0.42em] text-blue-100">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT - CORDILLERA ADMINISTRATIVE REGION</p>
                    <h3 class="mt-4 text-3xl font-extrabold leading-tight text-white font-['Space_Grotesk'] tracking-[-0.01em] xl:text-[2rem]">
                        Recruitment Selection and Placement Portal
                    </h3>
                    <!-- <p class="mt-5 block w-full whitespace-nowrap text-[clamp(0.5rem,0.42rem+0.18vw,0.6rem)] font-semibold uppercase leading-[1.5] tracking-[0.015em]">
                        DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT CORDILLERA ADMINISTRATIVE REGION
                    </p> -->
                    <p class="mt-4 text-sm font-semibold uppercase tracking-[0.24em] text-yellow-200 xl:text-base">
                        Matino, Mahusay, AT Maaasahan.
                    </p>
                </div>
            </section>

            <section class="relative bg-[rgba(249,252,255,0.95)] px-5 py-8 sm:px-8 lg:bg-[linear-gradient(180deg,rgba(255,255,255,0.82)_0%,rgba(248,251,255,0.92)_100%)] lg:px-10">
                <div class="mx-auto w-full max-w-xl">
                    <div class="mb-6 flex items-center gap-3 lg:hidden">
                        <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-12 w-12" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#0D2B70]">DILG CAR</p>
                            <p class="text-lg font-bold tracking-[-0.01em] text-[#0D2B70] font-['Space_Grotesk']">Access Portal</p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-5">
                            <h2 class="text-2xl font-extrabold tracking-[-0.01em] text-[#0D2B70] font-['Space_Grotesk']">Admin Login</h2>
                            <p class="mt-1 text-sm text-slate-500">Sign in to continue to your assigned dashboard.</p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any() && !$registerErrors->any())
                            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="adminLoginForm" action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4" autocomplete="off">
                            @csrf
                            <input type="hidden" name="auth_tab" value="login">

                            <div>
                                <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Email</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] py-2.5 pl-10 pr-3 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Password</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <input
                                        id="admin-password"
                                        type="password"
                                        name="password"
                                        required
                                        class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] py-2.5 pl-10 pr-10 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15"
                                    >
                                    <button
                                        type="button"
                                        onclick="toggleAdminPassword()"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                                        tabindex="-1"
                                        aria-label="Toggle password visibility"
                                    >
                                        <i id="admin-password-icon" class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- <div class="flex items-center justify-between pt-1 text-sm">
                                <button
                                    type="button"
                                    class="font-semibold text-[#0D2B70] hover:underline"
                                    onclick="document.getElementById('adminForgotModal').classList.remove('hidden')"
                                >
                                    Forgot Password?
                                </button>
                            </div> -->

                            <button type="submit" class="w-full rounded-[0.85rem] bg-[linear-gradient(135deg,#0d2b70_0%,#174493_100%)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_9px_20px_rgba(13,43,112,0.26)] transition-[transform,box-shadow,filter] hover:-translate-y-px hover:brightness-[1.02] hover:shadow-[0_12px_26px_rgba(13,43,112,0.35)]">
                                Sign In
                            </button>
                        </form>

                        <div class="mt-5 border-t border-slate-200 pt-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">New Employee Account</p>
                            <button id="openRegisterModalBtn" type="button"
                                class="mt-2 w-full rounded-[0.85rem] border border-[#0d2b70]/45 bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] transition-[background-color,color,border-color] hover:border-[#0d2b70] hover:bg-[#0d2b70] hover:text-white">
                                Register Employee Account
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="adminRegisterModal"
        class="fixed inset-0 z-[12000] {{ $openRegisterModal ? 'flex' : 'hidden' }} items-center justify-center bg-[linear-gradient(180deg,rgba(2,10,29,0.74)_0%,rgba(2,10,29,0.66)_100%)] px-4 py-6 backdrop-blur-[3px]">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-[#d8e3f3] bg-[linear-gradient(180deg,#ffffff_0%,#fbfdff_100%)] shadow-[0_24px_52px_rgba(7,24,58,0.3)]">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-bold text-[#0D2B70]">Employee Registration</h3>
                    <p class="text-xs text-slate-500">Role assignment is handled by superadmin after approval.</p>
                </div>
                <button id="closeRegisterModalBtn" type="button"
                    class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close registration modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="max-h-[78vh] overflow-y-auto p-6">
                @if ($registerErrors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc pl-5">
                            @foreach ($registerErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="adminRegisterForm" action="{{ route('admin.register.submit') }}" method="POST" class="space-y-4" autocomplete="off">
                    @csrf
                    <input type="hidden" name="auth_tab" value="register">

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                        </div>
                        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">Work Assignment</p>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Office</label>
                                    <input type="text" name="office" value="{{ old('office') }}" required placeholder="Enter office/unit"
                                        class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Designation</label>
                                    <input type="text" name="designation" value="{{ old('designation') }}" required placeholder="Enter position/designation"
                                        class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Password</label>
                            <input id="registerPassword" type="password" name="password" required minlength="8"
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Confirm Password</label>
                            <input id="registerPasswordConfirm" type="password" name="password_confirmation" required minlength="8"
                                class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] px-3 py-2.5 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15">
                        </div>
                    </div>

                    <div id="adminPasswordRequirementsPanel" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Password Requirements</p>
                        <ul id="passwordRequirements" class="mt-2 space-y-1 text-xs">
                            <li data-rule="length" class="flex items-center gap-2 text-slate-500"><i class="fa-regular fa-circle"></i><span>At least 8 characters</span></li>
                            <li data-rule="upper" class="flex items-center gap-2 text-slate-500"><i class="fa-regular fa-circle"></i><span>At least 1 uppercase letter</span></li>
                            <li data-rule="lower" class="flex items-center gap-2 text-slate-500"><i class="fa-regular fa-circle"></i><span>At least 1 lowercase letter</span></li>
                            <li data-rule="number" class="flex items-center gap-2 text-slate-500"><i class="fa-regular fa-circle"></i><span>At least 1 number</span></li>
                            <li data-rule="special" class="flex items-center gap-2 text-slate-500"><i class="fa-regular fa-circle"></i><span>At least 1 special character</span></li>
                            <li data-rule="match" class="flex items-center gap-2 text-slate-500"><i class="fa-regular fa-circle"></i><span>Password and confirm password must match</span></li>
                        </ul>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button id="cancelRegisterModalBtn" type="button"
                            class="rounded-[0.85rem] border border-[#0d2b70]/45 bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] transition-[background-color,color,border-color] hover:border-[#0d2b70] hover:bg-[#0d2b70] hover:text-white">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-[0.85rem] bg-[linear-gradient(135deg,#0d2b70_0%,#174493_100%)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_9px_20px_rgba(13,43,112,0.26)] transition-[transform,box-shadow,filter] hover:-translate-y-px hover:brightness-[1.02] hover:shadow-[0_12px_26px_rgba(13,43,112,0.35)]">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="adminForgotModal" class="fixed inset-0 z-[11000] hidden flex items-center justify-center bg-[linear-gradient(180deg,rgba(2,10,29,0.74)_0%,rgba(2,10,29,0.66)_100%)] px-4 py-6 backdrop-blur-[3px]">
        <div class="w-full max-w-md rounded-2xl border border-[#d8e3f3] bg-[linear-gradient(180deg,#ffffff_0%,#fbfdff_100%)] p-6 shadow-[0_24px_52px_rgba(7,24,58,0.3)]">
            <div class="flex items-start justify-between">
                <h3 class="text-lg font-bold text-[#0D2B70]">Password Reset</h3>
                <button type="button" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    onclick="document.getElementById('adminForgotModal').classList.add('hidden')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="mt-3 text-sm text-slate-600">
                Contact the superadmin to reset your account password.
            </p>
            <button type="button" class="mt-6 w-full rounded-[0.85rem] bg-[linear-gradient(135deg,#0d2b70_0%,#174493_100%)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_9px_20px_rgba(13,43,112,0.26)] transition-[transform,box-shadow,filter] hover:-translate-y-px hover:brightness-[1.02] hover:shadow-[0_12px_26px_rgba(13,43,112,0.35)]"
                onclick="document.getElementById('adminForgotModal').classList.add('hidden')">
                Close
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const registerModal = document.getElementById('adminRegisterModal');
            const openRegisterButton = document.getElementById('openRegisterModalBtn');
            const closeRegisterButton = document.getElementById('closeRegisterModalBtn');
            const cancelRegisterButton = document.getElementById('cancelRegisterModalBtn');

            const passwordInput = document.getElementById('registerPassword');
            const passwordConfirmInput = document.getElementById('registerPasswordConfirm');
            const passwordRequirementsPanel = document.getElementById('adminPasswordRequirementsPanel');

            const openRegisterModal = () => {
                if (!registerModal) return;
                registerModal.classList.remove('hidden');
                registerModal.classList.add('flex');
            };

            const closeRegisterModal = () => {
                if (!registerModal) return;
                registerModal.classList.add('hidden');
                registerModal.classList.remove('flex');
            };

            if (openRegisterButton) {
                openRegisterButton.addEventListener('click', openRegisterModal);
            }

            if (closeRegisterButton) {
                closeRegisterButton.addEventListener('click', closeRegisterModal);
            }

            if (cancelRegisterButton) {
                cancelRegisterButton.addEventListener('click', closeRegisterModal);
            }

            if (registerModal) {
                registerModal.addEventListener('click', (event) => {
                    if (event.target === registerModal) {
                        closeRegisterModal();
                    }
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && registerModal && !registerModal.classList.contains('hidden')) {
                    closeRegisterModal();
                }
            });

            const setRequirementState = (ruleElement, isValid) => {
                if (!ruleElement) return;
                const icon = ruleElement.querySelector('i');

                ruleElement.classList.toggle('text-emerald-700', isValid);
                ruleElement.classList.toggle('text-slate-500', !isValid);

                if (icon) {
                    icon.classList.toggle('fa-circle-check', isValid);
                    icon.classList.toggle('fa-circle', !isValid);
                    icon.classList.toggle('fa-regular', !isValid);
                    icon.classList.toggle('fa-solid', isValid);
                }
            };

            const updatePasswordRequirements = () => {
                const password = passwordInput ? passwordInput.value : '';
                const passwordConfirm = passwordConfirmInput ? passwordConfirmInput.value : '';

                const rules = {
                    length: password.length >= 8,
                    upper: /[A-Z]/.test(password),
                    lower: /[a-z]/.test(password),
                    number: /\d/.test(password),
                    special: /[^A-Za-z\d]/.test(password),
                    match: password.length > 0 && password === passwordConfirm,
                };

                Object.keys(rules).forEach((ruleName) => {
                    const element = document.querySelector(`[data-rule="${ruleName}"]`);
                    setRequirementState(element, rules[ruleName]);
                });
            };

            const setPasswordRequirementsVisibility = (isVisible) => {
                if (!passwordRequirementsPanel) return;

                passwordRequirementsPanel.classList.toggle('hidden', !isVisible);
            };

            const syncPasswordRequirementsVisibility = () => {
                const activeElement = document.activeElement;
                const shouldShow = activeElement === passwordInput || activeElement === passwordConfirmInput;

                setPasswordRequirementsVisibility(shouldShow);
            };

            if (passwordInput) {
                passwordInput.addEventListener('focus', () => {
                    setPasswordRequirementsVisibility(true);
                    updatePasswordRequirements();
                });
                passwordInput.addEventListener('input', updatePasswordRequirements);
                passwordInput.addEventListener('blur', () => {
                    requestAnimationFrame(syncPasswordRequirementsVisibility);
                });
            }

            if (passwordConfirmInput) {
                passwordConfirmInput.addEventListener('focus', () => {
                    setPasswordRequirementsVisibility(true);
                    updatePasswordRequirements();
                });
                passwordConfirmInput.addEventListener('input', updatePasswordRequirements);
                passwordConfirmInput.addEventListener('blur', () => {
                    requestAnimationFrame(syncPasswordRequirementsVisibility);
                });
            }

            updatePasswordRequirements();
            setPasswordRequirementsVisibility(false);

            const shouldOpenRegisterModal = @json($openRegisterModal);
            if (shouldOpenRegisterModal) {
                openRegisterModal();
            }
        });

        function toggleAdminPassword() {
            const input = document.getElementById('admin-password');
            const icon  = document.getElementById('admin-password-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>

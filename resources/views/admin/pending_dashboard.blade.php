<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Pending Approval</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#071A4D] via-[#0A2566] to-[#12398B] p-4">
    <div class="mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-5xl items-center justify-center">
        <div class="w-full overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl">
            <div class="grid gap-0 lg:grid-cols-[1.1fr_1fr]">
                <section class="bg-[#0B2A71] px-8 py-10 text-white">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-14 w-14" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-100">DILG CAR</p>
                            <h1 class="text-2xl font-extrabold">Employee Dashboard</h1>
                        </div>
                    </div>

                    <div class="mt-10 max-w-md space-y-4">
                        <h2 class="text-3xl font-extrabold">Registration Submitted</h2>
                        <p class="text-sm leading-relaxed text-blue-100">
                            Your account is currently waiting for superadmin approval. You will be able to access modules after a role is assigned.
                        </p>
                    </div>

                    <div class="mt-8 rounded-2xl border border-white/25 bg-white/10 p-5 text-sm text-blue-50">
                        <p><span class="font-semibold">Name:</span> {{ $admin->name }}</p>
                        <p class="mt-1"><span class="font-semibold">Email:</span> {{ $admin->email }}</p>
                        <p class="mt-1"><span class="font-semibold">Status:</span> Pending Approval</p>
                    </div>
                </section>

                <section class="bg-slate-50 px-8 py-10">
                    @if (session('registered_pending'))
                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('registered_pending') }}
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-[#0D2B70]">What happens next?</h3>
                        <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm text-slate-700">
                            <li>Superadmin reviews your registration request.</li>
                            <li>Superadmin approves and assigns your account role.</li>
                            <li>You can then access pages allowed by your assigned role.</li>
                        </ol>

                        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Keep this page open or login again later to check your approval status.
                        </div>

                        <form id="pendingAdminLogoutForm" action="{{ route('admin.logout') }}" method="POST" class="mt-6">
                            @csrf
                            <button type="button" @click.prevent="$dispatch('open-pending-logout-confirm')" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                Logout
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div id="pendingModal" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-900/55 px-4 py-6">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <h2 class="text-lg font-bold text-[#0D2B70]">Waiting for Approval</h2>
            <p class="mt-2 text-sm text-slate-600">
                Your registration is pending superadmin approval. You will receive access after your account type is assigned.
            </p>
            <button type="button" class="mt-6 w-full rounded-xl bg-[#0D2B70] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0A2259]"
                onclick="document.getElementById('pendingModal').classList.add('hidden')">
                Okay
            </button>
        </div>
    </div>

    <x-confirm-modal title="Confirm Logout" message="Are you sure you want to logout?"
        event="open-pending-logout-confirm" confirm="confirm-pending-logout" />

    <script>
        window.addEventListener('confirm-pending-logout', () => {
            const logoutForm = document.getElementById('pendingAdminLogoutForm');
            if (logoutForm) logoutForm.submit();
        });
    </script>
</body>
</html>

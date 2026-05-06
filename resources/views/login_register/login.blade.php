<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login - DILG CAR Recruitment and Selection Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />

  <meta property="og:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta property="og:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
  <meta property="og:image" content="{{ asset('images/dilg_rsp_thumbnail.png') }}" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:type" content="image/png" />
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:type" content="website" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="DILG - CAR Recruitment Selection and Placement Portal" />
  <meta name="twitter:description" content="Isa ka bang MATINO, MAHUSAY, at MAAASAHAN na manggagawang Pilipino?" />
  <meta name="twitter:image" content="{{ asset('images/dilg_rsp_thumbnail.png') }}" />

</head>
<body class="relative min-h-screen overflow-x-hidden overflow-y-auto bg-[radial-gradient(circle_at_85%_18%,rgba(79,172,254,0.24)_0%,rgba(79,172,254,0)_38%),radial-gradient(circle_at_12%_82%,rgba(15,100,201,0.22)_0%,rgba(15,100,201,0)_34%),linear-gradient(140deg,#031029_0%,#0a255f_46%,#12387e_100%)] p-3 font-['Montserrat'] md:p-6 lg:overflow-hidden">
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(24,99,156,0.78)_0%,rgba(255,255,255,0.012)_24%,rgba(255,255,255,0)_100%),linear-gradient(90deg,rgba(3,13,33,0.28)_0%,rgba(3,13,33,0)_44%,rgba(3,13,33,0.2)_100%)]"></div>
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_76%_24%,rgba(255,255,255,0.16)_0%,rgba(255,255,255,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,transparent_24%)] opacity-35"></div>
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.45)_0.55px,transparent_0.55px)] opacity-20 mix-blend-soft-light [background-size:9px_9px]"></div>

  <div class="relative z-10 mx-auto flex min-h-[calc(100vh-1.5rem)] w-full max-w-7xl items-center justify-center isolate">
    <div aria-hidden="true" class="pointer-events-none absolute left-1/2 top-[56%] z-0 h-[min(30rem,82vw)] w-[min(30rem,82vw)] -translate-x-1/2 -translate-y-[44%] rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.34)_0%,rgba(96,165,250,0.16)_34%,rgba(59,130,246,0.03)_62%,rgba(59,130,246,0)_72%)] blur-[14px] lg:hidden"></div>
    <div aria-hidden="true" class="pointer-events-none absolute right-[4%] top-[52%] z-0 hidden h-[min(40rem,46vw)] w-[min(40rem,46vw)] -translate-y-1/2 rounded-full bg-[radial-gradient(circle,rgba(147,197,253,0.34)_0%,rgba(96,165,250,0.16)_34%,rgba(59,130,246,0.03)_62%,rgba(59,130,246,0)_72%)] blur-[14px] lg:block"></div>

    <div class="relative z-10 grid w-full max-h-[calc(100vh-1.5rem)] overflow-hidden rounded-[1.9rem] border border-white/20 bg-[linear-gradient(140deg,rgba(255,255,255,0.96)_0%,rgba(247,251,255,0.96)_100%)] shadow-[0_30px_85px_rgba(2,9,25,0.37)] backdrop-blur-[10px] lg:grid-cols-[minmax(0,0.94fr)_minmax(0,1.06fr)]">
      <section class="relative bg-[rgba(249,252,255,0.95)] px-5 py-8 sm:px-8 lg:bg-[linear-gradient(180deg,rgba(255,255,255,0.82)_0%,rgba(248,251,255,0.92)_100%)] lg:px-10 xl:px-12">
        <div class="mx-auto w-full max-w-lg">
          <div class="mb-6 flex items-center gap-3 lg:hidden">
            <img src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" class="h-12 w-12" />
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#0D2B70]">DILG CAR</p>
              <p class="text-lg font-bold tracking-[-0.01em] text-[#0D2B70] font-['Space_Grotesk']">Recruitment Portal</p>
            </div>
          </div>

          <div>
            <div class="mb-6">
              <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Applicant Access</p>
              <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-[#0D2B70] font-['Space_Grotesk']">User Login</h2>
              <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to continue to your applicant dashboard.</p>
            </div>

            @if (session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
              </div>
            @endif

            @if (session('status'))
              <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                {{ session('status') }}
              </div>
            @endif

            @if ($errors->any())
              <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" autocomplete="off">
              @csrf

              <div>
                <label class="mb-1.5 block text-[0.68rem] font-bold uppercase tracking-[0.12em] text-[#52627d]">Email</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-envelope"></i>
                  </span>
                  <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
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
                    id="user_password"
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-[0.85rem] border border-[#cdd9eb] bg-[#fbfdff] py-2.5 pl-10 pr-10 text-sm text-[#203457] outline-none transition-[border-color,box-shadow,background-color] hover:border-[#b5c8e7] hover:bg-white focus:border-[#0d2b70] focus:bg-white focus:ring-4 focus:ring-[#0d2b70]/15"
                  >
                  <button
                    type="button"
                    id="togglePassword"
                    aria-label="Toggle password visibility"
                    aria-pressed="false"
                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                  >
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="flex flex-col gap-2 pt-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                <!-- <label class="inline-flex items-center gap-2 text-slate-600">
                  <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="rounded border-slate-300 text-[#0D2B70] focus:ring-[#0D2B70]/30">
                  <span class="font-semibold">Remember me</span>
                </label> -->
                <a href="{{ route('forgot.password.form') }}" class="font-semibold text-[#0D2B70] hover:underline">Forgot Password?</a>
              </div>

              <button type="submit" class="w-full rounded-[0.85rem] bg-[linear-gradient(135deg,#0d2b70_0%,#174493_100%)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_9px_20px_rgba(13,43,112,0.26)] transition-[transform,box-shadow,filter] hover:-translate-y-px hover:brightness-[1.02] hover:shadow-[0_12px_26px_rgba(13,43,112,0.35)]">
                Sign In
              </button>
            </form>

            <div class="mt-5 border-t border-slate-200 pt-4">
              <!-- <p class="text-xs uppercase tracking-wide text-slate-500">Alternative Access</p> -->
              <!-- <a
                href="{{ route('google.login', [], false) }}"
                class="use-loader mt-2 flex w-full items-center justify-center gap-3 rounded-[0.85rem] border border-[#0d2b70]/20 bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] transition-[border-color,box-shadow,transform] hover:-translate-y-px hover:border-[#0d2b70]/35 hover:shadow-[0_10px_24px_rgba(13,43,112,0.12)]"
              >
                <img src="{{ asset('images/google-icon.png') }}" alt="Google Icon" class="h-5 w-5">
                Continue with Google
              </a> -->

              @if (Route::has('register.form'))
                <p class="mt-4 text-center text-sm text-slate-600">
                  Don't have an account?
                  <a href="{{ route('register.form') }}" class="use-loader font-semibold text-[#0D2B70] hover:underline">Create one here</a>
                </p>
              @endif
            </div>
          </div>
        </div>
      </section>

      <section class="relative hidden overflow-hidden bg-[linear-gradient(145deg,rgba(255,255,255,0.1)_0%,rgba(255,255,255,0)_36%),linear-gradient(175deg,#081c47_0%,#0d2b70_54%,#18468f_100%)] px-6 py-10 text-white shadow-[inset_-1px_0_0_rgba(255,255,255,0.08)] lg:flex lg:items-center lg:justify-center xl:px-8">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_16%_14%,rgba(245,200,75,0.12)_0%,rgba(245,200,75,0)_28%),linear-gradient(180deg,rgba(255,255,255,0.08)_0%,rgba(255,255,255,0.03)_20%,rgba(255,255,255,0)_46%)] opacity-70"></div>

        <div class="relative z-10 w-full max-w-[40rem] px-2 text-center sm:px-4 xl:px-6">
          <img
            src="{{ asset('images/dilg_logo.png') }}"
            alt="DILG Logo"
            class="mx-auto h-24 w-24 rounded-full bg-white/10 p-3 shadow-[0_16px_32px_rgba(2,12,34,0.24)] xl:h-28 xl:w-28"
          />
          <p class="mt-6 text-sm uppercase tracking-[0.42em] text-blue-100">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</p>
          <p class="mt-1 text-xs uppercase tracking-[0.42em] text-blue-100">CORDILLERA ADMINISTRATIVE REGION</p>
          <h3 class="mt-4 text-3xl  leading-tight text-white font-['Space_Grotesk'] tracking-[-0.01em] xl:text-[2rem]">
            Recruitment Selection and Placement Portal
          </h3>
          <p class="mt-4 text-sm font-semibold uppercase tracking-[0.24em] text-yellow-200 xl:text-base">
            Matino, Mahusay, at Maaasahan.
          </p>
        </div>
      </section>
    </div>
  </div>

  @include('partials.loader')

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('user_password');

    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

        const icon = this.querySelector('i');
        if (!icon) return;

        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      });
    }
  </script>
</body>
</html>

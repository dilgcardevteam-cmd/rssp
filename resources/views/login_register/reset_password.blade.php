<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="flex h-screen flex-col bg-white font-['Montserrat']">
    <!-- Header Bar -->
    <header class="bg-[#002b6d] flex items-center h-20 px-6 space-x-6">
        <div class="flex-shrink-0">
            <img
                src="{{ asset('images/dilg_logo.png') }}"
                alt="DILG Logo"
                class="mx-auto mb-5 mt-5 max-w-[67px]"
                loading="lazy"
            />
        </div>
        <div class="flex flex-col text-white leading-tight max-w-lg">
            <span class="text-sm font-bold">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</span>
            <span class="text-xs opacity-70">CORDILLERA ADMINISTRATIVE REGION</span>
            <span class="text-xs font-bold text-yellow-400">
                RECRUITMENT SELECTION AND PLACEMENT PORTAL
            </span>
        </div>
    </header>

    <!-- Main content -->
    <main class="flex-grow flex justify-center items-center">
        <form method="POST" action="{{ route('forgot.password.reset') }}">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">
            <div class="bg-[#002b6d] rounded-3xl py-5 px-8 flex flex-col items-center shadow-md w-auto h-auto">
                <h1 class="mb-1 mt-10 text-center text-xl font-bold text-white">RESET PASSWORD</h1>
                <p class="mb-10 max-w-xs text-center text-sm text-white">
                    Please enter your new password and confirm it.
                </p>

                <input
                    required
                    type="password"
                    name="password"
                    minlength="6"
                    placeholder="Enter your new password"
                    class="mt-[-1.5rem] w-full max-w-xs rounded-full px-6 py-2 text-center placeholder:font-semibold placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                <input
                    required
                    type="password"
                    name="password_confirmation"
                    minlength="6"
                    placeholder="Confirm your new password"
                    class="mt-6 w-full max-w-xs rounded-full px-6 py-2 text-center placeholder:font-semibold placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                @if ($errors->any())
                    <div class="mb-3 mt-3 text-sm text-red-500">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button
                    class="bg-yellow-400 mb-10 hover:bg-yellow-500 text-gray-700 font-semibold rounded-full mt-6 py-2 px-14 shadow-md focus:outline-none focus:ring-4 focus:ring-blue-300"
                    type="submit">
                    CHANGE PASSWORD
                </button>
            </div>
        </form>
    </main>

    @include('partials.loader')
</body>
</html>

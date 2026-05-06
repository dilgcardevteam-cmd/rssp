<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', $title ?? 'Error')</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Montserrat Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;800&display=swap" rel="stylesheet" />

  <style>
    @keyframes barUpDown {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    @keyframes barDownUp {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(20px); }
    }

    .animate-barUpDown {
      animation: barUpDown 2s ease-in-out infinite;
    }

    .animate-barDownUp {
      animation: barDownUp 2s ease-in-out infinite;
    }
    body {
      font-family: 'Montserrat', sans-serif;
    }
  </style>
</head>
<body class="bg-[#f3f6fb] flex items-center justify-center min-h-screen relative overflow-hidden">

  <!-- Mobile top bars -->
  <div class="absolute top-0 left-0 w-full flex md:hidden z-0">
    <div class="w-1/3 h-[60px] bg-[#002c76] animate-barUpDown" style="animation-delay: 0s;"></div>
    <div class="w-1/3 h-[60px] bg-[#d2232a] animate-barUpDown" style="animation-delay: 0.2s;"></div>
    <div class="w-1/3 h-[60px] bg-[#fbd116] animate-barUpDown" style="animation-delay: 0.4s;"></div>
  </div>

  <!-- Mobile bottom bars -->
  <div class="absolute bottom-0 left-0 w-full flex md:hidden z-0">
    <div class="w-1/3 h-[60px] bg-[#d2232a] animate-barDownUp" style="animation-delay: 0s;"></div>
    <div class="w-1/3 h-[60px] bg-[#fbd116] animate-barDownUp" style="animation-delay: 0.2s;"></div>
    <div class="w-1/3 h-[60px] bg-[#002c76] animate-barDownUp" style="animation-delay: 0.4s;"></div>
  </div>

  <!-- Desktop left bars -->
  <div class="hidden md:block absolute left-0 top-0 w-[60px] h-[80%] bg-[#002c76] animate-barUpDown" style="animation-delay: 0s;"></div>
  <div class="hidden md:block absolute left-[60px] top-0 w-[60px] h-full bg-[#d2232a] animate-barUpDown" style="animation-delay: 0.2s;"></div>
  <div class="hidden md:block absolute left-[120px] top-0 w-[60px] h-[60%] bg-[#fbd116] animate-barUpDown" style="animation-delay: 0.4s;"></div>

  <!-- Desktop right bars -->
  <div class="hidden md:block absolute right-0 bottom-0 w-[60px] h-full bg-[#d2232a] animate-barDownUp" style="animation-delay: 0s;"></div>
  <div class="hidden md:block absolute right-[60px] bottom-0 w-[60px] h-[60%] bg-[#fbd116] animate-barDownUp" style="animation-delay: 0.2s;"></div>
  <div class="hidden md:block absolute right-[120px] bottom-0 w-[60px] h-[80%] bg-[#002c76] animate-barDownUp" style="animation-delay: 0.4s;"></div>

  <!-- Main content -->
  <div class="text-center px-6 z-10">
    <h1 class="text-6xl md:text-9xl font-extrabold">@yield('code', $code ?? 'Error')</h1>
    <h2 class="text-2xl md:text-4xl font-extrabold mb-6">@yield('heading', $message ?? 'Something went wrong')</h2>
    <p class="text-gray-700 font-semibold mb-6">@yield('message', 'An unexpected error has occurred.')</p>
    <p class="text-gray-500 mb-6">If you need assistance, please contact support.</p>
   <a href="/"
   class="hidden md:inline-block bg-[#002c76] text-white text-xs px-6 py-2 rounded-md hover:bg-[#001a4d] transition">
   GO HOME
</a>

  </div>

</body>
</html>

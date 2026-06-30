<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', '9-Box'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
      @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
      }
      .anim-fade-up { animation: fadeSlideUp 0.4s cubic-bezier(.2,.8,.3,1) both; }
      .anim-delay-1 { animation-delay: 0.06s; }
      .anim-delay-2 { animation-delay: 0.12s; }
      .anim-delay-3 { animation-delay: 0.18s; }
      .anim-delay-4 { animation-delay: 0.24s; }
    </style>
  </head>

  <body class="font-sans antialiased bg-surface text-ink min-h-screen flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-sm anim-fade-up">

      <!-- Logo -->
      <div class="flex justify-center mb-8 anim-fade-up">
        <img src="{{ asset('images/infocomm-logo.png') }}" alt="Infocomm" class="h-14 w-auto">
      </div>

      <!-- Card -->
      <div class="bg-canvas border border-border rounded-lg shadow-card px-8 py-8 anim-fade-up anim-delay-1">
        {{ $slot }}
      </div>

    </div>
  </body>
</html>
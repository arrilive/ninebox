<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', '9-Box'))</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 bg-gray-100 dark:bg-gray-900">

      <!-- Logo principal (ya no clickable) -->
      <div class="mb-8 flex justify-center">
        <img src="{{ asset('images/BPT-LOGO.png') }}" alt="BPT Group"
             class="h-16 sm:h-20 w-auto transition-transform duration-300 drop-shadow-md select-none" />
      </div>

      <!-- Contenido principal -->
      <div class="w-full sm:max-w-md px-6 py-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur 
                  shadow-md overflow-hidden sm:rounded-2xl">
        {{ $slot }}
      </div>

    </div>
  </body>
</html>
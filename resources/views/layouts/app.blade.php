<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>@yield('title', config('app.name', '9-Box'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Vite: solo entradas principales -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
        ])
    </head>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <!-- CSRF disponible para JS global -->
        <script>
            window.APP_CSRF = '{{ csrf_token() }}';
        </script>

        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Lugar opcional para modales inyectados desde las vistas -->
        @stack('modals')

        <!-- Stack opcional para scripts por-vista -->
        @stack('scripts')

        @if (session('alert'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const data = @json(session('alert'));

                    // Detectar modo oscuro de forma más robusta
                    const storedTheme = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const hasDarkClass = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

                    const isDark =
                        storedTheme === 'dark' ||
                        (!storedTheme && prefersDark) ||
                        hasDarkClass;

                    if (isDark) {
                        // 🌙 MODO OSCURO — tal cual tú lo tenías, SIN CAMBIOS
                        Swal.fire({
                            icon: data.type || 'success',
                            title: data.title || 'Operación realizada',
                            html: data.text || '',
                            confirmButtonText: 'Aceptar',
                            buttonsStyling: false,
                            backdrop: 'rgba(15,23,42,0.75)',
                            customClass: {
                                popup: 'rounded-2xl bg-slate-900/95 text-white border border-indigo-500/40 shadow-2xl',
                                title: 'text-xl font-semibold text-white',
                                htmlContainer: 'text-sm text-slate-200',
                                confirmButton: 'px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-500 hover:to-purple-500'
                            }
                        });
                    } else {
                        // ☀️ MODO CLARO — versión clara
                        Swal.fire({
                            icon: data.type || 'success',
                            title: data.title || 'Operación realizada',
                            html: data.text || '',
                            confirmButtonText: 'Aceptar',
                            buttonsStyling: false,
                            backdrop: 'rgba(15,23,42,0.45)',
                            customClass: {
                                popup: 'rounded-2xl bg-white text-slate-900 border border-indigo-500/20 shadow-2xl',
                                title: 'text-xl font-semibold text-slate-900',
                                htmlContainer: 'text-sm text-slate-700',
                                confirmButton: 'px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-500 hover:to-purple-500'
                            }
                        });
                    }
                });
            </script>
        @endif
    </body>
</html>
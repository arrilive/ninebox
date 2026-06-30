<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>@yield('title', config('app.name', '9-Box'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Vite: solo entradas principales -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
        ])
    </head>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <body class="font-sans antialiased bg-surface text-ink">
        <!-- CSRF disponible para JS global -->
        <script>
            window.APP_CSRF = '{{ csrf_token() }}';
        </script>

        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @if (request()->is('admin*') || request()->routeIs('admin.*'))
                <div class="bg-canvas border-b border-border py-2">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <a href="{{ route('admin.empresas.index') }}" class="text-sm font-medium text-primary hover:text-primary-hover inline-flex items-center gap-1">
                            <span>←</span>
                            <span>{{ __('Empresas') }}</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-canvas border-b border-border">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @elseif (trim($__env->yieldContent('header')))
                <header class="bg-canvas border-b border-border">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        @yield('header')
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 anim-fade-up">
                {!! $slot ?? $__env->yieldContent('content') !!}
            </main>
        </div>

        <!-- Lugar opcional para modales inyectados desde las vistas -->
        @stack('modals')

        <!-- Stack opcional para scripts por-vista -->
        @stack('scripts')

        @if (session('alert') || session('success'))
            @php
                $alert = session('alert') ?? [
                    'type' => 'success',
                    'title' => 'Operación realizada',
                    'text' => session('success')
                ];
            @endphp
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const data = @json($alert);

                    Swal.fire({
                        icon: data.type || 'success',
                        title: data.title || 'Operación realizada',
                        html: data.text || '',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        backdrop: 'rgba(25,25,24,0.4)',
                        customClass: {
                            popup: 'rounded-lg bg-canvas border border-border shadow-card p-6',
                            title: 'text-base font-semibold text-ink',
                            htmlContainer: 'text-sm text-ink-2 mt-2',
                            confirmButton: 'btn-primary'
                        }
                    });
                });
            </script>
        @endif
    </body>
</html>
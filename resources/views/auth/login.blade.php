<x-guest-layout>
    @section('title', 'Acceder | NineBox')

    <!-- Título de la pantalla -->
    <div class="mb-6 anim-fade-up anim-delay-1">
        <h2 class="text-xl font-semibold text-ink">Iniciar sesión</h2>
        <p class="text-sm text-ink-2 mt-1">Ingresa tus credenciales para continuar.</p>
    </div>

    @if(session('error'))
        <div class="mb-4 px-4 py-3 text-sm text-danger bg-red-50 border border-red-100 rounded anim-fade-up">
            {{ session('error') }}
        </div>
    @endif

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Correo -->
        <div class="anim-fade-up anim-delay-2">
            <label for="correo" class="form-label">{{ __('Correo Electrónico') }}</label>
            <input id="correo"
                type="email"
                name="correo"
                value="{{ old('correo') }}"
                class="form-input @error('correo') border-danger @enderror"
                required
                autofocus
                autocomplete="username"
                placeholder="tu@empresa.com">
            <x-input-error :messages="$errors->get('correo')" class="mt-1.5" />
        </div>

        <!-- Contraseña -->
        <div class="anim-fade-up anim-delay-3">
            <label for="password" class="form-label">{{ __('Contraseña') }}</label>
            <input id="password"
                type="password"
                name="password"
                class="form-input @error('password') border-danger @enderror"
                required
                autocomplete="current-password"
                placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Recordarme -->
        <div class="flex items-center justify-between anim-fade-up anim-delay-3">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me"
                    type="checkbox"
                    class="rounded border-border text-primary focus:ring-primary"
                    name="remember">
                <span class="ms-2 text-sm text-ink-2">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-primary hover:text-primary-hover transition-colors">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <!-- Submit -->
        <div class="pt-1 anim-fade-up anim-delay-4">
            <button type="submit" class="btn-primary w-full justify-center flex">
                {{ __('Iniciar sesión') }}
            </button>
        </div>
    </form>
</x-guest-layout>
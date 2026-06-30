<nav x-data="{ open: false }" class="bg-primary text-white h-[56px] flex items-center relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex justify-between items-center h-full">

        <!-- Izquierda: logo + links de nav -->
        @auth
            @php
                $user = Auth::user();
                $nombre =
                    trim(($user->nombre ?? '') . ' ' . ($user->apellido_paterno ?? '')) ?:
                    $user->user_name ?? 'Usuario';
                $esSuperadminNav = $user->esSuperadmin();
                $esDuenoNav = $user->esDueno();
            @endphp
        @else
            @php
                $esSuperadminNav = false;
                $esDuenoNav = false;
            @endphp
        @endauth

        <div class="flex items-center space-x-6">
            <a href="{{ route('ninebox.dashboard') }}" class="font-bold text-white text-lg tracking-tight">
                NineBox
            </a>

            @auth
                {{-- Link a Empresas visible para Superadmin y Dueño --}}
                @if ($esSuperadminNav || $esDuenoNav)
                    <a href="{{ route('admin.empresas.index') }}"
                        class="text-sm font-medium transition-colors {{ request()->routeIs('admin.*') ? 'text-white border-b-2 border-white/30 pb-[1px]' : 'text-white/80 hover:text-white' }}">
                        Empresas
                    </a>
                @endif
            @endauth
        </div>

        <!-- Derecha: desktop user dropdown -->
        @auth
            <div class="hidden md:flex items-center">
                <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                    <button @click="dropdownOpen = !dropdownOpen"
                        class="text-sm font-medium text-white hover:text-white/90 flex items-center gap-1.5 focus:outline-none px-2 py-1 rounded hover:bg-primary/20 transition-colors">
                        <!-- Avatar inicial -->
                        <span
                            class="w-7 h-7 rounded-full bg-white/10 text-white text-xs font-semibold flex items-center justify-center shrink-0">
                            {{ mb_strtoupper(mb_substr($nombre, 0, 1)) }}
                        </span>
                        <span>{{ $nombre }}</span>
                        <svg class="h-3.5 w-3.5 fill-current text-ink-3 transition-transform duration-200"
                            :class="dropdownOpen ? 'rotate-180' : 'rotate-0'" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-52 bg-canvas border border-border rounded-md shadow-card py-1 origin-top-right"
                        style="display: none;">
                        <div class="px-4 py-2 border-b border-border mb-1">
                            <div class="text-xs font-semibold text-ink truncate">{{ $nombre }}</div>
                            <div class="text-[11px] text-ink-3 truncate mt-0.5">{{ $user->correo }}</div>
                        </div>
                        @if ($esSuperadminNav || $esDuenoNav)
                            <a href="{{ route('admin.empresas.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-ink hover:bg-surface transition-colors">
                                <svg class="w-3.5 h-3.5 text-ink-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ __('Empresas') }}
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-ink hover:bg-surface transition-colors">
                            <svg class="w-3.5 h-3.5 text-ink-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('Perfil') }}
                        </a>
                        <div class="border-t border-border mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2 text-left px-4 py-2 text-sm text-ink hover:bg-surface transition-colors">
                                    <svg class="w-3.5 h-3.5 text-ink-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Cerrar sesión') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="flex items-center md:hidden">
                <button @click="open = !open" class="text-ink hover:text-primary focus:outline-none p-1" aria-label="Menu">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endauth
    </div>

    <!-- Mobile Menu -->
    @auth
        <div x-show="open" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute top-[56px] left-0 right-0 bg-canvas border-b border-border shadow-card py-2 md:hidden"
            style="display: none;">
            <div class="px-4 py-2.5 border-b border-border mb-1">
                <div class="text-sm font-semibold text-ink">{{ $nombre }}</div>
                <div class="text-xs text-ink-3 mt-0.5">{{ $user->correo }}</div>
            </div>
            @if ($esSuperadminNav || $esDuenoNav)
                <a href="{{ route('admin.empresas.index') }}" class="block px-4 py-2.5 text-sm text-ink hover:bg-surface">
                    {{ __('Empresas') }}
                </a>
            @endif
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-ink hover:bg-surface">
                {{ __('Perfil') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-border mt-1 pt-1">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-ink hover:bg-surface">
                    {{ __('Cerrar sesión') }}
                </button>
            </form>
        </div>
    @endauth
</nav>

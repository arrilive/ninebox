<nav x-data="{ open: false }"
     class="bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-gray-200 dark:border-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-auto py-2 md:h-20 gap-3 flex-wrap">
      {{-- LEFT: Logo + Fecha --}}
      <div class="flex items-center gap-3 md:gap-6 min-w-0">
        <a href="{{ route('jefe.dashboard') }}" class="shrink-0 inline-flex items-center focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
          <img src="{{ asset('images/BPT-LOGO.png') }}" alt="BPT Logo" class="block h-9 md:h-11 w-auto" />
        </a>

        {{-- Fecha chip con gradiente legible (claro/oscuro) --}}
        <span
          class="inline-flex items-center gap-2 px-5 py-2.5 md:px-6 md:py-3 rounded-2xl
                 text-[1.05rem] md:text-[1.1rem] font-semibold
                 text-black dark:text-white
                 shadow-md border border-indigo-400/50
                 bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600
                 drop-shadow-sm transition-all duration-200 select-none">
          <svg class="h-5 w-5 opacity-90" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v12A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-12A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zM4.5 6A.5.5 0 004 6.5V9h16V6.5a.5.5 0 00-.5-.5h-15z"/>
          </svg>
          {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </span>
      </div>

      {{-- RIGHT: Usuario + Cuenta --}}
      <div class="hidden sm:flex items-center gap-3 md:gap-4 min-w-0">
        {{-- Usuario | Depto (chip + espaciado fino) --}}
        <div class="inline-flex items-center px-4 py-2 md:px-5 md:py-2.5 rounded-2xl
                    bg-white/90 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700
                    text-gray-900 dark:text-gray-100 shadow-sm hover:shadow-md transition-all
                    max-w-[42vw] md:max-w-[48vw] whitespace-nowrap overflow-hidden
                    focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
          <div class="flex items-center gap-[0.6ch] md:gap-[0.9ch] min-w-0">
            <span class="font-semibold truncate" title="{{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}">
              {{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}
            </span>
            <span class="text-gray-400/70 select-none">|</span>
            <span class="truncate" title="{{ Auth::user()->departamento->nombre_departamento ?? 'Sin departamento' }}">
              {{ Auth::user()->departamento->nombre_departamento ?? 'Sin departamento' }}
            </span>
          </div>
        </div>

        {{-- Mi Cuenta --}}
        <x-dropdown align="right" width="56">
          <x-slot name="trigger">
            <button
              class="inline-flex items-center gap-2 rounded-2xl
                     px-4 py-2 text-sm md:px-5 md:py-2.5 md:text-[1.05rem] font-medium
                     text-gray-900 dark:text-gray-100
                     bg-white/90 dark:bg-gray-800/70
                     border border-gray-200 dark:border-gray-700
                     shadow-sm hover:shadow-md transition-all duration-200
                     focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
              <span>Mi Cuenta</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
              </svg>
            </button>
          </x-slot>

          <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">
              {{ __('Perfil') }}
            </x-dropdown-link>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                {{ __('Cerrar Sesión') }}
              </x-dropdown-link>
            </form>
          </x-slot>
        </x-dropdown>
      </div>

      {{-- HAMBURGER (móvil) --}}
      <div class="flex items-center sm:hidden">
        <button @click="open = ! open"
                class="inline-flex items-center justify-center p-2 rounded-lg
                       text-gray-600 dark:text-gray-300
                       hover:bg-gray-100 dark:hover:bg-gray-800
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900
                       transition duration-150 ease-in-out">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {{-- MOBILE MENU --}}
      <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden w-full border-t border-gray-200 dark:border-gray-800">
        <div class="px-4 py-3 flex items-center justify-between gap-3">
          {{-- Fecha (móvil) --}}
          <span
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-semibold 
                   text-black dark:text-white
                   shadow ring-1 ring-black/5
                   bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600">
            <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="currentColor">
              <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v12A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-12A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zM4.5 6A.5.5 0 004 6.5V9h16V6.5a.5.5 0 00-.5-.5h-15z"/>
            </svg>
            {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
          </span>

          {{-- Usuario compacto --}}
          <div class="inline-flex items-center rounded-xl
                      px-3 py-1.5 text-sm
                      bg-white/90 dark:bg-gray-800/70
                      border border-gray-200 dark:border-gray-700
                      text-gray-900 dark:text-gray-100 whitespace-nowrap max-w-[55vw] overflow-hidden">
            <span class="font-semibold truncate">
              {{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}
            </span>
          </div>
        </div>

        <div class="pt-3 pb-4 border-t border-gray-200 dark:border-gray-800">
          <div class="px-4 space-y-1 text-sm">
            <div class="text-gray-700 dark:text-gray-300">
              {{ Auth::user()->departamento->nombre_departamento ?? 'Sin departamento' }}
            </div>
            <div class="text-gray-500 dark:text-gray-400">
              {{ Auth::user()->correo }}
            </div>
          </div>

          <div class="mt-3 space-y-1">
            <x-responsive-nav-link :href="route('profile.edit')">
              {{ __('Perfil') }}
            </x-responsive-nav-link>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                {{ __('Cerrar Sesión') }}
              </x-responsive-nav-link>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
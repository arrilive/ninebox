<nav x-data="{ open: false }"
     data-sitenav
     class="relative z-50 bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-gray-200 dark:border-gray-800">

  {{-- ===== Estilos ===== --}}
  <style>
    :root{
      --brand-indigo:#4338ca; --brand-purple:#6d28d9;
      --anim-fast:.12s;
    }
    [x-cloak]{display:none!important;}

    [data-sitenav] .pill{
      display:inline-flex; align-items:center; gap:.6rem;
      padding:.55rem 1rem; border-radius:9999px; font-weight:700;
      background:rgba(255,255,255,.90); color:#0f172a;
      border:1px solid rgba(15,23,42,.10);
      box-shadow:0 6px 14px rgba(2,6,23,.08);
      white-space:nowrap;
    }

    [data-sitenav] .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
      font-weight:700; line-height:1; border-radius:9999px;
      padding:.55rem 1rem; border:1px solid transparent; cursor:pointer;
      transition: transform var(--anim-fast) ease, box-shadow var(--anim-fast) ease;
      box-shadow:0 6px 14px rgba(2,6,23,.08);
    }
    [data-sitenav] .btn-ghost{
      background:rgba(15,23,42,.04); border:1px solid rgba(15,23,42,.10); color:#0f172a;
    }

    [data-sitenav] .dropdown-panel{
      position:absolute; min-width:18rem; z-index:60; border-radius:1rem; overflow:hidden;
      background:rgba(255,255,255,.95);
      border:1px solid rgba(15,23,42,.08);
      box-shadow:0 24px 60px rgba(2,6,23,.25);
      backdrop-filter:blur(10px);
      color:#0f172a;
    }

    [data-sitenav] .menu-item{
      display:flex; align-items:center; gap:.6rem; width:100%;
      padding:.7rem 1rem; font-weight:600; border-radius:.75rem;
      background:transparent; cursor:pointer; text-decoration:none;
      transition: background .12s ease, color .12s ease;
      color:#0f172a;
    }
    [data-sitenav] .menu-item:hover{ background:rgba(99,102,241,.10); }

    [data-sitenav] .menu-item svg{
      width:1rem; height:1rem;
      flex:0 0 auto;
      fill:currentColor;
      opacity:.95;
    }

    @media (prefers-color-scheme: dark){
      [data-sitenav] .pill{
        background:rgba(31,41,55,.70); color:#f8fafc; border-color:rgba(255,255,255,.12);
      }
      [data-sitenav] .btn-ghost{
        background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.10); color:#f8fafc;
      }
      [data-sitenav] .dropdown-panel{
        background:rgba(17,24,39,.92); color:#ffffff;
      }
      [data-sitenav] .menu-item{ color:#ffffff; }
      [data-sitenav] .menu-item:hover{ background:rgba(99,102,241,.18); }
    }
  </style>

  @php
    $user = Auth::user();
    $nombre = trim(($user->nombre ?? '').' '.($user->apellido_paterno ?? '').' '.($user->apellido_materno ?? '')) ?: ($user->name ?? 'Usuario');
    $departamento = $user->departamento->nombre_departamento ?? 'Sin departamento';
  @endphp

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- ========= DESKTOP ≥ md ========= --}}
    <div class="hidden md:flex items-center justify-between h-20">
      <div class="flex items-center gap-6">
        <a href="{{ route('ninebox.dashboard') }}" class="shrink-0">
          <img src="{{ asset('images/BPT-LOGO.png') }}" alt="BPT Logo" class="block h-11 w-auto" />
        </a>

        <span class="pill text-[1.1rem] select-none">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v12A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-12A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zM4.5 6A.5.5 0 004 6.5V9h16V6.5a.5.5 0 00-.5-.5h-15z"/>
          </svg>
          {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </span>
      </div>

      <div class="flex items-center gap-3">
        <div x-data="{accOpen:false}" class="relative">
          <button
            @click="accOpen = !accOpen"
            @keydown.escape.window="accOpen=false"
            @click.outside="accOpen=false"
            class="btn btn-ghost px-4 py-2 text-[1.05rem]">
            <span>Mi Cuenta</span>
            <svg class="h-5 w-5 transition-transform duration-200"
                 :class="accOpen ? 'rotate-180' : 'rotate-0'" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                    clip-rule="evenodd"/>
            </svg>
          </button>

          <div x-cloak x-show="accOpen"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 translate-y-1"
               x-transition:enter-end="opacity-100 translate-y-0"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 translate-y-0"
               x-transition:leave-end="opacity-0 translate-y-1"
               class="dropdown-panel right-0 mt-2 w-[22rem]" role="menu">

            <div class="p-4">
              <div class="min-w-0">
                <div class="text-lg font-bold truncate">{{ $nombre }}</div>
                <div class="mt-1 text-base font-semibold opacity-90 truncate">{{ $departamento }}</div>
              </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700"></div>

            <div class="p-1">
              <a href="{{ route('profile.edit') }}" class="menu-item">
                <svg viewBox="0 0 24 24">
                  <path d="M12 12a5 5 0 100-10 5 5 0 000 10zM3 20.25a9 9 0 1118 0V21H3v-.75z"/>
                </svg>
                Perfil
              </a>

              @php $logoutUrl = route('logout'); @endphp
              <form id="logout-form-desktop" method="POST" action="{{ $logoutUrl }}" class="hidden">@csrf</form>
              <button type="button" class="menu-item"
                      @click.prevent="accOpen = false; $nextTick(() => { document.getElementById('logout-form-desktop').submit(); });">
                <svg viewBox="0 0 24 24">
                  <path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3h-8a2 2 0 00-2 2v3h2V5h8v14h-8v-3h-2v3a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2z"/>
                </svg>
                Cerrar sesión
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ========= MOBILE ========= --}}
    <div class="flex md:hidden items-center justify-between py-2">
      <div class="flex items-center gap-3">
        <a href="{{ route('ninebox.dashboard') }}" class="shrink-0">
          <img src="{{ asset('images/BPT-LOGO.png') }}" alt="BPT Logo" class="block h-9 w-auto" />
        </a>
        <span class="pill text-[0.95rem]">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v12A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-12A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zM4.5 6A.5.5 0 004 6.5V9h16V6.5a.5.5 0 00-.5-.5h-15z"/>
          </svg>
          {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </span>
      </div>

      <button @click="open = ! open" class="btn btn-ghost p-2 rounded-lg" aria-label="Abrir menú">
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
          <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
          <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden w-full border-t border-gray-200 dark:border-gray-800 mobile-wrap">
      <div class="px-4 py-3">
        <div class="min-w-0">
          <div class="text-lg font-bold truncate">{{ $nombre }}</div>
          <div class="mt-1 text-base font-semibold opacity-90 truncate">{{ $departamento }}</div>
        </div>
      </div>

      <div class="pt-3 pb-4 border-t border-gray-200 dark:border-gray-700">
        <div class="space-y-1 px-2">
          <a href="{{ route('profile.edit') }}" class="menu-item">
            <svg viewBox="0 0 24 24">
              <path d="M12 12a5 5 0 100-10 5 5 0 000 10zM3 20.25a9 9 0 1118 0V21H3v-.75z"/>
            </svg>
            Perfil
          </a>
          <form id="logout-form-mobile" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
          <button type="button" class="menu-item w-full text-left"
                  @click.prevent="open = false; $nextTick(() => { document.getElementById('logout-form-mobile').submit(); });">
            <svg viewBox="0 0 24 24">
              <path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3h-8a2 2 0 00-2 2v3h2V5h8v14h-8v-3h-2v3a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2z"/>
            </svg>
            Cerrar sesión
          </button>
        </div>
      </div>
    </div>

  </div>
</nav>
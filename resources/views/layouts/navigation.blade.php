<nav x-data="{ open: false }"
     class="relative z-50 bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-gray-200 dark:border-gray-800">
  <style>
    [x-cloak]{display:none!important;}

    /* ===== Pills / botones unificados ===== */
    .pill{
      display:inline-flex; align-items:center; gap:.6rem;
      padding:.55rem 1rem; border-radius:9999px; font-weight:700;
      background:rgba(255,255,255,.90); color:#0f172a;
      border:1px solid rgba(15,23,42,.10);
      box-shadow:0 6px 14px rgba(2,6,23,.08);
      backdrop-filter:blur(6px);
      white-space:nowrap;
    }
    .pill-muted{ font-weight:600; opacity:.95; }
    .pill-icon{ width:1.05rem; height:1.05rem; opacity:.9; }

    .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
      font-weight:700; line-height:1; border-radius:9999px;
      padding:.55rem 1rem; border:1px solid transparent; cursor:pointer;
      transition: transform .12s ease, box-shadow .12s ease, filter .12s ease, opacity .12s ease, background .12s ease;
      box-shadow:0 6px 14px rgba(2,6,23,.08);
      -webkit-tap-highlight-color: transparent;
    }
    .btn:hover{ transform:translateY(-1px); }
    .btn-ghost{
      background:rgba(15,23,42,.04); border:1px solid rgba(15,23,42,.10); color:#0f172a;
    }

    /* ===== Dropdown ===== */
    .dropdown-panel{
      position:absolute; min-width:14rem; z-index:60; border-radius:1rem; overflow:hidden;
      background:rgba(255,255,255,.95);
      border:1px solid rgba(15,23,42,.08);
      box-shadow:0 24px 60px rgba(2,6,23,.25), inset 0 1px 0 rgba(255,255,255,.6);
      backdrop-filter:blur(10px);
      color:#0f172a; /* texto en claro */
    }
    .menu-item{
      display:flex; align-items:center; gap:.6rem; width:100%;
      padding:.7rem 1rem; font-weight:600; border-radius:.75rem;
      background:transparent; cursor:pointer; text-decoration:none;
      transition: background .12s ease, color .12s ease;
      color:inherit;
    }
    .menu-item svg{ width:1rem; height:1rem; fill:currentColor; opacity:.9; }
    .menu-item:hover{ background:rgba(99,102,241,.10); }
    .menu-sep{ height:1px; width:100%; background:rgba(15,23,42,.08); }

    /* ===== Dark mode ===== */
    @media (prefers-color-scheme: dark){
      .pill{
        background:rgba(31,41,55,.70);
        color:#f8fafc;
        border-color:rgba(255,255,255,.12);
      }
      .pill-muted{ color:#dbeafe; opacity:.86; }
      .btn-ghost{
        background:rgba(255,255,255,.06);
        border-color:rgba(255,255,255,.10);
        color:#f8fafc;
      }
      .dropdown-panel{
        background:rgba(17,24,39,.92);
        border-color:rgba(255,255,255,.06);
        box-shadow:0 24px 60px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.04);
        color:#ffffff;
      }
      .menu-item,
      .menu-item *{ color:#ffffff; }
      .menu-item svg{ fill:currentColor; }
      .menu-item:hover{ background:rgba(99,102,241,.18); }
      .menu-sep{ background:rgba(255,255,255,.08); }
      .mobile-wrap{ color:#ffffff; }
      .mobile-wrap .pill-muted{ color:#dbeafe; opacity:.86; }
    }
  </style>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- ========= DESKTOP ≥ md ========= --}}
    <div class="hidden md:flex items-center justify-between h-20">
      {{-- LEFT --}}
      <div class="flex items-center gap-6">
        <a href="{{ route('ninebox.dashboard') }}" class="shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
          <img src="{{ asset('images/BPT-LOGO.png') }}" alt="BPT Logo" class="block h-11 w-auto" />
        </a>

        <span class="pill text-[1.1rem] select-none">
          <svg class="pill-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v12A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-12A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zM4.5 6A.5.5 0 004 6.5V9h16V6.5a.5.5 0 00-.5-.5h-15z"/>
          </svg>
          {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </span>
      </div>

      {{-- RIGHT (desktop) --}}
      <div class="flex items-center gap-3 min-w-0">
        <span class="pill max-w-[24rem] overflow-hidden">
          <span class="truncate">{{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}</span>
        </span>
        <span class="pill pill-muted max-w-[20rem] overflow-hidden">
          <span class="truncate">{{ Auth::user()->departamento->nombre_departamento ?? 'Sin departamento' }}</span>
        </span>

        {{-- Dropdown Mi Cuenta --}}
        <div x-data="{accOpen:false}" class="relative">
          <button
            @click="accOpen = !accOpen" @keydown.escape.window="accOpen=false" @click.outside="accOpen=false"
            :aria-expanded="accOpen" aria-haspopup="menu"
            class="btn btn-ghost px-5 py-2.5 text-[1.05rem]">
            <span>Mi Cuenta</span>
            <svg class="h-5 w-5 transition-transform duration-200" :class="accOpen ? 'rotate-180' : 'rotate-0'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
            </svg>
          </button>

          <div x-cloak x-show="accOpen"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 translate-y-1"
               x-transition:enter-end="opacity-100 translate-y-0"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 translate-y-0"
               x-transition:leave-end="opacity-0 translate-y-1"
               class="dropdown-panel right-0 mt-2 w-56"
               role="menu" aria-label="Menú de cuenta">
            <div class="p-1">
              <a href="{{ route('profile.edit') }}" class="menu-item" role="menuitem">
                <svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM3 20.25a9 9 0 1118 0V21H3v-.75z"/></svg>
                Perfil
              </a>
              <div class="menu-sep my-1"></div>

              {{-- === Logout robusto (desktop): botón → form oculto === --}}
              @php $logoutUrl = route('logout'); @endphp
              <form id="logout-form-desktop" method="POST" action="{{ $logoutUrl }}" class="hidden">
                @csrf
              </form>
              <button type="button" class="menu-item" role="menuitem"
                @click.prevent="accOpen = false; $nextTick(() => { document.getElementById('logout-form-desktop').submit(); });">
                <svg viewBox="0 0 24 24"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3h-8a2 2 0 00-2 2v3h2V5h8v14h-8v-3h-2v3a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
                Cerrar sesión
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ========= MOBILE < md ========= --}}
    <div class="flex md:hidden items-center justify-between py-2">
      <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('ninebox.dashboard') }}" class="shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
          <img src="{{ asset('images/BPT-LOGO.png') }}" alt="BPT Logo" class="block h-9 w-auto" />
        </a>
        <span class="pill text-[0.95rem] select-none">
          <svg class="pill-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v12A2.5 2.5 0 0119.5 21h-15A2.5 2.5 0 012 18.5v-12A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zM4.5 6A.5.5 0 004 6.5V9h16V6.5a.5.5 0 00-.5-.5h-15z"/>
          </svg>
          {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </span>
      </div>

      <button @click="open = ! open" class="btn btn-ghost p-2 rounded-lg">
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
          <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
          <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden w-full border-t border-gray-200 dark:border-gray-800 mobile-wrap">
      <div class="px-4 py-3 grid gap-2">
        <span class="pill max-w-full overflow-hidden">
          <svg class="pill-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/></svg>
          <span class="truncate">{{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}</span>
        </span>
        <span class="pill pill-muted max-w-full overflow-hidden">
          <svg class="pill-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zM4 11h16v2H4zM4 16h10v2H4z"/></svg>
          <span class="truncate">{{ Auth::user()->departamento->nombre_departamento ?? 'Sin departamento' }}</span>
        </span>
        <span class="pill pill-muted max-w-full overflow-hidden">
          <svg class="pill-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M2 6a2 2 0 012-2h16a2 2 0 012 2v.4l-10 6-10-6V6zm0 3.2l9.6 5.76a2 2 0 002.08 0L24 9.2V18a2 2 0 01-2 2H4a2 2 0 01-2-2V9.2z"/></svg>
          <span class="truncate">{{ Auth::user()->correo }}</span>
        </span>
      </div>

      <div class="pt-3 pb-4 border-t border-gray-200 dark:border-gray-800">
        <div class="mt-1 space-y-1 px-2">
          <a href="{{ route('profile.edit') }}" class="menu-item" role="menuitem">Perfil</a>

          {{-- === Logout robusto (mobile): botón → form oculto === --}}
          @php $logoutUrl = route('logout'); @endphp
          <form id="logout-form-mobile" method="POST" action="{{ $logoutUrl }}" class="hidden">
            @csrf
          </form>
          <button type="button" class="menu-item w-full text-left"
                  @click.prevent="open = false; $nextTick(() => { document.getElementById('logout-form-mobile').submit(); });">
            Cerrar sesión
          </button>
        </div>
      </div>
    </div>
  </div>
</nav>
<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar resumen (look & feel como el modal) --}}
        @php
          $total = max(1, count($empleados));
          $pendientes = max(0, $total - $empleadosEvaluados);
          $pct = min(100, round(($empleadosEvaluados / $total) * 100));
        @endphp

        <aside
          class="lg:w-80 flex-shrink-0 rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40
                 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg sticky top-4 overflow-hidden"
        >
          {{-- Header a juego con el modal (sin icono) --}}
          <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative">
              <h3 id="sidebar-title" class="text-black dark:text-white font-extrabold text-3xl tracking-tight">Resumen</h3>
              <p class="text-black dark:text-white text-xl leading-snug">Estado de la evaluación</p>
            </div>
          </div>

          {{-- Cuerpo --}}
          <div class="p-7 space-y-7">
            {{-- KPIs (bloque único estilo modal-glass) --}}
            <section aria-label="Indicadores clave">
              <div class="kpi-glass">
                <div class="kpi-brace kpi-brace--left" aria-hidden="true"></div>
                <div class="kpi-brace kpi-brace--right" aria-hidden="true"></div>

                <div class="kpi-row">
                  <span class="kpi-label text-gray-900 dark:text-gray-100">Total Empleados</span>
                  <span id="total-empleados" class="kpi-value">{{ $total }}</span>
                </div>

                <div class="kpi-row">
                  <span class="kpi-label text-emerald-600 dark:text-emerald-400">Evaluados</span>
                  <span id="empleados-evaluados" class="kpi-value">{{ $empleadosEvaluados }}</span>
                </div>

                <div class="kpi-row">
                  <span class="kpi-label text-rose-600 dark:text-red-400">Por Evaluar</span>
                  <span id="empleados-pendientes" class="kpi-value">{{ $pendientes }}</span>
                </div>
              </div>
            </section>

            {{-- Barra de progreso (SSR + JS) - tu lógica intacta --}}
            @php
              $total = max(1, count($empleados));
              $pct = min(100, round(($empleadosEvaluados / $total) * 100));
            @endphp

            <section class="mb-4">
              {{-- margen inferior para separar la barra del botón --}}
              <div class="flex items-center justify-between text-xl text-gray-900 dark:text-gray-300 mb-2">
                <span class="font-semibold tracking-tight">Avance</span>
              </div>

              <div class="w-full h-6 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden">
                <div
                  id="avance-bar"
                  class="h-6 bg-blue-600 text-center rounded-full text-white transition-[width] duration-300 ease-in-out"
                  style="width: {{ $pct }}%;"
                >
                  {{ $pct }}%
                </div>
              </div>
            </section>

            {{-- Botón Guardar Evaluación  --}}
            <div class="mt-5">
              <button
                id="btn-guardar-evaluacion"
                type="button"
                onclick="guardarEvaluacion()"
                class="w-full text-base tracking-wide bg-gradient-to-r from-indigo-600 to-purple-600
                       hover:from-indigo-700 hover:to-purple-700 text-white py-2 rounded-lg font-semibold
                       transition-all duration-200 transform hover:scale-[1.02] active:scale-95 shadow-md
                       disabled:cursor-not-allowed disabled:opacity-75"
                {{ $pendientes > 0 ? 'disabled' : '' }}
                aria-disabled="{{ $pendientes > 0 ? 'true' : 'false' }}"
              >
                Guardar Evaluación
              </button>

              <p class="text-[13px] text-center mt-3 text-gray-700 dark:text-gray-400">
                Evalúa a todos los empleados para activar
              </p>
            </div>
          </div>
        </aside>

        {{-- Matriz 9-Box --}}
        <div class="flex-1">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="relative w-full mx-auto" style="max-width: 96%;">
              <img
                src="{{ asset('images/9box-demo.png') }}"
                class="w-full h-auto rounded-xl shadow-lg select-none"
                id="ninebox-img"
                alt="9-Box"
                draggable="false"
                style="pointer-events: none;"
              >

              {{-- Botones sobre cada cuadrante --}}
              <button type="button" class="cuadrante-btn" style="position:absolute; left: 17.5%; top:18%; width:23.5%; height:25%;" data-cuadrante="1" title="Diamante en bruto" aria-label="Ver empleados en Diamante en bruto">
                @if(($asignacionesActuales[1] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[1] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left:42.5%; top:18%; width:23.5%; height:25%;" data-cuadrante="2" title="Estrella en desarrollo" aria-label="Ver empleados en Estrella en desarrollo">
                @if(($asignacionesActuales[2] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[2] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left:67.5%; top:18%; width:23%; height:25%;" data-cuadrante="3" title="Estrella" aria-label="Ver empleados en Estrella">
                @if(($asignacionesActuales[3] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[3] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left: 17.5%; top:45%; width:23.5%; height:25%;" data-cuadrante="4" title="Mal empleado" aria-label="Ver empleados en Mal empleado">
                @if(($asignacionesActuales[4] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[4] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left:42.5%; top:45%; width:23.5%; height:25%;" data-cuadrante="5" title="Personal sólido" aria-label="Ver empleados en Personal sólido">
                @if(($asignacionesActuales[5] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[5] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left:67.5%; top:45%; width:23%; height:25%;" data-cuadrante="6" title="Elemento importante" aria-label="Ver empleados en Elemento importante">
                @if(($asignacionesActuales[6] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[6] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left: 17.5%; top:72%; width:23.5%; height:25%;" data-cuadrante="7" title="Inaceptable" aria-label="Ver empleados en Inaceptable">
                @if(($asignacionesActuales[7] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[7] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left:42.5%; top:72%; width:23.5%; height:25%;" data-cuadrante="8" title="Aceptable" aria-label="Ver empleados en Aceptable">
                @if(($asignacionesActuales[8] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[8] }}</div>
                @endif
              </button>
              <button type="button" class="cuadrante-btn" style="position:absolute; left:67.5%; top:72%; width:23%; height:25%;" data-cuadrante="9" title="Personal clave" aria-label="Ver empleados en Personal clave">
                @if(($asignacionesActuales[9] ?? 0) > 0)
                  <div class="cuadrante-badge" style="position:absolute; left:90%; top:-10%;">{{ $asignacionesActuales[9] }}</div>
                @endif
              </button>
            </div>
          </div>
        </div>
        {{-- /flex-row gap-8 --}}
      </div>
    </div>

    <!-- Modal con glassmorphism -->
    <div id="modal-empleados" class="fixed inset-0 z-50 hidden"
         role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-desc">
      <div id="modal-backdrop" class="absolute inset-0"></div>
      <div class="relative h-full flex items-center justify-center p-4">
        <div id="modal-container"
             class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg rounded-2xl shadow-2xl w-full max-w-2xl
                    max-h-[80vh] overflow-hidden border border-white/10 dark:border-gray-700/40 transform scale-90 opacity-0
                    transition-all duration-300" tabindex="-1">
          <div class="bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 p-6 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex justify-between items-start">
              <div>
                <h3 id="modal-title" class="text-4xl md:text-5xl font-extrabold text-white mb-2 drop-shadow-lg"></h3>
                <p id="modal-desc" class="text-lg md:text-xl text-white/90"></p>
              </div>
              <button id="btn-cerrar-modal" class="text-white hover:bg-white/20 p-2 rounded-full transition-colors" aria-label="Cerrar modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 140px);">
            {{-- Asignados --}}
            <div class="mb-8">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-2 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <h4 class="text-xl font-bold text-gray-900 dark:text-white">Asignados</h4>
                <span id="count-asignados" class="ml-auto bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">0</span>
              </div>
              <div id="empty-asignados" class="text-center py-8 text-gray-400 dark:text-gray-500 hidden">
                <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                No hay empleados asignados
              </div>
              <ul id="lista-asignados" class="space-y-3"></ul>
            </div>

            {{-- Disponibles --}}
            <div>
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-2 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                </div>
                <h4 class="text-xl font-bold text-gray-900 dark:text-white">Disponibles</h4>
                <span id="count-disponibles" class="ml-auto bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">0</span>
              </div>
              <div id="empty-disponibles" class="text-center py-8 text-gray-400 dark:text-gray-500 hidden">
                <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Todos los empleados asignados
              </div>
              <ul id="lista-disponibles" class="space-y-3"></ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- CSS --}}
  <style>
    :root{
      --brand-indigo:#4338ca;
      --brand-purple:#6d28d9;
      --accent-cyan:#0891b2;
      --danger-red:#dc2626;
      --success-green:#059669;
      --modal-bg-light:rgba(255,255,255,0.96);
      --modal-bg-dark:rgba(8,10,20,0.92);
    }

    /* ===== KPIs estilo modal-glass ===== */
    .kpi-glass{
      position: relative;
      padding: 1.1rem 1.1rem;
      border-radius: .95rem;
      background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.04));
      border: 1px solid rgba(255,255,255,0.06);
      box-shadow: 0 10px 28px rgba(2,6,23,0.20) inset, 0 8px 22px rgba(2,6,23,0.12);
    }
    @media (prefers-color-scheme: light){
      .kpi-glass{
        background: linear-gradient(180deg, rgba(15,23,42,0.06), rgba(15,23,42,0.04));
        border: 1px solid rgba(15,23,42,0.06);
        box-shadow: 0 10px 28px rgba(2,6,23,0.06) inset, 0 8px 22px rgba(2,6,23,0.08);
      }
    }
    .kpi-row{
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      gap: .85rem;
      padding: 1rem 1.1rem;
      border-radius: .8rem;
      background: rgba(2,6,23,0.08);
    }
    .kpi-row + .kpi-row{ margin-top: .75rem; }
    @media (prefers-color-scheme: dark){
      .kpi-row{ background: rgba(2,6,23,0.18); }
    }
    .kpi-label{
      font-weight: 700;
      font-size: 1rem;
      letter-spacing: .2px;
    }
    .kpi-value{
      font-weight: 800;
      font-size: 2.15rem;
      line-height: 1;
      color: #0b1020;
      letter-spacing: .2px;
    }
    @media (prefers-color-scheme: dark){
      .kpi-value{ color: #e6eef8; text-shadow: 0 1px 0 rgba(0,0,0,.15); }
    }
    .kpi-brace{
      position: absolute;
      top: .6rem;
      bottom: .6rem;
      width: .6rem;
      border-radius: .6rem;
      background:
        radial-gradient(12px 8px at 50% 0%, rgba(255,255,255,.20), transparent 60%),
        radial-gradient(12px 8px at 50% 100%, rgba(255,255,255,.20), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.04));
      opacity: .7;
      pointer-events: none;
    }
    .kpi-brace--left{ left: .45rem; }
    .kpi-brace--right{ right: .45rem; transform: scaleX(-1); }

    /* =========================
       Cuadrantes (botones)
       ========================= */
    .cuadrante-btn{
      background: rgba(79,70,229,0.08);
      border: 2px solid transparent;
      border-radius: 12px;
      cursor: pointer;
      transition: transform .18s cubic-bezier(.2,.9,.3,1), box-shadow .18s ease, border-color .12s ease;
      z-index: 40; outline: none;
    }
    /* --- Badge con mismo look del botón + animación --- */
    .cuadrante-badge{
      position:absolute; left:90%; top:-10%;
      display:inline-flex; align-items:center; justify-content:center;
      padding:.25rem .75rem; border-radius:9999px;
      font-weight:700; font-size:.75rem; line-height:1;
      color:#fff;
      background-image: linear-gradient(90deg,#4f46e5 0%, #7c3aed 100%);
      border:1px solid rgba(0,0,0,.05);
      box-shadow:0 4px 14px rgba(2,6,23,.12);
      user-select:none; pointer-events:none;
      transform: translateZ(0);
    }
    @media (prefers-color-scheme: dark){
      .cuadrante-badge{ border-color: rgba(255,255,255,.10); }
    }
    @keyframes badge-bump{
      0%{ transform: scale(1); }
      25%{ transform: scale(1.12); }
      100%{ transform: scale(1); }
    }
    .cuadrante-badge.bump{ animation: badge-bump 220ms ease-out; }

    .cuadrante-btn:hover,
    .cuadrante-btn:focus{
      border-color: rgba(79,70,229,0.95);
      background: linear-gradient(180deg, rgba(79,70,229,0.12), rgba(79,70,229,0.08));
      transform: scale(1.04);
      box-shadow: 0 8px 20px rgba(79,70,229,0.12);
    }

    /* Modal wrapper */
    #modal-empleados{ position:fixed; inset:0; z-index:50; display:none; align-items:center; justify-content:center; padding:1rem; }
    #modal-empleados:not(.hidden){ display:flex; }

    /* Backdrop con transición */
    #modal-backdrop{
      position:absolute; inset:0; z-index:10;
      background: linear-gradient(180deg, rgba(10,12,20,0.72), rgba(67,56,202,0.18));
      backdrop-filter: blur(8px) saturate(120%); -webkit-backdrop-filter: blur(8px) saturate(120%);
      pointer-events:auto;
      opacity:0; transition: opacity .22s ease;
    }
    #modal-empleados:not(.hidden).show #modal-backdrop{ opacity:1; }

    /* Contenedor modal con transición suave */
    #modal-container{
      position:relative; z-index:20; width:100%; max-width:44rem; max-height:80vh; overflow:hidden; border-radius:1rem;
      background-color:var(--modal-bg-light); color:#0f172a; border:1px solid rgba(15,23,42,0.06);
      box-shadow:0 18px 48px rgba(2,6,23,0.18);
      transform: translateY(8px) scale(.98);
      opacity:0;
      transition: transform .22s cubic-bezier(.2,.9,.3,1), opacity .18s ease; outline:none;
    }
    @media (prefers-color-scheme: dark){
      #modal-container{ background-color:var(--modal-bg-dark); color:#e6eef8; border:1px solid rgba(255,255,255,0.04); box-shadow:0 20px 60px rgba(2,6,23,0.5); }
    }
    #modal-container>.bg-gradient-to-r, #modal-container .bg-gradient-to-r{
      background: linear-gradient(90deg, var(--brand-indigo) 0%, var(--brand-purple) 55%, var(--brand-indigo) 100%);
    }
    #modal-empleados:not(.hidden).show #modal-container{
      transform: translateY(0) scale(1);
      opacity:1;
    }

    /* Botón cerrar */
    #btn-cerrar-modal{
      width:40px; height:40px; display:inline-grid; place-items:center; border-radius:9999px;
      background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14);
      backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
      transition: transform .12s ease, background .12s ease, box-shadow .12s ease;
      cursor:pointer; box-shadow:0 4px 14px rgba(2,6,23,0.12);
    }
    #btn-cerrar-modal:hover{ transform:translateY(-2px); background:rgba(255,255,255,0.18); box-shadow:0 10px 30px rgba(2,6,23,0.16); }
    #btn-cerrar-modal svg{ width:18px; height:18px; color:white; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.35)); }
    @media (prefers-color-scheme: dark){
      #btn-cerrar-modal{ background:rgba(0,0,0,0.22); border:1px solid rgba(255,255,255,0.06); }
      #btn-cerrar-modal:hover{ background:rgba(255,255,255,0.06); }
    }

    /* Listas del modal */
    .lista-empleado{
      padding:.78rem; border-radius:.85rem; display:flex; align-items:center; justify-content:space-between;
      background: linear-gradient(180deg, rgba(255,255,255,0.64), rgba(255,255,255,0.50));
      transition: transform .12s ease, box-shadow .12s ease; border-left:4px solid transparent; gap:1rem;
    }
    .lista-empleado:hover{ transform:translateY(-4px); box-shadow:0 12px 30px rgba(2,6,23,0.06); }
    .lista-empleado.border-green{ border-left-color: rgba(5,150,105,0.95); }
    .lista-empleado.border-blue{ border-left-color: rgba(37,99,235,0.95); }
    @media (prefers-color-scheme: dark){
      .lista-empleado{ background: linear-gradient(180deg, rgba(15,23,42,0.14), rgba(15,23,42,0.06)); }
    }
    .avatar-icon{
      width:44px; height:44px; border-radius:9999px; display:inline-flex; align-items:center; justify-content:center;
      background: linear-gradient(180deg, rgba(15,23,42,0.06), rgba(67,56,202,0.06));
      box-shadow:0 4px 12px rgba(2,6,23,0.06); color:#0f172a;
    }
    .nombre-empleado{ font-weight:600; color:inherit; }

    /* Utilidades */
    @supports not ((-webkit-backdrop-filter: blur(8px)) or (backdrop-filter: blur(8px))){
      #modal-backdrop{ background: rgba(6,8,15,0.78); }
    }
    #ninebox-img{ user-select:none; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; }
    .cuadrante-btn:focus-visible,
    .btn-accion:focus-visible,
    #btn-cerrar-modal:focus-visible{ outline:3px solid rgba(79,70,229,0.18); outline-offset:3px; }

    /* Botón acción base (por si lo reutilizas) */
    .btn-accion{
      padding:.75rem 1.15rem; font-weight:700; font-size:1rem; border-radius:.9rem;
      box-shadow:0 8px 18px rgba(2,6,23,0.10);
      transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease, filter .12s ease;
      border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    }

    /* Botón primario (no tocado) */
    #btn-guardar-evaluacion{
      color:#fff;
      background-image: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
      border:1px solid rgba(0,0,0,0.05);
    }
    #btn-guardar-evaluacion:hover{ filter: brightness(1.05); }
    #btn-guardar-evaluacion[disabled]{
      background-image: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
      color:#fff; opacity:.78; filter: saturate(.9) grayscale(.06);
    }
    @media (prefers-color-scheme: dark){
      #btn-guardar-evaluacion{ border-color: rgba(255,255,255,0.10); }
      #btn-guardar-evaluacion[disabled]{ opacity:.9; filter:none; }
    }

    /* Sticky más agresivo del sidebar en pantallas largas */
    aside.sticky{
      max-height: calc(100vh - 1rem);
      overflow: hidden;
      display:flex; flex-direction:column;
    }
    aside.sticky > .p-7{ overflow:auto; }
    @media (min-width:1024px){ aside.sticky{ top:1rem; } }
    @media (min-width:1280px){ aside.sticky{ top:2rem; } }
  </style>

  {{-- JavaScript --}}
  <script>
    // TOKEN CSRF INYECTADO DESDE LARAVEL
    const CSRF_TOKEN = '{{ csrf_token() }}';

    console.log('asigna', @json($asignacionesActuales));

    const cuadrantesData = {
      1: { title: "Diamante en bruto", subtitle: "Alto Potencial - Bajo Desempeño", desc: "Gran potencial, su desempeño no ha sido exigido por lo que requiere desarrollarlo" },
      2: { title: "Estrella en desarrollo", subtitle: "Alto Potencial - Medio Desempeño", desc: "Potencial y desempeño en crecimiento, con la dirección adecuada puede convertirse en una estrella" },
      3: { title: "Estrella", subtitle: "Alto Potencial - Alto Desempeño", desc: "Empleados con alto desempeño y gran potencial, clave para la organización" },
      4: { title: "Mal empleado", subtitle: "Medio Potencial - Bajo Desempeño", desc: "Desempeño insuficiente, requiere mejora y desarrollo" },
      5: { title: "Personal sólido", subtitle: "Medio Potencial - Medio Desempeño", desc: "Desempeño aceptable, pero con potencial limitado para crecer" },
      6: { title: "Elemento importante", subtitle: "Medio Potencial - Alto Desempeño", desc: "Buena contribución actual, pero con un potencial de crecimiento incierto" },
      7: { title: "Inaceptable", subtitle: "Bajo Potencial - Bajo Desempeño", desc: "Desempeño inaceptable, requiere acción inmediata" },
      8: { title: "Aceptable", subtitle: "Bajo Potencial - Medio Desempeño", desc: "Desempeño básico, cumple con los mínimos requerimientos" },
      9: { title: "Personal clave", subtitle: "Bajo Potencial - Alto Desempeño", desc: "Empleados confiables con buen desempeño, pero con poco potencial de desarrollo" }
    };

    let cuadranteActual = null;
    let lastTriggerBtn = null;

    function getCuadranteButton(id){
      return document.querySelector(`.cuadrante-btn[data-cuadrante="${id}"]`);
    }

    // === Badge animado y consistente con el botón ===
    function setBadgeCount(cuadranteId, count){
      const btn = getCuadranteButton(cuadranteId);
      if(!btn) return;

      let badge = btn.querySelector('.cuadrante-badge');

      if(count > 0){
        if(!badge){
          badge = document.createElement('div');
          badge.className = 'cuadrante-badge';
          // posición ya definida por CSS; si quieres exacto como antes:
          badge.style.position = 'absolute';
          badge.style.left = '90%';
          badge.style.top  = '-10%';
          btn.appendChild(badge);
        }
        const old = badge.textContent;
        badge.textContent = String(count);

        if (old !== String(count)) {
          badge.classList.remove('bump');
          // reflow para reiniciar la animación
          // eslint-disable-next-line no-unused-expressions
          badge.offsetWidth;
          badge.classList.add('bump');
        }
      }else{
        if(badge) badge.remove();
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const bar = document.getElementById('avance-bar');
      const pct = @json($pct);
      if (bar) {
        bar.style.width = pct + '%';
        bar.textContent = pct + '%';
      }

      document.querySelectorAll('.cuadrante-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault(); e.stopPropagation();
          lastTriggerBtn = this;
          const id = this.getAttribute('data-cuadrante');
          mostrarModal(id);
        });
      });

      const modalWrapper = document.getElementById('modal-empleados');
      const modalBackdrop = document.getElementById('modal-backdrop');
      const btnCerrar = document.getElementById('btn-cerrar-modal');

      if (modalWrapper) {
        modalWrapper.addEventListener('click', function(e) {
          if (e.target === modalWrapper) cerrarModal();
        });
      }
      if (modalBackdrop) modalBackdrop.addEventListener('click', cerrarModal);
      if (btnCerrar) btnCerrar.addEventListener('click', (e) => { e.preventDefault(); cerrarModal(); });

      document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('modal-empleados');
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) cerrarModal();
      });

      const img = document.getElementById('ninebox-img');
      if (img) img.addEventListener('contextmenu', (e) => { e.preventDefault(); return false; });
    });

    // === Modal con entrada/salida suave (clase .show) ===
    async function mostrarModal(cuadrante){
      cuadranteActual = cuadrante;
      const data = cuadrantesData[cuadrante] || { title:'Cuadrante', subtitle:'', desc:'' };
      const titleEl = document.getElementById('modal-title');
      const descEl  = document.getElementById('modal-desc');
      if (titleEl) titleEl.textContent = data.title;
      if (descEl)  descEl.textContent  = data.desc;

      const countA = document.getElementById('count-asignados');
      const countD = document.getElementById('count-disponibles');
      if (countA) countA.textContent = '0';
      if (countD) countD.textContent = '0';

      try{
        const url = `/jefe/cuadrante/${encodeURIComponent(cuadrante)}/empleados`;
        const response = await fetch(url, {
          method:'GET',
          headers:{ 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
          credentials:'same-origin'
        });
        if(!response.ok){
          const errorText = await response.text();
          console.error('Error del servidor:', errorText);
          alert(`Error al cargar empleados (código ${response.status})`);
          return;
        }
        const result = await response.json();
        renderizarEmpleados(result.asignados || [], result.disponibles || []);
        setBadgeCount(cuadrante, (result.asignados || []).length);

        const modal = document.getElementById('modal-empleados');
        if (modal){
          modal.classList.remove('hidden');
          // deja que el DOM pinte, luego añade .show para disparar transiciones CSS
          requestAnimationFrame(() => modal.classList.add('show'));
        }
      }catch(error){
        console.error('Error fetch empleados:', error);
        alert('Error al cargar empleados: ' + error.message);
      }
    }

    function cerrarModal(){
      const modal = document.getElementById('modal-empleados');
      if (!modal) return;
      modal.classList.remove('show');
      setTimeout(() => {
        modal.classList.add('hidden');
        if (lastTriggerBtn) {
          try { lastTriggerBtn.focus(); } catch(_) {}
        } else {
          const firstBtn = document.querySelector('.cuadrante-btn');
          if (firstBtn) firstBtn.focus();
        }
      }, 220);
    }

    // === Resto de lógica sin cambios ===
    function renderizarEmpleados(asignados, disponibles){
      const listaAsignados = document.getElementById('lista-asignados');
      const listaDisponibles = document.getElementById('lista-disponibles');
      const emptyAsignados = document.getElementById('empty-asignados');
      const emptyDisponibles = document.getElementById('empty-disponibles');

      const countA = document.getElementById('count-asignados');
      const countD = document.getElementById('count-disponibles');
      if (countA) countA.textContent = asignados.length;
      if (countD) countD.textContent = disponibles.length;

      if (listaAsignados) listaAsignados.innerHTML = '';
      if (asignados.length > 0){
        if (emptyAsignados) emptyAsignados.classList.add('hidden');
        asignados.forEach((emp, index) => {
          const li = document.createElement('li');
          li.className = 'lista-empleado flex items-center justify-between border-l-4 border-green-500';
          li.style.animation = `slideIn 0.32s ease-out ${index * 0.05}s both`;

          const left = document.createElement('div');
          left.className = 'flex items-center gap-3';

          const icon = document.createElement('div');
          icon.className = 'avatar-icon';
          icon.innerHTML = `<svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>`;

          const name = document.createElement('span');
          name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado';
          name.textContent = `${emp.apellido_paterno||''} ${emp.apellido_materno||''}`;

          left.appendChild(icon);
          left.appendChild(name);

          const btn = document.createElement('button');
          btn.type = 'button';
          btn.dataset.id = emp.id;
          btn.className = 'btn-accion btn-eliminar eliminar-btn';
          btn.textContent = 'Eliminar';
          btn.addEventListener('click', (e) => { e.stopPropagation(); eliminarEmpleado(emp.id); });

          li.appendChild(left);
          li.appendChild(btn);
          if (listaAsignados) listaAsignados.appendChild(li);
        });
      }else{
        if (emptyAsignados) emptyAsignados.classList.remove('hidden');
      }

      if (listaDisponibles) listaDisponibles.innerHTML = '';
      if (disponibles.length > 0){
        if (emptyDisponibles) emptyDisponibles.classList.add('hidden');
        disponibles.forEach((emp, index) => {
          const li = document.createElement('li');
          li.className = 'lista-empleado flex items-center justify-between border-l-4 border-blue-500 cursor-pointer group';
          li.style.animation = `slideIn 0.32s ease-out ${index * 0.05}s both`;

          const left = document.createElement('div');
          left.className = 'flex items-center gap-3';

          const icon = document.createElement('div');
          icon.className = 'avatar-icon';
          icon.innerHTML = `<svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>`;

          const name = document.createElement('span');
          name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado';
          name.textContent = `${emp.apellido_paterno||''} ${emp.apellido_materno||''}`;

          left.appendChild(icon);
          left.appendChild(name);

          const btn = document.createElement('button');
          btn.type = 'button';
          btn.dataset.id = emp.id;
          btn.className = 'btn-accion btn-asignar asignar-btn';
          btn.innerHTML = `<svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Asignar`;
          btn.addEventListener('click', (e) => { e.stopPropagation(); asignarEmpleado(emp.id); });

          li.appendChild(left);
          li.appendChild(btn);
          if (listaDisponibles) listaDisponibles.appendChild(li);
        });
      }else{
        if (emptyDisponibles) emptyDisponibles.classList.remove('hidden');
      }
    }

    async function asignarEmpleado(usuarioId){
      try{
        const formData = new FormData();
        formData.append('usuario_id', parseInt(usuarioId));
        formData.append('ninebox_id', parseInt(cuadranteActual));
        formData.append('_token', CSRF_TOKEN);

        const response = await fetch('/jefe/asignar-empleado', {
          method:'POST',
          headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
          credentials:'same-origin',
          body: formData
        });
        if(!response.ok){
          const errorText = await response.text();
          console.error('Error al asignar:', errorText);
          alert('Error al asignar empleado (código ' + response.status + ')');
          return;
        }
        await response.json();
        await mostrarModal(cuadranteActual);
        await actualizarEstadisticas(true);
      }catch(error){
        console.error('Error fetch asignar:', error);
        alert('Error al asignar empleado: ' + error.message);
      }
    }

    async function eliminarEmpleado(usuarioId){
      if(!confirm('¿Eliminar esta asignación?')) return;
      try{
        const response = await fetch('/jefe/eliminar-asignacion', {
          method:'POST',
          headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept':'application/json',
            'X-Requested-With':'XMLHttpRequest'
          },
          credentials:'same-origin',
          body: JSON.stringify({ usuario_id: parseInt(usuarioId) })
        });
        if(!response.ok){
          const errorText = await response.text();
          console.error('Error al eliminar:', errorText);
          alert(`Error al eliminar asignación (código ${response.status})`);
          return;
        }
        await response.json();
        await mostrarModal(cuadranteActual);
        await actualizarEstadisticas(false);
      }catch(error){
        console.error('Error fetch eliminar:', error);
        alert('Error al eliminar asignación: ' + error.message);
      }
    }

    function actualizarEstadisticas(aumentar = true){
      try{
        const elTotal = document.getElementById('total-empleados');
        const elEval  = document.getElementById('empleados-evaluados');
        const elPend  = document.getElementById('empleados-pendientes');

        const total = parseInt(elTotal?.textContent || '0');
        const evaluados = parseInt(elEval?.textContent || '0');
        const actualEvaluados = aumentar ? evaluados + 1 : evaluados - 1;
        const pendientes = (total - actualEvaluados);

        if (elTotal) elTotal.textContent = total;
        if (elEval) elEval.textContent = actualEvaluados;
        if (elPend) elPend.textContent = pendientes;

        // Progreso
        const pct = total > 0 ? Math.round((actualEvaluados / total) * 100) : 0;
        const bar = document.getElementById('avance-bar');
        if (bar) {
          bar.textContent = pct + '%';
          bar.style.width = pct + '%';
        }

        // Botón Guardar Evaluación
        const btn = document.getElementById('btn-guardar-evaluacion');
        if (btn){
          const habilitar = pendientes === 0 && total > 0;
          btn.disabled = !habilitar;
        }
      }catch(err){
        console.log('Ocurrió un error', err);
      }
    }

    function guardarEvaluacion(){
      alert('Guardar evaluación (implementa la lógica en esta función según tu backend).');
    }
  </script>
</x-app-layout>
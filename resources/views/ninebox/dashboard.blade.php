<x-app-layout>
  @section('title', 'Panel General | 9-Box')
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col lg:flex-row gap-8">
        @php
          $totalEmpleados = $totalEmpleados ?? count($empleados ?? []);
          $empleadosEvaluados = $empleadosEvaluados ?? 0;
          $pendientes = max(0, $totalEmpleados - $empleadosEvaluados);
          $pct = $totalEmpleados > 0 ? min(100, round(($empleadosEvaluados / $totalEmpleados) * 100)) : 0;
          $anioActual = request('anio', now()->year);
          $mesActual = request('mes', now()->month);
          $encuestaEmpleadoBase = url('/encuestas');
          $esSuper = $esSuper ?? (isset($usuario) && method_exists($usuario, 'esSuperusuario') && $usuario->esSuperusuario());
        @endphp

        {{-- Sidebar resumen --}}
        <aside class="lg:w-80 flex-shrink-0 rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40
                 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg lg:sticky lg:top-4 lg:overflow-hidden">
          <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative">
              <h3 class="text-white font-extrabold text-3xl tracking-tight">Resumen</h3>
              @if (method_exists($usuario, 'esSuperusuario') && $usuario->esSuperusuario())
                  {{-- Superusuario: solo muestra "Resumen" y ya --}}
                  <p class="text-white text-xl leading-snug"></p>
              @else
                  {{-- Jefe: mostrar su departamento --}}
                  <p class="text-white text-xl leading-snug">
                      {{ optional($usuario->departamento)->nombre_departamento ?? 'Sin departamento' }}
                  </p>
              @endif
            </div>
          </div>
          <div class="p-7 space-y-7">
            {{-- Periodo --}}
            <section aria-label="Periodo" class="flex flex-wrap items-center gap-3">
              <div>
                @php
                    $primerAnio = 2025; 
                @endphp

                <label for="filtro-anio" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Año</label>
                <select id="filtro-anio"
                  class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                              focus:border-indigo-500 focus:ring-indigo-500">
                  @for ($i = now()->year; $i >= $primerAnio; $i--)
                    <option value="{{ $i }}" {{ (int)$i === (int)$anioActual ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
              <div>
                <label for="filtro-mes" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Mes</label>
                <select id="filtro-mes"
                  class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                               focus:border-indigo-500 focus:ring-indigo-500">
                  @foreach ([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num => $mes)
                    <option value="{{ $num }}" {{ (int)$num === (int)$mesActual ? 'selected' : '' }}>{{ $mes }}</option>
                  @endforeach
                </select>
              </div>
            </section>

            {{-- KPIs --}}
            <section aria-label="Indicadores clave">
              <div class="kpi-glass">
                <div class="kpi-row">
                  <span class="kpi-label text-rose-600 dark:text-red-400">Por evaluar</span>
                  <span id="empleados-pendientes" class="kpi-value">{{ $pendientes }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-emerald-600 dark:text-emerald-400">Evaluados</span>
                  <span id="empleados-evaluados" class="kpi-value">{{ $empleadosEvaluados }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-gray-900 dark:text-gray-100">Total Empleados</span>
                  <span id="total-empleados" class="kpi-value">{{ $totalEmpleados }}</span>
                </div>
              </div>
            </section>

            {{-- Barra de progreso --}}
            <section>
              <div class="flex items-center justify-between text-xl text-gray-900 dark:text-gray-300 mb-2">
                <span class="font-semibold tracking-tight">Progreso</span>
              </div>
              <div class="w-full h-6 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden">
                <div id="avance-bar"
                  class="h-6 text-center rounded-full text-white transition-[width] duration-300 ease-in-out btn-primary"
                  style="width: {{ $pct }}%;">
                  {{ $pct }}%
                </div>
              </div>
            </section>

            {{-- CTA lista empleados (solo jefes) --}}
            @if (isset($usuario) && method_exists($usuario, 'esJefe') && $usuario->esJefe())
              <div class="mt-2 space-y-2">
                <a
                  id="btn-por-evaluar"
                  href="{{ route('encuestas.empleados', ['anio' => $anioActual, 'mes' => $mesActual]) }}"
                  class="btn btn-primary btn-block"
                >
                  Evaluar empleados
                </a>
                <p class="text-[13px] text-center mt-1 text-gray-700 dark:text-gray-400">
                  Abre la lista de empleados para iniciar o continuar encuestas.
                </p>
              </div>
            @endif
          </div>
        </aside>

        {{-- Nine-Box + hotspots --}}
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
              @php
                $map = \App\Models\NineBox::posMap();
                $lefts = [1 => '17.5%', 2 => '42.5%', 3 => '67.5%'];
                $tops = [1 => '18%', 2 => '45%', 3 => '71.8%'];
                $W = ['default' => '23.5%', 'last' => '23%'];
                $H = '25%';
                $positions = [];
                foreach ($map as $id => $rc) {
                  $col = (int)$rc['col']; $row = (int)$rc['row'];
                  $positions[$id] = [
                    'left' => $lefts[$col],
                    'top' => $tops[$row],
                    'w' => $col === 3 ? $W['last'] : $W['default'],
                    'h' => $H,
                  ];
                }
                $order = collect($map)
                  ->map(fn($rc, $id) => ['id'=>$id,'row'=>$rc['row'],'col'=>$rc['col']])
                  ->sortBy([['row','asc'],['col','asc']])
                  ->pluck('id')
                  ->all();
              @endphp
              @foreach ($order as $i)
                @php
                  $pos = $positions[$i];
                  $count = collect($asignacionesActuales[$i] ?? [])->count();
                @endphp
                <button type="button" class="cuadrante-btn btn-surface"
                  style="position:absolute; left: {{ $pos['left'] }}; top: {{ $pos['top'] }};
                               width: {{ $pos['w'] }}; height: {{ $pos['h'] }};"
                  data-cuadrante="{{ $i }}"
                  aria-label="Ver empleados en cuadrante {{ $i }}">
                  @if($count > 0)
                    <div class="cuadrante-badge">{{ $count }}</div>
                  @endif
                </button>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Modal --}}
    <div id="modal-empleados" class="fixed inset-0 z-50 hidden"
      role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-desc">
      <div id="modal-backdrop" class="absolute inset-0"></div>
      <div class="relative h-full flex items-center justify-center p-4">
        <div id="modal-container"
          class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg rounded-2xl shadow-2xl w-full max-w-2xl
                    max-h-[80vh] overflow-hidden border border-white/10 dark:border-gray-700/40 transform scale-90 opacity-0
                    transition-all duration-300" tabindex="-1" aria-hidden="true">
          <div class="bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 p-6 relative overflow-hidden select-none">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex justify-between items-start">
              <div>
                <h3 id="modal-title" class="text-4xl md:text-5xl font-extrabold text-white mb-2 drop-shadow-lg"></h3>
                <p id="modal-desc" class="text-lg md:text-xl text-white/90"></p>
              </div>
              <button id="btn-cerrar-modal" class="btn btn-close btn-icon" aria-label="Cerrar modal" title="Cerrar">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          {{-- Cuerpo del modal --}}
          <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 140px);">
            <div id="section-asignados" class="mb-2">
              {{-- Header del modal (vacío inicialmente, se llena con JS) --}}
              <div id="modal-header-section" class="flex items-center gap-3 mb-4 select-none">
                <div class="badge-icon bg-gradient-to-r from-green-600 to-emerald-600" id="badge-asignados">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <h4 id="title-asignados" class="text-xl font-bold text-gray-900 dark:text-white">Asignados</h4>
                <span id="count-asignados" class="chip chip-success ml-auto">0</span>
              </div>

              <div id="empty-asignados" class="text-center py-8 text-gray-400 dark:text-gray-500 hidden select-none">
                <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                No hay empleados asignados en este cuadrante para el periodo seleccionado.
              </div>
              <ul id="lista-asignados" class="space-y-3"></ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- CSS --}}
  <style>
    :root{
      --brand-indigo:#4338ca; --brand-purple:#6d28d9; --accent-cyan:#0891b2;
      --danger-red:#dc2626; --success-green:#059669;
      --modal-bg-light:rgba(255,255,255,0.96); --modal-bg-dark:rgba(8,10,20,0.92);
      --glass-0:rgba(255,255,255,0.06); --glass-1:rgba(255,255,255,0.04);
      --shadow-soft:0 8px 18px rgba(2,6,23,0.10); --shadow-strong:0 18px 48px rgba(2,6,23,0.18);
      --radius-lg:.95rem; --radius-md:.9rem; --anim-fast:.12s;
    }
    *{box-sizing:border-box}
    :focus{outline:none}
    .sr-only{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}

    /* Botones */
    .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
      font-weight:700; line-height:1; border-radius:var(--radius-md);
      padding:.7rem 1.15rem; border:1px solid transparent; cursor:pointer;
      transition: transform var(--anim-fast) ease, box-shadow var(--anim-fast) ease,
                  filter var(--anim-fast) ease, opacity var(--anim-fast) ease, background var(--anim-fast) ease;
      box-shadow: var(--shadow-soft);
      -webkit-tap-highlight-color: transparent;
      user-select: none; caret-color: transparent;
    }
    .btn:hover{ transform: translateY(-1px); }
    .btn:active{ transform: translateY(0); }
    .btn:focus-visible{ outline:3px solid rgba(79,70,229,0.18); outline-offset:3px; }
    .btn-block{ width:100%; }
    .btn-sm{ padding:.5rem .85rem; font-size:.9rem; border-radius:.75rem; }
    .btn-icon{ width:40px; height:40px; padding:0; border-radius:9999px; aspect-ratio:1/1; }
    .btn-primary{
      color:#fff;
      background-image: linear-gradient(90deg, var(--brand-indigo) 0%, var(--brand-purple) 100%);
      border-color: rgba(0,0,0,0.06);
    }
    .btn-primary:hover{ filter:brightness(1.05); }
    .btn-danger{
      color:#fff; background-image: linear-gradient(90deg, #ef4444 0%, var(--danger-red) 100%);
      border-color: rgba(0,0,0,0.06);
    }
    .btn-danger:hover{ filter:brightness(1.05); }
    .btn-ghost{
      background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.14); color:#fff;
      backdrop-filter: blur(6px); box-shadow: 0 4px 14px rgba(2,6,23,0.12);
      user-select: none; caret-color: transparent;
    }
    @media (prefers-color-scheme: light){
      .btn-ghost{ background: rgba(15,23,42,0.06); color:#0f172a; border-color: rgba(15,23,42,0.10); }
    }
    .btn-close{
      color:#fff !important; background: rgba(255,255,255,0.12);
      border:1px solid rgba(255,255,255,0.14); box-shadow: 0 4px 14px rgba(2,6,23,0.12);
      backdrop-filter: blur(6px); line-height:0;
    }
    @media (prefers-color-scheme: light){
      .btn-close{ background: rgba(15,23,42,0.30); border-color: rgba(15,23,42,0.25); color:#fff !important; }
    }

    /* Chips / badges */
    .chip{
      display:inline-flex; align-items:center; justify-content:center; padding:.25rem .75rem;
      border-radius:9999px; font-weight:800; font-size:.8rem; color:#fff;
      box-shadow:0 4px 14px rgba(2,6,23,.12); user-select:none; caret-color: transparent;
    }
    .chip-success{ background-image: linear-gradient(90deg,#16a34a 0%, #059669 100%); }
    .chip-info{ background-image: linear-gradient(90deg,#2563eb 0%, #06b6d4 100%); }

    .badge-icon{
      padding:.5rem; border-radius:var(--radius-md); display:inline-grid; place-items:center; color:#fff;
      box-shadow:0 6px 16px rgba(2,6,23,.16); user-select: none; caret-color: transparent;
    }

    /* KPIs */
    .kpi-glass{
      position: relative; padding: 1.1rem; border-radius: var(--radius-lg);
      background: linear-gradient(180deg, var(--glass-0), var(--glass-1));
      border: 1px solid rgba(255,255,255,0.06);
      box-shadow: 0 10px 28px rgba(2,6,23,0.20) inset, 0 8px 22px rgba(2,6,23,0.12);
    }
    @media (prefers-color-scheme: light){
      .kpi-glass{ background: linear-gradient(180deg, rgba(15,23,42,0.06), rgba(15,23,42,0.04)); border: 1px solid rgba(15,23,42,0.06); }
    }
    .kpi-row{
      display: grid; grid-template-columns: 1fr auto; align-items: center; gap:.85rem;
      padding:1rem 1.1rem; border-radius:.8rem; background: rgba(2,6,23,0.08); user-select: none;
    }
    .kpi-row + .kpi-row{ margin-top:.75rem; }
    @media (prefers-color-scheme: dark){ .kpi-row{ background: rgba(2,6,23,0.18); } }
    .kpi-label{ font-weight:700; font-size:1rem; letter-spacing:.2px; }
    .kpi-value{ font-weight:800; font-size:2.15rem; line-height:1; color:#0b1020; letter-spacing:.2px; }
    @media (prefers-color-scheme: dark){ .kpi-value{ color:#e6eef8; text-shadow:0 1px 0 rgba(0,0,0,.15); } }

    /* Hotspots cuadrantes */
    .btn-surface{
      background: rgba(79,70,229,0.08);
      border: 2px solid transparent; border-radius: 12px;
      transition: transform .18s cubic-bezier(.2,.9,.3,1), box-shadow .18s ease, border-color .12s ease, background .12s ease;
      z-index:40; outline:none;
      cursor:pointer; user-select:none; caret-color: transparent;
    }
    .btn-surface:hover,.btn-surface:focus{
      border-color: rgba(79,70,229,0.95);
      background: linear-gradient(180deg, rgba(79,70,229,0.12), rgba(79,70,229,0.08));
      transform: scale(1.04);
      box-shadow: 0 8px 20px rgba(79,70,229,0.12);
    }

    /* Badge cuadrante */
    .cuadrante-badge{
      position:absolute; left:90%; top:-10%;
      display:inline-flex; align-items:center; justify-content:center;
      padding:.25rem .75rem; border-radius:9999px; font-weight:800; font-size:.75rem; line-height:1; color:#fff;
      background-image: linear-gradient(90deg,var(--brand-indigo) 0%, var(--brand-purple) 100%);
      border:1px solid rgba(0,0,0,.05);
      box-shadow:0 4px 14px rgba(2,6,23,.12); user-select:none; caret-color: transparent; pointer-events:none;
    }

    /* Modal */
    #modal-empleados{ position:fixed; inset:0; z-index:50; display:none; align-items:center; justify-content:center; padding:1rem; }
    #modal-empleados:not(.hidden){ display:flex; }

    #modal-backdrop{
      position:absolute; inset:0; z-index:10;
      background: linear-gradient(180deg, rgba(10,12,20,0.72), rgba(67,56,202,0.18));
      backdrop-filter: blur(8px) saturate(120%); -webkit-backdrop-filter: blur(8px) saturate(120%);
      pointer-events:auto; opacity:0; transition: opacity .22s ease;
    }
    #modal-empleados:not(.hidden).show #modal-backdrop{ opacity:1; }

    #modal-container{
      position:relative; z-index:20; width:100%; max-width:44rem; max-height:80vh; overflow:hidden; border-radius:1rem;
      background-color:var(--modal-bg-light); color:#0f172a; border:1px solid rgba(15,23,42,0.06);
      box-shadow: var(--shadow-strong);
      transform: translateY(8px) scale(.98); opacity:0;
      transition: transform .22s cubic-bezier(.2,.9,.3,1), opacity .18s ease;
    }
    @media (prefers-color-scheme: dark){
      #modal-container{ background-color:var(--modal-bg-dark); color:#e6eef8; border:1px solid rgba(255,255,255,0.04); box-shadow:0 20px 60px rgba(2,6,23,0.5); }
    }
    #modal-container>.bg-gradient-to-r, #modal-container .bg-gradient-to-r{
      background: linear-gradient(90deg, var(--brand-indigo) 0%, var(--brand-purple) 55%, var(--brand-indigo) 100%);
    }
    #modal-empleados:not(.hidden).show #modal-container{ transform: translateY(0) scale(1); opacity:1; }

    /* Lista empleados – hover suave + línea por departamento */
    .lista-empleado{
      padding:.78rem;
      border-radius:.85rem;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:1rem;
      background: linear-gradient(180deg, rgba(255,255,255,0.64), rgba(255,255,255,0.50));
      transition: transform .12s ease, box-shadow .12s ease;
      cursor:pointer;
      user-select:none;
      caret-color: transparent;
      border-left:4px solid var(--depto-color, rgba(5,150,105,0.95));
    }
    .lista-empleado:hover{
      transform:translateY(-4px);
      box-shadow:0 12px 30px rgba(2,6,23,0.06);
    }
    @media (prefers-color-scheme: dark){
      .lista-empleado{
        background: linear-gradient(180deg, rgba(15,23,42,0.14), rgba(15,23,42,0.06));
      }
    }

    .avatar-icon{
      width:44px; height:44px; border-radius:9999px; display:inline-flex; align-items:center; justify-content:center;
      background: linear-gradient(180deg, rgba(15,23,42,0.06), rgba(67,56,202,0.06));
      box-shadow:0 4px 12px rgba(2,6,23,0.06); color:#0f172a; user-select:none; caret-color: transparent;
    }
    .nombre-empleado{ font-weight:600; color:inherit; }

    .cuadrante-btn:focus-visible,
    #btn-cerrar-modal:focus-visible{ outline:3px solid rgba(79,70,229,0.18); outline-offset:3px; }

    @media (min-width:1024px){
      aside.lg\:sticky{ max-height: calc(100vh - 1rem); display:flex; flex-direction:column; }
      aside.lg\:sticky > .p-7{ overflow:auto; }
    }

    /* Encabezado por departamento */
    .depto-header{
      display:flex; align-items:center; gap:.75rem;
      margin-bottom:.85rem; margin-top:1.5rem; padding:.6rem .7rem;
      border-radius:.85rem;
      background: linear-gradient(180deg, rgba(255,255,255,0.64), rgba(255,255,255,0.50));
    }
    .depto-header:first-child{ margin-top:0; }
    @media (prefers-color-scheme: dark){
      .depto-header{ background: linear-gradient(180deg, rgba(15,23,42,0.14), rgba(15,23,42,0.06)); }
    }
    .depto-title{ font-weight:800; font-size:1.05rem; color:#0f172a; }
    @media (prefers-color-scheme: dark){ .depto-title{ color:#e6eef8; } }

    @keyframes slideIn{
      from{opacity:0;transform:translateY(8px);}
      to{opacity:1;transform:translateY(0);}
    }
  </style>

  {{-- JS --}}
  <script>
  (function () {
    'use strict';

    const ES_SUPER       = @json($esSuper);
    const ASIG           = @json($asignacionesActuales ?? []);
    const ENCUESTA_BASE  = @json($encuestaEmpleadoBase);
    const ANIO_ACTUAL    = @json($anioActual);
    const MES_ACTUAL     = @json($mesActual);
    // Año/mes reales (para no permitir meses futuros)
    const ANIO_HOY       = {{ now()->year }};
    const MES_HOY        = {{ now()->month }};

    const BASE_DESC = {
      6:{title:"Diamante en bruto",desc:"Gran potencial, su desempeño no ha sido exigido por lo que requiere desarrollarlo"},
      8:{title:"Estrella en desarrollo",desc:"Potencial y desempeño en crecimiento, con la dirección adecuada puede convertirse en una estrella"},
      9:{title:"Estrella",desc:"Empleados con alto desempeño y gran potencial, clave para la organización"},
      2:{title:"Mal empleado",desc:"Desempeño insuficiente, requiere mejora y desarrollo"},
      5:{title:"Personal sólido",desc:"Desempeño aceptable, pero con potencial limitado para crecer"},
      7:{title:"Elemento importante",desc:"Buena contribución actual, pero con un potencial de crecimiento incierto"},
      1:{title:"Inaceptable",desc:"Desempeño inaceptable, requiere acción inmediata"},
      3:{title:"Aceptable",desc:"Desempeño básico, cumple con los mínimos requerimientos"},
      4:{title:"Personal clave",desc:"Confiables con buen desempeño, pero con poco potencial de desarrollo"}
    };

    function getPeriodo(){
      const anioSel = document.getElementById('filtro-anio')?.value ?? ANIO_ACTUAL;
      const mesSel  = document.getElementById('filtro-mes')?.value  ?? MES_ACTUAL;
      return { anio: String(anioSel).trim(), mes: String(mesSel).trim() };
    }
    
    function reloadWithPeriodo() {
      const { anio, mes } = getPeriodo();
      const url = new URL(window.location.href);
      url.searchParams.set('anio', anio);
      url.searchParams.set('mes',  mes);
      window.location.href = url.toString();
    }

        const filtroMesEl = document.getElementById('filtro-mes');

        // Guardamos las opciones originales (1..12) para reconstruirlas sin perder nombres
        const OPCIONES_MESES_ORIGINALES = filtroMesEl
          ? Array.from(filtroMesEl.options).map(o => ({
              value: o.value,
              text:  o.text,
            }))
          : [];

        function limitarMesesPorAnio() {
          const anioEl = document.getElementById('filtro-anio');
          const mesEl  = document.getElementById('filtro-mes');
          if (!anioEl || !mesEl || OPCIONES_MESES_ORIGINALES.length === 0) return;

          const anioSel     = parseInt(anioEl.value || ANIO_ACTUAL, 10);
          const limiteMes   = (anioSel === ANIO_HOY) ? MES_HOY : 12; // no ir más allá del mes actual
          const mesPrevio   = parseInt(mesEl.value || MES_ACTUAL, 10);

          // Limpiamos el select y lo volvemos a llenar
          mesEl.innerHTML = '';

          OPCIONES_MESES_ORIGINALES.forEach(optData => {
            const v = parseInt(optData.value, 10);
            if (Number.isNaN(v)) return;

            if (v > limiteMes) return; // bloqueamos meses futuros

            const opt = document.createElement('option');
            opt.value = optData.value;
            opt.textContent = optData.text;

            if (v === mesPrevio && v <= limiteMes) {
              opt.selected = true;
            }

            mesEl.appendChild(opt);
          });

          // Si el mes previo era futuro y ya no existe, forzamos al último permitido
          if (!mesEl.value) {
            mesEl.value = String(limiteMes);
          }
        }

        // Al cargar el script, ajustamos los meses una vez
        limitarMesesPorAnio();

        // Cuando cambia el año: primero limitar meses y luego recargar
        document.getElementById('filtro-anio')?.addEventListener('change', function () {
          limitarMesesPorAnio();
          reloadWithPeriodo();
        });

        // Cuando cambia solo el mes, solo recargamos
        document.getElementById('filtro-mes')?.addEventListener('change',  reloadWithPeriodo);

    function urlEncuestaEmpleado(empId){
      const { anio, mes } = getPeriodo();
      const u = new URL(`${ENCUESTA_BASE}/${encodeURIComponent(empId)}`, window.location.origin);
      u.searchParams.set('anio', anio);
      u.searchParams.set('mes',  mes);
      return u.toString();
    }

    function agruparPorDepartamento(lista){
      return lista.reduce((acc, emp)=>{
        const d = emp.departamento_nombre || 'Sin departamento';
        (acc[d] ||= []).push(emp);
        return acc;
      },{});
    }

    function renderListaSimple(lista){
      const ul    = document.getElementById('lista-asignados');
      const empty = document.getElementById('empty-asignados');
      const title = document.getElementById('title-asignados');
      const badge = document.getElementById('badge-asignados');
      const header = document.getElementById('modal-header-section');

      if (header) header.style.display = 'flex';
      if (title) title.textContent = 'Asignados';
      if (badge) {
        badge.className = 'badge-icon bg-gradient-to-r from-green-600 to-emerald-600';
        badge.innerHTML = '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
      }

      ul.innerHTML = '';
      if (!Array.isArray(lista) || lista.length === 0){
        empty?.classList.remove('hidden');
        return;
      }
      empty?.classList.add('hidden');

      lista.forEach((emp, i)=>{
        const empId = Number(emp.usuario_id ?? emp.id);
        const li = document.createElement('li');
        li.className = 'lista-empleado';
        li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
        li.tabIndex = 0;
        li.setAttribute('role', 'button');

        const left = document.createElement('div');
        left.className = 'flex items-center gap-3';

        const avatar = document.createElement('div');
        avatar.className = 'avatar-icon';
        avatar.innerHTML = '<svg class="w-5 h-5 text-gray-700 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

        const fullName = [emp.nombre||'', emp.apellido_paterno||'', emp.apellido_materno||''].join(' ').trim() || `ID #${empId}`;
        const name = document.createElement('a');
        name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado underline-offset-2';
        name.textContent = fullName;
        name.href = urlEncuestaEmpleado(empId);

        left.appendChild(avatar);
        left.appendChild(name);
        li.appendChild(left);

        ul.appendChild(li);

        li.addEventListener('click', ()=>{ window.location.href = urlEncuestaEmpleado(empId); });
        li.addEventListener('keydown', (ev)=>{
          if(ev.key==='Enter'||ev.key===' '){ 
            ev.preventDefault(); 
            window.location.href = urlEncuestaEmpleado(empId);
          }
        });
      });
    }

    function renderAgrupadoPorDepto(lista){
      const ul    = document.getElementById('lista-asignados');
      const empty = document.getElementById('empty-asignados');
      const header = document.getElementById('modal-header-section');

      ul.innerHTML = '';

      if (!Array.isArray(lista) || lista.length === 0){
        empty?.classList.remove('hidden');
        if (header) header.style.display = 'none';
        return;
      }
      empty?.classList.add('hidden');

      const porDepto = agruparPorDepartamento(lista);
      const deptos = Object.keys(porDepto).sort((a,b)=>a.localeCompare(b,'es'));

      // PALETA por departamento
      const palette = [
        '#22c55e', // verde
        '#0ea5e9', // azul cielo
        '#a855f7', // violeta
        '#f97316', // naranja
        '#e11d48', // rosa
        '#14b8a6', // teal
        '#84cc16', // lima
      ];
      const colorByDepto = {};
      deptos.forEach((dep, idx) => {
        colorByDepto[dep] = palette[idx % palette.length];
      });

      if (deptos.length === 1){
        if (header) header.style.display = 'flex';
        const title = document.getElementById('title-asignados');
        const badge = document.getElementById('badge-asignados');
        if (title) title.textContent = deptos[0];
        if (badge) {
          badge.className = 'badge-icon bg-gradient-to-r from-indigo-600 to-purple-600';
          badge.innerHTML = '<svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16m-7 0v-4a1 1 0 011-1h2a1 1 0 011 1v4M7 7h2m-2 4h2m4-4h2m-2 4h2"/></svg>';
        }

        porDepto[deptos[0]]
          .sort((a,b)=> (a.nombre||'').localeCompare(b.nombre||'','es'))
          .forEach((emp, i)=>{
            const li = document.createElement('li');
            li.className = 'lista-empleado';
            li.style.cursor = 'default';
            li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
            li.style.setProperty('--depto-color', colorByDepto[deptos[0]] || 'rgba(5,150,105,0.95)');

            const left = document.createElement('div');
            left.className = 'flex items-center gap-3';

            const avatar = document.createElement('div');
            avatar.className = 'avatar-icon';
            avatar.innerHTML = '<svg class="w-5 h-5 text-gray-700 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

            const name = document.createElement('span');
            name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado';
            name.textContent = [emp.nombre||'', emp.apellido_paterno||'', emp.apellido_materno||''].join(' ').trim();

            left.appendChild(avatar);
            left.appendChild(name);
            li.appendChild(left);
            ul.appendChild(li);
          });
        return;
      }

      if (header) header.style.display = 'none';

      deptos.forEach(depNombre => {
        const empleados = porDepto[depNombre]
          .sort((a,b)=> (a.nombre||'').localeCompare(b.nombre||'','es'));

        const headerLi = document.createElement('li');
        headerLi.className = 'depto-header';

        const icon = document.createElement('div');
        icon.className = 'badge-icon bg-gradient-to-r from-indigo-600 to-purple-600';
        icon.innerHTML = '<svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16m-7 0v-4a1 1 0 011-1h2a1 1 0 011 1v4M7 7h2m-2 4h2m4-4h2m-2 4h2"/></svg>';

        const h = document.createElement('h5');
        h.className = 'depto-title';
        h.textContent = depNombre;

        const chip = document.createElement('span');
        chip.className = 'chip chip-success ml-auto';
        chip.textContent = String(empleados.length);

        headerLi.appendChild(icon);
        headerLi.appendChild(h);
        headerLi.appendChild(chip);
        ul.appendChild(headerLi);

        empleados.forEach((emp, i)=>{
          const li = document.createElement('li');
          li.className = 'lista-empleado';
          li.style.cursor = 'default';
          li.setAttribute('aria-disabled','true');
          li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
          li.style.setProperty('--depto-color', colorByDepto[depNombre] || 'rgba(5,150,105,0.95)');

          const left = document.createElement('div');
          left.className = 'flex items-center gap-3';

          const avatar = document.createElement('div');
          avatar.className = 'avatar-icon';
          avatar.innerHTML = '<svg class="w-5 h-5 text-gray-700 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

          const name = document.createElement('span');
          name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado';
          name.textContent = [emp.nombre||'', emp.apellido_paterno||'', emp.apellido_materno||''].join(' ').trim();

          left.appendChild(avatar);
          left.appendChild(name);
          li.appendChild(left);
          ul.appendChild(li);
        });
      });
    }

    function renderAsignados(cuadrante){
      const lista = Array.isArray(ASIG[cuadrante]) ? ASIG[cuadrante] : [];
      const count = document.getElementById('count-asignados');
      if (count) count.textContent = String(lista.length);
      
      if (ES_SUPER) {
        renderAgrupadoPorDepto(lista);
      } else {
        renderListaSimple(lista);
      }
    }

    let lastTriggerBtn = null;

    function mostrarModal(cuadrante){
      const meta = BASE_DESC[cuadrante] || {title:`Cuadrante ${cuadrante}`, desc:''};
      const title = document.getElementById('modal-title');
      const desc  = document.getElementById('modal-desc');
      if (title) title.textContent = meta.title;
      if (desc)  desc.textContent  = meta.desc || '';

      renderAsignados(cuadrante);

      const modal = document.getElementById('modal-empleados');
      modal?.classList.remove('hidden');
      requestAnimationFrame(()=>{
        modal?.classList.add('show');
        document.getElementById('btn-cerrar-modal')?.focus();
        document.getElementById('modal-container')?.setAttribute('aria-hidden','false');
      });
    }

    function cerrarModal(){
      const modal = document.getElementById('modal-empleados');
      if (!modal) return;
      modal.classList.remove('show');
      setTimeout(()=>{
        modal.classList.add('hidden');
        document.getElementById('modal-container')?.setAttribute('aria-hidden','true');
        (lastTriggerBtn?.focus?.());
      }, 220);
    }

    document.addEventListener('DOMContentLoaded', ()=>{
      const bar = document.getElementById('avance-bar');
      if (bar){
        const pctText = bar.textContent?.replace('%','').trim();
        const pct = Number.isFinite(+pctText) ? +pctText : parseInt(bar.style.width,10) || 0;
        bar.style.width = pct + '%';
        bar.textContent = pct + '%';
      }

      document.querySelectorAll('.cuadrante-btn').forEach(btn=>{
        btn.addEventListener('click', (e)=>{
          e.preventDefault();
          lastTriggerBtn = btn;
          const cuad = parseInt(btn.getAttribute('data-cuadrante'),10);
          mostrarModal(cuad);
        });
        btn.addEventListener('keydown', (e)=>{
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            lastTriggerBtn = btn;
            const cuad = parseInt(btn.getAttribute('data-cuadrante'),10);
            mostrarModal(cuad);
          }
        });
      });

      document.getElementById('modal-backdrop')?.addEventListener('click', cerrarModal);
      document.getElementById('btn-cerrar-modal')?.addEventListener('click', cerrarModal);
      document.getElementById('modal-empleados')?.addEventListener('click', (e)=>{
        if (e.target.id === 'modal-empleados') cerrarModal();
      });
      document.addEventListener('keydown', (ev)=>{
        if (ev.key === 'Escape'){
          const modal = document.getElementById('modal-empleados');
          if (modal && !modal.classList.contains('hidden')) cerrarModal();
        }
      });
    });
  })();
  </script>
</x-app-layout>
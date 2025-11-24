<x-app-layout>
  @section('title', 'Mi Equipo | 9-Box')
  @php
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $anioActual     = $anio ?? now()->year;
    $mesActual      = $mes ?? now()->month;
    $filtroSel      = request('filtro'); // null | evaluado | en_proceso | no_iniciado
    $totalPreguntas = $totalPreguntas ?? 10;

    $authEmail = auth()->user()->correo ?? (auth()->user()->email ?? '');
  @endphp

  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar SOLO en "Todos" --}}
        @if (!$filtroSel)
        <aside
          class="lg:w-80 flex-shrink-0 rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40
                 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg lg:sticky lg:top-4 lg:overflow-hidden">
          <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative">
              <h3 class="text-white font-extrabold text-3xl tracking-tight">Mi Equipo</h3>
              <p class="text-white text-xl leading-snug">
                Colaboradores — {{ $meses[$mesActual] }} {{ $anioActual }}
              </p>
            </div>
          </div>

          {{-- Igual que el sidebar base: más aire (p-7/space-y-7) --}}
          <div class="p-7 space-y-7">
            {{-- Selector de periodo (mismo layout) --}}
            <section aria-label="Periodo" class="flex flex-wrap items-center gap-3">
              <div>
                @php
                    $primerAnio = 2025;
                @endphp

                <label for="filtro-anio" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Año</label>
                <select id="filtro-anio"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                                focus:border-indigo-500 focus:ring-indigo-500">
                  @for ($y = now()->year; $y >= $primerAnio; $y--)
                    <option value="{{ $y }}" @selected($y==$anioActual)>{{ $y }}</option>
                  @endfor
                </select>
              </div>
              <div>
                <label for="filtro-mes" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Mes</label>
                <select id="filtro-mes"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                               focus:border-indigo-500 focus:ring-indigo-500">
                  @foreach ($meses as $num => $m)
                    <option value="{{ $num }}" @selected($num==$mesActual)>{{ $m }}</option>
                  @endforeach
                </select>
              </div>
            </section>

            {{-- KPIs (ahora clickables) --}}
            <section aria-label="Indicadores clave">
              <div class="kpi-glass">
                <div
                  class="kpi-row kpi-card cursor-pointer hover:brightness-110 transition"
                  data-filter-target="no_iniciado"
                >
                  <span class="kpi-label text-rose-600 dark:text-red-400">No iniciados</span>
                  <span id="kpi-noiniciado" class="kpi-value">{{ $kpi_no_iniciado }}</span>
                </div>
                <div
                  class="kpi-row kpi-card cursor-pointer hover:brightness-110 transition"
                  data-filter-target="en_proceso"
                >
                  <span class="kpi-label status-proceso-label">En proceso</span>
                  <span id="kpi-proceso" class="kpi-value">{{ $kpi_en_proceso }}</span>
                </div>
                <div
                  class="kpi-row kpi-card cursor-pointer hover:brightness-110 transition"
                  data-filter-target="evaluado"
                >
                  <span class="kpi-label text-emerald-600 dark:text-emerald-400">Evaluados</span>
                  <span id="kpi-evaluados" class="kpi-value">{{ $kpi_evaluados }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-gray-900 dark:text-gray-100">Total</span>
                  <span id="kpi-total" class="kpi-value">{{ $kpi_total }}</span>
                </div>
              </div>
            </section>

            <p class="text-[13px] text-gray-700 dark:text-gray-400">
              Completa las {{ $totalPreguntas }} preguntas para enviar tu evaluación. Una vez enviada, el sistema la ubicará automáticamente en su cuadrante.
            </p>
          </div>
        </aside>
        @endif

        {{-- Contenido principal --}}
        <div class="flex-1">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-5 border border-gray-200 dark:border-gray-700">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-6">

              {{-- Filtros rápidos --}}
              <div class="filters flex flex-wrap items-center gap-2 w-full justify-center mt-3 sm:mt-0 sm:w-auto sm:justify-end sm:ml-auto">
                @php
                  $base = fn($f)=>route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual,'filtro'=>$f]);
                @endphp
                <a href="{{ $base(null) }}"
                   id="filter-todos"
                   class="filter-chip btn {{ $filtroSel ? 'btn-ghost' : 'btn-primary' }}">
                  Todos
                </a>
                <a href="{{ $base('evaluado') }}"
                   id="filter-evaluado"
                   class="filter-chip btn {{ $filtroSel==='evaluado' ? 'btn-primary' : 'btn-ghost' }}">
                  Evaluados
                </a>
                <a href="{{ $base('en_proceso') }}"
                   id="filter-en_proceso"
                   class="filter-chip btn {{ $filtroSel==='en_proceso' ? 'btn-primary' : 'btn-ghost' }}">
                  En proceso
                </a>
                <a href="{{ $base('no_iniciado') }}"
                   id="filter-no_iniciado"
                   class="filter-chip btn {{ $filtroSel==='no_iniciado' ? 'btn-primary' : 'btn-ghost' }}">
                  No iniciados
                </a>
              </div>
            </div>

            {{-- Lista --}}
            @php $colores = ['evaluado'=>'chip-success', 'en_proceso'=>'chip-warning', 'no_iniciado'=>'chip-danger']; @endphp

            <ul id="lista-empleados" class="space-y-3">
              @foreach ($empleados as $e)
                <li class="lista-empleado"
                    data-empleado-id="{{ $e['id'] }}"
                    data-estado="{{ $e['estado'] }}"
                    data-progreso="{{ $e['progreso'] }}"
                >
                  <div class="flex items-center gap-3">
                    <div class="avatar-icon">
                      <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900 dark:text-white">{{ $e['nombre'] }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <span class="progreso-text text-sm font-semibold text-gray-800 dark:text-gray-200">
                      {{ $e['progreso'] }}
                    </span>
                    <span class="estado-chip chip chip-fixed {{ $colores[$e['estado']] ?? '' }}">
                      {{ ucfirst(str_replace('_', ' ', $e['estado'])) }}
                    </span>
                    <a
                      href="{{ route('encuestas.show', ['empleado'=>$e['id'],'anio'=>$anioActual,'mes'=>$mesActual]) }}"
                      class="btn btn-primary btn-sm open-encuesta-btn btn-eval-fixed"
                    >Evaluar</a>
                  </div>
                </li>
              @endforeach
            </ul>

            <div id="empty-state" class="text-center py-16 text-gray-500 dark:text-gray-400 hidden">
              <p class="text-lg">No hay empleados para este periodo/criterio.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Estilos reutilizados --}}
  <style>
    :root{
      --brand-indigo:#4338ca; --brand-purple:#6d28d9; --accent-cyan:#0891b2;
      --danger-red:#dc2626; --success-green:#059669;
      --glass-0:rgba(255,255,255,.06); --glass-1:rgba(255,255,255,.04);
      --shadow-soft:0 8px 18px rgba(2,6,23,.10); --shadow-strong:0 18px 48px rgba(2,6,23,.18);
      --radius-lg:.95rem; --radius-md:.9rem; --anim-fast:.12s;

      --status-proceso: #ca8a04;

      --chip-h: 32px;   --chip-w: 105px;
      --btn-h:  32px;   --btn-w:  100px;
    }

    *{box-sizing:border-box} :focus{outline:none}
    .sr-only{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}

    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-weight:700;line-height:1;border-radius:var(--radius-md);
      padding:.7rem 1.15rem;border:1px solid transparent;cursor:pointer;transition:transform var(--anim-fast) ease,box-shadow var(--anim-fast) ease,filter var(--anim-fast) ease,opacity var(--anim-fast) ease,background var(--anim-fast) ease;box-shadow:var(--shadow-soft)
    }
    .btn:hover{transform:translateY(-1px)} .btn:active{transform:translateY(0)} .btn:focus-visible{outline:3px solid rgba(79,70,229,.18);outline-offset:3px}
    .btn-block{width:100%} .btn-sm{padding:.5rem .85rem;font-size:.9rem;border-radius:.75rem}
    .btn-primary{color:#fff;background-image:linear-gradient(90deg,var(--brand-indigo) 0%,var(--brand-purple) 100%);border-color:rgba(0,0,0,.06)}
    .btn-primary:hover{filter:brightness(1.05)}
    .btn-ghost{background:rgba(15,23,42,.06);color:#0f172a;border-color:rgba(15,23,42,.10)}
    @media (prefers-color-scheme:dark){.btn-ghost{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.14)}}

    /* Chips */
    .chip{
      display:inline-flex;align-items:center;justify-content:center;
      padding:.2rem .7rem;border-radius:9999px;font-weight:800;font-size:.8rem;color:#fff;box-shadow:0 4px 14px rgba(2,6,23,.12)
    }
    .chip-success{background-image:linear-gradient(90deg,#16a34a 0%,#059669 100%)}
    .chip-warning{background-image:linear-gradient(90deg,#eab308 0%, var(--status-proceso) 100%)}
    .chip-danger{background-image:linear-gradient(90deg,#ef4444 0%,#dc2626 100%)}

    .kpi-glass{position:relative;padding:1.1rem;border-radius:var(--radius-lg);background:linear-gradient(180deg,var(--glass-0),var(--glass-1));
      border:1px solid rgba(255,255,255,.06);box-shadow:0 10px 28px rgba(2,6,23,.20) inset,0 8px 22px rgba(2,6,23,.12)}
    @media (prefers-color-scheme: light){ .kpi-glass{background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(15,23,42,.04));border:1px solid rgba(15,23,42,.06)} }
    .kpi-row{display:grid;grid-template-columns:1fr auto;align-items:center;gap:.85rem;padding:1rem 1.1rem;border-radius:.8rem;background:rgba(2,6,23,.08)}
    .kpi-row + .kpi-row{margin-top:.75rem} @media (prefers-color-scheme: dark){ .kpi-row{background:rgba(2,6,23,.18)} }
    .kpi-label{font-weight:700;font-size:1rem;letter-spacing:.2px}
    .kpi-value{font-weight:800;font-size:2.15rem;line-height:1;color:#0b1020;letter-spacing:.2px}
    @media (prefers-color-scheme: dark){ .kpi-value{color:#e6eef8;text-shadow:0 1px 0 rgba(0,0,0,.15)} }

    .lista-empleado{padding:.78rem;border-radius:.85rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;background:linear-gradient(180deg,rgba(255,255,255,.64),rgba(255,255,255,.50));transition:transform .12s ease,box-shadow .12s ease;border-left:4px solid transparent}
    .lista-empleado:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(2,6,23,.06)}
    @media (prefers-color-scheme: dark){ .lista-empleado{background:linear-gradient(180deg,rgba(15,23,42,.14),rgba(15,23,42,.06))} }
    .avatar-icon{width:44px;height:44px;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(67,56,202,.06));box-shadow:0 4px 12px rgba(2,6,23,.06);color:#0f172a}

    .status-proceso-label{ color: var(--status-proceso); }

    /* Tamaños fijos compactos para chips/botón Evaluar-Ver */
    .chip-fixed{
      display:inline-flex;align-items:center;justify-content:center;
      height:var(--chip-h);min-width:var(--chip-w);
      padding:0 .6rem;white-space:nowrap;text-align:center;
      overflow:hidden;text-overflow:ellipsis;
    }
    .btn-eval-fixed{
      display:inline-flex;align-items:center;justify-content:center;
      height:var(--btn-h);min-width:var(--btn-w);
      padding:0 .6rem;white-space:nowrap;text-align:center;line-height:1;
      overflow:hidden;text-overflow:ellipsis;
      font-size:.88rem;
    }

    /* Filtros más grandes en móvil y centrados; tamaño previo en sm+ */
    .filters .filter-chip{
      padding:.6rem 1rem;
      font-size:.95rem;
      border-radius:.8rem;
    }
    @media (min-width:640px){
      .filters .filter-chip{
        padding:.5rem .85rem;
        font-size:.9rem;
      }
    }
  </style>

  {{-- JS --}}
  <script>
    (function(){
      'use strict';

      const AUTH_EMAIL = @json($authEmail);
      const ANIO_INIT  = {{ (int)$anioActual }};
      const MES_INIT   = {{ (int)$mesActual }};
      const TOTAL      = {{ (int)$totalPreguntas }};
      const FILTRO     = @json($filtroSel); // null | 'evaluado' | 'en_proceso' | 'no_iniciado'
      // Año/mes reales del sistema
      const ANIO_HOY   = {{ now()->year }};
      const MES_HOY    = {{ now()->month }};

      // Periodo (si hay sidebar): actualización automática
      const anioEl = document.getElementById('filtro-anio');
      const mesEl  = document.getElementById('filtro-mes');

      // Guardamos las opciones originales de mes (1..12) para reconstruirlas
      const OPCIONES_MESES_ORIGINALES = mesEl
        ? Array.from(mesEl.options).map(o => ({
            value: o.value,
            text:  o.text,
          }))
        : [];

      function limitarMesesPorAnio() {
        if (!anioEl || !mesEl || OPCIONES_MESES_ORIGINALES.length === 0) return;

        const anioSel   = parseInt(anioEl.value || ANIO_INIT, 10);
        const limiteMes = (anioSel === ANIO_HOY) ? MES_HOY : 12; // si es el año actual, hasta el mes actual
        const mesPrevio = parseInt(mesEl.value || MES_INIT, 10);

        mesEl.innerHTML = '';

        OPCIONES_MESES_ORIGINALES.forEach(optData => {
          const v = parseInt(optData.value, 10);
          if (Number.isNaN(v)) return;

          if (v > limiteMes) return; // no dejamos ir al futuro

          const opt = document.createElement('option');
          opt.value = optData.value;
          opt.textContent = optData.text;

          if (v === mesPrevio && v <= limiteMes) {
            opt.selected = true;
          }

          mesEl.appendChild(opt);
        });

        if (!mesEl.value) {
          mesEl.value = String(limiteMes);
        }
      }

      function buildUrl(anio, mes){
        const url = new URL(window.location.href);
        url.searchParams.set('anio', anio);
        url.searchParams.set('mes', mes);
        if (FILTRO) url.searchParams.set('filtro', FILTRO);
        else url.searchParams.delete('filtro');
        return url.toString();
      }

      function navigate(){
        const a = anioEl?.value || ANIO_INIT;
        const m = mesEl?.value  || MES_INIT;
        window.location.href = buildUrl(a, m);
      }

      // Limitar meses al cargar
      limitarMesesPorAnio();

      // Al cambiar año: ajustamos meses + navegamos
      anioEl?.addEventListener('change', function(){
        limitarMesesPorAnio();
        navigate();
      });

      // Al cambiar solo mes: navegamos
      mesEl?.addEventListener('change', navigate);

      // ---- Borradores ----
      function draftKey(empId){
        return `encuesta_${AUTH_EMAIL}_${empId}_${anioEl?.value||ANIO_INIT}_${mesEl?.value||MES_INIT}`;
      }
      function readDraft(empId){
        try{
          const raw = sessionStorage.getItem(draftKey(empId));
          return raw ? JSON.parse(raw) : null;
        }catch(_){ return null; }
      }
      function respuestasContestadas(d){
        if (!d || !Array.isArray(d.respuestas)) return 0;
        return d.respuestas.filter(r => r && r.puntaje !== null && r.puntaje !== '' && r.puntaje !== undefined).length;
      }

      function applyStateFusion(){
        const list  = document.getElementById('lista-empleados');
        const empty = document.getElementById('empty-state');
        if (!list) return;

        const items = Array.from(list.querySelectorAll('li.lista-empleado'));
        let shown = 0;

        // KPIs (si sidebar está)
        const totalEl = document.getElementById('kpi-total');
        const evalEl  = document.getElementById('kpi-evaluados');
        const procEl  = document.getElementById('kpi-proceso');
        const noiniEl = document.getElementById('kpi-noiniciado');

        let cTotal=0, cEval=0, cProc=0, cNoIni=0;

        items.forEach(li=>{
          const id = parseInt(li.dataset.empleadoId, 10);
          const estadoSrv = String(li.dataset.estado || 'no_iniciado');
          const progEl = li.querySelector('.progreso-text');
          const chipEl = li.querySelector('.estado-chip');
          const btnEl  = li.querySelector('.open-encuesta-btn');

          let estado = estadoSrv;
          let prog   = li.dataset.progreso || '';

          // No mezclar borrador si ya está evaluado
          if (estadoSrv !== 'evaluado'){
            const d = readDraft(id);
            const filled = respuestasContestadas(d);
            if (filled > 0){
              estado = 'en_proceso';
              prog   = `${filled}/${TOTAL}`;
            } else {
              estado = 'no_iniciado';
              prog   = `0/${TOTAL}`;
            }
          }

          // pintar UI
          if (progEl) progEl.textContent = prog;
          if (chipEl){
            chipEl.classList.remove('chip-success','chip-warning','chip-danger');
            const estadoTexto = estado.replace('_', ' ');
            chipEl.textContent = estadoTexto.charAt(0).toUpperCase() + estadoTexto.slice(1);
            if (estado === 'evaluado')   chipEl.classList.add('chip-success');
            if (estado === 'en_proceso') chipEl.classList.add('chip-warning');
            if (estado === 'no_iniciado') chipEl.classList.add('chip-danger');
          }

          // botón Evaluar/Ver
          if (btnEl) {
            if (estado === 'evaluado') {
              btnEl.textContent = 'Ver';
            } else {
              btnEl.textContent = 'Evaluar';
            }
          }

          // filtro
          const show = (!FILTRO) || (estado === FILTRO);
          li.style.display = show ? '' : 'none';
          if (show) shown++;

          // KPIs
          cTotal++;
          if (estado === 'evaluado') cEval++;
          else if (estado === 'en_proceso') cProc++;
          else cNoIni++;
        });

        if (empty) empty.classList.toggle('hidden', shown > 0);

        if (totalEl){ totalEl.textContent = String(cTotal); }
        if (evalEl){  evalEl.textContent  = String(cEval); }
        if (procEl){  procEl.textContent  = String(cProc); }
        if (noiniEl){ noiniEl.textContent = String(cNoIni); }
      }

      // ---- KPIs clickables → filtros ----
      function initKpiClicks(){
        const filterMap = {
          'no_iniciado': 'filter-no_iniciado',
          'en_proceso':  'filter-en_proceso',
          'evaluado':    'filter-evaluado',
        };

        document.querySelectorAll('.kpi-card[data-filter-target]').forEach(card => {
          card.addEventListener('click', () => {
            const key   = card.getAttribute('data-filter-target');
            const btnId = filterMap[key];
            if (!btnId) return;

            const btn = document.getElementById(btnId);
            if (btn) btn.click();
          });
        });
      }

      document.addEventListener('DOMContentLoaded', () => {
        applyStateFusion();
        initKpiClicks();
      });
      window.addEventListener('load', applyStateFusion);
    })();
  </script>
</x-app-layout>
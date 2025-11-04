<x-app-layout>
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
              <h3 class="text-white font-extrabold text-3xl tracking-tight">Encuestas</h3>
              <p class="text-white text-xl leading-snug">Periodo seleccionado</p>
            </div>
          </div>

          <div class="p-5 space-y-5">
            {{-- Selector de periodo --}}
            <section aria-label="Periodo" class="flex flex-wrap items-end gap-3 mb-2">
              <div>
                <label for="filtro-anio" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Año</label>
                <select id="filtro-anio"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 
                               focus:border-indigo-500 focus:ring-indigo-500">
                  @for ($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}" @selected($y==$anioActual)>{{ $y }}</option>
                  @endfor
                </select>
              </div>
              <div>
                <label for="filtro-mes" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Mes</label>
                <select id="filtro-mes"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 
                               focus:border-indigo-500 focus:ring-indigo-500">
                  @foreach ($meses as $num => $m)
                    <option value="{{ $num }}" @selected($num==$mesActual)>{{ $m }}</option>
                  @endforeach
                </select>
              </div>
              <a id="btn-aplicar" href="{{ route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual]) }}"
                 class="btn btn-primary btn-sm">Aplicar</a>
            </section>

            {{-- KPIs --}}
            <section aria-label="Indicadores clave">
              <div class="kpi-glass">
                <div class="kpi-row">
                  <span class="kpi-label text-gray-900 dark:text-gray-100">Total</span>
                  <span id="kpi-total" class="kpi-value">{{ $kpi_total }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-emerald-600 dark:text-emerald-400">Evaluados</span>
                  <span id="kpi-evaluados" class="kpi-value">{{ $kpi_evaluados }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-yellow-600 dark:text-yellow-400">En proceso</span>
                  <span id="kpi-proceso" class="kpi-value">{{ $kpi_en_proceso }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-rose-600 dark:text-red-400">No iniciados</span>
                  <span id="kpi-noiniciado" class="kpi-value">{{ $kpi_no_iniciado }}</span>
                </div>
              </div>
            </section>

            <p class="text-[13px] text-gray-600 dark:text-gray-400">
              Completa las {{ $totalPreguntas }} preguntas para enviar; si no, queda como borrador.
            </p>
          </div>
        </aside>
        @endif

        {{-- Contenido principal --}}
        <div class="flex-1">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-5 border border-gray-200 dark:border-gray-700">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-6">
              <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">
                Empleados — {{ $meses[$mesActual] }} {{ $anioActual }}
              </h2>

              {{-- Filtros rápidos --}}
              <div class="flex flex-wrap items-center gap-2 sm:ml-auto">
                @php
                  $base = fn($f)=>route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual,'filtro'=>$f]);
                @endphp
                <a href="{{ $base(null) }}"
                   class="btn btn-sm {{ $filtroSel ? 'btn-ghost' : 'btn-primary' }}">Todos</a>
                <a href="{{ $base('evaluado') }}"
                   class="btn btn-sm {{ $filtroSel==='evaluado' ? 'btn-primary' : 'btn-ghost' }}">Evaluados</a>
                <a href="{{ $base('en_proceso') }}"
                   class="btn btn-sm {{ $filtroSel==='en_proceso' ? 'btn-primary' : 'btn-ghost' }}">En proceso</a>
                <a href="{{ $base('no_iniciado') }}"
                   class="btn btn-sm {{ $filtroSel==='no_iniciado' ? 'btn-primary' : 'btn-ghost' }}">No iniciados</a>
              </div>
            </div>

            {{-- SIEMPRE renderizamos lista + vacío; el JS decide qué mostrar --}}
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
                    <span class="estado-chip chip {{ $colores[$e['estado']] ?? '' }}">
                      {{ ucfirst(str_replace('_', ' ', $e['estado'])) }}
                    </span>
                    <a
                      href="{{ route('encuestas.show', ['empleado'=>$e['id'],'anio'=>$anioActual,'mes'=>$mesActual]) }}"
                      class="btn btn-primary btn-sm"
                      title="Abrir encuesta"
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
      --modal-bg-light:rgba(255,255,255,.96); --modal-bg-dark:rgba(8,10,20,.92);
      --glass-0:rgba(255,255,255,.06); --glass-1:rgba(255,255,255,.04);
      --shadow-soft:0 8px 18px rgba(2,6,23,.10); --shadow-strong:0 18px 48px rgba(2,6,23,.18);
      --radius-lg:.95rem; --radius-md:.9rem; --anim-fast:.12s;
    }
    *{box-sizing:border-box} :focus{outline:none} .sr-only{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-weight:700;line-height:1;border-radius:var(--radius-md);
      padding:.7rem 1.15rem;border:1px solid transparent;cursor:pointer;transition:transform var(--anim-fast) ease,box-shadow var(--anim-fast) ease,filter var(--anim-fast) ease,opacity var(--anim-fast) ease,background var(--anim-fast) ease;box-shadow:var(--shadow-soft)}
    .btn:hover{transform:translateY(-1px)} .btn:active{transform:translateY(0)} .btn:focus-visible{outline:3px solid rgba(79,70,229,.18);outline-offset:3px}
    .btn-block{width:100%} .btn-sm{padding:.5rem .85rem;font-size:.9rem;border-radius:.75rem}
    .btn-primary{color:#fff;background-image:linear-gradient(90deg,var(--brand-indigo) 0%,var(--brand-purple) 100%);border-color:rgba(0,0,0,.06)}
    .btn-primary:hover{filter:brightness(1.05)}
    .btn-ghost{background:rgba(15,23,42,.06);color:#0f172a;border-color:rgba(15,23,42,.10)}
    @media (prefers-color-scheme:dark){.btn-ghost{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.14)}}
    .chip{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .75rem;border-radius:9999px;font-weight:800;font-size:.8rem;color:#fff;box-shadow:0 4px 14px rgba(2,6,23,.12)}
    .chip-success{background-image:linear-gradient(90deg,#16a34a 0%,#059669 100%)} 
    .chip-warning{background-image:linear-gradient(90deg,#eab308 0%,#ca8a04 100%)} 
    .chip-danger{background-image:linear-gradient(90deg,#ef4444 0%,#dc2626 100%)}
    .kpi-glass{position:relative;padding:1.1rem;border-radius:var(--radius-lg);background:linear-gradient(180deg,var(--glass-0),var(--glass-1));
      border:1px solid rgba(255,255,255,.06);box-shadow:0 10px 28px rgba(2,6,23,.20) inset,0 8px 22px rgba(2,6,23,.12)}
    @media (prefers-color-scheme: light){ .kpi-glass{background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(15,23,42,.04));border:1px solid rgba(15,23,42,.06)} }
    .kpi-row{display:grid;grid-template-columns:1fr auto;align-items:center;gap:.85rem;padding:1rem 1.1rem;border-radius:.8rem;background:rgba(2,6,23,.08)}
    .kpi-row + .kpi-row{margin-top:.75rem} @media (prefers-color-scheme: dark){ .kpi-row{background:rgba(2,6,23,.18)} }
    .kpi-label{font-weight:700;font-size:1rem;letter-spacing:.2px} .kpi-value{font-weight:800;font-size:2.15rem;line-height:1;color:#0b1020;letter-spacing:.2px}
    @media (prefers-color-scheme: dark){ .kpi-value{color:#e6eef8;text-shadow:0 1px 0 rgba(0,0,0,.15)} }
    .lista-empleado{padding:.78rem;border-radius:.85rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;background:linear-gradient(180deg,rgba(255,255,255,.64),rgba(255,255,255,.50));transition:transform .12s ease,box-shadow .12s ease;border-left:4px solid transparent}
    .lista-empleado:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(2,6,23,.06)}
    @media (prefers-color-scheme: dark){ .lista-empleado{background:linear-gradient(180deg,rgba(15,23,42,.14),rgba(15,23,42,.06))} }
    .avatar-icon{width:44px;height:44px;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(67,56,202,.06));box-shadow:0 4px 12px rgba(2,6,23,.06);color:#0f172a}
    .hidden{display:none}
  </style>

  {{-- JS: periodo + fusión de borradores, KPIs y filtro dinámico --}}
  <script>
    (function(){
      'use strict';

      const AUTH_EMAIL = @json($authEmail);
      const ANIO       = {{ (int)$anioActual }};
      const MES        = {{ (int)$mesActual }};
      const TOTAL      = {{ (int)$totalPreguntas }};
      const FILTRO     = @json($filtroSel); // null | 'evaluado' | 'en_proceso' | 'no_iniciado'

      // Periodo (si hay sidebar)
      const anioEl = document.getElementById('filtro-anio');
      const mesEl  = document.getElementById('filtro-mes');
      const btn    = document.getElementById('btn-aplicar');
      function upd() {
        if (!btn) return;
        const url = new URL(btn.href);
        url.searchParams.set('anio', anioEl.value);
        url.searchParams.set('mes', mesEl.value);
        btn.href = url.toString();
      }
      anioEl?.addEventListener('change', upd);
      mesEl?.addEventListener('change', upd);

      // Draft helpers
      function draftKey(empId){ return `encuesta_${AUTH_EMAIL}_${empId}_${ANIO}_${MES}`; }
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
        const list = document.getElementById('lista-empleados');
        const empty = document.getElementById('empty-state');
        if (!list) return;

        const items = Array.from(list.querySelectorAll('li.lista-empleado'));
        let shown = 0;

        // KPIs sólo si el sidebar está presente
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

          let estado = estadoSrv;
          let prog   = li.dataset.progreso || '';

          // CORRECCIÓN: Solo actualizar estado si hay borradores para empleados no evaluados
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
            // Solo primera letra en mayúscula
            const estadoTexto = estado.replace('_', ' ');
            chipEl.textContent = estadoTexto.charAt(0).toUpperCase() + estadoTexto.slice(1);
            if (estado === 'evaluado')   chipEl.classList.add('chip-success');
            if (estado === 'en_proceso') chipEl.classList.add('chip-warning');
            if (estado === 'no_iniciado') chipEl.classList.add('chip-danger');
          }

          // filtro - CORRECCIÓN: Mostrar correctamente según el filtro seleccionado
          const show = (!FILTRO) || (estado === FILTRO);
          li.style.display = show ? '' : 'none';
          if (show) shown++;

          // KPIs - contabilizar correctamente
          cTotal++;
          if (estado === 'evaluado') cEval++;
          else if (estado === 'en_proceso') cProc++;
          else cNoIni++;
        });

        // vacío dinámico
        if (empty) empty.classList.toggle('hidden', shown > 0);

        // KPIs (si sidebar está)
        if (totalEl){ totalEl.textContent = String(cTotal); }
        if (evalEl){  evalEl.textContent  = String(cEval); }
        if (procEl){  procEl.textContent  = String(cProc); }
        if (noiniEl){ noiniEl.textContent = String(cNoIni); }
      }

      document.addEventListener('DOMContentLoaded', applyStateFusion);
      // También aplicar después de que la página esté completamente cargada
      window.addEventListener('load', applyStateFusion);
    })();
  </script>
</x-app-layout>
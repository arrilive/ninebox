{{-- resources/views/encuestas/empleados-index.blade.php --}}
<x-app-layout>
  @php
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $anioActual = $anio ?? now()->year;
    $mesActual  = $mes ?? now()->month;
    $filtroSel  = request('filtro');
  @endphp

  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar / KPIs --}}
        <aside class="lg:w-80 flex-shrink-0 rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40
                      bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg lg:sticky lg:top-4 lg:overflow-hidden">
          <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative">
              <h3 class="text-white font-extrabold text-3xl tracking-tight">Encuestas</h3>
              <p class="text-white/90 text-lg leading-snug">Periodo seleccionado</p>
            </div>
          </div>

          <div class="p-7 space-y-7">
            {{-- Selector de periodo --}}
            <section class="flex flex-wrap items-end gap-3 mb-2">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Año</label>
                <select id="filtro-anio" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                  @for ($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}" @selected($y==$anioActual)>{{ $y }}</option>
                  @endfor
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Mes</label>
                <select id="filtro-mes" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
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
                  <span class="kpi-value">{{ $kpi_total }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-emerald-600 dark:text-emerald-400">Evaluados</span>
                  <span class="kpi-value">{{ $kpi_evaluados }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-blue-600 dark:text-cyan-400">En proceso</span>
                  <span class="kpi-value">{{ $kpi_en_proceso }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-rose-600 dark:text-red-400">No iniciados</span>
                  <span class="kpi-value">{{ $kpi_no_iniciado }}</span>
                </div>
              </div>
            </section>

            {{-- Ayuda --}}
            <p class="text-[13px] text-gray-600 dark:text-gray-400">
              Filtra empleados y entra a la encuesta. Si completas las {{ $totalPreguntas }} preguntas, se envía y queda cerrada; si no, se guarda como borrador. 🌱
            </p>
          </div>
        </aside>

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

            {{-- Lista --}}
            @php $colores = ['evaluado'=>'chip-success', 'en_proceso'=>'chip-info', 'no_iniciado'=>'']; @endphp

            @if (count($empleados) === 0)
              <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <p class="text-lg">No hay empleados para este periodo/criterio.</p>
              </div>
            @else
              <ul class="space-y-3">
                @foreach ($empleados as $e)
                  <li class="lista-empleado">
                    <div class="flex items-center gap-3">
                      <div class="avatar-icon">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $e['nombre'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $e['departamento_nombre'] }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-3">
                      <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $e['progreso'] }}</span>
                      @if ($e['estado']!=='no_iniciado')
                        <span class="chip {{ $colores[$e['estado']] ?? '' }}">{{ str_replace('_',' ',$e['estado']) }}</span>
                      @else
                        <span class="chip" style="background:linear-gradient(90deg,#6b7280,#4b5563);">no iniciado</span>
                      @endif
                      <a
                        href="{{ route('encuestas.show', ['empleado'=>$e['id'],'anio'=>$anioActual,'mes'=>$mesActual]) }}"
                        class="btn btn-primary btn-sm"
                        title="Abrir encuesta"
                      >Evaluar</a>
                    </div>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Estilos reutilizados --}}
  <style>
    :root{ --brand-indigo:#4338ca; --brand-purple:#6d28d9; --glass-0:rgba(255,255,255,.06); --glass-1:rgba(255,255,255,.04); --radius-lg:.95rem; --radius-md:.9rem; --shadow-soft:0 8px 18px rgba(2,6,23,.10); }
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-weight:700;line-height:1;border-radius:var(--radius-md);padding:.7rem 1.15rem;border:1px solid transparent;cursor:pointer;transition:.12s;box-shadow:var(--shadow-soft)}
    .btn:hover{transform:translateY(-1px)} .btn-sm{padding:.5rem .85rem;font-size:.9rem;border-radius:.75rem}
    .btn-primary{color:#fff;background-image:linear-gradient(90deg,var(--brand-indigo),var(--brand-purple))}
    .btn-ghost{background:rgba(15,23,42,.06);color:#0f172a;border-color:rgba(15,23,42,.10)}
    @media (prefers-color-scheme:dark){.btn-ghost{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.14)}}
    .kpi-glass{position:relative;padding:1.1rem;border-radius:var(--radius-lg);background:linear-gradient(180deg,var(--glass-0),var(--glass-1));border:1px solid rgba(255,255,255,.06)}
    @media (prefers-color-scheme:light){.kpi-glass{background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(15,23,42,.04));border:1px solid rgba(15,23,42,.06)}}
    .kpi-row{display:grid;grid-template-columns:1fr auto;gap:.85rem;align-items:center;padding:1rem 1.1rem;border-radius:.8rem;background:rgba(2,6,23,.08)}
    @media (prefers-color-scheme:dark){.kpi-row{background:rgba(2,6,23,.18)}}
    .kpi-label{font-weight:700} .kpi-value{font-weight:800;font-size:2rem}
    .chip{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .75rem;border-radius:9999px;font-weight:800;font-size:.8rem;color:#fff;box-shadow:0 4px 14px rgba(2,6,23,.12)}
    .chip-success{background-image:linear-gradient(90deg,#16a34a,#059669)} .chip-info{background-image:linear-gradient(90deg,#2563eb,#06b6d4)}
    .lista-empleado{padding:.78rem;border-radius:.85rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;background:linear-gradient(180deg,rgba(255,255,255,.64),rgba(255,255,255,.50));border-left:4px solid transparent}
    .lista-empleado:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(2,6,23,.06)}
    @media (prefers-color-scheme:dark){.lista-empleado{background:linear-gradient(180deg,rgba(15,23,42,.14),rgba(15,23,42,.06))}}
    .avatar-icon{width:44px;height:44px;border-radius:9999px;display:inline-grid;place-items:center;background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(67,56,202,.06))}
  </style>

  {{-- JS pequeño para actualizar el link "Aplicar" --}}
  <script>
    const anioEl = document.getElementById('filtro-anio');
    const mesEl  = document.getElementById('filtro-mes');
    const btn    = document.getElementById('btn-aplicar');
    function upd() {
      const url = new URL(btn.href);
      url.searchParams.set('anio', anioEl.value);
      url.searchParams.set('mes', mesEl.value);
      btn.href = url.toString();
    }
    anioEl?.addEventListener('change', upd);
    mesEl?.addEventListener('change', upd);
  </script>
</x-app-layout>

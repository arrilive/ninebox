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
          $mesInicio = request('mes_inicio', request('mes', now()->month));
          $mesFin = request('mes_fin', request('mes', now()->month));
          $mesActual = $mesInicio; // Para compatibilidad
          $encuestaEmpleadoBase = url('/encuestas');
          $usuarioPresente = isset($usuario);
          $esSuper = $usuarioPresente && method_exists($usuario, 'esSuperusuario') && $usuario->esSuperusuario();
          $esDueno = $usuarioPresente && method_exists($usuario, 'esDueno') && $usuario->esDueno();
          $esJefe  = $usuarioPresente && method_exists($usuario, 'esJefe') && $usuario->esJefe();
          $departamentoFiltro = $departamentoFiltro ?? request('departamento', 'todos');
          $departamentosSeleccionados = $departamentosSeleccionados ?? [];
          $rolFiltro = $rolFiltro ?? request('rol', 'todos');
        @endphp

        {{-- Sidebar resumen --}}
        <aside class="lg:w-80 flex-shrink-0 rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40
                 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg lg:sticky lg:top-4 lg:overflow-hidden">
          <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative">
              <h3 class="text-white font-extrabold text-3xl tracking-tight">Resumen</h3>

              @if ($esSuper)
                  {{-- Admin --}}
                  <p class="text-white text-xl leading-snug"></p>
              @elseif ($esDueno)
                  {{-- Dueño --}}
                  <p class="text-white text-xl leading-snug">
                      {{ $usuario->user_name }}
                  </p>
              @else
                  {{-- Jefe --}}
                  <p class="text-white text-xl leading-snug">
                      {{ optional($usuario->departamento)->nombre_departamento ?? 'Sin departamento' }}
                  </p>
              @endif
            </div>
          </div>
          <div class="p-7 space-y-7">
          @php
            $primerAnio = 2025;
            $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
            $mesInicioNombre = $meses[$mesInicio] ?? 'Enero';
            $mesFinNombre = $meses[$mesFin] ?? 'Enero';
            
            // Texto del rango de meses considerando años
            $anioInicio = $anioInicio ?? $anioActual;
            $anioFin = $anioFin ?? $anioActual;
            
            if ($anioInicio === $anioFin) {
              // Mismo año
              $rangoTexto = ($mesInicio === $mesFin) 
                ? $mesInicioNombre . ' ' . $anioInicio
                : $mesInicioNombre . ' - ' . $mesFinNombre . ' ' . $anioInicio;
            } else {
              // Rango que cruza años
              $rangoTexto = $mesInicioNombre . ' ' . $anioInicio . ' - ' . $mesFinNombre . ' ' . $anioFin;
            }
            
            // Texto del rango de años
            $rangoAnioTexto = ($anioInicio === $anioFin) 
              ? (string)$anioInicio
              : $anioInicio . ' - ' . $anioFin;
          @endphp

            {{-- Filtros --}}
            <section aria-label="Filtros" class="space-y-4 border-b border-gray-200 dark:border-gray-700 pb-6">
              {{-- Rango de meses --}}
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1.5">Rango de meses</label>
                <div class="relative">
                  <button type="button" id="btn-rango-meses" 
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700
                               focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-left flex items-center justify-between
                               bg-white dark:bg-gray-900/70 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors
                               text-gray-900 dark:text-white">
                    <span id="rango-meses-texto" class="text-gray-900 dark:text-white">{{ $rangoTexto }}</span>
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                  
                  {{-- Panel desplegable del rango --}}
                  <div id="panel-rango-meses" class="hidden absolute z-50 mt-2 left-0 right-0 min-w-[280px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4">
                    <div class="space-y-4">
                      <div>
                        <label for="filtro-mes-inicio" class="block text-xs font-medium text-gray-700 dark:text-white mb-1.5">Mes Inicio</label>
                        <select id="filtro-mes-inicio"
                          class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                                       focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-sm">
                          @foreach ($meses as $num => $mes)
                            <option value="{{ $num }}" {{ (int)$num === (int)$mesInicio ? 'selected' : '' }}>{{ $mes }}</option>
                          @endforeach
                        </select>
                      </div>
                      
                      <div>
                        <label for="filtro-mes-fin" class="block text-xs font-medium text-gray-700 dark:text-white mb-1.5">Mes Fin</label>
                        <select id="filtro-mes-fin"
                          class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                                       focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-sm">
                          @foreach ($meses as $num => $mes)
                            <option value="{{ $num }}" {{ (int)$num === (int)$mesFin ? 'selected' : '' }}>{{ $mes }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Filtros adicionales para Admin/Dueño --}}
              @if ($esSuper || $esDueno)
                {{-- Departamento --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1.5">Departamento</label>
                  <div class="relative">
                    <button type="button" id="btn-departamento" 
                      class="w-full rounded-lg border border-gray-300 dark:border-gray-700
                                 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-left flex items-center justify-between
                                 bg-white dark:bg-gray-900/70 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors
                                 text-gray-900 dark:text-white">
                      <span id="departamento-texto" class="text-gray-900 dark:text-white">
                        @if(empty($departamentosSeleccionados))
                          Todos los departamentos
                        @elseif(count($departamentosSeleccionados) === 1)
                          @foreach ($departamentos ?? [] as $depto)
                            @if((string)$depto->id === (string)$departamentosSeleccionados[0])
                              {{ $depto->nombre_departamento }}
                              @break
                            @endif
                          @endforeach
                        @else
                          {{ count($departamentosSeleccionados) }} departamentos seleccionados
                        @endif
                      </span>
                      <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </button>
                    
                    {{-- Panel desplegable del departamento con checkboxes --}}
                    <div id="panel-departamento" class="hidden absolute z-50 mt-2 left-0 right-0 min-w-[280px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3 max-h-60 overflow-y-auto">
                      <div class="space-y-2">
                        <label class="flex items-center px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                          <input type="checkbox" id="check-todos-departamentos" 
                            class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            {{ empty($departamentosSeleccionados) ? 'checked' : '' }}>
                          <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Todos los departamentos</span>
                        </label>
                        @foreach ($departamentos ?? [] as $depto)
                          <label class="flex items-center px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <input type="checkbox" class="check-departamento w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                              value="{{ $depto->id }}" 
                              data-text="{{ $depto->nombre_departamento }}"
                              {{ in_array((string)$depto->id, array_map('strval', $departamentosSeleccionados)) ? 'checked' : '' }}>
                            <span class="ml-3 text-sm text-gray-900 dark:text-white">{{ $depto->nombre_departamento }}</span>
                          </label>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
                
                {{-- Rol --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1.5">Rol</label>
                  <div class="relative">
                    <button type="button" id="btn-rol" 
                      class="w-full rounded-lg border border-gray-300 dark:border-gray-700
                                 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-left flex items-center justify-between
                                 bg-white dark:bg-gray-900/70 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors
                                 text-gray-900 dark:text-white">
                      <span id="rol-texto" class="text-gray-900 dark:text-white">
                        @if($rolFiltro === 'todos')
                          Todos los roles
                        @elseif($rolFiltro === 'jefe')
                          Jefes
                        @elseif($rolFiltro === 'empleado')
                          Empleados
                        @endif
                      </span>
                      <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </button>
                    
                    {{-- Panel desplegable del rol --}}
                    <div id="panel-rol" class="hidden absolute z-50 mt-2 left-0 right-0 min-w-[200px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-2">
                      <button type="button" class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-900 dark:text-white {{ $rolFiltro === 'todos' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' }}"
                        data-value="todos" data-text="Todos los roles">
                        Todos los roles
                      </button>
                      <button type="button" class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-900 dark:text-white {{ $rolFiltro === 'jefe' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' }}"
                        data-value="jefe" data-text="Jefes">
                        Jefes
                      </button>
                      <button type="button" class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-900 dark:text-white {{ $rolFiltro === 'empleado' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' }}"
                        data-value="empleado" data-text="Empleados">
                        Empleados
                      </button>
                    </div>
                  </div>
                </div>
              @endif
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

            {{-- CTA lista empleados --}}
            @if ($usuarioPresente && ($esJefe || $esDueno))
              <div class="mt-2 space-y-2">
                <a
                  id="btn-por-evaluar"
                  href="{{ route('encuestas.empleados', ['anio' => $anioActual, 'mes' => $mesActual]) }}"
                  class="btn btn-primary btn-block"
                >
                  {{ $esDueno ? 'Evaluar' : 'Evaluar empleados' }}
                </a>
                <p class="text-[13px] text-center mt-1 text-gray-700 dark:text-gray-400">
                  {{ $esDueno
                      ? 'Abre la lista para iniciar o continuar encuestas.'
                      : 'Abre la lista de empleados para iniciar o continuar encuestas.'
                  }}
                </p>
              </div>
            @endif
          </div>
        </aside>

        {{-- Nine-Box + hotspots --}}
        <div class="flex-1">
          {{-- Filtro de rango de años (separado, solo admin/dueño) --}}
          @if ($esSuper || $esDueno)
            <div class="flex justify-end mb-4">
              <div class="relative">
                @php
                  $primerAnio = 2025;
                @endphp
                <button type="button" id="btn-rango-anios" 
                  class="rounded-lg border border-gray-300 dark:border-gray-700
                             focus:border-indigo-500 focus:ring-indigo-500 py-2 px-4 text-left flex items-center justify-between gap-2
                             bg-white dark:bg-gray-900/70 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors text-sm font-medium
                             text-gray-900 dark:text-white">
                  <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <span id="rango-anios-texto" class="text-gray-900 dark:text-white">{{ $rangoAnioTexto }}</span>
                  <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                
                {{-- Panel desplegable del rango de años --}}
                <div id="panel-rango-anios" class="hidden absolute z-50 mt-2 right-0 min-w-[280px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4">
                  <div class="space-y-4">
                    <div>
                      <label for="filtro-anio-inicio" class="block text-xs font-medium text-gray-700 dark:text-white mb-1.5">Año Inicio</label>
                      <select id="filtro-anio-inicio"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-sm">
                        @for ($i = now()->year; $i >= $primerAnio; $i--)
                          <option value="{{ $i }}" {{ (int)$i === (int)$anioInicio ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                      </select>
                    </div>
                    
                    <div>
                      <label for="filtro-anio-fin" class="block text-xs font-medium text-gray-700 dark:text-white mb-1.5">Año Fin</label>
                      <select id="filtro-anio-fin"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 text-sm">
                        @for ($i = now()->year; $i >= $primerAnio; $i--)
                          <option value="{{ $i }}" {{ (int)$i === (int)$anioFin ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @else
            {{-- Para jefes, mantener el botón simple de año --}}
            <div class="flex justify-end mb-4">
              <div class="relative">
                @php
                  $primerAnio = 2025;
                @endphp
                <button type="button" id="btn-anio" 
                  class="rounded-lg border border-gray-300 dark:border-gray-700
                             focus:border-indigo-500 focus:ring-indigo-500 py-2 px-4 text-left flex items-center justify-between gap-2
                             bg-white dark:bg-gray-900/70 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-colors text-sm font-medium
                             text-gray-900 dark:text-white">
                  <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <span id="anio-texto" class="text-gray-900 dark:text-white">{{ $anioActual }}</span>
                  <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                
                {{-- Panel desplegable del año --}}
                <div id="panel-anio" class="hidden absolute z-50 mt-2 right-0 min-w-[120px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-2 max-h-60 overflow-y-auto">
                  @for ($i = now()->year; $i >= $primerAnio; $i--)
                    <button type="button" class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-900 dark:text-white {{ (int)$i === (int)$anioActual ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' }}"
                      data-value="{{ $i }}" data-text="{{ $i }}">
                      {{ $i }}
                    </button>
                  @endfor
                </div>
              </div>
            </div>
          @endif

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
              <div id="modal-header-section" class="flex items-center gap-3 mb-4 select-none">
                <div class="badge-icon bg-gradient-to-r from-green-600 to-emerald-600" id="badge-asignados">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 0 0118 0z"/>
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

    /* Paneles desplegables */
    #panel-rango-meses, #panel-anio, #panel-rango-anios, #panel-departamento, #panel-rol{
      animation: slideDown 0.2s ease-out;
    }
    @keyframes slideDown{
      from{opacity:0;transform:translateY(-8px);}
      to{opacity:1;transform:translateY(0);}
    }
    #panel-rango-meses select option:disabled{
      color: #9ca3af;
      font-style: italic;
    }
    .filtro-option{
      font-size: 0.875rem;
    }
  </style>

  {{-- JS --}}
  <script>
  (function () {
    'use strict';

    const ES_GLOBAL      = @json($esSuper || $esDueno);
    const ASIG           = @json($asignacionesActuales ?? []);
    const ENCUESTA_BASE  = @json($encuestaEmpleadoBase);
    const ANIO_ACTUAL    = @json($anioActual);
    const ANIO_INICIO    = @json($anioInicio ?? $anioActual);
    const ANIO_FIN       = @json($anioFin ?? $anioActual);
    const MES_INICIO     = @json($mesInicio ?? $mesActual);
    const MES_FIN        = @json($mesFin ?? $mesActual);
    const MES_ACTUAL     = @json($mesActual);
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

    // Variables globales para almacenar valores de filtros
    let filtroAnio = ANIO_ACTUAL;
    let filtroAnioInicio = ANIO_INICIO;
    let filtroAnioFin = ANIO_FIN;
    let filtroDepartamento = @json($departamentoFiltro ?? 'todos');
    // Convertir a strings para que coincidan con los valores de los checkboxes
    // Usar Set para evitar duplicados y asegurar que todos sean strings
    const deptosRaw = @json($departamentosSeleccionados ?? []);
    let departamentosSeleccionados = Array.from(new Set(deptosRaw.map(d => String(d))));
    let filtroRol = @json($rolFiltro ?? 'todos');

    function getPeriodo(){
      const mesInicioSel = document.getElementById('filtro-mes-inicio')?.value ?? MES_INICIO;
      const mesFinSel = document.getElementById('filtro-mes-fin')?.value ?? MES_FIN;
      
      // Si es admin/dueño, obtener rangos de años
      let anioInicioSel = filtroAnioInicio;
      let anioFinSel = filtroAnioFin;
      
      if (ES_GLOBAL) {
        const anioInicioEl = document.getElementById('filtro-anio-inicio');
        const anioFinEl = document.getElementById('filtro-anio-fin');
        if (anioInicioEl) anioInicioSel = parseInt(anioInicioEl.value, 10) || filtroAnioInicio;
        if (anioFinEl) anioFinSel = parseInt(anioFinEl.value, 10) || filtroAnioFin;
      }
      
      return { 
        anio: String(anioInicioSel).trim(), // Para compatibilidad
        anio_inicio: String(anioInicioSel).trim(),
        anio_fin: String(anioFinSel).trim(),
        mes_inicio: String(mesInicioSel).trim(),
        mes_fin: String(mesFinSel).trim()
      };
    }
    
    function reloadWithPeriodo() {
      const { anio, anio_inicio, anio_fin, mes_inicio, mes_fin } = getPeriodo();
      const url = new URL(window.location.href);
      
      // Si es admin/dueño, usar rangos de años
      if (ES_GLOBAL) {
        url.searchParams.set('anio_inicio', anio_inicio);
        url.searchParams.set('anio_fin', anio_fin);
        // Mantener 'anio' para compatibilidad
        url.searchParams.set('anio', anio_inicio);
      } else {
        // Para jefes, usar año único
        url.searchParams.set('anio', anio);
        url.searchParams.delete('anio_inicio');
        url.searchParams.delete('anio_fin');
      }
      
      url.searchParams.set('mes_inicio', mes_inicio);
      url.searchParams.set('mes_fin', mes_fin);
      
      // Si es admin/dueño, agregar filtros adicionales
      if (ES_GLOBAL) {
        // Manejar múltiples departamentos
        // Limpiar todos los parámetros de departamento primero
        // Eliminar todos los parámetros que empiecen con 'departamento'
        const allParams = Array.from(url.searchParams.keys());
        allParams.forEach(key => {
          if (key === 'departamento' || key.startsWith('departamento[')) {
            url.searchParams.delete(key);
          }
        });
        
        // Agregar solo si hay departamentos seleccionados
        if (departamentosSeleccionados && departamentosSeleccionados.length > 0) {
          // Asegurar que no haya duplicados antes de agregar y convertir a strings
          const departamentosUnicos = Array.from(new Set(departamentosSeleccionados.map(d => String(d))));
          departamentosUnicos.forEach(deptId => {
            url.searchParams.append('departamento[]', deptId);
          });
        }
        if (filtroRol && filtroRol !== 'todos') {
          url.searchParams.set('rol', filtroRol);
        } else {
          url.searchParams.delete('rol');
        }
      }
      
      window.location.href = url.toString();
    }

    // Nombres de meses
    const NOMBRES_MESES = {
      1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril', 5: 'Mayo', 6: 'Junio',
      7: 'Julio', 8: 'Agosto', 9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
    };

    // Lista de todos los paneles para poder cerrarlos
    const todosLosPaneles = [
      { id: 'panel-rango-meses', btnId: 'btn-rango-meses' },
      { id: 'panel-departamento', btnId: 'btn-departamento' },
      { id: 'panel-rol', btnId: 'btn-rol' },
      { id: 'panel-anio', btnId: 'btn-anio' },
      { id: 'panel-rango-anios', btnId: 'btn-rango-anios' }
    ];

    // Función para cerrar todos los paneles excepto uno
    function cerrarOtrosPaneles(panelIdExcluido) {
      todosLosPaneles.forEach(({ id, btnId }) => {
        if (id !== panelIdExcluido) {
          const panel = document.getElementById(id);
          const btn = document.getElementById(btnId);
          if (panel && !panel.classList.contains('hidden')) {
            panel.classList.add('hidden');
            // Remover event listeners si existen
            if (btn && btn._clickOutsideHandler) {
              document.removeEventListener('click', btn._clickOutsideHandler);
              btn._clickOutsideHandler = null;
            }
          }
        }
      });
    }

    // Función genérica para manejar paneles desplegables
    function setupPanelDesplegable(btnId, panelId, onSelect) {
      const btn = document.getElementById(btnId);
      const panel = document.getElementById(panelId);
      if (!btn || !panel) return;

      let clickOutsideHandler = null;

      function abrirPanel(e) {
        e?.stopPropagation();
        // Cerrar otros paneles antes de abrir este
        cerrarOtrosPaneles(panelId);
        panel.classList.remove('hidden');
        setTimeout(() => {
          clickOutsideHandler = (e) => {
            if (!panel.contains(e.target) && !btn.contains(e.target)) {
              cerrarPanel();
            }
          };
          btn._clickOutsideHandler = clickOutsideHandler;
          document.addEventListener('click', clickOutsideHandler);
        }, 100);
      }

      function cerrarPanel() {
        panel.classList.add('hidden');
        if (clickOutsideHandler) {
          document.removeEventListener('click', clickOutsideHandler);
          clickOutsideHandler = null;
          btn._clickOutsideHandler = null;
        }
      }

      btn.addEventListener('click', (e) => {
        if (panel.classList.contains('hidden')) {
          abrirPanel(e);
        } else {
          cerrarPanel();
        }
      });

      // Cerrar al presionar Escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
          cerrarPanel();
        }
      });

      return { abrirPanel, cerrarPanel };
    }

    function actualizarTextoRango() {
      const mesInicioEl = document.getElementById('filtro-mes-inicio');
      const mesFinEl = document.getElementById('filtro-mes-fin');
      const textoEl = document.getElementById('rango-meses-texto');
      
      if (!mesInicioEl || !mesFinEl || !textoEl) return;
      
      const mesInicio = parseInt(mesInicioEl.value, 10);
      const mesFin = parseInt(mesFinEl.value, 10);
      
      const mesInicioNombre = NOMBRES_MESES[mesInicio] || 'Enero';
      const mesFinNombre = NOMBRES_MESES[mesFin] || 'Enero';
      
      // Obtener años actuales
      let anioInicio = filtroAnioInicio;
      let anioFin = filtroAnioFin;
      
      if (ES_GLOBAL) {
        const anioInicioEl = document.getElementById('filtro-anio-inicio');
        const anioFinEl = document.getElementById('filtro-anio-fin');
        if (anioInicioEl) anioInicio = parseInt(anioInicioEl.value, 10) || filtroAnioInicio;
        if (anioFinEl) anioFin = parseInt(anioFinEl.value, 10) || filtroAnioFin;
      }
      
      // Construir texto considerando años
      if (anioInicio === anioFin) {
        // Mismo año
        if (mesInicio === mesFin) {
          textoEl.textContent = `${mesInicioNombre} ${anioInicio}`;
        } else {
          textoEl.textContent = `${mesInicioNombre} - ${mesFinNombre} ${anioInicio}`;
        }
      } else {
        // Rango que cruza años
        textoEl.textContent = `${mesInicioNombre} ${anioInicio} - ${mesFinNombre} ${anioFin}`;
      }
    }

    function actualizarTextoRangoAnios() {
      if (!ES_GLOBAL) return;
      
      const anioInicioEl = document.getElementById('filtro-anio-inicio');
      const anioFinEl = document.getElementById('filtro-anio-fin');
      const textoEl = document.getElementById('rango-anios-texto');
      
      if (!anioInicioEl || !anioFinEl || !textoEl) return;
      
      const anioInicio = parseInt(anioInicioEl.value, 10);
      const anioFin = parseInt(anioFinEl.value, 10);
      
      if (anioInicio === anioFin) {
        textoEl.textContent = String(anioInicio);
      } else {
        textoEl.textContent = `${anioInicio} - ${anioFin}`;
      }
      
      // Actualizar también el texto del rango de meses
      actualizarTextoRango();
    }

    function limitarMesesPorAnio() {
      const mesInicioEl = document.getElementById('filtro-mes-inicio');
      const mesFinEl = document.getElementById('filtro-mes-fin');
      if (!mesInicioEl || !mesFinEl) return;

      // Obtener año actual (para admin/dueño puede ser rango, usar inicio)
      let anioActual = filtroAnio;
      if (ES_GLOBAL) {
        const anioInicioEl = document.getElementById('filtro-anio-inicio');
        if (anioInicioEl) {
          anioActual = parseInt(anioInicioEl.value, 10) || filtroAnioInicio;
        }
      }

      const limiteMes = (anioActual === ANIO_HOY) ? MES_HOY : 12;
      const mesInicioPrevio = parseInt(mesInicioEl.value || MES_INICIO, 10);
      const mesFinPrevio = parseInt(mesFinEl.value || MES_FIN, 10);

      // Limitar mes inicio solo si estamos en el año actual
      if (anioActual === ANIO_HOY) {
        Array.from(mesInicioEl.options).forEach(opt => {
          const v = parseInt(opt.value, 10);
          if (!Number.isNaN(v) && v > limiteMes) {
            opt.disabled = true;
          } else {
            opt.disabled = false;
          }
        });

        if (mesInicioPrevio > limiteMes) {
          mesInicioEl.value = String(limiteMes);
        }

        // Limitar mes fin
        Array.from(mesFinEl.options).forEach(opt => {
          const v = parseInt(opt.value, 10);
          if (!Number.isNaN(v) && v > limiteMes) {
            opt.disabled = true;
          } else {
            opt.disabled = false;
          }
        });

        if (mesFinPrevio > limiteMes) {
          mesFinEl.value = String(limiteMes);
        }
      } else {
        // Años pasados, habilitar todos los meses
        Array.from(mesInicioEl.options).forEach(opt => {
          opt.disabled = false;
        });
        Array.from(mesFinEl.options).forEach(opt => {
          opt.disabled = false;
        });
      }

      // Validar que mes_inicio <= mes_fin solo si estamos en el mismo año
      // Si hay rango de años, no validar
      if (!ES_GLOBAL || anioActual === parseInt(document.getElementById('filtro-anio-fin')?.value || anioActual, 10)) {
        const mesInicioVal = parseInt(mesInicioEl.value, 10);
        const mesFinVal = parseInt(mesFinEl.value, 10);
        if (mesInicioVal > mesFinVal) {
          mesFinEl.value = mesInicioEl.value;
        }
      }
      
      actualizarTextoRango();
    }

    // Configurar panel de rango de meses
    setupPanelDesplegable('btn-rango-meses', 'panel-rango-meses');
    const filtroMesInicioEl = document.getElementById('filtro-mes-inicio');
    const filtroMesFinEl = document.getElementById('filtro-mes-fin');

    // Aplicar automáticamente cuando cambian los meses
    filtroMesInicioEl?.addEventListener('change', function() {
      const mesInicioVal = parseInt(this.value, 10);
      const mesFinVal = parseInt(filtroMesFinEl?.value, 10);
      if (filtroMesFinEl && mesInicioVal > mesFinVal) {
        filtroMesFinEl.value = this.value;
      }
      actualizarTextoRango();
      reloadWithPeriodo();
    });

    filtroMesFinEl?.addEventListener('change', function() {
      const mesFinVal = parseInt(this.value, 10);
      const mesInicioVal = parseInt(filtroMesInicioEl?.value, 10);
      if (filtroMesInicioEl && mesInicioVal > mesFinVal) {
        filtroMesInicioEl.value = this.value;
      }
      actualizarTextoRango();
      reloadWithPeriodo();
    });

    // Configurar panel de año (solo para jefes, no admin/dueño)
    if (!ES_GLOBAL) {
      const panelAnio = setupPanelDesplegable('btn-anio', 'panel-anio');
      document.querySelectorAll('#panel-anio .filtro-option').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const value = btn.getAttribute('data-value');
          const text = btn.getAttribute('data-text');
          filtroAnio = value;
          document.getElementById('anio-texto').textContent = text;
          
          // Actualizar clases activas
          document.querySelectorAll('#panel-anio .filtro-option').forEach(b => {
            b.classList.remove('bg-indigo-50', 'dark:bg-indigo-900/20', 'text-indigo-600', 'dark:text-indigo-400');
          });
          btn.classList.add('bg-indigo-50', 'dark:bg-indigo-900/20', 'text-indigo-600', 'dark:text-indigo-400');
          
          panelAnio.cerrarPanel();
          limitarMesesPorAnio();
          reloadWithPeriodo();
        });
      });
    }

    // Configurar panel de rango de años (solo admin/dueño)
    if (ES_GLOBAL) {
      setupPanelDesplegable('btn-rango-anios', 'panel-rango-anios');
      const filtroAnioInicioEl = document.getElementById('filtro-anio-inicio');
      const filtroAnioFinEl = document.getElementById('filtro-anio-fin');

      // Aplicar automáticamente cuando cambian los años
      filtroAnioInicioEl?.addEventListener('change', function() {
        const anioInicioVal = parseInt(this.value, 10);
        const anioFinVal = parseInt(filtroAnioFinEl?.value, 10);
        if (filtroAnioFinEl && anioInicioVal > anioFinVal) {
          filtroAnioFinEl.value = this.value;
        }
        filtroAnioInicio = anioInicioVal;
        actualizarTextoRangoAnios();
        limitarMesesPorAnio();
        reloadWithPeriodo();
      });

      filtroAnioFinEl?.addEventListener('change', function() {
        const anioFinVal = parseInt(this.value, 10);
        const anioInicioVal = parseInt(filtroAnioInicioEl?.value, 10);
        if (filtroAnioInicioEl && anioInicioVal > anioFinVal) {
          filtroAnioInicioEl.value = this.value;
        }
        filtroAnioFin = anioFinVal;
        actualizarTextoRangoAnios();
        limitarMesesPorAnio();
        reloadWithPeriodo();
      });
    }

    // Configurar panel de departamento con checkboxes (solo admin/dueño)
    if (ES_GLOBAL) {
      const panelDepto = setupPanelDesplegable('btn-departamento', 'panel-departamento');
      
      function actualizarTextoDepartamento() {
        const textoEl = document.getElementById('departamento-texto');
        if (departamentosSeleccionados.length === 0) {
          textoEl.textContent = 'Todos los departamentos';
        } else if (departamentosSeleccionados.length === 1) {
          const check = document.querySelector(`.check-departamento[value="${departamentosSeleccionados[0]}"]`);
          if (check) {
            textoEl.textContent = check.getAttribute('data-text');
          } else {
            textoEl.textContent = '1 departamento seleccionado';
          }
        } else {
          textoEl.textContent = `${departamentosSeleccionados.length} departamentos seleccionados`;
        }
      }
      
      // Checkbox "Todos"
      const checkTodos = document.getElementById('check-todos-departamentos');
      if (checkTodos) {
        checkTodos.addEventListener('change', (e) => {
          if (e.target.checked) {
            // Desmarcar todos los demás
            document.querySelectorAll('.check-departamento').forEach(cb => {
              cb.checked = false;
            });
            // Limpiar el array completamente
            departamentosSeleccionados = [];
            filtroDepartamento = 'todos';
            actualizarTextoDepartamento();
            reloadWithPeriodo();
          }
        });
      }
      
      // Checkboxes individuales
      document.querySelectorAll('.check-departamento').forEach(check => {
        check.addEventListener('change', (e) => {
          const value = String(e.target.value); // Asegurar que sea string
          
          // Convertir todos los valores actuales a strings para comparación consistente
          departamentosSeleccionados = departamentosSeleccionados.map(d => String(d));
          
          if (e.target.checked) {
            // Desmarcar "Todos" si está marcado
            if (checkTodos) {
              checkTodos.checked = false;
            }
            // Agregar a la selección si no está
            if (!departamentosSeleccionados.includes(value)) {
              departamentosSeleccionados.push(value);
            }
          } else {
            // Remover de la selección (comparar como strings)
            departamentosSeleccionados = departamentosSeleccionados.filter(d => String(d) !== String(value));
            
            // Si no hay ninguno seleccionado, marcar "Todos"
            if (departamentosSeleccionados.length === 0 && checkTodos) {
              checkTodos.checked = true;
              filtroDepartamento = 'todos';
            }
          }
          
          // Limpiar duplicados usando Set y asegurar que todos sean strings
          departamentosSeleccionados = Array.from(new Set(departamentosSeleccionados.map(d => String(d))));
          
          actualizarTextoDepartamento();
          reloadWithPeriodo();
        });
      });
      
      // Inicializar: marcar checkboxes según los departamentos seleccionados
      // Asegurar que todos los valores sean strings
      departamentosSeleccionados = departamentosSeleccionados.map(d => String(d));
      
      if (departamentosSeleccionados.length === 0) {
        if (checkTodos) {
          checkTodos.checked = true;
        }
      } else {
        if (checkTodos) {
          checkTodos.checked = false;
        }
        document.querySelectorAll('.check-departamento').forEach(cb => {
          const cbValue = String(cb.value);
          if (departamentosSeleccionados.includes(cbValue)) {
            cb.checked = true;
          } else {
            cb.checked = false;
          }
        });
      }
      
      // Inicializar texto
      actualizarTextoDepartamento();

      // Configurar panel de rol
      const panelRol = setupPanelDesplegable('btn-rol', 'panel-rol');
      document.querySelectorAll('#panel-rol .filtro-option').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const value = btn.getAttribute('data-value');
          const text = btn.getAttribute('data-text');
          filtroRol = value;
          document.getElementById('rol-texto').textContent = text;
          
          // Actualizar clases activas
          document.querySelectorAll('#panel-rol .filtro-option').forEach(b => {
            b.classList.remove('bg-indigo-50', 'dark:bg-indigo-900/20', 'text-indigo-600', 'dark:text-indigo-400');
          });
          btn.classList.add('bg-indigo-50', 'dark:bg-indigo-900/20', 'text-indigo-600', 'dark:text-indigo-400');
          
          panelRol.cerrarPanel();
          reloadWithPeriodo();
        });
      });
    }

    // Inicializar
    limitarMesesPorAnio();
    actualizarTextoRango();
    if (ES_GLOBAL) {
      actualizarTextoRangoAnios();
    }

    function urlEncuestaEmpleado(empId){
      const { anio, mes_inicio } = getPeriodo();
      const u = new URL(`${ENCUESTA_BASE}/${encodeURIComponent(empId)}`, window.location.origin);
      u.searchParams.set('anio', anio);
      u.searchParams.set('mes', mes_inicio); // Para compatibilidad con la ruta de encuestas
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
        badge.innerHTML = '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 0 0118 0z"/></svg>';
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

      const palette = [
        '#22c55e',
        '#0ea5e9',
        '#a855f7',
        '#f97316',
        '#e11d48',
        '#14b8a6',
        '#84cc16',
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
            const empId = Number(emp.usuario_id ?? emp.id);

            const li = document.createElement('li');
            li.className = 'lista-empleado';
            li.style.cursor = 'pointer';
            li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
            li.style.setProperty('--depto-color', colorByDepto[deptos[0]] || 'rgba(5,150,105,0.95)');
            li.tabIndex = 0;
            li.setAttribute('role', 'button');

            const left = document.createElement('div');
            left.className = 'flex items-center gap-3';

            const avatar = document.createElement('div');
            avatar.className = 'avatar-icon';
            avatar.innerHTML = '<svg class="w-5 h-5 text-gray-700 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

            const fullName = [emp.nombre||'', emp.apellido_paterno||'', emp.apellido_materno||'']
              .join(' ')
              .trim() || `ID #${empId}`;

            const name = document.createElement('a');
            name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado underline-offset-2';
            name.textContent = fullName;
            name.href = urlEncuestaEmpleado(empId);

            left.appendChild(avatar);
            left.appendChild(name);
            li.appendChild(left);
            ul.appendChild(li);

            li.addEventListener('click', ()=>{
              window.location.href = urlEncuestaEmpleado(empId);
            });
            li.addEventListener('keydown', (ev)=>{
              if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                window.location.href = urlEncuestaEmpleado(empId);
              }
            });
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
          const empId = Number(emp.usuario_id ?? emp.id);

          const li = document.createElement('li');
          li.className = 'lista-empleado';
          li.style.cursor = 'pointer';
          li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
          li.style.setProperty('--depto-color', colorByDepto[depNombre] || 'rgba(5,150,105,0.95)');
          li.tabIndex = 0;
          li.setAttribute('role', 'button');

          const left = document.createElement('div');
          left.className = 'flex items-center gap-3';

          const avatar = document.createElement('div');
          avatar.className = 'avatar-icon';
          avatar.innerHTML = '<svg class="w-5 h-5 text-gray-700 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

          const fullName = [emp.nombre||'', emp.apellido_paterno||'', emp.apellido_materno||'']
            .join(' ')
            .trim() || `ID #${empId}`;

          const name = document.createElement('a');
          name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado underline-offset-2';
          name.textContent = fullName;
          name.href = urlEncuestaEmpleado(empId);

          left.appendChild(avatar);
          left.appendChild(name);
          li.appendChild(left);
          ul.appendChild(li);

          li.addEventListener('click', ()=>{
            window.location.href = urlEncuestaEmpleado(empId);
          });
          li.addEventListener('keydown', (ev)=>{
            if (ev.key === 'Enter' || ev.key === ' ') {
              ev.preventDefault();
              window.location.href = urlEncuestaEmpleado(empId);
            }
          });
        });
      });
    }

    function renderAsignados(cuadrante){
      const lista = Array.isArray(ASIG[cuadrante]) ? ASIG[cuadrante] : [];
      const count = document.getElementById('count-asignados');
      if (count) count.textContent = String(lista.length);
      
      // Admin y Dueño: agrupado por departamento, pero ahora CLICABLE
      if (ES_GLOBAL) {
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
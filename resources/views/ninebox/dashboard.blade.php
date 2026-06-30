@extends('layouts.app')

@section('title', 'Panel General | NineBox')

@section('content')
    <style>
        /* Animaciones para el modal (Notion-style) */
        @keyframes fadeInBackdrop {
            from { opacity: 0; backdrop-filter: blur(0px); -webkit-backdrop-filter: blur(0px); }
            to { opacity: 1; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
        }
        @keyframes fadeOutBackdrop {
            from { opacity: 1; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
            to { opacity: 0; backdrop-filter: blur(0px); -webkit-backdrop-filter: blur(0px); }
        }
        @keyframes zoomInModal {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes zoomOutModal {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(0.96); opacity: 0; }
        }

        .modal-anim-open #modal-backdrop {
            animation: fadeInBackdrop 0.2s cubic-bezier(0.2, 0.8, 0.3, 1) both;
        }
        .modal-anim-open #modal-container {
            animation: zoomInModal 0.2s cubic-bezier(0.2, 0.8, 0.3, 1) both;
        }
        .modal-anim-close #modal-backdrop {
            animation: fadeOutBackdrop 0.15s cubic-bezier(0.2, 0.8, 0.3, 1) both;
        }
        .modal-anim-close #modal-container {
            animation: zoomOutModal 0.15s cubic-bezier(0.2, 0.8, 0.3, 1) both;
        }
    </style>
    @php
        $totalEmpleados = $totalEmpleados ?? count($empleados ?? []);
        $empleadosEvaluados = $empleadosEvaluados ?? 0;
        $pendientes = max(0, $totalEmpleados - $empleadosEvaluados);
        $pct = $totalEmpleados > 0 ? min(100, round(($empleadosEvaluados / $totalEmpleados) * 100)) : 0;
        $anioActual = request('anio', now()->year);
        $mesInicio = request('mes_inicio', request('mes', now()->month));
        $mesFin = request('mes_fin', request('mes', now()->month));
        $mesActual = $mesInicio;
        $encuestaEmpleadoBase = url('/encuestas');
        $usuarioPresente = isset($usuario);
        $esSuper = $usuarioPresente && $usuario->esSuperadmin();
        $esDueno = $usuarioPresente && $usuario->esDueno();
        $esJefe = $usuarioPresente && $usuario->esJefe();
        $departamentoFiltro = $departamentoFiltro ?? request('departamento', 'todos');
        $departamentosSeleccionados = $departamentosSeleccionados ?? [];
        $rolFiltro = $rolFiltro ?? request('rol', 'todos');

        $baseNames = [
            1 => 'Inaceptable',
            2 => 'Mal empleado',
            3 => 'Aceptable',
            4 => 'Personal clave',
            5 => 'Personal sólido',
            6 => 'Diamante en bruto',
            7 => 'Elemento importante',
            8 => 'Estrella en des.',
            9 => 'Estrella',
        ];
    @endphp

    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- Sidebar resumen --}}
                <aside
                    class="lg:w-80 flex-shrink-0 bg-surface border border-border rounded-lg overflow-hidden lg:sticky lg:top-4 self-start">
                    <div class="px-6 py-6 bg-primary text-white">
                        <h3 class="font-semibold text-lg tracking-tight">{{ __('Resumen') }}</h3>
                        @if ($esSuper)
                            <p class="text-xs text-ink-3 mt-1">{{ __('Superadministrador') }}</p>
                        @elseif ($esDueno)
                            <p class="text-sm font-medium mt-1">{{ $usuario->nombre_completo }}</p>
                        @else
                            <p class="text-sm font-medium mt-1">
                                {{ optional($usuario->departamento)->nombre_departamento ?? __('Sin departamento') }}</p>
                        @endif
                    </div>

                    <div class="p-6 space-y-6">
                        @php
                            $primerAnio = 2025;
                            $meses = [
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Septiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre',
                            ];
                            $mesInicioNombre = $meses[$mesInicio] ?? 'Enero';
                            $mesFinNombre = $meses[$mesFin] ?? 'Enero';

                            $anioInicio = $anioInicio ?? $anioActual;
                            $anioFin = $anioFin ?? $anioActual;

                            if ($anioInicio === $anioFin) {
                                $rangoTexto =
                                    $mesInicio === $mesFin
                                        ? $mesInicioNombre . ' ' . $anioInicio
                                        : $mesInicioNombre . ' - ' . $mesFinNombre . ' ' . $anioInicio;
                            } else {
                                $rangoTexto =
                                    $mesInicioNombre . ' ' . $anioInicio . ' - ' . $mesFinNombre . ' ' . $anioFin;
                            }

                            $rangoAnioTexto =
                                $anioInicio === $anioFin ? (string) $anioInicio : $anioInicio . ' - ' . $anioFin;
                        @endphp

                        {{-- Filtros --}}
                        <section aria-label="Filtros" class="space-y-4 border-b border-border pb-6">
                            {{-- Selector de empresa (solo superadmin) --}}
                            @if ($esSuper)
                                <div>
                                    <label class="form-label">{{ __('Empresa') }}</label>
                                    <select id="filtro-empresa" class="form-input">
                                        <option value="0" {{ ($empresaFiltroId ?? 0) == 0 ? 'selected' : '' }}>
                                            {{ __('Todas las empresas') }}
                                        </option>
                                        @foreach ($empresas ?? [] as $emp)
                                            <option value="{{ $emp->id }}"
                                                {{ ($empresaFiltroId ?? 0) == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Rango de meses --}}
                            <div>
                                <label class="form-label">{{ __('Rango de meses') }}</label>
                                <div class="relative">
                                    <button type="button" id="btn-rango-meses"
                                        class="form-input text-left flex items-center justify-between">
                                        <span id="rango-meses-texto">{{ $rangoTexto }}</span>
                                        <svg class="w-4 h-4 text-ink-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    {{-- Panel desplegable del rango --}}
                                    <div id="panel-rango-meses"
                                        class="hidden absolute z-50 mt-2 left-0 right-0 min-w-[240px] bg-canvas rounded border border-border shadow-card p-4">
                                        <div class="space-y-4">
                                            <div>
                                                <label for="filtro-mes-inicio"
                                                    class="form-label text-xs">{{ __('Mes Inicio') }}</label>
                                                <select id="filtro-mes-inicio" class="form-input text-xs">
                                                    @foreach ($meses as $num => $mes)
                                                        <option value="{{ $num }}"
                                                            {{ (int) $num === (int) $mesInicio ? 'selected' : '' }}>
                                                            {{ $mes }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label for="filtro-mes-fin"
                                                    class="form-label text-xs">{{ __('Mes Fin') }}</label>
                                                <select id="filtro-mes-fin" class="form-input text-xs">
                                                    @foreach ($meses as $num => $mes)
                                                        <option value="{{ $num }}"
                                                            {{ (int) $num === (int) $mesFin ? 'selected' : '' }}>
                                                            {{ $mes }}</option>
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
                                    <label class="form-label">{{ __('Departamento') }}</label>
                                    <div class="relative">
                                        <button type="button" id="btn-departamento"
                                            class="form-input text-left flex items-center justify-between">
                                            <span id="departamento-texto">
                                                @if (empty($departamentosSeleccionados))
                                                    {{ __('Todos los departamentos') }}
                                                @elseif(count($departamentosSeleccionados) === 1)
                                                    @foreach ($departamentos ?? [] as $depto)
                                                        @if ((string) $depto->id === (string) $departamentosSeleccionados[0])
                                                            {{ $depto->nombre_departamento }}
                                                            @break
                                                        @endif
                                                    @endforeach
                                                @else
                                                    {{ count($departamentosSeleccionados) }} {{ __('seleccionados') }}
                                                @endif
                                            </span>
                                            <svg class="w-4 h-4 text-ink-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        {{-- Panel desplegable del departamento con checkboxes --}}
                                        <div id="panel-departamento"
                                            class="hidden absolute z-50 mt-2 left-0 right-0 min-w-[240px] bg-canvas rounded border border-border shadow-card p-3 max-h-60 overflow-y-auto">
                                            <div class="space-y-2">
                                                <label
                                                    class="flex items-center px-2 py-1 rounded hover:bg-surface transition-colors cursor-pointer select-none">
                                                    <input type="checkbox" id="check-todos-departamentos"
                                                        class="rounded border-border text-primary focus:ring-primary"
                                                        {{ empty($departamentosSeleccionados) ? 'checked' : '' }}>
                                                    <span
                                                        class="ml-2.5 text-sm font-medium text-ink">{{ __('Todos') }}</span>
                                                </label>
                                                @foreach ($departamentos ?? [] as $depto)
                                                    <label
                                                        class="flex items-center px-2 py-1 rounded hover:bg-surface transition-colors cursor-pointer select-none">
                                                        <input type="checkbox"
                                                            class="check-departamento rounded border-border text-primary focus:ring-primary"
                                                            value="{{ $depto->id }}"
                                                            data-text="{{ $depto->nombre_departamento }}"
                                                            {{ in_array((string) $depto->id, array_map('strval', $departamentosSeleccionados)) ? 'checked' : '' }}>
                                                        <span
                                                            class="ml-2.5 text-sm text-ink-2">{{ $depto->nombre_departamento }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Rol --}}
                                <div>
                                    <label class="form-label">{{ __('Rol') }}</label>
                                    <div class="relative">
                                        <button type="button" id="btn-rol"
                                            class="form-input text-left flex items-center justify-between">
                                            <span id="rol-texto">
                                                @if ($rolFiltro === 'todos')
                                                    {{ __('Todos los roles') }}
                                                @elseif($rolFiltro === 'jefe')
                                                    {{ __('Jefes') }}
                                                @elseif($rolFiltro === 'empleado')
                                                    {{ __('Empleados') }}
                                                @endif
                                            </span>
                                            <svg class="w-4 h-4 text-ink-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        {{-- Panel desplegable del rol --}}
                                        <div id="panel-rol"
                                            class="hidden absolute z-50 mt-2 left-0 right-0 min-w-[200px] bg-canvas rounded border border-border shadow-card p-1">
                                            <button type="button"
                                                class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-surface transition-colors text-ink {{ $rolFiltro === 'todos' ? 'bg-primary/10 text-primary font-medium' : '' }}"
                                                data-value="todos" data-text="{{ __('Todos los roles') }}">
                                                {{ __('Todos los roles') }}
                                            </button>
                                            <button type="button"
                                                class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-surface transition-colors text-ink {{ $rolFiltro === 'jefe' ? 'bg-primary/10 text-primary font-medium' : '' }}"
                                                data-value="jefe" data-text="{{ __('Jefes') }}">
                                                {{ __('Jefes') }}
                                            </button>
                                            <button type="button"
                                                class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-surface transition-colors text-ink {{ $rolFiltro === 'empleado' ? 'bg-primary/10 text-primary font-medium' : '' }}"
                                                data-value="empleado" data-text="{{ __('Empleados') }}">
                                                {{ __('Empleados') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </section>

                        {{-- KPIs --}}
                        <section aria-label="Indicadores clave" class="space-y-4">
                            <div class="grid grid-cols-1 gap-3">
                                <div class="flex justify-between items-center py-2 border-b border-border">
                                    <span class="text-sm text-ink-2">{{ __('Por evaluar') }}</span>
                                    <span id="empleados-pendientes"
                                        class="text-base font-bold text-ink">{{ $pendientes }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-border">
                                    <span class="text-sm text-ink-2">{{ __('Evaluados') }}</span>
                                    <span id="empleados-evaluados"
                                        class="text-base font-bold text-success">{{ $empleadosEvaluados }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-ink-2">{{ __('Total') }}</span>
                                    <span id="total-empleados"
                                        class="text-base font-bold text-ink">{{ $totalEmpleados }}</span>
                                </div>
                            </div>

                            {{-- Progreso --}}
                            <div class="pt-2">
                                <div class="flex items-center justify-between text-xs text-ink-2 mb-1">
                                    <span>{{ __('Progreso') }}</span>
                                    <span id="progreso-porcentaje" class="font-bold">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-border h-2 rounded overflow-hidden">
                                    <div id="avance-bar" class="h-full bg-primary transition-all duration-300"
                                        style="width: {{ $pct }}%;"></div>
                                </div>
                            </div>
                        </section>

                        {{-- CTA lista empleados --}}
                        @if ($usuarioPresente && ($esJefe || $esDueno))
                            <div class="pt-2">
                                <a id="btn-por-evaluar"
                                    href="{{ route('encuestas.empleados', ['anio' => $anioActual, 'mes' => $mesActual]) }}"
                                    class="btn-primary w-full inline-flex justify-center text-center">
                                    {{ $esDueno ? __('Evaluar Jefes') : __('Evaluar Equipo') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </aside>

                {{-- Nine-Box Grid --}}
                <div class="flex-1 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-ink">{{ __('Matriz Nine-Box') }}</h3>

                        {{-- Filtro de rango de años (solo admin/dueño) --}}
                        @if ($esSuper || $esDueno)
                            <div class="relative">
                                <button type="button" id="btn-rango-anios"
                                    class="form-input text-left flex items-center justify-between gap-2 !w-auto">
                                    <span id="rango-anios-texto">{{ $rangoAnioTexto }}</span>
                                    <svg class="w-4 h-4 text-ink-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- Panel desplegable del rango de años --}}
                                <div id="panel-rango-anios"
                                    class="hidden absolute z-50 mt-2 right-0 min-w-[240px] bg-canvas rounded border border-border shadow-card p-4">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="filtro-anio-inicio"
                                                class="form-label text-xs">{{ __('Año Inicio') }}</label>
                                            <select id="filtro-anio-inicio" class="form-input text-xs">
                                                @for ($i = now()->year; $i >= $primerAnio; $i--)
                                                    <option value="{{ $i }}"
                                                        {{ (int) $i === (int) $anioInicio ? 'selected' : '' }}>
                                                        {{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div>
                                            <label for="filtro-anio-fin"
                                                class="form-label text-xs">{{ __('Año Fin') }}</label>
                                            <select id="filtro-anio-fin" class="form-input text-xs">
                                                @for ($i = now()->year; $i >= $primerAnio; $i--)
                                                    <option value="{{ $i }}"
                                                        {{ (int) $i === (int) $anioFin ? 'selected' : '' }}>
                                                        {{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Para jefes, año único --}}
                            <div class="relative">
                                <button type="button" id="btn-anio"
                                    class="form-input text-left flex items-center justify-between gap-2 !w-auto">
                                    <span id="anio-texto">{{ $anioActual }}</span>
                                    <svg class="w-4 h-4 text-ink-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- Panel desplegable del año --}}
                                <div id="panel-anio"
                                    class="hidden absolute z-50 mt-2 right-0 min-w-[120px] bg-canvas rounded border border-border shadow-card p-2 max-h-60 overflow-y-auto">
                                    @for ($i = now()->year; $i >= $primerAnio; $i--)
                                        <button type="button"
                                            class="filtro-option w-full text-left px-3 py-2 rounded hover:bg-surface transition-colors text-ink {{ (int) $i === (int) $anioActual ? 'bg-primary/10 text-primary font-medium' : '' }}"
                                            data-value="{{ $i }}" data-text="{{ $i }}">
                                            {{ $i }}
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Matriz -->
                    <div class="card p-5">
                        <div class="relative w-full mx-auto" style="max-width: 96%;">
                            <img src="{{ asset('images/9box-demo.png') }}"
                                class="w-full h-auto rounded-md shadow-card select-none" id="ninebox-img" alt="9-Box"
                                draggable="false" style="pointer-events: none;">
                            @php
                                $map = \App\Models\NineBox::posMap();
                                $lefts = [1 => '17.5%', 2 => '42.5%', 3 => '67.5%'];
                                $tops = [1 => '18%', 2 => '45%', 3 => '71.8%'];
                                $W = ['default' => '23.5%', 'last' => '23%'];
                                $H = '25%';
                                $positions = [];
                                foreach ($map as $id => $rc) {
                                    $col = (int) $rc['col'];
                                    $row = (int) $rc['row'];
                                    $positions[$id] = [
                                        'left' => $lefts[$col],
                                        'top' => $tops[$row],
                                        'w' => $col === 3 ? $W['last'] : $W['default'],
                                        'h' => $H,
                                    ];
                                }
                                $order = collect($map)
                                    ->map(fn($rc, $id) => ['id' => $id, 'row' => $rc['row'], 'col' => $rc['col']])
                                    ->sortBy([['row', 'asc'], ['col', 'asc']])
                                    ->pluck('id')
                                    ->all();
                            @endphp
                            @foreach ($order as $i)
                                @php
                                    $pos = $positions[$i];
                                    $count = collect($asignacionesActuales[$i] ?? [])->count();
                                @endphp
                                <button type="button"
                                    class="cuadrante-btn absolute border-2 border-transparent hover:border-primary hover:bg-primary/5 hover:scale-[1.015] hover:shadow-lg active:scale-[0.985] rounded-md transition-all duration-200 ease-out focus:outline-none"
                                    style="left: {{ $pos['left'] }}; top: {{ $pos['top'] }}; width: {{ $pos['w'] }}; height: {{ $pos['h'] }};"
                                    data-cuadrante="{{ $i }}"
                                    aria-label="Ver empleados en cuadrante {{ $i }}">
                                    @if ($count > 0)
                                        <div
                                            class="absolute -top-2 -right-2 bg-primary text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-card select-none pointer-events-none">
                                            {{ $count }}
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div id="modal-empleados" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true"
            aria-labelledby="modal-title" aria-describedby="modal-desc">
            <div id="modal-backdrop" class="absolute inset-0 bg-ink/40 backdrop-blur-sm transition-opacity duration-200">
            </div>
            <div class="relative h-full flex items-center justify-center p-4">
                <div id="modal-container"
                    class="bg-canvas border border-border rounded-lg shadow-card w-full max-w-2xl max-h-[80vh] overflow-hidden transform scale-95 opacity-0 transition-all duration-200"
                    tabindex="-1" aria-hidden="true">
                    <div class="bg-primary text-white p-6 relative select-none">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 id="modal-title" class="text-lg font-semibold text-white"></h3>
                                <p id="modal-desc" class="text-xs text-ink-3 mt-1"></p>
                            </div>
                            <button id="btn-cerrar-modal" class="text-ink-3 hover:text-white focus:outline-none"
                                aria-label="Cerrar modal">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Cuerpo del modal --}}
                    <div class="p-6 overflow-y-auto bg-canvas" style="max-height: calc(80vh - 100px);">
                        <div id="section-asignados" class="mb-2">
                            <div id="modal-header-section"
                                class="flex items-center justify-between border-b border-border pb-3 mb-4 select-none">
                                <div class="flex items-center gap-2">
                                    <h4 id="title-asignados" class="text-sm font-semibold text-ink">Asignados</h4>
                                    <span id="count-asignados"
                                        class="badge bg-primary text-white text-xs px-2 py-0.5">0</span>
                                </div>
                            </div>

                            <div id="empty-asignados" class="text-center py-8 text-ink-2 text-sm hidden select-none">
                                {{ __('No hay colaboradores asignados en este cuadrante para el periodo seleccionado.') }}
                            </div>
                            <ul id="lista-asignados" class="space-y-2"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script>
        (function() {
            'use strict';

            const ES_GLOBAL = @json($esSuper || $esDueno);
            let ASIG = @json($asignacionesActuales ?? []);
            const ENCUESTA_BASE = @json($encuestaEmpleadoBase);
            const ANIO_ACTUAL = @json($anioActual);
            const ANIO_INICIO = @json($anioInicio ?? $anioActual);
            const ANIO_FIN = @json($anioFin ?? $anioActual);
            const MES_INICIO = @json($mesInicio ?? $mesActual);
            const MES_FIN = @json($mesFin ?? $mesActual);
            const MES_ACTUAL = @json($mesActual);
            const ANIO_HOY = {{ now()->year }};
            const MES_HOY = {{ now()->month }};

            const BASE_DESC = {
                6: {
                    title: "Diamante en bruto",
                    desc: "Gran potencial, su desempeño no ha sido exigido por lo que requiere desarrollarlo"
                },
                8: {
                    title: "Estrella en desarrollo",
                    desc: "Potencial y desempeño en crecimiento, con la dirección adecuada puede convertirse en una estrella"
                },
                9: {
                    title: "Estrella",
                    desc: "Empleados con alto desempeño y gran potencial, clave para la organización"
                },
                2: {
                    title: "Mal empleado",
                    desc: "Desempeño insuficiente, requiere mejora y desarrollo"
                },
                5: {
                    title: "Personal sólido",
                    desc: "Desempeño aceptable, pero con potencial limitado para crecer"
                },
                7: {
                    title: "Elemento importante",
                    desc: "Buena contribución actual, pero con un potencial de crecimiento incierto"
                },
                1: {
                    title: "Inaceptable",
                    desc: "Desempeño inaceptable, requiere acción inmediata"
                },
                3: {
                    title: "Aceptable",
                    desc: "Desempeño básico, cumple con los mínimos requerimientos"
                },
                4: {
                    title: "Personal clave",
                    desc: "Confiables con buen desempeño, pero con poco potencial de desarrollo"
                }
            };

            let filtroAnio = ANIO_ACTUAL;
            let filtroAnioInicio = ANIO_INICIO;
            let filtroAnioFin = ANIO_FIN;
            let filtroDepartamento = @json($departamentoFiltro ?? 'todos');
            const deptosRaw = @json($departamentosSeleccionados ?? []);
            let departamentosSeleccionados = Array.from(new Set(deptosRaw.map(d => String(d))));
            let filtroRol = @json($rolFiltro ?? 'todos');

            function getPeriodo() {
                const mesInicioSel = document.getElementById('filtro-mes-inicio')?.value ?? MES_INICIO;
                const mesFinSel = document.getElementById('filtro-mes-fin')?.value ?? MES_FIN;

                let anioInicioSel = ES_GLOBAL ? filtroAnioInicio : parseInt(filtroAnio, 10);
                let anioFinSel = ES_GLOBAL ? filtroAnioFin : parseInt(filtroAnio, 10);

                if (ES_GLOBAL) {
                    const anioInicioEl = document.getElementById('filtro-anio-inicio');
                    const anioFinEl = document.getElementById('filtro-anio-fin');
                    if (anioInicioEl) anioInicioSel = parseInt(anioInicioEl.value, 10) || filtroAnioInicio;
                    if (anioFinEl) anioFinSel = parseInt(anioFinEl.value, 10) || filtroAnioFin;
                }

                return {
                    anio: String(anioInicioSel).trim(),
                    anio_inicio: String(anioInicioSel).trim(),
                    anio_fin: String(anioFinSel).trim(),
                    mes_inicio: String(mesInicioSel).trim(),
                    mes_fin: String(mesFinSel).trim()
                };
            }

            let reloadTimeout = null;
            function reloadWithPeriodo() {
                if (reloadTimeout) {
                    clearTimeout(reloadTimeout);
                }
                reloadTimeout = setTimeout(() => {
                    const {
                        anio,
                        anio_inicio,
                        anio_fin,
                        mes_inicio,
                        mes_fin
                    } = getPeriodo();
                    const url = new URL(window.location.href);

                    if (ES_GLOBAL) {
                        url.searchParams.set('anio_inicio', anio_inicio);
                        url.searchParams.set('anio_fin', anio_fin);
                        url.searchParams.set('anio', anio_inicio);
                    } else {
                        url.searchParams.set('anio', anio);
                        url.searchParams.delete('anio_inicio');
                        url.searchParams.delete('anio_fin');
                    }

                    url.searchParams.set('mes_inicio', mes_inicio);
                    url.searchParams.set('mes_fin', mes_fin);

                    if (ES_GLOBAL) {
                        const allParams = Array.from(url.searchParams.keys());
                        allParams.forEach(key => {
                            if (key === 'departamento' || key.startsWith('departamento[')) {
                                url.searchParams.delete(key);
                            }
                        });

                        if (departamentosSeleccionados && departamentosSeleccionados.length > 0) {
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

                    fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        actualizarGridConDatos(data.rendimientos, data.totalEmpleados, data.empleadosEvaluados);
                    })
                    .catch(err => {
                        console.error('Error actualizando el grid:', err);
                    });
                }, 400);
            }

            const NOMBRES_MESES = {
                1: 'Enero',
                2: 'Febrero',
                3: 'Marzo',
                4: 'Abril',
                5: 'Mayo',
                6: 'Junio',
                7: 'Julio',
                8: 'Agosto',
                9: 'Septiembre',
                10: 'Octubre',
                11: 'Noviembre',
                12: 'Diciembre'
            };

            const todosLosPaneles = [{
                    id: 'panel-rango-meses',
                    btnId: 'btn-rango-meses'
                },
                {
                    id: 'panel-departamento',
                    btnId: 'btn-departamento'
                },
                {
                    id: 'panel-rol',
                    btnId: 'btn-rol'
                },
                {
                    id: 'panel-anio',
                    btnId: 'btn-anio'
                },
                {
                    id: 'panel-rango-anios',
                    btnId: 'btn-rango-anios'
                }
            ];

            function cerrarOtrosPaneles(panelIdExcluido) {
                todosLosPaneles.forEach(({
                    id,
                    btnId
                }) => {
                    if (id !== panelIdExcluido) {
                        const panel = document.getElementById(id);
                        const btn = document.getElementById(btnId);
                        if (panel && !panel.classList.contains('hidden')) {
                            panel.classList.add('hidden');
                            if (btn && btn._clickOutsideHandler) {
                                document.removeEventListener('click', btn._clickOutsideHandler);
                                btn._clickOutsideHandler = null;
                            }
                        }
                    }
                });
            }

            function setupPanelDesplegable(btnId, panelId) {
                const btn = document.getElementById(btnId);
                const panel = document.getElementById(panelId);
                if (!btn || !panel) return;

                let clickOutsideHandler = null;

                function abrirPanel(e) {
                    e?.stopPropagation();
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

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
                        cerrarPanel();
                    }
                });

                return {
                    abrirPanel,
                    cerrarPanel
                };
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

                let anioInicio = filtroAnioInicio;
                let anioFin = filtroAnioFin;

                if (ES_GLOBAL) {
                    const anioInicioEl = document.getElementById('filtro-anio-inicio');
                    const anioFinEl = document.getElementById('filtro-anio-fin');
                    if (anioInicioEl) anioInicio = parseInt(anioInicioEl.value, 10) || filtroAnioInicio;
                    if (anioFinEl) anioFin = parseInt(anioFinEl.value, 10) || filtroAnioFin;
                }

                if (anioInicio === anioFin) {
                    if (mesInicio === mesFin) {
                        textoEl.textContent = `${mesInicioNombre} ${anioInicio}`;
                    } else {
                        textoEl.textContent = `${mesInicioNombre} - ${mesFinNombre} ${anioInicio}`;
                    }
                } else {
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

                actualizarTextoRango();
            }

            function limitarMesesPorAnio() {
                const mesInicioEl = document.getElementById('filtro-mes-inicio');
                const mesFinEl = document.getElementById('filtro-mes-fin');
                if (!mesInicioEl || !mesFinEl) return;

                let anioActual = ES_GLOBAL ?
                    (parseInt(document.getElementById('filtro-anio-inicio')?.value, 10) || filtroAnioInicio) :
                    parseInt(filtroAnio, 10);

                const limiteMes = (anioActual === ANIO_HOY) ? MES_HOY : 12;
                const mesInicioPrevio = parseInt(mesInicioEl.value || MES_INICIO, 10);
                const mesFinPrevio = parseInt(mesFinEl.value || MES_FIN, 10);

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
                    Array.from(mesInicioEl.options).forEach(opt => {
                        opt.disabled = false;
                    });
                    Array.from(mesFinEl.options).forEach(opt => {
                        opt.disabled = false;
                    });
                }

                if (!ES_GLOBAL || anioActual === parseInt(document.getElementById('filtro-anio-fin')?.value ||
                        anioActual, 10)) {
                    const mesInicioVal = parseInt(mesInicioEl.value, 10);
                    const mesFinVal = parseInt(mesFinEl.value, 10);
                    if (mesInicioVal > mesFinVal) {
                        mesFinEl.value = mesInicioEl.value;
                    }
                }

                actualizarTextoRango();
            }

            setupPanelDesplegable('btn-rango-meses', 'panel-rango-meses');
            const filtroMesInicioEl = document.getElementById('filtro-mes-inicio');
            const filtroMesFinEl = document.getElementById('filtro-mes-fin');

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

            if (!ES_GLOBAL) {
                const panelAnio = setupPanelDesplegable('btn-anio', 'panel-anio');
                document.querySelectorAll('#panel-anio .filtro-option').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const value = btn.getAttribute('data-value');
                        const text = btn.getAttribute('data-text');
                        filtroAnio = parseInt(value, 10);
                        document.getElementById('anio-texto').textContent = text;

                        document.querySelectorAll('#panel-anio .filtro-option').forEach(b => {
                            b.classList.remove('bg-primary/10', 'text-primary', 'font-medium');
                        });
                        btn.classList.add('bg-primary/10', 'text-primary', 'font-medium');

                        panelAnio.cerrarPanel();
                        limitarMesesPorAnio();
                        reloadWithPeriodo();
                    });
                });
            }

            if (ES_GLOBAL) {
                setupPanelDesplegable('btn-rango-anios', 'panel-rango-anios');
                const filtroAnioInicioEl = document.getElementById('filtro-anio-inicio');
                const filtroAnioFinEl = document.getElementById('filtro-anio-fin');

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

            if (ES_GLOBAL) {
                const panelDepto = setupPanelDesplegable('btn-departamento', 'panel-departamento');

                function actualizarTextoDepartamento() {
                    const textoEl = document.getElementById('departamento-texto');
                    if (departamentosSeleccionados.length === 0) {
                        textoEl.textContent = 'Todos los departamentos';
                    } else if (departamentosSeleccionados.length === 1) {
                        const check = document.querySelector(
                            `.check-departamento[value="${departamentosSeleccionados[0]}"]`);
                        if (check) {
                            textoEl.textContent = check.getAttribute('data-text');
                        } else {
                            textoEl.textContent = '1 departamento seleccionado';
                        }
                    } else {
                        textoEl.textContent = `${departamentosSeleccionados.length} departamentos seleccionados`;
                    }
                }

                const checkTodos = document.getElementById('check-todos-departamentos');
                if (checkTodos) {
                    checkTodos.addEventListener('change', (e) => {
                        if (e.target.checked) {
                            document.querySelectorAll('.check-departamento').forEach(cb => {
                                cb.checked = false;
                            });
                            departamentosSeleccionados = [];
                            filtroDepartamento = 'todos';
                            actualizarTextoDepartamento();
                            reloadWithPeriodo();
                        }
                    });
                }

                document.querySelectorAll('.check-departamento').forEach(check => {
                    check.addEventListener('change', (e) => {
                        const value = String(e.target.value);
                        departamentosSeleccionados = departamentosSeleccionados.map(d => String(d));

                        if (e.target.checked) {
                            if (checkTodos) {
                                checkTodos.checked = false;
                            }
                            if (!departamentosSeleccionados.includes(value)) {
                                departamentosSeleccionados.push(value);
                            }
                        } else {
                            departamentosSeleccionados = departamentosSeleccionados.filter(d => String(
                                d) !== String(value));
                            if (departamentosSeleccionados.length === 0 && checkTodos) {
                                checkTodos.checked = true;
                                filtroDepartamento = 'todos';
                            }
                        }

                        departamentosSeleccionados = Array.from(new Set(departamentosSeleccionados.map(
                            d => String(d))));
                        actualizarTextoDepartamento();
                        reloadWithPeriodo();
                    });
                });

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

                actualizarTextoDepartamento();

                const panelRol = setupPanelDesplegable('btn-rol', 'panel-rol');
                document.querySelectorAll('#panel-rol .filtro-option').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const value = btn.getAttribute('data-value');
                        const text = btn.getAttribute('data-text');
                        filtroRol = value;
                        document.getElementById('rol-texto').textContent = text;

                        document.querySelectorAll('#panel-rol .filtro-option').forEach(b => {
                            b.classList.remove('bg-primary/10', 'text-primary', 'font-medium');
                        });
                        btn.classList.add('bg-primary/10', 'text-primary', 'font-medium');

                        panelRol.cerrarPanel();
                        reloadWithPeriodo();
                    });
                });
            }

            limitarMesesPorAnio();
            actualizarTextoRango();
            if (ES_GLOBAL) {
                actualizarTextoRangoAnios();
            }

            const filtroEmpresaEl = document.getElementById('filtro-empresa');
            if (filtroEmpresaEl) {
                filtroEmpresaEl.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    const val = this.value;
                    if (val === '0' || val === '') {
                        url.searchParams.delete('empresa_id');
                    } else {
                        url.searchParams.set('empresa_id', val);
                    }
                    url.searchParams.delete('departamento');
                    const allParams = Array.from(url.searchParams.keys());
                    allParams.forEach(key => {
                        if (key.startsWith('departamento[')) url.searchParams.delete(key);
                    });
                    window.location.href = url.toString();
                });
            }

            function urlEncuestaEmpleado(empId) {
                const {
                    anio,
                    mes_inicio
                } = getPeriodo();
                const u = new URL(`${ENCUESTA_BASE}/${encodeURIComponent(empId)}`, window.location.origin);
                u.searchParams.set('anio', anio);
                u.searchParams.set('mes', mes_inicio);
                return u.toString();
            }

            function agruparPorDepartamento(lista) {
                return lista.reduce((acc, emp) => {
                    const d = emp.departamento_nombre || 'Sin departamento';
                    (acc[d] ||= []).push(emp);
                    return acc;
                }, {});
            }

            function renderListaSimple(lista) {
                const ul = document.getElementById('lista-asignados');
                const empty = document.getElementById('empty-asignados');
                const title = document.getElementById('title-asignados');
                const badge = document.getElementById('badge-asignados');
                const header = document.getElementById('modal-header-section');

                if (header) header.style.display = 'flex';
                if (title) title.textContent = 'Asignados';
                if (badge) {
                    badge.className = 'badge bg-primary text-white text-xs px-2 py-0.5';
                }

                ul.innerHTML = '';
                if (!Array.isArray(lista) || lista.length === 0) {
                    empty?.classList.remove('hidden');
                    return;
                }
                empty?.classList.add('hidden');

                lista.forEach((emp, i) => {
                    const empId = Number(emp.usuario_id ?? emp.id);
                    const li = document.createElement('li');
                    li.className =
                        'flex items-center justify-between p-3 bg-surface hover:bg-border/30 rounded border border-border transition-colors duration-150';
                    li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
                    li.tabIndex = 0;
                    li.setAttribute('role', 'button');

                    const left = document.createElement('div');
                    left.className = 'flex items-center gap-3';

                    const avatar = document.createElement('div');
                    avatar.className =
                        'w-8 h-8 rounded-full bg-border flex items-center justify-center text-ink-2';
                    avatar.innerHTML =
                        '<svg class="w-4 h-4 text-ink-2" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

                    const fullName = [emp.nombre || '', emp.apellido_paterno || '', emp.apellido_materno || '']
                        .join(' ').trim() || `ID #${empId}`;
                    const name = document.createElement('a');
                    name.className = 'font-semibold text-ink hover:text-primary transition-colors text-sm';
                    name.textContent = fullName;
                    name.href = urlEncuestaEmpleado(empId);

                    left.appendChild(avatar);
                    left.appendChild(name);
                    li.appendChild(left);
                    ul.appendChild(li);

                    li.addEventListener('click', () => {
                        window.location.href = urlEncuestaEmpleado(empId);
                    });
                    li.addEventListener('keydown', (ev) => {
                        if (ev.key === 'Enter' || ev.key === ' ') {
                            ev.preventDefault();
                            window.location.href = urlEncuestaEmpleado(empId);
                        }
                    });
                });
            }

            function renderAgrupadoPorDepto(lista) {
                const ul = document.getElementById('lista-asignados');
                const empty = document.getElementById('empty-asignados');
                const header = document.getElementById('modal-header-section');

                ul.innerHTML = '';

                if (!Array.isArray(lista) || lista.length === 0) {
                    empty?.classList.remove('hidden');
                    if (header) header.style.display = 'none';
                    return;
                }
                empty?.classList.add('hidden');

                const porDepto = agruparPorDepartamento(lista);
                const deptos = Object.keys(porDepto).sort((a, b) => a.localeCompare(b, 'es'));

                const palette = [
                    'border-green-500',
                    'border-blue-500',
                    'border-purple-500',
                    'border-orange-500',
                    'border-red-500',
                    'border-teal-500',
                    'border-lime-500',
                ];
                const colorByDepto = {};
                deptos.forEach((dep, idx) => {
                    colorByDepto[dep] = palette[idx % palette.length];
                });

                if (deptos.length === 1) {
                    if (header) header.style.display = 'flex';
                    const title = document.getElementById('title-asignados');
                    const badge = document.getElementById('badge-asignados');
                    if (title) title.textContent = deptos[0];

                    porDepto[deptos[0]]
                        .sort((a, b) => (a.nombre || '').localeCompare(b.nombre || '', 'es'))
                        .forEach((emp, i) => {
                            const empId = Number(emp.usuario_id ?? emp.id);

                            const li = document.createElement('li');
                            li.className =
                                `flex items-center justify-between p-3 bg-surface hover:bg-border/30 rounded border-l-4 ${colorByDepto[deptos[0]] || 'border-green-500'} border-t border-r border-b border-border transition-colors duration-150`;
                            li.style.cursor = 'pointer';
                            li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
                            li.tabIndex = 0;
                            li.setAttribute('role', 'button');

                            const left = document.createElement('div');
                            left.className = 'flex items-center gap-3';

                            const avatar = document.createElement('div');
                            avatar.className =
                                'w-8 h-8 rounded-full bg-border flex items-center justify-center text-ink-2';
                            avatar.innerHTML =
                                '<svg class="w-4 h-4 text-ink-2" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

                            const fullName = [emp.nombre || '', emp.apellido_paterno || '', emp.apellido_materno ||
                                    ''
                                ]
                                .join(' ')
                                .trim() || `ID #${empId}`;

                            const name = document.createElement('a');
                            name.className = 'font-semibold text-ink hover:text-primary transition-colors text-sm';
                            name.textContent = fullName;
                            name.href = urlEncuestaEmpleado(empId);

                            left.appendChild(avatar);
                            left.appendChild(name);
                            li.appendChild(left);
                            ul.appendChild(li);

                            li.addEventListener('click', () => {
                                window.location.href = urlEncuestaEmpleado(empId);
                            });
                            li.addEventListener('keydown', (ev) => {
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
                        .sort((a, b) => (a.nombre || '').localeCompare(b.nombre || '', 'es'));

                    const headerLi = document.createElement('li');
                    headerLi.className =
                        'flex items-center justify-between py-2 border-b border-border mt-4 mb-2 select-none';

                    const h = document.createElement('h5');
                    h.className = 'text-xs font-semibold text-ink-2 uppercase tracking-wider';
                    h.textContent = depNombre;

                    const chip = document.createElement('span');
                    chip.className = 'badge bg-primary text-white text-[10px] font-bold px-2 py-0.5';
                    chip.textContent = String(empleados.length);

                    headerLi.appendChild(h);
                    headerLi.appendChild(chip);
                    ul.appendChild(headerLi);

                    empleados.forEach((emp, i) => {
                        const empId = Number(emp.usuario_id ?? emp.id);

                        const li = document.createElement('li');
                        li.className =
                            `flex items-center justify-between p-3 bg-surface hover:bg-border/30 rounded border-l-4 ${colorByDepto[depNombre] || 'border-green-500'} border-t border-r border-b border-border transition-colors duration-150`;
                        li.style.cursor = 'pointer';
                        li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;
                        li.tabIndex = 0;
                        li.setAttribute('role', 'button');

                        const left = document.createElement('div');
                        left.className = 'flex items-center gap-3';

                        const avatar = document.createElement('div');
                        avatar.className =
                            'w-8 h-8 rounded-full bg-border flex items-center justify-center text-ink-2';
                        avatar.innerHTML =
                            '<svg class="w-4 h-4 text-ink-2" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

                        const fullName = [emp.nombre || '', emp.apellido_paterno || '', emp
                                .apellido_materno || ''
                            ]
                            .join(' ')
                            .trim() || `ID #${empId}`;

                        const name = document.createElement('a');
                        name.className =
                            'font-semibold text-ink hover:text-primary transition-colors text-sm';
                        name.textContent = fullName;
                        name.href = urlEncuestaEmpleado(empId);

                        left.appendChild(avatar);
                        left.appendChild(name);
                        li.appendChild(left);
                        ul.appendChild(li);

                        li.addEventListener('click', () => {
                            window.location.href = urlEncuestaEmpleado(empId);
                        });
                        li.addEventListener('keydown', (ev) => {
                            if (ev.key === 'Enter' || ev.key === ' ') {
                                ev.preventDefault();
                                window.location.href = urlEncuestaEmpleado(empId);
                            }
                        });
                    });
                });
            }

            function renderAsignados(cuadrante) {
                const lista = Array.isArray(ASIG[cuadrante]) ? ASIG[cuadrante] : [];
                const count = document.getElementById('count-asignados');
                if (count) count.textContent = String(lista.length);

                if (ES_GLOBAL) {
                    renderAgrupadoPorDepto(lista);
                } else {
                    renderListaSimple(lista);
                }
            }

            window.actualizarGridConDatos = function(rendimientosPorCuadrante, totalEmpleados, empleadosEvaluados) {
                ASIG = rendimientosPorCuadrante || {};

                // 1. Actualizar los badges de conteo sobre los cuadrantes del grid
                for (let i = 1; i <= 9; i++) {
                    const btn = document.querySelector(`.cuadrante-btn[data-cuadrante="${i}"]`);
                    if (!btn) continue;

                    // Limpiar badge anterior si existe
                    const oldBadge = btn.querySelector('.absolute');
                    if (oldBadge) {
                        oldBadge.remove();
                    }

                    // Si hay asignaciones en este cuadrante, pintar el nuevo badge
                    const lista = ASIG[i] || [];
                    const count = lista.length;
                    if (count > 0) {
                        const badgeDiv = document.createElement('div');
                        badgeDiv.className = 'absolute -top-2 -right-2 bg-primary text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-card select-none pointer-events-none';
                        badgeDiv.textContent = count;
                        btn.appendChild(badgeDiv);
                    }
                }

                // 2. Actualizar los KPIs en el panel de resumen
                if (totalEmpleados !== undefined && empleadosEvaluados !== undefined) {
                    const pendientes = Math.max(0, totalEmpleados - empleadosEvaluados);
                    const pct = totalEmpleados > 0 ? Math.min(100, Math.round((empleadosEvaluados / totalEmpleados) * 100)) : 0;

                    const pendEl = document.getElementById('empleados-pendientes');
                    const evalEl = document.getElementById('empleados-evaluados');
                    const totEl = document.getElementById('total-empleados');
                    const pctEl = document.getElementById('progreso-porcentaje');
                    const barEl = document.getElementById('avance-bar');

                    if (pendEl) pendEl.textContent = pendientes;
                    if (evalEl) evalEl.textContent = empleadosEvaluados;
                    if (totEl) totEl.textContent = totalEmpleados;
                    if (pctEl) pctEl.textContent = `${pct}%`;
                    if (barEl) {
                        barEl.style.width = `${pct}%`;
                    }
                }
            };

            let lastTriggerBtn = null;

            window.mostrarModal = function(cuadrante) {
                const meta = BASE_DESC[cuadrante] || {
                    title: `Cuadrante ${cuadrante}`,
                    desc: ''
                };
                const title = document.getElementById('modal-title');
                const desc = document.getElementById('modal-desc');
                if (title) title.textContent = meta.title;
                if (desc) desc.textContent = meta.desc || '';

                renderAsignados(cuadrante);

                const modal = document.getElementById('modal-empleados');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.remove('modal-anim-close');
                    modal.classList.add('modal-anim-open');
                    document.getElementById('btn-cerrar-modal')?.focus();
                    document.getElementById('modal-container')?.setAttribute('aria-hidden', 'false');
                }
            }

            window.cerrarModal = function() {
                const modal = document.getElementById('modal-empleados');
                if (!modal) return;
                modal.classList.remove('modal-anim-open');
                modal.classList.add('modal-anim-close');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.getElementById('modal-container')?.setAttribute('aria-hidden', 'true');
                    if (lastTriggerBtn) lastTriggerBtn.focus();
                }, 200);
            }

            document.addEventListener('DOMContentLoaded', () => {
                const bar = document.getElementById('avance-bar');
                if (bar) {
                    const pctText = bar.style.width?.replace('%', '').trim();
                    const pct = Number.isFinite(+pctText) ? +pctText : parseInt(bar.style.width, 10) || 0;
                    bar.style.width = pct + '%';
                }

                document.querySelectorAll('.cuadrante-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        lastTriggerBtn = btn;
                        const cuad = parseInt(btn.getAttribute('data-cuadrante'), 10);
                        mostrarModal(cuad);
                    });
                });

                document.getElementById('modal-backdrop')?.addEventListener('click', cerrarModal);
                document.getElementById('btn-cerrar-modal')?.addEventListener('click', cerrarModal);
                document.getElementById('modal-empleados')?.addEventListener('click', (e) => {
                    if (e.target.id === 'modal-empleados') cerrarModal();
                });
                document.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Escape') {
                        const modal = document.getElementById('modal-empleados');
                        if (modal && !modal.classList.contains('hidden')) cerrarModal();
                    }
                });
            });
        })();
    </script>
@endsection

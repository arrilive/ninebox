@extends('layouts.app')

@section('title', 'Mi Equipo | NineBox')

@section('header')
    <x-breadcrumb :items="[
        [
            'label' => auth()->user()?->esSuperadmin() || auth()->user()?->esDueno() ? 'Evaluaciones' : 'Mi equipo',
            'url' => null,
        ],
    ]" />
    @php
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
        $anioActual = request('anio', $anio ?? now()->year);
        $mesActual = request('mes', $mes ?? now()->month);
        $authEmail = auth()->user()->correo ?? (auth()->user()->email ?? '');
        $primerAnio = 2025;
    @endphp
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('Mi Equipo') }}
        </h2>
        <div class="flex items-center space-x-3">
            <!-- Selector de Año -->
            <select id="select-anio" class="form-input !w-auto">
                @for ($y = now()->year; $y >= $primerAnio; $y--)
                    <option value="{{ $y }}" {{ (int) $y === (int) $anioActual ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <!-- Selector de Mes -->
            <select id="select-mes" class="form-input !w-auto">
                @foreach ($meses as $num => $m)
                    <option value="{{ $num }}" {{ (int) $num === (int) $mesActual ? 'selected' : '' }}>
                        {{ $m }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 select-none">
                <div class="card cursor-pointer hover:border-primary/40 transition-colors" onclick="filtrarRapido('')">
                    <div class="text-sm font-medium text-ink-2">{{ __('Total') }}</div>
                    <div id="kpi-total" class="text-2xl font-semibold text-ink mt-1">0</div>
                </div>
                <div class="card cursor-pointer hover:border-primary/40 transition-colors"
                    onclick="filtrarRapido('evaluado')">
                    <div class="text-sm font-medium text-ink-2">{{ __('Evaluados') }}</div>
                    <div id="kpi-evaluados" class="text-2xl font-semibold text-success mt-1">0</div>
                </div>
                <div class="card cursor-pointer hover:border-primary/40 transition-colors"
                    onclick="filtrarRapido('en_proceso')">
                    <div class="text-sm font-medium text-ink-2">{{ __('En progreso') }}</div>
                    <div id="kpi-proceso" class="text-2xl font-semibold text-warning mt-1">0</div>
                </div>
                <div class="card cursor-pointer hover:border-primary/40 transition-colors"
                    onclick="filtrarRapido('no_iniciado')">
                    <div class="text-sm font-medium text-ink-2">{{ __('Sin evaluar') }}</div>
                    <div id="kpi-noiniciado" class="text-2xl font-semibold text-ink-2 mt-1">0</div>
                </div>
            </div>

            <!-- Tabla de Colaboradores -->
            <div class="bg-canvas border border-border rounded-lg overflow-hidden shadow-card">
                @if (empty($empleados))
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-ink-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-ink">{{ __('No hay colaboradores') }}</h3>
                        <p class="mt-1 text-sm text-ink-2">{{ __('No tienes colaboradores asignados en este periodo.') }}
                        </p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-surface">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                    {{ __('Nombre') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                    {{ __('Departamento') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                    {{ __('Estado') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                    {{ __('Progreso') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tabla-cuerpo-empleados" class="bg-white divide-y divide-border">
                            @foreach ($empleados as $e)
                                <tr class="fila-empleado hover:bg-surface/50 transition-colors duration-150"
                                    data-empleado-id="{{ $e['id'] }}" data-estado="{{ $e['estado'] }}"
                                    data-progreso="{{ $e['progreso'] }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                        {{ $e['nombre'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                        {{ $e['departamento_nombre'] ?? ($e['departamento'] ?? '-') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                        <span class="badge estado-badge">
                                            {{ ucfirst(str_replace('_', ' ', $e['estado'])) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2 progreso-text">
                                        {{ $e['progreso'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('encuestas.show', ['empleado' => $e['id'], 'anio' => $anioActual, 'mes' => $mesActual]) }}"
                                            class="text-primary hover:text-primary-hover btn-accion-text">
                                            {{ $e['estado'] === 'evaluado' ? __('Ver') : __('Evaluar') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div id="empty-state" class="card text-center p-12 mt-6 hidden">
                <p class="text-ink-2 text-base">{{ __('No se encontraron colaboradores para el criterio seleccionado.') }}
                </p>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('select-anio').addEventListener('change', function() {
            let url = new URL(window.location.href);
            url.searchParams.set('anio', this.value);
            window.location.href = url.toString();
        });

        document.getElementById('select-mes').addEventListener('change', function() {
            let url = new URL(window.location.href);
            url.searchParams.set('mes', this.value);
            window.location.href = url.toString();
        });

        window.filtrarRapido = function(filtro) {
            const rows = document.querySelectorAll('.fila-empleado');
            let shown = 0;
            rows.forEach(row => {
                let estado = row.getAttribute('data-computed-estado') || row.getAttribute('data-estado');
                if (!filtro || estado === filtro) {
                    row.style.display = '';
                    shown++;
                } else {
                    row.style.display = 'none';
                }
            });

            const empty = document.getElementById('empty-state');
            if (empty) {
                empty.classList.toggle('hidden', shown > 0);
            }
        };

        (function() {
            'use strict';
            const AUTH_EMAIL = @json($authEmail);
            const ANIO_ACTUAL = @json($anioActual);
            const MES_ACTUAL = @json($mesActual);
            const TOTAL = 10;

            function draftKey(empId) {
                return `encuesta_${AUTH_EMAIL}_${empId}_${ANIO_ACTUAL}_${MES_ACTUAL}`;
            }

            function readDraft(empId) {
                try {
                    const raw = sessionStorage.getItem(draftKey(empId));
                    return raw ? JSON.parse(raw) : null;
                } catch (_) {
                    return null;
                }
            }

            function respuestasContestadas(d) {
                if (!d || !Array.isArray(d.respuestas)) return 0;
                return d.respuestas.filter(r => r && r.puntaje !== null && r.puntaje !== '' && r.puntaje !== undefined)
                    .length;
            }

            function applyStateFusion() {
                const rows = document.querySelectorAll('.fila-empleado');
                let cTotal = 0,
                    cEval = 0,
                    cProc = 0,
                    cNoIni = 0;

                rows.forEach(row => {
                    const id = parseInt(row.dataset.empleadoId, 10);
                    const estadoSrv = (row.dataset.estado || 'no_iniciado').trim().toLowerCase();
                    const progText = row.querySelector('.progreso-text');
                    const badgeEl = row.querySelector('.estado-badge');
                    const btnEl = row.querySelector('.btn-accion-text');

                    // Si el servidor indica que ya está evaluado, limpiamos cualquier draft local para evitar desincronizaciones
                    if (estadoSrv === 'evaluado') {
                        sessionStorage.removeItem(draftKey(id));
                    }

                    let estado = estadoSrv;
                    let prog = row.dataset.progreso || '0/10';

                    if (estadoSrv !== 'evaluado') {
                        const d = readDraft(id);
                        const filled = respuestasContestadas(d);
                        if (filled > 0) {
                            estado = 'en_proceso';
                            prog = `${filled}/${TOTAL}`;
                        } else {
                            estado = 'no_iniciado';
                            prog = `0/${TOTAL}`;
                        }
                    }

                    row.setAttribute('data-computed-estado', estado);

                    if (progText) progText.textContent = prog;

                    if (badgeEl) {
                        badgeEl.className = 'badge';
                        if (estado === 'evaluado') {
                            badgeEl.classList.add('bg-green-100', 'text-success');
                            badgeEl.textContent = 'Evaluada';
                        } else if (estado === 'en_proceso') {
                            badgeEl.classList.add('bg-amber-100', 'text-warning');
                            badgeEl.textContent = 'En progreso';
                        } else {
                            badgeEl.classList.add('bg-gray-100', 'text-ink-2');
                            badgeEl.textContent = 'Sin evaluar';
                        }
                    }

                    if (btnEl) {
                        btnEl.textContent = (estado === 'evaluado') ? 'Ver' : 'Evaluar';
                    }

                    cTotal++;
                    if (estado === 'evaluado') cEval++;
                    else if (estado === 'en_proceso') cProc++;
                    else cNoIni++;
                });

                const totalEl = document.getElementById('kpi-total');
                const evalEl = document.getElementById('kpi-evaluados');
                const procEl = document.getElementById('kpi-proceso');
                const noiniEl = document.getElementById('kpi-noiniciado');

                if (totalEl) totalEl.textContent = cTotal;
                if (evalEl) evalEl.textContent = cEval;
                if (procEl) procEl.textContent = cProc;
                if (noiniEl) noiniEl.textContent = cNoIni;

                const url = new URL(window.location.href);
                const filtroUrl = url.searchParams.get('filtro');
                if (filtroUrl) {
                    filtrarRapido(filtroUrl);
                } else {
                    filtrarRapido('');
                }
            }

            document.addEventListener('DOMContentLoaded', applyStateFusion);
            window.addEventListener('load', applyStateFusion);
        })();
    </script>
@endpush

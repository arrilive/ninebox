@extends('layouts.app')

@php
    $viewOnly = ($soloLectura ?? false) || session('encuesta_enviada', false) || session('ya_enviada', false);
@endphp

@section('title', 'Evaluación | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Mi equipo', 'url' => route('encuestas.empleados')],
        ['label' => $empleado->nombre_completo, 'url' => null],
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
        $anioActual = $anio ?? now()->year;
        $mesActual = $mes ?? now()->month;
        $totalPreguntas = $totalPreguntas ?? 10;
        $authEmail = auth()->user()->correo ?? (auth()->user()->email ?? '');
        $preguntasDesempeno = $preguntas->where('categoria', 'desempeno');
        $preguntasPotencial = $preguntas->where('categoria', 'potencial')->values();
        $offsetPot = $preguntasDesempeno->count();
    @endphp

    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="font-semibold text-xl text-ink leading-tight">
                {{ __('Evaluación de') }} {{ $empleado->nombre_completo }}
            </h2>
            <p class="text-sm text-ink-2 mt-1">
                <span class="badge bg-green-100 text-success font-semibold">
                    {{ $meses[$mesActual] }} {{ $anioActual }}
                </span>
            </p>
        </div>
        <div>
            <a href="{{ route('encuestas.empleados', ['anio' => $anioActual, 'mes' => $mesActual]) }}" class="btn-secondary">
                {{ __('← Volver al equipo') }}
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Barra de Progreso -->
            <div class="card anim-fade-up">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-ink">{{ __('Progreso de la evaluación') }}</span>
                    <span id="progreso-texto" class="text-sm font-semibold text-ink-2 transition-opacity duration-150">0/{{ $totalPreguntas }}</span>
                </div>
                <div class="w-full bg-surface h-2 rounded overflow-hidden border border-border">
                    <div id="progreso-barra" class="bg-primary h-full transition-all duration-300" style="width: 0%;"></div>
                </div>
            </div>

            <!-- Formulario de Evaluación -->
            <form id="form-encuesta"
                @if (!$viewOnly) method="POST" action="{{ route('encuestas.submit', ['empleado' => $empleadoId, 'anio' => $anioActual, 'mes' => $mesActual]) }}" @endif
                class="space-y-8">
                @csrf

                <!-- Sección: Desempeño -->
                <div class="space-y-6 anim-fade-up anim-delay-1">
                    <div class="border-b border-border pb-2">
                        <h3 class="text-lg font-semibold text-ink">{{ __('1. Desempeño Actual') }}</h3>
                        <p class="text-sm text-ink-2">
                            {{ __('Evalúe el rendimiento y contribución actual del colaborador.') }}</p>
                    </div>

                    @foreach ($preguntasDesempeno as $i => $p)
                        @php
                            $resp = $respuestas[$p->id] ?? null;
                            $valor = $resp->puntaje ?? null;
                        @endphp
                        <div class="card fila-pregunta-card space-y-4 anim-fade-up anim-delay-2 card-hover">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-ink leading-relaxed">
                                        {{ $loop->iteration }}. {{ $p->texto }}
                                    </h4>
                                </div>
                                <div class="flex flex-col items-start md:items-end gap-2 shrink-0">
                                    <div class="flex items-center space-x-1.5">
                                        @for ($n = 1; $n <= 5; $n++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="respuestas[{{ $i }}][puntaje]"
                                                    value="{{ $n }}"
                                                    {{ $valor !== null && (int) $valor === $n ? 'checked' : '' }}
                                                    class="sr-only peer puntaje-input" {{ $viewOnly ? 'disabled' : '' }}
                                                    onchange="updateUI()">
                                                <div
                                                    class="w-10 h-10 border border-border rounded flex items-center justify-center text-sm font-semibold text-ink bg-canvas hover:bg-surface peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary active:scale-95 transition-all duration-150 select-none">
                                                    {{ $n }}
                                                </div>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="flex justify-between w-full text-[10px] text-ink-3 px-1 select-none">
                                        <span>{{ __('1 - Nunca') }}</span>
                                        <span>{{ __('5 - Siempre') }}</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="respuestas[{{ $i }}][pregunta_id]"
                                value="{{ $p->id }}">
                            <input type="hidden" name="respuestas[{{ $i }}][comentario]" value="">
                        </div>
                    @endforeach
                </div>

                <!-- Sección: Potencial -->
                <div class="space-y-6 anim-fade-up anim-delay-3">
                    <div class="border-b border-border pb-2">
                        <h3 class="text-lg font-semibold text-ink">{{ __('2. Potencial de Desarrollo') }}</h3>
                        <p class="text-sm text-ink-2">
                            {{ __('Evalúe la capacidad de crecimiento futuro y adaptabilidad.') }}</p>
                    </div>

                    @php $offsetPot = count($preguntasDesempeno); @endphp
                    @foreach ($preguntasPotencial as $i => $p)
                        @php
                            $index = $offsetPot + $i;
                            $resp = $respuestas[$p->id] ?? null;
                            $valor = $resp->puntaje ?? null;
                        @endphp
                        <div class="card fila-pregunta-card space-y-4 anim-fade-up anim-delay-4 card-hover">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-ink leading-relaxed">
                                        {{ $offsetPot + $loop->iteration }}. {{ $p->texto }}
                                    </h4>
                                </div>
                                <div class="flex flex-col items-start md:items-end gap-2 shrink-0">
                                    <div class="flex items-center space-x-1.5">
                                        @for ($n = 1; $n <= 5; $n++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="respuestas[{{ $index }}][puntaje]"
                                                    value="{{ $n }}"
                                                    {{ $valor !== null && (int) $valor === $n ? 'checked' : '' }}
                                                    class="sr-only peer puntaje-input" {{ $viewOnly ? 'disabled' : '' }}
                                                    onchange="updateUI()">
                                                <div
                                                    class="w-10 h-10 border border-border rounded flex items-center justify-center text-sm font-semibold text-ink bg-canvas hover:bg-surface peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary active:scale-95 transition-all duration-150 select-none">
                                                    {{ $n }}
                                                </div>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="flex justify-between w-full text-[10px] text-ink-3 px-1 select-none">
                                        <span>{{ __('1 - Nunca') }}</span>
                                        <span>{{ __('5 - Siempre') }}</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="respuestas[{{ $index }}][pregunta_id]"
                                value="{{ $p->id }}">
                            <input type="hidden" name="respuestas[{{ $index }}][comentario]" value="">
                        </div>
                    @endforeach
                </div>

                <!-- Comentarios Generales -->
                <div class="card space-y-3 anim-fade-up anim-delay-5 card-hover">
                    <label class="form-label text-base font-semibold">
                        {{ __('Comentarios Generales (Opcional)') }}
                    </label>
                    <textarea name="comentario_general" id="comentario-general" class="form-input" rows="4"
                        placeholder="{{ __('Escriba observaciones generales sobre el desempeño o potencial del colaborador...') }}"
                        {{ $viewOnly ? 'readonly' : '' }}>{{ old('comentario_general', $comentarioGeneral ?? '') }}</textarea>
                </div>

                <!-- Acciones Formulario -->
                <div class="pt-4 anim-fade-up anim-delay-5">
                    @if ($viewOnly)
                        <div class="p-4 bg-green-50 border-l-4 border-success text-success text-sm rounded">
                            {{ __('Esta evaluación ya fue enviada y se encuentra en modo lectura.') }}
                        </div>
                    @else
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-border">
                            <button id="btn-enviar" type="submit" name="accion" value="enviar" class="btn-primary">
                                {{ __('Enviar evaluación') }}
                            </button>
                        </div>
                    @endif
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';
            const AUTH_EMAIL = @json($authEmail);
            const EMPLEADO_ID = @json($empleadoId);
            const ANIO = @json($anioActual);
            const MES = @json($mesActual);
            const TOTAL = {{ $totalPreguntas }};
            const VIEW_ONLY = @json($viewOnly);
            const STORAGE_KEY = `encuesta_${AUTH_EMAIL}_${EMPLEADO_ID}_${ANIO}_${MES}`;

            window.updateUI = function() {
                const total = TOTAL;
                const filled = document.querySelectorAll('.puntaje-input:checked').length;

                const textEl = document.getElementById('progreso-texto');
                const barEl  = document.getElementById('progreso-barra');

                if (textEl) {
                    textEl.classList.add('opacity-0');
                    setTimeout(() => {
                        textEl.textContent = `${filled}/${total}`;
                        textEl.classList.remove('opacity-0');
                    }, 50);
                }
                if (barEl) {
                    const pct = Math.min(100, Math.round((filled / total) * 100));
                    barEl.style.width = `${pct}%`;
                }

                const btn = document.getElementById('btn-enviar');
                if (btn) {
                    if (VIEW_ONLY) {
                        btn.style.display = 'none';
                        const parentDiv = btn.closest('.flex');
                        if (parentDiv) parentDiv.style.display = 'none';
                    } else {
                        if (filled >= total) {
                            btn.textContent = 'Enviar evaluación';
                            btn.disabled = false;
                            btn.classList.remove('opacity-50', 'cursor-not-allowed');
                            btn.classList.add('btn-primary');
                        } else {
                            btn.textContent = `Guardar (${filled}/${total})`;
                            btn.disabled = false;
                            btn.classList.remove('opacity-50', 'cursor-not-allowed');
                            btn.classList.add('btn-primary');
                        }
                    }
                }
            };

            function saveDraftLocal() {
                if (VIEW_ONLY) return;
                const respuestas = [];
                document.querySelectorAll('.fila-pregunta-card').forEach(card => {
                    const questionId = card.querySelector('input[name*="[pregunta_id]"]').value;
                    const selectedRadio = card.querySelector('.puntaje-input:checked');
                    respuestas.push({
                        pregunta_id: parseInt(questionId, 10),
                        puntaje: selectedRadio ? parseInt(selectedRadio.value, 10) : null,
                        comentario: null
                    });
                });
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
                    respuestas,
                    comentario_general: document.getElementById('comentario-general')?.value || ''
                }));
            }

            function restoreFromDraft() {
                if (VIEW_ONLY) {
                    // Restaurar estado visual de los botones desde los atributos HTML
                    // (los inputs tienen disabled en modo lectura, usar getAttribute no .value)
                    document.querySelectorAll('.puntaje-input').forEach(function(input) {
                        const val = input.getAttribute('value');
                        const pid = input.getAttribute('data-pregunta-id');
                        if (val && val !== '' && val !== 'null') {
                            document.querySelectorAll(
                                '.rating-btn[data-pregunta-id="' + pid + '"]'
                            ).forEach(function(b) {
                                b.classList.remove('rating-btn-selected');
                            });
                            const btn = document.querySelector(
                                '.rating-btn[data-pregunta-id="' + pid + '"][data-value="' + val + '"]'
                            );
                            if (btn) btn.classList.add('rating-btn-selected');
                        }
                    });
                    updateUI();
                    return;
                }

                const raw = sessionStorage.getItem(STORAGE_KEY);
                if (!raw) return;
                try {
                    const draft = JSON.parse(raw);
                    if (draft && Array.isArray(draft.respuestas)) {
                        draft.respuestas.forEach((r, idx) => {
                            if (r.puntaje) {
                                const radio = document.querySelector(
                                    `input[name="respuestas[${idx}][puntaje]"][value="${r.puntaje}"]`);
                                if (radio) radio.checked = true;
                            }
                        });
                    }
                    if (draft && draft.comentario_general && document.getElementById('comentario-general')) {
                        document.getElementById('comentario-general').value = draft.comentario_general;
                    }
                } catch (_) {}
            }

            document.getElementById('form-encuesta')?.addEventListener('submit', function(e) {
                const isEnviar = document.activeElement && document.activeElement.id === 'btn-enviar';
                const filled = Array.from(document.querySelectorAll('.puntaje-input:checked')).length;

                if (isEnviar) {
                    if (filled < TOTAL) {
                        e.preventDefault();
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'accion';
                        input.value = 'borrador';
                        this.appendChild(input);
                        saveDraftLocal();
                        this.submit();
                        return;
                    }

                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Revisa antes de enviar',
                        text: 'Una vez enviada la encuesta, no podrás editarla después.',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, enviar',
                        cancelButtonText: 'Cancelar',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'rounded-lg bg-canvas border border-border shadow-card p-6',
                            title: 'text-base font-semibold text-ink',
                            htmlContainer: 'text-sm text-ink-2 mt-2',
                            confirmButton: 'btn-primary mr-2',
                            cancelButton: 'btn-secondary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            sessionStorage.removeItem(STORAGE_KEY);
                            this.submit();
                        }
                    });
                } else {
                    saveDraftLocal();
                }
            });

            // Auto-save drafts on any input change
            if (!VIEW_ONLY) {
                document.querySelectorAll('.puntaje-input').forEach(input => {
                    input.addEventListener('change', saveDraftLocal);
                });
                const genComment = document.getElementById('comentario-general');
                if (genComment) {
                    genComment.addEventListener('input', saveDraftLocal);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                restoreFromDraft();
                updateUI();
            });
        })();
    </script>
@endpush

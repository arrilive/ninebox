<x-app-layout>
    @section('title', 'Evaluación | 9-Box')
    @php
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $anioActual = $anio ?? now()->year;
    $mesActual  = $mes ?? now()->month;
    $total      = $totalPreguntas ?? 10;
    $done       = $contestadas ?? 0;
    $authId     = auth()->id();
    $authEmail  = auth()->user()->correo ?? (auth()->user()->email ?? '');
    $preguntasDesempeno = $preguntas->where('categoria', 'desempeno');
    $preguntasPotencial = $preguntas->where('categoria', 'potencial');
    $viewOnly   = ($soloLectura ?? false) || session('encuesta_enviada', false) || session('ya_enviada', false);
    $offsetPot  = $preguntasDesempeno->count(); 
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40 overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
                    <div class="absolute inset-0 bg-black/10"></div>

                    <a href="{{ route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual]) }}"
                       class="btn btn-ghost absolute right-6 top-6 px-4 py-2 text-sm !text-white z-10">
                        <svg class="w-4 h-4 mr-2 !stroke-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </a>

                    <div class="relative">
                        <h1 class="text-4xl font-extrabold text-white tracking-tight pr-28">Evaluación 9-Box</h1>
                        <p class="mt-2 text-white font-bold tracking-tight text-lg md:text-xl pr-28">
                            {{ $meses[$mesActual] }} {{ $anioActual }} •
                            {{ trim(strtok($empleado->nombre, ' ').' '.$empleado->apellido_paterno) }}
                        </p>
                    </div>
                </div>

                {{-- Contador --}}
                <div class="px-6 pt-6">
                    <div class="flex items-center justify-between text-sm text-gray-900 dark:text-white mb-2">
                        <span class="font-semibold">
                            {{ $viewOnly ? 'Resultados de la evaluación' : 'Preguntas contestadas' }}
                        </span>
                        <span id="progress-count" class="font-bold animate-count text-base">{{ $done }}/{{ $total }}</span>
                    </div>
                    @error('ninebox')
                        <p class="mt-2 text-xs text-rose-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @if (session('encuesta_enviada'))
                        <p class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">Evaluación enviada y cerrada. Vista en modo lectura.</p>
                    @endif
                </div>

                {{-- Instrucciones --}}
                <div class="px-6 pt-4">
                    <div class="border border-white/10 dark:border-gray-700/40 rounded-xl p-6 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 dark:from-blue-900/20 dark:to-indigo-900/20">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-6 h-6 text-black dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-black dark:text-white mb-2 text-lg">
                                {{ $viewOnly ? 'Vista de resultados' : 'Instrucciones evaluación de desempeño' }}
                                </h3>

                                <p class="text-base text-black dark:text-white leading-relaxed">
                                Evalúe al colaborador en las dimensiones de <strong>Desempeño actual</strong> y <strong>Potencial de desarrollo</strong>
                                utilizando una escala del <strong>1</strong> al <strong>5</strong>, donde <strong>1</strong> representa “Nunca” y <strong>5</strong> representa “Siempre”.
                                
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Formulario / Vista --}}
                <form id="form-encuesta"
                    @if(!$viewOnly)
                    method="POST" action="{{ route('encuestas.submit', ['empleado'=>$empleadoId,'anio'=>$anioActual,'mes'=>$mesActual]) }}"
                    @endif
                    class="p-7 space-y-7">
                    @csrf

                    {{-- Tabs --}}
                    <div class="tabs-wrap">
                      <div class="tablist" role="tablist" aria-label="Dimensiones 9-Box">
                        <button type="button" class="tab-btn" role="tab" aria-selected="true" aria-controls="tab-desempeno" id="tabbtn-desempeno" data-tab="desempeno" tabindex="0">Desempeño actual</button>
                        <button type="button" class="tab-btn" role="tab" aria-selected="false" aria-controls="tab-potencial" id="tabbtn-potencial" data-tab="potencial" tabindex="-1">Potencial de desarrollo</button>
                      </div>
                    </div>

                    {{-- Desempeño --}}
                    <section id="tab-desempeno" class="tab-content space-y-6">
                        <div class="border-l-4 border-emerald-600 pl-4 py-2 bg-emerald-50/50 dark:bg-emerald-900/20">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Evalúe el rendimiento y contribución actual</h2>
                        </div>

                        @foreach ($preguntasDesempeno as $i => $p)
                            @php
                                $resp  = $respuestas[$p->id] ?? null;
                                $valor = $resp->puntaje ?? null;
                            @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-soft">
                                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white text-base leading-relaxed">
                                            {{ $loop->iteration }}) {{ $p->texto }}
                                        </p>
                                    </div>
                                    <div class="lg:w-48 {{ $viewOnly ? 'pointer-events-none opacity-80' : '' }}">
                                        <label class="sr-only">Puntaje para: {{ $p->texto }}</label>
                                        <div class="rating-buttons" data-readonly="{{ $viewOnly ? '1' : '0' }}">
                                            @for ($n=1; $n<=5; $n++)
                                                <button type="button"
                                                    class="rating-btn {{ $valor !== null && (int)$valor === $n ? 'rating-btn-selected' : '' }}"
                                                    data-value="{{ $n }}"
                                                    data-pregunta-id="{{ $p->id }}"
                                                    {{ $viewOnly ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                                                    {{ $n }}
                                                </button>
                                            @endfor
                                        </div>
                                        <input type="hidden"
                                            name="respuestas[{{ $i }}][puntaje]"
                                            value="{{ $valor }}"
                                            class="puntaje-input"
                                            data-pregunta-id="{{ $p->id }}"
                                            {{ $viewOnly ? 'disabled' : '' }}>
                                    </div>
                                </div>
                                <input type="hidden" name="respuestas[{{ $i }}][pregunta_id]" value="{{ $p->id }}" {{ $viewOnly ? 'disabled' : '' }}>
                            </div>
                        @endforeach

                        <div class="text-center pt-4">
                            <button type="button" class="btn btn-primary px-6 py-2 text-sm" onclick="switchToTab('potencial')">Continuar a potencial</button>
                        </div>
                    </section>

                    {{-- Potencial (numeración continua) --}}
                    <section id="tab-potencial" class="tab-content space-y-6 hidden">
                        <div class="border-l-4 border-cyan-500 pl-4 py-2 bg-cyan-50/50 dark:bg-cyan-900/20">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Evalúe la capacidad de crecimiento</h2>
                        </div>

                        @foreach ($preguntasPotencial as $i => $p)
                            @php
                                $index = $preguntasDesempeno->count() + $i;
                                $resp  = $respuestas[$p->id] ?? null;
                                $valor = $resp->puntaje ?? null;
                                $numero = $offsetPot + $loop->iteration; // continua 6,7,8,...
                            @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-soft">
                                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white text-base leading-relaxed">
                                            {{ $numero }}) {{ $p->texto }}
                                        </p>
                                    </div>
                                    <div class="lg:w-48 {{ $viewOnly ? 'pointer-events-none opacity-80' : '' }}">
                                        <label class="sr-only">Puntaje para: {{ $p->texto }}</label>
                                        <div class="rating-buttons" data-readonly="{{ $viewOnly ? '1' : '0' }}">
                                            @for ($n=1; $n<=5; $n++)
                                                <button type="button"
                                                    class="rating-btn {{ $valor !== null && (int)$valor === $n ? 'rating-btn-selected' : '' }}"
                                                    data-value="{{ $n }}"
                                                    data-pregunta-id="{{ $p->id }}"
                                                    {{ $viewOnly ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                                                    {{ $n }}
                                                </button>
                                            @endfor
                                        </div>
                                        <input type="hidden"
                                            name="respuestas[{{ $index }}][puntaje]"
                                            value="{{ $valor }}"
                                            class="puntaje-input"
                                            data-pregunta-id="{{ $p->id }}"
                                            {{ $viewOnly ? 'disabled' : '' }}>
                                    </div>
                                </div>
                                <input type="hidden" name="respuestas[{{ $index }}][pregunta_id]" value="{{ $p->id }}" {{ $viewOnly ? 'disabled' : '' }}>
                            </div>
                        @endforeach

                        <div class="text-center pt-4">
                            <button type="button" class="btn btn-primary px-6 py-2 text-sm" onclick="switchToTab('desempeno')">Volver a desempeño</button>
                        </div>
                    </section>

                    {{-- Comentario final -> feedback_publico --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-soft">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-base font-semibold text-gray-700 dark:text-white">
                                Comentarios adicionales (opcional)
                            </label>
                            <span id="word-count" class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                0 / 100 palabras
                            </span>
                        </div>

                        <textarea
                            id="comentario-general"
                            name="comentario_general"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 p-3 transition-all duration-200 text-sm resize-none overflow-hidden"
                            placeholder="Comparta observaciones adicionales sobre el colaborador, como fortalezas, áreas de oportunidad, logros o comentarios relevantes..."
                            {{ $viewOnly ? 'readonly' : '' }}
                            style="min-height: 100px;">{{ old('comentario_general', $comentarioGeneral ?? '') }}</textarea>

                        <p id="limit-warning" class="text-xs text-rose-600 dark:text-red-400 mt-2 hidden">
                            Has alcanzado el límite de 100 palabras
                        </p>
                    </div>

                    {{-- Botones finales --}}
                    <div class="pt-4">
                        @if ($viewOnly)
                            <a href="{{ route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual]) }}" class="btn btn-primary btn-block text-base py-3">
                                Volver al listado
                            </a>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
                                Evaluación completada
                            </p>
                        @else
                            <button id="btn-submit" type="submit" class="btn btn-primary btn-block text-base py-3">
                                {{ $done >= $total ? 'Enviar Evaluación' : 'Guardar progreso' }}
                            </button>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
                                {{ $done >= $total ?
                                'Evaluación completa - Lista para enviar' :
                                "Complete las {$total} preguntas para habilitar el envío final" }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Estilos --}}
    <style>
    :root{ --brand-indigo:#4338ca; --brand-purple:#6d28d9; --accent-cyan:#0891b2;
        --danger-red:#dc2626; --success-green:#059669; --modal-bg-light:rgba(255,255,255,.96);
        --modal-bg-dark:rgba(8,10,20,.92); --glass-0:rgba(255,255,255,.06); --glass-1:rgba(255,255,255,.04);
        --shadow-soft:0 8px 18px rgba(2,6,23,.10); --shadow-strong:0 18px 48px rgba(2,6,23,.18);
        --radius-lg:.95rem; --radius-md:.9rem; --anim-fast:.12s; }
    *{box-sizing:border-box} :focus{outline:none}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-weight:700;line-height:1;border-radius:var(--radius-md);
        padding:.7rem 1.15rem;border:1px solid transparent;cursor:pointer;transition:transform var(--anim-fast),box-shadow var(--anim-fast),opacity var(--anim-fast),background var(--anim-fast);box-shadow:var(--shadow-soft)}
    .btn:hover{transform:translateY(-1px)} .btn:active{transform:translateY(0)} .btn:focus-visible{outline:3px solid rgba(79,70,229,.18);outline-offset:3px}
    .btn-block{width:100%} .btn-primary{color:#fff;background-image:linear-gradient(90deg,var(--brand-indigo) 0%,var(--brand-purple) 100%);border-color:rgba(0,0,0,.06)}
    .btn-ghost{background:rgba(15,23,42,.06);color:#0f172a;border-color:rgba(15,23,42,.10)}
    @media (prefers-color-scheme:dark){.btn-ghost{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.14)}}
    .rating-buttons{display:flex;gap:.5rem;justify-content:center}
    .rating-btn{width:2.75rem;height:2.75rem;border-radius:.75rem;border:2px solid #e5e7eb;background:#fff;color:#6b7280;font-weight:bold;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center}
    @media (prefers-color-scheme:dark){.rating-btn{border-color:#4b5563;background:#1f2937;color:#9ca3af}}
    .rating-btn:hover{transform:scale(1.08);border-color:#9ca3af}
    .rating-btn-selected{background:#4338ca;color:#fff;border-color:#4338ca;transform:scale(1.08);box-shadow:0 4px 12px rgba(67,56,202,.28)}
    .tabs-wrap{background:linear-gradient(180deg,rgba(2,6,23,.03),rgba(2,6,23,0));border-radius:var(--radius-lg);padding:.5rem}
    .tablist{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
    .tab-btn{position:relative;display:inline-flex;align-items:center;justify-content:center;width:100%;padding:.9rem 1.1rem;border-radius:.9rem;font-weight:800;font-size:.975rem;letter-spacing:.2px;background:rgba(15,23,42,.06);color:#334155;border:1.5px solid rgba(15,23,42,.10);box-shadow:var(--shadow-soft);transition:transform var(--anim-fast),box-shadow var(--anim-fast),background var(--anim-fast),color var(--anim-fast),border-color var(--anim-fast)}
    .tab-btn:hover{transform:translateY(-1px)} .tab-btn:active{transform:translateY(0)}
    .tab-btn.active,.tab-btn[aria-selected="true"]{color:#fff;background:var(--brand-indigo);border-color:rgba(0,0,0,.08);box-shadow:0 8px 18px rgba(67,56,202,.22),inset 0 0 0 1px rgba(255,255,255,.06)}
    .tab-content{animation:fadeIn .24s ease-out}
    @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .animate-count{animation:fadeIn .3s ease-in-out}
    #comentario-general{resize:none!important}
    </style>

    {{-- JS --}}
    <script>
    function ready(fn){ if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn(); }

    function switchToTab(tabName){
      const tabBtns=document.querySelectorAll('.tab-btn[role="tab"]');
      const tabContents=document.querySelectorAll('.tab-content');
      tabBtns.forEach(b=>{const isTarget=b.dataset.tab===tabName;b.classList.toggle('active',isTarget);b.setAttribute('aria-selected',isTarget?'true':'false');b.tabIndex=isTarget?0:-1;});
      tabContents.forEach(c=>c.classList.add('hidden'));
      const target=document.getElementById(`tab-${tabName}`); if(target) target.classList.remove('hidden');
    }

    (function(){
      'use strict';
      const AUTH_EMAIL=@json($authEmail);
      const EMPLEADO_ID={{(int)$empleadoId}};
      const ANIO={{(int)$anioActual}};
      const MES={{(int)$mesActual}};
      const TOTAL={{(int)$total}};
      const VIEW_ONLY=@json($viewOnly);
      const JUST_SENT=@json(session('encuesta_enviada', false));
      const STORAGE_KEY=`encuesta_${AUTH_EMAIL}_${EMPLEADO_ID}_${ANIO}_${MES}`;
      const form=document.getElementById('form-encuesta');
      const finalNote=document.getElementById('comentario-general');
      const btn=document.getElementById('btn-submit');
      const countEl=document.getElementById('progress-count');

      const tabBtns=document.querySelectorAll('.tab-btn[role="tab"]');
      const tabOrder=Array.from(tabBtns);
      tabBtns.forEach(btnEl=>{
        btnEl.addEventListener('click',()=>switchToTab(btnEl.dataset.tab));
        btnEl.addEventListener('keydown',(e)=>{
          if(e.key==='ArrowRight'||e.key==='ArrowLeft'){
            e.preventDefault();
            const dir=e.key==='ArrowRight'?1:-1;
            const idx=tabOrder.indexOf(document.activeElement);
            const next=(idx+dir+tabOrder.length)%tabOrder.length;
            tabOrder[next].focus(); switchToTab(tabOrder[next].dataset.tab);
          }
        });
      });
      if(tabBtns.length>0){tabBtns[0].classList.add('active');tabBtns[0].setAttribute('aria-selected','true');tabBtns[0].tabIndex=0;}

      function clearDraft(){try{sessionStorage.removeItem(STORAGE_KEY);}catch{}}
      if(VIEW_ONLY||JUST_SENT){clearDraft();}

      function initializeRatingButtons(){
        if(VIEW_ONLY) return;
        document.querySelectorAll('.rating-btn').forEach(b=>{
          const group=b.closest('.rating-buttons'); if(group&&group.getAttribute('data-readonly')==='1') return;
          b.addEventListener('click',function(){
            const value=this.getAttribute('data-value');
            const preguntaId=this.getAttribute('data-pregunta-id');
            const input=document.querySelector(`.puntaje-input[data-pregunta-id="${preguntaId}"]`);
            const currentValue=input.value;
            const groupButtons=document.querySelectorAll(`.rating-btn[data-pregunta-id="${preguntaId}"]`);
            groupButtons.forEach(x=>x.classList.remove('rating-btn-selected'));
            if(currentValue!==value){ this.classList.add('rating-btn-selected'); input.value=value; } else { input.value=''; }
            saveAndRefresh();
          });
        });
      }

      function readDraft(){ if(VIEW_ONLY) return null; try{ const raw=sessionStorage.getItem(STORAGE_KEY); return raw?JSON.parse(raw):null;}catch{return null;} }
      function writeDraft(data){ if(VIEW_ONLY) return; try{ sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data)); }catch{} }
      function computeFilled(){ return Array.from(document.querySelectorAll('.puntaje-input')).filter(i=>i.value!=='').length; }

      function updateUI(){
        const filled=computeFilled();
        if(countEl){ countEl.classList.add('animate-count'); setTimeout(()=>countEl.classList.remove('animate-count'),300); countEl.textContent=`${filled}/${TOTAL}`; }
        if(btn && !VIEW_ONLY){ btn.textContent=(filled>=TOTAL)?'Enviar Evaluación':'Guardar progreso'; }
      }

      function snapshot(){
        if(VIEW_ONLY) return;
        const respuestas=[];
        document.querySelectorAll('.puntaje-input').forEach(input=>{
          const preguntaId=input.getAttribute('data-pregunta-id');
          respuestas.push({pregunta_id:parseInt(preguntaId,10), puntaje: input.value===''?null:parseInt(input.value,10)});
        });
        writeDraft({respuestas, comentario_general: finalNote?.value ?? ''});
      }
      const saveAndRefresh=()=>{ snapshot(); updateUI(); };

      function restoreFromDraftOrServer(){
        if(VIEW_ONLY){updateUI(); return;}
        const draft=readDraft();
        if(draft && Array.isArray(draft.respuestas)){
          draft.respuestas.forEach(r=>{
            if(r.puntaje!==null){
              const btn=document.querySelector(`.rating-btn[data-pregunta-id="${r.pregunta_id}"][data-value="${r.puntaje}"]`);
              const input=document.querySelector(`.puntaje-input[data-pregunta-id="${r.pregunta_id}"]`);
              if(btn&&input){
                document.querySelectorAll(`.rating-btn[data-pregunta-id="${r.pregunta_id}"]`).forEach(b=>b.classList.remove('rating-btn-selected'));
                btn.classList.add('rating-btn-selected'); input.value=r.puntaje;
              }
            }
          });
          if(finalNote && typeof draft.comentario_general==='string'){ finalNote.value=draft.comentario_general; }
        }
        updateUI();
      }

      if(finalNote && !VIEW_ONLY){ finalNote.addEventListener('input', ()=>{ snapshot(); }); }

     if (!VIEW_ONLY && form) {
        form.addEventListener('submit', (e) => {
            const filled = computeFilled();

            if (filled < TOTAL) {
                // Guardar progreso - permitir envío normal
                snapshot();
                updateUI();
                return; // no prevenimos, que el form se envíe normal
            }

            // Envío final - interceptamos para pedir confirmación con SweetAlert
            e.preventDefault();

            // Detectar tema actual (mismo criterio que usamos en app.blade.php)
            const storedTheme  = localStorage.getItem('theme');
            const prefersDark  = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const hasDarkClass = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

            const isDark =
                storedTheme === 'dark' ||
                (!storedTheme && prefersDark) ||
                hasDarkClass;

            const baseConfig = {
                title: 'Revisa antes de enviar',
                html: 'Una vez enviada la encuesta, no podrás editarla después',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
            };

            const darkConfig = Object.assign({}, baseConfig, {
                backdrop: 'rgba(15,23,42,0.75)',
                customClass: {
                    popup: 'rounded-2xl bg-slate-900/95 text-white border border-indigo-500/40 shadow-2xl',
                    title: 'text-xl font-semibold text-white',
                    htmlContainer: 'text-sm text-slate-200',
                    confirmButton: 'px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-500 hover:to-purple-500',
                    cancelButton:  'px-4 py-2 rounded-lg bg-gray-600 text-white font-semibold hover:bg-gray-500 ml-2'
                }
            });

            const lightConfig = Object.assign({}, baseConfig, {
                backdrop: 'rgba(15,23,42,0.45)',
                customClass: {
                    popup: 'rounded-2xl bg-white text-slate-900 border border-indigo-500/20 shadow-2xl',
                    title: 'text-xl font-semibold text-slate-900',
                    htmlContainer: 'text-sm text-slate-700',
                    confirmButton: 'px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-500 hover:to-purple-500',
                    cancelButton:  'px-4 py-2 rounded-lg bg-gray-200 text-slate-900 font-semibold hover:bg-gray-300 ml-2'
                }
            });

            Swal.fire(isDark ? darkConfig : lightConfig).then((result) => {
                if (result.isConfirmed) {
                    clearDraft();   // limpiamos borrador porque ya se envía definitivo
                    form.submit();  // submit nativo, NO dispara de nuevo el listener
                }
                // Si cancela, simplemente no hacemos nada y el form no se envía
            });
        });
    }

      ready(()=>{ initializeRatingButtons(); restoreFromDraftOrServer(); });
    })();

    // Contador de palabras
    (function(){
        const textarea = document.getElementById('comentario-general');
        const limitWarning = document.getElementById('limit-warning');
        const MAX_WORDS = 100;
        const VIEW_ONLY = @json($viewOnly);

        if (!textarea) return;

        function countWords(t){
            const s = String(t || '').trim();
            return s ? s.split(/\s+/).length : 0;
        }

        function adjustHeight(){
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 400) + 'px';
        }

        function updateWordCount(){
            const words = countWords(textarea.value);
            const wc = document.getElementById('word-count');
            if (wc) wc.textContent = `${words} / ${MAX_WORDS} palabras`;

            if (!VIEW_ONLY) {
              if (words >= MAX_WORDS) limitWarning.classList.remove('hidden');
              else limitWarning.classList.add('hidden');
            }
            adjustHeight();
        }

        function enforceWordLimit(e){
            const words = countWords(textarea.value);
            if (words > MAX_WORDS) {
              e.preventDefault();
              const arr = textarea.value.trim().split(/\s+/);
              textarea.value = arr.slice(0, MAX_WORDS).join(' ');
              updateWordCount();
              return false;
            }
        }

        if (!VIEW_ONLY) {
            textarea.addEventListener('input', (e) => {
              if (countWords(textarea.value) > MAX_WORDS) enforceWordLimit(e);
              else updateWordCount();
            });

            textarea.addEventListener('paste', () => {
              setTimeout(() => {
                if (countWords(textarea.value) > MAX_WORDS) {
                  const arr = textarea.value.trim().split(/\s+/);
                  textarea.value = arr.slice(0, MAX_WORDS).join(' ');
                }
                updateWordCount();
              }, 0);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateWordCount);
        } else {
            updateWordCount();
        }
    })();
    </script>
</x-app-layout>
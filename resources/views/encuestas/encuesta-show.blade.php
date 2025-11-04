<x-app-layout>
  @php
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $anioActual = $anio ?? now()->year;
    $mesActual  = $mes ?? now()->month;
    $total      = $totalPreguntas ?? 10;
    $done       = $contestadas ?? 0;

    $authId    = auth()->id();
    $authEmail = auth()->user()->correo ?? (auth()->user()->email ?? '');
    
    // Organizar preguntas por categoría
    $preguntasDesempeno = $preguntas->where('categoria', 'desempeno');
    $preguntasPotencial = $preguntas->where('categoria', 'potencial');
  @endphp

  <div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
          <div class="absolute inset-0 bg-black/10"></div>
          <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h1 class="text-2xl md:text-3xl font-extrabold text-white">Evaluación 9-Box</h1>
              <p class="text-white/90">{{ $meses[$mesActual] }} {{ $anioActual }} • ID Empleado #{{ $empleadoId }}</p>
            </div>
            <a href="{{ route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual]) }}" class="btn btn-ghost self-start md:self-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Volver
            </a>
          </div>
        </div>

        {{-- Progreso --}}
        <div class="px-6 pt-6">
          <div class="flex items-center justify-between text-gray-900 dark:text-gray-200 mb-2">
            <span class="font-semibold">Progreso de la evaluación</span>
            <span id="progress-count" class="text-sm font-bold">{{ $done }}/{{ $total }}</span>
          </div>
          @php $pct = $total>0 ? round(($done/$total)*100) : 0; @endphp
          <div class="w-full h-3 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden">
            <div id="bar" class="h-3 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-[0px] transition-all duration-500 ease-out" style="width: {{ $pct }}%;">.</div>
          </div>
        </div>

        {{-- Instrucciones --}}
        <div class="px-6 pt-4">
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 mt-1">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-1">Instrucciones</h3>
                <p class="text-sm text-blue-700 dark:text-blue-400">
                  Evalúa al colaborador en <strong>dos dimensiones principales</strong>: 
                  <span class="font-semibold">Desempeño actual</span> y 
                  <span class="font-semibold">Potencial de crecimiento</span>. 
                  Usa la escala del 1 al 5 para cada pregunta.
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- Formulario --}}
        <form id="form-encuesta" method="POST"
              action="{{ route('encuestas.submit', ['empleado'=>$empleadoId,'anio'=>$anioActual,'mes'=>$mesActual]) }}"
              class="p-6 space-y-8">
          @csrf

          {{-- Dimensión 1: Desempeño --}}
          <section class="space-y-6">
            <div class="border-l-4 border-emerald-500 pl-4 py-1">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">Desempeño Actual</h2>
              <p class="text-gray-600 dark:text-gray-400">Evalúa el rendimiento y contribución actual del colaborador</p>
            </div>

            @foreach ($preguntasDesempeno as $i => $p)
              @php
                $resp  = $respuestas[$p->id] ?? null;
                $valor = $resp->puntaje ?? null;
              @endphp

              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                  <div class="flex-1">
                    <p class="font-semibold text-gray-900 dark:text-white text-lg leading-relaxed">{{ $p->texto }}</p>
                  </div>

                  <div class="lg:w-48">
                    <label class="sr-only">Puntaje para: {{ $p->texto }}</label>
                    <div class="rating-buttons">
                      @for ($n=1; $n<=5; $n++)
                        <button type="button" 
                                class="rating-btn {{ $valor !== null && (int)$valor === $n ? 'rating-btn-selected' : '' }}" 
                                data-value="{{ $n }}"
                                data-pregunta-id="{{ $p->id }}">
                          {{ $n }}
                        </button>
                      @endfor
                    </div>
                    <input type="hidden" 
                           name="respuestas[{{ $i }}][puntaje]" 
                           value="{{ $valor }}" 
                           class="puntaje-input"
                           data-pregunta-id="{{ $p->id }}">
                  </div>
                </div>

                <input type="hidden" name="respuestas[{{ $i }}][pregunta_id]" value="{{ $p->id }}">
              </div>
            @endforeach
          </section>

          {{-- Dimensión 2: Potencial --}}
          <section class="space-y-6">
            <div class="border-l-4 border-cyan-500 pl-4 py-1">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">Potencial</h2>
              <p class="text-gray-600 dark:text-gray-400">Evalúa la capacidad de crecimiento y desarrollo futuro</p>
            </div>

            @foreach ($preguntasPotencial as $i => $p)
              @php
                $index = $preguntasDesempeno->count() + $i;
                $resp  = $respuestas[$p->id] ?? null;
                $valor = $resp->puntaje ?? null;
              @endphp

              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                  <div class="flex-1">
                    <p class="font-semibold text-gray-900 dark:text-white text-lg leading-relaxed">{{ $p->texto }}</p>
                  </div>

                  <div class="lg:w-48">
                    <label class="sr-only">Puntaje para: {{ $p->texto }}</label>
                    <div class="rating-buttons">
                      @for ($n=1; $n<=5; $n++)
                        <button type="button" 
                                class="rating-btn {{ $valor !== null && (int)$valor === $n ? 'rating-btn-selected' : '' }}" 
                                data-value="{{ $n }}"
                                data-pregunta-id="{{ $p->id }}">
                          {{ $n }}
                        </button>
                      @endfor
                    </div>
                    <input type="hidden" 
                           name="respuestas[{{ $index }}][puntaje]" 
                           value="{{ $valor }}" 
                           class="puntaje-input"
                           data-pregunta-id="{{ $p->id }}">
                  </div>
                </div>

                <input type="hidden" name="respuestas[{{ $index }}][pregunta_id]" value="{{ $p->id }}">
              </div>
            @endforeach
          </section>

          {{-- Comentario final --}}
          <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <label class="block text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3">Comentario General (opcional)</label>
            <textarea id="comentario-general" name="comentario_general" rows="4"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 p-3"
                      placeholder="Observaciones generales sobre la evaluación...">{{ old('comentario_general', $comentarioGeneral ?? '') }}</textarea>
          </div>

          <div class="pt-4">
            <button id="btn-submit" type="submit" class="btn btn-primary btn-block text-lg py-3">
              {{ $done >= $total ? 'Enviar evaluación' : 'Guardar borrador' }}
            </button>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
              {{ $done >= $total ? 
                 'Evaluación completa - Lista para enviar' : 
                 "Completa las {$total} preguntas para habilitar el envío" }}
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Estilos limpios --}}
  <style>
    :root{ 
      --brand-indigo:#4338ca; 
      --brand-purple:#6d28d9; 
      --shadow-soft:0 8px 18px rgba(2,6,23,.10); 
      --radius-md:.9rem; 
    }
    
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:.5rem;
      font-weight:700;
      line-height:1;
      border-radius:var(--radius-md);
      padding:.7rem 1.15rem;
      border:1px solid transparent;
      cursor:pointer;
      transition:all 0.2s ease;
      box-shadow:var(--shadow-soft);
    }
    
    .btn:hover{
      transform:translateY(-2px);
      box-shadow:0 12px 25px rgba(2,6,23,.15);
    }
    
    .btn-block{
      width:100%;
    }
    
    .btn-primary{
      color:#fff;
      background-image:linear-gradient(90deg,var(--brand-indigo),var(--brand-purple));
    }
    
    .btn-ghost{
      background:rgba(15,23,42,.06);
      color:#0f172a;
      border-color:rgba(15,23,42,.10);
    }
    
    @media (prefers-color-scheme:dark){
      .btn-ghost{
        background:rgba(255,255,255,.12);
        color:#fff;
        border-color:rgba(255,255,255,.14);
      }
    }
    
    .rating-buttons{
      display:flex;
      gap:0.5rem;
      justify-content:center;
    }
    
    .rating-btn{
      width:3rem;
      height:3rem;
      border-radius:0.75rem;
      border:2px solid #e5e7eb;
      background:white;
      color:#6b7280;
      font-weight:bold;
      font-size:1.1rem;
      cursor:pointer;
      transition:all 0.2s ease;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    
    @media (prefers-color-scheme:dark){
      .rating-btn{
        border-color:#4b5563;
        background:#1f2937;
        color:#9ca3af;
      }
    }
    
    .rating-btn:hover{
      transform:scale(1.1);
      border-color:#9ca3af;
    }
    
    .rating-btn-selected{
      background:linear-gradient(135deg, var(--brand-indigo), var(--brand-purple));
      color:white;
      border-color:var(--brand-indigo);
      transform:scale(1.1);
      box-shadow:0 4px 12px rgba(67, 56, 202, 0.3);
    }
  </style>

  {{-- JS mejorado --}}
  <script>
    (function(){
      'use strict';

      const AUTH_EMAIL = @json($authEmail);
      const EMPLEADO_ID= {{ (int)$empleadoId }};
      const ANIO       = {{ (int)$anioActual }};
      const MES        = {{ (int)$mesActual }};
      const TOTAL      = {{ (int)$total }};

      const STORAGE_KEY = `encuesta_${AUTH_EMAIL}_${EMPLEADO_ID}_${ANIO}_${MES}`;

      const form      = document.getElementById('form-encuesta');
      const finalNote = document.getElementById('comentario-general');
      const btn       = document.getElementById('btn-submit');
      const bar       = document.getElementById('bar');
      const countEl   = document.getElementById('progress-count');

      // ---- helpers de borrador
      function readDraft(){
        try{
          const raw = sessionStorage.getItem(STORAGE_KEY);
          return raw ? JSON.parse(raw) : null;
        }catch(e){ 
          console.error('Error reading draft:', e);
          return null; 
        }
      }
      
      function writeDraft(data){
        try{ 
          sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data)); 
        }catch(e){
          console.error('Error saving draft:', e);
        }
      }
      
      function clearDraft(){
        try{ 
          sessionStorage.removeItem(STORAGE_KEY); 
        }catch(e){
          console.error('Error clearing draft:', e);
        }
      }

      // ---- Sistema de rating con toggle
      function initializeRatingButtons() {
        document.querySelectorAll('.rating-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const preguntaId = this.getAttribute('data-pregunta-id');
            const input = document.querySelector(`.puntaje-input[data-pregunta-id="${preguntaId}"]`);
            const currentValue = input.value;
            
            // Si hace click en el mismo número, deseleccionar
            if (currentValue === value) {
              // Deseleccionar todos los botones del grupo
              const groupButtons = document.querySelectorAll(`.rating-btn[data-pregunta-id="${preguntaId}"]`);
              groupButtons.forEach(b => b.classList.remove('rating-btn-selected'));
              input.value = '';
            } else {
              // Seleccionar nuevo valor
              const groupButtons = document.querySelectorAll(`.rating-btn[data-pregunta-id="${preguntaId}"]`);
              groupButtons.forEach(b => b.classList.remove('rating-btn-selected'));
              this.classList.add('rating-btn-selected');
              input.value = value;
            }
            
            saveAndRefresh();
          });
        });
      }

      // ---- progreso + UI
      function computeFilled(){
        const inputs = document.querySelectorAll('.puntaje-input');
        return Array.from(inputs).filter(input => input.value !== '').length;
      }
      
      function updateUI(){
        const filled = computeFilled();
        const pct = TOTAL > 0 ? Math.round((filled/TOTAL)*100) : 0;
        
        if (bar) bar.style.width = pct + '%';
        if (countEl) countEl.textContent = `${filled}/${TOTAL}`;
        
        btn.textContent = (filled >= TOTAL) ? 'Enviar evaluación' : 'Guardar borrador';
        btn.classList.toggle('opacity-100', filled >= TOTAL);
        btn.classList.toggle('opacity-90', filled < TOTAL);
      }

      // ---- autoguardado
      function snapshot(){
        const respuestas = [];
        const inputs = document.querySelectorAll('.puntaje-input');
        
        inputs.forEach(input => {
          const preguntaId = input.getAttribute('data-pregunta-id');
          
          respuestas.push({
            pregunta_id: parseInt(preguntaId, 10),
            puntaje: input.value === '' ? null : parseInt(input.value, 10)
          });
        });
        
        const data = { 
          respuestas, 
          comentario_general: finalNote?.value ?? '' 
        };
        
        writeDraft(data);
      }
      
      const saveAndRefresh = () => { 
        snapshot(); 
        updateUI(); 
      };

      // ---- restaurar
      function restoreFromDraftOrServer(){
        const draft = readDraft();
        if (draft && Array.isArray(draft.respuestas)){
          draft.respuestas.forEach(respuesta => {
            if (respuesta.puntaje !== null) {
              // Buscar y activar el botón correspondiente
              const button = document.querySelector(`.rating-btn[data-pregunta-id="${respuesta.pregunta_id}"][data-value="${respuesta.puntaje}"]`);
              if (button) {
                const groupButtons = document.querySelectorAll(`.rating-btn[data-pregunta-id="${respuesta.pregunta_id}"]`);
                groupButtons.forEach(b => b.classList.remove('rating-btn-selected'));
                button.classList.add('rating-btn-selected');
                
                const input = document.querySelector(`.puntaje-input[data-pregunta-id="${respuesta.pregunta_id}"]`);
                if (input) {
                  input.value = respuesta.puntaje;
                }
              }
            }
          });
          
          if (finalNote && typeof draft.comentario_general === 'string'){
            finalNote.value = draft.comentario_general;
          }
        }
        updateUI();
      }

      // ---- listeners
      if (finalNote) {
        finalNote.addEventListener('input', () => { 
          snapshot(); 
        });
      }

      // Enviar: si está incompleta, sólo guarda borrador y cancela submit
      form.addEventListener('submit', (e)=>{
        const filled = computeFilled();
        if (filled < TOTAL){
          snapshot();
          updateUI();
          e.preventDefault();
          
          // Mostrar notificación más amigable
          const notification = document.createElement('div');
          notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-in slide-in-from-right duration-300';
          notification.innerHTML = `
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Borrador guardado. Completa las ${TOTAL - filled} preguntas restantes.</span>
            </div>
          `;
          document.body.appendChild(notification);
          
          setTimeout(() => {
            notification.remove();
          }, 3000);
          
          return;
        }
        
        // Encuesta completa: limpiar borrador y permitir submit
        clearDraft();
      });

      // Inicialización
      document.addEventListener('DOMContentLoaded', function() {
        initializeRatingButtons();
        restoreFromDraftOrServer();
      });
    })();
  </script>
</x-app-layout>
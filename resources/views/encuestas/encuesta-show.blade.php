{{-- resources/views/encuestas/encuesta-show.blade.php --}}
<x-app-layout>
  @php
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $anioActual = $anio ?? now()->year;
    $mesActual  = $mes ?? now()->month;
    $total      = $totalPreguntas ?? 10;
    $done       = $contestadas ?? 0;
  @endphp

  <div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Header gradiente --}}
        <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
          <div class="absolute inset-0 bg-black/10"></div>
          <div class="relative flex items-center justify-between gap-4">
            <div>
              <h1 class="text-2xl md:text-3xl font-extrabold text-white">Encuesta del periodo</h1>
              <p class="text-white/90">{{ $meses[$mesActual] }} {{ $anioActual }} • ID Empleado #{{ $empleadoId }}</p>
            </div>
            <a href="{{ route('encuestas.empleados', ['anio'=>$anioActual,'mes'=>$mesActual]) }}" class="btn btn-ghost">Volver</a>
          </div>
        </div>

        {{-- Progreso --}}
        <div class="px-6 pt-6">
          <div class="flex items-center justify-between text-gray-900 dark:text-gray-200 mb-2">
            <span class="font-semibold">Progreso</span>
            <span class="text-sm">{{ $done }}/{{ $total }}</span>
          </div>
          @php $pct = $total>0 ? round(($done/$total)*100) : 0; @endphp
          <div class="w-full h-3 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden">
            <div id="bar" class="h-3 rounded-full btn-primary text-[0px]" style="width: {{ $pct }}%;">.</div>
          </div>
        </div>

        {{-- Body / Form --}}
        <form method="POST" action="{{ route('encuestas.submit', ['empleado'=>$empleadoId,'anio'=>$anioActual,'mes'=>$mesActual]) }}" class="p-6 space-y-5">
          @csrf

          @foreach ($preguntas as $i => $p)
            @php
              $resp = $respuestas[$p->id] ?? null;
              $valor = $resp->puntaje ?? null;
              $nota  = $resp->comentario ?? '';
              $cat   = strtoupper($p->categoria);
            @endphp

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white/80 dark:bg-gray-900/70">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <span class="inline-block text-xs font-extrabold tracking-widest px-2 py-1 rounded-md
                               {{ $p->categoria==='desempeno' ? 'bg-emerald-600 text-white' : 'bg-cyan-600 text-white' }}">
                    {{ $cat }}
                  </span>
                  <p class="mt-2 font-semibold text-gray-900 dark:text-white">{{ $p->texto }}</p>
                </div>

                <div class="min-w-[120px]">
                  <label class="sr-only">Puntaje</label>
                  <select name="respuestas[{{ $i }}][puntaje]"
                          class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 puntaje-select">
                    <option value="" @selected($valor===null)>—</option>
                    @for ($n=0; $n<=5; $n++)
                      <option value="{{ $n }}" @selected($valor!==null && (int)$valor===$n)>{{ $n }}</option>
                    @endfor
                  </select>
                </div>
              </div>

              <div class="mt-3">
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Comentario (opcional)</label>
                <textarea name="respuestas[{{ $i }}][comentario]" rows="2"
                          class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                          placeholder="Observaciones...">{{ old("respuestas.$i.comentario", $nota) }}</textarea>
              </div>

              <input type="hidden" name="respuestas[{{ $i }}][pregunta_id]" value="{{ $p->id }}">
            </div>
          @endforeach

          <div class="pt-2">
            <button id="btn-submit" type="submit" class="btn btn-primary btn-block">
              {{ $done >= $total ? 'Enviar encuesta' : 'Guardar borrador' }}
            </button>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 text-center">
              El botón cambia a “Enviar encuesta” cuando completas las {{ $total }} preguntas. Mmm~ ✨
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Estilos reutilizados --}}
  <style>
    :root{ --brand-indigo:#4338ca; --brand-purple:#6d28d9; --shadow-soft:0 8px 18px rgba(2,6,23,.10); --radius-md:.9rem; }
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-weight:700;line-height:1;border-radius:var(--radius-md);padding:.7rem 1.15rem;border:1px solid transparent;cursor:pointer;transition:.12s;box-shadow:var(--shadow-soft)}
    .btn:hover{transform:translateY(-1px)} .btn-block{width:100%}
    .btn-primary{color:#fff;background-image:linear-gradient(90deg,var(--brand-indigo),var(--brand-purple))}
    .btn-ghost{background:rgba(15,23,42,.06);color:#0f172a;border-color:rgba(15,23,42,.10)}
    @media (prefers-color-scheme:dark){.btn-ghost{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.14)}}
  </style>

  {{-- JS: cambia el label del botón según respuestas llenas --}}
  <script>
    const selects = Array.from(document.querySelectorAll('.puntaje-select'));
    const btn = document.getElementById('btn-submit');
    const total = {{ (int)$total }};
    function updateLabel(){
      const filled = selects.filter(s => s.value !== '').length;
      btn.textContent = (filled >= total) ? 'Enviar encuesta' : 'Guardar borrador';
      const pct = total ? Math.round((filled/total)*100) : 0;
      const bar = document.getElementById('bar');
      if (bar) bar.style.width = pct + '%';
    }
    selects.forEach(s => s.addEventListener('change', updateLabel));
  </script>
</x-app-layout>

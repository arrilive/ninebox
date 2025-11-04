<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col lg:flex-row gap-8">
        @php
          $totalEmpleados      = $totalEmpleados ?? count($empleados ?? []);
          $empleadosEvaluados  = $empleadosEvaluados ?? 0;
          $pendientes          = max(0, $totalEmpleados - $empleadosEvaluados);
          $pct                 = $totalEmpleados > 0 ? min(100, round(($empleadosEvaluados / $totalEmpleados) * 100)) : 0;
          $anioActual = request('anio', now()->year);
          $mesActual  = request('mes',  now()->month);
        @endphp

        {{-- Sidebar resumen --}}
        <aside
          class="lg:w-80 flex-shrink-0 rounded-2xl shadow-2xl border border-white/10 dark:border-gray-700/40
                 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg lg:sticky lg:top-4 lg:overflow-hidden">
          <div class="px-6 py-6 bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 relative">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative">
              <h3 class="text-white font-extrabold text-3xl tracking-tight">Resumen</h3>
              <p class="text-white text-xl leading-snug">Estado de la evaluación</p>
            </div>
          </div>

          <div class="p-7 space-y-7">
            {{-- Periodo (sin fetch aquí; solo para armar el link de "Por evaluar") --}}
            <section aria-label="Periodo" class="flex flex-wrap items-center gap-3">
              <div>
                <label for="filtro-anio" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Año</label>
                <select id="filtro-anio"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-black
                               focus:border-indigo-500 focus:ring-indigo-500">
                  @for ($i = now()->year; $i >= 2020; $i--)
                    <option value="{{ $i }}" {{ (int)$i === (int)$anioActual ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>

              <div>
                <label for="filtro-mes" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Mes</label>
                <select id="filtro-mes"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900/70 dark:text-black
                               focus:border-indigo-500 focus:ring-indigo-500">
                  @foreach ([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num => $mes)
                    <option value="{{ $num }}" {{ (int)$num === (int)$mesActual ? 'selected' : '' }}>{{ $mes }}</option>
                  @endforeach
                </select>
              </div>
            </section>

            {{-- KPIs --}}
            <section aria-label="Indicadores clave">
              <div class="kpi-glass">
                <div class="kpi-row">
                  <span class="kpi-label text-gray-900 dark:text-gray-100">Total Empleados</span>
                  <span id="total-empleados" class="kpi-value">{{ $totalEmpleados }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-emerald-600 dark:text-emerald-400">Evaluados</span>
                  <span id="empleados-evaluados" class="kpi-value">{{ $empleadosEvaluados }}</span>
                </div>
                <div class="kpi-row">
                  <span class="kpi-label text-rose-600 dark:text-red-400">Por evaluar</span>
                  <span id="empleados-pendientes" class="kpi-value">{{ $pendientes }}</span>
                </div>
              </div>
            </section>

            {{-- Barra de progreso --}}
            <section>
              <div class="flex items-center justify-between text-xl text-gray-900 dark:text-gray-300 mb-2">
                <span class="font-semibold tracking-tight">Avance</span>
              </div>
              <div class="w-full h-6 bg-gray-200 rounded-full dark:bg-gray-700 overflow-hidden">
                <div id="avance-bar"
                     class="h-6 text-center rounded-full text-white transition-[width] duration-300 ease-in-out btn-primary"
                     style="width: {{ $pct }}%;">
                  {{ $pct }}%
                </div>
              </div>
            </section>

            {{-- CTA principal: Evaluar empleados --}}
            @if ($usuario->esJefe())
              <div class="mt-2 space-y-2">
                <a
                  id="btn-por-evaluar"
                  href="{{ route('encuestas.empleados', ['anio' => request('anio', now()->year), 'mes' => request('mes', now()->month)]) }}"
                  class="btn btn-primary btn-block"
                >
                  Evaluar empleados
                </a>

                <p class="text-[13px] text-center mt-1 text-gray-700 dark:text-gray-400">
                  Abre la lista de empleados para iniciar o continuar encuestas.
                </p>
              </div>
            @endif
          </div>
        </aside>

        {{-- Nine-Box: vista + cuadrantes clicables con modal SOLO lectura --}}
        <div class="flex-1">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="relative w-full mx-auto" style="max-width: 96%;">
              <img
                src="{{ asset('images/9box-demo.png') }}"
                class="w-full h-auto rounded-xl shadow-lg select-none"
                id="ninebox-img"
                alt="9-Box"
                draggable="false"
                style="pointer-events:none;"
              >

              {{-- Botones por cuadrante (solo vista; badges dinámicos) --}}
              @php
                // $asignacionesActuales debe ser: [cuadrante => Collection<empleado>]
                $positions = [
                  1=>['left'=>'17.5%','top'=>'18%','w'=>'23.5%','h'=>'25%'],
                  2=>['left'=>'42.5%','top'=>'18%','w'=>'23.5%','h'=>'25%'],
                  3=>['left'=>'67.5%','top'=>'18%','w'=>'23%','h'=>'25%'],
                  4=>['left'=>'17.5%','top'=>'45%','w'=>'23.5%','h'=>'25%'],
                  5=>['left'=>'42.5%','top'=>'45%','w'=>'23.5%','h'=>'25%'],
                  6=>['left'=>'67.5%','top'=>'45%','w'=>'23%','h'=>'25%'],
                  7=>['left'=>'17.5%','top'=>'72%','w'=>'23.5%','h'=>'25%'],
                  8=>['left'=>'42.5%','top'=>'72%','w'=>'23.5%','h'=>'25%'],
                  9=>['left'=>'67.5%','top'=>'72%','w'=>'23%','h'=>'25%']
                ];
              @endphp

              @for ($i = 1; $i <= 9; $i++)
                @php $pos = $positions[$i]; $count = ($asignacionesActuales[$i] ?? collect())->count(); @endphp
                <button type="button" class="cuadrante-btn btn-surface"
                        style="position:absolute; left: {{ $pos['left'] }}; top: {{ $pos['top'] }};
                               width: {{ $pos['w'] }}; height: {{ $pos['h'] }};"
                        data-cuadrante="{{ $i }}"
                        aria-label="Ver empleados en cuadrante {{ $i }}">
                  @if($count > 0)
                    <div class="cuadrante-badge">{{ $count }}</div>
                  @endif
                </button>
              @endfor
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Modal SOLO lectura --}}
    <div id="modal-empleados" class="fixed inset-0 z-50 hidden"
         role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-desc">
      <div id="modal-backdrop" class="absolute inset-0"></div>
      <div class="relative h-full flex items-center justify-center p-4">
        <div id="modal-container"
             class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg rounded-2xl shadow-2xl w-full max-w-2xl
                    max-h-[80vh] overflow-hidden border border-white/10 dark:border-gray-700/40 transform scale-90 opacity-0
                    transition-all duration-300" tabindex="-1">
          <div class="bg-gradient-to-r from-indigo-700 via-purple-700 to-indigo-800 p-6 relative overflow-hidden">
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

          <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 140px);">
            <div id="section-asignados" class="mb-2">
              <div class="flex items-center gap-3 mb-4">
                <div class="badge-icon bg-gradient-to-r from-green-600 to-emerald-600">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <h4 id="title-asignados" class="text-xl font-bold text-gray-900 dark:text-white">Asignados</h4>
                <span id="count-asignados" class="chip chip-success ml-auto">0</span>
              </div>
              <div id="empty-asignados" class="text-center py-8 text-gray-400 dark:text-gray-500 hidden">
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

  {{-- Estilos comunes (look & feel) --}}
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
    .chip{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .75rem;border-radius:9999px;font-weight:800;font-size:.8rem;color:#fff;box-shadow:0 4px 14px rgba(2,6,23,.12)}
    .chip-success{background-image:linear-gradient(90deg,#16a34a 0%,#059669 100%)} .chip-info{background-image:linear-gradient(90deg,#2563eb 0%,#06b6d4 100%)}
    .kpi-glass{position:relative;padding:1.1rem;border-radius:var(--radius-lg);background:linear-gradient(180deg,var(--glass-0),var(--glass-1));
      border:1px solid rgba(255,255,255,.06);box-shadow:0 10px 28px rgba(2,6,23,.20) inset,0 8px 22px rgba(2,6,23,.12)}
    @media (prefers-color-scheme: light){ .kpi-glass{background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(15,23,42,.04));border:1px solid rgba(15,23,42,.06)} }
    .kpi-row{display:grid;grid-template-columns:1fr auto;align-items:center;gap:.85rem;padding:1rem 1.1rem;border-radius:.8rem;background:rgba(2,6,23,.08)}
    .kpi-row + .kpi-row{margin-top:.75rem} @media (prefers-color-scheme: dark){ .kpi-row{background:rgba(2,6,23,.18)} }
    .kpi-label{font-weight:700;font-size:1rem;letter-spacing:.2px} .kpi-value{font-weight:800;font-size:2.15rem;line-height:1;color:#0b1020;letter-spacing:.2px}
    @media (prefers-color-scheme: dark){ .kpi-value{color:#e6eef8;text-shadow:0 1px 0 rgba(0,0,0,.15)} }
    .btn-surface{background:rgba(79,70,229,.08);border:2px solid transparent;border-radius:12px;transition:transform .18s cubic-bezier(.2,.9,.3,1),box-shadow .18s ease,border-color .12s ease,background .12s ease;z-index:40;outline:none}
    .btn-surface:hover,.btn-surface:focus{border-color:rgba(79,70,229,.95);background:linear-gradient(180deg,rgba(79,70,229,.12),rgba(79,70,229,.08));transform:scale(1.04);box-shadow:0 8px 20px rgba(79,70,229,.12)}
    .cuadrante-badge{position:absolute;left:90%;top:-10%;display:inline-flex;align-items:center;justify-content:center;padding:.25rem .75rem;border-radius:9999px;font-weight:800;font-size:.75rem;line-height:1;color:#fff;background-image:linear-gradient(90deg,var(--brand-indigo) 0%,var(--brand-purple) 100%);border:1px solid rgba(0,0,0,.05);box-shadow:0 4px 14px rgba(2,6,23,.12);user-select:none;pointer-events:none;transform:translateZ(0)}
    #modal-backdrop{position:absolute;inset:0;z-index:10;background:linear-gradient(180deg,rgba(10,12,20,.72),rgba(67,56,202,.18));backdrop-filter:blur(8px) saturate(120%);-webkit-backdrop-filter:blur(8px) saturate(120%);pointer-events:auto;opacity:0;transition:opacity .22s ease}
    #modal-empleados:not(.hidden).show #modal-backdrop{opacity:1}
    #modal-container{position:relative;z-index:20;width:100%;max-width:44rem;max-height:80vh;overflow:hidden;border-radius:1rem;background-color:var(--modal-bg-light);color:#0f172a;border:1px solid rgba(15,23,42,.06);box-shadow:var(--shadow-strong);transform:translateY(8px) scale(.98);opacity:0;transition:transform .22s cubic-bezier(.2,.9,.3,1),opacity .18s ease;outline:none}
    @media (prefers-color-scheme: dark){ #modal-container{background-color:var(--modal-bg-dark);color:#e6eef8;border:1px solid rgba(255,255,255,.04);box-shadow:0 20px 60px rgba(2,6,23,.5)} }
    #modal-container>.bg-gradient-to-r{background:linear-gradient(90deg,var(--brand-indigo) 0%,var(--brand-purple) 55%,var(--brand-indigo) 100%)}
    #modal-empleados:not(.hidden).show #modal-container{transform:translateY(0) scale(1);opacity:1}
    .lista-empleado{padding:.78rem;border-radius:.85rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;background:linear-gradient(180deg,rgba(255,255,255,.64),rgba(255,255,255,.50));transition:transform .12s ease,box-shadow .12s ease;border-left:4px solid rgba(5,150,105,.95)}
    .lista-empleado:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(2,6,23,.06)}
    @media (prefers-color-scheme: dark){ .lista-empleado{background:linear-gradient(180deg,rgba(15,23,42,.14),rgba(15,23,42,.06))} }
    .avatar-icon{width:44px;height:44px;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(15,23,42,.06),rgba(67,56,202,.06));box-shadow:0 4px 12px rgba(2,6,23,.06);color:#0f172a}
  </style>

  <script>
    (function(){
      'use strict';

      // Dataset que viene del controlador: { "1":[{id,nombre,...},...], ... }
      const ASIG = @json($asignacionesActuales ?? []);
      const BASE_DESC = {
        1:{title:"Diamante en bruto",desc:"Gran potencial con desempeño aún bajo."},
        2:{title:"Estrella en desarrollo",desc:"Potencial y desempeño en crecimiento."},
        3:{title:"Estrella",desc:"Alto desempeño y potencial."},
        4:{title:"Bajo/Medio",desc:"Desempeño por debajo del esperado."},
        5:{title:"Sólido",desc:"Cumple consistentemente, potencial medio."},
        6:{title:"Elemento importante",desc:"Buen aporte, potencial incierto."},
        7:{title:"Inaceptable",desc:"Requiere acción inmediata."},
        8:{title:"Aceptable",desc:"Cumple lo básico."},
        9:{title:"Personal clave",desc:"Confiable con buen desempeño."},
      };

      let lastTriggerBtn = null;

      function renderAsignados(cuadrante){
        const lista = Array.isArray(ASIG[cuadrante]) ? ASIG[cuadrante] : [];
        const ul = document.getElementById('lista-asignados');
        const empty = document.getElementById('empty-asignados');
        const count = document.getElementById('count-asignados');

        ul.innerHTML = '';
        if (count) count.textContent = String(lista.length);

        if (lista.length === 0){
          empty?.classList.remove('hidden');
          return;
        }
        empty?.classList.add('hidden');

        lista.forEach((emp, i)=>{
          const li = document.createElement('li');
          li.className = 'lista-empleado';
          li.style.animation = `slideIn 0.32s ease-out ${i*0.04}s both`;

          const left = document.createElement('div');
          left.className = 'flex items-center gap-3';

          const avatar = document.createElement('div');
          avatar.className = 'avatar-icon';
          avatar.innerHTML = '<svg class="w-5 h-5 text-gray-700 dark:text-gray-200" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a5 5 0 100-10 5 5 0 000 10zM2 18a8 8 0 0116 0H2z"/></svg>';

          const name = document.createElement('span');
          name.className = 'font-semibold text-gray-900 dark:text-white nombre-empleado';
          const fullName = [emp.nombre||'', emp.apellido_paterno||'', emp.apellido_materno||''].join(' ').trim() || `ID #${emp.id}`;
          name.textContent = fullName;

          left.appendChild(avatar); left.appendChild(name);
          li.appendChild(left);

          ul.appendChild(li);
        });
      }

      function mostrarModal(cuadrante){
        const data = BASE_DESC[cuadrante] || {title:`Cuadrante ${cuadrante}`, desc:''};
        const title = document.getElementById('modal-title');
        const desc  = document.getElementById('modal-desc');
        if (title) title.textContent = data.title;
        if (desc)  desc.textContent  = data.desc;

        renderAsignados(cuadrante);

        const modal = document.getElementById('modal-empleados');
        modal?.classList.remove('hidden');
        requestAnimationFrame(()=>modal?.classList.add('show'));
      }

      function cerrarModal(){
        const modal = document.getElementById('modal-empleados');
        if (!modal) return;
        modal.classList.remove('show');
        setTimeout(()=>{
          modal.classList.add('hidden');
          (lastTriggerBtn?.focus?.());
        }, 220);
      }

      function updatePorEvaluarLink(){
        const anioSel = document.getElementById('filtro-anio');
        const mesSel  = document.getElementById('filtro-mes');
        const btn     = document.getElementById('btn-por-evaluar');
        const baseUrl = @json(route('encuestas.empleados'));
        const anio = anioSel?.value || '{{ (int)$anioActual }}';
        const mes  = mesSel?.value  || '{{ (int)$mesActual }}';
        if (btn) btn.href = `${baseUrl}?anio=${encodeURIComponent(anio)}&mes=${encodeURIComponent(mes)}`;
      }

      document.addEventListener('DOMContentLoaded', ()=>{
        // Iniciar barra
        const bar = document.getElementById('avance-bar');
        if (bar){ bar.style.width = '{{ $pct }}%'; bar.textContent = '{{ $pct }}%'; }

        // Click cuadrantes → modal solo lectura
        document.querySelectorAll('.cuadrante-btn').forEach(btn=>{
          btn.addEventListener('click', (e)=>{
            e.preventDefault();
            lastTriggerBtn = btn;
            const cuad = parseInt(btn.getAttribute('data-cuadrante'),10);
            mostrarModal(cuad);
          });
        });

        // Cerrar modal
        document.getElementById('modal-backdrop')?.addEventListener('click', cerrarModal);
        document.getElementById('btn-cerrar-modal')?.addEventListener('click', cerrarModal);
        document.getElementById('modal-empleados')?.addEventListener('click', (e)=>{
          if (e.target.id === 'modal-empleados') cerrarModal();
        });

        // Actualizar link al cambiar periodo
        document.getElementById('filtro-anio')?.addEventListener('change', updatePorEvaluarLink);
        document.getElementById('filtro-mes')?.addEventListener('change', updatePorEvaluarLink);
        updatePorEvaluarLink();
      });
    })();
  </script>
</x-app-layout>

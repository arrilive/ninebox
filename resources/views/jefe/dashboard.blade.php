<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Bienvenido, {{ $jefe->apellido_paterno }} {{ $jefe->apellido_materno }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Departamento: <span class="font-semibold">{{ $jefe->departamento->nombre_departamento ?? 'Sin departamento' }}</span>
                </p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-4 py-2 rounded-lg font-semibold shadow-lg">
                    {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Sidebar resumen --}}
                <div class="lg:w-64 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sticky top-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Resumen</h3>

                        <div class="space-y-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border-l-4 border-blue-500">
                                <div class="text-sm text-blue-600 dark:text-blue-400 font-semibold">Total Empleados</div>
                                <div class="text-3xl font-bold text-blue-900 dark:text-blue-100" id="total-empleados">
                                    {{ count($empleados) }}
                                </div>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border-l-4 border-green-500">
                                <div class="text-sm text-green-600 dark:text-green-400 font-semibold">Evaluados</div>
                                <div class="text-3xl font-bold text-green-900 dark:text-green-100" id="empleados-evaluados">
                                    {{ $empleadosEvaluados }}
                                </div>
                            </div>

                            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border-l-4 border-red-500">
                                <div class="text-sm text-red-600 dark:text-red-400 font-semibold">Por Evaluar</div>
                                <div class="text-3xl font-bold text-red-900 dark:text-red-100" id="empleados-pendientes">
                                    {{ count($empleados) - $empleadosEvaluados }}
                                </div>
                            </div>
                        </div>

                        <button
                            id="btn-guardar-evaluacion"
                            onclick="guardarEvaluacion()"
                            class="w-full mt-6 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                            {{ (count($empleados) - $empleadosEvaluados) > 0 ? 'disabled' : '' }}
                        >
                            Guardar Evaluación
                        </button>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                            Evalúa a todos los empleados para activar
                        </p>
                    </div>
                </div>

                {{-- Matriz 9-Box --}}
                <div class="flex-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-4 border border-gray-200 dark:border-gray-700">
                        <div class="relative w-full mx-auto" style="max-width: 95%;">
                            <img src="{{ asset('images/9box-demo.png') }}" class="w-full h-auto rounded-xl shadow-lg" id="ninebox-img" alt="9-Box">
                            
                            {{-- Botones absolutamente posicionados sobre cada cuadrante --}}
                            <button type="button" class="cuadrante-btn"
                                style="position:absolute; left: 17.5%; top:18%; width:23.5%; height:25%;"
                                data-cuadrante="1" title="Diamante en bruto"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left:42.5%; top:18%; width:23.5%; height:25%;" 
                                data-cuadrante="2" title="Estrella en desarrollo"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left:67.5%; top:18%; width:23%; height:25%;" 
                                data-cuadrante="3" title="Estrella"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left: 17.5%; top:45%; width:23.5%; height:25%;" 
                                data-cuadrante="4" title="Mal empleado"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left:42.5%; top:45%; width:23.5%; height:25%;" 
                                data-cuadrante="5" title="Personal sólido"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left:67.5%; top:45%; width:23%; height:25%;" 
                                data-cuadrante="6" title="Elemento importante"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left: 17.5%; top:72%; width:23.5%; height:25%;" 
                                data-cuadrante="7" title="Inaceptable"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left:42.5%; top:72%; width:23.5%; height:25%;" 
                                data-cuadrante="8" title="Aceptable"></button>
                            
                            <button type="button" class="cuadrante-btn" 
                                style="position:absolute; left:67.5%; top:72%; width:23%; height:25%;" 
                                data-cuadrante="9" title="Personal clave"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- modal para empleados --}}
        <div id="modal-empleados" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
            <div id="modal-container" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <h3 id="modal-title" class="text-2xl font-bold mb-2 text-gray-900 dark:text-white"></h3>
                <p id="modal-desc" class="text-gray-600 dark:text-gray-300 mb-6"></p>
                
                {{-- Asignados --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Asignados</h4>
                        <span id="count-asignados" class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-semibold">0</span>
                    </div>
                    <div id="empty-asignados" class="text-center py-4 text-gray-500 dark:text-gray-400 hidden">
                        No hay empleados asignados a este cuadrante
                    </div>
                    <ul id="lista-asignados" class="space-y-2"></ul>
                </div>

                {{-- Disponibles --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Disponibles</h4>
                        <span id="count-disponibles" class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-semibold">0</span>
                    </div>
                    <div id="empty-disponibles" class="text-center py-4 text-gray-500 dark:text-gray-400 hidden">
                        Todos los empleados han sido asignados
                    </div>
                    <ul id="lista-disponibles" class="space-y-2"></ul>
                </div>

                <button onclick="cerrarModal()" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    {{-- Estilos --}}
    <style>
        .cuadrante-btn {
            background: rgba(99, 102, 241, 0.08);
            border: 2px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }
        .cuadrante-btn:hover, .cuadrante-btn:focus {
            border: 2px solid #6366f1;
            background: rgba(99, 102, 241, 0.2);
            outline: none;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
    </style>

    {{-- JavaScript --}}
    <script>
        // 🔥 TOKEN CSRF INYECTADO DIRECTAMENTE DESDE LARAVEL
        const CSRF_TOKEN = '{{ csrf_token() }}';
        
        const cuadrantesData = {
            1: { title: "Diamante en bruto", subtitle: "Alto Potencial - Bajo Desempeño", desc: "Gran potencial, su desempeño no ha sido exigido por lo que requiere desarrollarlo" },
            2: { title: "Estrella en desarrollo", subtitle: "Alto Potencial - Medio Desempeño", desc: "Potencial y desempeño en crecimiento, con la dirección adecuada puede convertirse en una estrella" },
            3: { title: "Estrella", subtitle: "Alto Potencial - Alto Desempeño", desc: "Empleados con alto desempeño y gran potencial, clave para la organización" },
            4: { title: "Mal empleado", subtitle: "Medio Potencial - Bajo Desempeño", desc: "Desempeño insuficiente, requiere mejora y desarrollo" },
            5: { title: "Personal sólido", subtitle: "Medio Potencial - Medio Desempeño", desc: "Desempeño aceptable, pero con potencial limitado para crecer" },
            6: { title: "Elemento importante", subtitle: "Medio Potencial - Alto Desempeño", desc: "Buena contribución actual, pero con un potencial de crecimiento incierto" },
            7: { title: "Inaceptable", subtitle: "Bajo Potencial - Bajo Desempeño", desc: "Desempeño inaceptable, requiere acción inmediata" },
            8: { title: "Aceptable", subtitle: "Bajo Potencial - Medio Desempeño", desc: "Desempeño básico, cumple con los mínimos requerimientos" },
            9: { title: "Personal clave", subtitle: "Bajo Potencial - Alto Desempeño", desc: "Empleados confiables con buen desempeño, pero con poco potencial de desarrollo" }
        };

        let cuadranteActual = null;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Token CSRF cargado:', CSRF_TOKEN.substring(0, 10) + '...');
            
            // Event listener para botones de cuadrantes
            document.querySelectorAll('.cuadrante-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = this.getAttribute('data-cuadrante');
                    console.log('Cuadrante clickeado:', id);
                    mostrarModal(id);
                });
            });

            // Cerrar modal al hacer clic fuera
            document.getElementById('modal-empleados').addEventListener('click', function(e) {
                if (e.target === this) cerrarModal();
            });

            // Cerrar con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') cerrarModal();
            });
        });

        async function mostrarModal(cuadrante) {
            cuadranteActual = cuadrante;
            const data = cuadrantesData[cuadrante] || { title: 'Cuadrante', subtitle: '', desc: '' };
            
            document.getElementById('modal-title').textContent = data.title;
            document.getElementById('modal-desc').textContent = data.desc;

            // Mostrar contadores en 0 mientras carga
            document.getElementById('count-asignados').textContent = '0';
            document.getElementById('count-disponibles').textContent = '0';

            try {
                const url = `/jefe/cuadrante/${encodeURIComponent(cuadrante)}/empleados`;
                console.log('Fetching URL:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin'
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error del servidor:', errorText);
                    alert(`Error al cargar empleados (código ${response.status})`);
                    return;
                }

                const result = await response.json();
                console.log('Datos recibidos:', result);
                
                renderizarEmpleados(result.asignados || [], result.disponibles || []);
                
                // Mostrar modal con animación
                const modal = document.getElementById('modal-empleados');
                modal.classList.remove('hidden');
                
                setTimeout(() => {
                    const container = document.getElementById('modal-container');
                    container.style.transform = 'scale(1)';
                    container.style.opacity = '1';
                }, 10);

            } catch (error) {
                console.error('Error fetch empleados:', error);
                alert('Error al cargar empleados: ' + error.message);
            }
        }

        function renderizarEmpleados(asignados, disponibles) {
            const listaAsignados = document.getElementById('lista-asignados');
            const listaDisponibles = document.getElementById('lista-disponibles');
            const emptyAsignados = document.getElementById('empty-asignados');
            const emptyDisponibles = document.getElementById('empty-disponibles');

            document.getElementById('count-asignados').textContent = asignados.length;
            document.getElementById('count-disponibles').textContent = disponibles.length;

            // Renderizar asignados
            listaAsignados.innerHTML = '';
            if (asignados.length > 0) {
                emptyAsignados.classList.add('hidden');
                asignados.forEach(emp => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg';
                    div.innerHTML = `
                        <span class="font-semibold text-gray-900 dark:text-white">${emp.apellido_paterno || ''} ${emp.apellido_materno || ''}</span>
                        <button type="button" data-id="${emp.id}" class="eliminar-btn text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium text-sm px-3 py-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                            Eliminar
                        </button>
                    `;
                    listaAsignados.appendChild(div);
                });

                listaAsignados.querySelectorAll('.eliminar-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        eliminarEmpleado(id);
                    });
                });
            } else {
                emptyAsignados.classList.remove('hidden');
            }

            // Renderizar disponibles
            listaDisponibles.innerHTML = '';
            if (disponibles.length > 0) {
                emptyDisponibles.classList.add('hidden');
                disponibles.forEach(emp => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition';
                    div.innerHTML = `
                        <span class="font-semibold text-gray-900 dark:text-white">${emp.apellido_paterno || ''} ${emp.apellido_materno || ''}</span>
                        <button type="button" data-id="${emp.id}" class="asignar-btn text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm px-3 py-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                            + Asignar
                        </button>
                    `;
                    listaDisponibles.appendChild(div);
                });

                listaDisponibles.querySelectorAll('.asignar-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        asignarEmpleado(id);
                    });
                });
            } else {
                emptyDisponibles.classList.remove('hidden');
            }
        }

        async function asignarEmpleado(usuarioId) {
            console.log('Asignando empleado:', usuarioId, 'a cuadrante:', cuadranteActual);
            
            try {
                const formData = new FormData();
                formData.append('usuario_id', parseInt(usuarioId));
                formData.append('ninebox_id', parseInt(cuadranteActual));
                formData.append('_token', CSRF_TOKEN);

                const response = await fetch('/jefe/asignar-empleado', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error al asignar:', errorText);
                    alert('Error al asignar empleado (codigo ' + response.status + ')');
                    return;
                }

                const result = await response.json();
                console.log('Resultado:', result);
                
                if (result.success) {
                    await mostrarModal(cuadranteActual);
                    actualizarEstadisticas();
                }
            } catch (error) {
                console.error('Error fetch asignar:', error);
                alert('Error al asignar empleado: ' + error.message);
            }
        }

        async function eliminarEmpleado(usuarioId) {
            if (!confirm('¿Eliminar esta asignación?')) return;

            try {
                const response = await fetch('/jefe/eliminar-asignacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ usuario_id: parseInt(usuarioId) })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error al eliminar:', errorText);
                    alert(`Error al eliminar asignación (código ${response.status})`);
                    return;
                }

                const result = await response.json();
                if (result.success) {
                    await mostrarModal(cuadranteActual);
                    actualizarEstadisticas();
                }
            } catch (error) {
                console.error('Error fetch eliminar:', error);
                alert('Error al eliminar asignación: ' + error.message);
            }
        }

        function actualizarEstadisticas() {
            location.reload();
        }

        async function guardarEvaluacion() {
            try {
                const response = await fetch('/jefe/guardar-evaluacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                });

                if (!response.ok) {
                    console.error('Error guardar evaluacion', response.status, await response.text());
                    alert('Error al guardar evaluación.');
                    return;
                }

                const result = await response.json();
                if (result.success) {
                    alert('Evaluación guardada correctamente para el ' + result.fecha);
                    actualizarEstadisticas();
                } else {
                    alert(result.error || 'No fue posible guardar la evaluación.');
                }
            } catch (error) {
                console.error('Error fetch guardarEvaluacion:', error);
                alert('Error al guardar evaluación (conexión).');
            }
        }

        function cerrarModal() {
            const modal = document.getElementById('modal-empleados');
            const container = document.getElementById('modal-container');
            
            container.style.transform = 'scale(0.95)';
            container.style.opacity = '0';
            
            setTimeout(() => modal.classList.add('hidden'), 200);
        }
    </script>
</x-app-layout>
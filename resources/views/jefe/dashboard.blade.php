<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard - Jefe') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Información del Jefe -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-4">Bienvenido, {{ $jefe->apellido_paterno }} {{ $jefe->apellido_materno }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                            <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold">Usuario</p>
                            <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $jefe->user_name }}</p>
                        </div>
                        
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                            <p class="text-sm text-green-600 dark:text-green-400 font-semibold">Departamento</p>
                            <p class="text-lg font-bold text-green-900 dark:text-green-100">
                                {{ $jefe->departamento->nombre_departamento ?? 'Sin departamento' }}
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                            <p class="text-sm text-purple-600 dark:text-purple-400 font-semibold">Fecha</p>
                            <p class="text-lg font-bold text-purple-900 dark:text-purple-100">
                                {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold">Correo Electrónico</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $jefe->correo }}</p>
                    </div>
                </div>
            </div>

            <!-- Lista de Empleados -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-bold">Mis Empleados</h4>
                        <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm font-semibold">
                            {{ $empleados->count() }} empleados
                        </span>
                    </div>
                    
                    @if($empleados->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Nombre Completo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Usuario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Teléfono
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($empleados as $empleado)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $empleado->apellido_paterno }} {{ $empleado->apellido_materno }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $empleado->user_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $empleado->telefono ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400 text-lg">No hay empleados asignados a este departamento.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.empresas.index') }}" class="text-ink-2 hover:text-ink">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-ink leading-tight">
                {{ $empresa->nombre }}
            </h2>
        </div>
    </div>
@endsection

@section('content')
    <div class="py-12 bg-canvas min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-success text-success text-sm rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Sección 1 — Resumen de la empresa -->
            <div class="bg-white border border-border rounded-lg p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-ink mb-1">{{ __('Resumen de la Empresa') }}</h3>
                        <p class="text-sm text-ink-2 mb-2">Identificador único (slug): <code class="bg-surface px-1.5 py-0.5 rounded text-primary">{{ $empresa->slug }}</code></p>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-ink-2">{{ __('Estado:') }}</span>
                            @if($empresa->activa)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-green-100 text-success">
                                    {{ __('Activa') }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-gray-100 text-ink-2">
                                    {{ __('Inactiva') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <form action="{{ route('admin.empresas.toggle', $empresa) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 border {{ $empresa->activa ? 'border-danger text-danger hover:bg-red-50' : 'border-success text-success hover:bg-green-50' }} text-sm font-medium rounded transition-colors duration-150">
                                {{ $empresa->activa ? __('Desactivar empresa') : __('Activar empresa') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección 2 — Departamentos -->
            <div class="bg-white border border-border rounded-lg shadow-sm">
                <div class="p-6 border-b border-border flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-ink">{{ __('Departamentos') }}</h3>
                    <a href="{{ route('admin.departamentos.crear', $empresa) }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
                        {{ __('Agregar departamento') }}
                    </a>
                </div>

                @if($departamentos->isEmpty())
                    <div class="p-12 text-center text-ink-2 text-sm">
                        {{ __('Aún no hay departamentos registrados en esta empresa.') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-surface">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Nombre') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Jefe de Departamento') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Colaboradores Evaluados') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-border">
                                @foreach($departamentos as $depto)
                                    <tr class="hover:bg-surface/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                            {{ $depto->nombre_departamento }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                            {{ $depto->jefe ? $depto->jefe->nombre_completo : __('Sin Jefe asignado') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                            {{ $depto->empleados_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('admin.departamentos.destroy', [$empresa, $depto]) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este departamento?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger hover:text-red-700">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Sección 3 — Usuarios -->
            <div class="bg-white border border-border rounded-lg shadow-sm">
                <div class="p-6 border-b border-border flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-ink">{{ __('Usuarios') }}</h3>
                    <a href="{{ route('admin.usuarios.crear', $empresa) }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
                        {{ __('Agregar usuario') }}
                    </a>
                </div>

                @if($usuariosPorTipo->isEmpty())
                    <div class="p-12 text-center text-ink-2 text-sm">
                        {{ __('Aún no hay usuarios registrados en esta empresa.') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-surface">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Nombre Completo') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Rol') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Departamento') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Correo / Usuario') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-border">
                                @foreach($usuariosPorTipo as $tipo => $usuarios)
                                    @foreach($usuarios as $usr)
                                        <tr class="hover:bg-surface/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                                {{ $usr->nombre_completo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @php
                                                    $badgeColors = [
                                                        'Superadmin' => 'bg-purple-100 text-purple-700',
                                                        'Dueño'      => 'bg-blue-100 text-blue-700',
                                                        'Jefe'       => 'bg-amber-100 text-amber-700',
                                                        'Empleado'   => 'bg-gray-100 text-ink-2',
                                                    ];
                                                    $color = $badgeColors[$tipo] ?? 'bg-gray-100 text-ink-2';
                                                @endphp
                                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded {{ $color }}">
                                                    {{ $tipo }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                                {{ $usr->departamento ? $usr->departamento->nombre_departamento : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                                <div>{{ $usr->correo }}</div>
                                                @if($usr->user_name)
                                                    <div class="text-xs text-ink-3">({{ $usr->user_name }})</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('admin.usuarios.destroy', [$empresa, $usr]) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-danger hover:text-red-700">
                                                        {{ __('Eliminar') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', $empresa->nombre . ' | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Empresas', 'url' => route('admin.empresas.index')],
        ['label' => $empresa->nombre, 'url' => null],
    ]" />
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ $empresa->nombre }}
        </h2>
        <a href="{{ route('admin.empresas.index') }}" class="btn-secondary">
            {{ __('← Volver a Empresas') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Sección 1 — Resumen de la empresa -->
            <div class="card">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h3 class="text-base font-semibold text-ink mb-1">{{ __('Resumen de la Empresa') }}</h3>
                        <p class="text-sm text-ink-2 mb-2">Identificador único (slug): <code
                                class="bg-surface px-1.5 py-0.5 rounded text-primary font-mono text-xs">{{ $empresa->slug }}</code>
                        </p>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-ink-2">{{ __('Estado:') }}</span>
                            @if ($empresa->activa)
                                <span class="badge bg-green-100 text-success">
                                    {{ __('Activa') }}
                                </span>
                            @else
                                <span class="badge bg-gray-100 text-ink-2">
                                    {{ __('Inactiva') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <form action="{{ route('admin.empresas.toggle', $empresa) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-secondary">
                                {{ $empresa->activa ? __('Desactivar empresa') : __('Activar empresa') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección 2 — Departamentos -->
            <div class="bg-canvas border border-border rounded-lg shadow-card overflow-hidden">
                <div class="p-6 border-b border-border flex justify-between items-center bg-white">
                    <h3 class="text-base font-semibold text-ink">{{ __('Departamentos') }}</h3>
                    <a href="{{ route('admin.departamentos.crear', $empresa) }}" class="btn-primary">
                        {{ __('Agregar departamento') }}
                    </a>
                </div>

                @if ($departamentos->isEmpty())
                    <div class="p-8 text-center text-ink-2 text-sm bg-white">
                        {{ __('Aún no hay departamentos registrados en esta empresa.') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-surface">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Nombre') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Jefe de Departamento') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Colaboradores Evaluados') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-border">
                                @foreach ($departamentos as $depto)
                                    <tr class="hover:bg-surface/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                            {{ $depto->nombre_departamento }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                            @if ($depto->jefe)
                                                {{ $depto->jefe->nombre_completo }}
                                            @else
                                                {{ __('Sin jefe asignado') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                            {{ $depto->empleados_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center gap-3">
                                                <a href="{{ route('admin.departamentos.editar', [$empresa, $depto]) }}"
                                                    class="text-primary hover:text-primary-hover">
                                                    {{ __('Editar') }}
                                                </a>
                                                <form
                                                    action="{{ route('admin.departamentos.destroy', [$empresa, $depto]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Seguro que deseas eliminar este departamento?');"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-danger hover:text-red-700 bg-transparent border-0 cursor-pointer text-sm font-medium">
                                                        {{ __('Eliminar') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Sección 3 — Usuarios -->
            <div class="bg-canvas border border-border rounded-lg shadow-card overflow-hidden">
                <div class="p-6 border-b border-border flex justify-between items-center bg-white">
                    <h3 class="text-base font-semibold text-ink">{{ __('Usuarios') }}</h3>
                    <a href="{{ route('admin.usuarios.crear', $empresa) }}" class="btn-primary">
                        {{ __('Agregar usuario') }}
                    </a>
                </div>

                @if ($usuariosPorTipo->isEmpty())
                    <div class="p-8 text-center text-ink-2 text-sm bg-white">
                        {{ __('Aún no hay usuarios registrados en esta empresa.') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-surface">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Nombre Completo') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Rol') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Departamento') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Correo / Usuario') }}
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-border">
                                @foreach ($usuariosPorTipo as $tipo => $usuarios)
                                    @foreach ($usuarios as $usr)
                                        <tr class="hover:bg-surface/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                                {{ $usr->nombre_completo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @php
                                                    $badgeColors = [
                                                        'Superadmin' =>
                                                            'bg-purple-50 text-purple-700 border border-purple-100',
                                                        'Dueño' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                                        'Jefe' => 'bg-amber-50 text-warning border border-amber-100',
                                                        'Empleado' => 'bg-gray-100 text-ink-2',
                                                    ];
                                                    $color = $badgeColors[$tipo] ?? 'bg-gray-100 text-ink-2';
                                                @endphp
                                                <span class="badge {{ $color }}">
                                                    {{ $tipo }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                                {{ $usr->departamento ? $usr->departamento->nombre_departamento : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                                <div>{{ $usr->correo }}</div>
                                                @if ($usr->user_name)
                                                    <div class="text-xs text-ink-3">({{ $usr->user_name }})</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center gap-3">
                                                    <a href="{{ route('admin.usuarios.editar', [$empresa, $usr]) }}"
                                                        class="text-primary hover:text-primary-hover">
                                                        {{ __('Editar') }}
                                                    </a>
                                                    <form action="{{ route('admin.usuarios.destroy', [$empresa, $usr]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-danger hover:text-red-700 bg-transparent border-0 cursor-pointer text-sm font-medium">
                                                            {{ __('Eliminar') }}
                                                        </button>
                                                    </form>
                                                </div>
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

@extends('layouts.app')

@section('title', 'Crear Usuario | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Empresas', 'url' => route('admin.empresas.index')],
        ['label' => $empresa->nombre, 'url' => route('admin.empresas.show', $empresa)],
        ['label' => 'Nuevo usuario', 'url' => null],
    ]" />
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Usuario') }} — {{ $empresa->nombre }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold text-ink mb-4">{{ __('Selecciona el rol a crear') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.usuarios.crear-tipo', [$empresa, 'dueno']) }}"
                        class="p-4 border border-border rounded-lg hover:shadow-card transition-colors bg-canvas">
                        <h4 class="font-medium mb-2">{{ __('Dueño') }}</h4>
                        <p class="text-sm text-ink-2">{{ __('Acceso a toda la empresa. Evalúa a todos los jefes.') }}</p>
                    </a>
                    <a href="{{ route('admin.usuarios.crear-tipo', [$empresa, 'jefe']) }}"
                        class="p-4 border border-border rounded-lg hover:shadow-card transition-colors bg-canvas">
                        <h4 class="font-medium mb-2">{{ __('Jefe') }}</h4>
                        <p class="text-sm text-ink-2">{{ __('Líder de departamento. Evalúa a sus empleados.') }}</p>
                    </a>
                    <a href="{{ route('admin.usuarios.crear-tipo', [$empresa, 'empleado']) }}"
                        class="p-4 border border-border rounded-lg hover:shadow-card transition-colors bg-canvas">
                        <h4 class="font-medium mb-2">{{ __('Empleado') }}</h4>
                        <p class="text-sm text-ink-2">{{ __('Colaborador evaluado. Sin acceso al sistema.') }}</p>
                    </a>
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.empresas.show', $empresa) }}" class="btn-secondary">{{ __('Cancelar') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

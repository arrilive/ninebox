@extends('layouts.app')

@section('title', 'Crear Empleado | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Empresas', 'url' => route('admin.empresas.index')],
        ['label' => $empresa->nombre, 'url' => route('admin.empresas.show', $empresa)],
        ['label' => 'Nuevo usuario', 'url' => route('admin.usuarios.crear', $empresa)],
        ['label' => 'Empleado', 'url' => null],
    ]" />
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Empleado') }} — {{ $empresa->nombre }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <form action="{{ route('admin.usuarios.store-tipo', [$empresa, 'empleado']) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="nombre" class="form-label">{{ __('Nombre') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="form-input"
                            required>
                    </div>

                    <div class="mb-6">
                        <label for="apellido_paterno" class="form-label">{{ __('Apellido Paterno') }}</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno"
                            value="{{ old('apellido_paterno') }}" class="form-input">
                    </div>

                    <div class="mb-6">
                        <label for="departamento_id" class="form-label">{{ __('Departamento') }} <span
                                class="text-danger">*</span></label>
                        <select name="departamento_id" id="departamento_id" class="form-input" required>
                            <option value="">{{ __('Seleccione un departamento...') }}</option>
                            @foreach ($departamentos as $d)
                                <option value="{{ $d->id }}">{{ $d->nombre_departamento }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-border">
                        <a href="{{ route('admin.usuarios.crear', $empresa) }}"
                            class="btn-secondary">{{ __('Volver') }}</a>
                        <button type="submit" class="btn-primary">{{ __('Crear Empleado') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

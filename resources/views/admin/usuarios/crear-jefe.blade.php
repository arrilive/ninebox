@extends('layouts.app')

@section('title', 'Crear Jefe | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Empresas', 'url' => route('admin.empresas.index')],
        ['label' => $empresa->nombre, 'url' => route('admin.empresas.show', $empresa)],
        ['label' => 'Nuevo usuario', 'url' => route('admin.usuarios.crear', $empresa)],
        ['label' => 'Jefe', 'url' => null],
    ]" />
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Jefe') }} — {{ $empresa->nombre }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <form action="{{ route('admin.usuarios.store-tipo', [$empresa, 'jefe']) }}" method="POST">
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
                        <label for="correo" class="form-label">{{ __('Correo (opcional)') }}</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo') }}" class="form-input">
                    </div>

                    <div class="mb-6">
                        <label for="user_name" class="form-label">{{ __('Nombre de Usuario') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="user_name" id="user_name" value="{{ old('user_name') }}"
                            class="form-input" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="password" class="form-label">{{ __('Contraseña') }} <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-input" required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="form-label">{{ __('Confirmar Contraseña') }} <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-input" required>
                        </div>
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
                        <p class="text-xs text-ink-2 mt-2">{{ __('Si no hay departamentos, crea uno primero.') }}</p>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-border">
                        <a href="{{ route('admin.usuarios.crear', $empresa) }}"
                            class="btn-secondary">{{ __('Volver') }}</a>
                        <button type="submit" class="btn-primary">{{ __('Crear Jefe') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

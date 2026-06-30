@extends('layouts.app')

@section('title', 'Editar Departamento | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Empresas', 'url' => route('admin.empresas.index')],
        ['label' => $empresa->nombre, 'url' => route('admin.empresas.show', $empresa)],
        ['label' => 'Editar departamento', 'url' => null],
    ]" />
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Editar Departamento') }} — {{ $empresa->nombre }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <form action="{{ route('admin.departamentos.update', [$empresa, $departamento]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="nombre_departamento" class="form-label">
                            {{ __('Nombre del departamento') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_departamento" id="nombre_departamento"
                            value="{{ old('nombre_departamento', $departamento->nombre_departamento) }}"
                            class="form-input @error('nombre_departamento') border-danger @enderror"
                            placeholder="Ej. Operaciones, Ventas" required>
                        @error('nombre_departamento')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.empresas.show', $empresa) }}" class="btn-secondary">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="btn-primary">
                            {{ __('Guardar cambios') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

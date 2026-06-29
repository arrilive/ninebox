@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Departamento') }} — {{ $empresa->nombre }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white border border-border rounded-lg overflow-hidden shadow-sm p-6">
                <form action="{{ route('admin.departamentos.store', $empresa) }}" method="POST">
                    @csrf

                    <!-- Nombre del departamento -->
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-medium text-ink mb-2">
                            {{ __('Nombre del departamento') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('nombre') border-danger @enderror" placeholder="Ej. Operaciones, Ventas" required>
                        @error('nombre')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jefe del departamento -->
                    <div class="mb-6">
                        <label for="jefe_id" class="block text-sm font-medium text-ink mb-2">
                            {{ __('Jefe de Departamento') }} <span class="text-xs text-ink-2">({{ __('Opcional') }})</span>
                        </label>
                        <select name="jefe_id" id="jefe_id" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('jefe_id') border-danger @enderror">
                            <option value="">{{ __('Seleccione un Jefe...') }}</option>
                            @foreach($jefesDisponibles as $jefe)
                                <option value="{{ $jefe->id }}" {{ old('jefe_id') == $jefe->id ? 'selected' : '' }}>
                                    {{ $jefe->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-ink-2">{{ __('Solo se muestran usuarios que ya tienen asignado el rol Jefe en esta empresa.') }}</p>
                        @error('jefe_id')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.empresas.show', $empresa) }}" class="px-4 py-2 border border-border text-ink-2 hover:bg-surface text-sm font-medium rounded transition-colors duration-150">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
                            {{ __('Crear departamento') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection

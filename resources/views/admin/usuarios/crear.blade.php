@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Usuario') }} — {{ $empresa->nombre }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white border border-border rounded-lg overflow-hidden shadow-sm p-6"
                 x-data="{ selectedRolId: '{{ old('tipo_usuario_id') }}', empleadoRolId: '{{ $tipos->firstWhere('tipo_nombre', 'Empleado')?->id }}' }">
                
                <form action="{{ route('admin.usuarios.store', $empresa) }}" method="POST">
                    @csrf

                    <!-- Nombre(s) -->
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-medium text-ink mb-2">
                            {{ __('Nombre') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('nombre') border-danger @enderror" placeholder="Ej. Juan" required>
                        @error('nombre')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Apellido Paterno -->
                        <div>
                            <label for="apellido_paterno" class="block text-sm font-medium text-ink mb-2">
                                {{ __('Apellido Paterno') }}
                            </label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('apellido_paterno') border-danger @enderror" placeholder="Ej. Pérez">
                            @error('apellido_paterno')
                                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Apellido Materno -->
                        <div>
                            <label for="apellido_materno" class="block text-sm font-medium text-ink mb-2">
                                {{ __('Apellido Materno') }}
                            </label>
                            <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('apellido_materno') border-danger @enderror" placeholder="Ej. Gómez">
                            @error('apellido_materno')
                                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Correo Electrónico -->
                        <div>
                            <label for="correo" class="block text-sm font-medium text-ink mb-2">
                                {{ __('Correo Electrónico') }}
                            </label>
                            <input type="email" name="correo" id="correo" value="{{ old('correo') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('correo') border-danger @enderror" placeholder="ejemplo@correo.com">
                            @error('correo')
                                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nombre de Usuario (user_name) -->
                        <div>
                            <label for="user_name" class="block text-sm font-medium text-ink mb-2">
                                {{ __('Nombre de Usuario') }}
                            </label>
                            <input type="text" name="user_name" id="user_name" value="{{ old('user_name') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('user_name') border-danger @enderror" placeholder="ej. juan.perez">
                            @error('user_name')
                                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Rol -->
                        <div>
                            <label for="tipo_usuario_id" class="block text-sm font-medium text-ink mb-2">
                                {{ __('Rol') }} <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_usuario_id" id="tipo_usuario_id" x-model="selectedRolId" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('tipo_usuario_id') border-danger @enderror" required>
                                <option value="">{{ __('Seleccione un Rol...') }}</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">
                                        {{ $tipo->tipo_nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_usuario_id')
                                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Departamento -->
                        <div>
                            <label for="departamento_id" class="block text-sm font-medium text-ink mb-2">
                                {{ __('Departamento') }}
                            </label>
                            <select name="departamento_id" id="departamento_id" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('departamento_id') border-danger @enderror">
                                <option value="">{{ __('Ninguno / Sin asignar') }}</option>
                                @foreach($departamentos as $depto)
                                    <option value="{{ $depto->id }}" {{ old('departamento_id') == $depto->id ? 'selected' : '' }}>
                                        {{ $depto->nombre_departamento }}
                                    </option>
                                @endforeach
                            </select>
                            @error('departamento_id')
                                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Campos de Contraseña (solo si rol != Empleado) -->
                    <div x-show="selectedRolId && selectedRolId != empleadoRolId" class="border-t border-border pt-6 mt-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contraseña -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-ink mb-2">
                                    {{ __('Contraseña') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" id="password" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('password') border-danger @enderror">
                                @error('password')
                                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirmación de Contraseña -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-ink mb-2">
                                    {{ __('Confirmar Contraseña') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-6 mt-6 border-t border-border">
                        <a href="{{ route('admin.empresas.show', $empresa) }}" class="px-4 py-2 border border-border text-ink-2 hover:bg-surface text-sm font-medium rounded transition-colors duration-150">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
                            {{ __('Crear usuario') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection

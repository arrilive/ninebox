@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Empresa') }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-canvas min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white border border-border rounded-lg overflow-hidden shadow-sm p-6">
                <form action="{{ route('admin.empresas.store') }}" method="POST">
                    @csrf

                    <!-- Nombre -->
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-medium text-ink mb-2">
                            {{ __('Nombre de la empresa') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('nombre') border-danger @enderror" placeholder="Ej. Dunosusa" required>
                        @error('nombre')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="mb-6">
                        <label for="slug" class="block text-sm font-medium text-ink mb-2">
                            {{ __('Slug') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="w-full px-3 py-2 border border-border rounded text-ink focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary @error('slug') border-danger @enderror" placeholder="ej-dunosusa" required>
                        <p class="mt-2 text-xs text-ink-2">{{ __('Identificador único para la URL. Solo letras minúsculas, números y guiones.') }}</p>
                        @error('slug')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.empresas.index') }}" class="px-4 py-2 border border-border text-ink-2 hover:bg-surface text-sm font-medium rounded transition-colors duration-150">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
                            {{ __('Crear empresa') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Script para auto-generar slug a partir del nombre -->
    <script>
        document.getElementById('nombre').addEventListener('input', function() {
            let name = this.value;
            let slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // remove invalid chars
                .replace(/\s+/g, '-')         // collapse whitespace and replace by -
                .replace(/-+/g, '-');         // collapse dashes
            document.getElementById('slug').value = slug;
        });
    </script>
@endsection

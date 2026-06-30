@extends('layouts.app')

@section('title', 'Crear Empresa | NineBox')

@section('header')
    <x-breadcrumb :items="[
        ['label' => 'Empresas', 'url' => route('admin.empresas.index')],
        ['label' => 'Nueva empresa', 'url' => null],
    ]" />
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('Crear Empresa') }}
    </h2>
@endsection

@section('content')
    <div class="py-12 bg-surface min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="card">
                <form action="{{ route('admin.empresas.store') }}" method="POST">
                    @csrf

                    <!-- Nombre -->
                    <div class="mb-6">
                        <label for="nombre" class="form-label">
                            {{ __('Nombre de la empresa') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                            class="form-input @error('nombre') border-danger @enderror" placeholder="Ej. Dunosusa" required>
                        @error('nombre')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>



                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.empresas.index') }}" class="btn-secondary">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="btn-primary">
                            {{ __('Crear empresa') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

@endsection

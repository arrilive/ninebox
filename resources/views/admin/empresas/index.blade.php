@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('Empresas') }}
        </h2>
        <a href="{{ route('admin.empresas.crear') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
            {{ __('Nueva empresa') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="py-12 bg-canvas min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-success text-success text-sm rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border border-border rounded-lg overflow-hidden shadow-sm">
                @if($empresas->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-ink-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-ink">{{ __('Aún no hay empresas') }}</h3>
                        <p class="mt-1 text-sm text-ink-2">{{ __('Crea la primera empresa para comenzar a administrar el sistema.') }}</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.empresas.crear') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded transition-colors duration-150">
                                {{ __('Crear la primera') }}
                            </a>
                        </div>
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
                                        {{ __('Departamentos') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Usuarios') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ink-2 uppercase tracking-wider">
                                        {{ __('Estado') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 class=text-left text-xs font-semibold text-ink-2 uppercase tracking-wider text-right">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-border">
                                @foreach($empresas as $empresa)
                                    <tr class="hover:bg-surface/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-ink">
                                                    {{ $empresa->nombre }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                            {{ $empresa->departamentos_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-2">
                                            {{ $empresa->usuarios_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($empresa->activa)
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded bg-green-100 text-success">
                                                    {{ __('Activa') }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded bg-gray-100 text-ink-2">
                                                    {{ __('Inactiva') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-3">
                                                <a href="{{ route('admin.empresas.show', $empresa) }}" class="text-primary hover:text-primary-hover">
                                                    {{ __('Ver') }}
                                                </a>
                                                <form action="{{ route('admin.empresas.toggle', $empresa) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-sm font-medium {{ $empresa->activa ? 'text-danger hover:text-red-700' : 'text-success hover:text-green-700' }}">
                                                        {{ $empresa->activa ? __('Desactivar') : __('Activar') }}
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

        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Personalizaciones del Sistema')

@section('content')
@php
    $tabInicial = request()->get('tab', 'redes-sociales');
@endphp
<div class="space-y-8" x-data="{ tab: '{{ $tabInicial }}' }">
    <h1 class="text-3xl font-extrabold tracking-tight">
        <span class="bg-gradient-to-r from-[var(--tema-from)] to-[var(--tema-to)] bg-clip-text text-transparent">Personalizaciones del Sistema</span>
    </h1>
    <p class="text-gray-600">Personaliza los textos, enlaces y elementos visuales del sistema para adaptarlos a tu empresa.</p>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border-2 border-gray-900 p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Configuración</h2>

        {{-- Pestañas --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-4">
            <button type="button" @click="tab = 'redes-sociales'" :class="tab === 'redes-sociales' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Redes Sociales</button>
            <button type="button" @click="tab = 'login'" :class="tab === 'login' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Configuración de Login</button>
            <button type="button" @click="tab = 'pie-pagina'" :class="tab === 'pie-pagina' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Pie de Página</button>
            <button type="button" @click="tab = 'encabezado'" :class="tab === 'encabezado' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Encabezado</button>
            <button type="button" @click="tab = 'otros-textos'" :class="tab === 'otros-textos' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Otros Textos</button>
        </div>

        {{-- Contenido de pestañas --}}
        @include('configuracion.tabs.redes-sociales')
        @include('configuracion.tabs.login')
        @include('configuracion.tabs.pie-pagina')
        @include('configuracion.tabs.encabezado')
        @include('configuracion.tabs.otros-textos')
    </div>
</div>
@endsection

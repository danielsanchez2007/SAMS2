@extends('layouts.app')

@section('title', 'Equipos de baja')

@section('content')

<div class="space-y-8">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-100">
                Equipos de baja
            </h1>
            <p class="mt-1 text-sm text-slate-400 max-w-2xl">
                Lista de equipos que han sido dados de baja con sus respectivas actas.
            </p>
        </div>
    </div>

    {{-- Barra de filtros --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('equipos-baja.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Código, descripción o motivo..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.150ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('equipos-baja.index', ['per_page' => $perPage ?? 20]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
                @endif
            </div>
            <span class="text-sm text-slate-500">Mostrar</span>
            <select name="per_page" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" @change="$refs.filterForm.submit()">
                <option value="10" {{ ($perPage ?? 20) == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-slate-500">por página</span>
        </form>
    </div>

    {{-- Tabla de equipos de baja --}}
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300 min-w-[800px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">
                            Fecha de baja
                        </th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">
                            Equipo
                        </th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">
                            Tipo
                        </th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">
                            Resumen del motivo
                        </th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">
                            Acta No.
                        </th>
                        <th class="text-right py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($equiposBaja as $baja)
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-4 px-6 text-slate-100">
                                {{ optional($baja->fecha_baja)->format('Y-m-d') }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-slate-100">{{ $baja->equipo->codigo ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400">{{ Str::limit($baja->equipo->descripcion ?? '', 50) }}</div>
                            </td>
                            <td class="py-4 px-6 text-slate-300">
                                {{ $baja->equipo->tipoEquipo->nombre ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-slate-300">
                                <div class="max-w-xs">{{ Str::limit($baja->resumen_baja ?? 'Sin resumen', 80) }}</div>
                            </td>
                            <td class="py-4 px-6 text-slate-300">
                                {{ $baja->acta_numero ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if($baja->acta_pdf)
                                <a href="{{ route('equipos-baja.download', $baja) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600/90 hover:bg-indigo-500 text-white shadow-sm transition"
                                   target="_blank">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Descargar</span>
                                </a>
                                @else
                                <span class="text-xs text-slate-500">Sin PDF</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 px-6 text-center text-slate-500 text-lg italic">
                                No hay equipos de baja registrados todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($equiposBaja, 'links'))
        <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/50">
            {{ $equiposBaja->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

</div>

@endsection

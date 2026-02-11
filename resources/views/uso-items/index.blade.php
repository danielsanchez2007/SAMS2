@extends('layouts.app')

@section('title', 'Uso de Items')

@section('content')

<div class="space-y-8" x-data="usoItemsApp()">

    <!-- Barra: filtro automático, tamaño tabla, acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('uso-items.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nombre del uso..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('uso-items.index', ['per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
                @endif
            </div>
            <span class="text-sm text-slate-500">Mostrar</span>
            <select name="per_page" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" @change="$refs.filterForm.submit()">
                <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-slate-500">por página</span>
        </form>
        <div class="flex flex-wrap gap-3">
            <button 
                type="button" 
                @click="openModalUso()" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar Uso
            </button>

            <button 
                type="button" 
                @click="showExportModal = true" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-slate-700 text-slate-300 text-sm font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300 shadow-md"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-4-3v1m0 0v1m0-1h1m-1 0h-1"/>
                </svg>
                Exportar
            </button>
        </div>
    </div>

    <!-- Tabla de usos de items -->
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300 min-w-[600px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre del Uso</th>
                        <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($usoItems as $i => $u)
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-4 px-6 font-medium text-slate-100">{{ $u->nombre }}</td>
                            <td class="py-4 px-8 text-right flex items-center justify-end gap-2">
                                <button 
                                    type="button" 
                                    @click="editUso({{ json_encode($u) }})" 
                                    class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                    title="Editar uso"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <form action="{{ route('uso-items.destroy.uso', $u) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar este uso? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200"
                                        title="Eliminar uso"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-16 px-6 text-center text-slate-500 text-lg italic">
                                No hay usos registrados todavía.<br>
                                <span class="text-slate-400">Haz clic en "Agregar Uso" para comenzar.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if(method_exists($usoItems, 'links'))
        <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/50">
            {{ $usoItems->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    <!-- Modal de Exportación -->
    <div x-show="showExportModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" 
         >
        <div class="tema-modal-export tema-modal-content rounded-2xl w-full max-w-4xl my-8 p-8" @click.stop>
            <h3 class="text-2xl font-bold mb-6 flex items-center gap-3" style="color: var(--tema-primary);">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-4-3v1m0 0v1m0-1h1m-1 0h-1"/>
                </svg>
                Exportar Usos de Items
            </h3>

            <div class="bg-white rounded-xl p-6 mb-8 border border-gray-200 max-h-[65vh] overflow-y-auto">
                <div class="flex justify-center gap-12 mb-8 opacity-90">
                    @if(!empty($logoSecondary))
                        <img src="{{ asset('storage/' . $logoSecondary) }}" alt="Logo secundario" class="h-14 object-contain bg-white border border-gray-900 rounded-lg px-4 py-2">
                    @else
                        <img src="{{ asset('logos/LOGO-INSTITUTO-PREVENTION-WORLD.png') }}" alt="Logo secundario" class="h-14 object-contain bg-white border border-gray-900 rounded-lg px-4 py-2">
                    @endif
                    @if(!empty($logoMain))
                        <img src="{{ asset('storage/' . $logoMain) }}" alt="Logo principal" class="h-14 object-contain bg-white border border-gray-900 rounded-lg px-4 py-2">
                    @else
                        <img src="{{ asset('img/logos/logoSams.png') }}" alt="Logo principal" class="h-14 object-contain bg-white border border-gray-900 rounded-lg px-4 py-2">
                    @endif
                </div>

                <p class="text-lg font-semibold mb-5" style="color: var(--tema-primary);">Lista de usos de items</p>

                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-800/80 text-slate-200 uppercase text-xs tracking-wider">
                            <th class="border border-slate-700 p-3 text-left">Nombre del Uso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($usoItems as $i => $u)
                            <tr class="hover:bg-slate-900/40 transition-colors">
                                <td class="border border-slate-800 p-3 font-medium">{{ $u->nombre }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="border border-slate-800 p-6 text-center text-slate-500">
                                    No hay usos para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('uso-items.export.excel') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl tema-gradient text-white font-semibold transition-all shadow-md hover:opacity-90">
                    Descargar Excel
                </a>

                <a href="{{ route('uso-items.export.pdf') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl tema-gradient text-white font-semibold transition-all shadow-md hover:opacity-90">
                    Descargar PDF
                </a>
            </div>

            <button type="button" 
                    @click="showExportModal = false" 
                    class="mt-6 w-full py-3.5 rounded-xl border-2 font-medium">
                Cerrar
            </button>
        </div>
    </div>

    <!-- Modal de Crear/Editar Uso -->
    @include('uso-items.modals.uso')

</div>

<!-- Script Alpine (sin cambios) -->
<script>
function usoItemsApp() {    
    return {
        showExportModal: false,
        modalUso: false,
        formUso: { id: null, nombre: '' },
        openModalUso() {
            this.formUso = { id: null, nombre: '' };
            this.modalUso = true;
        },
        editUso(u) {
            this.formUso = { id: u.id, nombre: u.nombre };
            this.modalUso = true;
        }
    };
}
</script>

@endsection
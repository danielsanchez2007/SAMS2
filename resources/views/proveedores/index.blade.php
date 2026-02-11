@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')

<div class="space-y-8" x-data="{ showModal: false, showExportModal: false, formData: { id: null, nombre: '' } }">

    <!-- Barra: filtro automático, tamaño tabla, acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('proveedores.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nombre del proveedor..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('proveedores.index', ['per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
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
            <button type="button" 
                    @click="formData = { id: null, nombre: '' }; showModal = true" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar Proveedor
            </button>

            <button type="button" @click="showExportModal = true" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-slate-700 text-slate-300 text-sm font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-4-3v1m0 0v1m0-1h1m-1 0h-1"/>
                </svg>
                Exportar
            </button>
        </div>
    </div>

    <!-- Tabla de proveedores -->
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300 min-w-[600px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre del Proveedor</th>
                        <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($proveedores as $i => $p)
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-4 px-6 font-medium text-slate-100">{{ $p->nombre }}</td>
                            <td class="py-4 px-8 text-right flex items-center justify-end gap-2">
                                <button type="button" 
                                        @click="formData = { id: {{ $p->id }}, nombre: '{{ addslashes($p->nombre) }}' }; showModal = true" 
                                        class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                        title="Editar proveedor">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <form action="{{ route('proveedores.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar este proveedor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200"
                                            title="Eliminar proveedor">
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
                                No hay proveedores registrados todavía.<br>
                                <span class="text-slate-400">Haz clic en "Agregar Proveedor" para comenzar.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($proveedores, 'hasPages') && ($proveedores->hasPages() || $proveedores->total() > 0))
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-t border-slate-800/60 bg-slate-900/50">
            <p class="text-sm text-slate-400">Mostrando {{ $proveedores->firstItem() ?? 0 }} a {{ $proveedores->lastItem() ?? 0 }} de {{ $proveedores->total() }} proveedores</p>
            <div class="flex items-center gap-2">
                @if ($proveedores->onFirstPage())
                    <span class="px-3 py-2 rounded-lg border border-slate-700 text-sm text-slate-500 cursor-not-allowed">Anterior</span>
                @else
                    <a href="{{ $proveedores->previousPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-800 transition-colors">Anterior</a>
                @endif
                <span class="text-sm text-slate-400 px-2">Página {{ $proveedores->currentPage() }} de {{ $proveedores->lastPage() }}</span>
                @if ($proveedores->hasMorePages())
                    <a href="{{ $proveedores->nextPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-800 transition-colors">Siguiente</a>
                @else
                    <span class="px-3 py-2 rounded-lg border border-slate-700 text-sm text-slate-500 cursor-not-allowed">Siguiente</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Modal Agregar/Editar Proveedor -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" 
         >
        
        <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700/60 backdrop-blur-md" @click.stop>
            
            <div class="p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-slate-100 mb-6" 
                    x-text="formData.id ? 'Editar Proveedor' : 'Nuevo Proveedor'">
                </h3>

                <form :action="formData.id ? '{{ url('proveedores') }}/' + formData.id : '{{ route('proveedores.store') }}'" 
                      method="POST" 
                      class="space-y-6">

                    @csrf
                    <template x-if="formData.id">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Nombre del Proveedor <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="nombre" 
                            x-model="formData.nombre" 
                            required 
                            placeholder="Ej: Proveedores Industriales S.A.S., Equipos y Herramientas Ltda..." 
                            class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                        >
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <button 
                            type="submit" 
                            class="flex-1 py-3.5 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                        >
                            Guardar Proveedor
                        </button>

                        <button 
                            type="button" 
                            @click="showModal = false" 
                            class="flex-1 py-3.5 rounded-xl border border-slate-700 text-slate-300 font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300"
                        >
                            Cancelar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Modal Exportar -->
    <div x-show="showExportModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
        
        <div class="tema-modal-opaco tema-modal-export tema-modal-content bg-slate-900 rounded-2xl shadow-2xl w-full max-w-4xl my-8 p-8 border border-slate-700/60" @click.stop>
            
            <h3 class="text-2xl font-bold text-indigo-400 mb-6 flex items-center gap-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-4-3v1m0 0v1m0-1h1m-1 0h-1"/>
                </svg>
                Exportar Proveedores
            </h3>

            <div class="bg-slate-950/60 rounded-xl p-6 mb-8 border border-slate-800/50 max-h-[65vh] overflow-y-auto">
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

                <p class="text-lg font-semibold text-indigo-300 mb-5">Lista de proveedores</p>

                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-800/80 text-slate-200 uppercase text-xs tracking-wider">
                            <th class="border border-slate-700 p-3 text-left">Nombre del Proveedor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($proveedores as $i => $p)
                            <tr class="hover:bg-slate-900/40 transition-colors">
                                <td class="border border-slate-800 p-3 font-medium">{{ $p->nombre }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="border border-slate-800 p-6 text-center text-slate-500">
                                    No hay proveedores para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('proveedores.export.excel') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-700/90 hover:bg-emerald-600 text-white font-semibold transition-all shadow-md hover:shadow-lg">
                    Descargar Excel
                </a>

                <a href="{{ route('proveedores.export.pdf') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-rose-700/90 hover:bg-rose-600 text-white font-semibold transition-all shadow-md hover:shadow-lg">
                    Descargar PDF
                </a>
            </div>

            <button type="button" 
                    @click="showExportModal = false" 
                    class="mt-6 w-full py-3.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white transition-all duration-200">
                Cerrar
            </button>
        </div>
    </div>

</div>

@endsection
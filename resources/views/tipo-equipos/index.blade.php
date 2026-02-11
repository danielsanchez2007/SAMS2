@extends('layouts.app')

@section('title', 'Tipo de Equipos')

@section('content')

<div class="space-y-8" x-data="tipoEquiposApp()">

    @if(session('success'))
    <div class="rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Barra: filtro automático, tamaño tabla, acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('tipo-equipos.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nombre del tipo de equipo..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('tipo-equipos.index', ['per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
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
                @click="openModalTipo()" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar Tipo
            </button>

            <button 
                type="button" 
                @click="showExportModal = true" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-btn-outline text-sm font-medium transition-all duration-300 shadow-md"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-4-3v1m0 0v1m0-1h1m-1 0h-1"/>
                </svg>
                Exportar
            </button>
        </div>
    </div>

    <!-- Tabla de tipos de equipos -->
        <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-300 min-w-[600px]">
                    <thead class="bg-slate-800/90">
                        <tr>
                            <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre del Tipo de Equipo</th>
                            <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-40">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                    @forelse($tipoEquipos as $i => $e)
                            <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                                <td class="py-4 px-6 font-medium text-slate-100">{{ $e->nombre }}</td>
                                <td class="py-4 px-8 text-right">
                                    <div class="flex items-center justify-end gap-1 flex-wrap">
                                        <button 
                                            type="button" 
                                            @click="editTipo({{ json_encode($e) }})" 
                                            class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                            title="Editar tipo de equipo"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        <form action="{{ route('tipo-equipos.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar este tipo de equipo? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200" title="Eliminar tipo de equipo">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-16 px-6 text-center text-slate-500 text-lg italic">
                                    No hay tipos de equipos registrados todavía.<br>
                                    <span class="text-slate-400">Haz clic en "Agregar Tipo" para comenzar.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($tipoEquipos, 'links'))
            <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/50">
                {{ $tipoEquipos->links('pagination::tailwind') }}
            </div>
            @endif
        </div>

    <!-- Modal de Exportación -->
    <div x-show="showExportModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto" 
         >
        <div class="tema-modal-opaco tema-modal-export tema-modal-content rounded-xl max-w-6xl w-full my-8 p-6 text-gray-900" @click.stop>
            {{-- Paso 1: Seleccionar tipo(s) de equipo --}}
            <div x-show="exportStep === 1">
                <h3 class="text-lg font-semibold mb-4" style="color: var(--tema-primary);">Seleccionar tipo(s) de equipo</h3>
                <p class="text-sm text-gray-600 mb-4">Elige uno, varios o todos los <strong>tipos de equipo</strong>. Solo se exportarán los equipos de los tipos elegidos.</p>
                <div class="flex gap-2 mb-4">
                    <button type="button" @click="exportSelectAllTipos()" class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-100">Seleccionar todos</button>
                    <button type="button" @click="exportDeselectAllTipos()" class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-100">Ninguno</button>
                </div>
                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4 mb-6 flex flex-wrap gap-3">
                    @foreach($todosLosTipoEquipos ?? [] as $te)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="exportTipoEquipoIds" value="{{ $te->id }}" class="rounded border-gray-400 text-[var(--tema-primary)] focus:ring-[var(--tema-primary)]">
                            <span class="text-sm">{{ $te->nombre }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showExportModal = false" class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium">Cancelar</button>
                    <button type="button" @click="exportShowPreview()" class="flex-1 px-4 py-2.5 rounded-lg tema-gradient text-white text-sm font-semibold hover:opacity-90">Ver vista previa</button>
                </div>
            </div>
            {{-- Paso 2: Vista previa y descargas --}}
            <div x-show="exportStep === 2">
                <h3 class="text-lg font-semibold mb-2" style="color: var(--tema-primary);">Vista previa — Programa de trazabilidad</h3>
                <p class="text-sm text-gray-600 mb-4">
                    <span x-text="'Tipos de equipo: ' + (exportTiposNombres.length ? exportTiposNombres.join(', ') : 'Todos los equipos')"></span>
                    · <span x-text="exportTotal + ' equipo(s)'"></span>
                </p>
                <p x-show="exportTiposNombres.length && exportTotal === 0" class="text-sm text-amber-700 mb-3 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200">
                    No hay equipos con los tipos de equipo seleccionados.
                </p>
                <div class="bg-white rounded-lg p-4 mb-6 max-h-[50vh] overflow-x-auto overflow-y-auto border border-gray-200">
                    <table class="w-full text-xs border-collapse border border-[#e3e3e0] min-w-[800px]">
                        <thead>
                            <tr class="bg-[#5B9BD5] text-white">
                                <th class="border p-2">Código</th>
                                <th class="border p-2">Equipo</th>
                                <th class="border p-2">Tipo</th>
                                <th class="border p-2">Serial/Modelo</th>
                                <th class="border p-2">Marca</th>
                                <th class="border p-2">Fabricante</th>
                                <th class="border p-2">F.Fab.</th>
                                <th class="border p-2">F.Compra</th>
                                <th class="border p-2">Uso</th>
                                <th class="border p-2">Cap.Estruct.</th>
                                <th class="border p-2">Ubicación</th>
                                <th class="border p-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in exportPreviewRows" :key="row.numero">
                                <tr>
                                    <td class="border p-2" x-text="row.numero"></td>
                                    <td class="border p-2" x-text="row.codigo"></td>
                                    <td class="border p-2" x-text="row.equipo"></td>
                                    <td class="border p-2" x-text="row.tipo"></td>
                                    <td class="border p-2" x-text="row.serial_modelo"></td>
                                    <td class="border p-2" x-text="row.marca"></td>
                                    <td class="border p-2" x-text="row.fabricante"></td>
                                    <td class="border p-2" x-text="row.fecha_fabricacion"></td>
                                    <td class="border p-2" x-text="row.fecha_compra"></td>
                                    <td class="border p-2" x-text="row.uso"></td>
                                    <td class="border p-2" x-text="row.capacidad_estructural"></td>
                                    <td class="border p-2" x-text="row.ubicacion"></td>
                                    <td class="border p-2" x-text="row.estado"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="exportStep = 1" class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium">← Volver</button>
                    <a :href="exportExcelUrl" class="flex-1 px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 text-center">Descargar Excel</a>
                    <a :href="exportPdfUrl" class="flex-1 px-4 py-2.5 rounded-lg bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 text-center">Descargar PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Crear/Editar Tipo de Equipo -->
    @include('tipo-equipos.modals.tipo')

    <script>
    function tipoEquiposApp() {
        return {
            showExportModal: false,
            modalTipo: false,
            formTipo: { id: null, nombre: '' },
            exportStep: 1,
            exportTipoEquipoIds: [],
            exportPreviewRows: [],
            exportTiposNombres: [],
            exportTotal: 0,
            exportPreviewLoading: false,
            exportSelectAllTipos() {
                const todosLosIds = @json(($todosLosTipoEquipos ?? collect())->pluck('id')->toArray());
                this.exportTipoEquipoIds = todosLosIds;
            },
            exportDeselectAllTipos() {
                this.exportTipoEquipoIds = [];
            },
            async exportShowPreview() {
                if (this.exportTipoEquipoIds.length === 0) {
                    alert('Por favor selecciona al menos un tipo de equipo.');
                    return;
                }
                this.exportPreviewLoading = true;
                try {
                    const params = new URLSearchParams();
                    this.exportTipoEquipoIds.forEach(id => params.append('tipo_equipos[]', id));
                    const response = await fetch('{{ route('tipo-equipos.export.preview') }}?' + params.toString());
                    const data = await response.json();
                    this.exportPreviewRows = data.equipos || [];
                    this.exportTiposNombres = data.tipos_seleccionados || [];
                    this.exportTotal = data.total || 0;
                    this.exportStep = 2;
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar la vista previa.');
                } finally {
                    this.exportPreviewLoading = false;
                }
            },
            get exportExcelUrl() {
                if (this.exportTipoEquipoIds.length === 0) return '#';
                const params = new URLSearchParams();
                this.exportTipoEquipoIds.forEach(id => params.append('tipo_equipos[]', id));
                return '{{ route('tipo-equipos.export.excel') }}?' + params.toString();
            },
            get exportPdfUrl() {
                if (this.exportTipoEquipoIds.length === 0) return '#';
                const params = new URLSearchParams();
                this.exportTipoEquipoIds.forEach(id => params.append('tipo_equipos[]', id));
                return '{{ route('tipo-equipos.export.pdf') }}?' + params.toString();
            },
            openModalTipo() {
                this.formTipo = { id: null, nombre: '' };
                this.modalTipo = true;
            },
            editTipo(e) {
                this.formTipo = { id: e.id, nombre: e.nombre };
                this.modalTipo = true;
            },
            openModalFormato(id, nombre, esReemplazar) {
                this.formatoTipoId = id;
                this.formatoTipoNombre = nombre;
                this.formatoReemplazar = esReemplazar;
                this.modalFormato = true;
            },
            openModalRellenar(id, nombre) {
                this.rellenarTipoId = id;
                this.rellenarTipoNombre = nombre;
                this.modalRellenar = true;
            }
        };
    }
    </script>

</div>

@endsection
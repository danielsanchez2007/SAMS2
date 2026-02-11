@extends('layouts.app')

@section('title', 'Inspeccionar equipos')

@section('content')

<div class="space-y-8" x-data="inspeccionarApp()">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-100">
                Inspeccionar equipos
            </h1>
            <p class="mt-1 text-sm text-slate-400 max-w-2xl">
                Aquí puedes **cargar el PDF de inspección** y **asignarlo a cada tipo de equipo**.
                Es el mismo concepto que la Hoja de Vida, pero pensado para los formatos de inspección.
            </p>
        </div>
    </div>

    {{-- Barra de filtros --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('inspeccionar.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-400">Buscar tipo de equipo:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nombre del tipo de equipo..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.150ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('inspeccionar.index', ['per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
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
    </div>

    {{-- Tabla de tipos de equipos con formato de inspección --}}
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300 min-w-[600px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">
                            Tipo de equipo
                        </th>
                        <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-64">
                            Inspeccionar
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($tipoEquipos as $e)
                        @php $tieneFormato = $e->tieneFormato(); @endphp
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-4 px-6 font-medium text-slate-100">
                                {{ $e->nombre }}
                            </td>
                            <td class="py-4 px-8 text-right">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    @if($tieneFormato)
                                        {{-- Seleccionar equipo de este tipo y hacer inspección --}}
                                        <button type="button"
                                                @click="openSeleccionarEquipo({{ $e->id }}, '{{ addslashes($e->nombre) }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-600/90 hover:bg-emerald-500 text-white shadow-sm transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Seleccionar equipo</span>
                                        </button>
                                    @else
                                        {{-- Aplicar nuevo PDF --}}
                                        <button type="button"
                                                @click="openModalFormato({{ $e->id }}, '{{ addslashes($e->nombre) }}', false)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-sky-600/90 hover:bg-sky-500 text-white shadow-sm transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>Subir PDF de inspección</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-16 px-6 text-center text-slate-500 text-lg italic">
                                No hay tipos de equipos registrados todavía.<br>
                                <span class="text-slate-400">Primero crea los tipos de equipo en el menú de configuraciones.</span>
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

    {{-- Modal Aplicar / Reemplazar formato de inspección --}}
    <div x-show="modalFormato" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 border border-slate-700/60" @click.stop>
            <h3 class="text-lg font-semibold text-slate-200 mb-2" x-text="formatoReemplazar ? 'Reemplazar PDF de inspección' : 'Subir PDF de inspección'"></h3>
            <p class="text-sm text-slate-400 mb-4">Tipo de equipo: <span class="font-medium text-slate-200" x-text="formatoTipoNombre"></span></p>
            <form :action="'{{ url('/tipo-equipos') }}/' + formatoTipoId + '/formato'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="formato_pdf_insp" class="block text-sm font-medium text-slate-400 mb-2">Archivo PDF de inspección</label>
                    <input type="file" name="formato" id="formato_pdf_insp" accept=".pdf" required
                        class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-medium file:cursor-pointer hover:file:bg-indigo-500 rounded-xl border border-slate-700/70 bg-slate-800/60">
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="modalFormato = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 text-sm font-medium hover:bg-slate-800 transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition" x-text="formatoReemplazar ? 'Reemplazar' : 'Subir'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal seleccionar equipo para inspección --}}
    <div x-show="modalSeleccionEquipo" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-4xl my-8 border border-slate-700/60 flex flex-col max-h-[90vh]" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60 flex-shrink-0">
                <h3 class="text-lg font-semibold text-slate-200">Seleccionar equipo para inspección</h3>
                <button type="button" @click="modalSeleccionEquipo = false" class="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700 rounded-lg transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="px-5 py-2 text-sm text-slate-400 border-b border-slate-700/60" x-show="tipoSeleccionNombre" x-text="'Tipo de equipo: ' + tipoSeleccionNombre"></p>
            <div class="p-4 flex-1 min-h-0 overflow-hidden">
                <div x-show="equiposLoading" class="py-10 text-center text-slate-400 text-sm">Cargando equipos…</div>
                <div x-show="!equiposLoading && equiposTipo.length === 0" class="py-10 text-center text-slate-500 text-sm">
                    No hay equipos registrados para este tipo.
                </div>
                <div x-show="!equiposLoading && equiposTipo.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm text-slate-200 min-w-[500px]">
                        <thead class="bg-slate-800/80">
                            <tr>
                                <th class="py-2 px-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Código</th>
                                <th class="py-2 px-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Descripción</th>
                                <th class="py-2 px-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Sede</th>
                                <th class="py-2 px-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="py-2 px-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            <template x-for="eq in equiposTipo" :key="eq.id">
                                <tr class="hover:bg-slate-800/60">
                                    <td class="py-2 px-3" x-text="eq.codigo"></td>
                                    <td class="py-2 px-3" x-text="eq.descripcion"></td>
                                    <td class="py-2 px-3" x-text="eq.sede || '—'"></td>
                                    <td class="py-2 px-3" x-text="eq.estado || '—'"></td>
                                    <td class="py-2 px-3 text-right">
                                        <button type="button"
                                                @click="abrirInspeccion(eq)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-600/90 hover:bg-emerald-500 text-white shadow-sm transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span x-text="(eq.inspecciones_count || 0) > 0 ? 'Volver a inspeccionar' : 'Inspeccionar'"></span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal realizar inspección (PDF + resumen a guardar) --}}
    <div x-show="modalInspeccion" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 overflow-y-auto">
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-5xl my-8 border border-slate-700/60 flex flex-col max-h-[95vh]" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60 flex-shrink-0">
                <div>
                    <h3 class="text-lg font-semibold text-slate-200">Inspección del equipo</h3>
                    <p class="text-xs text-slate-400 mt-1" x-show="inspeccionTipoNombre" x-text="'Tipo: ' + inspeccionTipoNombre"></p>
                    <p class="text-xs text-slate-400" x-show="inspeccionEquipoCodigo" x-text="'Equipo: ' + inspeccionEquipoCodigo"></p>
                </div>
                <button type="button" @click="cerrarInspeccion()" class="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700 rounded-lg transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-[2fr,1fr] gap-4 p-4 flex-1 min-h-0 overflow-hidden">
                <div class="border border-slate-700/60 rounded-lg bg-white min-h-[400px] overflow-hidden">
                    <template x-if="modalInspeccion && inspeccionTipoId">
                        <iframe
                            x-ref="iframeInspeccion"
                            :src="'{{ route('inspeccionar.formato-html', ['tipoEquipo' => 'ID']) }}'.replace('ID', inspeccionTipoId)"
                            class="w-full h-[70vh] lg:h-full rounded-lg border-0"
                            title="Formato de inspección editable"
                            @load="openInspeccionFormatoModal()">
                        </iframe>
                    </template>
                </div>
                <div class="flex flex-col gap-3 border border-slate-700/60 rounded-lg bg-slate-900/70 p-4">
                    <h4 class="text-sm font-semibold text-slate-200 mb-1">Resumen de la inspección</h4>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Fecha de la inspección</label>
                        <input type="date" x-model="formInspeccion.fecha_inspeccion" readonly class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Validez de la inspección</label>
                        <input
                            type="date"
                            x-model="formInspeccion.validez_inspeccion"
                            :readonly="modoInspeccion === 'ver'"
                            class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30"
                        >
                    </div>
                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="cerrarInspeccion()" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 text-xs font-medium hover:bg-slate-800 transition">Cerrar</button>

                        {{-- Botón dar de baja directamente desde la inspección (no en modo ver) --}}
                        <button
                            type="button"
                            x-show="modoInspeccion !== 'ver'"
                            @click="darDeBajaDesdeInspeccion()"
                            :disabled="savingInspeccion || savingActaBaja"
                            class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold transition disabled:opacity-50 flex items-center gap-2"
                        >
                            <span>Dar de baja</span>
                        </button>

                        {{-- Botón eliminar: solo en modo ver y si es del mismo día --}}
                        <button
                            type="button"
                            x-show="modoInspeccion === 'ver' && puedeEliminarInspeccionActual()"
                            @click="eliminarInspeccion()"
                            class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-semibold transition disabled:opacity-50 flex items-center gap-2"
                        >
                            Eliminar inspección
                        </button>

                        {{-- Botón guardar: solo para nuevas inspecciones (no en modo ver) --}}
                        <button
                            type="button"
                            x-show="modoInspeccion !== 'ver'"
                            @click="guardarInspeccion()"
                            :disabled="savingInspeccion"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition disabled:opacity-50 flex items-center gap-2"
                        >
                            <span x-show="!savingInspeccion">Guardar inspección</span>
                            <span x-show="savingInspeccion">Guardando…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal ver todas las inspecciones en cards --}}
    <div x-show="modalVerTodasInspecciones" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 overflow-y-auto">
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-6xl my-8 border border-slate-700/60 flex flex-col max-h-[95vh]" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60 flex-shrink-0">
                <h3 class="text-lg font-semibold text-slate-200">Todas las inspecciones</h3>
                <button type="button" @click="modalVerTodasInspecciones = false" class="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700 rounded-lg transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 flex-1 min-h-0 overflow-y-auto">
                <div x-show="todasInspecciones.length === 0" class="py-10 text-center text-slate-500 text-sm">
                    No hay inspecciones registradas.
                </div>
                <div x-show="todasInspecciones.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="(insp, index) in todasInspecciones" :key="insp.id">
                        <div class="bg-slate-800/60 border border-slate-700/60 rounded-lg p-4 hover:border-emerald-500/50 transition-colors">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400">Equipo</span>
                                    <span class="text-sm font-semibold text-slate-200" x-text="insp.equipo_codigo"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400">Tipo</span>
                                    <span class="text-sm text-slate-300" x-text="insp.tipo_equipo"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400">Fecha inspección</span>
                                    <span class="text-sm text-slate-300" x-text="insp.fecha_inspeccion"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400">Validez</span>
                                    <span class="text-sm text-slate-300" x-text="insp.validez_inspeccion"></span>
                                </div>
                                <div class="pt-2 mt-2 border-t border-slate-700/60">
                                    <button
                                        type="button"
                                        @click="verInspeccionDesdeCard(insp)"
                                        class="w-full px-3 py-2 rounded-lg bg-emerald-600/90 hover:bg-emerald-500 text-white text-xs font-medium transition"
                                    >
                                        Ver detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal pregunta si el equipo es de baja --}}
    <div x-show="modalEquipoBaja" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80">
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 border border-slate-700/60" @click.stop>
            <h3 class="text-lg font-semibold text-slate-200 mb-4">¿El equipo es de baja?</h3>
            <p class="text-sm text-slate-400 mb-6">Selecciona si este equipo debe darse de baja después de la inspección.</p>
            <div class="flex gap-3 justify-end">
                <button type="button" @click="confirmarGuardarInspeccion(false)" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 text-sm font-medium hover:bg-slate-800 transition">No, continuar normal</button>
                <button type="button" @click="confirmarGuardarInspeccion(true)" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold transition">Sí, es de baja</button>
            </div>
        </div>
    </div>

    {{-- Modal ACTA DE BAJA --}}
    <div x-show="modalActaBaja" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 overflow-y-auto">
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-6xl my-8 border border-slate-700/60 flex flex-col max-h-[95vh]" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60 flex-shrink-0">
                <div>
                    <h3 class="text-lg font-semibold text-slate-200">ACTA DE BAJA DE ELEMENTOS</h3>
                    <p class="text-xs text-slate-400 mt-1" x-show="inspeccionEquipoCodigo" x-text="'Equipo: ' + inspeccionEquipoCodigo"></p>
                </div>
                <button type="button" @click="modalActaBaja = false" class="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700 rounded-lg transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-[2fr,1fr] gap-4 p-4 flex-1 min-h-0 overflow-hidden">
                <div class="border border-slate-700/60 rounded-lg bg-white min-h-[400px] overflow-hidden">
                    <template x-if="modalActaBaja && inspeccionEquipoId">
                        <iframe
                            x-ref="iframeActaBaja"
                            :src="'{{ route('equipos-baja.formato-html', ['equipo' => 'ID']) }}'.replace('ID', inspeccionEquipoId)"
                            class="w-full h-[70vh] lg:h-full rounded-lg border-0"
                            title="Formato ACTA DE BAJA editable">
                        </iframe>
                    </template>
                </div>
                <div class="flex flex-col gap-3 border border-slate-700/60 rounded-lg bg-slate-900/70 p-4">
                    <h4 class="text-sm font-semibold text-slate-200 mb-1">Datos del acta</h4>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Fecha de baja <span class="text-red-400">*</span></label>
                        <input type="date" x-model="formActaBaja.fecha_baja" required class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Resumen del motivo de baja <span class="text-red-400">*</span></label>
                        <textarea x-model="formActaBaja.resumen_baja" required rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30" placeholder="Describe brevemente el motivo por el cual se da de baja el equipo..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Responsable de inventario - Nombre</label>
                        <input type="text" x-model="formActaBaja.responsable_inventario_nombre" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Responsable de inventario - C.C.</label>
                        <input type="text" x-model="formActaBaja.responsable_inventario_cc" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Gerente administrativa - Nombre</label>
                        <input type="text" x-model="formActaBaja.gerente_administrativa_nombre" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Gerente administrativa - C.C.</label>
                        <input type="text" x-model="formActaBaja.gerente_administrativa_cc" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div class="pt-2 flex gap-3 justify-end">
                        <button type="button" @click="modalActaBaja = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 text-xs font-medium hover:bg-slate-800 transition">Cancelar</button>
                        <button type="button" @click="guardarActaBaja()" :disabled="savingActaBaja" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition disabled:opacity-50 flex items-center gap-2">
                            <span x-show="!savingActaBaja">Guardar acta</span>
                            <span x-show="savingActaBaja">Guardando…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial rápido de inspecciones --}}
    <div class="mt-10 bg-slate-900/70 backdrop-blur-md rounded-2xl border border-slate-800/60 shadow-xl">
        <div class="px-6 py-4 border-b border-slate-800/60 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-200">Inspecciones recientes</h3>
            <p class="text-xs text-slate-500">Últimas 50 inspecciones guardadas</p>
        </div>
        <div class="overflow-x-auto max-h-[260px]">
            <table class="w-full text-xs text-slate-300 min-w-[700px]">
                <thead class="bg-slate-800/80">
                    <tr>
                        <th class="py-2 px-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Fecha inspección</th>
                        <th class="py-2 px-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Equipo</th>
                        <th class="py-2 px-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Tipo</th>
                        <th class="py-2 px-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Validez inspección</th>
                        <th class="py-2 px-3 text-right font-semibold text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60" x-ref="tablaInspecciones">
                    @forelse($inspecciones as $insp)
                        @php
                            $inspData = [
                                'id' => $insp->id,
                                'equipo_id' => $insp->equipo_id,
                                'tipo_equipo_id' => $insp->tipo_equipo_id,
                                'fecha_inspeccion' => optional($insp->fecha_inspeccion)->format('Y-m-d'),
                                'validez_inspeccion' => optional($insp->validez_inspeccion)->format('Y-m-d'),
                                'equipo_codigo' => $insp->equipo->codigo ?? '',
                                'tipo_equipo' => $insp->tipoEquipo->nombre ?? '',
                            ];
                        @endphp
                        <tr>
                            <td class="py-2 px-3">{{ optional($insp->fecha_inspeccion)->format('Y-m-d') }}</td>
                            <td class="py-2 px-3">{{ $insp->equipo->codigo ?? '' }}</td>
                            <td class="py-2 px-3">{{ $insp->tipoEquipo->nombre ?? '' }}</td>
                            <td class="py-2 px-3">{{ optional($insp->validez_inspeccion)->format('Y-m-d') }}</td>
                            <td class="py-2 px-3 text-right">
                                <div class="inline-flex gap-1">
                                    <button
                                        type="button"
                                        @click='modalVerTodasInspecciones = true'
                                        class="px-2.5 py-1 rounded-lg border border-slate-600 text-slate-200 text-[11px] hover:bg-slate-800 transition"
                                    >
                                        Ver
                                    </button>
                                    <button
                                        type="button"
                                        @click='rehacerInspeccion(@json($inspData))'
                                        class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] hover:bg-emerald-500 transition"
                                    >
                                        Rehacer inspección
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 px-3 text-center text-slate-500">Aún no hay inspecciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function inspeccionarApp() {
    return {
        modalFormato: false,
        formatoTipoId: null,
        formatoTipoNombre: '',
        formatoReemplazar: false,
        modalSeleccionEquipo: false,
        tipoSeleccionId: null,
        tipoSeleccionNombre: '',
        equiposTipo: [],
        equiposLoading: false,
        modalInspeccion: false,
        modalVerTodasInspecciones: false,
        modalEquipoBaja: false,
        modalActaBaja: false,
        equipoEsBaja: false,
        hoy: '{{ \Carbon\Carbon::today()->format('Y-m-d') }}',
        inspeccionTipoId: null,
        inspeccionTipoNombre: '',
        inspeccionEquipoId: null,
        inspeccionEquipoCodigo: '',
        inspeccionEditId: null,
        modoInspeccion: 'nuevo', // nuevo | ver | editar
        formInspeccion: {
            fecha_inspeccion: new Date().toISOString().slice(0,10),
            validez_inspeccion: '',
        },
        formActaBaja: {
            fecha_baja: new Date().toISOString().slice(0,10),
            resumen_baja: '',
            responsable_inventario_nombre: '',
            responsable_inventario_cc: '',
            gerente_administrativa_nombre: '',
            gerente_administrativa_cc: '',
            asistentes: [],
            items_baja: [],
        },
        savingInspeccion: false,
        savingActaBaja: false,
        // Lista de todas las inspecciones ya preparada en el controlador
        todasInspecciones: @json($todasInspecciones),

        openModalFormato(id, nombre, reemplazar = false) {
            this.formatoTipoId = id;
            this.formatoTipoNombre = nombre;
            this.formatoReemplazar = !!reemplazar;
            this.modalFormato = true;
        },

        async openSeleccionarEquipo(id, nombre) {
            this.tipoSeleccionId = id;
            this.tipoSeleccionNombre = nombre;
            this.modalSeleccionEquipo = true;
            this.equiposTipo = [];
            this.equiposLoading = true;
            try {
                const res = await fetch('{{ route('inspeccionar.tipo-equipos', ['tipoEquipo' => 'ID']) }}'.replace('ID', id), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    this.equiposTipo = data.equipos || [];
                } else {
                    alert('No se pudieron cargar los equipos de este tipo.');
                }
            } catch (e) {
                console.error(e);
                alert('Error al cargar los equipos. Intenta de nuevo.');
            } finally {
                this.equiposLoading = false;
            }
        },

        abrirInspeccion(eq) {
            this.inspeccionTipoId = this.tipoSeleccionId;
            this.inspeccionTipoNombre = this.tipoSeleccionNombre;
            this.inspeccionEquipoId = eq.id;
            this.inspeccionEquipoCodigo = eq.codigo;
            this.inspeccionEditId = null;
            this.modoInspeccion = 'nuevo';
            this.formInspeccion.fecha_inspeccion = new Date().toISOString().slice(0,10);
            this.formInspeccion.validez_inspeccion = '';
            this.modalInspeccion = true;
        },

        cerrarInspeccion() {
            this.guardarContenidoInspeccionHtml();
            this.modalInspeccion = false;
        },

        openInspeccionFormatoModal() {
            if (!this.$refs.iframeInspeccion || !this.inspeccionEquipoId || !this.inspeccionTipoId) return;
            
            const iframe = this.$refs.iframeInspeccion;
            
            setTimeout(() => {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    
                    if (this.modoInspeccion === 'ver') {
                        let savedContent = null;
                        
                        // Buscar primero con el ID de la inspección si existe
                        if (this.inspeccionEditId) {
                            const key = `inspeccion_html_${this.inspeccionEquipoId}_${this.inspeccionTipoId}_${this.inspeccionEditId}`;
                            savedContent = localStorage.getItem(key);
                        }
                        
                        // Si no se encuentra, buscar con 'nuevo' como fallback
                        if (!savedContent) {
                            const keyNuevo = `inspeccion_html_${this.inspeccionEquipoId}_${this.inspeccionTipoId}_nuevo`;
                            savedContent = localStorage.getItem(keyNuevo);
                        }
                        
                        if (savedContent) {
                            iframeDoc.open();
                            iframeDoc.write(savedContent);
                            iframeDoc.close();

                            // En modo ver, desactivar edición en todos los elementos contenteditable
                            const editables = iframeDoc.querySelectorAll('[contenteditable=\"true\"]');
                            editables.forEach(el => {
                                el.setAttribute('contenteditable', 'false');
                            });
                        }
                    }
                } catch (e) {
                    console.error('Error al cargar contenido guardado:', e);
                }
            }, 500);
        },

        guardarContenidoInspeccionHtml() {
            if (!this.$refs.iframeInspeccion || !this.inspeccionEquipoId || !this.inspeccionTipoId) return;
            
            const iframe = this.$refs.iframeInspeccion;
            
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const htmlContent = iframeDoc.documentElement.outerHTML;
                
                // Guardar con el ID de la inspección si existe, o con 'nuevo' si es una nueva
                const key = this.inspeccionEditId 
                    ? `inspeccion_html_${this.inspeccionEquipoId}_${this.inspeccionTipoId}_${this.inspeccionEditId}`
                    : `inspeccion_html_${this.inspeccionEquipoId}_${this.inspeccionTipoId}_nuevo`;
                
                localStorage.setItem(key, htmlContent);
            } catch (e) {
                console.error('Error al guardar contenido:', e);
            }
        },

        verInspeccion(data) {
            this.inspeccionTipoId = data.tipo_equipo_id;
            this.inspeccionTipoNombre = data.tipo_equipo || '';
            this.inspeccionEquipoId = data.equipo_id;
            this.inspeccionEquipoCodigo = data.equipo_codigo || '';
            this.inspeccionEditId = data.id || null;
            this.formInspeccion.fecha_inspeccion = data.fecha_inspeccion || new Date().toISOString().slice(0,10);
            this.formInspeccion.validez_inspeccion = data.validez_inspeccion || '';
            this.modoInspeccion = 'ver';
            this.modalInspeccion = true;
        },

        verInspeccionDesdeCard(insp) {
            this.modalVerTodasInspecciones = false;
            this.verInspeccion(insp);
        },

        rehacerInspeccion(data) {
            // Crear una NUEVA inspección para el mismo equipo/tipo (no editar la anterior)
            this.inspeccionTipoId = data.tipo_equipo_id;
            this.inspeccionTipoNombre = data.tipo_equipo || '';
            this.inspeccionEquipoId = data.equipo_id;
            this.inspeccionEquipoCodigo = data.equipo_codigo || '';
            this.inspeccionEditId = null;
            this.modoInspeccion = 'nuevo';
            this.formInspeccion.fecha_inspeccion = new Date().toISOString().slice(0,10);
            this.formInspeccion.validez_inspeccion = '';
            this.modalInspeccion = true;
        },

        puedeEliminarInspeccionActual() {
            return !!this.inspeccionEditId && this.formInspeccion.fecha_inspeccion === this.hoy;
        },

        async eliminarInspeccion() {
            if (!this.puedeEliminarInspeccionActual()) {
                alert('Solo puedes eliminar la inspección el mismo día en que se realizó.');
                return;
            }

            if (!confirm('¿Seguro que deseas eliminar esta inspección? Esta acción no se puede deshacer.')) {
                return;
            }

            this.savingInspeccion = true;

            try {
                const res = await fetch(`{{ route('inspeccionar.eliminar', ['inspeccion' => 'ID']) }}`.replace('ID', this.inspeccionEditId), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await res.json();

                if (!data.success) {
                    alert(data.message || 'No se pudo eliminar la inspección.');
                    return;
                }

                alert(data.message);
                this.modalInspeccion = false;
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('Error al eliminar la inspección. Intenta de nuevo.');
            } finally {
                this.savingInspeccion = false;
            }
        },

        async guardarInspeccion() {
            if (!this.inspeccionEquipoId || !this.inspeccionTipoId || !this.formInspeccion.fecha_inspeccion) {
                alert('Completa al menos la fecha de inspección.');
                return;
            }

            // Preguntar si el equipo es de baja
            this.modalEquipoBaja = true;
        },

        // Acción rápida: dar de baja directamente desde la inspección
        async darDeBajaDesdeInspeccion() {
            if (!this.inspeccionEquipoId || !this.inspeccionTipoId || !this.formInspeccion.fecha_inspeccion) {
                alert('Completa la fecha de inspección antes de dar de baja.');
                return;
            }

            // Marcar como equipo de baja y saltar directamente a guardar con esBaja = true
            this.equipoEsBaja = true;
            await this.confirmarGuardarInspeccion(true);
        },

        async confirmarGuardarInspeccion(esBaja = false) {
            this.modalEquipoBaja = false;
            this.equipoEsBaja = esBaja;
            
            this.guardarContenidoInspeccionHtml();
            this.savingInspeccion = true;
            try {
                const res = await fetch('{{ route('inspeccionar.guardar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        id: this.inspeccionEditId,
                        equipo_id: this.inspeccionEquipoId,
                        tipo_equipo_id: this.inspeccionTipoId,
                        fecha_inspeccion: this.formInspeccion.fecha_inspeccion,
                        validez_inspeccion: this.formInspeccion.validez_inspeccion,
                    }),
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message || 'No se pudo guardar la inspección.');
                    this.savingInspeccion = false;
                    return;
                }

                // Si el equipo es de baja, abrir modal de ACTA DE BAJA
                if (this.equipoEsBaja) {
                    this.modalInspeccion = false;
                    this.modalActaBaja = true;
                    // Inicializar datos del equipo en el formulario de baja
                    this.formActaBaja.fecha_baja = this.formInspeccion.fecha_inspeccion;
                } else {
                    // Si no es de baja, recargar normalmente
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
                alert('Error al guardar la inspección. Intenta de nuevo.');
            } finally {
                this.savingInspeccion = false;
            }
        },

        async guardarActaBaja() {
            if (!this.inspeccionEquipoId || !this.formActaBaja.fecha_baja || !this.formActaBaja.resumen_baja) {
                alert('Completa la fecha de baja y el resumen del motivo.');
                return;
            }

            this.savingActaBaja = true;
            try {
                // Obtener el HTML del iframe del acta
                let actaHtml = '';
                if (this.$refs.iframeActaBaja) {
                    try {
                        const iframeDoc = this.$refs.iframeActaBaja.contentDocument || this.$refs.iframeActaBaja.contentWindow.document;
                        actaHtml = iframeDoc.documentElement.outerHTML;
                    } catch (e) {
                        console.error('Error al obtener HTML del acta:', e);
                    }
                }

                const res = await fetch('{{ route('equipos-baja.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        equipo_id: this.inspeccionEquipoId,
                        fecha_baja: this.formActaBaja.fecha_baja,
                        resumen_baja: this.formActaBaja.resumen_baja,
                        responsable_inventario_nombre: this.formActaBaja.responsable_inventario_nombre,
                        responsable_inventario_cc: this.formActaBaja.responsable_inventario_cc,
                        gerente_administrativa_nombre: this.formActaBaja.gerente_administrativa_nombre,
                        gerente_administrativa_cc: this.formActaBaja.gerente_administrativa_cc,
                        asistentes: this.formActaBaja.asistentes,
                        items_baja: this.formActaBaja.items_baja,
                        acta_html: actaHtml,
                    }),
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message || 'No se pudo guardar el acta de baja.');
                    return;
                }

                alert('Inspección y acta de baja guardadas correctamente.');
                this.modalActaBaja = false;
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('Error al guardar el acta de baja. Intenta de nuevo.');
            } finally {
                this.savingActaBaja = false;
            }
        },
    }
}
</script>

@endsection


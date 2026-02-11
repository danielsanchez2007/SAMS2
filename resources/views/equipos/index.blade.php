@extends('layouts.app')

@section('title', 'Equipos')

@section('content')
<div class="space-y-6" x-data="equiposApp()" x-init="init()">
    {{-- Pestañas --}}
    <div class="flex items-center gap-1 sm:gap-2 border-b border-slate-700 overflow-x-auto scrollbar-hide">
        <a href="{{ route('equipos.index') }}" 
           class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-base font-medium transition-colors whitespace-nowrap {{ ($tab ?? 'equipos') === 'equipos' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
            Equipos
        </a>
        <a href="{{ route('equipos.debaja') }}" 
           class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-base font-medium transition-colors whitespace-nowrap {{ ($tab ?? 'equipos') === 'debaja' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
            Equipos debaja
        </a>
        <a href="{{ route('equipos.material-didactico') }}" 
           class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-base font-medium transition-colors whitespace-nowrap {{ ($tab ?? 'equipos') === 'material' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
            Material didáctico
        </a>
        <a href="{{ route('equipos.auditoria') }}" 
           class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-base font-medium transition-colors whitespace-nowrap {{ ($tab ?? 'equipos') === 'auditoria' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
            Auditoría
        </a>
    </div>

    {{-- Botón Códigos disponibles (pequeño, arriba) --}}
    <div class="flex justify-end">
        <button type="button" 
                @click="mostrarCodigosDisponibles = true; cargarCodigosDisponibles()"
                class="text-xs px-3 py-1.5 rounded-lg border border-slate-600 text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition relative">
            📋 Códigos disponibles
            <template x-if="codigosDisponibles.length > 0">
                <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-green-500 text-white text-xs font-bold flex items-center justify-center" 
                      x-text="codigosDisponibles.length"></span>
            </template>
        </button>
    </div>

    {{-- Barra: filtro automático al escribir, por página, agregar, exportar --}}
    <div class="flex flex-col gap-4">
        {{-- Filtros de Empresa, Sede y Bodega --}}
        <form x-ref="filterForm" method="GET" action="
            @if(($tab ?? 'equipos') === 'equipos')
                {{ route('equipos.index') }}
            @elseif(($tab ?? 'equipos') === 'debaja')
                {{ route('equipos.debaja') }}
            @elseif(($tab ?? 'equipos') === 'material')
                {{ route('equipos.material-didactico') }}
            @else
                {{ route('equipos.auditoria') }}
            @endif
        " class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 p-3 sm:p-4 bg-slate-800/50 rounded-xl border border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 flex-1 min-w-0">
                <label class="text-xs sm:text-sm font-medium text-slate-400 whitespace-nowrap">Empresa:</label>
                <select name="empresa_id" x-model="filtros.empresa_id" @change="cargarSedes(); $refs.filterForm.submit()"
                        class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500/20 min-w-0">
                    <option value="">-- Todas --</option>
                    @foreach($empresas ?? [] as $empresa)
                        <option value="{{ $empresa->id }}" {{ ($empresaId ?? '') == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 flex-1 min-w-0">
                <label class="text-xs sm:text-sm font-medium text-slate-400 whitespace-nowrap">Sede:</label>
                <select name="sede_id" x-model="filtros.sede_id" @change="cargarBodegas(); $refs.filterForm.submit()"
                        class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500/20 min-w-0"
                        :disabled="!filtros.empresa_id">
                    <option value="">-- Todas --</option>
                    <template x-for="sede in sedesFiltradas" :key="sede.id">
                        <option :value="sede.id" x-text="sede.nombre"></option>
                    </template>
                </select>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 flex-1 min-w-0">
                <label class="text-xs sm:text-sm font-medium text-slate-400 whitespace-nowrap">Bodega:</label>
                <select name="bodega_id" x-model="filtros.bodega_id" @change="$refs.filterForm.submit()"
                        class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500/20 min-w-0"
                        :disabled="!filtros.sede_id">
                    <option value="">-- Todas --</option>
                    <template x-for="bodega in bodegasFiltradas" :key="bodega.id">
                        <option :value="bodega.id" x-text="bodega.nombre"></option>
                    </template>
                </select>
            </div>
            
            <button type="button" @click="limpiarFiltros()" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-slate-600 text-slate-300 text-xs sm:text-sm hover:bg-slate-700 transition whitespace-nowrap">
                Limpiar filtros
            </button>
        </form>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <form x-ref="searchForm" method="GET" action="
                @if(($tab ?? 'equipos') === 'equipos')
                    {{ route('equipos.index') }}
                @elseif(($tab ?? 'equipos') === 'debaja')
                    {{ route('equipos.debaja') }}
                @elseif(($tab ?? 'equipos') === 'material')
                    {{ route('equipos.material-didactico') }}
                @else
                    {{ route('equipos.auditoria') }}
                @endif
            " class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="empresa_id" :value="filtros.empresa_id">
                <input type="hidden" name="sede_id" :value="filtros.sede_id">
                <input type="hidden" name="bodega_id" :value="filtros.bodega_id">
                <label class="text-sm font-medium text-slate-400">Buscar:</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar código, descripción, marca..."
                           class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition w-full sm:w-72 md:w-80 text-sm"
                           @input.debounce.100ms="$refs.searchForm.submit()"
                           autocomplete="off">
                    @if($search)
                        @php
                            $clearRoute = ($tab ?? 'equipos') === 'equipos'
                                ? route('equipos.index', ['per_page' => $perPage, 'empresa_id' => $empresaId, 'sede_id' => $sedeId, 'bodega_id' => $bodegaId])
                                : (($tab ?? 'equipos') === 'debaja'
                                    ? route('equipos.debaja', ['per_page' => $perPage, 'empresa_id' => $empresaId, 'sede_id' => $sedeId, 'bodega_id' => $bodegaId])
                                    : (($tab ?? 'equipos') === 'material'
                                        ? route('equipos.material-didactico', ['per_page' => $perPage, 'empresa_id' => $empresaId, 'sede_id' => $sedeId, 'bodega_id' => $bodegaId])
                                        : route('equipos.auditoria', ['per_page' => $perPage, 'empresa_id' => $empresaId, 'sede_id' => $sedeId, 'bodega_id' => $bodegaId])));
                        @endphp
                        <a href="{{ $clearRoute }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar búsqueda">✕</a>
                    @endif
                </div>
                <span class="text-sm text-slate-500">Mostrar</span>
                <select name="per_page" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20"
                        @change="$refs.searchForm.submit()">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-sm text-slate-500">por página</span>
            </form>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <a href="{{ route('almacen.index') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl tema-btn-outline text-xs sm:text-sm font-medium transition-all">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="hidden sm:inline">Almacén</span>
            </a>
            <button type="button" @click="openModal()" class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-xs sm:text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Agregar Equipo</span>
                <span class="sm:hidden">Agregar</span>
            </button>
            <button type="button" @click="showExportModal = true; exportStep = 1; exportEmpresaId = ''; exportTipoItemId = ''; exportTipoEquipoIds = []; exportTipoItemsFiltrados = []; exportTipoEquiposFiltrados = []; exportPreviewRows = []; exportTiposNombres = []; exportTotal = 0" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl border border-slate-700 text-slate-300 text-xs sm:text-sm font-medium hover:bg-slate-800 hover:text-white transition-all">Exportar</button>
        </div>
    </div>

    {{-- Tabla de equipos --}}
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-xs sm:text-sm text-slate-300 min-w-[800px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs w-20">Foto</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Código</th>
                        @if(($tab ?? 'equipos') === 'debaja' || ($tab ?? 'equipos') === 'material' || ($tab ?? 'equipos') === 'auditoria')
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Código Original</th>
                        @endif
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Tipo</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Descripción</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Estado</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Sede</th>
                        <th class="text-right py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($equipos as $i => $e)
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-3 px-5">
                                @php
                                    // Usar URL directa precalculada (mucho más rápido)
                                    $imgSrc = $e->imagen_url ?? null;
                                    $placeholder = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2256%22 height=%2256%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23475569%22 stroke-width=%221.5%22%3E%3Crect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/%3E%3Ccircle cx=%228.5%22 cy=%228.5%22 r=%221.5%22/%3E%3Cpath d=%22m21 15-5-5L5 21%22/%3E%3C/svg%3E';
                                @endphp
                                <button type="button" data-equipo-id="{{ $e->id }}" @click="openImageModal({{ $e->id }}, {{ json_encode($imgSrc ?? $placeholder) }}, {{ json_encode($e->codigo) }})" class="block focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-lg overflow-hidden ring-offset-2 ring-offset-slate-900">
                                    <img 
                                        src="{{ $imgSrc ?? $placeholder }}" 
                                        alt="{{ $e->codigo }}" 
                                        class="w-14 h-14 object-cover rounded-lg border border-slate-700/60 bg-slate-800 cursor-pointer hover:opacity-90 hover:scale-[1.02] transition-all duration-200" 
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.onerror=null; this.src='{{ $placeholder }}';"
                                    >
                                </button>
                            </td>
                            <td class="py-4 px-5 font-mono font-medium text-slate-100">{{ $e->codigo }}</td>
                            @if(($tab ?? 'equipos') === 'debaja' || ($tab ?? 'equipos') === 'material' || ($tab ?? 'equipos') === 'auditoria')
                            <td class="py-4 px-5 font-mono text-sm text-slate-400">
                                @if(($tab ?? 'equipos') === 'debaja' && isset($e->codigo_original))
                                    {{ $e->codigo_original }}
                                @elseif(($tab ?? 'equipos') === 'material' && isset($e->codigo_original))
                                    {{ $e->codigo_original }}
                                @elseif(($tab ?? 'equipos') === 'auditoria')
                                    @php
                                        // Para auditoría, buscar el código original en CodigoDisponible
                                        // Buscar el código que fue liberado cuando se traspasó este equipo
                                        // Buscar por descripción similar y fecha cercana
                                        $codigoOriginalObj = \App\Models\CodigoDisponible::where('codigo', 'not like', 'AU%')
                                            ->where('codigo', 'not like', 'MD%')
                                            ->where('codigo', 'not like', 'DB%')
                                            ->where('origen_tipo', 'equipos')
                                            ->where('descripcion_original', $e->descripcion)
                                            ->where('utilizado', false)
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                                        
                                        // Si no se encuentra por descripción, buscar el más reciente no utilizado
                                        if (!$codigoOriginalObj) {
                                            $codigoOriginalObj = \App\Models\CodigoDisponible::where('codigo', 'not like', 'AU%')
                                                ->where('codigo', 'not like', 'MD%')
                                                ->where('codigo', 'not like', 'DB%')
                                                ->where('origen_tipo', 'equipos')
                                                ->where('utilizado', false)
                                                ->orderBy('created_at', 'desc')
                                                ->first();
                                        }
                                    @endphp
                                    {{ $codigoOriginalObj->codigo ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            @endif
                            <td class="py-4 px-5 text-slate-200">{{ $e->tipoItem?->nombre ?? '—' }}</td>
                            <td class="py-4 px-5 max-w-xs truncate text-slate-200">{{ $e->descripcion }}</td>
                            <td class="py-4 px-5 text-slate-300">{{ $e->estadoRemision?->nombre ?? '—' }}</td>
                            <td class="py-4 px-5 text-slate-300">{{ $e->sede?->nombre ?? '—' }}</td>
                            <td class="py-4 px-5 text-right">
                                <div class="grid grid-cols-3 gap-0.5 sm:gap-1 w-32 sm:w-36">
                                    <button type="button" @click="openVer({{ $e->id }}, '{{ addslashes($e->codigo) }}', '{{ addslashes(Str::limit($e->descripcion, 40)) }}')" class="p-1.5 sm:p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-colors" title="Ver imágenes y archivos">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    @if(($tab ?? 'equipos') === 'debaja' && !empty($e->ultimaActaBaja?->acta_pdf))
                                    <a href="{{ route('equipos-baja.download', $e->ultimaActaBaja) }}"
                                       target="_blank"
                                       class="p-1.5 sm:p-2.5 text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/30 rounded-lg transition-colors"
                                       title="Descargar acta de baja">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    <button type="button" @click="abrirModalAlmacenHojasVida({{ $e->id }}, '{{ addslashes($e->codigo) }}')" class="col-span-2 row-span-2 p-2.5 sm:p-3.5 text-cyan-400 hover:text-cyan-300 hover:bg-cyan-950/30 rounded-lg transition-colors relative flex items-center justify-center" title="Almacén de hojas de vida" style="min-height: 4rem;">
                                        <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </button>
                                    @php
                                        $tienePdfAsignado = $e->tipoEquipo && \App\Models\AlmacenArchivo::where('tipo_equipo_id', $e->tipo_equipo_id)
                                            ->whereHas('etiqueta', function($q) {
                                                $q->where('nombre', 'Hoja de Vida');
                                            })
                                            ->exists();
                                    @endphp
                                    @if($tienePdfAsignado)
                                    <button type="button" @click="abrirHojaVidaPlantillaModal({{ $e->id }}, '{{ $e->codigo }}')" class="p-1.5 sm:p-2.5 tema-gradient tema-gradient-hover text-white hover:opacity-90 rounded-lg transition-colors" title="Exportar PDF con información del equipo">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </button>
                                    @elseif($e->tipoEquipo && $e->tipoEquipo->tieneFormato())
                                    <button type="button"
                                            @click="openInspeccionFormatoModal({{ $e->tipo_equipo_id ?? 'null' }}, '{{ addslashes($e->tipoEquipo->nombre ?? '') }}', '{{ addslashes($e->codigo) }}')"
                                            class="p-1.5 sm:p-2.5 text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/30 rounded-lg transition-colors"
                                            title="Rellenar formato de inspección">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    @endif
                                    @if(($tab ?? 'equipos') === 'equipos')
                                    <button type="button" @click="abrirModalTraspaso({{ json_encode($e) }})" class="p-1.5 sm:p-2.5 text-purple-400 hover:text-purple-300 hover:bg-purple-950/30 rounded-lg transition-colors" title="Traspasar">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    </button>
                                    @endif
                                    <button type="button" @click="editEquipo({{ json_encode([
                                        'id' => $e->id,
                                        'empresa_id' => $e->empresa_id,
                                        'tipo_item_id' => $e->tipo_item_id,
                                        'tipo_equipo_id' => $e->tipo_equipo_id,
                                        'codigo' => $e->codigo,
                                        'nombre' => $e->nombre,
                                        'codigo_bloqueado' => $e->codigo_bloqueado,
                                        'estado_remision_id' => $e->estado_remision_id,
                                        'descripcion' => $e->descripcion,
                                        'marca' => $e->marca,
                                        'proveedor_id' => $e->proveedor_id,
                                        'fabricante_id' => $e->fabricante_id,
                                        'modelo' => $e->modelo,
                                        'sede_id' => $e->sede_id,
                                        'bodega_id' => $e->bodega_id,
                                        'vida_util' => $e->vida_util,
                                        'fecha_fabricacion' => $e->fecha_fabricacion ? $e->fecha_fabricacion->format('Y-m-d') : null,
                                        'fecha_uso' => $e->fecha_uso ? $e->fecha_uso->format('Y-m-d') : null,
                                        'fecha_compra' => $e->fecha_compra ? $e->fecha_compra->format('Y-m-d') : null,
                                        'tipo_registro' => $e->tipo_registro,
                                        'uso_item_id' => $e->uso_item_id,
                                        'es_kit' => $e->es_kit,
                                        'nombre_kit' => $e->nombre_kit,
                                        'componentes_kit' => $e->componentes_kit,
                                        'valor' => $e->valor,
                                        'numero_factura' => $e->numero_factura,
                                        'capacidades_resistencia' => $e->capacidades_resistencia,
                                        'lote' => $e->lote,
                                        'tiene_manual_fabricante' => $e->tiene_manual_fabricante,
                                        'tiene_certificacion' => $e->tiene_certificacion,
                                        'tiene_imagen_general' => $e->tiene_imagen_general,
                                        'tiene_imagen_etiqueta' => $e->tiene_imagen_etiqueta,
                                        'imagen_general' => $e->imagen_general,
                                        'imagen_etiqueta' => $e->imagen_etiqueta,
                                    ]) }})" class="p-1.5 sm:p-2.5 text-amber-400 hover:text-amber-300 hover:bg-amber-950/30 rounded-lg transition-colors" title="Editar">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    @if(($tab ?? 'equipos') !== 'debaja')
                                        <form action="{{ route('equipos.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este equipo?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 sm:p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ (($tab ?? 'equipos') === 'debaja' || ($tab ?? 'equipos') === 'material' || ($tab ?? 'equipos') === 'auditoria') ? '8' : '7' }}" class="py-12 px-5 text-center text-slate-500">No hay equipos. Escribe en el buscador o agrega uno nuevo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Paginación y resumen --}}
        @if(method_exists($equipos, 'hasPages') && ($equipos->hasPages() || $equipos->total() > 0))
        <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-t border-slate-800/60 bg-slate-900/50">
            <p class="text-sm text-slate-400">
                Mostrando {{ $equipos->firstItem() ?? 0 }} a {{ $equipos->lastItem() ?? 0 }} de {{ $equipos->total() }} equipos
            </p>
            <div class="flex items-center gap-2">
                @if ($equipos->onFirstPage())
                    <span class="px-3 py-2 rounded-lg border border-slate-700 text-sm text-slate-500 cursor-not-allowed">Anterior</span>
                @else
                    <a href="{{ $equipos->previousPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-800 transition-colors">Anterior</a>
                @endif
                <span class="text-sm text-slate-400 px-2">
                    Página {{ $equipos->currentPage() }} de {{ $equipos->lastPage() }}
                </span>
                @if ($equipos->hasMorePages())
                    <a href="{{ $equipos->nextPageUrl() }}" class="px-3 py-2 rounded-lg border-2 tema-pagination text-sm font-medium transition-colors">Siguiente</a>
                @else
                    <span class="px-3 py-2 rounded-lg border border-slate-700 text-sm text-slate-500 cursor-not-allowed">Siguiente</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Exportar: paso 1 = seleccionar tipo(s) de equipo, paso 2 = vista previa --}}
    <div x-show="showExportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
        <div class="tema-modal-opaco tema-modal-export tema-modal-content rounded-xl max-w-6xl w-full my-8 p-6 text-gray-900" @click.stop>
            {{-- Paso 1: Seleccionar Empresa --}}
            <div x-show="exportStep === 1">
                <h3 class="text-lg font-semibold mb-4" style="color: var(--tema-primary);">Paso 1: Seleccionar Empresa</h3>
                <p class="text-sm text-gray-600 mb-4">Selecciona la empresa para filtrar los equipos.</p>
                <div class="mb-6">
                    <select x-model="exportEmpresaId" @change="exportTipoItemId = ''; exportTipoEquipoIds = []; cargarTiposItemsExport()" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm">
                        <option value="">-- Seleccione una empresa --</option>
                        @foreach($empresas ?? [] as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showExportModal = false" class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium">Cancelar</button>
                    <button type="button" @click="if (exportEmpresaId) exportStep = 2" :disabled="!exportEmpresaId" class="flex-1 px-4 py-2.5 rounded-lg tema-gradient text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">Siguiente</button>
                </div>
            </div>
            
            {{-- Paso 2: Seleccionar Tipo de Ítem --}}
            <div x-show="exportStep === 2">
                <h3 class="text-lg font-semibold mb-4" style="color: var(--tema-primary);">Paso 2: Seleccionar Tipo de Ítem</h3>
                <p class="text-sm text-gray-600 mb-4">Selecciona el tipo de ítem para filtrar los equipos.</p>
                <div class="mb-6">
                    <select x-model="exportTipoItemId" @change="exportTipoEquipoIds = []; cargarTiposEquiposExport()" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm">
                        <option value="">-- Seleccione un tipo de ítem --</option>
                        <template x-for="ti in exportTipoItemsFiltrados" :key="ti.id">
                            <option :value="ti.id" x-text="ti.nombre"></option>
                        </template>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="exportStep = 1" class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium">← Volver</button>
                    <button type="button" @click="if (exportTipoItemId) exportStep = 3" :disabled="!exportTipoItemId" class="flex-1 px-4 py-2.5 rounded-lg tema-gradient text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">Siguiente</button>
                </div>
            </div>
            
            {{-- Paso 3: Seleccionar Tipo(s) de Equipo --}}
            <div x-show="exportStep === 3">
                <h3 class="text-lg font-semibold mb-4" style="color: var(--tema-primary);">Paso 3: Seleccionar Tipo(s) de Equipo</h3>
                <p class="text-sm text-gray-600 mb-4">Elige uno, varios o todos los <strong>tipos de equipo</strong>. Solo se exportarán los equipos de los tipos elegidos.</p>
                <div class="flex gap-2 mb-4">
                    <button type="button" @click="exportSelectAllTiposEquipos()" class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-100">Seleccionar todos</button>
                    <button type="button" @click="exportDeselectAllTiposEquipos()" class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-100">Ninguno</button>
                </div>
                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4 mb-6 flex flex-wrap gap-3">
                    <template x-for="te in exportTipoEquiposFiltrados" :key="te.id">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="exportTipoEquipoIds" :value="te.id" class="rounded border-gray-400 text-[var(--tema-primary)] focus:ring-[var(--tema-primary)]">
                            <span class="text-sm" x-text="te.nombre"></span>
                        </label>
                    </template>
                    <p x-show="exportTipoEquiposFiltrados.length === 0" class="text-sm text-gray-500 w-full text-center py-4">No hay tipos de equipo disponibles para el tipo de ítem seleccionado.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="exportStep = 2" class="px-4 py-2.5 rounded-lg border-2 text-sm font-medium">← Volver</button>
                    <button type="button" @click="exportShowPreview()" :disabled="exportTipoEquipoIds.length === 0" class="flex-1 px-4 py-2.5 rounded-lg tema-gradient text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">Ver vista previa</button>
                </div>
            </div>
            
            {{-- Paso 4: Vista previa y descargas --}}
            <div x-show="exportStep === 4">
                <h3 class="text-lg font-semibold mb-2" style="color: var(--tema-primary);">Vista previa — Programa de trazabilidad</h3>
                <p class="text-sm text-gray-600 mb-4">
                    <span x-text="'Tipos de ítem: ' + (exportTiposNombres.length ? exportTiposNombres.join(', ') : 'Todos los equipos')"></span>
                    · <span x-text="exportTotal + ' equipo(s)'"></span>
                </p>
                <p x-show="exportTiposNombres.length && exportTotal === 0" class="text-sm text-amber-700 mb-3 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200">
                    No hay equipos con los tipos de ítem seleccionados.
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
                            <template x-for="(eq, idx) in exportPreviewRows" :key="eq.codigo + idx">
                                <tr>
                                    <td class="border p-2" x-text="eq.numero"></td>
                                    <td class="border p-2" x-text="eq.codigo"></td>
                                    <td class="border p-2 max-w-[200px] truncate" :title="eq.equipo" x-text="eq.equipo"></td>
                                    <td class="border p-2" x-text="eq.tipo"></td>
                                    <td class="border p-2" x-text="eq.serial_modelo"></td>
                                    <td class="border p-2" x-text="eq.marca"></td>
                                    <td class="border p-2" x-text="eq.fabricante"></td>
                                    <td class="border p-2" x-text="eq.fecha_fabricacion"></td>
                                    <td class="border p-2" x-text="eq.fecha_compra"></td>
                                    <td class="border p-2" x-text="eq.uso"></td>
                                    <td class="border p-2" x-text="eq.capacidad_estructural"></td>
                                    <td class="border p-2" x-text="eq.ubicacion"></td>
                                    <td class="border p-2" x-text="eq.estado"></td>
                                </tr>
                            </template>
                            <tr x-show="exportPreviewRows.length === 0 && !exportPreviewLoading"><td colspan="12" class="border p-2 text-center text-gray-500">No hay equipos. Selecciona tipos o deja ninguno para exportar todos.</td></tr>
                            <tr x-show="exportPreviewLoading"><td colspan="12" class="border p-2 text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex gap-3">
                    <a :href="exportPdfUrl()" class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-lg tema-gradient text-white text-sm font-medium hover:opacity-90 transition">Descargar PDF</a>
                </div>
                <button type="button" @click="exportStep = 1; exportEmpresaId = ''; exportTipoItemId = ''; exportTipoEquipoIds = []; exportTipoItemsFiltrados = []; exportTipoEquiposFiltrados = []; exportPreviewRows = []; exportTiposNombres = []; exportTotal = 0" class="mt-4 w-full py-2 rounded-lg border-2 text-sm font-medium">Volver a seleccionar</button>
                <button type="button" @click="showExportModal = false" class="mt-2 w-full py-2 rounded-lg border border-gray-300 text-sm">Cerrar</button>
            </div>
        </div>
    </div>

    {{-- Lightbox: ver imagen ampliada + Reemplazar imagen --}}
    <div x-show="showImageModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80">
        <div class="relative max-w-4xl max-h-[90vh] w-full flex flex-col items-center justify-center" @click.stop>
            <div class="flex items-center justify-between w-full max-w-2xl mb-3">
                <span class="text-white font-medium" x-text="imageAlt ? 'Equipo ' + imageAlt : ''"></span>
                <div class="flex items-center gap-2">
                    <input type="file" x-ref="replaceImageInput" accept="image/*" class="hidden" @change="submitReplaceImage($event)">
                    <button type="button" @click="$refs.replaceImageInput.click()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition" x-show="imageEquipoId">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Reemplazar imagen
                    </button>
                    <button type="button" @click="showImageModal = false" class="p-2 text-white hover:text-slate-300 rounded">✕ Cerrar</button>
                </div>
            </div>
            <img :src="imageUrl" :alt="imageAlt" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
            <p x-show="replaceLoading" class="mt-2 text-indigo-300 text-sm">Subiendo imagen…</p>
        </div>
    </div>

    {{-- Modal Hoja de vida: llenado rápido de campos vacíos (plantillas antiguas) --}}
    <div x-show="showHojaVidaModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-lg w-full my-8 p-6 text-white border border-slate-700" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-200">Hoja de vida PDF</h3>
                <button type="button" @click="showHojaVidaModal = false" class="p-2 text-slate-400 hover:text-white rounded">✕ Cerrar</button>
            </div>
            <p class="text-sm text-slate-400 mb-4" x-show="hojaVidaCodigo" x-text="'Equipo: ' + (hojaVidaCodigo || '')"></p>
            <div x-show="hojaVidaLoading" class="py-6 text-center text-slate-400">Cargando datos…</div>
            <div x-show="!hojaVidaLoading">
                <template x-if="hojaVidaEmptyKeys && hojaVidaEmptyKeys.length > 0">
                    <div class="mb-4">
                        <p class="text-sm text-amber-300 mb-3">Hay campos vacíos. Puedes completarlos aquí para que aparezcan en el PDF (solo para esta descarga):</p>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <template x-for="key in hojaVidaEmptyKeys" :key="key">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-0.5" :text="hojaVidaLabels && hojaVidaLabels[key] ? hojaVidaLabels[key] : key"></label>
                                    <input type="text" :value="hojaVidaOverrides[key] || ''" @input="hojaVidaOverrides[key] = $event.target.value" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" :placeholder="'Valor para ' + (hojaVidaLabels && hojaVidaLabels[key] ? hojaVidaLabels[key] : key)">
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <p x-show="!hojaVidaLoading && hojaVidaEmptyKeys && hojaVidaEmptyKeys.length === 0" class="text-sm text-emerald-400 mb-4">Todos los campos están completos. Puedes generar el PDF directamente.</p>
                <div class="flex gap-3">
                    <button type="button" @click="generarHojaVidaPdf()" :disabled="hojaVidaGenerando" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium disabled:opacity-50 transition">
                        <span x-show="!hojaVidaGenerando">Generar PDF</span>
                        <span x-show="hojaVidaGenerando">Generando…</span>
                    </button>
                    <button type="button" @click="showHojaVidaModal = false" class="px-4 py-3 rounded-xl border border-slate-600 text-slate-300 text-sm hover:bg-slate-800 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Hoja de vida plantilla GGO3: campos adicionales + vista previa --}}
    <div x-show="showHojaVidaPlantillaModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-5xl w-full my-8 p-6 text-white border border-slate-700" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-200">Hoja de vida PDF (campos adicionales)</h3>
                <button type="button" @click="showHojaVidaPlantillaModal = false" class="p-2 text-slate-400 hover:text-white rounded">✕ Cerrar</button>
            </div>
            <p class="text-sm text-slate-400 mb-4" x-show="hojaVidaPlantillaCodigo" x-text="'Equipo: ' + (hojaVidaPlantillaCodigo || '')"></p>
            <div class="grid grid-cols-1 lg:grid-cols-[1fr,2fr] gap-6">
                <div class="space-y-3">
                    <p class="text-xs text-slate-400">Completa estos campos opcionales. Solo se usarán para esta descarga.</p>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Puntos de anclaje</label>
                            <input type="text" x-model="hojaVidaPlantillaCampos.puntos_anclaje" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Talla</label>
                                <input type="text" x-model="hojaVidaPlantillaCampos.talla" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Material</label>
                                <input type="text" x-model="hojaVidaPlantillaCampos.material" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Color(es)</label>
                            <input type="text" x-model="hojaVidaPlantillaCampos.color" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Cumple normas</label>
                            <input type="text" x-model="hojaVidaPlantillaCampos.cumple_normas" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" placeholder="Ej: EN 361, EN 362…">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Otras especificaciones técnicas</label>
                            <textarea x-model="hojaVidaPlantillaCampos.otras_especificaciones" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Descripción general de las inspecciones y mantenimiento preventivo</label>
                            <textarea x-model="hojaVidaPlantillaCampos.descripcion_inspecciones" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Fecha de la inspección</label>
                                <input type="text" x-model="hojaVidaPlantillaCampos.fecha_inspeccion" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" placeholder="dd/mm/aaaa">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Validez de la inspección</label>
                                <input type="text" x-model="hojaVidaPlantillaCampos.validez_inspeccion" class="w-full px-3 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" placeholder="Ej: dic-26">
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="generarHojaVidaPlantillaPreview()" :disabled="hojaVidaPlantillaGenerando" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium disabled:opacity-50 transition">
                            <span x-show="!hojaVidaPlantillaGenerando">Generar vista previa</span>
                            <span x-show="hojaVidaPlantillaGenerando">Generando…</span>
                        </button>
                        <button type="button" @click="guardarCambiosVistaPrevia()" :disabled="!hojaVidaPlantillaPreviewHtml" class="px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition disabled:opacity-40">
                            Guardar
                        </button>
                        <button type="button" @click="descargarHojaVidaPlantilla()" :disabled="!hojaVidaPlantillaPreviewHtml" class="px-4 py-3 rounded-xl border border-slate-600 text-slate-300 text-sm hover:bg-slate-800 transition disabled:opacity-40">
                            Descargar PDF
                        </button>
                    </div>
                </div>
                <div class="border border-slate-700 rounded-lg bg-white min-h-[600px] overflow-auto">
                    <div x-show="hojaVidaPlantillaPreviewHtml" x-ref="previewContainer" class="p-4" x-html="hojaVidaPlantillaPreviewHtml"></div>
                    <div x-show="!hojaVidaPlantillaPreviewHtml" class="flex items-center justify-center h-full min-h-[600px]">
                        <p class="text-sm text-slate-400 text-center px-4">Genera la vista previa para ver y editar aquí el PDF de la hoja de vida antes de descargarlo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Formato de inspección (por tipo de equipo) --}}
    <div x-show="showInspeccionFormatoModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-4xl my-8 border border-slate-700/60 flex flex-col max-h-[90vh]" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/60 flex-shrink-0">
                <h3 class="text-lg font-semibold text-slate-200">Rellenar formato de inspección</h3>
                <button type="button" @click="showInspeccionFormatoModal = false" class="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700 rounded-lg transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="px-5 pt-3 pb-1 text-sm text-slate-400" x-show="inspeccionTipoNombre" x-text="'Tipo de equipo: ' + inspeccionTipoNombre"></p>
            <p class="px-5 pb-3 text-sm text-slate-400" x-show="inspeccionEquipoCodigo" x-text="'Equipo: ' + inspeccionEquipoCodigo"></p>
            <div class="p-4 flex-1 min-h-0 overflow-hidden">
                <template x-if="showInspeccionFormatoModal && inspeccionTipoEquipoId">
                    <iframe :src="'{{ url('/tipo-equipos') }}/' + inspeccionTipoEquipoId + '/formato'" class="w-full h-[70vh] min-h-[400px] rounded-lg border border-slate-700/60 bg-white" title="Formato de inspección"></iframe>
                </template>
            </div>
        </div>
    </div>

    {{-- Modal Almacén de Hojas de Vida --}}
    <div x-show="showModalAlmacenHojasVida" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black" @click.self="showModalAlmacenHojasVida = false">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-sm w-full p-6 text-white border border-slate-700" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-cyan-300">Almacén de Hojas de Vida</h3>
                <button type="button" @click="showModalAlmacenHojasVida = false" class="p-1.5 text-slate-400 hover:text-white rounded transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-400 mb-4" x-text="'Equipo: ' + (almacenHojasVidaCodigo || '')"></p>
            <div class="space-y-2">
                <button type="button" @click="verAlmacenHojasVida()" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver</span>
                </button>
                <button type="button" @click="exportarHojaVidaAlmacen()" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Exportar</span>
                </button>
                <button type="button" @click="abrirModalImportarPdf()" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Importar</span>
                </button>
                <button type="button" @click="verPdfsAlmacen()" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>PDFs</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Todos los PDFs --}}
    <div x-show="showModalTodosPdfs" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/90 overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full my-8 p-6 text-white border-2" style="border-color: var(--tema-primary);" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold" style="color: var(--tema-primary-text);">Todos los PDFs</h3>
                <button type="button" @click="showModalTodosPdfs = false; pdfSeleccionado = null; tipoEquipoSeleccionado = '';" class="p-2 text-slate-200 hover:text-white rounded transition hover:bg-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div x-show="pdfsCargando" class="py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2" style="border-color: var(--tema-primary);"></div>
                <p class="mt-4 text-white font-medium">Cargando PDFs...</p>
            </div>
            
            <div x-show="!pdfsCargando" class="space-y-6">
                {{-- PDFs Disponibles para Asignar --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-white mb-4" style="color: var(--tema-primary-text);">PDFs Disponibles para Asignar</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                        <template x-for="pdf in pdfsEquipo.filter(p => !p.tipo_equipo && !p.tipo_equipo_id)" :key="pdf.id">
                            <div class="border-2 rounded-lg p-4 transition bg-slate-800/50 relative"
                                 :style="pdfSeleccionado == pdf.id ? 'border-color: var(--tema-primary); background-color: var(--tema-primary-light);' : 'border-color: #475569;'">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1 cursor-pointer" @click="pdfSeleccionado = pdf.id">
                                        <h5 class="font-semibold text-white" x-text="pdf.nombre"></h5>
                                        <p class="text-xs text-slate-200 mt-1">Sin asignar</p>
                                    </div>
                                    <div class="ml-3 flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full border-2 cursor-pointer"
                                             :style="pdfSeleccionado == pdf.id ? 'background-color: var(--tema-primary); border-color: transparent;' : 'border-color: #64748b;'"
                                             @click.stop="pdfSeleccionado = pdf.id"></div>
                                        <button type="button" 
                                                @click.stop="eliminarPdf(pdf.id, pdf.nombre)"
                                                class="p-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-white transition shadow"
                                                title="Eliminar PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-3 bg-slate-900 rounded p-2 cursor-pointer" style="height: 200px; position: relative; overflow: hidden; z-index: 1;" @click="pdfSeleccionado = pdf.id">
                                    <iframe :src="pdf.ruta_url" class="w-full h-full rounded border border-slate-600" frameborder="0" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;"></iframe>
                                </div>
                            </div>
                        </template>
                        <div x-show="pdfsEquipo.filter(p => !p.tipo_equipo && !p.tipo_equipo_id).length === 0" class="col-span-3 py-8 text-center text-white">
                            No hay PDFs disponibles para asignar.
                        </div>
                    </div>
                </div>
                
                {{-- Selector de Tipo de Equipo --}}
                <div x-show="pdfSeleccionado" class="border-t pt-6 mt-6" style="border-color: var(--tema-primary);">
                    <label class="block text-sm font-semibold text-white mb-2">
                        Asignar a Tipo de Equipo <span class="text-red-400">*</span>
                    </label>
                    <select x-model="tipoEquipoSeleccionado" 
                            class="w-full px-4 py-3 rounded-lg border-2 bg-slate-800 text-white text-sm font-medium transition"
                            style="border-color: #475569;"
                            onfocus="this.style.borderColor='var(--tema-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--tema-ring), 0.3)';"
                            onblur="this.style.borderColor='#475569'; this.style.boxShadow='none';">
                        <option value="" class="bg-slate-800 text-white">— Seleccionar tipo de equipo —</option>
                        @foreach(\App\Models\TipoEquipo::orderBy('nombre')->get() as $tipo)
                            <option value="{{ $tipo->id }}" class="bg-slate-800 text-white">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Tabla de PDFs Asignados --}}
                <div x-show="pdfsEquipo.filter(p => p.tipo_equipo).length > 0" class="mt-6">
                    <h4 class="text-lg font-semibold text-white mb-4" style="color: var(--tema-primary-text);">PDFs Asignados</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-800">
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Tipo de Equipo</th>
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Nombre del PDF</th>
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Vista Previa</th>
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="pdf in pdfsEquipo.filter(p => p.tipo_equipo)" :key="pdf.id">
                                    <tr class="hover:bg-slate-800/70">
                                        <td class="border border-slate-600 px-4 py-2 text-white font-semibold" x-text="pdf.tipo_equipo || '—'"></td>
                                        <td class="border border-slate-600 px-4 py-2 text-white" x-text="pdf.nombre"></td>
                                        <td class="border border-slate-600 px-4 py-2">
                                            <div class="bg-slate-900 rounded p-2" style="width: 150px; height: 100px; position: relative; overflow: hidden;">
                                                <iframe :src="pdf.ruta_url" class="w-full h-full rounded border border-slate-600" frameborder="0" style="position: absolute; top: 0; left: 0; width: 200%; height: 200%; transform: scale(0.5); transform-origin: top left; pointer-events: none;"></iframe>
                                            </div>
                                        </td>
                                        <td class="border border-slate-600 px-4 py-2">
                                            <button type="button" 
                                                    @click="iniciarReemplazoFormato(pdf.id, pdf.tipo_equipo_id, pdf.tipo_equipo)"
                                                    class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-medium transition shadow">
                                                Reemplazar
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Botones de acción --}}
                <div class="flex gap-3 pt-4 border-t mt-6" style="border-color: var(--tema-primary);">
                    <button type="button" 
                            @click="asignarPdfATipoEquipo()"
                            :disabled="!pdfSeleccionado || !tipoEquipoSeleccionado"
                            class="flex-1 px-6 py-3 rounded-lg tema-gradient tema-gradient-hover text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg">
                        Asignar PDF Seleccionado
                    </button>
                    <button type="button" 
                            @click="showModalTodosPdfs = false; pdfSeleccionado = null; tipoEquipoSeleccionado = '';"
                            class="px-6 py-3 rounded-lg border-2 text-white text-sm font-semibold transition shadow"
                            style="border-color: var(--tema-primary); color: var(--tema-primary-text);"
                            onmouseover="this.style.backgroundColor='var(--tema-primary-light)';"
                            onmouseout="this.style.backgroundColor='transparent';">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ver PDFs Asignados --}}
    <div x-show="showModalVerPdfsAsignados" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/90 overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full my-8 p-6 text-white border-2" style="border-color: var(--tema-primary);" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold" style="color: var(--tema-primary-text);">PDFs Asignados por Tipo de Equipo</h3>
                <button type="button" @click="showModalVerPdfsAsignados = false" class="p-2 text-slate-200 hover:text-white rounded transition hover:bg-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div x-show="pdfsAsignadosCargando" class="py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2" style="border-color: var(--tema-primary);"></div>
                <p class="mt-4 text-white font-medium">Cargando PDFs asignados...</p>
            </div>
            
            <div x-show="!pdfsAsignadosCargando" class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-800">
                            <th class="border border-slate-600 px-4 py-3 text-left text-sm font-semibold text-white">Tipo de Equipo</th>
                            <th class="border border-slate-600 px-4 py-3 text-left text-sm font-semibold text-white">Nombre del PDF</th>
                            <th class="border border-slate-600 px-4 py-3 text-left text-sm font-semibold text-white">Vista Previa</th>
                            <th class="border border-slate-600 px-4 py-3 text-left text-sm font-semibold text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="pdf in pdfsAsignados" :key="pdf.id">
                            <tr class="hover:bg-slate-800/70">
                                <td class="border border-slate-600 px-4 py-3 text-white font-semibold" x-text="pdf.tipo_equipo || '—'"></td>
                                <td class="border border-slate-600 px-4 py-3 text-white" x-text="pdf.nombre"></td>
                                <td class="border border-slate-600 px-4 py-3">
                                    <div class="bg-slate-900 rounded p-2" style="width: 150px; height: 100px; position: relative; overflow: hidden;">
                                        <iframe :src="pdf.ruta_url" class="w-full h-full rounded border border-slate-600" frameborder="0" style="position: absolute; top: 0; left: 0; width: 200%; height: 200%; transform: scale(0.5); transform-origin: top left; pointer-events: none;"></iframe>
                                    </div>
                                </td>
                                <td class="border border-slate-600 px-4 py-3">
                                    <button type="button" 
                                            @click="iniciarReemplazoFormato(pdf.id, pdf.tipo_equipo_id, pdf.tipo_equipo)"
                                            class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-medium transition shadow">
                                        Reemplazar Formato
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="pdfsAsignados.length === 0">
                            <td colspan="4" class="border border-slate-600 px-4 py-8 text-center text-white">
                                No hay PDFs asignados a ningún tipo de equipo.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Reemplazar Formato --}}
    <div x-show="showModalReemplazarFormato" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/90 overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full my-8 p-6 text-white border-2" style="border-color: var(--tema-primary);" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold" style="color: var(--tema-primary-text);">Reemplazar Formato</h3>
                <button type="button" @click="cancelarReemplazoFormato()" class="p-2 text-slate-200 hover:text-white rounded transition hover:bg-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-white mb-6 font-semibold" x-text="'Reemplazando formato para: ' + (tipoEquipoReemplazoFormato || '')"></p>
            
            <div x-show="pdfsCargando" class="py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2" style="border-color: var(--tema-primary);"></div>
                <p class="mt-4 text-white font-medium">Cargando PDFs...</p>
            </div>
            
            <div x-show="!pdfsCargando" class="space-y-6">
                <h4 class="text-lg font-semibold text-white mb-4" style="color: var(--tema-primary-text);">Selecciona un PDF para reemplazar</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                    <template x-for="pdf in pdfsEquipo.filter(p => p.id != pdfActualReemplazoFormato && !p.tipo_equipo && !p.tipo_equipo_id)" :key="pdf.id">
                        <div class="border-2 rounded-lg p-4 cursor-pointer transition bg-slate-800/50"
                             :style="pdfSeleccionadoReemplazo == pdf.id ? 'border-color: var(--tema-primary); background-color: var(--tema-primary-light);' : 'border-color: #475569;'"
                             @click="pdfSeleccionadoReemplazo = pdf.id"
                             @mouseenter="if(pdfSeleccionadoReemplazo != pdf.id) $el.style.borderColor='#64748b';"
                             @mouseleave="if(pdfSeleccionadoReemplazo != pdf.id) $el.style.borderColor='#475569';">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h5 class="font-semibold text-white" x-text="pdf.nombre"></h5>
                                    <p class="text-xs text-slate-200 mt-1">Sin asignar</p>
                                </div>
                                <div class="ml-3">
                                    <div class="w-3 h-3 rounded-full border-2"
                                         :style="pdfSeleccionadoReemplazo == pdf.id ? 'background-color: var(--tema-primary); border-color: transparent;' : 'border-color: #64748b;'"></div>
                                </div>
                            </div>
                            <div class="mt-3 bg-slate-900 rounded p-2" style="height: 200px; position: relative; overflow: hidden; z-index: 1;">
                                <iframe :src="pdf.ruta_url" class="w-full h-full rounded border border-slate-600" frameborder="0" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;"></iframe>
                            </div>
                        </div>
                    </template>
                    <div x-show="pdfsEquipo.filter(p => p.id != pdfActualReemplazoFormato && !p.tipo_equipo && !p.tipo_equipo_id).length === 0" class="col-span-3 py-8 text-center text-white">
                        No hay PDFs disponibles para reemplazar.
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4 border-t mt-6" style="border-color: var(--tema-primary);">
                    <button type="button" 
                            @click="reemplazarFormato()"
                            :disabled="!pdfSeleccionadoReemplazo"
                            class="flex-1 px-6 py-3 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg">
                        Reemplazar Formato
                    </button>
                    <button type="button" 
                            @click="cancelarReemplazoFormato()"
                            class="px-6 py-3 rounded-lg border-2 text-white text-sm font-semibold transition shadow"
                            style="border-color: var(--tema-primary); color: var(--tema-primary-text);"
                            onmouseover="this.style.backgroundColor='var(--tema-primary-light)';"
                            onmouseout="this.style.backgroundColor='transparent';">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Importar PDF --}}
    <div x-show="showModalImportarPdf" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-5xl w-full my-8 p-6 text-white border border-slate-700" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold text-cyan-300">Importar PDF - Hoja de Vida</h3>
                <button type="button" @click="cerrarModalImportarPdf()" class="p-2 text-slate-400 hover:text-white rounded transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-400 mb-6" x-text="'Equipo: ' + (almacenHojasVidaCodigo || '')"></p>
            
            <form @submit.prevent="subirPdfImportar()" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Nombre del archivo <span class="text-red-400">*</span>
                    </label>
                    <input type="text" x-model="importarPdfForm.nombre" required
                           class="w-full px-4 py-3 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500"
                           placeholder="Ej: Hoja de vida - Mantenimiento 2024">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Seleccionar PDF <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" @change="cargarPdfVistaPrevia($event)" accept=".pdf,application/pdf" required
                               class="w-full px-4 py-3 rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500">
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Tamaño máximo: 20MB. Solo archivos PDF.</p>
                </div>
                
                {{-- Vista previa del PDF --}}
                <div x-show="importarPdfVistaPrevia" class="mt-6">
                    <h4 class="text-lg font-semibold text-slate-300 mb-3">Vista previa</h4>
                    <div class="border-2 border-slate-700 rounded-lg bg-slate-800 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-slate-400" x-text="importarPdfNombreArchivo || 'archivo.pdf'"></p>
                            <button type="button" @click="limpiarVistaPreviaPdf()" class="text-red-400 hover:text-red-300 text-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="bg-white rounded-lg p-2" style="min-height: 500px;">
                            <iframe :src="importarPdfVistaPrevia" class="w-full h-full min-h-[500px] rounded border border-slate-300" frameborder="0"></iframe>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="importarPdfSubiendo || !importarPdfForm.nombre || !importarPdfForm.archivo"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <span x-show="!importarPdfSubiendo">Guardar PDF</span>
                        <span x-show="importarPdfSubiendo">Guardando...</span>
                        <svg x-show="importarPdfSubiendo" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    <button type="button" @click="cerrarModalImportarPdf()" 
                            class="px-6 py-3 rounded-lg border border-slate-600 text-slate-300 text-sm hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Asignar PDFs a Tipo de Equipo --}}
    <div x-show="showModalAsignarPdf" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/90 overflow-y-auto">
        <div class="bg-slate-900 rounded-xl shadow-2xl max-w-6xl w-full my-8 p-6 text-white border-2" style="border-color: var(--tema-primary);" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold" style="color: var(--tema-primary-text);">Asignar PDFs a Tipo de Equipo</h3>
                <button type="button" @click="showModalAsignarPdf = false; pdfSeleccionado = null; tipoEquipoSeleccionado = '';" class="p-2 text-slate-200 hover:text-white rounded transition hover:bg-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-white mb-6 font-semibold" x-text="'Equipo: ' + (almacenHojasVidaCodigo || '')"></p>
            
            <div x-show="pdfsCargando" class="py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2" style="border-color: var(--tema-primary);"></div>
                <p class="mt-4 text-white font-medium">Cargando PDFs...</p>
            </div>
            
            <div x-show="!pdfsCargando" class="space-y-6">
                {{-- Tabla de Equipos Asignados --}}
                <div x-show="equiposAsignados.length > 0">
                    <h4 class="text-lg font-semibold text-white mb-4" style="color: var(--tema-primary-text);" x-text="'Equipos con tipo: ' + (tipoEquipoAsignado || '')"></h4>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-800">
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Foto</th>
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Código</th>
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Descripción</th>
                                    <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="equipo in equiposAsignados" :key="equipo.id">
                                    <tr class="hover:bg-slate-800/70">
                                        <td class="border border-slate-600 px-4 py-2">
                                            <img :src="equipo.imagen_url || '{{ asset('img/Icono.png') }}'" 
                                                 :alt="equipo.codigo" 
                                                 class="w-16 h-16 object-cover rounded">
                                        </td>
                                        <td class="border border-slate-600 px-4 py-2 text-white font-semibold" x-text="equipo.codigo"></td>
                                        <td class="border border-slate-600 px-4 py-2 text-white" x-text="equipo.descripcion || '—'"></td>
                                        <td class="border border-slate-600 px-4 py-2">
                                            <button type="button" 
                                                    @click="abrirHojaVidaPlantillaModal(equipo.id, equipo.codigo)"
                                                    class="px-4 py-2 rounded-lg tema-gradient tema-gradient-hover text-white text-sm font-medium transition shadow-lg">
                                                Exportar PDF
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" 
                            @click="equiposAsignados = []; tipoEquipoAsignado = null;"
                            class="mt-4 px-6 py-3 rounded-lg border-2 text-white text-sm font-medium transition"
                            style="border-color: var(--tema-primary); color: var(--tema-primary-text);"
                            onmouseover="this.style.backgroundColor='var(--tema-primary-light)';"
                            onmouseout="this.style.backgroundColor='transparent';">
                        Volver a PDFs
                    </button>
                </div>
                
                {{-- Lista de PDFs (sin asignar y asignados) --}}
                <div x-show="equiposAsignados.length === 0">
                    {{-- PDFs Disponibles para Asignar (ARRIBA) --}}
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-white mb-4" style="color: var(--tema-primary-text);">
                            <span x-show="!reasignandoPdf">PDFs Disponibles para Asignar</span>
                            <span x-show="reasignandoPdf">Selecciona un PDF para Reasignar</span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                            <template x-for="pdf in pdfsEquipo.filter(p => reasignandoPdf ? p.id != pdfActualReasignacion : (!p.tipo_equipo && !p.tipo_equipo_id))" :key="pdf.id">
                                <div class="border-2 rounded-lg p-4 cursor-pointer transition bg-slate-800/50"
                                     :style="pdfSeleccionado == pdf.id ? 'border-color: var(--tema-primary); background-color: var(--tema-primary-light);' : 'border-color: #475569;'"
                                     @click="pdfSeleccionado = pdf.id"
                                     @mouseenter="if(pdfSeleccionado != pdf.id) $el.style.borderColor='#64748b';"
                                     @mouseleave="if(pdfSeleccionado != pdf.id) $el.style.borderColor='#475569';">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <h5 class="font-semibold text-white" x-text="pdf.nombre"></h5>
                                            <p class="text-xs text-slate-200 mt-1">Sin asignar</p>
                                        </div>
                                        <div class="ml-3">
                                            <div class="w-3 h-3 rounded-full border-2"
                                                 :style="pdfSeleccionado == pdf.id ? 'background-color: var(--tema-primary); border-color: transparent;' : 'border-color: #64748b;'"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 bg-slate-900 rounded p-2" style="height: 200px; position: relative; overflow: hidden; z-index: 1;">
                                        <iframe :src="pdf.ruta_url" class="w-full h-full rounded border border-slate-600" frameborder="0" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;"></iframe>
                                    </div>
                                </div>
                            </template>
                            <div x-show="pdfsEquipo.filter(p => reasignandoPdf ? p.id != pdfActualReasignacion : (!p.tipo_equipo && !p.tipo_equipo_id)).length === 0" class="col-span-3 py-8 text-center text-white">
                                <span x-show="!reasignandoPdf">No hay PDFs disponibles para asignar.</span>
                                <span x-show="reasignandoPdf">No hay otros PDFs disponibles para reasignar.</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Tabla de PDFs Asignados (ABAJO) --}}
                    <div x-show="pdfsEquipo.filter(p => p.tipo_equipo).length > 0" class="mt-6">
                        <h4 class="text-lg font-semibold text-white mb-4" style="color: var(--tema-primary-text);">PDFs Asignados</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-slate-800">
                                        <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Nombre</th>
                                        <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Tipo de Equipo</th>
                                        <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Vista Previa</th>
                                        <th class="border border-slate-600 px-4 py-2 text-left text-sm font-semibold text-white">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="pdf in pdfsEquipo.filter(p => p.tipo_equipo)" :key="pdf.id">
                                        <tr class="hover:bg-slate-800/70">
                                            <td class="border border-slate-600 px-4 py-2 text-white font-semibold" x-text="pdf.nombre"></td>
                                            <td class="border border-slate-600 px-4 py-2 text-white" x-text="pdf.tipo_equipo || '—'"></td>
                                            <td class="border border-slate-600 px-4 py-2">
                                                <div class="bg-slate-900 rounded p-2" style="width: 150px; height: 100px; position: relative; overflow: hidden;">
                                                    <iframe :src="pdf.ruta_url" class="w-full h-full rounded border border-slate-600" frameborder="0" style="position: absolute; top: 0; left: 0; width: 200%; height: 200%; transform: scale(0.5); transform-origin: top left; pointer-events: none;"></iframe>
                                                </div>
                                            </td>
                                            <td class="border border-slate-600 px-4 py-2">
                                                <div class="flex gap-2">
                                                    <button type="button" 
                                                            @click="iniciarReasignacionPdf(pdf.id, pdf.tipo_equipo_id, pdf.tipo_equipo)"
                                                            class="px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-xs font-medium transition shadow"
                                                            title="Reasignar este PDF">
                                                        Reasignar
                                                    </button>
                                                    <button type="button" 
                                                            @click="desasignarPdf(pdf.id)"
                                                            class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-medium transition shadow"
                                                            title="Desasignar este PDF">
                                                        Desasignar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- Modo Reasignación --}}
                    <div x-show="reasignandoPdf" class="border-t pt-6 mt-6" style="border-color: var(--tema-primary);">
                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 mb-4">
                            <p class="text-sm text-orange-200 mb-2 font-semibold">
                                <strong>Reasignando PDF asignado a:</strong> <span x-text="nombreTipoEquipoReasignacion" class="text-orange-100 font-bold"></span>
                            </p>
                            <p class="text-xs text-white">Selecciona un PDF de la lista para reasignarlo a este tipo de equipo.</p>
                        </div>
                    </div>
                    
                    {{-- Selector de Tipo de Equipo (solo si no está en modo reasignación) --}}
                    <div x-show="pdfSeleccionado && !reasignandoPdf" class="border-t pt-6 mt-6" style="border-color: var(--tema-primary);">
                        <label class="block text-sm font-semibold text-white mb-2">
                            Asignar a Tipo de Equipo <span class="text-red-400">*</span>
                        </label>
                        <select x-model="tipoEquipoSeleccionado" 
                                class="w-full px-4 py-3 rounded-lg border-2 bg-slate-800 text-white text-sm font-medium transition"
                                style="border-color: #475569;"
                                onfocus="this.style.borderColor='var(--tema-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--tema-ring), 0.3)';"
                                onblur="this.style.borderColor='#475569'; this.style.boxShadow='none';">
                            <option value="" class="bg-slate-800 text-white">— Seleccionar tipo de equipo —</option>
                            @foreach(\App\Models\TipoEquipo::orderBy('nombre')->get() as $tipo)
                                <option value="{{ $tipo->id }}" class="bg-slate-800 text-white">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Botones de acción --}}
                    <div class="flex gap-3 pt-4 border-t mt-6" style="border-color: var(--tema-primary);">
                        <template x-if="reasignandoPdf">
                            <button type="button" 
                                    @click="reasignarPdf()"
                                    :disabled="!pdfSeleccionado || pdfSeleccionado == pdfActualReasignacion"
                                    class="flex-1 px-6 py-3 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg">
                                Reasignar PDF
                            </button>
                        </template>
                        <template x-if="!reasignandoPdf">
                            <button type="button" 
                                    @click="asignarPdfATipoEquipo()"
                                    :disabled="!pdfSeleccionado || !tipoEquipoSeleccionado"
                                    class="flex-1 px-6 py-3 rounded-lg tema-gradient tema-gradient-hover text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg">
                                Asignar PDF Seleccionado
                            </button>
                        </template>
                        <button type="button" 
                                @click="reasignandoPdf ? cancelarReasignacion() : (showModalAsignarPdf = false; pdfSeleccionado = null; tipoEquipoSeleccionado = ''; equiposAsignados = []; tipoEquipoAsignado = null;)"
                                class="px-6 py-3 rounded-lg border-2 text-white text-sm font-semibold transition shadow"
                                style="border-color: var(--tema-primary); color: var(--tema-primary-text);"
                                onmouseover="this.style.backgroundColor='var(--tema-primary-light)';"
                                onmouseout="this.style.backgroundColor='transparent';">
                            <span x-show="reasignandoPdf">Cancelar</span>
                            <span x-show="!reasignandoPdf">Cerrar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ver: imágenes y archivos del equipo (Almacén) --}}
    <div x-show="showVerModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div class="bg-[#0f172a] dark:bg-slate-900 rounded-xl shadow-2xl max-w-4xl w-full my-8 p-6 text-white border border-slate-700" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-indigo-300" x-text="'Imágenes y archivos — ' + (verEquipo ? verEquipo.codigo : '')"></h3>
                <button type="button" @click="showVerModal = false" class="p-2 text-slate-400 hover:text-white rounded">✕ Cerrar</button>
            </div>
            <p class="text-sm text-slate-400 mb-4" x-show="verEquipo" x-text="verEquipo ? verEquipo.descripcion : ''"></p>
            <div x-show="verLoading" class="py-8 text-center text-slate-400">Cargando…</div>
            <div x-show="!verLoading">
                {{-- Imágenes del equipo --}}
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-300 mb-2">Imágenes del Equipo</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" x-show="verImagenes.filter(img => img.tipo === 'equipo').length">
                        <template x-for="img in verImagenes.filter(img => img.tipo === 'equipo')" :key="img.id">
                            <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-2">
                                <img :src="img.ruta" :alt="img.etiqueta || ''" class="w-full h-24 object-cover rounded mb-2 cursor-pointer" @click="imageUrl = img.ruta; imageAlt = img.etiqueta || ''; showImageModal = true">
                                <p class="text-xs text-slate-400 truncate" x-text="img.etiqueta || '—'"></p>
                                <a :href="img.download_url" target="_blank" class="text-xs text-indigo-400 hover:text-indigo-300">Descargar</a>
                            </div>
                        </template>
                    </div>
                    <p class="text-sm text-slate-500" x-show="!verLoading && verImagenes.filter(img => img.tipo === 'equipo').length === 0">No hay imágenes del equipo guardadas.</p>
                </div>
                
                {{-- Imágenes del almacén --}}
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-300 mb-2">Imágenes del Almacén</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" x-show="verImagenes.filter(img => img.tipo === 'almacen').length">
                        <template x-for="img in verImagenes.filter(img => img.tipo === 'almacen')" :key="img.id">
                            <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-2">
                                <img :src="img.ruta" :alt="img.etiqueta || ''" class="w-full h-24 object-cover rounded mb-2 cursor-pointer" @click="imageUrl = img.ruta; imageAlt = img.etiqueta || ''; showImageModal = true">
                                <p class="text-xs text-slate-400 truncate" x-text="img.etiqueta || '—'"></p>
                                <a :href="img.download_url" target="_blank" class="text-xs text-indigo-400 hover:text-indigo-300">Descargar</a>
                            </div>
                        </template>
                    </div>
                    <p class="text-sm text-slate-500" x-show="!verLoading && verImagenes.filter(img => img.tipo === 'almacen').length === 0">No hay imágenes del almacén asociadas a este equipo. Puedes subirlas desde Almacén asignando este equipo.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-300 mb-2">Archivos del almacén</h4>
                    <ul class="space-y-2" x-show="verArchivos.length">
                        <template x-for="a in verArchivos" :key="a.id">
                            <li class="flex items-center justify-between py-2 border-b border-slate-700">
                                <span class="text-slate-200" x-text="a.nombre"></span>
                                <a :href="a.download_url" target="_blank" class="text-sm text-indigo-400 hover:text-indigo-300">Descargar</a>
                            </li>
                        </template>
                    </ul>
                    <p class="text-sm text-slate-500" x-show="!verLoading && verArchivos.length === 0">No hay archivos asociados a este equipo. Puedes subirlos desde Almacén asignando este equipo.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Traspasar --}}
    <div x-show="mostrarModalTraspaso" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70"
         >
        <div class="bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-700 text-white" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-purple-400">Traspasar Equipo</h3>
                <button type="button" @click="mostrarModalTraspaso = false" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <template x-if="equipoTraspaso">
                <div>
                    <p class="text-sm text-slate-400 mb-4">
                        Equipo: <span class="text-slate-200 font-medium" x-text="equipoTraspaso.codigo"></span>
                    </p>
                    <form method="POST" :action="'{{ url('/equipos') }}/' + equipoTraspaso.id + '/traspasar'" @submit="mostrarModalTraspaso = false">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-2">Destino *</label>
                                <select name="destino" x-model="traspasoForm.destino" @change="cargarCodigosDisponiblesTraspaso()" required
                                        class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                                    <option value="">-- Seleccione --</option>
                                    <option value="equipos_debaja">Equipos de baja (DB)</option>
                                    <option value="material_didactico">Material didáctico (MD)</option>
                                    <option value="auditoria">Auditoría (AU)</option>
                                </select>
                            </div>
                            
                            <div x-show="traspasoForm.destino">
                                <label class="block text-sm font-medium text-slate-400 mb-2">Número del código *</label>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-2 rounded-lg bg-slate-700 text-slate-200 font-mono text-lg font-semibold" x-text="
                                        traspasoForm.destino === 'equipos_debaja' ? 'DB' :
                                        traspasoForm.destino === 'material_didactico' ? 'MD' :
                                        traspasoForm.destino === 'auditoria' ? 'AU' : ''
                                    "></span>
                                    <input type="number" name="numero_codigo" x-model="traspasoForm.numero_codigo" required min="1"
                                           class="flex-1 px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100"
                                           placeholder="Ej: 1, 2, 3...">
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Ingrese solo el número. El prefijo (MD, AU) se agregará automáticamente.</p>
                                
                                <!-- Códigos disponibles -->
                                <div x-show="codigosDisponiblesTraspaso.length > 0" class="mt-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                                    <p class="text-xs font-semibold text-slate-300 mb-2">Códigos disponibles:</p>
                                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                                        <template x-for="codigo in codigosDisponiblesTraspaso" :key="codigo.codigo">
                                            <button type="button" 
                                                    @click="traspasoForm.numero_codigo = codigo.codigo.replace(/^DB|^MD|^AU/, '')"
                                                    class="px-2 py-1 text-xs rounded border border-slate-600 bg-slate-700 text-slate-200 hover:bg-slate-600 hover:border-slate-500 transition"
                                                    x-text="codigo.codigo"
                                                    title="Click para usar este código"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-700">
                                <p class="text-sm text-slate-300 mb-2">
                                    <span class="font-semibold text-purple-400">Código actual:</span> 
                                    <span x-text="equipoTraspaso.codigo" class="text-slate-100"></span>
                                </p>
                                <p class="text-xs text-slate-400 mb-3">
                                    El código actual se liberará y estará disponible para reutilizar.
                                </p>
                                
                                <!-- Último equipo traspasado -->
                                <div x-show="ultimoEquipoTraspasado" class="mt-3 pt-3 border-t border-slate-700">
                                    <p class="text-xs text-slate-400 mb-1">Último equipo traspasado:</p>
                                    <p class="text-sm text-slate-200">
                                        <span class="font-semibold" x-text="ultimoEquipoTraspasado.codigo"></span>
                                        <span class="text-slate-400 mx-2">←</span>
                                        <span class="text-slate-300" x-text="ultimoEquipoTraspasado.codigo_original"></span>
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1" x-text="ultimoEquipoTraspasado.descripcion"></p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition">
                                Traspasar
                            </button>
                            <button type="button" @click="mostrarModalTraspaso = false" class="px-6 py-3 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal Códigos Disponibles --}}
    <div x-show="mostrarCodigosDisponibles" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto"
         >
        <div class="bg-slate-900 rounded-2xl shadow-2xl max-w-3xl w-full p-6 border border-slate-700 text-white" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-semibold text-indigo-400">Códigos Disponibles</h3>
                    <template x-if="codigosDisponibles.length > 0">
                        <span class="px-3 py-1 rounded-full bg-green-600 text-white text-xs font-semibold" 
                              x-text="codigosDisponibles.length + ' código(s) disponible(s)'"></span>
                    </template>
                </div>
                <button type="button" @click="mostrarCodigosDisponibles = false" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <template x-if="codigosDisponibles.length > 0 && codigosDisponibles.some(c => c.es_secuencia)">
                <div class="mb-4 p-3 rounded-lg bg-blue-900/50 border border-blue-700">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-300">📢 Campaña de Recuperación de Códigos</p>
                            <p class="text-xs text-blue-400 mt-1">
                                Se detectaron códigos faltantes en la secuencia. Estos códigos están disponibles para reutilizar y ayudan a mantener la continuidad de la numeración.
                            </p>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="codigosCargando" class="py-8 text-center text-slate-400">Cargando códigos...</div>
            <div x-show="!codigosCargando">
                <div class="max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-800 sticky top-0">
                            <tr>
                                <th class="text-left py-3 px-4 text-slate-400">Código</th>
                                <th class="text-left py-3 px-4 text-slate-400">Origen</th>
                                <th class="text-left py-3 px-4 text-slate-400">Descripción</th>
                                <th class="text-left py-3 px-4 text-slate-400">Fecha</th>
                                <th class="text-right py-3 px-4 text-slate-400">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-for="codigo in codigosDisponibles" :key="codigo.id || ('seq_' + codigo.codigo)">
                                <tr class="hover:bg-slate-800/50" :class="codigo.es_secuencia ? 'bg-blue-900/20' : ''">
                                    <td class="py-3 px-4 font-mono text-slate-200">
                                        <div class="flex items-center gap-2">
                                            <span x-text="codigo.codigo"></span>
                                            <template x-if="codigo.es_secuencia">
                                                <span class="px-2 py-0.5 rounded text-xs bg-blue-600 text-white font-semibold">Secuencia</span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-300" x-text="codigo.origen_tipo"></td>
                                    <td class="py-3 px-4 text-slate-300 max-w-xs truncate" :title="codigo.descripcion_original" x-text="codigo.descripcion_original || '—'"></td>
                                    <td class="py-3 px-4 text-slate-400" x-text="codigo.fecha_disponible"></td>
                                    <td class="py-3 px-4 text-right">
                                        <button type="button" 
                                                @click="usarCodigoDisponible(codigo)"
                                                class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                                            Usar
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="codigosDisponibles.length === 0 && !codigosCargando">
                                <td colspan="5" class="py-8 text-center text-slate-500">No hay códigos disponibles.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('equipos.modals.equipo', compact('tipoItems', 'tipoEquipos', 'estadoRemisiones', 'proveedores', 'fabricantes', 'sedes', 'bodegas', 'usoItems'))
</div>

<script>
function equiposApp() {
    return {
        showModal: false,
        showExportModal: false,
        mostrarModalTraspaso: false,
        equipoTraspaso: null,
        ultimoEquipoTraspasado: null,
        codigosDisponiblesTraspaso: [],
        traspasoForm: {
            destino: '',
            numero_codigo: ''
        },
        mostrarCodigosDisponibles: false,
        codigosDisponibles: [],
        codigosCargando: false,
        filtros: {
            empresa_id: @json($empresaId ?? ''),
            sede_id: @json($sedeId ?? ''),
            bodega_id: @json($bodegaId ?? '')
        },
        sedesFiltradas: @json($sedes ?? []),
        bodegasFiltradas: @json($bodegas ?? []),
        todasLasSedes: @json($sedes ?? []),
        todasLasBodegas: @json($bodegas ?? []),
        sedesModal: @json($sedes ?? []),
        exportStep: 1,
        exportTipoIds: [],
        exportPreviewRows: [],
        exportTiposNombres: [],
        exportTotal: 0,
        exportPreviewLoading: false,
        logos: {
            principal: '{{ !empty($logoMain) ? asset('storage/' . $logoMain) : '' }}',
            secundario: '{{ !empty($logoSecondary) ? asset('storage/' . $logoSecondary) : '' }}',
        },
        showImageModal: false,
        imageUrl: '',
        imageAlt: '',
        imageEquipoId: null,
        replaceLoading: false,
        showVerModal: false,
        verEquipo: null,
        verImagenes: [],
        verArchivos: [],
        verLoading: false,
        showHojaVidaModal: false,
        hojaVidaEquipoId: null,
        hojaVidaCodigo: '',
        // Modal para formato de inspección (por tipo de equipo)
        showInspeccionFormatoModal: false,
        inspeccionTipoEquipoId: null,
        inspeccionTipoNombre: '',
        inspeccionEquipoCodigo: '',
        showHojaVidaPlantillaModal: false,
        hojaVidaPlantillaEquipoId: null,
        hojaVidaPlantillaCodigo: '',
        hojaVidaPlantillaCampos: {
            puntos_anclaje: '',
            talla: '',
            material: '',
            color: '',
            otras_especificaciones: '',
            cumple_normas: '',
            descripcion_inspecciones: '',
            fecha_inspeccion: '',
            validez_inspeccion: '',
        },
        hojaVidaPlantillaPreviewUrl: null,
        hojaVidaPlantillaPreviewHtml: null,
        hojaVidaPlantillaGenerando: false,
        showModalAlmacenHojasVida: false,
        almacenHojasVidaEquipoId: null,
        almacenHojasVidaCodigo: '',
        showModalImportarPdf: false,
        importarPdfForm: {
            nombre: '',
            archivo: null
        },
        importarPdfVistaPrevia: null,
        importarPdfNombreArchivo: '',
        importarPdfSubiendo: false,
        showModalAsignarPdf: false,
        pdfsEquipo: [],
        pdfsCargando: false,
        pdfSeleccionado: null,
        tipoEquipoSeleccionado: '',
        reasignandoPdf: false,
        pdfActualReasignacion: null,
        tipoEquipoReasignacion: null,
        nombreTipoEquipoReasignacion: '',
        showModalVerPdfsAsignados: false,
        pdfsAsignados: [],
        pdfsAsignadosCargando: false,
        showModalReemplazarFormato: false,
        pdfActualReemplazoFormato: null,
        tipoEquipoReemplazoFormato: null,
        tipoEquipoReemplazoFormatoId: null,
        pdfSeleccionadoReemplazo: null,
        modalAnteriorReemplazo: 'todos',
        hojaVidaPlaceholders: {},
        hojaVidaLabels: {},
        hojaVidaEmptyKeys: [],
        hojaVidaOverrides: {},
        hojaVidaLoading: false,
        hojaVidaGenerando: false,
        showMiniProveedor: false,
        showMiniFabricante: false,
        nuevoProveedor: '',
        nuevoFabricante: '',
        proveedoresList: @json($proveedores),
        fabricantesList: @json($fabricantes),
        ultimoCodigo: null,
        codigosSaltados: [],
        confirmarCodigosSaltados: false,
        puedeDesbloquearCodigo: @json($puedeDesbloquearCodigo ?? false),
        mostrarPasswordModal: false,
        passwordCodigo: '',
        form: {
            id: null,
            empresa_id: @json($empresaId ?? ''),
            tipo_item_id: '',
            tipo_equipo_id: '',
            codigo: '',
            nombre: '',
            codigo_bloqueado: false,
            passwordVerificada: false,
            estado_remision_id: '',
            tipo_registro: 'normal',
            descripcion: '',
            marca: '',
            proveedor_id: '',
            fabricante_id: '',
            modelo: '',
            sede_id: '',
            bodega_id: '',
            vida_util: 12,
            fecha_fabricacion: '',
            fecha_uso: '',
            uso_item_id: '',
            es_kit: false,
            nombre_kit: '',
            descripcion_kit_general: '',
            componentes_kit: '',
            valor: '',
            numero_factura: '',
            capacidades_resistencia: '',
            lote: '',
            fecha_compra: '',
            tiene_manual_fabricante: false,
            tiene_certificacion: false,
            tiene_imagen_general: false,
            tiene_imagen_etiqueta: false,
            imagen_general: null,
            imagen_etiqueta: null,
        },
        bodegasModal: @json($bodegas ?? []),
        kitItems: [],
        exportEmpresaId: '',
        exportTipoItemId: '',
        exportTipoEquipoIds: [],
        exportTipoItemsFiltrados: [],
        exportTipoEquiposFiltrados: [],
        cargarTiposItemsExport() {
            if (!this.exportEmpresaId) {
                this.exportTipoItemsFiltrados = [];
                return;
            }
            fetch('{{ route('equipos.export.tipos-items') }}?empresa_id=' + this.exportEmpresaId)
                .then(r => r.json())
                .then(data => {
                    this.exportTipoItemsFiltrados = data.tipo_items || [];
                })
                .catch(() => {
                    this.exportTipoItemsFiltrados = [];
                });
        },
        cargarTiposEquiposExport() {
            if (!this.exportEmpresaId || !this.exportTipoItemId) {
                this.exportTipoEquiposFiltrados = [];
                return;
            }
            fetch('{{ route('equipos.export.tipos-equipos') }}?empresa_id=' + this.exportEmpresaId + '&tipo_item_id=' + this.exportTipoItemId)
                .then(r => r.json())
                .then(data => {
                    this.exportTipoEquiposFiltrados = data.tipo_equipos || [];
                })
                .catch(() => {
                    this.exportTipoEquiposFiltrados = [];
                });
        },
        exportSelectAllTiposEquipos() {
            this.exportTipoEquipoIds = this.exportTipoEquiposFiltrados.map(te => te.id);
        },
        exportDeselectAllTiposEquipos() {
            this.exportTipoEquipoIds = [];
        },
        async exportShowPreview() {
            if (this.exportTipoEquipoIds.length === 0) {
                alert('Por favor selecciona al menos un tipo de equipo.');
                return;
            }
            this.exportPreviewLoading = true;
            this.exportPreviewRows = [];
            try {
                const params = new URLSearchParams();
                params.append('empresa_id', this.exportEmpresaId);
                params.append('tipo_item_id', this.exportTipoItemId);
                this.exportTipoEquipoIds.forEach(id => params.append('tipo_equipos[]', id));
                const res = await fetch('{{ route('equipos.export.preview-data') }}?' + params.toString());
                const data = await res.json();
                this.exportPreviewRows = data.equipos || [];
                this.exportTiposNombres = data.tipos_seleccionados || [];
                this.exportTotal = data.total ?? 0;
                this.exportStep = 4;
            } catch (e) {
                alert('Error al cargar la vista previa.');
            } finally {
                this.exportPreviewLoading = false;
            }
        },
        exportPdfUrl() {
            const params = new URLSearchParams();
            params.append('empresa_id', this.exportEmpresaId);
            params.append('tipo_item_id', this.exportTipoItemId);
            this.exportTipoEquipoIds.forEach(id => params.append('tipo_equipos[]', id));
            return '{{ url('/equipos/export/pdf') }}?' + params.toString();
        },
        openImageModal(equipoId, url, alt) {
            this.imageEquipoId = equipoId;
            this.imageUrl = url;
            this.imageAlt = alt;
            this.showImageModal = true;
        },
        async submitReplaceImage(event) {
            const file = event.target.files?.[0];
            if (!file || !this.imageEquipoId) return;
            this.replaceLoading = true;
            const formData = new FormData();
            formData.append('imagen', file);
            formData.append('_token', '{{ csrf_token() }}');
            try {
                const res = await fetch('{{ url("/equipos") }}/' + this.imageEquipoId + '/imagen', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    this.imageUrl = data.url;
                    const btn = document.querySelector(`button[data-equipo-id="${this.imageEquipoId}"]`);
                    if (btn) {
                        const img = btn.querySelector('img');
                        if (img) img.src = data.url;
                    }
                } else {
                    alert(data.error || 'Error al subir la imagen.');
                }
            } catch (e) {
                alert('Error al subir la imagen. Intenta de nuevo.');
            } finally {
                this.replaceLoading = false;
                event.target.value = '';
            }
        },
        async openHojaVidaModal(equipoId) {
            this.showHojaVidaModal = true;
            this.hojaVidaEquipoId = equipoId;
            this.hojaVidaPlaceholders = {};
            this.hojaVidaLabels = {};
            this.hojaVidaEmptyKeys = [];
            this.hojaVidaOverrides = {};
            this.hojaVidaLoading = true;
            try {
                const url = '{{ url('/equipos') }}/' + equipoId + '/hoja-vida-datos';
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                this.hojaVidaPlaceholders = data.placeholders || {};
                this.hojaVidaLabels = data.labels || {};
                this.hojaVidaEmptyKeys = data.empty_keys || [];
                this.hojaVidaCodigo = data.equipo_codigo || '';
                this.hojaVidaOverrides = {};
            } catch (e) {
                this.hojaVidaEmptyKeys = [];
            } finally {
                this.hojaVidaLoading = false;
            }
        },
        async generarHojaVidaPdf() {
            if (!this.hojaVidaEquipoId) return;
            this.hojaVidaGenerando = true;
            try {
                const url = '{{ url('/equipos') }}/' + this.hojaVidaEquipoId + '/hoja-vida';
                const body = JSON.stringify({ overrides: this.hojaVidaOverrides });
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/pdf', 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                });
                if (!res.ok) throw new Error('Error al generar el PDF');
                const blob = await res.blob();
                const objUrl = URL.createObjectURL(blob);
                window.open(objUrl, '_blank');
                setTimeout(() => URL.revokeObjectURL(objUrl), 5000);
                this.showHojaVidaModal = false;
            } catch (e) {
                alert('No se pudo generar el PDF. Intenta de nuevo.');
            } finally {
                this.hojaVidaGenerando = false;
            }
        },
        abrirModalAlmacenHojasVida(equipoId, codigo) {
            this.almacenHojasVidaEquipoId = equipoId;
            this.almacenHojasVidaCodigo = codigo;
            this.showModalAlmacenHojasVida = true;
        },
        async exportarHojaVidaAlmacen() {
            if (!this.almacenHojasVidaEquipoId) return;
            this.showModalAlmacenHojasVida = false;
            // Abrir el modal de hoja de vida para exportar
            await this.openHojaVidaModal(this.almacenHojasVidaEquipoId);
        },
        abrirHojaVidaPlantillaModal(equipoId, codigo) {
            this.hojaVidaPlantillaEquipoId = equipoId;
            this.hojaVidaPlantillaCodigo = codigo || '';

            // Valores por defecto de los campos
            const camposPorDefecto = {
                puntos_anclaje: '',
                talla: '',
                material: '',
                color: '',
                otras_especificaciones: '',
                cumple_normas: '',
                descripcion_inspecciones: '',
                fecha_inspeccion: '',
                validez_inspeccion: '',
            };

            // Intentar cargar valores guardados previamente en este navegador
            let camposGuardados = null;
            try {
                const key = 'hojaVidaPlantillaCampos_' + equipoId;
                const raw = window.localStorage.getItem(key);
                if (raw) {
                    camposGuardados = JSON.parse(raw);
                }
            } catch (e) {
                console.warn('No se pudieron cargar los campos guardados de la hoja de vida:', e);
            }

            // Combinar por defecto + guardados (si existen)
            this.hojaVidaPlantillaCampos = camposGuardados
                ? { ...camposPorDefecto, ...camposGuardados }
                : { ...camposPorDefecto };

            this.hojaVidaPlantillaPreviewUrl = null;
            this.hojaVidaPlantillaPreviewHtml = null;
            this.showHojaVidaPlantillaModal = true;

            // Si había datos guardados, generar automáticamente la vista previa
            if (camposGuardados) {
                this.$nextTick(() => {
                    this.generarHojaVidaPlantillaPreview();
                });
            }
        },
        async generarHojaVidaPlantillaPreview() {
            if (!this.hojaVidaPlantillaEquipoId) return;
            this.hojaVidaPlantillaGenerando = true;
            try {
                const url = '{{ url('/equipos') }}/' + this.hojaVidaPlantillaEquipoId + '/exportar-pdf-plantilla-html';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.hojaVidaPlantillaCampos),
                });
                if (!res.ok) {
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        const data = await res.json();
                        throw new Error(data.message || 'Error al generar la vista previa');
                    }
                    throw new Error('Error al generar la vista previa');
                }
                const html = await res.text();
                this.hojaVidaPlantillaPreviewHtml = html;
                
                // Esperar a que el HTML se renderice y luego configurar los event listeners
                await this.$nextTick();
                this.configurarEditoresVistaPrevia();
            } catch (e) {
                alert('No se pudo generar la vista previa. ' + (e.message || 'Intenta de nuevo.'));
            } finally {
                this.hojaVidaPlantillaGenerando = false;
            }
        },
        configurarEditoresVistaPrevia() {
            // Esperar un momento para que Alpine renderice el HTML
            setTimeout(() => {
                const previewContainer = this.$refs.previewContainer;
                if (!previewContainer) return;
                
                const editableCells = previewContainer.querySelectorAll('[contenteditable="true"]');
                editableCells.forEach(cell => {
                    // Remover listeners anteriores si existen
                    const newCell = cell.cloneNode(true);
                    cell.parentNode.replaceChild(newCell, cell);
                    
                    newCell.addEventListener('blur', (e) => {
                        const field = e.target.getAttribute('data-field');
                        const value = e.target.innerText.trim();
                        if (field) {
                            // Mapear campos del HTML a campos del formulario
                            const fieldMap = {
                                'nombre': 'nombre',
                                'codigo': 'codigo',
                                'ubicacion': 'ubicacion',
                                'serial': 'serial',
                                'modelo': 'modelo',
                                'marca': 'marca',
                                'lote': 'lote',
                                'fabricante': 'fabricante',
                                'proveedor': 'proveedor',
                                'uso': 'uso',
                                'fecha_compra': 'fecha_compra',
                                'fecha_fabricacion': 'fecha_fabricacion',
                                'puntos_anclaje': 'puntos_anclaje',
                                'talla': 'talla',
                                'cumple_normas': 'cumple_normas',
                                'capacidades_resistencia': 'capacidades_resistencia',
                                'material': 'material',
                                'color': 'color',
                                'otras_especificaciones': 'otras_especificaciones',
                                'descripcion_inspecciones': 'descripcion_inspecciones',
                                'fecha_inspeccion': 'fecha_inspeccion',
                                'validez_inspeccion': 'validez_inspeccion',
                            };
                            
                            const mappedField = fieldMap[field];
                            if (mappedField && this.hojaVidaPlantillaCampos.hasOwnProperty(mappedField)) {
                                this.hojaVidaPlantillaCampos[mappedField] = value;
                            }
                        }
                    });
                });
            }, 100);
        },
        guardarCambiosVistaPrevia() {
            if (!this.hojaVidaPlantillaPreviewHtml) return;
            
            // Extraer todos los valores editados del HTML
            const previewContainer = document.querySelector('[x-html]');
            if (previewContainer) {
                const editableCells = previewContainer.querySelectorAll('[contenteditable="true"]');
                editableCells.forEach(cell => {
                    const field = cell.getAttribute('data-field');
                    const value = cell.innerText.trim();
                    if (field && this.hojaVidaPlantillaCampos.hasOwnProperty(field)) {
                        this.hojaVidaPlantillaCampos[field] = value;
                    }
                });
            }

            // Guardar los campos localmente por equipo para que se mantengan al volver a entrar
            try {
                if (this.hojaVidaPlantillaEquipoId) {
                    const key = 'hojaVidaPlantillaCampos_' + this.hojaVidaPlantillaEquipoId;
                    window.localStorage.setItem(key, JSON.stringify(this.hojaVidaPlantillaCampos));
                }
            } catch (e) {
                console.warn('No se pudieron guardar los campos de la hoja de vida en localStorage:', e);
            }

            // Regenerar la vista previa con los cambios guardados
            this.generarHojaVidaPlantillaPreview();
            alert('Cambios guardados correctamente.');
        },
        async descargarHojaVidaPlantilla() {
            if (!this.hojaVidaPlantillaEquipoId) return;
            
            // Guardar cambios antes de descargar
            this.guardarCambiosVistaPrevia();
            
            try {
                const url = '{{ url('/equipos') }}/' + this.hojaVidaPlantillaEquipoId + '/exportar-pdf-plantilla';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/pdf',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.hojaVidaPlantillaCampos),
                });
                if (!res.ok) {
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        const data = await res.json();
                        throw new Error(data.message || 'Error al generar el PDF');
                    }
                    throw new Error('Error al generar el PDF');
                }
                const blob = await res.blob();
                const objUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objUrl;
                link.download = 'hoja-vida-equipo-' + (this.hojaVidaPlantillaCodigo || this.hojaVidaPlantillaEquipoId) + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(() => URL.revokeObjectURL(objUrl), 100);
            } catch (e) {
                alert('No se pudo descargar el PDF. ' + (e.message || 'Intenta de nuevo.'));
            }
        },
        async imprimirHojaVidaAlmacen() {
            if (!this.almacenHojasVidaEquipoId) return;
            this.showModalAlmacenHojasVida = false;
            try {
                // Generar el PDF y abrirlo en una nueva ventana para imprimir
                const url = '{{ url('/equipos') }}/' + this.almacenHojasVidaEquipoId + '/hoja-vida';
                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/pdf',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Error al generar el PDF');
                const blob = await res.blob();
                const objUrl = URL.createObjectURL(blob);
                const printWindow = window.open(objUrl, '_blank');
                if (printWindow) {
                    printWindow.onload = () => {
                        setTimeout(() => {
                            printWindow.print();
                        }, 500);
                    };
                }
                setTimeout(() => URL.revokeObjectURL(objUrl), 10000);
            } catch (e) {
                alert('No se pudo generar el PDF para imprimir. Intenta de nuevo.');
            }
        },
        async verAlmacenHojasVida() {
            if (!this.almacenHojasVidaEquipoId) return;
            this.showModalAlmacenHojasVida = false;
            this.showModalVerPdfsAsignados = true;
            await this.cargarPdfsAsignados();
        },
        async cargarPdfsAsignados() {
            if (!this.almacenHojasVidaEquipoId) {
                console.error('No hay almacenHojasVidaEquipoId');
                return;
            }
            this.pdfsAsignadosCargando = true;
            this.pdfsAsignados = [];
            try {
                const url = '{{ url('/equipos') }}/' + this.almacenHojasVidaEquipoId + '/pdfs-almacen?t=' + Date.now();
                console.log('Cargando PDFs asignados desde:', url);
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                console.log('Respuesta de PDFs asignados:', data);
                if (response.ok && data.success) {
                    // Solo mostrar PDFs asignados
                    this.pdfsAsignados = (data.pdfs || []).filter(p => p.tipo_equipo);
                    console.log('PDFs asignados cargados:', this.pdfsAsignados.length);
                } else {
                    console.error('Error al cargar PDFs asignados:', data.message);
                    alert('Error al cargar los PDFs asignados: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error al cargar los PDFs asignados:', error);
                alert('Error al cargar los PDFs asignados: ' + error.message);
            } finally {
                this.pdfsAsignadosCargando = false;
            }
        },
        async iniciarReemplazoFormato(pdfId, tipoEquipoId, tipoEquipoNombre) {
            this.pdfActualReemplazoFormato = pdfId;
            this.tipoEquipoReemplazoFormatoId = tipoEquipoId;
            this.tipoEquipoReemplazoFormato = tipoEquipoNombre;
            this.pdfSeleccionadoReemplazo = null;
            // Guardar qué modal estaba abierto
            const modalTodosAbierto = this.showModalTodosPdfs;
            const modalVerAbierto = this.showModalVerPdfsAsignados;
            // Cerrar modales actuales
            this.showModalVerPdfsAsignados = false;
            this.showModalTodosPdfs = false;
            // Abrir modal de reemplazo
            this.showModalReemplazarFormato = true;
            await this.cargarPdfsEquipo();
            // Guardar qué modal volver a abrir después
            this.modalAnteriorReemplazo = modalTodosAbierto ? 'todos' : (modalVerAbierto ? 'ver' : 'todos');
        },
        cancelarReemplazoFormato() {
            this.showModalReemplazarFormato = false;
            this.pdfActualReemplazoFormato = null;
            this.tipoEquipoReemplazoFormatoId = null;
            this.tipoEquipoReemplazoFormato = null;
            this.pdfSeleccionadoReemplazo = null;
            // Volver al modal que estaba abierto antes
            if (this.modalAnteriorReemplazo === 'todos') {
                this.showModalTodosPdfs = true;
            } else if (this.modalAnteriorReemplazo === 'ver') {
                this.showModalVerPdfsAsignados = true;
            } else {
                this.showModalTodosPdfs = true;
            }
        },
        async reemplazarFormato() {
            if (!this.pdfActualReemplazoFormato || !this.pdfSeleccionadoReemplazo || !this.tipoEquipoReemplazoFormatoId) {
                alert('Por favor, selecciona un PDF para reemplazar.');
                return;
            }
            
            if (this.pdfActualReemplazoFormato == this.pdfSeleccionadoReemplazo) {
                alert('Debes seleccionar un PDF diferente al actual.');
                return;
            }
            
            try {
                const response = await fetch('{{ route('equipos.reemplazar-pdf-tipo-equipo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        pdf_actual_id: this.pdfActualReemplazoFormato,
                        pdf_nuevo_id: this.pdfSeleccionadoReemplazo,
                        tipo_equipo_id: this.tipoEquipoReemplazoFormatoId
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alert('Formato reemplazado correctamente.');
                    this.cancelarReemplazoFormato();
                    // Recargar PDFs en el modal que esté abierto
                    if (this.showModalTodosPdfs) {
                        await this.cargarPdfsEquipo();
                    } else if (this.showModalVerPdfsAsignados) {
                        await this.cargarPdfsAsignados();
                    }
                } else {
                    alert('Error al reemplazar el formato: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al reemplazar el formato.');
            }
        },
        async verPdfsAlmacen() {
            if (!this.almacenHojasVidaEquipoId) return;
            this.showModalAlmacenHojasVida = false;
            this.showModalTodosPdfs = true;
            await this.cargarPdfsEquipo();
        },
        async cargarPdfsEquipo() {
            if (!this.almacenHojasVidaEquipoId) {
                console.error('No hay almacenHojasVidaEquipoId');
                return;
            }
            this.pdfsCargando = true;
            this.pdfsEquipo = [];
            try {
                const url = '{{ url('/equipos') }}/' + this.almacenHojasVidaEquipoId + '/pdfs-almacen?t=' + Date.now();
                console.log('Cargando PDFs desde:', url);
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                console.log('Respuesta de PDFs:', data);
                if (response.ok && data.success) {
                    this.pdfsEquipo = data.pdfs || [];
                    console.log('PDFs cargados:', this.pdfsEquipo.length);
                } else {
                    console.error('Error al cargar PDFs:', data.message);
                    alert('Error al cargar los PDFs: ' + (data.message || 'Error desconocido'));
                    this.pdfsEquipo = [];
                }
            } catch (error) {
                console.error('Error al cargar los PDFs:', error);
                alert('Error al cargar los PDFs: ' + error.message);
                this.pdfsEquipo = [];
            } finally {
                this.pdfsCargando = false;
            }
        },
        async asignarPdfATipoEquipo() {
            if (!this.pdfSeleccionado || !this.tipoEquipoSeleccionado) {
                alert('Por favor, selecciona un PDF y un tipo de equipo.');
                return;
            }
            
            try {
                const response = await fetch('{{ route('equipos.asignar-pdf-tipo-equipo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        pdf_id: this.pdfSeleccionado,
                        tipo_equipo_id: this.tipoEquipoSeleccionado
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alert('PDF asignado correctamente.');
                    // Limpiar selección primero
                    this.pdfSeleccionado = null;
                    this.tipoEquipoSeleccionado = '';
                    this.equiposAsignados = [];
                    this.tipoEquipoAsignado = null;
                    
                    // Recargar PDFs para actualizar la lista completa
                    await this.cargarPdfsEquipo();
                } else {
                    alert(data.message || 'Error al asignar el PDF.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al asignar el PDF.');
            }
        },
        iniciarReasignacionPdf(pdfId, tipoEquipoId, nombreTipoEquipo) {
            this.reasignandoPdf = true;
            this.pdfActualReasignacion = pdfId;
            this.tipoEquipoReasignacion = tipoEquipoId;
            this.nombreTipoEquipoReasignacion = nombreTipoEquipo;
            this.pdfSeleccionado = null;
            this.tipoEquipoSeleccionado = tipoEquipoId;
        },
        cancelarReasignacion() {
            this.reasignandoPdf = false;
            this.pdfActualReasignacion = null;
            this.tipoEquipoReasignacion = null;
            this.nombreTipoEquipoReasignacion = '';
            this.pdfSeleccionado = null;
            this.tipoEquipoSeleccionado = '';
        },
        async reasignarPdf() {
            if (!this.pdfActualReasignacion || !this.pdfSeleccionado || !this.tipoEquipoReasignacion) {
                alert('Por favor, selecciona un PDF para reasignar.');
                return;
            }
            
            if (this.pdfActualReasignacion == this.pdfSeleccionado) {
                alert('Debes seleccionar un PDF diferente al actual.');
                return;
            }
            
            try {
                const response = await fetch('{{ route('equipos.reemplazar-pdf-tipo-equipo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        pdf_actual_id: this.pdfActualReasignacion,
                        pdf_nuevo_id: this.pdfSeleccionado,
                        tipo_equipo_id: this.tipoEquipoReasignacion
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Limpiar estado de reasignación primero
                    this.cancelarReasignacion();
                    
                    // Recargar PDFs para actualizar la lista
                    await this.cargarPdfsEquipo();
                    
                    // Limpiar selección y equipos asignados para mostrar la lista de PDFs
                    this.equiposAsignados = [];
                    this.tipoEquipoAsignado = null;
                    this.pdfSeleccionado = null;
                    this.tipoEquipoSeleccionado = '';
                    
                    alert('PDF reasignado correctamente.');
                } else {
                    alert('Error al reasignar el PDF: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                    alert('Error al reasignar el PDF.');
            }
        },
        async desasignarPdf(pdfId) {
            if (!confirm('¿Estás seguro de que deseas desasignar este PDF?')) {
                return;
            }
            
            try {
                const response = await fetch('{{ route('equipos.desasignar-pdf-tipo-equipo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        pdf_id: pdfId
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alert('PDF desasignado correctamente.');
                    // Recargar PDFs para actualizar la lista
                    await this.cargarPdfsEquipo();
                    // Limpiar selección
                    this.pdfSeleccionado = null;
                    this.tipoEquipoSeleccionado = '';
                } else {
                    alert('Error al desasignar el PDF: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al desasignar el PDF.');
            }
        },
        async eliminarPdf(pdfId, pdfNombre) {
            if (!confirm('¿Estás seguro de que deseas eliminar el PDF "' + pdfNombre + '"?\n\nEsta acción no se puede deshacer.')) {
                return;
            }
            
            try {
                const response = await fetch('{{ route('equipos.eliminar-pdf') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        pdf_id: pdfId
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alert('PDF eliminado correctamente.');
                    // Recargar PDFs para actualizar la lista
                    await this.cargarPdfsEquipo();
                    // Si el modal de ver asignados está abierto, recargarlo también
                    if (this.showModalVerPdfsAsignados) {
                        await this.cargarPdfsAsignados();
                    }
                    // Limpiar selección si el PDF eliminado estaba seleccionado
                    if (this.pdfSeleccionado == pdfId) {
                        this.pdfSeleccionado = null;
                        this.tipoEquipoSeleccionado = '';
                    }
                } else {
                    alert('Error al eliminar el PDF: ' + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el PDF.');
            }
        },
        abrirModalImportarPdf() {
            if (!this.almacenHojasVidaEquipoId) return;
            this.showModalAlmacenHojasVida = false;
            this.showModalImportarPdf = true;
            this.importarPdfForm = { nombre: '', archivo: null };
            this.importarPdfVistaPrevia = null;
            this.importarPdfNombreArchivo = '';
        },
        async exportarPdfEquipo(equipoId) {
            try {
                const url = '{{ url('/equipos') }}/' + equipoId + '/exportar-pdf-plantilla';
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/pdf',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    // Intentar leer el mensaje de error si es JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error al generar el PDF');
                    }
                    throw new Error('Error al generar el PDF');
                }
                
                // Verificar que la respuesta sea un PDF
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Error al generar el PDF');
                }
                
                const blob = await response.blob();
                const objUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objUrl;
                link.download = 'hoja-vida-equipo-' + equipoId + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(() => URL.revokeObjectURL(objUrl), 100);
            } catch (error) {
                console.error('Error:', error);
                alert('No se pudo generar el PDF. ' + (error.message || 'Intenta de nuevo.'));
            }
        },
        cerrarModalImportarPdf() {
            this.showModalImportarPdf = false;
            this.importarPdfForm = { nombre: '', archivo: null };
            this.importarPdfVistaPrevia = null;
            this.importarPdfNombreArchivo = '';
        },
        cargarPdfVistaPrevia(event) {
            const file = event.target.files[0];
            if (!file) {
                this.importarPdfVistaPrevia = null;
                this.importarPdfNombreArchivo = '';
                this.importarPdfForm.archivo = null;
                return;
            }
            
            // Validar que sea PDF
            if (file.type !== 'application/pdf') {
                alert('Por favor, selecciona un archivo PDF.');
                event.target.value = '';
                this.importarPdfVistaPrevia = null;
                this.importarPdfNombreArchivo = '';
                this.importarPdfForm.archivo = null;
                return;
            }
            
            // Validar tamaño (20MB)
            if (file.size > 20 * 1024 * 1024) {
                alert('El archivo es demasiado grande. El tamaño máximo es 20MB.');
                event.target.value = '';
                this.importarPdfVistaPrevia = null;
                this.importarPdfNombreArchivo = '';
                this.importarPdfForm.archivo = null;
                return;
            }
            
            this.importarPdfForm.archivo = file;
            this.importarPdfNombreArchivo = file.name;
            
            // Crear URL para vista previa
            const reader = new FileReader();
            reader.onload = (e) => {
                this.importarPdfVistaPrevia = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        limpiarVistaPreviaPdf() {
            this.importarPdfVistaPrevia = null;
            this.importarPdfNombreArchivo = '';
            this.importarPdfForm.archivo = null;
            // Limpiar el input de archivo
            const fileInput = document.querySelector('input[type="file"][accept=".pdf,application/pdf"]');
            if (fileInput) {
                fileInput.value = '';
            }
        },
        async subirPdfImportar() {
            if (!this.almacenHojasVidaEquipoId || !this.importarPdfForm.nombre || !this.importarPdfForm.archivo) {
                alert('Por favor, completa todos los campos.');
                return;
            }
            
            this.importarPdfSubiendo = true;
            
            try {
                const formData = new FormData();
                formData.append('nombre', this.importarPdfForm.nombre);
                formData.append('archivo', this.importarPdfForm.archivo);
                formData.append('equipo_id', this.almacenHojasVidaEquipoId);
                formData.append('tipo', 'hoja_vida');
                
                const response = await fetch('{{ route('equipos.importar-pdf') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alert('PDF importado correctamente.');
                    this.cerrarModalImportarPdf();
                    // Recargar PDFs en el modal de todos los PDFs si está abierto
                    if (this.showModalTodosPdfs) {
                        await this.cargarPdfsEquipo();
                        // Seleccionar el PDF recién importado
                        setTimeout(() => {
                            const pdfsSinAsignar = this.pdfsEquipo.filter(p => !p.tipo_equipo);
                            if (pdfsSinAsignar.length > 0) {
                                this.pdfSeleccionado = pdfsSinAsignar[0].id;
                            }
                        }, 500);
                    }
                    // Recargar PDFs en el modal de ver si está abierto
                    if (this.showModalVerPdfsAsignados) {
                        await this.cargarPdfsAsignados();
                    }
                } else {
                    alert(data.message || 'Error al importar el PDF. Intenta de nuevo.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al importar el PDF. Intenta de nuevo.');
            } finally {
                this.importarPdfSubiendo = false;
            }
        },
        openVer(id, codigo, descripcion) {
            this.verEquipo = { id, codigo, descripcion };
            this.showVerModal = true;
            this.verLoading = true;
            this.verImagenes = [];
            this.verArchivos = [];
            const url = '{{ url('/equipos') }}/' + id + '/almacen';
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    this.verImagenes = data.imagenes || [];
                    this.verArchivos = data.archivos || [];
                    this.verLoading = false;
                })
                .catch(() => { this.verLoading = false; });
        },
        async openModal() {
            // Si hay código prellenado desde código disponible
            const codigoPrellenado = @json(session('codigo_prellenado', null));
            // Si el código prellenado tiene prefijo IN, extraer solo el número
            let codigoInicial = codigoPrellenado || '';
            if (codigoInicial && codigoInicial.toUpperCase().startsWith('IN')) {
                const match = codigoInicial.match(/^IN(\d+)$/i);
                if (match) {
                    codigoInicial = match[1];
                }
            }
            this.form = {
                id: null, empresa_id: this.filtros.empresa_id || '', tipo_item_id: '', tipo_equipo_id: '', codigo: codigoInicial, nombre: '', codigo_bloqueado: false, passwordVerificada: false, estado_remision_id: '',
                tipo_registro: 'normal',
                descripcion: '', marca: '', proveedor_id: '', fabricante_id: '', modelo: '',
                sede_id: '', bodega_id: '', vida_util: 12, fecha_fabricacion: '', fecha_uso: '',
                uso_item_id: '', es_kit: false, nombre_kit: '', descripcion_kit_general: '', componentes_kit: '', valor: '',
                numero_factura: '', capacidades_resistencia: '', lote: '', fecha_compra: '',
                tiene_manual_fabricante: false, tiene_certificacion: false,
                tiene_imagen_general: false, tiene_imagen_etiqueta: false, imagen_general: null, imagen_etiqueta: null,
            };
            this.bodegasModal = this.todasLasBodegas;
            this.sedesModal = this.todasLasSedes;
            this.kitItems = [];
            this.ultimoCodigo = null;
            this.codigosSaltados = [];
            this.confirmarCodigosSaltados = false;
            this.showModal = true;
            
            // Cargar sedes y último código si hay empresa seleccionada
            if (this.form.empresa_id) {
                await this.cargarSedesModal();
                await this.cargarUltimoCodigo();
            }
            
            // Formatear el código con IN después de cargar todo
            if (this.form.codigo) {
                this.formatearCodigoIN();
            }
            
            // Limpiar código prellenado de la sesión después de usarlo
            if (codigoPrellenado) {
                fetch('{{ route('equipos.index') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ limpiar_codigo_prellenado: true })
                }).catch(() => {});
            }
        },
        async cargarUltimoCodigo() {
            if (!this.form.empresa_id) {
                this.ultimoCodigo = null;
                return;
            }
            try {
                const res = await fetch('{{ route('equipos.ultimo-codigo') }}?empresa_id=' + this.form.empresa_id, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.ultimoCodigo = data.ultimo_codigo || null;
            } catch (e) {
                this.ultimoCodigo = null;
            }
        },
        formatearCodigoIN() {
            if (!this.form.codigo) {
                return;
            }
            
            // Si el código ya empieza con "IN", mantenerlo pero asegurar que tenga el formato correcto
            if (this.form.codigo.toUpperCase().startsWith('IN')) {
                // Si tiene "IN" seguido de números, mantenerlo
                const match = this.form.codigo.match(/^IN(\d+)$/i);
                if (match) {
                    this.form.codigo = 'IN' + match[1];
                    return;
                }
                // Si tiene "IN" pero con más texto, extraer solo el número
                const numeroMatch = this.form.codigo.match(/(\d+)/);
                if (numeroMatch) {
                    this.form.codigo = 'IN' + numeroMatch[1];
                    return;
                }
            }
            
            // Si solo tiene números, agregar "IN" al inicio
            if (/^\d+$/.test(this.form.codigo)) {
                this.form.codigo = 'IN' + this.form.codigo;
                return;
            }
            
            // Si tiene otro prefijo, reemplazarlo por "IN"
            const numeroMatch = this.form.codigo.match(/(\d+)$/);
            if (numeroMatch) {
                this.form.codigo = 'IN' + numeroMatch[1];
            }
        },
        async verificarCodigoSaltado() {
            this.codigosSaltados = [];
            this.confirmarCodigosSaltados = false;
            
            if (!this.form.codigo || !this.form.empresa_id || !this.ultimoCodigo) {
                return;
            }
            
            // Asegurar que el código tenga el formato IN
            if (!this.form.codigo.toUpperCase().startsWith('IN')) {
                this.formatearCodigoIN();
            }
            
            // Extraer números de los códigos
            const matchIngresado = this.form.codigo.match(/^IN(\d+)$/i);
            const matchUltimo = this.ultimoCodigo.codigo.match(/^IN(\d+)$/i);
            
            if (!matchIngresado) {
                return;
            }
            
            if (!matchUltimo) {
                return;
            }
            
            const numeroIngresado = parseInt(matchIngresado[1]);
            const ultimoNumero = parseInt(matchUltimo[1]);
            
            // Si el número ingresado es mucho mayor al último + 1, hay códigos saltados
            if (numeroIngresado > ultimoNumero + 1) {
                const saltados = [];
                for (let i = ultimoNumero + 1; i < numeroIngresado; i++) {
                    const codigoSaltado = 'IN' + String(i).padStart(matchIngresado[1].length, '0');
                    saltados.push(codigoSaltado);
                }
                this.codigosSaltados = saltados;
            }
        },
        async editEquipo(e) {
            // Mantener el estado de bloqueo del equipo (siempre mostrar candado bloqueado por defecto)
            // Si el usuario no tiene permiso, siempre debe estar bloqueado
            const codigoBloqueado = !this.puedeDesbloquearCodigo ? true : (e.codigo_bloqueado !== undefined ? e.codigo_bloqueado : true);
            
            // Extraer solo el número del código si tiene prefijo "IN"
            let codigoMostrar = e.codigo;
            if (e.codigo && e.codigo.toUpperCase().startsWith('IN')) {
                const match = e.codigo.match(/^IN(\d+)$/i);
                if (match) {
                    codigoMostrar = match[1];
                }
            }
            
            // Formatear fechas para inputs de tipo date (YYYY-MM-DD)
            const formatearFecha = (fecha) => {
                if (!fecha) return '';
                
                // Si ya está en formato YYYY-MM-DD, devolverlo tal cual
                if (/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
                    return fecha;
                }
                
                // Si viene en formato ISO 8601 (YYYY-MM-DDTHH:mm:ss), extraer solo la fecha
                if (/^\d{4}-\d{2}-\d{2}T/.test(fecha)) {
                    return fecha.split('T')[0];
                }
                
                // Si viene en formato d/m/Y o d-m-Y, convertir
                const formatoDMY = fecha.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
                if (formatoDMY) {
                    const dia = formatoDMY[1].padStart(2, '0');
                    const mes = formatoDMY[2].padStart(2, '0');
                    const año = formatoDMY[3];
                    return `${año}-${mes}-${dia}`;
                }
                
                // Si viene en formato Y/m/d o Y-m-d, convertir
                const formatoYMD = fecha.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/);
                if (formatoYMD) {
                    const año = formatoYMD[1];
                    const mes = formatoYMD[2].padStart(2, '0');
                    const dia = formatoYMD[3].padStart(2, '0');
                    return `${año}-${mes}-${dia}`;
                }
                
                // Intentar parsear como Date
                try {
                    const fechaObj = new Date(fecha);
                    if (!isNaN(fechaObj.getTime())) {
                        const año = fechaObj.getFullYear();
                        const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
                        const dia = String(fechaObj.getDate()).padStart(2, '0');
                        return `${año}-${mes}-${dia}`;
                    }
                } catch (err) {
                    // Si falla, intentar devolver vacío
                }
                
                return '';
            };
            
            this.form = {
                id: e.id, empresa_id: e.empresa_id || '', tipo_item_id: e.tipo_item_id || '', tipo_equipo_id: e.tipo_equipo_id || '', codigo: codigoMostrar, nombre: e.nombre || '', codigo_bloqueado: codigoBloqueado, passwordVerificada: false,
                estado_remision_id: e.estado_remision_id || '', descripcion: e.descripcion, marca: e.marca || '',
                proveedor_id: e.proveedor_id || '', fabricante_id: e.fabricante_id || '', modelo: e.modelo || '',
                sede_id: e.sede_id || '', bodega_id: e.bodega_id || '', vida_util: e.vida_util || 12,
                fecha_fabricacion: formatearFecha(e.fecha_fabricacion), fecha_uso: formatearFecha(e.fecha_uso),
                tipo_registro: e.tipo_registro || 'normal',
                uso_item_id: e.uso_item_id || '', es_kit: e.es_kit, nombre_kit: e.nombre_kit || '',
                descripcion_kit_general: '', // se rellenará al parsear componentes_kit
                componentes_kit: e.componentes_kit || '', valor: e.valor || '', numero_factura: e.numero_factura || '',
                capacidades_resistencia: e.capacidades_resistencia || '', lote: e.lote || '',
                fecha_compra: formatearFecha(e.fecha_compra), tiene_manual_fabricante: e.tiene_manual_fabricante,
                tiene_certificacion: e.tiene_certificacion, tiene_imagen_general: e.tiene_imagen_general,
                tiene_imagen_etiqueta: e.tiene_imagen_etiqueta, imagen_general: e.imagen_general || null,
                imagen_etiqueta: e.imagen_etiqueta || null,
            };
            
            // Guardar los valores de sede y bodega antes de cargar las opciones
            const sedeIdGuardado = this.form.sede_id;
            const bodegaIdGuardado = this.form.bodega_id;
            
            // Cargar sedes según la empresa seleccionada
            if (this.form.empresa_id) {
                await this.cargarSedesModal(true); // Preservar valores
                // Restaurar el valor de sede después de cargar
                if (sedeIdGuardado) {
                    this.form.sede_id = sedeIdGuardado;
                    // Cargar bodegas después de restaurar la sede
                    await this.cargarBodegasModal(true); // Preservar valores
                    // Restaurar el valor de bodega después de cargar
                    if (bodegaIdGuardado) {
                        this.form.bodega_id = bodegaIdGuardado;
                    }
                }
            } else {
                this.sedesModal = this.todasLasSedes;
                this.bodegasModal = this.todasLasBodegas;
            }
            // Parsear componentes_kit existentes (si los hay) a estructura de items + descripción general
            this.kitItems = [];
            this.form.descripcion_kit_general = '';
            if (e.componentes_kit) {
                const lineas = String(e.componentes_kit).split('\n').map(l => l.trim()).filter(l => l.length);
                lineas.forEach((linea) => {
                    if (linea.startsWith('DESC|')) {
                        this.form.descripcion_kit_general = linea.slice(5).trim();
                        return;
                    }
                    const partes = linea.split('|');
                    const codigo = (partes[0] || '').trim();
                    const nombre = (partes[1] || '').trim();
                    const descripcion = (partes[2] || '').trim();
                    this.kitItems.push({
                        codigo: codigo || '',
                        nombre,
                        descripcion,
                    });
                });
            }
            this.sincronizarCodigosKit();
            this.showModal = true;
        },
        async agregarProveedor() {
            if (!this.nuevoProveedor.trim()) return;
            try {
                const res = await fetch('{{ route('proveedores.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ nombre: this.nuevoProveedor })
                });
                const data = await res.json();
                this.proveedoresList.push(data);
                this.form.proveedor_id = data.id;
                this.nuevoProveedor = '';
                this.showMiniProveedor = false;
            } catch (e) { alert('Error al crear proveedor'); }
        },
        async agregarFabricante() {
            if (!this.nuevoFabricante.trim()) return;
            try {
                const res = await fetch('{{ route('fabricantes.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ nombre: this.nuevoFabricante })
                });
                const data = await res.json();
                this.fabricantesList.push(data);
                this.form.fabricante_id = data.id;
                this.nuevoFabricante = '';
                this.showMiniFabricante = false;
            } catch (e) { alert('Error al crear fabricante'); }
        },
        async cargarSedesModal(preservarValores = false) {
            const sedeIdActual = preservarValores ? this.form.sede_id : '';
            const bodegaIdActual = preservarValores ? this.form.bodega_id : '';
            
            if (!this.form.empresa_id) {
                this.sedesModal = this.todasLasSedes;
                if (!preservarValores) {
                this.form.sede_id = '';
                this.bodegasModal = this.todasLasBodegas;
                this.form.bodega_id = '';
                }
                return;
            }
            try {
                const res = await fetch(`{{ url('/api/sedes') }}?empresa_id=${this.form.empresa_id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.sedesModal = data.sedes || [];
                if (!preservarValores) {
                this.form.sede_id = '';
                this.bodegasModal = this.todasLasBodegas;
                this.form.bodega_id = '';
                } else {
                    // Restaurar valores si se están preservando
                    this.form.sede_id = sedeIdActual;
                    this.form.bodega_id = bodegaIdActual;
                }
            } catch (e) {
                this.sedesModal = [];
                if (!preservarValores) {
                this.form.sede_id = '';
                this.bodegasModal = this.todasLasBodegas;
                this.form.bodega_id = '';
                } else {
                    // Restaurar valores si se están preservando
                    this.form.sede_id = sedeIdActual;
                    this.form.bodega_id = bodegaIdActual;
                }
            }
        },
        openInspeccionFormatoModal(tipoEquipoId, tipoNombre, codigoEquipo) {
            if (!tipoEquipoId) {
                alert('Este equipo no tiene un tipo de equipo con formato de inspección asignado.');
                return;
            }
            this.inspeccionTipoEquipoId = tipoEquipoId;
            this.inspeccionTipoNombre = tipoNombre || '';
            this.inspeccionEquipoCodigo = codigoEquipo || '';
            this.showInspeccionFormatoModal = true;
        },
        async cargarBodegasModal(preservarValores = false) {
            const bodegaIdActual = preservarValores ? this.form.bodega_id : '';
            
            if (!this.form.sede_id) {
                this.bodegasModal = this.todasLasBodegas;
                if (!preservarValores) {
                this.form.bodega_id = '';
                } else {
                    this.form.bodega_id = bodegaIdActual;
                }
                return;
            }
            try {
                const res = await fetch(`{{ url('/api/bodegas') }}?sede_id=${this.form.sede_id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.bodegasModal = data.bodegas || [];
                if (!preservarValores) {
                this.form.bodega_id = '';
                } else {
                    // Restaurar valor si se está preservando
                    this.form.bodega_id = bodegaIdActual;
                }
            } catch (e) {
                this.bodegasModal = [];
                if (!preservarValores) {
                this.form.bodega_id = '';
                } else {
                    // Restaurar valor si se está preservando
                    this.form.bodega_id = bodegaIdActual;
                }
            }
        },
        agregarKitItem() {
            if (!this.form.codigo) {
                alert('Primero escribe el código del kit para generar los códigos de los items.');
                return;
            }
            this.kitItems.push({
                codigo: '',
                nombre: '',
                descripcion: '',
            });
            this.sincronizarCodigosKit();
        },
        eliminarKitItem(index) {
            this.kitItems.splice(index, 1);
            this.sincronizarCodigosKit();
        },
        sincronizarCodigosKit() {
            if (!this.form.codigo) return;
            this.kitItems = this.kitItems.map((item, idx) => ({
                codigo: `${this.form.codigo}.${idx + 1}`,
                nombre: item.nombre || '',
                descripcion: item.descripcion || '',
            }));
        },
        kitItemsSerializados() {
            const lineas = [];
            if (this.form.descripcion_kit_general && this.form.descripcion_kit_general.trim().length) {
                lineas.push('DESC|' + this.form.descripcion_kit_general.replace(/\r?\n/g, ' ').trim());
            }
            if (this.kitItems && this.kitItems.length) {
                this.kitItems.forEach(item => {
                    if (!item.nombre && !item.descripcion) return;
                    const codigo = item.codigo || '';
                    const nombre = (item.nombre || '').replace(/\r?\n/g, ' ').trim();
                    const desc = (item.descripcion || '').replace(/\r?\n/g, ' ').trim();
                    lineas.push(`${codigo}|${nombre}|${desc}`.trim());
                });
            }
            return lineas.join('\n');
        },
        async abrirModalTraspaso(equipo) {
            this.equipoTraspaso = equipo;
            this.traspasoForm = {
                destino: '',
                numero_codigo: ''
            };
            this.ultimoEquipoTraspasado = null;
            this.codigosDisponiblesTraspaso = [];
            this.mostrarModalTraspaso = true;
            
            // Cargar el último destino usado para este equipo
            if (this.filtros.empresa_id && equipo.id) {
                try {
                    const url = '{{ route('equipos.ultimo-traspasado') }}?empresa_id=' + this.filtros.empresa_id + '&equipo_id=' + equipo.id;
                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    if (data.ultimo_equipo && data.ultimo_equipo.destino) {
                        this.traspasoForm.destino = data.ultimo_equipo.destino;
                        this.ultimoEquipoTraspasado = data.ultimo_equipo;
                        // Cargar códigos disponibles para ese destino
                        await this.cargarCodigosDisponiblesTraspaso();
                    }
                } catch (e) {
                    console.error('Error al cargar último destino:', e);
                }
            }
        },
        async cargarUltimoEquipoTraspasado() {
            if (!this.traspasoForm.destino || !this.filtros.empresa_id) {
                this.ultimoEquipoTraspasado = null;
                this.codigosDisponiblesTraspaso = [];
                return;
            }
            
            try {
                const equipoId = this.equipoTraspaso?.id || '';
                const url = '{{ route('equipos.ultimo-traspasado') }}?empresa_id=' + this.filtros.empresa_id + '&destino=' + this.traspasoForm.destino + (equipoId ? '&equipo_id=' + equipoId : '');
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.ultimoEquipoTraspasado = data.ultimo_equipo || null;
                this.codigosDisponiblesTraspaso = data.codigos_disponibles || [];
            } catch (e) {
                console.error('Error al cargar último equipo traspasado:', e);
                this.ultimoEquipoTraspasado = null;
                this.codigosDisponiblesTraspaso = [];
            }
        },
        async cargarCodigosDisponiblesTraspaso() {
            if (!this.traspasoForm.destino || !this.filtros.empresa_id) {
                this.codigosDisponiblesTraspaso = [];
                return;
            }
            await this.cargarUltimoEquipoTraspasado();
        },
        async cargarCodigosDisponibles() {
            this.codigosCargando = true;
            try {
                // Incluir empresa_id si está seleccionada en los filtros
                const empresaId = this.filtros.empresa_id || '';
                const url = empresaId 
                    ? '{{ route('equipos.codigos-disponibles') }}?empresa_id=' + empresaId
                    : '{{ route('equipos.codigos-disponibles') }}';
                
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.codigosDisponibles = data.codigos || [];
            } catch (e) {
                console.error('Error al cargar códigos disponibles:', e);
                this.codigosDisponibles = [];
            } finally {
                this.codigosCargando = false;
            }
        },
        async usarCodigoDisponible(codigo) {
            try {
                // Si es un código de secuencia (no tiene ID), solo prellenar y abrir modal
                if (codigo.es_secuencia || !codigo.id) {
                    this.mostrarCodigosDisponibles = false;
                    // Prellenar el código en el formulario y abrir el modal
                    // Si el código tiene prefijo IN, extraer solo el número
                    let codigoMostrar = codigo.codigo;
                    if (codigo.codigo && codigo.codigo.toUpperCase().startsWith('IN')) {
                        const match = codigo.codigo.match(/^IN(\d+)$/i);
                        if (match) {
                            codigoMostrar = match[1];
                        }
                    }
                    this.form.codigo = codigoMostrar;
                    // Si hay empresa seleccionada en filtros, prellenarla también
                    if (this.filtros.empresa_id) {
                        this.form.empresa_id = this.filtros.empresa_id;
                    }
                    await this.openModal();
                    // Formatear el código después de abrir el modal
                    this.formatearCodigoIN();
                    return;
                }
                
                // Si es un código de la tabla, usar el método normal
                const formData = new FormData();
                formData.append('codigo_disponible_id', codigo.id);
                formData.append('_token', '{{ csrf_token() }}');
                
                const res = await fetch('{{ route('equipos.crear-con-codigo') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (res.ok && data.success) {
                    this.mostrarCodigosDisponibles = false;
                    
                    // Si hay datos del equipo original, prellenar todos los campos
                    if (data.datos_equipo) {
                        // Cargar sedes y bodegas primero si hay empresa (necesario para que los selects funcionen)
                        if (data.datos_equipo.empresa_id) {
                            this.form.empresa_id = data.datos_equipo.empresa_id;
                            await this.cargarSedesModal();
                            if (data.datos_equipo.sede_id) {
                                this.form.sede_id = data.datos_equipo.sede_id;
                                await this.cargarBodegasModal();
                            }
                        }
                        
                        // Prellenar todos los campos del formulario con los datos del equipo original
                        Object.keys(data.datos_equipo).forEach(key => {
                            if (data.datos_equipo[key] !== null && data.datos_equipo[key] !== undefined) {
                                // Convertir valores booleanos
                                if (typeof data.datos_equipo[key] === 'boolean') {
                                    this.form[key] = data.datos_equipo[key];
                                } else if (data.datos_equipo[key] !== '') {
                                    this.form[key] = data.datos_equipo[key];
                                }
                            }
                        });
                        
                        // Asegurar que el código esté prellenado (usando el código disponible, no el original)
                        // Si el código tiene prefijo IN, extraer solo el número
                        let codigoMostrar = data.codigo;
                        if (data.codigo && data.codigo.toUpperCase().startsWith('IN')) {
                            const match = data.codigo.match(/^IN(\d+)$/i);
                            if (match) {
                                codigoMostrar = match[1];
                            }
                        }
                        this.form.codigo = codigoMostrar;
                        
                        // Mantener el candado si estaba bloqueado (importante: el código disponible debe mantener el estado del candado)
                        // Si el equipo original tenía el código bloqueado, mantenerlo bloqueado
                        if (data.datos_equipo.codigo_bloqueado !== undefined) {
                            this.form.codigo_bloqueado = data.datos_equipo.codigo_bloqueado;
                        }
                    } else {
                        // Si no hay datos del equipo original, solo prellenar el código
                        // Si el código tiene prefijo IN, extraer solo el número
                        let codigoMostrar = data.codigo;
                        if (data.codigo && data.codigo.toUpperCase().startsWith('IN')) {
                            const match = data.codigo.match(/^IN(\d+)$/i);
                            if (match) {
                                codigoMostrar = match[1];
                            }
                        }
                        this.form.codigo = codigoMostrar;
                    // Si hay empresa seleccionada en filtros, prellenarla también
                    if (this.filtros.empresa_id) {
                        this.form.empresa_id = this.filtros.empresa_id;
                            await this.cargarSedesModal();
                    }
                    }
                    
                    await this.openModal();
                    // Formatear el código después de abrir el modal
                    this.formatearCodigoIN();
                } else {
                    alert(data.error || 'Error al seleccionar el código.');
                }
            } catch (e) {
                console.error('Error al seleccionar código:', e);
                alert('Error al seleccionar el código.');
            }
        },
        async cargarSedes() {
            if (!this.filtros.empresa_id) {
                this.sedesFiltradas = this.todasLasSedes;
                this.filtros.sede_id = '';
                this.bodegasFiltradas = this.todasLasBodegas;
                this.filtros.bodega_id = '';
                return;
            }
            try {
                const res = await fetch(`{{ url('/api/sedes') }}?empresa_id=${this.filtros.empresa_id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.sedesFiltradas = data.sedes || [];
                this.filtros.sede_id = '';
                this.bodegasFiltradas = [];
                this.filtros.bodega_id = '';
            } catch (e) {
                this.sedesFiltradas = [];
            }
        },
        async cargarBodegas() {
            if (!this.filtros.sede_id) {
                this.bodegasFiltradas = this.todasLasBodegas;
                this.filtros.bodega_id = '';
                return;
            }
            try {
                const res = await fetch(`{{ url('/api/bodegas') }}?sede_id=${this.filtros.sede_id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.bodegasFiltradas = data.bodegas || [];
                this.filtros.bodega_id = '';
            } catch (e) {
                this.bodegasFiltradas = [];
            }
        },
        limpiarFiltros() {
            this.filtros = {
                empresa_id: '',
                sede_id: '',
                bodega_id: ''
            };
            this.sedesFiltradas = this.todasLasSedes;
            this.bodegasFiltradas = this.todasLasBodegas;
            window.location.href = '{{ ($tab ?? "equipos") === "equipos" ? route("equipos.index") : (($tab ?? "equipos") === "debaja" ? route("equipos.debaja") : route("equipos.material-didactico")) }}';
        },
        async verificarPasswordCodigo() {
            if (!this.passwordCodigo) {
                alert('Por favor ingresa la contraseña');
                return;
            }
            
            try {
                const response = await fetch('{{ route("equipos.verificar-password-codigo") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        password: this.passwordCodigo
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.form.passwordVerificada = true;
                    this.form.codigo_bloqueado = false;
                    this.mostrarPasswordModal = false;
                    // Mantener la contraseña para enviarla con el formulario
                    alert('✅ Contraseña correcta. Puedes editar el código ahora.');
                } else {
                    alert('❌ Contraseña incorrecta. Intenta nuevamente.');
                    this.passwordCodigo = '';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al verificar la contraseña. Intenta nuevamente.');
            }
        },
        async submitForm(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            // Deshabilitar el botón de guardar para evitar doble envío
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.textContent : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Guardando...';
            }
            
            try {
                const url = form.action;
                const method = form.querySelector('input[name="_method"]')?.value || 'POST';
                
                const response = await fetch(url, {
                    method: method === 'PUT' ? 'POST' : 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                // Verificar el tipo de contenido de la respuesta
                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                
                let data = {};
                if (isJson) {
                    try {
                        data = await response.json();
                    } catch (e) {
                        console.error('Error al parsear JSON:', e);
                        // Si falla el parseo pero la respuesta fue exitosa, cerrar modal y recargar
                        if (response.ok || response.status === 200 || response.status === 201) {
                            alert('Equipo guardado correctamente.');
                            this.showModal = false;
                            setTimeout(() => {
                            window.location.reload();
                            }, 100);
                            return;
                        }
                        alert('Error al procesar la respuesta del servidor.');
                        return;
                    }
                } else {
                    // Si la respuesta no es JSON pero fue exitosa, cerrar modal y recargar
                    if (response.ok || response.status === 200 || response.status === 201 || response.status === 302) {
                        alert('Equipo guardado correctamente.');
                        this.showModal = false;
                        setTimeout(() => {
                        window.location.reload();
                        }, 100);
                        return;
                    }
                    // Si no es exitosa y no es JSON, intentar parsear como texto para ver el error
                    const text = await response.text();
                    console.error('Respuesta no JSON:', text);
                    alert('Error al procesar la respuesta del servidor.');
                    return;
                }
                
                // Manejar errores de validación (422)
                if (response.status === 422 && data.errors) {
                    let errorMsg = 'Errores de validación:\n';
                    Object.keys(data.errors).forEach(key => {
                        if (Array.isArray(data.errors[key])) {
                            errorMsg += `- ${data.errors[key][0]}\n`;
                        } else {
                            errorMsg += `- ${data.errors[key]}\n`;
                        }
                    });
                    alert(errorMsg);
                    return;
                }
                
                // Si la respuesta fue exitosa
                if (response.ok && (response.status === 200 || response.status === 201)) {
                    if (data.success !== false) {
                        // Construir mensaje de éxito
                        let mensaje = 'Equipo guardado correctamente.';
                        
                        // Si se usó contraseña, agregar información adicional
                        if (data.usarPassword || (this.form.passwordVerificada && !this.puedeDesbloquearCodigo)) {
                            this.form.codigo_bloqueado = true;
                            this.form.passwordVerificada = false;
                            this.passwordCodigo = '';
                            mensaje += '\n\nEl candado se cerró automáticamente.\nLa contraseña cambió automáticamente después de usarse.\n\nPara editar el código nuevamente, deberás ingresar la nueva contraseña establecida por el administrador.';
                        }
                        
                        // Si hay errores de archivos, agregarlos al mensaje (solo como advertencia)
                        if (data.errores_archivos && data.errores_archivos.length > 0) {
                            mensaje += '\n\nAdvertencia: Algunos archivos no se pudieron subir:\n' + data.errores_archivos.join('\n');
                            mensaje += '\n\nEl equipo se guardó correctamente sin esos archivos.';
                        }
                        
                        // Cerrar modal primero
                        this.showModal = false;
                        
                        // Recargar lista de códigos disponibles inmediatamente
                        await this.cargarCodigosDisponibles();
                        
                        // Mostrar mensaje y recargar automáticamente
                        alert(mensaje);
                        
                        // Recargar página automáticamente después de un breve delay
                        setTimeout(() => {
                        window.location.reload();
                        }, 200);
                        return;
                    }
                }
                
                // Si hay errores pero la respuesta fue exitosa (200/201), tratar como advertencias
                if (response.ok && (response.status === 200 || response.status === 201)) {
                    // Si llegamos aquí y hay errores, son advertencias, no errores bloqueantes
                if (data.errors) {
                        let errorMsg = 'Equipo guardado correctamente.\n\nAdvertencias:\n';
                    Object.keys(data.errors).forEach(key => {
                        if (Array.isArray(data.errors[key])) {
                            errorMsg += `- ${data.errors[key][0]}\n`;
                        } else {
                            errorMsg += `- ${data.errors[key]}\n`;
                        }
                    });
                    alert(errorMsg);
                        this.showModal = false;
                        await this.cargarCodigosDisponibles();
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                        return;
                } else if (data.message) {
                    alert(data.message);
                        this.showModal = false;
                        await this.cargarCodigosDisponibles();
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                        return;
                    }
                }
                
                // Mostrar errores si los hay (solo si NO fue exitoso)
                if (!response.ok && data.errors) {
                    let errorMsg = 'Errores de validación:\n';
                    Object.keys(data.errors).forEach(key => {
                        if (Array.isArray(data.errors[key])) {
                            errorMsg += `- ${data.errors[key][0]}\n`;
                } else {
                            errorMsg += `- ${data.errors[key]}\n`;
                        }
                    });
                    alert(errorMsg);
                } else if (!response.ok && data.message) {
                    alert('Error: ' + data.message);
                } else if (!response.ok) {
                    alert('Error al guardar el equipo. Intenta nuevamente.');
                }
            } catch (error) {
                console.error('Error:', error);
                // Si hay un error de red pero el equipo pudo haberse guardado, intentar recargar
                alert('Hubo un problema al procesar la solicitud. Si el equipo se guardó, aparecerá al recargar la página.');
                this.showModal = false;
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } finally {
                // Rehabilitar el botón de guardar
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            }
        },
        init() {
            // Cargar sedes y bodegas filtradas al inicio si hay empresa/sede seleccionada
            if (this.filtros.empresa_id) {
                this.cargarSedes();
            }
            if (this.filtros.sede_id) {
                this.cargarBodegas();
            }
        },
    };
}
</script>
@endsection

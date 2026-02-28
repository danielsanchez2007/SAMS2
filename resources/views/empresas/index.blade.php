@extends('layouts.app')

@section('title', 'Empresas')

@section('content')

<div class="space-y-8" x-data="empresasApp()">

    <!-- Pestañas -->
    <div class="border-b border-slate-800/60">
        <nav class="flex gap-8 overflow-x-auto pb-1">
            <a href="{{ route('empresas.index', ['tab' => 'empresa', 'q' => $search]) }}"
               class="pb-4 px-2 text-sm font-medium border-b-2 transition-all duration-200 whitespace-nowrap
                      {{ $tab === 'empresa' 
                         ? 'border-indigo-500 text-indigo-400 font-semibold' 
                         : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                EMPRESAS
            </a>

            <a href="{{ route('empresas.index', ['tab' => 'sede', 'q' => $search, 'per_page' => $perPage ?? 10]) }}"
               class="pb-4 px-2 text-sm font-medium border-b-2 transition-all duration-200 whitespace-nowrap
                      {{ $tab === 'sede' 
                         ? 'border-indigo-500 text-indigo-400 font-semibold' 
                         : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                SEDES
            </a>

            <a href="{{ route('empresas.index', ['tab' => 'bodega', 'q' => $search]) }}"
               class="pb-4 px-2 text-sm font-medium border-b-2 transition-all duration-200 whitespace-nowrap
                      {{ $tab === 'bodega' 
                         ? 'border-indigo-500 text-indigo-400 font-semibold' 
                         : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                BODEGAS
            </a>
        </nav>
    </div>

    <!-- Barra: filtro automático, acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('empresas.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Buscar..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('empresas.index', ['tab' => $tab, 'per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
                @endif
            </div>
            <span class="text-sm text-slate-500">Mostrar</span>
            <select name="per_page" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20"
                    @change="$refs.filterForm.submit()">
                <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-slate-500">por página</span>
        </form>
        <div class="flex flex-wrap gap-3">
            @if($tab === 'empresa')
                <button type="button" @click="openModalEmpresa()" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar Empresa
                </button>
            @elseif($tab === 'sede')
                <button type="button" @click="openModalSede()" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar Sede
                </button>
            @else
                <button type="button" @click="openModalBodega()" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar Bodega
                </button>
            @endif

                    </div>
    </div>

    <!-- Tabla según pestaña -->
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        @if($tab === 'empresa')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-300">
                    <thead class="bg-slate-800/90">
                        <tr>
                            <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre / País</th>
                            <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                        @forelse($empresas as $i => $e)
                            <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                                <td class="py-4 px-6 font-medium text-slate-100">{{ $e->nombre }} {{ $e->pais ? ' / ' . $e->pais : '' }}</td>
                                <td class="py-4 px-8 text-right flex items-center justify-end gap-2">
                                    <button type="button" @click="editEmpresa({{ json_encode($e) }})" 
                                            class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                            title="Editar empresa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('empresas.destroy.empresa', $e) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar esta empresa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200"
                                                title="Eliminar empresa">
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
                                    No hay empresas registradas todavía.<br>
                                    <span class="text-slate-400">Haz clic en "Agregar Empresa" para comenzar.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @php $paginator = $empresas; $label = 'empresas'; @endphp
            @if(method_exists($paginator, 'hasPages') && ($paginator->hasPages() || $paginator->total() > 0))
            <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/50 flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-slate-400">Mostrando {{ $paginator->firstItem() ?? 0 }} a {{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} {{ $label }}</p>
                <div class="flex items-center gap-2">
                    @if($paginator->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg bg-slate-800/60 text-slate-500 text-sm cursor-not-allowed">Anterior</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-slate-700/60 text-slate-300 hover:bg-slate-600 text-sm">Anterior</a>
                    @endif
                    <span class="text-sm text-slate-400">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
                    @if($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-slate-700/60 text-slate-300 hover:bg-slate-600 text-sm">Siguiente</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg bg-slate-800/60 text-slate-500 text-sm cursor-not-allowed">Siguiente</span>
                    @endif
                </div>
            </div>
            @endif
        @elseif($tab === 'sede')
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-300">
                    <thead class="bg-slate-800/90">
                        <tr>
                            <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre / País / Municipio / Departamento</th>
                            <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Empresa</th>
                            <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                        @forelse($sedes as $i => $s)
                            <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                                <td class="py-4 px-6 font-medium text-slate-100">
                                    {{ $s->nombre }} 
                                    {{ $s->pais ? ' / ' . $s->pais : '' }} 
                                    {{ $s->municipio ? ' / ' . $s->municipio : '' }} 
                                    {{ $s->departamento ? ' / ' . $s->departamento : '' }}
                                </td>
                                <td class="py-4 px-6 text-slate-300">{{ $s->empresa->nombre ?? '—' }}</td>
                                <td class="py-4 px-8 text-right flex items-center justify-end gap-2">
                                    <button type="button" @click="editSede({{ json_encode($s) }})" 
                                            class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                            title="Editar sede">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('empresas.destroy.sede', $s) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar esta sede?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200"
                                                title="Eliminar sede">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 px-6 text-center text-slate-500 text-lg italic">
                                    No hay sedes registradas todavía.<br>
                                    <span class="text-slate-400">Haz clic en "Agregar Sede" para comenzar.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-300">
                    <thead class="bg-slate-800/90">
                        <tr>
                            <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre</th>
                            <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Sede (ubicación)</th>
                            <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                        @forelse($bodegas as $i => $b)
                            @php
                                $ubicacion = $b->sede ? trim(implode(', ', array_filter([$b->sede->municipio, $b->sede->departamento, $b->sede->pais]))) : '';
                            @endphp
                            <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                                <td class="py-4 px-6 font-medium text-slate-100">{{ $b->nombre }}</td>
                                <td class="py-4 px-6 text-slate-300">
                                    {{ $b->sede->nombre ?? '—' }} 
                                    {{ $ubicacion ? '(' . $ubicacion . ')' : '' }}
                                </td>
                                <td class="py-4 px-8 text-right flex items-center justify-end gap-2">
                                    <button type="button" @click="editBodega({{ json_encode($b) }})" 
                                            class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                            title="Editar bodega">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('empresas.destroy.bodega', $b) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar esta bodega?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200"
                                                title="Eliminar bodega">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 px-6 text-center text-slate-500 text-lg italic">
                                    No hay bodegas registradas todavía.<br>
                                    <span class="text-slate-400">Haz clic en "Agregar Bodega" para comenzar.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @php $paginator = $bodegas; $label = 'bodegas'; @endphp
            @if(method_exists($paginator, 'hasPages') && ($paginator->hasPages() || $paginator->total() > 0))
            <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/50 flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-slate-400">Mostrando {{ $paginator->firstItem() ?? 0 }} a {{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} {{ $label }}</p>
                <div class="flex items-center gap-2">
                    @if($paginator->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg bg-slate-800/60 text-slate-500 text-sm cursor-not-allowed">Anterior</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-slate-700/60 text-slate-300 hover:bg-slate-600 text-sm">Anterior</a>
                    @endif
                    <span class="text-sm text-slate-400">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
                    @if($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-slate-700/60 text-slate-300 hover:bg-slate-600 text-sm">Siguiente</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg bg-slate-800/60 text-slate-500 text-sm cursor-not-allowed">Siguiente</span>
                    @endif
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Modales -->
    @include('empresas.modals.empresa')
    @include('empresas.modals.sede')
    @include('empresas.modals.bodega')

</div>

<script>
// Script Alpine (sin cambios)
function empresasApp() {
    return {
        modalEmpresa: false,
        modalSede: false,
        modalBodega: false,
        formEmpresa: { id: null, nombre: '', pais: '', pais_otro: '' },
        formSede: { id: null, empresa_id: '', nombre: '', pais: '', municipio: '', departamento: '', pais_otro: '', departamento_otro: '', municipio_otro: '' },
        formBodega: { id: null, sede_id: '', nombre: '' },
        departamentosPorPais: @json($departamentosPorPais ?? []),
        municipiosPorDepartamento: @json($municipiosPorDepartamento ?? []),
        empresas: @json(isset($empresas) && method_exists($empresas, 'items') ? $empresas->items() : ($empresas ?? [])),
        sedes: @json(isset($sedes) && method_exists($sedes, 'items') ? $sedes->items() : ($sedes ?? [])),
        paises: @json($paises ?? []),
        openModalEmpresa() {
            this.formEmpresa = { id: null, nombre: '', pais: '', pais_otro: '' };
            this.modalEmpresa = true;
        },
        editEmpresa(e) {
            var pais = e.pais || '';
            var paisOtro = this.paises.includes(pais) ? '' : pais;
            this.formEmpresa = { id: e.id, nombre: e.nombre, pais: paisOtro ? 'Otro' : pais, pais_otro: paisOtro };
            this.modalEmpresa = true;
        },
        openModalSede() {
            this.formSede = { id: null, empresa_id: '', nombre: '', pais: '', municipio: '', departamento: '', pais_otro: '', departamento_otro: '', municipio_otro: '' };
            this.modalSede = true;
        },
        editSede(s) {
            var pais = s.pais || '';
            var paisOtro = this.paises.includes(pais) ? '' : pais;
            var deptList = this.departamentosPorPais[paisOtro ? 'Otro' : pais] || [];
            var munList = this.municipiosPorDepartamento[s.departamento] || [];
            var departamentoOtro = (deptList.length && !deptList.includes(s.departamento)) ? (s.departamento || '') : '';
            var municipioOtro = (munList.length && !munList.includes(s.municipio)) ? (s.municipio || '') : '';
            this.formSede = {
                id: s.id, empresa_id: s.empresa_id, nombre: s.nombre,
                pais: paisOtro ? 'Otro' : pais, pais_otro: paisOtro,
                departamento: departamentoOtro ? 'Otro' : (s.departamento || ''), departamento_otro: departamentoOtro,
                municipio: municipioOtro ? 'Otro' : (s.municipio || ''), municipio_otro: municipioOtro
            };
            this.modalSede = true;
        },
        openModalBodega() {
            this.formBodega = { id: null, sede_id: '', nombre: '' };
            this.modalBodega = true;
        },
        editBodega(b) {
            this.formBodega = { id: b.id, sede_id: b.sede_id, nombre: b.nombre };
            this.modalBodega = true;
        }
    };
}
</script>

@endsection
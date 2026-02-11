@extends('layouts.app')

@section('title', 'Asignar')

@section('content')
@php
    $placeholderImg = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2256%22 height=%2256%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23475569%22 stroke-width=%221.5%22%3E%3Crect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/%3E%3Ccircle cx=%228.5%22 cy=%228.5%22 r=%221.5%22/%3E%3Cpath d=%22m21 15-5-5L5 21%22/%3E%3C/svg%3E';
    $reasignarUsuarioId = $reasignarUsuarioId ?? '';
    $userReasignar = $reasignarUsuarioId ? $usuarios->firstWhere('id', (int) $reasignarUsuarioId) : null;
    $nombreReasignar = $userReasignar ? ($userReasignar->nombre . ' ' . $userReasignar->apellidos) : null;
@endphp
<div class="space-y-6" x-data="{
    modalEditar: false, editId: null, editUsuarioId: '', editCodigo: '',
    modalReasignar: false, reasignarUsuarioId: '', reasignarUsuarioNombre: '',
    modalVer: false, viewData: {}
}">
    <h1 class="text-3xl font-extrabold tracking-tight">
        <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400 bg-clip-text text-transparent">Asignar</span>
    </h1>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-300 px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Crear asignación --}}
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border border-slate-800/60 p-6" @if($reasignarUsuarioId) id="form-crear-asignacion" @endif>
        @if($nombreReasignar)
            <p class="text-sm text-indigo-300 mb-3">Asignando otro ítem a <strong>{{ $nombreReasignar }}</strong>. El mismo usuario puede tener varios ítems.</p>
        @endif
        <h2 class="text-lg font-semibold text-slate-200 mb-4">Crear asignación</h2>
        <form method="POST" action="{{ route('asignar.store') }}" class="flex flex-wrap items-end gap-4">
            @csrf
            <div class="min-w-[200px]">
                <label for="equipo_id" class="block text-sm font-medium text-slate-400 mb-1">Ítem (equipo)</label>
                <select name="equipo_id" id="equipo_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-700/70 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">— Seleccionar ítem —</option>
                    @foreach($equiposDisponibles as $eq)
                        <option value="{{ $eq->id }}" {{ (old('equipo_id') ?: $equipoId) == $eq->id ? 'selected' : '' }}>{{ $eq->codigo }} — {{ \Illuminate\Support\Str::limit($eq->descripcion, 40) }}</option>
                    @endforeach
                </select>
                @if($equiposDisponibles->isEmpty())
                    <p class="text-xs text-slate-500 mt-1">No hay ítems disponibles (todos están asignados).</p>
                @endif
            </div>
            <div class="min-w-[200px]">
                <label for="usuario_id" class="block text-sm font-medium text-slate-400 mb-1">Usuario a cargo</label>
                <select name="usuario_id" id="usuario_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-700/70 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">— Seleccionar usuario —</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ (old('usuario_id') ?: $reasignarUsuarioId) == $u->id ? 'selected' : '' }}>{{ $u->nombre }} {{ $u->apellidos }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold text-sm transition-all">
                Crear asignación
            </button>
        </form>
    </div>

    {{-- Filtros y tabla --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('asignar.index') }}" class="flex flex-wrap items-center gap-3" x-ref="filterForm">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Usuario, ítem, código..."
                   class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-56 text-sm"
                   @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
            <label class="text-sm font-medium text-slate-400">Usuario:</label>
            <select name="usuario_id" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" @change="$refs.filterForm.submit()">
                <option value="">Todos</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id }}" {{ $usuarioId == $u->id ? 'selected' : '' }}>{{ $u->nombre }} {{ $u->apellidos }}</option>
                @endforeach
            </select>
            <label class="text-sm font-medium text-slate-400">Ítem:</label>
            <select name="equipo_id" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" @change="$refs.filterForm.submit()">
                <option value="">Todos</option>
                @foreach($equiposAsignadosParaFiltro ?? [] as $eq)
                    <option value="{{ $eq->id }}" {{ $equipoId == $eq->id ? 'selected' : '' }}>{{ $eq->codigo }}</option>
                @endforeach
            </select>
            <span class="text-sm text-slate-500">Mostrar</span>
            <select name="per_page" class="px-3 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20" @change="$refs.filterForm.submit()">
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-slate-500">por página</span>
        </form>
    </div>

    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border border-slate-800/60 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300 min-w-[700px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs w-20">Foto usuario</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs w-20">Imagen ítem</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Usuario a cargo</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Ítem (código)</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Descripción</th>
                        <th class="text-left py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs">Fecha asignación</th>
                        <th class="text-right py-4 px-5 font-semibold text-slate-400 uppercase tracking-wider text-xs w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($asignaciones as $i => $a)
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-3 px-5">
                                @php
                                    $u = $a->usuario;
                                    if ($u->imagen_usuario) {
                                        $imagenPath = $u->imagen_usuario;
                                        $imgU = str_starts_with($imagenPath, 'http') 
                                            ? $imagenPath 
                                            : (str_starts_with($imagenPath, 'storage/') 
                                                ? asset('storage/' . str_replace('storage/', '', $imagenPath)) 
                                                : asset($imagenPath));
                                    } else {
                                        $imgU = $placeholderImg;
                                    }
                                @endphp
                                <img src="{{ $imgU }}" alt="{{ $u->nombre }} {{ $u->apellidos }}" 
                                     class="w-12 h-12 rounded-full object-cover border-2 border-slate-600 bg-slate-800 shadow-lg" 
                                     onerror="this.src='{{ $placeholderImg }}';">
                            </td>
                            <td class="py-3 px-5">
                                @php $imgE = $a->equipo->imagen_url ?? $placeholderImg; @endphp
                                <img src="{{ $imgE }}" alt="{{ $a->equipo->codigo }}" class="w-12 h-12 rounded-lg object-cover border border-slate-700/60 bg-slate-800" loading="lazy" onerror="this.src='{{ $placeholderImg }}';">
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-100">{{ $a->usuario->nombre }} {{ $a->usuario->apellidos }}</td>
                            <td class="py-4 px-5 font-mono text-slate-200">{{ $a->equipo->codigo }}</td>
                            <td class="py-4 px-5 max-w-xs truncate text-slate-300">{{ $a->equipo->descripcion }}</td>
                            <td class="py-4 px-5 text-slate-400">{{ $a->fecha_asignacion?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click="modalReasignar = true; reasignarUsuarioId = '{{ $a->usuario_id }}'; reasignarUsuarioNombre = '{{ addslashes($a->usuario->nombre . ' ' . $a->usuario->apellidos) }}'" class="p-2 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-colors" title="Re-asignar: asignar más ítems a este usuario">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                    @php
                                        $usuarioImagen = $placeholderImg;
                                        if ($a->usuario->imagen_usuario) {
                                            $imagenPath = $a->usuario->imagen_usuario;
                                            $usuarioImagen = str_starts_with($imagenPath, 'http') 
                                                ? $imagenPath 
                                                : (str_starts_with($imagenPath, 'storage/') 
                                                    ? asset('storage/' . str_replace('storage/', '', $imagenPath)) 
                                                    : asset($imagenPath));
                                        }
                                        $viewPayload = [
                                            'equipo_id' => $a->equipo_id,
                                            'equipo_tiene_formato' => $a->equipo->tipoEquipo && $a->equipo->tipoEquipo->tieneFormato(),
                                            'usuario_nombre' => $a->usuario->nombre . ' ' . $a->usuario->apellidos,
                                            'usuario_imagen' => $usuarioImagen,
                                            'usuario_correo' => $a->usuario->correo_electronico ?? $a->usuario->correo_corporativo ?? '—',
                                            'equipo_codigo' => $a->equipo->codigo,
                                            'equipo_descripcion' => $a->equipo->descripcion,
                                            'equipo_imagen' => $a->equipo->imagen_url ?? $placeholderImg,
                                            'equipo_tipo' => $a->equipo->tipoItem->nombre ?? '—',
                                            'equipo_estado' => $a->equipo->estadoItem->nombre ?? '—',
                                            'equipo_marca' => $a->equipo->marca ?? '—',
                                            'equipo_modelo' => $a->equipo->modelo ?? '—',
                                            'fecha' => $a->fecha_asignacion?->format('d/m/Y H:i') ?? '—',
                                        ];
                                    @endphp
                                    <button type="button" data-view-payload="{{ base64_encode(json_encode($viewPayload)) }}" @click="viewData = JSON.parse(atob($event.currentTarget.dataset.viewPayload)); modalVer = true" class="p-2 text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/30 rounded-lg transition-colors" title="Ver detalle">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button type="button" @click="modalEditar = true; editId = {{ $a->id }}; editUsuarioId = '{{ $a->usuario_id }}'; editCodigo = '{{ addslashes($a->equipo->codigo) }}'" class="p-2 text-amber-400 hover:text-amber-300 hover:bg-amber-950/30 rounded-lg transition-colors" title="Editar usuario a cargo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form action="{{ route('asignar.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('¿Quitar esta asignación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-colors" title="Quitar asignación">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-5 text-center text-slate-500">No hay asignaciones. Crea una arriba o ajusta los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($asignaciones, 'hasPages') && ($asignaciones->hasPages() || $asignaciones->total() > 0))
        <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-t border-slate-800/60 bg-slate-900/50">
            <p class="text-sm text-slate-400">
                Mostrando {{ $asignaciones->firstItem() ?? 0 }} a {{ $asignaciones->lastItem() ?? 0 }} de {{ $asignaciones->total() }} asignaciones
            </p>
            <div class="flex items-center gap-2">
                @if ($asignaciones->onFirstPage())
                    <span class="px-3 py-2 rounded-lg border border-slate-700 text-sm text-slate-500 cursor-not-allowed">Anterior</span>
                @else
                    <a href="{{ $asignaciones->previousPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-800 transition-colors">Anterior</a>
                @endif
                <span class="text-sm text-slate-400 px-2">Pág. {{ $asignaciones->currentPage() }} de {{ $asignaciones->lastPage() }}</span>
                @if ($asignaciones->hasMorePages())
                    <a href="{{ $asignaciones->nextPageUrl() }}" class="px-3 py-2 rounded-lg border border-slate-700 text-sm font-medium text-slate-300 hover:bg-slate-800 transition-colors">Siguiente</a>
                @else
                    <span class="px-3 py-2 rounded-lg border border-slate-700 text-sm text-slate-500 cursor-not-allowed">Siguiente</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Re-asignar: seleccionar más ítems para el mismo usuario --}}
    <div x-show="modalReasignar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div x-show="modalReasignar" x-transition class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col border border-slate-700/50 my-4" @click.stop>
            <div class="p-6 border-b border-slate-700/50 shrink-0">
                <h3 class="text-lg font-semibold text-slate-200">Asignar más ítems</h3>
                <p class="text-sm text-slate-400 mt-1">Usuario: <span class="text-indigo-300 font-medium" x-text="reasignarUsuarioNombre"></span>. Marca los ítems que quieras asignarle.</p>
            </div>
            <form method="POST" action="{{ route('asignar.store.multi') }}" class="flex flex-col flex-1 min-h-0">
                @csrf
                <input type="hidden" name="usuario_id" :value="reasignarUsuarioId">
                <div class="p-4 overflow-y-auto flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse($equiposDisponibles as $eq)
                        @php $imgEq = isset($eq->imagen_url) ? $eq->imagen_url : (\App\Http\Controllers\EquipoController::getImagenUrl($eq) ?? $placeholderImg); @endphp
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-700/60 bg-slate-800/50 hover:bg-slate-800/80 cursor-pointer transition-colors">
                            <input type="checkbox" name="equipo_ids[]" value="{{ $eq->id }}" class="rounded border-slate-600 text-indigo-600 focus:ring-indigo-500/20">
                            <img src="{{ $imgEq }}" alt="" class="w-14 h-14 rounded-lg object-cover border border-slate-600 shrink-0" onerror="this.src='{{ $placeholderImg }}'">
                            <div class="min-w-0">
                                <span class="font-mono text-slate-200 text-sm block">{{ $eq->codigo }}</span>
                                <span class="text-slate-400 text-xs truncate block">{{ \Illuminate\Support\Str::limit($eq->descripcion, 35) }}</span>
                            </div>
                        </label>
                    @empty
                        <p class="col-span-2 text-slate-500 text-sm py-4">No hay ítems disponibles para asignar (todos están asignados).</p>
                    @endforelse
                </div>
                <div class="p-4 border-t border-slate-700/50 flex gap-3 justify-end shrink-0">
                    <button type="button" @click="modalReasignar = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 text-sm font-medium hover:bg-slate-800 transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold transition">Asignar seleccionados</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Ver: detalle completo de la asignación --}}
    <div x-show="modalVer" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div x-show="modalVer" x-transition class="bg-slate-900 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-slate-700/50 my-4" @click.stop>
            <div class="p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-200">Detalle de la asignación</h3>
                    <button type="button" @click="modalVer = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">✕</button>
                </div>
                <template x-if="Object.keys(viewData).length">
                    <div class="space-y-5">
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                            <img :src="viewData.usuario_imagen" alt="" class="w-16 h-16 rounded-full object-cover border-2 border-slate-600">
                            <div>
                                <p class="font-semibold text-slate-100" x-text="viewData.usuario_nombre"></p>
                                <p class="text-sm text-slate-400">Correo: <span x-text="viewData.usuario_correo"></span></p>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Ítem asignado</p>
                            <img :src="viewData.equipo_imagen" alt="" class="w-full h-40 object-contain rounded-lg bg-slate-900/80 border border-slate-700 mb-3">
                            <p class="font-mono text-indigo-300 font-semibold" x-text="viewData.equipo_codigo"></p>
                            <p class="text-slate-300 text-sm mt-1" x-text="viewData.equipo_descripcion"></p>
                            <dl class="grid grid-cols-2 gap-2 mt-3 text-sm">
                                <dt class="text-slate-500">Tipo</dt><dd class="text-slate-300" x-text="viewData.equipo_tipo"></dd>
                                <dt class="text-slate-500">Estado</dt><dd class="text-slate-300" x-text="viewData.equipo_estado"></dd>
                                <dt class="text-slate-500">Marca</dt><dd class="text-slate-300" x-text="viewData.equipo_marca"></dd>
                                <dt class="text-slate-500">Modelo</dt><dd class="text-slate-300" x-text="viewData.equipo_modelo"></dd>
                            </dl>
                        </div>
                        <p class="text-sm text-slate-400">Fecha de asignación: <span class="text-slate-200 font-medium" x-text="viewData.fecha"></span></p>
                        <div class="pt-2" x-show="viewData.equipo_id && viewData.equipo_tiene_formato">
                            <a :href="'/equipos/' + viewData.equipo_id + '/hoja-vida'" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600/80 hover:bg-emerald-600 text-white text-sm font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Descargar hoja de vida (PDF)
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Modal Editar asignación --}}
    <div x-show="modalEditar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div x-show="modalEditar" x-transition class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-700/50" @click.stop>
            <h3 class="text-lg font-semibold text-slate-200 mb-2">Editar asignación</h3>
            <p class="text-sm text-slate-400 mb-4">Ítem: <span class="font-mono text-slate-200" x-text="editCodigo"></span>. Cambia el usuario a cargo.</p>
            <form :action="'{{ url('/asignar') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="usuario_id" :value="editUsuarioId">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Usuario a cargo</label>
                    <select x-model="editUsuarioId" required class="w-full px-4 py-2.5 rounded-xl border border-slate-700/70 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-indigo-500/20">
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->nombre }} {{ $u->apellidos }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="modalEditar = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 text-sm font-medium hover:bg-slate-800 transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

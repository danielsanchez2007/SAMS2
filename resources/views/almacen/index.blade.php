@extends('layouts.app')

@section('title', 'Almacén')

@php
    $equiposParaSelector = $equipos->map(function($eq) {
        $img = $eq->imagen_general ? asset('storage/' . $eq->imagen_general) : url('/equipos/' . $eq->id . '/imagen');
        return ['id' => $eq->id, 'codigo' => $eq->codigo, 'descripcion' => Str::limit($eq->descripcion ?? '', 40), 'imagen_url' => $img];
    })->values()->all();
@endphp

@section('content')
<div class="space-y-6" x-data="{ tab: 'imagenes' }">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-100">Almacén</h1>
        <a href="{{ route('equipos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 text-sm font-medium">
            ← Volver a Equipos
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-emerald-900/40 border border-emerald-700/50 text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-rose-900/40 border border-rose-700/50 text-rose-300 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Pestañas Imágenes / Archivos --}}
    <div class="flex gap-2 border-b border-slate-700 pb-2">
        <button type="button" @click="tab = 'imagenes'"
                class="px-5 py-2.5 rounded-lg text-sm font-semibold transition"
                :class="tab === 'imagenes' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'">
            Imágenes
        </button>
        <button type="button" @click="tab = 'archivos'"
                class="px-5 py-2.5 rounded-lg text-sm font-semibold transition"
                :class="tab === 'archivos' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'">
            Archivos
        </button>
    </div>

    {{-- Sección Imágenes --}}
    <div x-show="tab === 'imagenes'" x-cloak class="space-y-6">
        <div class="bg-slate-900/70 rounded-xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold text-indigo-400 mb-4">Crear etiqueta (imagen)</h2>
            <p class="text-sm text-slate-400 mb-3">Cada etiqueta puede tener una imagen. Las etiquetas se ven para todos los ítems.</p>
            <form method="POST" action="{{ route('almacen.store.etiqueta') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="tipo" value="imagen">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Nombre etiqueta</label>
                    <input type="text" name="nombre" required maxlength="255"
                           class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 w-56">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">Crear etiqueta</button>
            </form>
        </div>
        <div class="bg-slate-900/70 rounded-xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold text-indigo-400 mb-4">Subir imagen a etiqueta</h2>
            <form method="POST" action="{{ route('almacen.store.imagen') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Etiqueta</label>
                    <select name="etiqueta_id" required class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 w-56">
                        <option value="">— Seleccionar —</option>
                        @foreach($etiquetasImagen as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative" x-data="equipoDropdown(@js($equiposParaSelector))" @click.away="open = false">
                    <label class="block text-sm text-slate-400 mb-1">Equipo (al que pertenece)</label>
                    <input type="hidden" name="equipo_id" :value="selected ? selected.id : ''">
                    <button type="button" @click="open = !open"
                            class="w-72 min-h-[2.5rem] px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 text-left flex items-center gap-3">
                        <template x-if="selected">
                            <span class="flex items-center gap-3 flex-1 min-w-0">
                                <img :src="selected.imagen_url" alt="" class="w-9 h-9 rounded object-cover flex-shrink-0 bg-slate-700"
                                     loading="lazy" @@error="$event.target.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2236%22 height=%2236%22 viewBox=%220 0 24 24%22 fill=%22%23475569%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E'">
                                <span class="truncate font-medium" x-text="selected.codigo"></span>
                                <span class="truncate text-slate-400 text-xs" x-text="selected.descripcion || ''"></span>
                            </span>
                        </template>
                        <template x-if="!selected">
                            <span class="text-slate-400">— Ninguno / Todos —</span>
                        </template>
                        <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute left-0 top-full mt-1 w-80 max-h-72 overflow-hidden rounded-xl border border-slate-600 bg-slate-800 shadow-xl z-20 flex flex-col">
                        <input type="text" x-model="query" placeholder="Buscar por código o descripción..."
                               class="m-2 px-3 py-2 rounded-lg border border-slate-600 bg-slate-900 text-slate-100 text-sm placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <div class="overflow-y-auto flex-1 p-1 max-h-56">
                            <button type="button" @click="selected = null; open = false"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left hover:bg-slate-700/50 transition text-slate-400">
                                <span class="w-9 h-9 rounded bg-slate-700 flex-shrink-0 flex items-center justify-center text-xs">—</span>
                                <span>Ninguno / Todos</span>
                            </button>
                            <template x-for="eq in filteredEquipos" :key="eq.id">
                                <button type="button" @click="selected = eq; open = false"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left hover:bg-slate-700/50 transition">
                                    <img :src="eq.imagen_url" alt="" class="w-9 h-9 rounded object-cover flex-shrink-0 bg-slate-700"
                                         loading="lazy" @@error="$event.target.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2236%22 height=%2236%22 viewBox=%220 0 24 24%22 fill=%22%23475569%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E'">
                                    <div class="min-w-0 flex-1">
                                        <span class="font-medium text-slate-200 block truncate" x-text="eq.codigo"></span>
                                        <span class="text-xs text-slate-400 block truncate" x-text="eq.descripcion || ''"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" required
                           class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-slate-700 file:text-slate-200">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">Guardar imagen</button>
            </form>
            <p class="text-xs text-slate-500 mt-2">Si la etiqueta ya tiene imagen para ese equipo, se reemplazará.</p>
        </div>
        
        {{-- Tabla de todas las imágenes al final --}}
        <div class="bg-slate-900/70 rounded-xl border border-slate-800 p-6" 
             x-data="{ 
                 filtroEquipo: '',
                 mostrarFila(equipoId) {
                     if (!this.filtroEquipo) return true;
                     if (this.filtroEquipo === 'sin_equipo') return !equipoId || equipoId === null;
                     return String(this.filtroEquipo) === String(equipoId);
                 }
             }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-indigo-400">Todas las Imágenes</h2>
                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-400">Filtrar por equipo:</label>
                    <select x-model="filtroEquipo" class="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 text-sm">
                        <option value="">— Todos los equipos —</option>
                        <option value="sin_equipo">— Sin equipo asignado —</option>
                        @foreach($equipos as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->codigo }} — {{ Str::limit($eq->descripcion, 30) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Imagen</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Etiqueta</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Equipo</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Nombre Original</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($imagenes ?? [] as $img)
                            @php
                                $equipoId = $img->equipo->id ?? null;
                                $imgUrl = $img->ruta_url ?? (str_starts_with($img->ruta, 'storage/') 
                                    ? asset('storage/' . str_replace('storage/', '', $img->ruta)) 
                                    : asset($img->ruta));
                                $esImagenEquipo = isset($img->tipo) && ($img->tipo === 'equipo_general' || $img->tipo === 'equipo_etiqueta');
                            @endphp
                            <tr x-show="mostrarFila({{ $equipoId ?? 'null' }})" 
                                class="border-b border-slate-800 hover:bg-slate-800/40">
                                <td class="py-3 px-4">
                                    <img src="{{ $imgUrl }}" 
                                         alt="{{ $img->etiqueta?->nombre ?? ($img->nombre_original ?? 'Imagen') }}" 
                                         class="w-20 h-20 object-cover rounded border border-slate-700 cursor-pointer hover:border-indigo-500 transition"
                                         onclick="window.open('{{ $imgUrl }}', '_blank')"
                                         title="Click para ver en tamaño completo">
                                </td>
                                <td class="py-3 px-4">
                                    @if($esImagenEquipo)
                                        <span class="text-xs text-indigo-400 font-medium">{{ $img->nombre_original }}</span>
                                    @else
                                        <span class="font-medium text-slate-200">{{ $img->etiqueta?->nombre ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($img->equipo)
                                        <div>
                                            <span class="font-medium text-slate-200">{{ $img->equipo->codigo }}</span>
                                            <p class="text-xs text-slate-400">{{ Str::limit($img->equipo->descripcion ?? '', 30) }}</p>
                                        </div>
                                    @else
                                        <span class="text-slate-500">— Todos —</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-slate-300">{{ $img->nombre_original ?? '—' }}</span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ $imgUrl }}" 
                                           download
                                           class="text-indigo-400 hover:text-indigo-300"
                                           title="Descargar">⬇ Descargar</a>
                                        @if(!$esImagenEquipo)
                                            <form method="POST" action="{{ route('almacen.destroy.imagen', $img) }}" 
                                                  class="inline" 
                                                  onsubmit="return confirm('¿Eliminar esta imagen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-rose-400 hover:text-rose-300"
                                                        title="Eliminar">🗑 Eliminar</button>
                                            </form>
                                        @else
                                            <span class="text-slate-500 text-xs">Editar desde Equipos</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-4 text-center text-slate-500">
                                    No hay imágenes. Crea una etiqueta y sube una imagen arriba.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sección Archivos --}}
    <div x-show="tab === 'archivos'" x-cloak class="space-y-6">
        <div class="bg-slate-900/70 rounded-xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold text-indigo-400 mb-4">Crear etiqueta (archivos)</h2>
            <form method="POST" action="{{ route('almacen.store.etiqueta') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="tipo" value="archivo">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Nombre etiqueta</label>
                    <input type="text" name="nombre" required maxlength="255"
                           class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 w-56">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">Crear etiqueta</button>
            </form>
        </div>
        <div class="bg-slate-900/70 rounded-xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold text-indigo-400 mb-4">Subir archivo</h2>
            <p class="text-sm text-slate-400 mb-3">Indica un nombre y sube cualquier tipo de archivo. Opcionalmente asigna etiqueta y equipo al que pertenece.</p>
            <form method="POST" action="{{ route('almacen.store.archivo') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Nombre</label>
                    <input type="text" name="nombre" required maxlength="255" placeholder="Ej: Manual de uso"
                           class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 w-56">
                </div>
                <div class="relative" x-data="equipoDropdown(@js($equiposParaSelector))" @click.away="open = false">
                    <label class="block text-sm text-slate-400 mb-1">Equipo (al que pertenece)</label>
                    <input type="hidden" name="equipo_id" :value="selected ? selected.id : ''">
                    <button type="button" @click="open = !open"
                            class="w-72 min-h-[2.5rem] px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 text-left flex items-center gap-3">
                        <template x-if="selected">
                            <span class="flex items-center gap-3 flex-1 min-w-0">
                                <img :src="selected.imagen_url" alt="" class="w-9 h-9 rounded object-cover flex-shrink-0 bg-slate-700"
                                     loading="lazy" @@error="$event.target.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2236%22 height=%2236%22 viewBox=%220 0 24 24%22 fill=%22%23475569%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E'">
                                <span class="truncate font-medium" x-text="selected.codigo"></span>
                                <span class="truncate text-slate-400 text-xs" x-text="selected.descripcion || ''"></span>
                            </span>
                        </template>
                        <template x-if="!selected">
                            <span class="text-slate-400">— Ninguno —</span>
                        </template>
                        <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute left-0 top-full mt-1 w-80 max-h-72 overflow-hidden rounded-xl border border-slate-600 bg-slate-800 shadow-xl z-20 flex flex-col">
                        <input type="text" x-model="query" placeholder="Buscar por código o descripción..."
                               class="m-2 px-3 py-2 rounded-lg border border-slate-600 bg-slate-900 text-slate-100 text-sm placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <div class="overflow-y-auto flex-1 p-1 max-h-56">
                            <button type="button" @click="selected = null; open = false"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left hover:bg-slate-700/50 transition text-slate-400">
                                <span class="w-9 h-9 rounded bg-slate-700 flex-shrink-0 flex items-center justify-center text-xs">—</span>
                                <span>Ninguno</span>
                            </button>
                            <template x-for="eq in filteredEquipos" :key="eq.id">
                                <button type="button" @click="selected = eq; open = false"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left hover:bg-slate-700/50 transition">
                                    <img :src="eq.imagen_url" alt="" class="w-9 h-9 rounded object-cover flex-shrink-0 bg-slate-700"
                                         loading="lazy" @@error="$event.target.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2236%22 height=%2236%22 viewBox=%220 0 24 24%22 fill=%22%23475569%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E'">
                                    <div class="min-w-0 flex-1">
                                        <span class="font-medium text-slate-200 block truncate" x-text="eq.codigo"></span>
                                        <span class="text-xs text-slate-400 block truncate" x-text="eq.descripcion || ''"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Etiqueta (opcional)</label>
                    <select name="etiqueta_id" class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 w-56">
                        <option value="">— Ninguna —</option>
                        @foreach($etiquetasArchivo as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Archivo</label>
                    <input type="file" name="archivo" required
                           class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-slate-700 file:text-slate-200">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500">Guardar archivo</button>
            </form>
        </div>
        <div class="bg-slate-900/70 rounded-xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold text-indigo-400 mb-4">Archivos guardados</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Nombre</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Equipo</th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-400">Etiqueta</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivos as $a)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/40">
                                <td class="py-3 px-4">{{ $a->nombre }}</td>
                                <td class="py-3 px-4">{{ $a->equipo ? $a->equipo->codigo . ' — ' . Str::limit($a->equipo->descripcion, 20) : '—' }}</td>
                                <td class="py-3 px-4">{{ $a->etiqueta?->nombre ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('almacen.download.archivo', $a) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">Descargar</a>
                                    <form method="POST" action="{{ route('almacen.destroy.archivo', $a) }}" class="inline" onsubmit="return confirm('¿Eliminar este archivo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 px-4 text-center text-slate-500">No hay archivos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('equipoDropdown', (equiposList) => ({
        equiposList: equiposList || [],
        selected: null,
        open: false,
        query: '',
        get filteredEquipos() {
            const q = this.query.toLowerCase().trim();
            if (!q) return this.equiposList;
            return this.equiposList.filter(e =>
                (e.codigo && e.codigo.toLowerCase().includes(q)) ||
                (e.descripcion && e.descripcion.toLowerCase().includes(q))
            );
        }
    }));
});
</script>
@endsection

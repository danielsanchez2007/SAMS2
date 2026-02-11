@extends('layouts.app')

@section('title', 'Roles')

@section('content')

<div class="space-y-8" x-data="rolesApp()">

    <!-- Barra: filtro automático, tamaño tabla, acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form x-ref="filterForm" method="GET" action="{{ route('roles.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nombre del rol..."
                       class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80 text-sm"
                       @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('roles.index', ['per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
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
                @click="openModalRol()" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar Rol
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

    <!-- Tabla de roles -->
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border-2 tema-table-wrap shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300 min-w-[600px]">
                <thead class="bg-slate-800/90">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 uppercase tracking-wider text-xs">Nombre del Rol</th>
                        <th class="text-right py-4 px-8 font-semibold text-slate-400 uppercase tracking-wider text-xs w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($roles as $i => $r)
                        <tr class="hover:bg-slate-800/50 transition-colors duration-200">
                            <td class="py-4 px-6 font-medium text-slate-100">{{ $r->nombre }}</td>
                            <td class="py-4 px-8 text-right flex items-center justify-end gap-2">
                                <button 
                                    type="button" 
                                    @click="editRol({{ json_encode($r) }})" 
                                    class="p-2.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/30 rounded-lg transition-all duration-200"
                                    title="Editar rol"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <form action="{{ route('roles.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('¿Realmente deseas eliminar este rol? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="p-2.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-lg transition-all duration-200"
                                        title="Eliminar rol"
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
                                No hay roles registrados todavía.<br>
                                <span class="text-slate-400">Haz clic en "Agregar Rol" para comenzar.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($roles, 'links'))
        <div class="px-6 py-4 border-t border-slate-800/60 bg-slate-900/50">
            {{ $roles->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    <!-- Modal de Exportación -->
    <div x-show="showExportModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" 
         >
        <div class="tema-modal-export tema-modal-content rounded-2xl w-full max-w-4xl my-8 p-8" @click.stop>
            <h3 class="text-2xl font-bold mb-6 flex items-center gap-3" style="color: var(--tema-primary);">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2m-4-3v1m0 0v1m0-1h1m-1 0h-1"/>
                </svg>
                Exportar Roles
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

                <p class="text-lg font-semibold mb-5" style="color: var(--tema-primary);">Lista de roles</p>

                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="text-white uppercase text-xs tracking-wider" style="background: linear-gradient(135deg, var(--tema-from), var(--tema-to));">
                            <th class="border border-white/30 p-3 text-left">Nombre del Rol</th>
                            <th class="border border-white/30 p-3 text-left">Permisos / Módulos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($roles as $i => $r)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="border border-gray-200 p-3 font-medium text-gray-900">{{ $r->nombre }}</td>
                                <td class="border border-gray-200 p-3 text-xs text-gray-700">
                                    @php $permisos = $r->permisos ?? []; @endphp
                                    @if(!empty($permisos))
                                        @foreach($permisos as $key => $p)
                                            @if(!empty($p['acceso']))
                                                <span class="inline-block bg-slate-800/60 px-2 py-0.5 rounded mr-1.5 mb-1">
                                                    {{ $modulos[$key] ?? $key }}
                                                    @if(!empty($p['agregar']) || !empty($p['editar']) || !empty($p['eliminar']))
                                                        <span class="text-indigo-300/80 text-xs">
                                                            ({{ implode(', ', array_filter([
                                                                !empty($p['agregar']) ? 'Agregar' : null,
                                                                !empty($p['editar']) ? 'Editar' : null,
                                                                !empty($p['eliminar']) ? 'Eliminar' : null,
                                                            ])) }})
                                                        </span>
                                                    @endif
                                                </span>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-slate-500">— Sin permisos asignados —</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="border border-slate-800 p-6 text-center text-slate-500">
                                    No hay roles para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('roles.export.excel') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl tema-gradient text-white font-semibold transition-all shadow-md hover:opacity-90">
                    Descargar Excel
                </a>

                <a href="{{ route('roles.export.pdf') }}" 
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

    <!-- Modal de Crear/Editar Rol -->
    @include('roles.modals.rol', ['modulos' => $modulos])

</div>

<!-- Script Alpine -->
<script>
function rolesApp() {
    return {
        showExportModal: false,
        modalRol: false,
        formRol: { id: null, nombre: '', permisos: {} },
        openModalRol() {
            this.formRol = { id: null, nombre: '', permisos: {} };
            this.modalRol = true;
            // Abrir todas las categorías por defecto
            this.$nextTick(() => {
                const categorias = ['Usuarios', 'Equipos', 'Operaciones', 'Configuración', 'Otros'];
                categorias.forEach(cat => {
                    if (this.$el.querySelector(`[x-data*="categoriasAbiertas"]`)) {
                        // Las categorías se abrirán automáticamente con x-data
                    }
                });
            });
        },
        editRol(r) {
            this.formRol = {
                id: r.id,
                nombre: r.nombre,
                permisos: r.permisos || {}
            };
            this.modalRol = true;
        }
    };
}
</script>

@endsection
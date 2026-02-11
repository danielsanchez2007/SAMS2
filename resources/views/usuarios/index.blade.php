@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="space-y-6" x-data="usuariosApp()">
    {{-- Barra: filtro automático, tamaño tabla, agregar, exportar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('usuarios.index') }}" class="flex flex-wrap gap-3 items-center" x-ref="filterForm">
            <label class="text-sm font-medium text-slate-400">Buscar:</label>
            <div class="relative">
                <input type="text" name="q" value="{{ $search }}" placeholder="Nombre, cédula..."
                       class="pl-4 pr-10 py-2.5 text-sm rounded-xl border border-slate-700/70 bg-slate-900/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none w-72 sm:w-80"
                       @input.debounce.100ms="$refs.filterForm.submit()" autocomplete="off">
                @if($search)
                <a href="{{ route('usuarios.index', ['per_page' => $perPage ?? 10]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300" title="Limpiar">✕</a>
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
        <div class="flex gap-3">
            <button 
                type="button" 
                @click="openModalUsuario()" 
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl tema-gradient tema-gradient-hover text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Agregar Usuario
            </button>

            <button 
                type="button" 
                @click="showExportModal = true" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 text-sm font-medium hover:bg-slate-800 hover:text-white transition shadow-sm"
            >
                Exportar
            </button>
        </div>
    </div>

    {{-- Tabla: usuarios --}}
    <div class="bg-slate-900/80 backdrop-blur-sm rounded-xl border-2 tema-table-wrap shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300">
                <thead class="bg-slate-800/80">
                    <tr>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400 w-24">Imagen</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400">Cédula</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400">Nombre completo</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400">Rol</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-400">Empresa</th>
                        <th class="text-center py-4 px-6 font-semibold text-slate-400">Estado</th>
                        <th class="text-right py-4 px-6 font-semibold text-slate-400 w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($usuarios as $i => $u)
                        <tr class="hover:bg-slate-800/60 transition-colors duration-150">
                            <td class="py-4 px-6">
                                @if($u->imagen_usuario)
                                    @php
                                        $imagenPath = $u->imagen_usuario;
                                        $imagenUrl = str_starts_with($imagenPath, 'http') 
                                            ? $imagenPath 
                                            : (str_starts_with($imagenPath, 'storage/') 
                                                ? asset('storage/' . str_replace('storage/', '', $imagenPath)) 
                                                : asset($imagenPath));
                                    @endphp
                                    <img src="{{ $imagenUrl }}" alt="{{ $u->nombre }} {{ $u->apellidos }}" 
                                         class="w-16 h-16 rounded-full object-cover border-2 border-slate-600 shadow-lg">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-2 border-slate-600 flex items-center justify-center shadow-lg">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6">{{ $u->cedula }}</td>
                            <td class="py-4 px-6 font-medium text-slate-100">{{ $u->nombre }} {{ $u->apellidos }}</td>
                            <td class="py-4 px-6">
                                @if($u->role)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                                        {{ $u->role->nombre }}
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-400">{{ $u->empresa?->nombre ?? '—' }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium tracking-wide
                                    {{ $u->activo 
                                        ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' 
                                        : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                    {{ $u->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right flex items-center justify-end gap-2">
                                <button 
                                    type="button" 
                                    @click="editUsuario({{ json_encode($u) }})" 
                                    class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-900/30 rounded-lg transition"
                                    title="Editar"
                                >✎</button>
                                <button 
                                    type="button" 
                                    @click="openResetPasswordModal({{ json_encode($u) }})" 
                                    class="p-2 text-yellow-400 hover:text-yellow-300 hover:bg-yellow-900/30 rounded-lg transition"
                                    title="Restablecer contraseña"
                                >🔑</button>
                                <form action="{{ route('usuarios.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="p-2 text-rose-400 hover:text-rose-300 hover:bg-rose-900/30 rounded-lg transition"
                                        title="Eliminar"
                                    >🗑</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-6 text-center text-slate-500 italic">
                                No hay usuarios registrados aún. Agrega uno para comenzar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($usuarios, 'links'))
        <div class="px-6 py-4 border-t border-slate-700/50 bg-slate-800/30 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-slate-400">
                Mostrando {{ $usuarios->firstItem() ?? 0 }} a {{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} usuarios
            </p>
            <div class="flex items-center gap-2">
                @if ($usuarios->onFirstPage())
                    <span class="px-3 py-1.5 rounded-lg border border-slate-600 text-slate-500 text-sm cursor-not-allowed">Anterior</span>
                @else
                    <a href="{{ $usuarios->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-slate-600 text-slate-300 text-sm font-medium hover:bg-slate-700 transition">Anterior</a>
                @endif
                <span class="text-sm text-slate-400">Página {{ $usuarios->currentPage() }} de {{ $usuarios->lastPage() }}</span>
                @if ($usuarios->hasMorePages())
                    <a href="{{ $usuarios->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border-2 tema-pagination text-sm font-medium transition">Siguiente</a>
                @else
                    <span class="px-3 py-1.5 rounded-lg border border-slate-600 text-slate-500 text-sm cursor-not-allowed">Siguiente</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Exportar --}}
    <div x-show="showExportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto">
        <div class="tema-modal-export tema-modal-content rounded-2xl max-w-6xl w-full my-8 p-8" @click.stop>
            <h3 class="text-xl font-semibold mb-6" style="color: var(--tema-primary);">Vista previa — PDF / Excel</h3>
            <div class="bg-white rounded-xl p-6 mb-8 max-h-[60vh] overflow-y-auto border border-gray-200">
                <div class="flex justify-center gap-10 mb-8">
                    <img :src="logos.secundario || '{{ asset('logos/LOGO-INSTITUTO-PREVENTION-WORLD.png') }}'" alt="Logo secundario" class="h-16 object-contain bg-white border border-gray-900 rounded-lg px-4 py-2">
                    <img :src="logos.principal || '{{ asset('img/logos/logoSams.png') }}'" alt="Logo principal" class="h-16 object-contain bg-white border border-gray-900 rounded-lg px-4 py-2">
                </div>
                <p class="text-base font-semibold mb-4" style="color: var(--tema-primary);">Usuarios</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="text-white" style="background: linear-gradient(135deg, var(--tema-from), var(--tema-to));">
                                <th class="border border-white/30 p-3 text-left">Cédula</th>
                                <th class="border border-white/30 p-3 text-left">Nombre</th>
                                <th class="border border-white/30 p-3 text-left">Apellidos</th>
                                <th class="border border-white/30 p-3 text-left">Username</th>
                                <th class="border border-white/30 p-3 text-left">Empresa</th>
                                <th class="border border-white/30 p-3 text-left">Cargo</th>
                                <th class="border border-white/30 p-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($usuarios as $i => $u)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-200 p-3 text-gray-900">{{ $u->cedula }}</td>
                                    <td class="border border-gray-200 p-3 text-gray-900">{{ $u->nombre }}</td>
                                    <td class="border border-gray-200 p-3 text-gray-900">{{ $u->apellidos }}</td>
                                    <td class="border border-gray-200 p-3 text-gray-900">{{ $u->username }}</td>
                                    <td class="border border-gray-200 p-3 text-gray-900">{{ $u->empresa?->nombre ?? '—' }}</td>
                                    <td class="border border-gray-200 p-3 text-gray-900">{{ $u->cargo?->nombre ?? '—' }}</td>
                                    <td class="border border-gray-200 p-3">
                                        <span class="{{ $u->activo ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ $u->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="border border-gray-200 p-4 text-center text-gray-500">No hay usuarios.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('usuarios.export.excel') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl tema-gradient text-white text-sm font-semibold hover:opacity-90 transition shadow-md">Descargar Excel</a>
                <a href="{{ route('usuarios.export.pdf') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl tema-gradient text-white text-sm font-semibold hover:opacity-90 transition shadow-md">Descargar PDF</a>
            </div>
            <button type="button" @click="showExportModal = false" class="tema-modal-export mt-6 w-full py-3 rounded-xl border-2 font-medium">Cerrar</button>
        </div>
    </div>

    @include('usuarios.modals.usuario', ['empresas' => $empresas, 'sedes' => $sedes, 'grupos' => $grupos, 'cargos' => $cargos, 'roles' => $roles, 'ubicacion' => $ubicacion])

    {{-- Modal Restablecer Contraseña --}}
    <div x-show="showResetPasswordModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto"
         >
        <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full p-8 border border-slate-700/50 text-slate-100" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-yellow-400">Restablecer Contraseña</h3>
                <button type="button" @click="showResetPasswordModal = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="resetPasswordUsuario">
                <div>
                    <p class="text-sm text-slate-400 mb-4">
                        Restablecer contraseña para: <span class="text-slate-200 font-medium" x-text="resetPasswordUsuario.nombre + ' ' + resetPasswordUsuario.apellidos"></span>
                    </p>
                    <form method="POST" :action="'{{ url('/usuarios') }}/' + resetPasswordUsuario.id + '/reset-password'" @submit="showResetPasswordModal = false">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-1">Nueva contraseña</label>
                                <input type="password" name="password" x-model="resetPasswordForm.password" required
                                       class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100"
                                       placeholder="Mínimo 6 caracteres">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-1">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" x-model="resetPasswordForm.password_confirmation" required
                                       class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100"
                                       placeholder="Repite la contraseña">
                            </div>
                        </div>
                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="flex-1 px-5 py-2.5 rounded-lg bg-yellow-600 text-white font-medium hover:bg-yellow-500 transition">Restablecer</button>
                            <button type="button" @click="showResetPasswordModal = false" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 transition">Cancelar</button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>

<!-- Script Alpine.js con campos condicionales -->
<script>
function usuariosApp() {
    return {
        showExportModal: false,
        showResetPasswordModal: false,
        resetPasswordUsuario: null,
        resetPasswordForm: {
            password: '',
            password_confirmation: ''
        },
        logos: {
            principal: '{{ !empty($logoMain) ? asset('storage/' . $logoMain) : '' }}',
            secundario: '{{ !empty($logoSecondary) ? asset('storage/' . $logoSecondary) : '' }}',
        },
        roles: @json($roles),
        modalUsuario: false,
        formUsuario: {
            id: null,
            tipo_documento: 'Cédula de ciudadanía',
            cedula: '',
            nombre: '',
            apellidos: '',
            fecha_nacimiento: '',
            direccion: '',
            telefono: '',
            correo_electronico: '',
            tiene_correo_corporativo: false,
            correo_corporativo: '',
            tiene_telefono_corporativo: false,
            telefono_corporativo: '',
            departamento: '',
            departamento_otro: '',
            municipio: '',
            municipio_otro: '',
            tratamiento: '',
            tratamiento_select: '',
            empresa_id: '',
            sede_id: '',
            grupo_id: '',
            cargo_id: '',
            role_id: '',
            username: '',
            password: '',
            password_confirmation: '',
            activo: true,
            imagen_usuario: '',
            firma_imagen: ''
        },
        openModalUsuario() {
            this.formUsuario = {
                id: null, 
                tipo_documento: 'Cédula de ciudadanía', 
                cedula: '', 
                nombre: '', 
                apellidos: '', 
                fecha_nacimiento: '',
                direccion: '', 
                telefono: '', 
                correo_electronico: '', 
                tiene_correo_corporativo: false, 
                correo_corporativo: '',
                tiene_telefono_corporativo: false, 
                telefono_corporativo: '', 
                departamento: '', 
                departamento_otro: '',
                municipio: '', 
                municipio_otro: '', 
                tratamiento: '', 
                tratamiento_select: '',
                empresa_id: '', 
                sede_id: '', 
                grupo_id: '', 
                cargo_id: '', 
                role_id: '',
                username: '', 
                password: '', 
                password_confirmation: '', 
                activo: true, 
                imagen_usuario: '', 
                firma_imagen: ''
            };
            this.modalUsuario = true;
        },
        editUsuario(u) {
            // Determinar si el tratamiento es uno de los predefinidos o personalizado
            const tratamientosPredef = ['', 'Sr.', 'Sra.', 'Señora.', 'Dr.', 'Dra.', 'Ing.', 'Lic.', 'Arq.'];
            const esPredef = tratamientosPredef.includes(u.tratamiento);
            
            this.formUsuario = {
                id: u.id, 
                tipo_documento: u.tipo_documento, 
                cedula: u.cedula, 
                nombre: u.nombre, 
                apellidos: u.apellidos,
                fecha_nacimiento: u.fecha_nacimiento, 
                direccion: u.direccion, 
                telefono: u.telefono, 
                correo_electronico: u.correo_electronico,
                tiene_correo_corporativo: u.tiene_correo_corporativo || false, 
                correo_corporativo: u.correo_corporativo || '',
                tiene_telefono_corporativo: u.tiene_telefono_corporativo || false, 
                telefono_corporativo: u.telefono_corporativo || '',
                departamento: u.departamento, 
                departamento_otro: u.departamento_otro || '', 
                municipio: u.municipio, 
                municipio_otro: u.municipio_otro || '',
                tratamiento: esPredef ? u.tratamiento : u.tratamiento,
                tratamiento_select: esPredef ? u.tratamiento : 'Otro',
                empresa_id: u.empresa_id, 
                sede_id: u.sede_id, 
                grupo_id: u.grupo_id, 
                cargo_id: u.cargo_id, 
                role_id: u.role_id,
                username: u.username, 
                password: '', 
                password_confirmation: '', 
                activo: u.activo, 
                imagen_usuario: u.imagen_usuario || '', 
                firma_imagen: u.firma_imagen || ''
            };
            this.modalUsuario = true;
        },
        autoUsername() {
            if (this.formUsuario.correo_electronico && !this.formUsuario.id) {
                this.formUsuario.username = this.formUsuario.correo_electronico;
            }
        },
        openResetPasswordModal(usuario) {
            this.resetPasswordUsuario = usuario;
            this.resetPasswordForm = {
                password: '',
                password_confirmation: ''
            };
            this.showResetPasswordModal = true;
        }
    };
}
</script>
@endsection
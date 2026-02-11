@extends('layouts.app')

@section('title', 'Seguridad')

@section('content')
<div class="space-y-6" x-data="seguridadApp()">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold" style="color: var(--tema-primary);">Seguridad del Sistema</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Panel del Candado y Código --}}
        <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-xl p-8">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Código de Seguridad</h2>
            
            {{-- Candado Visual --}}
            <div class="flex flex-col items-center justify-center mb-8 p-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                <button 
                    type="button"
                    @click="intentarDesbloquear()"
                    class="focus:outline-none transition-transform hover:scale-110"
                    :disabled="!puedeEditar && seguridad.bloqueado"
                >
                    <template x-if="seguridad.bloqueado">
                        <svg 
                            class="w-24 h-24 text-gray-400 transition-colors duration-300"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </template>
                    <template x-if="!seguridad.bloqueado">
                        <svg 
                            class="w-24 h-24 text-green-500 transition-colors duration-300"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>
                    </template>
                </button>
                <p class="mt-4 text-lg font-medium" 
                   :class="seguridad.bloqueado ? 'text-gray-600' : 'text-green-600'"
                   x-text="seguridad.bloqueado ? '🔒 Bloqueado' : '🔓 Desbloqueado'">
                </p>
            </div>

            {{-- Gráfica de personas con permiso --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">Personas con Permiso</label>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200 p-6">
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-5xl font-bold mb-2" style="color: var(--tema-primary);">
                                {{ $totalPersonasConPermiso }}
                            </div>
                            <div class="text-sm text-gray-600 font-medium">
                                @if($totalPersonasConPermiso == 1)
                                    Persona con permiso
                                @else
                                    Personas con permiso
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200">
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            <span>Total de usuarios autorizados</span>
                            <span class="font-semibold">{{ $totalPersonasConPermiso }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones de acción (solo si tiene permiso) --}}
            <template x-if="puedeEditar">
                <div class="space-y-4">
                    <button 
                        type="button"
                        @click="mostrarEditor = true"
                        class="w-full px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-md"
                    >
                        ✏️ Editar Código
                    </button>
                    <form method="POST" action="{{ route('seguridad.toggle-bloqueo') }}" class="inline-block w-full">
                        @csrf
                        <input type="hidden" name="bloqueado" :value="seguridad.bloqueado ? '0' : '1'">
                        <button 
                            type="submit"
                            class="w-full px-6 py-3 rounded-lg font-semibold transition shadow-md"
                            :class="seguridad.bloqueado ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-yellow-600 hover:bg-yellow-700 text-white'"
                        >
                            <span x-text="seguridad.bloqueado ? '🔓 Desbloquear' : '🔒 Bloquear'"></span>
                        </button>
                    </form>
                    @if(($user['role'] ?? '') === 'mega_admin')
                    <button 
                        type="button"
                        @click="mostrarPasswordModal = true"
                        class="w-full px-6 py-3 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition shadow-md"
                    >
                        🔑 Establecer Contraseña de Edición
                    </button>
                    @endif
                </div>
            </template>

            {{-- Botones para usuarios sin permiso (pueden editar con contraseña) --}}
            <template x-if="!puedeEditar">
                <div class="space-y-4">
                    <button 
                        type="button"
                        @click="equipoSeleccionado = ''; nuevoCodigo = ''; mostrarEditor = true"
                        class="w-full px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-md"
                    >
                        ✏️ Editar Código de Equipo (con contraseña)
                    </button>
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            ℹ️ Puedes editar el código ingresando la contraseña. La contraseña cambiará automáticamente después de usarla.
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Panel de Permisos (solo para mega admin) --}}
        @if(($user['role'] ?? '') === 'mega_admin')
        <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-xl p-8">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Gestión de Permisos</h2>
            
            {{-- Formulario para agregar permiso --}}
            <div class="mb-6 p-6 bg-gray-50 rounded-xl border border-gray-200">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Asignar Permiso a Persona</h3>
                <p class="text-sm text-gray-600 mb-4">Al asignar permiso a una persona, automáticamente podrá editar y desbloquear códigos. Solo necesitas hacerlo una vez.</p>
                <form method="POST" action="{{ route('seguridad.agregar-permiso') }}">
                    @csrf
                    <input type="hidden" name="tipo" value="usuario">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Persona</label>
                            <select name="usuario_id" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700">
                                <option value="">-- Seleccione una persona --</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->nombre }} {{ $usuario->apellidos }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Solo puedes seleccionar una persona a la vez. La persona podrá editar y desbloquear códigos automáticamente.</p>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition">
                            ➕ Agregar Permiso
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla de permisos existentes --}}
            <div>
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Personas con Permiso</h3>
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Nombre</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Usuario</th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-700">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($permisos->where('usuario_id', '!=', null) as $permiso)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4">
                                            <span class="font-medium text-gray-800">👤 {{ $permiso->usuario->nombre }} {{ $permiso->usuario->apellidos }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-gray-600">{{ $permiso->usuario->username }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form method="POST" action="{{ route('seguridad.eliminar-permiso', $permiso) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('¿Quitar el permiso a {{ $permiso->usuario->nombre }} {{ $permiso->usuario->apellidos }}?')"
                                                        class="px-3 py-1 rounded-lg bg-red-600 text-white text-xs font-medium hover:bg-red-700 transition">
                                                    🗑️ Quitar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 px-4 text-center text-gray-500">
                                            No hay personas con permiso configurado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>


    {{-- Modal para editar código --}}
    <div x-show="mostrarEditor" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70"
         >
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Editar Código de Equipo</h3>
                <button type="button" @click="mostrarEditor = false; equipoSeleccionado = ''; nuevoCodigo = '';" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('seguridad.update-codigo') }}" @submit="mostrarEditor = false; equipoSeleccionado = ''; nuevoCodigo = '';">
                @csrf
                <template x-if="!puedeEditar">
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña de Edición *</label>
                        <input 
                            type="password" 
                            name="password_edicion" 
                            required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700"
                            placeholder="Ingresa la contraseña para editar el código"
                        >
                        <p class="text-xs text-gray-600 mt-2">La contraseña cambiará automáticamente después de usarla.</p>
                    </div>
                </template>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Equipo *</label>
                    <select 
                        name="equipo_id" 
                        x-model="equipoSeleccionado"
                        @change="cargarCodigoEquipo()"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700"
                    >
                        <option value="">-- Seleccione un equipo --</option>
                        @foreach($equipos ?? [] as $equipo)
                            <option value="{{ $equipo['id'] }}">{{ $equipo['display'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-600 mt-1">Selecciona el equipo al que deseas cambiar el código.</p>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Código *</label>
                    <input 
                        type="text" 
                        name="codigo" 
                        x-model="nuevoCodigo"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 font-mono text-sm"
                        placeholder="Ingresa el nuevo código del equipo..."
                    >
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                        💾 Guardar
                    </button>
                    <button type="button" @click="mostrarEditor = false; equipoSeleccionado = ''; nuevoCodigo = '';" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal para establecer contraseña (solo mega admin) --}}
    <div x-show="mostrarPasswordModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70"
         >
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Establecer Contraseña de Edición</h3>
                <button type="button" @click="mostrarPasswordModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('seguridad.update-password') }}" @submit="mostrarPasswordModal = false">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña *</label>
                    <input 
                        type="password" 
                        name="password_edicion" 
                        required
                        minlength="4"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700"
                        placeholder="Ingresa la nueva contraseña (mínimo 4 caracteres)"
                    >
                    <p class="text-xs text-gray-600 mt-2">Esta contraseña permitirá a usuarios sin permiso editar códigos. Cambiará automáticamente después de cada uso.</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition">
                        🔑 Establecer
                    </button>
                    <button type="button" @click="mostrarPasswordModal = false" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </div>
</div>

<script>
function seguridadApp() {
    return {
        puedeEditar: {{ $puedeEditar ? 'true' : 'false' }},
        seguridad: {
            codigo: @json($seguridad->codigo ?? ''),
            bloqueado: {{ $seguridad->bloqueado ? 'true' : 'false' }}
        },
        mostrarEditor: false,
        mostrarPasswordModal: false,
        equipoSeleccionado: '',
        nuevoCodigo: '',
        equipos: @json($equipos ?? []),
        cargarCodigoEquipo() {
            if (this.equipoSeleccionado) {
                const equipo = this.equipos.find(e => String(e.id) === String(this.equipoSeleccionado));
                if (equipo) {
                    this.nuevoCodigo = equipo.codigo || '';
                } else {
                    this.nuevoCodigo = '';
                }
            } else {
                this.nuevoCodigo = '';
            }
        },
        intentarDesbloquear() {
            if (!this.puedeEditar && this.seguridad.bloqueado) {
                alert('⚠️ No tienes permiso para desbloquear el código. Contacta al administrador.');
            } else if (this.puedeEditar) {
                this.equipoSeleccionado = '';
                this.nuevoCodigo = '';
                this.mostrarEditor = true;
            }
        }
    };
}
</script>
@endsection

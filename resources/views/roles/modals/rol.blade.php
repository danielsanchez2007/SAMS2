@php
    // Organizar módulos por categorías
    $categorias = [
        'Usuarios' => [
            'usuarios' => 'Usuarios',
            'roles' => 'Rol',
            'cargos' => 'Cargo',
            'grupos' => 'Grupo',
        ],
        'Equipos' => [
            'equipos' => 'Equipos',
            'tipo_equipos' => 'Tipo de equipos',
            'tipo_items' => 'Tipo de items',
            'uso_items' => 'Uso de items',
            'estado_items' => 'Estado de items',
            'estado_remision' => 'Estado de remisión',
        ],
        'Operaciones' => [
            'asignar' => 'Asignar',
            'inspeccionar' => 'Inspeccionar',
        ],
        'Configuración' => [
            'empresas' => 'Empresas',
            'proveedores' => 'Proveedores',
            'fabricantes' => 'Fabricantes',
            'redes_sociales' => 'Redes Sociales',
            'configuracion_login' => 'Configuración de Login',
        ],
    ];
    
    // Agregar módulos adicionales que puedan existir pero no estén en las categorías
    $modulosRestantes = [];
    foreach ($modulos as $key => $label) {
        $encontrado = false;
        foreach ($categorias as $catModulos) {
            if (isset($catModulos[$key])) {
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $modulosRestantes[$key] = $label;
        }
    }
    
    if (!empty($modulosRestantes)) {
        $categorias['Otros'] = $modulosRestantes;
    }
@endphp

<div x-show="modalRol" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
    <div class="tema-modal-opaco bg-white dark:bg-[#161615] rounded-xl shadow-xl max-w-4xl w-full my-8 p-6 border border-[#e3e3e0] dark:border-[#3E3E3A] max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold" x-text="formRol.id ? 'Editar rol' : 'Nuevo rol'"></h3>
            <button type="button" @click="modalRol = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form :action="formRol.id ? '{{ url('roles') }}/' + formRol.id : '{{ route('roles.store') }}'" method="POST">
            @csrf
            <template x-if="formRol.id">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Nombre del rol</label>
                <input type="text" name="nombre" x-model="formRol.nombre" required placeholder="Ej: Supervisor, Operador..."
                       class="w-full px-4 py-2.5 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-[var(--tema-primary)]/20 focus:border-[var(--tema-primary)] outline-none">
            </div>

            <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-4">Seleccione los módulos a los que tendrá acceso y qué podrá hacer en cada uno:</p>
            
            <div class="space-y-3 mb-6" x-data="{ categoriasAbiertas: { 
                @foreach($categorias as $nombreCategoria => $modulosCategoria)
                '{{ $nombreCategoria }}': true{{ !$loop->last ? ',' : '' }}
                @endforeach
            } }">
                @foreach($categorias as $nombreCategoria => $modulosCategoria)
                <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg overflow-hidden">
                    <button 
                        type="button"
                        @click="categoriasAbiertas['{{ $nombreCategoria }}'] = !categoriasAbiertas['{{ $nombreCategoria }}']"
                        class="w-full flex items-center justify-between px-4 py-3 bg-[#f5f5f4] dark:bg-[#262625] hover:bg-[#e8e8e7] dark:hover:bg-[#2f2f2e] transition-colors"
                    >
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $nombreCategoria }}</span>
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform duration-200" 
                             :class="categoriasAbiertas['{{ $nombreCategoria }}'] ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="categoriasAbiertas['{{ $nombreCategoria }}']" x-cloak class="overflow-hidden transition-all duration-200">
                        <div class="bg-white dark:bg-[#161615]">
                <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-[#1f1f1e] border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <tr>
                                        <th class="text-left py-2 px-4 font-medium text-gray-700 dark:text-gray-300">Módulo</th>
                                        <th class="text-center py-2 px-2 w-20 text-gray-700 dark:text-gray-300">Acceso</th>
                                        <th class="text-center py-2 px-2 w-20 text-gray-700 dark:text-gray-300">Agregar</th>
                                        <th class="text-center py-2 px-2 w-20 text-gray-700 dark:text-gray-300">Editar</th>
                                        <th class="text-center py-2 px-2 w-20 text-gray-700 dark:text-gray-300">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                                    @foreach($modulosCategoria as $key => $label)
                                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-[#fafaf9] dark:hover:bg-[#1f1f1e] transition-colors">
                                        <td class="py-3 px-4 text-gray-900 dark:text-gray-100">{{ $label }}</td>
                                        <td class="py-3 px-2 text-center">
                                    <input type="checkbox" name="permisos[{{ $key }}][acceso]" value="1"
                                           :checked="formRol.permisos && formRol.permisos['{{ $key }}'] && formRol.permisos['{{ $key }}'].acceso"
                                                   class="w-4 h-4 rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 cursor-pointer">
                                </td>
                                        <td class="py-3 px-2 text-center">
                                    <input type="checkbox" name="permisos[{{ $key }}][agregar]" value="1"
                                           :checked="formRol.permisos && formRol.permisos['{{ $key }}'] && formRol.permisos['{{ $key }}'].agregar"
                                                   class="w-4 h-4 rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 cursor-pointer">
                                </td>
                                        <td class="py-3 px-2 text-center">
                                    <input type="checkbox" name="permisos[{{ $key }}][editar]" value="1"
                                           :checked="formRol.permisos && formRol.permisos['{{ $key }}'] && formRol.permisos['{{ $key }}'].editar"
                                                   class="w-4 h-4 rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 cursor-pointer">
                                </td>
                                        <td class="py-3 px-2 text-center">
                                    <input type="checkbox" name="permisos[{{ $key }}][eliminar]" value="1"
                                           :checked="formRol.permisos && formRol.permisos['{{ $key }}'] && formRol.permisos['{{ $key }}'].eliminar"
                                                   class="w-4 h-4 rounded border-[#e3e3e0] dark:border-[#3E3E3A] text-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 cursor-pointer">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex gap-3 pt-4 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                <button type="submit" class="flex-1 px-6 py-2.5 rounded-lg tema-gradient text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                    Guardar
                </button>
                <button type="button" @click="modalRol = false" class="flex-1 px-6 py-2.5 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-[#1f1f1e] transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

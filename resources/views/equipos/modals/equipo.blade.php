{{-- Modal principal de Equipo --}}
<div x-show="showModal" x-cloak class="tema-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="tema-modal-opaco tema-modal-content bg-white rounded-xl shadow-xl max-w-5xl w-full my-8 p-6 max-h-[95vh] overflow-y-auto" @click.stop>
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-lg font-semibold" x-text="form.id ? 'Editar equipo' : 'Nuevo equipo'"></h3>
            {{-- Cuadro del último equipo registrado --}}
            @if(isset($ultimoEquipoData) && $ultimoEquipoData)
            <div class="flex-shrink-0 w-48 p-3 rounded-lg border-2 border-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 shadow-sm">
                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 mb-2">Último equipo registrado</p>
                <div class="flex items-center gap-2">
                    @if($ultimoEquipoData['imagen_url'])
                        <img src="{{ $ultimoEquipoData['imagen_url'] }}" 
                             alt="{{ $ultimoEquipoData['nombre'] }}" 
                             class="w-12 h-12 rounded object-cover border border-indigo-200 dark:border-indigo-700 flex-shrink-0"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22 viewBox=%220 0 24 24%22 fill=%22%23475569%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E'">
                    @else
                        <div class="w-12 h-12 rounded bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-indigo-900 dark:text-indigo-100 truncate" title="{{ $ultimoEquipoData['codigo'] }}">{{ $ultimoEquipoData['codigo'] }}</p>
                        <p class="text-xs text-indigo-700 dark:text-indigo-300 truncate" title="{{ $ultimoEquipoData['nombre'] }}">{{ $ultimoEquipoData['nombre'] }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <form :action="form.id ? '{{ url('equipos') }}/' + form.id : '{{ route('equipos.store') }}'" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                {{-- Empresa --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Empresa *</label>
                    <select name="empresa_id" x-model="form.empresa_id" @change="cargarUltimoCodigo(); cargarSedesModal()" required class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                        <option value="">-- Seleccione --</option>
                        @foreach($empresas ?? [] as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de Item --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de Item</label>
                    <select name="tipo_item_id" x-model="form.tipo_item_id" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                        <option value="">-- Seleccione --</option>
                        @foreach($tipoItems as $ti)
                            <option value="{{ $ti->id }}">{{ $ti->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de Equipo (para hoja de vida) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de Equipo</label>
                    <select name="tipo_equipo_id" x-model="form.tipo_equipo_id" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                        <option value="">-- Seleccione --</option>
                        @foreach($tipoEquipos ?? [] as $te)
                            <option value="{{ $te->id }}">{{ $te->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-0.5">Define el formato de hoja de vida que se usará al exportar.</p>
                </div>

                {{-- Código con candado y último código usado --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium mb-1">Código *</label>
                    <div class="flex gap-2 items-start">
                        {{-- Último código utilizado (lado izquierdo) --}}
                        <div x-show="ultimoCodigo" x-cloak class="flex-shrink-0 w-56 p-3 rounded-lg border-2 border-blue-400 bg-blue-50 dark:bg-blue-900/30 shadow-md">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-bold text-blue-800 dark:text-blue-200">Último código utilizado</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <img :src="ultimoCodigo?.imagen_url || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22%3E%3Crect width=%2250%22 height=%2250%22 fill=%22%23e5e7eb%22/%3E%3C/svg%3E'" 
                                     alt="Imagen del equipo" 
                                     class="w-12 h-12 rounded-lg object-cover flex-shrink-0 border-2 border-blue-300 dark:border-blue-600"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22%3E%3Crect width=%2250%22 height=%2250%22 fill=%22%23e5e7eb%22/%3E%3C/svg%3E'">
                                <div class="flex-1 min-w-0">
                                    <div class="mb-1">
                                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Código:</span>
                                        <span class="text-xs font-mono font-bold text-blue-900 dark:text-blue-100 ml-1" x-text="ultimoCodigo?.codigo"></span>
                                    </div>
                                    <div class="mb-1" x-show="ultimoCodigo?.nombre">
                                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Nombre:</span>
                                        <span class="text-xs text-blue-900 dark:text-blue-100 ml-1 font-medium line-clamp-1" x-text="ultimoCodigo?.nombre"></span>
                                    </div>
                                    <div x-show="ultimoCodigo?.descripcion">
                                        <p class="text-xs text-blue-800 dark:text-blue-200 line-clamp-2 mt-0.5" x-text="ultimoCodigo?.descripcion"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Campo código y botón --}}
                        <div class="flex-1 flex gap-2">
                            <div class="flex-1 flex items-center gap-2">
                                <span class="px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-gray-100 dark:bg-[#262625] text-sm font-mono font-semibold">IN</span>
                                <input type="text" name="codigo" x-model="form.codigo" required :readonly="form.codigo_bloqueado && !puedeDesbloquearCodigo && !form.passwordVerificada" 
                                       @input="formatearCodigoIN(); verificarCodigoSaltado()"
                                       :class="form.codigo_bloqueado && !puedeDesbloquearCodigo && !form.passwordVerificada ? 'bg-gray-100 dark:bg-[#262625] cursor-not-allowed' : ''"
                                       class="flex-1 px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm font-mono"
                                       placeholder="Ej: 1, 2, 3...">
                            </div>
                            <button type="button" 
                                    @click="puedeDesbloquearCodigo ? form.codigo_bloqueado = !form.codigo_bloqueado : mostrarPasswordModal = true" 
                                    class="px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] text-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                    :title="puedeDesbloquearCodigo ? (form.codigo_bloqueado ? 'Desbloquear código' : 'Bloquear código') : 'Ingresar contraseña para desbloquear'">
                                <span x-show="form.codigo_bloqueado">🔒</span>
                                <span x-show="!form.codigo_bloqueado">🔓</span>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Ingrese solo el número. El prefijo "IN" se agregará automáticamente.</p>
                        <div x-show="puedeDesbloquearCodigo" class="mt-1 text-xs text-blue-600 dark:text-blue-400 font-medium">
                            ✅ Tienes permiso para editar códigos. Puedes cambiar el código directamente.
                        </div>
                        <div x-show="!puedeDesbloquearCodigo && form.codigo_bloqueado" class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                            ℹ️ Haz clic en el candado e ingresa la contraseña para editar el código.
                        </div>
                    </div>
                    <input type="hidden" name="codigo_bloqueado" :value="form.codigo_bloqueado ? '1' : '0'">
                    <template x-if="form.passwordVerificada && !puedeDesbloquearCodigo">
                        <input type="hidden" name="password_edicion_codigo" :value="passwordCodigo">
                    </template>
                    <input type="hidden" name="confirmar_codigos_saltados" :value="confirmarCodigosSaltados ? '1' : '0'">
                    {{-- Alerta de códigos saltados --}}
                    <div x-show="codigosSaltados.length > 0" x-cloak class="mt-2 p-3 rounded border border-yellow-300 bg-yellow-50 dark:bg-yellow-900/20">
                        <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">⚠️ Códigos saltados detectados:</p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-2">
                            Los siguientes códigos serán agregados a "Códigos disponibles": 
                            <span class="font-mono" x-text="codigosSaltados.join(', ')"></span>
                        </p>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="confirmarCodigosSaltados" class="rounded border-yellow-300">
                            <span class="text-xs text-yellow-800 dark:text-yellow-200">Confirmar y agregar códigos saltados</span>
                        </label>
                    </div>
                </div>

                {{-- Estado de Remisión --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Estado de Remisión</label>
                    <select name="estado_remision_id" x-model="form.estado_remision_id" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                        <option value="">-- Seleccione --</option>
                        @foreach($estadoRemisiones ?? [] as $er)
                            <option value="{{ $er->id }}">{{ $er->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de registro (solo normal, las otras opciones se usan al traspasar) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Tipo de registro</label>
                    <select name="tipo_registro" x-model="form.tipo_registro" required class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm" disabled>
                        <option value="normal" selected>Equipo normal</option>
                    </select>
                    <input type="hidden" name="tipo_registro" value="normal">
                    <p class="text-xs text-slate-500 mt-0.5">Para cambiar a otro tipo, usa la opción de traspasar después de crear el equipo.</p>
                </div>

                {{-- Nombre --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre *</label>
                    <input type="text" name="nombre" x-model="form.nombre" required class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Descripción --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Descripción *</label>
                    <textarea name="descripcion" x-model="form.descripcion" required rows="2" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm"></textarea>
                </div>

                {{-- Marca --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Marca</label>
                    <input type="text" name="marca" x-model="form.marca" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Proveedor con mini modal --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Proveedor</label>
                    <div class="flex gap-2">
                        <select name="proveedor_id" x-model="form.proveedor_id" class="flex-1 px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                            <option value="">-- Seleccione --</option>
                            <template x-for="p in proveedoresList" :key="p.id">
                                <option :value="p.id" x-text="p.nombre"></option>
                            </template>
                        </select>
                        <button type="button" @click="showMiniProveedor = true" class="px-3 py-2 rounded bg-green-600 text-white text-sm font-bold" title="Agregar proveedor">+</button>
                    </div>
                </div>

                {{-- Fabricante con mini modal --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Fabricante</label>
                    <div class="flex gap-2">
                        <select name="fabricante_id" x-model="form.fabricante_id" class="flex-1 px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                            <option value="">-- Seleccione --</option>
                            <template x-for="f in fabricantesList" :key="f.id">
                                <option :value="f.id" x-text="f.nombre"></option>
                            </template>
                        </select>
                        <button type="button" @click="showMiniFabricante = true" class="px-3 py-2 rounded bg-green-600 text-white text-sm font-bold" title="Agregar fabricante">+</button>
                    </div>
                </div>

                {{-- Modelo --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Modelo</label>
                    <input type="text" name="modelo" x-model="form.modelo" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Sede --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Sede</label>
                    <select name="sede_id"
                            x-model="form.sede_id"
                            @change="cargarBodegasModal()"
                            :disabled="!form.empresa_id"
                            class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="">-- Seleccione --</option>
                        <template x-for="s in sedesModal" :key="s.id">
                            <option :value="s.id" x-text="s.nombre"></option>
                        </template>
                    </select>
                </div>

                {{-- Bodega --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Bodega</label>
                    <select name="bodega_id" x-model="form.bodega_id" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                        <option value="">-- Seleccione --</option>
                        <template x-for="b in bodegasModal" :key="b.id">
                            <option :value="b.id" x-text="b.nombre"></option>
                        </template>
                    </select>
                </div>

                {{-- Vida útil (slider) --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium mb-1">Vida útil: <span x-text="form.vida_util"></span> meses</label>
                    <input type="range" name="vida_util" x-model="form.vida_util" min="0" max="120" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                </div>

                {{-- Fecha fabricación --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha de fabricación</label>
                    <input type="date" name="fecha_fabricacion" x-model="form.fecha_fabricacion" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Fecha de uso --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha de uso</label>
                    <input type="date" name="fecha_uso" x-model="form.fecha_uso" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Uso del Item --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Uso del Item</label>
                    <select name="uso_item_id" x-model="form.uso_item_id" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                        <option value="">-- Seleccione --</option>
                        @foreach($usoItems as $ui)
                            <option value="{{ $ui->id }}">{{ $ui->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ¿Es un kit? --}}
            <div class="mb-4 p-4 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg">
                <label class="flex items-center gap-2 cursor-pointer mb-3">
                    <input type="checkbox" name="es_kit" x-model="form.es_kit" value="1" class="rounded border-[#e3e3e0]">
                    <span class="text-sm font-medium">¿Es un Kit?</span>
                </label>
                <div x-show="form.es_kit" x-cloak class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nombre del Kit</label>
                            <input type="text" name="nombre_kit" x-model="form.nombre_kit" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                            <label class="block text-xs font-medium mt-3 mb-1 text-slate-600 dark:text-slate-300">Descripción del Kit</label>
                            <textarea name="descripcion_kit_general"
                                      x-model="form.descripcion_kit_general"
                                      rows="2"
                                      class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-xs"
                                      placeholder="Descripción general de lo que contiene el kit"></textarea>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium mb-1">Items del Kit</label>
                            <p class="text-xs text-slate-500 mb-2">
                                Cada item usa el mismo código del kit con un número: <span class="font-mono" x-text="form.codigo || 'CODIGO'"></span>.1, .2, .3 ...
                            </p>
                            <div class="space-y-2">
                                <template x-for="(item, index) in kitItems" :key="index">
                                    <div class="flex flex-col gap-1 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-2">
                                        <div class="flex items-center gap-2">
                                            <input type="text"
                                                   class="w-32 px-2 py-1 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-gray-100 dark:bg-[#262625] text-xs font-mono"
                                                   x-model="item.codigo"
                                                   readonly>
                                            <input type="text"
                                                   class="flex-1 px-2 py-1 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-xs"
                                                   placeholder="Nombre del item"
                                                   x-model="item.nombre">
                                            <button type="button"
                                                    class="px-2 py-1 rounded bg-rose-600 text-white text-xs"
                                                    @click="eliminarKitItem(index)"
                                                    title="Quitar item">🗑</button>
                                        </div>
                                        <input type="text"
                                               class="w-full px-2 py-1 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-xs"
                                               placeholder="Descripción corta del item"
                                               x-model="item.descripcion">
                                    </div>
                                </template>
                                <button type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] text-xs font-medium hover:bg-gray-100 dark:hover:bg-[#1a1a1a]"
                                        @click="agregarKitItem()">
                                    <span>＋ Agregar item</span>
                                </button>
                                {{-- Serialización de items del kit para el backend --}}
                                <input type="hidden" name="componentes_kit" :value="kitItemsSerializados()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                {{-- Valor --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Valor</label>
                    <input type="number" name="valor" x-model="form.valor" step="0.01" min="0" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Número de factura --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Número de factura</label>
                    <input type="text" name="numero_factura" x-model="form.numero_factura" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Lote --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Lote</label>
                    <input type="text" name="lote" x-model="form.lote" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>

                {{-- Capacidades y resistencia --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Capacidades y resistencia</label>
                    <textarea name="capacidades_resistencia" x-model="form.capacidades_resistencia" rows="2" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm"></textarea>
                </div>

                {{-- Fecha de compra --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha de compra</label>
                    <input type="date" name="fecha_compra" x-model="form.fecha_compra" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm">
                </div>
            </div>

            {{-- Archivos opcionales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- Manual de fabricante --}}
                <div class="p-4 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="tiene_manual_fabricante" x-model="form.tiene_manual_fabricante" value="1" class="rounded border-[#e3e3e0]">
                        <span class="text-sm font-medium">¿Tiene manual de fabricante?</span>
                    </label>
                    <div x-show="form.tiene_manual_fabricante" x-cloak>
                        <input type="file" name="manual_fabricante_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
                    </div>
                </div>

                {{-- Certificación --}}
                <div class="p-4 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="tiene_certificacion" x-model="form.tiene_certificacion" value="1" class="rounded border-[#e3e3e0]">
                        <span class="text-sm font-medium">¿Tiene certificación del fabricante?</span>
                    </label>
                    <div x-show="form.tiene_certificacion" x-cloak>
                        <input type="file" name="certificacion_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
                    </div>
                </div>

                {{-- Imagen general --}}
                <div class="p-4 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="tiene_imagen_general" x-model="form.tiene_imagen_general" value="1" class="rounded border-[#e3e3e0]">
                        <span class="text-sm font-medium">¿Tiene imagen general?</span>
                    </label>
                    <div x-show="form.tiene_imagen_general" x-cloak class="space-y-2">
                        <template x-if="form.imagen_general">
                            <div><img :src="form.imagen_general && form.imagen_general.startsWith('storage/') ? '{{ asset('storage') }}/' + form.imagen_general.replace('storage/', '') : (form.imagen_general ? '{{ asset('') }}' + form.imagen_general : '')" alt="Imagen actual" class="max-h-24 rounded border border-[#e3e3e0]"></div>
                        </template>
                        <input type="file" name="imagen_general_file" accept="image/*" class="w-full text-sm">
                    </div>
                </div>

                {{-- Imagen de etiqueta --}}
                <div class="p-4 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" name="tiene_imagen_etiqueta" x-model="form.tiene_imagen_etiqueta" value="1" class="rounded border-[#e3e3e0]">
                        <span class="text-sm font-medium">¿Tiene imagen de etiqueta?</span>
                    </label>
                    <div x-show="form.tiene_imagen_etiqueta" x-cloak class="space-y-2">
                        <template x-if="form.imagen_etiqueta">
                            <div><img :src="form.imagen_etiqueta && form.imagen_etiqueta.startsWith('storage/') ? '{{ asset('storage') }}/' + form.imagen_etiqueta.replace('storage/', '') : (form.imagen_etiqueta ? '{{ asset('') }}' + form.imagen_etiqueta : '')" alt="Etiqueta actual" class="max-h-24 rounded border border-[#e3e3e0]"></div>
                        </template>
                        <input type="file" name="imagen_etiqueta_file" accept="image/*" class="w-full text-sm">
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="submit" class="flex-1 py-2 rounded bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] text-sm font-medium">Guardar</button>
                <button type="button" @click="showModal = false" class="flex-1 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] text-sm">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Mini Modal Proveedor --}}
<div x-show="showMiniProveedor" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white dark:bg-[#161615] rounded-xl shadow-xl max-w-sm w-full p-5 border border-[#e3e3e0] dark:border-[#3E3E3A]" @click.stop>
        <h4 class="text-base font-semibold mb-3">Agregar Proveedor</h4>
        <input type="text" x-model="nuevoProveedor" placeholder="Nombre del proveedor" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm mb-3">
        <div class="flex gap-2">
            <button type="button" @click="agregarProveedor()" class="flex-1 py-2 rounded bg-green-600 text-white text-sm font-medium">Agregar</button>
            <button type="button" @click="showMiniProveedor = false" class="flex-1 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] text-sm">Cancelar</button>
        </div>
    </div>
</div>

{{-- Mini Modal Fabricante --}}
<div x-show="showMiniFabricante" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white dark:bg-[#161615] rounded-xl shadow-xl max-w-sm w-full p-5 border border-[#e3e3e0] dark:border-[#3E3E3A]" @click.stop>
        <h4 class="text-base font-semibold mb-3">Agregar Fabricante</h4>
        <input type="text" x-model="nuevoFabricante" placeholder="Nombre del fabricante" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm mb-3">
        <div class="flex gap-2">
            <button type="button" @click="agregarFabricante()" class="flex-1 py-2 rounded bg-green-600 text-white text-sm font-medium">Agregar</button>
            <button type="button" @click="showMiniFabricante = false" class="flex-1 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] text-sm">Cancelar</button>
        </div>
    </div>
</div>

{{-- Modal para contraseña de código --}}
<div x-show="mostrarPasswordModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white dark:bg-[#161615] rounded-xl shadow-xl max-w-sm w-full p-5 border border-[#e3e3e0] dark:border-[#3E3E3A]" @click.stop>
        <h4 class="text-base font-semibold mb-3">🔒 Desbloquear Código</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Ingresa la contraseña establecida por el administrador para editar el código.</p>
        <input type="password" x-model="passwordCodigo" placeholder="Contraseña" class="w-full px-3 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] text-sm mb-3">
        <div class="flex gap-2">
            <button type="button" @click="verificarPasswordCodigo()" class="flex-1 py-2 rounded bg-blue-600 text-white text-sm font-medium">Verificar</button>
            <button type="button" @click="mostrarPasswordModal = false; passwordCodigo = ''" class="flex-1 py-2 rounded border border-[#e3e3e0] dark:border-[#3E3E3A] text-sm">Cancelar</button>
        </div>
    </div>
</div>

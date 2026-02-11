{{-- Partial: modal Crear/Editar Usuario con campos condicionales --}}
<div x-show="modalUsuario" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 overflow-y-auto"
     >
    <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl max-w-4xl w-full my-8 p-8 border border-slate-700/50 text-slate-100 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-blue-400" x-text="formUsuario.id ? 'Editar usuario' : 'Nuevo usuario'"></h3>
            <button type="button" @click="modalUsuario = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" :action="formUsuario.id ? '{{ url('/usuarios') }}/' + formUsuario.id : '{{ route('usuarios.store') }}'" enctype="multipart/form-data">
            @csrf
            <template x-if="formUsuario.id">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 space-y-0">
                {{-- Tipo documento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Tipo documento</label>
                    <select name="tipo_documento" x-model="formUsuario.tipo_documento" required
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="Cédula de ciudadanía">Cédula de ciudadanía</option>
                        <option value="Cédula de extranjería">Cédula de extranjería</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                {{-- Cédula --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Cédula / Documento</label>
                    <input type="text" name="cedula" x-model="formUsuario.cedula" required
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Nombre --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Nombre</label>
                    <input type="text" name="nombre" x-model="formUsuario.nombre" required
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Apellidos --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Apellidos</label>
                    <input type="text" name="apellidos" x-model="formUsuario.apellidos" required
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Fecha nacimiento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Fecha nacimiento</label>
                    <input type="date" name="fecha_nacimiento" x-model="formUsuario.fecha_nacimiento" required
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Tratamiento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Tratamiento</label>
                    <select x-model="formUsuario.tratamiento_select" @change="formUsuario.tratamiento = $event.target.value !== 'Otro' ? $event.target.value : ''"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        <option value="Sr.">Sr.</option>
                        <option value="Sra.">Sra.</option>
                        <option value="Señora.">Señora.</option>
                        <option value="Dr.">Dr.</option>
                        <option value="Dra.">Dra.</option>
                        <option value="Ing.">Ing.</option>
                        <option value="Lic.">Lic.</option>
                        <option value="Arq.">Arq.</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                {{-- Tratamiento personalizado (solo si selecciona "Otro") --}}
                <div x-show="formUsuario.tratamiento_select === 'Otro'" x-cloak class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-1">Especificar tratamiento</label>
                    <input type="text" name="tratamiento" x-model="formUsuario.tratamiento" placeholder="Ej: Prof., Mtro., etc."
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Input oculto para tratamiento cuando no es "Otro" --}}
                <input type="hidden" name="tratamiento" x-show="formUsuario.tratamiento_select !== 'Otro'" :value="formUsuario.tratamiento_select">
                
                {{-- Dirección --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-1">Dirección</label>
                    <input type="text" name="direccion" x-model="formUsuario.direccion"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Teléfono --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Teléfono</label>
                    <input type="text" name="telefono" x-model="formUsuario.telefono"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Correo electrónico --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Correo electrónico</label>
                    <input type="email" name="correo_electronico" x-model="formUsuario.correo_electronico" @input="autoUsername()"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                
                {{-- Checkbox: Tiene correo corporativo --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="tiene_correo_corporativo" id="tiene_correo_corporativo" value="1" 
                               x-model="formUsuario.tiene_correo_corporativo"
                               class="rounded border-slate-600 bg-slate-800 text-blue-600">
                        <label for="tiene_correo_corporativo" class="text-sm text-slate-400">Tiene correo corporativo</label>
                    </div>
                </div>
                {{-- Campo correo corporativo (solo si checkbox está marcado) --}}
                <div x-show="formUsuario.tiene_correo_corporativo" x-cloak class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-1">Correo corporativo</label>
                    <input type="email" name="correo_corporativo" x-model="formUsuario.correo_corporativo"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100"
                           placeholder="correo@empresa.com">
                </div>
                
                {{-- Checkbox: Tiene teléfono corporativo --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="tiene_telefono_corporativo" id="tiene_telefono_corporativo" value="1" 
                               x-model="formUsuario.tiene_telefono_corporativo"
                               class="rounded border-slate-600 bg-slate-800 text-blue-600">
                        <label for="tiene_telefono_corporativo" class="text-sm text-slate-400">Tiene teléfono corporativo</label>
                    </div>
                </div>
                {{-- Campo teléfono corporativo (solo si checkbox está marcado) --}}
                <div x-show="formUsuario.tiene_telefono_corporativo" x-cloak class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-1">Teléfono corporativo</label>
                    <input type="text" name="telefono_corporativo" x-model="formUsuario.telefono_corporativo"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100"
                           placeholder="Ej: +57 321 1234567">
                </div>
                
                {{-- Departamento --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Departamento</label>
                    <select name="departamento" x-model="formUsuario.departamento"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        @foreach($ubicacion['departamentos']['Colombia'] ?? $ubicacion['departamentos']['default'] ?? [] as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                {{-- Departamento otro (solo si selecciona "Otro") --}}
                <div x-show="formUsuario.departamento === 'Otro'" x-cloak>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Especificar departamento</label>
                    <input type="text" name="departamento_otro" x-model="formUsuario.departamento_otro" placeholder="Nombre del departamento"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Espacio vacío si no es "Otro" para mantener el grid --}}
                <div x-show="formUsuario.departamento !== 'Otro'" class="hidden md:block"></div>
                
                {{-- Municipio --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Municipio</label>
                    <select name="municipio" x-model="formUsuario.municipio"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        @php
                            $deptos = $ubicacion['departamentos']['Colombia'] ?? $ubicacion['departamentos']['default'] ?? [];
                        @endphp
                        @foreach($deptos as $d)
                            @foreach($ubicacion['municipios'][$d] ?? ['Otro'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        @endforeach
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                {{-- Municipio otro (solo si selecciona "Otro") --}}
                <div x-show="formUsuario.municipio === 'Otro'" x-cloak>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Especificar municipio</label>
                    <input type="text" name="municipio_otro" x-model="formUsuario.municipio_otro" placeholder="Nombre del municipio"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                
                {{-- Empresa --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Empresa</label>
                    <select name="empresa_id" x-model="formUsuario.empresa_id"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        @foreach($empresas as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Sede --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Sede</label>
                    <select name="sede_id" x-model="formUsuario.sede_id"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        @foreach($sedes as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Grupo --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Grupo</label>
                    <select name="grupo_id" x-model="formUsuario.grupo_id"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        @foreach($grupos as $g)
                            <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Cargo --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Cargo</label>
                    <select name="cargo_id" x-model="formUsuario.cargo_id"
                            class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                        <option value="">—</option>
                        @foreach($cargos as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Rol (solo editable por admin y mega_admin) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Rol</label>
                    @if($puedeCambiarRol ?? false)
                        <select name="role_id" x-model="formUsuario.role_id" required
                                class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                            <option value="">— Seleccione —</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->nombre }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800/50 text-slate-400">
                            <span x-text="formUsuario.role_id ? roles.find(r => r.id == formUsuario.role_id)?.nombre || '—' : '—'"></span>
                        </div>
                        <input type="hidden" name="role_id" x-model="formUsuario.role_id">
                        <p class="text-xs text-slate-500 mt-1">Solo los administradores pueden cambiar el rol.</p>
                    @endif
                </div>
                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Username</label>
                    <input type="text" name="username" x-model="formUsuario.username" required @input="autoUsername()"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                </div>
                {{-- Contraseña (solo crear) --}}
                <div x-show="!formUsuario.id" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Contraseña</label>
                        <input type="password" name="password" x-model="formUsuario.password"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" x-model="formUsuario.password_confirmation"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                </div>
                {{-- Contraseña (editar, opcional) --}}
                <div x-show="formUsuario.id" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Nueva contraseña (dejar en blanco para no cambiar)</label>
                        <input type="password" name="password" x-model="formUsuario.password"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" x-model="formUsuario.password_confirmation"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                </div>
                
                {{-- Imagen de usuario (obligatoria) --}}
                <div class="md:col-span-2 mt-4 border-t border-slate-700 pt-4">
                    <label class="block text-sm font-medium text-slate-400 mb-1">
                        Imagen de usuario <span class="text-red-400">*</span>
                    </label>
                    <input type="file" name="imagen_usuario" accept="image/*" :required="!formUsuario.id"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-slate-700 file:text-slate-200">
                    <p class="text-xs text-slate-500 mt-1">La foto es obligatoria para poder usar el sistema.</p>
                    <template x-if="formUsuario.imagen_usuario && formUsuario.id">
                        <p class="text-xs text-slate-500 mt-1" x-text="'Actual: ' + formUsuario.imagen_usuario"></p>
                    </template>
                </div>
                
                {{-- Firma (obligatoria) --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-1">
                        Firma (imagen) <span class="text-red-400">*</span>
                    </label>
                    <input type="file" name="firma_imagen" accept="image/*" :required="!formUsuario.id"
                           class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-slate-700 file:text-slate-200">
                    <p class="text-xs text-slate-500 mt-1">La firma es obligatoria para poder usar el sistema.</p>
                    <template x-if="formUsuario.firma_imagen && formUsuario.id">
                        <p class="text-xs text-slate-500 mt-1" x-text="'Actual: ' + formUsuario.firma_imagen"></p>
                    </template>
                </div>
                
                {{-- Activo --}}
                <div class="md:col-span-2 flex items-center gap-2 mt-4 border-t border-slate-700 pt-4">
                    <input type="checkbox" name="activo" id="modal_activo" value="1" :checked="formUsuario.activo"
                           class="rounded border-slate-600 bg-slate-800 text-blue-600">
                    <label for="modal_activo" class="text-sm text-slate-400">Usuario activo</label>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-700 text-white font-medium hover:bg-blue-600 transition">Guardar</button>
                <button type="button" @click="modalUsuario = false" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 transition">Cancelar</button>
            </div>
        </form>
    </div>
</div>

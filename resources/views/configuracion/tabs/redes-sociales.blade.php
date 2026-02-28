{{-- Redes Sociales --}}
<div>
    <div x-show="tab === 'redes-sociales'" x-cloak class="space-y-6" x-data="redesSocialesManager({{ json_encode($redesSociales) }}, '{{ $nombreUsuario }}', '{{ $esAdmin ? 'true' : 'false' }}', '{{ $puedeAgregarYo ? 'true' : 'false' }}', '{{ $imagenUsuario }}', '{{ $telefonoUsuario }}', '{{ json_encode($usuarios ?? []) }}', '{{ $userIdActual }}')">
        <form action="{{ route('configuracion.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tab" value="redes-sociales">

            <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Redes Sociales y Enlaces</h3>
                        <p class="text-sm text-gray-600 mt-1">Agrega cualquier red social, pagina web o enlace con su icono personalizado.</p>
                    </div>
                    <button type="button" @click="agregarRed()" 
                        class="px-4 py-2 rounded-lg tema-gradient text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all">
                        + Agregar Red
                    </button>
                </div>
                
                <div class="space-y-4" x-ref="redesContainer">
                    <template x-for="(red, index) in redes" :key="index">
                        <div class="bg-white rounded-xl p-5 border-2 border-gray-200 shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nombre</label>
                                    <input type="text" 
                                        x-model="red.nombre" 
                                        :name="`redes_sociales[${index}][nombre]`"
                                        placeholder="Ej: Facebook, WhatsApp, Web" 
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                </div>
                                
                                <div class="md:col-span-4" x-show="red.icono_svg !== 'whatsapp'">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">URL / Enlace</label>
                                    <input type="text" 
                                        x-model="red.url" 
                                        :name="`redes_sociales[${index}][url]`"
                                        placeholder="https://..." 
                                        @input="red.url = $event.target.value"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                </div>

                                <div class="md:col-span-4" x-show="red.icono_svg === 'whatsapp'">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Numeros de WhatsApp</label>
                                    <button type="button" @click="mostrarModalWhatsApp(index)" 
                                        class="w-full px-3 py-2 rounded-lg border-2 border-green-500 bg-green-50 text-green-700 font-semibold text-sm hover:bg-green-100 transition-all flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        Gestionar Numeros (<span x-text="red.numeros_whatsapp ? red.numeros_whatsapp.length : 0"></span>)
                                    </button>
                                    <input type="hidden" :name="`redes_sociales[${index}][numeros_whatsapp]`" :value="JSON.stringify(red.numeros_whatsapp || [])">
                                    <input type="hidden" :name="`redes_sociales[${index}][url]`" :value="red.url || 'whatsapp://'">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tipo Icono</label>
                                    <select x-model="red.tipo_icono" 
                                        :name="`redes_sociales[${index}][tipo_icono]`"
                                        @change="red.icono_svg = ''; red.icono_imagen = ''"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                        <option value="svg">SVG Predefinido</option>
                                        <option value="imagen">Imagen Personalizada</option>
                                    </select>
                                </div>
                                
                                <div class="md:col-span-2" x-show="red.tipo_icono === 'svg'">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Icono SVG</label>
                                    <select x-model="red.icono_svg" 
                                        :name="`redes_sociales[${index}][icono_svg]`"
                                        @change="if($event.target.value === 'whatsapp') { red.url = 'whatsapp://'; } else if(red.url === 'whatsapp://') { red.url = ''; }"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                        <option value="">Seleccionar...</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="twitter">Twitter</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="youtube">YouTube</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="whatsapp">WhatsApp</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="pinterest">Pinterest</option>
                                        <option value="snapchat">Snapchat</option>
                                        <option value="telegram">Telegram</option>
                                        <option value="github">GitHub</option>
                                        <option value="web">Web/Globo</option>
                                        <option value="email">Email</option>
                                        <option value="phone">Telefono</option>
                                    </select>
                                </div>
                                
                                <div class="md:col-span-2" x-show="red.tipo_icono === 'imagen'">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Icono Imagen</label>
                                    <input type="file" 
                                        :name="`redes_sociales[${index}][icono_imagen]`"
                                        accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                    <template x-if="red.icono_imagen_existente">
                                        <input type="hidden" 
                                            :name="`redes_sociales[${index}][icono_imagen_existente]`"
                                            :value="red.icono_imagen_existente">
                                        <img :src="red.icono_imagen_existente" alt="Icono" class="mt-2 h-8 w-auto object-contain">
                                    </template>
                                </div>
                                
                                <div class="md:col-span-1 flex items-end">
                                    <button type="button" @click="eliminarRed(index)" 
                                        class="w-full px-3 py-2 rounded-lg bg-red-50 border-2 border-red-300 text-red-700 font-semibold text-sm hover:bg-red-100 transition-all">
                                        
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="redes.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <p class="text-sm">No hay redes sociales configuradas. Haz clic en "Agregar Red" para comenzar.</p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl tema-gradient text-white font-semibold shadow-md hover:shadow-lg transition">
                    Guardar Cambios
                </button>
            </div>
        </form>

        <!-- Modal para gestionar numeros de WhatsApp -->
        <div x-show="modalWhatsApp.show" x-cloak 
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="modalWhatsApp.show = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Gestionar Numeros de WhatsApp</h3>
                        <button type="button" @click="modalWhatsApp.show = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Seccion para agregar "Yo" -->
                    <div x-show="puedeAgregarYo && !tieneMiNumeroYo" class="mb-4 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                        <button type="button" @click="agregarNumeroYo()" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold transition-all">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Agregar "Yo"
                            </span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        <p class="text-xs text-blue-600 mt-2">Agrega tu propio numero de contacto. Se cargaran automaticamente tu nombre y foto. Deberas ingresar manualmente tu numero de WhatsApp y una breve descripcion.</p>
                    </div>

                    <!-- Seccion para editar "Mi Numero" -->
                    <div x-show="tieneNumeroYo" class="mb-4 p-4 bg-blue-50 border-2 border-blue-300 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <template x-if="numeroYo.imagen && numeroYo.imagen.trim() !== ''">
                                    <div>
                                        <img :src="numeroYo.imagen" alt="Foto" 
                                             class="w-20 h-20 rounded-full object-cover border-4 border-blue-400 shadow-lg"
                                             @error="$el.style.display='none'; $el.nextElementSibling.style.display='flex';"
                                             loading="lazy">
                                        <div class="w-20 h-20 rounded-full bg-blue-200 flex items-center justify-center border-4 border-blue-400 shadow-lg" style="display: none;">
                                            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!numeroYo.imagen || numeroYo.imagen.trim() === ''">
                                    <div class="w-20 h-20 rounded-full bg-blue-200 flex items-center justify-center border-4 border-blue-400 shadow-lg">
                                        <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </template>
                                <span class="text-lg font-bold text-blue-700">Mi Numero</span>
                            </div>
                            <button type="button" x-show="puedeAgregarYo" @click="eliminarNumeroYo()" 
                                class="px-3 py-1 rounded-lg bg-red-50 border-2 border-red-300 text-red-700 font-semibold text-xs hover:bg-red-100 transition-all">
                                
                            </button>
                        </div>
                        <div class="space-y-2">
                            <input type="text" 
                                x-model="numeroYo.numero"
                                placeholder="Numero de WhatsApp (ej: 573001234567) *" 
                                :readonly="!puedeAgregarYo"
                                :class="puedeAgregarYo ? 'bg-white' : 'bg-gray-100 cursor-not-allowed'"
                                required
                                class="w-full px-3 py-2 rounded-lg border border-blue-300 text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none">
                            <input type="text" 
                                x-model="numeroYo.nombre"
                                placeholder="Nombre" 
                                readonly
                                class="w-full px-3 py-2 rounded-lg border border-blue-300 bg-gray-100 text-sm text-gray-600 cursor-not-allowed">
                            <textarea 
                                x-model="numeroYo.descripcion"
                                placeholder="Breve descripcion (opcional)" 
                                rows="2"
                                :readonly="!puedeAgregarYo"
                                :class="puedeAgregarYo ? 'bg-white' : 'bg-gray-100 cursor-not-allowed'"
                                class="w-full px-3 py-2 rounded-lg border border-blue-300 text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Buscador -->
                    <div class="relative">
                        <input type="text" 
                            x-model="modalWhatsApp.busqueda"
                            placeholder="Buscar numero o nombre..." 
                            class="w-full px-4 py-3 pl-10 rounded-lg border border-gray-300 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none">
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Lista de numeros existentes -->
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <template x-for="numero in numerosFiltrados" :key="numero.uid || ('num-' + numero.numero)">
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg border-2 border-gray-200 hover:border-gray-300 transition-all">
                                <div class="flex-shrink-0">
                                    <template x-if="numero.imagen && numero.imagen.trim() !== ''">
                                        <div>
                                            <img :src="numero.imagen" alt="Foto" 
                                                 class="w-20 h-20 rounded-full object-cover border-4 border-gray-300 shadow-lg"
                                                 @error="$el.style.display='none'; $el.nextElementSibling.style.display='flex';"
                                                 loading="lazy">
                                            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-300 shadow-lg" style="display: none;">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!numero.imagen || numero.imagen.trim() === ''">
                                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-300 shadow-lg">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="text" 
                                        x-model="numero.numero"
                                        placeholder="Numero (ej: 573001234567)" 
                                        :readonly="!esAdmin"
                                        :class="esAdmin ? 'bg-white' : 'bg-gray-100 cursor-not-allowed'"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none">
                                    <input type="text" 
                                        x-model="numero.nombre"
                                        placeholder="Nombre (opcional)" 
                                        :readonly="!esAdmin"
                                        :class="esAdmin ? 'bg-white' : 'bg-gray-100 cursor-not-allowed'"
                                        class="w-full px-3 py-2 mt-2 rounded-lg border border-gray-300 text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none">
                                    <textarea 
                                        x-model="numero.descripcion"
                                        placeholder="Descripcion (opcional)" 
                                        rows="2"
                                        :readonly="!esAdmin"
                                        :class="esAdmin ? 'bg-white' : 'bg-gray-100 cursor-not-allowed'"
                                        class="w-full px-3 py-2 mt-2 rounded-lg border border-gray-300 text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none resize-none"></textarea>
                                </div>
                                <button type="button" x-show="esAdmin" @click="eliminarNumeroWhatsApp(numero.uid)" 
                                    class="px-3 py-2 rounded-lg bg-red-50 border-2 border-red-300 text-red-700 font-semibold text-sm hover:bg-red-100 transition-all">
                                    
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Selector de usuarios para administradores -->
                    <div x-show="esAdmin" class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Seleccionar Usuario</label>
                        <select x-model="usuarioSeleccionado" @change="agregarNumeroDesdeUsuario()" 
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none">
                            <option value="">-- Seleccionar usuario --</option>
                            <template x-for="usuario in usuarios" :key="usuario.id">
                                <option :value="usuario.id" x-text="usuario.nombre"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Al seleccionar un usuario, se agregara automaticamente con su nombre, foto y telefono.</p>
                    </div>
                    <div x-show="!esAdmin" class="text-center py-4 text-sm text-gray-500">
                        Solo los administradores pueden agregar, editar o eliminar numeros de contacto.
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 flex justify-end gap-4">
                    <button type="button" @click="modalWhatsApp.show = false" 
                        class="px-6 py-2 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button" @click="guardarNumerosWhatsApp()" 
                        class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition">
                        Guardar Numeros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function redesSocialesManager(redesIniciales = [], nombreUsuario = '', esAdmin = false, puedeAgregarYo = false, imagenUsuario = '', telefonoUsuario = '', usuarios = [], userIdActual = '') {
        return {
            makeUid() {
                try {
                    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                        return window.crypto.randomUUID();
                    }
                } catch (_) {}
                return 'uid-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2);
            },
            esAdmin: esAdmin,
            puedeAgregarYo: puedeAgregarYo,
            usuarios: usuarios,
            usuarioSeleccionado: '',
            userIdActual: userIdActual,
            nombreUsuarioActual: nombreUsuario,
            redes: redesIniciales.map(r => {
                let url = r.url || '';
                if (r.icono_svg !== 'whatsapp' && url === 'whatsapp://') {
                    url = '';
                }
                return {
                    nombre: r.nombre || '',
                    url: url,
                    tipo_icono: r.tipo_icono || 'svg',
                    icono_svg: r.icono_svg || '',
                    icono_imagen: r.icono_imagen || '',
                    icono_imagen_existente: r.icono_imagen || null,
                    numeros_whatsapp: (r.numeros_whatsapp || []).map(n => ({
                        uid: n.uid || null,
                        numero: n.numero || '',
                        nombre: n.nombre || '',
                        descripcion: n.descripcion || '',
                        imagen: n.imagen || '',
                        es_yo: n.es_yo || false
                    }))
                };
            }),
            nombreUsuario: nombreUsuario,
            imagenUsuario: imagenUsuario,
            telefonoUsuario: telefonoUsuario,
            usuarioSeleccionadoData: null,
            numeroYo: {
                numero: '',
                nombre: nombreUsuario,
                descripcion: '',
                imagen: imagenUsuario || '',
                es_yo: true
            },
            modalWhatsApp: {
                show: false,
                index: null,
                busqueda: ''
            },
            
            get tieneNumeroYo() {
                if (this.modalWhatsApp.index === null) return false;
                const numeros = this.redes[this.modalWhatsApp.index].numeros_whatsapp || [];
                return numeros.some(n => n.es_yo === true);
            },
            
            get tieneMiNumeroYo() {
                if (this.modalWhatsApp.index === null) return false;
                const numeros = this.redes[this.modalWhatsApp.index].numeros_whatsapp || [];
                return numeros.some(n => n.es_yo === true && n.nombre && n.nombre.trim() === this.nombreUsuarioActual.trim());
            },
            
            get numerosFiltrados() {
                if (!this.modalWhatsApp.show || this.modalWhatsApp.index === null) return [];
                const numeros = this.redes[this.modalWhatsApp.index].numeros_whatsapp || [];
                const numerosNormales = numeros.filter(n => !n.es_yo).map(n => ({
                    ...n,
                    uid: n.uid || (n.uid = this.makeUid()),
                }));
                if (!this.modalWhatsApp.busqueda) return numerosNormales;
                const busqueda = this.modalWhatsApp.busqueda.toLowerCase();
                return numerosNormales.filter(n => 
                    (n.numero && n.numero.toLowerCase().includes(busqueda)) ||
                    (n.nombre && n.nombre.toLowerCase().includes(busqueda)) ||
                    (n.descripcion && n.descripcion && n.descripcion.toLowerCase().includes(busqueda))
                );
            },
            
            agregarRed() {
                this.redes.push({
                    nombre: '',
                    url: '',
                    tipo_icono: 'svg',
                    icono_svg: '',
                    icono_imagen: '',
                    icono_imagen_existente: null,
                    numeros_whatsapp: []
                });
            },
            
            eliminarRed(index) {
                if (confirm('¿Estas seguro de eliminar esta red social?')) {
                    this.redes.splice(index, 1);
                }
            },
            
            mostrarModalWhatsApp(index) {
                this.modalWhatsApp.index = index;
                this.modalWhatsApp.show = true;
                this.modalWhatsApp.busqueda = '';
                this.usuarioSeleccionado = '';
                if (!this.redes[index].numeros_whatsapp) {
                    this.redes[index].numeros_whatsapp = [];
                }
                this.redes[index].numeros_whatsapp = (this.redes[index].numeros_whatsapp || []).map(n => ({
                    uid: n.uid || this.makeUid(),
                    numero: n.numero || '',
                    nombre: n.nombre || '',
                    descripcion: n.descripcion || '',
                    imagen: n.imagen || '',
                    es_yo: !!n.es_yo,
                }));
                const numeroYoExistente = this.redes[index].numeros_whatsapp.find(n => 
                    n.es_yo === true && n.nombre && n.nombre.trim() === this.nombreUsuarioActual.trim()
                );
                if (numeroYoExistente) {
                    this.numeroYo = { ...numeroYoExistente };
                } else {
                    this.numeroYo = {
                        numero: '',
                        nombre: this.nombreUsuario,
                        descripcion: '',
                        imagen: this.imagenUsuario || '',
                        es_yo: true
                    };
                }
            },
            
            agregarNumeroYo() {
                if (!this.puedeAgregarYo) {
                    alert('No tienes permiso para agregar tu numero de contacto.');
                    return;
                }
                
                if (this.tieneMiNumeroYo) {
                    alert('Ya tienes tu numero de contacto agregado. Puedes editarlo en la seccion "Mi Numero" arriba.');
                    return;
                }
                
                if (this.modalWhatsApp.index !== null) {
                    if (!this.redes[this.modalWhatsApp.index].numeros_whatsapp) {
                        this.redes[this.modalWhatsApp.index].numeros_whatsapp = [];
                    }
                    const nuevoNumeroYo = {
                        uid: this.makeUid(),
                        numero: '',
                        nombre: this.nombreUsuario,
                        descripcion: '',
                        imagen: this.imagenUsuario || '',
                        es_yo: true
                    };
                    this.redes[this.modalWhatsApp.index].numeros_whatsapp = [
                        ...this.redes[this.modalWhatsApp.index].numeros_whatsapp,
                        nuevoNumeroYo
                    ];
                    this.numeroYo = { ...nuevoNumeroYo };
                }
            },
            
            agregarNumeroDesdeUsuario() {
                if (!this.esAdmin) {
                    alert('Solo los administradores pueden agregar numeros.');
                    return;
                }
                
                if (!this.usuarioSeleccionado) {
                    return;
                }
                
                if (this.modalWhatsApp.index === null) return;
                
                const usuario = this.usuarios.find(u => u.id == this.usuarioSeleccionado);
                if (!usuario) return;
                
                const numeros = this.redes[this.modalWhatsApp.index].numeros_whatsapp || [];
                const yaExiste = numeros.some(n => 
                    (n.numero && usuario.telefono && n.numero === usuario.telefono) ||
                    (n.nombre && n.nombre === usuario.nombre)
                );
                
                if (yaExiste) {
                    alert('Este usuario ya esta agregado en la lista.');
                    this.usuarioSeleccionado = '';
                    return;
                }
                
                if (!this.redes[this.modalWhatsApp.index].numeros_whatsapp) {
                    this.redes[this.modalWhatsApp.index].numeros_whatsapp = [];
                }
                
                const imagenParaGuardar = usuario.imagen_ruta || usuario.imagen || '';
                const nuevoNumero = {
                    uid: this.makeUid(),
                    numero: usuario.telefono || '',
                    nombre: usuario.nombre || '',
                    descripcion: '',
                    imagen: imagenParaGuardar,
                    es_yo: false
                };
                
                this.redes[this.modalWhatsApp.index].numeros_whatsapp = [
                    ...this.redes[this.modalWhatsApp.index].numeros_whatsapp,
                    nuevoNumero
                ];
                
                this.usuarioSeleccionado = '';
            },
            
            eliminarNumeroYo() {
                if (!this.puedeAgregarYo) {
                    alert('No tienes permiso para eliminar tu numero de contacto.');
                    return;
                }
                
                if (this.modalWhatsApp.index === null) return;
                
                if (!confirm('¿Eliminar tu numero de contacto?')) return;
                
                const numeros = [...(this.redes[this.modalWhatsApp.index].numeros_whatsapp || [])];
                const indexYo = numeros.findIndex(n => 
                    n.es_yo === true && n.nombre && n.nombre.trim() === this.nombreUsuarioActual.trim()
                );
                
                if (indexYo !== -1) {
                    const nuevosNumeros = numeros.filter((_, idx) => idx !== indexYo);
                    this.redes[this.modalWhatsApp.index].numeros_whatsapp = nuevosNumeros;
                }
                
                this.numeroYo = {
                    numero: '',
                    nombre: this.nombreUsuario,
                    descripcion: '',
                    imagen: this.imagenUsuario || '',
                    es_yo: true
                };
            },
            
            eliminarNumeroWhatsApp(uid) {
                if (!this.esAdmin) {
                    alert('Solo los administradores pueden eliminar numeros.');
                    return;
                }
                
                if (this.modalWhatsApp.index === null) return;
                
                if (!confirm('¿Eliminar este numero?')) return;
                
                const numeros = [...(this.redes[this.modalWhatsApp.index].numeros_whatsapp || [])];
                this.redes[this.modalWhatsApp.index].numeros_whatsapp = numeros.filter(n => n.uid !== uid);
            },
            
            guardarNumerosWhatsApp() {
                if (this.modalWhatsApp.index !== null && this.tieneMiNumeroYo) {
                    const numeros = this.redes[this.modalWhatsApp.index].numeros_whatsapp || [];
                    const indexYo = numeros.findIndex(n => 
                        n.es_yo === true && n.nombre && n.nombre.trim() === this.nombreUsuarioActual.trim()
                    );
                    if (indexYo !== -1) {
                        this.redes[this.modalWhatsApp.index].numeros_whatsapp[indexYo] = {
                            ...this.numeroYo,
                            es_yo: true
                        };
                    }
                }
                if (this.modalWhatsApp.index !== null) {
                    this.redes[this.modalWhatsApp.index].numeros_whatsapp = (this.redes[this.modalWhatsApp.index].numeros_whatsapp || []).map(n => {
                        const { uid, ...rest } = n;
                        return rest;
                    });
                }
                this.modalWhatsApp.show = false;
            }
        };
    }
    </script>
</div>

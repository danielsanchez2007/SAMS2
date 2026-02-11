@extends('layouts.app')

@section('title', 'Redes Sociales')

@section('content')
<div class="space-y-8" x-data="redesSocialesManager({{ json_encode($redesSociales) }})">
    <h1 class="text-3xl font-extrabold tracking-tight">
        <span class="bg-gradient-to-r from-[var(--tema-from)] to-[var(--tema-to)] bg-clip-text text-transparent">Redes Sociales y Enlaces</span>
    </h1>
    <p class="text-gray-600">Agrega cualquier red social, página web o enlace con su icono personalizado. Estos aparecerán en el footer del sistema.</p>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border-2 border-gray-900 p-8">
        <form action="{{ route('configuracion.redes-sociales.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
            @csrf

            <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Redes Sociales y Enlaces</h3>
                        <p class="text-sm text-gray-600 mt-1">Agrega cualquier red social, página web o enlace con su icono personalizado.</p>
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
                                <!-- Nombre -->
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nombre</label>
                                    <input type="text" 
                                        x-model="red.nombre" 
                                        :name="`redes_sociales[${index}][nombre]`"
                                        placeholder="Ej: Facebook, WhatsApp, Web" 
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                </div>
                                
                                <!-- URL -->
                                <div class="md:col-span-4">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">URL / Enlace</label>
                                    <input type="url" 
                                        x-model="red.url" 
                                        :name="`redes_sociales[${index}][url]`"
                                        placeholder="https://..." 
                                        class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                                </div>
                                
                                <!-- Tipo de Icono -->
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
                                
                                <!-- Icono SVG Predefinido -->
                                <div class="md:col-span-2" x-show="red.tipo_icono === 'svg'">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Icono SVG</label>
                                    <select x-model="red.icono_svg" 
                                        :name="`redes_sociales[${index}][icono_svg]`"
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
                                        <option value="phone">Teléfono</option>
                                    </select>
                                </div>
                                
                                <!-- Icono Imagen Personalizada -->
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
                                
                                <!-- Botón Eliminar -->
                                <div class="md:col-span-1 flex items-end">
                                    <button type="button" @click="eliminarRed(index)" 
                                        class="w-full px-3 py-2 rounded-lg bg-red-50 border-2 border-red-300 text-red-700 font-semibold text-sm hover:bg-red-100 transition-all">
                                        🗑️
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
    </div>
</div>

<script>
function redesSocialesManager(redesIniciales = []) {
    return {
        redes: redesIniciales.map(r => ({
            nombre: r.nombre || '',
            url: r.url || '',
            tipo_icono: r.tipo_icono || 'svg',
            icono_svg: r.icono_svg || '',
            icono_imagen: r.icono_imagen || '',
            icono_imagen_existente: r.icono_imagen || null
        })),
        
        agregarRed() {
            this.redes.push({
                nombre: '',
                url: '',
                tipo_icono: 'svg',
                icono_svg: '',
                icono_imagen: '',
                icono_imagen_existente: null
            });
        },
        
        eliminarRed(index) {
            this.redes.splice(index, 1);
        }
    };
}
</script>
@endsection

<div x-show="tab === 'encabezado'" x-cloak class="space-y-6">
    <form action="{{ route('configuracion.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="tab" value="encabezado">

        <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración del Encabezado</h3>
            <p class="text-sm text-gray-600 mb-6">Personaliza los textos que aparecen en el encabezado del sistema.</p>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nombre de la Empresa</label>
                    <input type="text" name="encabezado_texto_nombre_empresa" value="{{ $encabezado['texto_nombre_empresa'] ?? config('app.name') }}" 
                        placeholder="{{ config('app.name') }}" 
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    <p class="mt-1 text-xs text-gray-500">Nombre que aparece junto al logo en el encabezado</p>
                </div>
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

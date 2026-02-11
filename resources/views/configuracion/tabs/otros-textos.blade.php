<div x-show="tab === 'otros-textos'" x-cloak class="space-y-6">
    <form action="{{ route('configuracion.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="tab" value="otros-textos">

        <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Otros Textos del Sistema</h3>
            <p class="text-sm text-gray-600 mb-6">Personaliza otros textos que aparecen en diferentes partes del sistema.</p>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Texto de Bienvenida</label>
                    <input type="text" name="otros_texto_bienvenida" value="{{ $otrosTextos['texto_bienvenida'] ?? 'Bienvenido' }}" 
                        placeholder="Bienvenido" 
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Texto del Dashboard</label>
                    <input type="text" name="otros_texto_dashboard" value="{{ $otrosTextos['texto_dashboard'] ?? 'Panel de Control' }}" 
                        placeholder="Panel de Control" 
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
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

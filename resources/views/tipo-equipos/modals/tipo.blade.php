<div x-show="modalTipo" 
     x-cloak 
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    
    <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700/60" 
         @click.stop>
        
        <div class="p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-slate-100" 
                    x-text="formTipo.id ? 'Editar Tipo de Equipo' : 'Nuevo Tipo de Equipo'">
                </h3>
                <button 
                    type="button" 
                    @click="modalTipo = false" 
                    class="text-slate-400 hover:text-slate-200 transition-colors"
                    title="Cerrar"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-slate-400 text-sm mb-6" x-show="!formTipo.id">
                Define la categoría o clasificación del equipo (ejemplo: Equipo de medición, Herramienta eléctrica, Equipo de protección, Vehículo, Dispositivo electrónico...)
            </p>

            <form :action="formTipo.id ? '{{ url('tipo-equipos') }}/' + formTipo.id : '{{ route('tipo-equipos.store') }}'" 
                  method="POST" 
                  class="space-y-6">

                @csrf
                <template x-if="formTipo.id">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Nombre del Tipo <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        x-model="formTipo.nombre" 
                        required 
                        placeholder="Ej: Equipo de medición, Herramienta eléctrica, EPP, Vehículo utilitario, Máquina pesada, Dispositivo de comunicación..." 
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <button 
                        type="submit" 
                        class="flex-1 py-3.5 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    >
                        Guardar Tipo
                    </button>

                    <button 
                        type="button" 
                        @click="modalTipo = false" 
                        class="flex-1 py-3.5 rounded-xl border border-slate-700 text-slate-300 font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300"
                    >
                        Cancelar
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

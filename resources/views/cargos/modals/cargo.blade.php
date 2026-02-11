<div x-show="modalCargo" 
     x-cloak 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" 
     >
    
    <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700/60" @click.stop>
        
        <div class="p-6 sm:p-8">
            <h3 class="text-2xl font-bold text-slate-100 mb-6" 
                x-text="formCargo.id ? 'Editar Cargo' : 'Nuevo Cargo'">
            </h3>

            <form :action="formCargo.id ? '{{ url('cargos') }}/' + formCargo.id : '{{ route('cargos.store') }}'" 
                  method="POST" 
                  class="space-y-6">

                @csrf
                <template x-if="formCargo.id">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Nombre del Cargo <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        x-model="formCargo.nombre" 
                        required 
                        placeholder="Ej: Analista de Seguridad, Coordinador de Prevención..." 
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <button 
                        type="submit" 
                        class="flex-1 py-3.5 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    >
                        Guardar Cargo
                    </button>

                    <button 
                        type="button" 
                        @click="modalCargo = false" 
                        class="flex-1 py-3.5 rounded-xl border border-slate-700 text-slate-300 font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300"
                    >
                        Cancelar
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
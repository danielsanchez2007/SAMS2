<div x-show="modalEmpresa" 
     x-cloak 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" 
     >
    
    <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-700/60" @click.stop>
        
        <div class="p-6 sm:p-8">
            <h3 class="text-2xl font-bold text-slate-100 mb-6" 
                x-text="formEmpresa.id ? 'Editar Empresa' : 'Nueva Empresa'">
            </h3>

            <form :action="formEmpresa.id ? '{{ url('empresas/empresa') }}/' + formEmpresa.id : '{{ route('empresas.store.empresa') }}'" 
                  method="POST" 
                  class="space-y-6">

                @csrf
                <template x-if="formEmpresa.id">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Nombre de la Empresa -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Nombre de la Empresa <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        x-model="formEmpresa.nombre" 
                        required 
                        placeholder="Ej: Prevention World S.A.S., Soluciones Industriales Ltda., Seguridad Total..." 
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                </div>

                <!-- Selección de País -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        País <span class="text-rose-400">*</span>
                    </label>
                    <select 
                        name="pais" 
                        x-model="formEmpresa.pais" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                        <option value="">Seleccionar país</option>
                        @foreach(collect($paises ?? [])->all() as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                        <option value="Otro">Otro (especificar abajo)</option>
                    </select>

                    <!-- Campo "Otro" cuando se selecciona -->
                    <div x-show="formEmpresa.pais === 'Otro'" x-cloak class="mt-4">
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Especifique el país <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="pais_otro" 
                            x-model="formEmpresa.pais_otro" 
                            placeholder="Escriba el nombre del país" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                        >
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <button 
                        type="submit" 
                        class="flex-1 py-3.5 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    >
                        Guardar Empresa
                    </button>

                    <button 
                        type="button" 
                        @click="modalEmpresa = false" 
                        class="flex-1 py-3.5 rounded-xl border border-slate-700 text-slate-300 font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300"
                    >
                        Cancelar
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
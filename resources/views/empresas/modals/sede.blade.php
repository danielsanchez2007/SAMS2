<div x-show="modalSede" 
     x-cloak 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" 
     >
    
    <div class="tema-modal-opaco bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl border border-slate-700/60 overflow-y-auto max-h-[90vh]" @click.stop>
        
        <div class="p-6 sm:p-8">
            <h3 class="text-2xl font-bold text-slate-100 mb-6" 
                x-text="formSede.id ? 'Editar Sede' : 'Nueva Sede'">
            </h3>

            <p class="text-slate-400 text-sm mb-8">
                La ubicación (país, departamento, municipio) se hereda de la empresa seleccionada, pero puedes ajustarla.
            </p>

            <form :action="formSede.id ? '{{ url('empresas/sede') }}/' + formSede.id : '{{ route('empresas.store.sede') }}'" 
                  method="POST" 
                  class="space-y-7">

                @csrf
                <template x-if="formSede.id">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Empresa -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Empresa <span class="text-rose-400">*</span>
                    </label>
                    <select 
                        name="empresa_id" 
                        x-model="formSede.empresa_id" 
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                        <option value="">Seleccionar empresa</option>
                        @foreach($allEmpresas as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Nombre de la Sede -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Nombre de la Sede <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        x-model="formSede.nombre" 
                        required 
                        placeholder="Ej: Sede Principal, Sede Norte, Planta Industrial, Oficina Central..." 
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                </div>

                <!-- País -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        País <span class="text-rose-400">*</span>
                    </label>
                    <select 
                        name="pais" 
                        x-model="formSede.pais" 
                        @change="formSede.departamento = ''; formSede.municipio = ''; formSede.pais_otro = '';"
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                        <option value="">Seleccionar país</option>
                        @foreach($paises ?? [] as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                        <option value="Otro">Otro (especificar abajo)</option>
                    </select>

                    <div x-show="formSede.pais === 'Otro'" x-cloak class="mt-4">
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Especifique el país <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="pais_otro" 
                            x-model="formSede.pais_otro" 
                            placeholder="Escriba el nombre del país" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                        >
                    </div>
                </div>

                <!-- Departamento -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Departamento <span class="text-rose-400">*</span>
                    </label>
                    <select 
                        name="departamento" 
                        x-model="formSede.departamento" 
                        @change="formSede.municipio = ''; formSede.departamento_otro = '';"
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                        <option value="">Seleccionar departamento</option>
                        <template x-for="d in (departamentosPorPais[formSede.pais] || departamentosPorPais['default'] || [])" :key="d">
                            <option :value="d" x-text="d"></option>
                        </template>
                        <option value="Otro">Otro</option>
                    </select>

                    <div x-show="formSede.departamento === 'Otro'" x-cloak class="mt-4">
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Especifique el departamento <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="departamento_otro" 
                            x-model="formSede.departamento_otro" 
                            placeholder="Escriba el departamento" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                        >
                    </div>
                </div>

                <!-- Municipio -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Municipio <span class="text-rose-400">*</span>
                    </label>
                    <select 
                        name="municipio" 
                        x-model="formSede.municipio" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                    >
                        <option value="">Seleccionar municipio</option>
                        <template x-for="m in (municipiosPorDepartamento[formSede.departamento] || [])" :key="m">
                            <option :value="m" x-text="m"></option>
                        </template>
                        <option value="Otro">Otro</option>
                    </select>

                    <div x-show="formSede.municipio === 'Otro'" x-cloak class="mt-4">
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Especifique el municipio <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="municipio_otro" 
                            x-model="formSede.municipio_otro" 
                            placeholder="Escriba el municipio" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all shadow-inner"
                        >
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row gap-4 mt-10">
                    <button 
                        type="submit" 
                        class="flex-1 py-3.5 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    >
                        Guardar Sede
                    </button>

                    <button 
                        type="button" 
                        @click="modalSede = false" 
                        class="flex-1 py-3.5 rounded-xl border border-slate-700 text-slate-300 font-medium hover:bg-slate-800 hover:text-white hover:border-slate-600 transition-all duration-300"
                    >
                        Cancelar
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>  
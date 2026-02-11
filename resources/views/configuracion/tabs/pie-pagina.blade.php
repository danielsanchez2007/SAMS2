<div x-show="tab === 'pie-pagina'" x-cloak class="space-y-6">
    <form action="{{ route('configuracion.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="tab" value="pie-pagina">

        <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración del Pie de Página</h3>
            <p class="text-sm text-gray-600 mb-6">Personaliza los textos y enlaces que aparecen en el footer del sistema.</p>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Descripción del Pie de Página <span class="text-xs text-gray-500 font-normal">(opcional - dejar en blanco para ocultar)</span></label>
                    <textarea name="pie_descripcion" rows="3" 
                        placeholder="Estamos construyendo la nueva experiencia en el trabajo... (dejar en blanco para ocultar)" 
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">{{ $piePagina['descripcion'] ?? '' }}</textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Texto "Concepto" <span class="text-xs text-gray-500 font-normal">(opcional)</span></label>
                        <input type="text" name="pie_texto_concepto" value="{{ $piePagina['texto_concepto'] ?? '' }}" 
                            placeholder="Dejar en blanco para ocultar"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        <input type="text" name="pie_enlace_concepto" value="{{ $piePagina['enlace_concepto'] ?? '' }}" 
                            placeholder="URL del enlace (opcional)" 
                            class="w-full px-4 py-2.5 mt-2 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Texto "¿Quiénes somos?" <span class="text-xs text-gray-500 font-normal">(opcional)</span></label>
                        <input type="text" name="pie_texto_quienes_somos" value="{{ $piePagina['texto_quienes_somos'] ?? '' }}" 
                            placeholder="Dejar en blanco para ocultar"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        <input type="text" name="pie_enlace_quienes_somos" value="{{ $piePagina['enlace_quienes_somos'] ?? '' }}" 
                            placeholder="URL del enlace (opcional)" 
                            class="w-full px-4 py-2.5 mt-2 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Texto "FAQ" <span class="text-xs text-gray-500 font-normal">(opcional)</span></label>
                        <input type="text" name="pie_texto_faq" value="{{ $piePagina['texto_faq'] ?? '' }}" 
                            placeholder="Dejar en blanco para ocultar"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        <input type="text" name="pie_enlace_faq" value="{{ $piePagina['enlace_faq'] ?? '' }}" 
                            placeholder="URL del enlace (opcional)" 
                            class="w-full px-4 py-2.5 mt-2 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                </div>

                <div class="border-t border-gray-300 pt-5">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Enlaces Legales <span class="text-xs text-gray-500 font-normal">(opcional - dejar en blanco para ocultar)</span></h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Aviso Legal</label>
                            <input type="text" name="pie_texto_aviso_legal" value="{{ $piePagina['texto_aviso_legal'] ?? '' }}" 
                                placeholder="Dejar en blanco para ocultar"
                                class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <input type="text" name="pie_enlace_aviso_legal" value="{{ $piePagina['enlace_aviso_legal'] ?? '' }}" 
                                placeholder="URL (opcional)" 
                                class="w-full px-3 py-2 mt-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Términos y Condiciones</label>
                            <input type="text" name="pie_texto_terminos" value="{{ $piePagina['texto_terminos'] ?? '' }}" 
                                placeholder="Dejar en blanco para ocultar"
                                class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <input type="text" name="pie_enlace_terminos" value="{{ $piePagina['enlace_terminos'] ?? '' }}" 
                                placeholder="URL (opcional)" 
                                class="w-full px-3 py-2 mt-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Política de Privacidad</label>
                            <input type="text" name="pie_texto_privacidad" value="{{ $piePagina['texto_privacidad'] ?? '' }}" 
                                placeholder="Dejar en blanco para ocultar"
                                class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <input type="text" name="pie_enlace_privacidad" value="{{ $piePagina['enlace_privacidad'] ?? '' }}" 
                                placeholder="URL (opcional)" 
                                class="w-full px-3 py-2 mt-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Cookies</label>
                            <input type="text" name="pie_texto_cookies" value="{{ $piePagina['texto_cookies'] ?? '' }}" 
                                placeholder="Dejar en blanco para ocultar"
                                class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <input type="text" name="pie_enlace_cookies" value="{{ $piePagina['enlace_cookies'] ?? '' }}" 
                                placeholder="URL (opcional)" 
                                class="w-full px-3 py-2 mt-2 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Texto de Copyright <span class="text-xs text-gray-500 font-normal">(opcional)</span></label>
                    <input type="text" name="pie_texto_copyright" value="{{ $piePagina['texto_copyright'] ?? '' }}" 
                        placeholder="Dejar en blanco para ocultar" 
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

@extends('layouts.app')

@section('title', 'Gemini AI - Prueba')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="geminiApp()">
    
    <!-- Encabezado -->
    <div class="bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center gap-4 mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <div>
                <h1 class="text-3xl font-bold">Gemini AI - Centro de Pruebas</h1>
                <p class="text-indigo-100 mt-1">Prueba las capacidades de inteligencia artificial de Google Gemini</p>
            </div>
        </div>
        
        <!-- Estado del servicio -->
        <div class="mt-6 flex items-center gap-3 bg-white/10 rounded-lg px-4 py-3 backdrop-blur-sm">
            @if($isConfigured)
                <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="font-semibold">✓ Gemini está configurado y listo</span>
            @else
                <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                <span class="font-semibold">✗ Gemini no está configurado</span>
                <span class="text-sm text-indigo-100">(Verifica la API Key en .env)</span>
            @endif
        </div>
    </div>

    <!-- Formulario de generación de contenido -->
    <div class="bg-slate-900/70 backdrop-blur-md rounded-2xl border border-slate-800/60 shadow-2xl p-6">
        <h2 class="text-xl font-bold text-slate-100 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            Generar contenido con Gemini
        </h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Tu prompt:</label>
                <textarea 
                    x-model="prompt" 
                    rows="6" 
                    placeholder="Escribe aquí lo que quieres que Gemini genere... Ejemplo: 'Escribe un email profesional solicitando información sobre equipos médicos'"
                    class="w-full px-4 py-3 rounded-xl border border-slate-700/70 bg-slate-950/60 text-slate-100 placeholder-slate-500 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all"
                    :disabled="loading"
                ></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Temperatura (creatividad):</label>
                    <input 
                        type="number" 
                        x-model="temperature" 
                        min="0" 
                        max="2" 
                        step="0.1"
                        class="w-full px-4 py-2 rounded-lg border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none"
                        :disabled="loading"
                    >
                    <p class="text-xs text-slate-500 mt-1">0 = Preciso, 2 = Creativo</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tokens máximos:</label>
                    <input 
                        type="number" 
                        x-model="maxTokens" 
                        min="100" 
                        max="2048" 
                        step="100"
                        class="w-full px-4 py-2 rounded-lg border border-slate-700/70 bg-slate-950/60 text-slate-100 focus:border-indigo-500/70 focus:ring-2 focus:ring-indigo-500/20 outline-none"
                        :disabled="loading"
                    >
                    <p class="text-xs text-slate-500 mt-1">Longitud de respuesta</p>
                </div>
            </div>

            <button 
                @click="generate()" 
                :disabled="loading || !prompt.trim()"
                class="w-full py-3 px-6 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Generando...' : 'Generar con Gemini'"></span>
            </button>
        </div>
    </div>

    <!-- Respuesta -->
    <div x-show="response || error" x-cloak class="bg-slate-900/70 backdrop-blur-md rounded-2xl border border-slate-800/60 shadow-2xl p-6">
        <!-- Error -->
        <div x-show="error" class="bg-rose-950/60 border border-rose-800/50 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-rose-200 mb-1">Error</h3>
                    <p class="text-rose-300 text-sm" x-text="error"></p>
                </div>
            </div>
        </div>

        <!-- Respuesta exitosa -->
        <div x-show="response">
            <h3 class="text-lg font-bold text-slate-100 mb-3 flex items-center gap-2">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Respuesta de Gemini
            </h3>
            <div class="bg-slate-950/60 rounded-lg p-4 border border-slate-700/50">
                <p class="text-slate-200 whitespace-pre-wrap" x-text="response"></p>
            </div>
        </div>
    </div>

</div>

<script>
function geminiApp() {
    return {
        prompt: '',
        temperature: 0.7,
        maxTokens: 1000,
        loading: false,
        response: '',
        error: '',
        
        async generate() {
            if (!this.prompt.trim()) return;
            
            this.loading = true;
            this.error = '';
            this.response = '';
            
            try {
                const res = await fetch('{{ route('gemini.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        prompt: this.prompt,
                        temperature: parseFloat(this.temperature),
                        maxOutputTokens: parseInt(this.maxTokens)
                    })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    this.response = data.message;
                } else {
                    this.error = data.error || 'Error desconocido';
                }
            } catch (err) {
                this.error = 'Error de conexión: ' + err.message;
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection

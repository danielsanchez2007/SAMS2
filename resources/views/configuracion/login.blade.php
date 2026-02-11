@extends('layouts.app')

@section('title', 'Configuración de Login')

@section('content')
<div class="space-y-8">
    <h1 class="text-3xl font-extrabold tracking-tight">
        <span class="bg-gradient-to-r from-[var(--tema-from)] to-[var(--tema-to)] bg-clip-text text-transparent">Configuración de Login e Información</span>
    </h1>
    <p class="text-gray-600">Personaliza los textos y mensajes que aparecen en la página de login y la vista informativa.</p>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border-2 border-gray-900 p-8">
        <form action="{{ route('configuracion.login.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración de Login e Información</h3>
                <p class="text-sm text-gray-600 mb-6">Personaliza los textos y mensajes que aparecen en la página de login y la vista informativa.</p>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Título Principal</label>
                        <input type="text" name="login_titulo_principal" value="{{ $configLogin['titulo_principal'] ?? 'Sistema de Gestión SAMS' }}" 
                            placeholder="Sistema de Gestión SAMS" 
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        <p class="mt-1 text-xs text-gray-500">Título que aparece en la vista informativa</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Descripción Principal</label>
                        <textarea name="login_descripcion_principal" rows="3" 
                            placeholder="La solución integral para la gestión de equipos, usuarios y recursos empresariales..." 
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">{{ $configLogin['descripcion_principal'] ?? 'La solución integral para la gestión de equipos, usuarios y recursos empresariales. Tecnología moderna, interfaz intuitiva y máxima seguridad.' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Descripción que aparece debajo del título principal</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Texto Botón Login</label>
                            <input type="text" name="login_texto_boton_login" value="{{ $configLogin['texto_boton_login'] ?? 'Iniciar Sesión' }}" 
                                placeholder="Iniciar Sesión" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Texto Botón Información</label>
                            <input type="text" name="login_texto_boton_info" value="{{ $configLogin['texto_boton_info'] ?? 'Ver Información' }}" 
                                placeholder="Ver Información" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Texto Botón Registro</label>
                            <input type="text" name="login_texto_boton_registro" value="{{ $configLogin['texto_boton_registro'] ?? 'Registrarse' }}" 
                                placeholder="Registrarse" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Texto de Ayuda</label>
                        <input type="text" name="login_texto_ayuda" value="{{ $configLogin['texto_ayuda'] ?? '¿Problemas para acceder? Contacta al administrador del sistema.' }}" 
                            placeholder="¿Problemas para acceder? Contacta al administrador del sistema." 
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-900 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                        <p class="mt-1 text-xs text-gray-500">Mensaje de ayuda que aparece en la página de login</p>
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
</div>
@endsection

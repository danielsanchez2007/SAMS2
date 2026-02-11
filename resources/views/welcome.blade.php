@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

<div class="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 py-10 px-5 md:px-8 lg:px-12">
    <div class="mx-auto max-w-7xl space-y-12">

        <!-- Hero / Welcome Card -->
        <div class="relative overflow-hidden rounded-3xl border border-slate-800/60 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-950 shadow-2xl backdrop-blur-xl">
            <!-- Fondo decorativo sutil -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,0.08),transparent_40%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,rgba(99,102,241,0.06),transparent_50%)]"></div>

            <div class="relative px-8 py-14 md:px-12 lg:px-16 lg:py-20 text-center md:text-left">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight">
                    <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400 bg-clip-text text-transparent">
                        Panel Mega Admin
                    </span>
                </h1>

                <p class="mt-5 text-lg md:text-xl text-slate-300/90 max-w-3xl leading-relaxed">
                    Bienvenido al centro de control.  
                    Acceso completo habilitado. Gestiona usuarios, reportes, configuraciones y más desde una interfaz moderna y segura.
                </p>

                <!-- Opcional: botón principal -->
                <!--
                <div class="mt-10 flex flex-wrap justify-center md:justify-start gap-5">
                    <a href="{{ route('usuarios.index') }}"
                       class="inline-flex items-center gap-3 rounded-xl tema-gradient tema-gradient-hover px-8 py-4 text-base font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Gestionar Usuarios
                    </a>
                </div>
                -->
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 xl:gap-8">
            
            <!-- Card 1 -->
            <div class="group relative rounded-2xl border border-slate-800/70 bg-slate-900/60 backdrop-blur-md p-7 transition-all duration-300 hover:border-blue-700/50 hover:shadow-2xl hover:shadow-blue-950/30 hover:-translate-y-1.5">
                <div class="absolute -inset-px rounded-2xl bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <div class="relative flex items-start gap-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-900/70 to-blue-950/70 text-blue-300 transition-colors group-hover:from-blue-800/80 group-hover:to-blue-900/80">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-slate-100 group-hover:text-blue-300 transition-colors">
                            Acceso Completo
                        </h3>
                        <p class="mt-3 text-slate-400 leading-relaxed">
                            Todas las funcionalidades y permisos activados según tu rol de administrador.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="group relative rounded-2xl border border-slate-800/70 bg-slate-900/60 backdrop-blur-md p-7 transition-all duration-300 hover:border-indigo-700/50 hover:shadow-2xl hover:shadow-indigo-950/30 hover:-translate-y-1.5">
                <div class="absolute -inset-px rounded-2xl bg-gradient-to-br from-indigo-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <div class="relative flex items-start gap-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-900/70 to-indigo-950/70 text-indigo-300 transition-colors group-hover:from-indigo-800/80 group-hover:to-indigo-900/80">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-slate-100 group-hover:text-indigo-300 transition-colors">
                            Rápido & Moderno
                        </h3>
                        <p class="mt-3 text-slate-400 leading-relaxed">
                            Interfaz optimizada 2026 con alto rendimiento y diseño actualizado.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="group relative rounded-2xl border border-slate-800/70 bg-slate-900/60 backdrop-blur-md p-7 transition-all duration-300 hover:border-emerald-700/50 hover:shadow-2xl hover:shadow-emerald-950/30 hover:-translate-y-1.5">
                <div class="absolute -inset-px rounded-2xl bg-gradient-to-br from-emerald-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <div class="relative flex items-start gap-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-900/70 to-emerald-950/70 text-emerald-300 transition-colors group-hover:from-emerald-800/80 group-hover:to-emerald-900/80">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-slate-100 group-hover:text-emerald-300 transition-colors">
                            Alta Seguridad
                        </h3>
                        <p class="mt-3 text-slate-400 leading-relaxed">
                            Autenticación reforzada, auditoría en tiempo real y protección activa.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection
@extends('layouts.app')

@section('title', $title ?? 'Sección')

@section('content')

<div class="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 py-10 px-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-10">

        <!-- Tarjeta principal de sección en construcción -->
        <div class="relative overflow-hidden rounded-3xl border border-slate-800/60 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-950 shadow-2xl backdrop-blur-xl">
            <!-- Capas decorativas muy sutiles -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-950/6 via-transparent to-indigo-950/4 pointer-events-none"></div>
            <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_15%_25%,rgba(59,130,246,0.1),transparent_45%)]"></div>

            <div class="relative px-8 py-16 sm:px-12 lg:px-16 lg:py-20 text-center sm:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
                    <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400 bg-clip-text text-transparent">
                        {{ $title ?? 'Sección' }}
                    </span>
                </h1>

                <p class="mt-6 text-lg sm:text-xl text-slate-300/90 max-w-3xl mx-auto sm:mx-0 leading-relaxed">
                    {{ $description ?? 'Esta sección se encuentra en desarrollo. Estamos trabajando para ofrecerte la mejor experiencia posible.' }}
                </p>

                <div class="mt-10 inline-flex items-center gap-3 px-7 py-4 rounded-xl bg-slate-800/70 border border-slate-700/60 text-slate-300 text-base font-medium shadow-inner">
                    <svg class="w-6 h-6 text-amber-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>En construcción – Próximamente disponible</span>
                </div>

                <!-- Opcional: pequeño mensaje motivador o timeline -->
                <p class="mt-12 text-slate-500 text-sm">
                    Gracias por tu paciencia. Esta funcionalidad estará lista muy pronto.
                </p>
            </div>
        </div>

        <!-- Placeholder visual más limpio y moderno (opcional) -->
        <div class="flex justify-center">
            <div class="max-w-md w-full p-10 sm:p-12 bg-slate-900/50 backdrop-blur-md rounded-2xl border border-slate-800/60 text-center shadow-xl">
                <div class="mx-auto w-20 h-20 rounded-full bg-slate-800/70 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>

                <h3 class="text-xl font-semibold text-slate-200 mb-3">
                    Estamos trabajando aquí
                </h3>

                <p class="text-slate-400 leading-relaxed">
                    Muy pronto tendrás acceso completo a esta sección con todas sus funcionalidades.
                </p>
            </div>
        </div>

    </div>
</div>

@endsection 
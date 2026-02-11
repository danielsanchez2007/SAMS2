@php
    $temaConfig = \App\Helpers\TemaHelper::temaActual();
    $temaFrom = $temaConfig['from'] ?? '#005870';
    $temaTo = $temaConfig['to'] ?? '#2dbae1';
    $temaPrimary = $temaConfig['primary'] ?? '#1b819d';
    $temaPrimaryHover = $temaConfig['primary_hover'] ?? $temaFrom;
    $temaPrimaryLight = $temaConfig['primary_light'] ?? 'rgba(27, 129, 157, 0.2)';
    $logoMain = \Illuminate\Support\Facades\Cache::get(
        config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal')
    );
    $logoSecondary = \Illuminate\Support\Facades\Cache::get(
        config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario')
    );
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer contraseña — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (\App\Helpers\AssetHelper::shouldLoadViteAssets())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --tema-from: {{ $temaFrom }};
            --tema-to: {{ $temaTo }};
            --tema-primary: {{ $temaPrimary }};
            --tema-primary-hover: {{ $temaPrimaryHover }};
            --tema-primary-light: {{ $temaPrimaryLight }};
        }
        .tema-gradient { background: linear-gradient(to right, var(--tema-from), var(--tema-to)); }
        .tema-gradient-hover:hover { background: linear-gradient(to right, var(--tema-primary-hover), var(--tema-to)); }
        .tema-ring:focus { box-shadow: 0 0 0 2px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center antialiased p-4 relative overflow-auto text-gray-900">

    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(0,0,0,0.06),transparent_45%)] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,0,0,0.05),transparent_55%)] pointer-events-none"></div>

    <div class="relative w-full max-w-lg z-10 flex items-center justify-center min-h-screen py-8">

        <div class="bg-white rounded-3xl shadow-xl border-2 border-gray-900 overflow-hidden flex flex-col max-h-[90vh] w-full">

            <div class="tema-gradient p-8 text-center relative shrink-0">
                <div class="absolute inset-0 bg-black/5"></div>
                <div class="relative flex items-center justify-center gap-6">
                    <img src="{{ !empty($logoMain) ? asset('storage/' . $logoMain) : asset('img/logos/logoSams.png') }}" alt="SAMS" class="h-20 w-auto object-contain drop-shadow-lg">
                    <img src="{{ !empty($logoSecondary) ? asset('storage/' . $logoSecondary) : asset('img/logos/LOGO-INSTITUTO-PREVENTION-WORLD.png') }}" alt="Prevention World" class="h-20 w-auto object-contain drop-shadow-lg">
                </div>
            </div>

            <div class="p-6 sm:p-8 overflow-y-auto flex-1 min-h-0">
                @if (session('success'))
                    <div class="mb-8 p-5 rounded-2xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 text-base shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-8 p-5 rounded-2xl bg-rose-50 border border-rose-800/40 text-rose-800 text-base shadow-sm">
                        <ul class="list-disc list-inside space-y-1.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($dev_code))
                    <div class="mb-8 p-5 rounded-2xl bg-amber-50 border border-amber-700/40 text-amber-800 text-base shadow-sm">
                        <p class="font-semibold mb-1">{{ session('mail_error') ? 'No se pudo enviar el correo' : 'Correo no configurado (modo desarrollo)' }}</p>
                        <p class="text-sm text-amber-800 mb-2">{{ session('mail_error') ?? 'El envío de correo está en log, por eso no llegó al buzón. Usa este código para continuar:' }}</p>
                        <p class="text-2xl font-mono font-bold tracking-widest text-center py-2 bg-amber-100 rounded-xl">{{ $dev_code }}</p>
                    </div>
                @endif

                <h2 class="text-xl font-bold text-gray-900 mb-2">Nueva contraseña</h2>
                <p class="text-gray-600 text-sm mb-6">Ingresa el correo, el código que recibiste por correo y tu nueva contraseña.</p>

                <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-800 mb-2">Correo electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $email ?? '') }}"
                                required
                                class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-900 bg-white text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-semibold text-gray-800 mb-2">Código de 6 dígitos</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="code"
                                id="code"
                                value="{{ old('code') }}"
                                required
                                maxlength="6"
                                pattern="[0-9]{6}"
                                autocomplete="one-time-code"
                                class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-900 bg-white text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner tracking-[0.4em] text-center text-lg"
                                placeholder="000000"
                            >
                        </div>
                        <p class="text-xs text-gray-600 mt-1">El código que te llegó por correo (válido 60 minutos).</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-800 mb-2">Nueva contraseña</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                :type="show ? 'text' : 'password'"
                                name="password"
                                id="password"
                                required
                                minlength="6"
                                class="w-full pl-12 pr-12 py-3.5 rounded-xl border border-gray-900 bg-white text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner"
                                placeholder="Mínimo 6 caracteres"
                            >
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-gray-800">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-800 mb-2">Confirmar contraseña</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            minlength="6"
                            class="w-full px-4 py-3.5 rounded-xl border border-gray-900 bg-white text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner"
                            placeholder="Repite la contraseña"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 px-6 rounded-xl tema-gradient text-white font-semibold text-lg shadow-lg hover:shadow-xl tema-gradient-hover transform hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 focus:ring-offset-white"
                    >
                        Restablecer contraseña
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-300 text-center space-y-2">
                    <a href="{{ route('password.forgot') }}" class="block text-sm text-gray-600 hover:text-gray-900">¿No recibiste el código? Solicitar otro</a>
                    <a href="{{ route('login') }}" class="block text-sm text-gray-900 hover:text-gray-700 font-medium">← Volver al inicio de sesión</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

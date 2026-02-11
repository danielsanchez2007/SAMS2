@php
    $temaConfig = \App\Helpers\TemaHelper::temaActual();
    $temaFrom = $temaConfig['from'] ?? '#4f46e5';
    $temaTo = $temaConfig['to'] ?? '#7c3aed';
    $temaPrimary = $temaConfig['primary'] ?? '#6366f1';
    $temaPrimaryHover = $temaConfig['primary_hover'] ?? $temaFrom;
    $temaPrimaryLight = $temaConfig['primary_light'] ?? 'rgba(99, 102, 241, 0.2)';
    $temaGradientFull = $temaConfig['gradient_full'] ?? 'linear-gradient(to right, ' . $temaFrom . ', ' . $temaTo . ')';
    $logoMain = \Illuminate\Support\Facades\Cache::get(
        config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal')
    );
    $configLogin = \Illuminate\Support\Facades\Cache::get('sistema_config_login', [
        'texto_boton_login' => 'Iniciar Sesión',
        'texto_boton_info' => 'Ver Información',
        'texto_boton_registro' => 'Registrarse',
        'texto_ayuda' => '¿Problemas para acceder? Contacta al administrador del sistema.',
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
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
            --tema-gradient-full: {{ $temaGradientFull }};
        }
        .tema-gradient { background: var(--tema-gradient-full); }
        .tema-gradient-hover:hover { opacity: 0.95; filter: brightness(1.05); }
        .tema-ring:focus { box-shadow: 0 0 0 2px rgba(0,0,0,0.15); }
        /* Scrollbar personalizado */
        .login-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .login-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .login-scroll::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .login-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center antialiased p-4 relative overflow-hidden text-gray-900">

    <!-- Capas decorativas sutiles de fondo -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(0,0,0,0.06),transparent_45%)] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,0,0,0.05),transparent_55%)] pointer-events-none"></div>

    <div class="relative w-full max-w-sm z-10">

        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-xl border-2 border-gray-900 overflow-hidden max-h-[90vh] flex flex-col">

            <!-- Header con gradiente -->
            <div class="tema-gradient p-6 text-center relative flex-shrink-0">
                <div class="absolute inset-0 bg-black/5"></div>
                <div class="relative">
                    <div class="mb-2 flex justify-center">
                        @php
                            $logoUrl = !empty($logoMain) 
                                ? (str_starts_with($logoMain, 'storage/') 
                                    ? asset('storage/' . str_replace('storage/', '', $logoMain)) 
                                    : asset($logoMain))
                                : asset('img/logos/logoSams.png');
                        @endphp
                        <img src="{{ $logoUrl }}" 
                             alt="{{ config('app.name') }}" 
                             class="h-16 w-auto object-contain drop-shadow-lg"
                             onerror="this.onerror=null; this.src='{{ asset('img/logos/logoSams.png') }}';">
                    </div>
                </div>
            </div>

            <!-- Formulario con scroll -->
            <div class="p-6 overflow-y-auto flex-1 login-scroll" style="max-height: calc(90vh - 120px);">
                @if (session('success'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 text-sm shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-800/40 text-rose-800 text-sm shadow-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Campo Usuario -->
                    <div>
                        <label for="usuario" class="block text-xs font-semibold text-gray-800 mb-1.5">
                            Usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="usuario" 
                                id="usuario" 
                                value="{{ old('usuario') }}"
                                required
                                autofocus
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner"
                                placeholder="Ingresa tu usuario"
                            >
                        </div>
                        @error('usuario')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-800 mb-1.5">
                            Contraseña
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                :type="show ? 'text' : 'password'"
                                name="password" 
                                id="password" 
                                required
                                class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-900 bg-white text-sm text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner"
                                placeholder="Ingresa tu contraseña"
                            >
                            <button 
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-800 transition-colors duration-200"
                            >
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Olvidé mi contraseña -->
                    <div class="text-right">
                        <a href="{{ route('password.forgot') }}" class="text-xs text-gray-900 hover:text-gray-700 font-medium">
                            ¿Olvidé mi contraseña?
                        </a>
                    </div>

                    <!-- Botón de envío -->
                    <button 
                        type="submit"
                        class="w-full py-3 px-4 rounded-lg tema-gradient text-white font-semibold text-base shadow-lg hover:shadow-xl tema-gradient-hover transform hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 focus:ring-offset-white"
                    >
                        {{ $configLogin['texto_boton_login'] ?? 'Iniciar Sesión' }}
                    </button>
                </form>

                <!-- Botones adicionales -->
                <div class="mt-4 flex flex-col gap-2">
                    <a href="{{ route('informacion') }}" 
                       class="w-full py-2.5 px-4 rounded-lg bg-white border-2 border-gray-300 text-gray-800 font-semibold text-sm shadow-md hover:shadow-lg hover:border-[var(--tema-primary)] transition-all duration-300 text-center">
                        {{ $configLogin['texto_boton_info'] ?? 'Ver Información' }}
                    </a>
                    <a href="{{ route('register') }}" 
                       class="w-full py-2.5 px-4 rounded-lg bg-gray-100 border-2 border-gray-300 text-gray-800 font-semibold text-sm shadow-md hover:shadow-lg hover:bg-gray-200 transition-all duration-300 text-center">
                        {{ $configLogin['texto_boton_registro'] ?? 'Registrarse' }}
                    </a>
                </div>

                <!-- Info adicional -->
                <div class="mt-6 pt-4 border-t border-gray-300 text-center">
                    <p class="text-xs text-gray-600">
                        {{ $configLogin['texto_ayuda'] ?? '¿Problemas para acceder? Contacta al administrador del sistema.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600">
                © {{ date('Y') }} {{ config('app.name') }} • Todos los derechos reservados
            </p>
        </div>
    </div>

</body>
</html>
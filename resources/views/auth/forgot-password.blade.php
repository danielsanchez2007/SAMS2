@php
    $temaActual = \Illuminate\Support\Facades\Cache::get(
        config('temas_sistema.cache_key', 'sistema_tema_color'),
        config('temas_sistema.default', 'indigo')
    );
    $temaConfig = config('temas_sistema.temas.' . $temaActual, []);
    $temaFrom = $temaConfig['from'] ?? '#4f46e5';
    $temaTo = $temaConfig['to'] ?? '#7c3aed';
    $temaPrimary = $temaConfig['primary'] ?? '#6366f1';
    $temaPrimaryHover = $temaConfig['primary_hover'] ?? '#4f46e5';
    $temaPrimaryLight = $temaConfig['primary_light'] ?? 'rgba(99, 102, 241, 0.2)';
    $logoMain = \Illuminate\Support\Facades\Cache::get(
        config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal')
    );
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Olvidé mi contraseña — {{ config('app.name') }}</title>
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
        }
        .tema-gradient { background: linear-gradient(to right, var(--tema-from), var(--tema-to)); }
        .tema-gradient-hover:hover { background: linear-gradient(to right, var(--tema-primary-hover), var(--tema-to)); }
        .tema-ring:focus { box-shadow: 0 0 0 2px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center antialiased p-4 relative overflow-hidden text-gray-900">

    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(0,0,0,0.06),transparent_45%)] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,0,0,0.05),transparent_55%)] pointer-events-none"></div>

    <div class="relative w-full max-w-md z-10">

        <div class="bg-white rounded-3xl shadow-xl border-2 border-gray-900 overflow-hidden">

            <div class="tema-gradient p-10 text-center relative">
                <div class="absolute inset-0 bg-black/5"></div>
                <div class="relative">
                    <div class="mb-4 flex justify-center">
                        <img src="{{ !empty($logoMain) ? asset('storage/' . $logoMain) : asset('img/logos/logoSams.png') }}" alt="{{ config('app.name') }}" class="h-24 w-auto object-contain drop-shadow-lg">
                    </div>
                </div>
            </div>

            <div class="p-8 sm:p-10">
                @if ($errors->any())
                    <div class="mb-8 p-5 rounded-2xl bg-rose-50 border border-rose-800/40 text-rose-800 text-base shadow-sm">
                        <ul class="list-disc list-inside space-y-1.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h2 class="text-xl font-bold text-gray-900 mb-2">Recuperar contraseña</h2>
                <p class="text-gray-600 text-sm mb-6">Escribe el correo que tienes registrado en el sistema. Si existe, te enviaremos un código para restablecer tu contraseña.</p>

                <form method="POST" action="{{ route('password.forgot.send') }}" class="space-y-6">
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
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-900 bg-white text-gray-900 placeholder-gray-500 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20 outline-none transition-all duration-200 shadow-inner"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 px-6 rounded-xl tema-gradient text-white font-semibold text-lg shadow-lg hover:shadow-xl tema-gradient-hover transform hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 focus:ring-offset-white"
                    >
                        Enviar código
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-300 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-900 hover:text-gray-700 font-medium">
                        ← Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

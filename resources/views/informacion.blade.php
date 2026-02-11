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
    $redesSociales = \Illuminate\Support\Facades\Cache::get('sistema_redes_sociales', []);
    $configLogin = \Illuminate\Support\Facades\Cache::get('sistema_config_login', [
        'titulo_principal' => 'Sistema de Gestión SAMS',
        'descripcion_principal' => 'La solución integral para la gestión de equipos, usuarios y recursos empresariales. Tecnología moderna, interfaz intuitiva y máxima seguridad.',
        'texto_boton_login' => 'Iniciar Sesión',
        'texto_boton_info' => 'Ver Información',
        'texto_boton_registro' => 'Registrarse',
    ]);
    $piePagina = \Illuminate\Support\Facades\Cache::get('sistema_pie_pagina', [
        'descripcion' => 'Estamos construyendo la nueva experiencia en el trabajo. Sistema de gestión avanzado con tecnología moderna y diseño profesional.',
        'enlace_concepto' => '#',
        'texto_concepto' => 'Concepto',
        'enlace_quienes_somos' => '#',
        'texto_quienes_somos' => '¿Quiénes somos?',
        'enlace_faq' => '#',
        'texto_faq' => 'FAQ',
        'texto_aviso_legal' => 'Aviso legal',
        'enlace_aviso_legal' => '#',
        'texto_terminos' => 'Términos y condiciones de uso',
        'enlace_terminos' => '#',
        'texto_privacidad' => 'Política de privacidad',
        'enlace_privacidad' => '#',
        'texto_cookies' => 'Gestionar cookies',
        'enlace_cookies' => '#',
        'texto_copyright' => 'Todos los derechos reservados',
    ]);
    $hexToRgb = function (string $hex): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return '79, 70, 229';
        }
        $int = hexdec($hex);
        $r = ($int >> 16) & 255;
        $g = ($int >> 8) & 255;
        $b = $int & 255;
        return "{$r}, {$g}, {$b}";
    };
    $temaFromRgb = $hexToRgb($temaFrom);
    $temaToRgb = $hexToRgb($temaTo);
    $logoUrl = !empty($logoMain) 
        ? (str_starts_with($logoMain, 'storage/') 
            ? asset('storage/' . str_replace('storage/', '', $logoMain)) 
            : asset($logoMain))
        : asset('img/logos/logoSams.png');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SAMS — Sistema de Gestión</title>
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen antialiased text-gray-900">
    
    <!-- Header -->
    @include('components.header-publico')

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 px-6 lg:px-8" style="background: linear-gradient(135deg, rgba(var(--tema-from-rgb), 0.1) 0%, rgba(var(--tema-to-rgb), 0.05) 100%);">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight mb-6">
                <span style="background: var(--tema-gradient-full); -webkit-background-clip: text; background-clip: text; color: transparent;">
                    {{ $configLogin['titulo_principal'] ?? 'Sistema de Gestión SAMS' }}
                </span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-700 max-w-3xl mx-auto mb-10 leading-relaxed">
                {{ $configLogin['descripcion_principal'] ?? 'La solución integral para la gestión de equipos, usuarios y recursos empresariales. Tecnología moderna, interfaz intuitiva y máxima seguridad.' }}
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('login') }}" class="px-8 py-4 rounded-xl tema-gradient text-white font-semibold text-lg shadow-lg hover:shadow-xl tema-gradient-hover transform hover:-translate-y-1 transition-all duration-300">
                    {{ $configLogin['texto_boton_login'] ?? 'Iniciar Sesión' }}
                </a>
                <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl bg-white border-2 border-gray-300 text-gray-800 font-semibold text-lg shadow-lg hover:shadow-xl hover:border-[var(--tema-primary)] transition-all duration-300">
                    {{ $configLogin['texto_boton_registro'] ?? 'Registrarse' }}
                </a>
                <a href="#caracteristicas" class="px-8 py-4 rounded-xl bg-white border-2 border-gray-300 text-gray-800 font-semibold text-lg shadow-lg hover:shadow-xl hover:border-[var(--tema-primary)] transition-all duration-300">
                    Conocer Más
                </a>
            </div>
        </div>
    </section>

    <!-- Características -->
    <section id="caracteristicas" class="py-20 px-6 lg:px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-4xl font-extrabold text-center mb-16">
                <span style="background: var(--tema-gradient-full); -webkit-background-clip: text; background-clip: text; color: transparent;">
                    Características Principales
                </span>
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="group relative rounded-2xl border-2 border-gray-200 bg-white p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl tema-gradient text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Gestión Completa</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Administra usuarios, equipos, inventarios y recursos desde una única plataforma integrada.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="group relative rounded-2xl border-2 border-gray-200 bg-white p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl tema-gradient text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Rápido & Eficiente</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Interfaz optimizada con alto rendimiento y diseño moderno para máxima productividad.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="group relative rounded-2xl border-2 border-gray-200 bg-white p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl tema-gradient text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Seguridad Avanzada</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Autenticación reforzada, auditoría en tiempo real y protección de datos empresariales.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="group relative rounded-2xl border-2 border-gray-200 bg-white p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl tema-gradient text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Reportes Inteligentes</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Genera reportes detallados y análisis de datos para una toma de decisiones informada.
                    </p>
                </div>

                <!-- Card 5 -->
                <div class="group relative rounded-2xl border-2 border-gray-200 bg-white p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl tema-gradient text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Personalizable</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Adapta el sistema a las necesidades de tu empresa con temas, logos y configuraciones personalizadas.
                    </p>
                </div>

                <!-- Card 6 -->
                <div class="group relative rounded-2xl border-2 border-gray-200 bg-white p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl tema-gradient text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Soporte 24/7</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Asistencia técnica disponible las 24 horas del día para resolver cualquier consulta o problema.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto" style="background: linear-gradient(90deg, rgba({{ $temaFromRgb }}, 0.95), rgba({{ $temaToRgb }}, 0.9)); color: #fff;">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
            @php
                $tieneDescripcion = !empty($piePagina['descripcion'] ?? '');
                $tieneConcepto = !empty($piePagina['texto_concepto'] ?? '');
                $tieneQuienesSomos = !empty($piePagina['texto_quienes_somos'] ?? '');
                $tieneFaq = !empty($piePagina['texto_faq'] ?? '');
                $tieneAcercaDe = $tieneConcepto || $tieneQuienesSomos || $tieneFaq;
                $tieneRedesSociales = is_array($redesSociales) && count($redesSociales) > 0;
                $columnasVisibles = ($tieneDescripcion ? 1 : 0) + ($tieneAcercaDe ? 1 : 0) + ($tieneRedesSociales ? 1 : 0);
                $gridCols = $columnasVisibles > 0 ? 'md:grid-cols-' . min($columnasVisibles, 3) : 'md:grid-cols-1';
            @endphp
            @if($tieneDescripcion || $tieneAcercaDe || $tieneRedesSociales)
            <div class="grid grid-cols-1 {{ $gridCols }} gap-8 mb-8 pb-8 border-b border-white/20">
                @if($tieneDescripcion)
                <div>
                    <div class="mb-4">
                        <img src="{{ $logoUrl }}" alt="SAMS" class="h-12 w-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('img/logos/logoSams.png') }}';">
                    </div>
                    <p class="text-white/90 text-sm leading-relaxed">
                        {{ $piePagina['descripcion'] }}
                    </p>
                </div>
                @endif
                @if($tieneAcercaDe)
                <div>
                    <h3 class="text-white font-bold text-sm mb-4 uppercase tracking-wide">Acerca de</h3>
                    <ul class="space-y-2">
                        @if($tieneConcepto)
                        <li><a href="{{ !empty($piePagina['enlace_concepto']) ? $piePagina['enlace_concepto'] : '#' }}" class="text-white/80 hover:text-white text-sm transition-colors">{{ $piePagina['texto_concepto'] }}</a></li>
                        @endif
                        @if($tieneQuienesSomos)
                        <li><a href="{{ !empty($piePagina['enlace_quienes_somos']) ? $piePagina['enlace_quienes_somos'] : '#' }}" class="text-white/80 hover:text-white text-sm transition-colors">{{ $piePagina['texto_quienes_somos'] }}</a></li>
                        @endif
                        @if($tieneFaq)
                        <li><a href="{{ !empty($piePagina['enlace_faq']) ? $piePagina['enlace_faq'] : '#' }}" class="text-white/80 hover:text-white text-sm transition-colors">{{ $piePagina['texto_faq'] }}</a></li>
                        @endif
                    </ul>
                </div>
                @endif
                @if($tieneRedesSociales)
                <div>
                    <h3 class="text-white font-bold text-sm mb-4 uppercase tracking-wide">Conócenos</h3>
                    <div class="flex items-center gap-4 flex-wrap">
                        @if(is_array($redesSociales) && count($redesSociales) > 0)
                            @foreach($redesSociales as $index => $red)
                                @if(!empty($red['nombre']))
                                    @php
                                        $esWhatsApp = ($red['icono_svg'] ?? '') === 'whatsapp';
                                        $tieneNumerosWhatsApp = !empty($red['numeros_whatsapp']) && is_array($red['numeros_whatsapp']) && count($red['numeros_whatsapp']) > 0;
                                        $tieneMultiplesNumeros = $esWhatsApp && $tieneNumerosWhatsApp && count($red['numeros_whatsapp']) > 1;
                                        $urlRed = trim($red['url'] ?? '');
                                        $tieneUrlValida = !empty($urlRed) && $urlRed !== 'whatsapp://' && $urlRed !== '#';
                                        $tieneIcono = !empty($red['icono_svg']) || !empty($red['icono_imagen']);
                                    @endphp
                                    @if($tieneMultiplesNumeros)
                                        {{-- WhatsApp con múltiples números: mostrar selector --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" 
                                                class="w-14 h-14 rounded-full bg-[#25D366] hover:bg-[#20BA5A] flex items-center justify-center transition-all group-hover:scale-110 shadow-lg hover:shadow-xl border-2 border-white/20"
                                                title="{{ $red['nombre'] }}">
                                                @include('components.icono-red-social', [
                                                    'tipo' => $red['tipo_icono'] ?? 'svg',
                                                    'icono' => $red['icono_svg'] ?? '',
                                                    'imagen' => $red['icono_imagen'] ?? '',
                                                    'nombre' => $red['nombre']
                                                ])
                                            </button>
                                            {{-- Modal centrado para seleccionar número de WhatsApp --}}
                                            <div x-show="open" x-cloak 
                                                class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4"
                                                @click.away="open = false"
                                                @keydown.escape.window="open = false">
                                                <div class="bg-white dark:bg-[#1b1b18] rounded-2xl shadow-2xl border-2 border-gray-200 dark:border-[#3E3E3A] w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col"
                                                     @click.stop>
                                                    {{-- Header del modal --}}
                                                    <div class="p-6 border-b border-gray-200 dark:border-[#3E3E3A] bg-gradient-to-r from-green-50 to-green-100/50 dark:from-[#1b1b18] dark:to-[#2a2a27]">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-12 h-12 rounded-full bg-[#25D366] flex items-center justify-center shadow-lg">
                                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                                    </svg>
                                                                </div>
                                                                <div>
                                                                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Selecciona un número</h3>
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">Elige un contacto para iniciar una conversación</p>
                                                                </div>
                                                            </div>
                                                            <button @click="open = false" 
                                                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#3E3E3A] rounded-lg transition">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    {{-- Contenido del modal --}}
                                                    <div class="p-6 overflow-y-auto flex-1">
                                                        <div class="space-y-3">
                                                            @foreach($red['numeros_whatsapp'] as $numero)
                                                                @php
                                                                    $imagenUrl = null;
                                                                    if (!empty($numero['imagen'])) {
                                                                        $imagenPath = trim($numero['imagen']);
                                                                        // Si ya es una URL completa (http/https), usarla directamente
                                                                        if (str_starts_with($imagenPath, 'http://') || str_starts_with($imagenPath, 'https://')) {
                                                                            $imagenUrl = $imagenPath;
                                                                        }
                                                                        // Si empieza con storage/, usar asset('storage/...')
                                                                        elseif (str_starts_with($imagenPath, 'storage/')) {
                                                                            $imagenUrl = asset('storage/' . str_replace('storage/', '', $imagenPath));
                                                                        }
                                                                        // Si empieza con public/img/, usar asset directamente
                                                                        elseif (str_starts_with($imagenPath, 'public/img/')) {
                                                                            $imagenUrl = asset(str_replace('public/', '', $imagenPath));
                                                                        }
                                                                        // Si empieza con img/, usar asset directamente
                                                                        elseif (str_starts_with($imagenPath, 'img/')) {
                                                                            $imagenUrl = asset($imagenPath);
                                                                        }
                                                                        // Para cualquier otra ruta, intentar con asset
                                                                        else {
                                                                            $imagenUrl = asset($imagenPath);
                                                                        }
                                                                    }
                                                                @endphp
                                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $numero['numero']) }}" 
                                                                   target="_blank" rel="noopener noreferrer"
                                                                   class="flex items-center gap-4 p-5 rounded-xl hover:bg-gray-50 dark:hover:bg-[#2a2a27] text-gray-900 dark:text-gray-100 border-2 border-gray-200 dark:border-[#3E3E3A] hover:border-[var(--tema-primary)]/50 hover:shadow-lg transition-all group cursor-pointer">
                                                                    <div class="flex-shrink-0">
                                                                        @php
                                                                            // Intentar siempre mostrar la imagen si existe, incluso si la URL no se resolvió correctamente
                                                                            $mostrarImagen = !empty($numero['imagen']);
                                                                            if ($mostrarImagen && !$imagenUrl) {
                                                                                // Si no se resolvió la URL pero hay una ruta, intentar directamente
                                                                                $imagenUrl = asset($numero['imagen']);
                                                                            }
                                                                        @endphp
                                                                        @if($mostrarImagen && $imagenUrl)
                                                                            <img src="{{ $imagenUrl }}" alt="{{ $numero['nombre'] ?: $numero['numero'] }}" 
                                                                                 class="w-20 h-20 rounded-full object-cover border-4 border-[var(--tema-primary)]/20 shadow-lg group-hover:border-[var(--tema-primary)]/50 transition-all"
                                                                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                                                 loading="lazy">
                                                                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--tema-primary)]/20 to-[var(--tema-primary)]/10 flex items-center justify-center border-4 border-[var(--tema-primary)]/20 shadow-lg group-hover:border-[var(--tema-primary)]/50 transition-all" style="display: none;">
                                                                                <svg class="w-12 h-12 text-[var(--tema-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                                </svg>
                                                                            </div>
                                                                        @else
                                                                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--tema-primary)]/20 to-[var(--tema-primary)]/10 flex items-center justify-center border-4 border-[var(--tema-primary)]/20 shadow-lg group-hover:border-[var(--tema-primary)]/50 transition-all">
                                                                                <svg class="w-12 h-12 text-[var(--tema-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                                </svg>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-1 group-hover:text-[var(--tema-primary)] transition-colors">
                                                                            {{ $numero['nombre'] ?: $numero['numero'] }}
                                                                        </div>
                                                                        @if(!empty($numero['descripcion']))
                                                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $numero['descripcion'] }}</div>
                                                                        @endif
                                                                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-500">
                                                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                                                            </svg>
                                                                            <span class="font-medium">{{ $numero['numero'] }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-shrink-0 flex items-center">
                                                                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center group-hover:bg-[#25D366] transition-colors">
                                                                            <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($esWhatsApp && $tieneNumerosWhatsApp && !empty($red['numeros_whatsapp'][0]['numero'] ?? ''))
                                        {{-- WhatsApp con un solo número --}}
                                        @php
                                            $numero = $red['numeros_whatsapp'][0];
                                            $urlWhatsApp = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $numero['numero']);
                                        @endphp
                                        <a href="{{ $urlWhatsApp }}" target="_blank" rel="noopener noreferrer" 
                                           class="w-14 h-14 rounded-full bg-[#25D366] hover:bg-[#20BA5A] flex items-center justify-center transition-all group-hover:scale-110 shadow-lg hover:shadow-xl border-2 border-white/20"
                                           title="{{ $numero['nombre'] ?: $numero['numero'] }}">
                                            @include('components.icono-red-social', [
                                                'tipo' => $red['tipo_icono'] ?? 'svg',
                                                'icono' => $red['icono_svg'] ?? '',
                                                'imagen' => $red['icono_imagen'] ?? '',
                                                'nombre' => $red['nombre']
                                            ])
                                        </a>
                                    @elseif($tieneUrlValida || ($tieneIcono && !$esWhatsApp) || ($esWhatsApp && $tieneIcono && !$tieneNumerosWhatsApp))
                                        {{-- Otras redes sociales --}}
                                        <div class="relative group" x-data="{ open: false }">
                                            <a href="{{ $tieneUrlValida ? $urlRed : '#' }}" target="{{ $tieneUrlValida ? '_blank' : '_self' }}" rel="noopener noreferrer" 
                                               @mouseenter="open = true" @mouseleave="open = false"
                                               class="w-14 h-14 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all group-hover:scale-110 shadow-lg hover:shadow-xl"
                                               title="{{ $red['nombre'] }}">
                                                @include('components.icono-red-social', [
                                                    'tipo' => $red['tipo_icono'] ?? 'svg',
                                                    'icono' => $red['icono_svg'] ?? '',
                                                    'imagen' => $red['icono_imagen'] ?? '',
                                                    'nombre' => $red['nombre']
                                                ])
                                            </a>
                                            <div x-show="open" x-cloak
                                                class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 bg-white dark:bg-[#1b1b18] rounded-xl shadow-2xl border-2 border-gray-200 dark:border-[#3E3E3A] p-4 min-w-[240px] z-50 pointer-events-none">
                                                <div class="flex items-center gap-4">
                                                    <div class="flex-shrink-0">
                                                        @if(!empty($red['icono_imagen']))
                                                            <img src="{{ str_starts_with($red['icono_imagen'], 'http') ? $red['icono_imagen'] : asset($red['icono_imagen']) }}" alt="{{ $red['nombre'] }}" 
                                                                 class="w-16 h-16 rounded-full object-cover border-2 border-gray-200 dark:border-[#3E3E3A] shadow-lg">
                                                        @else
                                                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[var(--tema-primary)]/20 to-[var(--tema-primary)]/10 flex items-center justify-center border-2 border-[var(--tema-primary)]/30 shadow-lg">
                                                                @include('components.icono-red-social', [
                                                                    'tipo' => $red['tipo_icono'] ?? 'svg',
                                                                    'icono' => $red['icono_svg'] ?? '',
                                                                    'imagen' => $red['icono_imagen'] ?? '',
                                                                    'nombre' => $red['nombre'],
                                                                    'class' => 'w-10 h-10 text-[var(--tema-primary)]'
                                                                ])
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-bold text-base text-gray-900 dark:text-gray-100 mb-1">
                                                            {{ $red['nombre'] }}
                                                        </div>
                                                        @if($tieneUrlValida)
                                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">
                                                                {{ parse_url($urlRed, PHP_URL_HOST) ?: $urlRed }}
                                                            </div>
                                                        @else
                                                            <div class="text-xs text-gray-500 dark:text-gray-400 italic">
                                                                Sin enlace configurado
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif
            @php
                $tieneAvisoLegal = !empty($piePagina['texto_aviso_legal'] ?? '');
                $tieneTerminos = !empty($piePagina['texto_terminos'] ?? '');
                $tienePrivacidad = !empty($piePagina['texto_privacidad'] ?? '');
                $tieneCookies = !empty($piePagina['texto_cookies'] ?? '');
                $tieneEnlacesLegales = $tieneAvisoLegal || $tieneTerminos || $tienePrivacidad || $tieneCookies;
                $tieneCopyright = !empty($piePagina['texto_copyright'] ?? '');
            @endphp
            @if($tieneEnlacesLegales || $tieneCopyright)
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
                @if($tieneEnlacesLegales)
                <div class="flex flex-wrap items-center gap-4 text-white/80">
                    @if($tieneAvisoLegal)
                    <a href="{{ !empty($piePagina['enlace_aviso_legal']) ? $piePagina['enlace_aviso_legal'] : '#' }}" class="hover:text-white transition-colors">{{ $piePagina['texto_aviso_legal'] }}</a>
                    @endif
                    @if($tieneTerminos)
                    <a href="{{ !empty($piePagina['enlace_terminos']) ? $piePagina['enlace_terminos'] : '#' }}" class="hover:text-white transition-colors">{{ $piePagina['texto_terminos'] }}</a>
                    @endif
                    @if($tienePrivacidad)
                    <a href="{{ !empty($piePagina['enlace_privacidad']) ? $piePagina['enlace_privacidad'] : '#' }}" class="hover:text-white transition-colors">{{ $piePagina['texto_privacidad'] }}</a>
                    @endif
                    @if($tieneCookies)
                    <a href="{{ !empty($piePagina['enlace_cookies']) ? $piePagina['enlace_cookies'] : '#' }}" class="hover:text-white transition-colors">{{ $piePagina['texto_cookies'] }}</a>
                    @endif
                </div>
                @endif
                @if($tieneCopyright)
                <div class="text-white/80">
                    @php
                        $encabezado = \Illuminate\Support\Facades\Cache::get('sistema_encabezado', [
                            'texto_nombre_empresa' => config('app.name'),
                        ]);
                    @endphp
                    <span>© {{ date('Y') }} {{ $encabezado['texto_nombre_empresa'] ?? config('app.name') }} • {{ $piePagina['texto_copyright'] }}</span>
                </div>
                @elseif(!$tieneEnlacesLegales)
                <div class="text-white/80">
                    @php
                        $encabezado = \Illuminate\Support\Facades\Cache::get('sistema_encabezado', [
                            'texto_nombre_empresa' => config('app.name'),
                        ]);
                    @endphp
                    <span>© {{ date('Y') }} {{ $encabezado['texto_nombre_empresa'] ?? config('app.name') }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>
    </footer>

</body>
</html>

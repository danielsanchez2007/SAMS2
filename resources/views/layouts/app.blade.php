@php
    $user = session('sams2_user');
    use App\Helpers\PermisoHelper;
    $temaConfig = $temaConfig ?? config('temas_sistema.temas.' . (config('temas_sistema.default') ?? 'indigo'), []);
    $temaFrom = $temaConfig['from'] ?? '#4f46e5';
    $temaTo = $temaConfig['to'] ?? '#7c3aed';
    $temaPrimary = $temaConfig['primary'] ?? '#6366f1';
    $temaPrimaryHover = $temaConfig['primary_hover'] ?? '#4f46e5';
    $temaPrimaryLight = $temaConfig['primary_light'] ?? 'rgba(99, 102, 241, 0.2)';
    $temaPrimaryText = $temaConfig['primary_text'] ?? '#a5b4fc';
    $temaRing = $temaConfig['ring'] ?? '99, 102, 241';
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
    $temaPrimaryRgb = $hexToRgb($temaPrimary);
    $redesSociales = \Illuminate\Support\Facades\Cache::get('sistema_redes_sociales', []);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        :root {
            --tema-from: {{ $temaFrom }};
            --tema-to: {{ $temaTo }};
            --tema-primary: {{ $temaPrimary }};
            --tema-primary-hover: {{ $temaPrimaryHover }};
            --tema-primary-light: {{ $temaPrimaryLight }};
            --tema-primary-text: {{ $temaPrimaryText }};
            --tema-ring: {{ $temaRing }};
            --tema-from-rgb: {{ $temaFromRgb }};
            --tema-to-rgb: {{ $temaToRgb }};
            --tema-primary-rgb: {{ $temaPrimaryRgb }};
            --tema-gradient-full: {{ $temaConfig['gradient_full'] ?? 'linear-gradient(to right, var(--tema-from), var(--tema-to))' }};
        }
        .tema-gradient { background: var(--tema-gradient-full); }
        .tema-gradient-hover:hover { background: linear-gradient(to right, var(--tema-primary-hover), var(--tema-to)); }
        .tema-bg { background-color: var(--tema-primary); }
        .tema-bg-light { background-color: var(--tema-primary-light); }
        .tema-text { color: var(--tema-primary-text); }
        .tema-ring:focus { box-shadow: 0 0 0 2px rgba(var(--tema-ring), 0.3); }
        .tema-border { border-color: var(--tema-primary); }
        .hover\:tema-text:hover { color: var(--tema-primary-text); }
        .active-tema { background: var(--tema-primary-light); color: var(--tema-primary); font-weight: 600; }
        /* Todo el sistema con el tema: fondos con más color, poco blanco */
        body.tema-base-claro { background: linear-gradient(135deg, rgba(var(--tema-from-rgb), 0.25) 0%, rgba(var(--tema-to-rgb), 0.18) 50%, rgba(var(--tema-from-rgb), 0.2) 100%); color: #0f172a; }
        body.tema-base-claro .tema-header { background: linear-gradient(90deg, rgba(var(--tema-from-rgb), 0.95), rgba(var(--tema-to-rgb), 0.9)); color: #fff; border: none; box-shadow: 0 2px 10px rgba(var(--tema-primary-rgb), 0.3); }
        body.tema-base-claro .tema-header a, body.tema-base-claro .tema-header span { color: #fff !important; }
        body.tema-base-claro .tema-header button { background: rgba(255,255,255,0.2) !important; border-color: rgba(255,255,255,0.5) !important; color: #fff !important; }
        body.tema-base-claro .tema-header button:hover { background: rgba(255,255,255,0.3) !important; }
        body.tema-base-claro .tema-sidebar { background: linear-gradient(180deg, rgba(var(--tema-from-rgb), 0.28), rgba(var(--tema-to-rgb), 0.18)); border-right: 2px solid var(--tema-primary); }
        body.tema-base-claro .tema-sidebar a:hover { background: rgba(var(--tema-primary-rgb), 0.2); }
        /* Texto del sidebar en tema claro - oscuro para legibilidad */
        body.tema-base-claro .tema-sidebar,
        body.tema-base-claro .tema-sidebar * {
            color: #1f2937 !important;
        }
        body.tema-base-claro .tema-sidebar a,
        body.tema-base-claro .tema-sidebar button,
        body.tema-base-claro .tema-sidebar span {
            color: #1f2937 !important;
        }
        body.tema-base-claro .tema-sidebar .tema-gradient,
        body.tema-base-claro .tema-sidebar .tema-gradient * {
            color: #fff !important;
        }
        /* Clase helper para texto del sidebar */
        .tema-sidebar-text {
            color: #1f2937;
        }
        /* Texto del sidebar en tema oscuro - blanco para legibilidad */
        body.dark .tema-sidebar-text,
        .dark .tema-sidebar-text,
        [class*="dark"] .tema-sidebar-text {
            color: #ffffff !important;
        }
        body.tema-base-oscuro .tema-sidebar-text,
        body.tema-base-oscuro .tema-sidebar,
        body.tema-base-oscuro .tema-sidebar * {
            color: #ffffff !important;
        }
        body.tema-base-oscuro .tema-sidebar a,
        body.tema-base-oscuro .tema-sidebar button,
        body.tema-base-oscuro .tema-sidebar span {
            color: #ffffff !important;
        }
        body.tema-base-claro .tema-main { background: linear-gradient(180deg, rgba(var(--tema-from-rgb), 0.18) 0%, rgba(var(--tema-to-rgb), 0.12) 100%); }
        body.tema-base-claro .tema-footer { background: linear-gradient(90deg, rgba(var(--tema-from-rgb), 0.95), rgba(var(--tema-to-rgb), 0.9)); color: #fff; border: none; box-shadow: 0 -2px 10px rgba(var(--tema-primary-rgb), 0.2); }
        body.tema-base-claro .tema-footer span { color: rgba(255,255,255,0.95) !important; }
        body.tema-base-claro .tema-panel { background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(var(--tema-from-rgb), 0.18)); border: 2px solid var(--tema-primary); box-shadow: 0 4px 20px rgba(var(--tema-primary-rgb), 0.25); }
        body.tema-base-claro .tema-dropdown { background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(var(--tema-from-rgb), 0.18)); border: 2px solid var(--tema-primary); box-shadow: 0 10px 25px rgba(var(--tema-primary-rgb), 0.25); }
        body.tema-base-claro .tema-card { background: linear-gradient(135deg, rgba(255,255,255,0.85), rgba(var(--tema-from-rgb), 0.15)); border: 2px solid var(--tema-primary); box-shadow: 0 2px 12px rgba(var(--tema-primary-rgb), 0.2); }
        .tema-modal-backdrop { background: rgba(0,0,0,0.6); }
        .tema-modal-content { background: #fff; border: 2px solid var(--tema-primary); border-radius: 0.75rem; box-shadow: 0 25px 50px rgba(var(--tema-primary-rgb), 0.25); }
        /* Tablas: fondos con color del tema, encabezado con gradiente */
        body.tema-base-claro table { background: rgba(var(--tema-primary-rgb), 0.08); color: #0f172a; border: 2px solid var(--tema-primary); }
        body.tema-base-claro thead { background: var(--tema-gradient-full); }
        body.tema-base-claro thead th { color: #fff; border-color: rgba(255,255,255,0.4); font-weight: 600; }
        body.tema-base-claro tbody tr { background: rgba(255,255,255,0.7); }
        body.tema-base-claro tbody tr:nth-child(even) { background: rgba(var(--tema-primary-rgb), 0.12); }
        body.tema-base-claro tbody tr:hover { background: rgba(var(--tema-primary-rgb), 0.22); }
        body.tema-base-claro td,
        body.tema-base-claro th { border-color: rgba(var(--tema-primary-rgb), 0.5); }
        body.tema-base-claro .table-border,
        body.tema-base-claro .table-bordered { border-color: #0f172a; }
        body.tema-base-claro .tema-modal-content,
        body.tema-base-claro .tema-modal-content * { color: #0f172a; }
        body.tema-base-claro input,
        body.tema-base-claro select,
        body.tema-base-claro textarea {
            background: rgba(255,255,255,0.85);
            color: #0f172a;
            border-color: var(--tema-primary);
        }
        body.tema-base-claro input::placeholder,
        body.tema-base-claro textarea::placeholder { color: #64748b; }
        /* Contenedores de tablas: fondo con tinte del tema (no blanco puro) */
        body.tema-base-claro .tema-table-wrap { background: linear-gradient(180deg, rgba(var(--tema-from-rgb), 0.15), rgba(var(--tema-to-rgb), 0.1)) !important; }
        /* Forzar vistas oscuras a fondos con tinte tema (reducir blanco) */
        body.tema-base-claro [class*="bg-slate-9"],
        body.tema-base-claro [class*="bg-slate-8"],
        body.tema-base-claro [class*="bg-slate-7"],
        body.tema-base-claro [class*="bg-slate-6"],
        body.tema-base-claro [class*="bg-slate-5"] { background: rgba(var(--tema-primary-rgb), 0.1) !important; }
        /* Modales: contenido e inputs siempre opacos (no traslúcidos) */
        body.tema-base-claro .tema-modal-opaco,
        body.tema-base-claro .fixed.inset-0.z-50 > div,
        body.tema-base-claro [class*="fixed"][class*="inset-0"][class*="z-50"] > div.rounded-2xl,
        body.tema-base-claro [class*="fixed"][class*="inset-0"][class*="z-50"] > div.rounded-xl {
            background: #fff !important;
        }
        body.tema-base-claro .tema-modal-opaco input,
        body.tema-base-claro .tema-modal-opaco select,
        body.tema-base-claro .tema-modal-opaco textarea,
        body.tema-base-claro .fixed.inset-0.z-50 > div input,
        body.tema-base-claro .fixed.inset-0.z-50 > div select,
        body.tema-base-claro .fixed.inset-0.z-50 > div textarea {
            background: #fff !important;
        }
        body.tema-base-claro [class*="text-slate-1"],
        body.tema-base-claro [class*="text-slate-2"],
        body.tema-base-claro [class*="text-slate-3"],
        body.tema-base-claro [class*="text-slate-4"] { color: #0f172a !important; }
        body.tema-base-claro [class*="border-slate-9"],
        body.tema-base-claro [class*="border-slate-8"],
        body.tema-base-claro [class*="border-slate-7"],
        body.tema-base-claro [class*="border-slate-6"] { border-color: #0f172a !important; }
        body.tema-base-claro [class*="from-slate"],
        body.tema-base-claro [class*="to-slate"],
        body.tema-base-claro [class*="via-slate"] { background-image: none !important; }
        /* Botones en blanco con bordes y texto negro */
        body.tema-base-claro button,
        body.tema-base-claro .btn,
        body.tema-base-claro .button,
        body.tema-base-claro [type="button"],
        body.tema-base-claro [type="submit"] {
            background-color: #fff;
            color: #0f172a;
            border: 1px solid #0f172a;
        }
        body.tema-base-claro button:hover,
        body.tema-base-claro .btn:hover,
        body.tema-base-claro .button:hover,
        body.tema-base-claro [type="button"]:hover,
        body.tema-base-claro [type="submit"]:hover {
            background-color: #f1f5f9;
        }
        /* Mantener botones con tema explícito */
        body.tema-base-claro .tema-gradient,
        body.tema-base-claro .tema-bg {
            color: #fff;
            border-color: transparent;
        }
        /* Botón Exportar y secundarios con tema */
        body.tema-base-claro .tema-btn-outline {
            border: 2px solid var(--tema-primary) !important;
            color: var(--tema-primary) !important;
            background: rgba(255,255,255,0.9) !important;
        }
        body.tema-base-claro .tema-btn-outline:hover {
            background: rgba(var(--tema-primary-rgb), 0.1) !important;
            color: var(--tema-primary) !important;
        }
        /* Paginación con tema */
        body.tema-base-claro .tema-pagination a {
            border-color: var(--tema-primary) !important;
            color: var(--tema-primary) !important;
        }
        body.tema-base-claro .tema-pagination a:hover {
            background: rgba(var(--tema-primary-rgb), 0.1) !important;
        }
        /* Contenedor de tablas: borde y fondo con tema */
        body.tema-base-claro .tema-table-wrap {
            border-color: var(--tema-primary) !important;
        }
        /* Modales: fondos opacos (no traslúcidos), bordes y sombra */
        body.tema-base-claro .modal,
        body.tema-base-claro .modal-content,
        body.tema-base-claro .tema-modal-content,
        body.tema-base-claro .tema-modal-export {
            background: #fff !important;
            color: #0f172a !important;
            border: 2px solid var(--tema-primary) !important;
            box-shadow: 0 25px 50px rgba(var(--tema-primary-rgb), 0.2) !important;
        }
        /* Botones de modales de exportación con tema */
        body.tema-base-claro .tema-modal-export a[href*="export.excel"],
        body.tema-base-claro .tema-modal-export a[href*="export/excel"] {
            background: linear-gradient(to right, var(--tema-from), var(--tema-to)) !important;
            color: #fff !important;
            border: none !important;
        }
        body.tema-base-claro .tema-modal-export a[href*="export.pdf"],
        body.tema-base-claro .tema-modal-export a[href*="export/pdf"] {
            background: linear-gradient(to right, var(--tema-primary-hover), var(--tema-to)) !important;
            color: #fff !important;
            border: none !important;
        }
        body.tema-base-claro .tema-modal-export button[type="button"] {
            border-color: var(--tema-primary) !important;
            color: var(--tema-primary) !important;
        }
        body.tema-base-claro .tema-modal-export button[type="button"]:hover {
            background: rgba(var(--tema-primary-rgb), 0.1) !important;
        }
    </style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        @media (max-width: 1023px) {
            body.menu-open {
                overflow: hidden;
            }
            /* Sidebar completamente opaco en móvil */
            .tema-sidebar {
                background: linear-gradient(180deg, rgba(var(--tema-from-rgb), 1), rgba(var(--tema-to-rgb), 1)) !important;
                opacity: 1 !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
            /* Asegurar texto legible en móvil - tema claro */
            @media (max-width: 1023px) {
                body.tema-base-claro .tema-sidebar,
                body.tema-base-claro .tema-sidebar * {
                    color: #1f2937 !important;
                }
                body.tema-base-claro .tema-sidebar a,
                body.tema-base-claro .tema-sidebar button,
                body.tema-base-claro .tema-sidebar span {
                    color: #1f2937 !important;
                }
                body.tema-base-claro .tema-sidebar .tema-gradient,
                body.tema-base-claro .tema-sidebar .tema-gradient * {
                    color: #fff !important;
                }
                /* Tema oscuro en móvil */
                body.tema-base-oscuro .tema-sidebar,
                body.tema-base-oscuro .tema-sidebar * {
                    color: #ffffff !important;
                }
                body.tema-base-oscuro .tema-sidebar a,
                body.tema-base-oscuro .tema-sidebar button,
                body.tema-base-oscuro .tema-sidebar span {
                    color: #ffffff !important;
                }
            }
        }
        /* Ocultar scrollbar en móvil pero mantener funcionalidad */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        /* Ajustes para móvil */
        @media (max-width: 640px) {
            .tema-sidebar {
                width: 280px;
            }
        }
    </style>
</head>

<body class="tema-base-claro min-h-screen flex flex-col antialiased bg-gray-50 text-gray-900" 
      x-data="{ menuMobileOpen: false }" 
      @keydown.escape.window="menuMobileOpen = false"
      :class="{ 'menu-open': menuMobileOpen }">

    <!-- Header -->
    <header class="tema-header sticky top-0 z-50">
        <div class="flex items-center justify-between h-16 px-5 lg:px-8">
            <div class="flex items-center gap-3 lg:gap-5">
                <!-- Botón menú móvil -->
                <button 
                    type="button"
                    @click.stop="menuMobileOpen = !menuMobileOpen"
                    class="lg:hidden p-2 rounded-lg text-gray-800 hover:bg-gray-200 transition z-50 relative"
                    aria-label="Abrir menú"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center focus:outline-none">
                    @php
                        $logoUrl = !empty($logoMain) 
                            ? (str_starts_with($logoMain, 'storage/') ? asset('storage/' . str_replace('storage/', '', $logoMain)) : asset($logoMain))
                            : asset('img/logos/logoSams.png');
                    @endphp
                    <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="h-10 lg:h-14 w-auto object-contain">
                </a>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button 
                    type="button" 
                    @click="open = !open" 
                    @click.outside="open = false"
                    class="flex items-center gap-3 px-5 py-2.5 rounded-xl bg-gray-100 border border-gray-800 text-gray-800 hover:bg-gray-200 transition-all duration-200 shadow-sm"
                >
                    @if(session('sams2_user.imagen'))
                        @php
                            $imagenPath = session('sams2_user.imagen');
                            // Si empieza con 'storage/', usar asset('storage/...'), sino usar asset directo
                            $imagenUrl = str_starts_with($imagenPath, 'storage/') ? asset('storage/' . str_replace('storage/', '', $imagenPath)) : asset($imagenPath);
                        @endphp
                        <img src="{{ $imagenUrl }}?v={{ time() }}" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-600 shrink-0" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <svg class="w-6 h-6 text-slate-400 shrink-0 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                    <span class="text-sm font-semibold hidden sm:inline text-gray-800">
                        {{ session('sams2_user.name') ?? 'Usuario' }}
                    </span>
                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div 
                    x-show="open" 
                    x-cloak
                    x-transition
                    class="tema-dropdown absolute right-0 mt-3 w-64 origin-top-right rounded-2xl shadow-xl divide-y divide-gray-200"
                >
                    <div class="py-2">
                        <a href="{{ route('ayuda') }}" 
                           class="block px-5 py-3 text-sm text-gray-700 hover:bg-gray-100 tema-text transition-colors">
                            Ayuda
                        </a>
                        <a href="{{ route('perfil') }}" 
                           class="block px-5 py-3 text-sm text-gray-700 hover:bg-gray-100 tema-text transition-colors">
                            Perfil
                        </a>
                    </div>
                    <div class="py-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full text-left px-5 py-3 text-sm text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 relative">
        
        <!-- Overlay móvil -->
        <div 
            x-show="menuMobileOpen"
            x-cloak
            @click="menuMobileOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside 
            class="tema-sidebar w-72 flex-shrink-0 fixed lg:sticky inset-y-0 left-0 z-50 lg:z-auto transform transition-transform duration-300 ease-in-out lg:transform-none"
            :class="menuMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            @click.stop
            style="top: 64px; height: calc(100vh - 64px); overflow-y: auto; overflow-x: hidden;"
            x-show="true"
        >
            <nav class="p-5 space-y-1.5"             x-data="{ 
                usuarios: {{ request()->routeIs('usuarios.*', 'roles.*', 'cargos.*', 'grupos.*') ? 'true' : 'false' }}, 
                usos: {{ request()->routeIs('equipos.*', 'almacen.*', 'tipo-equipos.*', 'tipo-items.*', 'uso-items.*', 'estado-remision.*', 'asignar.*') ? 'true' : 'false' }}, 
                config: {{ request()->routeIs('empresas.*', 'proveedores.*', 'fabricantes.*', 'configuracion.*') ? 'true' : 'false' }} 
            }">
                
                <!-- Botón cerrar móvil -->
                <div class="flex items-center justify-between mb-4 lg:hidden">
                    <span class="text-lg font-bold tema-sidebar-text">Menú</span>
                    <button 
                        type="button"
                        @click.stop="menuMobileOpen = false"
                        class="p-2 rounded-lg tema-sidebar-text hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        aria-label="Cerrar menú"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <a href="{{ route('dashboard') }}" 
                   @click.stop="menuMobileOpen = false"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'tema-gradient text-white shadow-lg' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Inicio
                </a>

                @if(PermisoHelper::puedeAlgunoDe(['usuarios', 'roles', 'cargos', 'grupos']))
                <div>
                    <button 
                        type="button" 
                        @click="usuarios = !usuarios" 
                        class="flex items-center justify-between w-full px-5 py-3.5 rounded-xl text-base font-medium tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                    >
                        <span class="flex items-center gap-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Usuarios
                        </span>
                        <svg class="w-5 h-5 transition-transform duration-300" :class="usuarios ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="usuarios" x-cloak class="mt-1 space-y-1 pl-6">
                        @if(PermisoHelper::puede('usuarios'))
                        <a href="{{ route('usuarios.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('usuarios.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Usuarios</a>
                        @endif
                        @if(PermisoHelper::puede('roles'))
                        <a href="{{ route('roles.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('roles.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Roles</a>
                        @endif
                        @if(PermisoHelper::puede('cargos'))
                        <a href="{{ route('cargos.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('cargos.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Cargos</a>
                        @endif
                        @if(PermisoHelper::puede('grupos'))
                        <a href="{{ route('grupos.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('grupos.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Grupos</a>
                        @endif
                    </div>
                </div>
                @endif

                @if(PermisoHelper::puedeAlgunoDe(['equipos', 'tipo_equipos', 'tipo_items', 'uso_items', 'estado_items', 'estado_remision', 'asignar']))
                <div>
                    <button 
                        type="button" 
                        @click="usos = !usos" 
                        class="flex items-center justify-between w-full px-5 py-3.5 rounded-xl text-base font-medium tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                    >
                        <span class="flex items-center gap-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Usos
                        </span>
                        <svg class="w-5 h-5 transition-transform duration-300" :class="usos ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="usos" x-cloak class="mt-1 space-y-1 pl-6">
                        @if(PermisoHelper::puede('equipos'))
                        <a href="{{ route('equipos.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('equipos.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Equipos</a>
                        @endif
                        @if(PermisoHelper::puede('equipos'))
                        <a href="{{ route('almacen.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('almacen.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Almacén</a>
                        @endif
                        @if(PermisoHelper::puede('equipos'))
                        <a href="{{ route('equipos-baja.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('equipos-baja.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Equipos de baja</a>
                        @endif
                        @if(PermisoHelper::puede('tipo_equipos'))
                        <a href="{{ route('tipo-equipos.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('tipo-equipos.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Tipo Equipos</a>
                        @endif
                        @if(PermisoHelper::puede('tipo_items'))
                        <a href="{{ route('tipo-items.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('tipo-items.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Tipo Items</a>
                        @endif
                        @if(PermisoHelper::puede('uso_items'))
                        <a href="{{ route('uso-items.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('uso-items.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Uso Items</a>
                        @endif
                        @if(PermisoHelper::puede('estado_items'))
                        @endif
                        @if(PermisoHelper::puede('estado_remision'))
                        <a href="{{ route('estado-remision.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('estado-remision.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Estado Remisión</a>
                        @endif
                        @if(PermisoHelper::puede('asignar'))
                        <a href="{{ route('asignar.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('asignar.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Asignar</a>
                        @endif
                    </div>
                </div>
                @endif

                @if(PermisoHelper::puede('inspeccionar'))
                <a href="{{ route('inspeccionar.index') }}" 
                   @click.stop="menuMobileOpen = false"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('inspeccionar.*') ? 'tema-gradient text-white shadow-lg' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Inspeccionar
                </a>
                @endif

                @if(PermisoHelper::puedeAlgunoDe(['empresas', 'proveedores', 'fabricantes', 'redes_sociales', 'configuracion_login']))
                <div>
                    <button 
                        type="button" 
                        @click="config = !config" 
                        class="flex items-center justify-between w-full px-5 py-3.5 rounded-xl text-base font-medium tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                    >
                        <span class="flex items-center gap-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            configuraciones
                        </span>
                        <svg class="w-5 h-5 transition-transform duration-300" :class="config ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="config" x-cloak class="mt-1 space-y-1 pl-6">
                        @if(PermisoHelper::puede('empresas'))
                        <a href="{{ route('empresas.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('empresas.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Empresas</a>
                        @endif
                        @if(PermisoHelper::puede('proveedores'))
                        <a href="{{ route('proveedores.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('proveedores.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Proveedores</a>
                        @endif
                        @if(PermisoHelper::puede('fabricantes'))
                        <a href="{{ route('fabricantes.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('fabricantes.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Fabricantes</a>
                        @endif
                        @if(PermisoHelper::puedeAlgunoDe(['redes_sociales', 'configuracion_login']))
                        <a href="{{ route('configuracion.index') }}" @click.stop="menuMobileOpen = false" class="block px-5 py-3 rounded-lg text-sm font-medium transition {{ request()->routeIs('configuracion.*') ? 'active-tema' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">Personalizaciones</a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Seguridad (solo administradores) --}}
                @php
                    $esAdmin = false;
                    if (($user['role'] ?? null) === 'mega_admin') {
                        $esAdmin = true;
                    } elseif (isset($user['role_id'])) {
                        $role = \App\Models\Role::find($user['role_id']);
                        if ($role && strtolower($role->nombre) === 'administrador') {
                            $esAdmin = true;
                        }
                    }
                @endphp
                @if($esAdmin)
                <a href="{{ route('seguridad.index') }}"
                   @click.stop="menuMobileOpen = false"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('seguridad.*') ? 'tema-gradient text-white shadow-lg' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Seguridad
                </a>
                @endif

                @if(($user['role'] ?? '') === 'mega_admin')
                <a href="{{ route('personalizacion.index') }}"
                   @click.stop="menuMobileOpen = false"
                   class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('personalizacion.*') ? 'tema-gradient text-white shadow-lg' : 'tema-sidebar-text hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Personalización de sistema
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main content -->
        <main class="tema-main flex-1 overflow-auto p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-0">
            @if (session('success'))
                <div class="mb-8 p-5 rounded-2xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 text-base shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-8 p-5 rounded-2xl bg-rose-50 border border-rose-800/40 text-rose-800 text-base shadow-sm">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="tema-footer mt-auto" style="background: linear-gradient(90deg, rgba(var(--tema-from-rgb), 0.95), rgba(var(--tema-to-rgb), 0.9)); color: #fff;">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
            <!-- Sección superior: Logo, descripción, Acerca de y Redes Sociales (máximo 2 filas) -->
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
                <!-- Columna 1: Logo y descripción -->
                @if($tieneDescripcion)
                <div>
                    <div class="mb-4">
                        @php
                            $logoUrl = !empty($logoMain) 
                                ? (str_starts_with($logoMain, 'storage/') ? asset('storage/' . str_replace('storage/', '', $logoMain)) : asset($logoMain))
                                : asset('img/logos/logoSams.png');
                        @endphp
                        <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="h-12 w-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('img/logos/logoSams.png') }}';">
                    </div>
                    <p class="text-white/90 text-sm leading-relaxed">
                        {{ $piePagina['descripcion'] }}
                    </p>
                </div>
                @endif

                <!-- Columna 2: Enlaces rápidos -->
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

                <!-- Columna 3: Redes Sociales -->
                @if($tieneRedesSociales)
                <div>
                    <h3 class="text-white font-bold text-sm mb-4 uppercase tracking-wide">Conócenos</h3>
                    <div class="flex items-center gap-4 flex-wrap max-w-md">
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
                                        {{-- Otras redes sociales o WhatsApp sin números --}}
                                        <div class="relative group" x-data="{ open: false }">
                                            <a href="{{ $tieneUrlValida ? $urlRed : ($esWhatsApp ? '#' : '#') }}" target="{{ $tieneUrlValida ? '_blank' : '_self' }}" rel="noopener noreferrer" 
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

            <!-- Sección inferior: Legal y copyright -->
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
                    <span>© {{ date('Y') }} {{ $encabezado['texto_nombre_empresa'] ?? config('app.name') }} • {{ $piePagina['texto_copyright'] }}</span>
                </div>
                @elseif(!$tieneEnlacesLegales)
                <div class="text-white/80">
                    <span>© {{ date('Y') }} {{ $encabezado['texto_nombre_empresa'] ?? config('app.name') }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>
    </footer>

    <!-- Botón flotante de Gemini AI -->
    <div x-data="geminiChat()" x-init="loadFemaleVoice(); loadHistory()" class="fixed bottom-4 right-4 md:bottom-6 md:right-6 z-50">
        
        <!-- Chat panel -->
        <div x-show="open" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 transform translate-y-4 scale-95"
             class="tema-modal-opaco tema-panel absolute bottom-16 md:bottom-20 right-0 w-[calc(100vw-2rem)] md:w-[32rem] lg:w-[36rem] xl:w-[40rem] rounded-2xl shadow-2xl flex flex-col"
             style="height: calc(100vh - 8rem); max-height: 42rem; min-height: 28rem;">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-slate-700/50 tema-bg-light rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full tema-gradient flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-100">Asistente Gemini</h3>
                        <p class="text-xs text-slate-400" x-text="mode === 'sams' ? 'Solo SAMS2 (con datos)' : 'Conocimiento completo'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select x-model="mode" class="text-xs px-2 py-1 rounded-lg border border-slate-600 bg-slate-800 text-slate-200 focus:ring-2 focus:ring-[var(--tema-primary)]/30" title="Modo de respuesta">
                        <option value="sams">Modo SAMS</option>
                        <option value="full">Modo Full</option>
                    </select>
                    <button @click="open = false" class="text-slate-400 hover:text-slate-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" x-ref="messagesContainer">
                
                <!-- Welcome message -->
                <template x-if="messages.length === 0">
                    <div class="text-center py-8">
                        <div class="w-16 h-16 rounded-full tema-gradient flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-2">¡Hola! Soy tu asistente Gemini</h4>
                        <p class="text-sm text-gray-600 px-4">Conozco todo sobre el sistema SAMS2. Pregúntame lo que necesites:</p>
                        <div class="mt-4 space-y-2 text-xs text-left max-w-xs mx-auto">
                            <button @click="input = '¿Qué módulos tiene SAMS2?'; sendMessage()" 
                                    class="w-full text-left px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-800 transition">
                                📚 ¿Qué módulos tiene SAMS2?
                            </button>
                            <button @click="input = '¿Cuántos usuarios tienen el mismo nombre?'; sendMessage()" 
                                    class="w-full text-left px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-800 transition">
                                👥 ¿Hay usuarios duplicados?
                            </button>
                            <button @click="input = '¿Cómo agrego un nuevo equipo?'; sendMessage()" 
                                    class="w-full text-left px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-800 transition">
                                💡 ¿Cómo agrego un equipo?
                            </button>
                            <button @click="input = 'Revisar usuarios con nombres duplicados, parecidos o usernames repetidos'; sendMessage()" 
                                    class="w-full text-left px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-800 transition">
                                🔍 Revisar usuarios duplicados/parecidos
                            </button>
                        </div>
                        <p class="mt-3 text-xs text-gray-500 px-2">🎤 Haz clic en el micrófono y habla para escribir con voz</p>
                    </div>
                </template>

                <!-- Messages -->
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div class="flex items-start gap-2 max-w-[90%]" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                            <div :class="msg.role === 'user' 
                                ? 'tema-gradient text-white rounded-2xl rounded-br-sm px-4 py-3' 
                                : 'bg-slate-800/70 text-slate-200 rounded-2xl rounded-bl-sm px-4 py-3'">
                                <p class="text-sm whitespace-pre-wrap" x-html="formatMessage(msg.text)"></p>
                            </div>
                            <template x-if="msg.role !== 'user'">
                                <button type="button" @click="speakText(msg.text)" 
                                        class="flex-shrink-0 p-2 rounded-lg tema-text hover:bg-gray-200 transition" 
                                        :title="speaking ? 'Detener' : 'Escuchar con voz femenina'">
                                    <svg x-show="!speaking" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                    </svg>
                                    <svg x-show="speaking" x-cloak class="w-5 h-5 animate-pulse text-rose-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6 18.5V5.5a1.5 1.5 0 013 0v13a1.5 1.5 0 01-3 0zM12 18.5V5.5a1.5 1.5 0 013 0v13a1.5 1.5 0 01-3 0zM18 18.5V5.5a1.5 1.5 0 013 0v13a1.5 1.5 0 01-3 0z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Loading indicator -->
                <template x-if="loading">
                    <div class="flex justify-start">
                        <div class="bg-gray-100 border border-gray-300 text-gray-800 rounded-2xl rounded-bl-sm px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-[var(--tema-primary)] rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-[var(--tema-primary)] rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-[var(--tema-primary)] rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Input area -->
            <div class="p-4 border-t border-slate-700/50 bg-slate-900/50">
                <div class="flex items-center gap-4 mb-2 flex-wrap">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" x-model="autoSpeak" class="rounded border-slate-600 bg-slate-800 accent-[var(--tema-primary)]">
                        Reproducir respuestas (voz)
                    </label>
                </div>
                <form @submit.prevent="sendMessage()" class="flex gap-2">
                    <button type="button" @click="startListening()" 
                            :class="listening ? 'bg-rose-600 text-white animate-pulse shadow-lg' : 'bg-gray-200 tema-text hover:bg-gray-300'"
                            class="flex-shrink-0 p-2.5 rounded-xl transition-all duration-200" 
                            :title="listening ? '🎤 Escuchando... (haz clic para detener y enviar)' : '🎤 Habla aquí (micrófono)'">
                        <svg x-show="!listening" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v7m0-3.5a3.5 3.5 0 01-7 0V11M5 11a7 7 0 0114 0"/>
                        </svg>
                        <svg x-show="listening" x-cloak class="w-5 h-5 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                        </svg>
                    </button>
                    <input 
                        type="text" 
                        x-model="input" 
                        placeholder="Escribe o habla tu pregunta..."
                        :disabled="loading"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-400 bg-white text-gray-900 placeholder-gray-500 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition-all text-sm disabled:opacity-50"
                    >
                    <button 
                        type="submit" 
                        :disabled="!input.trim() || loading"
                        class="px-4 py-2.5 rounded-xl tema-gradient text-white font-semibold tema-gradient-hover transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
                <button @click="clearChat()" class="mt-2 text-xs text-slate-500 hover:text-slate-300 transition">
                    🗑️ Limpiar conversación
                </button>
            </div>
        </div>

        <!-- Floating button -->
        <button 
            @click="toggleChat()" 
            class="w-14 h-14 md:w-16 md:h-16 rounded-full tema-gradient text-white shadow-2xl hover:opacity-90 hover:scale-110 transition-all duration-300 flex items-center justify-center group relative">
            <svg x-show="!open" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            
            <!-- Badge notification (opcional) -->
            <span x-show="messages.length === 0" class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 rounded-full border-2 border-white"></span>
        </button>
    </div>

    <script>
    function geminiChat() {
        return {
            open: false,
            input: '',
            loading: false,
            messages: [],
            mode: 'sams',
            autoSpeak: false,
            speaking: false,
            listening: false,
            femaleVoice: null,
            recognition: null,
            savingHistory: false,
            finalTranscript: '',
            
            loadFemaleVoice() {
                const load = () => {
                    const voices = speechSynthesis.getVoices();
                    const esVoices = voices.filter(v => v.lang.startsWith('es'));
                    const femaleNames = ['female','mujer','helena','sabina','monica','paulina','lucia','zira','laura','sofia'];
                    this.femaleVoice = esVoices.find(v => femaleNames.some(n => v.name.toLowerCase().includes(n)))
                        || esVoices.find(v => v.name.toLowerCase().includes('google') && !v.name.toLowerCase().includes('male'))
                        || esVoices[0];
                };
                if (speechSynthesis.getVoices().length) load();
                else speechSynthesis.onvoiceschanged = load;
            },
            speakText(text) {
                if (!text) return;
                const t = text.replace(/<[^>]*>/g, '').trim();
                if (!t) return;
                if (this.speaking) {
                    speechSynthesis.cancel();
                    this.speaking = false;
                    return;
                }
                speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(t);
                utterance.lang = 'es-ES';
                utterance.rate = 0.95;
                if (this.femaleVoice) utterance.voice = this.femaleVoice;
                utterance.onend = () => { this.speaking = false; };
                utterance.onerror = () => { this.speaking = false; };
                this.speaking = true;
                speechSynthesis.speak(utterance);
            },
            toggleChat() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (!this.femaleVoice) this.loadFemaleVoice();
                        if (!this.recognition) this.initRecognition();
                    });
                }
            },
            initRecognition() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) {
                    alert('❌ Tu navegador no soporta reconocimiento de voz.\n\nUsa Chrome, Edge o Safari.');
                    return;
                }
                
                // Siempre crear una nueva instancia para evitar estados inválidos
                try {
                this.recognition = new SpeechRecognition();
                    this.recognition.continuous = false;
                this.recognition.interimResults = true;
                this.recognition.lang = 'es-ES';
                    this.recognition.maxAlternatives = 1;
                    
                    this.recognition.onstart = () => {
                        this.listening = true;
                        this.finalTranscript = '';
                        if (!this.input.trim()) {
                            this.input = '';
                        }
                    };
                    
                this.recognition.onresult = (e) => {
                        let interim = '';
                        this.finalTranscript = '';
                        
                    for (let i = e.resultIndex; i < e.results.length; i++) {
                            const transcript = e.results[i][0].transcript;
                            if (e.results[i].isFinal) {
                                this.finalTranscript += transcript + ' ';
                            } else {
                                interim += transcript;
                            }
                        }
                        
                        this.input = (this.input.trim() ? this.input.trim() + ' ' : '') + this.finalTranscript + interim;
                };
                    
                this.recognition.onerror = (e) => {
                        this.listening = false;
                        
                        if (e.error === 'not-allowed') {
                            alert('⚠️ Permiso de micrófono denegado.\n\nHaz clic en el candado 🔒 y permite el micrófono.');
                        } else if (e.error === 'no-speech') {
                            // Normal, no hacer nada
                        } else if (e.error !== 'aborted') {
                            console.warn('Error de reconocimiento:', e.error);
                        }
                    };
                    
                this.recognition.onend = () => {
                    this.listening = false;
                        if (this.finalTranscript.trim() && this.input.trim()) {
                            setTimeout(() => this.sendMessage(), 300);
                        }
                    };
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al inicializar: ' + error.message);
                }
            },
            async startListening() {
                // Si ya está escuchando, detener
                if (this.listening) {
                    try {
                        if (this.recognition) {
                    this.recognition.stop();
                        }
                    } catch (e) {}
                    this.listening = false;
                    return;
                }
                
                // Verificar soporte
                if (!window.SpeechRecognition && !window.webkitSpeechRecognition) {
                    alert('Tu navegador no soporta reconocimiento de voz. Usa Chrome, Edge o Safari.');
                    return;
                }
                
                // Limpiar input
                if (!this.input.trim()) {
                    this.input = '';
                }
                
                try {
                    // Limpiar reconocimiento anterior
                    if (this.recognition) {
                        try {
                        this.recognition.stop();
                        } catch (e) {}
                        this.recognition = null;
                    }
                    
                    // Solicitar permiso primero
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        stream.getTracks().forEach(track => track.stop());
                    } catch (permErr) {
                        if (permErr.name === 'NotAllowedError') {
                            alert('⚠️ Permiso de micrófono denegado.\n\nHaz clic en el candado 🔒 y permite el micrófono.');
                        } else if (permErr.name === 'NotFoundError') {
                            alert('❌ No se encontró ningún micrófono.');
                        }
                        return;
                    }
                    
                    // Inicializar reconocimiento
                    this.initRecognition();
                    
                    if (!this.recognition) {
                        return;
                    }
                    
                    // Pequeño delay
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    // Iniciar
                    this.recognition.start();
                    
                } catch (err) {
                    console.error('Error:', err);
                        this.listening = false;
                    this.recognition = null;
                    
                    if (err.name === 'InvalidStateError') {
                        // Reintentar después de un momento
                        setTimeout(() => {
                            this.startListening();
                        }, 500);
                    }
                }
            },
            
            async sendMessage() {
                // Detener reconocimiento de voz si está activo
                if (this.listening && this.recognition) {
                    try {
                        this.recognition.stop();
                        this.listening = false;
                    } catch (e) {
                        console.warn('Error al detener reconocimiento:', e);
                    }
                }
                
                const message = this.input.trim();
                if (!message || this.loading) return;
                
                // Agregar mensaje del usuario
                this.messages.push({
                    role: 'user',
                    text: message
                });
                
                this.input = '';
                this.loading = true;
                this.scrollToBottom();
                
                try {
                    const baseHistory = this.messages.slice(0, -1).map(m => ({
                        role: m.role,
                        text: m.text
                    }));

                    const sendChatRequest = (modeToUse) => fetch('{{ route('gemini.chat') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            message: message,
                            history: baseHistory,
                            mode: modeToUse
                        })
                    });

                    const parseChatResponse = async (response) => {
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            return await response.json();
                        }
                        const rawBody = await response.text();
                        return {
                            success: false,
                            error: rawBody
                                ? `Respuesta no válida del servidor (HTTP ${response.status}).`
                                : null
                        };
                    };

                    const getHttpErrorMessage = (response, data) => {
                        let errorMsg = data?.error || data?.message || null;
                        if (errorMsg) return errorMsg;

                        if (response.status === 419) {
                            return 'La sesión expiró. Recarga la página e inicia sesión de nuevo.';
                        }
                        if (response.status === 503) {
                            return 'El servicio de Gemini no está disponible. Intenta nuevamente en unos minutos.';
                        }
                        if (response.status === 500) {
                            return 'Error interno del servidor. Por favor, intenta más tarde.';
                        }
                        return `Error del servidor (HTTP ${response.status}).`;
                    };

                    let activeMode = this.mode;
                    let response = await sendChatRequest(activeMode);
                    let data = await parseChatResponse(response);

                    if (!response.ok) {
                        let errorMsg = getHttpErrorMessage(response, data);
                        const normalizedError = (errorMsg || '').toLowerCase();
                        const invalidApiKeyInFullMode = activeMode === 'full' && (
                            normalizedError.includes('api key') ||
                            normalizedError.includes('gemini_api_key') ||
                            normalizedError.includes('not valid') ||
                            normalizedError.includes('invalid') ||
                            normalizedError.includes('no es válida') ||
                            normalizedError.includes('no es valida')
                        );

                        if (invalidApiKeyInFullMode) {
                            this.mode = 'sams';
                            this.messages.push({
                                role: 'assistant',
                                text: '⚠️ Modo Full no disponible por API Key inválida. Cambié automáticamente a Modo SAMS para mantener el asistente funcionando.'
                            });

                            activeMode = 'sams';
                            response = await sendChatRequest(activeMode);
                            data = await parseChatResponse(response);

                            if (!response.ok) {
                                errorMsg = getHttpErrorMessage(response, data);
                                this.messages.push({
                                    role: 'assistant',
                                    text: '❌ ' + errorMsg
                                });
                                console.error('Error HTTP de Gemini (retry SAMS):', { status: response.status, data });
                                return;
                            }
                        } else {
                            this.messages.push({
                                role: 'assistant',
                                text: '❌ ' + errorMsg
                            });
                            console.error('Error HTTP de Gemini:', { status: response.status, data });
                            return;
                        }
                    }

                    if (data?.success) {
                        this.messages.push({
                            role: 'assistant',
                            text: data.message
                        });
                        if (this.autoSpeak) {
                            this.$nextTick(() => this.speakText(data.message));
                        }
                        // Guardar historial automáticamente
                        this.saveHistory();
                    } else {
                        const errorMsg = data?.error || 'Error desconocido. Por favor, verifica la configuración de Gemini.';
                        this.messages.push({
                            role: 'assistant',
                            text: '❌ ' + errorMsg
                        });
                        console.error('Error de Gemini:', data);
                    }
                } catch (err) {
                    const errorMsg = 'Error de conexión. Por favor, verifica tu conexión a internet e intenta nuevamente.';
                    
                    this.messages.push({
                        role: 'assistant',
                        text: '❌ ' + errorMsg
                    });
                    console.error('Error:', err);
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                }
            },
            
            async loadHistory() {
                try {
                    const response = await fetch('{{ route('gemini.get-history') }}', {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.history && data.history.messages) {
                            this.messages = data.history.messages;
                            this.mode = data.history.mode || 'sams';
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    }
                } catch (err) {
                    console.warn('Error cargando historial:', err);
                }
            },
            
            async saveHistory() {
                if (this.savingHistory || this.messages.length === 0) return;
                this.savingHistory = true;
                try {
                    await fetch('{{ route('gemini.save-history') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            messages: this.messages,
                            mode: this.mode
                        })
                    });
                } catch (err) {
                    console.warn('Error guardando historial:', err);
                } finally {
                    this.savingHistory = false;
                }
            },
            
            clearChat() {
                if (confirm('¿Deseas limpiar toda la conversación?')) {
                    this.messages = [];
                    this.saveHistory();
                }
            },
            
            scrollToBottom() {
                this.$nextTick(() => {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },
            
            formatMessage(text) {
                // Convertir URLs a links
                text = text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="underline tema-text hover:opacity-90">$1</a>');
                
                // Convertir **texto** a negrita
                text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                
                // Convertir *texto* a cursiva
                text = text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
                
                // Convertir líneas que empiezan con - a lista
                text = text.replace(/^- (.+)$/gm, '• $1');
                
                return text;
            }
        };
    }
    </script>

</body>
</html>
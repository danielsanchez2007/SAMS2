@php
    $temaConfig = \App\Helpers\TemaHelper::temaActual();
    $temaFrom = $temaConfig['from'] ?? '#4f46e5';
    $temaTo = $temaConfig['to'] ?? '#7c3aed';
    $temaGradientFull = $temaConfig['gradient_full'] ?? 'linear-gradient(to right, ' . $temaFrom . ', ' . $temaTo . ')';
    $logoMain = \Illuminate\Support\Facades\Cache::get(
        config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal')
    );
    
    $configLogin = \Illuminate\Support\Facades\Cache::get('sistema_config_login', [
        'texto_boton_login' => 'Iniciar Sesión2',
    ]);
@endphp
<header class="sticky top-0 z-50 shadow-lg" style="background: {{ $temaGradientFull }};">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                @php
                    $logoUrl = !empty($logoMain) 
                        ? (str_starts_with($logoMain, 'storage/') 
                            ? asset('storage/' . str_replace('storage/', '', $logoMain)) 
                            : asset($logoMain))
                        : asset('img/logos/logoSams.png');
                @endphp
                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="h-16 lg:h-20 w-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('img/logos/logoSams.png') }}';">
                @php
                    $encabezado = \Illuminate\Support\Facades\Cache::get('sistema_encabezado', [
                        'texto_nombre_empresa' => config('app.name'),
                    ]);
                @endphp
                <span class="text-white text-xl font-bold">{{ $encabezado['texto_nombre_empresa'] ?? config('app.name') }}</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-semibold transition-all duration-200">
                    {{ $configLogin['texto_boton_login'] ?? 'Iniciar Sesión' }}
                </a>                    
            </div>
        </div>
    </div>
</header>

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
    ]);
    $ubicacion = config('sams2_ubicacion', []);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro — {{ config('app.name') }}</title>
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
        .register-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .register-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .register-scroll::-webkit-scrollbar-thumb {
            background: var(--tema-primary);
            border-radius: 10px;
        }
        .register-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--tema-primary-hover);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4" x-data="registerForm()">
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="tema-gradient p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @php
                        $logoUrl = !empty($logoMain) 
                            ? (str_starts_with($logoMain, 'storage/') ? asset('storage/' . str_replace('storage/', '', $logoMain)) : asset($logoMain))
                            : asset('img/logos/logoSams.png');
                    @endphp
                    <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="h-10 w-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('img/logos/logoSams.png') }}';">
                    <h1 class="text-2xl font-bold">Registro de Usuario</h1>
                </div>
                <a href="{{ route('login') }}" class="text-white/90 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto register-scroll p-6">
            @if($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tipo documento --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo documento <span class="text-red-500">*</span></label>
                        <select name="tipo_documento" x-model="form.tipo_documento" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="Cédula de ciudadanía">Cédula de ciudadanía</option>
                            <option value="Cédula de extranjería">Cédula de extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    {{-- Cédula --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula / Documento <span class="text-red-500">*</span></label>
                        <input type="text" name="cedula" x-model="form.cedula" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" x-model="form.nombre" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Apellidos --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                        <input type="text" name="apellidos" x-model="form.apellidos" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Fecha nacimiento --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha nacimiento <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_nacimiento" x-model="form.fecha_nacimiento" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Tratamiento --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tratamiento</label>
                        <select x-model="form.tratamiento_select" @change="form.tratamiento = $event.target.value !== 'Otro' ? $event.target.value : ''"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            <option value="Sr.">Sr.</option>
                            <option value="Sra.">Sra.</option>
                            <option value="Señora.">Señora.</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Dra.">Dra.</option>
                            <option value="Ing.">Ing.</option>
                            <option value="Lic.">Lic.</option>
                            <option value="Arq.">Arq.</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    {{-- Tratamiento personalizado --}}
                    <div x-show="form.tratamiento_select === 'Otro'" x-cloak class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Especificar tratamiento</label>
                        <input type="text" name="tratamiento" x-model="form.tratamiento" placeholder="Ej: Prof., Mtro., etc."
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    <input type="hidden" name="tratamiento" x-show="form.tratamiento_select !== 'Otro'" :value="form.tratamiento_select">
                    
                    {{-- Dirección --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="direccion" x-model="form.direccion"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" x-model="form.telefono"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Correo electrónico --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
                        <input type="email" name="correo_electronico" x-model="form.correo_electronico" required @input="autoUsername()"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    
                    {{-- Checkbox: Tiene correo corporativo --}}
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="tiene_correo_corporativo" id="tiene_correo_corporativo" value="1" 
                                   x-model="form.tiene_correo_corporativo"
                                   class="rounded border-gray-300 text-[var(--tema-primary)]">
                            <label for="tiene_correo_corporativo" class="text-sm text-gray-700">Tiene correo corporativo</label>
                        </div>
                    </div>
                    {{-- Campo correo corporativo --}}
                    <div x-show="form.tiene_correo_corporativo" x-cloak class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo corporativo</label>
                        <input type="email" name="correo_corporativo" x-model="form.correo_corporativo"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition"
                               placeholder="correo@empresa.com">
                    </div>
                    
                    {{-- Checkbox: Tiene teléfono corporativo --}}
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="tiene_telefono_corporativo" id="tiene_telefono_corporativo" value="1" 
                                   x-model="form.tiene_telefono_corporativo"
                                   class="rounded border-gray-300 text-[var(--tema-primary)]">
                            <label for="tiene_telefono_corporativo" class="text-sm text-gray-700">Tiene teléfono corporativo</label>
                        </div>
                    </div>
                    {{-- Campo teléfono corporativo --}}
                    <div x-show="form.tiene_telefono_corporativo" x-cloak class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono corporativo</label>
                        <input type="text" name="telefono_corporativo" x-model="form.telefono_corporativo"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition"
                               placeholder="Ej: +57 321 1234567">
                    </div>
                    
                    {{-- Departamento --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                        <select name="departamento" x-model="form.departamento"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            @foreach($ubicacion['departamentos']['Colombia'] ?? $ubicacion['departamentos']['default'] ?? [] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    {{-- Departamento otro --}}
                    <div x-show="form.departamento === 'Otro'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Especificar departamento</label>
                        <input type="text" name="departamento_otro" x-model="form.departamento_otro" placeholder="Nombre del departamento"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    <div x-show="form.departamento !== 'Otro'" class="hidden md:block"></div>
                    
                    {{-- Municipio --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Municipio</label>
                        <select name="municipio" x-model="form.municipio"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            @php
                                $deptos = $ubicacion['departamentos']['Colombia'] ?? $ubicacion['departamentos']['default'] ?? [];
                            @endphp
                            @foreach($deptos as $d)
                                @foreach($ubicacion['municipios'][$d] ?? ['Otro'] as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    {{-- Municipio otro --}}
                    <div x-show="form.municipio === 'Otro'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Especificar municipio</label>
                        <input type="text" name="municipio_otro" x-model="form.municipio_otro" placeholder="Nombre del municipio"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    
                    {{-- Empresa --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                        <select name="empresa_id" x-model="form.empresa_id"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            @foreach($empresas as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Sede --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sede</label>
                        <select name="sede_id" x-model="form.sede_id"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            @foreach($sedes as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Grupo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                        <select name="grupo_id" x-model="form.grupo_id"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            @foreach($grupos as $g)
                                <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Cargo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                        <select name="cargo_id" x-model="form.cargo_id"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                            <option value="">—</option>
                            @foreach($cargos as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Username --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" x-model="form.username" required @input="autoUsername()"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Contraseña --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña <span class="text-red-500">*</span></label>
                        <input type="password" name="password" x-model="form.password" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    {{-- Confirmar contraseña --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" x-model="form.password_confirmation" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-[var(--tema-primary)] focus:ring-2 focus:ring-[var(--tema-primary)]/20 outline-none transition">
                    </div>
                    
                    {{-- Imagen de usuario --}}
                    <div class="md:col-span-2 mt-4 border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Imagen de usuario (foto) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="imagen_usuario" accept="image/*" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700">
                        <p class="text-xs text-gray-500 mt-1">La foto es obligatoria para poder usar el sistema.</p>
                    </div>
                    
                    {{-- Firma --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Firma (imagen) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="firma_imagen" accept="image/*" required
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700">
                        <p class="text-xs text-gray-500 mt-1">La firma es obligatoria para poder usar el sistema.</p>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" class="flex-1 px-6 py-3 rounded-xl tema-gradient text-white font-semibold shadow-md hover:shadow-lg transition">
                        Registrarse
                    </button>
                    <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                form: {
                    tipo_documento: 'Cédula de ciudadanía',
                    cedula: '',
                    nombre: '',
                    apellidos: '',
                    fecha_nacimiento: '',
                    direccion: '',
                    telefono: '',
                    correo_electronico: '',
                    tiene_correo_corporativo: false,
                    correo_corporativo: '',
                    tiene_telefono_corporativo: false,
                    telefono_corporativo: '',
                    departamento: '',
                    departamento_otro: '',
                    municipio: '',
                    municipio_otro: '',
                    tratamiento: '',
                    tratamiento_select: '',
                    empresa_id: '',
                    sede_id: '',
                    grupo_id: '',
                    cargo_id: '',
                    username: '',
                    password: '',
                    password_confirmation: ''
                },
                autoUsername() {
                    if (this.form.correo_electronico && !this.form.username) {
                        const email = this.form.correo_electronico.split('@')[0];
                        this.form.username = email.toLowerCase().replace(/[^a-z0-9]/g, '');
                    }
                }
            };
        }
    </script>
</body>
</html>

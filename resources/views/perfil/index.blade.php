@extends('layouts.app')

@section('title', 'Perfil')

@section('content')
@php
    $ubicacion = $ubicacion ?? config('ubicacion', []);
    $deptos = $ubicacion['departamentos']['Colombia'] ?? $ubicacion['departamentos']['default'] ?? [];
    $tratamientosPredef = ['', 'Sr.', 'Sra.', 'Señora.', 'Dr.', 'Dra.', 'Ing.', 'Lic.', 'Arq.'];
@endphp

<div class="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 py-10 px-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl space-y-8">

        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
            <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400 bg-clip-text text-transparent">Perfil</span>
        </h1>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-5 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-300 px-5 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($esMegaAdmin ?? false)
            <div class="rounded-2xl border border-slate-800/60 bg-slate-900/80 backdrop-blur-xl p-8 text-center">
                <p class="text-slate-300">Sesión <strong>Mega Admin</strong>. No hay perfil editable en base de datos.</p>
                <p class="text-slate-500 text-sm mt-2">Los datos de perfil (nombre, foto, etc.) solo aplican a usuarios registrados en el sistema.</p>
            </div>
        @else
        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/80 backdrop-blur-xl shadow-xl overflow-hidden" x-data="perfilForm()">
            <form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
                @csrf
                @method('PUT')

                {{-- Foto de perfil (preview + input) --}}
                <div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-slate-700/50">
                    <div class="relative">
                        <div class="w-28 h-28 rounded-full border-2 border-slate-600 bg-slate-800 overflow-hidden flex items-center justify-center shrink-0">
                            <template x-if="fotoPreview">
                                <img :src="fotoPreview" alt="Foto" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            </template>
                            <template x-if="!fotoPreview && fotoActual">
                                @if($usuario->imagen_usuario)
                                    @php
                                        $imagenPath = $usuario->imagen_usuario;
                                        $imagenUrl = str_starts_with($imagenPath, 'storage/') ? asset('storage/' . str_replace('storage/', '', $imagenPath)) : asset($imagenPath);
                                    @endphp
                                    <img src="{{ $imagenUrl }}?v={{ time() }}" alt="Foto actual" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22112%22 height=%22112%22%3E%3Ccircle cx=%2256%22 cy=%2256%22 r=%2256%22 fill=%22%23334155%22/%3E%3Cpath d=%22M56 28c-7.7 0-14 6.3-14 14s6.3 14 14 14 14-6.3 14-14-6.3-14-14-14zm0 8c3.3 0 6 2.7 6 6s-2.7 6-6 6-6-2.7-6-6 2.7-6 6-6zm-14 18c-2.2 0-4-1.8-4-4v-2c0-1.1.9-2 2-2h24c1.1 0 2 .9 2 2v2c0 2.2-1.8 4-4 4H42z%22 fill=%22%2364758b%22/%3E%3C/svg%3E';">
                                @else
                                    <svg class="w-12 h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                @endif
                            </template>
                            <template x-if="!fotoPreview && !fotoActual">
                                <svg class="w-12 h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </template>
                        </div>
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Foto de perfil</label>
                        <input type="file" name="imagen_usuario" accept="image/*" @change="fotoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                               class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-medium hover:file:bg-indigo-500">
                        <p class="text-xs text-slate-500 mt-1">Se mostrará en el icono del menú superior. Máx. 10 MB. Formatos: JPG, PNG, GIF, WEBP, BMP, SVG</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tipo documento --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Tipo documento</label>
                        <select name="tipo_documento" x-model="tipo_documento" required
                                class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                            <option value="Cédula de ciudadanía">Cédula de ciudadanía</option>
                            <option value="Cédula de extranjería">Cédula de extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    {{-- Cédula --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Cédula / Documento</label>
                        <input type="text" name="cedula" x-model="cedula" required
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100" value="{{ old('cedula', $usuario->cedula ?? '') }}">
                    </div>
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Nombre</label>
                        <input type="text" name="nombre" x-model="nombre" required
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100" value="{{ old('nombre', $usuario->nombre ?? '') }}">
                    </div>
                    {{-- Apellidos --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Apellidos</label>
                        <input type="text" name="apellidos" x-model="apellidos" required
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100" value="{{ old('apellidos', $usuario->apellidos ?? '') }}">
                    </div>
                    {{-- Fecha nacimiento --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Fecha nacimiento</label>
                        <input type="date" name="fecha_nacimiento" x-model="fecha_nacimiento" required
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100" value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d') ?? '') }}">
                    </div>
                    {{-- Tratamiento --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Tratamiento</label>
                        <select x-model="tratamiento_select" @change="tratamiento_select !== 'Otro' && (tratamiento = tratamiento_select)"
                                class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
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
                    <div x-show="tratamiento_select === 'Otro'" x-cloak class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Especificar tratamiento</label>
                        <input type="text" x-model="tratamiento" placeholder="Ej: Prof., Mtro."
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                    <input type="hidden" name="tratamiento" :value="tratamiento_select === 'Otro' ? tratamiento : tratamiento_select">

                    {{-- Dirección --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion', $usuario->direccion ?? '') }}"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono ?? '') }}"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                    {{-- Correo electrónico --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Correo electrónico</label>
                        <input type="email" name="correo_electronico" value="{{ old('correo_electronico', $usuario->correo_electronico ?? '') }}"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="tiene_correo_corporativo" id="tiene_correo_corporativo" value="1" x-model="tiene_correo"
                                   class="rounded border-slate-600 bg-slate-800 text-blue-600">
                            <label for="tiene_correo_corporativo" class="text-sm text-slate-400">Tiene correo corporativo</label>
                        </div>
                    </div>
                    <div class="md:col-span-2" x-show="tiene_correo" x-cloak>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Correo corporativo</label>
                        <input type="email" name="correo_corporativo" value="{{ old('correo_corporativo', $usuario->correo_corporativo ?? '') }}" placeholder="correo@empresa.com"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="tiene_telefono_corporativo" id="tiene_telefono_corporativo" value="1" x-model="tiene_telefono"
                                   class="rounded border-slate-600 bg-slate-800 text-blue-600">
                            <label for="tiene_telefono_corporativo" class="text-sm text-slate-400">Tiene teléfono corporativo</label>
                        </div>
                    </div>
                    <div class="md:col-span-2" x-show="tiene_telefono" x-cloak>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Teléfono corporativo</label>
                        <input type="text" name="telefono_corporativo" value="{{ old('telefono_corporativo', $usuario->telefono_corporativo ?? '') }}" placeholder="Ej: +57 321 1234567"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>

                    {{-- Departamento --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Departamento</label>
                        <select name="departamento" class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                            <option value="">—</option>
                            @foreach($deptos as $d)
                                <option value="{{ $d }}" {{ old('departamento', $usuario->departamento ?? '') == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                            <option value="Otro" {{ old('departamento', $usuario->departamento ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Municipio</label>
                        <select name="municipio" class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                            <option value="">—</option>
                            @foreach($deptos as $d)
                                @foreach($ubicacion['municipios'][$d] ?? ['Otro'] as $m)
                                    <option value="{{ $m }}" {{ old('municipio', $usuario->municipio ?? '') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            @endforeach
                            <option value="Otro" {{ old('municipio', $usuario->municipio ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Especificar departamento (si eligió Otro)</label>
                        <input type="text" name="departamento_otro" value="{{ old('departamento_otro', $usuario->departamento_otro ?? '') }}" placeholder="Nombre del departamento"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Especificar municipio (si eligió Otro)</label>
                        <input type="text" name="municipio_otro" value="{{ old('municipio_otro', $usuario->municipio_otro ?? '') }}" placeholder="Nombre del municipio"
                               class="w-full px-4 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-100">
                    </div>

                    {{-- Rol (solo lectura) --}}
                    <div class="md:col-span-2 pt-4 border-t border-slate-700/50">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Rol</label>
                        <div class="px-4 py-2 rounded-lg border border-slate-600 bg-slate-800/50 text-slate-300">
                            @if($usuario->role)
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                                    {{ $usuario->role->nombre }}
                                </span>
                            @else
                                <span class="text-slate-500">Sin rol asignado</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">El rol solo puede ser modificado por un administrador.</p>
                    </div>

                    {{-- Firma (imagen) --}}
                    <div class="md:col-span-2 pt-4 border-t border-slate-700/50">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Firma (imagen)</label>
                        <div class="flex flex-wrap items-start gap-4">
                            @if($usuario->firma_imagen ?? null)
                                @php
                                    $firmaPath = $usuario->firma_imagen;
                                    $firmaUrl = str_starts_with($firmaPath, 'storage/') ? asset('storage/' . str_replace('storage/', '', $firmaPath)) : asset($firmaPath);
                                @endphp
                                <div class="w-40 h-20 rounded-lg border border-slate-600 bg-slate-800 overflow-hidden flex items-center justify-center">
                                    <img src="{{ $firmaUrl }}?v={{ time() }}" alt="Firma actual" class="max-w-full max-h-full object-contain" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22160%22 height=%2280%22%3E%3Crect width=%22160%22 height=%2280%22 fill=%22%23334155%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%2364758b%22 font-size=%2214%22%3EFirma%3C/text%3E%3C/svg%3E';">
                                </div>
                            @endif
                            <div>
                                <input type="file" name="firma_imagen" accept="image/*" @change="firmaPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                       class="text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-600 file:text-white hover:file:bg-slate-500">
                                <p class="text-xs text-slate-500 mt-1">Opcional. Máx. 10 MB. Formatos: JPG, PNG, GIF, WEBP, BMP, SVG</p>
                            </div>
                        </div>
                        <div x-show="firmaPreview" x-cloak class="mt-2">
                            <div class="w-40 h-20 rounded-lg border border-slate-600 bg-slate-800 overflow-hidden flex items-center justify-center">
                                <img :src="firmaPreview" alt="Vista previa firma" class="max-w-full max-h-full object-contain">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-6 py-3 rounded-xl tema-gradient tema-gradient-hover text-white font-semibold shadow-lg hover:shadow-xl transition">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
        <script>
        function perfilForm() {
            const tratamientosPredef = ['', 'Sr.', 'Sra.', 'Señora.', 'Dr.', 'Dra.', 'Ing.', 'Lic.', 'Arq.'];
            const tratamientoVal = @json(old('tratamiento', $usuario->tratamiento ?? ''));
            const esPredef = tratamientosPredef.includes(tratamientoVal);
            return {
                tipo_documento: @json(old('tipo_documento', $usuario->tipo_documento ?? 'Cédula de ciudadanía')),
                cedula: @json(old('cedula', $usuario->cedula ?? '')),
                nombre: @json(old('nombre', $usuario->nombre ?? '')),
                apellidos: @json(old('apellidos', $usuario->apellidos ?? '')),
                fecha_nacimiento: @json(old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d') ?? '')),
                tratamiento: tratamientoVal,
                tratamiento_select: esPredef ? tratamientoVal : (tratamientoVal ? 'Otro' : ''),
                fotoPreview: null,
                firmaPreview: null,
                fotoActual: {{ ($usuario->imagen_usuario ?? null) ? 'true' : 'false' }},
                tiene_correo: {{ old('tiene_correo_corporativo', $usuario->tiene_correo_corporativo ?? false) ? 'true' : 'false' }},
                tiene_telefono: {{ old('tiene_telefono_corporativo', $usuario->tiene_telefono_corporativo ?? false) ? 'true' : 'false' }}
            };
        }
        </script>
        @endif
    </div>
</div>
@endsection

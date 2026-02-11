@extends('layouts.app')

@section('title', 'Personalización de sistema')

@section('content')
@php
    $tabInicial = 'por2';
    if (str_starts_with($temaActual, 'p3_') || $temaActual === 'custom_p3') $tabInicial = 'por3';
    elseif (str_starts_with($temaActual, 'p4_') || $temaActual === 'custom_p4') $tabInicial = 'por4';
    elseif (str_starts_with($temaActual, 'p5_') || $temaActual === 'custom_p5') $tabInicial = 'por5';
    elseif (str_starts_with($temaActual, 'p6_') || $temaActual === 'custom_p6') $tabInicial = 'por6';
@endphp
<div class="space-y-8" x-data="{ tab: '{{ $tabInicial }}' }">
    <h1 class="text-3xl font-extrabold tracking-tight">
        <span class="bg-gradient-to-r from-[var(--tema-from)] to-[var(--tema-to)] bg-clip-text text-transparent">Personalización de sistema</span>
    </h1>
    <p class="text-gray-600">Elige un tema de color y actualiza los logos. El cambio se aplica a todo el sistema: menú, tablas, botones y más.</p>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-800/40 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border-2 border-gray-900 p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Temas y logos</h2>

        {{-- Pestañas --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-4">
            <button type="button" @click="tab = 'por2'" :class="tab === 'por2' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Por 2 colores</button>
            <button type="button" @click="tab = 'por3'" :class="tab === 'por3' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Por 3 colores</button>
            <button type="button" @click="tab = 'por4'" :class="tab === 'por4' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Por 4 colores</button>
            <button type="button" @click="tab = 'por5'" :class="tab === 'por5' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Por 5 colores</button>
            <button type="button" @click="tab = 'por6'" :class="tab === 'por6' ? 'tema-gradient text-white border-transparent' : 'bg-gray-100 text-gray-700 border-gray-300'"
                class="px-4 py-2 rounded-xl font-medium text-sm border transition">Por 6 colores</button>
        </div>

        <form action="{{ route('personalizacion.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
            @csrf

            {{-- Por 2 colores --}}
            <div x-show="tab === 'por2'" x-cloak class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                    @foreach(array_filter($temas, fn($t) => ($t['tipo'] ?? '') === 'por_2' && empty($t['editable'])) as $key => $tema)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="tema" value="{{ $key }}" {{ $temaActual === $key ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="rounded-2xl border-2 border-gray-900 bg-white p-4 transition-all duration-200
                                peer-checked:border-[var(--tema-primary)] peer-checked:ring-2 peer-checked:ring-[var(--tema-primary)] peer-checked:ring-offset-2 peer-checked:ring-offset-white
                                group-hover:bg-gray-50">
                                <div class="w-full h-14 rounded-xl mb-3 flex overflow-hidden">
                                    <span class="flex-1" style="background: {{ $tema['from'] ?? '#4f46e5' }}"></span>
                                    <span class="flex-1" style="background: {{ $tema['to'] ?? '#7c3aed' }}"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 block text-center">{{ $tema['nombre'] ?? $key }}</span>
                            </div>
                        </label>
                    @endforeach
                    {{-- Editable --}}
                    @php $custom = $temas['custom_editable'] ?? null; @endphp
                    @if($custom)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="tema" value="custom_editable" {{ $temaActual === 'custom_editable' ? 'checked' : '' }}
                                class="sr-only peer" id="tema_custom">
                            <div class="rounded-2xl border-2 border-dashed border-gray-500 bg-white p-4 transition-all duration-200
                                peer-checked:border-[var(--tema-primary)] peer-checked:ring-2 peer-checked:ring-[var(--tema-primary)] peer-checked:ring-offset-2 peer-checked:ring-offset-white
                                group-hover:bg-gray-50">
                                <div class="w-full h-14 rounded-xl mb-3 flex overflow-hidden">
                                    <span class="flex-1" style="background: {{ $customColores[0] ?? '#4f46e5' }}"></span>
                                    <span class="flex-1" style="background: {{ $customColores[1] ?? '#7c3aed' }}"></span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 block text-center">Personalizado</span>
                                <span class="text-xs text-gray-500 block text-center mt-0.5">Escoge colores abajo</span>
                            </div>
                        </label>
                    @endif
                </div>
                {{-- Selector editable (solo visible cuando custom_editable está seleccionado) --}}
                <div id="custom-colors-block" class="p-5 rounded-xl border-2 border-dashed border-gray-400 bg-gray-50 hidden">
                    <p class="text-sm font-medium text-gray-800 mb-3">Elige tus colores personalizados (por 2):</p>
                    <div class="flex flex-wrap gap-6">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Color 1</label>
                            <input type="color" id="picker_from" value="{{ $customColores[0] ?? '#4f46e5' }}"
                                class="h-12 w-24 rounded-lg border-2 border-gray-900 cursor-pointer block mb-1">
                            <input type="text" name="custom_from" value="{{ $customColores[0] ?? '#4f46e5' }}"
                                class="block w-28 text-xs font-mono px-2 py-1 rounded border border-gray-300" maxlength="7" pattern="#[0-9A-Fa-f]{6}"
                                placeholder="#4f46e5">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Color 2</label>
                            <input type="color" id="picker_to" value="{{ $customColores[1] ?? '#7c3aed' }}"
                                class="h-12 w-24 rounded-lg border-2 border-gray-900 cursor-pointer block mb-1">
                            <input type="text" name="custom_to" value="{{ $customColores[1] ?? '#7c3aed' }}"
                                class="block w-28 text-xs font-mono px-2 py-1 rounded border border-gray-300" maxlength="7" pattern="#[0-9A-Fa-f]{6}"
                                placeholder="#7c3aed">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Por 3, 4, 5, 6 colores --}}
            @foreach(['por3' => ['p3_', 'custom_p3', 3, $customColoresP3 ?? []], 'por4' => ['p4_', 'custom_p4', 4, $customColoresP4 ?? []], 'por5' => ['p5_', 'custom_p5', 5, $customColoresP5 ?? []], 'por6' => ['p6_', 'custom_p6', 6, $customColoresP6 ?? []]] as $tabId => $conf)
                @php $prefix = $conf[0]; $customKey = $conf[1]; $numColores = $conf[2]; $customCols = $conf[3]; @endphp
                <div x-show="tab === '{{ $tabId }}'" x-cloak class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                        @foreach(array_filter($temas, fn($t, $k) => str_starts_with($k, $prefix) && $k !== $customKey, ARRAY_FILTER_USE_BOTH) as $key => $tema)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="tema" value="{{ $key }}" {{ $temaActual === $key ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="rounded-2xl border-2 border-gray-900 bg-white p-4 transition-all duration-200
                                    peer-checked:border-[var(--tema-primary)] peer-checked:ring-2 peer-checked:ring-[var(--tema-primary)] peer-checked:ring-offset-2 peer-checked:ring-offset-white
                                    group-hover:bg-gray-50">
                                    <div class="w-full h-14 rounded-xl mb-3 flex overflow-hidden">
                                        @foreach(($tema['colores'] ?? [$tema['from'], $tema['to']]) as $c)
                                            <span class="flex-1" style="background: {{ $c }}"></span>
                                        @endforeach
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 block text-center">{{ $tema['nombre'] ?? $key }}</span>
                                </div>
                            </label>
                        @endforeach
                        {{-- Personalizado --}}
                        @php $cust = $temas[$customKey] ?? null; @endphp
                        @if($cust)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="tema" value="{{ $customKey }}" {{ $temaActual === $customKey ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="rounded-2xl border-2 border-dashed border-gray-500 bg-white p-4 transition-all duration-200
                                    peer-checked:border-[var(--tema-primary)] peer-checked:ring-2 peer-checked:ring-[var(--tema-primary)] peer-checked:ring-offset-2 peer-checked:ring-offset-white
                                    group-hover:bg-gray-50">
                                    <div class="w-full h-14 rounded-xl mb-3 flex overflow-hidden">
                                        @foreach(($cust['colores'] ?? array_fill(0, $numColores, '#4f46e5')) as $c)
                                            <span class="flex-1" style="background: {{ $c }}"></span>
                                        @endforeach
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 block text-center">Personalizado</span>
                                    <span class="text-xs text-gray-500 block text-center mt-0.5">Escoge colores abajo</span>
                                </div>
                            </label>
                        @endif
                    </div>
                    {{-- Selector editable para por {{ $numColores }} --}}
                    <div id="custom-block-{{ $tabId }}" class="p-5 rounded-xl border-2 border-dashed border-gray-400 bg-gray-50 hidden">
                        <p class="text-sm font-medium text-gray-800 mb-3">Elige tus {{ $numColores }} colores personalizados:</p>
                        <div class="flex flex-wrap gap-6">
                            @for($i = 0; $i < $numColores; $i++)
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Color {{ $i + 1 }}</label>
                                    <input type="color" id="picker_{{ $tabId }}_{{ $i }}" value="{{ $customCols[$i] ?? '#4f46e5' }}"
                                        class="h-12 w-24 rounded-lg border-2 border-gray-900 cursor-pointer block mb-1">
                                    <input type="text" name="custom_colores_{{ $numColores }}[]" value="{{ $customCols[$i] ?? '#4f46e5' }}"
                                        class="block w-28 text-xs font-mono px-2 py-1 rounded border border-gray-300" maxlength="7" pattern="#[0-9A-Fa-f]{6}">
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endforeach


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-900">Logo principal</label>
                    @if(!empty($logoMain))
                        <div class="flex items-center gap-3">
                            @php
                                $logoMainUrl = str_starts_with($logoMain, 'storage/') ? asset('storage/' . str_replace('storage/', '', $logoMain)) : asset($logoMain);
                            @endphp
                            <img src="{{ $logoMainUrl }}" alt="Logo principal" class="h-14 w-auto object-contain border border-gray-900 rounded-lg bg-white px-2 py-1">
                            <span class="text-xs text-gray-600">Actual</span>
                        </div>
                    @endif
                    <input type="file" name="logo_main" accept="image/png,image/jpeg,image/webp" class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-gray-900">
                </div>
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-900">Logo secundario (opcional)</label>
                    @if(!empty($logoSecondary))
                        <div class="flex items-center gap-3">
                            @php
                                $logoSecondaryUrl = str_starts_with($logoSecondary, 'storage/') ? asset('storage/' . str_replace('storage/', '', $logoSecondary)) : asset($logoSecondary);
                            @endphp
                            <img src="{{ $logoSecondaryUrl }}" alt="Logo secundario" class="h-14 w-auto object-contain border border-gray-900 rounded-lg bg-white px-2 py-1">
                            <span class="text-xs text-gray-600">Actual</span>
                        </div>
                    @endif
                    <input type="file" name="logo_secondary" accept="image/png,image/jpeg,image/webp" class="w-full px-3 py-2 rounded-lg border border-gray-900 bg-white text-gray-900">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition-all shadow-lg hover:opacity-90 tema-gradient">
                    Aplicar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="personalizacion"]');
    if (!form) return;

    const customBlocks = {
        custom_editable: document.getElementById('custom-colors-block'),
        custom_p3: document.getElementById('custom-block-por3'),
        custom_p4: document.getElementById('custom-block-por4'),
        custom_p5: document.getElementById('custom-block-por5'),
        custom_p6: document.getElementById('custom-block-por6'),
    };

    function toggleCustomBlocks() {
        const checked = form.querySelector('input[name="tema"]:checked');
        const val = checked ? checked.value : '';
        Object.keys(customBlocks).forEach(function(k) {
            const el = customBlocks[k];
            if (el) el.classList.toggle('hidden', val !== k);
        });
    }

    document.querySelectorAll('input[type="color"]').forEach(function(inp) {
        const next = inp.nextElementSibling;
        if (next && next.tagName === 'INPUT') {
            inp.addEventListener('input', function() { next.value = inp.value; });
            next.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(next.value)) inp.value = next.value;
            });
        }
    });

    form.querySelectorAll('input[name="tema"]').forEach(function(radio) {
        radio.addEventListener('change', toggleCustomBlocks);
    });

    toggleCustomBlocks();
});

</script>
@endsection

<?php

namespace App\Http\Controllers;

use App\Helpers\TemaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class PersonalizacionController extends Controller
{
    public static function getCacheKey(): string
    {
        return config('temas_sistema.cache_key', 'sistema_tema_color');
    }

    /**
     * Solo megadmin puede acceder (middleware lo comprueba).
     */
    public function index(): View
    {
        $temas = TemaHelper::todosTemas();
        $temaActual = Cache::get(self::getCacheKey(), config('temas_sistema.default', 'indigo'));
        $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));
        $customColores = Cache::get(config('temas_sistema.custom_cache_key', 'sistema_tema_custom_colores'), ['#4f46e5', '#7c3aed']);
        $defaultsP3 = ['#4f46e5', '#7c3aed', '#6366f1'];
        $defaultsP4 = ['#4f46e5', '#7c3aed', '#6366f1', '#22c55e'];
        $defaultsP5 = ['#4f46e5', '#7c3aed', '#6366f1', '#22c55e', '#f59e0b'];
        $defaultsP6 = ['#4f46e5', '#7c3aed', '#6366f1', '#22c55e', '#f59e0b', '#ec4899'];

        return view('personalizacion.index', [
            'temas' => $temas,
            'temaActual' => $temaActual,
            'logoMain' => $logoMain,
            'logoSecondary' => $logoSecondary,
            'customColores' => $customColores,
            'customColoresP3' => Cache::get(config('temas_sistema.custom_cache_p3', 'sistema_tema_custom_p3'), $defaultsP3),
            'customColoresP4' => Cache::get(config('temas_sistema.custom_cache_p4', 'sistema_tema_custom_p4'), $defaultsP4),
            'customColoresP5' => Cache::get(config('temas_sistema.custom_cache_p5', 'sistema_tema_custom_p5'), $defaultsP5),
            'customColoresP6' => Cache::get(config('temas_sistema.custom_cache_p6', 'sistema_tema_custom_p6'), $defaultsP6),
        ]);
    }

    /**
     * Guardar el tema seleccionado para todo el sistema.
     */
    public function store(Request $request): RedirectResponse
    {
        $todos = TemaHelper::todosTemas();
        $clavesValidas = implode(',', array_keys($todos));

        $reglas = [
            'tema' => 'required|string|in:' . $clavesValidas,
            'logo_main' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'logo_secondary' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ];

        if ($request->input('tema') === 'custom_editable') {
            $reglas['custom_from'] = 'required|string|regex:/^#[0-9A-Fa-f]{6}$/';
            $reglas['custom_to'] = 'required|string|regex:/^#[0-9A-Fa-f]{6}$/';
        }
        foreach (['custom_p3' => 3, 'custom_p4' => 4, 'custom_p5' => 5, 'custom_p6' => 6] as $ck => $n) {
            if ($request->input('tema') === $ck) {
                $reglas["custom_colores_{$n}"] = 'required|array';
                $reglas["custom_colores_{$n}.*"] = 'required|string|regex:/^#[0-9A-Fa-f]{6}$/';
            }
        }

        $request->validate($reglas);

        Cache::forever(self::getCacheKey(), $request->input('tema'));

        if ($request->input('tema') === 'custom_editable') {
            Cache::forever(config('temas_sistema.custom_cache_key', 'sistema_tema_custom_colores'), [
                $request->input('custom_from'),
                $request->input('custom_to'),
            ]);
        }
        foreach (['custom_p3' => [config('temas_sistema.custom_cache_p3', 'sistema_tema_custom_p3'), 3], 'custom_p4' => [config('temas_sistema.custom_cache_p4', 'sistema_tema_custom_p4'), 4], 'custom_p5' => [config('temas_sistema.custom_cache_p5', 'sistema_tema_custom_p5'), 5], 'custom_p6' => [config('temas_sistema.custom_cache_p6', 'sistema_tema_custom_p6'), 6]] as $ck => [$cacheKey, $n]) {
            if ($request->input('tema') === $ck) {
                $cols = $request->input("custom_colores_{$n}", []);
                Cache::forever($cacheKey, array_slice(array_pad($cols, $n, '#4f46e5'), 0, $n));
            }
        }

        if ($request->hasFile('logo_main')) {
            // Eliminar logo anterior si existe
            $logoAnterior = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
            if ($logoAnterior && file_exists(public_path($logoAnterior))) {
                unlink(public_path($logoAnterior));
            }
            // Guardar en public/img/logos/logos
            $file = $request->file('logo_main');
            $filename = 'logo_principal_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos/logos'), $filename);
            $path = 'img/logos/logos/' . $filename;
            Cache::forever(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'), $path);
        }
        if ($request->hasFile('logo_secondary')) {
            // Eliminar logo anterior si existe
            $logoAnterior = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));
            if ($logoAnterior && file_exists(public_path($logoAnterior))) {
                unlink(public_path($logoAnterior));
            }
            // Guardar en public/img/logos/logos
            $file = $request->file('logo_secondary');
            $filename = 'logo_secundario_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos/logos'), $filename);
            $path = 'img/logos/logos/' . $filename;
            Cache::forever(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'), $path);
        }

        return redirect()
            ->route('personalizacion.index')
            ->with('success', 'Personalización creada correctamente.');
    }
}

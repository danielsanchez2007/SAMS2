<?php

namespace App\Helpers;

class TemaHelper
{
    /**
     * Convierte hex a rgb string (r, g, b).
     */
    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return '79, 70, 229';
        }
        $int = hexdec($hex);
        $r = ($int >> 16) & 255;
        $g = ($int >> 8) & 255;
        $b = $int & 255;
        return "{$r}, {$g}, {$b}";
    }

    /**
     * Normaliza un tema (con from/to o colores) al formato completo.
     */
    public static function normalizar(array $tema): array
    {
        if (isset($tema['from'], $tema['to']) && empty($tema['colores'])) {
            $tema['gradient_full'] = $tema['gradient_full'] ?? 'linear-gradient(to right, ' . ($tema['from'] ?? '#4f46e5') . ', ' . ($tema['to'] ?? '#7c3aed') . ')';
            return $tema;
        }

        $colores = $tema['colores'] ?? [$tema['from'] ?? '#4f46e5', $tema['to'] ?? '#7c3aed'];
        $c1 = $colores[0];
        $cLast = $colores[count($colores) - 1];
        $cPrimary = $colores[min(1, count($colores) - 1)] ?? $cLast;

        $primaryRgb = self::hexToRgb($cPrimary);
        $parts = explode(',', $primaryRgb);
        $r = (int) trim($parts[0] ?? 99);
        $g = (int) trim($parts[1] ?? 102);
        $b = (int) trim($parts[2] ?? 241);

        $gradientFull = 'linear-gradient(to right, ' . implode(', ', $colores) . ')';

        return array_merge($tema, [
            'from' => $c1,
            'to' => $cLast,
            'primary' => $cPrimary,
            'primary_hover' => $c1,
            'primary_light' => "rgba({$r}, {$g}, {$b}, 0.2)",
            'primary_text' => '#' . sprintf('%02x%02x%02x', min(255, $r + 80), min(255, $g + 80), min(255, $b + 80)),
            'ring' => $primaryRgb,
            'gradient_full' => $gradientFull,
        ]);
    }

    /**
     * Obtiene todos los temas consolidados (por 2, por 3, por 4, por 5, por 6, custom).
     */
    public static function todosTemas(): array
    {
        $por2 = config('temas_sistema.temas', []);
        $por3 = config('temas_sistema.temas_por_3', []);
        $por4 = config('temas_sistema.temas_por_4', []);
        $por5 = config('temas_sistema.temas_por_5', []);
        $por6 = config('temas_sistema.temas_por_6', []);

        $customCacheKey = config('temas_sistema.custom_cache_key', 'sistema_tema_custom_colores');
        $custom = \Illuminate\Support\Facades\Cache::get($customCacheKey);

        $out = [];
        foreach ($por2 as $k => $t) {
            $out[$k] = self::normalizar(array_merge($t, ['tipo' => 'por_2']));
        }
        foreach ($por3 as $k => $t) {
            $out['p3_' . $k] = self::normalizar(array_merge($t, ['tipo' => 'por_3']));
        }
        foreach ($por4 as $k => $t) {
            $out['p4_' . $k] = self::normalizar(array_merge($t, ['tipo' => 'por_4']));
        }
        foreach ($por5 as $k => $t) {
            $out['p5_' . $k] = self::normalizar(array_merge($t, ['tipo' => 'por_5']));
        }
        foreach ($por6 as $k => $t) {
            $out['p6_' . $k] = self::normalizar(array_merge($t, ['tipo' => 'por_6']));
        }

        $customColores = ($custom && is_array($custom) && count($custom) >= 2)
            ? $custom
            : ['#4f46e5', '#7c3aed'];
        $out['custom_editable'] = self::normalizar([
            'nombre' => 'Personalizado',
            'editable' => true,
            'colores' => $customColores,
            'tipo' => 'por_2',
        ]);

        $customByType = [
            'custom_p3' => [config('temas_sistema.custom_cache_p3', 'sistema_tema_custom_p3'), 3],
            'custom_p4' => [config('temas_sistema.custom_cache_p4', 'sistema_tema_custom_p4'), 4],
            'custom_p5' => [config('temas_sistema.custom_cache_p5', 'sistema_tema_custom_p5'), 5],
            'custom_p6' => [config('temas_sistema.custom_cache_p6', 'sistema_tema_custom_p6'), 6],
        ];
        foreach ($customByType as $key => [$cacheKey, $n]) {
            $cols = \Illuminate\Support\Facades\Cache::get($cacheKey);
            $cols = ($cols && is_array($cols) && count($cols) >= $n) ? $cols : null;
            if (!$cols) {
                $defaults = ['#4f46e5', '#7c3aed', '#6366f1', '#22c55e', '#f59e0b', '#ec4899'];
                $cols = array_slice(array_pad($defaults, $n, '#4f46e5'), 0, $n);
            }
            $out[$key] = self::normalizar([
                'nombre' => 'Personalizado',
                'editable' => true,
                'colores' => $cols,
                'tipo' => 'por_' . $n,
            ]);
        }

        return $out;
    }

    /**
     * Obtiene el tema actual (por clave cacheada).
     */
    public static function temaActual(): array
    {
        $key = \Illuminate\Support\Facades\Cache::get(
            config('temas_sistema.cache_key', 'sistema_tema_color'),
            config('temas_sistema.default', 'indigo')
        );
        $todos = self::todosTemas();
        $tema = $todos[$key] ?? $todos['indigo'] ?? [];
        return self::normalizar($tema);
    }
}

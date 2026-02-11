<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LogoHelper
{
    public static function getLogoPaths(): array
    {
        $main = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $secondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));

        $mainPath = $main ? Storage::disk('public')->path($main) : null;
        $secondaryPath = $secondary ? Storage::disk('public')->path($secondary) : null;

        if (empty($mainPath) || !file_exists($mainPath)) {
            $mainPath = file_exists(public_path('logos/logoSams1.png'))
                ? public_path('logos/logoSams1.png')
                : public_path('img/logos/logoSams.png');
        }

        if (empty($secondaryPath) || !file_exists($secondaryPath)) {
            $secondaryPath = public_path('logos/LOGO-INSTITUTO-PREVENTION-WORLD.png');
        }

        return [
            'main' => $mainPath,
            'secondary' => $secondaryPath,
        ];
    }

    public static function getBase64Logos(): array
    {
        $paths = self::getLogoPaths();

        return [
            'logoSamsBase64' => self::toBase64($paths['main']),
            'logoPreventionBase64' => self::toBase64($paths['secondary']),
        ];
    }

    private static function toBase64(?string $path): ?string
    {
        if (empty($path) || !file_exists($path)) {
            return null;
        }
        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}

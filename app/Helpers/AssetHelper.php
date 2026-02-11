<?php

namespace App\Helpers;

class AssetHelper
{
    private static ?bool $hotStatus = null;

    /**
     * Decide si se puede cargar @vite de forma segura.
     *
     * - Si hay "hot" válido y alcanzable: usa dev server.
     * - Si "hot" está obsoleto/inaccesible: lo elimina para forzar fallback.
     * - Si existe manifest de build: también permite @vite.
     */
    public static function shouldLoadViteAssets(): bool
    {
        self::ensureHotFileIsUsable();

        return is_file(public_path('hot')) || is_file(public_path('build/manifest.json'));
    }

    private static function ensureHotFileIsUsable(): void
    {
        if (self::$hotStatus !== null) {
            return;
        }

        $hotPath = public_path('hot');

        if (!is_file($hotPath)) {
            self::$hotStatus = false;
            return;
        }

        $hotUrl = trim((string) @file_get_contents($hotPath));
        if ($hotUrl === '') {
            @unlink($hotPath);
            self::$hotStatus = false;
            return;
        }

        if (!self::isUrlReachable($hotUrl)) {
            // Hot file obsoleto (Vite caído/puerto ocupado): eliminar para que @vite use manifest.
            @unlink($hotPath);
            self::$hotStatus = false;
            return;
        }

        self::$hotStatus = true;
    }

    private static function isUrlReachable(string $url): bool
    {
        $parts = parse_url($url);
        $host = $parts['host'] ?? null;
        $port = $parts['port'] ?? null;

        if (!$host || !$port) {
            return false;
        }

        $errno = 0;
        $errstr = '';
        $timeoutSeconds = 0.2;
        $connection = @fsockopen($host, (int) $port, $errno, $errstr, $timeoutSeconds);

        if (!is_resource($connection)) {
            return false;
        }

        fclose($connection);
        return true;
    }
}

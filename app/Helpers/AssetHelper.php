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

        if (!self::isViteServerAvailable($hotUrl)) {
            // Hot file obsoleto (Vite caído/puerto ocupado por otro proceso): eliminar para usar manifest.
            @unlink($hotPath);
            self::$hotStatus = false;
            return;
        }

        self::$hotStatus = true;
    }

    private static function isViteServerAvailable(string $url): bool
    {
        $probeUrl = rtrim($url, '/') . '/@vite/client';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 0.8,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($probeUrl, false, $context);
        if ($body === false) {
            return false;
        }

        $statusCode = null;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $statusCode = (int) $matches[1];
        }

        if ($statusCode === null || $statusCode < 200 || $statusCode >= 400) {
            return false;
        }

        // Vite client contiene estos tokens en JS; evita aceptar cualquier servidor en ese puerto.
        return str_contains($body, 'vite/client')
            || str_contains($body, 'import.meta.hot')
            || str_contains($body, '__vite');
    }
}

<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Obtiene la URL correcta de una imagen, ya sea de storage/ o de public/img/
     */
    public static function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        
        // Si empieza con storage/, usar asset('storage/...')
        if (str_starts_with($path, 'storage/')) {
            return asset('storage/' . str_replace('storage/', '', $path));
        }
        
        // Si ya es una ruta de public/img, usar asset directo
        return asset($path);
    }
}

<?php

namespace App\Helpers;

class PermisoHelper
{
    /**
     * Verifica si el usuario actual tiene permiso para un módulo y acción.
     */
    public static function puede(string $modulo, string $accion = 'acceso'): bool
    {
        $user = session('sams2_user');
        
        if (!$user) {
            return false;
        }
        
        // Mega Admin tiene acceso total (permisos = null)
        if ($user['role'] === 'mega_admin' || $user['permisos'] === null) {
            return true;
        }
        
        // Usuarios normales: verificar permisos del rol
        $permisos = $user['permisos'] ?? [];
        
        if (!isset($permisos[$modulo])) {
            return false;
        }
        
        return $permisos[$modulo][$accion] ?? false;
    }

    /**
     * Verifica si el usuario puede acceder a cualquier submódulo de un grupo.
     */
    public static function puedeAlgunoDe(array $modulos): bool
    {
        foreach ($modulos as $modulo) {
            if (self::puede($modulo, 'acceso')) {
                return true;
            }
        }
        return false;
    }
}

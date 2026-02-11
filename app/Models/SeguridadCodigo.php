<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadCodigo extends Model
{
    protected $table = 'seguridad_codigo';

    protected $fillable = ['codigo', 'bloqueado', 'password_edicion'];

    protected $casts = [
        'bloqueado' => 'boolean',
    ];

    protected $hidden = ['password_edicion'];

    /**
     * Obtiene el registro único de seguridad (singleton).
     */
    public static function obtener(): self
    {
        return self::firstOrCreate([], [
            'codigo' => null,
            'bloqueado' => true,
        ]);
    }

    /**
     * Verifica si un usuario puede editar el código.
     */
    public static function puedeEditar($usuarioId = null, $roleId = null): bool
    {
        $user = session('sams2_user');
        
        // Mega Admin siempre puede editar
        if (($user['role'] ?? null) === 'mega_admin') {
            return true;
        }

        // Verificar permisos específicos
        $permiso = \App\Models\SeguridadPermiso::where(function($q) use ($usuarioId, $roleId, $user) {
            if ($usuarioId) {
                $q->where('usuario_id', $usuarioId);
            } elseif ($roleId) {
                $q->where('role_id', $roleId);
            } else {
                // Usar datos de sesión
                if (isset($user['id']) && $user['id'] !== 'mega_admin') {
                    $q->where('usuario_id', $user['id']);
                }
                if (isset($user['role_id'])) {
                    $q->orWhere('role_id', $user['role_id']);
                }
            }
        })->where('puede_editar', true)->first();

        return $permiso !== null;
    }
}

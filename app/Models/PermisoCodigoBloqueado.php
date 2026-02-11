<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoCodigoBloqueado extends Model
{
    protected $table = 'permisos_codigo_bloqueado';

    protected $fillable = [
        'usuario_id', 'role_id', 'puede_desbloquear_codigo'
    ];

    protected $casts = [
        'puede_desbloquear_codigo' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Verifica si un usuario puede desbloquear códigos.
     */
    public static function puedeDesbloquear($usuarioId = null, $roleId = null): bool
    {
        $user = session('sams2_user');
        
        // Mega Admin siempre puede
        if (($user['role'] ?? null) === 'mega_admin') {
            return true;
        }
        
        // Verificar si el rol es "administrador" (buscar por nombre)
        if (isset($user['role_id'])) {
            $role = Role::find($user['role_id']);
            if ($role && strtolower($role->nombre) === 'administrador') {
                return true;
            }
        }

        // Verificar permisos específicos
        $permiso = self::where(function($q) use ($usuarioId, $roleId, $user) {
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
        })->where('puede_desbloquear_codigo', true)->first();

        return $permiso !== null;
    }
}

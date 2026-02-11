<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'tipo_documento', 'cedula', 'nombre', 'apellidos', 'fecha_nacimiento',
        'direccion', 'telefono', 'correo_electronico',
        'tiene_correo_corporativo', 'correo_corporativo',
        'tiene_telefono_corporativo', 'telefono_corporativo',
        'departamento', 'departamento_otro', 'municipio', 'municipio_otro',
        'tratamiento', 'empresa_id', 'sede_id', 'grupo_id', 'cargo_id', 'role_id',
        'username', 'password', 'activo', 'imagen_usuario', 'firma_imagen',
    ];

    protected $casts = [
        'tiene_correo_corporativo' => 'boolean',
        'tiene_telefono_corporativo' => 'boolean',
        'activo' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    protected $hidden = ['password'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    /**
     * Verifica si el usuario tiene permiso para un módulo y acción específica.
     */
    public function tienePermiso(string $modulo, string $accion = 'acceso'): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->getPermiso($modulo, $accion);
    }
}

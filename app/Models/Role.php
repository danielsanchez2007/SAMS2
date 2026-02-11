<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre', 'permisos'];

    protected $casts = [
        'permisos' => 'array',
    ];

    public function getPermiso(string $modulo, string $accion): bool
    {
        $permisos = $this->permisos ?? [];
        return (bool) ($permisos[$modulo][$accion] ?? false);
    }
}

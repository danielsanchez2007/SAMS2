<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguridadPermiso extends Model
{
    protected $table = 'seguridad_permisos';

    protected $fillable = ['usuario_id', 'role_id', 'puede_editar'];

    protected $casts = [
        'puede_editar' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

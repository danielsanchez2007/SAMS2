<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipoBaja extends Model
{
    protected $table = 'equipos_baja';

    protected $fillable = [
        'equipo_id',
        'fecha_baja',
        'acta_numero',
        'resumen_baja',
        'acta_pdf',
        'responsable_inventario_nombre',
        'responsable_inventario_cc',
        'gerente_administrativa_nombre',
        'gerente_administrativa_cc',
        'asistentes',
        'items_baja',
    ];

    protected $casts = [
        'fecha_baja' => 'date',
        'asistentes' => 'array',
        'items_baja' => 'array',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}

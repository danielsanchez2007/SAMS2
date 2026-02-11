<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeccionEquipo extends Model
{
    protected $table = 'inspecciones_equipos';

    protected $fillable = [
        'equipo_id',
        'tipo_equipo_id',
        'fecha_inspeccion',
        'validez_inspeccion',
    ];

    protected $casts = [
        'fecha_inspeccion' => 'date',
        'validez_inspeccion' => 'date',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function tipoEquipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }
}


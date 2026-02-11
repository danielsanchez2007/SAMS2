<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodigoDisponible extends Model
{
    protected $table = 'codigos_disponibles';

    protected $fillable = [
        'codigo', 'origen_tipo', 'equipo_original_id', 'descripcion_original', 'utilizado'
    ];

    protected $casts = [
        'utilizado' => 'boolean',
    ];

    public function equipoOriginal(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_original_id');
    }
}

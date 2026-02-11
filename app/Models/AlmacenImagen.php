<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlmacenImagen extends Model
{
    protected $table = 'almacen_imagenes';

    protected $fillable = ['etiqueta_id', 'equipo_id', 'ruta', 'nombre_original'];

    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(EtiquetaAlmacen::class, 'etiqueta_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}

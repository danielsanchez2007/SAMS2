<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlmacenArchivo extends Model
{
    protected $table = 'almacen_archivos';

    protected $fillable = ['nombre', 'ruta', 'etiqueta_id', 'equipo_id', 'tipo_equipo_id', 'mime_type'];

    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(EtiquetaAlmacen::class, 'etiqueta_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function tipoEquipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }
}

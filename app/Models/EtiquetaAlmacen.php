<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EtiquetaAlmacen extends Model
{
    protected $table = 'etiquetas_almacen';

    protected $fillable = ['nombre', 'tipo'];

    public function imagenes(): HasMany
    {
        return $this->hasMany(AlmacenImagen::class, 'etiqueta_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(AlmacenArchivo::class, 'etiqueta_id');
    }
}

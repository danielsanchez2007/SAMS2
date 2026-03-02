<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    protected $table = 'tipo_equipos';

    protected $fillable = ['nombre', 'formato_archivo', 'formato_html'];

    public function tieneFormato(): bool
    {
        return !empty($this->formato_archivo);
    }
}

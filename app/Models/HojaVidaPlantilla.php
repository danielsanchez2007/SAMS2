<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaVidaPlantilla extends Model
{
    protected $table = 'hoja_vida_plantilla';

    protected $fillable = ['tipo_equipo_id', 'nombre', 'contenido_html'];

    public function tipoEquipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class);
    }

    public function valoresFijos(): HasMany
    {
        return $this->hasMany(HojaVidaValorFijo::class, 'hoja_vida_plantilla_id');
    }

    /**
     * Obtiene la plantilla asignada a un tipo de equipo (para exportar hoja de vida).
     * Si $tipoEquipoId es null, devuelve la plantilla global (tipo_equipo_id null), si existe.
     */
    public static function porTipoEquipo(?int $tipoEquipoId): ?self
    {
        $query = self::with('valoresFijos');
        if ($tipoEquipoId !== null) {
            $query->where('tipo_equipo_id', $tipoEquipoId);
        } else {
            $query->whereNull('tipo_equipo_id');
        }
        return $query->first();
    }
}

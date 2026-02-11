<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDidactico extends Model
{
    protected $table = 'material_didactico';

    protected $fillable = [
        'empresa_id', 'equipo_original_id', 'codigo_original', 'codigo', 'tipo_item_id', 'tipo_equipo_id', 'codigo_bloqueado',
        'estado_remision_id', 'descripcion', 'marca', 'proveedor_id', 'fabricante_id', 'modelo',
        'sede_id', 'bodega_id', 'vida_util', 'fecha_fabricacion', 'fecha_uso', 'uso_item_id',
        'es_kit', 'nombre_kit', 'componentes_kit', 'valor', 'numero_factura',
        'capacidades_resistencia', 'lote', 'fecha_compra',
        'tiene_manual_fabricante', 'manual_fabricante',
        'tiene_certificacion', 'certificacion_fabricante',
        'tiene_imagen_general', 'imagen_general',
        'tiene_imagen_etiqueta', 'imagen_etiqueta',
    ];

    protected $casts = [
        'codigo_bloqueado' => 'boolean',
        'es_kit' => 'boolean',
        'tiene_manual_fabricante' => 'boolean',
        'tiene_certificacion' => 'boolean',
        'tiene_imagen_general' => 'boolean',
        'tiene_imagen_etiqueta' => 'boolean',
        'fecha_fabricacion' => 'date',
        'fecha_uso' => 'date',
        'fecha_compra' => 'date',
        'valor' => 'decimal:2',
        'vida_util' => 'integer',
    ];

    public function equipoOriginal(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_original_id');
    }

    public function tipoItem(): BelongsTo
    {
        return $this->belongsTo(TipoItem::class);
    }

    public function tipoEquipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class);
    }

    public function estadoRemision(): BelongsTo
    {
        return $this->belongsTo(EstadoRemision::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function fabricante(): BelongsTo
    {
        return $this->belongsTo(Fabricante::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function usoItem(): BelongsTo
    {
        return $this->belongsTo(UsoItem::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}

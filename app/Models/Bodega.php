<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bodega extends Model
{
    protected $fillable = ['sede_id', 'nombre'];

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }
}

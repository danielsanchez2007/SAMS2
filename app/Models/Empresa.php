<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = ['nombre', 'pais'];

    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class);
    }
}

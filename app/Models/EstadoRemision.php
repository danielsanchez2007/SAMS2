<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoRemision extends Model
{
    protected $table = 'estado_remisiones';

    protected $fillable = ['nombre'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsoItem extends Model
{
    protected $table = 'uso_items';

    protected $fillable = ['nombre'];
}

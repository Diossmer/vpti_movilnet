<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class PerifericoDescripcion extends Model
{
    protected $table = 'periferico_descripcions';

    protected $fillable = [
        'periferico_id',
        'descripcion_id',
    ];

    protected $hidden = [
        'periferico_id',
        'descripcion_id',
        'created_at',
        'updated_at'
    ];
}

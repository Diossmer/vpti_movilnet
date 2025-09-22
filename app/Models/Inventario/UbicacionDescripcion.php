<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class UbicacionDescripcion extends Model
{
    protected $table = 'ubicacion_descripcions';

    protected $fillable = [
        'ubicacion_id',
        'descripcion_id',
    ];

    protected $hidden = [
        'ubicacion_id',
        'descripcion_id',
        'created_at',
        'updated_at'
    ];
}

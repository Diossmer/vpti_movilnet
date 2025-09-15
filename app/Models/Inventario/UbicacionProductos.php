<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class UbicacionProductos extends Model
{
    protected $table = 'ubicacion_productos';

    protected $fillable = [
        'ubicacion_id',
        'producto_id',
    ];

    protected $hidden = [
        'ubicacion_id',
        'producto_id',
        'created_at',
        'updated_at'
    ];
}

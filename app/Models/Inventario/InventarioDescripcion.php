<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class InventarioDescripcion extends Model
{
    protected $table = 'inventario_descripcions';

    protected $fillable = [
        'inventario_id',
        'descripcion_id',
    ];

    protected $hidden = [
        'inventario_id',
        'descripcion_id',
        'created_at',
        'updated_at'
    ];
}

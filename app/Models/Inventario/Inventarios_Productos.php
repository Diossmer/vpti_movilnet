<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Inventarios_Productos extends Model
{
    protected $table = 'inventarios_productos';

    protected $fillable = [
        'inventario_id',
        'producto_id',
    ];

    protected $hidden = [
        'inventario_id',
        'producto_id',
        'created_at',
        'updated_at'
    ];
}

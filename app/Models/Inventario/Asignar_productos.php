<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Asignar_productos extends Model
{
    protected $table = 'asignados_productos';

    protected $fillable = [
        'asignar_id',
        'producto_id',
    ];

    protected $hidden = [
        'asignar_id',
        'producto_id',
        'created_at',
        'updated_at'
    ];
}

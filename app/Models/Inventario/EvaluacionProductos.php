<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class EvaluacionProductos extends Model
{
    protected $table = 'evaluacion_productos';

    protected $fillable = [
        'evaluacion_id',
        'producto_id',
    ];

    protected $hidden = [
        'evaluacion_id',
        'producto_id',
        'created_at',
        'updated_at'
    ];
}

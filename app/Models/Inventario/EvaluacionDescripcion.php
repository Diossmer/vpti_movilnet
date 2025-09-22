<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDescripcion extends Model
{
    protected $table = 'evaluacion_descripcions';

    protected $fillable = [
        'evaluacion_id',
        'descripcion_id',
    ];

    protected $hidden = [
        'evaluacion_id',
        'descripcion_id',
        'created_at',
        'updated_at'
    ];
}

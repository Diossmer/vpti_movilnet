<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class AsignarDescripcion extends Model
{
    protected $table = 'asignar_descripcions';

    protected $fillable = [
        'asignar_id',
        'descripcion_id',
    ];

    protected $hidden = [
        'asignar_id',
        'descripcion_id',
        'created_at',
        'updated_at'
    ];
}

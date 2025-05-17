<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Perifericos_Productos extends Model
{
    protected $table = 'perifericos_productos';

    protected $fillable = [
        'periferico_id',
        'producto_id',
    ];

    protected $hidden = [
        'periferico_id',
        'producto_id',
        'created_at',
        'updated_at'
    ];
}

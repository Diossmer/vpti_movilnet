<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluaciones extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = [
        'estado_fisico',
        'escala',
        'compatibilidad',
        'reemplazo',
        'mantenimineto',
        'notas',
        'producto_id',
        'estatus_id',
        'descripcion_id',
    ];

    protected $hidden = [
        'producto_id',
        'estatus_id',
        'descripcion_id',
        'created_at',
        'updated_at'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventario\Productos::class,'producto_id');
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estatus::class,'estatus_id');
    }

    public function descripcion(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventario\Descripcion::class,'descripcion_id');
    }
}

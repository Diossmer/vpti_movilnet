<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evaluaciones extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = [
        'escala',
        'compatibilidad',
        'reemplazo',
        'mantenimiento',
        'notas',
        'estatus_id',
    ];

    protected $hidden = [
        'estatus_id',
        'created_at',
        'updated_at'
    ];

    /* public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Productos::class,'evaluacion_productos','evaluacion_id','producto_id')->withTimestamps();
    } */

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estatus::class,'estatus_id');
    }

    public function descripciones(): BelongsToMany
    {
        return $this->belongsToMany(Descripcion::class,'evaluacion_descripcions','evaluacion_id','descripcion_id')->withTimestamps();
    }
}

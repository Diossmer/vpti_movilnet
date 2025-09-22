<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asignacion extends Model
{
    protected $table = 'asignacion';

    protected $fillable = [
        'fecha_asignar',
        'fecha_devolucion',
        'destino',
        'comentario',
        'usuario_id',
        'estatus_id',
    ];

    protected $hidden = [
        'usuario_id',
        'estatus_id',
        'created_at',
        'updated_at'
    ];

    /* public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Productos::class,'asignados_productos','asignar_id','producto_id')->withTimestamps();
    } */

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estatus::class,'estatus_id');
    }
    
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Usuarios::class,'usuario_id');
    }

    public function descripciones(): BelongsToMany
    {
        return $this->belongsToMany(Descripcion::class,'asignar_descripcions','asignar_id','descripcion_id')->withTimestamps();
    }
}

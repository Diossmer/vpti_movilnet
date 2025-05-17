<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventarios extends Model
{
    protected $table = 'inventarios';

    protected $fillable = [
        'cantidad_existente',
        'entrada',
        'salida',
        'descripcion',
        'estatus_id',
    ];

    protected $hidden = [
        'estatus_id',
        'created_at',
        'updated_at'
    ];

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estatus::class,'estatus_id');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Productos::class,'inventarios_productos','inventario_id','producto_id')->withTimestamps();
    }
}

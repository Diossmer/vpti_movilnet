<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Perifericos extends Model
{
    protected $table = 'perifericos';

    protected $fillable = [
        'cantidad_existente',
        'entrada',
        'salida',
        'descripcion',
        'estatus_id'
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
        return $this->belongsToMany(\App\Models\Inventario\Productos::class,'perifericos_productos','periferico_id','producto_id')->withTimestamps();
    }
}

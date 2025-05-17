<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Productos extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'usuario_id',
        'estatus_id',
    ];

    protected $hidden = [
        'usuario_id',
        'estatus_id',
        'created_at',
        'updated_at'
    ];

    public function descripciones(): HasMany
    {
        return $this->hasMany(Descripcion::class,'producto_id');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluaciones::class,'producto_id');
    }

    public function perifericos(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Inventario\Perifericos::class,'perifericos_productos','producto_id','periferico_id')->withTimestamps();
    }

    public function inventarios(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Inventario\Inventarios::class,'inventarios_productos','producto_id','inventario_id')->withTimestamps();
    }

    //administrativo
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Usuarios::class,'usuario_id');
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Estatus::class,'estatus_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estatus extends Model
{
    protected $table = 'estatus';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuarios::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Productos::class,'id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Asignacion::class,'estatus_id');
    }

    public function perifericos(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Perifericos::class,'estatus_id');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Inventarios::class,'estatus_id');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Evaluaciones::class,'estatus_id');
    }
}

<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Descripcion extends Model
{
    protected $table = 'descripcion';

    protected $fillable = [
        'codigo',
        'modelo',
        'dispositivo',
        'serial',
        'marca',
        'codigo_inv',
        'observacion',
        'producto_id'
    ];

    protected $hidden = [
        'producto_id',
        'created_at',
        'updated_at'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class,'descripcion_id');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluaciones::class,'descripcion_id');
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class,'descripcion_id');
    }
}

<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ubicacion extends Model
{
    protected $table = 'ubicacion';

    protected $fillable = [
        'origen',
        'destino',
        'piso',
        'region',
        'capital',
        'descripcion_id'
    ];

    protected $hidden = [
        'descripcion_id',
        'created_at',
        'updated_at'
    ];

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Productos::class,'ubicacion_productos','ubicacion_id','producto_id')->withTimestamps();
    }

    public function descripcion(): BelongsTo
    {
        return $this->belongsTo(Descripcion::class,'descripcion_id');
    }
}

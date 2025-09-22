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
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /* public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Productos::class,'ubicacion_productos','ubicacion_id','producto_id')->withTimestamps();
    } */

    public function descripciones(): BelongsToMany
    {
        return $this->belongsToMany(Descripcion::class,'ubicacion_descripcions','ubicacion_id','descripcion_id')->withTimestamps();
    }
}

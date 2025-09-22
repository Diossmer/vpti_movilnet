<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function asignaciones(): BelongsToMany
    {
        return $this->belongsToMany(Asignacion::class,'asignar_descripcions','descripcion_id','asignar_id')->withTimestamps();
    }

    public function evaluaciones(): BelongsToMany
    {
        return $this->belongsToMany(Evaluaciones::class,'evaluacion_descripcions','descripcion_id','evaluacion_id')->withTimestamps();
    }

    public function inventarios(): BelongsToMany
    {
        return $this->belongsToMany(Inventarios::class,'inventario_descripcions','descripcion_id','inventario_id')->withTimestamps();
    }

    public function perifericos(): BelongsToMany
    {
        return $this->belongsToMany(Perifericos::class,'periferico_descripcions','descripcion_id','periferico_id')->withTimestamps();
    }
    
    public function ubicaciones(): BelongsToMany
    {
        return $this->belongsToMany(Ubicacion::class,'ubicacion_descripcions','descripcion_id','ubicacion_id')->withTimestamps();
    }
}

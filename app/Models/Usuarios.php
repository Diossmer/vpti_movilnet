<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuarios extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'cedula',
        'usuario',
        'correo',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'telefono_casa',
        'telefono_celular',
        'telefono_alternativo',
        'password' ,
        'estatus_id',
        'rol_id',
    ];

    protected $hidden = [
        'estatus_id',
        'rol_id',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'correo_verified_at'
    ];

    protected function casts(): array
    {
        return [
            'correo_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(Estatus::class);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Roles::class);
    }

    //Inventario
    public function productos(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Productos::class,'usuario_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Inventario\Asignacion::class,'usuario_id');
    }
}

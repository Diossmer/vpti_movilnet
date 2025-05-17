<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Usuarios;
use App\Models\Estatus;
use App\Models\Roles;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Estatus::create([
            'nombre' => 'Activo',
            'descripcion' => 'Activo en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Incorporado',
            'descripcion' => 'Incorporado en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Asignado',
            'descripcion' => 'Asignado en el sistema a un usuario',
        ]);
        Estatus::create([
            'nombre' => 'Sustitución',
            'descripcion' => 'Sustitución en el sistema de un producto',
        ]);
        Estatus::create([
            'nombre' => 'Inactivo',
            'descripcion' => 'Inactivo en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Ausente',
            'descripcion' => 'Activo en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Principal',
            'descripcion' => 'Presente en la aplicación',
        ]);
        Estatus::create([
            'nombre' => 'Instalado',
            'descripcion' => 'Instalado en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Obsoleto',
            'descripcion' => 'Obsoleto en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Depositado',
            'descripcion' => 'Depositado en el sistema',
        ]);
        Estatus::create([
            'nombre' => 'Desincorporado',
            'descripcion' => 'Desincorporado del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Por Desincorporar',
            'descripcion' => 'Por desincorporar del sistema',
        ]);
        Estatus::create([
            'nombre' => 'En Revisión',
            'descripcion' => 'En revisión del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Pendiente',
            'descripcion' => 'Pendiente de revisión',
        ]);
        Estatus::create([
            'nombre' => 'Desvalijado',
            'descripcion' => 'Desvalijado del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Egresado',
            'descripcion' => 'Egresado del sistema',
        ]);
        Estatus::create([
            'nombre' => 'En Investigación',
            'descripcion' => 'En investigación del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Excluido',
            'descripcion' => 'Excluido del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Extraviado',
            'descripcion' => 'Extraviado del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Eliminado',
            'descripcion' => 'Eliminado del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Por Recuperar',
            'descripcion' => 'Por recuperar del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Recuperado',
            'descripcion' => 'Recuperado del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Producción',
            'descripcion' => 'Producción del sistema que utiliza el producto',
        ]);
        Estatus::create([
            'nombre' => 'Desarollo',
            'descripcion' => 'Desarrollo del sistema que utiliza el producto',
        ]);
        Estatus::create([
            'nombre' => 'Prueba',
            'descripcion' => 'Prueba del sistema que utiliza el producto',
        ]);
        Estatus::create([
            'nombre' => 'Reparación',
            'descripcion' => 'Reparación del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Por Recuperar Egreso',
            'descripcion' => 'Por recuperar egreso del sistema',
        ]);
        Estatus::create([
            'nombre' => 'Por Recuperar Sustitución',
            'descripcion' => 'Por recuperar sustitución del sistema',
        ]);
        Roles::create([
            'nombre' => 'SuperUsuario',
            'descripcion' => 'Control del Sistema.',
        ]);
        Roles::create([
            'nombre' => 'Control de calidad',
            'descripcion' => 'Inspector de productos.',
        ]);
        Roles::create([
            'nombre' => 'Recepción',
            'descripcion' => 'Verifica, Controla, Registra, Analiza y Ubica los Productos.',
        ]);
        Roles::create([
            'nombre' => 'Gestion de productos',
            'descripcion' => 'Preparación y Despacho de los Productos.',
        ]);
        Usuarios::create([
            'nombre' => 'principal',
            'apellido' => 'administrador',
            'cedula' => '12345678',
            'usuario'=> 'admin',
            'correo' => 'admin@gmail.com',
            'direccion'=>'av.prueba,dep.prueba,res.prueba.',
            'ciudad'=>'distrito capital',
            'estado'=>'caracas',
            'codigo_postal'=>'1088',
            'telefono_casa'=>'02120000000',
            'telefono_celular'=>'04160000000',
            'telefono_alternativo'=>'04240000000',
            'password' => Hash::make('5555'),
            'estatus_id'=>Estatus::where('nombre','=','Principal')->first()?->id,
            'rol_id'=>Roles::first()->id,
        ]);
    }
}

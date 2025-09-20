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
            'descripcion' => 'tiene permisos totales sobre el sistema. Su función principal es la administración y el control general.

Creación y gestión de usuarios y roles: Puede crear, modificar y eliminar cualquier cuenta de usuario, incluyendo la asignación de roles.

Acceso completo a todos los módulos: Tiene visibilidad y control sobre todas las funciones, desde la gestión de productos y colas hasta la visualización de todos los datos.

Gestión del sistema: Realiza tareas de mantenimiento, como la configuración de permisos, la supervisión de la actividad del sistema y la resolución de problemas a nivel administrativo.',
        ]);
        Roles::create([
            'nombre' => 'Control de calidad',
            'descripcion' => 'se enfoca en verificar que los productos y procesos cumplan con los estándares establecidos antes de ser distribuidos o almacenados.

Inspección de productos: Su función es revisar los periféricos y no periféricos que ingresan al inventario para asegurar que no tengan defectos.

Actualización de estado: Marcar los productos como "aprobados", "rechazados" o "en revisión" dentro del sistema.

Generación de informes: Crear reportes sobre la calidad de los productos, identificando fallas recurrentes o problemas con los proveedores.',
        ]);
        Roles::create([
            'nombre' => 'Recepción',
            'descripcion' => 'se encarga de la entrada inicial de productos al inventario.

Registro de productos: Registrar la llegada de nuevos productos, ya sean periféricos o no, en el sistema del inventario.

Asignación inicial: Etiquetar y asignar una ubicación temporal a los productos recibidos.

Validación de envíos: Corroborar que la cantidad y tipo de productos recibidos coincidan con la orden de compra o el manifiesto de envío.',
        ]);
        Roles::create([
            'nombre' => 'Gestion de productos',
            'descripcion' => 'administra y mantiene la información de los productos a lo largo de su ciclo de vida en el inventario.

Actualización de inventario: Monitorear los niveles de stock y actualizar la cantidad de productos disponibles.

Clasificación y organización: Mantener la base de datos de productos organizada, asignando categorías, subcategorías y descripciones detalladas.

Administración de colas: Gestionar las "colas" de trabajo relacionadas con los productos, como pedidos pendientes, devoluciones o movimientos entre almacenes.',
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
            'estatus_id'=>Estatus::where('nombre','=','Activo')->first()?->id,
            'rol_id'=>Roles::first()->id,
        ]);
    }
}

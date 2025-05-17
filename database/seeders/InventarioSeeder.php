<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario\Descripcion;
use App\Models\Inventario\Asignacion;
use App\Models\Inventario\Productos;
use App\Models\Inventario\Evaluaciones;
use App\Models\Inventario\Ubicacion;
use App\Models\Inventario\Inventarios;
use App\Models\Inventario\Perifericos;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        Productos::create([
            'nombre' => 'N/A',
            'usuario_id'=>1,
            'estatus_id'=>1,
        ]);

        Descripcion::create([
            'codigo' => 'N/A',
            'modelo' => 'N/A',
            'dispositivo' => 'N/A',
            'serial' => 'N/A',
            'marca' => 'N/A',
            'codigo_inv' => 'N/A',
            'observacion' => 'N/A',
            'producto_id' => 'N/A',
            'observacion' => 'N/A',
            'producto_id' => 1
        ]);

        Ubicacion::create([
            'origen'=>'N/A',
            'destino'=>'N/A',
            'piso'=>'N/A',
            'region'=>'N/A',
            "estado"=>"N/A",
            'capital'=>'N/A',
            'descripcion_id'=>1,
        ]);

        Asignacion::create([
            'fecha_asignar'=>'2025/03/02',
            'fecha_devolucion'=>now(),
            'destino'=>'ninguno',
            'comentario'=>'N/A',
            'usuario_id'=>1,
            'estatus_id'=>1,
            'descripcion_id'=>1,
        ]);

        Evaluaciones::create([
            'estado_fisico'=>'N/A',
            'escala'=>'N/A',
            'compatibilidad'=>'N/A',
            'reemplazo'=>'N/A',
            'mantenimineto'=>'N/A',
            'notas'=>'N/A',
            'producto_id'=>1,
            'estatus_id'=>1,
            'descripcion_id'=>1,
        ]);

        Inventarios::create([
            'cantidad_existente'=>0,
            'entrada'=>0,
            'salida'=>0,
            'descripcion' => 'N/A',
            'estatus_id'=>1,
        ]);

        Perifericos::create([
            'cantidad_existente'=>0,
            'entrada'=>0,
            'salida'=>0,
            'descripcion' => 'N/A',
            'estatus_id'=>1,
        ]);
    }
}

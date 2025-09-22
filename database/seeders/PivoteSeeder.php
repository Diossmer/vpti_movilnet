<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
/* use App\Models\Inventario\Inventarios_Productos;
use App\Models\Inventario\Asignar_productos;
use App\Models\Inventario\Perifericos_Productos;
use App\Models\Inventario\EvaluacionProductos;
use App\Models\Inventario\UbicacionProductos; */

use App\Models\Inventario\AsignarDescripcion;
use App\Models\Inventario\EvaluacionDescripcion;
use App\Models\Inventario\UbicacionDescripcion;
use App\Models\Inventario\InventarioDescripcion;
use App\Models\Inventario\PerifericoDescripcion;

class PivoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AsignarDescripcion::create([
            'asignar_id'=>1,
            'descripcion_id'=>1,
        ]);

        EvaluacionDescripcion::create([
            'evaluacion_id'=>1,
            'descripcion_id'=>1,
        ]);

        UbicacionDescripcion::create([
            'ubicacion_id'=>1,
            'descripcion_id'=>1,
        ]);

        InventarioDescripcion::create([
            'inventario_id'=>1,
            'descripcion_id'=>1,
        ]);

        PerifericoDescripcion::create([
            'periferico_id'=>1,
            'descripcion_id'=>1,
        ]);

        /* Asignar_productos::create([
            'asignar_id'=>1,
            'producto_id'=>1,
        ]);

        EvaluacionProductos::create([
            'evaluacion_id'=>1,
            'producto_id'=>1,
        ]);

        UbicacionProductos::create([
            'ubicacion_id'=>1,
            'producto_id'=>1,
        ]);

        Inventarios_Productos::create([
            'inventario_id'=>1,
            'producto_id'=>1,
        ]);

        Perifericos_Productos::create([
            'periferico_id'=>1,
            'producto_id'=>1,
        ]); */
    }
}

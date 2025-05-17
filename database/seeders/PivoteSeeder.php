<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario\Inventarios_Productos;
use App\Models\Inventario\Asignar_productos;
use App\Models\Inventario\Perifericos_Productos;

class PivoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Asignar_productos::create([
            'asignar_id'=>1,
            'producto_id'=>1,
        ]);

        Inventarios_Productos::create([
            'inventario_id'=>1,
            'producto_id'=>1,
        ]);

        Perifericos_Productos::create([
            'periferico_id'=>1,
            'producto_id'=>1,
        ]);
    }
}

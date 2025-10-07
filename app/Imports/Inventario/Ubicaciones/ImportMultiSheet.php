<?php

namespace App\Imports\Inventario\Ubicaciones;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $UbicacionesImport;

     public function __construct()
     {
        $this->UbicacionesImport = new \App\Imports\Inventario\Ubicaciones\UbicacionesImport();
     }

     public function sheets(): array
     {
         return [
             0 => $this->UbicacionesImport
         ];
     }
}

<?php

namespace App\Imports\Inventario\Productos;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $ProductosImport;

    public function __construct()
    {
       $this->ProductosImport = new \App\Imports\Inventario\Productos\ProductosImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->ProductosImport
        ];
    }
}

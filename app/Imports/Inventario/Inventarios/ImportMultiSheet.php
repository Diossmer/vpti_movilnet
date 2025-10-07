<?php

namespace App\Imports\Inventario\Inventarios;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $InventariosImport;

    public function __construct()
    {
       $this->InventariosImport = new \App\Imports\Inventario\Inventarios\InventariosImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->InventariosImport
        ];
    }
}

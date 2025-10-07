<?php

namespace App\Imports\Inventario\Descripciones;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $DescripcionesImport;

    public function __construct()
    {
       $this->DescripcionesImport = new \App\Imports\Inventario\Descripciones\DescripcionesImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->DescripcionesImport
        ];
    }
}

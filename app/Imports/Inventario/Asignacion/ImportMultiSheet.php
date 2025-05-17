<?php

namespace App\Imports\Inventario\Asignacion;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $AsignacionImport;

    public function __construct()
    {
       $this->AsignacionImport = new \App\Imports\Inventario\Asignacion\AsignacionImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->AsignacionImport
        ];
    }
}

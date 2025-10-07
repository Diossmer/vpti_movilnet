<?php

namespace App\Imports\Inventario\Evaluaciones;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $EvaluacionesImport;

    public function __construct()
    {
       $this->EvaluacionesImport = new \App\Imports\Inventario\Evaluaciones\EvaluacionesImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->EvaluacionesImport
        ];
    }
}

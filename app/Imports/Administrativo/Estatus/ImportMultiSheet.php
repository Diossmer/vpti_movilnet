<?php

namespace App\Imports\Administrativo\Estatus;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $EstatusImport;

    public function __construct()
    {
        $this->EstatusImport = new EstatusImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->EstatusImport
        ];
    }
}

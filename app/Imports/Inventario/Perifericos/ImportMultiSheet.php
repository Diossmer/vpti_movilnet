<?php

namespace App\Imports\Inventario\Perifericos;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $PerifericosImport;

     public function __construct()
     {
        $this->PerifericosImport = new \App\Imports\Inventario\Perifericos\PerifericosImport();
     }

     public function sheets(): array
     {
         return [
             0 => $this->PerifericosImport
         ];
     }
}

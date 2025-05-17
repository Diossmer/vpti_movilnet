<?php

namespace App\Imports\Administrativo\Usuarios;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $UsuariosImport;

    public function __construct()
    {
    $this->UsuariosImport = new \App\Imports\Administrativo\Usuarios\UsuariosImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->UsuariosImport
        ];
    }
}

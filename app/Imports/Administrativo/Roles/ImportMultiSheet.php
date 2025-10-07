<?php

namespace App\Imports\Administrativo\Roles;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportMultiSheet implements WithMultipleSheets
{
    public $RolesImport;

    public function __construct()
    {
    $this->RolesImport = new \App\Imports\Administrativo\Roles\RolesImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->RolesImport
        ];
    }
}

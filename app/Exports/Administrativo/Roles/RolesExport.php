<?php

namespace App\Exports\Administrativo\Roles;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class RolesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected $roles;

    public function __construct(?Collection $roles=null)
    {
        $this->roles = $roles ?? collect();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]]
        ];
    }

    public function collection()
    {
        return $this->roles;
    }

    public function headings(): array
    {
        return array_keys($this->roles->first()->toArray());
    }

    public function title(): string
    {
        return "Roles";
    }
}

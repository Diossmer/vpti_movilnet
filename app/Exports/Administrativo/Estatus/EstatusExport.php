<?php

namespace App\Exports\Administrativo\Estatus;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class EstatusExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected $estatus;

    public function __construct(?Collection $estatus=null)
    {
        $this->estatus = $estatus ?? collect();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]]
        ];
    }

    public function collection()
    {
        return $this->estatus;
    }

    public function headings(): array
    {
        return array_keys($this->estatus->first()->toArray());
    }

    public function title(): string
    {
        return "Estatus";
    }
}

<?php

namespace App\Exports\Inventario\Ubicaciones;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class UbicacionesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle,WithMapping
{
    protected $ubicaciones;

    public function __construct(?Collection $ubicaciones=null)
    {
        $this->ubicaciones = $ubicaciones ?? collect();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]]
        ];
    }

    public function map($request): array
    {
        return [
            'origen'=>$request->origen??"Sin data",
            'destino'=>$request->destino??"Sin data",
            'piso'=>$request->piso??"Sin data",
            'region'=>$request->region??"Sin data",
            'estado'=>$request->estado??"Sin data",
            'capital'=>$request->capital??"Sin data",
            'descripcion_id'=>$request->descripcion->modelo??"Sin data",
        ];
    }

    public function collection()
    {
        return $this->ubicaciones;
    }

    public function headings(): array
    {
        return array_keys($this->ubicaciones->first()->toArray());
    }

    public function title(): string
    {
        return "Ubicaciones";
    }
}

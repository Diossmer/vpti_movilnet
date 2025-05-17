<?php

namespace App\Exports\Inventario\Evaluaciones;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class EvaluacionesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $autorizados;

    public function __construct(?Collection $autorizados=null)
    {
        $this->autorizados = $autorizados ?? collect();
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
            'estado_fisico'=>$request->estado_fisico,
            'escala'=>$request->escala,
            'compatibilidad'=>$request->compatibilidad,
            'reemplazo'=>$request->reemplazo,
            'mantenimineto'=>$request->mantenimineto,
            'notas'=>$request->notas,
            'producto_id'=>$request->producto->nombre??"Sin data",
            'estatus_id'=>$request->estatus->nombre??"Sin data",
            'descripcion_id'=>$request->descripcion->modelo,
        ];
    }

    public function collection()
    {
        return $this->autorizados;
    }

    public function headings(): array
    {
        return array_keys($this->autorizados->first()->toArray());
    }

    public function title(): string
    {
        return "Autorizados";
    }
}

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
            'escala'=>$request->escala??"Sin data",
            'compatibilidad'=>$request->compatibilidad??"Sin data",
            'reemplazo'=>$request->reemplazo??"Sin data",
            'mantenimiento'=>$request->mantenimiento??"Sin data",
            'notas'=>$request->notas??"Sin data",
            'estatus_id'=>$request->estatus?->nombre??null,
            'descripcion_id'=>$request->descripcion?->modelo??null,
            'producto_id'=>$request->productos->map(function($producto){
                return $producto?->nombre;
            })->implode(',')??null,
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
        return "Evaluaciones";
    }
}

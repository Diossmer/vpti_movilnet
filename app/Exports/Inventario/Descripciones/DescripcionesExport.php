<?php

namespace App\Exports\Inventario\Descripciones;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class DescripcionesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $descripciones;

    public function __construct(?Collection $descripciones=null)
    {
        $this->descripciones = $descripciones ?? collect();
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
            'codigo'=>$request->codigo??"Sin data",
            'modelo'=>$request->modelo,
            'dispositivo'=>$request->dispositivo??"Sin data",
            'serial'=>$request->serial??"Sin data",
            'marca'=>$request->marca??"Sin data",
            'codigo_inv'=>$request->codigo_inv??"Sin data",
            'observacion'=>$request->observacion??"Sin data",
            'producto_id'=>$request->producto->nombre??"Sin data",
            'asignaciones'=>$request->asignaciones->map(function($asignacion){
                return $asignacion?->destino;
            })->implode(',') ?? "Sin data",
            'evaluaciones'=>$request->evaluaciones->map(function($evaluacion){
                return $evaluacion->estado_fisico;
            })->implode(',') ?? "Sin data",
        ];
    }

    public function collection()
    {
        return $this->descripciones;
    }

    public function headings(): array
    {
        return array_keys($this->descripciones->first()->toArray());
    }

    public function title(): string
    {
        return "Descripciones";
    }
}

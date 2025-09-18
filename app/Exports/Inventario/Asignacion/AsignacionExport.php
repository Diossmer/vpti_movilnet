<?php

namespace App\Exports\Inventario\Asignacion;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class AsignacionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $asignacion;

    public function __construct(?Collection $asignacion=null)
    {
        $this->asignacion = $asignacion ?? collect();
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
            'fecha_asignar'=>$request->fecha_asignar??null,
            'fecha_devolucion'=>$request->fecha_devolucion??null,
            'destino'=>$request->destino??"Sin data",
            'comentario'=>$request->comentario??"Sin data",
            'usuario_id'=>$request->usuario->usuario??null,
            'estatus_id'=>$request->estatus?->nombre??null,
            'descripcion_id'=>$request->descripcion->modelo??null,
            'productos'=>$request->productos?->map(function($producto){
                return $producto->nombre;
            })->implode(',')??null,
        ];
    }

    public function collection()
    {
        return $this->asignacion;
    }

    public function headings(): array
    {
        return array_keys($this->asignacion->first()->toArray());
    }

    public function title(): string
    {
        return "Asignacion";
    }
}

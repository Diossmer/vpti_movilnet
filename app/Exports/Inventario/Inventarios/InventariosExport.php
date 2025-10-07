<?php

namespace App\Exports\Inventario\Inventarios;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class InventariosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $inventarios;

    public function __construct(?Collection $inventarios=null)
    {
        $this->inventarios = $inventarios ?? collect();
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
            'cantidad_existente'=>$request->cantidad_existente?? 0,
            'entrada'=>$request->entrada?? 0,
            'salida'=>$request->salida?? 0,
            'observacion'=>$request->observacion??null,
            'estatus_id'=>$request->estatus->nombre?? null,
            /* 'productos'=>$request->productos->map(function($producto){
                return $producto?->nombre;
            })->implode(',') ?? null, */
            'descripcion_id'=>$request->descripciones->map(function($descripcion){
                return $descripcion?->serial. ' ' .$descripcion?->producto?->nombre;
            })->implode(',')??null,
        ];
    }

    public function collection()
    {
        return $this->inventarios;
    }

    public function headings(): array
    {
        return array_keys($this->inventarios->first()->toArray());
    }

    public function title(): string
    {
        return "Sin-Periféricos";
    }
}

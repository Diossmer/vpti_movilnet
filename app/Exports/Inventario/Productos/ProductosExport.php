<?php

namespace App\Exports\Inventario\Productos;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class ProductosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $productos;

    public function __construct(?Collection $productos=null)
    {
        $this->productos = $productos ?? collect();
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
            'nombre'=>$request->nombre??"Sin data",
            'descripcion_id'=>$request->descripciones->map(function($descripcion){
                return $descripcion?->marca;
            })->implode(',')??null,
            'usuario_id'=>$request->usuario->usuario??null,
            'estatus_id'=>$request->estatus->nombre??null,
        ];
    }

    public function collection()
    {
        return $this->productos;
    }

    public function headings(): array
    {
        return array_keys($this->productos->first()->toArray());
    }

    public function title(): string
    {
        return "Productos";
    }
}

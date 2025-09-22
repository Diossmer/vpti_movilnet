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
            'codigo'=>$request->codigo??null,
            'modelo'=>$request->modelo,
            'dispositivo'=>$request->dispositivo??null,
            'serial'=>$request->serial??null,
            'marca'=>$request->marca??null,
            'observacion'=>$request->observacion??null,
            'codigo_inv'=>$request->codigo_inv??null,
            'producto'=>$request->producto->nombre??null,
            'asignaciones'=>$request->asignaciones->map(function($asignacion){
                return $asignacion?->destino;
            })->implode(',') ?? null,
            'evaluaciones'=>$request->evaluaciones->map(function($evaluacion){
                return \App\Models\Estatus::where('id','=',$evaluacion->estatus_id)->first()?->nombre;
            })->implode(',') ?? null,
            'inventarios'=>$request->inventarios->map(function($inventario){
                return $inventario?->cantidad_existente;
            })->implode(',') ?? null,
            'perifericos'=>$request->perifericos->map(function($periferico){
                return $periferico?->cantidad_existente;
            })->implode(',') ?? null,
            'ubicaciones'=>$request->ubicaciones->map(function($ubicacion){
                return $ubicacion?->origen;
            })->implode(',') ?? null,
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

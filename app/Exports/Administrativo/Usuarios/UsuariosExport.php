<?php

namespace App\Exports\Administrativo\Usuarios;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class UsuariosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithMapping
{
    protected $usuarios;

    public function __construct(?Collection $usuarios=null)
    {
        $this->usuarios = $usuarios ?? collect();
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
            'nombre'=>$request->nombre??null,
            'apellido'=>$request->apellido??null,
            'cedula'=>$request->cedula??null,
            'usuario'=>$request->usuario??null,
            'correo'=>$request->correo??null,
            'direccion'=>$request->direccion??null,
            'ciudad'=>$request->ciudad??null,
            'estado'=>$request->estado??null,
            'telefono_casa'=>$request->telefono_casa??null,
            'telefono_celular'=>$request->telefono_celular??null,
            'telefono_alternativo'=>$request->telefono_alternativo??null,
            'codigo_postal'=>$request->codigo_postal??null,
            'estatus_id'=>$request->estatus->nombre??null,
            'rol_id'=>$request->rol->nombre??null,
            'productos'=>$request->productos->map(function($producto){
                return $producto?->nombre;
            })->implode(',') ?? null,
            'asignaciones'=>$request->asignaciones->map(function($asignacion){
                return $asignacion?->destino;
            })->implode(',') ?? null,
        ];
    }

    public function collection()
    {
        return $this->usuarios;
    }

    public function headings(): array
    {
        return array_keys($this->usuarios->first()->toArray());
    }

    public function title(): string
    {
        return "Usuarios";
    }
}

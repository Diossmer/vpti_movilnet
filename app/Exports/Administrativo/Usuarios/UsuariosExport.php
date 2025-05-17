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
            'nombre'=>$request->nombre??"Sin data",
            'apellido'=>$request->apellido??"Sin data",
            'cedula'=>$request->cedula??"Sin data",
            'usuario'=>$request->usuario??"Sin data",
            'correo'=>$request->correo??"Sin data",
            'direccion'=>$request->direccion??"Sin data",
            'ciudad'=>$request->ciudad??"Sin data",
            'estado'=>$request->estado??"Sin data",
            'telefono_casa'=>$request->telefono_casa??"Sin data",
            'telefono_celular'=>$request->telefono_celular??"Sin data",
            'telefono_alternativo'=>$request->telefono_alternativo??"Sin data",
            'codigo_postal'=>$request->codigo_postal??"Sin data",
            'estatus_id'=>$request->estatus->nombre??"Sin data",
            'rol_id'=>$request->rol->nombre??"Sin data",
            'productos'=>$request->productos->map(function($producto){
                return $producto?->nombre;
            })->implode(',') ?? "Sin data",
            'asignaciones'=>$request->asignaciones->map(function($asignacion){
                return $asignacion?->destino;
            })->implode(',') ?? "Sin data",
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

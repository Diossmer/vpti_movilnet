<?php

namespace App\Imports\Inventario\Asignacion;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AsignacionImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ShouldQueue
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row["productos"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: está vacío.");
                }
                $asignar = \App\Models\Inventario\Asignacion::updateOrCreate(
                    [
                        // Clave única compuesta (debe coincidir con uniqueBy)
                        'estatus_id'=> \App\Models\Estatus::where('nombre','=',Str::lower(trim($row['estatus'])))->first()?->id ?? null,
                        'descripcion_id'=>\App\Models\Inventario\Descripcion::where('modelo','=',Str::lower(trim($row['descripcion'])))->first()?->id ?? null,
                        'usuario_id'=> (Auth::id() === 1)?\App\Models\Usuarios::where('usuario',trim($row["usuario"]))->first()?->id:Auth::id(),
                    ],
                    [
                        // Campos actualizables
                        'fecha_asignar' => $row['fecha_asignar'] ?? null,
                        'fecha_devolucion' => $row['fecha_devolucion'] ?? null,
                        'destino' => Str::lower(trim($row['destino'])) ?? null,
                        'comentario' => Str::lower(trim($row['comentario'])) ?? null,
                    ]
                );
                $productoId = \App\Models\Inventario\Productos::whereIn('nombre',array_map('Str::lower', array_map('trim', explode(',', $row['productos']))))->get()->pluck('id')->toArray();
                $asignar->productos()->sync($productoId);
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $this->registrosFallidos++;
                    continue;
                }
                throw $e;
            } catch (\Exception $e) {
                Log::error("Error al procesar la fila: " . $e->getMessage());
                $this->registrosFallidos++;
                continue;
            }
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return ['producto_id'];
    }

    public function rules(): array
    {
        return [];
    }

    public function customValidationMessages()
    {
        return [];
    }

    public function getRegistrosCargados()
    {
        return $this->registrosCargados;
    }

    public function getRegistrosFallidos()
    {
        return $this->registrosFallidos;
    }

    public function getRegistrosPendientes()
    {
        return $this->registrosPendientes;
    }
}

<?php

namespace App\Imports\Inventario\Evaluaciones;

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

class EvaluacionesImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ShouldQueue
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row["producto"]||$row["descripcion"]||$row["estatus"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: producto está vacío.");
                }
                \App\Models\Inventario\Evaluaciones::updateOrCreate(
                    [
                        // Clave única compuesta (debe coincidir con uniqueBy)
                        'producto_id' => \App\Models\Inventario\Productos::where('nombre','=',Str::lower(trim($row['producto'])))->first()?->id ?? null,
                        'descripcion_id'=>\App\Models\Inventario\Descripcion::where('modelo','=',Str::lower(trim($row['descripcion'])))->first()?->id ?? null,
                        'estatus_id'=>\App\Models\Estatus::where('nombre','=',Str::lower(trim($row['estatus'])))->first()?->id ?? null,
                    ],
                    [
                        // Campos actualizables
                        'estado_fisico'=>Str::lower(trim($row['estado_fisico']))?? null,
                        'escala'=>Str::lower(trim($row['escala']))?? null,
                        'compatibilidad'=>Str::lower(trim($row['compatibilidad']))?? null,
                        'reemplazo'=>Str::lower(trim($row['reemplazo']))?? null,
                        'mantenimineto'=>Str::lower(trim($row['mantenimineto']))?? null,
                        'notas'=>Str::lower(trim($row['notas']))?? null,
                    ]
                );
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: {$row['producto']} {$row['descripcion']} {$row['estatus']}");
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
        return ['producto_id', 'descripcion_id','estatus_id','mantenimineto'];
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

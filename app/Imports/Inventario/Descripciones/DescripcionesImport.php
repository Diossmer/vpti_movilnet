<?php

namespace App\Imports\Inventario\Descripciones;

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
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DescripcionesImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row["producto"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: producto está vacío.");
                }
                \App\Models\Inventario\Descripcion::updateOrCreate(
                    [
                        'observacion' => trim($row["observacion"]),
                        'producto_id' => trim($row["producto"])
                    ],
                    [
                        'codigo' => Str::lower(trim($row['codigo'])) ?? null,
                        'modelo' => Str::lower(trim($row['modelo'])) ?? null,
                        'dispositivo' => Str::lower(trim($row['dispositivo'])) ?? null,
                        'serial' => Str::lower(trim($row['serial'])) ?? null,
                        'marca' => Str::lower(trim($row['marca'])) ?? null,
                        'codigo_inv' => Str::lower(trim($row['codigo_inv'])) ?? null,
                        'observacion' => Str::lower(trim($row['observacion'])) ?? null,
                        'producto_id' => \App\Models\Inventario\Productos::where('nombre','=',Str::lower(trim($row['producto'])))->first()?->id ?? null,
                    ]
                );
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: producto {$row['producto']}");
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
        return ['observacion'];
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

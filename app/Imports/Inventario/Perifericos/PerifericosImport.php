<?php

namespace App\Imports\Inventario\Perifericos;

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

class PerifericosImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row["cantidad_existente"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: cargos está vacío.");
                }
                $perifericos = \App\Models\Inventario\Perifericos::updateOrCreate(
                    [
                        'cantidad_existente' => $row["cantidad_existente"],
                        'entrada' => $row["entrada"] ?? 0,
                        'salida' => $row["salida"] ?? 0,
                    ],
                    [
                        'descripcion' => Str::lower(trim($row["descripcion"])) ?? null,
                        'estatus_id' => \App\Models\Estatus::where('nombre','=',$row['estatus'])->first()->id ?? null,
                    ]
                );
                $perifericoID = \App\Models\Inventario\Productos::whereIn('nombre',array_map('Str::lower', array_map('trim', explode(',', $row['productos']))))->get()->pluck('id')->toArray();
                $perifericos->productos()->sync($perifericoID);
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: cantidad_existente {$row['cantidad_existente']}");
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
        return [];
    }

    public function rules(): array
    {
        return [
            '*.cantidad_existente' => ['required','unique:perifericos,cantidad_existente'],
            '*.descripcion' => 'nullable|string|max:500',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.cantidad_existente.required' => 'El campo :attribute es obligatorio.',
            '*.descripcion.string' => 'El campo descripción debe ser string.',
        ];
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

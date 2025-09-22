<?php

namespace App\Imports\Inventario\Inventarios;

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

class InventariosImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row["cantidad_existente"]) || empty($row['descripciones'])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: cantidad_existente o descripciones están vacíos.");
                }
                $inventarios = \App\Models\Inventario\Inventarios::updateOrCreate(
                    [
                        'cantidad_existente' => $row["cantidad_existente"]?? 0,
                        'entrada' => $row["entrada"] ?? 0,
                        'salida' => $row["salida"] ?? 0,
                    ],
                    [
                        'observacion' => Str::lower(trim($row["observacion"])) ?? null,
                        'estatus_id' => \App\Models\Estatus::where('nombre','=',$row['estatus'])->first()->id ?? null,
                    ]
                );
                /* $productosID = \App\Models\Inventario\Productos::whereIn('nombre',array_map('Str::lower', array_map('trim', explode(',', $row['productos']))))->get()->pluck('id')->toArray();
                $inventarios->productos()->sync($productosID); */
                $descripcionID = [];
                $descripciones = array_map('trim', explode(',', $row['descripciones']));

                foreach ($descripciones as $descripcion_string) {
                    // Ejemplo de cómo manejar "n/a N/A" o strings con espacios
                    $parts = explode(' ', $descripcion_string);
                    $marca = Str::lower($parts[0]);
                    unset($parts[0]);
                    $producto_nombre = Str::lower(implode(' ', $parts));

                    // Busca la descripción usando la marca y el nombre del producto
                    $descripcion = \App\Models\Inventario\Descripcion::where('marca', $marca)
                        ->whereHas('producto', function($query) use ($producto_nombre) {
                            $query->where('nombre', $producto_nombre);
                        })
                        ->first();

                    if ($descripcion) {
                        $descripcionID[] = $descripcion->id;
                    }
                }
                // Ahora puedes usar el array de IDs
                $inventarios->descripciones()->sync($descripcionID);
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
            '*.cantidad_existente' => ['required','unique:inventarios,cantidad_existente'],
            '*.observacion' => 'nullable|string|max:500',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.cantidad_existente.required' => 'El campo :attribute es obligatorio.',
            '*.observacion.string' => 'El campo observacion debe ser string.',
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

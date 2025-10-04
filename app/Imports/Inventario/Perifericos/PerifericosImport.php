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
                // 1. Aseguramos que 'descripciones' se lee como una cadena (string) para evitar errores con explode()
                $rawDescriptions = $row['descripciones'] ?? ''; 

                if (empty($row["cantidad_existente"]) || empty($rawDescriptions)) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: cantidad_existente o descripciones están vacíos.");
                }
                
                $perifericos = \App\Models\Inventario\Perifericos::updateOrCreate(
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
                
                // 2. Procesar los seriales de la cadena $rawDescriptions
                $serials = array_filter(array_map(function ($serial) {
                    return Str::lower(trim($serial));
                }, explode(',', $rawDescriptions)));
                
                // 3. Buscar IDs de las descripciones (solo por serial)
                $descripcionID = \App\Models\Inventario\Descripcion::whereIn('serial', $serials)
                    ->pluck('id')
                    ->toArray();
                
                // 4. Sincronizar (He asumido que 'evaluacion' era un typo y debe ser $perifericos)
                $perifericos->descripciones()->sync($descripcionID);
                
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: cantidad_existente {$row['cantidad_existente']}", ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
                    $this->registrosFallidos++;
                    continue;
                }
                throw $e;
            } catch (\Exception $e) {
                Log::error("Error al procesar la fila: ", [$e->getMessage(), 'fecha_hora' => now()->toDateTimeString(), Auth::user()]);
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
            // He quitado el unique para 'cantidad_existente' de aquí ya que lo estás manejando en updateOrCreate
            '*.cantidad_existente' => 'required',
            '*.observacion' => 'nullable|string|max:500',
            // Asegúrate que la columna 'descripciones' existe
            '*.descripciones' => 'required|string', 
        ];
    }
    
    // ... (rest of the methods: customValidationMessages, getRegistrosCargados, etc. remain unchanged)

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

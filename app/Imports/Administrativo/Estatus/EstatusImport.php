<?php

namespace App\Imports\Administrativo\Estatus;

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

class EstatusImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Validar que los campos obligatorios no estén vacíos
                if (empty($row["nombre"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: estatus está vacío.");
                }

                // Busca un usuario con la misma cédula, usuario y correo, o crea uno nuevo
                \App\Models\Estatus::updateOrCreate(
                    [ // Campos únicos y condiciones de búsqueda (array asociativo)
                        'nombre' => trim($row["nombre"]),
                        'descripcion' => trim($row["descripcion"])
                    ],
                    [ // Datos a actualizar/crear
                        'nombre' => Str::lower(trim($row["nombre"])) ?? null,
                        'descripcion' => Str::lower(trim($row["descripcion"])) ?? null
                    ]
                );
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: Estatus {$row['nombre']}");
                    $this->registrosFallidos++;
                    continue;
                }
                throw $e; // Lanza otras excepciones
            } catch (\Exception $e) {
                // Maneja otros errores (por ejemplo, validaciones)
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
        return ['nombre'];
    }

    public function rules(): array
    {
        return [
            '*.nombre' => ['required','unique:estatus,nombre'],
            '*.descripcion' => 'nullable|string|max:500',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.estatus.required' => 'El campo :attribute es obligatorio.',
            '*.estatus.unique' => 'El :attribute ya está registrado en el sistema.',
            '*.descripcion.string' => 'El campo descripción del estatus debe ser string.',
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

<?php

namespace App\Imports\Inventario\Productos;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductosImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $registrosCargados = 0;
    protected $registrosFallidos = 0;
    protected $registrosPendientes = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                if (empty($row["nombre"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: productos está vacío.");
                }
                $producto = \App\Models\Inventario\Productos::updateOrCreate(
                    [
                        'nombre' => trim($row["nombre"])
                    ],
                    [
                        'usuario_id'=> (Auth::id() === 1)?\App\Models\Usuarios::where('usuario',trim($row["usuario"]))->first()?->id:Auth::id(),
                        'estatus_id'=>\App\Models\Estatus::where('nombre',trim($row["estatus"]))->first()?->id ?? null,
                    ]
                );
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: productos {$row['nombre']}");
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
        return ['nombre'];
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

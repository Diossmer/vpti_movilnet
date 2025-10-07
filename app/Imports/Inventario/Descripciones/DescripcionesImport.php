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
                \App\Models\Inventario\Productos::updateOrCreate(
                    [
                        'nombre' => trim($row["producto"])
                    ],
                    [
                        'usuario_id'=> (\Auth::user()->rol_id === 1)?\App\Models\Usuarios::where('usuario',trim($row["usuario"]))->first()?->id:\Auth::id(),
                    ]
                );
                $descripcion = \App\Models\Inventario\Descripcion::updateOrCreate(
                    [
                        'observacion' => trim($row["observacion"]),
                        'producto_id' => \App\Models\Inventario\Productos::where('nombre','=',Str::lower(trim($row['producto'])))->first()?->id ?? null,
                    ],
                    [
                        'codigo' => Str::lower(trim($row['codigo'])) ?? null,
                        'modelo' => Str::lower(trim($row['modelo'])) ?? null,
                        'dispositivo' => Str::lower(trim($row['dispositivo'])) ?? null,
                        'serial' => Str::lower(trim($row['serial'])) ?? null,
                        'marca' => Str::lower(trim($row['marca'])) ?? null,
                        'codigo_inv' => Str::upper(trim($row['codigo_inv'])) ?? null,
                        'observacion' => Str::lower(trim($row['observacion'])) ?? null,
                    ]
                );
                $asignacionesID = \App\Models\Inventario\Asignacion::whereIn('destino',array_map('Str::lower', array_map('trim', explode(',', $row['asignaciones']))))->get()->pluck('id')->toArray();
                $descripcion->asignaciones()->sync($asignacionesID);
                
                $evaluacionesID = \App\Models\Inventario\Evaluaciones::whereIn('escala',array_map('Str::lower', array_map('trim', explode(',', $row['evaluaciones']))))->get()->pluck('id')->toArray();
                $descripcion->evaluaciones()->sync($evaluacionesID);
                
                $perifericosID = \App\Models\Inventario\Perifericos::whereIn('observacion',array_map('Str::lower', array_map('trim', explode(',', $row['perifericos']))))->get()->pluck('id')->toArray();
                $descripcion->perifericos()->sync($perifericosID);
                
                $ubicacionesID = \App\Models\Inventario\Ubicacion::whereIn('origen',array_map('Str::lower', array_map('trim', explode(',', $row['ubicaciones']))))->get()->pluck('id')->toArray();
                $descripcion->ubicaciones()->sync($ubicacionesID);
                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: producto {$row['producto']}", ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
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

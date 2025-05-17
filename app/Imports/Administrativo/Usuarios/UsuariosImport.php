<?php

namespace App\Imports\Administrativo\Usuarios;

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

class UsuariosImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithSkipDuplicates, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
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
                if (empty($row["cedula"]) || empty($row["usuario"]) || empty($row["correo"])) {
                    $this->registrosPendientes++;
                    throw new \Exception("Fila inválida: cédula, usuario o correo están vacíos.");
                }

                // Busca un usuario con la misma cédula, usuario y correo, o crea uno nuevo
                \App\Models\Usuarios::updateOrCreate(
                    [
                        'cedula' => $row["cedula"],
                        'usuario' => $row["usuario"],
                        'correo' => $row["correo"],
                    ],
                    [
                        'usuario' => Str::lower(trim($row["usuario"])) ?? null,
                        'nombre' => Str::lower(trim($row["nombre"])) ?? null,
                        'apellido' => Str::lower(trim($row["apellido"])) ?? null,
                        'direccion' => Str::lower(trim($row["direccion"])) ?? null,
                        'ciudad' => Str::lower(trim($row["ciudad"])) ?? null,
                        'estado' => Str::lower(trim($row["estado"])) ?? null,
                        'codigo_postal' => $row["codigo_postal"] ?? null,
                        'telefono_casa' => $row["telefono_casa"] ?? null,
                        'telefono_celular' => $row["telefono_celular"] ?? null,
                        'telefono_alternativo' => $row["telefono_alternativo"] ?? null,
                        'password' => Hash::make($row["password"] ?? null),
                        'estatus_id' => \App\Models\Estatus::where('nombre','=',$row['estatus'])->first()->id ?? null,
                        'rol_id' => \App\Models\Roles::where('nombre','=',$row['rol'])->first()->id ?? null,
                    ]
                );

                $this->registrosCargados++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    Log::warning("Registro duplicado: Cédula {$row['cedula']}, Usuario {$row['usuario']}, Correo {$row['correo']}");
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
        return ['cedula', 'correo', 'usuario'];
    }

    public function rules(): array
    {
        return [
            '*.correo' => ['email','unique:usuarios,correo'],
            '*.usuario' => ['required','unique:usuarios,usuario'],
            '*.cedula' => ['required','unique:usuarios,cedula'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.correo.email' => 'El :attribute debe ser una dirección de correo válida.',
            '*.correo.unique' => 'El :attribute ya está registrado en el sistema.',
            '*.usuario.required' => 'El campo :attribute es obligatorio.',
            '*.usuario.unique' => 'El :attribute ya está registrado en el sistema.',
            '*.cedula.required' => 'El campo :attribute es obligatorio.',
            '*.cedula.unique' => 'La :attribute ya está registrada en el sistema.',
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

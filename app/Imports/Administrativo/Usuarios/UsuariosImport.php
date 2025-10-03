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
                $usuario = \App\Models\Usuarios::updateOrCreate(
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
                        'cargo' => Str::lower(trim($row["cargo"])) ?? null,
                        'codigo_postal' => $row["codigo_postal"] ?? null,
                        'telefono_casa' => $row["telefono_casa"] ?? null,
                        'telefono_celular' => $row["telefono_celular"] ?? null,
                        'telefono_alternativo' => $row["telefono_alternativo"] ?? null,
                        'password' => Hash::make($row["password"] ?? null),
                        'estatus_id' => \App\Models\Estatus::where('nombre','=',$row['estatus'])->first()->id ?? null,
                        'rol_id' => \App\Models\Roles::where('nombre','=',$row['rol'])->first()->id ?? null,
                    ]
                );                
                // Desvincula todos los productos y asignaciones del usuario
                \App\Models\Inventario\Productos::where('usuario_id', $usuario->id)->update(['usuario_id' => null]);
                \App\Models\Inventario\Asignacion::where('usuario_id', $usuario->id)->update(['usuario_id' => null]);

                // Vincula los productos al usuario de forma masiva
                // Aseguramos que $row['productos'] no sea null antes de usarlo
                $productos = array_map('Str::lower', array_map('trim', explode(',', $row['productos'] ?? '')));
                
                // Filtramos cualquier cadena vacía resultante de la manipulación
                $productos = array_filter($productos); 

                if (!empty($productos)) {
                    \App\Models\Inventario\Productos::whereIn('nombre', $productos)
                        ->update(['usuario_id' => $usuario->id]);
                }

                // Vincula las asignaciones al usuario de forma masiva
                // NOTA: $asignaciones AHORA DEBE CONTENER LOS SERIALES (si no, el nombre de la columna debe ser ajustado en el Excel).
                $seriales_a_vincular = array_map('Str::lower', array_map('trim', explode(',', $row['asignaciones'] ?? '')));

                // 1. Filtrar cualquier cadena vacía resultante
                $seriales_a_vincular = array_filter($seriales_a_vincular); 

                // 2. Ejecutar la vinculación usando whereHas
                if (!empty($seriales_a_vincular)) {
                    \App\Models\Inventario\Asignacion::whereHas('descripciones', function ($query) use ($seriales_a_vincular) {
                        // CORRECCIÓN: Usamos la relación 'descripciones' (BelongsToMany)
                        // y buscamos en la columna 'serial' (asumiendo que así se llama en la tabla 'descripcions')
                        $query->whereIn('serial', $seriales_a_vincular);
                    })
                    ->update(['usuario_id' => $usuario->id]);
                } else {
                    // Opcional: Log para ver cuándo se salta la actualización
                    Log::debug("Asignaciones saltadas: Valor de 'asignaciones' vacío para usuario ID: " . $usuario->id);
                }

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

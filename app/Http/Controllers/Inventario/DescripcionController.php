<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Descripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Descripciones\ExportMultiSheet;
use App\Imports\Inventario\Descripciones\ImportMultiSheet;
use PDF;

class DescripcionController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $descripcion = Descripcion::with('producto.usuario','asignaciones','evaluaciones','inventarios','perifericos','ubicaciones')->get();
                if($descripcion->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un descripcion.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un descripcion.'], 404);
                }
                return response()->json($descripcion, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'codigo' => 'nullable|string|max:255',
                    'modelo' => 'required|string|max:255',
                    'dispositivo' => 'nullable|string|max:255',
                    'serial' => 'required|string|max:255',
                    'marca' => 'required|string|max:255',
                    'codigo_inv' => 'nullable|string|max:255',
                    'observacion' => 'nullable|string|max:500',
                    'producto_id' => 'required|integer|exists:productos,id',
                ], [
                    'codigo.string' => 'La codigo debe ser una cadena de texto.',
                    'codigo.max' => 'La codigo no puede exceder 50 caracteres.',
                    'modelo.string' => 'El modelo debe ser una cadena de texto.',
                    'modelo.max' => 'El modelo no puede exceder 100 caracteres.',
                    'dispositivo.string' => 'El dispositivo debe ser una cadena de texto.',
                    'dispositivo.max' => 'El dispositivo no puede exceder 50 caracteres.',
                    'serial.string' => 'El serial debe ser una cadena de texto.',
                    'serial.max' => 'El serial no puede exceder 100 caracteres.',
                    'marca.string' => 'El marca debe ser una cadena de texto.',
                    'marca.max' => 'El marca no puede exceder 50 caracteres.',
                    'codigo_inv.string' => 'La codigo_inv debe ser una cadena de texto.',
                    'codigo_inv.max' => 'La codigo_inv no puede exceder 50 caracteres.',
                    'observacion.string' => 'La observación debe ser una cadena de texto.',
                    'observacion.max' => 'La observación no puede exceder 255 caracteres.',
                    'producto_id.required' => 'El campo producto es obligatorio.',
                    'producto_id.exists' => 'El producto seleccionado no es válido.',
                ]);

                $descripcion = Descripcion::create([
                    'codigo'=>$request->codigo,
                    'modelo'=>$request->modelo,
                    'dispositivo'=>$request->dispositivo,
                    'serial'=>$request->serial,
                    'marca'=>$request->marca,
                    'codigo_inv'=>$request->codigo_inv,
                    'observacion'=>$request->observacion,
                    'producto_id'=>$request->producto_id,
                ])->load(['producto','asignaciones','evaluaciones','inventarios','perifericos','ubicaciones']);
                if(is_null($descripcion)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un descripcion.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un descripcion.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$descripcion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Descripcion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function show(?string $id)
    {
        try {
            if(Auth::check()){
                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones','inventarios','perifericos','ubicaciones')->find($id);
                if(is_null($descripcion)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un descripcion.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un descripcion.'], 404);
                }
                return response()->json($descripcion, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function update(Request $request, ?string $id)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'codigo' => 'nullable|string|max:255',
                    'modelo' => 'required|string|max:255',
                    'dispositivo' => 'nullable|string|max:255',
                    'serial' => 'required|string|max:255',
                    'marca' => 'required|string|max:255',
                    'codigo_inv' => 'nullable|string|max:255',
                    'observacion' => 'nullable|string|max:500',
                    'producto_id' => 'required|integer|exists:productos,id',
                ], [
                    'codigo.string' => 'La codigo debe ser una cadena de texto.',
                    'codigo.max' => 'La codigo no puede exceder 50 caracteres.',
                    'modelo.string' => 'El modelo debe ser una cadena de texto.',
                    'modelo.max' => 'El modelo no puede exceder 100 caracteres.',
                    'dispositivo.string' => 'El dispositivo debe ser una cadena de texto.',
                    'dispositivo.max' => 'El dispositivo no puede exceder 50 caracteres.',
                    'serial.string' => 'El serial debe ser una cadena de texto.',
                    'serial.max' => 'El serial no puede exceder 100 caracteres.',
                    'marca.string' => 'El marca debe ser una cadena de texto.',
                    'marca.max' => 'El marca no puede exceder 50 caracteres.',
                    'codigo_inv.string' => 'La codigo_inv debe ser una cadena de texto.',
                    'codigo_inv.max' => 'La codigo_inv no puede exceder 50 caracteres.',
                    'observacion.string' => 'La observación debe ser una cadena de texto.',
                    'observacion.max' => 'La observación no puede exceder 255 caracteres.',
                    'producto_id.required' => 'El campo producto es obligatorio.',
                    'producto_id.exists' => 'El producto seleccionado no es válido.',
                ]);

                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones','inventarios','perifericos','ubicaciones')->find($id);
                if(is_null($descripcion)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un descripcion.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un descripcion.'], 404);
                }
                $descripcion->update([
                    'codigo'=>$request->codigo,
                    'modelo'=>$request->modelo,
                    'dispositivo'=>$request->dispositivo,
                    'serial'=>$request->serial,
                    'marca'=>$request->marca,
                    'codigo_inv'=>$request->codigo_inv,
                    'observacion'=>$request->observacion,
                    'producto_id'=>$request->producto_id,
                ]);
                Log::channel('usuario')->info('Se actualizó correctamente.'.$descripcion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Descripcion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function destroy(?string $id)
    {
        try {
            if(Auth::check()){
                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones','inventarios','perifericos','ubicaciones')->find($id);;
                if(is_null($descripcion)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado eliminar descripcion.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar descripcion.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$descripcion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $descripcion->destroy($id);

                return response()->json(['mensaje'=>'Se eliminó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(?string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Descripcion::with('producto.usuario','asignaciones.usuario','evaluaciones.estatus','perifericos.estatus','ubicaciones')->where('id','=',$id)->get()->makeHidden(['id']));
                if(!$data){
                    Log::channel('sistema')->debug('No se ha logrado exportar la descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado exportar la descripcion.", 404);
                }
                Log::channel('usuario')->info('Se exportó correctamente: ', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Descripcion::with('producto.usuario','asignaciones.usuario','evaluaciones.estatus','perifericos.estatus','ubicaciones')->get()->makeHidden(['id']));
            if(!$data){
                Log::channel('sistema')->debug('No se ha logrado exportar la descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("No se ha logrado exportar la descripcion.", 404);
            }
            Log::channel('usuario')->info('Se exportó correctamente: ', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return ($data)->download('*.xlsx');
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al exportar el archivo: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al exportar el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importar(Request $request){
        try {
            set_time_limit(600); // 300 segundos = 5 minutos
            Log::info('Archivos recibidos:', $request->hasFile('file') ? $request->file('file') : [$request->allFiles()]);
            $request->validate([
                'file' => 'required|array',
                'file.*' => 'required|file|mimes:xlsx,xls',
            ]);
            $MultiSheet = new ImportMultiSheet();
            foreach ($request->file('file') as $archivo) {
                Excel::import($MultiSheet, $archivo);
            }
            $descripcionCargados = $MultiSheet?->DescripcionesImport->getRegistrosCargados();
            $descripcionFallidos = $MultiSheet?->DescripcionesImport->getRegistrosFallidos();
            $descripcionPendientes = $MultiSheet?->DescripcionesImport->getRegistrosPendientes();
            Log::channel('usuario')->info('Se importó correctamente.', ['pendientes' => $descripcionPendientes,'fallidos' => $descripcionFallidos,'cargados' => $descripcionCargados,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
            'descripcion' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $descripcionCargados,
                'fallidos' => $descripcionFallidos,
                'pendientes' => $descripcionPendientes,
            ]], 200);
        } catch (\Exception $e) {
            Log::error('Error al importar el archivo: ' . $e->getMessage());
            Log::channel('errores')->error('Error al importar el archivo: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
                'estatus' => 'error',
                'error' => 'Error al importar el archivo: ' . $e->getMessage(),
            ], 500);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'field' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }
        }
    }

    public function principalproducto(Request $request)
    {
        try {
            // 1. Validar los datos de la petición.
            $request->validate([
                'codigo' => 'nullable|string|max:255',
                'modelo' => 'required|string|max:255',
                'dispositivo' => 'nullable|string|max:255',
                'serial' => 'required|string|max:255',
                'marca' => 'required|string|max:255',
                'codigo_inv' => 'nullable|string|max:255',
                'observacion' => 'nullable|string|max:500',
                'nombre' => 'required|string|max:255',
                'usuario_id' => 'exists:usuarios,id',
            ], [
                'codigo.string' => 'La codigo debe ser una cadena de texto.',
                'codigo.max' => 'La codigo no puede exceder 50 caracteres.',
                'modelo.string' => 'El modelo debe ser una cadena de texto.',
                'modelo.max' => 'El modelo no puede exceder 100 caracteres.',
                'dispositivo.string' => 'El dispositivo debe ser una cadena de texto.',
                'dispositivo.max' => 'El dispositivo no puede exceder 50 caracteres.',
                'serial.string' => 'El serial debe ser una cadena de texto.',
                'serial.max' => 'El serial no puede exceder 100 caracteres.',
                'marca.string' => 'El marca debe ser una cadena de texto.',
                'marca.max' => 'El marca no puede exceder 50 caracteres.',
                'codigo_inv.string' => 'La codigo_inv debe ser una cadena de texto.',
                'codigo_inv.max' => 'La codigo_inv no puede exceder 50 caracteres.',
                'observacion.string' => 'La observación debe ser una cadena de texto.',
                'observacion.max' => 'La observación no puede exceder 255 caracteres.',
                'nombre.required' => 'El campo nombre del producto está vacío.',
                'usuario_id.exists' => 'El usuarios seleccionado no es válido.',
            ]);
            // 2. Buscar o crear el Producto por su nombre.
            // Si el producto existe, se devuelve. Si no existe, se crea.
            $producto = \App\Models\Inventario\Productos::firstOrCreate(
                ['nombre' => $request->nombre], // Criterio de búsqueda: nombre
                [
                    // Atributos de creación si el producto NO existe
                    'usuario_id' => Auth::id(), // Usamos el ID del usuario autenticado
                    // No se incluye 'estatus_id'
                ] 
            );
            // 3. Crear la Descripción y asociarla con el ID del Producto.
            $descripcion = Descripcion::create([
                'codigo' => $request->codigo,
                'modelo' => $request->modelo,
                'dispositivo' => $request->dispositivo,
                'serial' => $request->serial,
                'marca' => $request->marca,
                'codigo_inv' => $request->codigo_inv,
                'observacion' => $request->observacion,
                'producto_id' => $producto->id, // Usa el ID del producto encontrado o creado
            ]);
            // 4. Cargar las relaciones necesarias para la respuesta.
            $descripcion->load(['producto','asignaciones','evaluaciones','inventarios','perifericos','ubicaciones']);
            if(is_null($descripcion)){
                Log::channel('sistema')->debug('No se ha logrado guardar un descripcion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("No se ha logrado guardar un descripcion.", 404);
                return response()->json(['error'=>'No se ha logrado guardar un descripcion.'], 404);
            }
            // 5. Devolver la respuesta de éxito.
            Log::channel('usuario')->info('Se almacenó correctamente.'.$producto->nombre.' serial'.$descripcion->serial,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['mensaje' => "Se almacenó correctamente el detalle para el producto: {$producto->nombre} serial: {$descripcion->serial}"], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Descripcion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Asignacion\ExportMultiSheet;
use App\Imports\Inventario\Asignacion\ImportMultiSheet;
use PDF;

class AsignacionController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $asignacion = Asignacion::with('estatus', 'usuario', 'descripciones.producto')->get();
                if($asignacion->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un asignacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un asignacion.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un asignacion.'], 404);
                }
                return response()->json($asignacion, 200);
            }else{
                Log::channel('errores')->error('No está asignacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta asignacion.", 401);
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
                    'fecha_asignar' => 'required|date',
                    'fecha_devolucion' => 'nullable|date',
                    'comentario' => 'nullable|string|max:500',
                    'destino' => 'required|string',
                    'usuario_id' => 'required|integer|exists:usuarios,id',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    //'producto_id' => 'required|array|exists:productos,id',
                    'descripcion_id'=>'required|distinct|array|exists:descripcion,id',
                ], [
                    'fecha_asignar.required' => 'La fecha de asignación es obligatoria',
                    'destino.string' => 'el destino tiene que ser un texto',
                    'comentario.max' => 'El comentario no debe exceder 500 caracteres',
                    'estatus_id.exists' => 'Estado no válido',
                    'descripcion_id.exists' => 'La descripcion especificado no existe',
                    'descripcion_id.array' => 'El campo descripcion_id debe ser un número entero.',
                    'descripcion_id.distinct' => 'La descripción ID está duplicada dentro del arreglo de entrada.',
                    'usuario_id.exists' => 'El usuario especificado no existe',
                    //'producto_id.exists' => 'El producto especificado no existe',
                    //'producto_id.required' => 'El campo producto_id es obligatorio.',
                    //'producto_id.array' => 'El campo producto_id debe ser un número entero.',
                ]);
                $asignaciones = Asignacion::with('descripciones')
                    ->whereHas('descripciones', function ($query) use ($request) {
                        $query->whereIn('descripcion_id', $request->descripcion_id);
                    })->get();
                if($asignaciones->isNotEmpty()){
                    $seriales = $asignaciones->flatMap(fn ($u) => $u->descripciones)->pluck('serial')->unique()->implode(', ');
                    Log::channel('sistema')->debug('No se ha logrado guardar por que está duplicado. ',['seriales_duplicados' => $seriales,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar. Serial duplicado: {$seriales}", 400);
                    return response()->json(['error' => "No se ha logrado guardar. Serial duplicado: {$seriales}"], 400); 
                }
                $asignacion = Asignacion::create([
                    'fecha_asignar'=>$request->fecha_asignar,
                    'fecha_devolucion'=>$request->fecha_devolucion,
                    'comentario'=>$request->comentario,
                    'destino'=>$request->destino,
                    'estatus_id'=>$request->estatus_id,
                    'usuario_id'=>(Auth::id()===1)?$request->usuario_id:Auth::id(),
                ])->load(['estatus', 'usuario', 'descripciones.producto']);
                /* if($request->filled('producto_id')){
                    $asignacion->productos()->sync($request->producto_id);
                } */
                if($request->filled('descripcion_id')){
                    $asignacion->descripciones()->sync($request->descripcion_id);
                }
                if(is_null($asignacion)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un asignacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un asignacion.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un asignacion.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$asignacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está Asignacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta asignacion.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de asignacion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $asignacion = Asignacion::with('estatus', 'usuario', 'descripciones.producto')->find($id);
                if(is_null($asignacion)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un asignacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un asignacion.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un asignacion.'], 404);
                }
                return response()->json($asignacion, 200);
            }else{
                Log::channel('errores')->error('No está asignacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta asignacion.", 401);
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
                    'fecha_asignar' => 'required|date',
                    'fecha_devolucion' => 'nullable|date',
                    'comentario' => 'nullable|string|max:500',
                    'destino' => 'required|string',
                    'usuario_id' => 'required|integer|exists:usuarios,id',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    //'producto_id' => 'required|array|exists:productos,id',
                    'descripcion_id'=>'required|distinct|array|exists:descripcion,id',
                ], [
                    'fecha_asignar.required' => 'La fecha de asignación es obligatoria',
                    'destino.string' => 'el destino tiene que ser un texto',
                    'comentario.max' => 'El comentario no debe exceder 500 caracteres',
                    'estatus_id.exists' => 'Estado no válido',
                    'descripcion_id.exists' => 'La descripcion especificado no existe',
                    'descripcion_id.array' => 'El campo descripcion_id debe ser un número entero.',
                    'descripcion_id.distinct' => 'La descripción ID está duplicada dentro del arreglo de entrada.',
                    'usuario_id.exists' => 'El usuario especificado no existe',
                    //'producto_id.exists' => 'El producto especificado no existe',
                    //'producto_id.required' => 'El campo producto_id es obligatorio.',
                    //'producto_id.array' => 'El campo producto_id debe ser un número entero.',
                ]);
                $asignacion = Asignacion::with('estatus', 'usuario', 'descripciones.producto')->find($id);
                if(is_null($asignacion)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un asignacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un asignacion.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un asignacion.'], 404);
                }
                $asignacion->update([
                    'fecha_asignar'=>$request->fecha_asignar,
                    'fecha_devolucion'=>$request->fecha_devolucion,
                    'comentario'=>$request->comentario,
                    'destino'=>$request->destino,
                    'estatus_id'=>$request->estatus_id,
                    'usuario_id'=>(Auth::id()===1)?$request->usuario_id:Auth::id(),
                ]);
                /* if($request->filled('producto_id')){
                    $asignacion->productos()->sync($request->producto_id);
                } */
                if($request->filled('descripcion_id')){
                    $asignacion->descripciones()->sync($request->descripcion_id);
                }
                Log::channel('usuario')->info('Se actualizó correctamente.'.$asignacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está asignacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta asignacion.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Asignacion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $asignacion = Asignacion::with('estatus', 'usuario', 'descripciones.producto')->find($id);
                if(is_null($asignacion)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar asignacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado eliminar asignacion.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar asignacion.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$asignacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $asignacion->destroy($id);

                return response()->json(['mensaje'=>'Se eliminó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está asignacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta asignacion.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(?string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Asignacion::with('estatus', 'usuario', 'descripciones.producto')->where('id','=',$id)->get()->makeHidden(['id']));
                if(!$data){
                    Log::channel('sistema')->debug('No se ha logrado exportar una asignación. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado exportar una asignación.", 404);
                }
                Log::channel('usuario')->info('Se exportó correctamente: ', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Asignacion::with('estatus', 'usuario', 'descripciones.producto')->get()->makeHidden(['id']));
            if(!$data){
                Log::channel('sistema')->debug('No se ha logrado exportar una asignación. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("No se ha logrado exportar una asignación.", 404);
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
            $asignacionCargados = $MultiSheet?->AsignacionImport->getRegistrosCargados();
            $asignacionFallidos = $MultiSheet?->AsignacionImport->getRegistrosFallidos();
            $asignacionPendientes = $MultiSheet?->AsignacionImport->getRegistrosPendientes();
            Log::channel('usuario')->info('Se importó correctamente.', ['pendientes' => $asignacionPendientes,'fallidos' => $asignacionFallidos,'cargados' => $asignacionCargados,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
            'asignacion' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $asignacionCargados,
                'fallidos' => $asignacionFallidos,
                'pendientes' => $asignacionPendientes,
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

    public function generatepdf(?string $id = null, ?string $docs = null)
    {
        try {
            // Preparar los datos para la vista.
            $data = [
                'title' => Auth::user()?->rol->nombre ?? '',
                'subtitle' => $docs ?? null,
                'date' => date('d/m/Y'),
                'asignaciones' => Asignacion::with('estatus', 'usuario', 'descripciones.producto')->get(),
                'usuario' => \App\Models\Usuarios::find($id)?? '',
                //'usuario' => \App\Models\Usuarios::find(Auth::id())?? '',
            ];
            Log::channel('sistema')->debug('Generando PDF para asignación.', ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
            // Generar y mostrar el PDF.
            $pdf = Pdf::loadView('pdf.asignaciones', $data);
            Log::channel('usuario')->info('Se generó correctamente el PDF.', ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
            return $pdf->stream("reporte_asignacion.pdf");

        } catch (\Exception $e) {
            // Registrar el error para depuración.
            Log::error("Error al generar PDF: " . $e->getMessage());
            Log::channel('errores')->error('Error al generar el PDF: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            // Retornar una respuesta de error.
            return response()->json(['error' => 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.'], 500);
        }
    }
}

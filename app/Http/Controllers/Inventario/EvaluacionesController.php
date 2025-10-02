<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Evaluaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Evaluaciones\ExportMultiSheet;
use App\Imports\Inventario\Evaluaciones\ImportMultiSheet;
use PDF;

class EvaluacionesController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $evaluacion = Evaluaciones::with('estatus','descripciones.producto')->get();
                if($evaluacion->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un evaluacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un evaluacion.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un evaluacion.'], 404);
                }
                return response()->json($evaluacion, 200);
            }else{
                Log::channel('errores')->error('No está evaluacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta evaluacion.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                $validated = $request->validate([
                    //'estado_fisico' => 'required|string|max:255',
                    'escala' => 'nullable|string|max:255',
                    'compatibilidad' => 'nullable|string',
                    'reemplazo' => 'nullable|string',
                    'mantenimiento' => 'nullable|string|max:500',
                    'notas' => 'nullable|string|max:1000',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    'descripcion_id'=>'required|array|distinct|exists:descripcion,id',
                    //'producto_id' => 'required|array|exists:productos,id',
                ],
                [
                    //'producto_id.exists' => 'El producto especificado no existe',
                    //'producto_id.required' => 'El campo producto_id es obligatorio.',
                    //'producto_id.array' => 'El campo producto_id debe ser un arreglo.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido',
                    'descripcion_id.exists' => 'La descripción seleccionada no existe',
                    'descripcion_id.array' => 'El campo descripcion_id debe ser un número entero.',
                    'descripcion_id.distinct' => 'La descripción ID está duplicada dentro del arreglo de entrada.',
                ]);
                $evaluaciones = Evaluaciones::with('descripciones')
                    ->whereHas('descripciones', function ($query) use ($request) {
                        $query->whereIn('descripcion_id', $request->descripcion_id);
                    })->get();
                if($evaluaciones->isNotEmpty()){
                    $seriales = $evaluaciones->flatMap(fn ($u) => $u->descripciones)->pluck('serial')->unique()->implode(', ');
                    Log::channel('sistema')->debug('No se ha logrado guardar por que está duplicado. ',['seriales_duplicados' => $seriales,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar. Serial duplicado: {$seriales}", 400);
                    return response()->json(['error' => "No se ha logrado guardar. Serial duplicado: {$seriales}"], 400); 
                }
                $evaluacion = Evaluaciones::create([
                    'estado_fisico'=>$request->estado_fisico,
                    'escala'=>$request->escala,
                    'compatibilidad'=>$request->compatibilidad,
                    'reemplazo'=>$request->reemplazo,
                    'mantenimiento'=>$request->mantenimiento,
                    'notas'=>$request->notas,
                    'estatus_id'=>$request->estatus_id,
                ])->load(['estatus','descripciones.producto']);
                /* if($request->filled('producto_id')){
                    $evaluacion->productos()->sync($request->producto_id);
                } */
                if($request->filled('descripcion_id')){
                    $evaluacion->descripciones()->sync($request->descripcion_id);
                }
                if(is_null($evaluacion)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un evaluacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un evaluacion.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un evaluacion.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$evaluacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está evaluacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta evaluacion.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de evaluacion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function show(?string $id)
    {
        try {
            if(Auth::check()){
                $evaluacion = Evaluaciones::with('estatus','descripciones.producto')->find($id);
                if(is_null($evaluacion)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un evaluacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un evaluacion.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un evaluacion.'], 404);
                }
                return response()->json($evaluacion, 200);
            }else{
                Log::channel('errores')->error('No está evaluacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta evaluacion.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function update(Request $request, ?string $id)
    {
        try {
            if(Auth::check()){
                $validated = $request->validate([
                    //'estado_fisico' => 'required|string|max:255',
                    'escala' => 'nullable|string|max:255',
                    'compatibilidad' => 'nullable|string',
                    'reemplazo' => 'nullable|string',
                    'mantenimiento' => 'nullable|string|max:500',
                    'notas' => 'nullable|string|max:1000',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    'descripcion_id'=>'required|array|distinct|exists:descripcion,id',
                    //'producto_id' => 'required|array|exists:productos,id',
                ],
                [
                    //'producto_id.exists' => 'El producto especificado no existe',
                    //'producto_id.required' => 'El campo producto_id es obligatorio.',
                    //'producto_id.array' => 'El campo producto_id debe ser un arreglo.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido',
                    'descripcion_id.exists' => 'La descripción seleccionada no existe',
                    'descripcion_id.array' => 'El campo descripcion_id debe ser un número entero.',
                    'descripcion_id.distinct' => 'La descripción ID está duplicada dentro del arreglo de entrada.',
                ]);
                $evaluacion = Evaluaciones::with('estatus','descripciones.producto')->find($id);
                if(is_null($evaluacion)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un evaluacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un evaluacion.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un evaluacion.'], 404);
                }
                $evaluacion->update([
                    //'estado_fisico'=>$request->estado_fisico,
                    'escala'=>$request->escala,
                    'compatibilidad'=>$request->compatibilidad,
                    'reemplazo'=>$request->reemplazo,
                    'mantenimiento'=>$request->mantenimiento,
                    'notas'=>$request->notas,
                    'estatus_id'=>$request->estatus_id,
                ]);
                /* if($request->filled('producto_id')){
                    $evaluacion->productos()->sync($request->producto_id);
                } */
                if($request->filled('descripcion_id')){
                    $evaluacion->descripciones()->sync($request->descripcion_id);
                }
                Log::channel('usuario')->info('Se actualizó correctamente.'.$evaluacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está evaluacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta evaluacion.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de evaluacion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function destroy(?string $id)
    {
        try {
            if(Auth::check()){
                $evaluacion = Evaluaciones::with('estatus','descripciones.producto')->find($id);
                if(is_null($evaluacion)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar evaluacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado eliminar evaluacion.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar evaluacion.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$evaluacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $evaluacion->destroy($id);

                return response()->json(['mensaje'=>'Se eliminó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está evaluacion.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta evaluacion.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(?string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Evaluaciones::with('estatus','descripciones.producto')->where('id','=',$id)->get()->makeHidden(['id']));
                if(!$data){
                    Log::channel('sistema')->debug('No se ha logrado exportar una evaluación. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado exportar una evaluación.", 404);
                }
                Log::channel('usuario')->info('Se exportó correctamente: ', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Evaluaciones::with('estatus','descripciones.producto')->get()->makeHidden(['id']));
            if(!$data){
                Log::channel('sistema')->debug('No se ha logrado exportar una evaluación. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("No se ha logrado exportar una evaluación.", 404);
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
            $evaluacionCargados = $MultiSheet?->EvaluacionesImport->getRegistrosCargados();
            $evaluacionFallidos = $MultiSheet?->EvaluacionesImport->getRegistrosFallidos();
            $evaluacionPendientes = $MultiSheet?->EvaluacionesImport->getRegistrosPendientes();
            Log::channel('usuario')->info('Se importó correctamente.', ['pendientes' => $evaluacionPendientes,'fallidos' => $evaluacionFallidos,'cargados' => $evaluacionCargados,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
            'evaluacion' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $evaluacionCargados,
                'fallidos' => $evaluacionFallidos,
                'pendientes' => $evaluacionPendientes,
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
                'evaluaciones' => Evaluaciones::with('estatus','descripciones.producto')->get(),
                'usuario' => \App\Models\Usuarios::find($id)?? '',
                //'usuario' => \App\Models\Usuarios::find(Auth::id())?? '',
            ];
            Log::channel('sistema')->debug('Generando PDF para evaluación.', ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
            // Generar y mostrar el PDF.
            $pdf = Pdf::loadView('pdf.evaluaciones', $data);
            Log::channel('usuario')->info('Se generó correctamente el PDF.', ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
            return $pdf->stream("reporte_evaluacion.pdf");

        } catch (\Exception $e) {
            // Registrar el error para depuración.
            Log::error("Error al generar PDF: " . $e->getMessage());
            Log::channel('errores')->error('Error al generar el PDF: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            // Retornar una respuesta de error.
            return response()->json(['error' => 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.'], 500);
        }
    }
}

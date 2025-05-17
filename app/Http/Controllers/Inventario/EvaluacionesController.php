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
                $evaluacion = Evaluaciones::with('producto','estatus','descripcion')->get();
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
                    'estado_fisico' => 'required|string|max:255',
                    'escala' => 'required|string|max:255',
                    'compatibilidad' => 'required|string',
                    'reemplazo' => 'required|string',
                    'mantenimineto' => 'required|string|max:500',
                    'notas' => 'nullable|string|max:1000',
                    'producto_id' => 'required|integer|exists:productos,id',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    'descripcion_id' => 'required|integer|exists:descripcion,id',
                ],
                [
                    'mantenimineto.required' => 'El campo mantenimiento es obligatorio',
                    'producto_id.exists' => 'El producto seleccionado no existe',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido',
                    'descripcion_id.exists' => 'La descripción seleccionada no existe'
                ]);

                $evaluacion = Evaluaciones::create([
                    'estado_fisico'=>$request->estado_fisico,
                    'escala'=>$request->escala,
                    'compatibilidad'=>$request->compatibilidad,
                    'reemplazo'=>$request->reemplazo,
                    'mantenimineto'=>$request->mantenimineto,
                    'notas'=>$request->notas,
                    'producto_id'=>$request->producto_id,
                    'estatus_id'=>$request->estatus_id,
                    'descripcion_id'=>$request->descripcion_id,
                ])->load(['producto','estatus','descripcion']);
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

    public function show(string $id)
    {
        try {
            if(Auth::check()){
                $evaluacion = Evaluaciones::with('producto','estatus','descripcion')->find($id);
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

    public function update(Request $request, string $id)
    {
        try {
            if(Auth::check()){
                $validated = $request->validate([
                    'estado_fisico' => 'required|string|max:255',
                    'escala' => 'required|string|max:255',
                    'compatibilidad' => 'required|string',
                    'reemplazo' => 'required|string',
                    'mantenimineto' => 'required|string|max:500',
                    'notas' => 'nullable|string|max:1000',
                    'producto_id' => 'required|integer|exists:productos,id',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    'descripcion_id' => 'required|integer|exists:descripcion,id',
                ],
                [
                    'mantenimineto.required' => 'El campo mantenimiento es obligatorio', // Sugerir corrección de typo en mensaje
                    'producto_id.exists' => 'El producto seleccionado no existe',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido',
                    'descripcion_id.exists' => 'La descripción seleccionada no existe'
                ]);

                $evaluacion = Evaluaciones::with('producto','estatus','descripcion')->find($id);
                if(is_null($evaluacion)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un evaluacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un evaluacion.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un evaluacion.'], 404);
                }

                $evaluacion->update([
                    'estado_fisico'=>$request->estado_fisico,
                    'escala'=>$request->escala,
                    'compatibilidad'=>$request->compatibilidad,
                    'reemplazo'=>$request->reemplazo,
                    'mantenimineto'=>$request->mantenimineto,
                    'notas'=>$request->notas,
                    'producto_id'=>$request->producto_id,
                    'estatus_id'=>$request->estatus_id,
                    'descripcion_id'=>$request->descripcion_id,
                ]);
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

    public function destroy(string $id)
    {
        try {
            if(Auth::check()){
                $evaluacion = Evaluaciones::with('producto','estatus','descripcion')->find($id);
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

    public function exportar(string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Evaluaciones::with('producto','estatus','descripcion')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Evaluaciones::with('producto','estatus','descripcion')->get()->makeHidden(['id']));
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

    public function generatepdf(string $id=null, string $docs=null){
        // Obtén los datos necesarios para generar el PDF
        $evaluacion = Evaluaciones::with('producto', 'usuario', 'estatus', 'cargo')->where('id','=',$id)?->first()?? null;
        $authenticado = Auth::user();
        // Genera el PDF
        $pdf = PDF::loadView('pdf.autorizacion',compact('evaluacion','authenticado'));
        // Descarga el PDF
        return $pdf->stream("{$docs}.pdf");
        //return $pdf->download("{$docs}.pdf");
    }
}

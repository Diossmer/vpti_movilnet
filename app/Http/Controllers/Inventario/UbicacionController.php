<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Ubicaciones\ExportMultiSheet;
use App\Imports\Inventario\Ubicaciones\ImportMultiSheet;
use PDF;


class UbicacionController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $ubicacion = Ubicacion::with('descripcion','productos')->get();
                if($ubicacion->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un ubicacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un ubicacion.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un ubicacion.'], 404);
                }
                return response()->json($ubicacion, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
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
                    'origen' => 'nullable|string|max:255|different:destino',
                    'destino' => 'required|string|max:255',
                    'piso' => 'nullable|string',
                    'region' => 'nullable|string|max:255',
                    'capital' => 'nullable|string|max:255',
                    'descripcion_id' => 'required|integer|exists:descripcion,id',
                    'producto_id' => 'required|array|exists:productos,id',
                ], [
                    'origen.different' => 'Origen y destino deben ser diferentes',
                    'region' => 'Región no válida. Opciones: Norte, Sur, Este, Oeste, Centro',
                    'producto_id.exists' => 'El producto especificado no existe',
                    'producto_id.required' => 'El campo producto_id es obligatorio.',
                    'producto_id.array' => 'El campo producto_id debe ser un arreglo.',
                ]);

                $ubicacion = Ubicacion::create([
                    'origen'=>$request->origen,
                    'destino'=>$request->destino,
                    'piso'=>$request->piso,
                    'region'=>$request->region,
                    'estado'=>$request->estado,
                    'capital'=>$request->capital,
                    'descripcion_id'=>$request->descripcion_id,
                ])->load(['descripcion','productos']);
                if($request->filled('producto_id')){
                    $ubicacion->productos()->sync($request->producto_id);
                }
                if(is_null($ubicacion)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un ubicacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un ubicacion.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un ubicacion.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$ubicacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Ubicacion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $ubicacion = Ubicacion::with('descripcion','productos')->find($id);
                if(is_null($ubicacion)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un ubicacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un ubicacion.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un ubicacion.'], 404);
                }
                return response()->json($ubicacion, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
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
                    'origen' => 'nullable|string|max:255|different:destino',
                    'destino' => 'required|string|max:255',
                    'piso' => 'nullable|string',
                    'region' => 'nullable|string|max:255',
                    'capital' => 'nullable|string|max:255',
                    'descripcion_id' => 'required|integer|exists:descripcion,id',
                    'producto_id' => 'required|array|exists:productos,id',
                ], [
                    'origen.different' => 'Origen y destino deben ser diferentes',
                    'region' => 'Región no válida. Opciones: Norte, Sur, Este, Oeste, Centro',
                    'producto_id.exists' => 'El producto especificado no existe',
                    'producto_id.required' => 'El campo producto_id es obligatorio.',
                    'producto_id.array' => 'El campo producto_id debe ser un arreglo.',
                ]);

                $ubicacion = Ubicacion::with('descripcion','productos')->find($id);
                if(is_null($ubicacion)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un ubicacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un ubicacion.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un ubicacion.'], 404);
                }
                $ubicacion->update([
                    'origen'=>$request->origen,
                    'destino'=>$request->destino,
                    'piso'=>$request->piso,
                    'region'=>$request->region,
                    'estado'=>$request->estado,
                    'capital'=>$request->capital,
                    'descripcion_id'=>$request->descripcion_id,
                ]);
                if($request->filled('producto_id')){
                    $ubicacion->productos()->sync($request->producto_id);
                }
                Log::channel('usuario')->info('Se actualizó correctamente.'.$ubicacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Ubicacion: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $ubicacion = Ubicacion::with('descripcion','productos')->find($id);
                if(is_null($ubicacion)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar ubicacion. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado eliminar ubicacion.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar ubicacion.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$ubicacion,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $ubicacion->destroy($id);

                return response()->json(['mensaje'=>'Se eliminó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(?string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Ubicacion::with('descripcion','productos')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Ubicacion::with('descripcion','productos')->get()->makeHidden(['id']));
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
            $ubicacionCargados = $MultiSheet?->UbicacionesImport->getRegistrosCargados();
            $ubicacionFallidos = $MultiSheet?->UbicacionesImport->getRegistrosFallidos();
            $ubicacionPendientes = $MultiSheet?->UbicacionesImport->getRegistrosPendientes();
            return response()->json([
            'ubicacion' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $ubicacionCargados,
                'fallidos' => $ubicacionFallidos,
                'pendientes' => $ubicacionPendientes,
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

    /**
     * Genera un reporte PDF de inventario.
     *
     * @param string|null $id
     * @param string|null $docs
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function generatepdf(?string $id = null, ?string $docs = null)
    {
        try {
            // Preparar los datos para la vista.
            $data = [
                'title' => Auth::user()?->rol->nombre ?? '',
                'subtitle' => $docs ?? null,
                'date' => date('d/m/Y'),
                'ubicaciones' => Ubicacion::with('descripcion','productos')->get(),
                'usuario' => \App\Models\Usuarios::find($id)?? '',
                //'usuario' => \App\Models\Usuarios::find(Auth::id())?? '',
            ];

            // Generar y mostrar el PDF.
            $pdf = Pdf::loadView('pdf.ubicaciones', $data);
            return $pdf->stream("reporte_inventario.pdf");

        } catch (\Exception $e) {
            // Registrar el error para depuración.
            Log::error("Error al generar PDF: " . $e->getMessage());
            
            // Retornar una respuesta de error.
            return response()->json(['error' => 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.'], 500);
        }
    }
}

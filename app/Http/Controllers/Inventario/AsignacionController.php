<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Asignacion;
use App\Models\Inventario\Autorizado;
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
                $asignacion = Asignacion::with('productos', 'estatus', 'usuario', 'descripcion')->get();
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'fecha_asignar' => 'required|date|after_or_equal:today',
                    'fecha_devolucion' => 'nullable|date',
                    'comentario' => 'nullable|string|max:500',
                    'destino' => 'nullable|string',
                    'usuario_id' => 'required|integer|exists:usuarios,id',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    'producto_id' => 'required|array|exists:productos,id',
                    'descripcion_id'=>'required|exists:descripcion,id',
                ], [
                    'fecha_asignar.required' => 'La fecha de asignación es obligatoria',
                    'fecha_asignar.after_or_equal' => 'No puedes asignar en fechas pasadas',
                    'fecha_devolucion.after_or_equal' => 'La devolución debe ser posterior a la asignación',
                    'destino.string' => 'el destino tiene que ser un texto',
                    'comentario.max' => 'El comentario no debe exceder 500 caracteres',
                    'estatus_id.exists' => 'Estado no válido',
                    'descripcion_id.exists' => 'La descripcion especificado no existe',
                    'usuario_id.exists' => 'El usuario especificado no existe',
                    'producto_id.exists' => 'El producto especificado no existe',
                    'producto_id.required' => 'El campo producto_id es obligatorio.',
                    'producto_id.array' => 'El campo producto_id debe ser un número entero.',
                ]);
                $asignacion = Asignacion::create([
                    'fecha_asignar'=>$request->fecha_asignar,
                    'fecha_devolucion'=>$request->fecha_devolucion,
                    'comentario'=>$request->comentario,
                    'destino'=>$request->destino,
                    'estatus_id'=>$request->estatus_id,
                    'usuario_id'=>(Auth::id()===1)?$request->usuario_id:Auth::id(),
                    'descripcion_id'=>$request->descripcion_id,
                ])->load(['productos', 'estatus', 'usuario', 'descripcion']);
                if($request->filled('producto_id')){
                    $asignacion->productos()->sync($request->producto_id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            if(Auth::check()){
                $asignacion = Asignacion::with('productos', 'estatus', 'usuario', 'descripcion')->find($id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            if(Auth::check()){
                
                $request->validate([
                    'fecha_asignar' => 'required|date|after_or_equal:today',
                    'fecha_devolucion' => 'nullable|date|after_or_equal:fecha_asignar',
                    'comentario' => 'nullable|string|max:500',
                    'destino' => 'nullable|string',
                    'usuario_id' => 'required|integer|exists:usuarios,id',
                    'estatus_id' => 'required|integer|exists:estatus,id',
                    'producto_id' => 'required|array|exists:productos,id',
                    'descripcion_id'=>'required|exists:descripcion,id',
                ], [
                    'fecha_asignar.required' => 'La fecha de asignación es obligatoria',
                    'fecha_asignar.after_or_equal' => 'No puedes asignar en fechas pasadas',
                    'fecha_devolucion.after_or_equal' => 'La devolución debe ser posterior a la asignación',
                    'destino.string' => 'el destino tiene que ser un texto',
                    'comentario.max' => 'El comentario no debe exceder 500 caracteres',
                    'estatus_id.exists' => 'Estado no válido',
                    'descripcion_id.exists' => 'La descripcion especificado no existe',
                    'usuario_id.exists' => 'El usuario especificado no existe',
                    'producto_id.exists' => 'El producto especificado no existe',
                    'producto_id.required' => 'El campo producto_id es obligatorio.',
                    'producto_id.array' => 'El campo producto_id debe ser un número entero.',
                ]);

                $asignacion = Asignacion::with('productos', 'estatus', 'usuario', 'descripcion')->find($id);
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
                    'descripcion_id'=>$request->descripcion_id,
                ]);
                if($request->filled('producto_id')){
                    $asignacion->productos()->sync($request->producto_id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if(Auth::check()){
                $asignacion = Asignacion::with('productos', 'estatus', 'usuario', 'descripcion')->find($id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Asignacion::with('estatus','productos', 'usuario')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Asignacion::with('estatus','productos', 'usuario')->get()->makeHidden(['id']));
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
        $asistencias = \App\Models\Asistencias::with('usuario','estatus')->get();
        $data = [
            'title' => 'Reporte de Ventas',
            'date' => date('d/m/Y'),
            'asistencias' => $asistencias,
        ];

        // Genera el PDF
        $pdf = PDF::loadView('pdf.reporte', $data);

        // Descarga el PDF
        return $pdf->download("{$id}.pdf");

        // Muestra el PDF en el navegador
        // return $pdf->stream('reporte_ventas.pdf');
    }
}

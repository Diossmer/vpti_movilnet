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
                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones')->get();
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'codigo' => 'required|string|max:255',
                    'modelo' => 'required|string|max:255',
                    'dispositivo' => 'required|string|max:255',
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
                    'nucleo.max' => 'El núcleo no puede exceder 255 caracteres.',
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
                ])->load(['producto','asignaciones','evaluaciones']);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            if(Auth::check()){
                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones')->find($id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'codigo' => 'required|string|max:255',
                    'modelo' => 'required|string|max:255',
                    'dispositivo' => 'required|string|max:255',
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
                    'nucleo.max' => 'El núcleo no puede exceder 255 caracteres.',
                    'observacion.string' => 'La observación debe ser una cadena de texto.',
                    'observacion.max' => 'La observación no puede exceder 255 caracteres.',
                    'producto_id.required' => 'El campo producto es obligatorio.',
                    'producto_id.exists' => 'El producto seleccionado no es válido.',
                ]);

                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones')->find($id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if(Auth::check()){
                $descripcion = Descripcion::with('producto','asignaciones','evaluaciones')->find($id);;
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Descripcion::with('producto','asignaciones','evaluaciones')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Descripcion::with('producto','asignaciones','evaluaciones')->get()->makeHidden(['id']));
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

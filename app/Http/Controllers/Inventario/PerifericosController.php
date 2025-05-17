<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Perifericos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Perifericos\ExportMultiSheet;
use App\Imports\Inventario\Perifericos\ImportMultiSheet;
use PDF;

class PerifericosController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $perifericos = Perifericos::with('estatus','productos')->get();
                if($perifericos->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un perifericos.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un perifericos.'], 404);
                }
                return response()->json($perifericos, 200);
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
                    'cantidad_existente' => 'required|integer|min:0',
                    'entrada' => 'required|integer|min:0',
                    'salida' => 'required|integer|min:0',
                    'descripcion' => 'nullable|string',
                    'estatus_id' => 'required|exists:estatus,id',
                ], [
                    'cantidad_existente.required' => 'La cantidad existente es obligatoria',
                    'cantidad_existente.integer' => 'La cantidad debe ser un número entero',
                    'cantidad_existente.min' => 'La cantidad no puede ser negativa',
                    'entrada.required' => 'El campo entrada es obligatorio',
                    'entrada.integer' => 'La entrada debe ser un número entero',
                    'entrada.min' => 'La entrada no puede ser negativa',
                    'salida.required' => 'El campo salida es obligatorio',
                    'salida.integer' => 'La salida debe ser un número entero',
                    'salida.min' => 'La salida no puede ser negativa',
                    'descripcion.string' => 'La descripción debe ser una cadena de texto.',
                    'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                ]);

                $perifericos = Perifericos::create([
                    'cantidad_existente'=>$request->cantidad_existente,
                    'entrada'=>$request->entrada,
                    'salida'=>$request->salida,
                    'descripcion'=>$request->descripcion,
                    'estatus_id'=>$request->estatus_id,
                ])->load(['estatus','productos']);
                if(is_null($perifericos)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un perifericos.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un perifericos.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$perifericos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de perifericos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $perifericos = Perifericos::with('estatus','productos')->find($id);
                if(is_null($perifericos)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un perifericos.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un perifericos.'], 404);
                }
                return response()->json($perifericos, 200);
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
                    'cantidad_existente' => 'required|integer|min:0',
                    'entrada' => 'required|integer|min:0',
                    'salida' => 'required|integer|min:0',
                    'descripcion' => 'nullable|string',
                    'estatus_id' => 'required|exists:estatus,id',
                ], [
                    'cantidad_existente.required' => 'La cantidad existente es obligatoria',
                    'cantidad_existente.integer' => 'La cantidad debe ser un número entero',
                    'cantidad_existente.min' => 'La cantidad no puede ser negativa',
                    'entrada.required' => 'El campo entrada es obligatorio',
                    'entrada.integer' => 'La entrada debe ser un número entero',
                    'entrada.min' => 'La entrada no puede ser negativa',
                    'salida.required' => 'El campo salida es obligatorio',
                    'salida.integer' => 'La salida debe ser un número entero',
                    'salida.min' => 'La salida no puede ser negativa',
                    'descripcion.string' => 'La descripción debe ser una cadena de texto.',
                    'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                ]);

                $perifericos = Perifericos::with('estatus','productos')->find($id);
                if(is_null($perifericos)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un perifericos.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un perifericos.'], 404);
                }
                $perifericos->update([
                    'cantidad_existente'=>$request->cantidad_existente,
                    'entrada'=>$request->entrada,
                    'salida'=>$request->salida,
                    'descripcion'=>$request->descripcion,
                    'estatus_id'=>$request->estatus_id,
                ]);
                Log::channel('usuario')->info('Se actualizó correctamente.'.$perifericos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de perifericos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $perifericos = Perifericos::with('estatus','productos')->find($id);
                if(is_null($perifericos)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado eliminar perifericos.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar perifericos.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$perifericos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $perifericos->destroy($id);

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
                $data = new ExportMultiSheet(Perifericos::with('estatus','productos')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Perifericos::with('estatus','productos')->get()->makeHidden(['id']));
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
            $perifericosCargados = $MultiSheet?->PerifericosImport->getRegistrosCargados();
            $perifericosFallidos = $MultiSheet?->PerifericosImport->getRegistrosFallidos();
            $perifericosPendientes = $MultiSheet?->PerifericosImport->getRegistrosPendientes();
            return response()->json([
            'perifericos' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $perifericosCargados,
                'fallidos' => $perifericosFallidos,
                'pendientes' => $perifericosPendientes,
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

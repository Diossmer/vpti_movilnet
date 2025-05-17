<?php

namespace App\Http\Controllers;

use App\Models\Estatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Administrativo\Estatus\ExportMultiSheet;
use App\Imports\Administrativo\Estatus\ImportMultiSheet;
use PDF;

class EstatusController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                //
                $estatus = Estatus::with('usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones')->get();
                if($estatus->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar estatus. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado encontrar un estatus.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar estatus.'], 404);
                }
                return response()->json($estatus, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=> $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'nombre' => 'required|string|max:255',
                    'descripcion' => 'nullable|string|max:500',
                ], [
                    'nombre.required' => 'El campo nombre del estatus está vacío.',
                    'descripcion.required' => 'El campo descripción del estatus está vacío.',
                ]);

                $estatus = Estatus::create([
                    'nombre'=>$request->nombre,
                    'descripcion'=>$request->descripcion,
                ])->load(['usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones']);
                if(is_null($estatus)){
                    Log::channel('sistema')->debug('No se ha logrado guardar estatus. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado guardar estatus.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar estatus.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$estatus,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Permisos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        }  catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            if(Auth::check()){
                //
                $estatus = Estatus::with('usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones')->find($id);
                if(is_null($estatus)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar estatus. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado mostrar estatus.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar estatus.'], 404);
                }
                return response()->json($estatus, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
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
                    'nombre' => 'required|string|max:255',
                    'descripcion' => 'nullable|string|max:500',
                ], [
                    'nombre.required' => 'El campo nombre del estatus está vacío.',
                    'descripcion.required' => 'El campo descripción del estatus está vacío.',
                ]);

                $estatus = Estatus::with('usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones')->find($id);
                if(is_null($estatus)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar estatus. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado actualizar estatus.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar estatus.'], 404);
                }
                $estatus->update([
                    'nombre'=>$request->nombre,
                    'descripcion'=>$request->descripcion,
                ]);
                Log::channel('usuario')->info('Se actualizó correctamente.'.$estatus,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Estatus: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                //
                $estatus = Estatus::with('usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones')->find($id);
                if(is_null($estatus)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar estatus. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado eliminar estatus.", 404);
                    return response()->json(['error' => 'No se ha logrado eliminar estatus.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$estatus,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $estatus->destroy($id);

                return response()->json(['mensaje'=>'Se eliminó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportar(string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Estatus::with('usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Estatus::with('usuarios','productos','asignaciones','perifericos','inventarios','evaluaciones')->get()->makeHidden(['id']));
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
            $estatusCargados = $MultiSheet?->EstatusImport->getRegistrosCargados();
            $estatusFallidos = $MultiSheet?->EstatusImport->getRegistrosFallidos();
            $estatusPendientes = $MultiSheet?->EstatusImport->getRegistrosPendientes();
            return response()->json([
            'estatus' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $estatusCargados,
                'fallidos' => $estatusFallidos,
                'pendientes' => $estatusPendientes,
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

<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Productos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Productos\ExportMultiSheet;
use App\Imports\Inventario\Productos\ImportMultiSheet;
use PDF;

class ProductosController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $productos = Productos::with('descripciones','evaluaciones','perifericos','inventarios','usuario','estatus')->get();
                if($productos->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un productos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un productos.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un productos.'], 404);
                }
                return response()->json($productos, 200);
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
                    'nombre' => 'required|string|max:255',
                    'usuario_id' => 'required|exists:usuarios,id',
                    'estatus_id' => 'required|exists:estatus,id',
                ], [
                    'nombre.required' => 'El campo nombre del producto está vacío.',
                    'usuario_id.required' => 'El campo usuarios es obligatorio.',
                    'usuario_id.exists' => 'El usuarios seleccionado no es válido.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                ]);

                $productos = Productos::create([
                    'nombre'=>$request->nombre,
                    'usuario_id'=>(Auth::id()===1)?$request->usuario_id:Auth::id(),
                    'estatus_id'=>$request->estatus_id,
                ])->load(['descripciones','evaluaciones','perifericos','inventarios','usuario','estatus']);
                if(is_null($productos)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un productos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un productos.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un productos.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$productos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Productos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $productos = Productos::with('descripciones','evaluaciones','perifericos','inventarios','usuario','estatus')->find($id);
                if(is_null($productos)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un productos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un productos.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un productos.'], 404);
                }
                return response()->json($productos, 200);
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
                    'nombre' => 'required|string|max:255',
                    'usuario_id' => 'required|exists:usuarios,id',
                    'estatus_id' => 'required|exists:estatus,id',
                    'categoria_id' => 'required|exists:categorias,id',
                ], [
                    'nombre.required' => 'El campo nombre del producto está vacío.',
                    'usuario_id.required' => 'El campo usuarios es obligatorio.',
                    'usuario_id.exists' => 'El usuarios seleccionado no es válido.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                ]);

                $productos = Productos::with('descripciones','evaluaciones','perifericos','inventarios','usuario','estatus')->find($id);
                if(is_null($productos)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un productos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un productos.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un productos.'], 404);
                }
                $productos->update([
                    'nombre'=>$request->nombre,
                    'usuario_id'=>(Auth::id()===1)?$request->usuario_id:Auth::id(),
                    'estatus_id'=>$request->estatus_id,
                ]);
                Log::channel('usuario')->info('Se actualizó correctamente.'.$productos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Productos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $productos = Productos::with('descripciones','evaluaciones','perifericos','inventarios','usuario','estatus')->find($id);
                if(is_null($productos)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar productos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado eliminar productos.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar productos.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$productos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $productos->destroy($id);

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
                $data = new ExportMultiSheet(Productos::with('usuario','estatus')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Productos::with('usuario','estatus')->get()->makeHidden(['id']));
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
            $productosCargados = $MultiSheet?->ProductosImport->getRegistrosCargados();
            $productosFallidos = $MultiSheet?->ProductosImport->getRegistrosFallidos();
            $productosPendientes = $MultiSheet?->ProductosImport->getRegistrosPendientes();
            return response()->json([
            'productos' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $productosCargados,
                'fallidos' => $productosFallidos,
                'pendientes' => $productosPendientes,
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

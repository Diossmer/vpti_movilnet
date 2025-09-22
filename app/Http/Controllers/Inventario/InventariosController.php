<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Inventarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Inventario\Inventarios\ExportMultiSheet;
use App\Imports\Inventario\Inventarios\ImportMultiSheet;
use PDF;

class InventariosController extends Controller
{
    public function index()
    {
        try {
            if(Auth::check()){
                $inventarios = Inventarios::with('estatus','descripciones.producto')->get();
                if($inventarios->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un inventario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado encontrar un inventario.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un inventario.'], 404);
                }
                return response()->json($inventarios, 200);
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
                    'entrada' => 'nullable|integer|min:0',
                    'salida' => 'nullable|integer|min:0',
                    'observacion' => 'nullable|string',
                    'estatus_id' => 'required|exists:estatus,id',
                    //'producto_id' => 'required|array|exists:productos,id',
                    'descripcion_id'=>'required|array|exists:descripcion,id',
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
                    'observacion.string' => 'La observacion debe ser una cadena de texto.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                    //'producto_id.exists' => 'El producto especificado no existe',
                    //'producto_id.required' => 'El campo producto_id es obligatorio.',
                    //'producto_id.array' => 'El campo producto_id debe ser un arreglo.',
                    'descripcion_id.exists' => 'La descripción seleccionada no existe',
                    'descripcion_id.array' => 'El campo descripcion_id debe ser un número entero.',
                ]);

                $inventario = Inventarios::create([
                    'cantidad_existente'=>$request->cantidad_existente,
                    'entrada'=>$request->entrada,
                    'salida'=>$request->salida,
                    'descripcion'=>$request->descripcion,
                    'estatus_id'=>$request->estatus_id,
                ])->load(['estatus','descripciones.producto']);
                /* if($request->filled('producto_id')){
                    $inventario->productos()->sync($request->producto_id);
                } */
                if($request->filled('descripcion_id')){
                    $inventario->descripciones()->sync($request->descripcion_id);
                }
                if(is_null($inventario)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un inventario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado guardar un inventario.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un inventario.'], 404);
                }
                Log::channel('usuario')->info('Se inventario correctamente.'.$inventario,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se inventario correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de inventario: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $inventario = Inventarios::with('estatus','descripciones.producto')->find($id);
                if(is_null($inventario)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un inventario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado mostrar un inventario.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un inventario.'], 404);
                }
                return response()->json($inventario, 200);
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
                $request->validate([
                    'cantidad_existente' => 'required|integer|min:0',
                    'entrada' => 'nullable|integer|min:0',
                    'salida' => 'nullable|integer|min:0',
                    'observacion' => 'nullable|string',
                    'estatus_id' => 'required|exists:estatus,id',
                    //'producto_id' => 'required|array|exists:productos,id',
                    'descripcion_id'=>'required|array|exists:descripcion,id',
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
                    'observacion.string' => 'La observacion debe ser una cadena de texto.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                    //'producto_id.exists' => 'El producto especificado no existe',
                    //'producto_id.required' => 'El campo producto_id es obligatorio.',
                    //'producto_id.array' => 'El campo producto_id debe ser un arreglo.',
                    'descripcion_id.exists' => 'La descripción seleccionada no existe',
                    'descripcion_id.array' => 'El campo descripcion_id debe ser un número entero.',
                ]);

                $inventario = Inventarios::with('estatus','descripciones.producto')->find($id);
                if(is_null($inventario)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un inventario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado actualizar un inventario.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un inventario.'], 404);
                }
                $inventario->update([
                    'cantidad_existente'=>$request->cantidad_existente,
                    'entrada'=>$request->entrada,
                    'salida'=>$request->salida,
                    'observacion'=>$request->observacion,
                    'estatus_id'=>$request->estatus_id,
                ]);
                /* if($request->filled('producto_id')){
                    $inventario->productos()->sync($request->producto_id);
                } */
                if($request->filled('descripcion_id')){
                    $inventario->descripciones()->sync($request->descripcion_id);
                }
                Log::channel('usuario')->info('Se actualizó correctamente.'.$inventario,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de inventario: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $inventario = Inventarios::with('estatus','descripciones.producto')->find($id);
                if(is_null($inventario)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar inventario. ',['fecha_hora' => now()->toDateTimeString()]);
                    throw new Exception("No se ha logrado eliminar inventario.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar inventario.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$inventario,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $inventario->destroy($id);

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

    public function exportar(?string $id = null)
    {
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Inventarios::with('estatus','descripciones.producto')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Inventarios::with('estatus','descripciones.producto')->get()->makeHidden(['id']));
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
            $inventariosCargados = $MultiSheet?->InventariosImport->getRegistrosCargados();
            $inventariosFallidos = $MultiSheet?->InventariosImport->getRegistrosFallidos();
            $inventariosPendientes = $MultiSheet?->InventariosImport->getRegistrosPendientes();
            return response()->json([
            'inventarios' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $inventariosCargados,
                'fallidos' => $inventariosFallidos,
                'pendientes' => $inventariosPendientes,
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
                'inventarios' => Inventarios::with('estatus','descripciones.producto')->get(),
                'usuario' => \App\Models\Usuarios::find($id)?? '',
                //'usuario' => \App\Models\Usuarios::find(Auth::id())?? '',
            ];

            // Generar y mostrar el PDF.
            $pdf = Pdf::loadView('pdf.sinperifericos', $data);
            return $pdf->stream("reporte_inventario.pdf");

        } catch (\Exception $e) {
            // Registrar el error para depuración.
            Log::error("Error al generar PDF: " . $e->getMessage());
            
            // Retornar una respuesta de error.
            return response()->json(['error' => 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.'], 500);
        }
    }
}

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
                $perifericos = Perifericos::with('estatus','descripciones.producto')->get();
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                if(count($request->descripcion_id) == $request->entrada || (count($request->descripcion_id) == $request->salida && $request->entrada == 0)){
                    $request->validate([
                        /* 'cantidad_existente' => 'required|integer|min:0', */
                        'entrada' => 'nullable|integer|min:0',
                        'salida' => 'nullable|integer|min:0',
                        'observacion' => 'nullable|string',
                        'estatus_id' => 'required|exists:estatus,id',
                        //'producto_id' => 'required|array|exists:productos,id',
                        'descripcion_id'=>'required|array|distinct|exists:descripcion,id',
                    ], [
                        /* 'cantidad_existente.required' => 'La cantidad existente es obligatoria',
                        'cantidad_existente.integer' => 'La cantidad debe ser un número entero',
                        'cantidad_existente.min' => 'La cantidad no puede ser negativa', */
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
                        'descripcion_id.distinct' => 'La descripción ID está duplicada dentro del arreglo de entrada.',
                    ]);
                    $perifericos = Perifericos::with('descripciones')
                        ->whereHas('descripciones', function ($query) use ($request) {
                            $query->whereIn('descripcion_id', $request->descripcion_id);
                        })->get();
                    if($perifericos->isNotEmpty()){
                        $seriales = $perifericos->flatMap(fn ($u) => $u->descripciones)->pluck('serial')->unique()->implode(', ');
                        Log::channel('sistema')->debug('No se ha logrado guardar por que está duplicado. ',['seriales_duplicados' => $seriales,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                        throw new Exception("No se ha logrado guardar. Serial duplicado: {$seriales}", 400);
                        return response()->json(['error' => "No se ha logrado guardar. Serial duplicado: {$seriales}"], 400); 
                    }
                    $perifericos = Perifericos::create([
                        'cantidad_existente'=>$request->cantidad_existente,
                        'entrada'=>$request->entrada,
                        'salida'=>$request->salida,
                        'observacion'=>$request->observacion,
                        'estatus_id'=>$request->estatus_id,
                    ])->load(['estatus','descripciones.producto']);
                    /* if($request->filled('producto_id')){
                        $perifericos->productos()->sync($request->producto_id);
                    } */
                    if($request->filled('descripcion_id')){
                        $perifericos->descripciones()->sync($request->descripcion_id);
                    }
                    if(is_null($perifericos)){
                        Log::channel('sistema')->debug('No se ha logrado guardar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                        throw new Exception("No se ha logrado guardar un perifericos.", 404);
                        return response()->json(['error'=>'No se ha logrado guardar un perifericos.'], 404);
                    }
                    Log::channel('usuario')->info('Se almacenó correctamente.'.$perifericos,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
                } else {
                    Log::channel('errores')->error('La cantidad de descripciones no coincide con entrada/salida.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("La cantidad de descripciones no coincide con entrada/salida.", 400);
                }
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de perifericos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $perifericos = Perifericos::with('estatus','descripciones.producto')->find($id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function update(Request $request, ?string $id)
    {
        try {
            if(Auth::check()){
                if(count($request->descripcion_id) == $request->entrada || (count($request->descripcion_id) == $request->salida && $request->entrada == 0)){
                    
                    // --- Validaciones ---
                    $request->validate([
                        // ... (Validaciones se mantienen igual)
                        'entrada' => 'nullable|integer|min:0',
                        'salida' => 'nullable|integer|min:0',
                        'observacion' => 'nullable|string',
                        'estatus_id' => 'required|exists:estatus,id',
                        'descripcion_id'=>'required|array|distinct|exists:descripcion,id',
                    ], [
                        // ... (Mensajes de error se mantienen igual)
                        'entrada.min' => 'La entrada no puede ser negativa',
                        'salida.min' => 'La salida no puede ser negativa',
                        'descripcion_id.distinct' => 'La descripción ID está duplicada dentro del arreglo de entrada.',
                    ]);

                    //Obtener el modelo a actualizar
                    $perifericosSaved = Perifericos::with('estatus','descripciones.producto')->find($id);

                    if(is_null($perifericosSaved)){
                        Log::channel('sistema')->debug('No se encontró el periférico para actualizar.',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                        throw new Exception("No se ha logrado actualizar un perifericos. El ID no existe.", 404);
                        // Eliminado: return response()->json(['error'=>'No se ha logrado actualizar un perifericos.'], 404);
                    }
                    
                    //Chequeo de duplicados (Descripciones ya asociadas a OTRO Periférico)
                    $perifericos_duplicados = Perifericos::with('descripciones')
                        ->whereHas('descripciones', function ($query) use ($request) {
                            $query->whereIn('descripcion_id', $request->descripcion_id);
                        })->where('id', '=', $id)->get();
                    //$array_id = $perifericos_duplicados->flatMap(fn ($p) => $p->descripciones)->pluck('id');
                    if($perifericos_duplicados->isNotEmpty() && ($request->salida && $request->entrada == 0)){
                        //ACTUALIZACIÓN DE DATOS (Este bloque es el que se ejecuta si no hay errores)
                        $perifericosSaved->update([
                            'entrada'=>$request->entrada,
                            'salida'=>$request->salida,
                            'observacion'=>$request->observacion,
                            'estatus_id'=>$request->estatus_id,
                        ]);
                        //SINCRONIZACIÓN DE LA RELACIÓN
                        if($request->filled('descripcion_id')){
                            $perifericosSaved->descripciones()->sync($request->descripcion_id);
                        }

                        Log::channel('usuario')->info('Se actualizó correctamente.'.$perifericosSaved,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                        return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
                    }
                    if($perifericos_duplicados->isNotEmpty()){
                        $seriales = $perifericos_duplicados->flatMap(fn ($u) => $u->descripciones)->pluck('serial')->unique()->implode(', ');
                        Log::channel('sistema')->debug('No se ha logrado guardar por que está duplicado. ',['seriales_duplicados' => $seriales,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                        throw new Exception("No se ha logrado guardar. Serial duplicado: {$seriales}", 400);
                        // Eliminado: return response()->json(['error' => "No se ha logrado guardar. Serial duplicado: {$seriales}"], 400); 
                    }

                    //ACTUALIZACIÓN DE DATOS (Este bloque es el que se ejecuta si no hay errores)
                    $perifericosSaved->update([
                        'entrada'=>$request->entrada,
                        'salida'=>$request->salida,
                        'observacion'=>$request->observacion,
                        'estatus_id'=>$request->estatus_id,
                    ]);
                    //SINCRONIZACIÓN DE LA RELACIÓN
                    if($request->filled('descripcion_id')){
                        $perifericosSaved->descripciones()->sync($request->descripcion_id);
                    }

                    Log::channel('usuario')->info('Se actualizó correctamente.'.$perifericosSaved,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);

                } else {
                    // Si la condición inicial de cantidad falla
                    Log::channel('errores')->error('La cantidad de descripciones no coincide con entrada/salida.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("La cantidad de descripciones no coincide con entrada/salida.", 400); // 400 es más apropiado que 402
                }
            } else {
                // Usuario no autenticado
                Log::channel('errores')->error('Usuario no autenticado para actualizar.', ['fecha_hora' => now()->toDateTimeString()]);
                throw new Exception("No está autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de perifericos: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            // Devolver 500 para errores internos o el código de la excepción si lo especificaste (ej: 404, 400)
            return response()->json(['error'=>$e->getMessage()], $e->getCode() ?: 500); 
        }
    }

    public function destroy(?string $id)
    {
        try {
            if(Auth::check()){
                $perifericos = Perifericos::with('estatus','descripciones.producto')->find($id);
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
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(?string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Perifericos::with('estatus','descripciones.producto')->where('id','=',$id)->get()->makeHidden(['id']));
                if(!$data){
                    Log::channel('sistema')->debug('No se ha logrado exportar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new Exception("No se ha logrado exportar un perifericos.", 404);
                }
                Log::channel('usuario')->info('Se exportó correctamente: ', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Perifericos::with('estatus','descripciones.producto')->get()->makeHidden(['id']));
            if(!$data){
                Log::channel('sistema')->debug('No se ha logrado exportar un perifericos. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new Exception("No se ha logrado exportar un perifericos.", 404);
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
            $perifericosCargados = $MultiSheet?->PerifericosImport->getRegistrosCargados();
            $perifericosFallidos = $MultiSheet?->PerifericosImport->getRegistrosFallidos();
            $perifericosPendientes = $MultiSheet?->PerifericosImport->getRegistrosPendientes();
            Log::channel('usuario')->info('Se importó correctamente.', ['pendientes' => $perifericosPendientes,'fallidos' => $perifericosFallidos,'cargados' => $perifericosCargados,'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                'perifericos' => Perifericos::with('estatus','descripciones.producto')->get(),
                'usuario' => \App\Models\Usuarios::find($id)?? '',
                //'usuario' => \App\Models\Usuarios::find(Auth::id())?? '',
            ];
            Log::channel('sistema')->debug('Generando PDF para perifericos.', ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
            // Generar y mostrar el PDF.
            $pdf = Pdf::loadView('pdf.perifericos', $data);
            Log::channel('usuario')->info('Se generó correctamente el PDF.', ['fecha_hora' => now()->toDateTimeString(), Auth::user()]);
            return $pdf->stream("reporte_perifericos.pdf");

        } catch (\Exception $e) {
            // Registrar el error para depuración.
            Log::error("Error al generar PDF: " . $e->getMessage());
            Log::channel('errores')->error('Error al generar el PDF: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            // Retornar una respuesta de error.
            return response()->json(['error' => 'No se pudo generar el PDF. Por favor, inténtalo de nuevo.'], 500);
        }
    }
}

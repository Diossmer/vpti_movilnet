<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Administrativo\Roles\ExportMultiSheet;
use App\Imports\Administrativo\Roles\ImportMultiSheet;
use PDF;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if(Auth::check()){
                $rol = Roles::with('usuarios')->get();
                if($rol->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar un rol. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado encontrar un rol.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar un rol.'], 404);
                }
                return response()->json($rol, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'nombre' => 'required|string|max:255',
                    'descripcion' => 'nullable|string|max:500',
                ], [
                    'nombre.required' => 'El campo nombre del rol está vacío.',
                    'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
                ]);

                $rol = Roles::create([
                    'nombre'=>$request->nombre,
                    'descripcion'=>$request->descripcion,
                ])->load(['usuarios']);
                if(is_null($rol)){
                    Log::channel('sistema')->debug('No se ha logrado guardar un rol. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado guardar un rol.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar un rol.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$rol,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Roles: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            if(Auth::check()){
                //
                $rol = Roles::with('usuarios')->find($id);
                if(is_null($rol)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar un rol. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado mostrar un rol.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar un rol.'], 404);
                }
                return response()->json($rol, 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            if(Auth::check()){
                $request->validate([
                    'nombre' => 'required|string|max:255',
                    'descripcion' => 'nullable|string|max:500',
                ], [
                    'nombre.required' => 'El campo nombre del rol está vacío.',
                    'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
                ]);

                $rol = Roles::with('usuarios')->find($id);
                if(is_null($rol)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar un rol. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado actualizar un rol.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar un rol.'], 404);
                }
                $rol->update([
                    'nombre'=>$request->nombre,
                    'descripcion'=>$request->descripcion,
                ]);
                Log::channel('usuario')->info('Se actualizó correctamente.'.$rol,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Roles: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            if(Auth::check()){
                //
                $rol = Roles::with('usuarios')->find($id);
                if(is_null($rol)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar un rol. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado eliminar un rol.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar un rol.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$rol,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                $rol->delete();
                return response()->json(['mensaje'=>'Se eliminó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error($e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    public function exportar(string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Roles::where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Roles::get()->makeHidden(['id']));
            return ($data)->download('*.xlsx');
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al exportar el archivo: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
                'status' => 'error',
                'error' => 'Error al exportar el archivo: ' . $e->getMessage(),
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
            $rolesCargados = $MultiSheet?->RolesImport->getRegistrosCargados();
            $rolesFallidos = $MultiSheet?->RolesImport->getRegistrosFallidos();
            $rolesPendientes = $MultiSheet?->RolesImport->getRegistrosPendientes();
            return response()->json([
            'roles' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $rolesCargados,
                'fallidos' => $rolesFallidos,
                'pendientes' => $rolesPendientes,
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

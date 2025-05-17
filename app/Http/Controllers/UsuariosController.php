<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Administrativo\Usuarios\ExportMultiSheet;
use App\Imports\Administrativo\Usuarios\ImportMultiSheet;
use PDF;

class UsuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if(Auth::check()){
                $usuario = Usuarios::with('estatus','rol','productos','asignaciones')->get();
                if($usuario->isEmpty()){
                    Log::channel('sistema')->debug('No se ha logrado encontrar usuario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado encontrar un usuario.", 404);
                    return response()->json(['error'=>'No se ha logrado encontrar usuario.'], 404);
                }
                return response()->json($usuario, 200);
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
                // Validación de los datos del request
                $request->validate([
                    'nombre' => 'required|string|max:255',
                    'apellido' => 'required|string|max:255',
                    'cedula' => 'required|string|max:20|unique:usuarios,cedula',
                    'usuario' => 'required|string|max:255|unique:usuarios,usuario',
                    'correo' => 'required|email|max:255|unique:usuarios,correo',
                    'direccion' => 'nullable|string|max:500',
                    'ciudad' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|max:255',
                    'telefono_casa' => 'nullable|string|max:15',
                    'telefono_celular' => 'nullable|string|max:15',
                    'telefono_alternativo' => 'nullable|string|max:15',
                    'codigo_postal' => 'nullable|string|max:10',
                    'password' => 'required|string|min:8',
                    'estatus_id' => 'required|exists:estatus,id',
                    'rol_id' => 'required|exists:roles,id',
                ], [
                    'nombre.required' => 'El campo nombre es obligatorio.',
                    'apellido.required' => 'El campo apellido es obligatorio.',
                    'cedula.required' => 'El campo cédula es obligatorio.',
                    'cedula.unique' => 'La cédula ya está en uso.',
                    'usuario.required' => 'El campo usuario es obligatorio.',
                    'usuario.unique' => 'El usuario ya está en uso.',
                    'correo.required' => 'El campo correo es obligatorio.',
                    'correo.email' => 'El correo debe ser una dirección de correo válida.',
                    'correo.unique' => 'El correo ya está en uso.',
                    'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
                    'telefono_casa.max' => 'El teléfono de casa no puede exceder 15 caracteres.',
                    'telefono_celular.max' => 'El teléfono celular no puede exceder 15 caracteres.',
                    'telefono_alternativo.max' => 'El teléfono alternativo no puede exceder 15 caracteres.',
                    'codigo_postal.max' => 'El código postal no puede exceder 10 caracteres.',
                    'password.required' => 'El campo contraseña es obligatorio.',
                    'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                    'password.confirmed' => 'Las contraseñas no coinciden.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                    'rol_id.required' => 'El campo rol es obligatorio.',
                    'rol_id.exists' => 'El rol seleccionado no es válido.',
                ]);

                $usuario = Usuarios::create([
                    'nombre'=>$request->nombre,
                    'apellido'=>$request->apellido,
                    'cedula'=>$request->cedula,
                    'usuario'=>$request->usuario,
                    'correo'=>$request->correo,
                    'direccion'=>$request->direccion,
                    'ciudad'=>$request->ciudad,
                    'estado'=>$request->estado,
                    'telefono_casa'=>$request->telefono_casa,
                    'telefono_celular'=>$request->telefono_celular,
                    'telefono_alternativo'=>$request->telefono_alternativo,
                    'codigo_postal'=>$request->codigo_postal,
                    'password'=>Hash::make($request->password),
                    'estatus_id'=>$request->estatus_id,
                    'rol_id'=>$request->rol_id,
                ])->load(['estatus','rol','productos','asignaciones']);
                if(is_null($usuario)){
                    Log::channel('sistema')->debug('No se ha logrado guardar usuario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado guardar usuario.", 404);
                    return response()->json(['error'=>'No se ha logrado guardar usuario.'], 404);
                }
                Log::channel('usuario')->info('Se almacenó correctamente.'.$usuario,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Usuarios: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $usuario = Usuarios::with('estatus','rol','productos','asignaciones')->find($id);
                if(is_null($usuario)){
                    Log::channel('sistema')->debug('No se ha logrado mostrar usuario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado mostrar usuario.", 404);
                    return response()->json(['error'=>'No se ha logrado mostrar usuario.'], 404);
                }
                return response()->json($usuario, 200);
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
                    'apellido' => 'required|string|max:255',
                    'cedula' => 'required|string|max:20|unique:usuarios,cedula',
                    'usuario' => 'required|string|max:255|unique:usuarios,usuario',
                    'correo' => 'required|email|max:255|unique:usuarios,correo',
                    'direccion' => 'nullable|string|max:500',
                    'ciudad' => 'nullable|string|max:255',
                    'estado' => 'nullable|string|max:255',
                    'telefono_casa' => 'nullable|string|max:15',
                    'telefono_celular' => 'nullable|string|max:15',
                    'telefono_alternativo' => 'nullable|string|max:15',
                    'codigo_postal' => 'nullable|string|max:10',
                    'password' => 'required|string|min:8',
                    'estatus_id' => 'required|exists:estatus,id',
                    'rol_id' => 'required|exists:roles,id',
                ], [
                    'nombre.required' => 'El campo nombre es obligatorio.',
                    'apellido.required' => 'El campo apellido es obligatorio.',
                    'cedula.required' => 'El campo cédula es obligatorio.',
                    'cedula.unique' => 'La cédula ya está en uso.',
                    'usuario.required' => 'El campo usuario es obligatorio.',
                    'usuario.unique' => 'El usuario ya está en uso.',
                    'correo.required' => 'El campo correo es obligatorio.',
                    'correo.email' => 'El correo debe ser una dirección de correo válida.',
                    'correo.unique' => 'El correo ya está en uso.',
                    'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
                    'telefono_casa.max' => 'El teléfono de casa no puede exceder 15 caracteres.',
                    'telefono_celular.max' => 'El teléfono celular no puede exceder 15 caracteres.',
                    'telefono_alternativo.max' => 'El teléfono alternativo no puede exceder 15 caracteres.',
                    'codigo_postal.max' => 'El código postal no puede exceder 10 caracteres.',
                    'password.required' => 'El campo contraseña es obligatorio.',
                    'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                    'password.confirmed' => 'Las contraseñas no coinciden.',
                    'estatus_id.required' => 'El campo estatus es obligatorio.',
                    'estatus_id.exists' => 'El estatus seleccionado no es válido.',
                    'rol_id.required' => 'El campo rol es obligatorio.',
                    'rol_id.exists' => 'El rol seleccionado no es válido.',
                ]);

                $usuario = Usuarios::with('estatus','rol','productos','asignaciones')->find($id);
                if(is_null($usuario)){
                    Log::channel('sistema')->debug('No se ha logrado actualizar usuario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado actualizar usuario.", 404);
                    return response()->json(['error'=>'No se ha logrado actualizar usuario.'], 404);
                }
                $usuario->update([
                    'nombre'=>$request->nombre,
                    'apellido'=>$request->apellido,
                    'cedula'=>$request->cedula,
                    'usuario'=>$request->usuario,
                    'correo'=>$request->correo,
                    'direccion'=>$request->direccion,
                    'ciudad'=>$request->ciudad,
                    'estado'=>$request->estado,
                    'telefono_casa'=>$request->telefono_casa,
                    'telefono_celular'=>$request->telefono_celular,
                    'telefono_alternativo'=>$request->telefono_alternativo,
                    'codigo_postal'=>$request->codigo_postal,
                    'password'=>Hash::make($request->password),
                    'estatus_id'=>$request->estatus_id,
                    'rol_id'=>$request->rol_id,
                ]);
                Log::channel('usuario')->info('Se actualizó correctamente.'.$usuario,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje'=>'Se actualizó correctamente.'], 200);
            }else{
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                throw new \Exception("no esta autorizado.", 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validacion de Usuarios: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
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
                $usuario = Usuarios::with('estatus','rol','productos','asignaciones')->find($id);
                if(is_null($usuario)){
                    Log::channel('sistema')->debug('No se ha logrado eliminar usuario. ',['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                    throw new \Exception("No se ha logrado eliminar usuario.", 404);
                    return response()->json(['error'=>'No se ha logrado eliminar usuario.'], 404);
                }
                Log::channel('usuario')->info('Se eliminó correctamente.'.$usuario,['fecha_hora' => now()->toDateTimeString(),Auth::user()]);

                $usuario->destroy($id);

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

    /**
     * Remove the specified resource from storage.
     */
    public function exportar(string $id=null){
        try {
            if(is_numeric($id)){
                $data = new ExportMultiSheet(Usuarios::with('estatus','rol','productos','asignaciones')->where('id','=',$id)->get()->makeHidden(['id']));
                return ($data)->download('*.xlsx');
            }
            $data = new ExportMultiSheet(Usuarios::with('estatus','rol','productos','asignaciones')->get()->makeHidden(['id']));
            return ($data)->download('*.xlsx');
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al exportar el archivo: ', [$e->getMessage(),'fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json([
                'estatus' => 'error',
                'error' => 'Error al exportar el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
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
            $usuariosCargados = $MultiSheet?->UsuariosImport->getRegistrosCargados();
            $usuariosFallidos = $MultiSheet?->UsuariosImport->getRegistrosFallidos();
            $usuariosPendientes = $MultiSheet?->UsuariosImport->getRegistrosPendientes();
            return response()->json([
            'usuarios' => 'success',
            'mensaje' => 'Archivo importado correctamente.',
            'estatus' => [
                'cargados' => $usuariosCargados,
                'fallidos' => $usuariosFallidos,
                'pendientes' => $usuariosPendientes,
            ]], 200);
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error al importar el archivo: ' . $e->getMessage());

            // Respuesta JSON de error
            return response()->json([
                'estatus' => 'error',
                'error' => 'Error al importar el archivo: ' . $e->getMessage(),
            ], 500);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $failure->row();
                $failure->attribute();
                $failure->errors();
                $failure->values();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function generatepdf(string $id=null, string $docs=null){
        // Obtén los datos necesarios para generar el PDF
        $asistencias = \App\Models\Asistencias::with('usuario','estatus')->get();
        $data = [
            'title' => 'Reporte',
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

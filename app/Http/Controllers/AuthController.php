<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificacionCorreo;
use App\Models\Usuarios;
use App\Models\Asistencias;
use App\Models\Estatus;

class AuthController extends Controller
{
    public function ingresar(Request $request){
        try {
            $request->validate([
                'usuario' => 'required|string',
                'password' => 'required|string',
            ],[
                'usuario'=>'El campo usuario esta vacío.',
                'password'=>'El campo contraseña esta vacío.',
            ]);
            $credentials = ['password' => $request->password];
            if ($request->filled('usuario')) {
                $credentials['usuario'] = $request->usuario;
            }
            if (! $token = auth()->attempt($credentials)) {
                Log::channel('errores')->error('No está autorizado.', ['fecha_hora' => now()->toDateTimeString()]);
                return response()->json(['error' => 'No esta autorizado'], 401);
            }
            $usuario=auth('api')->setToken($token)->user()->load('estatus','rol','productos','asignaciones');
            if($usuario->estatus->nombre !== 'Activo' && $usuario->estatus->nombre !== 'activo'){ 
                Log::channel('errores')->error('No esta autorizado por el administrador.', ['fecha_hora' => now()->toDateTimeString()]);
                return response()->json(['error' => 'No esta autorizado por el administrador.'], 401);
            }
            $token = Auth::login($usuario);
            Log::channel('usuario')->info('Usuario logeado.', [
                'usuario' => $usuario->usuario,
                'correo' => $usuario->correo,
                'cedula'=> $usuario->cedula,
                'fecha_hora' => now()->toDateTimeString()
            ]);
            return $this->respondWithToken($token);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('sistema logearse: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error inesperado: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => 'Error inesperado, por favor intente nuevamente.'], 500);
        }
    }

    public function perfil(){
        try {
            if (!auth()->check()) {
                Log::channel('sistema')->debug('No hay sesión activa', ['fecha_hora' => now()->toDateTimeString()]);
                return response()->json(['error' => 'No hay sesión activa'], 401);
            }
            $usuario = auth()->user()->load('estatus','rol','productos','asignaciones');
            Log::channel('usuario')->info('Usuario logeado.', [
                'usuario' => $usuario->usuario,
                'correo' => $usuario->correo,
                'cedula'=> $usuario->cedula,
                'fecha_hora' => now()->toDateTimeString()
            ]);
            return response()->json($usuario);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('sistema logearse: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error inesperado: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => 'Error inesperado, por favor intente nuevamente.'], 500);
        }
    }

    public function salir(){
        try {
            if (!auth()->check()) {
                Log::channel('sistema')->debug('No hay sesión activa', ['fecha_hora' => now()->toDateTimeString()]);
                return response()->json(['error' => 'No hay sesión activa'], 401);
            }
            Log::channel('usuario')->info('Usuario finalizado', [
                'usuario' => Auth::user()->usuario,
                'correo' => Auth::user()->correo,
                'cedula' => Auth::user()->cedula,
                'fecha_hora' => now()->toDateTimeString()
            ]);
            auth()->logout();
            return response()->json(['Sesión cerrada exitosamente'],200);
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al cerrar sesión: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => 'Error inesperado al cerrar sesión'], 500);
        }
    }

    public function refrescar(){
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (\Exception $e) {
            Log::channel('errores')->error('No se pudo refrescar el token y la sesión: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['error' => 'No se pudo refrescar el token y la sesión'], 500);
        }
    }

    public function refrescarPerfil(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            Log::channel('sistema')->debug('refrescarPerfil: No hay sesión activa', ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => 'No hay sesión activa'], 401);
        }

        try {
            // 1. Validación dinámica y ajustada (Usando 'sometimes' para PATCH)
            // IMPORTANTE: Se ignora la cédula/correo/usuario del usuario actual en las reglas 'unique'.
            $request->validate([
                'nombre' => 'sometimes|required|string|max:255',
                'apellido' => 'sometimes|required|string|max:255',
                'correo' => 'sometimes|required|email|max:255|unique:usuarios,correo,'.$usuario->id,
                'direccion' => 'nullable|string|max:500',
                'ciudad' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:255',
                'telefono_casa' => 'nullable|string|max:15',
                'telefono_celular' => 'sometimes|required|string|max:15',
                'telefono_alternativo' => 'nullable|string|max:15',
                'codigo_postal' => 'nullable|string|max:10',
            ], [
                'nombre.required' => 'El campo nombre es obligatorio.',
                'correo.unique' => 'El correo ya está en uso.',
                'telefono_celular.required' => 'El campo telefono celular es obligatorio.',
            ]);

            $data = $request->only([
                'nombre', 'apellido', 'correo', 'direccion', 'ciudad', 'estado',
                'telefono_casa', 'telefono_celular', 'telefono_alternativo', 'codigo_postal'
            ]);

            foreach ($data as $key => $value) {
                if ($request->has($key)) {
                    $usuario->{$key} = $value;
                }
            }
            
            $usuario->save();
            
            Log::channel('usuario')->info('Datos de perfil actualizados', ['user_id' => $usuario->id, 'cambios' => $data]);
            return response()->json(['mensaje'=>"Se actualizó correctamente."], 200);

        } catch (ValidationException $e) {
            Log::channel('sistema')->debug('Validación fallida en refrescarPerfil: ' . $e->getMessage());
            // Laravel devuelve automáticamente 422 con los errores detallados.
            return response()->json(['error' => $e->validator->errors()], 422); 
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error inesperado al actualizar el perfil: ' . $e->getMessage());
            return response()->json(['error' => 'Error inesperado al actualizar el perfil.'], 500);
        }
    }

    public function refrescarContrasena(Request $request){
        $request->validate([
            'password_old' => 'required|string|min:4',
            'password' => 'required|string|min:4',
        ]);
        try {
            if (Hash::check($request->password_old, auth()->user()->password)) {
                $usuario = auth()->user();
                $usuario->password = Hash::make($request->password);
                $usuario->save();
                Log::channel('usuario')->info('Contraseña actualizada', [
                    'usuario' => $usuario->usuario,
                    'correo' => $usuario->correo,
                    'cedula' => $usuario->cedula,
                    'fecha_hora' => now()->toDateTimeString()
                ]);
                $this->respondWithToken(auth()->refresh());
                return response()->json(['mensaje' => 'Su contraseña se cambio con ¡exito!.'],200);
            } else {
                Log::channel('errores')->error('Contraseña inválida', ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
                return response()->json(['mensaje' => 'Su contraseña anterior es inválida.'], 403);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al actualizar la contraseña: ' . $e->getMessage(), ['fecha_hora' => now()->toDateTimeString(),Auth::user()]);
            return response()->json(['mensaje' => 'Error inesperado al actualizar la contraseña.'], 500);
        }
    }

    /*public function enviarVerificacionCorreo(Request $request)
    {
        try {
            if($request->usuario == Usuarios::where('usuario',$request->usuario)->first()->usuario){
                $usuario = Usuarios::where('usuario',$request->usuario)->first();
                if (!$usuario) {
                    Log::channel('errores')->error('Usuario no autenticado.', ['fecha_hora' => now()->toDateTimeString()]);
                    return response()->json(['message' => 'Usuario no autenticado.'], 401);
                }
                Notification::send($usuario, new VerificacionCorreo());
                return response()->json(['message' => 'Correo de verificación enviado.']);
            }else if($request->cedula == Usuarios::where('cedula',$request->cedula)->first()->cedula){
                $usuario = Usuarios::where('usuario',$request->cedula)->first();
                if (!$usuario) {
                    Log::channel('errores')->error('Usuario no autenticado.', ['fecha_hora' => now()->toDateTimeString()]);
                    return response()->json(['message' => 'Usuario no autenticado.'], 401);
                }
                Notification::send($usuario, new VerificacionCorreo());
                return response()->json(['message' => 'Correo de verificación enviado.']);
            }
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al enviar correo de verificación: ' . $e->getMessage(), [
                'fecha_hora' => now()->toDateTimeString(),
                'usuario_id' => $usuario->id ?? null,
            ]);
            return response()->json(['message' => 'Error inesperado al enviar el correo de verificación.'], 500);
        }
    }*/

    /*public function verificar(Request $request, $id)
    {
        $usuario = Usuario::find($id);
        $usuario->correo_verified_at = now();
        $usuario->save();

        return response()->json(['message' => 'Correo verificado exitosamente.']);
    }*/

    protected function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ])->withCookie(cookie('token', $token, auth()->factory()->getTTL() * 60));
    }
}

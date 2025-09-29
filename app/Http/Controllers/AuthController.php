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
            $usuario =auth('api')->setToken($token)->user()->load('estatus','rol','productos','asignaciones');
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

    public function refrescarPerfil(Request $request){
        $usuario = auth()->user();
        if (!$usuario) {
            Log::channel('sistema')->debug('refrescarPerfil: No hay sesión activa', ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => 'No hay sesión activa'], 401);
        }
        try {
            // Se actualizan las propiedades del objeto $usuario, NO se sobrescribe la variable $usuario.
            // Usamos $request->has() ya que la interfaz Vue envía solo el campo editado al salir del foco.
            if ($request->has('nombre')) {
                $usuario->nombre = $request->nombre;
            }
            if ($request->has('apellido')) {
                $usuario->apellido = $request->apellido;
            }
            if ($request->has('correo')) {
                // Validación opcional para el correo
                $request->validate(['correo' => 'email|unique:usuarios,correo,' . $usuario->id]);
                $usuario->correo = $request->correo;
            }
            if ($request->has('direccion')) {
                $usuario->direccion = $request->direccion;
            }
            if ($request->has('ciudad')) {
                $usuario->ciudad = $request->ciudad;
            }
            if ($request->has('estado')) {
                $usuario->estado = $request->estado;
            }
            if ($request->has('telefono_casa')) {
                $usuario->telefono_casa = $request->telefono_casa;
            }
            if ($request->has('telefono_celular')) {
                $usuario->telefono_celular = $request->telefono_celular;
            }
            if ($request->has('telefono_alternativo')) {
                $usuario->telefono_alternativo = $request->telefono_alternativo;
            }
            if ($request->has('codigo_postal')) {
                $usuario->codigo_postal = $request->codigo_postal;
            }

            $usuario->save();
            Log::channel('usuario')->info('Perfil actualizado', [
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'cedula' => $usuario->cedula,
                'usuario' => $usuario->usuario,
                'correo' => $usuario->correo,
                'direccion' => $usuario->direccion,
                'ciudad' => $usuario->ciudad,
                'estado' => $usuario->estado,
                'telefono_casa' => $usuario->telefono_casa,
                'telefono_celular' => $usuario->telefono_celular,
                'telefono_alternativo' => $usuario->telefono_alternativo,
                'codigo_postal' => $usuario->codigo_postal,
                'estatus_id' => $usuario->estatus_id,
                'rol_id' => $usuario->rol_id,
                'fecha_hora' => now()->toDateTimeString()
            ]);
            return response()->json(['mensaje'=>"Se almacenó correctamente."], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('sistema')->debug('Validación fallida en refrescarPerfil: ' . $e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('errores')->error('Error al actualizar el perfil: ' . $e->getMessage(), ['fecha_hora' => now()->toDateTimeString(), 'user_id' => $usuario->id ?? 'N/A']);
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
                return $this->respondWithToken(auth()->refresh());
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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Valida el token de autorización
            // Comprueba si el encabezado 'Authorization' contiene un token Bearer.
            if (preg_match('/Bearer\s(\S+)/', $request->header('Authorization'), $matches) && $request->user()) {
                $token = $matches[1]; // Extrae el token de la cabecera.

                // Compara el token extraído con un token específico (hardcoded).
                if ($token !== "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbÑ9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWÑ0IjoxNzI2NjY2MDg2LCJleHAiOjE3MjY2Njk2ODYsIm5iZiI6MTcyNjK2NjA4NiwianRpIjoiZmdvVkxMYThOdVNmVXp1OCIsInN1YiI6IjBhOBFmYTI2LWQ2NzUtNDdiYy1iZHc2LTI5MDE2YjA1MjNjNSIsInBydiI6IjU1NG01YWVhNGNi0jU0MDE5ZTEyMDY3YTdjYTUhN2Y2NzVkNTQ0Y2EhfQ.zGiEnwokGbwnSthqvB3lCDhl7wNEdX3c7QFVTThT4gU") {
                    // Si el token no coincide, permite continuar con la solicitud.
                    return $next($request);
                }
            }
            Log::channel('errores')->error('Autenticación inválida Token', ['fecha_hora' => now()->toDateTimeString()]);
            // Si no se proporciona un token válido, devuelve un error de autenticación.
            return response()->json(['error' => 'Autenticación inválida'], 401);
        } catch (Throwable $e) {
            // Captura cualquier error inesperado que ocurra durante la validación.
            Log::channel('errores')->error('Error inesperado: '.$e->getMessage(), ['fecha_hora' => now()->toDateTimeString()]);
            report($e); // Reporta el error.
            return false; // Devuelve false en caso de error.
        }
    }
}

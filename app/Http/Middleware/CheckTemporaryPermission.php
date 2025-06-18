<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckTemporaryPermission
{
    /**
     * Maneja una petición entrante para verificar permisos temporales.
     * 
     * Los permisos temporales permiten otorgar acceso por tiempo limitado,
     * útil para emergencias, turnos especiales, o accesos de reemplazo.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Permiso requerido
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $this->unauthorizedResponse($request, 'Usuario no autenticado');
        }

        // Admin siempre puede acceder
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar permiso regular primero
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // Verificar permiso temporal
        $hasTemporaryPermission = $this->checkTemporaryPermission($user, $permission);

        if (!$hasTemporaryPermission) {
            Log::warning('CheckTemporaryPermission: Usuario sin permiso temporal válido', [
                'user_id' => $user->id,
                'permission' => $permission,
                'route' => $request->route()->getName(),
                'ip' => $request->ip()
            ]);

            return $this->unauthorizedResponse($request, 'No tienes permiso para acceder a esta sección');
        }

        Log::info('CheckTemporaryPermission: Acceso autorizado por permiso temporal', [
            'user_id' => $user->id,
            'permission' => $permission,
            'route' => $request->route()->getName()
        ]);

        return $next($request);
    }

    /**
     * Verificar si el usuario tiene el permiso temporal válido
     */
    private function checkTemporaryPermission($user, string $permission): bool
    {
        $now = Carbon::now();

        return DB::table('permisos_temporales')
            ->where('usuario_id', $user->id)
            ->where('permiso', $permission)
            ->where('activo', true)
            ->where('fecha_inicio', '<=', $now)
            ->where(function($query) use ($now) {
                $query->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>=', $now);
            })
            ->exists();
    }

    /**
     * Respuesta para acceso no autorizado
     */
    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'code' => 'TEMPORARY_PERMISSION_REQUIRED',
                'message' => 'No tienes permiso temporal válido para acceder a este recurso'
            ], 403);
        }

        return redirect()->back()
            ->withErrors(['permission' => $message])
            ->with('error', 'No tienes permiso para acceder a este recurso');
    }
} 
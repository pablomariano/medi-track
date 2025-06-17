<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions Lista de permisos requeridos (el usuario necesita al menos uno)
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar que el usuario esté activo
        if (!$user->isActive()) {
            abort(403, 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // Si es administrador, permitir acceso a todo
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        if (empty($permissions) || $user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        // Log del intento de acceso no autorizado
        \Log::warning('Acceso denegado por falta de permisos', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_permissions' => $permissions,
            'user_role' => $user->role?->nombre,
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Respuesta según el tipo de request
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.',
                'required_permissions' => $permissions
            ], 403);
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
} 
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Lista de roles permitidos (el usuario necesita al menos uno)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
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

        // Verificar si el usuario tiene al menos uno de los roles requeridos
        if (empty($roles) || $user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Log del intento de acceso no autorizado
        \Log::warning('Acceso denegado por rol insuficiente', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_roles' => $roles,
            'user_role' => $user->role?->nombre,
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Respuesta según el tipo de request
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tu rol no permite acceder a esta funcionalidad.',
                'required_roles' => $roles,
                'user_role' => $user->role?->nombre
            ], 403);
        }

        abort(403, 'Tu rol no permite acceder a esta sección.');
    }
} 
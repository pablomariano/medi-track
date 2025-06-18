<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckAssignment
{
    /**
     * Maneja una petición entrante.
     * 
     * Verifica si el usuario actual tiene asignación válida con el paciente especificado.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $type  Tipo de asignación: 'medico', 'cuidador', 'apoderado'
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $type = null): Response
    {
        $user = auth()->user();

        // Admin siempre puede acceder
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Obtener ID del paciente desde la ruta
        $pacienteId = $this->getPacienteIdFromRoute($request);

        if (!$pacienteId) {
            Log::warning('CheckAssignment: No se encontró paciente_id en la ruta', [
                'route' => $request->route()->getName(),
                'params' => $request->route()->parameters(),
                'user_id' => $user->id ?? null
            ]);

            return $this->unauthorizedResponse($request, 'ID de paciente no encontrado');
        }

        // Verificar la asignación según el tipo especificado o el rol del usuario
        $hasAssignment = $this->checkAssignment($user, $pacienteId, $type);

        if (!$hasAssignment) {
            Log::warning('CheckAssignment: Usuario sin asignación válida', [
                'user_id' => $user->id,
                'paciente_id' => $pacienteId,
                'type' => $type,
                'user_role' => $user->role?->nombre,
                'route' => $request->route()->getName()
            ]);

            return $this->unauthorizedResponse($request, 'No tienes asignación con este paciente');
        }

        Log::info('CheckAssignment: Acceso autorizado por asignación', [
            'user_id' => $user->id,
            'paciente_id' => $pacienteId,
            'type' => $type ?: $user->role?->nombre,
            'route' => $request->route()->getName()
        ]);

        return $next($request);
    }

    /**
     * Extrae el ID del paciente desde los parámetros de la ruta
     */
    private function getPacienteIdFromRoute(Request $request): ?int
    {
        $params = $request->route()->parameters();

        // Intentar diferentes nombres de parámetros
        $possibleKeys = ['paciente', 'paciente_id', 'id'];

        foreach ($possibleKeys as $key) {
            if (isset($params[$key])) {
                return (int) $params[$key];
            }
        }

        // Buscar en la query string
        if ($request->has('paciente_id')) {
            return (int) $request->get('paciente_id');
        }

        // Si estamos en una ruta anidada, buscar en el request
        if ($request->has('paciente')) {
            return (int) $request->get('paciente');
        }

        return null;
    }

    /**
     * Verificar la asignación del usuario con el paciente
     */
    private function checkAssignment($user, int $pacienteId, ?string $type): bool
    {
        if (!$user) {
            return false;
        }

        // Si no se especifica tipo, usar el rol del usuario
        $checkType = $type ?: $user->role?->nombre;

        switch ($checkType) {
            case 'medico':
                return $this->checkMedicoAssignment($user, $pacienteId);
            
            case 'cuidador':
                return $this->checkCuidadorAssignment($user, $pacienteId);
            
            case 'apoderado':
                return $this->checkApoderadoAssignment($user, $pacienteId);
            
            case 'paciente':
                return $this->checkPacienteOwnership($user, $pacienteId);
            
            default:
                return false;
        }
    }

    /**
     * Verificar asignación médico-paciente
     */
    private function checkMedicoAssignment($user, int $pacienteId): bool
    {
        return DB::table('paciente_medicos')
            ->where('paciente_id', $pacienteId)
            ->where('medico_usuario_id', $user->id)
            ->where(function($query) {
                $query->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>', now());
            })
            ->exists();
    }

    /**
     * Verificar asignación cuidador-paciente
     */
    private function checkCuidadorAssignment($user, int $pacienteId): bool
    {
        return DB::table('paciente_cuidadores')
            ->where('paciente_id', $pacienteId)
            ->where('cuidador_usuario_id', $user->id)
            ->where('activo', true)
            ->where(function($query) {
                $query->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>', now());
            })
            ->exists();
    }

    /**
     * Verificar asignación apoderado-paciente
     */
    private function checkApoderadoAssignment($user, int $pacienteId): bool
    {
        return DB::table('paciente_apoderados')
            ->where('paciente_id', $pacienteId)
            ->where('apoderado_usuario_id', $user->id)
            ->exists();
    }

    /**
     * Verificar si el usuario es el mismo paciente
     */
    private function checkPacienteOwnership($user, int $pacienteId): bool
    {
        return DB::table('pacientes')
            ->where('id', $pacienteId)
            ->where('usuario_id', $user->id)
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
                'code' => 'ASSIGNMENT_REQUIRED',
                'message' => 'No tienes asignación válida para acceder a este recurso'
            ], 403);
        }

        return redirect()->back()
            ->withErrors(['assignment' => $message])
            ->with('error', 'No tienes asignación para acceder a este recurso');
    }
} 
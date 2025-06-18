<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditService;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo auditar en producción o cuando esté habilitado
        if (!$this->debeAuditar($request)) {
            return $response;
        }

        // Determinar si es una acción que requiere auditoría
        if ($this->esAccionAuditable($request)) {
            $this->registrarAcceso($request, $response);
        }

        return $response;
    }

    /**
     * Determinar si se debe auditar la request
     */
    private function debeAuditar(Request $request): bool
    {
        // No auditar en testing a menos que sea explícito
        if (app()->environment('testing') && !config('audit.enable_in_testing', false)) {
            return false;
        }

        // No auditar requests de assets
        if ($this->esRequestDeAsset($request)) {
            return false;
        }

        // No auditar requests AJAX de polling frecuente
        if ($this->esPollingAjax($request)) {
            return false;
        }

        return true;
    }

    /**
     * Determinar si es una acción que requiere auditoría
     */
    private function esAccionAuditable(Request $request): bool
    {
        $method = $request->method();
        $path = $request->path();

        // Siempre auditar métodos que modifican datos
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // Auditar accesos a rutas sensibles (GET)
        $rutasSensibles = [
            'pacientes',
            'personal-medico',
            'tratamientos',
            'administraciones',
            'asignaciones-medicos',
            'asignaciones-cuidadores',
            'usuarios',
            'roles',
            'permisos',
            'audit-logs'
        ];

        foreach ($rutasSensibles as $ruta) {
            if (str_contains($path, $ruta)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registrar el acceso
     */
    private function registrarAcceso(Request $request, Response $response): void
    {
        try {
            $contexto = [
                'metodo' => $request->method(),
                'path' => $request->path(),
                'parametros_get' => $request->query(),
                'status_code' => $response->getStatusCode(),
                'tiempo_respuesta' => $this->getTiempoRespuesta($request),
            ];

            // Agregar parámetros POST/PUT solo si no son sensibles
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
                $contexto['parametros_post'] = $this->filtrarParametrosSensibles($request->all());
            }

            // Determinar la acción específica
            $accion = $this->determinarAccion($request);
            
            // Determinar severidad
            $severidad = $this->determinarSeveridad($request, $response);

            AuditService::log($accion, [
                'contexto' => $contexto,
                'severidad' => $severidad
            ]);

        } catch (\Exception $e) {
            // Log el error pero no fallar la request
            \Log::error('Error en AuditLogger: ' . $e->getMessage());
        }
    }

    /**
     * Determinar la acción específica basada en la request
     */
    private function determinarAccion(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Mapear acciones específicas
        if ($method === 'GET') {
            return 'access';
        }

        if ($method === 'POST') {
            if (str_contains($path, 'login')) {
                return 'login';
            }
            return 'create';
        }

        if (in_array($method, ['PUT', 'PATCH'])) {
            return 'update';
        }

        if ($method === 'DELETE') {
            return 'delete';
        }

        return 'access';
    }

    /**
     * Determinar severidad basada en la request y response
     */
    private function determinarSeveridad(Request $request, Response $response): string
    {
        $statusCode = $response->getStatusCode();
        $path = $request->path();

        // Errores son siempre de alta severidad
        if ($statusCode >= 400) {
            return $statusCode >= 500 ? 'critical' : 'high';
        }

        // Rutas críticas
        $rutasCriticas = [
            'usuarios',
            'roles',
            'permisos',
            'login',
            'logout'
        ];

        foreach ($rutasCriticas as $ruta) {
            if (str_contains($path, $ruta)) {
                return 'high';
            }
        }

        // Rutas médicas importantes
        $rutasMedicas = [
            'pacientes',
            'tratamientos',
            'administraciones',
            'asignaciones'
        ];

        foreach ($rutasMedicas as $ruta) {
            if (str_contains($path, $ruta)) {
                return 'medium';
            }
        }

        return 'low';
    }

    /**
     * Verificar si es request de asset estático
     */
    private function esRequestDeAsset(Request $request): bool
    {
        $path = $request->path();
        $extensiones = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2'];

        foreach ($extensiones as $ext) {
            if (str_ends_with($path, $ext)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si es polling AJAX frecuente
     */
    private function esPollingAjax(Request $request): bool
    {
        if (!$request->ajax()) {
            return false;
        }

        $rutasPolling = [
            'notifications/count',
            'dashboard/live',
            'health-check',
            'heartbeat'
        ];

        $path = $request->path();
        foreach ($rutasPolling as $ruta) {
            if (str_contains($path, $ruta)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filtrar parámetros sensibles
     */
    private function filtrarParametrosSensibles(array $parametros): array
    {
        $camposSensibles = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            '_token',
            'api_key',
            'secret'
        ];

        foreach ($camposSensibles as $campo) {
            if (isset($parametros[$campo])) {
                $parametros[$campo] = '[FILTRADO]';
            }
        }

        return $parametros;
    }

    /**
     * Obtener tiempo de respuesta (si está disponible)
     */
    private function getTiempoRespuesta(Request $request): ?float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }

        return null;
    }
} 
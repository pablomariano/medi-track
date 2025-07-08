<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $filtros = $request->only([
            'usuario_id', 'accion', 'tabla', 'severidad',
            'fecha_inicio', 'fecha_fin', 'busqueda', 'page'
        ]);

        $query = AuditService::buscar($filtros);

        $logs = $query->paginate(25)->withQueryString();

        $estadisticas = AuditService::getEstadisticas(30);

        return Inertia::render('Audit/Index', [
            'logs' => $logs,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros,
            'usuarios_disponibles' => $this->getUsuariosDisponibles(),
            'acciones_disponibles' => $this->getAccionesDisponibles(),
            'tablas_disponibles' => $this->getTablasDisponibles(),
            'severidades_disponibles' => $this->getSeveridadesDisponibles()
        ]);
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('usuario');

        return Inertia::render('Audit/Show', [
            'log' => $auditLog,
            'cambios_detallados' => $auditLog->resumen_cambios
        ]);
    }

    /**
     * Export audit logs for compliance.
     */
    public function exportCompliance(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);

        $datos = AuditService::generarReporteCompliance($fechaInicio, $fechaFin);

        $nombreArchivo = "reporte_auditoria_{$fechaInicio->format('Y-m-d')}_{$fechaFin->format('Y-m-d')}.csv";

        return response()->streamDownload(function () use ($datos) {
            $handle = fopen('php://output', 'w');
            
            // Encabezados CSV
            fputcsv($handle, [
                'Fecha y Hora',
                'Usuario',
                'Acción',
                'Tabla',
                'ID Registro',
                'IP',
                'Severidad',
                'Cambios'
            ]);

            // Datos
            foreach ($datos as $fila) {
                fputcsv($handle, [
                    $fila['fecha_hora'],
                    $fila['usuario'],
                    $fila['accion'],
                    $fila['tabla'] ?? '',
                    $fila['registro_id'] ?? '',
                    $fila['ip'],
                    $fila['severidad'],
                    $fila['cambios'] ?? ''
                ]);
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\""
        ]);
    }

    /**
     * Get dashboard data for audit overview.
     */
    public function dashboard()
    {
        $estadisticas = AuditService::getEstadisticas(30);
        $actividadSospechosa = AuditService::detectarActividadSospechosa();
        
        // Logs críticos recientes
        $logsCriticos = AuditLog::criticos()
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Accesos recientes por IP
        $accesosPorIp = AuditLog::selectRaw('ip_address, COUNT(*) as total, MAX(created_at) as ultimo_acceso')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('ip_address')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Audit/Dashboard', [
            'estadisticas' => $estadisticas,
            'actividad_sospechosa' => $actividadSospechosa,
            'logs_criticos' => $logsCriticos,
            'accesos_por_ip' => $accesosPorIp
        ]);
    }

    /**
     * Get user activity history.
     */
    public function userActivity(Request $request, $userId)
    {
        $limite = $request->get('limite', 50);
        $actividad = AuditService::getActividadUsuario($userId, $limite);

        return response()->json([
            'actividad' => $actividad,
            'total' => $actividad->count()
        ]);
    }

    /**
     * Clean old audit logs.
     */
    public function cleanOldLogs(Request $request)
    {
        $request->validate([
            'dias_retencion' => 'required|integer|min:30|max:3650' // Entre 30 días y 10 años
        ]);

        $diasRetencion = $request->dias_retencion;
        $eliminados = AuditService::limpiarLogsAntiguos($diasRetencion);

        // Registrar la limpieza como acción de auditoría
        AuditService::log('delete', [
            'contexto' => [
                'tipo' => 'limpieza_logs',
                'logs_eliminados' => $eliminados,
                'dias_retencion' => $diasRetencion
            ],
            'severidad' => 'high'
        ]);

        return response()->json([
            'message' => "Se eliminaron {$eliminados} registros de auditoría anteriores a {$diasRetencion} días.",
            'logs_eliminados' => $eliminados
        ]);
    }

    /**
     * Get real-time audit statistics.
     */
    public function liveStats()
    {
        $hoy = Carbon::today();
        
        $stats = [
            'acciones_hoy' => AuditLog::whereDate('created_at', $hoy)->count(),
            'acciones_criticas_hoy' => AuditLog::criticos()->whereDate('created_at', $hoy)->count(),
            'usuarios_activos_hoy' => AuditLog::whereDate('created_at', $hoy)
                ->whereNotNull('usuario_id')
                ->distinct('usuario_id')
                ->count(),
            'ultima_accion' => AuditLog::with('usuario')
                ->orderBy('created_at', 'desc')
                ->first(),
            'acciones_ultima_hora' => AuditLog::where('created_at', '>=', Carbon::now()->subHour())->count()
        ];

        return response()->json($stats);
    }

    /**
     * Search audit logs with advanced filters.
     */
    public function search(Request $request)
    {
        $filtros = $request->validate([
            'busqueda' => 'nullable|string|max:255',
            'usuario_id' => 'nullable|exists:users,id',
            'accion' => 'nullable|string',
            'tabla' => 'nullable|string',
            'severidad' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'ip_address' => 'nullable|string',
            'registro_id' => 'nullable|integer'
        ]);

        $query = AuditService::buscar($filtros);

        if ($request->ip_address) {
            $query->where('ip_address', 'like', "%{$request->ip_address}%");
        }

        if ($request->registro_id) {
            $query->where('registro_id', $request->registro_id);
        }

        $resultados = $query->paginate(25)->withQueryString();

        return response()->json($resultados);
    }

    /**
     * Get available users for filtering.
     */
    private function getUsuariosDisponibles()
    {
        return AuditLog::selectRaw('DISTINCT usuario_id, created_by_name')
            ->whereNotNull('usuario_id')
            ->orderBy('created_by_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->usuario_id,
                    'name' => $item->created_by_name
                ];
            });
    }

    /**
     * Get available actions for filtering.
     */
    private function getAccionesDisponibles()
    {
        return [
            ['value' => 'create', 'label' => 'Creación'],
            ['value' => 'update', 'label' => 'Actualización'],
            ['value' => 'delete', 'label' => 'Eliminación'],
            ['value' => 'access', 'label' => 'Acceso'],
            ['value' => 'login', 'label' => 'Inicio de sesión'],
            ['value' => 'logout', 'label' => 'Cierre de sesión'],
            ['value' => 'permission_grant', 'label' => 'Otorgamiento de permiso'],
            ['value' => 'permission_revoke', 'label' => 'Revocación de permiso']
        ];
    }

    /**
     * Get available tables for filtering.
     */
    private function getTablasDisponibles()
    {
        return AuditLog::selectRaw('DISTINCT tabla_afectada')
            ->whereNotNull('tabla_afectada')
            ->orderBy('tabla_afectada')
            ->pluck('tabla_afectada')
            ->map(function ($tabla) {
                return [
                    'value' => $tabla,
                    'label' => ucfirst(str_replace('_', ' ', $tabla))
                ];
            });
    }

    /**
     * Get available severities for filtering.
     */
    private function getSeveridadesDisponibles()
    {
        return [
            ['value' => 'low', 'label' => 'Baja'],
            ['value' => 'medium', 'label' => 'Media'],
            ['value' => 'high', 'label' => 'Alta'],
            ['value' => 'critical', 'label' => 'Crítica']
        ];
    }
} 
<?php

namespace App\Services;

use App\Models\AuditLog;
use Carbon\Carbon;

class AuditService
{
    /**
     * Registrar una acción de auditoría
     */
    public static function log(string $accion, array $opciones = [])
    {
        return AuditLog::registrar([
            'accion' => $accion,
            'tabla_afectada' => $opciones['tabla'] ?? null,
            'registro_id' => $opciones['registro_id'] ?? null,
            'datos_anteriores' => $opciones['datos_anteriores'] ?? null,
            'datos_nuevos' => $opciones['datos_nuevos'] ?? null,
            'contexto_adicional' => $opciones['contexto'] ?? null,
            'severidad' => $opciones['severidad'] ?? 'medium',
        ]);
    }

    /**
     * Registrar acceso a un recurso específico
     */
    public static function logAccess(string $recurso, $registroId = null, array $contexto = [])
    {
        return self::log('access', [
            'tabla' => $recurso,
            'registro_id' => $registroId,
            'contexto' => array_merge($contexto, ['tipo' => 'acceso_recurso']),
            'severidad' => 'low'
        ]);
    }

    /**
     * Registrar login de usuario
     */
    public static function logLogin($usuario, array $contexto = [])
    {
        return self::log('login', [
            'contexto' => array_merge($contexto, [
                'tipo' => 'autenticacion',
                'usuario_id' => $usuario->id,
                'usuario_nombre' => $usuario->name,
                'rol' => $usuario->role ? $usuario->role->nombre : null
            ]),
            'severidad' => 'medium'
        ]);
    }

    /**
     * Registrar logout de usuario
     */
    public static function logLogout($usuario = null, array $contexto = [])
    {
        $user = $usuario ?? auth()->user();
        
        return self::log('logout', [
            'contexto' => array_merge($contexto, [
                'tipo' => 'autenticacion',
                'usuario_id' => $user ? $user->id : null,
                'usuario_nombre' => $user ? $user->name : 'Desconocido'
            ]),
            'severidad' => 'low'
        ]);
    }

    /**
     * Registrar creación de modelo
     */
    public static function logCreation($modelo, array $contexto = [])
    {
        $tabla = $modelo->getTable();
        $datos = $modelo->getAttributes();

        // Limpiar datos sensibles
        $datosSeguros = self::limpiarDatosSensibles($datos);

        return self::log('create', [
            'tabla' => $tabla,
            'registro_id' => $modelo->getKey(),
            'datos_nuevos' => $datosSeguros,
            'contexto' => array_merge($contexto, [
                'tipo' => 'crud',
                'modelo_clase' => get_class($modelo)
            ]),
            'severidad' => self::determinarSeveridadPorTabla($tabla, 'create')
        ]);
    }

    /**
     * Registrar actualización de modelo
     */
    public static function logUpdate($modelo, array $datosOriginales, array $contexto = [])
    {
        $tabla = $modelo->getTable();
        $datosNuevos = $modelo->getAttributes();

        // Limpiar datos sensibles
        $datosSegurosOriginales = self::limpiarDatosSensibles($datosOriginales);
        $datosSegurosNuevos = self::limpiarDatosSensibles($datosNuevos);

        return self::log('update', [
            'tabla' => $tabla,
            'registro_id' => $modelo->getKey(),
            'datos_anteriores' => $datosSegurosOriginales,
            'datos_nuevos' => $datosSegurosNuevos,
            'contexto' => array_merge($contexto, [
                'tipo' => 'crud',
                'modelo_clase' => get_class($modelo),
                'campos_modificados' => array_keys($modelo->getDirty())
            ]),
            'severidad' => self::determinarSeveridadPorTabla($tabla, 'update')
        ]);
    }

    /**
     * Registrar eliminación de modelo
     */
    public static function logDeletion($modelo, array $contexto = [])
    {
        $tabla = $modelo->getTable();
        $datos = $modelo->getAttributes();

        // Limpiar datos sensibles
        $datosSeguros = self::limpiarDatosSensibles($datos);

        return self::log('delete', [
            'tabla' => $tabla,
            'registro_id' => $modelo->getKey(),
            'datos_anteriores' => $datosSeguros,
            'contexto' => array_merge($contexto, [
                'tipo' => 'crud',
                'modelo_clase' => get_class($modelo)
            ]),
            'severidad' => self::determinarSeveridadPorTabla($tabla, 'delete')
        ]);
    }

    /**
     * Registrar otorgamiento de permiso
     */
    public static function logPermissionGrant($usuario, string $permiso, array $contexto = [])
    {
        return self::log('permission_grant', [
            'contexto' => array_merge($contexto, [
                'tipo' => 'autorizacion',
                'usuario_objetivo_id' => $usuario->id,
                'usuario_objetivo_nombre' => $usuario->name,
                'permiso_otorgado' => $permiso
            ]),
            'severidad' => 'high'
        ]);
    }

    /**
     * Registrar revocación de permiso
     */
    public static function logPermissionRevoke($usuario, string $permiso, array $contexto = [])
    {
        return self::log('permission_revoke', [
            'contexto' => array_merge($contexto, [
                'tipo' => 'autorizacion',
                'usuario_objetivo_id' => $usuario->id,
                'usuario_objetivo_nombre' => $usuario->name,
                'permiso_revocado' => $permiso
            ]),
            'severidad' => 'high'
        ]);
    }

    /**
     * Obtener estadísticas de auditoría
     */
    public static function getEstadisticas($dias = 30)
    {
        $fechaInicio = Carbon::now()->subDays($dias);

        return [
            'total_acciones' => AuditLog::where('created_at', '>=', $fechaInicio)->count(),
            'acciones_criticas' => AuditLog::criticos()->where('created_at', '>=', $fechaInicio)->count(),
            'usuarios_activos' => AuditLog::where('created_at', '>=', $fechaInicio)
                ->whereNotNull('usuario_id')
                ->distinct('usuario_id')
                ->count(),
            'acciones_por_dia' => AuditLog::estadisticasPorDia($dias)->get()->toArray(),
            'acciones_por_tipo' => AuditLog::estadisticasPorAccion($dias)->get()->toArray(),
            'usuarios_mas_activos' => AuditLog::usuariosMasActivos($dias, 5)->get()->toArray(),
            'tablas_mas_modificadas' => AuditLog::selectRaw('tabla_afectada, COUNT(*) as total')
                ->where('created_at', '>=', $fechaInicio)
                ->whereNotNull('tabla_afectada')
                ->groupBy('tabla_afectada')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->toArray()
        ];
    }

    /**
     * Buscar en logs de auditoría
     */
    public static function buscar(array $filtros = [])
    {
        $query = AuditLog::with('usuario');

        if (isset($filtros['usuario_id'])) {
            $query->porUsuario($filtros['usuario_id']);
        }

        if (isset($filtros['accion'])) {
            $query->porAccion($filtros['accion']);
        }

        if (isset($filtros['tabla'])) {
            $query->porTabla($filtros['tabla']);
        }

        if (isset($filtros['severidad'])) {
            $query->porSeveridad($filtros['severidad']);
        }

        if (isset($filtros['fecha_inicio'])) {
            $query->where('created_at', '>=', $filtros['fecha_inicio']);
        }

        if (isset($filtros['fecha_fin'])) {
            $query->where('created_at', '<=', $filtros['fecha_fin']);
        }

        if (isset($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $query->where(function($q) use ($busqueda) {
                $q->where('created_by_name', 'like', "%{$busqueda}%")
                  ->orWhere('tabla_afectada', 'like', "%{$busqueda}%")
                  ->orWhere('ip_address', 'like', "%{$busqueda}%")
                  ->orWhereJsonContains('contexto_adicional', $busqueda);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Limpiar datos sensibles antes de guardar en logs
     */
    private static function limpiarDatosSensibles(array $datos)
    {
        $camposSensibles = [
            'password',
            'password_confirmation',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes'
        ];

        foreach ($camposSensibles as $campo) {
            if (isset($datos[$campo])) {
                $datos[$campo] = '[OCULTO]';
            }
        }

        return $datos;
    }

    /**
     * Determinar severidad basada en la tabla y acción
     */
    private static function determinarSeveridadPorTabla(string $tabla, string $accion)
    {
        // Tablas críticas que requieren alta severidad
        $tablasCriticas = [
            'users',
            'roles',
            'permisos',
            'rol_permisos'
        ];

        // Tablas médicas importantes
        $tablasMedicas = [
            'pacientes',
            'personal_medico',
            'tratamientos',
            'administraciones',
            'paciente_medicos',
            'paciente_cuidadores'
        ];

        if (in_array($tabla, $tablasCriticas)) {
            return $accion === 'delete' ? 'critical' : 'high';
        }

        if (in_array($tabla, $tablasMedicas)) {
            return $accion === 'delete' ? 'high' : 'medium';
        }

        return 'low';
    }

    /**
     * Generar reporte de compliance
     */
    public static function generarReporteCompliance($fechaInicio, $fechaFin)
    {
        return AuditLog::exportarParaCompliance($fechaInicio, $fechaFin);
    }

    /**
     * Limpiar logs antiguos
     */
    public static function limpiarLogsAntiguos($diasRetencion = 90)
    {
        return AuditLog::limpiarAntiguos($diasRetencion);
    }

    /**
     * Obtener actividad reciente de un usuario
     */
    public static function getActividadUsuario($usuarioId, $limite = 50)
    {
        return AuditLog::with('usuario')
            ->porUsuario($usuarioId)
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Verificar actividad sospechosa
     */
    public static function detectarActividadSospechosa($dias = 1)
    {
        $fechaInicio = Carbon::now()->subDays($dias);
        
        // Múltiples intentos de acceso fallidos
        $intentosFallidos = AuditLog::where('created_at', '>=', $fechaInicio)
            ->where('accion', 'login')
            ->whereJsonContains('contexto_adicional->resultado', 'fallido')
            ->selectRaw('ip_address, COUNT(*) as intentos')
            ->groupBy('ip_address')
            ->having('intentos', '>', 5)
            ->get();

        // Accesos fuera de horario normal (ejemplo: 22:00 - 6:00)
        $accesosFueraHorario = AuditLog::where('created_at', '>=', $fechaInicio)
            ->whereRaw('HOUR(created_at) NOT BETWEEN 6 AND 22')
            ->where('accion', 'access')
            ->with('usuario')
            ->get();

        // Muchas acciones en poco tiempo
        $actividadIntensa = AuditLog::where('created_at', '>=', Carbon::now()->subHour())
            ->selectRaw('usuario_id, COUNT(*) as acciones')
            ->groupBy('usuario_id')
            ->having('acciones', '>', 50)
            ->with('usuario')
            ->get();

        return [
            'intentos_fallidos' => $intentosFallidos,
            'accesos_fuera_horario' => $accesosFueraHorario,
            'actividad_intensa' => $actividadIntensa
        ];
    }
} 
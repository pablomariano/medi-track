<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla_afectada',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
        'metodo_http',
        'url',
        'ruta',
        'contexto_adicional',
        'session_id',
        'severidad',
        'created_by_name'
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'contexto_adicional' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false; // Solo usamos created_at

    // Relación con el usuario que realizó la acción
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes para consultas frecuentes
    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($dias));
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla_afectada', $tabla);
    }

    public function scopePorSeveridad($query, $severidad)
    {
        return $query->where('severidad', $severidad);
    }

    public function scopeCriticos($query)
    {
        return $query->where('severidad', 'critical');
    }

    public function scopeAltos($query)
    {
        return $query->whereIn('severidad', ['high', 'critical']);
    }

    // Métodos de análisis
    public function scopeEstadisticasPorDia($query, $dias = 30)
    {
        return $query->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::now()->subDays($dias))
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc');
    }

    public function scopeEstadisticasPorAccion($query, $dias = 30)
    {
        return $query->selectRaw('accion, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::now()->subDays($dias))
            ->groupBy('accion')
            ->orderBy('total', 'desc');
    }

    public function scopeUsuariosMasActivos($query, $dias = 30, $limit = 10)
    {
        return $query->selectRaw('usuario_id, created_by_name, COUNT(*) as total_acciones')
            ->where('created_at', '>=', Carbon::now()->subDays($dias))
            ->whereNotNull('usuario_id')
            ->groupBy('usuario_id', 'created_by_name')
            ->orderBy('total_acciones', 'desc')
            ->limit($limit);
    }

    // Métodos de utilidad
    public function getDescripcionAccionAttribute()
    {
        $acciones = [
            'create' => 'Creación',
            'update' => 'Actualización',
            'delete' => 'Eliminación',
            'access' => 'Acceso',
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'permission_grant' => 'Otorgamiento de permiso',
            'permission_revoke' => 'Revocación de permiso'
        ];

        return $acciones[$this->accion] ?? $this->accion;
    }

    public function getSeveridadColorAttribute()
    {
        $colores = [
            'low' => 'text-gray-600',
            'medium' => 'text-blue-600',
            'high' => 'text-orange-600',
            'critical' => 'text-red-600'
        ];

        return $colores[$this->severidad] ?? 'text-gray-600';
    }

    public function getSeveridadBadgeAttribute()
    {
        $badges = [
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800'
        ];

        return $badges[$this->severidad] ?? 'bg-gray-100 text-gray-800';
    }

    public function getTiempoTranscurridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Método para buscar cambios específicos
    public function scopeBuscarCambios($query, $campo, $valor)
    {
        return $query->where(function($q) use ($campo, $valor) {
            $q->whereJsonContains('datos_anteriores->' . $campo, $valor)
              ->orWhereJsonContains('datos_nuevos->' . $campo, $valor);
        });
    }

    // Método para obtener el resumen de cambios
    public function getResumenCambiosAttribute()
    {
        if (!$this->datos_anteriores || !$this->datos_nuevos) {
            return null;
        }

        $cambios = [];
        $anteriores = $this->datos_anteriores;
        $nuevos = $this->datos_nuevos;

        foreach ($nuevos as $campo => $valorNuevo) {
            $valorAnterior = $anteriores[$campo] ?? null;
            
            if ($valorAnterior !== $valorNuevo) {
                $cambios[] = [
                    'campo' => $campo,
                    'anterior' => $valorAnterior,
                    'nuevo' => $valorNuevo
                ];
            }
        }

        return $cambios;
    }

    // Método estático para registrar auditoría
    public static function registrar(array $datos)
    {
        $request = request();
        $user = auth()->user();

        // Manejar sesión de forma segura
        $sessionId = null;
        try {
            if ($request && $request->hasSession()) {
                $sessionId = $request->session()->getId();
            }
        } catch (\Exception $e) {
            // Ignorar errores de sesión en testing/console
        }

        return self::create([
            'usuario_id' => $user ? $user->id : null,
            'created_by_name' => $user ? $user->name : 'Sistema',
            'accion' => $datos['accion'],
            'tabla_afectada' => $datos['tabla_afectada'] ?? null,
            'registro_id' => $datos['registro_id'] ?? null,
            'datos_anteriores' => $datos['datos_anteriores'] ?? null,
            'datos_nuevos' => $datos['datos_nuevos'] ?? null,
            'ip_address' => $request ? $request->ip() : '127.0.0.1',
            'user_agent' => $request ? $request->userAgent() : 'Sistema',
            'metodo_http' => $request ? $request->method() : null,
            'url' => $request ? $request->fullUrl() : null,
            'ruta' => $request && $request->route() ? $request->route()->getName() : null,
            'contexto_adicional' => $datos['contexto_adicional'] ?? null,
            'session_id' => $sessionId,
            'severidad' => $datos['severidad'] ?? 'medium',
            'created_at' => Carbon::now()
        ]);
    }

    // Método para limpiar logs antiguos
    public static function limpiarAntiguos($diasRetencion = 90)
    {
        $fechaLimite = Carbon::now()->subDays($diasRetencion);
        
        return self::where('created_at', '<', $fechaLimite)->delete();
    }

    // Método para exportar logs
    public static function exportarParaCompliance($fechaInicio, $fechaFin)
    {
        return self::with('usuario')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'fecha_hora' => $log->created_at->format('Y-m-d H:i:s'),
                    'usuario' => $log->created_by_name,
                    'accion' => $log->descripcion_accion,
                    'tabla' => $log->tabla_afectada,
                    'registro_id' => $log->registro_id,
                    'ip' => $log->ip_address,
                    'severidad' => $log->severidad,
                    'cambios' => $log->resumen_cambios ? json_encode($log->resumen_cambios, JSON_UNESCAPED_UNICODE) : null
                ];
            });
    }
} 
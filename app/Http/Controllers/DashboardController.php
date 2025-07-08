<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Administracion;
use App\Models\EstadisticaConsumo;
use App\Models\Alerta;
use App\Models\User;
use App\Models\Role;
use App\Models\Permiso;
use App\Models\Medicamento;
use App\Services\TemporalAdherenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected TemporalAdherenceService $temporalService;

    public function __construct(TemporalAdherenceService $temporalService)
    {
        $this->temporalService = $temporalService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Verificar si el usuario necesita onboarding
        if ($this->userNeedsOnboarding($user)) {
            return redirect()->route('welcome.new-user');
        }

        // Obtener estadísticas generales
        $estadisticasGenerales = $this->obtenerEstadisticasGenerales();
        
        // Obtener datos de adherencia de los últimos 7 días
        $adherenciaUltimos7Dias = $this->obtenerAdherenciaUltimos7Dias();
        
        // Obtener actividad reciente
        $actividadReciente = $this->obtenerActividadReciente();
        
        return Inertia::render('dashboard', [
            'estadisticasGenerales' => $estadisticasGenerales,
            'adherenciaUltimos7Dias' => $adherenciaUltimos7Dias,
            'actividadReciente' => $actividadReciente
        ]);
    }
    
    /**
     * Verificar si el usuario necesita onboarding
     */
    private function userNeedsOnboarding($user): bool
    {
        // Si ya completó el onboarding
        if (session('onboarding_completed')) {
            return false;
        }

        // Si es un usuario recién creado (menos de 7 días)
        $userAge = $user->created_at->diffInDays(now());
        if ($userAge > 7) {
            return false;
        }

        // Verificar si ha completado acciones básicas
        $hasBasicData = $this->userHasBasicData($user);
        
        return !$hasBasicData;
    }

    /**
     * Verificar si el usuario tiene datos básicos
     */
    private function userHasBasicData($user): bool
    {
        // Verificar según el rol
        switch ($user->role->nombre ?? 'paciente') {
            case 'paciente':
                // Verificar si tiene paciente asociado o tratamientos
                return $user->pacientes()->exists();
                
            case 'medico':
                // Verificar si tiene perfil médico completo
                return $user->personalMedico()->exists();
                
            case 'cuidador':
                // Verificar si tiene perfil de cuidador
                return $user->cuidadores()->exists();
                
            case 'apoderado':
                // Verificar si tiene perfil de apoderado
                return $user->apoderados()->exists();
                
            default:
                return false;
        }
    }
    
    private function obtenerEstadisticasGenerales()
    {
        $pacientesActivos = Paciente::whereHas('tratamientos', function($query) {
            $query->where('estado', 'Activo');
        })->count();
        
        $tratamientosActivos = Tratamiento::where('estado', 'Activo')->count();
        
        $alertasPendientes = Alerta::where('revisada', false)->count();
        
        // Calcular adherencia media real de los últimos 30 días
        $adherenciaMedia = $this->calcularAdherenciaMediaReal();
        
        return [
            'pacientes_activos' => $pacientesActivos,
            'tratamientos_activos' => $tratamientosActivos,
            'adherencia_media' => round($adherenciaMedia, 1),
            'alertas_pendientes' => $alertasPendientes
        ];
    }
    
    private function calcularAdherenciaMediaReal()
    {
        $fechaInicio = Carbon::now()->subDays(30);
        
        // Obtener todas las administraciones programadas de los últimos 30 días
        $administraciones = Administracion::whereNotNull('fecha_hora_programada')
            ->where('fecha_hora_programada', '>=', $fechaInicio)
            ->get();
            
        if ($administraciones->isEmpty()) {
            return 0;
        }
        
        $totalProgramadas = $administraciones->count();
        $totalExitosas = $administraciones->whereIn('estado', ['Administrada', 'Tardía'])->count();
        
        return $totalProgramadas > 0 ? ($totalExitosas / $totalProgramadas) * 100 : 0;
    }
    
    private function obtenerAdherenciaUltimos7Dias()
    {
        $datos = [];
        $hoy = Carbon::now();
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = $hoy->copy()->subDays($i);
            $fechaStr = $fecha->format('Y-m-d');
            
            // Obtener administraciones programadas del día
            $administracionesDia = Administracion::whereDate('fecha_hora_programada', $fecha)
                ->whereNotNull('fecha_hora_programada')
                ->get();
            
            $dosisProgamadas = $administracionesDia->count();
            $dosisAdministradas = $administracionesDia->where('estado', 'Administrada')->count();
            $dosisTardias = $administracionesDia->where('estado', 'Tardía')->count();
            $dosisOmitidas = $administracionesDia->where('estado', 'Omitida')->count();
            
            // Métricas temporales avanzadas
            $administracionesConMetricas = $administracionesDia->where('score_puntualidad', '>', 0);
            $dosisPuntuales = $administracionesDia->where('categoria_temporal', 'puntual')->count();
            $dosisTempranas = $administracionesDia->whereIn('categoria_temporal', ['temprano', 'muy_temprano'])->count();
            $dosisTardiasTempo = $administracionesDia->whereIn('categoria_temporal', ['tardio', 'muy_tardio'])->count();
            $scorePuntualidadPromedio = $administracionesConMetricas->avg('score_puntualidad') ?? 0;
            
            // Métricas de tiempo específicas
            $tiempoPromedioAdelanto = $administracionesDia->where('minutos_adelanto', '>', 0)->avg('minutos_adelanto') ?? 0;
            $tiempoPromedioRetraso = $administracionesDia->where('minutos_retraso', '>', 0)->avg('minutos_retraso') ?? 0;
            
            // Calcular variabilidad del día (desviación estándar de scores)
            $scoresPuntualidad = $administracionesConMetricas->pluck('score_puntualidad')->toArray();
            $variabilidadDia = $this->calcularDesviacionEstandar($scoresPuntualidad);
            
            // Calcular adherencia real (administradas + tardías / programadas)
            $adherencia = $dosisProgamadas > 0 
                ? round((($dosisAdministradas + $dosisTardias) / $dosisProgamadas) * 100, 1)
                : 0;
            
            // Traducir manualmente los nombres de los días a español
            $diasSemana = [
                'Monday' => 'Lun',
                'Tuesday' => 'Mar', 
                'Wednesday' => 'Mié',
                'Thursday' => 'Jue',
                'Friday' => 'Vie',
                'Saturday' => 'Sáb',
                'Sunday' => 'Dom'
            ];
            
            $dayNameEn = $fecha->format('l'); // Nombre completo en inglés
            $dayName = $diasSemana[$dayNameEn] ?? $dayNameEn;
            
            $datos[] = [
                'day' => $dayName,
                'fullDate' => $fechaStr,
                'adherencia' => $adherencia,
                'dosis_administradas' => $dosisAdministradas + $dosisTardias,
                'dosis_programadas' => $dosisProgamadas,
                'dosis_omitidas' => $dosisOmitidas,
                'dosis_tardias' => $dosisTardias,
                // Métricas temporales avanzadas
                'temporal_metrics' => [
                    'dosis_puntuales' => $dosisPuntuales,
                    'dosis_tempranas' => $dosisTempranas,
                    'dosis_tardias_temporales' => $dosisTardiasTempo,
                    'score_puntualidad_promedio' => round($scorePuntualidadPromedio, 1),
                    'tiempo_promedio_adelanto' => round($tiempoPromedioAdelanto, 1),
                    'tiempo_promedio_retraso' => round($tiempoPromedioRetraso, 1),
                    'variabilidad_dia' => round($variabilidadDia, 1),
                    'porcentaje_puntualidad' => $dosisProgamadas > 0 ? round(($dosisPuntuales / $dosisProgamadas) * 100, 1) : 0
                ]
            ];
        }
        
        return $datos;
    }

    /**
     * Calcula la desviación estándar de un array de valores
     */
    private function calcularDesviacionEstandar(array $values): float
    {
        if (empty($values)) return 0;
        
        $count = count($values);
        if ($count < 2) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / $count;
        
        return sqrt($variance);
    }
    
    private function obtenerActividadReciente()
    {
        // Obtener las últimas administraciones reales
        $administraciones = Administracion::with(['paciente', 'medicamentoTratamiento.medicamento'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        
        $actividades = [];
        
        foreach ($administraciones as $admin) {
            $pacienteNombre = $admin->paciente->nombre ?? 'Paciente desconocido';
            $medicamentoNombre = $admin->medicamentoTratamiento->medicamento->nombre ?? 'Medicamento';
            
            $accion = '';
            switch ($admin->estado) {
                case 'Administrada':
                    $accion = "Administración completada - {$medicamentoNombre}";
                    break;
                case 'Omitida':
                    $accion = "Dosis omitida reportada - {$medicamentoNombre}";
                    break;
                case 'Tardía':
                    $accion = "Administración tardía - {$medicamentoNombre}";
                    break;
                default:
                    $accion = "Actualización de tratamiento - {$medicamentoNombre}";
            }
            
            $timeAgo = $admin->updated_at->locale('es')->diffForHumans();
            
            $actividades[] = [
                'id' => $admin->id,
                'user' => $pacienteNombre,
                'action' => $accion,
                'time' => $timeAgo
            ];
            
            if (count($actividades) >= 4) break;
        }
        
        // Si no hay suficientes administraciones, completar con alertas reales
        if (count($actividades) < 4) {
            $alertas = Alerta::with('paciente')
                ->orderBy('fecha_generada', 'desc')
                ->limit(4 - count($actividades))
                ->get();
            
            foreach ($alertas as $alerta) {
                $pacienteNombre = $alerta->paciente->nombre ?? 'Paciente desconocido';
                $actividades[] = [
                    'id' => 'alert_' . $alerta->id,
                    'user' => $pacienteNombre,
                    'action' => $alerta->mensaje,
                    'time' => Carbon::parse($alerta->fecha_generada)->locale('es')->diffForHumans()
                ];
            }
        }
        
        return array_slice($actividades, 0, 4);
    }
    
    public function refresh()
    {
        // Endpoint para refrescar solo los datos reales del dashboard
        return response()->json([
            'estadisticasGenerales' => $this->obtenerEstadisticasGenerales(),
            'adherenciaUltimos7Dias' => $this->obtenerAdherenciaUltimos7Dias(),
            'actividadReciente' => $this->obtenerActividadReciente()
        ]);
    }

    /**
     * Dashboard específico para administradores
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea administrador
        if (!$user->hasRole('admin')) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder al panel de administración.');
        }

        $stats = $this->obtenerEstadisticasAdmin();
        
        return Inertia::render('AdminDashboard', [
            'stats' => $stats
        ]);
    }

    /**
     * Obtener estadísticas específicas para administrador
     */
    private function obtenerEstadisticasAdmin()
    {
        // Estadísticas de usuarios
        $totalUsuarios = User::count();
        $usuariosActivos = User::where('activo', true)->count();
        
        // Estadísticas de roles y permisos
        $rolesCount = Role::count();
        $permisosCount = Permiso::count();
        
        // Estadísticas de alertas
        $alertasPendientes = Alerta::where('revisada', false)->count();
        
        // Estadísticas de medicamentos
        $medicamentosCount = Medicamento::count();
        
        // Estadísticas de pacientes y tratamientos
        $pacientesActivos = Paciente::whereHas('tratamientos', function($query) {
            $query->where('estado', 'Activo');
        })->count();
        
        $tratamientosActivos = Tratamiento::where('estado', 'Activo')->count();

        return [
            'total_usuarios' => $totalUsuarios,
            'usuarios_activos' => $usuariosActivos,
            'roles_count' => $rolesCount,
            'permisos_count' => $permisosCount,
            'alertas_pendientes' => $alertasPendientes,
            'medicamentos_count' => $medicamentosCount,
            'pacientes_activos' => $pacientesActivos,
            'tratamientos_activos' => $tratamientosActivos,
        ];
    }

    /**
     * Dashboard específico para adherencia temporal
     */
    public function adherenciaTemporal()
    {
        $user = Auth::user();
        
        // Verificar permisos de acceso
        if (!$user->hasAnyRole(['admin', 'medico', 'cuidador'])) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder al dashboard de adherencia temporal.');
        }

        // Obtener lista de pacientes con tratamientos activos
        $pacientes = Paciente::with(['user', 'tratamientos.medicamentoTratamientos.medicamento'])
            ->whereHas('tratamientos', function($query) {
                $query->where('estado', 'Activo');
            })
            ->get()
            ->map(function($paciente) {
                return [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre . ' ' . $paciente->apellido,
                    'email' => $paciente->user->email ?? 'No disponible',
                    'tratamientos_activos' => $paciente->tratamientos->where('estado', 'Activo')->count(),
                ];
            });

        // Métricas generales del sistema
        $metricas = $this->obtenerMetricasTemporalesGenerales();
        
        return Inertia::render('Dashboard/AdherenciaTemporal', [
            'pacientes' => $pacientes,
            'metricas' => $metricas
        ]);
    }

    /**
     * Obtener métricas temporales generales del sistema
     */
    private function obtenerMetricasTemporalesGenerales()
    {
        $fechaInicio = Carbon::now()->subDays(30);
        
        // Obtener administraciones con métricas temporales de los últimos 30 días
        $administraciones = Administracion::whereNotNull('score_puntualidad')
            ->where('fecha_hora_programada', '>=', $fechaInicio)
            ->get();

        if ($administraciones->isEmpty()) {
            return [
                'total_administraciones' => 0,
                'score_promedio' => 0,
                'porcentaje_puntuales' => 0,
                'tiempo_promedio_retraso' => 0,
                'tiempo_promedio_adelanto' => 0,
                'variabilidad_sistema' => 0,
                'distribucion_categorias' => [
                    'muy_temprano' => 0,
                    'temprano' => 0,
                    'puntual' => 0,
                    'tardio' => 0,
                    'muy_tardio' => 0
                ]
            ];
        }

        $totalAdministraciones = $administraciones->count();
        $scorePromedio = $administraciones->avg('score_puntualidad');
        $dosisPuntuales = $administraciones->where('categoria_temporal', 'puntual')->count();
        $porcentajePuntuales = ($dosisPuntuales / $totalAdministraciones) * 100;
        
        $tiempoPromedioRetraso = $administraciones->where('minutos_retraso', '>', 0)->avg('minutos_retraso') ?? 0;
        $tiempoPromedioAdelanto = $administraciones->where('minutos_adelanto', '>', 0)->avg('minutos_adelanto') ?? 0;
        
        // Calcular variabilidad del sistema
        $scores = $administraciones->pluck('score_puntualidad')->toArray();
        $variabilidadSistema = $this->calcularDesviacionEstandar($scores);
        
        // Distribución por categorías
        $distribucionCategorias = [
            'muy_temprano' => $administraciones->where('categoria_temporal', 'muy_temprano')->count(),
            'temprano' => $administraciones->where('categoria_temporal', 'temprano')->count(),
            'puntual' => $administraciones->where('categoria_temporal', 'puntual')->count(),
            'tardio' => $administraciones->where('categoria_temporal', 'tardio')->count(),
            'muy_tardio' => $administraciones->where('categoria_temporal', 'muy_tardio')->count(),
        ];

        return [
            'total_administraciones' => $totalAdministraciones,
            'score_promedio' => round($scorePromedio, 1),
            'porcentaje_puntuales' => round($porcentajePuntuales, 1),
            'tiempo_promedio_retraso' => round($tiempoPromedioRetraso, 1),
            'tiempo_promedio_adelanto' => round($tiempoPromedioAdelanto, 1),
            'variabilidad_sistema' => round($variabilidadSistema, 1),
            'distribucion_categorias' => $distribucionCategorias
        ];
    }
} 
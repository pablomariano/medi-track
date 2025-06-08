<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\AdministracionMedicamento;
use App\Models\AlertaMedicamento;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class MonitoreoController extends Controller
{
    /**
     * Dashboard principal de monitoreo para médicos
     */
    public function dashboardMedico()
    {
        $user = Auth::user();
        $medico = Medico::where('usuario_id', $user->id)->first();
        
        if (!$medico) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de médico.');
        }

        // Pacientes bajo cuidado del médico
        $pacientes = Paciente::whereHas('tratamientos', function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id)
                      ->where('activo', true);
            })
            ->with(['tratamientos' => function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id)
                      ->where('activo', true)
                      ->with('medicamentos.medicamento');
            }])
            ->get();

        // Estadísticas generales
        $stats = $this->obtenerEstadisticasMedico($medico);

        // Alertas críticas sin resolver
        $alertasCriticas = AlertaMedicamento::whereHas('administracion.tratamiento', function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id);
            })
            ->where('tipo_alerta', 'critica')
            ->where('resuelta', false)
            ->with(['administracion.tratamiento.paciente'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Tratamientos con problemas de adherencia
        $problemasAdherencia = $this->obtenerProblemasAdherencia($medico);

        // Próximas administraciones críticas
        $proximasAdministraciones = $this->obtenerProximasAdministraciones($medico);

        return Inertia::render('Seguimiento/Medicos/Dashboard', [
            'pacientes' => $pacientes,
            'stats' => $stats,
            'alertasCriticas' => $alertasCriticas,
            'problemasAdherencia' => $problemasAdherencia,
            'proximasAdministraciones' => $proximasAdministraciones
        ]);
    }

    /**
     * Vista detallada de un paciente específico
     */
    public function verPaciente(Paciente $paciente)
    {
        $user = Auth::user();
        $medico = Medico::where('usuario_id', $user->id)->first();
        
        // Verificar que el médico tiene acceso a este paciente
        $tieneAcceso = $paciente->tratamientos()
            ->where('medico_responsable_id', $medico->id)
            ->exists();
            
        if (!$tieneAcceso) {
            return redirect()->back()->with('error', 'No tienes acceso a este paciente.');
        }

        // Cargar datos completos del paciente
        $paciente->load([
            'tratamientos' => function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id)
                      ->with(['medicamentos.medicamento', 'cuidadorAsignado.user']);
            },
            'apoderados.user'
        ]);

        // Historial de administraciones reciente (últimos 30 días)
        $historialAdministraciones = AdministracionMedicamento::whereHas('tratamiento', function($query) use ($medico, $paciente) {
                $query->where('medico_responsable_id', $medico->id)
                      ->where('paciente_id', $paciente->id);
            })
            ->with(['medicamento', 'cuidador.user'])
            ->where('fecha_programada', '>=', now()->subDays(30))
            ->orderBy('fecha_programada', 'desc')
            ->get();

        // Análisis de adherencia
        $analisisAdherencia = $this->analizarAdherenciaPaciente($paciente, $medico);

        // Alertas del paciente
        $alertasPaciente = AlertaMedicamento::whereHas('administracion.tratamiento', function($query) use ($medico, $paciente) {
                $query->where('medico_responsable_id', $medico->id)
                      ->where('paciente_id', $paciente->id);
            })
            ->with('administracion.medicamento')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Seguimiento/Medicos/PacienteDetalle', [
            'paciente' => $paciente,
            'historialAdministraciones' => $historialAdministraciones,
            'analisisAdherencia' => $analisisAdherencia,
            'alertasPaciente' => $alertasPaciente
        ]);
    }

    /**
     * Reportes y análisis avanzados
     */
    public function reportes(Request $request)
    {
        $user = Auth::user();
        $medico = Medico::where('usuario_id', $user->id)->first();

        $fechaInicio = $request->input('fecha_inicio', now()->subMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        // Reporte de adherencia por paciente
        $reporteAdherencia = $this->generarReporteAdherencia($medico, $fechaInicio, $fechaFin);

        // Reporte de alertas
        $reporteAlertas = $this->generarReporteAlertas($medico, $fechaInicio, $fechaFin);

        // Reporte de efectividad de tratamientos
        $reporteEfectividad = $this->generarReporteEfectividad($medico, $fechaInicio, $fechaFin);

        // Estadísticas comparativas
        $estadisticasComparativas = $this->obtenerEstadisticasComparativas($medico, $fechaInicio, $fechaFin);

        return Inertia::render('Seguimiento/Medicos/Reportes', [
            'reporteAdherencia' => $reporteAdherencia,
            'reporteAlertas' => $reporteAlertas,
            'reporteEfectividad' => $reporteEfectividad,
            'estadisticasComparativas' => $estadisticasComparativas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]
        ]);
    }

    /**
     * Obtener estadísticas generales del médico
     */
    private function obtenerEstadisticasMedico($medico)
    {
        $tratamientosActivos = Tratamiento::where('medico_responsable_id', $medico->id)
            ->where('activo', true)
            ->count();

        $pacientesActivos = Paciente::whereHas('tratamientos', function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id)
                      ->where('activo', true);
            })->count();

        $administracionesHoy = AdministracionMedicamento::whereHas('tratamiento', function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id);
            })
            ->whereDate('fecha_programada', today())
            ->count();

        $alertasPendientes = AlertaMedicamento::whereHas('administracion.tratamiento', function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id);
            })
            ->where('resuelta', false)
            ->count();

        // Tasa de adherencia promedio (últimos 30 días)
        $adherenciaPromedio = $this->calcularAdherenciaPromedio($medico);

        return [
            'tratamientos_activos' => $tratamientosActivos,
            'pacientes_activos' => $pacientesActivos,
            'administraciones_hoy' => $administracionesHoy,
            'alertas_pendientes' => $alertasPendientes,
            'adherencia_promedio' => $adherenciaPromedio
        ];
    }

    /**
     * Obtener tratamientos con problemas de adherencia
     */
    private function obtenerProblemasAdherencia($medico)
    {
        return Tratamiento::where('medico_responsable_id', $medico->id)
            ->where('activo', true)
            ->with(['paciente', 'medicamentos.medicamento'])
            ->get()
            ->filter(function($tratamiento) {
                $adherencia = $this->calcularAdherenciaTratamiento($tratamiento);
                return $adherencia < 80; // Menos de 80% de adherencia
            })
            ->take(5);
    }

    /**
     * Obtener próximas administraciones críticas
     */
    private function obtenerProximasAdministraciones($medico)
    {
        return AdministracionMedicamento::whereHas('tratamiento', function($query) use ($medico) {
                $query->where('medico_responsable_id', $medico->id);
            })
            ->where('estado', 'programada')
            ->where('fecha_programada', '>=', now())
            ->where('fecha_programada', '<=', now()->addHours(24))
            ->whereHas('medicamento', function($query) {
                $query->where('requiere_supervision', true);
            })
            ->with(['tratamiento.paciente', 'medicamento'])
            ->orderBy('fecha_programada')
            ->limit(10)
            ->get();
    }

    /**
     * Calcular adherencia promedio del médico
     */
    private function calcularAdherenciaPromedio($medico)
    {
        $tratamientos = Tratamiento::where('medico_responsable_id', $medico->id)
            ->where('activo', true)
            ->get();

        if ($tratamientos->isEmpty()) {
            return 0;
        }

        $adherenciaTotal = $tratamientos->sum(function($tratamiento) {
            return $this->calcularAdherenciaTratamiento($tratamiento);
        });

        return round($adherenciaTotal / $tratamientos->count(), 1);
    }

    /**
     * Calcular adherencia de un tratamiento específico
     */
    private function calcularAdherenciaTratamiento($tratamiento)
    {
        $inicioCalculo = now()->subDays(30);
        
        $administracionesProgramadas = AdministracionMedicamento::where('tratamiento_id', $tratamiento->id)
            ->where('fecha_programada', '>=', $inicioCalculo)
            ->where('fecha_programada', '<=', now())
            ->count();

        if ($administracionesProgramadas === 0) {
            return 100; // Si no hay administraciones programadas, consideramos 100%
        }

        $administracionesRealizadas = AdministracionMedicamento::where('tratamiento_id', $tratamiento->id)
            ->where('fecha_programada', '>=', $inicioCalculo)
            ->where('fecha_programada', '<=', now())
            ->where('estado', 'administrado')
            ->count();

        return round(($administracionesRealizadas / $administracionesProgramadas) * 100, 1);
    }

    /**
     * Análisis detallado de adherencia de un paciente
     */
    private function analizarAdherenciaPaciente($paciente, $medico)
    {
        $tratamientos = $paciente->tratamientos()
            ->where('medico_responsable_id', $medico->id)
            ->where('activo', true)
            ->get();

        $analisis = [];
        
        foreach ($tratamientos as $tratamiento) {
            $adherencia = $this->calcularAdherenciaTratamiento($tratamiento);
            
            $analisis[] = [
                'tratamiento_id' => $tratamiento->id,
                'descripcion' => $tratamiento->descripcion,
                'adherencia' => $adherencia,
                'estado' => $this->determinarEstadoAdherencia($adherencia),
                'medicamentos_count' => $tratamiento->medicamentos->count()
            ];
        }

        return $analisis;
    }

    /**
     * Determinar estado de adherencia basado en porcentaje
     */
    private function determinarEstadoAdherencia($porcentaje)
    {
        if ($porcentaje >= 90) return 'excelente';
        if ($porcentaje >= 80) return 'buena';
        if ($porcentaje >= 60) return 'regular';
        return 'deficiente';
    }

    // Métodos adicionales para reportes (implementación básica)
    private function generarReporteAdherencia($medico, $fechaInicio, $fechaFin)
    {
        // Implementación simplificada - en producción sería más compleja
        return [
            'adherencia_promedio' => $this->calcularAdherenciaPromedio($medico),
            'tendencia' => 'mejorando', // Lógica para calcular tendencia
            'pacientes_problematicos' => 2
        ];
    }

    private function generarReporteAlertas($medico, $fechaInicio, $fechaFin)
    {
        return [
            'total_alertas' => 15,
            'alertas_criticas' => 3,
            'alertas_resueltas' => 12
        ];
    }

    private function generarReporteEfectividad($medico, $fechaInicio, $fechaFin)
    {
        return [
            'tratamientos_completados' => 8,
            'tratamientos_suspendidos' => 1,
            'tasa_exito' => 85.5
        ];
    }

    private function obtenerEstadisticasComparativas($medico, $fechaInicio, $fechaFin)
    {
        return [
            'periodo_anterior' => [
                'adherencia' => 82.3,
                'alertas' => 18
            ],
            'periodo_actual' => [
                'adherencia' => 85.7,
                'alertas' => 15
            ]
        ];
    }
} 
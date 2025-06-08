<?php

namespace App\Http\Controllers;

use App\Models\AdministracionMedicamento;
use App\Models\Medicamento;
use App\Models\Paciente;
use App\Models\Tratamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportesController extends Controller
{
    /**
     * Dashboard principal de reportes y gráficos
     */
    public function dashboard(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        // Datos para gráficos
        $datosGraficos = [
            'consumosPorDia' => $this->getConsumosPorDia($fechaInicio, $fechaFin),
            'consumosPorMedicamento' => $this->getConsumosPorMedicamento($fechaInicio, $fechaFin),
            'consumosPorPaciente' => $this->getConsumosPorPaciente($fechaInicio, $fechaFin),
            'adherenciaTratamientos' => $this->getAdherenciaTratamientos($fechaInicio, $fechaFin),
            'estadisticasGenerales' => $this->getEstadisticasGenerales($fechaInicio, $fechaFin)
        ];

        return Inertia::render('Reportes/Dashboard', [
            'datosGraficos' => $datosGraficos,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]
        ]);
    }

    /**
     * Reporte específico de un paciente
     */
    public function reportePaciente(Paciente $paciente, Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $datosGraficos = [
            'timelineConsumos' => $this->getTimelineConsumosPaciente($paciente->id, $fechaInicio, $fechaFin),
            'medicamentosMasConsumidos' => $this->getMedicamentosMasConsumidosPaciente($paciente->id, $fechaInicio, $fechaFin),
            'adherenciaPorTratamiento' => $this->getAdherenciaPorTratamientoPaciente($paciente->id, $fechaInicio, $fechaFin),
            'estadisticasPaciente' => $this->getEstadisticasPaciente($paciente->id, $fechaInicio, $fechaFin)
        ];

        $paciente->load(['tratamientos.medicamentos.medicamento']);

        return Inertia::render('Reportes/ReportePaciente', [
            'paciente' => $paciente,
            'datosGraficos' => $datosGraficos,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]
        ]);
    }

    /**
     * Reporte específico de un medicamento
     */
    public function reporteMedicamento(Medicamento $medicamento, Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $datosGraficos = [
            'consumosPorDia' => $this->getConsumosMedicamentoPorDia($medicamento->id, $fechaInicio, $fechaFin),
            'pacientesQueConsumen' => $this->getPacientesQueConsumenMedicamento($medicamento->id, $fechaInicio, $fechaFin),
            'dosisAdministradas' => $this->getDosisAdministradasMedicamento($medicamento->id, $fechaInicio, $fechaFin),
            'estadisticasMedicamento' => $this->getEstadisticasMedicamento($medicamento->id, $fechaInicio, $fechaFin)
        ];

        return Inertia::render('Reportes/ReporteMedicamento', [
            'medicamento' => $medicamento,
            'datosGraficos' => $datosGraficos,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]
        ]);
    }

    /**
     * Obtener consumos por día (para gráfico de líneas)
     */
    private function getConsumosPorDia($fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::select(
                DB::raw('DATE(fecha_administracion) as fecha'),
                DB::raw('COUNT(*) as total_administraciones'),
                DB::raw('COUNT(CASE WHEN estado = "administrado" THEN 1 END) as administraciones_exitosas'),
                DB::raw('COUNT(CASE WHEN estado = "omitida" OR estado = "rechazada" THEN 1 END) as administraciones_fallidas')
            )
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw('DATE(fecha_administracion)'))
            ->orderBy('fecha')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('Y-m-d'),
                    'fecha_label' => Carbon::parse($item->fecha)->format('d/m'),
                    'total' => $item->total_administraciones,
                    'exitosas' => $item->administraciones_exitosas,
                    'fallidas' => $item->administraciones_fallidas,
                    'tasa_exito' => $item->total_administraciones > 0 
                        ? round(($item->administraciones_exitosas / $item->total_administraciones) * 100, 1)
                        : 0
                ];
            });
    }

    /**
     * Obtener consumos por medicamento (para gráfico de barras)
     */
    private function getConsumosPorMedicamento($fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::join('medicamentos', 'administraciones_medicamentos.medicamento_id', '=', 'medicamentos.id')
            ->select(
                'medicamentos.nombre_comercial',
                'medicamentos.id as medicamento_id',
                DB::raw('COUNT(*) as total_administraciones'),
                DB::raw('COUNT(CASE WHEN administraciones_medicamentos.estado = "administrado" THEN 1 END) as administraciones_exitosas'),
                DB::raw('SUM(administraciones_medicamentos.dosis_administrada) as total_dosis')
            )
            ->whereBetween('administraciones_medicamentos.fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy('medicamentos.id', 'medicamentos.nombre_comercial')
            ->orderBy('total_administraciones', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'medicamento' => $item->nombre_comercial,
                    'medicamento_id' => $item->medicamento_id,
                    'total_administraciones' => $item->total_administraciones,
                    'administraciones_exitosas' => $item->administraciones_exitosas,
                    'total_dosis' => $item->total_dosis ?? 0,
                    'tasa_exito' => $item->total_administraciones > 0 
                        ? round(($item->administraciones_exitosas / $item->total_administraciones) * 100, 1)
                        : 0
                ];
            });
    }

    /**
     * Obtener consumos por paciente
     */
    private function getConsumosPorPaciente($fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::join('tratamientos', 'administraciones_medicamentos.tratamiento_id', '=', 'tratamientos.id')
            ->join('pacientes', 'tratamientos.paciente_id', '=', 'pacientes.id')
            ->select(
                'pacientes.id as paciente_id',
                DB::raw('CONCAT(pacientes.nombres, " ", pacientes.apellidos) as nombre_completo'),
                DB::raw('COUNT(*) as total_administraciones'),
                DB::raw('COUNT(CASE WHEN administraciones_medicamentos.estado = "administrado" THEN 1 END) as administraciones_exitosas')
            )
            ->whereBetween('administraciones_medicamentos.fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy('pacientes.id', 'pacientes.nombres', 'pacientes.apellidos')
            ->orderBy('total_administraciones', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'paciente_id' => $item->paciente_id,
                    'nombre' => $item->nombre_completo,
                    'total_administraciones' => $item->total_administraciones,
                    'administraciones_exitosas' => $item->administraciones_exitosas,
                    'tasa_exito' => $item->total_administraciones > 0 
                        ? round(($item->administraciones_exitosas / $item->total_administraciones) * 100, 1)
                        : 0
                ];
            });
    }

    /**
     * Obtener adherencia por tratamientos activos
     */
    private function getAdherenciaTratamientos($fechaInicio, $fechaFin)
    {
        return Tratamiento::select(
                'tratamientos.id',
                'tratamientos.descripcion',
                DB::raw('CONCAT(pacientes.nombres, " ", pacientes.apellidos) as nombre_paciente'),
                DB::raw('COUNT(administraciones_medicamentos.id) as total_programadas'),
                DB::raw('COUNT(CASE WHEN administraciones_medicamentos.estado = "administrado" THEN 1 END) as total_administradas')
            )
            ->join('pacientes', 'tratamientos.paciente_id', '=', 'pacientes.id')
            ->leftJoin('administraciones_medicamentos', 'tratamientos.id', '=', 'administraciones_medicamentos.tratamiento_id')
            ->where('tratamientos.activo', true)
            ->whereBetween('administraciones_medicamentos.fecha_programada', [$fechaInicio, $fechaFin])
            ->groupBy('tratamientos.id', 'tratamientos.descripcion', 'pacientes.nombres', 'pacientes.apellidos')
            ->having('total_programadas', '>', 0)
            ->get()
            ->map(function ($item) {
                $adherencia = $item->total_programadas > 0 
                    ? round(($item->total_administradas / $item->total_programadas) * 100, 1)
                    : 0;
                    
                return [
                    'tratamiento_id' => $item->id,
                    'descripcion' => $item->descripcion,
                    'paciente' => $item->nombre_paciente,
                    'total_programadas' => $item->total_programadas,
                    'total_administradas' => $item->total_administradas,
                    'adherencia' => $adherencia,
                    'estado_adherencia' => $this->clasificarAdherencia($adherencia)
                ];
            })
            ->sortByDesc('adherencia');
    }

    /**
     * Obtener estadísticas generales
     */
    private function getEstadisticasGenerales($fechaInicio, $fechaFin)
    {
        $totalAdministraciones = AdministracionMedicamento::whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])->count();
        $administracionesExitosas = AdministracionMedicamento::where('estado', 'administrado')
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])->count();
        $pacientesActivos = Paciente::whereHas('tratamientos.administraciones', function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin]);
        })->count();
        $medicamentosUsados = Medicamento::whereHas('administraciones', function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin]);
        })->count();

        return [
            'total_administraciones' => $totalAdministraciones,
            'administraciones_exitosas' => $administracionesExitosas,
            'tasa_exito_global' => $totalAdministraciones > 0 
                ? round(($administracionesExitosas / $totalAdministraciones) * 100, 1)
                : 0,
            'pacientes_activos' => $pacientesActivos,
            'medicamentos_usados' => $medicamentosUsados,
            'promedio_administraciones_diarias' => $totalAdministraciones > 0 
                ? round($totalAdministraciones / max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin))), 1)
                : 0
        ];
    }

    /**
     * Clasificar adherencia según porcentaje
     */
    private function clasificarAdherencia($porcentaje)
    {
        if ($porcentaje >= 90) return 'excelente';
        if ($porcentaje >= 80) return 'buena';
        if ($porcentaje >= 60) return 'regular';
        return 'deficiente';
    }

    // Métodos específicos para reportes individuales

    private function getTimelineConsumosPaciente($pacienteId, $fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::join('tratamientos', 'administraciones_medicamentos.tratamiento_id', '=', 'tratamientos.id')
            ->join('medicamentos', 'administraciones_medicamentos.medicamento_id', '=', 'medicamentos.id')
            ->select(
                'administraciones_medicamentos.*',
                'medicamentos.nombre_comercial',
                'medicamentos.concentracion'
            )
            ->where('tratamientos.paciente_id', $pacienteId)
            ->whereBetween('administraciones_medicamentos.fecha_administracion', [$fechaInicio, $fechaFin])
            ->orderBy('administraciones_medicamentos.fecha_administracion', 'desc')
            ->get();
    }

    private function getMedicamentosMasConsumidosPaciente($pacienteId, $fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::join('tratamientos', 'administraciones_medicamentos.tratamiento_id', '=', 'tratamientos.id')
            ->join('medicamentos', 'administraciones_medicamentos.medicamento_id', '=', 'medicamentos.id')
            ->select(
                'medicamentos.nombre_comercial',
                DB::raw('COUNT(*) as total_administraciones'),
                DB::raw('SUM(administraciones_medicamentos.dosis_administrada) as total_dosis')
            )
            ->where('tratamientos.paciente_id', $pacienteId)
            ->where('administraciones_medicamentos.estado', 'administrado')
            ->whereBetween('administraciones_medicamentos.fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy('medicamentos.id', 'medicamentos.nombre_comercial')
            ->orderBy('total_administraciones', 'desc')
            ->get();
    }

    private function getAdherenciaPorTratamientoPaciente($pacienteId, $fechaInicio, $fechaFin)
    {
        return Tratamiento::select(
                'tratamientos.*',
                DB::raw('COUNT(administraciones_medicamentos.id) as total_programadas'),
                DB::raw('COUNT(CASE WHEN administraciones_medicamentos.estado = "administrado" THEN 1 END) as total_administradas')
            )
            ->leftJoin('administraciones_medicamentos', 'tratamientos.id', '=', 'administraciones_medicamentos.tratamiento_id')
            ->where('tratamientos.paciente_id', $pacienteId)
            ->where('tratamientos.activo', true)
            ->whereBetween('administraciones_medicamentos.fecha_programada', [$fechaInicio, $fechaFin])
            ->groupBy('tratamientos.id')
            ->get()
            ->map(function ($tratamiento) {
                $adherencia = $tratamiento->total_programadas > 0 
                    ? round(($tratamiento->total_administradas / $tratamiento->total_programadas) * 100, 1)
                    : 0;
                    
                return [
                    'tratamiento' => $tratamiento,
                    'adherencia' => $adherencia,
                    'estado' => $this->clasificarAdherencia($adherencia)
                ];
            });
    }

    private function getEstadisticasPaciente($pacienteId, $fechaInicio, $fechaFin)
    {
        $totalAdmin = AdministracionMedicamento::whereHas('tratamiento', function($query) use ($pacienteId) {
            $query->where('paciente_id', $pacienteId);
        })->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])->count();

        $exitosas = AdministracionMedicamento::whereHas('tratamiento', function($query) use ($pacienteId) {
            $query->where('paciente_id', $pacienteId);
        })->where('estado', 'administrado')
        ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])->count();

        return [
            'total_administraciones' => $totalAdmin,
            'administraciones_exitosas' => $exitosas,
            'tasa_exito' => $totalAdmin > 0 ? round(($exitosas / $totalAdmin) * 100, 1) : 0
        ];
    }

    // Métodos para reportes de medicamentos
    private function getConsumosMedicamentoPorDia($medicamentoId, $fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::select(
                DB::raw('DATE(fecha_administracion) as fecha'),
                DB::raw('COUNT(*) as total_administraciones'),
                DB::raw('SUM(dosis_administrada) as total_dosis')
            )
            ->where('medicamento_id', $medicamentoId)
            ->where('estado', 'administrado')
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw('DATE(fecha_administracion)'))
            ->orderBy('fecha')
            ->get();
    }

    private function getPacientesQueConsumenMedicamento($medicamentoId, $fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::join('tratamientos', 'administraciones_medicamentos.tratamiento_id', '=', 'tratamientos.id')
            ->join('pacientes', 'tratamientos.paciente_id', '=', 'pacientes.id')
            ->select(
                DB::raw('CONCAT(pacientes.nombres, " ", pacientes.apellidos) as nombre_completo'),
                DB::raw('COUNT(*) as total_administraciones')
            )
            ->where('administraciones_medicamentos.medicamento_id', $medicamentoId)
            ->where('administraciones_medicamentos.estado', 'administrado')
            ->whereBetween('administraciones_medicamentos.fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy('pacientes.id', 'pacientes.nombres', 'pacientes.apellidos')
            ->orderBy('total_administraciones', 'desc')
            ->get();
    }

    private function getDosisAdministradasMedicamento($medicamentoId, $fechaInicio, $fechaFin)
    {
        return AdministracionMedicamento::select(
                'dosis_administrada',
                DB::raw('COUNT(*) as frecuencia')
            )
            ->where('medicamento_id', $medicamentoId)
            ->where('estado', 'administrado')
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])
            ->groupBy('dosis_administrada')
            ->orderBy('dosis_administrada')
            ->get();
    }

    private function getEstadisticasMedicamento($medicamentoId, $fechaInicio, $fechaFin)
    {
        $total = AdministracionMedicamento::where('medicamento_id', $medicamentoId)
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])->count();

        $exitosas = AdministracionMedicamento::where('medicamento_id', $medicamentoId)
            ->where('estado', 'administrado')
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])->count();

        $totalDosis = AdministracionMedicamento::where('medicamento_id', $medicamentoId)
            ->where('estado', 'administrado')
            ->whereBetween('fecha_administracion', [$fechaInicio, $fechaFin])
            ->sum('dosis_administrada');

        return [
            'total_administraciones' => $total,
            'administraciones_exitosas' => $exitosas,
            'total_dosis_consumida' => $totalDosis ?? 0,
            'tasa_exito' => $total > 0 ? round(($exitosas / $total) * 100, 1) : 0
        ];
    }
} 
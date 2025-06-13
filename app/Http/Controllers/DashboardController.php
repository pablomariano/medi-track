<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Administracion;
use App\Models\EstadisticaConsumo;
use App\Models\Alerta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
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
                'dosis_tardias' => $dosisTardias
            ];
        }
        
        return $datos;
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
} 
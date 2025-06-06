<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Paciente;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\Apoderado;
use App\Models\PrincipioActivo;
use App\Models\Medicamento;
use App\Models\Tratamiento;
use App\Models\AdministracionMedicamento;
use App\Models\AlertaMedicamento;
use App\Models\AutorizacionTratamiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard with system statistics.
     */
    public function index()
    {
        try {
            // Estadísticas generales del sistema
            $systemStats = [
                'usuarios_total' => User::count(),
                'pacientes_total' => Paciente::count(),
                'medicos_total' => PersonalMedico::count(),
                'cuidadores_total' => Cuidador::count(),
                'apoderados_total' => Apoderado::count(),
            ];

            // Estadísticas del sistema de medicamentos
            $medicationStats = [
                'principios_activos' => PrincipioActivo::count(),
                'principios_activos_activos' => PrincipioActivo::activos()->count(),
                'medicamentos_total' => Medicamento::count(),
                'medicamentos_activos' => Medicamento::activos()->count(),
                'medicamentos_vencidos' => Medicamento::where('fecha_vencimiento', '<', now())->count(),
                'medicamentos_proximo_vencer' => Medicamento::proximosAVencer(30)->count(),
                'medicamentos_controlados' => Medicamento::controlados()->count(),
            ];

            // Estadísticas de tratamientos
            $treatmentStats = [
                'tratamientos_total' => Tratamiento::count(),
                'tratamientos_activos' => Tratamiento::activos()->count(),
                'administraciones_hoy' => AdministracionMedicamento::pendientesHoy()->count(),
                'administraciones_vencidas' => AdministracionMedicamento::vencidas()->count(),
                'alertas_activas' => AlertaMedicamento::activas()->count(),
                'alertas_criticas' => AlertaMedicamento::criticas()->count(),
                'autorizaciones_pendientes' => AutorizacionTratamiento::pendientes()->count(),
            ];

            // Alertas recientes
            $alertasRecientes = AlertaMedicamento::activas()
                                               ->with(['tratamiento.paciente'])
                                               ->orderBy('nivel_prioridad', 'desc')
                                               ->orderBy('fecha_activacion', 'desc')
                                               ->limit(5)
                                               ->get();

            // Administraciones pendientes para hoy
            $administracionesHoy = AdministracionMedicamento::pendientesHoy()
                                                           ->with([
                                                               'medicamentoTratamiento.medicamento.principioActivo',
                                                               'medicamentoTratamiento.tratamiento.paciente',
                                                               'cuidador.user'
                                                           ])
                                                           ->orderBy('fecha_hora_programada')
                                                           ->limit(10)
                                                           ->get();

            // Medicamentos próximos a vencer
            $medicamentosVencer = Medicamento::proximosAVencer(30)
                                            ->with(['principioActivo', 'formaFarmaceutica'])
                                            ->orderBy('fecha_vencimiento')
                                            ->limit(5)
                                            ->get();

            // Autorizaciones pendientes
            $autorizacionesPendientes = AutorizacionTratamiento::pendientes()
                                                              ->with([
                                                                  'tratamiento.paciente',
                                                                  'apoderado.user'
                                                              ])
                                                              ->orderBy('fecha_solicitud')
                                                              ->limit(5)
                                                              ->get();

            // Actividad reciente del sistema
            $actividadReciente = $this->getActividadReciente();

            return Inertia::render('dashboard', [
                'systemStats' => $systemStats,
                'medicationStats' => $medicationStats, 
                'treatmentStats' => $treatmentStats,
                'alertasRecientes' => $alertasRecientes,
                'administracionesHoy' => $administracionesHoy,
                'medicamentosVencer' => $medicamentosVencer,
                'autorizacionesPendientes' => $autorizacionesPendientes,
                'actividadReciente' => $actividadReciente
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar dashboard: ' . $e->getMessage());
            
            // Dashboard básico en caso de error
            return Inertia::render('dashboard', [
                'systemStats' => ['error' => 'Error al cargar estadísticas'],
                'medicationStats' => [],
                'treatmentStats' => [],
                'alertasRecientes' => collect(),
                'administracionesHoy' => collect(),
                'medicamentosVencer' => collect(),
                'autorizacionesPendientes' => collect(),
                'actividadReciente' => collect()
            ]);
        }
    }

    /**
     * Show medication system test page.
     */
    public function testMedicamentos()
    {
        try {
            // Obtener datos de prueba para verificar el sistema
            $principiosActivos = PrincipioActivo::with('medicamentos')->get();
            $medicamentos = Medicamento::with([
                'principioActivo',
                'formaFarmaceutica',
                'viaAdministracion',
                'unidadConcentracion'
            ])->get();

            $tratamientos = Tratamiento::with([
                'paciente',
                'medico.user',
                'medicamentos'
            ])->limit(10)->get();

            // Verificar relaciones
            $relationshipTests = [
                'principios_con_medicamentos' => $principiosActivos->filter(function($pa) {
                    return $pa->medicamentos->count() > 0;
                })->count(),
                'medicamentos_con_relaciones' => $medicamentos->filter(function($med) {
                    return $med->principioActivo && $med->formaFarmaceutica;
                })->count(),
                'tratamientos_con_pacientes' => $tratamientos->filter(function($trat) {
                    return $trat->paciente;
                })->count()
            ];

            return view('dashboard.test-medicamentos', compact(
                'principiosActivos',
                'medicamentos',
                'tratamientos',
                'relationshipTests'
            ));

        } catch (\Exception $e) {
            Log::error('Error en test de medicamentos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar datos de prueba: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint para obtener estadísticas en tiempo real.
     */
    public function apiStats()
    {
        try {
            $stats = [
                'administraciones_pendientes' => AdministracionMedicamento::programadas()->count(),
                'alertas_criticas' => AlertaMedicamento::criticas()->count(),
                'autorizaciones_pendientes' => AutorizacionTratamiento::pendientes()->count(),
                'medicamentos_vencidos' => Medicamento::where('fecha_vencimiento', '<', now())->count(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Get recent system activity for dashboard.
     */
    private function getActividadReciente()
    {
        try {
            $actividad = collect();

            // Medicamentos creados recientemente
            $medicamentosRecientes = Medicamento::where('creado_en', '>=', now()->subDays(7))
                                               ->with('principioActivo')
                                               ->orderBy('creado_en', 'desc')
                                               ->limit(3)
                                               ->get()
                                               ->map(function($med) {
                                                   return [
                                                       'tipo' => 'medicamento_creado',
                                                       'mensaje' => "Medicamento '{$med->nombre_comercial}' agregado al sistema",
                                                       'fecha' => $med->creado_en,
                                                       'icono' => '💊',
                                                       'color' => 'text-blue-600'
                                                   ];
                                               });

            // Tratamientos recientes
            $tratamientosRecientes = Tratamiento::where('creado_en', '>=', now()->subDays(7))
                                               ->with(['paciente', 'medico.user'])
                                               ->orderBy('creado_en', 'desc')
                                               ->limit(3)
                                               ->get()
                                               ->map(function($trat) {
                                                   return [
                                                       'tipo' => 'tratamiento_creado',
                                                       'mensaje' => "Tratamiento '{$trat->nombre}' iniciado para {$trat->paciente->nombre_completo}",
                                                       'fecha' => $trat->creado_en,
                                                       'icono' => '🩺',
                                                       'color' => 'text-green-600'
                                                   ];
                                               });

            // Alertas recientes
            $alertasRecientes = AlertaMedicamento::where('fecha_activacion', '>=', now()->subDays(3))
                                                ->with('tratamiento.paciente')
                                                ->orderBy('fecha_activacion', 'desc')
                                                ->limit(3)
                                                ->get()
                                                ->map(function($alert) {
                                                    return [
                                                        'tipo' => 'alerta_activada',
                                                        'mensaje' => "Alerta: {$alert->titulo}",
                                                        'fecha' => $alert->fecha_activacion,
                                                        'icono' => '🚨',
                                                        'color' => $alert->color_prioridad
                                                    ];
                                                });

            return $actividad->merge($medicamentosRecientes)
                            ->merge($tratamientosRecientes)
                            ->merge($alertasRecientes)
                            ->sortByDesc('fecha')
                            ->take(10)
                            ->values();

        } catch (\Exception $e) {
            Log::error('Error al obtener actividad reciente: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Dashboard específico para médicos.
     */
    public function dashboardMedico()
    {
        try {
            $medico = PersonalMedico::where('usuario_id', auth()->id())->first();
            
            if (!$medico) {
                return redirect()->route('dashboard')->with('error', 'No tiene permisos de médico.');
            }

            $stats = [
                'pacientes_total' => $medico->pacientesEnTratamiento()->count(),
                'tratamientos_activos' => $medico->tratamientosActivos()->count(),
                'tratamientos_pausados' => $medico->tratamientosPrescritos()
                                                 ->where('estado', Tratamiento::ESTADO_PAUSADO)
                                                 ->count(),
                'autorizaciones_pendientes' => AutorizacionTratamiento::whereHas('tratamiento', function($q) use ($medico) {
                    $q->where('medico_usuario_id', $medico->usuario_id);
                })->pendientes()->count()
            ];

            $tratamientosRecientes = $medico->tratamientosPrescritos()
                                          ->with(['paciente', 'medicamentos'])
                                          ->orderBy('creado_en', 'desc')
                                          ->limit(5)
                                          ->get();

            return view('dashboard.medico', compact('stats', 'tratamientosRecientes', 'medico'));

        } catch (\Exception $e) {
            Log::error('Error en dashboard médico: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar dashboard médico.');
        }
    }

    /**
     * Dashboard específico para cuidadores.
     */
    public function dashboardCuidador()
    {
        try {
            $cuidador = Cuidador::where('usuario_id', auth()->id())->first();
            
            if (!$cuidador) {
                return redirect()->route('dashboard')->with('error', 'No tiene permisos de cuidador.');
            }

            $stats = [
                'administraciones_hoy' => $cuidador->administracionesHoy()->count(),
                'administraciones_pendientes' => $cuidador->administracionesPendientes()->count(),
                'administraciones_completadas' => $cuidador->administracionesMedicamentos()
                                                          ->administradas()
                                                          ->whereBetween('fecha_hora_real', [
                                                              now()->startOfDay(),
                                                              now()->endOfDay()
                                                          ])->count(),
                'pacientes_asignados' => $cuidador->administracionesMedicamentos()
                                                 ->whereHas('medicamentoTratamiento.tratamiento')
                                                 ->distinct('medicamento_tratamiento_id')
                                                 ->count()
            ];

            $administracionesPendientes = $cuidador->administracionesPendientes()
                                                  ->with([
                                                      'medicamentoTratamiento.medicamento.principioActivo',
                                                      'medicamentoTratamiento.tratamiento.paciente'
                                                  ])
                                                  ->orderBy('fecha_hora_programada')
                                                  ->limit(10)
                                                  ->get();

            return view('dashboard.cuidador', compact('stats', 'administracionesPendientes', 'cuidador'));

        } catch (\Exception $e) {
            Log::error('Error en dashboard cuidador: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar dashboard cuidador.');
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TemporalAdherenceService;
use App\Models\Administracion;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TemporalAdherenceController extends Controller
{
    protected TemporalAdherenceService $temporalService;

    public function __construct(TemporalAdherenceService $temporalService)
    {
        $this->temporalService = $temporalService;
    }

    /**
     * Obtiene métricas temporales para un paciente específico
     * 
     * @param Request $request
     * @param int $pacienteId
     * @return JsonResponse
     */
    public function getPatientMetrics(Request $request, int $pacienteId): JsonResponse
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'medicamento_id' => 'nullable|integer|exists:medicamentos,id',
                'tratamiento_id' => 'nullable|integer|exists:tratamientos,id',
            ]);

            // Verificar que el paciente existe
            $paciente = Paciente::findOrFail($pacienteId);

            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin);
            $medicamentoId = $request->medicamento_id;
            $tratamientoId = $request->tratamiento_id;

            // Obtener métricas del período
            $metricas = $this->temporalService->calculatePeriodMetrics(
                $pacienteId,
                $fechaInicio,
                $fechaFin,
                $medicamentoId,
                $tratamientoId
            );

            // Agregar información contextual
            $metricas['periodo'] = [
                'inicio' => $fechaInicio->format('Y-m-d'),
                'fin' => $fechaFin->format('Y-m-d'),
                'dias' => $fechaInicio->diffInDays($fechaFin) + 1
            ];

            $metricas['paciente'] = [
                'id' => $paciente->id,
                'nombre' => $paciente->nombre,
                'medicamento_filtro' => $medicamentoId,
                'tratamiento_filtro' => $tratamientoId
            ];

            // Calcular porcentajes para la UI
            $total = $metricas['total_administraciones'];
            if ($total > 0) {
                $metricas['porcentajes'] = [
                    'puntuales' => round(($metricas['dosis_puntuales'] / $total) * 100, 1),
                    'tempranas' => round(($metricas['dosis_tempranas'] / $total) * 100, 1),
                    'tardias' => round(($metricas['dosis_tardias'] / $total) * 100, 1),
                ];
            } else {
                $metricas['porcentajes'] = ['puntuales' => 0, 'tempranas' => 0, 'tardias' => 0];
            }

            return response()->json([
                'success' => true,
                'data' => $metricas
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error obteniendo métricas temporales del paciente', [
                'paciente_id' => $pacienteId,
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtiene las tendencias diarias de un paciente específico
     * 
     * @param Request $request
     * @param int $pacienteId
     * @return JsonResponse
     */
    public function getPatientTrends(Request $request, int $pacienteId): JsonResponse
    {
        try {
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            ]);

            $paciente = Paciente::findOrFail($pacienteId);

            // Por defecto, última semana (7 días)
            $fechaInicio = $request->fecha_inicio 
                ? Carbon::parse($request->fecha_inicio)
                : Carbon::now()->subDays(7);
            
            $fechaFin = $request->fecha_fin 
                ? Carbon::parse($request->fecha_fin)
                : Carbon::now();

            // Obtener administraciones del período
            $administraciones = Administracion::where('paciente_id', $pacienteId)
                ->whereNotNull('score_puntualidad')
                ->whereBetween('fecha_hora_programada', [$fechaInicio, $fechaFin])
                ->orderBy('fecha_hora_programada')
                ->get();

            // Agrupar por día y calcular métricas
            $tendenciasPorDia = [];
            $fechaActual = $fechaInicio->copy();

            while ($fechaActual <= $fechaFin) {
                $administracionesDia = $administraciones->filter(function($admin) use ($fechaActual) {
                    return Carbon::parse($admin->fecha_hora_programada)->isSameDay($fechaActual);
                });

                if ($administracionesDia->isNotEmpty()) {
                    // Calcular métricas del día
                    $promedioAdelanto = $administracionesDia->where('minutos_adelanto', '>', 0)->avg('minutos_adelanto') ?? 0;
                    $promedioRetraso = $administracionesDia->where('minutos_retraso', '>', 0)->avg('minutos_retraso') ?? 0;
                    
                    // Calcular variabilidad (desviación estándar de los scores)
                    $scores = $administracionesDia->pluck('score_puntualidad')->toArray();
                    $variabilidad = $this->calcularDesviacionEstandar($scores);

                    $tendenciasPorDia[] = [
                        'fecha' => $fechaActual->format('Y-m-d'),
                        'promedio_adelanto' => round($promedioAdelanto, 1),
                        'promedio_retraso' => round($promedioRetraso, 1),
                        'variabilidad' => round($variabilidad, 1),
                        'total_administraciones' => $administracionesDia->count(),
                        'score_promedio' => round($administracionesDia->avg('score_puntualidad'), 1)
                    ];
                } else {
                    // Día sin administraciones
                    $tendenciasPorDia[] = [
                        'fecha' => $fechaActual->format('Y-m-d'),
                        'promedio_adelanto' => 0,
                        'promedio_retraso' => 0,
                        'variabilidad' => 0,
                        'total_administraciones' => 0,
                        'score_promedio' => 0
                    ];
                }

                $fechaActual->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => $tendenciasPorDia,
                'meta' => [
                    'paciente_id' => $pacienteId,
                    'paciente_nombre' => $paciente->nombre,
                    'periodo' => [
                        'inicio' => $fechaInicio->format('Y-m-d'),
                        'fin' => $fechaFin->format('Y-m-d'),
                        'dias' => $fechaInicio->diffInDays($fechaFin) + 1
                    ],
                    'total_administraciones' => $administraciones->count()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error obteniendo tendencias del paciente', [
                'paciente_id' => $pacienteId,
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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

    /**
     * Analiza tendencias comparando dos períodos
     * 
     * @param Request $request
     * @param int $pacienteId
     * @return JsonResponse
     */
    public function analyzeTrends(Request $request, int $pacienteId): JsonResponse
    {
        try {
            $request->validate([
                'periodo_actual_inicio' => 'required|date',
                'periodo_actual_fin' => 'required|date|after_or_equal:periodo_actual_inicio',
                'periodo_anterior_inicio' => 'required|date',
                'periodo_anterior_fin' => 'required|date|after_or_equal:periodo_anterior_inicio|before:periodo_actual_inicio',
            ]);

            $paciente = Paciente::findOrFail($pacienteId);

            $periodoActualInicio = Carbon::parse($request->periodo_actual_inicio);
            $periodoActualFin = Carbon::parse($request->periodo_actual_fin);
            $periodoAnteriorInicio = Carbon::parse($request->periodo_anterior_inicio);
            $periodoAnteriorFin = Carbon::parse($request->periodo_anterior_fin);

            $analisis = $this->temporalService->analyzeTrends(
                $pacienteId,
                $periodoActualInicio,
                $periodoActualFin,
                $periodoAnteriorInicio,
                $periodoAnteriorFin
            );

            // Enriquecer con contexto adicional
            $analisis['contexto'] = [
                'paciente' => [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre
                ],
                'periodos' => [
                    'actual' => [
                        'inicio' => $periodoActualInicio->format('Y-m-d'),
                        'fin' => $periodoActualFin->format('Y-m-d'),
                        'dias' => $periodoActualInicio->diffInDays($periodoActualFin) + 1
                    ],
                    'anterior' => [
                        'inicio' => $periodoAnteriorInicio->format('Y-m-d'),
                        'fin' => $periodoAnteriorFin->format('Y-m-d'),
                        'dias' => $periodoAnteriorInicio->diffInDays($periodoAnteriorFin) + 1
                    ]
                ]
            ];

            // Generar insights automáticos
            $analisis['insights'] = $this->generateInsights($analisis);

            return response()->json([
                'success' => true,
                'data' => $analisis
            ]);

        } catch (\Exception $e) {
            \Log::error('Error analizando tendencias temporales', [
                'paciente_id' => $pacienteId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error analizando tendencias'
            ], 500);
        }
    }

    /**
     * Obtiene distribución detallada de administraciones por período
     * 
     * @param Request $request
     * @param int $pacienteId
     * @return JsonResponse
     */
    public function getDistribution(Request $request, int $pacienteId): JsonResponse
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo_distribucion' => 'required|in:horaria,diaria,semanal',
                'categoria_temporal' => 'nullable|in:muy_temprano,temprano,puntual,tardio,muy_tardio'
            ]);

            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin);
            $tipoDistribucion = $request->tipo_distribucion;
            $categoriaFiltro = $request->categoria_temporal;

            $query = Administracion::where('paciente_id', $pacienteId)
                ->porPeriodo($fechaInicio, $fechaFin)
                ->conMetricasTemporales();

            if ($categoriaFiltro) {
                $query->porCategoria($categoriaFiltro);
            }

            $administraciones = $query->get();

            $distribucion = $this->calculateDistribution($administraciones, $tipoDistribucion);

            return response()->json([
                'success' => true,
                'data' => [
                    'distribucion' => $distribucion,
                    'total_administraciones' => $administraciones->count(),
                    'periodo' => [
                        'inicio' => $fechaInicio->format('Y-m-d'),
                        'fin' => $fechaFin->format('Y-m-d')
                    ],
                    'filtros' => [
                        'tipo' => $tipoDistribucion,
                        'categoria' => $categoriaFiltro
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error obteniendo distribución temporal', [
                'paciente_id' => $pacienteId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo distribución'
            ], 500);
        }
    }

    /**
     * Compara múltiples pacientes en métricas temporales
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function comparePatients(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'pacientes' => 'required|array|min:2|max:5',
                'pacientes.*' => 'integer|exists:pacientes,id',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            ]);

            $pacientesIds = $request->pacientes;
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin);

            $comparacion = [];

            foreach ($pacientesIds as $pacienteId) {
                $paciente = Paciente::find($pacienteId);
                if (!$paciente) continue;

                $metricas = $this->temporalService->calculatePeriodMetrics(
                    $pacienteId,
                    $fechaInicio,
                    $fechaFin
                );

                $comparacion[] = [
                    'paciente' => [
                        'id' => $paciente->id,
                        'nombre' => $paciente->nombre
                    ],
                    'metricas' => $metricas
                ];
            }

            // Calcular estadísticas de comparación
            $estadisticas = $this->calculateComparisonStats($comparacion);

            return response()->json([
                'success' => true,
                'data' => [
                    'comparacion' => $comparacion,
                    'estadisticas' => $estadisticas,
                    'periodo' => [
                        'inicio' => $fechaInicio->format('Y-m-d'),
                        'fin' => $fechaFin->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error comparando pacientes', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la comparación'
            ], 500);
        }
    }

    /**
     * Calcula distribución por tipo especificado
     */
    private function calculateDistribution($administraciones, string $tipo): array
    {
        switch ($tipo) {
            case 'horaria':
                return $this->calculateHourlyDistribution($administraciones);
            case 'diaria':
                return $this->calculateDailyDistribution($administraciones);
            case 'semanal':
                return $this->calculateWeeklyDistribution($administraciones);
            default:
                return [];
        }
    }

    /**
     * Distribución por hora del día
     */
    private function calculateHourlyDistribution($administraciones): array
    {
        $distribution = array_fill(0, 24, ['count' => 0, 'avg_score' => 0]);
        
        foreach ($administraciones as $admin) {
            $hora = Carbon::parse($admin->fecha_hora_administrada)->hour;
            $distribution[$hora]['count']++;
            $distribution[$hora]['scores'][] = $admin->score_puntualidad;
        }

        // Calcular promedios
        foreach ($distribution as $hora => &$data) {
            if (!empty($data['scores'])) {
                $data['avg_score'] = round(collect($data['scores'])->avg(), 2);
                unset($data['scores']);
            }
        }

        return $distribution;
    }

    /**
     * Distribución por día del mes
     */
    private function calculateDailyDistribution($administraciones): array
    {
        $distribution = [];
        
        foreach ($administraciones as $admin) {
            $fecha = Carbon::parse($admin->fecha_hora_administrada)->format('Y-m-d');
            
            if (!isset($distribution[$fecha])) {
                $distribution[$fecha] = ['count' => 0, 'scores' => []];
            }
            
            $distribution[$fecha]['count']++;
            $distribution[$fecha]['scores'][] = $admin->score_puntualidad;
        }

        // Calcular promedios y ordenar por fecha
        foreach ($distribution as $fecha => &$data) {
            $data['avg_score'] = round(collect($data['scores'])->avg(), 2);
            unset($data['scores']);
        }

        ksort($distribution);
        return $distribution;
    }

    /**
     * Distribución por día de la semana
     */
    private function calculateWeeklyDistribution($administraciones): array
    {
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $distribution = [];

        foreach ($days as $day) {
            $distribution[$day] = ['count' => 0, 'scores' => []];
        }
        
        foreach ($administraciones as $admin) {
            $dayOfWeek = Carbon::parse($admin->fecha_hora_administrada)->dayOfWeek;
            $dayName = $days[$dayOfWeek === 0 ? 6 : $dayOfWeek - 1];
            
            $distribution[$dayName]['count']++;
            $distribution[$dayName]['scores'][] = $admin->score_puntualidad;
        }

        // Calcular promedios
        foreach ($distribution as $day => &$data) {
            if (!empty($data['scores'])) {
                $data['avg_score'] = round(collect($data['scores'])->avg(), 2);
            } else {
                $data['avg_score'] = 0;
            }
            unset($data['scores']);
        }

        return $distribution;
    }

    /**
     * Genera insights automáticos basados en el análisis de tendencias
     */
    private function generateInsights(array $analisis): array
    {
        $insights = [];
        $cambios = $analisis['cambios'];

        // Insight sobre puntualidad
        if ($cambios['mejora_puntualidad']) {
            $insights[] = [
                'tipo' => 'positive',
                'mensaje' => "La puntualidad mejoró en {$cambios['puntualidad']} puntos",
                'categoria' => 'puntualidad'
            ];
        } elseif ($cambios['puntualidad'] < -5) {
            $insights[] = [
                'tipo' => 'warning',
                'mensaje' => "La puntualidad disminuyó en " . abs($cambios['puntualidad']) . " puntos",
                'categoria' => 'puntualidad'
            ];
        }

        // Insight sobre consistencia
        if ($cambios['mejora_consistencia']) {
            $insights[] = [
                'tipo' => 'positive',
                'mensaje' => "La consistencia en los horarios mejoró",
                'categoria' => 'consistencia'
            ];
        } elseif ($cambios['variabilidad'] > 10) {
            $insights[] = [
                'tipo' => 'warning',
                'mensaje' => "Los horarios se volvieron menos consistentes",
                'categoria' => 'consistencia'
            ];
        }

        return $insights;
    }

    /**
     * Calcula estadísticas de comparación entre pacientes
     */
    private function calculateComparisonStats(array $comparacion): array
    {
        $puntualidades = [];
        $variabilidades = [];

        foreach ($comparacion as $item) {
            $puntualidades[] = $item['metricas']['puntualidad_promedio'];
            $variabilidades[] = $item['metricas']['variabilidad_horaria'];
        }

        return [
            'puntualidad' => [
                'promedio' => round(collect($puntualidades)->avg(), 2),
                'maximo' => round(collect($puntualidades)->max(), 2),
                'minimo' => round(collect($puntualidades)->min(), 2),
            ],
            'variabilidad' => [
                'promedio' => round(collect($variabilidades)->avg(), 2),
                'maximo' => round(collect($variabilidades)->max(), 2),
                'minimo' => round(collect($variabilidades)->min(), 2),
            ]
        ];
    }
} 
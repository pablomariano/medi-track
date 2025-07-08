<?php

namespace App\Services;

use App\Models\Administracion;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TemporalAdherenceService
{
    const TOLERANCE_IDEAL = 15; // ±15 minutos para puntualidad perfecta
    const TOLERANCE_GOOD = 30;  // ±30 minutos para buena puntualidad
    const TOLERANCE_FAIR = 60;  // ±60 minutos para puntualidad aceptable
    
    /**
     * Calcula métricas temporales para una administración
     */
    public function calculateTemporalMetrics(Administracion $administracion): array
    {
        if (!$administracion->fecha_hora_programada || !$administracion->fecha_hora_administrada) {
            return [
                'minutos_adelanto' => null,
                'minutos_retraso' => null,
                'score_puntualidad' => null,
                'categoria_temporal' => null
            ];
        }

        $programada = Carbon::parse($administracion->fecha_hora_programada);
        $administrada = Carbon::parse($administracion->fecha_hora_administrada);
        
        // Calcular diferencia en minutos (positivo = tarde, negativo = temprano)
        $diferencia = $administrada->diffInMinutes($programada, false);
        
        $minutos_adelanto = $diferencia < 0 ? abs($diferencia) : 0;
        $minutos_retraso = $diferencia > 0 ? $diferencia : 0;
        
        // Calcular score de puntualidad (0-100)
        $score = $this->calculatePunctualityScore($diferencia);
        
        // Determinar categoría temporal
        $categoria = $this->determineTemporalCategory($diferencia);
        
        return [
            'minutos_adelanto' => $minutos_adelanto,
            'minutos_retraso' => $minutos_retraso,
            'score_puntualidad' => $score,
            'categoria_temporal' => $categoria
        ];
    }

    /**
     * Calcula el score de puntualidad basado en función exponencial
     */
    public function calculatePunctualityScore(int $diferencia_minutos): float
    {
        $diferencia_abs = abs($diferencia_minutos);
        
        // Puntualidad perfecta dentro de ±15 minutos
        if ($diferencia_abs <= self::TOLERANCE_IDEAL) {
            return 100.0;
        }
        
        // Función exponencial decreciente después de 15 minutos
        // Score = 100 * e^(-0.05 * (diferencia - 15))
        $score = 100 * exp(-0.05 * ($diferencia_abs - self::TOLERANCE_IDEAL));
        
        return round(max(0, $score), 2);
    }

    /**
     * Determina la categoría temporal basada en la diferencia
     */
    public function determineTemporalCategory(int $diferencia_minutos): string
    {
        if ($diferencia_minutos <= -self::TOLERANCE_FAIR) {
            return 'muy_temprano'; // Más de 60 min antes
        } elseif ($diferencia_minutos <= -self::TOLERANCE_GOOD) {
            return 'temprano';     // 30-60 min antes
        } elseif (abs($diferencia_minutos) <= self::TOLERANCE_GOOD) {
            return 'puntual';      // ±30 minutos
        } elseif ($diferencia_minutos <= self::TOLERANCE_FAIR) {
            return 'tardio';       // 30-60 min después
        } else {
            return 'muy_tardio';   // Más de 60 min después
        }
    }

    /**
     * Calcula métricas agregadas para un paciente en un período
     */
    public function calculatePeriodMetrics(int $paciente_id, Carbon $fecha_inicio, Carbon $fecha_fin, ?int $medicamento_id = null, ?int $tratamiento_id = null): array
    {
        $cacheKey = "temporal_metrics_{$paciente_id}_{$fecha_inicio->format('Y-m-d')}_{$fecha_fin->format('Y-m-d')}_{$medicamento_id}_{$tratamiento_id}";
        
        return Cache::remember($cacheKey, 6 * 60 * 60, function () use ($paciente_id, $fecha_inicio, $fecha_fin, $medicamento_id, $tratamiento_id) {
            $query = Administracion::where('paciente_id', $paciente_id)
                ->whereBetween('fecha_hora_programada', [$fecha_inicio, $fecha_fin])
                ->whereNotNull('fecha_hora_administrada')
                ->whereNotNull('score_puntualidad');

            if ($medicamento_id) {
                $query->where('medicamento_id', $medicamento_id);
            }
            
            if ($tratamiento_id) {
                $query->where('tratamiento_id', $tratamiento_id);
            }

            $administraciones = $query->get();

            if ($administraciones->isEmpty()) {
                return $this->getEmptyMetrics();
            }

            return $this->computeAggregatedMetrics($administraciones);
        });
    }

    /**
     * Computa métricas agregadas a partir de administraciones
     */
    private function computeAggregatedMetrics($administraciones): array
    {
        $total = $administraciones->count();
        
        // Conteos por categoría
        $categorias = $administraciones->groupBy('categoria_temporal')->map->count();
        
        // Métricas de tiempo
        $scores = $administraciones->pluck('score_puntualidad')->filter();
        $adelantos = $administraciones->where('minutos_adelanto', '>', 0)->pluck('minutos_adelanto');
        $retrasos = $administraciones->where('minutos_retraso', '>', 0)->pluck('minutos_retraso');
        
        // Distribución horaria
        $distribucion_horas = $this->calculateHourlyDistribution($administraciones);
        
        // Patrones semanales
        $patrones_semanales = $this->calculateWeeklyPatterns($administraciones);
        
        // Variabilidad (desviación estándar de los scores)
        $variabilidad = $this->calculateStandardDeviation($scores->toArray());

        return [
            'total_administraciones' => $total,
            'puntualidad_promedio' => round($scores->avg(), 2),
            'dosis_puntuales' => $categorias->get('puntual', 0),
            'dosis_tempranas' => ($categorias->get('muy_temprano', 0) + $categorias->get('temprano', 0)),
            'dosis_tardias' => ($categorias->get('tardio', 0) + $categorias->get('muy_tardio', 0)),
            'tiempo_promedio_adelanto' => round($adelantos->avg() ?? 0, 2),
            'tiempo_promedio_retraso' => round($retrasos->avg() ?? 0, 2),
            'variabilidad_horaria' => round($variabilidad, 2),
            'distribucion_por_horas' => $distribucion_horas,
            'patrones_semanales' => $patrones_semanales,
            'categorias_detalle' => [
                'muy_temprano' => $categorias->get('muy_temprano', 0),
                'temprano' => $categorias->get('temprano', 0),
                'puntual' => $categorias->get('puntual', 0),
                'tardio' => $categorias->get('tardio', 0),
                'muy_tardio' => $categorias->get('muy_tardio', 0),
            ]
        ];
    }

    /**
     * Calcula distribución por hora del día
     */
    private function calculateHourlyDistribution($administraciones): array
    {
        $distribution = array_fill(0, 24, 0);
        
        foreach ($administraciones as $admin) {
            if ($admin->fecha_hora_administrada) {
                $hora = Carbon::parse($admin->fecha_hora_administrada)->hour;
                $distribution[$hora]++;
            }
        }
        
        return $distribution;
    }

    /**
     * Calcula patrones por día de la semana
     */
    private function calculateWeeklyPatterns($administraciones): array
    {
        $patterns = [];
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        
        foreach ($administraciones as $admin) {
            if ($admin->fecha_hora_administrada) {
                $dayOfWeek = Carbon::parse($admin->fecha_hora_administrada)->dayOfWeek;
                $dayName = $days[$dayOfWeek === 0 ? 6 : $dayOfWeek - 1]; // Ajustar para que Lunes sea 0
                
                if (!isset($patterns[$dayName])) {
                    $patterns[$dayName] = ['count' => 0, 'avg_score' => 0, 'scores' => []];
                }
                
                $patterns[$dayName]['count']++;
                $patterns[$dayName]['scores'][] = $admin->score_puntualidad;
            }
        }
        
        // Calcular promedios
        foreach ($patterns as $day => &$data) {
            $data['avg_score'] = round(collect($data['scores'])->avg(), 2);
            unset($data['scores']); // Remover array detallado para ahorrar espacio
        }
        
        return $patterns;
    }

    /**
     * Calcula desviación estándar
     */
    private function calculateStandardDeviation(array $values): float
    {
        if (empty($values)) return 0;
        
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);
        
        return sqrt($variance);
    }

    /**
     * Retorna métricas vacías por defecto
     */
    private function getEmptyMetrics(): array
    {
        return [
            'total_administraciones' => 0,
            'puntualidad_promedio' => 0,
            'dosis_puntuales' => 0,
            'dosis_tempranas' => 0,
            'dosis_tardias' => 0,
            'tiempo_promedio_adelanto' => 0,
            'tiempo_promedio_retraso' => 0,
            'variabilidad_horaria' => 0,
            'distribucion_por_horas' => array_fill(0, 24, 0),
            'patrones_semanales' => [],
            'categorias_detalle' => [
                'muy_temprano' => 0,
                'temprano' => 0,
                'puntual' => 0,
                'tardio' => 0,
                'muy_tardio' => 0,
            ]
        ];
    }

    /**
     * Analiza tendencias comparando períodos
     */
    public function analyzeTrends(int $paciente_id, Carbon $periodo_actual_inicio, Carbon $periodo_actual_fin, Carbon $periodo_anterior_inicio, Carbon $periodo_anterior_fin): array
    {
        $metricas_actuales = $this->calculatePeriodMetrics($paciente_id, $periodo_actual_inicio, $periodo_actual_fin);
        $metricas_anteriores = $this->calculatePeriodMetrics($paciente_id, $periodo_anterior_inicio, $periodo_anterior_fin);
        
        $cambio_puntualidad = $metricas_actuales['puntualidad_promedio'] - $metricas_anteriores['puntualidad_promedio'];
        $cambio_variabilidad = $metricas_actuales['variabilidad_horaria'] - $metricas_anteriores['variabilidad_horaria'];
        
        return [
            'periodo_actual' => $metricas_actuales,
            'periodo_anterior' => $metricas_anteriores,
            'cambios' => [
                'puntualidad' => round($cambio_puntualidad, 2),
                'variabilidad' => round($cambio_variabilidad, 2),
                'mejora_puntualidad' => $cambio_puntualidad > 0,
                'mejora_consistencia' => $cambio_variabilidad < 0, // Menor variabilidad = mayor consistencia
            ]
        ];
    }

    /**
     * Invalidar cache de métricas para un paciente
     */
    public function invalidatePatientCache(int $paciente_id): void
    {
        Cache::tags(["patient_{$paciente_id}_temporal"])->flush();
    }
} 
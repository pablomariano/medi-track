<?php

namespace App\Observers;

use App\Models\Administracion;
use App\Services\TemporalAdherenceService;
use Illuminate\Support\Facades\Log;

class AdministracionObserver
{
    protected $temporalService;

    public function __construct(TemporalAdherenceService $temporalService)
    {
        $this->temporalService = $temporalService;
    }

    /**
     * Handle the Administracion "creating" event.
     */
    public function creating(Administracion $administracion): void
    {
        $this->calculateTemporalMetrics($administracion);
    }

    /**
     * Handle the Administracion "updating" event.
     */
    public function updating(Administracion $administracion): void
    {
        // Solo recalcular si cambió alguna fecha relevante
        if ($administracion->isDirty(['fecha_hora_programada', 'fecha_hora_administrada'])) {
            $this->calculateTemporalMetrics($administracion);
        }
    }

    /**
     * Handle the Administracion "updated" event.
     */
    public function updated(Administracion $administracion): void
    {
        // Invalidar cache después de actualizar
        if ($administracion->wasChanged(['fecha_hora_programada', 'fecha_hora_administrada', 'score_puntualidad'])) {
            $this->invalidatePatientCache($administracion);
        }
    }

    /**
     * Handle the Administracion "created" event.
     */
    public function created(Administracion $administracion): void
    {
        // Invalidar cache después de crear
        $this->invalidatePatientCache($administracion);
    }

    /**
     * Handle the Administracion "deleted" event.
     */
    public function deleted(Administracion $administracion): void
    {
        // Invalidar cache después de eliminar
        $this->invalidatePatientCache($administracion);
    }

    /**
     * Calcula y asigna métricas temporales a la administración
     */
    private function calculateTemporalMetrics(Administracion $administracion): void
    {
        try {
            // Solo calcular si tenemos las fechas necesarias
            if (!$administracion->fecha_hora_programada || !$administracion->fecha_hora_administrada) {
                // Limpiar métricas temporales si no tenemos datos completos
                $administracion->minutos_adelanto = null;
                $administracion->minutos_retraso = null;
                $administracion->score_puntualidad = null;
                $administracion->categoria_temporal = null;
                return;
            }

            // Calcular métricas usando el servicio
            $metricas = $this->temporalService->calculateTemporalMetrics($administracion);

            // Asignar las métricas calculadas
            $administracion->minutos_adelanto = $metricas['minutos_adelanto'];
            $administracion->minutos_retraso = $metricas['minutos_retraso'];
            $administracion->score_puntualidad = $metricas['score_puntualidad'];
            $administracion->categoria_temporal = $metricas['categoria_temporal'];

            // También calcular la diferencia en minutos total para compatibilidad
            if (!$administracion->isDirty('minutos_diferencia')) {
                $administracion->minutos_diferencia = $administracion->getDiferenciaTemporal();
            }

        } catch (\Exception $e) {
            Log::error('Error calculando métricas temporales para administración', [
                'administracion_id' => $administracion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // En caso de error, mantener valores seguros
            $administracion->minutos_adelanto = null;
            $administracion->minutos_retraso = null;
            $administracion->score_puntualidad = null;
            $administracion->categoria_temporal = null;
        }
    }

    /**
     * Invalida el cache de métricas temporales para el paciente
     */
    private function invalidatePatientCache(Administracion $administracion): void
    {
        try {
            if ($administracion->paciente_id) {
                $this->temporalService->invalidatePatientCache($administracion->paciente_id);
            }
        } catch (\Exception $e) {
            Log::warning('Error invalidando cache de paciente', [
                'paciente_id' => $administracion->paciente_id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 
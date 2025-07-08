<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Administracion;
use App\Services\TemporalAdherenceService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecalculateTemporalMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adherence:recalculate-temporal 
                            {--patient= : ID del paciente específico (opcional)}
                            {--treatment= : ID del tratamiento específico (opcional)}
                            {--days= : Número de días hacia atrás para procesar (default: 90)}
                            {--chunk=100 : Tamaño del chunk para procesamiento por lotes}
                            {--force : Recalcular incluso si ya existen métricas temporales}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula las métricas temporales de adherencia para administraciones existentes';

    protected TemporalAdherenceService $temporalService;

    public function __construct(TemporalAdherenceService $temporalService)
    {
        parent::__construct();
        $this->temporalService = $temporalService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Iniciando recálculo de métricas temporales...');

        $patientId = $this->option('patient');
        $treatmentId = $this->option('treatment');
        $days = (int) $this->option('days');
        $chunkSize = (int) $this->option('chunk');
        $force = $this->option('force');

        // Validar parámetros
        if ($chunkSize < 10 || $chunkSize > 1000) {
            $this->error('El tamaño del chunk debe estar entre 10 y 1000');
            return self::FAILURE;
        }

        // Construir query base
        $query = $this->buildBaseQuery($patientId, $treatmentId, $days, $force);

        // Contar total de registros
        $totalRecords = $query->count();
        
        if ($totalRecords === 0) {
            $this->info('✅ No hay administraciones para procesar.');
            return self::SUCCESS;
        }

        $this->info("📊 Se procesarán {$totalRecords} administraciones en chunks de {$chunkSize}");

        // Confirmar en producción
        if (app()->environment('production') && !$this->confirm('¿Estás seguro de proceder en producción?')) {
            $this->info('❌ Operación cancelada');
            return self::FAILURE;
        }

        $processedCount = 0;
        $errorCount = 0;
        $startTime = microtime(true);

        // Crear barra de progreso
        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        try {
            // Procesar en chunks para evitar problemas de memoria
            $query->chunk($chunkSize, function ($administraciones) use (&$processedCount, &$errorCount, $progressBar) {
                foreach ($administraciones as $administracion) {
                    try {
                        $this->processAdministracion($administracion);
                        $processedCount++;
                    } catch (\Exception $e) {
                        $this->logError($administracion, $e);
                        $errorCount++;
                    }
                    
                    $progressBar->advance();
                }
            });

        } catch (\Exception $e) {
            $progressBar->finish();
            $this->error("\n❌ Error durante el procesamiento: " . $e->getMessage());
            return self::FAILURE;
        }

        $progressBar->finish();
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->newLine();
        $this->info("✅ Procesamiento completado en {$duration} segundos");
        $this->info("📈 Administraciones procesadas: {$processedCount}");
        
        if ($errorCount > 0) {
            $this->warn("⚠️  Errores encontrados: {$errorCount}");
            $this->info("🔍 Revisa los logs para más detalles");
        }

        // Limpiar cache después del procesamiento
        $this->info("🧹 Limpiando cache de métricas temporales...");
        $this->clearTemporalCache($patientId);

        return self::SUCCESS;
    }

    /**
     * Construye la consulta base para obtener administraciones
     */
    private function buildBaseQuery(?int $patientId, ?int $treatmentId, int $days, bool $force)
    {
        $query = Administracion::query()
            ->whereNotNull('fecha_hora_programada')
            ->whereNotNull('fecha_hora_administrada');

        // Filtrar por período si se especifica
        if ($days > 0) {
            $fechaLimite = Carbon::now()->subDays($days);
            $query->where('fecha_hora_programada', '>=', $fechaLimite);
        }

        // Filtrar por paciente específico
        if ($patientId) {
            $query->where('paciente_id', $patientId);
        }

        // Filtrar por tratamiento específico
        if ($treatmentId) {
            $query->porTratamiento($treatmentId);
        }

        // Si no es forzado, solo procesar las que no tienen métricas
        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('score_puntualidad')
                  ->orWhereNull('categoria_temporal')
                  ->orWhereNull('minutos_adelanto')
                  ->orWhereNull('minutos_retraso');
            });
        }

        return $query->orderBy('fecha_hora_programada');
    }

    /**
     * Procesa una administración individual
     */
    private function processAdministracion(Administracion $administracion): void
    {
        // Desactivar observers temporalmente para evitar loops
        Administracion::withoutEvents(function () use ($administracion) {
            $metricas = $this->temporalService->calculateTemporalMetrics($administracion);
            
            // Actualizar los campos calculados
            $administracion->update([
                'minutos_adelanto' => $metricas['minutos_adelanto'],
                'minutos_retraso' => $metricas['minutos_retraso'],
                'score_puntualidad' => $metricas['score_puntualidad'],
                'categoria_temporal' => $metricas['categoria_temporal'],
                'minutos_diferencia' => $administracion->getDiferenciaTemporal(),
            ]);
        });
    }

    /**
     * Registra errores en el log
     */
    private function logError(Administracion $administracion, \Exception $e): void
    {
        \Log::error('Error recalculando métricas temporales', [
            'command' => 'adherence:recalculate-temporal',
            'administracion_id' => $administracion->id,
            'paciente_id' => $administracion->paciente_id,
            'fecha_programada' => $administracion->fecha_hora_programada,
            'fecha_administrada' => $administracion->fecha_hora_administrada,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Limpia el cache de métricas temporales
     */
    private function clearTemporalCache(?int $patientId): void
    {
        try {
            if ($patientId) {
                // Limpiar cache para paciente específico
                $this->temporalService->invalidatePatientCache($patientId);
            } else {
                // Limpiar cache general (esto depende de cómo implementes el cache)
                \Cache::tags(['temporal_metrics'])->flush();
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  No se pudo limpiar el cache: " . $e->getMessage());
        }
    }
} 
<?php

namespace App\Console\Commands;

use App\Models\Administracion;
use App\Models\Alerta;
use App\Models\Tratamiento;
use App\Services\RealTimeNotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessRealTimeNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-real-time 
                            {--dry-run : Run without sending actual notifications}
                            {--patient-id= : Process notifications for specific patient only}
                            {--hours-back=2 : Hours to look back for unprocessed events}
                            {--type= : Specific notification type to process (omitted|late|adverse)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send real-time notifications for critical medication events';

    protected $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new RealTimeNotificationService();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $patientId = $this->option('patient-id');
        $hoursBack = (int) $this->option('hours-back');
        $notificationType = $this->option('type');

        $this->info("🔄 Procesando notificaciones en tiempo real...");
        
        if ($dryRun) {
            $this->warn("🔍 Modo DRY-RUN activado - No se enviarán emails reales");
        }

        $cutoffTime = now()->subHours($hoursBack);
        $totalNotifications = 0;

        // Process different types of notifications
        if (!$notificationType || $notificationType === 'omitted') {
            $totalNotifications += $this->processOmittedDoses($cutoffTime, $patientId, $dryRun);
        }

        if (!$notificationType || $notificationType === 'late') {
            $totalNotifications += $this->processLateDoses($cutoffTime, $patientId, $dryRun);
        }

        if (!$notificationType || $notificationType === 'adverse') {
            $totalNotifications += $this->processAdverseEffects($cutoffTime, $patientId, $dryRun);
        }

        // Process treatment state changes
        $totalNotifications += $this->processTreatmentChanges($cutoffTime, $patientId, $dryRun);

        // Process escalation alerts (multiple consecutive omissions)
        $totalNotifications += $this->processEscalationAlerts($patientId, $dryRun);

        $this->info("✅ Procesamiento completado");
        $this->info("📧 Total de notificaciones " . ($dryRun ? "que se enviarían" : "enviadas") . ": {$totalNotifications}");

        return Command::SUCCESS;
    }

    /**
     * Process omitted dose notifications
     */
    private function processOmittedDoses(Carbon $cutoffTime, ?int $patientId, bool $dryRun): int
    {
        $this->info("🚨 Procesando dosis omitidas...");

        $query = Administracion::where('estado', Administracion::ESTADO_OMITIDA)
            ->where('updated_at', '>=', $cutoffTime)
            ->whereDoesntHave('alerta', function($q) {
                $q->where('tipo', Alerta::TIPO_DOSIS_OMITIDA);
            });

        if ($patientId) {
            $query->where('paciente_id', $patientId);
        }

        $omittedAdministrations = $query->with(['paciente', 'medicamentoTratamiento.medicamento'])->get();

        $sent = 0;
        foreach ($omittedAdministrations as $administracion) {
            if (!$dryRun) {
                $sent += $this->notificationService->sendDoseOmittedNotification($administracion);
            } else {
                $this->line("📧 Se enviaría notificación de dosis omitida para {$administracion->paciente->nombre}");
                $sent++;
            }
        }

        $this->info("   ✅ Dosis omitidas procesadas: {$sent}");
        return $sent;
    }

    /**
     * Process late dose notifications
     */
    private function processLateDoses(Carbon $cutoffTime, ?int $patientId, bool $dryRun): int
    {
        $this->info("⏰ Procesando dosis tardías...");

        $query = Administracion::where('estado', Administracion::ESTADO_ADMINISTRADA)
            ->where('es_dentro_ventana_tolerancia', false)
            ->where('minutos_retraso', '>', 30) // Only notify if more than 30 minutes late
            ->where('updated_at', '>=', $cutoffTime)
            ->whereDoesntHave('alerta', function($q) {
                $q->where('tipo', 'dosis_tardia');
            });

        if ($patientId) {
            $query->where('paciente_id', $patientId);
        }

        $lateAdministrations = $query->with(['paciente', 'medicamentoTratamiento.medicamento'])->get();

        $sent = 0;
        foreach ($lateAdministrations as $administracion) {
            if (!$dryRun) {
                $sent += $this->notificationService->sendLateDoseNotification($administracion);
            } else {
                $this->line("📧 Se enviaría notificación de dosis tardía para {$administracion->paciente->nombre} ({$administracion->minutos_retraso} min)");
                $sent++;
            }
        }

        $this->info("   ✅ Dosis tardías procesadas: {$sent}");
        return $sent;
    }

    /**
     * Process adverse effects notifications
     */
    private function processAdverseEffects(Carbon $cutoffTime, ?int $patientId, bool $dryRun): int
    {
        $this->info("⚠️ Procesando efectos adversos...");

        $query = Administracion::whereNotNull('efectos_adversos')
            ->where('updated_at', '>=', $cutoffTime)
            ->whereDoesntHave('alerta', function($q) {
                $q->where('tipo', Alerta::TIPO_EFECTO_ADVERSO);
            });

        if ($patientId) {
            $query->where('paciente_id', $patientId);
        }

        $adverseEffectsAdministrations = $query->with(['paciente', 'medicamentoTratamiento.medicamento'])->get();

        $sent = 0;
        foreach ($adverseEffectsAdministrations as $administracion) {
            if (!$dryRun) {
                $sent += $this->notificationService->sendAdverseEffectsNotification($administracion);
            } else {
                $this->line("📧 Se enviaría notificación de efectos adversos para {$administracion->paciente->nombre}");
                $sent++;
            }
        }

        $this->info("   ✅ Efectos adversos procesados: {$sent}");
        return $sent;
    }

    /**
     * Process treatment state changes
     */
    private function processTreatmentChanges(Carbon $cutoffTime, ?int $patientId, bool $dryRun): int
    {
        $this->info("📋 Procesando cambios de tratamiento...");

        $query = Tratamiento::where('updated_at', '>=', $cutoffTime)
            ->whereIn('estado', [
                Tratamiento::ESTADO_PAUSADO,
                Tratamiento::ESTADO_COMPLETADO,
                Tratamiento::ESTADO_SUSPENDIDO
            ])
            ->whereDoesntHave('alerta', function($q) {
                $q->where('tipo', 'cambio_tratamiento')
                    ->where('created_at', '>=', Carbon::now()->subHours(2));
            });

        if ($patientId) {
            $query->where('paciente_id', $patientId);
        }

        $changedTreatments = $query->with('paciente')->get();

        $sent = 0;
        foreach ($changedTreatments as $tratamiento) {
            if (!$dryRun) {
                $sent += $this->notificationService->sendTreatmentStateChangeNotification(
                    $tratamiento,
                    'Activo', // Assuming previous state was active
                    'Cambio de estado procesado automáticamente'
                );
            } else {
                $this->line("📧 Se enviaría notificación de cambio de tratamiento para {$tratamiento->paciente->nombre} ({$tratamiento->estado})");
                $sent++;
            }
        }

        $this->info("   ✅ Cambios de tratamiento procesados: {$sent}");
        return $sent;
    }

    /**
     * Process escalation alerts for multiple consecutive omissions
     */
    private function processEscalationAlerts(?int $patientId, bool $dryRun): int
    {
        $this->info("🚨 Procesando alertas de escalamiento...");

        $query = Administracion::where('estado', Administracion::ESTADO_OMITIDA)
            ->where('fecha_hora_programada', '>=', now()->subDays(7))
            ->with(['paciente', 'medicamentoTratamiento.medicamento']);

        if ($patientId) {
            $query->where('paciente_id', $patientId);
        }

        $omittedAdministrations = $query->get()
            ->groupBy('paciente_id');

        $sent = 0;
        foreach ($omittedAdministrations as $pacienteId => $administraciones) {
            $consecutiveCount = $administraciones->count();
            
            // Check if we need to escalate (3+ omissions and no recent escalation alert)
            if ($consecutiveCount >= 3) {
                $hasRecentEscalation = Alerta::where('paciente_id', $pacienteId)
                    ->where('tipo', 'escalamiento_adherencia')
                    ->where('created_at', '>=', now()->subDays(1))
                    ->exists();

                if (!$hasRecentEscalation) {
                    $paciente = $administraciones->first()->paciente;
                    
                    if (!$dryRun) {
                        // Create escalation alert
                        $alerta = Alerta::create([
                            'paciente_id' => $pacienteId,
                            'tipo' => 'escalamiento_adherencia',
                            'prioridad' => Alerta::PRIORIDAD_CRITICA,
                            'titulo' => 'Escalamiento por Múltiples Omisiones',
                            'mensaje' => "Paciente {$paciente->nombre} ha omitido {$consecutiveCount} dosis en los últimos 7 días",
                            'fecha_hora' => now(),
                            'leida' => false,
                            'datos_adicionales' => [
                                'consecutive_omissions' => $consecutiveCount,
                                'period_days' => 7,
                                'escalation_level' => 'critical'
                            ]
                        ]);

                        // This could trigger additional notifications or actions
                        $this->warn("🚨 ESCALAMIENTO CRÍTICO: {$paciente->nombre} - {$consecutiveCount} omisiones");
                        $sent++;
                    } else {
                        $this->line("🚨 Se crearía alerta de escalamiento para {$paciente->nombre} ({$consecutiveCount} omisiones)");
                        $sent++;
                    }
                }
            }
        }

        $this->info("   ✅ Alertas de escalamiento procesadas: {$sent}");
        return $sent;
    }
} 
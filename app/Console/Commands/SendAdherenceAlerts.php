<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdherenceReportService;

class SendAdherenceAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adherence:send-alerts 
                            {--threshold=70 : Adherence threshold below which to send alerts (percentage)}
                            {--days=3 : Number of days to analyze for adherence}
                            {--patient-id= : Send alert for specific patient ID}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send adherence alerts for patients with poor adherence';

    protected $adherenceService;

    /**
     * Execute the console command.
     */
    public function handle(AdherenceReportService $adherenceService)
    {
        $this->adherenceService = $adherenceService;
        
        $threshold = (float) $this->option('threshold');
        $days = (int) $this->option('days');
        $patientId = $this->option('patient-id');
        $dryRun = $this->option('dry-run');

        $this->info("🚨 Starting adherence alert analysis...");
        $this->info("📊 Threshold: {$threshold}% | Period: {$days} days");
        
        if ($dryRun) {
            $this->warn("📋 DRY RUN MODE - No alerts will be sent");
        }

        try {
            if ($patientId) {
                $sent = $this->sendAlertForPatient($patientId, $threshold, $days, $dryRun);
            } else {
                $sent = $this->sendAlertsForAllPatients($threshold, $days, $dryRun);
            }

            if ($dryRun) {
                $this->info("📋 Dry run completed. Would have sent {$sent} alerts.");
            } else {
                $this->info("✅ Successfully sent {$sent} alerts.");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error sending alerts: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Send alerts for all patients with poor adherence
     */
    private function sendAlertsForAllPatients($threshold, $days, $dryRun = false): int
    {
        $pacientes = \App\Models\Paciente::whereHas('tratamientos', function($query) {
            $query->where('estado', 'Activo');
        })->with(['usuario', 'tratamientos.medico', 'apoderados.usuario', 'cuidadores.usuario'])->get();

        $alertsSent = 0;
        $periodoInicio = now()->subDays($days);
        $periodoFin = now();

        $this->info("🔍 Analyzing {$pacientes->count()} patients...");

        foreach ($pacientes as $paciente) {
            $adherenceData = $this->getAdherenceData($paciente, $periodoInicio, $periodoFin);
            
            if ($this->shouldSendAlert($adherenceData, $threshold)) {
                if ($dryRun) {
                    $this->displayAlertInfo($paciente, $adherenceData, true);
                    $recipients = $this->getAlertRecipients($paciente);
                    $alertsSent += count($recipients);
                } else {
                    $sent = $this->sendAlert($paciente, $adherenceData, $periodoInicio, $periodoFin);
                    $alertsSent += $sent;
                    if ($sent > 0) {
                        $this->displayAlertInfo($paciente, $adherenceData, false);
                    }
                }
            }
        }

        return $alertsSent;
    }

    /**
     * Send alert for specific patient
     */
    private function sendAlertForPatient($patientId, $threshold, $days, $dryRun = false): int
    {
        $paciente = \App\Models\Paciente::with(['usuario', 'tratamientos.medico', 'apoderados.usuario', 'cuidadores.usuario'])
            ->find($patientId);

        if (!$paciente) {
            $this->error("❌ Patient with ID {$patientId} not found.");
            return 0;
        }

        $this->info("👤 Analyzing patient: {$paciente->nombre}");

        $periodoInicio = now()->subDays($days);
        $periodoFin = now();
        $adherenceData = $this->getAdherenceData($paciente, $periodoInicio, $periodoFin);

        if (!$this->shouldSendAlert($adherenceData, $threshold)) {
            $this->info("✅ Patient {$paciente->nombre} has acceptable adherence ({$adherenceData['adherencia_porcentaje']}%)");
            return 0;
        }

        if ($dryRun) {
            $this->displayAlertInfo($paciente, $adherenceData, true);
            $recipients = $this->getAlertRecipients($paciente);
            return count($recipients);
        }

        $sent = $this->sendAlert($paciente, $adherenceData, $periodoInicio, $periodoFin);
        if ($sent > 0) {
            $this->displayAlertInfo($paciente, $adherenceData, false);
        }

        return $sent;
    }

    /**
     * Get adherence data for a patient
     */
    private function getAdherenceData($paciente, $periodoInicio, $periodoFin): array
    {
        $administraciones = \App\Models\Administracion::where('paciente_id', $paciente->id)
            ->whereNotNull('fecha_hora_programada')
            ->whereBetween('fecha_hora_programada', [$periodoInicio, $periodoFin])
            ->get();

        $totalDosis = $administraciones->count();
        $dosisAdministradas = $administraciones->whereIn('estado', ['Administrada', 'Tardía'])->count();
        $dosisOmitidas = $administraciones->where('estado', 'Omitida')->count();

        return [
            'total_dosis' => $totalDosis,
            'dosis_administradas' => $dosisAdministradas,
            'dosis_omitidas' => $dosisOmitidas,
            'adherencia_porcentaje' => $totalDosis > 0 ? round(($dosisAdministradas / $totalDosis) * 100, 1) : 0,
        ];
    }

    /**
     * Check if alert should be sent
     */
    private function shouldSendAlert($adherenceData, $threshold): bool
    {
        return $adherenceData['adherencia_porcentaje'] < $threshold && $adherenceData['total_dosis'] > 0;
    }

    /**
     * Send alert for a patient
     */
    private function sendAlert($paciente, $adherenceData, $periodoInicio, $periodoFin): int
    {
        $alertData = [
            'tipo_alerta' => 'adherencia_baja',
            'adherencia_porcentaje' => $adherenceData['adherencia_porcentaje'],
            'dosis_omitidas' => $adherenceData['dosis_omitidas'],
            'total_dosis' => $adherenceData['total_dosis'],
            'periodo_analizado' => $periodoInicio->format('d/m/Y') . ' - ' . $periodoFin->format('d/m/Y')
        ];

        $recipients = $this->getAlertRecipients($paciente);
        $sent = 0;

        foreach ($recipients as $recipient) {
            try {
                $mail = \App\Mail\AdherenceReportMail::alert($paciente, $alertData, $recipient);
                \Illuminate\Support\Facades\Mail::to($recipient->email)->send($mail);
                $sent++;
            } catch (\Exception $e) {
                $this->error("❌ Failed to send alert to {$recipient->email}: " . $e->getMessage());
            }
        }

        return $sent;
    }

    /**
     * Get alert recipients for a patient
     */
    private function getAlertRecipients($paciente): array
    {
        $recipients = [];

        // Add medical staff (priority for alerts)
        foreach ($paciente->tratamientos as $tratamiento) {
            if ($tratamiento->medico && $tratamiento->medico->email_verified_at) {
                $recipients[] = $tratamiento->medico;
            }
        }

        // Add guardians
        foreach ($paciente->apoderados as $apoderado) {
            if ($apoderado->usuario && $apoderado->usuario->email_verified_at) {
                $recipients[] = $apoderado->usuario;
            }
        }

        return array_unique($recipients, SORT_REGULAR);
    }

    /**
     * Display alert information
     */
    private function displayAlertInfo($paciente, $adherenceData, $isDryRun): void
    {
        $status = $isDryRun ? 'Would alert' : 'Alert sent';
        $icon = $isDryRun ? '📋' : '🚨';
        
        $this->line(sprintf(
            "%s %s: %s - Adherence: %s%% (%d/%d doses)",
            $icon,
            $status,
            $paciente->nombre,
            $adherenceData['adherencia_porcentaje'],
            $adherenceData['dosis_administradas'],
            $adherenceData['total_dosis']
        ));

        if ($isDryRun) {
            $recipients = $this->getAlertRecipients($paciente);
            $this->line("    Recipients: " . count($recipients));
            foreach ($recipients as $recipient) {
                $this->line("    - {$recipient->email} ({$recipient->role->nombre})");
            }
        }
    }
} 
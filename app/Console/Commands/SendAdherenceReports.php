<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdherenceReportService;

class SendAdherenceReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adherence:send-reports 
                            {--type=weekly : Type of report to send (weekly, monthly)}
                            {--patient-id= : Send report for specific patient ID}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send adherence reports to patients and medical staff';

    protected $adherenceService;

    /**
     * Execute the console command.
     */
    public function handle(AdherenceReportService $adherenceService)
    {
        $this->adherenceService = $adherenceService;
        
        $type = $this->option('type');
        $patientId = $this->option('patient-id');
        $dryRun = $this->option('dry-run');

        $this->info("🚀 Starting adherence report generation...");
        
        if ($dryRun) {
            $this->warn("📋 DRY RUN MODE - No emails will be sent");
        }

        try {
            switch ($type) {
                case 'weekly':
                    $sent = $this->sendWeeklyReports($patientId, $dryRun);
                    break;
                case 'monthly':
                    $sent = $this->sendMonthlyReports($patientId, $dryRun);
                    break;
                default:
                    $this->error("❌ Invalid report type. Use 'weekly' or 'monthly'");
                    return 1;
            }

            if ($dryRun) {
                $this->info("📋 Dry run completed. Would have sent {$sent} reports.");
            } else {
                $this->info("✅ Successfully sent {$sent} reports.");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error sending reports: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Send weekly reports
     */
    private function sendWeeklyReports($patientId = null, $dryRun = false): int
    {
        $this->info("📅 Generating weekly reports...");
        
        if ($patientId) {
            return $this->sendReportForPatient($patientId, 'weekly', $dryRun);
        }

        if ($dryRun) {
            $pacientes = \App\Models\Paciente::whereHas('tratamientos', function($query) {
                $query->where('estado', 'Activo');
            })->with(['usuario', 'tratamientos.medico', 'apoderados.usuario', 'cuidadores.usuario'])->get();

            $count = 0;
            foreach ($pacientes as $paciente) {
                $recipients = $this->getReportRecipients($paciente);
                if (!empty($recipients)) {
                    $count += count($recipients);
                    $this->line("📧 Would send to {$paciente->nombre}: " . count($recipients) . " recipients");
                }
            }
            return $count;
        }

        return $this->adherenceService->sendWeeklyReports();
    }

    /**
     * Send monthly reports
     */
    private function sendMonthlyReports($patientId = null, $dryRun = false): int
    {
        $this->info("📅 Generating monthly reports...");
        
        if ($patientId) {
            return $this->sendReportForPatient($patientId, 'monthly', $dryRun);
        }

        $pacientes = \App\Models\Paciente::whereHas('tratamientos', function($query) {
            $query->where('estado', 'Activo');
        })->with(['usuario', 'tratamientos.medico', 'apoderados.usuario', 'cuidadores.usuario'])->get();

        $totalSent = 0;
        $periodoInicio = now()->startOfMonth()->subMonth();
        $periodoFin = now()->endOfMonth()->subMonth();

        foreach ($pacientes as $paciente) {
            if ($dryRun) {
                $recipients = $this->getReportRecipients($paciente);
                if (!empty($recipients)) {
                    $totalSent += count($recipients);
                    $this->line("📧 Would send to {$paciente->nombre}: " . count($recipients) . " recipients");
                }
            } else {
                $reportData = $this->adherenceService->generateAdherenceReport($paciente, $periodoInicio, $periodoFin, 'mensual');
                $recipients = $this->getReportRecipients($paciente);

                if (!empty($recipients)) {
                    $sent = $this->adherenceService->sendReport($paciente, $reportData, $periodoInicio, $periodoFin, $recipients, 'mensual');
                    $totalSent += $sent;
                    $this->line("📧 Sent to {$paciente->nombre}: {$sent} reports");
                }
            }
        }

        return $totalSent;
    }

    /**
     * Send report for specific patient
     */
    private function sendReportForPatient($patientId, $type, $dryRun = false): int
    {
        $paciente = \App\Models\Paciente::with(['usuario', 'tratamientos.medico', 'apoderados.usuario', 'cuidadores.usuario'])
            ->find($patientId);

        if (!$paciente) {
            $this->error("❌ Patient with ID {$patientId} not found.");
            return 0;
        }

        $this->info("👤 Generating {$type} report for: {$paciente->nombre}");

        // Set period based on type
        if ($type === 'weekly') {
            $periodoInicio = now()->startOfWeek()->subWeek();
            $periodoFin = now()->endOfWeek()->subWeek();
        } else {
            $periodoInicio = now()->startOfMonth()->subMonth();
            $periodoFin = now()->endOfMonth()->subMonth();
        }

        $recipients = $this->getReportRecipients($paciente);
        
        if (empty($recipients)) {
            $this->warn("⚠ No valid recipients found for {$paciente->nombre}");
            return 0;
        }

        if ($dryRun) {
            $this->line("📧 Would send to " . count($recipients) . " recipients:");
            foreach ($recipients as $recipient) {
                $this->line("  - {$recipient->email} ({$recipient->role->nombre})");
            }
            return count($recipients);
        }

        $reportData = $this->adherenceService->generateAdherenceReport($paciente, $periodoInicio, $periodoFin, $type);
        $sent = $this->adherenceService->sendReport($paciente, $reportData, $periodoInicio, $periodoFin, $recipients, $type);
        
        $this->line("📧 Sent {$sent} reports for {$paciente->nombre}");
        return $sent;
    }

    /**
     * Get report recipients for a patient (replicated from service for dry-run)
     */
    private function getReportRecipients($paciente): array
    {
        $recipients = [];

        // Add patient if has user account and email verified
        if ($paciente->usuario && $paciente->usuario->email_verified_at) {
            $recipients[] = $paciente->usuario;
        }

        // Add medical staff
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

        // Add caregivers
        foreach ($paciente->cuidadores as $cuidador) {
            if ($cuidador->usuario && $cuidador->usuario->email_verified_at) {
                $recipients[] = $cuidador->usuario;
            }
        }

        return array_unique($recipients, SORT_REGULAR);
    }
} 
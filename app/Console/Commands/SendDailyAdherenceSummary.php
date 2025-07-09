<?php

namespace App\Console\Commands;

use App\Models\Administracion;
use App\Models\Paciente;
use App\Models\User;
use App\Mail\DailyAdherenceSummaryMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDailyAdherenceSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adherence:send-daily-summary 
                            {--dry-run : Run without sending actual emails}
                            {--date= : Specific date to analyze (YYYY-MM-DD)}
                            {--email= : Send to specific email address}
                            {--patient-id= : Include specific patient only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily adherence summary to medical staff - Perfect for testing the notification system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificDate = $this->option('date');
        $specificEmail = $this->option('email');
        $specificPatientId = $this->option('patient-id');

        // Determine date to analyze
        $analysisDate = $specificDate ? Carbon::parse($specificDate) : Carbon::yesterday();
        
        $this->info("📊 Generando resumen de adherencia para: {$analysisDate->format('d/m/Y')}");
        
        if ($dryRun) {
            $this->warn("🔍 Modo DRY-RUN activado - No se enviarán emails reales");
        }

        // Gather adherence data
        $summaryData = $this->generateAdherenceSummary($analysisDate, $specificPatientId);
        
        if (empty($summaryData['patients'])) {
            $this->warn("⚠️ No se encontraron datos de administraciones para esta fecha");
            return Command::SUCCESS;
        }

        // Display summary in console
        $this->displaySummary($summaryData);

        // Send emails
        $emailsSent = $this->sendSummaryEmails($summaryData, $analysisDate, $dryRun, $specificEmail);

        $this->info("✅ Resumen completado");
        $this->info("📧 Emails " . ($dryRun ? "que se enviarían" : "enviados") . ": {$emailsSent}");

        return Command::SUCCESS;
    }

    /**
     * Generate adherence summary for the specified date
     */
    private function generateAdherenceSummary(Carbon $date, ?int $specificPatientId): array
    {
        $query = Administracion::whereDate('fecha_hora_programada', $date);
        
        if ($specificPatientId) {
            $query->where('paciente_id', $specificPatientId);
        }

        $administrations = $query->with(['paciente', 'medicamentoTratamiento.medicamento'])->get();

        $summary = [
            'analysis_date' => $date,
            'total_scheduled' => $administrations->count(),
            'total_administered' => $administrations->where('estado', Administracion::ESTADO_ADMINISTRADA)->count(),
            'total_omitted' => $administrations->where('estado', Administracion::ESTADO_OMITIDA)->count(),
            'total_pending' => $administrations->where('estado', Administracion::ESTADO_PENDIENTE)->count(),
            'total_late' => $administrations->where('estado', Administracion::ESTADO_TARDIA)->count(),
            'adherence_rate' => 0,
            'punctuality_rate' => 0,
            'patients' => [],
            'medications' => [],
            'alerts_generated' => 0,
            'top_performers' => [],
            'needs_attention' => []
        ];

        // Calculate overall rates
        if ($summary['total_scheduled'] > 0) {
            $summary['adherence_rate'] = round(
                ($summary['total_administered'] / $summary['total_scheduled']) * 100, 
                1
            );
        }

        // Calculate punctuality rate
        $punctualDoses = $administrations->where('es_dentro_ventana_tolerancia', true)->count();
        if ($summary['total_administered'] > 0) {
            $summary['punctuality_rate'] = round(
                ($punctualDoses / $summary['total_administered']) * 100, 
                1
            );
        }

        // Group by patients
        $patientGroups = $administrations->groupBy('paciente_id');
        
        foreach ($patientGroups as $pacienteId => $patientAdministrations) {
            $patient = $patientAdministrations->first()->paciente;
            $administered = $patientAdministrations->where('estado', Administracion::ESTADO_ADMINISTRADA)->count();
            $total = $patientAdministrations->count();
            $omitted = $patientAdministrations->where('estado', Administracion::ESTADO_OMITIDA)->count();
            
            $patientAdherence = $total > 0 ? round(($administered / $total) * 100, 1) : 0;
            
            $patientData = [
                'id' => $patient->id,
                'name' => $patient->nombre,
                'total_doses' => $total,
                'administered' => $administered,
                'omitted' => $omitted,
                'adherence_rate' => $patientAdherence,
                'status' => $this->getAdherenceStatus($patientAdherence),
                'needs_attention' => $patientAdherence < 80 || $omitted >= 2
            ];
            
            $summary['patients'][] = $patientData;
            
            // Classify patients
            if ($patientAdherence >= 90) {
                $summary['top_performers'][] = $patientData;
            } elseif ($patientAdherence < 70 || $omitted >= 2) {
                $summary['needs_attention'][] = $patientData;
            }
        }

        // Group by medications
        $medicationGroups = $administrations->groupBy(function($item) {
            return $item->medicamentoTratamiento?->medicamento?->id ?? 'unknown';
        });

        foreach ($medicationGroups as $medicamentoId => $medicationAdministrations) {
            if ($medicamentoId === 'unknown') continue;
            
            $medication = $medicationAdministrations->first()->medicamentoTratamiento?->medicamento;
            if (!$medication) continue;
            
            $administered = $medicationAdministrations->where('estado', Administracion::ESTADO_ADMINISTRADA)->count();
            $total = $medicationAdministrations->count();
            
            $summary['medications'][] = [
                'name' => $medication->nombre,
                'total_doses' => $total,
                'administered' => $administered,
                'adherence_rate' => $total > 0 ? round(($administered / $total) * 100, 1) : 0
            ];
        }

        // Count recent alerts
        $summary['alerts_generated'] = \App\Models\Alerta::whereDate('created_at', $date)->count();

        return $summary;
    }

    /**
     * Get adherence status color/text
     */
    private function getAdherenceStatus(float $adherenceRate): array
    {
        if ($adherenceRate >= 90) {
            return ['text' => 'Excelente', 'color' => 'green', 'emoji' => '🟢'];
        } elseif ($adherenceRate >= 80) {
            return ['text' => 'Bueno', 'color' => 'blue', 'emoji' => '🔵'];
        } elseif ($adherenceRate >= 70) {
            return ['text' => 'Regular', 'color' => 'yellow', 'emoji' => '🟡'];
        } else {
            return ['text' => 'Crítico', 'color' => 'red', 'emoji' => '🔴'];
        }
    }

    /**
     * Display summary in console
     */
    private function displaySummary(array $summary): void
    {
        $this->info("\n📈 RESUMEN DE ADHERENCIA - {$summary['analysis_date']->format('d/m/Y')}");
        $this->info("=" . str_repeat("=", 50));
        
        $this->line("📊 Métricas Generales:");
        $this->line("   • Total dosis programadas: {$summary['total_scheduled']}");
        $this->line("   • Dosis administradas: {$summary['total_administered']}");
        $this->line("   • Dosis omitidas: {$summary['total_omitted']}");
        $this->line("   • Adherencia general: {$summary['adherence_rate']}%");
        $this->line("   • Puntualidad: {$summary['punctuality_rate']}%");
        
        if (!empty($summary['needs_attention'])) {
            $this->warn("\n⚠️ Pacientes que requieren atención:");
            foreach ($summary['needs_attention'] as $patient) {
                $this->warn("   • {$patient['name']}: {$patient['adherence_rate']}% adherencia");
            }
        }

        if (!empty($summary['top_performers'])) {
            $this->info("\n🌟 Pacientes destacados:");
            foreach ($summary['top_performers'] as $patient) {
                $this->info("   • {$patient['name']}: {$patient['adherence_rate']}% adherencia");
            }
        }

        $this->line("\n🚨 Alertas generadas: {$summary['alerts_generated']}");
    }

    /**
     * Send summary emails
     */
    private function sendSummaryEmails(array $summary, Carbon $date, bool $dryRun, ?string $specificEmail): int
    {
        $emailsSent = 0;
        
        if ($specificEmail) {
            // Send to specific email for testing
            if (!$dryRun) {
                $mail = new DailyAdherenceSummaryMail($summary, $date);
                Mail::to($specificEmail)->send($mail);
                $emailsSent++;
                $this->info("📧 Email enviado a: {$specificEmail}");
            } else {
                $this->line("📧 Se enviaría email a: {$specificEmail}");
                $emailsSent++;
            }
        } else {
            // Send to medical staff
            $medicalStaff = User::whereHas('role', function($query) {
                $query->where('nombre', 'medico');
            })->whereNotNull('email_verified_at')->get();

            foreach ($medicalStaff as $doctor) {
                if (!$dryRun) {
                    try {
                        $mail = new DailyAdherenceSummaryMail($summary, $date, $doctor);
                        Mail::to($doctor->email)->send($mail);
                        $emailsSent++;
                        $this->line("📧 Email enviado a: {$doctor->name} ({$doctor->email})");
                    } catch (\Exception $e) {
                        $this->error("❌ Error enviando a {$doctor->email}: {$e->getMessage()}");
                    }
                } else {
                    $this->line("📧 Se enviaría email a: {$doctor->name} ({$doctor->email})");
                    $emailsSent++;
                }
            }

            // Also send to administrators
            $admins = User::whereHas('role', function($query) {
                $query->where('nombre', 'admin');
            })->whereNotNull('email_verified_at')->get();

            foreach ($admins as $admin) {
                if (!$dryRun) {
                    try {
                        $mail = new DailyAdherenceSummaryMail($summary, $date, $admin);
                        Mail::to($admin->email)->send($mail);
                        $emailsSent++;
                        $this->line("📧 Email enviado a admin: {$admin->name} ({$admin->email})");
                    } catch (\Exception $e) {
                        $this->error("❌ Error enviando a {$admin->email}: {$e->getMessage()}");
                    }
                } else {
                    $this->line("📧 Se enviaría email a admin: {$admin->name} ({$admin->email})");
                    $emailsSent++;
                }
            }
        }

        return $emailsSent;
    }
} 
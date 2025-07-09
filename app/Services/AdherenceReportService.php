<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\User;
use App\Models\Administracion;
use App\Models\EstadisticaConsumo;
use App\Models\ResumenAdherenciaPaciente;
use App\Mail\AdherenceReportMail;
use App\Services\TemporalAdherenceService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AdherenceReportService
{
    protected $temporalService;

    public function __construct(TemporalAdherenceService $temporalService)
    {
        $this->temporalService = $temporalService;
    }

    /**
     * Generate comprehensive adherence report for a patient
     */
    public function generateAdherenceReport(
        Paciente $paciente,
        Carbon $periodoInicio,
        Carbon $periodoFin,
        string $tipoReporte = 'semanal'
    ): array {
        return [
            'paciente' => $this->getPatientSummary($paciente),
            'periodo' => $this->getPeriodSummary($periodoInicio, $periodoFin, $tipoReporte),
            'adherencia_general' => $this->getGeneralAdherence($paciente, $periodoInicio, $periodoFin),
            'metricas_temporales' => $this->getTemporalMetrics($paciente, $periodoInicio, $periodoFin),
            'medicamentos_detalle' => $this->getMedicationDetails($paciente, $periodoInicio, $periodoFin),
            'tendencias' => $this->getTrends($paciente, $periodoInicio, $periodoFin),
            'alertas' => $this->getActiveAlerts($paciente),
            'recomendaciones' => $this->generateRecommendations($paciente, $periodoInicio, $periodoFin),
        ];
    }

    /**
     * Send adherence report to specified recipients
     */
    public function sendReport(
        Paciente $paciente,
        array $reportData,
        Carbon $periodoInicio,
        Carbon $periodoFin,
        array $recipients,
        string $tipoReporte = 'semanal'
    ): int {
        $sentCount = 0;

        foreach ($recipients as $recipient) {
            try {
                $mail = new AdherenceReportMail(
                    $paciente,
                    $reportData,
                    $periodoInicio,
                    $periodoFin,
                    $tipoReporte,
                    $recipient
                );

                Mail::to($recipient->email)->send($mail);
                $sentCount++;

                \Log::info('Adherence report sent', [
                    'paciente_id' => $paciente->id,
                    'recipient_email' => $recipient->email,
                    'tipo_reporte' => $tipoReporte,
                    'periodo' => $periodoInicio->format('Y-m-d') . ' to ' . $periodoFin->format('Y-m-d')
                ]);

            } catch (\Exception $e) {
                \Log::error('Failed to send adherence report', [
                    'paciente_id' => $paciente->id,
                    'recipient_email' => $recipient->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $sentCount;
    }

    /**
     * Send weekly reports to all patients with active treatments
     */
    public function sendWeeklyReports(): int
    {
        $pacientes = Paciente::whereHas('tratamientos', function($query) {
            $query->where('estado', 'Activo');
        })->with(['usuario', 'tratamientos.medico', 'apoderados.usuario', 'cuidadores.usuario'])->get();

        $totalSent = 0;
        $periodoInicio = now()->startOfWeek()->subWeek();
        $periodoFin = now()->endOfWeek()->subWeek();

        foreach ($pacientes as $paciente) {
            $reportData = $this->generateAdherenceReport($paciente, $periodoInicio, $periodoFin, 'semanal');
            $recipients = $this->getReportRecipients($paciente);

            if (!empty($recipients)) {
                $sent = $this->sendReport($paciente, $reportData, $periodoInicio, $periodoFin, $recipients, 'semanal');
                $totalSent += $sent;
            }
        }

        return $totalSent;
    }

    /**
     * Send adherence alerts for patients with poor adherence
     */
    public function sendAdherenceAlerts(): int
    {
        $alertsSent = 0;
        $periodoInicio = now()->subDays(3);
        $periodoFin = now();

        $pacientes = Paciente::whereHas('tratamientos', function($query) {
            $query->where('estado', 'Activo');
        })->get();

        foreach ($pacientes as $paciente) {
            $adherenceData = $this->getGeneralAdherence($paciente, $periodoInicio, $periodoFin);
            
            // Send alert if adherence is below 70%
            if ($adherenceData['adherencia_porcentaje'] < 70 && $adherenceData['total_dosis'] > 0) {
                $alertData = [
                    'tipo_alerta' => 'adherencia_baja',
                    'adherencia_porcentaje' => $adherenceData['adherencia_porcentaje'],
                    'dosis_omitidas' => $adherenceData['dosis_omitidas'],
                    'total_dosis' => $adherenceData['total_dosis'],
                    'periodo_analizado' => $periodoInicio->format('d/m/Y') . ' - ' . $periodoFin->format('d/m/Y')
                ];

                $recipients = $this->getAlertRecipients($paciente);
                
                foreach ($recipients as $recipient) {
                    try {
                        $mail = AdherenceReportMail::alert($paciente, $alertData, $recipient);
                        Mail::to($recipient->email)->send($mail);
                        $alertsSent++;
                    } catch (\Exception $e) {
                        \Log::error('Failed to send adherence alert', [
                            'paciente_id' => $paciente->id,
                            'recipient_email' => $recipient->email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        return $alertsSent;
    }

    /**
     * Get patient summary for report
     */
    private function getPatientSummary(Paciente $paciente): array
    {
        return [
            'id' => $paciente->id,
            'nombre' => $paciente->nombre,
            'edad' => $paciente->fecha_nacimiento ? Carbon::parse($paciente->fecha_nacimiento)->age : null,
            'tratamientos_activos' => $paciente->tratamientos()->where('estado', 'Activo')->count(),
            'medicamentos_activos' => $paciente->tratamientos()
                ->where('estado', 'Activo')
                ->withCount('medicamentoTratamientos')
                ->get()
                ->sum('medicamento_tratamientos_count'),
        ];
    }

    /**
     * Get period summary
     */
    private function getPeriodSummary(Carbon $inicio, Carbon $fin, string $tipo): array
    {
        return [
            'inicio' => $inicio->format('Y-m-d'),
            'fin' => $fin->format('Y-m-d'),
            'dias' => $inicio->diffInDays($fin) + 1,
            'tipo' => $tipo,
            'descripcion' => $this->formatPeriodDescription($inicio, $fin, $tipo)
        ];
    }

    /**
     * Get general adherence metrics
     */
    private function getGeneralAdherence(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        $administraciones = Administracion::where('paciente_id', $paciente->id)
            ->whereNotNull('fecha_hora_programada')
            ->whereBetween('fecha_hora_programada', [$inicio, $fin])
            ->get();

        $totalDosis = $administraciones->count();
        $dosisAdministradas = $administraciones->whereIn('estado', ['Administrada', 'Tardía'])->count();
        $dosisOmitidas = $administraciones->where('estado', 'Omitida')->count();
        $dosisTardias = $administraciones->where('estado', 'Tardía')->count();

        return [
            'total_dosis' => $totalDosis,
            'dosis_administradas' => $dosisAdministradas,
            'dosis_omitidas' => $dosisOmitidas,
            'dosis_tardias' => $dosisTardias,
            'adherencia_porcentaje' => $totalDosis > 0 ? round(($dosisAdministradas / $totalDosis) * 100, 1) : 0,
            'puntualidad_porcentaje' => $dosisAdministradas > 0 ? round((($dosisAdministradas - $dosisTardias) / $dosisAdministradas) * 100, 1) : 0,
        ];
    }

    /**
     * Get temporal metrics using the existing service
     */
    private function getTemporalMetrics(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        try {
            return $this->temporalService->calculateMetrics($paciente->id, $inicio, $fin);
        } catch (\Exception $e) {
            \Log::error('Error calculating temporal metrics for report', [
                'paciente_id' => $paciente->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get medication-specific details
     */
    private function getMedicationDetails(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        $medicamentos = [];

        $tratamientos = $paciente->tratamientos()->where('estado', 'Activo')->with('medicamentoTratamientos.medicamento')->get();

        foreach ($tratamientos as $tratamiento) {
            foreach ($tratamiento->medicamentoTratamientos as $medTrat) {
                $administraciones = Administracion::where('paciente_id', $paciente->id)
                    ->where('medicamento_tratamiento_id', $medTrat->id)
                    ->whereNotNull('fecha_hora_programada')
                    ->whereBetween('fecha_hora_programada', [$inicio, $fin])
                    ->get();

                $total = $administraciones->count();
                $administradas = $administraciones->whereIn('estado', ['Administrada', 'Tardía'])->count();

                $medicamentos[] = [
                    'nombre' => $medTrat->medicamento->nombre,
                    'dosis' => $medTrat->dosis_cantidad . ' ' . $medTrat->dosis_unidad,
                    'frecuencia' => $medTrat->frecuencia_horas . ' horas',
                    'total_dosis' => $total,
                    'dosis_administradas' => $administradas,
                    'adherencia_porcentaje' => $total > 0 ? round(($administradas / $total) * 100, 1) : 0,
                ];
            }
        }

        return $medicamentos;
    }

    /**
     * Get trends comparison with previous period
     */
    private function getTrends(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        $dias = $inicio->diffInDays($fin) + 1;
        $inicioAnterior = $inicio->copy()->subDays($dias);
        $finAnterior = $inicio->copy()->subDay();

        $actual = $this->getGeneralAdherence($paciente, $inicio, $fin);
        $anterior = $this->getGeneralAdherence($paciente, $inicioAnterior, $finAnterior);

        return [
            'adherencia_cambio' => $actual['adherencia_porcentaje'] - $anterior['adherencia_porcentaje'],
            'puntualidad_cambio' => $actual['puntualidad_porcentaje'] - $anterior['puntualidad_porcentaje'],
            'mejora_adherencia' => $actual['adherencia_porcentaje'] > $anterior['adherencia_porcentaje'],
            'mejora_puntualidad' => $actual['puntualidad_porcentaje'] > $anterior['puntualidad_porcentaje'],
        ];
    }

    /**
     * Get active alerts for patient
     */
    private function getActiveAlerts(Paciente $paciente): array
    {
        return $paciente->alertas()
            ->where('revisada', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($alerta) {
                return [
                    'tipo' => $alerta->tipo,
                    'nivel' => $alerta->nivel_prioridad,
                    'mensaje' => $alerta->descripcion,
                    'fecha' => $alerta->created_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();
    }

    /**
     * Generate recommendations based on adherence data
     */
    private function generateRecommendations(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        $adherenceData = $this->getGeneralAdherence($paciente, $inicio, $fin);
        $recommendations = [];

        if ($adherenceData['adherencia_porcentaje'] < 80) {
            $recommendations[] = [
                'tipo' => 'adherencia',
                'mensaje' => 'La adherencia está por debajo del objetivo. Considere recordatorios adicionales o ajustes en el horario.',
                'prioridad' => 'alta'
            ];
        }

        if ($adherenceData['puntualidad_porcentaje'] < 70) {
            $recommendations[] = [
                'tipo' => 'puntualidad',
                'mensaje' => 'Muchas dosis se administran fuera del horario programado. Revise la rutina diaria del paciente.',
                'prioridad' => 'media'
            ];
        }

        if ($adherenceData['dosis_omitidas'] > $adherenceData['total_dosis'] * 0.2) {
            $recommendations[] = [
                'tipo' => 'omisiones',
                'mensaje' => 'Alto número de dosis omitidas. Considere implementar alarmas o recordatorios automáticos.',
                'prioridad' => 'alta'
            ];
        }

        return $recommendations;
    }

    /**
     * Get report recipients for a patient
     */
    private function getReportRecipients(Paciente $paciente): array
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

    /**
     * Get alert recipients for a patient (more restricted than report recipients)
     */
    private function getAlertRecipients(Paciente $paciente): array
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
     * Format period description for display
     */
    private function formatPeriodDescription(Carbon $inicio, Carbon $fin, string $tipo): string
    {
        return match ($tipo) {
            'diario' => $inicio->format('d \d\e F \d\e Y'),
            'semanal' => 'Semana del ' . $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y'),
            'mensual' => $inicio->format('F \d\e Y'),
            default => $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y')
        };
    }
} 
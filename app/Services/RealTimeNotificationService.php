<?php

namespace App\Services;

use App\Models\Administracion;
use App\Models\Alerta;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Mail\RealTimeNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RealTimeNotificationService
{
    /**
     * Send notification for omitted dose
     */
    public function sendDoseOmittedNotification(Administracion $administracion): int
    {
        try {
            $paciente = $administracion->paciente;
            $medicamento = $administracion->medicamento;
            $tratamiento = $administracion->tratamiento;

            // Check for consecutive omissions to determine priority
            $consecutiveOmissions = $this->getConsecutiveOmissions($paciente, $medicamento);
            $priority = $consecutiveOmissions >= 3 ? 'critica' : 'alta';

            // Create alert record
            $alerta = Alerta::create([
                'paciente_id' => $paciente->id,
                'tratamiento_id' => $tratamiento?->id,
                'medicamento_id' => $medicamento?->id,
                'tipo' => Alerta::TIPO_DOSIS_OMITIDA,
                'prioridad' => $priority,
                'titulo' => 'Dosis Omitida',
                'mensaje' => "Dosis de {$medicamento?->nombre} omitida el " . $administracion->fecha_hora_programada->format('d/m/Y H:i'),
                'fecha_hora' => now(),
                'leida' => false,
                'datos_adicionales' => [
                    'administracion_id' => $administracion->id,
                    'consecutive_omissions' => $consecutiveOmissions,
                    'medication_name' => $medicamento?->nombre,
                    'scheduled_time' => $administracion->fecha_hora_programada
                ]
            ]);

            // Get recipients based on priority
            $recipients = $this->getDoseOmittedRecipients($paciente, $priority);
            
            // Send emails
            $sent = 0;
            foreach ($recipients as $recipient) {
                $mail = RealTimeNotificationMail::doseOmitted($paciente, $administracion, $alerta, $recipient);
                Mail::to($recipient->email)->send($mail);
                $sent++;
            }

            Log::info('Dose omitted notification sent', [
                'paciente_id' => $paciente->id,
                'administracion_id' => $administracion->id,
                'recipients' => $sent,
                'priority' => $priority,
                'consecutive_omissions' => $consecutiveOmissions
            ]);

            return $sent;

        } catch (\Exception $e) {
            Log::error('Failed to send dose omitted notification', [
                'administracion_id' => $administracion->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Send notification for adverse effects
     */
    public function sendAdverseEffectsNotification(Administracion $administracion): int
    {
        try {
            $paciente = $administracion->paciente;
            $medicamento = $administracion->medicamento;
            $tratamiento = $administracion->tratamiento;

            // Create critical alert
            $alerta = Alerta::create([
                'paciente_id' => $paciente->id,
                'tratamiento_id' => $tratamiento?->id,
                'medicamento_id' => $medicamento?->id,
                'tipo' => Alerta::TIPO_EFECTO_ADVERSO,
                'prioridad' => Alerta::PRIORIDAD_CRITICA,
                'titulo' => 'Efectos Adversos Reportados',
                'mensaje' => "Efectos adversos reportados tras administración de {$medicamento?->nombre}",
                'fecha_hora' => now(),
                'leida' => false,
                'datos_adicionales' => [
                    'administracion_id' => $administracion->id,
                    'efectos_adversos' => $administracion->efectos_adversos,
                    'intensidad' => $administracion->intensidad_sintoma,
                    'administered_time' => $administracion->fecha_hora_administrada
                ]
            ]);

            // Get medical team (critical priority)
            $recipients = $this->getAdverseEffectsRecipients($paciente);
            
            // Send emails immediately
            $sent = 0;
            foreach ($recipients as $recipient) {
                $mail = RealTimeNotificationMail::adverseEffects($paciente, $administracion, $alerta, $recipient);
                Mail::to($recipient->email)->send($mail);
                $sent++;
            }

            Log::critical('Adverse effects notification sent', [
                'paciente_id' => $paciente->id,
                'administracion_id' => $administracion->id,
                'efectos_adversos' => $administracion->efectos_adversos,
                'recipients' => $sent
            ]);

            return $sent;

        } catch (\Exception $e) {
            Log::error('Failed to send adverse effects notification', [
                'administracion_id' => $administracion->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Send notification for late dose (outside tolerance window)
     */
    public function sendLateDoseNotification(Administracion $administracion): int
    {
        try {
            $paciente = $administracion->paciente;
            $medicamento = $administracion->medicamento;
            $minutesLate = $administracion->minutos_retraso;

            // Create alert
            $alerta = Alerta::create([
                'paciente_id' => $paciente->id,
                'tipo' => 'dosis_tardia',
                'prioridad' => 'media',
                'titulo' => 'Dosis Tardía',
                'mensaje' => "Dosis de {$medicamento?->nombre} administrada con {$minutesLate} minutos de retraso",
                'fecha_hora' => now(),
                'leida' => false,
                'datos_adicionales' => [
                    'administracion_id' => $administracion->id,
                    'minutes_late' => $minutesLate,
                    'scheduled_time' => $administracion->fecha_hora_programada,
                    'actual_time' => $administracion->fecha_hora_administrada
                ]
            ]);

            // Get caregivers and patient (if has account)
            $recipients = $this->getLateDoseRecipients($paciente);
            
            // Send emails
            $sent = 0;
            foreach ($recipients as $recipient) {
                $mail = RealTimeNotificationMail::lateDose($paciente, $administracion, $alerta, $recipient);
                Mail::to($recipient->email)->send($mail);
                $sent++;
            }

            return $sent;

        } catch (\Exception $e) {
            Log::error('Failed to send late dose notification', [
                'administracion_id' => $administracion->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Send notification for treatment state change
     */
    public function sendTreatmentStateChangeNotification(Tratamiento $tratamiento, string $previousState, string $motivo = null): int
    {
        try {
            $paciente = $tratamiento->paciente;

            // Create alert
            $alerta = Alerta::create([
                'paciente_id' => $paciente->id,
                'tratamiento_id' => $tratamiento->id,
                'tipo' => 'cambio_tratamiento',
                'prioridad' => 'alta',
                'titulo' => 'Cambio de Estado de Tratamiento',
                'mensaje' => "Tratamiento '{$tratamiento->nombre}' cambió de {$previousState} a {$tratamiento->estado}",
                'fecha_hora' => now(),
                'leida' => false,
                'datos_adicionales' => [
                    'tratamiento_id' => $tratamiento->id,
                    'previous_state' => $previousState,
                    'new_state' => $tratamiento->estado,
                    'motivo' => $motivo,
                    'changed_by' => auth()->user()?->name
                ]
            ]);

            // Get all stakeholders
            $recipients = $this->getTreatmentChangeRecipients($paciente);
            
            // Send emails
            $sent = 0;
            foreach ($recipients as $recipient) {
                $mail = RealTimeNotificationMail::treatmentStateChange($paciente, $tratamiento, $alerta, $recipient);
                Mail::to($recipient->email)->send($mail);
                $sent++;
            }

            Log::info('Treatment state change notification sent', [
                'tratamiento_id' => $tratamiento->id,
                'paciente_id' => $paciente->id,
                'previous_state' => $previousState,
                'new_state' => $tratamiento->estado,
                'recipients' => $sent
            ]);

            return $sent;

        } catch (\Exception $e) {
            Log::error('Failed to send treatment state change notification', [
                'tratamiento_id' => $tratamiento->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get consecutive omissions for a patient and medication
     */
    private function getConsecutiveOmissions(Paciente $paciente, $medicamento): int
    {
        return Administracion::where('paciente_id', $paciente->id)
            ->whereHas('medicamentoTratamiento', function($query) use ($medicamento) {
                $query->where('medicamento_id', $medicamento?->id);
            })
            ->where('estado', Administracion::ESTADO_OMITIDA)
            ->where('fecha_hora_programada', '>=', now()->subDays(7))
            ->orderBy('fecha_hora_programada', 'desc')
            ->count();
    }

    /**
     * Get recipients for dose omitted notifications
     */
    private function getDoseOmittedRecipients(Paciente $paciente, string $priority): array
    {
        $recipients = [];

        // Always include medical staff for dose omissions
        foreach ($paciente->medicosVigentes as $medico) {
            if ($medico->user && $medico->user->email_verified_at) {
                $recipients[] = $medico->user;
            }
        }

        // Include caregivers
        foreach ($paciente->cuidadoresVigentes as $cuidador) {
            if ($cuidador->user && $cuidador->user->email_verified_at) {
                $recipients[] = $cuidador->user;
            }
        }

        // For critical priority, also include guardians
        if ($priority === 'critica') {
            foreach ($paciente->apoderados as $apoderado) {
                if ($apoderado->user && $apoderado->user->email_verified_at) {
                    $recipients[] = $apoderado->user;
                }
            }
        }

        return array_unique($recipients, SORT_REGULAR);
    }

    /**
     * Get recipients for adverse effects notifications (medical team only)
     */
    private function getAdverseEffectsRecipients(Paciente $paciente): array
    {
        $recipients = [];

        // Medical staff only for adverse effects
        foreach ($paciente->medicosVigentes as $medico) {
            if ($medico->user && $medico->user->email_verified_at) {
                $recipients[] = $medico->user;
            }
        }

        return array_unique($recipients, SORT_REGULAR);
    }

    /**
     * Get recipients for late dose notifications
     */
    private function getLateDoseRecipients(Paciente $paciente): array
    {
        $recipients = [];

        // Include caregivers
        foreach ($paciente->cuidadoresVigentes as $cuidador) {
            if ($cuidador->user && $cuidador->user->email_verified_at) {
                $recipients[] = $cuidador->user;
            }
        }

        // Include patient if has account
        if ($paciente->user && $paciente->user->email_verified_at) {
            $recipients[] = $paciente->user;
        }

        return array_unique($recipients, SORT_REGULAR);
    }

    /**
     * Get recipients for treatment change notifications (all stakeholders)
     */
    private function getTreatmentChangeRecipients(Paciente $paciente): array
    {
        $recipients = [];

        // Patient
        if ($paciente->user && $paciente->user->email_verified_at) {
            $recipients[] = $paciente->user;
        }

        // Medical staff
        foreach ($paciente->medicosVigentes as $medico) {
            if ($medico->user && $medico->user->email_verified_at) {
                $recipients[] = $medico->user;
            }
        }

        // Guardians
        foreach ($paciente->apoderados as $apoderado) {
            if ($apoderado->user && $apoderado->user->email_verified_at) {
                $recipients[] = $apoderado->user;
            }
        }

        // Caregivers
        foreach ($paciente->cuidadoresVigentes as $cuidador) {
            if ($cuidador->user && $cuidador->user->email_verified_at) {
                $recipients[] = $cuidador->user;
            }
        }

        return array_unique($recipients, SORT_REGULAR);
    }
} 
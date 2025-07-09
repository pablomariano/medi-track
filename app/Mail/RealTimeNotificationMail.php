<?php

namespace App\Mail;

use App\Models\Administracion;
use App\Models\Alerta;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RealTimeNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $paciente;
    public $administracion;
    public $tratamiento;
    public $alerta;
    public $recipient;
    public $notificationType;
    public $priorityLevel;

    /**
     * Create dose omitted notification
     */
    public static function doseOmitted(Paciente $paciente, Administracion $administracion, Alerta $alerta, User $recipient): self
    {
        $mail = new self();
        $mail->paciente = $paciente;
        $mail->administracion = $administracion;
        $mail->alerta = $alerta;
        $mail->recipient = $recipient;
        $mail->notificationType = 'dose_omitted';
        $mail->priorityLevel = $alerta->prioridad;
        
        return $mail;
    }

    /**
     * Create adverse effects notification
     */
    public static function adverseEffects(Paciente $paciente, Administracion $administracion, Alerta $alerta, User $recipient): self
    {
        $mail = new self();
        $mail->paciente = $paciente;
        $mail->administracion = $administracion;
        $mail->alerta = $alerta;
        $mail->recipient = $recipient;
        $mail->notificationType = 'adverse_effects';
        $mail->priorityLevel = 'critica';
        
        return $mail;
    }

    /**
     * Create late dose notification
     */
    public static function lateDose(Paciente $paciente, Administracion $administracion, Alerta $alerta, User $recipient): self
    {
        $mail = new self();
        $mail->paciente = $paciente;
        $mail->administracion = $administracion;
        $mail->alerta = $alerta;
        $mail->recipient = $recipient;
        $mail->notificationType = 'late_dose';
        $mail->priorityLevel = 'media';
        
        return $mail;
    }

    /**
     * Create treatment state change notification
     */
    public static function treatmentStateChange(Paciente $paciente, Tratamiento $tratamiento, Alerta $alerta, User $recipient): self
    {
        $mail = new self();
        $mail->paciente = $paciente;
        $mail->tratamiento = $tratamiento;
        $mail->alerta = $alerta;
        $mail->recipient = $recipient;
        $mail->notificationType = 'treatment_change';
        $mail->priorityLevel = 'alta';
        
        return $mail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'dose_omitted' => '🚨 MediTrack: Dosis Omitida - ' . $this->paciente->nombre,
            'adverse_effects' => '⚠️ MediTrack: Efectos Adversos Reportados - ' . $this->paciente->nombre,
            'late_dose' => '⏰ MediTrack: Dosis Tardía - ' . $this->paciente->nombre,
            'treatment_change' => '📋 MediTrack: Cambio de Tratamiento - ' . $this->paciente->nombre,
        ];

        $priority = match($this->priorityLevel) {
            'critica' => 1, // Highest priority
            'alta' => 2,
            'media' => 3,
            default => 3
        };

        return new Envelope(
            subject: $subjects[$this->notificationType] ?? 'MediTrack: Notificación',
            priority: $priority
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $views = [
            'dose_omitted' => 'emails.real-time.dose-omitted',
            'adverse_effects' => 'emails.real-time.adverse-effects',
            'late_dose' => 'emails.real-time.late-dose',
            'treatment_change' => 'emails.real-time.treatment-change',
        ];

        return new Content(
            view: $views[$this->notificationType] ?? 'emails.real-time.generic',
            with: [
                'paciente' => $this->paciente,
                'administracion' => $this->administracion,
                'tratamiento' => $this->tratamiento,
                'alerta' => $this->alerta,
                'recipient' => $this->recipient,
                'notificationType' => $this->notificationType,
                'priorityLevel' => $this->priorityLevel,
                'medicamento' => $this->administracion?->medicamento,
                'fechaGeneracion' => now(),
                'urgencyLevel' => $this->getUrgencyLevel(),
                'actionRequired' => $this->getActionRequired(),
                'nextSteps' => $this->getNextSteps()
            ]
        );
    }

    /**
     * Get urgency level for display
     */
    private function getUrgencyLevel(): string
    {
        return match($this->priorityLevel) {
            'critica' => 'CRÍTICA',
            'alta' => 'ALTA',
            'media' => 'MEDIA',
            'baja' => 'BAJA',
            default => 'NORMAL'
        };
    }

    /**
     * Get action required message
     */
    private function getActionRequired(): string
    {
        return match($this->notificationType) {
            'dose_omitted' => 'Revisar motivo de omisión y considerar administración alternativa',
            'adverse_effects' => 'Evaluar inmediatamente al paciente y considerar ajuste de tratamiento',
            'late_dose' => 'Verificar el estado del paciente y registrar la administración',
            'treatment_change' => 'Revisar el cambio y comunicar al equipo de cuidado',
            default => 'Revisar la situación del paciente'
        };
    }

    /**
     * Get next steps recommendations
     */
    private function getNextSteps(): array
    {
        return match($this->notificationType) {
            'dose_omitted' => [
                'Contactar al cuidador o paciente inmediatamente',
                'Verificar el motivo de la omisión',
                'Evaluar si es posible administrar la dosis pendiente',
                'Ajustar el cronograma si es necesario',
                'Registrar las acciones tomadas'
            ],
            'adverse_effects' => [
                'Contactar al paciente inmediatamente',
                'Evaluar la severidad de los efectos adversos',
                'Considerar suspender o ajustar la medicación',
                'Documentar todos los síntomas reportados',
                'Planificar seguimiento médico inmediato'
            ],
            'late_dose' => [
                'Confirmar que la dosis fue administrada',
                'Verificar el estado del paciente',
                'Ajustar horarios futuros si es necesario',
                'Proporcionar recordatorios adicionales'
            ],
            'treatment_change' => [
                'Revisar los detalles del cambio',
                'Comunicar al equipo de cuidado',
                'Actualizar las instrucciones de medicación',
                'Programar seguimiento apropiado'
            ],
            default => [
                'Revisar la situación',
                'Contactar al equipo médico si es necesario'
            ]
        };
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 
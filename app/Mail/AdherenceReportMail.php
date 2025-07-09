<?php

namespace App\Mail;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AdherenceReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paciente;
    public $reporteData;
    public $periodoInicio;
    public $periodoFin;
    public $tipoReporte;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Paciente $paciente,
        array $reporteData,
        Carbon $periodoInicio,
        Carbon $periodoFin,
        string $tipoReporte = 'semanal',
        ?User $recipient = null
    ) {
        $this->paciente = $paciente;
        $this->reporteData = $reporteData;
        $this->periodoInicio = $periodoInicio;
        $this->periodoFin = $periodoFin;
        $this->tipoReporte = $tipoReporte;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->generateSubject();
        
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->getViewTemplate();
        
        return new Content(
            view: $view,
            with: [
                'paciente' => $this->paciente,
                'reporte' => $this->reporteData,
                'periodoInicio' => $this->periodoInicio,
                'periodoFin' => $this->periodoFin,
                'tipoReporte' => $this->tipoReporte,
                'recipient' => $this->recipient,
                'fechaGeneracion' => now(),
            ],
        );
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

    /**
     * Generate email subject based on report type and recipient
     */
    private function generateSubject(): string
    {
        $periodo = $this->formatPeriodo();
        $pacienteNombre = $this->paciente->nombre;
        
        return match ($this->tipoReporte) {
            'diario' => "Reporte Diario de Adherencia - {$pacienteNombre} ({$periodo})",
            'semanal' => "Reporte Semanal de Adherencia - {$pacienteNombre} ({$periodo})",
            'mensual' => "Reporte Mensual de Adherencia - {$pacienteNombre} ({$periodo})",
            'alerta' => "🚨 Alerta de Adherencia - {$pacienteNombre}",
            default => "Reporte de Adherencia - {$pacienteNombre}"
        };
    }

    /**
     * Get appropriate view template based on report type and recipient
     */
    private function getViewTemplate(): string
    {
        $role = $this->recipient?->role?->nombre ?? 'general';
        
        return match ($role) {
            'paciente' => 'emails.adherence.patient-report',
            'medico' => 'emails.adherence.medical-report',
            'cuidador' => 'emails.adherence.caregiver-report',
            'apoderado' => 'emails.adherence.guardian-report',
            default => 'emails.adherence.general-report'
        };
    }

    /**
     * Format period for subject line
     */
    private function formatPeriodo(): string
    {
        if ($this->periodoInicio->isSameDay($this->periodoFin)) {
            return $this->periodoInicio->format('d/m/Y');
        }
        
        return $this->periodoInicio->format('d/m/Y') . ' - ' . $this->periodoFin->format('d/m/Y');
    }

    /**
     * Create adherence alert email
     */
    public static function alert(Paciente $paciente, array $alertData, User $recipient): self
    {
        return new self(
            $paciente,
            $alertData,
            now()->subDay(),
            now(),
            'alerta',
            $recipient
        );
    }

    /**
     * Create weekly adherence report
     */
    public static function weekly(Paciente $paciente, array $reporteData, User $recipient): self
    {
        return new self(
            $paciente,
            $reporteData,
            now()->startOfWeek(),
            now()->endOfWeek(),
            'semanal',
            $recipient
        );
    }

    /**
     * Create monthly adherence report
     */
    public static function monthly(Paciente $paciente, array $reporteData, User $recipient): self
    {
        return new self(
            $paciente,
            $reporteData,
            now()->startOfMonth(),
            now()->endOfMonth(),
            'mensual',
            $recipient
        );
    }
} 
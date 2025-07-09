<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\User;

class DailyAdherenceSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $summaryData;
    public Carbon $analysisDate;
    public ?User $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(array $summaryData, Carbon $analysisDate, ?User $recipient = null)
    {
        $this->summaryData = $summaryData;
        $this->analysisDate = $analysisDate;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $adherenceEmoji = $this->getAdherenceEmoji($this->summaryData['adherence_rate']);
        
        return new Envelope(
            subject: "MediTrack {$adherenceEmoji} Resumen Diario de Adherencia - {$this->analysisDate->format('d/m/Y')}",
            tags: ['daily-summary', 'adherence-report'],
            metadata: [
                'report_type' => 'daily_adherence_summary',
                'analysis_date' => $this->analysisDate->format('Y-m-d'),
                'total_patients' => count($this->summaryData['patients']),
                'adherence_rate' => $this->summaryData['adherence_rate']
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.daily-adherence-summary',
            with: [
                'summaryData' => $this->summaryData,
                'analysisDate' => $this->analysisDate,
                'recipient' => $this->recipient,
                'adherenceStatus' => $this->getOverallAdherenceStatus(),
                'priorityActions' => $this->getPriorityActions(),
                'highlights' => $this->getHighlights(),
            ],
        );
    }

    /**
     * Get emoji based on adherence rate
     */
    private function getAdherenceEmoji(float $adherenceRate): string
    {
        if ($adherenceRate >= 90) return '🟢';
        if ($adherenceRate >= 80) return '🔵';
        if ($adherenceRate >= 70) return '🟡';
        return '🔴';
    }

    /**
     * Get overall adherence status
     */
    private function getOverallAdherenceStatus(): array
    {
        $rate = $this->summaryData['adherence_rate'];
        
        if ($rate >= 90) {
            return [
                'level' => 'excellent',
                'text' => 'Excelente',
                'color' => '#16a34a',
                'bgColor' => '#dcfce7',
                'icon' => '🌟',
                'message' => 'El nivel de adherencia es excepcional. ¡Mantener el excelente trabajo!'
            ];
        } elseif ($rate >= 80) {
            return [
                'level' => 'good',
                'text' => 'Bueno',
                'color' => '#2563eb',
                'bgColor' => '#dbeafe',
                'icon' => '👍',
                'message' => 'Buen nivel de adherencia general. Algunas oportunidades de mejora.'
            ];
        } elseif ($rate >= 70) {
            return [
                'level' => 'fair',
                'text' => 'Regular',
                'color' => '#d97706',
                'bgColor' => '#fef3c7',
                'icon' => '⚠️',
                'message' => 'Adherencia moderada. Se recomienda reforzar el seguimiento.'
            ];
        } else {
            return [
                'level' => 'critical',
                'text' => 'Crítico',
                'color' => '#dc2626',
                'bgColor' => '#fee2e2',
                'icon' => '🚨',
                'message' => 'Nivel de adherencia preocupante. Requiere intervención inmediata.'
            ];
        }
    }

    /**
     * Get priority actions based on data
     */
    private function getPriorityActions(): array
    {
        $actions = [];
        
        if (count($this->summaryData['needs_attention']) > 0) {
            $actions[] = [
                'priority' => 'high',
                'title' => 'Pacientes requieren atención',
                'description' => count($this->summaryData['needs_attention']) . ' paciente(s) con adherencia <70% o 2+ dosis omitidas',
                'action' => 'Revisar y contactar pacientes',
                'icon' => '👥'
            ];
        }

        if ($this->summaryData['total_omitted'] > 0) {
            $actions[] = [
                'priority' => 'medium',
                'title' => 'Dosis omitidas registradas',
                'description' => $this->summaryData['total_omitted'] . ' dosis fueron omitidas ayer',
                'action' => 'Analizar patrones y causas',
                'icon' => '⏰'
            ];
        }

        if ($this->summaryData['punctuality_rate'] < 80) {
            $actions[] = [
                'priority' => 'medium',
                'title' => 'Mejorar puntualidad',
                'description' => 'Puntualidad del ' . $this->summaryData['punctuality_rate'] . '% (objetivo: >80%)',
                'action' => 'Revisar horarios y recordatorios',
                'icon' => '⏱️'
            ];
        }

        if (empty($actions)) {
            $actions[] = [
                'priority' => 'low',
                'title' => 'Día exitoso',
                'description' => 'No se requieren acciones inmediatas',
                'action' => 'Mantener el seguimiento rutinario',
                'icon' => '✅'
            ];
        }

        return $actions;
    }

    /**
     * Get highlights for the report
     */
    private function getHighlights(): array
    {
        $highlights = [];

        // Best performing patient
        if (!empty($this->summaryData['top_performers'])) {
            $best = collect($this->summaryData['top_performers'])->sortByDesc('adherence_rate')->first();
            $highlights[] = [
                'type' => 'success',
                'title' => 'Mejor adherencia',
                'value' => $best['name'],
                'detail' => $best['adherence_rate'] . '% de adherencia',
                'icon' => '🏆'
            ];
        }

        // Most active medication
        if (!empty($this->summaryData['medications'])) {
            $mostActive = collect($this->summaryData['medications'])->sortByDesc('total_doses')->first();
            $highlights[] = [
                'type' => 'info',
                'title' => 'Medicamento más usado',
                'value' => $mostActive['name'],
                'detail' => $mostActive['total_doses'] . ' dosis programadas',
                'icon' => '💊'
            ];
        }

        // Alerts generated
        if ($this->summaryData['alerts_generated'] > 0) {
            $highlights[] = [
                'type' => 'warning',
                'title' => 'Alertas generadas',
                'value' => $this->summaryData['alerts_generated'],
                'detail' => 'nueva(s) alerta(s) ayer',
                'icon' => '🚨'
            ];
        } else {
            $highlights[] = [
                'type' => 'success',
                'title' => 'Sin alertas',
                'value' => '0',
                'detail' => 'alertas generadas ayer',
                'icon' => '✅'
            ];
        }

        return $highlights;
    }
} 
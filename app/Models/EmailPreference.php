<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmailPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_summary_frequency',
        'dose_omitted_notifications',
        'adverse_effects_notifications',
        'late_dose_notifications',
        'treatment_change_notifications',
        'appointment_reminders',
        'medication_reminders',
        'adherence_reports',
        'preferred_notification_time',
        'notification_urgency_level',
        'notification_days',
        'last_test_email_sent',
        'test_emails_sent_today',
    ];

    protected $casts = [
        'dose_omitted_notifications' => 'boolean',
        'adverse_effects_notifications' => 'boolean',
        'late_dose_notifications' => 'boolean',
        'treatment_change_notifications' => 'boolean',
        'appointment_reminders' => 'boolean',
        'medication_reminders' => 'boolean',
        'adherence_reports' => 'boolean',
        'notification_days' => 'array',
        'last_test_email_sent' => 'datetime',
        'preferred_notification_time' => 'datetime:H:i',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener o crear preferencias para un usuario
     */
    public static function getOrCreateForUser(User $user): EmailPreference
    {
        return self::firstOrCreate(
            ['user_id' => $user->id],
            [
                'daily_summary_frequency' => 'weekly',
                'dose_omitted_notifications' => true,
                'adverse_effects_notifications' => true,
                'late_dose_notifications' => true,
                'treatment_change_notifications' => true,
                'appointment_reminders' => true,
                'medication_reminders' => $user->hasRole('paciente'),
                'adherence_reports' => true,
                'preferred_notification_time' => '08:00:00',
                'notification_urgency_level' => 'all',
                'notification_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ]
        );
    }

    /**
     * Verificar si puede enviar email de prueba
     */
    public function canSendTestEmail(): bool
    {
        // Máximo 3 emails de prueba por día
        if ($this->test_emails_sent_today >= 3) {
            return false;
        }

        // Al menos 5 minutos entre emails de prueba
        if ($this->last_test_email_sent && 
            $this->last_test_email_sent->diffInMinutes(now()) < 5) {
            return false;
        }

        return true;
    }

    /**
     * Registrar envío de email de prueba
     */
    public function recordTestEmailSent(): void
    {
        $this->update([
            'last_test_email_sent' => now(),
            'test_emails_sent_today' => $this->shouldResetDailyCounter() 
                ? 1 
                : $this->test_emails_sent_today + 1,
        ]);
    }

    /**
     * Verificar si el usuario quiere recibir notificaciones en el día actual
     */
    public function wantsNotificationsToday(): bool
    {
        if (!$this->notification_days) {
            return true; // Si no hay configuración específica, enviar siempre
        }

        $today = strtolower(now()->format('l')); // 'monday', 'tuesday', etc.
        return in_array($today, $this->notification_days);
    }

    /**
     * Verificar si debe recibir un tipo específico de notificación
     */
    public function shouldReceiveNotification(string $type, string $urgency = 'medium'): bool
    {
        // Verificar día de la semana
        if (!$this->wantsNotificationsToday()) {
            return false;
        }

        // Verificar nivel de urgencia
        if ($this->notification_urgency_level === 'critical_only' && $urgency !== 'critical') {
            return false;
        }

        if ($this->notification_urgency_level === 'high_only' && !in_array($urgency, ['high', 'critical'])) {
            return false;
        }

        // Verificar tipo específico de notificación
        $typeField = $type . '_notifications';
        if (property_exists($this, $typeField)) {
            return $this->$typeField;
        }

        return true;
    }

    /**
     * Obtener configuración para mostrar en el frontend
     */
    public function getDisplayConfig(): array
    {
        return [
            'daily_summary' => [
                'frequency' => $this->daily_summary_frequency,
                'time' => $this->preferred_notification_time ? $this->preferred_notification_time->format('H:i') : '08:00',
                'days' => $this->notification_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ],
            'notifications' => [
                'dose_omitted' => $this->dose_omitted_notifications,
                'adverse_effects' => $this->adverse_effects_notifications,
                'late_dose' => $this->late_dose_notifications,
                'treatment_change' => $this->treatment_change_notifications,
                'appointment_reminders' => $this->appointment_reminders,
                'medication_reminders' => $this->medication_reminders,
                'adherence_reports' => $this->adherence_reports,
            ],
            'urgency_level' => $this->notification_urgency_level,
            'test_email' => [
                'can_send' => $this->canSendTestEmail(),
                'sent_today' => $this->test_emails_sent_today,
                'last_sent' => $this->last_test_email_sent?->diffForHumans(),
            ],
        ];
    }

    /**
     * Verificar si debe reiniciar el contador diario
     */
    private function shouldResetDailyCounter(): bool
    {
        if (!$this->last_test_email_sent) {
            return true;
        }

        return !$this->last_test_email_sent->isToday();
    }

    /**
     * Scopes para consultas comunes
     */
    public function scopeWantsDailySummary($query)
    {
        return $query->where('daily_summary_frequency', '!=', 'disabled');
    }

    public function scopeWantsWeeklySummary($query)
    {
        return $query->where('daily_summary_frequency', 'weekly');
    }

    public function scopeWantsMonthlyReports($query)
    {
        return $query->where('daily_summary_frequency', 'monthly');
    }
} 
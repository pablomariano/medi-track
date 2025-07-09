<?php

namespace App\Http\Controllers;

use App\Models\EmailPreference;
use App\Mail\DailyAdherenceSummaryMail;
use App\Console\Commands\SendDailyAdherenceSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class EmailPreferencesController extends Controller
{
    /**
     * Mostrar la página de preferencias de email
     */
    public function index()
    {
        $user = auth()->user();
        $preferences = EmailPreference::getOrCreateForUser($user);
        
        return Inertia::render('Settings/EmailPreferences', [
            'preferences' => $preferences->getDisplayConfig(),
            'user' => [
                'name' => $user->display_name,
                'email' => $user->email,
                'role' => $user->role?->nombre,
                'email_verified' => $user->isEmailVerified(),
            ],
            'frequencyOptions' => $this->getFrequencyOptions(),
            'urgencyLevelOptions' => $this->getUrgencyLevelOptions(),
            'notificationTypeOptions' => $this->getNotificationTypeOptions($user),
            'dayOptions' => $this->getDayOptions(),
        ]);
    }

    /**
     * Actualizar las preferencias de email
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'daily_summary_frequency' => 'required|in:disabled,daily,weekly,monthly',
            'preferred_notification_time' => 'required|date_format:H:i',
            'notification_urgency_level' => 'required|in:all,high_only,critical_only',
            'notification_days' => 'nullable|array',
            'notification_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'dose_omitted_notifications' => 'boolean',
            'adverse_effects_notifications' => 'boolean',
            'late_dose_notifications' => 'boolean',
            'treatment_change_notifications' => 'boolean',
            'appointment_reminders' => 'boolean',
            'medication_reminders' => 'boolean',
            'adherence_reports' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $preferences = EmailPreference::getOrCreateForUser($user);
        
        $updateData = [
            'daily_summary_frequency' => $request->daily_summary_frequency,
            'preferred_notification_time' => $request->preferred_notification_time . ':00',
            'notification_urgency_level' => $request->notification_urgency_level,
            'notification_days' => $request->notification_days ?? [],
            'dose_omitted_notifications' => $request->boolean('dose_omitted_notifications'),
            'adverse_effects_notifications' => $request->boolean('adverse_effects_notifications'),
            'late_dose_notifications' => $request->boolean('late_dose_notifications'),
            'treatment_change_notifications' => $request->boolean('treatment_change_notifications'),
            'appointment_reminders' => $request->boolean('appointment_reminders'),
            'medication_reminders' => $request->boolean('medication_reminders'),
            'adherence_reports' => $request->boolean('adherence_reports'),
        ];

        $preferences->update($updateData);

        return back()->with('success', '✅ Preferencias de email actualizadas correctamente');
    }

    /**
     * Enviar email de prueba
     */
    public function sendTestEmail(Request $request)
    {
        $user = auth()->user();
        $preferences = EmailPreference::getOrCreateForUser($user);
        
        // Verificar si puede enviar email de prueba
        if (!$preferences->canSendTestEmail()) {
            $message = $preferences->test_emails_sent_today >= 3 
                ? '⚠️ Has alcanzado el límite de 3 emails de prueba por día'
                : '⏰ Debes esperar al menos 5 minutos entre emails de prueba';
                
            return back()->with('error', $message);
        }

        try {
            // Generar datos de prueba para el resumen de adherencia
            $testSummaryData = $this->generateTestSummaryData();
            $analysisDate = Carbon::yesterday();
            
            // Crear y enviar el email de prueba
            $mail = new DailyAdherenceSummaryMail($testSummaryData, $analysisDate, $user);
            Mail::to($user->email)->send($mail);
            
            // Registrar el envío
            $preferences->recordTestEmailSent();
            
            return back()->with('success', '📧 Email de prueba enviado correctamente. Revisa tu bandeja de entrada.');
            
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error al enviar el email de prueba: ' . $e->getMessage());
        }
    }

    /**
     * Enviar resumen de adherencia manual
     */
    public function sendAdherenceReport(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date|before_or_equal:today',
            'patient_id' => 'nullable|integer|exists:pacientes,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $date = $request->date ? Carbon::parse($request->date) : Carbon::yesterday();
            $patientId = $request->patient_id;
            
            // Construir el comando
            $command = 'adherence:send-daily-summary';
            $params = [
                '--email' => $user->email,
                '--date' => $date->format('Y-m-d')
            ];
            
            if ($patientId) {
                $params['--patient-id'] = $patientId;
            }
            
            // Ejecutar el comando
            Artisan::call($command, $params);
            $output = Artisan::output();
            
            // Verificar si el comando fue exitoso
            if (str_contains($output, 'Emails enviados: 1')) {
                return back()->with('success', "📊 Resumen de adherencia del {$date->format('d/m/Y')} enviado correctamente");
            } else {
                return back()->with('warning', '⚠️ El resumen fue generado pero puede no haber datos para la fecha seleccionada');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error al generar el resumen: ' . $e->getMessage());
        }
    }

    /**
     * Obtener opciones de frecuencia
     */
    private function getFrequencyOptions(): array
    {
        return [
            ['value' => 'disabled', 'label' => 'Deshabilitado', 'description' => 'No recibir resúmenes automáticos'],
            ['value' => 'daily', 'label' => 'Diario', 'description' => 'Recibir resumen cada día'],
            ['value' => 'weekly', 'label' => 'Semanal', 'description' => 'Recibir resumen cada semana'],
            ['value' => 'monthly', 'label' => 'Mensual', 'description' => 'Recibir resumen cada mes'],
        ];
    }

    /**
     * Obtener opciones de nivel de urgencia
     */
    private function getUrgencyLevelOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'Todas las notificaciones', 'description' => 'Recibir todas las alertas'],
            ['value' => 'high_only', 'label' => 'Solo alta prioridad', 'description' => 'Solo alertas importantes y críticas'],
            ['value' => 'critical_only', 'label' => 'Solo críticas', 'description' => 'Solo emergencias médicas'],
        ];
    }

    /**
     * Obtener tipos de notificaciones según el rol
     */
    private function getNotificationTypeOptions($user): array
    {
        $baseOptions = [
            [
                'key' => 'dose_omitted_notifications',
                'label' => 'Dosis omitidas',
                'description' => 'Cuando un paciente no toma su medicamento',
                'icon' => '💊',
                'urgency' => 'high'
            ],
            [
                'key' => 'adverse_effects_notifications',
                'label' => 'Efectos adversos',
                'description' => 'Reportes de reacciones negativas a medicamentos',
                'icon' => '⚠️',
                'urgency' => 'critical'
            ],
            [
                'key' => 'late_dose_notifications',
                'label' => 'Dosis tardías',
                'description' => 'Medicamentos tomados fuera del horario programado',
                'icon' => '⏰',
                'urgency' => 'medium'
            ],
            [
                'key' => 'treatment_change_notifications',
                'label' => 'Cambios en tratamiento',
                'description' => 'Modificaciones en medicamentos o dosis',
                'icon' => '📋',
                'urgency' => 'high'
            ],
            [
                'key' => 'appointment_reminders',
                'label' => 'Recordatorios de citas',
                'description' => 'Citas médicas próximas',
                'icon' => '📅',
                'urgency' => 'medium'
            ],
            [
                'key' => 'adherence_reports',
                'label' => 'Reportes de adherencia',
                'description' => 'Resúmenes de cumplimiento del tratamiento',
                'icon' => '📊',
                'urgency' => 'low'
            ],
        ];

        // Agregar notificaciones específicas para pacientes
        if ($user->hasRole('paciente')) {
            $baseOptions[] = [
                'key' => 'medication_reminders',
                'label' => 'Recordatorios de medicamentos',
                'description' => 'Recordatorios para tomar medicamentos',
                'icon' => '🔔',
                'urgency' => 'medium'
            ];
        }

        return $baseOptions;
    }

    /**
     * Obtener opciones de días de la semana
     */
    private function getDayOptions(): array
    {
        return [
            ['value' => 'monday', 'label' => 'Lunes'],
            ['value' => 'tuesday', 'label' => 'Martes'],
            ['value' => 'wednesday', 'label' => 'Miércoles'],
            ['value' => 'thursday', 'label' => 'Jueves'],
            ['value' => 'friday', 'label' => 'Viernes'],
            ['value' => 'saturday', 'label' => 'Sábado'],
            ['value' => 'sunday', 'label' => 'Domingo'],
        ];
    }

    /**
     * Generar datos de prueba para el email
     */
    private function generateTestSummaryData(): array
    {
        return [
            'analysis_date' => Carbon::yesterday(),
            'total_scheduled' => 12,
            'total_administered' => 10,
            'total_omitted' => 1,
            'total_pending' => 1,
            'total_late' => 1,
            'adherence_rate' => 83.3,
            'punctuality_rate' => 80.0,
            'alerts_generated' => 2,
            'patients' => [
                [
                    'id' => 1,
                    'name' => 'Juan Pérez (Demo)',
                    'total_doses' => 4,
                    'administered' => 4,
                    'omitted' => 0,
                    'adherence_rate' => 100.0,
                    'status' => ['text' => 'Excelente', 'color' => 'green', 'emoji' => '🟢'],
                    'needs_attention' => false
                ],
                [
                    'id' => 2,
                    'name' => 'María García (Demo)',
                    'total_doses' => 4,
                    'administered' => 3,
                    'omitted' => 1,
                    'adherence_rate' => 75.0,
                    'status' => ['text' => 'Regular', 'color' => 'yellow', 'emoji' => '🟡'],
                    'needs_attention' => true
                ]
            ],
            'medications' => [
                [
                    'name' => 'Ibuprofeno 600mg (Demo)',
                    'total_doses' => 6,
                    'administered' => 5,
                    'adherence_rate' => 83.3
                ],
                [
                    'name' => 'Paracetamol 500mg (Demo)',
                    'total_doses' => 6,
                    'administered' => 5,
                    'adherence_rate' => 83.3
                ]
            ],
            'top_performers' => [
                [
                    'id' => 1,
                    'name' => 'Juan Pérez (Demo)',
                    'total_doses' => 4,
                    'administered' => 4,
                    'adherence_rate' => 100.0,
                    'status' => ['text' => 'Excelente', 'emoji' => '🟢']
                ]
            ],
            'needs_attention' => [
                [
                    'id' => 2,
                    'name' => 'María García (Demo)',
                    'total_doses' => 4,
                    'administered' => 3,
                    'omitted' => 1,
                    'adherence_rate' => 75.0,
                    'status' => ['text' => 'Regular', 'emoji' => '🟡']
                ]
            ]
        ];
    }
} 
import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { 
    Mail, 
    Settings, 
    Bell, 
    Clock, 
    Calendar,
    Send,
    TestTube,
    CheckCircle,
    AlertCircle,
    Info,
    Shield,
    User,
    Zap
} from 'lucide-react';

interface FrequencyOption {
    value: string;
    label: string;
    description: string;
}

interface UrgencyLevelOption {
    value: string;
    label: string;
    description: string;
}

interface NotificationTypeOption {
    key: string;
    label: string;
    description: string;
    icon: string;
    urgency: string;
}

interface DayOption {
    value: string;
    label: string;
}

interface UserInfo {
    name: string;
    email: string;
    role: string;
    email_verified: boolean;
}

interface PreferencesConfig {
    daily_summary: {
        frequency: string;
        time: string;
        days: string[];
    };
    notifications: Record<string, boolean>;
    urgency_level: string;
    test_email: {
        can_send: boolean;
        sent_today: number;
        last_sent: string | null;
    };
}

interface Props {
    preferences: PreferencesConfig;
    user: UserInfo;
    frequencyOptions: FrequencyOption[];
    urgencyLevelOptions: UrgencyLevelOption[];
    notificationTypeOptions: NotificationTypeOption[];
    dayOptions: DayOption[];
}

export default function EmailPreferences({
    preferences,
    user,
    frequencyOptions,
    urgencyLevelOptions,
    notificationTypeOptions,
    dayOptions
}: Props) {
    const [isTestEmailLoading, setIsTestEmailLoading] = useState(false);
    const [isReportLoading, setIsReportLoading] = useState(false);

    const { data, setData, post, processing, errors, wasSuccessful } = useForm({
        daily_summary_frequency: preferences.daily_summary.frequency,
        preferred_notification_time: preferences.daily_summary.time,
        notification_urgency_level: preferences.urgency_level,
        notification_days: preferences.daily_summary.days,
        dose_omitted_notifications: preferences.notifications.dose_omitted || false,
        adverse_effects_notifications: preferences.notifications.adverse_effects || false,
        late_dose_notifications: preferences.notifications.late_dose || false,
        treatment_change_notifications: preferences.notifications.treatment_change || false,
        appointment_reminders: preferences.notifications.appointment_reminders || false,
        medication_reminders: preferences.notifications.medication_reminders || false,
        adherence_reports: preferences.notifications.adherence_reports || false,
    });

    const { data: testData, setData: setTestData, post: postTest } = useForm({
        action: 'test_email'
    });

    const { data: reportData, setData: setReportData, post: postReport } = useForm({
        date: '',
        patient_id: ''
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('email-preferences.update'));
    };

    const handleTestEmail = () => {
        setIsTestEmailLoading(true);
        postTest(route('email-preferences.send-test'), {
            onFinish: () => setIsTestEmailLoading(false)
        });
    };

    const handleSendReport = () => {
        setIsReportLoading(true);
        postReport(route('email-preferences.send-report'), {
            onFinish: () => setIsReportLoading(false)
        });
    };

    const handleDayToggle = (day: string) => {
        const newDays = data.notification_days.includes(day)
            ? data.notification_days.filter(d => d !== day)
            : [...data.notification_days, day];
        setData('notification_days', newDays);
    };

    const handleNotificationToggle = (key: string, checked: boolean) => {
        setData(key as keyof typeof data, checked);
    };

    const getNotificationIcon = (iconStr: string) => {
        return <span className="text-lg">{iconStr}</span>;
    };

    const getUrgencyBadge = (urgency: string) => {
        const urgencyColors = {
            critical: 'bg-red-100 text-red-800',
            high: 'bg-orange-100 text-orange-800',
            medium: 'bg-yellow-100 text-yellow-800',
            low: 'bg-green-100 text-green-800'
        };
        return urgencyColors[urgency as keyof typeof urgencyColors] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AppSidebarLayout>
            <Head title="Preferencias de Email" />
            
            <div className="container mx-auto p-6 space-y-6 max-w-4xl">
                {/* Header */}
                <div className="flex items-center gap-4 mb-8">
                    <div className="p-3 bg-blue-100 rounded-lg">
                        <Mail className="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Preferencias de Email</h1>
                        <p className="text-gray-600">Configura cómo y cuándo recibir notificaciones por correo electrónico</p>
                    </div>
                </div>

                {/* User Status Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <User className="h-5 w-5" />
                            Estado de la Cuenta
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div className="flex items-center gap-3">
                                <Badge variant="outline" className="w-fit">
                                    {user.role}
                                </Badge>
                                <span className="text-sm text-gray-600">Rol</span>
                            </div>
                            <div className="flex items-center gap-3">
                                {user.email_verified ? (
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                ) : (
                                    <AlertCircle className="h-4 w-4 text-red-500" />
                                )}
                                <span className="text-sm text-gray-600">
                                    Email {user.email_verified ? 'verificado' : 'no verificado'}
                                </span>
                            </div>
                            <div className="flex items-center gap-3">
                                <Mail className="h-4 w-4 text-blue-500" />
                                <span className="text-sm text-gray-600">{user.email}</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant={preferences.test_email.can_send ? "default" : "secondary"}>
                                    {preferences.test_email.sent_today}/3 pruebas hoy
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Notification Types */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Bell className="h-5 w-5" />
                                Tipos de Notificaciones
                            </CardTitle>
                            <CardDescription>
                                Selecciona qué tipos de notificaciones quieres recibir
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {notificationTypeOptions.map((option) => (
                                    <div key={option.key} className="flex items-center justify-between p-4 border rounded-lg">
                                        <div className="flex items-center gap-3">
                                            {getNotificationIcon(option.icon)}
                                            <div>
                                                <div className="font-medium flex items-center gap-2">
                                                    {option.label}
                                                    <Badge className={getUrgencyBadge(option.urgency)} variant="secondary">
                                                        {option.urgency}
                                                    </Badge>
                                                </div>
                                                <div className="text-sm text-gray-500">{option.description}</div>
                                            </div>
                                        </div>
                                        <Checkbox
                                            checked={data[option.key as keyof typeof data] as boolean}
                                            onCheckedChange={(checked) => handleNotificationToggle(option.key, checked as boolean)}
                                        />
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Urgency Level */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                Nivel de Urgencia
                            </CardTitle>
                            <CardDescription>
                                Controla qué tipos de alertas quieres recibir según su importancia
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Select 
                                value={data.notification_urgency_level} 
                                onValueChange={(value) => setData('notification_urgency_level', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecciona el nivel de urgencia" />
                                </SelectTrigger>
                                <SelectContent>
                                    {urgencyLevelOptions.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            <div>
                                                <div className="font-medium">{option.label}</div>
                                                <div className="text-sm text-gray-500">{option.description}</div>
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </CardContent>
                    </Card>

                    {/* Schedule Configuration */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                Frecuencia de Resúmenes
                            </CardTitle>
                            <CardDescription>
                                Configura con qué frecuencia quieres recibir resúmenes de adherencia
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="frequency">Frecuencia</Label>
                                <Select 
                                    value={data.daily_summary_frequency} 
                                    onValueChange={(value) => setData('daily_summary_frequency', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona la frecuencia" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {frequencyOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                <div>
                                                    <div className="font-medium">{option.label}</div>
                                                    <div className="text-sm text-gray-500">{option.description}</div>
                                                </div>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {data.daily_summary_frequency !== 'disabled' && (
                                <>
                                    <div className="space-y-2">
                                        <Label htmlFor="time">Hora Preferida</Label>
                                        <Input
                                            id="time"
                                            type="time"
                                            value={data.preferred_notification_time}
                                            onChange={(e) => setData('preferred_notification_time', e.target.value)}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Días de la Semana</Label>
                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            {dayOptions.map((day) => (
                                                <div 
                                                    key={day.value}
                                                    className={`p-2 text-center border rounded cursor-pointer transition-colors ${
                                                        data.notification_days.includes(day.value)
                                                            ? 'bg-blue-100 border-blue-300 text-blue-700'
                                                            : 'bg-gray-50 border-gray-200 hover:bg-gray-100'
                                                    }`}
                                                    onClick={() => handleDayToggle(day.value)}
                                                >
                                                    <div className="text-sm font-medium">{day.label}</div>
                                                </div>
                                            ))}
                                        </div>
                                        <p className="text-sm text-gray-500">
                                            Selecciona los días en que quieres recibir notificaciones
                                        </p>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </form>

                {/* Testing Section */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TestTube className="h-5 w-5" />
                            Email de Prueba
                        </CardTitle>
                        <CardDescription>
                            Envía un email de prueba para verificar que todo funciona correctamente
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <Alert>
                            <Info className="h-4 w-4" />
                            <AlertDescription>
                                El email de prueba incluye datos de demostración para que puedas ver exactamente cómo se verán los resúmenes reales.
                                Límite: 3 emails de prueba por día, con 5 minutos mínimo entre envíos.
                            </AlertDescription>
                        </Alert>

                        <div className="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <div className="font-medium">Resumen de Adherencia (Prueba)</div>
                                <div className="text-sm text-gray-500">
                                    Incluye métricas, pacientes destacados y recomendaciones
                                </div>
                                {preferences.test_email.last_sent && (
                                    <div className="text-xs text-gray-400">
                                        Último envío: {preferences.test_email.last_sent}
                                    </div>
                                )}
                            </div>
                            <Button
                                onClick={handleTestEmail}
                                disabled={!preferences.test_email.can_send || isTestEmailLoading}
                                variant="outline"
                            >
                                {isTestEmailLoading ? (
                                    <div className="flex items-center gap-2">
                                        <div className="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
                                        Enviando...
                                    </div>
                                ) : (
                                    <div className="flex items-center gap-2">
                                        <Send className="h-4 w-4" />
                                        Enviar Prueba
                                    </div>
                                )}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Manual Report */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Zap className="h-5 w-5" />
                            Resumen Manual
                        </CardTitle>
                        <CardDescription>
                            Genera y envía un resumen de adherencia con datos reales para una fecha específica
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="report-date">Fecha (opcional)</Label>
                                <Input
                                    id="report-date"
                                    type="date"
                                    value={reportData.date}
                                    onChange={(e) => setReportData('date', e.target.value)}
                                    max={new Date().toISOString().split('T')[0]}
                                />
                                <p className="text-xs text-gray-500 mt-1">
                                    Deja vacío para analizar ayer
                                </p>
                            </div>
                            <div>
                                <Label htmlFor="patient-id">ID Paciente (opcional)</Label>
                                <Input
                                    id="patient-id"
                                    type="number"
                                    value={reportData.patient_id}
                                    onChange={(e) => setReportData('patient_id', e.target.value)}
                                    placeholder="Ej: 123"
                                />
                                <p className="text-xs text-gray-500 mt-1">
                                    Deja vacío para todos los pacientes
                                </p>
                            </div>
                        </div>

                        <Button
                            onClick={handleSendReport}
                            disabled={isReportLoading}
                            className="w-full"
                        >
                            {isReportLoading ? (
                                <div className="flex items-center gap-2">
                                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                    Generando Resumen...
                                </div>
                            ) : (
                                <div className="flex items-center gap-2">
                                    <Send className="h-4 w-4" />
                                    Generar y Enviar Resumen
                                </div>
                            )}
                        </Button>
                    </CardContent>
                </Card>

                {/* Save Button */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-600">
                                    Los cambios se aplicarán inmediatamente a las próximas notificaciones
                                </p>
                                {wasSuccessful && (
                                    <p className="text-sm text-green-600 mt-1">
                                        ✅ Configuración guardada correctamente
                                    </p>
                                )}
                            </div>
                            <Button 
                                type="submit"
                                onClick={handleSubmit}
                                disabled={processing}
                                className="min-w-[120px]"
                            >
                                {processing ? (
                                    <div className="flex items-center gap-2">
                                        <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                        Guardando...
                                    </div>
                                ) : (
                                    <div className="flex items-center gap-2">
                                        <Settings className="h-4 w-4" />
                                        Guardar Cambios
                                    </div>
                                )}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 
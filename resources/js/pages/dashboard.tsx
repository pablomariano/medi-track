import { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Activity,
    Users, 
    Pill, 
    Stethoscope,
    UserCheck,
    AlertTriangle,
    Clock,
    TrendingUp,
    Calendar,
    CheckCircle,
    XCircle,
    RefreshCw,
    Building2,
    Siren,
    UserCog,
    HeartHandshake,
    PlusCircle,
    Eye,
    Timer,
    Zap
} from 'lucide-react';
import { ChartContainer, ChartConfig, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';
import { Bar, BarChart, Line, LineChart, XAxis, YAxis, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';

interface SystemStats {
    usuarios_total: number;
    pacientes_total: number;
    medicos_total: number;
    cuidadores_total: number;
    apoderados_total: number;
}

interface MedicationStats {
    principios_activos: number;
    principios_activos_activos: number;
    medicamentos_total: number;
    medicamentos_activos: number;
    medicamentos_vencidos: number;
    medicamentos_proximo_vencer: number;
    medicamentos_controlados: number;
}

interface TreatmentStats {
    tratamientos_total: number;
    tratamientos_activos: number;
    administraciones_hoy: number;
    administraciones_vencidas: number;
    alertas_activas: number;
    alertas_criticas: number;
    autorizaciones_pendientes: number;
}

interface Alerta {
    id: number;
    tipo: string;
    mensaje: string;
    nivel_prioridad: 'baja' | 'media' | 'alta' | 'critica';
    created_at: string;
    tratamiento: {
        paciente: {
            nombre: string;
        };
    };
}

interface AdministracionHoy {
    id: number;
    fecha_hora_programada: string;
    estado: string;
    medicamento_tratamiento: {
        medicamento: {
            nombre_comercial: string;
            principio_activo: {
                nombre_generico: string;
            };
        };
        tratamiento: {
            paciente: {
                nombre: string;
            };
        };
    };
    cuidador?: {
        user: {
            name: string;
        };
    };
}

interface MedicamentoVencer {
    id: number;
    nombre_comercial: string;
    fecha_vencimiento: string;
    stock_actual: number;
    principio_activo: {
        nombre_generico: string;
    };
    forma_farmaceutica: {
        nombre: string;
    };
}

interface AutorizacionPendiente {
    id: number;
    fecha_solicitud: string;
    estado: string;
    tratamiento: {
        nombre: string;
        paciente: {
            nombre: string;
        };
    };
    apoderado: {
        user: {
            name: string;
        };
    };
}

interface ActividadReciente {
    id: number;
    descripcion: string;
    fecha: string;
    usuario?: string;
    tipo: string;
}

interface Props {
    systemStats: SystemStats;
    medicationStats: MedicationStats;
    treatmentStats: TreatmentStats;
    alertasRecientes: Alerta[];
    administracionesHoy: AdministracionHoy[];
    medicamentosVencer: MedicamentoVencer[];
    autorizacionesPendientes: AutorizacionPendiente[];
    actividadReciente: ActividadReciente[];
}

export default function Dashboard({ 
    systemStats, 
    medicationStats, 
    treatmentStats, 
    alertasRecientes, 
    administracionesHoy, 
    medicamentosVencer, 
    autorizacionesPendientes, 
    actividadReciente 
}: Props) {
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [lastUpdate, setLastUpdate] = useState(new Date());

    const refreshData = () => {
        setIsRefreshing(true);
        setTimeout(() => {
            setIsRefreshing(false);
            setLastUpdate(new Date());
            // En una implementación real, aquí harías una llamada para refrescar los datos
            window.location.reload();
        }, 1000);
    };

    const formatTime = (dateString: string) => {
        return new Date(dateString).toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const getDaysUntilExpiry = (dateString: string) => {
        const today = new Date();
        const expiryDate = new Date(dateString);
        const diffTime = expiryDate.getTime() - today.getTime();
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    };

    const getPriorityBadge = (priority: string) => {
        const variants = {
            'baja': 'bg-green-100 text-green-800',
            'media': 'bg-yellow-100 text-yellow-800',
            'alta': 'bg-orange-100 text-orange-800',
            'critica': 'bg-red-100 text-red-800'
        };
        return variants[priority as keyof typeof variants] || variants.baja;
    };

    // Datos para el gráfico de medicamentos
    const medicamentosChartData = [
        { name: 'Activos', value: medicationStats.medicamentos_activos, color: '#22c55e' },
        { name: 'Vencidos', value: medicationStats.medicamentos_vencidos, color: '#ef4444' },
        { name: 'Por Vencer', value: medicationStats.medicamentos_proximo_vencer, color: '#f59e0b' },
        { name: 'Inactivos', value: medicationStats.medicamentos_total - medicationStats.medicamentos_activos, color: '#6b7280' }
    ];

    // Datos para el gráfico de tratamientos
    const treatmentChartData = [
        { name: 'Administraciones Hoy', value: treatmentStats.administraciones_hoy },
        { name: 'Vencidas', value: treatmentStats.administraciones_vencidas },
        { name: 'Alertas Activas', value: treatmentStats.alertas_activas },
        { name: 'Tratamientos Activos', value: treatmentStats.tratamientos_activos }
    ];

    const chartConfig = {
        value: {
            label: "Cantidad",
            color: "hsl(var(--chart-1))",
        },
    } satisfies ChartConfig;

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' }
            ]}
        >
            <Head title="Dashboard - MediTrack" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard del Sistema</h1>
                        <p className="text-muted-foreground">
                            Visión general del sistema de gestión médica
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-sm text-muted-foreground">
                            Actualizado: {lastUpdate.toLocaleTimeString('es-ES', { 
                                hour: '2-digit', 
                                minute: '2-digit' 
                            })}
                        </span>
                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={refreshData}
                            disabled={isRefreshing}
                        >
                            <RefreshCw className={`h-4 w-4 mr-2 ${isRefreshing ? 'animate-spin' : ''}`} />
                            Actualizar
                        </Button>
                    </div>
                </div>

                {/* Alertas Críticas */}
                {(treatmentStats.alertas_criticas > 0 || medicationStats.medicamentos_vencidos > 0 || treatmentStats.administraciones_vencidas > 0) && (
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2 text-red-800">
                                <Siren className="h-5 w-5" />
                                Alertas Críticas del Sistema
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {treatmentStats.alertas_criticas > 0 && (
                                    <div className="flex items-center gap-2">
                                        <AlertTriangle className="h-4 w-4 text-red-600" />
                                        <span className="text-sm font-medium text-red-800">
                                            {treatmentStats.alertas_criticas} alertas críticas de tratamientos
                                        </span>
                                    </div>
                                )}
                                {medicationStats.medicamentos_vencidos > 0 && (
                                    <div className="flex items-center gap-2">
                                        <XCircle className="h-4 w-4 text-red-600" />
                                        <span className="text-sm font-medium text-red-800">
                                            {medicationStats.medicamentos_vencidos} medicamentos vencidos
                                        </span>
                                    </div>
                                )}
                                {treatmentStats.administraciones_vencidas > 0 && (
                                    <div className="flex items-center gap-2">
                                        <Timer className="h-4 w-4 text-red-600" />
                                        <span className="text-sm font-medium text-red-800">
                                            {treatmentStats.administraciones_vencidas} administraciones vencidas
                                        </span>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Estadísticas Principales */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Sistema de Usuarios */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Usuarios del Sistema</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{systemStats.usuarios_total}</div>
                            <div className="text-xs text-muted-foreground space-y-1 mt-2">
                                <p>👥 {systemStats.pacientes_total} pacientes</p>
                                <p>👨‍⚕️ {systemStats.medicos_total} médicos</p>
                                <p>👩‍⚕️ {systemStats.cuidadores_total} cuidadores</p>
                                <p>👨‍👩‍👧‍👦 {systemStats.apoderados_total} apoderados</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Medicamentos */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Medicamentos</CardTitle>
                            <Pill className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{medicationStats.medicamentos_total}</div>
                            <div className="text-xs text-muted-foreground space-y-1 mt-2">
                                <p className="text-green-600">✅ {medicationStats.medicamentos_activos} activos</p>
                                <p className="text-red-600">❌ {medicationStats.medicamentos_vencidos} vencidos</p>
                                <p className="text-yellow-600">⚠️ {medicationStats.medicamentos_proximo_vencer} por vencer</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Tratamientos */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tratamientos</CardTitle>
                            <Stethoscope className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{treatmentStats.tratamientos_total}</div>
                            <div className="text-xs text-muted-foreground space-y-1 mt-2">
                                <p className="text-green-600">🟢 {treatmentStats.tratamientos_activos} activos</p>
                                <p className="text-blue-600">📅 {treatmentStats.administraciones_hoy} admin. hoy</p>
                                <p className="text-orange-600">🔔 {treatmentStats.alertas_activas} alertas</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Acciones Pendientes */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Acciones Pendientes</CardTitle>
                            <Clock className="h-4 w-4 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">
                                {treatmentStats.administraciones_hoy + treatmentStats.autorizaciones_pendientes}
                            </div>
                            <div className="text-xs text-muted-foreground space-y-1 mt-2">
                                <p>💊 {treatmentStats.administraciones_hoy} administraciones hoy</p>
                                <p>📋 {treatmentStats.autorizaciones_pendientes} autorizaciones</p>
                                <p className="text-red-600">⏰ {treatmentStats.administraciones_vencidas} vencidas</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Gráficos */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Gráfico de Medicamentos */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Pill className="h-5 w-5" />
                                Estado de Medicamentos
                            </CardTitle>
                            <CardDescription>
                                Distribución del inventario de medicamentos
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={medicamentosChartData}
                                            cx="50%"
                                            cy="50%"
                                            outerRadius={80}
                                            dataKey="value"
                                            label={({ name, value }) => `${name}: ${value}`}
                                        >
                                            {medicamentosChartData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={entry.color} />
                                            ))}
                                        </Pie>
                                        <ChartTooltip />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Gráfico de Actividad Diaria */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Activity className="h-5 w-5" />
                                Actividad del Sistema
                            </CardTitle>
                            <CardDescription>
                                Estadísticas operacionales del día
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ChartContainer config={chartConfig} className="h-[300px] w-full">
                                <BarChart data={treatmentChartData}>
                                    <ChartTooltip content={<ChartTooltipContent />} />
                                    <XAxis
                                        dataKey="name"
                                        tickLine={false}
                                        tickMargin={10}
                                        axisLine={false}
                                        tickFormatter={(value) => value.split(' ')[0]}
                                    />
                                    <Bar dataKey="value" fill="hsl(var(--chart-1))" radius={4} />
                                </BarChart>
                            </ChartContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Sección de Alertas y Acciones */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Administraciones del Día */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <Calendar className="h-5 w-5" />
                                        Administraciones de Hoy
                                    </CardTitle>
                                    <CardDescription>
                                        Medicamentos programados para hoy
                                    </CardDescription>
                                </div>
                                <Badge variant="outline">
                                    {administracionesHoy.length} pendientes
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3 max-h-[300px] overflow-y-auto">
                                {administracionesHoy.length === 0 ? (
                                    <div className="text-center py-8">
                                        <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-3" />
                                        <p className="text-muted-foreground">
                                            No hay administraciones pendientes para hoy
                                        </p>
                                    </div>
                                ) : (
                                    administracionesHoy.map((admin) => (
                                        <div key={admin.id} className="flex items-center justify-between p-3 border rounded-lg">
                                            <div className="flex-1">
                                                <p className="font-medium">
                                                    {admin.medicamento_tratamiento.medicamento.nombre_comercial}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {admin.medicamento_tratamiento.tratamiento.paciente.nombre}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {admin.medicamento_tratamiento.medicamento.principio_activo.nombre_generico}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-medium">{formatTime(admin.fecha_hora_programada)}</p>
                                                <Badge variant={admin.estado === 'programada' ? 'outline' : 'default'}>
                                                    {admin.estado}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                            {administracionesHoy.length > 0 && (
                                <div className="mt-4 pt-4 border-t">
                                    <Button className="w-full" size="sm" asChild>
                                        <Link href="/administraciones">
                                            <Eye className="h-4 w-4 mr-2" />
                                            Ver Todas las Administraciones
                                        </Link>
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Alertas Recientes */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <AlertTriangle className="h-5 w-5" />
                                        Alertas del Sistema
                                    </CardTitle>
                                    <CardDescription>
                                        Notificaciones importantes
                                    </CardDescription>
                                </div>
                                <Badge variant="destructive">
                                    {alertasRecientes.length} activas
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3 max-h-[300px] overflow-y-auto">
                                {alertasRecientes.length === 0 ? (
                                    <div className="text-center py-8">
                                        <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-3" />
                                        <p className="text-muted-foreground">
                                            No hay alertas activas
                                        </p>
                                    </div>
                                ) : (
                                    alertasRecientes.map((alerta) => (
                                        <div key={alerta.id} className="p-3 border rounded-lg">
                                            <div className="flex items-start justify-between mb-2">
                                                <p className="font-medium text-sm">{alerta.mensaje}</p>
                                                <Badge 
                                                    variant="outline" 
                                                    className={getPriorityBadge(alerta.nivel_prioridad)}
                                                >
                                                    {alerta.nivel_prioridad}
                                                </Badge>
                                            </div>
                                            <p className="text-xs text-muted-foreground">
                                                Paciente: {alerta.tratamiento.paciente.nombre}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {formatDate(alerta.created_at)} {formatTime(alerta.created_at)}
                                            </p>
                                        </div>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Sección Inferior */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Medicamentos Próximos a Vencer */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Zap className="h-5 w-5 text-yellow-600" />
                                Medicamentos por Vencer
                            </CardTitle>
                            <CardDescription>
                                Próximos 30 días
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3 max-h-[250px] overflow-y-auto">
                                {medicamentosVencer.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-4">
                                        No hay medicamentos próximos a vencer
                                    </p>
                                ) : (
                                    medicamentosVencer.map((medicamento) => {
                                        const daysLeft = getDaysUntilExpiry(medicamento.fecha_vencimiento);
                                        return (
                                            <div key={medicamento.id} className="flex items-center justify-between p-2 border rounded">
                                                <div>
                                                    <p className="font-medium text-sm">{medicamento.nombre_comercial}</p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {medicamento.principio_activo.nombre_generico} - {medicamento.forma_farmaceutica.nombre}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        Stock: {medicamento.stock_actual}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <Badge variant={daysLeft <= 7 ? 'destructive' : daysLeft <= 30 ? 'secondary' : 'default'}>
                                                        {daysLeft} días
                                                    </Badge>
                                                    <p className="text-xs text-muted-foreground mt-1">
                                                        {formatDate(medicamento.fecha_vencimiento)}
                                                    </p>
                                                </div>
                                            </div>
                                        );
                                    })
                                )}
                            </div>
                            {medicamentosVencer.length > 0 && (
                                <div className="mt-4 pt-4 border-t">
                                    <Button variant="outline" className="w-full" size="sm" asChild>
                                        <Link href="/medicamentos/inventario">
                                            <Eye className="h-4 w-4 mr-2" />
                                            Ver Alertas de Inventario
                                        </Link>
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Accesos Rápidos */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Building2 className="h-5 w-5" />
                                Accesos Rápidos
                            </CardTitle>
                            <CardDescription>
                                Navegación directa a funciones principales
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-3">
                                <Button variant="outline" className="h-auto p-4 flex flex-col gap-2" asChild>
                                    <Link href="/medicamentos/create">
                                        <PlusCircle className="h-6 w-6" />
                                        <span className="text-sm">Nuevo Medicamento</span>
                                    </Link>
                                </Button>
                                <Button variant="outline" className="h-auto p-4 flex flex-col gap-2" asChild>
                                    <Link href="/tratamientos/create">
                                        <HeartHandshake className="h-6 w-6" />
                                        <span className="text-sm">Nuevo Tratamiento</span>
                                    </Link>
                                </Button>
                                <Button variant="outline" className="h-auto p-4 flex flex-col gap-2" asChild>
                                    <Link href="/medicamentos/inventario">
                                        <AlertTriangle className="h-6 w-6" />
                                        <span className="text-sm">Alertas Inventario</span>
                                    </Link>
                                </Button>
                                <Button variant="outline" className="h-auto p-4 flex flex-col gap-2" asChild>
                                    <Link href="/tratamientos">
                                        <Activity className="h-6 w-6" />
                                        <span className="text-sm">Ver Tratamientos</span>
                                    </Link>
                                </Button>
                            </div>

                            {/* Información de Actividad Reciente */}
                            {actividadReciente.length > 0 && (
                                <div className="mt-6 pt-4 border-t">
                                    <h4 className="font-medium text-sm mb-3">Actividad Reciente</h4>
                                    <div className="space-y-2 max-h-[120px] overflow-y-auto">
                                        {actividadReciente.slice(0, 3).map((actividad) => (
                                            <div key={actividad.id} className="text-xs text-muted-foreground">
                                                <p>{actividad.descripcion}</p>
                                                <p className="text-xs">{formatDate(actividad.fecha)} - {actividad.usuario}</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppSidebarLayout>
    );
}

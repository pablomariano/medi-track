import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Heart, 
    Edit, 
    Calendar, 
    User, 
    Stethoscope, 
    Pill, 
    Clock, 
    AlertCircle,
    CheckCircle,
    XCircle,
    Pause,
    Eye
} from 'lucide-react';

interface Paciente {
    id: number;
    nombre: string;
    numero_documento: string;
}

interface Medico {
    usuario_id: number;
    nombre: string;
    numero_licencia: string;
}

interface Medicamento {
    id: number;
    nombre: string;
    concentracion: string;
    unidad_concentracion: string;
    forma_farmaceutica: string;
    pivot: {
        id: number;
        dosis_cantidad: number;
        unidad_dosis: string;
        frecuencia_horas: number;
        tolerancia_antes_minutos?: number;
        tolerancia_despues_minutos?: number;
        instrucciones_especiales?: string;
        estado: string;
        orden: number;
    };
}

interface HorarioProgramado {
    id: number;
    hora_programada: string;
    dias_semana: string;
    fecha_inicio: string;
    fecha_fin?: string;
    activo: boolean;
}

interface Administracion {
    id: number;
    fecha_hora_programada: string;
    fecha_hora_administrada?: string;
    estado: string;
    dosis_administrada?: number;
    observaciones?: string;
}

interface TratamientoData {
    id: number;
    paciente_id: number;
    medico_usuario_id: number;
    nombre: string;
    objetivo?: string;
    diagnostico?: string;
    tipo: 'Programado';
    estado: string;
    fecha_inicio: string;
    fecha_fin?: string;
    observaciones?: string;
    paciente: Paciente;
    medico: Medico;
    medicamentos: Medicamento[];
    horarios_programados?: HorarioProgramado[];
    administraciones_recientes?: Administracion[];
    created_at: string;
    updated_at: string;
}

interface Props {
    tratamiento: TratamientoData;
}

export default function ShowMiTratamiento({ tratamiento }: Props) {
    const getEstadoBadge = (estado: string) => {
        const config = {
            'Activo': { variant: 'default' as const, icon: CheckCircle, color: 'text-green-600' },
            'Pausado': { variant: 'secondary' as const, icon: Pause, color: 'text-yellow-600' },
            'Completado': { variant: 'outline' as const, icon: CheckCircle, color: 'text-blue-600' },
            'Suspendido': { variant: 'destructive' as const, icon: XCircle, color: 'text-red-600' }
        };
        return config[estado as keyof typeof config] || config['Activo'];
    };

    const getTipoBadge = (tipo: string) => {
        return tipo === 'Programado' 
            ? { variant: 'default' as const, color: 'bg-blue-100 text-blue-800' }
            : { variant: 'outline' as const, color: 'bg-orange-100 text-orange-800' };
    };

    const formatDate = (dateString: string) => {
        if (!dateString) return 'No definida';
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const formatDateTime = (dateString: string) => {
        if (!dateString) return 'No definida';
        return new Date(dateString).toLocaleString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatFrequency = (horas: number) => {
        if (horas <= 0) return 'Frecuencia no definida';
        
        const vecesPorDia = Math.round(24 / horas);
        if (vecesPorDia >= 1) {
            return `${vecesPorDia} ${vecesPorDia === 1 ? 'vez' : 'veces'} al día`;
        }
        
        const diasEntreDosis = Math.round(horas / 24);
        return `Cada ${diasEntreDosis} ${diasEntreDosis === 1 ? 'día' : 'días'}`;
    };

    const estadoConfig = getEstadoBadge(tratamiento.estado);
    const tipoConfig = getTipoBadge(tratamiento.tipo);
    const EstadoIcon = estadoConfig.icon;

    return (
        <AppSidebarLayout>
            <Head title={`Mi Tratamiento - ${tratamiento.nombre}`} />
            
            <div className="container mx-auto p-6 space-y-6 max-w-none">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Link href={route('mis-tratamientos.index')}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver a Mis Tratamientos
                            </Button>
                        </Link>
                        <Heart className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold">Detalles del Tratamiento</h1>
                    </div>
                    <Link href={route('mis-tratamientos.edit', tratamiento.id)}>
                        <Button>
                            <Edit className="h-4 w-4 mr-2" />
                            Editar
                        </Button>
                    </Link>
                </div>

                {/* Información Principal */}
                <Card>
                    <CardHeader>
                        <div className="flex items-start justify-between">
                            <div>
                                <CardTitle className="text-xl">{tratamiento.nombre}</CardTitle>
                                <CardDescription className="mt-2">
                                    {tratamiento.objetivo || 'Tratamiento de medicamentos'}
                                </CardDescription>
                            </div>
                            <div className="flex flex-col items-end space-y-2">
                                <Badge variant={estadoConfig.variant} className="flex items-center space-x-1">
                                    <EstadoIcon className="h-3 w-3" />
                                    <span>{tratamiento.estado}</span>
                                </Badge>
                                <Badge variant={tipoConfig.variant} className={tipoConfig.color}>
                                    {tratamiento.tipo}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p className="text-sm font-medium">Fecha de inicio</p>
                                    <p className="text-sm text-muted-foreground">
                                        {formatDate(tratamiento.fecha_inicio)}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p className="text-sm font-medium">Fecha de fin</p>
                                    <p className="text-sm text-muted-foreground">
                                        {tratamiento.fecha_fin ? formatDate(tratamiento.fecha_fin) : 'Sin fecha límite'}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {tratamiento.observaciones && (
                            <div className="p-4 bg-muted rounded-lg">
                                <p className="text-sm font-medium mb-1">Observaciones</p>
                                <p className="text-sm text-muted-foreground">{tratamiento.observaciones}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Medicamentos */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Pill className="h-5 w-5 text-primary" />
                            <span>Medicamentos ({tratamiento.medicamentos?.length || 0})</span>
                        </CardTitle>
                        <CardDescription>
                            Medicamentos incluidos en este tratamiento
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {tratamiento.medicamentos && tratamiento.medicamentos.length > 0 ? (
                            <div className="space-y-4">
                                {tratamiento.medicamentos.map((medicamento) => (
                                    <div key={medicamento.id} className="p-4 border rounded-lg">
                                        <div className="flex items-start justify-between">
                                            <div className="space-y-2">
                                                <h4 className="font-semibold">{medicamento.nombre}</h4>
                                                <div className="text-sm text-muted-foreground space-y-1">
                                                    <p>
                                                        <span className="font-medium">Dosis:</span> {medicamento.pivot.dosis_cantidad} {medicamento.pivot.unidad_dosis}
                                                    </p>
                                                    <p>
                                                        <span className="font-medium">Frecuencia:</span> {formatFrequency(medicamento.pivot.frecuencia_horas)}
                                                    </p>
                                                    {medicamento.forma_farmaceutica && (
                                                        <p>
                                                            <span className="font-medium">Forma:</span> {medicamento.forma_farmaceutica}
                                                        </p>
                                                    )}
                                                    {medicamento.pivot.instrucciones_especiales && (
                                                        <p>
                                                            <span className="font-medium">Instrucciones:</span> {medicamento.pivot.instrucciones_especiales}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <Badge variant={medicamento.pivot.estado === 'Activo' ? 'default' : 'secondary'}>
                                                {medicamento.pivot.estado}
                                            </Badge>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8 text-muted-foreground">
                                <Pill className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p>No hay medicamentos asociados a este tratamiento</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Horarios Programados */}
                {tratamiento.horarios_programados && tratamiento.horarios_programados.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <Clock className="h-5 w-5 text-primary" />
                                <span>Horarios Programados</span>
                            </CardTitle>
                            <CardDescription>
                                Horarios configurados para este tratamiento
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {tratamiento.horarios_programados.map((horario) => (
                                    <div key={horario.id} className="p-3 border rounded-lg">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="font-medium">{horario.hora_programada}</span>
                                            <Badge variant={horario.activo ? 'default' : 'secondary'} className="text-xs">
                                                {horario.activo ? 'Activo' : 'Inactivo'}
                                            </Badge>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {horario.dias_semana || 'Todos los días'}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Desde: {formatDate(horario.fecha_inicio)}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Administraciones Recientes */}
                {tratamiento.administraciones_recientes && tratamiento.administraciones_recientes.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <CheckCircle className="h-5 w-5 text-primary" />
                                <span>Administraciones Recientes</span>
                            </CardTitle>
                            <CardDescription>
                                Últimas administraciones de medicamentos
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {tratamiento.administraciones_recientes.slice(0, 10).map((administracion) => (
                                    <div key={administracion.id} className="flex items-center justify-between p-3 border rounded-lg">
                                        <div className="space-y-1">
                                            <p className="text-sm font-medium">
                                                {formatDateTime(administracion.fecha_hora_programada)}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {administracion.observaciones || 'Sin observaciones'}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <Badge variant={
                                                administracion.estado === 'Administrado' ? 'default' :
                                                administracion.estado === 'Omitido' ? 'destructive' : 'secondary'
                                            }>
                                                {administracion.estado}
                                            </Badge>
                                            {administracion.fecha_hora_administrada && (
                                                <p className="text-xs text-muted-foreground mt-1">
                                                    {formatDateTime(administracion.fecha_hora_administrada)}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Enlaces útiles */}
                <Card>
                    <CardHeader>
                        <CardTitle>Enlaces útiles</CardTitle>
                        <CardDescription>
                            Acciones relacionadas con tu tratamiento
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid md:grid-cols-2 gap-4">
                            <Link href="/mi-cronograma" className="block">
                                <div className="p-4 border rounded-lg hover:bg-primary/5 hover:border-primary/50 transition-all duration-200">
                                    <div className="flex items-center gap-3">
                                        <Calendar className="h-5 w-5 text-primary" />
                                        <div>
                                            <h4 className="font-medium">Mi Cronograma</h4>
                                            <p className="text-sm text-muted-foreground">Ver horarios de hoy</p>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                            <Link href={route('mis-tratamientos.index')} className="block">
                                <div className="p-4 border rounded-lg hover:bg-primary/5 hover:border-primary/50 transition-all duration-200">
                                    <div className="flex items-center gap-3">
                                        <Pill className="h-5 w-5 text-primary" />
                                        <div>
                                            <h4 className="font-medium">Mis Tratamientos</h4>
                                            <p className="text-sm text-muted-foreground">Ver todos mis tratamientos</p>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 
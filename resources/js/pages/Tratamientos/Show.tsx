import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
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
    Pause
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

export default function ShowTratamiento({ tratamiento }: Props) {
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

    const estadoConfig = getEstadoBadge(tratamiento.estado);
    const tipoConfig = getTipoBadge(tratamiento.tipo);
    const EstadoIcon = estadoConfig.icon;

    return (
        <AppLayout>
            <Head title={`Tratamiento - ${tratamiento.nombre}`} />
            
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Link href={route('tratamientos.index')}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver a Tratamientos
                            </Button>
                        </Link>
                        <Heart className="h-6 w-6 text-green-600" />
                        <h1 className="text-2xl font-bold text-gray-900">Detalles del Tratamiento</h1>
                    </div>
                    <Link href={route('tratamientos.edit', tratamiento.id)}>
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
                                    {tratamiento.objetivo || 'Sin objetivo definido'}
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
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="flex items-center space-x-2">
                                <User className="h-4 w-4 text-gray-500" />
                                <div>
                                    <p className="text-sm font-medium">Paciente</p>
                                    <p className="text-sm text-gray-600">
                                        {tratamiento.paciente.nombre}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        Doc: {tratamiento.paciente.numero_documento}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Stethoscope className="h-4 w-4 text-gray-500" />
                                <div>
                                    <p className="text-sm font-medium">Médico Responsable</p>
                                    <p className="text-sm text-gray-600">
                                        {tratamiento.medico.nombre}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        Licencia: {tratamiento.medico.numero_licencia}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Calendar className="h-4 w-4 text-gray-500" />
                                <div>
                                    <p className="text-sm font-medium">Fechas</p>
                                    <p className="text-sm text-gray-600">
                                        Inicio: {formatDate(tratamiento.fecha_inicio)}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        Fin: {formatDate(tratamiento.fecha_fin || '')}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {tratamiento.diagnostico && (
                            <div>
                                <p className="text-sm font-medium mb-1">Diagnóstico</p>
                                <p className="text-sm text-gray-700 bg-blue-50 border border-blue-100 p-3 rounded">
                                    {tratamiento.diagnostico}
                                </p>
                            </div>
                        )}

                        {tratamiento.observaciones && (
                            <div>
                                <p className="text-sm font-medium mb-1">Observaciones</p>
                                <p className="text-sm text-gray-700 bg-amber-50 border border-amber-100 p-3 rounded">
                                    {tratamiento.observaciones}
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Medicamentos */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Pill className="h-5 w-5" />
                            <span>Medicamentos</span>
                        </CardTitle>
                        <CardDescription>
                            Medicamentos incluidos en este tratamiento
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {tratamiento.medicamentos.length > 0 ? (
                            <div className="space-y-4">
                                {tratamiento.medicamentos.map((medicamento, index) => (
                                    <div key={medicamento.id} className="border rounded-lg p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <h4 className="font-medium">{medicamento.nombre}</h4>
                                                {medicamento.concentracion && (
                                                    <p className="text-xs text-gray-500">
                                                        {medicamento.concentracion} {medicamento.unidad_concentracion}
                                                    </p>
                                                )}
                                            </div>
                                            <Badge variant="outline">
                                                Orden {medicamento.pivot?.orden || index + 1}
                                            </Badge>
                                        </div>

                                        {medicamento.pivot && (
                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-3 text-sm">
                                                <div>
                                                    <span className="font-medium">Dosis:</span>
                                                    <span className="ml-1">
                                                        {medicamento.pivot.dosis_cantidad} {medicamento.pivot.unidad_dosis}
                                                    </span>
                                                </div>

                                                <div className="space-y-2 text-sm">
                                                    <div>
                                                        <span className="font-medium">Frecuencia:</span>
                                                        <span className="ml-1">
                                                            Cada {medicamento.pivot.frecuencia_horas} horas
                                                        </span>
                                                    </div>
                                                    {medicamento.pivot.tolerancia_antes_minutos && (
                                                        <div>
                                                            <span className="font-medium">Ventana antes:</span>
                                                            <span className="ml-1">
                                                                {medicamento.pivot.tolerancia_antes_minutos} minutos
                                                            </span>
                                                        </div>
                                                    )}
                                                    {medicamento.pivot.tolerancia_despues_minutos && (
                                                        <div>
                                                            <span className="font-medium">Ventana después:</span>
                                                            <span className="ml-1">
                                                                {medicamento.pivot.tolerancia_despues_minutos} minutos
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>

                                                {medicamento.pivot.estado && (
                                                    <div>
                                                        <span className="font-medium">Estado:</span>
                                                        <Badge variant="outline" className="ml-1">
                                                            {medicamento.pivot.estado}
                                                        </Badge>
                                                    </div>
                                                )}

                                                {medicamento.pivot.instrucciones_especiales && (
                                                    <div className="col-span-full">
                                                        <span className="font-medium">Instrucciones:</span>
                                                        <p className="text-gray-700 mt-1 bg-green-50 border border-green-100 p-2 rounded">
                                                            {medicamento.pivot.instrucciones_especiales}
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8 text-gray-500">
                                <Pill className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                                <p>No hay medicamentos asignados a este tratamiento</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Horarios Programados */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Clock className="h-5 w-5" />
                            <span>Horarios Programados</span>
                        </CardTitle>
                        <CardDescription>
                            Horarios automáticos generados para este tratamiento
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {tratamiento.horarios_programados && tratamiento.horarios_programados.length > 0 ? (
                            <div className="space-y-3">
                                {tratamiento.horarios_programados.map((horario) => (
                                    <div key={horario.id} className="border rounded-lg p-3">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="font-medium">
                                                    {horario.hora_programada}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    {horario.dias_semana}
                                                </p>
                                            </div>
                                            <Badge variant={horario.activo ? "default" : "secondary"}>
                                                {horario.activo ? "Activo" : "Inactivo"}
                                            </Badge>
                                        </div>
                                        <div className="mt-2 text-sm text-gray-500">
                                            <p>Desde: {horario.fecha_inicio}</p>
                                            {horario.fecha_fin && <p>Hasta: {horario.fecha_fin}</p>}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8 text-gray-500">
                                <Clock className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                                <p>No hay horarios programados</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Administraciones Recientes */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <CheckCircle className="h-5 w-5" />
                            <span>Administraciones Recientes</span>
                        </CardTitle>
                        <CardDescription>
                            Últimas 20 administraciones registradas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {tratamiento.administraciones_recientes && tratamiento.administraciones_recientes.length > 0 ? (
                            <div className="space-y-3">
                                {tratamiento.administraciones_recientes.map((admin) => (
                                    <div key={admin.id} className="border rounded-lg p-3">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="font-medium">
                                                {formatDateTime(admin.fecha_hora_administrada || '')}
                                            </span>
                                            <Badge variant={
                                                admin.estado === 'Administrada' ? 'default' : 
                                                admin.estado === 'Omitida' ? 'destructive' : 'secondary'
                                            }>
                                                {admin.estado}
                                            </Badge>
                                        </div>
                                        <div className="text-sm text-gray-600">
                                            <p>Dosis: {admin.dosis_administrada}</p>
                                            {admin.fecha_hora_programada && (
                                                <p>Programada: {formatDateTime(admin.fecha_hora_programada)}</p>
                                            )}
                                            {admin.observaciones && (
                                                <p className="mt-1 bg-indigo-50 border border-indigo-100 p-2 rounded">
                                                    <span className="font-medium text-indigo-700">Obs:</span> {admin.observaciones}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8 text-gray-500">
                                <CheckCircle className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                                <p>No hay administraciones registradas</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Información del Sistema */}
                <Card>
                    <CardHeader>
                        <CardTitle>Información del Sistema</CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm text-gray-600">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><span className="font-medium">Creado:</span> {formatDateTime(tratamiento.created_at)}</p>
                            </div>
                            <div>
                                <p><span className="font-medium">Actualizado:</span> {formatDateTime(tratamiento.updated_at)}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 
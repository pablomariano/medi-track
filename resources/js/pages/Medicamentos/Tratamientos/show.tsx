import { Head, Link, router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
    ArrowLeft, 
    Edit, 
    User,
    UserCheck,
    Pill,
    Calendar,
    FileText,
    Building,
    Activity,
    Clock,
    TrendingUp,
    CheckCircle,
    Play,
    Pause,
    AlertTriangle,
    BarChart3,
    History,
    Stethoscope
} from 'lucide-react';

interface Paciente {
    id: number;
    nombre: string;
}

interface PersonalMedico {
    user: {
        id: number;
        name: string;
    };
}

interface PrincipioActivo {
    id: number;
    nombre_generico: string;
}

interface FormaFarmaceutica {
    id: number;
    nombre: string;
}

interface ViaAdministracion {
    id: number;
    nombre: string;
}

interface UnidadDosis {
    id: number;
    nombre: string;
    simbolo: string;
}

interface Medicamento {
    id: number;
    nombre_comercial: string;
    principio_activo: PrincipioActivo;
    forma_farmaceutica: FormaFarmaceutica;
    via_administracion: ViaAdministracion;
}

interface MedicamentoTratamiento {
    id: number;
    medicamento: Medicamento;
    tipo_esquema: string;
    dosis_cantidad: number;
    unidad_dosis: UnidadDosis;
    frecuencia_horas?: number;
    duracion_dias?: number;
    fecha_inicio: string;
    fecha_fin?: string;
    indicaciones_uso?: string;
    activo: boolean;
    orden_prescripcion: number;
}

interface HistorialItem {
    id: number;
    accion: string;
    campo_modificado?: string;
    valor_anterior?: string;
    valor_nuevo?: string;
    motivo?: string;
    creado_en: string;
    usuario?: {
        id: number;
        name: string;
    };
}

interface Tratamiento {
    id: number;
    nombre: string;
    diagnostico?: string;
    objetivo_terapeutico?: string;
    estado: string;
    fecha_inicio: string;
    fecha_fin_estimada?: string;
    fecha_fin?: string;
    medico_prescriptor?: string;
    institucion?: string;
    observaciones?: string;
    paciente: Paciente;
    medico?: PersonalMedico;
    medicamento_tratamientos: MedicamentoTratamiento[];
    historial: HistorialItem[];
    created_at: string;
    updated_at: string;
}

interface Stats {
    duracion_dias?: number;
    porcentaje_completado?: number;
    medicamentos_activos: number;
    administraciones_programadas: number;
    administraciones_completadas: number;
}

interface Props {
    tratamiento: Tratamiento;
    stats: Stats;
}

export default function Show({ tratamiento, stats }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getDurationDays = () => {
        if (!tratamiento.fecha_fin_estimada) return null;
        const start = new Date(tratamiento.fecha_inicio);
        const end = new Date(tratamiento.fecha_fin_estimada);
        const diffTime = end.getTime() - start.getTime();
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    };

    const getElapsedDays = () => {
        const start = new Date(tratamiento.fecha_inicio);
        const today = new Date();
        const diffTime = today.getTime() - start.getTime();
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    };

    const getEstadoBadge = (estado: string) => {
        const variants = {
            'Activo': { variant: 'default' as const, className: 'bg-green-100 text-green-800' },
            'Pausado': { variant: 'secondary' as const, className: 'bg-yellow-100 text-yellow-800' },
            'Completado': { variant: 'outline' as const, className: 'bg-blue-100 text-blue-800' },
            'Suspendido': { variant: 'destructive' as const, className: '' },
            'Modificado': { variant: 'secondary' as const, className: 'bg-purple-100 text-purple-800' },
        };

        const config = variants[estado as keyof typeof variants] || variants['Activo'];
        
        return (
            <Badge variant={config.variant} className={config.className}>
                {estado}
            </Badge>
        );
    };

    const getEsquemaBadge = (esquema: string) => {
        const variants = {
            'Fijo': 'bg-blue-100 text-blue-800',
            'Variable': 'bg-purple-100 text-purple-800',
            'PRN': 'bg-orange-100 text-orange-800',
            'Escalonamiento': 'bg-green-100 text-green-800',
            'Reduccion': 'bg-yellow-100 text-yellow-800',
            'Alterno': 'bg-gray-100 text-gray-800',
        };

        return (
            <Badge variant="outline" className={variants[esquema as keyof typeof variants] || 'bg-gray-100 text-gray-800'}>
                {esquema}
            </Badge>
        );
    };

    const handleToggleStatus = () => {
        router.post(route('tratamientos.toggle-status', tratamiento.id), {}, {
            preserveScroll: true,
        });
    };

    const handleCompletar = () => {
        if (confirm('¿Está seguro de que desea completar este tratamiento?')) {
            router.post(route('tratamientos.completar', tratamiento.id), {}, {
                preserveScroll: true,
            });
        }
    };

    const durationDays = getDurationDays();
    const elapsedDays = getElapsedDays();

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Tratamientos', href: '/tratamientos' },
                { title: tratamiento.nombre, href: `/tratamientos/${tratamiento.id}` }
            ]}
        >
            <Head title={`${tratamiento.nombre} - Tratamiento`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/tratamientos">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="text-3xl font-bold tracking-tight">{tratamiento.nombre}</h1>
                                {getEstadoBadge(tratamiento.estado)}
                            </div>
                            <p className="text-muted-foreground">
                                Paciente: {tratamiento.paciente.nombre} | Desde: {formatDate(tratamiento.fecha_inicio)}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={handleToggleStatus}>
                            {tratamiento.estado === 'Activo' ? (
                                <>
                                    <Pause className="h-4 w-4 mr-2" />
                                    Pausar
                                </>
                            ) : (
                                <>
                                    <Play className="h-4 w-4 mr-2" />
                                    Reactivar
                                </>
                            )}
                        </Button>
                        {(tratamiento.estado === 'Activo' || tratamiento.estado === 'Pausado') && (
                            <Button variant="outline" onClick={handleCompletar}>
                                <CheckCircle className="h-4 w-4 mr-2" />
                                Completar
                            </Button>
                        )}
                        <Button asChild>
                            <Link href={`/tratamientos/${tratamiento.id}/edit`}>
                                <Edit className="h-4 w-4 mr-2" />
                                Editar
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Duración</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {durationDays || 'N/A'}
                                {durationDays && <span className="text-sm font-normal ml-1">días</span>}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {elapsedDays} días transcurridos
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Medicamentos</CardTitle>
                            <Pill className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.medicamentos_activos}</div>
                            <p className="text-xs text-muted-foreground">Activos</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Programadas</CardTitle>
                            <Clock className="h-4 w-4 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">{stats.administraciones_programadas}</div>
                            <p className="text-xs text-muted-foreground">Administraciones</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completadas</CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.administraciones_completadas}</div>
                            <p className="text-xs text-muted-foreground">Administraciones</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Progreso</CardTitle>
                            <TrendingUp className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">
                                {stats.porcentaje_completado || 0}%
                            </div>
                            <p className="text-xs text-muted-foreground">Completado</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Información Principal */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Información Básica */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Información del Tratamiento
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-3">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Nombre</p>
                                            <p className="text-lg font-semibold">{tratamiento.nombre}</p>
                                        </div>
                                        
                                        {tratamiento.diagnostico && (
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Diagnóstico</p>
                                                <p className="font-medium">{tratamiento.diagnostico}</p>
                                            </div>
                                        )}

                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Estado</p>
                                            <div className="mt-1">
                                                {getEstadoBadge(tratamiento.estado)}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Fecha de Inicio</p>
                                            <p className="font-medium">{formatDate(tratamiento.fecha_inicio)}</p>
                                        </div>

                                        {tratamiento.fecha_fin_estimada && (
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Fecha Fin Estimada</p>
                                                <p className="font-medium">{formatDate(tratamiento.fecha_fin_estimada)}</p>
                                            </div>
                                        )}

                                        {tratamiento.fecha_fin && (
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Fecha de Finalización</p>
                                                <p className="font-medium">{formatDate(tratamiento.fecha_fin)}</p>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {tratamiento.objetivo_terapeutico && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground mb-2">Objetivo Terapéutico</p>
                                            <p className="text-sm leading-6">{tratamiento.objetivo_terapeutico}</p>
                                        </div>
                                    </>
                                )}

                                {tratamiento.observaciones && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground mb-2">Observaciones</p>
                                            <p className="text-sm leading-6">{tratamiento.observaciones}</p>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {/* Medicamentos del Tratamiento */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Pill className="h-5 w-5" />
                                    Medicamentos Prescritos ({tratamiento.medicamento_tratamientos.length})
                                </CardTitle>
                                <CardDescription>
                                    Esquemas de medicación y dosificación
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {tratamiento.medicamento_tratamientos.map((medicamentoTratamiento) => (
                                        <Card key={medicamentoTratamiento.id} className="border-2">
                                            <CardHeader className="pb-3">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-2">
                                                        <Badge variant="outline">#{medicamentoTratamiento.orden_prescripcion}</Badge>
                                                        <span className="font-medium">
                                                            {medicamentoTratamiento.medicamento.nombre_comercial}
                                                        </span>
                                                        {getEsquemaBadge(medicamentoTratamiento.tipo_esquema)}
                                                    </div>
                                                    <Badge 
                                                        variant={medicamentoTratamiento.activo ? "default" : "destructive"}
                                                        className={medicamentoTratamiento.activo ? "bg-green-100 text-green-800" : ""}
                                                    >
                                                        {medicamentoTratamiento.activo ? 'Activo' : 'Inactivo'}
                                                    </Badge>
                                                </div>
                                            </CardHeader>
                                            <CardContent className="space-y-3">
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <p className="text-sm font-medium text-muted-foreground">Principio Activo</p>
                                                        <p className="font-medium">{medicamentoTratamiento.medicamento.principio_activo.nombre_generico}</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-sm font-medium text-muted-foreground">Forma / Vía</p>
                                                        <p className="font-medium">
                                                            {medicamentoTratamiento.medicamento.forma_farmaceutica.nombre} - {medicamentoTratamiento.medicamento.via_administracion.nombre}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div>
                                                        <p className="text-sm font-medium text-muted-foreground">Dosis</p>
                                                        <p className="font-medium">
                                                            {medicamentoTratamiento.dosis_cantidad} {medicamentoTratamiento.unidad_dosis.simbolo}
                                                        </p>
                                                    </div>
                                                    {medicamentoTratamiento.frecuencia_horas && (
                                                        <div>
                                                            <p className="text-sm font-medium text-muted-foreground">Frecuencia</p>
                                                            <p className="font-medium">Cada {medicamentoTratamiento.frecuencia_horas}h</p>
                                                        </div>
                                                    )}
                                                    {medicamentoTratamiento.duracion_dias && (
                                                        <div>
                                                            <p className="text-sm font-medium text-muted-foreground">Duración</p>
                                                            <p className="font-medium">{medicamentoTratamiento.duracion_dias} días</p>
                                                        </div>
                                                    )}
                                                    <div>
                                                        <p className="text-sm font-medium text-muted-foreground">Esquema</p>
                                                        <p className="font-medium">{medicamentoTratamiento.tipo_esquema}</p>
                                                    </div>
                                                </div>

                                                {medicamentoTratamiento.indicaciones_uso && (
                                                    <div>
                                                        <p className="text-sm font-medium text-muted-foreground">Indicaciones</p>
                                                        <p className="text-sm">{medicamentoTratamiento.indicaciones_uso}</p>
                                                    </div>
                                                )}

                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t">
                                                    <div>
                                                        <p className="text-sm font-medium text-muted-foreground">Inicio</p>
                                                        <p className="text-sm">{formatDate(medicamentoTratamiento.fecha_inicio)}</p>
                                                    </div>
                                                    {medicamentoTratamiento.fecha_fin && (
                                                        <div>
                                                            <p className="text-sm font-medium text-muted-foreground">Fin</p>
                                                            <p className="text-sm">{formatDate(medicamentoTratamiento.fecha_fin)}</p>
                                                        </div>
                                                    )}
                                                </div>
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Historial del Tratamiento */}
                        {tratamiento.historial.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <History className="h-5 w-5" />
                                        Historial de Cambios
                                    </CardTitle>
                                    <CardDescription>
                                        Últimas modificaciones del tratamiento
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {tratamiento.historial.map((item) => (
                                            <div key={item.id} className="flex items-start gap-3 p-3 border rounded-lg">
                                                <Activity className="h-4 w-4 mt-1 text-muted-foreground" />
                                                <div className="flex-1">
                                                    <p className="font-medium">{item.accion}</p>
                                                    {item.campo_modificado && (
                                                        <p className="text-sm text-muted-foreground">
                                                            Campo: {item.campo_modificado}
                                                            {item.valor_anterior && item.valor_nuevo && (
                                                                <span className="ml-2">
                                                                    "{item.valor_anterior}" → "{item.valor_nuevo}"
                                                                </span>
                                                            )}
                                                        </p>
                                                    )}
                                                    {item.motivo && (
                                                        <p className="text-sm text-muted-foreground">{item.motivo}</p>
                                                    )}
                                                    <div className="flex items-center gap-4 mt-1 text-xs text-muted-foreground">
                                                        <span>{formatDateTime(item.creado_en)}</span>
                                                        {item.usuario && (
                                                            <span>por {item.usuario.name}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Panel Lateral */}
                    <div className="space-y-6">
                        {/* Información del Paciente */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5" />
                                    Paciente
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <p className="font-medium">{tratamiento.paciente.nombre}</p>
                                    <p className="text-sm text-muted-foreground">ID: {tratamiento.paciente.id}</p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Información del Médico */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Stethoscope className="h-5 w-5" />
                                    Información Médica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {tratamiento.medico && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Médico Tratante</p>
                                        <p className="font-medium">{tratamiento.medico.user.name}</p>
                                    </div>
                                )}

                                {tratamiento.medico_prescriptor && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Médico Prescriptor</p>
                                        <p className="font-medium">{tratamiento.medico_prescriptor}</p>
                                    </div>
                                )}

                                {tratamiento.institucion && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Institución</p>
                                        <p className="font-medium">{tratamiento.institucion}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Estadísticas Detalladas */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <BarChart3 className="h-5 w-5" />
                                    Estadísticas
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Medicamentos Activos</p>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="secondary" className="text-lg px-3 py-1">
                                            {stats.medicamentos_activos}
                                        </Badge>
                                    </div>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Administraciones</p>
                                    <div className="space-y-1">
                                        <div className="flex justify-between text-sm">
                                            <span>Programadas:</span>
                                            <Badge variant="outline">{stats.administraciones_programadas}</Badge>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span>Completadas:</span>
                                            <Badge variant="outline" className="bg-green-100 text-green-800">
                                                {stats.administraciones_completadas}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                {durationDays && (
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Progreso Temporal</p>
                                        <div className="space-y-1">
                                            <div className="flex justify-between text-sm">
                                                <span>Días transcurridos:</span>
                                                <span>{elapsedDays}</span>
                                            </div>
                                            <div className="flex justify-between text-sm">
                                                <span>Duración total:</span>
                                                <span>{durationDays} días</span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2 mt-2">
                                                <div 
                                                    className="bg-blue-600 h-2 rounded-full" 
                                                    style={{ width: `${Math.min((elapsedDays / durationDays) * 100, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Información del Sistema */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Activity className="h-5 w-5" />
                                    Sistema
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">ID del Tratamiento</p>
                                    <p className="font-mono text-sm">{tratamiento.id}</p>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Fecha de Creación</p>
                                    <p className="text-sm">{formatDateTime(tratamiento.created_at)}</p>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Última Actualización</p>
                                    <p className="text-sm">{formatDateTime(tratamiento.updated_at)}</p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Acciones Rápidas */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Acciones
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button className="w-full" asChild>
                                    <Link href={`/tratamientos/${tratamiento.id}/edit`}>
                                        <Edit className="h-4 w-4 mr-2" />
                                        Editar Tratamiento
                                    </Link>
                                </Button>
                                
                                <Button 
                                    variant="outline" 
                                    className="w-full" 
                                    onClick={handleToggleStatus}
                                >
                                    {tratamiento.estado === 'Activo' ? (
                                        <>
                                            <Pause className="h-4 w-4 mr-2" />
                                            Pausar Tratamiento
                                        </>
                                    ) : (
                                        <>
                                            <Play className="h-4 w-4 mr-2" />
                                            Reactivar Tratamiento
                                        </>
                                    )}
                                </Button>

                                {(tratamiento.estado === 'Activo' || tratamiento.estado === 'Pausado') && (
                                    <Button 
                                        variant="outline" 
                                        className="w-full" 
                                        onClick={handleCompletar}
                                    >
                                        <CheckCircle className="h-4 w-4 mr-2" />
                                        Completar Tratamiento
                                    </Button>
                                )}

                                <Button variant="outline" className="w-full" asChild>
                                    <Link href="/tratamientos">
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Volver al Listado
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 
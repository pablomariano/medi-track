import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { 
    Plus, 
    Users, 
    UserCheck, 
    Clock, 
    X,
    Eye,
    History,
    Stethoscope
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { CanAccess } from '@/components/auth/CanAccess';

interface PersonalMedico {
    usuario_id: number;
    especialidad: string;
    anos_experiencia: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Paciente {
    id: number;
    nombre: string;
    numero_documento: string;
    telefono_emergencia: string;
    activo: boolean;
}

interface AsignacionMedico {
    paciente_id: number;
    medico_usuario_id: number;
    es_medico_principal: boolean;
    fecha_asignacion: string;
    fecha_fin: string | null;
    especialidad_tratamiento: string | null;
    paciente: Paciente;
    medico: PersonalMedico;
    estado: 'principal' | 'secundaria' | 'finalizada';
    dias_restantes: number | null;
}

interface Estadisticas {
    total: number;
    vigentes: number;
    principales: number;
    finalizadas: number;
}

interface Props {
    asignaciones: {
        data: AsignacionMedico[];
        links: any[];
        meta: any;
    };
    estadisticas: Estadisticas;
}

export default function Index({ asignaciones, estadisticas }: Props) {

    const handleDelete = (asignacion: AsignacionMedico) => {
        if (confirm('¿Estás seguro de que deseas finalizar esta asignación?')) {
            router.delete(route('asignaciones-medicos.destroy', {
                paciente: asignacion.paciente_id,
                medico: asignacion.medico_usuario_id
            }));
        }
    };

    const getEstadoBadge = (asignacion: AsignacionMedico) => {
        const badges = {
            principal: { 
                texto: 'Médico Principal', 
                className: 'bg-blue-100 text-blue-800' 
            },
            secundaria: { 
                texto: 'Médico Secundario', 
                className: 'bg-green-100 text-green-800' 
            },
            finalizada: { 
                texto: 'Finalizada', 
                className: 'bg-gray-100 text-gray-800' 
            }
        };

        const badge = badges[asignacion.estado];
        return (
            <Badge variant="secondary" className={badge.className}>
                {badge.texto}
            </Badge>
        );
    };

    const getDiasRestantes = (asignacion: AsignacionMedico) => {
        if (!asignacion.fecha_fin) {
            return <span className="text-sm text-gray-500">Indefinida</span>;
        }

        if (asignacion.dias_restantes === null || asignacion.dias_restantes <= 0) {
            return <span className="text-sm text-red-600">Vencida</span>;
        }

        const color = asignacion.dias_restantes <= 7 ? 'text-orange-600' : 'text-green-600';
        return (
            <span className={`text-sm ${color}`}>
                {asignacion.dias_restantes} día{asignacion.dias_restantes !== 1 ? 's' : ''}
            </span>
        );
    };

    return (
        <AppLayout>
            <Head title="Asignaciones Médico-Paciente" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900">
                            Asignaciones Médico-Paciente
                        </h1>
                        <p className="text-muted-foreground">
                            Gestiona las asignaciones entre médicos y pacientes
                        </p>
                    </div>
                    
                    <div className="flex items-center gap-3">
                        <CanAccess permission="personal-medico.create">
                            <Button asChild variant="outline">
                                <Link href={route('asignaciones-medicos.historial')}>
                                    <History className="h-4 w-4 mr-2" />
                                    Historial
                                </Link>
                            </Button>
                        </CanAccess>
                        
                        <CanAccess permission="personal-medico.create">
                            <Button asChild>
                                <Link href={route('asignaciones-medicos.create')}>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nueva Asignación
                                </Link>
                            </Button>
                        </CanAccess>
                    </div>
                </div>

                {/* Estadísticas */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Asignaciones
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.total}</div>
                            <p className="text-xs text-muted-foreground">
                                Todas las asignaciones registradas
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Asignaciones Vigentes
                            </CardTitle>
                            <UserCheck className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.vigentes}</div>
                            <p className="text-xs text-muted-foreground">
                                Activas sin fecha de fin
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Médicos Principales
                            </CardTitle>
                            <Stethoscope className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.principales}</div>
                            <p className="text-xs text-muted-foreground">
                                Asignaciones como médico principal
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Finalizadas
                            </CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{estadisticas.finalizadas}</div>
                            <p className="text-xs text-muted-foreground">
                                Asignaciones completadas
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabla de Asignaciones */}
                <Card>
                    <CardHeader>
                        <CardTitle>Asignaciones Médico-Paciente</CardTitle>
                        <CardDescription>
                            Lista de todas las asignaciones entre médicos y pacientes
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Paciente</TableHead>
                                        <TableHead>Médico</TableHead>
                                        <TableHead>Especialidad</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead>Fecha Asignación</TableHead>
                                        <TableHead>Días Restantes</TableHead>
                                        <TableHead className="text-right">Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {asignaciones.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center py-6">
                                                No hay asignaciones registradas
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        asignaciones.data.map((asignacion) => (
                                            <TableRow key={`${asignacion.paciente_id}-${asignacion.medico_usuario_id}`}>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">{asignacion.paciente.nombre}</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            Doc: {asignacion.paciente.numero_documento}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">{asignacion.medico.user.name}</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {asignacion.medico.anos_experiencia} años exp.
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="text-sm">{asignacion.medico.especialidad}</div>
                                                        {asignacion.especialidad_tratamiento && (
                                                            <div className="text-xs text-muted-foreground">
                                                                Tto: {asignacion.especialidad_tratamiento}
                                                            </div>
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {getEstadoBadge(asignacion)}
                                                </TableCell>
                                                <TableCell>
                                                    {format(new Date(asignacion.fecha_asignacion), 'dd/MM/yyyy', { locale: es })}
                                                </TableCell>
                                                <TableCell>
                                                    {getDiasRestantes(asignacion)}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <CanAccess permission="personal-medico.view">
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                asChild
                                                            >
                                                                <Link href={route('asignaciones-medicos.show', {
                                                                    paciente: asignacion.paciente_id,
                                                                    medico: asignacion.medico_usuario_id
                                                                })}>
                                                                    <Eye className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        </CanAccess>
                                                        
                                                        <CanAccess permission="personal-medico.delete">
                                                            {asignacion.estado !== 'finalizada' && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleDelete(asignacion)}
                                                                    className="text-red-600 hover:text-red-900"
                                                                >
                                                                    <X className="h-4 w-4" />
                                                                </Button>
                                                            )}
                                                        </CanAccess>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Paginación */}
                        {asignaciones.links && asignaciones.links.length > 3 && (
                            <div className="flex items-center justify-center gap-2 mt-4">
                                {asignaciones.links.map((link, index) => (
                                    <Button
                                        key={index}
                                        variant={link.active ? "default" : "outline"}
                                        size="sm"
                                        onClick={() => link.url && router.get(link.url)}
                                        disabled={!link.url}
                                        className="min-w-[40px]"
                                    >
                                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                    </Button>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 
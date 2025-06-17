import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft, Edit, Trash2, CheckCircle, XCircle, User, Phone, MapPin, Calendar, FileText, Activity, UserCheck, Plus } from 'lucide-react';
import { Link, router } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface User {
    id: number;
    name: string;
    email: string;
}

interface Genero {
    id: string;
    nombre: string;
}

interface Cuidador {
    usuario_id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    experiencia_anos: number | null;
    tarifa_hora: number | null;
    pivot: {
        fecha_asignacion: string;
        fecha_fin: string | null;
        activo: boolean;
    };
}

interface Paciente {
    id: number;
    usuario_id: number | null;
    nombre: string;
    fecha_nacimiento: string | null;
    genero_id: string | null;
    numero_documento: string | null;
    tipo_documento: string | null;
    tipo_sangre: string | null;
    altura: number | null;
    direccion: string | null;
    telefono_emergencia: string | null;
    observaciones_medicas: string | null;
    activo: boolean;
    created_at: string;
    user: User | null;
    genero: Genero | null;
    cuidadores_vigentes: Cuidador[];
}

interface Props {
    paciente: Paciente;
}

export default function Show({ paciente }: Props) {
    const handleDelete = () => {
        if (confirm('¿Estás seguro de que deseas eliminar este paciente?')) {
            router.delete(route('pacientes.destroy', paciente.id));
        }
    };

    const calcularEdad = (fechaNacimiento: string | null) => {
        if (!fechaNacimiento) return 'No especificado';
        const today = new Date();
        const birthDate = new Date(fechaNacimiento);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age + ' años';
    };

    const formatTipoDocumento = (tipo: string | null) => {
        const tipos: Record<string, string> = {
            'rut': 'RUT',
            'ci': 'Cédula de Identidad',
            'passport': 'Pasaporte',
            'otro': 'Otro'
        };
        return tipos[tipo || ''] || (tipo ? tipo.toUpperCase() : 'No especificado');
    };

    const formatFecha = (fecha: string) => {
        return new Date(fecha).toLocaleDateString('es-CL', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-4">
                        <Link href={route('pacientes.index')}>
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">{paciente.nombre}</h1>
                            <p className="text-muted-foreground">
                                Información del paciente
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Link href={route('pacientes.edit', paciente.id)}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Editar
                            </Button>
                        </Link>
                        <Button variant="destructive" onClick={handleDelete}>
                            <Trash2 className="h-4 w-4 mr-2" />
                            Eliminar
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Información Principal */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Información Básica */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5" />
                                    Información Básica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Nombre Completo</h4>
                                        <p className="font-medium">{paciente.nombre}</p>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Estado</h4>
                                        <div className="flex items-center gap-2">
                                            {paciente.activo ? (
                                                <>
                                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                                    <Badge variant="secondary" className="bg-green-100 text-green-800">
                                                        Activo
                                                    </Badge>
                                                </>
                                            ) : (
                                                <>
                                                    <XCircle className="h-4 w-4 text-red-500" />
                                                    <Badge variant="secondary" className="bg-red-100 text-red-800">
                                                        Inactivo
                                                    </Badge>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Fecha de Nacimiento</h4>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <p>{paciente.fecha_nacimiento ? formatFecha(paciente.fecha_nacimiento) : 'No especificado'}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Edad</h4>
                                        <p className="font-medium">{calcularEdad(paciente.fecha_nacimiento)}</p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Género</h4>
                                        <p>{paciente.genero ? paciente.genero.nombre : 'No especificado'}</p>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Registrado el</h4>
                                        <p>{formatFecha(paciente.created_at)}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Documentación */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="h-5 w-5" />
                                    Documentación
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Tipo de Documento</h4>
                                        <p>{formatTipoDocumento(paciente.tipo_documento)}</p>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Número de Documento</h4>
                                        <p className="font-mono">{paciente.numero_documento || 'No especificado'}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Información Médica */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Activity className="h-5 w-5" />
                                    Información Médica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Tipo de Sangre</h4>
                                        <div>
                                            {paciente.tipo_sangre ? (
                                                <Badge variant="secondary" className="bg-red-100 text-red-800">
                                                    {paciente.tipo_sangre.toUpperCase()}
                                                </Badge>
                                            ) : (
                                                <p className="text-muted-foreground">No especificado</p>
                                            )}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Altura</h4>
                                        <p>{paciente.altura ? `${paciente.altura} cm` : 'No especificado'}</p>
                                    </div>
                                </div>

                                {paciente.observaciones_medicas && (
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Observaciones Médicas</h4>
                                        <div className="bg-muted/50 p-3 rounded-md">
                                            <p className="text-sm whitespace-pre-wrap">{paciente.observaciones_medicas}</p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Información de Contacto */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Phone className="h-5 w-5" />
                                    Información de Contacto
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h4 className="font-medium text-sm text-muted-foreground">Teléfono de Emergencia</h4>
                                    <p>{paciente.telefono_emergencia || 'No especificado'}</p>
                                </div>

                                {paciente.direccion && (
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground flex items-center gap-2">
                                            <MapPin className="h-4 w-4" />
                                            Dirección
                                        </h4>
                                        <div className="bg-muted/50 p-3 rounded-md">
                                            <p className="text-sm">{paciente.direccion}</p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Cuidadores Asignados */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="flex items-center gap-2">
                                        <UserCheck className="h-5 w-5" />
                                        Cuidadores Asignados
                                    </CardTitle>
                                    <Link href={route('asignaciones-cuidadores.create')}>
                                        <Button size="sm">
                                            <Plus className="h-4 w-4 mr-2" />
                                            Asignar Cuidador
                                        </Button>
                                    </Link>
                                </div>
                                <CardDescription>
                                    Personal de cuidado actualmente asignado a este paciente
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {paciente.cuidadores_vigentes && paciente.cuidadores_vigentes.length > 0 ? (
                                    <div className="space-y-4">
                                        {paciente.cuidadores_vigentes.map((cuidador) => (
                                            <div key={cuidador.usuario_id} className="flex items-center justify-between p-3 border rounded-lg">
                                                <div className="flex items-center space-x-3">
                                                    <div className="flex-shrink-0">
                                                        <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <UserCheck className="h-4 w-4 text-blue-600" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p className="font-medium">{cuidador.user.name}</p>
                                                        <p className="text-sm text-muted-foreground">{cuidador.user.email}</p>
                                                        <div className="flex items-center gap-2 mt-1">
                                                            <Badge variant="outline" className="text-xs">
                                                                Desde: {formatFecha(cuidador.pivot.fecha_asignacion)}
                                                            </Badge>
                                                            {cuidador.experiencia_anos && (
                                                                <Badge variant="secondary" className="text-xs">
                                                                    {cuidador.experiencia_anos} años exp.
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <Badge variant="secondary" className="bg-green-100 text-green-800">
                                                        Activo
                                                    </Badge>
                                                    <Button variant="outline" size="sm" asChild>
                                                        <Link href={route('asignaciones-cuidadores.show', [paciente.id, cuidador.usuario_id])}>
                                                            Ver
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-6">
                                        <UserCheck className="mx-auto h-12 w-12 text-muted-foreground" />
                                        <h3 className="mt-2 text-sm font-medium text-muted-foreground">Sin cuidadores asignados</h3>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Este paciente no tiene cuidadores asignados actualmente.
                                        </p>
                                        <div className="mt-6">
                                            <Link href={route('asignaciones-cuidadores.create')}>
                                                <Button>
                                                    <Plus className="h-4 w-4 mr-2" />
                                                    Asignar primer cuidador
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Usuario del Sistema */}
                        {paciente.user && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Usuario del Sistema</CardTitle>
                                    <CardDescription>
                                        Cuenta de acceso asociada
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        <div>
                                            <h4 className="font-medium text-sm text-muted-foreground">Nombre</h4>
                                            <p className="font-medium">{paciente.user.name}</p>
                                        </div>
                                        <div>
                                            <h4 className="font-medium text-sm text-muted-foreground">Email</h4>
                                            <p className="text-sm text-muted-foreground">{paciente.user.email}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Estadísticas rápidas */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Resumen</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">ID del Paciente</span>
                                    <Badge variant="outline">#{paciente.id}</Badge>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">Tiene Usuario</span>
                                    {paciente.user ? (
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                    ) : (
                                        <XCircle className="h-4 w-4 text-red-500" />
                                    )}
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">Información Médica</span>
                                    {(paciente.tipo_sangre || paciente.altura || paciente.observaciones_medicas) ? (
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                    ) : (
                                        <XCircle className="h-4 w-4 text-red-500" />
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 
import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft, Edit, Trash2, CheckCircle, XCircle, User, Phone, MapPin, Calendar, FileText, Activity } from 'lucide-react';
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
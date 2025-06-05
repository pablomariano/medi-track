import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft, Edit, Trash2, CheckCircle, XCircle, User, Phone, Mail, MailCheck, Shield, Calendar, Clock } from 'lucide-react';
import { Link, router } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface Role {
    id: number;
    nombre: string;
    descripcion: string;
    activo: boolean;
}

interface Usuario {
    id: number;
    name: string;
    email: string;
    telefono: string | null;
    activo: boolean;
    email_verified_at: string | null;
    ultimo_acceso: string | null;
    created_at: string;
    role: Role | null;
}

interface Props {
    usuario: Usuario;
}

export default function Show({ usuario }: Props) {
    const handleDelete = () => {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            router.delete(route('usuarios.destroy', usuario.id));
        }
    };

    const formatFecha = (fecha: string | null) => {
        if (!fecha) return 'No disponible';
        return new Date(fecha).toLocaleDateString('es-CL', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatFechaCorta = (fecha: string) => {
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
                        <Link href={route('usuarios.index')}>
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold">{usuario.name}</h1>
                            <p className="text-muted-foreground">
                                Información del usuario
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Link href={route('usuarios.edit', usuario.id)}>
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
                                        <p className="font-medium">{usuario.name}</p>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Email</h4>
                                        <div className="flex items-center gap-2">
                                            <Mail className="h-4 w-4 text-muted-foreground" />
                                            <p>{usuario.email}</p>
                                        </div>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Teléfono</h4>
                                        <div className="flex items-center gap-2">
                                            <Phone className="h-4 w-4 text-muted-foreground" />
                                            <p>{usuario.telefono || 'No especificado'}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Registrado el</h4>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <p>{formatFechaCorta(usuario.created_at)}</p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Rol y Permisos */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Shield className="h-5 w-5" />
                                    Rol y Permisos
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Rol Asignado</h4>
                                        {usuario.role ? (
                                            <div className="mt-2">
                                                <Badge variant="secondary" className="bg-blue-100 text-blue-800 text-sm">
                                                    {usuario.role.nombre}
                                                </Badge>
                                                <p className="text-sm text-muted-foreground mt-1">{usuario.role.descripcion}</p>
                                            </div>
                                        ) : (
                                            <p className="text-muted-foreground">Sin rol asignado</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Actividad */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Clock className="h-5 w-5" />
                                    Actividad
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div>
                                        <h4 className="font-medium text-sm text-muted-foreground">Último Acceso</h4>
                                        <p>{formatFecha(usuario.ultimo_acceso)}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Estado del Usuario */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Estado del Usuario</CardTitle>
                                <CardDescription>
                                    Estado actual y configuración
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">Estado General</span>
                                    <div className="flex items-center gap-2">
                                        {usuario.activo ? (
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

                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">Email Verificado</span>
                                    <div className="flex items-center gap-2">
                                        {usuario.email_verified_at ? (
                                            <>
                                                <MailCheck className="h-4 w-4 text-green-500" />
                                                <Badge variant="secondary" className="bg-green-100 text-green-800">
                                                    Verificado
                                                </Badge>
                                            </>
                                        ) : (
                                            <>
                                                <Mail className="h-4 w-4 text-amber-500" />
                                                <Badge variant="secondary" className="bg-amber-100 text-amber-800">
                                                    Pendiente
                                                </Badge>
                                            </>
                                        )}
                                    </div>
                                </div>

                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">Tiene Rol</span>
                                    {usuario.role ? (
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                    ) : (
                                        <XCircle className="h-4 w-4 text-red-500" />
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Información del Sistema */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Información del Sistema</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">ID del Usuario</span>
                                    <Badge variant="outline">#{usuario.id}</Badge>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-muted-foreground">Tiene Teléfono</span>
                                    {usuario.telefono ? (
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                    ) : (
                                        <XCircle className="h-4 w-4 text-red-500" />
                                    )}
                                </div>
                                {usuario.email_verified_at && (
                                    <div>
                                        <span className="text-sm text-muted-foreground">Email verificado el</span>
                                        <p className="text-xs text-muted-foreground mt-1">
                                            {formatFecha(usuario.email_verified_at)}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 
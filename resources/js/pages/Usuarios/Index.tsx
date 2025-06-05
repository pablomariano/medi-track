import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Plus, Pencil, Trash2, Eye, CheckCircle, XCircle, Mail, MailCheck } from 'lucide-react';
import { router } from '@inertiajs/react';
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

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    usuarios: {
        data: Usuario[];
        links: PaginationLink[];
    };
}

export default function Index({ usuarios }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            router.delete(route('usuarios.destroy', id));
        }
    };

    const formatFecha = (fecha: string | null) => {
        if (!fecha) return 'Nunca';
        return new Date(fecha).toLocaleDateString('es-CL', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Usuarios</CardTitle>
                                <CardDescription>
                                    Gestiona los usuarios del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('usuarios.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Usuario
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Rol</TableHead>
                                    <TableHead>Teléfono</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Email Verificado</TableHead>
                                    <TableHead>Último Acceso</TableHead>
                                    <TableHead className="w-[120px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {usuarios.data.map((usuario) => (
                                    <TableRow key={usuario.id}>
                                        <TableCell className="font-medium">{usuario.name}</TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {usuario.email}
                                        </TableCell>
                                        <TableCell>
                                            {usuario.role ? (
                                                <Badge variant="secondary" className="bg-blue-100 text-blue-800">
                                                    {usuario.role.nombre}
                                                </Badge>
                                            ) : (
                                                <span className="text-gray-400 text-sm">Sin rol</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {usuario.telefono ? (
                                                <span className="text-sm">{usuario.telefono}</span>
                                            ) : (
                                                <span className="text-gray-400 text-sm">Sin teléfono</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {usuario.activo ? (
                                                    <>
                                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                                        <span className="text-sm text-green-600">Activo</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <XCircle className="h-4 w-4 text-red-500" />
                                                        <span className="text-sm text-red-600">Inactivo</span>
                                                    </>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {usuario.email_verified_at ? (
                                                    <>
                                                        <MailCheck className="h-4 w-4 text-green-500" />
                                                        <span className="text-sm text-green-600">Verificado</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <Mail className="h-4 w-4 text-amber-500" />
                                                        <span className="text-sm text-amber-600">Pendiente</span>
                                                    </>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span className="text-sm text-muted-foreground">
                                                {formatFecha(usuario.ultimo_acceso)}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1">
                                                <Link href={route('usuarios.show', usuario.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Link href={route('usuarios.edit', usuario.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(usuario.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 
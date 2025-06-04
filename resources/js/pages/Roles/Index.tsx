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
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { router } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

interface Role {
    id: number;
    nombre: string;
    descripcion: string;
    activo: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    roles: {
        data: Role[];
        links: PaginationLink[];
    };
}

export default function Index({ roles }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este rol?')) {
            router.delete(route('roles.destroy', id));
        }
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Roles</CardTitle>
                                <CardDescription>
                                    Gestiona los roles del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('roles.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Rol
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Descripción</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {roles.data.map((role) => (
                                    <TableRow key={role.id}>
                                        <TableCell className="font-medium">{role.nombre}</TableCell>
                                        <TableCell>{role.descripcion}</TableCell>
                                        <TableCell>
                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                role.activo 
                                                    ? 'bg-green-100 text-green-800' 
                                                    : 'bg-red-100 text-red-800'
                                            }`}>
                                                {role.activo ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('roles.edit', role.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(role.id)}
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
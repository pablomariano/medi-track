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

interface Permiso {
    id: number;
    nombre: string;
    descripcion: string;
    modulo: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    permisos: {
        data: Permiso[];
        links: PaginationLink[];
    };
}

export default function Index({ permisos }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este permiso?')) {
            router.delete(route('permisos.destroy', id));
        }
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Permisos</CardTitle>
                                <CardDescription>
                                    Gestiona los permisos del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('permisos.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Permiso
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
                                    <TableHead>Módulo</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {permisos.data.map((permiso) => (
                                    <TableRow key={permiso.id}>
                                        <TableCell className="font-medium">{permiso.nombre}</TableCell>
                                        <TableCell>{permiso.descripcion}</TableCell>
                                        <TableCell>
                                            {permiso.modulo && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {permiso.modulo}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('permisos.edit', permiso.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(permiso.id)}
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
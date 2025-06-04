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
import { Plus, Pencil, Trash2, CheckCircle, XCircle } from 'lucide-react';
import { router } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

interface Apoderado {
    usuario_id: number;
    relacion_paciente: string;
    es_contacto_emergencia: boolean;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    apoderados: {
        data: Apoderado[];
        links: PaginationLink[];
    };
}

export default function Index({ apoderados }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este apoderado?')) {
            router.delete(route('apoderados.destroy', id));
        }
    };

    const formatRelacion = (relacion: string) => {
        const relaciones: { [key: string]: string } = {
            'padre': 'Padre',
            'madre': 'Madre',
            'hermano': 'Hermano/a',
            'abuelo': 'Abuelo/a',
            'tutor': 'Tutor Legal',
            'otro': 'Otro'
        };
        return relaciones[relacion] || (relacion ? relacion.charAt(0).toUpperCase() + relacion.slice(1) : '');
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Apoderados</CardTitle>
                                <CardDescription>
                                    Gestiona los apoderados del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('apoderados.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Apoderado
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
                                    <TableHead>Relación</TableHead>
                                    <TableHead>Contacto Emergencia</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {apoderados.data.map((apoderado) => (
                                    <TableRow key={apoderado.usuario_id}>
                                        <TableCell className="font-medium">{apoderado.user.name}</TableCell>
                                        <TableCell>{apoderado.user.email}</TableCell>
                                        <TableCell>
                                            {apoderado.relacion_paciente && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    {formatRelacion(apoderado.relacion_paciente)}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {apoderado.es_contacto_emergencia ? (
                                                    <>
                                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                                        <span className="text-sm text-green-600">Sí</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <XCircle className="h-4 w-4 text-red-500" />
                                                        <span className="text-sm text-red-600">No</span>
                                                    </>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('apoderados.edit', apoderado.usuario_id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(apoderado.usuario_id)}
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
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

interface Cuidador {
    usuario_id: number;
    certificaciones: string;
    experiencia_anos: number;
    disponibilidad_horaria: string;
    tarifa_hora: number;
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
    cuidadores: {
        data: Cuidador[];
        links: PaginationLink[];
    };
}

export default function Index({ cuidadores }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este cuidador?')) {
            router.delete(route('cuidadores.destroy', id));
        }
    };

    const formatTarifa = (tarifa: number) => {
        if (!tarifa) return '';
        return `$${tarifa.toLocaleString('es-CL')}`;
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Cuidadores</CardTitle>
                                <CardDescription>
                                    Gestiona los cuidadores del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('cuidadores.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Cuidador
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
                                    <TableHead>Experiencia</TableHead>
                                    <TableHead>Disponibilidad</TableHead>
                                    <TableHead>Tarifa/Hora</TableHead>
                                    <TableHead>Certificaciones</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {cuidadores.data.map((cuidador) => (
                                    <TableRow key={cuidador.usuario_id}>
                                        <TableCell className="font-medium">{cuidador.user.name}</TableCell>
                                        <TableCell>{cuidador.user.email}</TableCell>
                                        <TableCell>
                                            {cuidador.experiencia_anos && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {cuidador.experiencia_anos} años
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {cuidador.disponibilidad_horaria && (
                                                <span className="text-sm text-gray-600">
                                                    {cuidador.disponibilidad_horaria}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {cuidador.tarifa_hora && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {formatTarifa(cuidador.tarifa_hora)}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell className="max-w-xs">
                                            {cuidador.certificaciones && (
                                                <span className="text-sm text-gray-600 truncate block">
                                                    {cuidador.certificaciones.length > 50 
                                                        ? cuidador.certificaciones.substring(0, 50) + '...'
                                                        : cuidador.certificaciones
                                                    }
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('cuidadores.edit', cuidador.usuario_id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(cuidador.usuario_id)}
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
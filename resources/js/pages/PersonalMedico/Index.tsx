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

interface PersonalMedico {
    usuario_id: number;
    especialidad: string;
    numero_colegiatura: string;
    institucion: string;
    anos_experiencia: number;
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
    personalMedico: {
        data: PersonalMedico[];
        links: PaginationLink[];
    };
}

export default function Index({ personalMedico }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este personal médico?')) {
            router.delete(route('personal-medico.destroy', id));
        }
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Personal Médico</CardTitle>
                                <CardDescription>
                                    Gestiona el personal médico del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('personal-medico.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Personal Médico
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
                                    <TableHead>Especialidad</TableHead>
                                    <TableHead>Colegiatura</TableHead>
                                    <TableHead>Institución</TableHead>
                                    <TableHead>Experiencia</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {personalMedico.data.map((medico) => (
                                    <TableRow key={medico.usuario_id}>
                                        <TableCell className="font-medium">{medico.user.name}</TableCell>
                                        <TableCell>{medico.user.email}</TableCell>
                                        <TableCell>
                                            {medico.especialidad && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {medico.especialidad}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>{medico.numero_colegiatura}</TableCell>
                                        <TableCell>{medico.institucion}</TableCell>
                                        <TableCell>
                                            {medico.anos_experiencia && (
                                                <span className="text-sm text-gray-600">
                                                    {medico.anos_experiencia} años
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('personal-medico.edit', medico.usuario_id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(medico.usuario_id)}
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
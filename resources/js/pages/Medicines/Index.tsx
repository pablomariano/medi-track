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

interface Medicine {
    id: number;
    nombre: string;
    medida: string;
    unidad_medida: string;
    descripcion: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    medicines: {
        data: Medicine[];
        links: PaginationLink[];
    };
}

export default function Index({ medicines }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este medicamento?')) {
            router.delete(route('medicines.destroy', id));
        }
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Medicamentos</CardTitle>
                                <CardDescription>
                                    Gestiona el inventario de medicamentos
                                </CardDescription>
                            </div>
                            <Link href={route('medicines.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Medicamento
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Medida</TableHead>
                                    <TableHead>Unidad de Medida</TableHead>
                                    <TableHead>Descripción</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {medicines.data.map((medicine) => (
                                    <TableRow key={medicine.id}>
                                        <TableCell className="font-medium">{medicine.nombre}</TableCell>
                                        <TableCell>{medicine.medida}</TableCell>
                                        <TableCell>{medicine.unidad_medida}</TableCell>
                                        <TableCell>{medicine.descripcion}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('medicines.edit', medicine.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(medicine.id)}
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
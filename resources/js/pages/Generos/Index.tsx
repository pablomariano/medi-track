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

interface Genero {
    id: string;
    nombre: string;
}

interface Props {
    generos: Genero[];
}

export default function Index({ generos }: Props) {
    const handleDelete = (id: string) => {
        if (confirm('¿Estás seguro de que deseas eliminar este género?')) {
            router.delete(route('generos.destroy', id));
        }
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Géneros</CardTitle>
                                <CardDescription>
                                    Gestiona los géneros del sistema
                                </CardDescription>
                            </div>
                            <Link href={route('generos.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Género
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {generos.map((genero) => (
                                    <TableRow key={genero.id}>
                                        <TableCell className="font-medium">
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {genero.id}
                                            </span>
                                        </TableCell>
                                        <TableCell>{genero.nombre}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Link href={route('generos.edit', genero.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(genero.id)}
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
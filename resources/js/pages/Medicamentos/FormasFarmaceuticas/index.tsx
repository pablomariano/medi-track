import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { 
    Plus, 
    Search, 
    MoreHorizontal, 
    Edit, 
    Trash2,
    RefreshCw
} from 'lucide-react';

interface FormaFarmaceutica {
    id: number;
    nombre: string;
    descripcion?: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    formasFarmaceuticas: {
        data: FormaFarmaceutica[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        activo?: string;
    };
}

export default function Index({ formasFarmaceuticas, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleFilter = () => {
        router.get('/medicamentos/formas-farmaceuticas', {
            search: search || undefined,
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        router.get('/medicamentos/formas-farmaceuticas');
    };

    const handleDelete = (id: number) => {
        if (confirm('¿Está seguro que desea eliminar esta forma farmacéutica?')) {
            router.delete(`/medicamentos/formas-farmaceuticas/${id}`);
        }
    };

    const toggleStatus = (id: number) => {
        router.post(`/medicamentos/formas-farmaceuticas/${id}/toggle-status`);
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Formas Farmacéuticas', href: '/medicamentos/formas-farmaceuticas' }
            ]}
        >
            <Head title="Formas Farmacéuticas" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Formas Farmacéuticas</h1>
                        <p className="text-muted-foreground">
                            Gestión de formas farmacéuticas (tabletas, cápsulas, jarabes, etc.)
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/medicamentos/formas-farmaceuticas/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Nueva Forma
                        </Link>
                    </Button>
                </div>

                {/* Filtros */}
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center gap-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar por nombre..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-10"
                                    onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                                />
                            </div>
                            <Button onClick={handleFilter} variant="default">
                                <Search className="h-4 w-4 mr-2" />
                                Buscar
                            </Button>
                            <Button onClick={clearFilters} variant="outline">
                                <RefreshCw className="h-4 w-4 mr-2" />
                                Limpiar
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Resultados */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">
                            Formas Farmacéuticas ({formasFarmaceuticas.total})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nombre</TableHead>
                                        <TableHead>Descripción</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead className="text-right">Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {formasFarmaceuticas.data.map((forma) => (
                                        <TableRow key={forma.id}>
                                            <TableCell className="font-medium">
                                                {forma.nombre}
                                            </TableCell>
                                            <TableCell>
                                                {forma.descripcion || '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge 
                                                    variant={forma.activo ? "default" : "destructive"}
                                                    className={forma.activo ? "bg-green-100 text-green-800" : ""}
                                                >
                                                    {forma.activo ? 'Activo' : 'Inactivo'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" className="h-8 w-8 p-0">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/medicamentos/formas-farmaceuticas/${forma.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Editar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => toggleStatus(forma.id)}>
                                                            <RefreshCw className="h-4 w-4 mr-2" />
                                                            {forma.activo ? 'Desactivar' : 'Activar'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(forma.id)}
                                                            className="text-red-600"
                                                        >
                                                            <Trash2 className="h-4 w-4 mr-2" />
                                                            Eliminar
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Paginación */}
                        {formasFarmaceuticas.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((formasFarmaceuticas.current_page - 1) * formasFarmaceuticas.per_page) + 1} a{' '}
                                    {Math.min(formasFarmaceuticas.current_page * formasFarmaceuticas.per_page, formasFarmaceuticas.total)} de{' '}
                                    {formasFarmaceuticas.total} resultados
                                </div>
                                <div className="flex gap-2">
                                    {formasFarmaceuticas.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/formas-farmaceuticas', {
                                                ...filters,
                                                page: formasFarmaceuticas.current_page - 1
                                            })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {formasFarmaceuticas.current_page < formasFarmaceuticas.last_page && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/formas-farmaceuticas', {
                                                ...filters,
                                                page: formasFarmaceuticas.current_page + 1
                                            })}
                                        >
                                            Siguiente
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 
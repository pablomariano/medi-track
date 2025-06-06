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

interface ViaAdministracion {
    id: number;
    nombre: string;
    descripcion?: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    viasAdministracion: {
        data: ViaAdministracion[];
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

export default function Index({ viasAdministracion, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleFilter = () => {
        router.get('/medicamentos/vias-administracion', {
            search: search || undefined,
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        router.get('/medicamentos/vias-administracion');
    };

    const handleDelete = (id: number) => {
        if (confirm('¿Está seguro que desea eliminar esta vía de administración?')) {
            router.delete(`/medicamentos/vias-administracion/${id}`);
        }
    };

    const toggleStatus = (id: number) => {
        router.post(`/medicamentos/vias-administracion/${id}/toggle-status`);
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Vías de Administración', href: '/medicamentos/vias-administracion' }
            ]}
        >
            <Head title="Vías de Administración" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Vías de Administración</h1>
                        <p className="text-muted-foreground">
                            Gestión de vías de administración (oral, intravenosa, intramuscular, etc.)
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/medicamentos/vias-administracion/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Nueva Vía
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
                            Vías de Administración ({viasAdministracion.total})
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
                                    {viasAdministracion.data.map((via) => (
                                        <TableRow key={via.id}>
                                            <TableCell className="font-medium">
                                                {via.nombre}
                                            </TableCell>
                                            <TableCell>
                                                {via.descripcion || '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge 
                                                    variant={via.activo ? "default" : "destructive"}
                                                    className={via.activo ? "bg-green-100 text-green-800" : ""}
                                                >
                                                    {via.activo ? 'Activo' : 'Inactivo'}
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
                                                            <Link href={`/medicamentos/vias-administracion/${via.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Editar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => toggleStatus(via.id)}>
                                                            <RefreshCw className="h-4 w-4 mr-2" />
                                                            {via.activo ? 'Desactivar' : 'Activar'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(via.id)}
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
                        {viasAdministracion.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((viasAdministracion.current_page - 1) * viasAdministracion.per_page) + 1} a{' '}
                                    {Math.min(viasAdministracion.current_page * viasAdministracion.per_page, viasAdministracion.total)} de{' '}
                                    {viasAdministracion.total} resultados
                                </div>
                                <div className="flex gap-2">
                                    {viasAdministracion.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/vias-administracion', {
                                                ...filters,
                                                page: viasAdministracion.current_page - 1
                                            })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {viasAdministracion.current_page < viasAdministracion.last_page && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/vias-administracion', {
                                                ...filters,
                                                page: viasAdministracion.current_page + 1
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
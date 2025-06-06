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

interface UnidadMedida {
    id: number;
    nombre: string;
    simbolo: string;
    tipo: string;
    descripcion?: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    unidadesMedida: {
        data: UnidadMedida[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        tipo?: string;
        activo?: string;
    };
}

export default function Index({ unidadesMedida, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleFilter = () => {
        router.get('/medicamentos/unidades-medida', {
            search: search || undefined,
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        router.get('/medicamentos/unidades-medida');
    };

    const handleDelete = (id: number) => {
        if (confirm('¿Está seguro que desea eliminar esta unidad de medida?')) {
            router.delete(`/medicamentos/unidades-medida/${id}`);
        }
    };

    const toggleStatus = (id: number) => {
        router.post(`/medicamentos/unidades-medida/${id}/toggle-status`);
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Unidades de Medida', href: '/medicamentos/unidades-medida' }
            ]}
        >
            <Head title="Unidades de Medida" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Unidades de Medida</h1>
                        <p className="text-muted-foreground">
                            Gestión de unidades de medida para medicamentos
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/medicamentos/unidades-medida/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Nueva Unidad
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
                                    placeholder="Buscar por nombre o símbolo..."
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
                            Unidades de Medida ({unidadesMedida.total})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nombre</TableHead>
                                        <TableHead>Símbolo</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Descripción</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead className="text-right">Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {unidadesMedida.data.map((unidad) => (
                                        <TableRow key={unidad.id}>
                                            <TableCell className="font-medium">
                                                {unidad.nombre}
                                            </TableCell>
                                            <TableCell>
                                                <code className="bg-gray-100 px-2 py-1 rounded text-sm">
                                                    {unidad.simbolo}
                                                </code>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    {unidad.tipo}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {unidad.descripcion || '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge 
                                                    variant={unidad.activo ? "default" : "destructive"}
                                                    className={unidad.activo ? "bg-green-100 text-green-800" : ""}
                                                >
                                                    {unidad.activo ? 'Activo' : 'Inactivo'}
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
                                                            <Link href={`/medicamentos/unidades-medida/${unidad.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Editar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => toggleStatus(unidad.id)}>
                                                            <RefreshCw className="h-4 w-4 mr-2" />
                                                            {unidad.activo ? 'Desactivar' : 'Activar'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(unidad.id)}
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
                        {unidadesMedida.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((unidadesMedida.current_page - 1) * unidadesMedida.per_page) + 1} a{' '}
                                    {Math.min(unidadesMedida.current_page * unidadesMedida.per_page, unidadesMedida.total)} de{' '}
                                    {unidadesMedida.total} resultados
                                </div>
                                <div className="flex gap-2">
                                    {unidadesMedida.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/unidades-medida', {
                                                ...filters,
                                                page: unidadesMedida.current_page - 1
                                            })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {unidadesMedida.current_page < unidadesMedida.last_page && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/unidades-medida', {
                                                ...filters,
                                                page: unidadesMedida.current_page + 1
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
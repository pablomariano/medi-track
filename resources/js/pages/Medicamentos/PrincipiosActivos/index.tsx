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
    Eye, 
    Trash2,
    Filter,
    RefreshCw
} from 'lucide-react';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';

interface PrincipioActivo {
    id: number;
    nombre_generico: string;
    nombre_comercial?: string;
    clasificacion_atc?: string;
    grupo_farmacologico: string;
    descripcion?: string;
    activo: boolean;
    medicamentos_count?: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    principiosActivos: {
        data: PrincipioActivo[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    grupos: string[];
    filters: {
        search?: string;
        grupo_farmacologico?: string;
        activo?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function Index({ principiosActivos, grupos, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedGrupo, setSelectedGrupo] = useState(filters.grupo_farmacologico || '');
    const [selectedEstado, setSelectedEstado] = useState(filters.activo || '');

    const handleFilter = () => {
        router.get('/medicamentos/principios-activos', {
            search: search || undefined,
            grupo_farmacologico: selectedGrupo || undefined,
            activo: selectedEstado || undefined,
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        setSelectedGrupo('');
        setSelectedEstado('');
        router.get('/medicamentos/principios-activos');
    };

    const handleDelete = (id: number) => {
        if (confirm('¿Está seguro que desea eliminar este principio activo?')) {
            router.delete(`/medicamentos/principios-activos/${id}`);
        }
    };

    const toggleStatus = (id: number) => {
        router.post(`/medicamentos/principios-activos/${id}/toggle-status`);
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Principios Activos', href: '/medicamentos/principios-activos' }
            ]}
        >
            <Head title="Principios Activos" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Principios Activos</h1>
                        <p className="text-muted-foreground">
                            Gestión de principios activos farmacéuticos
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/medicamentos/principios-activos/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Nuevo Principio Activo
                        </Link>
                    </Button>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filtros
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar por nombre..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-10"
                                    onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                                />
                            </div>
                            
                            <Select value={selectedGrupo} onValueChange={setSelectedGrupo}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Grupo farmacológico" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos los grupos</SelectItem>
                                    {grupos.map((grupo) => (
                                        <SelectItem key={grupo} value={grupo}>
                                            {grupo}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedEstado} onValueChange={setSelectedEstado}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="1">Activos</SelectItem>
                                    <SelectItem value="0">Inactivos</SelectItem>
                                </SelectContent>
                            </Select>

                            <div className="flex gap-2">
                                <Button onClick={handleFilter} variant="default">
                                    <Search className="h-4 w-4 mr-2" />
                                    Filtrar
                                </Button>
                                <Button onClick={clearFilters} variant="outline">
                                    <RefreshCw className="h-4 w-4 mr-2" />
                                    Limpiar
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Resultados */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">
                            Resultados ({principiosActivos.total})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nombre Genérico</TableHead>
                                        <TableHead>Nombre Comercial</TableHead>
                                        <TableHead>Grupo Farmacológico</TableHead>
                                        <TableHead>Clasificación ATC</TableHead>
                                        <TableHead>Medicamentos</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead className="text-right">Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {principiosActivos.data.map((principio) => (
                                        <TableRow key={principio.id}>
                                            <TableCell className="font-medium">
                                                {principio.nombre_generico}
                                            </TableCell>
                                            <TableCell>
                                                {principio.nombre_comercial || '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    {principio.grupo_farmacologico}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {principio.clasificacion_atc || '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {principio.medicamentos_count || 0}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge 
                                                    variant={principio.activo ? "default" : "destructive"}
                                                    className={principio.activo ? "bg-green-100 text-green-800" : ""}
                                                >
                                                    {principio.activo ? 'Activo' : 'Inactivo'}
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
                                                            <Link href={`/medicamentos/principios-activos/${principio.id}`}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                Ver detalles
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/medicamentos/principios-activos/${principio.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Editar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => toggleStatus(principio.id)}>
                                                            <RefreshCw className="h-4 w-4 mr-2" />
                                                            {principio.activo ? 'Desactivar' : 'Activar'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(principio.id)}
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
                        {principiosActivos.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((principiosActivos.current_page - 1) * principiosActivos.per_page) + 1} a{' '}
                                    {Math.min(principiosActivos.current_page * principiosActivos.per_page, principiosActivos.total)} de{' '}
                                    {principiosActivos.total} resultados
                                </div>
                                <div className="flex gap-2">
                                    {principiosActivos.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/principios-activos', {
                                                ...filters,
                                                page: principiosActivos.current_page - 1
                                            })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {principiosActivos.current_page < principiosActivos.last_page && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos/principios-activos', {
                                                ...filters,
                                                page: principiosActivos.current_page + 1
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
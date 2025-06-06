import { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
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
    Search,
    Plus,
    Filter,
    X,
    Eye,
    Edit,
    Trash2,
    Play,
    Pause,
    CheckCircle,
    MoreHorizontal,
    User,
    Pill,
    Calendar,
    Activity,
    TrendingUp,
    Users,
    Clock
} from 'lucide-react';

interface Paciente {
    id: number;
    nombre: string;
}

interface Medico {
    id: number;
    nombre: string;
}

interface Medicamento {
    id: number;
    nombre_comercial: string;
    principio_activo: {
        nombre_generico: string;
    };
}

interface Tratamiento {
    id: number;
    nombre: string;
    diagnostico?: string;
    estado: string;
    fecha_inicio: string;
    fecha_fin_estimada?: string;
    paciente: Paciente;
    medico?: {
        user: {
            name: string;
        };
    };
    medicamentos: Medicamento[];
    created_at: string;
}

interface Stats {
    total: number;
    activos: number;
    pausados: number;
    completados: number;
}

interface Filters {
    search?: string;
    estado?: string;
    paciente_id?: string;
    medico_id?: string;
    fecha_desde?: string;
    fecha_hasta?: string;
}

interface Props {
    tratamientos: {
        data: Tratamiento[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    pacientes: Paciente[];
    medicos: Medico[];
    stats: Stats;
    filters: Filters;
}

export default function Index({ tratamientos, pacientes, medicos, stats, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedEstado, setSelectedEstado] = useState(filters.estado || '');
    const [selectedPaciente, setSelectedPaciente] = useState(filters.paciente_id || '');
    const [selectedMedico, setSelectedMedico] = useState(filters.medico_id || '');
    const [fechaDesde, setFechaDesde] = useState(filters.fecha_desde || '');
    const [fechaHasta, setFechaHasta] = useState(filters.fecha_hasta || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = () => {
        router.get(route('tratamientos.index'), {
            search: searchTerm,
            estado: selectedEstado,
            paciente_id: selectedPaciente,
            medico_id: selectedMedico,
            fecha_desde: fechaDesde,
            fecha_hasta: fechaHasta,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setSelectedEstado('');
        setSelectedPaciente('');
        setSelectedMedico('');
        setFechaDesde('');
        setFechaHasta('');
        router.get(route('tratamientos.index'));
    };

    const handleToggleStatus = (tratamiento: Tratamiento) => {
        router.post(route('tratamientos.toggle-status', tratamiento.id), {}, {
            preserveScroll: true,
        });
    };

    const handleCompletar = (tratamiento: Tratamiento) => {
        if (confirm('¿Está seguro de que desea completar este tratamiento?')) {
            router.post(route('tratamientos.completar', tratamiento.id), {}, {
                preserveScroll: true,
            });
        }
    };

    const handleDelete = (tratamiento: Tratamiento) => {
        if (confirm('¿Está seguro de que desea eliminar este tratamiento?')) {
            router.delete(route('tratamientos.destroy', tratamiento.id), {
                preserveScroll: true,
            });
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const getEstadoBadge = (estado: string) => {
        const variants = {
            'Activo': { variant: 'default' as const, className: 'bg-green-100 text-green-800' },
            'Pausado': { variant: 'secondary' as const, className: 'bg-yellow-100 text-yellow-800' },
            'Completado': { variant: 'outline' as const, className: 'bg-blue-100 text-blue-800' },
            'Suspendido': { variant: 'destructive' as const, className: '' },
            'Modificado': { variant: 'secondary' as const, className: 'bg-purple-100 text-purple-800' },
        };

        const config = variants[estado as keyof typeof variants] || variants['Activo'];
        
        return (
            <Badge variant={config.variant} className={config.className}>
                {estado}
            </Badge>
        );
    };

    const activeFiltersCount = [
        searchTerm, selectedEstado, selectedPaciente, selectedMedico, fechaDesde, fechaHasta
    ].filter(Boolean).length;

    useEffect(() => {
        const hasFilters = activeFiltersCount > 0;
        setShowFilters(hasFilters);
    }, [activeFiltersCount]);

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Tratamientos', href: '/tratamientos' }
            ]}
        >
            <Head title="Tratamientos" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Tratamientos</h1>
                        <p className="text-muted-foreground">
                            Sistema de prescripción y seguimiento médico
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={route('tratamientos.create')}>
                            <Plus className="h-4 w-4 mr-2" />
                            Nuevo Tratamiento
                        </Link>
                    </Button>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Tratamientos</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Activos</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.activos}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pausados</CardTitle>
                            <Clock className="h-4 w-4 text-yellow-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">{stats.pausados}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completados</CardTitle>
                            <CheckCircle className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{stats.completados}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-base">Filtros de Búsqueda</CardTitle>
                            <div className="flex items-center gap-2">
                                {activeFiltersCount > 0 && (
                                    <Badge variant="secondary">
                                        {activeFiltersCount} filtro{activeFiltersCount !== 1 ? 's' : ''} activo{activeFiltersCount !== 1 ? 's' : ''}
                                    </Badge>
                                )}
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setShowFilters(!showFilters)}
                                >
                                    <Filter className="h-4 w-4 mr-2" />
                                    {showFilters ? 'Ocultar' : 'Mostrar'} Filtros
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    {showFilters && (
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Buscar</label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Nombre, diagnóstico o paciente..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-10"
                                            onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Estado</label>
                                    <Select value={selectedEstado} onValueChange={setSelectedEstado}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Todos los estados" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Todos los estados</SelectItem>
                                            <SelectItem value="Activo">Activo</SelectItem>
                                            <SelectItem value="Pausado">Pausado</SelectItem>
                                            <SelectItem value="Completado">Completado</SelectItem>
                                            <SelectItem value="Suspendido">Suspendido</SelectItem>
                                            <SelectItem value="Modificado">Modificado</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Paciente</label>
                                    <Select value={selectedPaciente} onValueChange={setSelectedPaciente}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Todos los pacientes" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Todos los pacientes</SelectItem>
                                            {pacientes.map((paciente) => (
                                                <SelectItem key={paciente.id} value={paciente.id.toString()}>
                                                    {paciente.nombre}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Médico</label>
                                    <Select value={selectedMedico} onValueChange={setSelectedMedico}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Todos los médicos" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Todos los médicos</SelectItem>
                                            {medicos.map((medico) => (
                                                <SelectItem key={medico.id} value={medico.id.toString()}>
                                                    {medico.nombre}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Fecha Desde</label>
                                    <Input
                                        type="date"
                                        value={fechaDesde}
                                        onChange={(e) => setFechaDesde(e.target.value)}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Fecha Hasta</label>
                                    <Input
                                        type="date"
                                        value={fechaHasta}
                                        onChange={(e) => setFechaHasta(e.target.value)}
                                    />
                                </div>
                            </div>

                            <div className="flex gap-2">
                                <Button onClick={handleSearch}>
                                    <Search className="h-4 w-4 mr-2" />
                                    Aplicar Filtros
                                </Button>
                                <Button variant="outline" onClick={clearFilters}>
                                    <X className="h-4 w-4 mr-2" />
                                    Limpiar
                                </Button>
                            </div>
                        </CardContent>
                    )}
                </Card>

                {/* Tabla de Tratamientos */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Pill className="h-5 w-5" />
                            Tratamientos ({tratamientos.total})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Tratamiento</TableHead>
                                        <TableHead>Paciente</TableHead>
                                        <TableHead>Médico</TableHead>
                                        <TableHead>Medicamentos</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead>Fecha Inicio</TableHead>
                                        <TableHead className="text-right">Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {tratamientos.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center py-8">
                                                <div className="flex flex-col items-center gap-2">
                                                    <Pill className="h-8 w-8 text-muted-foreground" />
                                                    <p className="text-muted-foreground">
                                                        No se encontraron tratamientos
                                                    </p>
                                                    <Button variant="outline" asChild>
                                                        <Link href={route('tratamientos.create')}>
                                                            <Plus className="h-4 w-4 mr-2" />
                                                            Crear Primer Tratamiento
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        tratamientos.data.map((tratamiento) => (
                                            <TableRow key={tratamiento.id}>
                                                <TableCell>
                                                    <div>
                                                        <p className="font-medium">{tratamiento.nombre}</p>
                                                        {tratamiento.diagnostico && (
                                                            <p className="text-sm text-muted-foreground">
                                                                {tratamiento.diagnostico}
                                                            </p>
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-4 w-4 text-muted-foreground" />
                                                        <span>{tratamiento.paciente.nombre}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <span>
                                                        {tratamiento.medico?.user?.name || 'No asignado'}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        {tratamiento.medicamentos.slice(0, 2).map((medicamento, index) => (
                                                            <div key={medicamento.id} className="text-sm">
                                                                <span className="font-medium">{medicamento.nombre_comercial}</span>
                                                                <span className="text-muted-foreground ml-1">
                                                                    ({medicamento.principio_activo.nombre_generico})
                                                                </span>
                                                            </div>
                                                        ))}
                                                        {tratamiento.medicamentos.length > 2 && (
                                                            <p className="text-xs text-muted-foreground">
                                                                +{tratamiento.medicamentos.length - 2} más...
                                                            </p>
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {getEstadoBadge(tratamiento.estado)}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-2">
                                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                                        <span>{formatDate(tratamiento.fecha_inicio)}</span>
                                                    </div>
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
                                                                <Link href={route('tratamientos.show', tratamiento.id)}>
                                                                    <Eye className="h-4 w-4 mr-2" />
                                                                    Ver Detalles
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={route('tratamientos.edit', tratamiento.id)}>
                                                                    <Edit className="h-4 w-4 mr-2" />
                                                                    Editar
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem onClick={() => handleToggleStatus(tratamiento)}>
                                                                {tratamiento.estado === 'Activo' ? (
                                                                    <>
                                                                        <Pause className="h-4 w-4 mr-2" />
                                                                        Pausar
                                                                    </>
                                                                ) : (
                                                                    <>
                                                                        <Play className="h-4 w-4 mr-2" />
                                                                        Reactivar
                                                                    </>
                                                                )}
                                                            </DropdownMenuItem>
                                                            {(tratamiento.estado === 'Activo' || tratamiento.estado === 'Pausado') && (
                                                                <DropdownMenuItem onClick={() => handleCompletar(tratamiento)}>
                                                                    <CheckCircle className="h-4 w-4 mr-2" />
                                                                    Completar
                                                                </DropdownMenuItem>
                                                            )}
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem 
                                                                onClick={() => handleDelete(tratamiento)}
                                                                className="text-red-600"
                                                            >
                                                                <Trash2 className="h-4 w-4 mr-2" />
                                                                Eliminar
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Paginación */}
                        {tratamientos.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((tratamientos.current_page - 1) * tratamientos.per_page) + 1} a{' '}
                                    {Math.min(tratamientos.current_page * tratamientos.per_page, tratamientos.total)} de{' '}
                                    {tratamientos.total} resultados
                                </div>
                                <div className="flex space-x-2">
                                    {tratamientos.links.map((link, index) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? "default" : "outline"}
                                            size="sm"
                                            onClick={() => link.url && router.get(link.url)}
                                            disabled={!link.url}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 
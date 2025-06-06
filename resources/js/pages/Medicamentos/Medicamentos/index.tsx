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
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { 
    Plus, 
    Search, 
    MoreHorizontal, 
    Edit, 
    Eye,
    Trash2,
    RefreshCw,
    Filter,
    AlertTriangle,
    Package
} from 'lucide-react';

interface PrincipioActivo {
    id: number;
    nombre_generico: string;
    grupo_farmacologico: string;
}

interface FormaFarmaceutica {
    id: number;
    nombre: string;
}

interface ViaAdministracion {
    id: number;
    nombre: string;
}

interface UnidadMedida {
    id: number;
    nombre: string;
    simbolo: string;
}

interface Medicamento {
    id: number;
    nombre_comercial: string;
    codigo_barras?: string;
    concentracion: number;
    lote?: string;
    fecha_vencimiento?: string;
    precio_unitario?: number;
    stock_actual: number;
    stock_minimo: number;
    activo: boolean;
    principio_activo: PrincipioActivo;
    forma_farmaceutica: FormaFarmaceutica;
    via_administracion: ViaAdministracion;
    unidad_concentracion: UnidadMedida;
    created_at: string;
    updated_at: string;
}

interface Props {
    medicamentos: {
        data: Medicamento[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    principiosActivos: PrincipioActivo[];
    formasFarmaceuticas: FormaFarmaceutica[];
    viasAdministracion: ViaAdministracion[];
    filters: {
        search?: string;
        principio_activo_id?: string;
        forma_farmaceutica_id?: string;
        via_administracion_id?: string;
        stock_bajo?: string;
        vencidos?: string;
        activo?: string;
        sort_by?: string;
        sort_direction?: string;
    };
}

export default function Index({ 
    medicamentos, 
    principiosActivos, 
    formasFarmaceuticas, 
    viasAdministracion, 
    filters 
}: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedPrincipio, setSelectedPrincipio] = useState(filters.principio_activo_id || '');
    const [selectedForma, setSelectedForma] = useState(filters.forma_farmaceutica_id || '');
    const [selectedVia, setSelectedVia] = useState(filters.via_administracion_id || '');
    const [selectedStockBajo, setSelectedStockBajo] = useState(filters.stock_bajo || '');
    const [selectedVencidos, setSelectedVencidos] = useState(filters.vencidos || '');
    const [selectedEstado, setSelectedEstado] = useState(filters.activo || '');

    const handleFilter = () => {
        router.get('/medicamentos', {
            search: search || undefined,
            principio_activo_id: selectedPrincipio || undefined,
            forma_farmaceutica_id: selectedForma || undefined,
            via_administracion_id: selectedVia || undefined,
            stock_bajo: selectedStockBajo || undefined,
            vencidos: selectedVencidos || undefined,
            activo: selectedEstado || undefined,
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        setSelectedPrincipio('');
        setSelectedForma('');
        setSelectedVia('');
        setSelectedStockBajo('');
        setSelectedVencidos('');
        setSelectedEstado('');
        router.get('/medicamentos');
    };

    const handleDelete = (id: number) => {
        if (confirm('¿Está seguro que desea eliminar este medicamento?')) {
            router.delete(`/medicamentos/${id}`);
        }
    };

    const toggleStatus = (id: number) => {
        router.post(`/medicamentos/${id}/toggle-status`);
    };

    const isStockBajo = (medicamento: Medicamento) => {
        return medicamento.stock_actual <= medicamento.stock_minimo;
    };

    const isVencido = (medicamento: Medicamento) => {
        if (!medicamento.fecha_vencimiento) return false;
        return new Date(medicamento.fecha_vencimiento) < new Date();
    };

    const isProximoAVencer = (medicamento: Medicamento) => {
        if (!medicamento.fecha_vencimiento) return false;
        const fechaVencimiento = new Date(medicamento.fecha_vencimiento);
        const fechaLimite = new Date();
        fechaLimite.setMonth(fechaLimite.getMonth() + 3); // 3 meses
        return fechaVencimiento <= fechaLimite && fechaVencimiento >= new Date();
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '/medicamentos' }
            ]}
        >
            <Head title="Medicamentos" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Medicamentos</h1>
                        <p className="text-muted-foreground">
                            Gestión completa del inventario de medicamentos
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/medicamentos/inventario/alertas">
                                <AlertTriangle className="h-4 w-4 mr-2" />
                                Alertas
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href="/medicamentos/create">
                                <Plus className="h-4 w-4 mr-2" />
                                Nuevo Medicamento
                            </Link>
                        </Button>
                    </div>
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
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar medicamento..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-10"
                                    onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                                />
                            </div>
                            
                            <Select value={selectedPrincipio} onValueChange={setSelectedPrincipio}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Principio activo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos los principios</SelectItem>
                                    {principiosActivos.map((principio) => (
                                        <SelectItem key={principio.id} value={principio.id.toString()}>
                                            {principio.nombre_generico}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedForma} onValueChange={setSelectedForma}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Forma farmacéutica" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas las formas</SelectItem>
                                    {formasFarmaceuticas.map((forma) => (
                                        <SelectItem key={forma.id} value={forma.id.toString()}>
                                            {forma.nombre}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedVia} onValueChange={setSelectedVia}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Vía de administración" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas las vías</SelectItem>
                                    {viasAdministracion.map((via) => (
                                        <SelectItem key={via.id} value={via.id.toString()}>
                                            {via.nombre}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <Select value={selectedStockBajo} onValueChange={setSelectedStockBajo}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Stock" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todo el stock</SelectItem>
                                    <SelectItem value="1">Stock bajo</SelectItem>
                                    <SelectItem value="0">Stock normal</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={selectedVencidos} onValueChange={setSelectedVencidos}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Vencimiento" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem value="vencidos">Vencidos</SelectItem>
                                    <SelectItem value="proximo">Próximos a vencer</SelectItem>
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
                                <Button onClick={handleFilter} variant="default" className="flex-1">
                                    <Search className="h-4 w-4 mr-2" />
                                    Filtrar
                                </Button>
                                <Button onClick={clearFilters} variant="outline">
                                    <RefreshCw className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Resultados */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg flex items-center gap-2">
                            <Package className="h-5 w-5" />
                            Medicamentos ({medicamentos.total})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Medicamento</TableHead>
                                        <TableHead>Principio Activo</TableHead>
                                        <TableHead>Concentración</TableHead>
                                        <TableHead>Forma/Vía</TableHead>
                                        <TableHead>Stock</TableHead>
                                        <TableHead>Vencimiento</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead className="text-right">Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {medicamentos.data.map((medicamento) => (
                                        <TableRow key={medicamento.id}>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">{medicamento.nombre_comercial}</div>
                                                    {medicamento.lote && (
                                                        <div className="text-sm text-muted-foreground">
                                                            Lote: {medicamento.lote}
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">{medicamento.principio_activo.nombre_generico}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {medicamento.principio_activo.grupo_farmacologico}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {medicamento.concentracion} {medicamento.unidad_concentracion.simbolo}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-sm">
                                                    <div>{medicamento.forma_farmaceutica.nombre}</div>
                                                    <div className="text-muted-foreground">{medicamento.via_administracion.nombre}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Badge 
                                                        variant={isStockBajo(medicamento) ? "destructive" : "secondary"}
                                                    >
                                                        {medicamento.stock_actual}
                                                    </Badge>
                                                    {isStockBajo(medicamento) && (
                                                        <AlertTriangle className="h-4 w-4 text-red-500" />
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {medicamento.fecha_vencimiento ? (
                                                    <div className="flex items-center gap-2">
                                                        <Badge 
                                                            variant={
                                                                isVencido(medicamento) ? "destructive" : 
                                                                isProximoAVencer(medicamento) ? "destructive" : "outline"
                                                            }
                                                            className={
                                                                isProximoAVencer(medicamento) && !isVencido(medicamento) ? 
                                                                "bg-yellow-100 text-yellow-800" : ""
                                                            }
                                                        >
                                                            {new Date(medicamento.fecha_vencimiento).toLocaleDateString()}
                                                        </Badge>
                                                        {(isVencido(medicamento) || isProximoAVencer(medicamento)) && (
                                                            <AlertTriangle className="h-4 w-4 text-red-500" />
                                                        )}
                                                    </div>
                                                ) : '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge 
                                                    variant={medicamento.activo ? "default" : "destructive"}
                                                    className={medicamento.activo ? "bg-green-100 text-green-800" : ""}
                                                >
                                                    {medicamento.activo ? 'Activo' : 'Inactivo'}
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
                                                            <Link href={`/medicamentos/${medicamento.id}`}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                Ver detalles
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/medicamentos/${medicamento.id}/edit`}>
                                                                <Edit className="h-4 w-4 mr-2" />
                                                                Editar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => toggleStatus(medicamento.id)}>
                                                            <RefreshCw className="h-4 w-4 mr-2" />
                                                            {medicamento.activo ? 'Desactivar' : 'Activar'}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(medicamento.id)}
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
                        {medicamentos.last_page > 1 && (
                            <div className="flex items-center justify-between px-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Mostrando {((medicamentos.current_page - 1) * medicamentos.per_page) + 1} a{' '}
                                    {Math.min(medicamentos.current_page * medicamentos.per_page, medicamentos.total)} de{' '}
                                    {medicamentos.total} resultados
                                </div>
                                <div className="flex gap-2">
                                    {medicamentos.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos', {
                                                ...filters,
                                                page: medicamentos.current_page - 1
                                            })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {medicamentos.current_page < medicamentos.last_page && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get('/medicamentos', {
                                                ...filters,
                                                page: medicamentos.current_page + 1
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
import React, { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { 
  DropdownMenu, 
  DropdownMenuContent, 
  DropdownMenuTrigger,
  DropdownMenuCheckboxItem,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel
} from '@/components/ui/dropdown-menu';
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from '@/components/ui/select';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { 
  Search, 
  Filter, 
  Columns, 
  Download, 
  MoreHorizontal,
  ChevronLeft,
  ChevronRight,
  Eye,
  Edit,
  Trash2,
  Calendar,
  User,
  UserCheck,
  Heart,
  Clock,
  TrendingUp,
  ArrowUpDown,
  ArrowUp,
  ArrowDown
} from 'lucide-react';

// Tipos de datos
interface User {
  id: number;
  name: string;
  email: string;
}

interface Paciente {
  id: number;
  nombre: string;
  numero_documento: string | null;
  telefono_emergencia: string | null;
  activo: boolean;
}

interface Cuidador {
  usuario_id: number;
  user: User;
  experiencia_anos: number | null;
  tarifa_hora: number | null;
}

interface AsignacionCuidador {
  paciente_id: number;
  cuidador_usuario_id: number;
  fecha_asignacion: string;
  fecha_fin: string | null;
  activo: boolean;
  paciente: Paciente;
  cuidador: Cuidador;
  estado_calculado: 'vigente' | 'vencida' | 'finalizada';
  duracion_dias: number;
}

interface Estadisticas {
  total: number;
  activas: number;
  vigentes: number;
  finalizadas: number;
}

interface Props {
  asignaciones: {
    data: AsignacionCuidador[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filtros: {
    paciente_nombre?: string;
    cuidador_nombre?: string;
    estado?: string;
    fecha_desde?: string;
    fecha_hasta?: string;
    experiencia_min?: string;
  };
  estadisticas: Estadisticas;
  pacientes: Array<{id: number; nombre: string; numero_documento: string | null}>;
  cuidadores: Array<{usuario_id: number; nombre: string; email: string}>;
  sort: {
    column: string;
    direction: string;
  };
}

// Definición de columnas
const allColumns = [
  { key: 'paciente', label: 'Paciente', visible: true },
  { key: 'cuidador', label: 'Cuidador', visible: true },
  { key: 'fecha_asignacion', label: 'Fecha Asignación', visible: true },
  { key: 'fecha_fin', label: 'Fecha Fin', visible: true },
  { key: 'duracion', label: 'Duración', visible: true },
  { key: 'estado', label: 'Estado', visible: true },
  { key: 'experiencia', label: 'Experiencia', visible: false },
  { key: 'tarifa', label: 'Tarifa/Hora', visible: false },
  { key: 'telefono', label: 'Teléfono', visible: false },
  { key: 'acciones', label: 'Acciones', visible: true }
];

// Tipo para las columnas visibles
type VisibleColumns = {
  [key: string]: boolean;
};

function formatearFecha(fecha: string | null) {
  if (!fecha) return '-';
  return new Date(fecha).toLocaleDateString('es-CL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
}

function formatearDuracion(dias: number) {
  if (dias === 0) return 'Hoy';
  if (dias === 1) return '1 día';
  if (dias < 30) return `${dias} días`;
  if (dias < 365) return `${Math.floor(dias / 30)} meses`;
  return `${Math.floor(dias / 365)} años`;
}

function formatearTarifa(tarifa: number | null) {
  return tarifa ? `$${new Intl.NumberFormat('es-CL').format(tarifa)}` : 'No especificada';
}

export default function HistorialAsignaciones({ 
  asignaciones, 
  filtros, 
  estadisticas, 
  pacientes, 
  cuidadores,
  sort 
}: Props) {
  // Estados para filtros locales
  const [searchTerm, setSearchTerm] = useState(filtros.paciente_nombre || '');
  const [cuidadorTerm, setCuidadorTerm] = useState(filtros.cuidador_nombre || '');
  const [estadoFilter, setEstadoFilter] = useState(filtros.estado || 'todos');
  const [fechaDesde, setFechaDesde] = useState(filtros.fecha_desde || '');
  const [fechaHasta, setFechaHasta] = useState(filtros.fecha_hasta || '');
  const [experienciaMin, setExperienciaMin] = useState(filtros.experiencia_min || '');
  
  // Estados para tabla
  const [visibleColumns, setVisibleColumns] = useState<VisibleColumns>(
    allColumns.reduce((acc, col) => ({ ...acc, [col.key]: col.visible }), {} as VisibleColumns)
  );
  const [selectedRows, setSelectedRows] = useState<string[]>([]);

  const toggleColumnVisibility = (columnKey: string) => {
    setVisibleColumns(prev => ({
      ...prev,
      [columnKey]: !prev[columnKey]
    }));
  };

  const toggleRowSelection = (id: string) => {
    setSelectedRows(prev => 
      prev.includes(id) ? prev.filter(rowId => rowId !== id) : [...prev, id]
    );
  };

  const toggleAllRows = () => {
    if (selectedRows.length === asignaciones.data.length) {
      setSelectedRows([]);
    } else {
      setSelectedRows(asignaciones.data.map(a => `${a.paciente_id}-${a.cuidador_usuario_id}`));
    }
  };

  const handleSort = (column: string) => {
    const direction = sort.column === column && sort.direction === 'asc' ? 'desc' : 'asc';
    
    const params = new URLSearchParams(window.location.search);
    params.set('sort', column);
    params.set('direction', direction);
    
    router.get(window.location.pathname, Object.fromEntries(params));
  };

  const applyFilters = () => {
    const params: any = {};
    
    if (searchTerm) params.paciente_nombre = searchTerm;
    if (cuidadorTerm) params.cuidador_nombre = cuidadorTerm;
    if (estadoFilter && estadoFilter !== 'todos') params.estado = estadoFilter;
    if (fechaDesde) params.fecha_desde = fechaDesde;
    if (fechaHasta) params.fecha_hasta = fechaHasta;
    if (experienciaMin) params.experiencia_min = experienciaMin;

    router.get(route('asignaciones-cuidadores.historial'), params);
  };

  const clearFilters = () => {
    setSearchTerm('');
    setCuidadorTerm('');
    setEstadoFilter('todos');
    setFechaDesde('');
    setFechaHasta('');
    setExperienciaMin('');
    router.get(route('asignaciones-cuidadores.historial'));
  };

  const exportData = () => {
    console.log('Exportando datos...', selectedRows.length > 0 ? selectedRows : 'todos');
  };

  const getEstadoBadge = (estado: string) => {
    switch (estado) {
      case 'vigente':
        return <Badge variant="secondary" className="bg-green-100 text-green-800">Vigente</Badge>;
      case 'vencida':
        return <Badge variant="secondary" className="bg-orange-100 text-orange-800">Vencida</Badge>;
      case 'finalizada':
        return <Badge variant="secondary" className="bg-red-100 text-red-800">Finalizada</Badge>;
      default:
        return <Badge variant="outline">Desconocido</Badge>;
    }
  };

  const getSortIcon = (column: string) => {
    if (sort.column !== column) {
      return <ArrowUpDown className="h-4 w-4" />;
    }
    return sort.direction === 'asc' ? <ArrowUp className="h-4 w-4" /> : <ArrowDown className="h-4 w-4" />;
  };

  return (
    <AppSidebarLayout>
      <Head title="Historial de Asignaciones de Cuidadores" />
      
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Historial de Asignaciones</h1>
            <p className="text-muted-foreground mt-1">
              Registro completo de asignaciones entre pacientes y cuidadores
            </p>
          </div>
          <div className="flex items-center space-x-2">
            <Badge variant="outline" className="text-primary border-primary/20">
              {asignaciones.total} registros
            </Badge>
            {selectedRows.length > 0 && (
              <Badge variant="outline" className="text-primary border-primary/20">
                {selectedRows.length} seleccionados
              </Badge>
            )}
          </div>
        </div>

        {/* Estadísticas rápidas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardContent className="flex items-center p-6">
              <UserCheck className="h-8 w-8 text-blue-600" />
              <div className="ml-4">
                <p className="text-2xl font-bold">{estadisticas.total}</p>
                <p className="text-sm text-muted-foreground">Total Asignaciones</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="flex items-center p-6">
              <Heart className="h-8 w-8 text-green-600" />
              <div className="ml-4">
                <p className="text-2xl font-bold">{estadisticas.vigentes}</p>
                <p className="text-sm text-muted-foreground">Vigentes</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="flex items-center p-6">
              <Clock className="h-8 w-8 text-orange-600" />
              <div className="ml-4">
                <p className="text-2xl font-bold">{estadisticas.activas}</p>
                <p className="text-sm text-muted-foreground">Activas</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="flex items-center p-6">
              <TrendingUp className="h-8 w-8 text-purple-600" />
              <div className="ml-4">
                <p className="text-2xl font-bold">{estadisticas.finalizadas}</p>
                <p className="text-sm text-muted-foreground">Finalizadas</p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filtros */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <Filter className="h-5 w-5" />
              <span>Filtros Avanzados</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {/* Búsqueda por paciente */}
              <div className="space-y-2">
                <Label htmlFor="search-paciente">Buscar Paciente</Label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="search-paciente"
                    placeholder="Nombre o documento..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>

              {/* Búsqueda por cuidador */}
              <div className="space-y-2">
                <Label htmlFor="search-cuidador">Buscar Cuidador</Label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="search-cuidador"
                    placeholder="Nombre o email..."
                    value={cuidadorTerm}
                    onChange={(e) => setCuidadorTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>

              {/* Filtro por estado */}
              <div className="space-y-2">
                <Label>Estado</Label>
                <Select value={estadoFilter} onValueChange={setEstadoFilter}>
                  <SelectTrigger>
                    <SelectValue placeholder="Todos los estados" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="todos">Todos los estados</SelectItem>
                    <SelectItem value="vigente">Vigentes</SelectItem>
                    <SelectItem value="activo">Activas</SelectItem>
                    <SelectItem value="vencida">Vencidas</SelectItem>
                    <SelectItem value="finalizada">Finalizadas</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Fecha desde */}
              <div className="space-y-2">
                <Label htmlFor="fecha-desde">Fecha desde</Label>
                <Input
                  id="fecha-desde"
                  type="date"
                  value={fechaDesde}
                  onChange={(e) => setFechaDesde(e.target.value)}
                />
              </div>

              {/* Fecha hasta */}
              <div className="space-y-2">
                <Label htmlFor="fecha-hasta">Fecha hasta</Label>
                <Input
                  id="fecha-hasta"
                  type="date"
                  value={fechaHasta}
                  onChange={(e) => setFechaHasta(e.target.value)}
                />
              </div>

              {/* Experiencia mínima */}
              <div className="space-y-2">
                <Label htmlFor="experiencia-min">Experiencia mínima (años)</Label>
                <Input
                  id="experiencia-min"
                  type="number"
                  min="0"
                  placeholder="Ej: 2"
                  value={experienciaMin}
                  onChange={(e) => setExperienciaMin(e.target.value)}
                />
              </div>
            </div>

            {/* Botones de filtro */}
            <div className="flex items-center justify-between mt-4">
              <div className="flex items-center space-x-2">
                <Button onClick={applyFilters}>
                  Aplicar Filtros
                </Button>
                <Button variant="outline" onClick={clearFilters}>
                  Limpiar Filtros
                </Button>
              </div>
              <div className="text-sm text-muted-foreground">
                Mostrando {asignaciones.data.length} de {asignaciones.total} registros
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Tabla */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Registros de Asignaciones</CardTitle>
              <div className="flex items-center space-x-2">
                {/* Selector de columnas */}
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm">
                      <Columns className="h-4 w-4 mr-2" />
                      Columnas
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-48">
                    <DropdownMenuLabel>Mostrar columnas</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {allColumns.map((column) => (
                      <DropdownMenuCheckboxItem
                        key={column.key}
                        checked={visibleColumns[column.key]}
                        onCheckedChange={() => toggleColumnVisibility(column.key)}
                      >
                        {column.label}
                      </DropdownMenuCheckboxItem>
                    ))}
                  </DropdownMenuContent>
                </DropdownMenu>

                {/* Exportar */}
                <Button variant="outline" size="sm" onClick={exportData}>
                  <Download className="h-4 w-4 mr-2" />
                  Exportar
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12">
                      <Checkbox
                        checked={selectedRows.length === asignaciones.data.length && asignaciones.data.length > 0}
                        onCheckedChange={toggleAllRows}
                      />
                    </TableHead>
                    {visibleColumns.paciente && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('paciente')}
                      >
                        <div className="flex items-center space-x-1">
                          <User className="h-4 w-4" />
                          <span>Paciente</span>
                          {getSortIcon('paciente')}
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.cuidador && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('cuidador')}
                      >
                        <div className="flex items-center space-x-1">
                          <Heart className="h-4 w-4" />
                          <span>Cuidador</span>
                          {getSortIcon('cuidador')}
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.fecha_asignacion && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('fecha_asignacion')}
                      >
                        <div className="flex items-center space-x-1">
                          <Calendar className="h-4 w-4" />
                          <span>Fecha Asignación</span>
                          {getSortIcon('fecha_asignacion')}
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.fecha_fin && (
                      <TableHead>Fecha Fin</TableHead>
                    )}
                    {visibleColumns.duracion && (
                      <TableHead>Duración</TableHead>
                    )}
                    {visibleColumns.estado && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('activo')}
                      >
                        <div className="flex items-center space-x-1">
                          <span>Estado</span>
                          {getSortIcon('activo')}
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.experiencia && (
                      <TableHead>Experiencia</TableHead>
                    )}
                    {visibleColumns.tarifa && (
                      <TableHead>Tarifa/Hora</TableHead>
                    )}
                    {visibleColumns.telefono && (
                      <TableHead>Teléfono</TableHead>
                    )}
                    {visibleColumns.acciones && (
                      <TableHead className="w-20">Acciones</TableHead>
                    )}
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {asignaciones.data.map((asignacion) => (
                    <TableRow 
                      key={`${asignacion.paciente_id}-${asignacion.cuidador_usuario_id}`}
                      className={selectedRows.includes(`${asignacion.paciente_id}-${asignacion.cuidador_usuario_id}`) ? 'bg-muted/50' : ''}
                    >
                      <TableCell>
                        <Checkbox
                          checked={selectedRows.includes(`${asignacion.paciente_id}-${asignacion.cuidador_usuario_id}`)}
                          onCheckedChange={() => toggleRowSelection(`${asignacion.paciente_id}-${asignacion.cuidador_usuario_id}`)}
                        />
                      </TableCell>
                      {visibleColumns.paciente && (
                        <TableCell className="font-medium">
                          <div>
                            <div className="font-medium">{asignacion.paciente.nombre}</div>
                            {asignacion.paciente.numero_documento && (
                              <div className="text-sm text-muted-foreground">
                                Doc: {asignacion.paciente.numero_documento}
                              </div>
                            )}
                          </div>
                        </TableCell>
                      )}
                      {visibleColumns.cuidador && (
                        <TableCell>
                          <div>
                            <div className="font-medium">{asignacion.cuidador.user.name}</div>
                            <div className="text-sm text-muted-foreground">
                              {asignacion.cuidador.user.email}
                            </div>
                          </div>
                        </TableCell>
                      )}
                      {visibleColumns.fecha_asignacion && (
                        <TableCell>
                          {formatearFecha(asignacion.fecha_asignacion)}
                        </TableCell>
                      )}
                      {visibleColumns.fecha_fin && (
                        <TableCell>
                          {formatearFecha(asignacion.fecha_fin)}
                        </TableCell>
                      )}
                      {visibleColumns.duracion && (
                        <TableCell>
                          {formatearDuracion(asignacion.duracion_dias)}
                        </TableCell>
                      )}
                      {visibleColumns.estado && (
                        <TableCell>
                          {getEstadoBadge(asignacion.estado_calculado)}
                        </TableCell>
                      )}
                      {visibleColumns.experiencia && (
                        <TableCell>
                          {asignacion.cuidador.experiencia_anos ? 
                            `${asignacion.cuidador.experiencia_anos} años` : '-'}
                        </TableCell>
                      )}
                      {visibleColumns.tarifa && (
                        <TableCell>
                          {formatearTarifa(asignacion.cuidador.tarifa_hora)}
                        </TableCell>
                      )}
                      {visibleColumns.telefono && (
                        <TableCell>
                          {asignacion.paciente.telefono_emergencia || '-'}
                        </TableCell>
                      )}
                      {visibleColumns.acciones && (
                        <TableCell>
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem asChild>
                                <a href={route('asignaciones-cuidadores.show', [
                                  asignacion.paciente_id, 
                                  asignacion.cuidador_usuario_id
                                ])}>
                                  <Eye className="h-4 w-4 mr-2" />
                                  Ver detalles
                                </a>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <a href={route('asignaciones-cuidadores.edit', [
                                  asignacion.paciente_id, 
                                  asignacion.cuidador_usuario_id
                                ])}>
                                  <Edit className="h-4 w-4 mr-2" />
                                  Editar
                                </a>
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem className="text-red-600">
                                <Trash2 className="h-4 w-4 mr-2" />
                                Finalizar
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      )}
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>

            {/* Paginación */}
            <div className="flex items-center justify-between mt-4">
              <div className="flex items-center space-x-2">
                <span className="text-sm text-muted-foreground">Mostrar</span>
                <Select 
                  value={asignaciones.per_page.toString()} 
                  onValueChange={(value) => {
                    const params = new URLSearchParams(window.location.search);
                    params.set('per_page', value);
                    params.set('page', '1');
                    router.get(window.location.pathname, Object.fromEntries(params));
                  }}
                >
                  <SelectTrigger className="w-20">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="10">10</SelectItem>
                    <SelectItem value="20">20</SelectItem>
                    <SelectItem value="50">50</SelectItem>
                    <SelectItem value="100">100</SelectItem>
                  </SelectContent>
                </Select>
                <span className="text-sm text-muted-foreground">por página</span>
              </div>

              <div className="flex items-center space-x-2">
                <span className="text-sm text-muted-foreground">
                  Página {asignaciones.current_page} de {asignaciones.last_page} ({asignaciones.total} registros)
                </span>
                <div className="flex items-center space-x-1">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      const params = new URLSearchParams(window.location.search);
                      params.set('page', (asignaciones.current_page - 1).toString());
                      router.get(window.location.pathname, Object.fromEntries(params));
                    }}
                    disabled={asignaciones.current_page === 1}
                  >
                    <ChevronLeft className="h-4 w-4" />
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      const params = new URLSearchParams(window.location.search);
                      params.set('page', (asignaciones.current_page + 1).toString());
                      router.get(window.location.pathname, Object.fromEntries(params));
                    }}
                    disabled={asignaciones.current_page === asignaciones.last_page}
                  >
                    <ChevronRight className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppSidebarLayout>
  );
}

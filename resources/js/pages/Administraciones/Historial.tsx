import React, { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
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
import AppLayout from '@/layouts/app-layout';
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
  Pill
} from 'lucide-react';

// Tipos de datos
interface Administracion {
  id: number;
  fecha_hora_programada: string;
  fecha_hora_administrada: string | null;
  medicamento: { 
    id: number;
    nombre: string;
    concentracion?: string;
    unidad_concentracion?: string;
  };
  paciente: { 
    id: number;
    nombre: string; 
  };
  tratamiento: {
    id: number;
    nombre: string;
  };
  dosis_administrada: number;
  unidad_dosis: string;
  estado: 'Administrada' | 'Omitida' | 'Pendiente';
  observaciones: string | null;
  efectos_observados?: string | null;
  motivo_no_administracion?: string | null;
  administrado_por?: string;
}

// Mock de datos expandido
const mockAdministraciones: Administracion[] = [
  {
    id: 1,
    fecha_hora_programada: '2024-01-15T08:00:00',
    fecha_hora_administrada: '2024-01-15T08:05:00',
    medicamento: { id: 1, nombre: 'Paracetamol', concentracion: '500', unidad_concentracion: 'mg' },
    paciente: { id: 1, nombre: 'Juan Pérez' },
    tratamiento: { id: 1, nombre: 'Analgesia post-operatoria' },
    dosis_administrada: 500,
    unidad_dosis: 'mg',
    estado: 'Administrada',
    observaciones: 'Sin novedades',
    administrado_por: 'Dr. García'
  },
  {
    id: 2,
    fecha_hora_programada: '2024-01-15T12:00:00',
    fecha_hora_administrada: null,
    medicamento: { id: 2, nombre: 'Ibuprofeno', concentracion: '400', unidad_concentracion: 'mg' },
    paciente: { id: 2, nombre: 'Ana López' },
    tratamiento: { id: 2, nombre: 'Antiinflamatorio' },
    dosis_administrada: 400,
    unidad_dosis: 'mg',
    estado: 'Omitida',
    observaciones: 'Paciente dormía',
    motivo_no_administracion: 'Paciente en descanso'
  },
  {
    id: 3,
    fecha_hora_programada: '2024-01-15T16:00:00',
    fecha_hora_administrada: '2024-01-15T16:10:00',
    medicamento: { id: 3, nombre: 'Amoxicilina', concentracion: '250', unidad_concentracion: 'mg' },
    paciente: { id: 3, nombre: 'Carlos Ruiz' },
    tratamiento: { id: 3, nombre: 'Antibiótico' },
    dosis_administrada: 250,
    unidad_dosis: 'mg',
    estado: 'Administrada',
    observaciones: 'Paciente tolera bien',
    efectos_observados: 'Ninguno',
    administrado_por: 'Enf. Martínez'
  },
  {
    id: 4,
    fecha_hora_programada: '2024-01-16T09:00:00',
    fecha_hora_administrada: '2024-01-16T09:00:00',
    medicamento: { id: 4, nombre: 'Metformina', concentracion: '850', unidad_concentracion: 'mg' },
    paciente: { id: 4, nombre: 'María González' },
    tratamiento: { id: 4, nombre: 'Control diabetes' },
    dosis_administrada: 850,
    unidad_dosis: 'mg',
    estado: 'Administrada',
    observaciones: 'Control glucémico',
    administrado_por: 'Dr. Rodríguez'
  },
  {
    id: 5,
    fecha_hora_programada: '2024-01-16T14:00:00',
    fecha_hora_administrada: null,
    medicamento: { id: 5, nombre: 'Losartán', concentracion: '50', unidad_concentracion: 'mg' },
    paciente: { id: 5, nombre: 'Pedro Sánchez' },
    tratamiento: { id: 5, nombre: 'Antihipertensivo' },
    dosis_administrada: 50,
    unidad_dosis: 'mg',
    estado: 'Pendiente',
    observaciones: null
  }
];

// Definición de columnas
const allColumns = [
  { key: 'fecha_hora_programada', label: 'Fecha Programada', visible: true },
  { key: 'fecha_hora_administrada', label: 'Fecha Administrada', visible: true },
  { key: 'medicamento', label: 'Medicamento', visible: true },
  { key: 'paciente', label: 'Paciente', visible: true },
  { key: 'tratamiento', label: 'Tratamiento', visible: false },
  { key: 'dosis', label: 'Dosis', visible: true },
  { key: 'estado', label: 'Estado', visible: true },
  { key: 'observaciones', label: 'Observaciones', visible: true },
  { key: 'administrado_por', label: 'Administrado Por', visible: false },
  { key: 'acciones', label: 'Acciones', visible: true }
];

// Tipo para las columnas visibles
type VisibleColumns = {
  [key: string]: boolean;
};

function formatearFecha(fecha: string | null) {
  if (!fecha) return '-';
  return new Date(fecha).toLocaleString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit',
    year: '2-digit',
  });
}

function formatearFechaCorta(fecha: string | null) {
  if (!fecha) return '-';
  return new Date(fecha).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: '2-digit',
  });
}

export default function HistorialAdministraciones() {
  // Estados para filtros
  const [searchTerm, setSearchTerm] = useState('');
  const [estadoFilter, setEstadoFilter] = useState<string>('todos');
  const [fechaDesde, setFechaDesde] = useState('');
  const [fechaHasta, setFechaHasta] = useState('');
  const [medicamentoFilter, setMedicamentoFilter] = useState('');
  
  // Estados para tabla
  const [visibleColumns, setVisibleColumns] = useState<VisibleColumns>(
    allColumns.reduce((acc, col) => ({ ...acc, [col.key]: col.visible }), {} as VisibleColumns)
  );
  const [selectedRows, setSelectedRows] = useState<number[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [sortColumn, setSortColumn] = useState<string>('fecha_hora_programada');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('desc');

  // Datos filtrados y paginados
  const filteredData = useMemo(() => {
    let filtered = mockAdministraciones;

    // Filtro por búsqueda
    if (searchTerm) {
      filtered = filtered.filter(item =>
        item.medicamento.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.paciente.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.tratamiento.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (item.observaciones && item.observaciones.toLowerCase().includes(searchTerm.toLowerCase()))
      );
    }

    // Filtro por estado
    if (estadoFilter !== 'todos') {
      filtered = filtered.filter(item => item.estado === estadoFilter);
    }

    // Filtro por medicamento
    if (medicamentoFilter) {
      filtered = filtered.filter(item => 
        item.medicamento.nombre.toLowerCase().includes(medicamentoFilter.toLowerCase())
      );
    }

    // Filtro por fechas
    if (fechaDesde) {
      filtered = filtered.filter(item => 
        new Date(item.fecha_hora_programada) >= new Date(fechaDesde)
      );
    }
    if (fechaHasta) {
      filtered = filtered.filter(item => 
        new Date(item.fecha_hora_programada) <= new Date(fechaHasta + 'T23:59:59')
      );
    }

    // Ordenamiento
    filtered.sort((a, b) => {
      let aValue: any, bValue: any;
      
      switch (sortColumn) {
        case 'fecha_hora_programada':
          aValue = new Date(a.fecha_hora_programada);
          bValue = new Date(b.fecha_hora_programada);
          break;
        case 'medicamento':
          aValue = a.medicamento.nombre;
          bValue = b.medicamento.nombre;
          break;
        case 'paciente':
          aValue = a.paciente.nombre;
          bValue = b.paciente.nombre;
          break;
        case 'estado':
          aValue = a.estado;
          bValue = b.estado;
          break;
        default:
          aValue = a[sortColumn as keyof Administracion];
          bValue = b[sortColumn as keyof Administracion];
      }

      if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
      if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
      return 0;
    });

    return filtered;
  }, [searchTerm, estadoFilter, medicamentoFilter, fechaDesde, fechaHasta, sortColumn, sortDirection]);

  // Paginación
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  const paginatedData = filteredData.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  // Funciones de utilidad
  const toggleColumnVisibility = (columnKey: string) => {
    setVisibleColumns(prev => ({
      ...prev,
      [columnKey]: !prev[columnKey]
    }));
  };

  const toggleRowSelection = (id: number) => {
    setSelectedRows(prev =>
      prev.includes(id) ? prev.filter(rowId => rowId !== id) : [...prev, id]
    );
  };

  const toggleAllRows = () => {
    setSelectedRows(
      selectedRows.length === paginatedData.length 
        ? [] 
        : paginatedData.map(item => item.id)
    );
  };

  const handleSort = (column: string) => {
    if (sortColumn === column) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortColumn(column);
      setSortDirection('asc');
    }
  };

  const clearFilters = () => {
    setSearchTerm('');
    setEstadoFilter('todos');
    setFechaDesde('');
    setFechaHasta('');
    setMedicamentoFilter('');
  };

  const exportData = () => {
    // Implementar exportación
    console.log('Exportando datos...', selectedRows.length > 0 ? selectedRows : 'todos');
  };

  return (
    <AppLayout>
      <Head title="Historial de Administraciones" />
      
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Historial de Administraciones</h1>
            <p className="text-muted-foreground mt-1">
              Registro completo de administraciones de medicamentos
            </p>
          </div>
          <div className="flex items-center space-x-2">
            <Badge variant="outline" className="text-primary border-primary/20">
              {filteredData.length} registros
            </Badge>
            {selectedRows.length > 0 && (
              <Badge variant="outline" className="text-primary border-primary/20">
                {selectedRows.length} seleccionados
              </Badge>
            )}
          </div>
        </div>

        {/* Filtros */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <Filter className="h-5 w-5" />
              <span>Filtros</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {/* Búsqueda general */}
              <div className="space-y-2">
                <Label htmlFor="search">Búsqueda general</Label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="search"
                    placeholder="Buscar medicamento, paciente..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
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
                    <SelectItem value="Administrada">Administrada</SelectItem>
                    <SelectItem value="Omitida">Omitida</SelectItem>
                    <SelectItem value="Pendiente">Pendiente</SelectItem>
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
            </div>

            {/* Botones de filtro */}
            <div className="flex items-center justify-between mt-4">
              <Button variant="outline" onClick={clearFilters}>
                Limpiar filtros
              </Button>
              <div className="text-sm text-muted-foreground">
                Mostrando {filteredData.length} de {mockAdministraciones.length} registros
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Tabla */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Registros de Administraciones</CardTitle>
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
                        checked={selectedRows.length === paginatedData.length && paginatedData.length > 0}
                        onCheckedChange={toggleAllRows}
                      />
                    </TableHead>
                    {visibleColumns.fecha_hora_programada && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('fecha_hora_programada')}
                      >
                        <div className="flex items-center space-x-1">
                          <Calendar className="h-4 w-4" />
                          <span>Fecha Programada</span>
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.fecha_hora_administrada && (
                      <TableHead>Fecha Administrada</TableHead>
                    )}
                    {visibleColumns.medicamento && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('medicamento')}
                      >
                        <div className="flex items-center space-x-1">
                          <Pill className="h-4 w-4" />
                          <span>Medicamento</span>
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.paciente && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('paciente')}
                      >
                        <div className="flex items-center space-x-1">
                          <User className="h-4 w-4" />
                          <span>Paciente</span>
                        </div>
                      </TableHead>
                    )}
                    {visibleColumns.tratamiento && (
                      <TableHead>Tratamiento</TableHead>
                    )}
                    {visibleColumns.dosis && (
                      <TableHead>Dosis</TableHead>
                    )}
                    {visibleColumns.estado && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('estado')}
                      >
                        Estado
                      </TableHead>
                    )}
                    {visibleColumns.observaciones && (
                      <TableHead>Observaciones</TableHead>
                    )}
                    {visibleColumns.administrado_por && (
                      <TableHead>Administrado Por</TableHead>
                    )}
                    {visibleColumns.acciones && (
                      <TableHead className="w-20">Acciones</TableHead>
                    )}
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {paginatedData.map((admin) => (
                    <TableRow 
                      key={admin.id}
                      className={selectedRows.includes(admin.id) ? 'bg-muted/50' : ''}
                    >
                      <TableCell>
                        <Checkbox
                          checked={selectedRows.includes(admin.id)}
                          onCheckedChange={() => toggleRowSelection(admin.id)}
                        />
                      </TableCell>
                      {visibleColumns.fecha_hora_programada && (
                        <TableCell className="font-medium">
                          {formatearFecha(admin.fecha_hora_programada)}
                        </TableCell>
                      )}
                      {visibleColumns.fecha_hora_administrada && (
                        <TableCell>
                          {formatearFecha(admin.fecha_hora_administrada)}
                        </TableCell>
                      )}
                      {visibleColumns.medicamento && (
                        <TableCell>
                          <div>
                            <div className="font-medium">{admin.medicamento.nombre}</div>
                            {admin.medicamento.concentracion && (
                              <div className="text-sm text-muted-foreground">
                                {admin.medicamento.concentracion} {admin.medicamento.unidad_concentracion}
                              </div>
                            )}
                          </div>
                        </TableCell>
                      )}
                      {visibleColumns.paciente && (
                        <TableCell>{admin.paciente.nombre}</TableCell>
                      )}
                      {visibleColumns.tratamiento && (
                        <TableCell>
                          <div className="text-sm">{admin.tratamiento.nombre}</div>
                        </TableCell>
                      )}
                      {visibleColumns.dosis && (
                        <TableCell>
                          {admin.dosis_administrada} {admin.unidad_dosis}
                        </TableCell>
                      )}
                      {visibleColumns.estado && (
                        <TableCell>
                          <Badge 
                            variant={
                              admin.estado === 'Administrada' ? 'default' : 
                              admin.estado === 'Omitida' ? 'destructive' : 
                              'secondary'
                            }
                          >
                            {admin.estado}
                          </Badge>
                        </TableCell>
                      )}
                      {visibleColumns.observaciones && (
                        <TableCell>
                          <div className="w-full max-w-[200px] truncate" title={admin.observaciones || ''}>
                            {admin.observaciones || '-'}
                          </div>
                        </TableCell>
                      )}
                      {visibleColumns.administrado_por && (
                        <TableCell>{admin.administrado_por || '-'}</TableCell>
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
                              <DropdownMenuItem>
                                <Eye className="h-4 w-4 mr-2" />
                                Ver detalles
                              </DropdownMenuItem>
                              <DropdownMenuItem>
                                <Edit className="h-4 w-4 mr-2" />
                                Editar
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem className="text-red-600">
                                <Trash2 className="h-4 w-4 mr-2" />
                                Eliminar
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
                  value={itemsPerPage.toString()} 
                  onValueChange={(value) => {
                    setItemsPerPage(Number(value));
                    setCurrentPage(1);
                  }}
                >
                  <SelectTrigger className="w-20">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="5">5</SelectItem>
                    <SelectItem value="10">10</SelectItem>
                    <SelectItem value="20">20</SelectItem>
                    <SelectItem value="50">50</SelectItem>
                  </SelectContent>
                </Select>
                <span className="text-sm text-muted-foreground">por página</span>
              </div>

              <div className="flex items-center space-x-2">
                <span className="text-sm text-muted-foreground">
                  Página {currentPage} de {totalPages} ({filteredData.length} registros)
                </span>
                <div className="flex items-center space-x-1">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
                    disabled={currentPage === 1}
                  >
                    <ChevronLeft className="h-4 w-4" />
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(Math.min(totalPages, currentPage + 1))}
                    disabled={currentPage === totalPages}
                  >
                    <ChevronRight className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
} 
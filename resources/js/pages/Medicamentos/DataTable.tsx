import React, { useState, useMemo } from 'react';
import { Head, Link, router } from '@inertiajs/react';
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
  Pill, 
  Search, 
  Filter, 
  Settings2, 
  ChevronUp, 
  ChevronDown, 
  MoreHorizontal,
  Eye,
  Edit,
  Trash2,
  Plus,
  Download,
  RefreshCw,
  AlertTriangle,
  CheckCircle,
  X
} from 'lucide-react';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo: string;
  concentracion: string;
  unidad_concentracion: string;
  forma_farmaceutica: string;
  via_administracion: string;
  presentacion: string;
  unidades_por_presentacion: number;
  requiere_receta: boolean;
  categoria_terapeutica: string;
  laboratorio: string;
  codigo_barras: string;
  registro_sanitario: string;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

interface Props {
  medicamentos: {
    data: Medicamento[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
}

// Definición de columnas
const allColumns = [
  { key: 'nombre', label: 'Nombre', visible: true },
  { key: 'principio_activo', label: 'Principio Activo', visible: true },
  { key: 'concentracion', label: 'Concentración', visible: true },
  { key: 'forma_farmaceutica', label: 'Forma Farmacéutica', visible: false },
  { key: 'via_administracion', label: 'Vía Administración', visible: false },
  { key: 'presentacion', label: 'Presentación', visible: false },
  { key: 'categoria_terapeutica', label: 'Categoría', visible: true },
  { key: 'laboratorio', label: 'Laboratorio', visible: false },
  { key: 'requiere_receta', label: 'Receta', visible: true },
  { key: 'activo', label: 'Estado', visible: true },
  { key: 'acciones', label: 'Acciones', visible: true }
];

// Tipo para las columnas visibles
type VisibleColumns = {
  [K in typeof allColumns[number]['key']]: boolean;
};

export default function DataTable({ medicamentos }: Props) {
  // Estados para filtros
  const [searchTerm, setSearchTerm] = useState('');
  const [estadoFilter, setEstadoFilter] = useState('todos');
  const [recetaFilter, setRecetaFilter] = useState('todos');
  const [categoriaFilter, setCategoriaFilter] = useState('todos');
  
  // Estados para tabla
  const [selectedRows, setSelectedRows] = useState<number[]>([]);
  const [sortColumn, setSortColumn] = useState<string>('nombre');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
  const [currentPage, setCurrentPage] = useState(medicamentos.current_page);
  const [itemsPerPage, setItemsPerPage] = useState(medicamentos.per_page);
  
  // Estado para columnas visibles
  const [visibleColumns, setVisibleColumns] = useState<VisibleColumns>(
    allColumns.reduce((acc, col) => ({
      ...acc,
      [col.key]: col.visible
    }), {} as VisibleColumns)
  );

  // Obtener categorías únicas para el filtro
  const categorias = useMemo(() => {
    const cats = medicamentos.data
      .map(m => m.categoria_terapeutica)
      .filter(Boolean)
      .filter((cat, index, arr) => arr.indexOf(cat) === index);
    return cats.sort();
  }, [medicamentos.data]);

  // Filtrar datos
  const filteredData = useMemo(() => {
    return medicamentos.data.filter(medicamento => {
      const matchesSearch = searchTerm === '' || 
        medicamento.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
        medicamento.principio_activo.toLowerCase().includes(searchTerm.toLowerCase()) ||
        medicamento.laboratorio?.toLowerCase().includes(searchTerm.toLowerCase());
      
      const matchesEstado = estadoFilter === 'todos' || 
        (estadoFilter === 'activo' && medicamento.activo) ||
        (estadoFilter === 'inactivo' && !medicamento.activo);
      
      const matchesReceta = recetaFilter === 'todos' ||
        (recetaFilter === 'si' && medicamento.requiere_receta) ||
        (recetaFilter === 'no' && !medicamento.requiere_receta);
      
      const matchesCategoria = categoriaFilter === 'todos' ||
        medicamento.categoria_terapeutica === categoriaFilter;
      
      return matchesSearch && matchesEstado && matchesReceta && matchesCategoria;
    });
  }, [medicamentos.data, searchTerm, estadoFilter, recetaFilter, categoriaFilter]);

  // Ordenar datos
  const sortedData = useMemo(() => {
    return [...filteredData].sort((a, b) => {
      const aValue = a[sortColumn as keyof Medicamento];
      const bValue = b[sortColumn as keyof Medicamento];
      
      if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
      if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
      return 0;
    });
  }, [filteredData, sortColumn, sortDirection]);

  // Paginación
  const paginatedData = useMemo(() => {
    const startIndex = (currentPage - 1) * itemsPerPage;
    return sortedData.slice(startIndex, startIndex + itemsPerPage);
  }, [sortedData, currentPage, itemsPerPage]);

  const totalPages = Math.ceil(sortedData.length / itemsPerPage);

  // Funciones de manejo
  const handleSort = (column: string) => {
    if (sortColumn === column) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortColumn(column);
      setSortDirection('asc');
    }
  };

  const handleSelectRow = (id: number) => {
    setSelectedRows(prev => 
      prev.includes(id) 
        ? prev.filter(rowId => rowId !== id)
        : [...prev, id]
    );
  };

  const handleSelectAll = () => {
    if (selectedRows.length === paginatedData.length) {
      setSelectedRows([]);
    } else {
      setSelectedRows(paginatedData.map(item => item.id));
    }
  };

  const clearFilters = () => {
    setSearchTerm('');
    setEstadoFilter('todos');
    setRecetaFilter('todos');
    setCategoriaFilter('todos');
  };

  const handleDelete = (id: number) => {
    if (confirm('¿Estás seguro de que deseas desactivar este medicamento?')) {
      router.delete(route('medicamentos.destroy', id));
    }
  };

  const handleBulkDelete = () => {
    if (selectedRows.length === 0) return;
    
    if (confirm(`¿Estás seguro de que deseas desactivar ${selectedRows.length} medicamentos?`)) {
      // Aquí implementarías la eliminación masiva
      console.log('Eliminar medicamentos:', selectedRows);
    }
  };

  const exportData = () => {
    // Implementar exportación
    console.log('Exportar datos filtrados');
  };

  return (
    <AppLayout>
      <Head title="Medicamentos - Data Table" />

      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Medicamentos</h1>
            <p className="text-muted-foreground mt-1">
              Gestión completa del inventario de medicamentos
            </p>
          </div>
          <div className="flex items-center space-x-2">
            <Badge variant="outline" className="text-primary border-primary/20">
              {filteredData.length} medicamentos
            </Badge>
            {selectedRows.length > 0 && (
              <Badge variant="outline" className="text-primary border-primary/20">
                {selectedRows.length} seleccionados
              </Badge>
            )}
            <Link href={route('medicamentos.create')}>
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Nuevo Medicamento
              </Button>
            </Link>
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
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                  <Input
                    id="search"
                    placeholder="Nombre, principio activo, laboratorio..."
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
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="todos">Todos</SelectItem>
                    <SelectItem value="activo">Activos</SelectItem>
                    <SelectItem value="inactivo">Inactivos</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Filtro por receta */}
              <div className="space-y-2">
                <Label>Requiere Receta</Label>
                <Select value={recetaFilter} onValueChange={setRecetaFilter}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="todos">Todos</SelectItem>
                    <SelectItem value="si">Sí</SelectItem>
                    <SelectItem value="no">No</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Filtro por categoría */}
              <div className="space-y-2">
                <Label>Categoría Terapéutica</Label>
                <Select value={categoriaFilter} onValueChange={setCategoriaFilter}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="todos">Todas</SelectItem>
                    {categorias.map(categoria => (
                      <SelectItem key={categoria} value={categoria}>
                        {categoria}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="flex items-center justify-between mt-4">
              <Button variant="outline" onClick={clearFilters}>
                <X className="h-4 w-4 mr-2" />
                Limpiar Filtros
              </Button>
              
              <div className="flex items-center space-x-2">
                {selectedRows.length > 0 && (
                  <>
                    <Button variant="destructive" size="sm" onClick={handleBulkDelete}>
                      <Trash2 className="h-4 w-4 mr-2" />
                      Desactivar Seleccionados
                    </Button>
                    <DropdownMenuSeparator />
                  </>
                )}
                
                <Button variant="outline" size="sm" onClick={exportData}>
                  <Download className="h-4 w-4 mr-2" />
                  Exportar
                </Button>
                
                <Button variant="outline" size="sm" onClick={() => window.location.reload()}>
                  <RefreshCw className="h-4 w-4 mr-2" />
                  Actualizar
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Data Table */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Lista de Medicamentos</CardTitle>
                <CardDescription>
                  {filteredData.length} de {medicamentos.total} medicamentos
                </CardDescription>
              </div>
              
              {/* Selector de columnas */}
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="outline" size="sm">
                    <Settings2 className="h-4 w-4 mr-2" />
                    Columnas
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-48">
                  <DropdownMenuLabel>Mostrar columnas</DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  {allColumns.map(column => (
                    <DropdownMenuCheckboxItem
                      key={column.key}
                      checked={visibleColumns[column.key as keyof VisibleColumns]}
                      onCheckedChange={(checked) => 
                        setVisibleColumns(prev => ({
                          ...prev,
                          [column.key]: checked
                        }))
                      }
                    >
                      {column.label}
                    </DropdownMenuCheckboxItem>
                  ))}
                </DropdownMenuContent>
              </DropdownMenu>
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
                        onCheckedChange={handleSelectAll}
                      />
                    </TableHead>
                    
                    {visibleColumns.nombre && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('nombre')}
                      >
                        <div className="flex items-center space-x-1">
                          <Pill className="h-4 w-4" />
                          <span>Nombre</span>
                          {sortColumn === 'nombre' && (
                            sortDirection === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />
                          )}
                        </div>
                      </TableHead>
                    )}
                    
                    {visibleColumns.principio_activo && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('principio_activo')}
                      >
                        <div className="flex items-center space-x-1">
                          <span>Principio Activo</span>
                          {sortColumn === 'principio_activo' && (
                            sortDirection === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />
                          )}
                        </div>
                      </TableHead>
                    )}
                    
                    {visibleColumns.concentracion && (
                      <TableHead>Concentración</TableHead>
                    )}
                    
                    {visibleColumns.forma_farmaceutica && (
                      <TableHead>Forma Farmacéutica</TableHead>
                    )}
                    
                    {visibleColumns.via_administracion && (
                      <TableHead>Vía Administración</TableHead>
                    )}
                    
                    {visibleColumns.presentacion && (
                      <TableHead>Presentación</TableHead>
                    )}
                    
                    {visibleColumns.categoria_terapeutica && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('categoria_terapeutica')}
                      >
                        <div className="flex items-center space-x-1">
                          <span>Categoría</span>
                          {sortColumn === 'categoria_terapeutica' && (
                            sortDirection === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />
                          )}
                        </div>
                      </TableHead>
                    )}
                    
                    {visibleColumns.laboratorio && (
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('laboratorio')}
                      >
                        <div className="flex items-center space-x-1">
                          <span>Laboratorio</span>
                          {sortColumn === 'laboratorio' && (
                            sortDirection === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />
                          )}
                        </div>
                      </TableHead>
                    )}
                    
                    {visibleColumns.requiere_receta && (
                      <TableHead>Receta</TableHead>
                    )}
                    
                    {visibleColumns.activo && (
                      <TableHead>Estado</TableHead>
                    )}
                    
                    {visibleColumns.acciones && (
                      <TableHead className="w-24">Acciones</TableHead>
                    )}
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {paginatedData.map((medicamento) => (
                    <TableRow 
                      key={medicamento.id}
                      className={selectedRows.includes(medicamento.id) ? 'bg-muted/50' : ''}
                    >
                      <TableCell>
                        <Checkbox
                          checked={selectedRows.includes(medicamento.id)}
                          onCheckedChange={() => handleSelectRow(medicamento.id)}
                        />
                      </TableCell>
                      
                      {visibleColumns.nombre && (
                        <TableCell className="font-medium">
                          <div>
                            <div className="font-semibold">{medicamento.nombre}</div>
                            {medicamento.codigo_barras && (
                              <div className="text-xs text-muted-foreground">
                                Código: {medicamento.codigo_barras}
                              </div>
                            )}
                          </div>
                        </TableCell>
                      )}
                      
                      {visibleColumns.principio_activo && (
                        <TableCell>{medicamento.principio_activo}</TableCell>
                      )}
                      
                      {visibleColumns.concentracion && (
                        <TableCell>
                          {medicamento.concentracion} {medicamento.unidad_concentracion}
                        </TableCell>
                      )}
                      
                      {visibleColumns.forma_farmaceutica && (
                        <TableCell>{medicamento.forma_farmaceutica}</TableCell>
                      )}
                      
                      {visibleColumns.via_administracion && (
                        <TableCell>{medicamento.via_administracion}</TableCell>
                      )}
                      
                      {visibleColumns.presentacion && (
                        <TableCell>
                          {medicamento.presentacion}
                          {medicamento.unidades_por_presentacion && (
                            <span className="text-muted-foreground">
                              {' '}({medicamento.unidades_por_presentacion} unidades)
                            </span>
                          )}
                        </TableCell>
                      )}
                      
                      {visibleColumns.categoria_terapeutica && (
                        <TableCell>
                          {medicamento.categoria_terapeutica && (
                            <Badge variant="outline">
                              {medicamento.categoria_terapeutica}
                            </Badge>
                          )}
                        </TableCell>
                      )}
                      
                      {visibleColumns.laboratorio && (
                        <TableCell>{medicamento.laboratorio}</TableCell>
                      )}
                      
                      {visibleColumns.requiere_receta && (
                        <TableCell>
                          {medicamento.requiere_receta ? (
                            <Badge variant="outline" className="text-orange-600 border-orange-200">
                              <AlertTriangle className="h-3 w-3 mr-1" />
                              Sí
                            </Badge>
                          ) : (
                            <Badge variant="outline" className="text-green-600 border-green-200">
                              <CheckCircle className="h-3 w-3 mr-1" />
                              No
                            </Badge>
                          )}
                        </TableCell>
                      )}
                      
                      {visibleColumns.activo && (
                        <TableCell>
                          <Badge variant={medicamento.activo ? "default" : "secondary"}>
                            {medicamento.activo ? "Activo" : "Inactivo"}
                          </Badge>
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
                                <Link href={route('medicamentos.show', medicamento.id)}>
                                  <Eye className="h-4 w-4 mr-2" />
                                  Ver detalles
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={route('medicamentos.edit', medicamento.id)}>
                                  <Edit className="h-4 w-4 mr-2" />
                                  Editar
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem 
                                onClick={() => handleDelete(medicamento.id)}
                                className="text-destructive"
                              >
                                <Trash2 className="h-4 w-4 mr-2" />
                                Desactivar
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
                <Label>Elementos por página:</Label>
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
              </div>

              <div className="flex items-center space-x-2">
                <span className="text-sm text-muted-foreground">
                  Mostrando {((currentPage - 1) * itemsPerPage) + 1} a {Math.min(currentPage * itemsPerPage, sortedData.length)} de {sortedData.length} resultados
                </span>
                
                <div className="flex items-center space-x-1">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                    disabled={currentPage === 1}
                  >
                    Anterior
                  </Button>
                  
                  <span className="text-sm px-2">
                    Página {currentPage} de {totalPages}
                  </span>
                  
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                    disabled={currentPage === totalPages}
                  >
                    Siguiente
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
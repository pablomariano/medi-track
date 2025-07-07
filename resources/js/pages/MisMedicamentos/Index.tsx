import React, { useState, useMemo } from 'react';
import { Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pill, Search, Plus, Calendar, Activity, User, ArrowRight, Filter } from 'lucide-react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo?: string;
  presentacion?: string;
  concentracion?: string;
  via_administracion?: string;
  categoria?: string;
  laboratorio?: string;
  descripcion?: string;
  indicaciones?: string;
  contraindicaciones?: string;
  efectos_secundarios?: string;
  precio?: number;
  disponible?: boolean;
  requiere_receta?: boolean;
  tratamientos_activos?: number;
}

interface Props {
  medicamentos?: Medicamento[];
  categorias?: string[];
  estadisticas?: {
    total_medicamentos: number;
    en_tratamiento: number;
    disponibles: number;
  };
}

export default function MisMedicamentos({ 
  medicamentos = [], 
  categorias = [],
  estadisticas 
}: Props) {
  const auth = useAuth();
  const [busqueda, setBusqueda] = useState('');
  const [categoriaFiltro, setCategoriaFiltro] = useState('todas');
  const [tipoFiltro, setTipoFiltro] = useState('todos');

  // Filtrar medicamentos según búsqueda y filtros
  const medicamentosFiltrados = useMemo(() => {
    return medicamentos.filter(medicamento => {
      const coincideBusqueda = 
        medicamento.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
        medicamento.principio_activo?.toLowerCase().includes(busqueda.toLowerCase()) ||
        medicamento.laboratorio?.toLowerCase().includes(busqueda.toLowerCase());

      const coincideCategoria = 
        categoriaFiltro === 'todas' || medicamento.categoria === categoriaFiltro;

      const coincideTipo = 
        tipoFiltro === 'todos' || 
        (tipoFiltro === 'en_tratamiento' && medicamento.tratamientos_activos && medicamento.tratamientos_activos > 0) ||
        (tipoFiltro === 'disponibles' && medicamento.disponible) ||
        (tipoFiltro === 'con_receta' && medicamento.requiere_receta);

      return coincideBusqueda && coincideCategoria && coincideTipo;
    });
  }, [medicamentos, busqueda, categoriaFiltro, tipoFiltro]);

  // Función para obtener color según el estado
  const obtenerColorDisponibilidad = (medicamento: Medicamento) => {
    if (medicamento.tratamientos_activos && medicamento.tratamientos_activos > 0) {
      return 'bg-green-100 text-green-800 border-green-200';
    }
    if (medicamento.disponible) {
      return 'bg-blue-100 text-blue-800 border-blue-200';
    }
    return 'bg-gray-100 text-gray-800 border-gray-200';
  };

  // Función para obtener texto del estado
  const obtenerTextoEstado = (medicamento: Medicamento) => {
    if (medicamento.tratamientos_activos && medicamento.tratamientos_activos > 0) {
      return `En tratamiento (${medicamento.tratamientos_activos})`;
    }
    if (medicamento.disponible) {
      return 'Disponible';
    }
    return 'No disponible';
  };

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-7xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Mis Medicamentos
              </h1>
              <p className="text-gray-600 mt-2">
                Explora y busca información sobre medicamentos disponibles
              </p>
            </div>
          </div>
        </div>

        {/* Estadísticas */}
        {estadisticas && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold">{estadisticas.total_medicamentos}</div>
                    <p className="text-xs text-muted-foreground">Total medicamentos</p>
                  </div>
                  <Pill className="h-8 w-8 text-blue-600" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold text-green-600">{estadisticas.en_tratamiento}</div>
                    <p className="text-xs text-muted-foreground">En mis tratamientos</p>
                  </div>
                  <Activity className="h-8 w-8 text-green-600" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold text-blue-600">{estadisticas.disponibles}</div>
                    <p className="text-xs text-muted-foreground">Disponibles</p>
                  </div>
                  <div className="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span className="text-sm font-bold text-blue-600">✓</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Buscador y Filtros */}
        <Card className="mb-6">
          <CardContent className="pt-6">
            <div className="space-y-4">
              {/* Buscador */}
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Buscar medicamentos por nombre, principio activo o laboratorio..."
                  value={busqueda}
                  onChange={(e) => setBusqueda(e.target.value)}
                  className="pl-10"
                />
              </div>

              {/* Filtros */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Select value={categoriaFiltro} onValueChange={setCategoriaFiltro}>
                    <SelectTrigger>
                      <SelectValue placeholder="Todas las categorías" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="todas">Todas las categorías</SelectItem>
                      {categorias.map((categoria) => (
                        <SelectItem key={categoria} value={categoria}>
                          {categoria}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Select value={tipoFiltro} onValueChange={setTipoFiltro}>
                    <SelectTrigger>
                      <SelectValue placeholder="Todos los tipos" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="todos">Todos los medicamentos</SelectItem>
                      <SelectItem value="en_tratamiento">En mis tratamientos</SelectItem>
                      <SelectItem value="disponibles">Disponibles</SelectItem>
                      <SelectItem value="con_receta">Requiere receta</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="flex items-center gap-2">
                  <Filter className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm text-muted-foreground">
                    {medicamentosFiltrados.length} de {medicamentos.length} medicamentos
                  </span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Sin resultados */}
        {medicamentosFiltrados.length === 0 && medicamentos.length > 0 && (
          <Card>
            <CardContent className="text-center py-12">
              <Search className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
              <h3 className="text-lg font-medium text-muted-foreground mb-2">
                No se encontraron medicamentos
              </h3>
              <p className="text-sm text-muted-foreground mb-6">
                Intenta con otros términos de búsqueda o ajusta los filtros.
              </p>
              <Button variant="outline" onClick={() => {
                setBusqueda('');
                setCategoriaFiltro('todas');
                setTipoFiltro('todos');
              }}>
                Limpiar filtros
              </Button>
            </CardContent>
          </Card>
        )}

        {/* Lista de medicamentos */}
        {medicamentosFiltrados.length > 0 && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            {medicamentosFiltrados.map((medicamento) => (
              <Card key={medicamento.id} className="hover:shadow-lg transition-shadow">
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <CardTitle className="text-lg mb-1">{medicamento.nombre}</CardTitle>
                      {medicamento.principio_activo && (
                        <CardDescription className="text-sm">
                          {medicamento.principio_activo}
                        </CardDescription>
                      )}
                    </div>
                    <Badge className={obtenerColorDisponibilidad(medicamento)}>
                      {obtenerTextoEstado(medicamento)}
                    </Badge>
                  </div>
                </CardHeader>
                
                <CardContent className="space-y-3">
                  {/* Información básica */}
                  <div className="space-y-2 text-sm">
                    {medicamento.presentacion && (
                      <div>
                        <span className="font-medium">Presentación:</span> {medicamento.presentacion}
                      </div>
                    )}
                    {medicamento.concentracion && (
                      <div>
                        <span className="font-medium">Concentración:</span> {medicamento.concentracion}
                      </div>
                    )}
                    {medicamento.via_administracion && (
                      <div>
                        <span className="font-medium">Vía:</span> {medicamento.via_administracion}
                      </div>
                    )}
                    {medicamento.laboratorio && (
                      <div>
                        <span className="font-medium">Laboratorio:</span> {medicamento.laboratorio}
                      </div>
                    )}
                  </div>

                  {/* Indicaciones breves */}
                  {medicamento.indicaciones && (
                    <div className="p-2 bg-blue-50 rounded border-l-4 border-blue-400">
                      <p className="text-xs">
                        <span className="font-medium">Indicaciones:</span> 
                        {medicamento.indicaciones.length > 100 
                          ? `${medicamento.indicaciones.substring(0, 100)}...`
                          : medicamento.indicaciones
                        }
                      </p>
                    </div>
                  )}

                  {/* Precio y receta */}
                  <div className="flex items-center justify-between pt-2 border-t">
                    <div className="flex items-center gap-2">
                      {medicamento.requiere_receta && (
                        <Badge variant="outline" className="text-xs">
                          Receta médica
                        </Badge>
                      )}
                      {medicamento.categoria && (
                        <Badge variant="secondary" className="text-xs">
                          {medicamento.categoria}
                        </Badge>
                      )}
                    </div>
                    {medicamento.precio && (
                      <span className="text-sm font-medium">
                        ${medicamento.precio.toLocaleString()}
                      </span>
                    )}
                  </div>

                  {/* Acciones */}
                  <div className="pt-2">
                    {medicamento.tratamientos_activos && medicamento.tratamientos_activos > 0 ? (
                      <Link href="/mis-tratamientos" className="block">
                        <Button variant="outline" size="sm" className="w-full">
                          Ver en tratamientos
                        </Button>
                      </Link>
                    ) : (
                      <Link href="/mis-tratamientos/crear" className="block">
                        <Button variant="outline" size="sm" className="w-full">
                          <Plus className="h-3 w-3 mr-2" />
                          Agregar a tratamiento
                        </Button>
                      </Link>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Sin medicamentos registrados */}
        {medicamentos.length === 0 && (
          <Card>
            <CardContent className="text-center py-12">
              <Pill className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
              <h3 className="text-lg font-medium text-muted-foreground mb-2">
                No hay medicamentos disponibles
              </h3>
              <p className="text-sm text-muted-foreground mb-6">
                Actualmente no hay medicamentos registrados en el sistema.
              </p>
            </CardContent>
          </Card>
        )}

        {/* Enlaces útiles */}
        <Card>
          <CardHeader>
            <CardTitle>Enlaces útiles</CardTitle>
            <CardDescription>
              Acciones relacionadas con tus medicamentos y tratamientos
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-3 gap-4">
              <Link href="/mis-tratamientos" className="block">
                <div className="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <Activity className="h-5 w-5 text-primary" />
                    <div>
                      <h4 className="font-medium">Mis Tratamientos</h4>
                      <p className="text-sm text-muted-foreground">Ver tratamientos activos</p>
                    </div>
                    <ArrowRight className="h-4 w-4 ml-auto text-muted-foreground" />
                  </div>
                </div>
              </Link>
              <Link href="/mi-cronograma" className="block">
                <div className="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <Calendar className="h-5 w-5 text-primary" />
                    <div>
                      <h4 className="font-medium">Mi Cronograma</h4>
                      <p className="text-sm text-muted-foreground">Horarios de medicamentos</p>
                    </div>
                    <ArrowRight className="h-4 w-4 ml-auto text-muted-foreground" />
                  </div>
                </div>
              </Link>
              <Link href="/mi-perfil" className="block">
                <div className="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <User className="h-5 w-5 text-primary" />
                    <div>
                      <h4 className="font-medium">Mi Perfil</h4>
                      <p className="text-sm text-muted-foreground">Información personal</p>
                    </div>
                    <ArrowRight className="h-4 w-4 ml-auto text-muted-foreground" />
                  </div>
                </div>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppSidebarLayout>
  );
} 
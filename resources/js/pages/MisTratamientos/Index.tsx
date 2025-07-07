import React from 'react';
import { Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pill, Plus, Calendar, Clock, CheckCircle, XCircle, AlertCircle, User, ArrowRight, Eye, Edit, Activity } from 'lucide-react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo?: string;
  presentacion?: string;
  dosis: number;
  unidad_dosis: string;
  frecuencia: number;
  tipo_frecuencia: string;
  instrucciones?: string;
  tolerancia_antes?: number;
  tolerancia_despues?: number;
  duracion_dias?: number;
  estado_medicamento?: string;
}

interface Medico {
  name: string;
}

interface Tratamiento {
  id: number;
  nombre?: string;
  fecha_inicio: string;
  fecha_fin?: string;
  estado: 'activo' | 'pausado' | 'finalizado';
  indicaciones?: string;
  medicamentos: Medicamento[];
  medico?: Medico;
  adherencia?: {
    porcentaje: number;
    administraciones_completadas: number;
    administraciones_totales: number;
  };
}

interface Props {
  tratamientos?: Tratamiento[];
  estadisticas?: {
    total_activos: number;
    adherencia_promedio: number;
    proxima_administracion?: {
      fecha_hora: string;
      medicamento: string;
    };
  };
}

export default function MisTratamientos({ tratamientos = [], estadisticas }: Props) {
  const auth = useAuth();
  const user = auth.user;

  // Función para formatear fecha
  const formatearFecha = (fecha: string) => {
    return new Date(fecha).toLocaleDateString('es-CL', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Función para formatear frecuencia
  const formatearFrecuencia = (horas: number) => {
    if (horas === 24) return 'Una vez al día';
    if (horas === 12) return 'Cada 12 horas';
    if (horas === 8) return 'Cada 8 horas';
    if (horas === 6) return 'Cada 6 horas';
    if (horas === 4) return 'Cada 4 horas';
    return `Cada ${horas} horas`;
  };

  // Obtener color según estado
  const obtenerColorEstado = (estado: string) => {
    switch (estado) {
      case 'activo':
        return 'bg-green-50 text-green-700 border-green-200';
      case 'pausado':
        return 'bg-yellow-50 text-yellow-700 border-yellow-200';
      case 'finalizado':
        return 'bg-muted text-muted-foreground border-border';
      default:
        return 'bg-muted text-muted-foreground border-border';
    }
  };

  // Obtener icono según estado
  const obtenerIconoEstado = (estado: string) => {
    switch (estado) {
      case 'activo':
        return <CheckCircle className="h-5 w-5 text-green-600" />;
      case 'pausado':
        return <Clock className="h-5 w-5 text-yellow-600" />;
      case 'finalizado':
        return <XCircle className="h-5 w-5 text-muted-foreground" />;
      default:
        return <AlertCircle className="h-5 w-5 text-muted-foreground" />;
    }
  };

  // Separar tratamientos por estado
  const tratamientosActivos = tratamientos.filter(t => t.estado === 'activo');
  const tratamientosPausados = tratamientos.filter(t => t.estado === 'pausado');
  const tratamientosFinalizados = tratamientos.filter(t => t.estado === 'finalizado');

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-6xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold">
                Mis Tratamientos
              </h1>
              <p className="text-muted-foreground mt-2">
                Gestiona tus medicamentos y tratamientos activos
              </p>
            </div>
            <Link href="/mis-tratamientos/crear">
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Nuevo Tratamiento
              </Button>
            </Link>
          </div>
        </div>

        {/* Estadísticas */}
        {estadisticas && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold">{estadisticas.total_activos}</div>
                    <p className="text-xs text-muted-foreground">Tratamientos activos</p>
                  </div>
                  <Pill className="h-8 w-8 text-primary" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold">{estadisticas.adherencia_promedio}%</div>
                    <p className="text-xs text-muted-foreground">Adherencia promedio</p>
                  </div>
                  <div className={`h-8 w-8 rounded-full flex items-center justify-center ${
                    estadisticas.adherencia_promedio >= 80 ? 'bg-green-100' : 
                    estadisticas.adherencia_promedio >= 60 ? 'bg-yellow-100' : 'bg-red-100'
                  }`}>
                    <span className={`text-sm font-bold ${
                      estadisticas.adherencia_promedio >= 80 ? 'text-green-600' : 
                      estadisticas.adherencia_promedio >= 60 ? 'text-yellow-600' : 'text-red-600'
                    }`}>
                      {Math.round(estadisticas.adherencia_promedio)}
                    </span>
                  </div>
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    {estadisticas.proxima_administracion ? (
                      <>
                        <div className="text-sm font-bold">Próximo</div>
                        <p className="text-xs text-muted-foreground">
                          {estadisticas.proxima_administracion.medicamento}
                        </p>
                      </>
                    ) : (
                      <>
                        <div className="text-sm font-bold">Sin pendientes</div>
                        <p className="text-xs text-muted-foreground">
                          No hay medicamentos programados
                        </p>
                      </>
                    )}
                  </div>
                  <Clock className="h-8 w-8 text-muted-foreground" />
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Sin tratamientos - Estado de bienvenida */}
        {tratamientos.length === 0 && (
          <div className="space-y-6">
            <Card className="text-center py-12">
              <CardContent className="space-y-6">
                <div className="mx-auto p-4 bg-primary/10 rounded-full w-fit">
                  <Activity className="h-12 w-12 text-primary" />
                </div>
                <div>
                  <h3 className="text-xl font-semibold mb-2">
                    ¡Crea tu primer tratamiento!
                  </h3>
                  <p className="text-muted-foreground max-w-lg mx-auto">
                    Un tratamiento te permite organizar tus medicamentos con horarios específicos y 
                    llevar un control completo de tu adherencia. Es fácil y solo toma unos minutos.
                  </p>
                </div>
                <div className="flex flex-col sm:flex-row gap-3 justify-center">
                  <Link href="/mis-tratamientos/crear">
                    <Button size="lg">
                      <Plus className="h-5 w-5 mr-2" />
                      Crear Primer Tratamiento
                    </Button>
                  </Link>
                  <Link href="/new-user-welcome">
                    <Button variant="outline" size="lg">
                      Ver Guía Completa
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>

            {/* Pasos para crear tratamiento */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CheckCircle className="h-5 w-5 text-primary" />
                  ¿Cómo crear un tratamiento?
                </CardTitle>
                <CardDescription>
                  Sigue estos sencillos pasos para comenzar
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid md:grid-cols-3 gap-6">
                  <div className="text-center space-y-2">
                    <div className="mx-auto w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                      <span className="text-sm font-semibold text-primary">1</span>
                    </div>
                    <h4 className="font-medium">Información básica</h4>
                    <p className="text-sm text-muted-foreground">
                      Dale un nombre a tu tratamiento (ej: "Medicamentos matutinos")
                    </p>
                  </div>
                  <div className="text-center space-y-2">
                    <div className="mx-auto w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                      <span className="text-sm font-semibold text-primary">2</span>
                    </div>
                    <h4 className="font-medium">Agregar medicamentos</h4>
                    <p className="text-sm text-muted-foreground">
                      Selecciona o crea los medicamentos con sus dosis y frecuencias
                    </p>
                  </div>
                  <div className="text-center space-y-2">
                    <div className="mx-auto w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                      <span className="text-sm font-semibold text-primary">3</span>
                    </div>
                    <h4 className="font-medium">Configurar horarios</h4>
                    <p className="text-sm text-muted-foreground">
                      Define cuándo tomar cada medicamento y recibe recordatorios
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Ejemplos de tratamientos */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Pill className="h-5 w-5 text-primary" />
                  Ejemplos de tratamientos
                </CardTitle>
                <CardDescription>
                  Ideas para organizar tus medicamentos
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid md:grid-cols-2 gap-4">
                  <div className="p-4 border rounded-lg bg-muted/30">
                    <h5 className="font-medium mb-2">Medicamentos crónicos</h5>
                    <p className="text-sm text-muted-foreground">
                      Para medicamentos que tomas a largo plazo como hipertensión, diabetes, etc.
                    </p>
                  </div>
                  <div className="p-4 border rounded-lg bg-muted/30">
                    <h5 className="font-medium mb-2">Vitaminas y suplementos</h5>
                    <p className="text-sm text-muted-foreground">
                      Para vitaminas diarias, omega 3, calcio y otros suplementos.
                    </p>
                  </div>
                  <div className="p-4 border rounded-lg bg-muted/30">
                    <h5 className="font-medium mb-2">Tratamiento temporal</h5>
                    <p className="text-sm text-muted-foreground">
                      Para antibióticos, analgésicos o medicamentos con fecha de fin.
                    </p>
                  </div>
                  <div className="p-4 border rounded-lg bg-muted/30">
                    <h5 className="font-medium mb-2">Medicamentos PRN</h5>
                    <p className="text-sm text-muted-foreground">
                      Para medicamentos "según necesidad" como analgésicos de rescate.
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Tratamientos Activos */}
        {tratamientosActivos.length > 0 && (
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="h-5 w-5 text-green-600" />
                Tratamientos Activos ({tratamientosActivos.length})
              </CardTitle>
              <CardDescription>
                Medicamentos que estás tomando actualmente
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {tratamientosActivos.map((tratamiento) => (
                <div key={tratamiento.id} className="border rounded-lg p-4 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <Pill className="h-5 w-5 text-primary" />
                        <h4 className="font-medium text-lg">{tratamiento.nombre || 'Tratamiento'}</h4>
                        <Badge className={obtenerColorEstado(tratamiento.estado)}>
                          {obtenerIconoEstado(tratamiento.estado)}
                          <span className="ml-1">Activo</span>
                        </Badge>
                      </div>
                      
                      {/* Información del médico */}
                      {tratamiento.medico && (
                        <div className="text-sm text-muted-foreground mb-3">
                          <span className="font-medium">Médico:</span> Dr. {tratamiento.medico.name}
                        </div>
                      )}

                      {/* Lista de medicamentos */}
                      <div className="space-y-3 mb-3">
                        {tratamiento.medicamentos.map((medicamento, index) => (
                          <div key={medicamento.id} className="p-3 bg-background rounded border hover:shadow-sm transition-shadow duration-200">
                            <h5 className="font-medium text-primary mb-2">{medicamento.nombre}</h5>
                            <div className="grid md:grid-cols-2 gap-2 text-sm text-muted-foreground">
                              <div>
                                <span className="font-medium">Dosis:</span> {medicamento.dosis} {medicamento.unidad_dosis}
                              </div>
                              <div>
                                <span className="font-medium">Frecuencia:</span> {formatearFrecuencia(medicamento.frecuencia)}
                              </div>
                              {medicamento.principio_activo && (
                                <div className="md:col-span-2">
                                  <span className="font-medium">Principio activo:</span> {medicamento.principio_activo}
                                </div>
                              )}
                              {medicamento.instrucciones && (
                                <div className="md:col-span-2">
                                  <span className="font-medium">Instrucciones:</span> {medicamento.instrucciones}
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>

                      <div className="text-sm text-muted-foreground">
                        <span className="font-medium">Desde:</span> {formatearFecha(tratamiento.fecha_inicio)}
                      </div>

                      {tratamiento.adherencia && (
                        <div className="mt-3 p-2 bg-background rounded border">
                          <div className="flex items-center justify-between text-sm">
                            <span>Adherencia:</span>
                            <span className="font-medium">{tratamiento.adherencia.porcentaje}%</span>
                          </div>
                          <div className="w-full bg-muted rounded-full h-2 mt-1">
                            <div 
                              className={`h-2 rounded-full ${
                                tratamiento.adherencia.porcentaje >= 80 ? 'bg-green-600' : 
                                tratamiento.adherencia.porcentaje >= 60 ? 'bg-yellow-600' : 'bg-red-600'
                              }`}
                              style={{ width: `${tratamiento.adherencia.porcentaje}%` }}
                            ></div>
                          </div>
                          <p className="text-xs text-muted-foreground mt-1">
                            {tratamiento.adherencia.administraciones_completadas} de {tratamiento.adherencia.administraciones_totales} dosis completadas
                          </p>
                        </div>
                      )}

                      {tratamiento.indicaciones && (
                        <div className="mt-3 p-2 bg-primary/10 rounded border-l-4 border-primary">
                          <p className="text-sm">
                            <span className="font-medium">Indicaciones:</span> {tratamiento.indicaciones}
                          </p>
                        </div>
                      )}
                    </div>
                    
                    {/* Botones de acción */}
                    <div className="flex space-x-2 ml-4">
                      <Link href={route('mis-tratamientos.show', tratamiento.id)}>
                        <Button variant="outline" size="sm">
                          <Eye className="h-4 w-4" />
                        </Button>
                      </Link>
                      <Link href={route('mis-tratamientos.edit', tratamiento.id)}>
                        <Button variant="outline" size="sm">
                          <Edit className="h-4 w-4" />
                        </Button>
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Tratamientos Pausados */}
        {tratamientosPausados.length > 0 && (
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-5 w-5 text-yellow-600" />
                Tratamientos Pausados ({tratamientosPausados.length})
              </CardTitle>
              <CardDescription>
                Medicamentos temporalmente pausados
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {tratamientosPausados.map((tratamiento) => (
                <div key={tratamiento.id} className="border rounded-lg p-4 bg-yellow-50 hover:bg-yellow-100 transition-colors duration-200">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <Pill className="h-5 w-5 text-muted-foreground" />
                        <h4 className="font-medium text-lg">{tratamiento.nombre || 'Tratamiento'}</h4>
                        <Badge className={obtenerColorEstado(tratamiento.estado)}>
                          {obtenerIconoEstado(tratamiento.estado)}
                          <span className="ml-1">Pausado</span>
                        </Badge>
                      </div>
                      <div className="text-sm text-muted-foreground">
                        <span className="font-medium">Medicamentos:</span> {tratamiento.medicamentos.map(m => m.nombre).join(', ')}
                      </div>
                      <div className="text-sm text-muted-foreground mt-1">
                        <span className="font-medium">Desde:</span> {formatearFecha(tratamiento.fecha_inicio)}
                      </div>
                    </div>
                    
                    {/* Botones de acción */}
                    <div className="flex space-x-2 ml-4">
                      <Link href={route('mis-tratamientos.show', tratamiento.id)}>
                        <Button variant="outline" size="sm">
                          <Eye className="h-4 w-4" />
                        </Button>
                      </Link>
                      <Link href={route('mis-tratamientos.edit', tratamiento.id)}>
                        <Button variant="outline" size="sm">
                          <Edit className="h-4 w-4" />
                        </Button>
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Tratamientos Finalizados */}
        {tratamientosFinalizados.length > 0 && (
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <XCircle className="h-5 w-5 text-muted-foreground" />
                Tratamientos Finalizados ({tratamientosFinalizados.length})
              </CardTitle>
              <CardDescription>
                Medicamentos que has completado
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {tratamientosFinalizados.slice(0, 3).map((tratamiento) => (
                <div key={tratamiento.id} className="border rounded-lg p-4 bg-muted/50 hover:bg-muted transition-colors duration-200">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <Pill className="h-5 w-5 text-muted-foreground" />
                        <h4 className="font-medium text-lg">{tratamiento.nombre || 'Tratamiento'}</h4>
                        <Badge className={obtenerColorEstado(tratamiento.estado)}>
                          {obtenerIconoEstado(tratamiento.estado)}
                          <span className="ml-1">Finalizado</span>
                        </Badge>
                      </div>
                      <div className="text-sm text-muted-foreground mb-1">
                        <span className="font-medium">Medicamentos:</span> {tratamiento.medicamentos.map(m => m.nombre).join(', ')}
                      </div>
                      <div className="text-sm text-muted-foreground">
                        <span className="font-medium">Período:</span> {formatearFecha(tratamiento.fecha_inicio)} - {tratamiento.fecha_fin ? formatearFecha(tratamiento.fecha_fin) : 'Fecha no especificada'}
                      </div>
                    </div>
                    
                    {/* Botones de acción */}
                    <div className="flex space-x-2 ml-4">
                      <Link href={route('mis-tratamientos.show', tratamiento.id)}>
                        <Button variant="outline" size="sm">
                          <Eye className="h-4 w-4" />
                        </Button>
                      </Link>
                      <Link href={route('mis-tratamientos.edit', tratamiento.id)}>
                        <Button variant="outline" size="sm">
                          <Edit className="h-4 w-4" />
                        </Button>
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
              {tratamientosFinalizados.length > 3 && (
                <p className="text-sm text-muted-foreground text-center py-2">
                  Y {tratamientosFinalizados.length - 3} tratamientos finalizados más...
                </p>
              )}
            </CardContent>
          </Card>
        )}

        {/* Enlaces útiles */}
        <Card>
          <CardHeader>
            <CardTitle>Enlaces útiles</CardTitle>
            <CardDescription>
              Acciones relacionadas con tus tratamientos
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-2 gap-4">
              <Link href="/mi-cronograma" className="block">
                <div className="p-4 border rounded-lg hover:bg-primary/5 hover:border-primary/50 transition-all duration-200">
                  <div className="flex items-center gap-3">
                    <Calendar className="h-5 w-5 text-primary" />
                    <div>
                      <h4 className="font-medium">Mi Cronograma</h4>
                      <p className="text-sm text-muted-foreground">Ver horarios de medicamentos de hoy</p>
                    </div>
                    <ArrowRight className="h-4 w-4 ml-auto text-muted-foreground" />
                  </div>
                </div>
              </Link>
              <Link href="/mi-perfil" className="block">
                <div className="p-4 border rounded-lg hover:bg-primary/5 hover:border-primary/50 transition-all duration-200">
                  <div className="flex items-center gap-3">
                    <User className="h-5 w-5 text-primary" />
                    <div>
                      <h4 className="font-medium">Mi Perfil</h4>
                      <p className="text-sm text-muted-foreground">Actualizar información médica</p>
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
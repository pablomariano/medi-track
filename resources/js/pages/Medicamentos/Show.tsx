import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import { 
  Pill, 
  Edit, 
  ArrowLeft, 
  AlertTriangle, 
  CheckCircle,
  Calendar,
  User,
  Activity,
  Clock,
  FileText,
  BarChart3,
  Stethoscope,
  Heart
} from 'lucide-react';

interface Paciente {
  id: number;
  nombre: string;
}

interface Tratamiento {
  id: number;
  nombre: string;
  tipo: string;
  estado: string;
  fecha_inicio: string;
  fecha_fin?: string;
  paciente: Paciente;
  pivot: {
    dosis_cantidad: number;
    unidad_dosis: string;
    frecuencia_horas?: number;
    instrucciones_especiales?: string;
    estado: string;
  };
}

interface Cuidador {
  id: number;
  name: string;
}

interface Administracion {
  id: number;
  fecha_hora_programada?: string;
  fecha_hora_administrada: string;
  dosis_administrada: number;
  estado: string;
  observaciones?: string;
  efectos_adversos?: string;
  cuidador?: Cuidador;
}

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
  categoria_terapeutica?: string;
  laboratorio?: string;
  codigo_barras?: string;
  registro_sanitario?: string;
  contraindicaciones?: string;
  efectos_secundarios?: string;
  interacciones?: string;
  activo: boolean;
  created_at: string;
  updated_at: string;
  tratamientos: Tratamiento[];
  administraciones: Administracion[];
}

interface Props {
  medicamento: Medicamento;
}

export default function Show({ medicamento }: Props) {
  const formatearFecha = (fecha: string) => {
    return new Date(fecha).toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatearFechaHora = (fecha: string) => {
    return new Date(fecha).toLocaleString('es-ES', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getEstadoBadge = (estado: string) => {
    const config = {
      'Activo': { variant: 'default' as const, color: 'text-green-600 border-green-200' },
      'Pausado': { variant: 'outline' as const, color: 'text-yellow-600 border-yellow-200' },
      'Suspendido': { variant: 'outline' as const, color: 'text-red-600 border-red-200' },
      'Completado': { variant: 'outline' as const, color: 'text-blue-600 border-blue-200' },
      'Administrada': { variant: 'default' as const, color: 'text-green-600 border-green-200' },
      'Pendiente': { variant: 'outline' as const, color: 'text-orange-600 border-orange-200' },
      'Omitida': { variant: 'outline' as const, color: 'text-red-600 border-red-200' },
      'Tardía': { variant: 'outline' as const, color: 'text-yellow-600 border-yellow-200' }
    };

    const { variant, color } = config[estado as keyof typeof config] || { variant: 'outline' as const, color: '' };
    
    return (
      <Badge variant={variant} className={color}>
        {estado}
      </Badge>
    );
  };

  // Estadísticas calculadas
  const administracionesAdministradas = medicamento.administraciones.filter(a => a.estado === 'Administrada').length;
  const administracionesTotales = medicamento.administraciones.length;
  const tratamientosActivos = medicamento.tratamientos.filter(t => t.estado === 'Activo').length;
  const pacientesUnicos = new Set(medicamento.tratamientos.map(t => t.paciente.id)).size;

  return (
    <AppLayout>
      <Head title={`${medicamento.nombre} - Detalles`} />

      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <Link href="/medicamentos-datatable">
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Volver al listado
              </Button>
            </Link>
            <div className="flex items-center space-x-2">
              <Pill className="h-8 w-8 text-blue-600" />
              <div>
                <h1 className="text-3xl font-bold text-foreground">{medicamento.nombre}</h1>
                <p className="text-muted-foreground">
                  {medicamento.concentracion} {medicamento.unidad_concentracion}
                </p>
              </div>
            </div>
          </div>
          
          <div className="flex items-center space-x-2">
            <Badge variant={medicamento.activo ? "default" : "secondary"}>
              {medicamento.activo ? "Activo" : "Inactivo"}
            </Badge>
            {medicamento.requiere_receta && (
              <Badge variant="outline" className="text-orange-600 border-orange-200">
                <AlertTriangle className="h-3 w-3 mr-1" />
                Requiere Receta
              </Badge>
            )}
            <Link href={route('medicamentos.edit', medicamento.id)}>
              <Button>
                <Edit className="h-4 w-4 mr-2" />
                Editar
              </Button>
            </Link>
          </div>
        </div>

        {/* Estadísticas Rápidas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tratamientos Activos</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{tratamientosActivos}</div>
              <p className="text-xs text-muted-foreground">
                de {medicamento.tratamientos.length} totales
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pacientes</CardTitle>
              <User className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{pacientesUnicos}</div>
              <p className="text-xs text-muted-foreground">únicos</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Administraciones</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-purple-600">{administracionesTotales}</div>
              <p className="text-xs text-muted-foreground">registros totales</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tasa de Cumplimiento</CardTitle>
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-emerald-600">
                {administracionesTotales > 0 ? Math.round((administracionesAdministradas / administracionesTotales) * 100) : 0}%
              </div>
              <p className="text-xs text-muted-foreground">
                {administracionesAdministradas} de {administracionesTotales}
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Información del Medicamento */}
          <div className="lg:col-span-2 space-y-6">
            {/* Información Básica */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <Pill className="h-5 w-5" />
                  <span>Información del Medicamento</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">Principio Activo</label>
                    <p className="text-foreground">{medicamento.principio_activo}</p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">Forma Farmacéutica</label>
                    <p className="text-foreground">{medicamento.forma_farmaceutica}</p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">Vía de Administración</label>
                    <p className="text-foreground">{medicamento.via_administracion}</p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">Presentación</label>
                    <p className="text-foreground">
                      {medicamento.presentacion}
                      {medicamento.unidades_por_presentacion && (
                        <span className="text-muted-foreground"> ({medicamento.unidades_por_presentacion} unidades)</span>
                      )}
                    </p>
                  </div>
                  {medicamento.categoria_terapeutica && (
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Categoría Terapéutica</label>
                      <p className="text-foreground">{medicamento.categoria_terapeutica}</p>
                    </div>
                  )}
                  {medicamento.laboratorio && (
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Laboratorio</label>
                      <p className="text-foreground">{medicamento.laboratorio}</p>
                    </div>
                  )}
                  {medicamento.codigo_barras && (
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Código de Barras</label>
                      <p className="text-foreground font-mono">{medicamento.codigo_barras}</p>
                    </div>
                  )}
                  {medicamento.registro_sanitario && (
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Registro Sanitario</label>
                      <p className="text-foreground font-mono">{medicamento.registro_sanitario}</p>
                    </div>
                  )}
                </div>

                {(medicamento.contraindicaciones || medicamento.efectos_secundarios || medicamento.interacciones) && (
                  <>
                    <Separator />
                    <div className="space-y-4">
                      {medicamento.contraindicaciones && (
                        <div>
                          <label className="text-sm font-medium text-muted-foreground flex items-center space-x-1">
                            <AlertTriangle className="h-4 w-4 text-red-500" />
                            <span>Contraindicaciones</span>
                          </label>
                          <p className="text-foreground text-sm mt-1">{medicamento.contraindicaciones}</p>
                        </div>
                      )}
                      {medicamento.efectos_secundarios && (
                        <div>
                          <label className="text-sm font-medium text-muted-foreground flex items-center space-x-1">
                            <Heart className="h-4 w-4 text-orange-500" />
                            <span>Efectos Secundarios</span>
                          </label>
                          <p className="text-foreground text-sm mt-1">{medicamento.efectos_secundarios}</p>
                        </div>
                      )}
                      {medicamento.interacciones && (
                        <div>
                          <label className="text-sm font-medium text-muted-foreground flex items-center space-x-1">
                            <Stethoscope className="h-4 w-4 text-blue-500" />
                            <span>Interacciones</span>
                          </label>
                          <p className="text-foreground text-sm mt-1">{medicamento.interacciones}</p>
                        </div>
                      )}
                    </div>
                  </>
                )}
              </CardContent>
            </Card>

            {/* Tratamientos Asociados */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <Activity className="h-5 w-5" />
                  <span>Tratamientos Asociados</span>
                </CardTitle>
                <CardDescription>
                  {medicamento.tratamientos.length} tratamientos utilizan este medicamento
                </CardDescription>
              </CardHeader>
              <CardContent>
                {medicamento.tratamientos.length === 0 ? (
                  <div className="text-center py-8">
                    <Activity className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-semibold text-muted-foreground mb-2">
                      Sin tratamientos asociados
                    </h3>
                    <p className="text-muted-foreground">
                      Este medicamento no está siendo utilizado en ningún tratamiento actualmente.
                    </p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {medicamento.tratamientos.map((tratamiento) => (
                      <div key={tratamiento.id} className="p-4 border rounded-lg">
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center space-x-2 mb-2">
                              <h4 className="font-semibold">{tratamiento.nombre}</h4>
                              {getEstadoBadge(tratamiento.estado)}
                              <Badge variant="outline">{tratamiento.tipo}</Badge>
                            </div>
                            <p className="text-sm text-muted-foreground mb-2">
                              <User className="h-4 w-4 inline mr-1" />
                              {tratamiento.paciente.nombre}
                            </p>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                              <div>
                                <span className="font-medium">Dosis:</span> {tratamiento.pivot.dosis_cantidad} {tratamiento.pivot.unidad_dosis}
                              </div>
                              {tratamiento.pivot.frecuencia_horas && (
                                <div>
                                  <span className="font-medium">Frecuencia:</span> cada {tratamiento.pivot.frecuencia_horas} horas
                                </div>
                              )}
                              <div>
                                <span className="font-medium">Inicio:</span> {formatearFecha(tratamiento.fecha_inicio)}
                              </div>
                              {tratamiento.fecha_fin && (
                                <div>
                                  <span className="font-medium">Fin:</span> {formatearFecha(tratamiento.fecha_fin)}
                                </div>
                              )}
                            </div>
                            {tratamiento.pivot.instrucciones_especiales && (
                              <div className="mt-2 p-2 bg-muted rounded text-sm">
                                <span className="font-medium">Instrucciones:</span> {tratamiento.pivot.instrucciones_especiales}
                              </div>
                            )}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Panel Lateral */}
          <div className="space-y-6">
            {/* Administraciones Recientes */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <Clock className="h-5 w-5" />
                  <span>Administraciones Recientes</span>
                </CardTitle>
                <CardDescription>
                  Últimas {Math.min(5, medicamento.administraciones.length)} administraciones
                </CardDescription>
              </CardHeader>
              <CardContent>
                {medicamento.administraciones.length === 0 ? (
                  <div className="text-center py-6">
                    <Clock className="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p className="text-sm text-muted-foreground">Sin administraciones registradas</p>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {medicamento.administraciones
                      .sort((a, b) => new Date(b.fecha_hora_administrada).getTime() - new Date(a.fecha_hora_administrada).getTime())
                      .slice(0, 5)
                      .map((administracion) => (
                        <div key={administracion.id} className="p-3 border rounded-lg">
                          <div className="flex items-center justify-between mb-2">
                            {getEstadoBadge(administracion.estado)}
                            <span className="text-xs text-muted-foreground">
                              {formatearFechaHora(administracion.fecha_hora_administrada)}
                            </span>
                          </div>
                          <div className="text-sm">
                            <div>Dosis: {administracion.dosis_administrada}</div>
                            {administracion.cuidador && (
                              <div className="text-muted-foreground">
                                Por: {administracion.cuidador.name}
                              </div>
                            )}
                          </div>
                          {administracion.observaciones && (
                            <p className="text-xs text-muted-foreground mt-1">
                              {administracion.observaciones}
                            </p>
                          )}
                        </div>
                      ))}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Información Adicional */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <FileText className="h-5 w-5" />
                  <span>Información del Sistema</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Fecha de Registro</label>
                  <p className="text-sm">{formatearFecha(medicamento.created_at)}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Última Actualización</label>
                  <p className="text-sm">{formatearFecha(medicamento.updated_at)}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Estado</label>
                  <div className="flex items-center space-x-2 mt-1">
                    {medicamento.activo ? (
                      <CheckCircle className="h-4 w-4 text-green-500" />
                    ) : (
                      <AlertTriangle className="h-4 w-4 text-red-500" />
                    )}
                    <span className="text-sm">{medicamento.activo ? 'Activo' : 'Inactivo'}</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}